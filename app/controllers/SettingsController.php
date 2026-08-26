<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/models/Model.php';
require_once dirname(__DIR__, 2) . '/app/models/SettingsModel.php';
require_once dirname(__DIR__, 2) . '/app/services/WorkingDaysService.php';

class SettingsController
{
    private SettingsModel $settingsModel;

    public function __construct()
    {
        $this->settingsModel = new SettingsModel();
    }

    public function index(): void
    {
        Security::requireAuth();

        $statuses = $this->settingsModel->getStatuses(false);
        $stages = $this->settingsModel->getStages(false);
        $priorities = $this->settingsModel->getPriorities(false);
        $sources = $this->settingsModel->getSources(false);
        $models = $this->settingsModel->getModels(false);
        $colors = $this->settingsModel->getColors(false);
        $salesTargets = $this->settingsModel->getSalesTargets();
        $leadGenTargets = $this->settingsModel->getLeadGenerationTargets();

        $workingDaysService = new WorkingDaysService();
        $workingDays = $workingDaysService->getWorkingDaysConfig();
        $holidays = $this->settingsModel->getHolidays((int) date('Y'));

        $currentYear = (int) date('Y');

        $activePage = 'settings';
        Response::view('settings.index', compact(
            'activePage', 'statuses', 'stages', 'priorities', 'sources', 'models', 'colors',
            'salesTargets', 'leadGenTargets', 'workingDays', 'holidays', 'currentYear'
        ));
    }

    public function storeSource(): void
    {
        Security::requireAuth();
        if (!Security::verifyCSRFToken($_POST['_csrf_token'] ?? '')) { Response::error('Invalid form.'); return; }
        $name = trim($_POST['name'] ?? '');
        if ($name === '') { Response::error('Name required.'); return; }
        $maxOrder = $this->settingsModel->fetchOne("SELECT MAX(sort_order) as m FROM lead_sources");
        $this->settingsModel->createSource($name, ($maxOrder['m'] ?? 0) + 1);
        Response::success('Source added.');
    }

    public function updateSource(): void
    {
        Security::requireAuth();
        if (!Security::verifyCSRFToken($_POST['_csrf_token'] ?? '')) { Response::error('Invalid form.'); return; }
        $id = (int) ($_POST['id'] ?? 0);
        $data = [];
        if (isset($_POST['name'])) $data['name'] = trim($_POST['name']);
        if (isset($_POST['active'])) $data['active'] = (int) $_POST['active'];
        $this->settingsModel->updateSource($id, $data);
        Response::success('Source updated.');
    }

    public function storeStatus(): void
    {
        Security::requireAuth();
        if (!Security::verifyCSRFToken($_POST['_csrf_token'] ?? '')) { Response::error('Invalid form.'); return; }
        $name = trim($_POST['name'] ?? '');
        if ($name === '') { Response::error('Name required.'); return; }
        $color = trim($_POST['color'] ?? '#6c757d');
        $maxOrder = $this->settingsModel->fetchOne("SELECT MAX(sort_order) as m FROM lead_statuses");
        $this->settingsModel->createStatus($name, $color, ($maxOrder['m'] ?? 0) + 1);
        Response::success('Status added.');
    }

    public function updateStatus(): void
    {
        Security::requireAuth();
        if (!Security::verifyCSRFToken($_POST['_csrf_token'] ?? '')) { Response::error('Invalid form.'); return; }
        $id = (int) ($_POST['id'] ?? 0);
        $data = [];
        if (isset($_POST['name'])) $data['name'] = trim($_POST['name']);
        if (isset($_POST['color'])) $data['color'] = trim($_POST['color']);
        if (isset($_POST['active'])) $data['active'] = (int) $_POST['active'];
        if (isset($_POST['sort_order'])) $data['sort_order'] = (int) $_POST['sort_order'];
        $this->settingsModel->updateStatus($id, $data);
        Response::success('Status updated.');
    }

    public function storeStage(): void
    {
        Security::requireAuth();
        if (!Security::verifyCSRFToken($_POST['_csrf_token'] ?? '')) { Response::error('Invalid form.'); return; }
        $name = trim($_POST['name'] ?? '');
        if ($name === '') { Response::error('Name required.'); return; }
        $color = trim($_POST['color'] ?? '#6c757d');
        $maxOrder = $this->settingsModel->fetchOne("SELECT MAX(sort_order) as m FROM opportunity_stages");
        $this->settingsModel->createStage($name, $color, ($maxOrder['m'] ?? 0) + 1);
        Response::success('Stage added.');
    }

