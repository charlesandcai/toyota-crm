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
require_once dirname(__DIR__, 2) . '/app/services/CalendarEventService.php';

class DashboardController
{
    public function index(): void
    {
        Security::requireAuth();

        $leadModel = new LeadModel();
        $settingsModel = new SettingsModel();
        $activityModel = new ActivityModel();
        $workingDaysService = new WorkingDaysService();

        $month = (int) ($_GET['month'] ?? date('n'));
        $year = (int) ($_GET['year'] ?? date('Y'));
        $today = date('Y-m-d');
        $db = Database::getConnection();

        $userId = Security::isAdmin() ? null : Security::userId();
        $salesMetrics = new SalesMetricsService($userId);

        $releaseStage = $settingsModel->getSetting('closed_release_stage') ?: 'Released';

        // KPI Cards (filtered by the selected reporting month/year)
        $totalLeads = $leadModel->getTotalCount($userId, $year, $month);
        $activeDeals = $leadModel->getActiveDealsCount($userId, $year, $month, $releaseStage);
        $warmLeads = $leadModel->getWarmLeadsCount($userId, $year, $month);
        $dealsToClose = $leadModel->getDealsToCloseCount($userId, $year, $month);

        // Sales Target
        $salesData = $salesMetrics->getMonthlyData($year, $month);

        // Lead Generation
        $leadGenData = $leadModel->getLeadsBySource($year, $month, $userId);
        $leadGenTargets = [];
        $targets = $settingsModel->getLeadGenerationTargets();
        foreach ($targets as $t) {
            if ((int)$t['year'] === $year && (int)$t['month'] === $month) {
                $leadGenTargets[$t['source_id']] = (int) $t['target'];
            }
        }
        $workingDaysLeft = $salesData['working_days_left'];

        // Needs Attention - Overdue
        $overdueLeads = $leadModel->getOverdueLeads($userId);

        // Needs Attention - Due Today
        $dueTodayLeads = $leadModel->getDueTodayLeads($userId);

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
        $releaseWatchLeads = $leadModel->getReleaseWatchLeads($releaseStageIds, $userId);

        // Warm/Hot Leads
        $allActiveLeads = $leadModel->getWarmHotLeads(50, $userId);
        $warmHotLeads = [];
        foreach ($allActiveLeads['leads'] as $lead) {
            if (WarmLeadService::isWarmOrHot($lead)) {
                $warmHotLeads[] = $lead;
                if (count($warmHotLeads) >= 10) break;
            }
        }

        // Pipeline Summary
        $statusCounts = $leadModel->getLeadCountsByStatus($userId);
        $stageCounts = $leadModel->getStageCounts($userId);

        // Calendar events: TODAY / 1 WEEK / 1 MONTH
        $calendarService = new CalendarEventService();
        $todayEvents = $calendarService->getEvents($today, $today, $userId);
        $weekStart = date('Y-m-d', strtotime('+1 day'));
        $weekEnd = date('Y-m-d', strtotime('+7 days'));
        $monthEnd = date('Y-m-d', strtotime('+30 days'));
        $weekEvents = $calendarService->getEvents($weekStart, $weekEnd, $userId);
        $monthEvents = $calendarService->getEvents($weekStart, $monthEnd, $userId);

        $activePage = 'dashboard';
        Response::view('dashboard.index', compact(
            'activePage', 'totalLeads', 'activeDeals', 'warmLeads', 'dealsToClose',
            'salesData', 'leadGenData', 'leadGenTargets', 'workingDaysLeft',
            'overdueLeads', 'dueTodayLeads', 'releaseWatchLeads', 'warmHotLeads',
            'statusCounts', 'stageCounts', 'month', 'year',
            'todayEvents', 'weekEvents', 'monthEvents'
        ));
    }
}
