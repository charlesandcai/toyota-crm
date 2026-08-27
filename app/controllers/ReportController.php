<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/models/Model.php';
require_once dirname(__DIR__, 2) . '/app/models/LeadModel.php';
require_once dirname(__DIR__, 2) . '/app/models/SettingsModel.php';
require_once dirname(__DIR__, 2) . '/app/services/SalesMetricsService.php';
require_once dirname(__DIR__, 2) . '/app/services/FollowUpService.php';

class ReportController
{
    public function index(): void
    {
        Security::requireAuth();
        $activePage = 'reports';
        Response::view('reports.index', compact('activePage'));
    }

    public function monthlySummary(): void
    {
        Security::requireAuth();

        $userId = Security::isAdmin() ? null : Security::userId();
        $salesMetrics = new SalesMetricsService($userId);
        $settingsModel = new SettingsModel();
        $leadModel = new LeadModel();

        $year = (int) ($_GET['year'] ?? date('Y'));
        $summary = $salesMetrics->getMonthlySummary();

        $leadGenTargets = [];
        $allTargets = $settingsModel->getLeadGenerationTargets();
        foreach ($allTargets as $t) {
            if ((int)$t['year'] === $year) {
                $leadGenTargets[(int)$t['month']][] = $t;
            }
        }

        $leadGenActual = [];
        $sources = $settingsModel->getSources();
        for ($m = 1; $m <= 12; $m++) {
            $leadsBySource = $leadModel->getLeadsBySource($year, $m, $userId);
            foreach ($leadsBySource as $lbs) {
                $leadGenActual[$m][$lbs['id']] = (int) $lbs['lead_count'];
            }
        }

        $activePage = 'reports';
        Response::view('reports.monthly-summary', compact('activePage', 'summary', 'leadGenTargets', 'sources', 'year'));
    }

    public function leadPerformance(): void
    {
        Security::requireAuth();

        $userId = Security::isAdmin() ? null : Security::userId();
        $leadModel = new LeadModel();
        $settingsModel = new SettingsModel();

        $year = (int) ($_GET['year'] ?? date('Y'));
        $month = (int) ($_GET['month'] ?? 0);

        $byStatus = $leadModel->getLeadCountsByStatus($userId);
        $bySource = $leadModel->getLeadsBySource($year, $month ?: (int) date('n'), $userId);
        $byModel = $leadModel->getLeadsCountByModel($userId);
        $byPriority = $leadModel->getLeadsCountByPriority($userId);

        $totalLeads = $leadModel->getLeadsCountByMonth($year, $month ?: (int) date('n'), $userId);

        $activePage = 'reports';
        Response::view('reports.lead-performance', compact(
            'activePage', 'byStatus', 'bySource', 'byModel', 'byPriority', 'totalLeads', 'year', 'month'
        ));
    }

    public function salesPerformance(): void
    {
        Security::requireAuth();

        $userId = Security::isAdmin() ? null : Security::userId();
        $salesMetrics = new SalesMetricsService($userId);
        $leadModel = new LeadModel();
        $settingsModel = new SettingsModel();

        $year = (int) ($_GET['year'] ?? date('Y'));
        $summary = $salesMetrics->getMonthlySummary();

        $byStage = $leadModel->getStageCounts($userId);

        $activePage = 'reports';
        Response::view('reports.sales-performance', compact('activePage', 'summary', 'byStage'));
    }

    public function followupPerformance(): void
    {
        Security::requireAuth();

        $userId = Security::isAdmin() ? null : Security::userId();
        $leadModel = new LeadModel();

        $overdue = $leadModel->getOverdueLeads($userId);
        $dueToday = $leadModel->getDueTodayLeads($userId);
        $noFollowup = $leadModel->getNoFollowupCount($userId);

        $activePage = 'reports';
        Response::view('reports.followup-performance', compact('activePage', 'overdue', 'dueToday', 'noFollowup'));
    }
}