    public function updateStage(): void
    {
        Security::requireAuth();
        if (!Security::verifyCSRFToken($_POST['_csrf_token'] ?? '')) { Response::error('Invalid form.'); return; }
        $id = (int) ($_POST['id'] ?? 0);
        $data = [];
        if (isset($_POST['name'])) $data['name'] = trim($_POST['name']);
        if (isset($_POST['color'])) $data['color'] = trim($_POST['color']);
        if (isset($_POST['active'])) $data['active'] = (int) $_POST['active'];
        if (isset($_POST['sort_order'])) $data['sort_order'] = (int) $_POST['sort_order'];
        $this->settingsModel->updateStage($id, $data);
        Response::success('Stage updated.');
    }

    public function storePriority(): void
    {
        Security::requireAuth();
        if (!Security::verifyCSRFToken($_POST['_csrf_token'] ?? '')) { Response::error('Invalid form.'); return; }
        $name = trim($_POST['name'] ?? '');
        if ($name === '') { Response::error('Name required.'); return; }
        $color = trim($_POST['color'] ?? '#6c757d');
        $maxLevel = $this->settingsModel->fetchOne("SELECT MAX(level) as m FROM priorities");
        $this->settingsModel->createPriority($name, $color, ($maxLevel['m'] ?? 0) + 1);
        Response::success('Priority added.');
    }

    public function updatePriority(): void
    {
        Security::requireAuth();
        if (!Security::verifyCSRFToken($_POST['_csrf_token'] ?? '')) { Response::error('Invalid form.'); return; }
        $id = (int) ($_POST['id'] ?? 0);
        $data = [];
        if (isset($_POST['name'])) $data['name'] = trim($_POST['name']);
        if (isset($_POST['color'])) $data['color'] = trim($_POST['color']);
        if (isset($_POST['active'])) $data['active'] = (int) $_POST['active'];
        $this->settingsModel->updatePriority($id, $data);
        Response::success('Priority updated.');
    }

    public function storeModel(): void
    {
        Security::requireAuth();
        if (!Security::verifyCSRFToken($_POST['_csrf_token'] ?? '')) { Response::error('Invalid form.'); return; }
        $name = trim($_POST['name'] ?? '');
        if ($name === '') { Response::error('Name required.'); return; }
        $maxOrder = $this->settingsModel->fetchOne("SELECT MAX(sort_order) as m FROM vehicle_models");
        $this->settingsModel->createModel($name, ($maxOrder['m'] ?? 0) + 1);
        Response::success('Model added.');
    }

    public function updateModel(): void
    {
        Security::requireAuth();
        if (!Security::verifyCSRFToken($_POST['_csrf_token'] ?? '')) { Response::error('Invalid form.'); return; }
        $id = (int) ($_POST['id'] ?? 0);
        $data = [];
        if (isset($_POST['name'])) $data['name'] = trim($_POST['name']);
        if (isset($_POST['active'])) $data['active'] = (int) $_POST['active'];
        $this->settingsModel->updateModel($id, $data);
        Response::success('Model updated.');
    }

    public function storeColor(): void
    {
        Security::requireAuth();
        if (!Security::verifyCSRFToken($_POST['_csrf_token'] ?? '')) { Response::error('Invalid form.'); return; }
        $name = trim($_POST['name'] ?? '');
        if ($name === '') { Response::error('Name required.'); return; }
        $maxOrder = $this->settingsModel->fetchOne("SELECT MAX(sort_order) as m FROM vehicle_colors");
        $this->settingsModel->createColor($name, ($maxOrder['m'] ?? 0) + 1);
        Response::success('Color added.');
    }

    public function updateColor(): void
    {
        Security::requireAuth();
        if (!Security::verifyCSRFToken($_POST['_csrf_token'] ?? '')) { Response::error('Invalid form.'); return; }
        $id = (int) ($_POST['id'] ?? 0);
        $data = [];
        if (isset($_POST['name'])) $data['name'] = trim($_POST['name']);
        if (isset($_POST['active'])) $data['active'] = (int) $_POST['active'];
        $this->settingsModel->updateColor($id, $data);
        Response::success('Color updated.');
    }

