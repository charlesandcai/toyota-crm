<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/models/Model.php';
require_once dirname(__DIR__, 2) . '/app/models/LeadModel.php';
require_once dirname(__DIR__, 2) . '/app/models/SettingsModel.php';
require_once dirname(__DIR__, 2) . '/app/models/ActivityModel.php';
require_once dirname(__DIR__, 2) . '/app/services/SalesMetricsService.php';
require_once dirname(__DIR__, 2) . '/app/services/FollowUpService.php';
require_once dirname(__DIR__, 2) . '/app/services/WarmLeadService.php';
require_once dirname(__DIR__, 2) . '/app/services/WorkingDaysService.php';

class DashboardController
{
    public function index(): void
    {
        Security::requireAuth();

        $leadModel = new LeadModel();
        $settingsModel = new SettingsModel();
        $activityModel = new ActivityModel();
        $salesMetrics = new SalesMetricsService();
        $workingDaysService = new WorkingDaysService();

        $month = (int) ($_GET['month'] ?? date('n'));
        $year = (int) ($_GET['year'] ?? date('Y'));
        $today = date('Y-m-d');
        $db = Database::getConnection();

        // KPI Cards
        $totalLeads = $leadModel->getTotalCount();
        $activeDeals = $leadModel->getActiveDealsCount();
        $warmLeads = $leadModel->getWarmLeadsCount();
        $dealsToClose = $leadModel->getDealsToCloseCount();

        // Sales Target
        $salesData = $salesMetrics->getMonthlyData($year, $month);

        // Lead Generation
        $leadGenData = $leadModel->getLeadsBySource($year, $month);
        $leadGenTargets = [];
        $targets = $settingsModel->getLeadGenerationTargets();
        foreach ($targets as $t) {
            if ((int)$t['year'] === $year && (int)$t['month'] === $month) {
                $leadGenTargets[$t['source_id']] = (int) $t['target'];
            }
        }
        $workingDaysLeft = $salesData['working_days_left'];

        // Needs Attention - Overdue
        $overdueLeads = $leadModel->findWithDetails(
            ['quick_filter' => 'overdue'],
            'l.next_step_date',
            'ASC',
            0,
            10
        );

        // Needs Attention - Due Today
        $dueTodayLeads = $leadModel->findWithDetails(
            ['quick_filter' => 'due_today'],
            'l.priority_level',
            'ASC',
            0,
            10
        );

        // Release Watch
        $releaseStages = WarmLeadService::getReleaseWatchStages();
        $releaseStageIds = [];
        foreach ($releaseStages as $rs) {
            $stage = $settingsModel->getStages();
            foreach ($stage as $s) {
                if ($s['name'] === $rs) {
                    $releaseStageIds[] = $s['id'];
                }
            }
        }
        $releaseWatchLeads = [];
        if (!empty($releaseStageIds)) {
            $placeholders = implode(',', array_fill(0, count($releaseStageIds), '?'));
            $releaseWatchLeads = $leadModel->fetchAll(
                "SELECT l.*, ls.name as status_name, os.name as stage_name, os.color as stage_color,
                        p.name as priority_name, p.color as priority_color, p.level as priority_level,
                        vm.name as model_name, src.name as source_name
                 FROM leads l
                 LEFT JOIN lead_statuses ls ON l.status_id = ls.id
                 LEFT JOIN opportunity_stages os ON l.opportunity_stage_id = os.id
                 LEFT JOIN priorities p ON l.priority_id = p.id
                 LEFT JOIN vehicle_models vm ON l.model_id = vm.id
                 LEFT JOIN lead_sources src ON l.source_id = src.id
                 WHERE l.archived = 0 AND l.opportunity_stage_id IN ({$placeholders})
                 ORDER BY p.level ASC, os.sort_order DESC, CASE WHEN l.next_step_date IS NULL THEN 1 ELSE 0 END, l.next_step_date ASC
                 LIMIT 15",
                $releaseStageIds
            );
        }

        // Warm/Hot Leads
        $allActiveLeads = $leadModel->findWithDetails([], 'p.level', 'ASC', 0, 50);
        $warmHotLeads = [];
        foreach ($allActiveLeads['leads'] as $lead) {
            if (WarmLeadService::isWarmOrHot($lead)) {
                $warmHotLeads[] = $lead;
                if (count($warmHotLeads) >= 10) break;
            }
        }

        // Pipeline Summary
        $statusCounts = $leadModel->getLeadCountsByStatus();
        $stageCounts = $db->query(
            "SELECT os.id, os.name, os.color, COUNT(l.id) as count
             FROM opportunity_stages os
             LEFT JOIN leads l ON l.opportunity_stage_id = os.id AND l.archived = 0
             WHERE os.active = 1
             GROUP BY os.id, os.name, os.color
             ORDER BY os.sort_order"
        )->fetchAll();

        $activePage = 'dashboard';
        Response::view('dashboard.index', compact(
            'activePage', 'totalLeads', 'activeDeals', 'warmLeads', 'dealsToClose',
            'salesData', 'leadGenData', 'leadGenTargets', 'workingDaysLeft',
            'overdueLeads', 'dueTodayLeads', 'releaseWatchLeads', 'warmHotLeads',
            'statusCounts', 'stageCounts', 'month', 'year'
        ));
    }
}
