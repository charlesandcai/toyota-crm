<?php
declare(strict_types=1);

require_once __DIR__ . '/Model.php';

class LeadModel extends Model
{
    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT l.*, 
                    ls.name as status_name, ls.color as status_color,
                    os.name as stage_name, os.color as stage_color,
                    p.name as priority_name, p.color as priority_color, p.level as priority_level,
                    src.name as source_name,
                    vm.name as model_name,
                    vc.name as color_name
             FROM leads l
             LEFT JOIN lead_statuses ls ON l.status_id = ls.id
             LEFT JOIN opportunity_stages os ON l.opportunity_stage_id = os.id
             LEFT JOIN priorities p ON l.priority_id = p.id
             LEFT JOIN lead_sources src ON l.source_id = src.id
             LEFT JOIN vehicle_models vm ON l.model_id = vm.id
             LEFT JOIN vehicle_colors vc ON l.color_id = vc.id
             WHERE l.id = ? AND l.archived = 0",
            [$id]
        );
    }

    public function findByLeadId(string $leadId): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM leads WHERE lead_id = ?",
            [$leadId]
        );
    }

    public function findWithDetails(array $filters = [], string $sort = 'l.next_step_date', string $direction = 'ASC', int $offset = 0, int $limit = 25): array
    {
        $conditions = ['l.archived = 0'];
        $params = [];

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $conditions[] = "(l.lead_name LIKE ? OR l.phone LIKE ? OR l.email LIKE ? OR l.company LIKE ? OR l.lead_id LIKE ?)";
            $params = array_merge($params, [$search, $search, $search, $search, $search]);
        }
        if (!empty($filters['status_id'])) {
            $conditions[] = "l.status_id = ?";
            $params[] = (int) $filters['status_id'];
        }
        if (!empty($filters['opportunity_stage_id'])) {
            $conditions[] = "l.opportunity_stage_id = ?";
            $params[] = (int) $filters['opportunity_stage_id'];
        }
        if (!empty($filters['priority_id'])) {
            $conditions[] = "l.priority_id = ?";
            $params[] = (int) $filters['priority_id'];
        }
        if (!empty($filters['source_id'])) {
            $conditions[] = "l.source_id = ?";
            $params[] = (int) $filters['source_id'];
        }
        if (!empty($filters['model_id'])) {
            $conditions[] = "l.model_id = ?";
            $params[] = (int) $filters['model_id'];
        }
        if (!empty($filters['date_from'])) {
            $conditions[] = "l.created_at >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $conditions[] = "l.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        if (!empty($filters['quick_filter'])) {
            $qf = $filters['quick_filter'];
            $today = date('Y-m-d');
            if ($qf === 'new') {
                $conditions[] = "ls.name = 'New Lead'";
            } elseif ($qf === 'hot') {
                $conditions[] = "(p.name = 'High' OR os.sort_order >= 5)";
            } elseif ($qf === 'followup') {
                $conditions[] = "l.next_step_date IS NOT NULL";
            } elseif ($qf === 'due_today') {
                $conditions[] = "l.next_step_date = ?";
                $params[] = $today;
            } elseif ($qf === 'overdue') {
                $conditions[] = "l.next_step_date IS NOT NULL";
                $conditions[] = "l.next_step_date < ?";
                $params[] = $today;
            } elseif ($qf === 'active_deals') {
                $conditions[] = "l.opportunity_stage_id IS NOT NULL";
            } elseif ($qf === 'lost') {
                $conditions[] = "ls.name = 'Lost'";
            }
        }

        $where = implode(' AND ', $conditions);

        $totalResult = $this->fetchOne(
            "SELECT COUNT(*) as count FROM leads l 
             LEFT JOIN lead_statuses ls ON l.status_id = ls.id
             LEFT JOIN opportunity_stages os ON l.opportunity_stage_id = os.id
             LEFT JOIN priorities p ON l.priority_id = p.id
             WHERE {$where}",
            $params
        );
        $total = (int) ($totalResult['count'] ?? 0);

        $allowedSorts = [
            'l.next_step_date' => 'l.next_step_date',
            'l.last_contact_date' => 'l.last_contact_date',
            'l.created_at' => 'l.created_at',
            'l.lead_name' => 'l.lead_name',
            'p.level' => 'p.level',
        ];
        $sortCol = $allowedSorts[$sort] ?? 'l.next_step_date';
        $sortDir = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        $nullOrder = "CASE WHEN {$sortCol} IS NULL THEN 1 ELSE 0 END";

        $leads = $this->fetchAll(
            "SELECT l.*, 
                    ls.name as status_name, ls.color as status_color,
                    os.name as stage_name, os.color as stage_color,
                    p.name as priority_name, p.color as priority_color, p.level as priority_level,
                    src.name as source_name,
                    vm.name as model_name,
                    vc.name as color_name,
                    DATEDIFF(CURDATE(), l.last_contact_date) as days_since_contact
             FROM leads l
             LEFT JOIN lead_statuses ls ON l.status_id = ls.id
             LEFT JOIN opportunity_stages os ON l.opportunity_stage_id = os.id
             LEFT JOIN priorities p ON l.priority_id = p.id
             LEFT JOIN lead_sources src ON l.source_id = src.id
             LEFT JOIN vehicle_models vm ON l.model_id = vm.id
             LEFT JOIN vehicle_colors vc ON l.color_id = vc.id
             WHERE {$where}
             ORDER BY {$nullOrder}, {$sortCol} {$sortDir}
             LIMIT ? OFFSET ?",
            array_merge($params, [$limit, $offset])
        );

        return ['leads' => $leads, 'total' => $total];
    }

    public function generateLeadId(): string
    {
        $last = $this->fetchOne("SELECT lead_id FROM leads ORDER BY id DESC LIMIT 1");
        if ($last) {
            $num = (int) substr($last['lead_id'], 1);
            return 'C' . str_pad((string)($num + 1), 4, '0', STR_PAD_LEFT);
        }
        return 'C0001';
    }

    public function create(array $data): int
    {
        return $this->insert('leads', $data);
    }

    public function updateById(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update('leads', $data, 'id = ?', [$id]);
    }

    public function archive(int $id): bool
    {
        return $this->updateById($id, ['archived' => 1, 'archived_at' => date('Y-m-d H:i:s')]);
    }

    public function restore(int $id): bool
    {
        return $this->updateById($id, ['archived' => 0, 'archived_at' => null]);
    }

    public function forceDelete(int $id): bool
    {
        return $this->delete('leads', 'id = ?', [$id]);
    }

    public function findByIdForAdmin(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT l.*, 
                    ls.name as status_name, ls.color as status_color,
                    os.name as stage_name, os.color as stage_color,
                    p.name as priority_name, p.color as priority_color,
                    src.name as source_name,
                    vm.name as model_name,
                    vc.name as color_name
             FROM leads l
             LEFT JOIN lead_statuses ls ON l.status_id = ls.id
             LEFT JOIN opportunity_stages os ON l.opportunity_stage_id = os.id
             LEFT JOIN priorities p ON l.priority_id = p.id
             LEFT JOIN lead_sources src ON l.source_id = src.id
             LEFT JOIN vehicle_models vm ON l.model_id = vm.id
             LEFT JOIN vehicle_colors vc ON l.color_id = vc.id
             WHERE l.id = ?",
            [$id]
        );
    }

    public function findArchived(array $filters = [], string $sort = 'l.archived_at', string $direction = 'DESC', int $offset = 0, int $limit = 25): array
    {
        $conditions = ['l.archived = 1'];
        $params = [];

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $conditions[] = "(l.lead_name LIKE ? OR l.phone LIKE ? OR l.email LIKE ? OR l.lead_id LIKE ?)";
            $params = array_merge($params, [$search, $search, $search, $search]);
        }
        if (!empty($filters['status_id'])) {
            $conditions[] = "l.status_id = ?";
            $params[] = (int) $filters['status_id'];
        }
        if (!empty($filters['opportunity_stage_id'])) {
            $conditions[] = "l.opportunity_stage_id = ?";
            $params[] = (int) $filters['opportunity_stage_id'];
        }
        if (!empty($filters['priority_id'])) {
            $conditions[] = "l.priority_id = ?";
            $params[] = (int) $filters['priority_id'];
        }
        if (!empty($filters['source_id'])) {
            $conditions[] = "l.source_id = ?";
            $params[] = (int) $filters['source_id'];
        }

        $where = implode(' AND ', $conditions);

        $totalResult = $this->fetchOne(
            "SELECT COUNT(*) as count FROM leads l WHERE {$where}",
            $params
        );
        $total = (int) ($totalResult['count'] ?? 0);

        $allowedSorts = [
            'l.archived_at' => 'l.archived_at',
            'l.lead_id' => 'l.lead_id',
            'l.lead_name' => 'l.lead_name',
            'l.last_contact_date' => 'l.last_contact_date',
            'l.created_at' => 'l.created_at',
        ];
        $sortCol = $allowedSorts[$sort] ?? 'l.archived_at';
        $sortDir = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        $leads = $this->fetchAll(
            "SELECT l.*, 
                    ls.name as status_name, ls.color as status_color,
                    os.name as stage_name, os.color as stage_color,
                    p.name as priority_name, p.color as priority_color,
                    src.name as source_name,
                    vm.name as model_name,
                    vc.name as color_name
             FROM leads l
             LEFT JOIN lead_statuses ls ON l.status_id = ls.id
             LEFT JOIN opportunity_stages os ON l.opportunity_stage_id = os.id
             LEFT JOIN priorities p ON l.priority_id = p.id
             LEFT JOIN lead_sources src ON l.source_id = src.id
             LEFT JOIN vehicle_models vm ON l.model_id = vm.id
             LEFT JOIN vehicle_colors vc ON l.color_id = vc.id
             WHERE {$where}
             ORDER BY {$sortCol} {$sortDir}
             LIMIT ? OFFSET ?",
            array_merge($params, [$limit, $offset])
        );

        return ['leads' => $leads, 'total' => $total];
    }

    public function getArchivedCount(): int
    {
        return $this->count('leads', 'archived = 1');
    }

    public function getTotalCount(): int
    {
        return $this->count('leads', 'archived = 0');
    }

    public function getActiveDealsCount(): int
    {
        return $this->count('leads', 'archived = 0 AND opportunity_stage_id IS NOT NULL');
    }

    public function getWarmLeadsCount(): int
    {
        $result = $this->fetchOne(
            "SELECT COUNT(*) as count FROM leads l
             LEFT JOIN priorities p ON l.priority_id = p.id
             LEFT JOIN opportunity_stages os ON l.opportunity_stage_id = os.id
             WHERE l.archived = 0 AND (p.name = 'High' OR os.sort_order >= 5)"
        );
        return (int) ($result['count'] ?? 0);
    }

    public function getDealsToCloseCount(): int
    {
        $result = $this->fetchOne(
            "SELECT COUNT(*) as count FROM leads l
             LEFT JOIN opportunity_stages os ON l.opportunity_stage_id = os.id
             WHERE l.archived = 0 AND os.name IN ('Approved','Booked','Reserved','With PO','Downpayment')"
        );
        return (int) ($result['count'] ?? 0);
    }

    public function getLeadsBySource(int $year, int $month): array
    {
        return $this->fetchAll(
            "SELECT src.id, src.name, COUNT(l.id) as lead_count
             FROM lead_sources src
             LEFT JOIN leads l ON l.source_id = src.id 
                AND l.archived = 0
                AND YEAR(l.initial_contact_date) = ? 
                AND MONTH(l.initial_contact_date) = ?
             WHERE src.active = 1
             GROUP BY src.id, src.name
             ORDER BY src.sort_order",
            [$year, $month]
        );
    }

    public function getClosedDealsCount(string $releaseStage, int $year, int $month): int
    {
        $result = $this->fetchOne(
            "SELECT COUNT(*) as count FROM leads l
             LEFT JOIN opportunity_stages os ON l.opportunity_stage_id = os.id
             WHERE l.archived = 0 
                AND os.name = ?
                AND YEAR(l.updated_at) = ?
                AND MONTH(l.updated_at) = ?",
            [$releaseStage, $year, $month]
        );
        return (int) ($result['count'] ?? 0);
    }

    public function getLeadsCountByMonth(int $year, int $month): int
    {
        return $this->count('leads', 
            "archived = 0 AND YEAR(initial_contact_date) = ? AND MONTH(initial_contact_date) = ?",
            [$year, $month]
        );
    }

    public function getDealsCountByMonth(int $year, int $month): int
    {
        $result = $this->fetchOne(
            "SELECT COUNT(*) as count FROM leads l
             WHERE l.archived = 0 
                AND l.opportunity_stage_id IS NOT NULL
                AND YEAR(l.initial_contact_date) = ?
                AND MONTH(l.initial_contact_date) = ?",
            [$year, $month]
        );
        return (int) ($result['count'] ?? 0);
    }

    public function getLeadCountsByStatus(): array
    {
        return $this->fetchAll(
            "SELECT ls.id, ls.name, ls.color, COUNT(l.id) as count
             FROM lead_statuses ls
             LEFT JOIN leads l ON l.status_id = ls.id AND l.archived = 0
             WHERE ls.active = 1
             GROUP BY ls.id, ls.name, ls.color
             ORDER BY ls.sort_order"
        );
    }

    public function searchNames(string $query): array
    {
        return $this->fetchAll(
            "SELECT id, lead_id, lead_name FROM leads 
             WHERE archived = 0 AND lead_name LIKE ?
             ORDER BY lead_name LIMIT 10",
            ['%' . $query . '%']
        );
    }
}