    public function storeTarget(): void
    {
        Security::requireAuth();
        if (!Security::verifyCSRFToken($_POST['_csrf_token'] ?? '')) { Response::error('Invalid form.'); return; }
        $year = (int) ($_POST['year'] ?? 0);
        $month = (int) ($_POST['month'] ?? 0);
        $target = (int) ($_POST['target'] ?? 0);
        if ($year === 0 || $month === 0) { Response::error('Year and month required.'); return; }
        $this->settingsModel->setSalesTarget($year, $month, $target);
        Response::success('Sales target saved.');
    }

    public function updateTarget(): void
    {
        Security::requireAuth();
        if (!Security::verifyCSRFToken($_POST['_csrf_token'] ?? '')) { Response::error('Invalid form.'); return; }
        $id = (int) ($_POST['id'] ?? 0);
        $target = (int) ($_POST['target'] ?? 0);
        if ($id === 0) { Response::error('Invalid target.'); return; }
        $this->settingsModel->updateSalesTarget($id, $target);
        Response::success('Sales target updated.');
    }

    public function deleteTarget(): void
    {
        Security::requireAuth();
        if (!Security::verifyCSRFToken($_POST['_csrf_token'] ?? '')) { Response::error('Invalid form.'); return; }
        $id = (int) ($_POST['id'] ?? 0);
        if ($id === 0) { Response::error('Invalid target.'); return; }
        $this->settingsModel->deleteSalesTarget($id);
        Response::success('Sales target deleted.');
    }

    public function storeLeadTarget(): void
    {
        Security::requireAuth();
        if (!Security::verifyCSRFToken($_POST['_csrf_token'] ?? '')) { Response::error('Invalid form.'); return; }
        $year = (int) ($_POST['year'] ?? 0);
        $month = (int) ($_POST['month'] ?? 0);
        $sourceId = (int) ($_POST['source_id'] ?? 0);
        $target = (int) ($_POST['target'] ?? 0);
        if ($year === 0 || $month === 0 || $sourceId === 0) { Response::error('All fields required.'); return; }
        $this->settingsModel->setLeadGenTarget($year, $month, $sourceId, $target);
        Response::success('Lead target saved.');
    }

    public function updateLeadTarget(): void
    {
        Security::requireAuth();
        if (!Security::verifyCSRFToken($_POST['_csrf_token'] ?? '')) { Response::error('Invalid form.'); return; }
        $id = (int) ($_POST['id'] ?? 0);
        $target = (int) ($_POST['target'] ?? 0);
        if ($id === 0) { Response::error('Invalid target.'); return; }
        $this->settingsModel->updateLeadGenTarget($id, $target);
        Response::success('Lead target updated.');
    }

    public function deleteLeadTarget(): void
    {
        Security::requireAuth();
        if (!Security::verifyCSRFToken($_POST['_csrf_token'] ?? '')) { Response::error('Invalid form.'); return; }
        $id = (int) ($_POST['id'] ?? 0);
        if ($id === 0) { Response::error('Invalid target.'); return; }
        $this->settingsModel->deleteLeadGenTarget($id);
        Response::success('Lead target deleted.');
    }

    public function updateWorkingDays(): void
    {
        Security::requireAuth();
        if (!Security::verifyCSRFToken($_POST['_csrf_token'] ?? '')) { Response::error('Invalid form.'); return; }
        $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
        foreach ($days as $day) {
            $isWorking = isset($_POST['working_days']) && in_array($day, $_POST['working_days']);
            $this->settingsModel->updateWorkingDay($day, $isWorking);
        }
        Response::success('Working days updated.');
    }

    public function storeHoliday(): void
    {
        Security::requireAuth();
        if (!Security::verifyCSRFToken($_POST['_csrf_token'] ?? '')) { Response::error('Invalid form.'); return; }
        $date = trim($_POST['holiday_date'] ?? '');
        $name = trim($_POST['holiday_name'] ?? '');
        if ($date === '' || $name === '') { Response::error('Date and name required.'); return; }
        $this->settingsModel->addHoliday($date, $name);
        Response::success('Holiday added.');
    }

    public function deleteHoliday(): void
    {
        Security::requireAuth();
        if (!Security::verifyCSRFToken($_POST['_csrf_token'] ?? '')) { Response::error('Invalid form.'); return; }
        $id = (int) ($_POST['id'] ?? 0);
        $this->settingsModel->deleteHoliday($id);
        Response::success('Holiday removed.');
    }
}
