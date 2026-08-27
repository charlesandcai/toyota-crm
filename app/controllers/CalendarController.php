<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/models/Model.php';
require_once dirname(__DIR__, 2) . '/app/models/SettingsModel.php';
require_once dirname(__DIR__, 2) . '/app/services/WorkingDaysService.php';
require_once dirname(__DIR__, 2) . '/app/services/CalendarEventService.php';

class CalendarController
{
    private CalendarEventService $calendar;

    public function __construct()
    {
        $this->calendar = new CalendarEventService();
    }

    public function index(): void
    {
        Security::requireAuth();

        $settingsModel = new SettingsModel();
        $workingDaysService = new WorkingDaysService();

        $workingDays = $workingDaysService->getWorkingDaysConfig();
        $year = (int) date('Y');
        $holidays = $settingsModel->getHolidays($year);
        $colors = CalendarEventService::colors();
        $typeLabels = CalendarEventService::typeLabels();

        $activePage = 'calendar';
        Response::view('calendar.index', compact(
            'activePage', 'workingDays', 'holidays', 'year', 'colors', 'typeLabels'
        ));
    }

    public function events(): void
    {
        Security::requireAuth();

        $start = $_GET['start'] ?? date('Y-m-d');
        $end = $_GET['end'] ?? $start;

        if (!$this->isValidDate($start) || !$this->isValidDate($end) || $start > $end) {
            Response::error('Invalid date range.', [], 400);
            return;
        }

        $userId = Security::isAdmin() ? null : Security::userId();
        $events = $this->calendar->getEvents($start, $end, $userId);

        Response::json(['events' => $events]);
    }

    private function isValidDate(string $date): bool
    {
        $parsed = DateTime::createFromFormat('Y-m-d', $date);
        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }
}