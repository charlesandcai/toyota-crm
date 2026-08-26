<?php
declare(strict_types=1);

require_once __DIR__ . '/Model.php';

class ActivityModel extends Model
{
    public function getByLeadId(int $leadId): array
    {
        return $this->fetchAll(
            "SELECT a.*, u.full_name as created_by_name
             FROM activities a
             LEFT JOIN users u ON a.created_by = u.id
             WHERE a.lead_id = ?
             ORDER BY a.activity_date DESC, a.created_at DESC",
            [$leadId]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne("SELECT * FROM activities WHERE id = ?", [$id]);
    }

    public function create(array $data): int
    {
        return $this->insert('activities', $data);
    }

    public function getRecent(int $limit = 10): array
    {
        return $this->fetchAll(
            "SELECT a.*, l.lead_name, l.lead_id as lead_code, vm.name as model_name
             FROM activities a
             JOIN leads l ON a.lead_id = l.id
             LEFT JOIN vehicle_models vm ON l.model_id = vm.id
             WHERE l.archived = 0
             ORDER BY a.activity_date DESC
             LIMIT ?",
            [$limit]
        );
    }

    public function getByMonth(int $year, int $month): array
    {
        return $this->fetchAll(
            "SELECT a.*, l.lead_name, l.lead_id as lead_code
             FROM activities a
             JOIN leads l ON a.lead_id = l.id
             WHERE YEAR(a.activity_date) = ? AND MONTH(a.activity_date) = ? AND l.archived = 0
             ORDER BY a.activity_date DESC",
            [$year, $month]
        );
    }

    public function getTodayActivities(): array
    {
        return $this->fetchAll(
            "SELECT a.*, l.lead_name, l.lead_id as lead_code
             FROM activities a
             JOIN leads l ON a.lead_id = l.id
             WHERE DATE(a.activity_date) = CURDATE() AND l.archived = 0
             ORDER BY a.activity_date DESC"
        );
    }
}
