<?php
declare(strict_types=1);

require_once __DIR__ . '/Model.php';

class LeadModel extends Model
{
    public function findById(int $id, ?int $userId = null): ?array
    {
        $conditions = ['l.id = ?', 'l.archived = 0'];
        $params = [$id];

        if ($userId !== null) {
            $conditions[] = 'l.user_id = ?';
            $params[] = $userId;
        }

        $where = implode(' AND ', $conditions);

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
             WHERE {$where}",
            $params
        );
    }

    public function findAnyById(int $id): ?array
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
             WHERE l.id = ?",
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

    public function findWithDetails(array $filters = [], string $sort = 'l.next_step_date', string $direction = 'ASC', int $offset = 0, int $limit = 25, ?int $userId = null): array
    {
        $conditions = ['l.archived = 0'];
        $params = [];

        if ($userId !== null) {
            $conditions[] = 'l.user_id = ?';
            $params[] = $userId;
        }

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
                $conditions[] = "(p.name = 'High' OR os.name IN ('Test Drive','Financing Appli','Approved','Booked','Reserved','With PO','Downpayment') OR l.next_step_date <= ?)";
                $params[] = $today;
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

    public function findArchived(array $filters = [], string $sort = 'l.archived_at', string $direction = 'DESC', int $offset = 0, int $limit = 25, ?int $userId = null): array
    {
        $conditions = ['l.archived = 1'];
        $params = [];

        if ($userId !== null) {
            $conditions[] = 'l.user_id = ?';
            $params[] = $userId;
        }

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

    public function getArchivedCount(?int $userId = null): int
    {
        if ($userId !== null) {
            return $this->count('leads', 'archived = 1 AND user_id = ?', [$userId]);
        }
        return $this->count('leads', 'archived = 1');
    }

    public function getTotalCount(?int $userId = null, ?int $year = null, ?int $month = null): int
    {
        $conditions = ['archived = 0'];
        $params = [];

        if ($year !== null && $month !== null) {
            $conditions[] = 'YEAR(initial_contact_date) = ?';
            $conditions[] = 'MONTH(initial_contact_date) = ?';
            $params[] = $year;
            $params[] = $month;
        }
        if ($userId !== null) {
            $conditions[] = 'user_id = ?';
            $params[] = $userId;
        }

        return $this->count('leads', implode(' AND ', $conditions), $params);
    }

    public function getActiveDealsCount(?int $userId = null, ?int $year = null, ?int $month = null, ?string $releaseStage = null): int
    {
        $releaseStage = $releaseStage ?: 'Released';
        $conditions = ['l.archived = 0', 'l.opportunity_stage_id IS NOT NULL', 'os.name <> ?'];
        $params = [$releaseStage];

        if ($year !== null && $month !== null) {
            $conditions[] = 'YEAR(l.initial_contact_date) = ?';
            $conditions[] = 'MONTH(l.initial_contact_date) = ?';
            $params[] = $year;
            $params[] = $month;
        }
        if ($userId !== null) {
            $conditions[] = 'l.user_id = ?';
            $params[] = $userId;
        }

        $result = $this->fetchOne(
            "SELECT COUNT(*) as count FROM leads l
             LEFT JOIN opportunity_stages os ON l.opportunity_stage_id = os.id
             WHERE " . implode(' AND ', $conditions),
            $params
        );
        return (int) ($result['count'] ?? 0);
    }

    public function getWarmLeadsCount(?int $userId = null, ?int $year = null, ?int $month = null): int
    {
        $conditions = [
            "l.archived = 0",
            "(p.name = 'High' OR os.name IN ('Test Drive','Financing Appli','Approved','Booked','Reserved','With PO','Downpayment') OR l.next_step_date <= ?)",
        ];
        $params = [date('Y-m-d')];

        if ($year !== null && $month !== null) {
            $conditions[] = 'YEAR(l.initial_contact_date) = ?';
            $conditions[] = 'MONTH(l.initial_contact_date) = ?';
            $params[] = $year;
            $params[] = $month;
        }
        if ($userId !== null) {
            $conditions[] = 'l.user_id = ?';
            $params[] = $userId;
        }

        $result = $this->fetchOne(
            "SELECT COUNT(*) as count FROM leads l
             LEFT JOIN priorities p ON l.priority_id = p.id
             LEFT JOIN opportunity_stages os ON l.opportunity_stage_id = os.id
             WHERE " . implode(' AND ', $conditions),
            $params
        );
        return (int) ($result['count'] ?? 0);
    }

    public function getDealsToCloseCount(?int $userId = null, ?int $year = null, ?int $month = null): int
    {
        $conditions = [
            'l.archived = 0',
            "os.name IN ('Approved','Booked','Reserved','With PO','Downpayment')",
        ];
        $params = [];

        if ($year !== null && $month !== null) {
            $conditions[] = 'YEAR(l.initial_contact_date) = ?';
            $conditions[] = 'MONTH(l.initial_contact_date) = ?';
            $params[] = $year;
            $params[] = $month;
        }
        if ($userId !== null) {
            $conditions[] = 'l.user_id = ?';
            $params[] = $userId;
        }

        $result = $this->fetchOne(
            "SELECT COUNT(*) as count FROM leads l
             LEFT JOIN opportunity_stages os ON l.opportunity_stage_id = os.id
             WHERE " . implode(' AND ', $conditions),
            $params
        );
        return (int) ($result['count'] ?? 0);
    }

    public function getLeadsBySource(int $year, int $month, ?int $userId = null): array
    {
        $userCondition = $userId !== null ? ' AND l.user_id = ?' : '';
        $userParam = $userId !== null ? [$userId] : [];

        return $this->fetchAll(
            "SELECT src.id, src.name, COUNT(l.id) as lead_count
             FROM lead_sources src
             LEFT JOIN leads l ON l.source_id = src.id 
                AND l.archived = 0
                AND YEAR(l.initial_contact_date) = ? 
                AND MONTH(l.initial_contact_date) = ?
                {$userCondition}
             WHERE src.active = 1
             GROUP BY src.id, src.name
             ORDER BY src.sort_order",
            array_merge([$year, $month], $userParam)
        );
    }

    public function getClosedDealsCount(string $releaseStage, int $year, int $month, ?int $userId = null): int
    {
        $conditions = [
            'l.archived = 0',
            'l.release_date IS NOT NULL',
            'YEAR(l.release_date) = ?',
            'MONTH(l.release_date) = ?',
        ];
        $params = [$year, $month];
        if ($userId !== null) {
            $conditions[] = 'l.user_id = ?';
            $params[] = $userId;
        }

        $result = $this->fetchOne(
            "SELECT COUNT(*) as count FROM leads l
             WHERE " . implode(' AND ', $conditions),
            $params
        );
        return (int) ($result['count'] ?? 0);
    }

    public function getLeadsCountByMonth(int $year, int $month, ?int $userId = null): int
    {
        if ($userId !== null) {
            return $this->count('leads', 
                "archived = 0 AND YEAR(initial_contact_date) = ? AND MONTH(initial_contact_date) = ? AND user_id = ?",
                [$year, $month, $userId]
            );
        }
        return $this->count('leads', 
            "archived = 0 AND YEAR(initial_contact_date) = ? AND MONTH(initial_contact_date) = ?",
            [$year, $month]
        );
    }

    public function getDealsCountByMonth(int $year, int $month, ?int $userId = null): int
    {
        $userCondition = $userId !== null ? ' AND l.user_id = ?' : '';
        $params = [$year, $month];
        if ($userId !== null) {
            $params[] = $userId;
        }

        $result = $this->fetchOne(
            "SELECT COUNT(*) as count FROM leads l
             WHERE l.archived = 0 
                AND l.opportunity_stage_id IS NOT NULL
                AND YEAR(l.initial_contact_date) = ?
                AND MONTH(l.initial_contact_date) = ?
                {$userCondition}",
            $params
        );
        return (int) ($result['count'] ?? 0);
    }

    public function getLeadCountsByStatus(?int $userId = null): array
    {
        $userCondition = $userId !== null ? ' AND l.user_id = ?' : '';
        $userParam = $userId !== null ? [$userId] : [];

        return $this->fetchAll(
            "SELECT ls.id, ls.name, ls.color, COUNT(l.id) as count
             FROM lead_statuses ls
             LEFT JOIN leads l ON l.status_id = ls.id AND l.archived = 0 {$userCondition}
             WHERE ls.active = 1
             GROUP BY ls.id, ls.name, ls.color
             ORDER BY ls.sort_order",
            $userParam
        );
    }

    public function searchNames(string $query, ?int $userId = null): array
    {
        $userCondition = $userId !== null ? ' AND user_id = ?' : '';
        $params = ['%' . $query . '%'];
        if ($userId !== null) {
            $params[] = $userId;
        }

        return $this->fetchAll(
            "SELECT id, lead_id, lead_name FROM leads 
             WHERE archived = 0 AND lead_name LIKE ? {$userCondition}
             ORDER BY lead_name LIMIT 10",
            $params
        );
    }

    public function ownsLead(int $leadId, int $userId): bool
    {
        $result = $this->fetchOne(
            "SELECT id FROM leads WHERE id = ? AND user_id = ?",
            [$leadId, $userId]
        );
        return $result !== null;
    }

    public function getOverdueLeads(?int $userId = null): array
    {
        return $this->findWithDetails(['quick_filter' => 'overdue'], 'l.next_step_date', 'ASC', 0, 10, $userId);
    }

    public function getDueTodayLeads(?int $userId = null): array
    {
        return $this->findWithDetails(['quick_filter' => 'due_today'], 'l.priority_level', 'ASC', 0, 10, $userId);
    }

    public function getReleaseWatchLeads(array $stageIds, ?int $userId = null): array
    {
        if (empty($stageIds)) return [];

        $placeholders = implode(',', array_fill(0, count($stageIds), '?'));
        $userCondition = $userId !== null ? ' AND l.user_id = ?' : '';
        $params = $stageIds;
        if ($userId !== null) {
            $params[] = $userId;
        }

        return $this->fetchAll(
            "SELECT l.*, ls.name as status_name, os.name as stage_name, os.color as stage_color,
                    p.name as priority_name, p.color as priority_color, p.level as priority_level,
                    vm.name as model_name, src.name as source_name
             FROM leads l
             LEFT JOIN lead_statuses ls ON l.status_id = ls.id
             LEFT JOIN opportunity_stages os ON l.opportunity_stage_id = os.id
             LEFT JOIN priorities p ON l.priority_id = p.id
             LEFT JOIN vehicle_models vm ON l.model_id = vm.id
             LEFT JOIN lead_sources src ON l.source_id = src.id
             WHERE l.archived = 0 AND l.opportunity_stage_id IN ({$placeholders}) {$userCondition}
             ORDER BY p.level ASC, os.sort_order DESC, CASE WHEN l.next_step_date IS NULL THEN 1 ELSE 0 END, l.next_step_date ASC
             LIMIT 15",
            $params
        );
    }

    public function getWarmHotLeads(int $limit = 50, ?int $userId = null): array
    {
        return $this->findWithDetails([], 'p.level', 'ASC', 0, $limit, $userId);
    }

    public function getStageLeads(int $stageId, ?int $userId = null): array
    {
        $userCondition = $userId !== null ? ' AND l.user_id = ?' : '';
        $params = [$stageId];
        if ($userId !== null) {
            $params[] = $userId;
        }

        return $this->fetchAll(
            "SELECT l.*, 
                    ls.name as status_name, ls.color as status_color,
                    p.name as priority_name, p.color as priority_color, p.level as priority_level,
                    src.name as source_name,
                    vm.name as model_name
             FROM leads l
             LEFT JOIN lead_statuses ls ON l.status_id = ls.id
             LEFT JOIN priorities p ON l.priority_id = p.id
             LEFT JOIN lead_sources src ON l.source_id = src.id
             LEFT JOIN vehicle_models vm ON l.model_id = vm.id
             WHERE l.archived = 0 AND l.opportunity_stage_id = ? {$userCondition}
             ORDER BY p.level ASC, CASE WHEN l.next_step_date IS NULL THEN 1 ELSE 0 END, l.next_step_date ASC",
            $params
        );
    }

    public function getStageCounts(?int $userId = null): array
    {
        $userCondition = $userId !== null ? ' AND l.user_id = ?' : '';
        $userParam = $userId !== null ? [$userId] : [];

        return $this->fetchAll(
            "SELECT os.id, os.name, os.color, COUNT(l.id) as count
             FROM opportunity_stages os
             LEFT JOIN leads l ON l.opportunity_stage_id = os.id AND l.archived = 0 {$userCondition}
             WHERE os.active = 1
             GROUP BY os.id, os.name, os.color
             ORDER BY os.sort_order",
            $userParam
        );
    }

    public function getNoFollowupCount(?int $userId = null): int
    {
        if ($userId !== null) {
            return $this->count('leads', "archived = 0 AND (next_step_date IS NULL OR next_step_date = '') AND user_id = ?", [$userId]);
        }
        return $this->count('leads', "archived = 0 AND (next_step_date IS NULL OR next_step_date = '')");
    }

    public function getLeadsCountByModel(?int $userId = null): array
    {
        $userCondition = $userId !== null ? ' AND l.user_id = ?' : '';
        $userParam = $userId !== null ? [$userId] : [];

        return $this->fetchAll(
            "SELECT vm.id, vm.name, COUNT(l.id) as count
             FROM vehicle_models vm
             LEFT JOIN leads l ON l.model_id = vm.id AND l.archived = 0 {$userCondition}
             WHERE vm.active = 1
             GROUP BY vm.id, vm.name
             ORDER BY count DESC",
            $userParam
        );
    }

    public function getLeadsCountByPriority(?int $userId = null): array
    {
        $userCondition = $userId !== null ? ' AND l.user_id = ?' : '';
        $userParam = $userId !== null ? [$userId] : [];

        return $this->fetchAll(
            "SELECT p.id, p.name, p.color, COUNT(l.id) as count
             FROM priorities p
             LEFT JOIN leads l ON l.priority_id = p.id AND l.archived = 0 {$userCondition}
             WHERE p.active = 1
             GROUP BY p.id, p.name, p.color
             ORDER BY p.level",
            $userParam
        );
    }
}
