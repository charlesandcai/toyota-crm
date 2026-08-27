<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/database.php';

/**
 * Generates calendar events dynamically from the leads / activities / holidays
 * data model. No events are stored redundantly; everything is derived on demand.
 *
 * Event types:
 *   Follow-up, Test Drive, Financing, Approved, Booked, Reserved, With PO,
 *   Downpayment, Released, Release Anniversary, Birthday, Holiday.
 *
 * Leads table remains the single source of truth for customer information.
 */
class CalendarEventService
{
    private PDO $db;

    private const COLORS = [
        'followup'    => '#2E86C1',
        'test_drive'  => '#18A999',
        'financing'   => '#6C3483',
        'approved'    => '#229954',
        'booked'      => '#B7950B',
        'reserved'    => '#8E6CC3',
        'with_po'     => '#E67E22',
        'downpayment' => '#C0392B',
        'released'    => '#34495E',
        'anniversary' => '#1F618D',
        'birthday'    => '#C2185B',
        'holiday'     => '#7F8C8D',
    ];

    private const TYPE_LABELS = [
        'followup'    => 'Follow-up',
        'test_drive'  => 'Test Drive',
        'financing'   => 'Financing',
        'approved'    => 'Approved',
        'booked'      => 'Booked',
        'reserved'    => 'Reserved',
        'with_po'     => 'With PO',
        'downpayment' => 'Downpayment',
        'released'    => 'Released',
        'anniversary' => 'Release Anniversary',
        'birthday'    => 'Birthday',
        'holiday'     => 'Holiday',
    ];

    private const STAGE_EVENTS = [
        'Test Drive'      => 'test_drive',
        'Financing Appli' => 'financing',
        'Approved'        => 'approved',
        'Booked'          => 'booked',
        'Reserved'        => 'reserved',
        'With PO'         => 'with_po',
        'Downpayment'     => 'downpayment',
        'Released'        => 'released',
    ];

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public static function colors(): array
    {
        return self::COLORS;
    }

    public static function typeLabels(): array
    {
        return self::TYPE_LABELS;
    }

    /**
     * Return all derived events between start and end (inclusive).
     */
    public function getEvents(string $start, string $end, ?int $userId = null): array
    {
        $events = [];

        if ($start === '' || $end === '' || $start > $end) {
            return $events;
        }

        foreach ($this->loadLeads($userId) as $lead) {
            // Pipeline stage event (or a generic follow-up when no stage maps).
            $stageType = self::STAGE_EVENTS[$lead['stage_name'] ?? ''] ?? null;

            if (!empty($lead['next_step_date'])) {
                $occurrence = $lead['next_step_date'];
                if ($occurrence >= $start && $occurrence <= $end) {
                    $typeKey = $stageType !== null ? $stageType : 'followup';
                    $events[] = $this->leadEvent($lead, $typeKey, $occurrence);
                }
            }

            // Birthday — annual recurrence.
            if (!empty($lead['birthday'])) {
                foreach ($this->annualOccurrences($lead['birthday'], $start, $end) as $occurrence) {
                    $events[] = $this->leadEvent($lead, 'birthday', $occurrence);
                }
            }

            // Release anniversary — annual recurrence from release date.
            if (!empty($lead['release_date'])) {
                foreach ($this->annualOccurrences($lead['release_date'], $start, $end) as $occurrence) {
                    $events[] = $this->leadEvent($lead, 'anniversary', $occurrence);
                }
            }
        }

        foreach ($this->loadHolidays($start, $end) as $holiday) {
            $events[] = [
                'id'            => 'holiday-' . $holiday['id'],
                'type'          => 'holiday',
                'type_label'    => 'Holiday',
                'title'         => $holiday['name'],
                'date'          => $holiday['holiday_date'],
                'color'         => self::COLORS['holiday'],
                'lead_id'       => null,
                'lead_url'      => null,
                'lead_name'     => null,
                'model_name'    => null,
                'stage_name'    => null,
                'priority_name' => null,
                'next_step'     => null,
                'notes'         => null,
                'recurring'     => false,
            ];
        }

        usort($events, static function (array $a, array $b): int {
            return $a['date'] <=> $b['date'];
        });

        return $events;
    }

    private function leadEvent(array $lead, string $typeKey, string $date): array
    {
        $label = self::TYPE_LABELS[$typeKey] ?? ucfirst($typeKey);

        return [
            'id'            => $typeKey . '-' . $lead['id'],
            'type'          => $typeKey,
            'type_label'    => $label,
            'title'         => $label . ' — ' . ($lead['lead_name'] ?? ''),
            'date'          => $date,
            'color'         => self::COLORS[$typeKey] ?? '#6c757d',
            'lead_id'       => (int) $lead['id'],
            'lead_url'      => Url::route('leads/' . $lead['id']),
            'lead_name'     => $lead['lead_name'] ?? null,
            'model_name'    => $lead['model_name'] ?? null,
            'stage_name'    => $lead['stage_name'] ?? null,
            'priority_name' => $lead['priority_name'] ?? null,
            'next_step'     => $lead['next_step'] ?? null,
            'notes'         => $lead['notes'] ?? null,
            'recurring'     => in_array($typeKey, ['birthday', 'anniversary'], true),
        ];
    }

    /**
     * Active (non-archived) leads, scoped to the current user for
     * non-admin users, with the joined stage/priority/model names.
     */
    private function loadLeads(?int $userId): array
    {
        $conditions = ['l.archived = 0'];
        $params = [];

        if ($userId !== null) {
            $conditions[] = 'l.user_id = ?';
            $params[] = $userId;
        }

        $stmt = $this->db->prepare(
            "SELECT l.*,
                    os.name AS stage_name,
                    p.name AS priority_name,
                    vm.name AS model_name
             FROM leads l
             LEFT JOIN opportunity_stages os ON l.opportunity_stage_id = os.id
             LEFT JOIN priorities p ON l.priority_id = p.id
             LEFT JOIN vehicle_models vm ON l.model_id = vm.id
             WHERE " . implode(' AND ', $conditions)
        );
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    private function loadHolidays(string $start, string $end): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM holidays
             WHERE holiday_date BETWEEN ? AND ?
             ORDER BY holiday_date"
        );
        $stmt->execute([$start, $end]);

        return $stmt->fetchAll();
    }

    /**
     * Monthly/day occurrences of an annual date within the requested range.
     * Feb 29 birthdays collapse to Feb 28 on non-leap years.
     */
    private function annualOccurrences(string $date, string $start, string $end): array
    {
        $startYear = (int) date('Y', strtotime($start));
        $endYear = (int) date('Y', strtotime($end));
        $month = (int) date('n', strtotime($date));
        $day = (int) date('j', strtotime($date));

        $occurrences = [];
        for ($year = $startYear; $year <= $endYear; $year++) {
            $occurrence = $this->buildDate($year, $month, $day);
            if ($occurrence >= $start && $occurrence <= $end) {
                $occurrences[] = $occurrence;
            }
        }

        return $occurrences;
    }

    private function buildDate(int $year, int $month, int $day): string
    {
        $lastDay = (int) date('t', mktime(0, 0, 0, $month, 1, $year));
        $safeDay = min($day, $lastDay);
        return sprintf('%04d-%02d-%02d', $year, $month, $safeDay);
    }
}