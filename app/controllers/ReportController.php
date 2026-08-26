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

        $salesMetrics = new SalesMetricsService();
        $settingsModel = new SettingsModel();
        $leadModel = new LeadModel();

        $year = (int) ($_GET['year'] ?? date('Y'));
        $summary = $salesMetrics->getMonthlySummary();

        // Get lead generation targets
        $leadGenTargets = [];
        $allTargets = $settingsModel->getLeadGenerationTargets();
        foreach ($allTargets as $t) {
            if ((int)$t['year'] === $year) {
                $leadGenTargets[(int)$t['month']][] = $t;
            }
        }

        // Actual leads per month per source
        $leadGenActual = [];
        $sources = $settingsModel->getSources();
        for ($m = 1; $m <= 12; $m++) {
            $leadsBySource = $leadModel->getLeadsBySource($year, $m);
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

        $leadModel = new LeadModel();
        $settingsModel = new SettingsModel();

        $year = (int) ($_GET['year'] ?? date('Y'));
        $month = (int) ($_GET['month'] ?? 0);

        $byStatus = $leadModel->getLeadCountsByStatus();
        $bySource = $leadModel->getLeadsBySource($year, $month ?: (int) date('n'));
        
        $db = Database::getConnection();
        $byModel = $db->query(
            "SELECT vm.id, vm.name, COUNT(l.id) as count
             FROM vehicle_models vm
             LEFT JOIN leads l ON l.model_id = vm.id AND l.archived = 0
             WHERE vm.active = 1
             GROUP BY vm.id, vm.name
             ORDER BY count DESC"
        )->fetchAll();

        $byPriority = $db->query(
            "SELECT p.id, p.name, p.color, COUNT(l.id) as count
             FROM priorities p
             LEFT JOIN leads l ON l.priority_id = p.id AND l.archived = 0
             WHERE p.active = 1
             GROUP BY p.id, p.name, p.color
             ORDER BY p.level"
        )->fetchAll();

        $totalLeads = $leadModel->getLeadsCountByMonth($year, $month ?: (int) date('n'));

        $activePage = 'reports';
        Response::view('reports.lead-performance', compact(
            'activePage', 'byStatus', 'bySource', 'byModel', 'byPriority', 'totalLeads', 'year', 'month'
        ));
    }

    public function salesPerformance(): void
    {
        Security::requireAuth();

        $salesMetrics = new SalesMetricsService();
        $leadModel = new LeadModel();
        $settingsModel = new SettingsModel();

        $year = (int) ($_GET['year'] ?? date('Y'));
        $summary = $salesMetrics->getMonthlySummary();

        $db = Database::getConnection();
        $byStage = $db->query(
            "SELECT os.id, os.name, os.color, COUNT(l.id) as count
             FROM opportunity_stages os
             LEFT JOIN leads l ON l.opportunity_stage_id = os.id AND l.archived = 0
             WHERE os.active = 1
             GROUP BY os.id, os.name, os.color
             ORDER BY os.sort_order"
        )->fetchAll();

        $activePage = 'reports';
        Response::view('reports.sales-performance', compact('activePage', 'summary', 'byStage'));
    }

    public function followupPerformance(): void
    {
        Security::requireAuth();

        $leadModel = new LeadModel();
        $today = date('Y-m-d');

        $overdue = $leadModel->findWithDetails(['quick_filter' => 'overdue'], 'l.next_step_date', 'ASC', 0, 50);
        $dueToday = $leadModel->findWithDetails(['quick_filter' => 'due_today'], 'l.next_step_date', 'ASC', 0, 50);

        $db = Database::getConnection();
        $noFollowup = $db->query(
            "SELECT COUNT(*) as count FROM leads WHERE archived = 0 AND (next_step_date IS NULL OR next_step_date = '')"
        )->fetch()['count'];

        $activePage = 'reports';
        Response::view('reports.followup-performance', compact('activePage', 'overdue', 'dueToday', 'noFollowup'));
    }
}
