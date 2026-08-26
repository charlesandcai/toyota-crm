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

class ApiController
{
    public function __construct()
    {
        header('Content-Type: application/json; charset=utf-8');
    }

    public function dashboardKpi(): void
    {
        $this->checkAuth();
        $leadModel = new LeadModel();
        $salesMetrics = new SalesMetricsService();

        $month = (int) ($_GET['month'] ?? date('n'));
        $year = (int) ($_GET['year'] ?? date('Y'));
        $salesData = $salesMetrics->getMonthlyData($year, $month);

        Response::success('', [
            'total_leads' => $leadModel->getTotalCount(),
            'active_deals' => $leadModel->getActiveDealsCount(),
            'warm_leads' => $leadModel->getWarmLeadsCount(),
            'deals_to_close' => $leadModel->getDealsToCloseCount(),
            'sales_target' => $salesData,
        ]);
    }

    public function needsAttention(): void
    {
        $this->checkAuth();
        $leadModel = new LeadModel();

        $overdue = $leadModel->findWithDetails(['quick_filter' => 'overdue'], 'l.next_step_date', 'ASC', 0, 10);
        $dueToday = $leadModel->findWithDetails(['quick_filter' => 'due_today'], 'l.priority_level', 'ASC', 0, 10);

        Response::success('', [
            'overdue' => $overdue['leads'],
            'due_today' => $dueToday['leads'],
        ]);
    }

    public function releaseWatch(): void
    {
        $this->checkAuth();
        $leadModel = new LeadModel();
        $settingsModel = new SettingsModel();

        $releaseStages = WarmLeadService::getReleaseWatchStages();
        $stages = $settingsModel->getStages();
        $stageIds = [];
        foreach ($stages as $s) {
            if (in_array($s['name'], $releaseStages)) {
                $stageIds[] = $s['id'];
            }
        }

        $leads = [];
        if (!empty($stageIds)) {
            $placeholders = implode(',', array_fill(0, count($stageIds), '?'));
            $leads = $leadModel->fetchAll(
                "SELECT l.*, os.name as stage_name, p.name as priority_name, p.level as priority_level, vm.name as model_name
                 FROM leads l
                 LEFT JOIN opportunity_stages os ON l.opportunity_stage_id = os.id
                 LEFT JOIN priorities p ON l.priority_id = p.id
                 LEFT JOIN vehicle_models vm ON l.model_id = vm.id
                 WHERE l.archived = 0 AND l.opportunity_stage_id IN ({$placeholders})
                 ORDER BY p.level ASC, os.sort_order DESC, CASE WHEN l.next_step_date IS NULL THEN 1 ELSE 0 END, l.next_step_date ASC
                 LIMIT 15",
                $stageIds
            );
        }

        Response::success('', ['leads' => $leads]);
    }

    public function warmLeads(): void
    {
        $this->checkAuth();
        $leadModel = new LeadModel();

        $allActive = $leadModel->findWithDetails([], 'p.level', 'ASC', 0, 50);
        $warm = [];
        foreach ($allActive['leads'] as $lead) {
            if (WarmLeadService::isWarmOrHot($lead)) {
                $warm[] = $lead;
                if (count($warm) >= 10) break;
            }
        }

        Response::success('', ['leads' => $warm]);
    }

    public function pipelineSummary(): void
    {
        $this->checkAuth();
        $leadModel = new LeadModel();

        $stageCounts = $leadModel->getLeadCountsByStatus();
        Response::success('', ['stages' => $stageCounts]);
    }

    public function salesTarget(): void
    {
        $this->checkAuth();
        $salesMetrics = new SalesMetricsService();

        $month = (int) ($_GET['month'] ?? date('n'));
        $year = (int) ($_GET['year'] ?? date('Y'));

        Response::success('', $salesMetrics->getMonthlyData($year, $month));
    }

    public function leadGeneration(): void
    {
        $this->checkAuth();
        $leadModel = new LeadModel();
        $settingsModel = new SettingsModel();

        $month = (int) ($_GET['month'] ?? date('n'));
        $year = (int) ($_GET['year'] ?? date('Y'));

        $leads = $leadModel->getLeadsBySource($year, $month);
        $targets = $settingsModel->getLeadGenerationTargets();

        $workingDaysService = new WorkingDaysService();
        $workingDaysLeft = $workingDaysService->remainingWorkingDaysInMonth($year, $month);

        Response::success('', [
            'sources' => $leads,
            'targets' => $targets,
            'working_days_left' => $workingDaysLeft,
        ]);
    }

