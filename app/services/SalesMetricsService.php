<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/services/WorkingDaysService.php';

class SalesMetricsService
{
    private SettingsModel $settings;
    private LeadModel $leadModel;
    private WorkingDaysService $workingDaysService;
    private ?int $userId;

    public function __construct(?int $userId = null)
    {
        require_once dirname(__DIR__, 2) . '/app/models/SettingsModel.php';
        require_once dirname(__DIR__, 2) . '/app/models/LeadModel.php';
        $this->settings = new SettingsModel();
        $this->leadModel = new LeadModel();
        $this->workingDaysService = new WorkingDaysService();
        $this->userId = $userId;
    }

    public function getMonthlyData(int $year, int $month): array
    {
        $target = $this->settings->getSalesTarget($year, $month);
        $targetValue = $target ? (int) $target['target'] : 0;

        $releaseStage = $this->settings->getSetting('closed_release_stage') ?: 'Released';
        $closed = $this->leadModel->getClosedDealsCount($releaseStage, $year, $month, $this->userId);
        $remaining = max(0, $targetValue - $closed);

        $workingDaysLeft = $this->workingDaysService->remainingWorkingDaysInMonth($year, $month);

        $closingRatio = $this->getAverageClosingRatio();
        $leadsNeeded = 0;
        if ($closingRatio > 0 && $remaining > 0) {
            $leadsNeeded = (int) ceil($remaining / $closingRatio);
        }

        return [
            'target' => $targetValue,
            'closed' => $closed,
            'remaining' => $remaining,
            'working_days_left' => $workingDaysLeft,
            'leads_needed' => $leadsNeeded,
            'closing_ratio' => $closingRatio,
        ];
    }

    public function getAverageClosingRatio(): float
    {
        $db = Database::getConnection();
        $userCondition = $this->userId !== null ? ' AND l.user_id = ?' : '';
        $params = [];
        if ($this->userId !== null) {
            $params[] = $this->userId;
        }

        $stmt = $db->prepare(
            "SELECT 
                SUM(CASE WHEN l.opportunity_stage_id IS NOT NULL THEN 1 ELSE 0 END) as total_deals,
                COUNT(*) as total_leads
             FROM leads l
             WHERE l.archived = 0 AND YEAR(l.initial_contact_date) = YEAR(CURDATE()) AND MONTH(l.initial_contact_date) <= MONTH(CURDATE()) {$userCondition}"
        );
        $stmt->execute($params);
        $result = $stmt->fetch();

        $totalDeals = (int) ($result['total_deals'] ?? 0);
        $totalLeads = (int) ($result['total_leads'] ?? 0);

        if ($totalLeads === 0) {
            return 0.0;
        }

        return round($totalDeals / $totalLeads, 4);
    }

    public function getMonthlySummary(): array
    {
        $year = (int) date('Y');
        $months = [];

        for ($m = 1; $m <= 12; $m++) {
            $leads = $this->leadModel->getLeadsCountByMonth($year, $m, $this->userId);
            $deals = $this->leadModel->getDealsCountByMonth($year, $m, $this->userId);
            $closingRatio = $leads > 0 ? round($deals / $leads, 4) : 0;
            $target = $this->settings->getSalesTarget($year, $m);

            $months[] = [
                'month' => $m,
                'month_name' => date('F', mktime(0, 0, 0, $m, 1)),
                'total_leads' => $leads,
                'total_deals' => $deals,
                'closing_ratio' => $closingRatio,
                'sales_target' => $target ? (int) $target['target'] : 0,
            ];
        }

        return $months;
    }
}
