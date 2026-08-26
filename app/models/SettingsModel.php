<?php
declare(strict_types=1);

require_once __DIR__ . '/Model.php';

class SettingsModel extends Model
{
    public function getSetting(string $key): ?string
    {
        $result = $this->fetchOne("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
        return $result['setting_value'] ?? null;
    }

    public function setSetting(string $key, string $value): void
    {
        $exists = $this->fetchOne("SELECT id FROM settings WHERE setting_key = ?", [$key]);
        if ($exists) {
            $this->update('settings', ['setting_value' => $value], 'setting_key = ?', [$key]);
        } else {
            $this->insert('settings', ['setting_key' => $key, 'setting_value' => $value]);
        }
    }

    public function getStatuses(bool $activeOnly = true): array
    {
        $where = $activeOnly ? 'WHERE active = 1' : '';
        return $this->fetchAll("SELECT * FROM lead_statuses {$where} ORDER BY sort_order");
    }

    public function getStatusById(int $id): ?array
    {
        return $this->fetchOne("SELECT * FROM lead_statuses WHERE id = ?", [$id]);
    }

    public function createStatus(string $name, ?string $color, int $sortOrder): int
    {
        return $this->insert('lead_statuses', [
            'name' => $name,
            'color' => $color,
            'sort_order' => $sortOrder,
            'active' => 1,
        ]);
    }

    public function updateStatus(int $id, array $data): bool
    {
        return $this->update('lead_statuses', $data, 'id = ?', [$id]);
    }

    public function getStages(bool $activeOnly = true): array
    {
        $where = $activeOnly ? 'WHERE active = 1' : '';
        return $this->fetchAll("SELECT * FROM opportunity_stages {$where} ORDER BY sort_order");
    }

    public function getStageById(int $id): ?array
    {
        return $this->fetchOne("SELECT * FROM opportunity_stages WHERE id = ?", [$id]);
    }

    public function createStage(string $name, ?string $color, int $sortOrder): int
    {
        return $this->insert('opportunity_stages', [
            'name' => $name,
            'color' => $color,
            'sort_order' => $sortOrder,
            'active' => 1,
        ]);
    }

    public function updateStage(int $id, array $data): bool
    {
        return $this->update('opportunity_stages', $data, 'id = ?', [$id]);
    }

    public function getPriorities(bool $activeOnly = true): array
    {
        $where = $activeOnly ? 'WHERE active = 1' : '';
        return $this->fetchAll("SELECT * FROM priorities {$where} ORDER BY level");
    }

    public function createPriority(string $name, ?string $color, int $level): int
    {
        return $this->insert('priorities', [
            'name' => $name,
            'color' => $color,
            'level' => $level,
            'active' => 1,
        ]);
    }

    public function updatePriority(int $id, array $data): bool
    {
        return $this->update('priorities', $data, 'id = ?', [$id]);
    }

    public function getSources(bool $activeOnly = true): array
    {
        $where = $activeOnly ? 'WHERE active = 1' : '';
        return $this->fetchAll("SELECT * FROM lead_sources {$where} ORDER BY sort_order");
    }

    public function getSourceById(int $id): ?array
    {
        return $this->fetchOne("SELECT * FROM lead_sources WHERE id = ?", [$id]);
    }

    public function createSource(string $name, int $sortOrder): int
    {
        return $this->insert('lead_sources', [
            'name' => $name,
            'sort_order' => $sortOrder,
            'active' => 1,
        ]);
    }

    public function updateSource(int $id, array $data): bool
    {
        return $this->update('lead_sources', $data, 'id = ?', [$id]);
    }

    public function getModels(bool $activeOnly = true): array
    {
        $where = $activeOnly ? 'WHERE active = 1' : '';
        return $this->fetchAll("SELECT * FROM vehicle_models {$where} ORDER BY sort_order");
    }

    public function createModel(string $name, int $sortOrder): int
    {
        return $this->insert('vehicle_models', [
            'name' => $name,
            'sort_order' => $sortOrder,
            'active' => 1,
        ]);
    }

    public function updateModel(int $id, array $data): bool
    {
        return $this->update('vehicle_models', $data, 'id = ?', [$id]);
    }

    public function getColors(bool $activeOnly = true): array
    {
        $where = $activeOnly ? 'WHERE active = 1' : '';
        return $this->fetchAll("SELECT * FROM vehicle_colors {$where} ORDER BY sort_order");
    }

    public function createColor(string $name, int $sortOrder): int
    {
        return $this->insert('vehicle_colors', [
            'name' => $name,
            'sort_order' => $sortOrder,
            'active' => 1,
        ]);
    }

    public function updateColor(int $id, array $data): bool
    {
        return $this->update('vehicle_colors', $data, 'id = ?', [$id]);
    }

    public function getSalesTargets(): array
    {
        return $this->fetchAll("SELECT * FROM sales_targets ORDER BY year DESC, month DESC");
    }

    public function getSalesTarget(int $year, int $month): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM sales_targets WHERE year = ? AND month = ?",
            [$year, $month]
        );
    }

    public function setSalesTarget(int $year, int $month, int $target): void
    {
        $existing = $this->getSalesTarget($year, $month);
        if ($existing) {
            $this->update('sales_targets', ['target' => $target], 'id = ?', [$existing['id']]);
        } else {
            $this->insert('sales_targets', [
                'year' => $year,
                'month' => $month,
                'target' => $target,
            ]);
        }
    }

    public function getLeadGenerationTargets(): array
    {
        return $this->fetchAll("SELECT lgt.*, src.name as source_name FROM lead_generation_targets lgt JOIN lead_sources src ON lgt.source_id = src.id ORDER BY lgt.year DESC, lgt.month DESC, src.sort_order");
    }

    public function getLeadGenTarget(int $year, int $month, int $sourceId): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM lead_generation_targets WHERE year = ? AND month = ? AND source_id = ?",
            [$year, $month, $sourceId]
        );
    }

    public function setLeadGenTarget(int $year, int $month, int $sourceId, int $target): void
    {
        $existing = $this->getLeadGenTarget($year, $month, $sourceId);
        if ($existing) {
            $this->update('lead_generation_targets', ['target' => $target], 'id = ?', [$existing['id']]);
        } else {
            $this->insert('lead_generation_targets', [
                'year' => $year,
                'month' => $month,
                'source_id' => $sourceId,
                'target' => $target,
            ]);
        }
    }

    public function deleteSalesTarget(int $id): bool
    {
        return (bool) $this->query("DELETE FROM sales_targets WHERE id = ?", [$id])->rowCount();
    }

    public function updateSalesTarget(int $id, int $target): bool
    {
        return $this->update('sales_targets', ['target' => $target], 'id = ?', [$id]);
    }

    public function deleteLeadGenTarget(int $id): bool
    {
        return (bool) $this->query("DELETE FROM lead_generation_targets WHERE id = ?", [$id])->rowCount();
    }

    public function updateLeadGenTarget(int $id, int $target): bool
    {
        return $this->update('lead_generation_targets', ['target' => $target], 'id = ?', [$id]);
    }

    public function getWorkingDays(): array
    {
        return $this->fetchAll("SELECT * FROM working_days ORDER BY day_of_week");
    }

    public function updateWorkingDay(string $day, bool $isWorking): void
    {
        $existing = $this->fetchOne("SELECT id FROM working_days WHERE day_of_week = ?", [$day]);
        if ($existing) {
            $this->update('working_days', ['is_working' => $isWorking ? 1 : 0], 'day_of_week = ?', [$day]);
        } else {
            $this->insert('working_days', [
                'day_of_week' => $day,
                'is_working' => $isWorking ? 1 : 0,
            ]);
        }
    }

    public function getHolidays(int $year): array
    {
        return $this->fetchAll(
            "SELECT * FROM holidays WHERE YEAR(holiday_date) = ? ORDER BY holiday_date",
            [$year]
        );
    }

    public function addHoliday(string $date, string $name): int
    {
        return $this->insert('holidays', [
            'holiday_date' => $date,
            'name' => $name,
        ]);
    }

    public function deleteHoliday(int $id): bool
    {
        return (bool) $this->query("DELETE FROM holidays WHERE id = ?", [$id])->rowCount();
    }
}