    public function dailyLeads(): void
    {
        $this->checkAuth();
        $leadModel = new LeadModel();

        $today = date('Y-m-d');
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT src.name, COUNT(l.id) as count
             FROM lead_sources src
             LEFT JOIN leads l ON l.source_id = src.id AND DATE(l.initial_contact_date) = ? AND l.archived = 0
             WHERE src.active = 1
             GROUP BY src.id, src.name
             ORDER BY src.sort_order"
        );
        $stmt->execute([$today]);
        $dailyLeads = $stmt->fetchAll();

        Response::success('', ['leads' => $dailyLeads, 'date' => $today]);
    }

    public function leadsList(): void
    {
        $this->checkAuth();
        $leadModel = new LeadModel();

        $query = trim($_GET['q'] ?? '');
        if ($query === '') {
            Response::success('', []);
            return;
        }

        $leads = $leadModel->searchNames($query);
        Response::success('', $leads);
    }

    public function updateLeadStatus(): void
    {
        $this->checkAuth();
        $id = (int) ($_GET['params'][0] ?? 0);
        $data = json_decode(file_get_contents('php://input'), true);
        $value = $data['value'] ?? null;

        $leadModel = new LeadModel();
        $leadModel->updateById($id, ['status_id' => $value ? (int) $value : null]);
        Response::success('Status updated.');
    }

    public function updateLeadStage(): void
    {
        $this->checkAuth();
        $id = (int) ($_GET['params'][0] ?? 0);
        $data = json_decode(file_get_contents('php://input'), true);
        $value = $data['value'] ?? null;

        $leadModel = new LeadModel();
        $leadModel->updateById($id, ['opportunity_stage_id' => $value ? (int) $value : null]);
        Response::success('Stage updated.');
    }

    public function updateLeadPriority(): void
    {
        $this->checkAuth();
        $id = (int) ($_GET['params'][0] ?? 0);
        $data = json_decode(file_get_contents('php://input'), true);
        $value = $data['value'] ?? null;

        $leadModel = new LeadModel();
        $leadModel->updateById($id, ['priority_id' => $value ? (int) $value : null]);
        Response::success('Priority updated.');
    }

    public function addActivity(): void
    {
        $this->checkAuth();
        $id = (int) ($_GET['params'][0] ?? 0);
        $data = json_decode(file_get_contents('php://input'), true);

        $activityModel = new ActivityModel();
        $activityModel->create([
            'lead_id' => $id,
            'activity_type' => $data['activity_type'] ?? 'Other',
            'activity_date' => $data['activity_date'] ?? date('Y-m-d H:i:s'),
            'notes' => $data['notes'] ?? null,
            'next_step' => $data['next_step'] ?? null,
            'next_step_date' => $data['next_step_date'] ?? null,
            'created_by' => Security::userId(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $leadModel = new LeadModel();
        $updateData = ['last_contact_date' => date('Y-m-d')];
        if (!empty($data['next_step'])) $updateData['next_step'] = $data['next_step'];
        if (!empty($data['next_step_date'])) $updateData['next_step_date'] = $data['next_step_date'];
        $leadModel->updateById($id, $updateData);

        Response::success('Activity added.');
    }

    public function updateFollowup(): void
    {
        $this->checkAuth();
        $id = (int) ($_GET['params'][0] ?? 0);
        $data = json_decode(file_get_contents('php://input'), true);

        $leadModel = new LeadModel();
        $updateData = [];
        if (isset($data['next_step'])) $updateData['next_step'] = $data['next_step'] ?: null;
        if (isset($data['next_step_date'])) $updateData['next_step_date'] = $data['next_step_date'] ?: null;

        $leadModel->updateById($id, $updateData);
        Response::success('Follow-up updated.');
    }

    public function pipelineUpdateStage(): void
    {
        $this->checkAuth();
        $id = (int) ($_GET['params'][0] ?? 0);
        $data = json_decode(file_get_contents('php://input'), true);
        $stageId = $data['stage_id'] ?? null;

        $leadModel = new LeadModel();
        $leadModel->updateById($id, ['opportunity_stage_id' => $stageId ? (int) $stageId : null]);
        Response::success('Pipeline stage updated.');
    }

    private function checkAuth(): void
    {
        if (!Security::isLoggedIn()) {
            Response::error('Authentication required.', [], 401);
        }
    }
}
