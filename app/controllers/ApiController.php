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
        $userId = Security::isAdmin() ? null : Security::userId();
        $salesMetrics = new SalesMetricsService($userId);

        $month = (int) ($_GET['month'] ?? date('n'));
        $year = (int) ($_GET['year'] ?? date('Y'));
        $salesData = $salesMetrics->getMonthlyData($year, $month);

        $settingsModel = new SettingsModel();
        $releaseStage = $settingsModel->getSetting('closed_release_stage') ?: 'Released';

        Response::success('', [
            'total_leads' => $leadModel->getTotalCount($userId, $year, $month),
            'active_deals' => $leadModel->getActiveDealsCount($userId, $year, $month, $releaseStage),
            'warm_leads' => $leadModel->getWarmLeadsCount($userId, $year, $month),
            'deals_to_close' => $leadModel->getDealsToCloseCount($userId, $year, $month),
            'sales_target' => $salesData,
        ]);
    }

    public function needsAttention(): void
    {
        $this->checkAuth();
        $leadModel = new LeadModel();
        $userId = Security::isAdmin() ? null : Security::userId();

        $overdue = $leadModel->getOverdueLeads($userId);
        $dueToday = $leadModel->getDueTodayLeads($userId);

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
        $userId = Security::isAdmin() ? null : Security::userId();

        $releaseStages = WarmLeadService::getReleaseWatchStages();
        $stages = $settingsModel->getStages();
        $stageIds = [];
        foreach ($stages as $s) {
            if (in_array($s['name'], $releaseStages)) {
                $stageIds[] = $s['id'];
            }
        }

        $leads = $leadModel->getReleaseWatchLeads($stageIds, $userId);

        Response::success('', ['leads' => $leads]);
    }

    public function warmLeads(): void
    {
        $this->checkAuth();
        $leadModel = new LeadModel();
        $userId = Security::isAdmin() ? null : Security::userId();

        $allActive = $leadModel->getWarmHotLeads(50, $userId);
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
        $userId = Security::isAdmin() ? null : Security::userId();

        $stageCounts = $leadModel->getLeadCountsByStatus($userId);
        Response::success('', ['stages' => $stageCounts]);
    }

    public function salesTarget(): void
    {
        $this->checkAuth();
        $userId = Security::isAdmin() ? null : Security::userId();
        $salesMetrics = new SalesMetricsService($userId);

        $month = (int) ($_GET['month'] ?? date('n'));
        $year = (int) ($_GET['year'] ?? date('Y'));

        Response::success('', $salesMetrics->getMonthlyData($year, $month));
    }

    public function leadGeneration(): void
    {
        $this->checkAuth();
        $leadModel = new LeadModel();
        $settingsModel = new SettingsModel();
        $userId = Security::isAdmin() ? null : Security::userId();

        $month = (int) ($_GET['month'] ?? date('n'));
        $year = (int) ($_GET['year'] ?? date('Y'));

        $leads = $leadModel->getLeadsBySource($year, $month, $userId);
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
        $userId = Security::isAdmin() ? null : Security::userId();

        $today = date('Y-m-d');
        $db = Database::getConnection();

        $userCondition = $userId !== null ? ' AND l.user_id = ?' : '';
        $params = [$today];
        if ($userId !== null) {
            $params[] = $userId;
        }

        $stmt = $db->prepare(
            "SELECT src.name, COUNT(l.id) as count
             FROM lead_sources src
             LEFT JOIN leads l ON l.source_id = src.id AND DATE(l.initial_contact_date) = ? AND l.archived = 0 {$userCondition}
             WHERE src.active = 1
             GROUP BY src.id, src.name
             ORDER BY src.sort_order"
        );
        $stmt->execute($params);
        $dailyLeads = $stmt->fetchAll();

        Response::success('', ['leads' => $dailyLeads, 'date' => $today]);
    }

    public function leadsList(): void
    {
        $this->checkAuth();
        $leadModel = new LeadModel();
        $userId = Security::isAdmin() ? null : Security::userId();

        $query = trim($_GET['q'] ?? '');
        if ($query === '') {
            Response::success('', []);
            return;
        }

        $leads = $leadModel->searchNames($query, $userId);
        Response::success('', $leads);
    }

    public function updateLeadStatus(): void
    {
        $this->checkAuth();
        $id = (int) ($_GET['params'][0] ?? 0);
        $userId = Security::isAdmin() ? null : Security::userId();

        // Authorization check
        if ($userId !== null) {
            $leadModel = new LeadModel();
            if (!$leadModel->ownsLead($id, $userId)) {
                Response::error('Access denied.', [], 403);
                return;
            }
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $value = $data['value'] ?? null;

        $leadModel = $leadModel ?? new LeadModel();
        $leadModel->updateById($id, ['status_id' => $value ? (int) $value : null]);
        Response::success('Status updated.');
    }

    public function updateLeadStage(): void
    {
        $this->checkAuth();
        $id = (int) ($_GET['params'][0] ?? 0);
        $userId = Security::isAdmin() ? null : Security::userId();

        if ($userId !== null) {
            $leadModel = new LeadModel();
            if (!$leadModel->ownsLead($id, $userId)) {
                Response::error('Access denied.', [], 403);
                return;
            }
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $value = $data['value'] ?? null;

        $leadModel = $leadModel ?? new LeadModel();
        $leadModel->updateById($id, ['opportunity_stage_id' => $value ? (int) $value : null]);
        Response::success('Stage updated.');
    }

    public function updateLeadPriority(): void
    {
        $this->checkAuth();
        $id = (int) ($_GET['params'][0] ?? 0);
        $userId = Security::isAdmin() ? null : Security::userId();

        if ($userId !== null) {
            $leadModel = new LeadModel();
            if (!$leadModel->ownsLead($id, $userId)) {
                Response::error('Access denied.', [], 403);
                return;
            }
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $value = $data['value'] ?? null;

        $leadModel = $leadModel ?? new LeadModel();
        $leadModel->updateById($id, ['priority_id' => $value ? (int) $value : null]);
        Response::success('Priority updated.');
    }

    public function addActivity(): void
    {
        $this->checkAuth();
        $id = (int) ($_GET['params'][0] ?? 0);
        $userId = Security::isAdmin() ? null : Security::userId();

        // Verify lead ownership
        if ($userId !== null) {
            $leadModel = new LeadModel();
            if (!$leadModel->ownsLead($id, $userId)) {
                Response::error('Access denied.', [], 403);
                return;
            }
        }

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

        $leadModel = $leadModel ?? new LeadModel();
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
        $userId = Security::isAdmin() ? null : Security::userId();

        if ($userId !== null) {
            $leadModel = new LeadModel();
            if (!$leadModel->ownsLead($id, $userId)) {
                Response::error('Access denied.', [], 403);
                return;
            }
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $leadModel = $leadModel ?? new LeadModel();
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
        $userId = Security::isAdmin() ? null : Security::userId();

        if ($userId !== null) {
            $leadModel = new LeadModel();
            if (!$leadModel->ownsLead($id, $userId)) {
                Response::error('Access denied.', [], 403);
                return;
            }
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $stageId = $data['stage_id'] ?? null;

        $leadModel = $leadModel ?? new LeadModel();
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
