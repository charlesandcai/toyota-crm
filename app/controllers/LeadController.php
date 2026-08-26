<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/models/Model.php';
require_once dirname(__DIR__, 2) . '/app/models/LeadModel.php';
require_once dirname(__DIR__, 2) . '/app/models/SettingsModel.php';
require_once dirname(__DIR__, 2) . '/app/models/ActivityModel.php';
require_once dirname(__DIR__, 2) . '/app/services/FollowUpService.php';

class LeadController
{
    private LeadModel $leadModel;
    private SettingsModel $settingsModel;

    public function __construct()
    {
        $this->leadModel = new LeadModel();
        $this->settingsModel = new SettingsModel();
    }

    public function index(): void
    {
        Security::requireAuth();

        $filters = [];
        if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];
        if (!empty($_GET['status_id'])) $filters['status_id'] = (int) $_GET['status_id'];
        if (!empty($_GET['opportunity_stage_id'])) $filters['opportunity_stage_id'] = (int) $_GET['opportunity_stage_id'];
        if (!empty($_GET['priority_id'])) $filters['priority_id'] = (int) $_GET['priority_id'];
        if (!empty($_GET['source_id'])) $filters['source_id'] = (int) $_GET['source_id'];
        if (!empty($_GET['model_id'])) $filters['model_id'] = (int) $_GET['model_id'];
        if (!empty($_GET['date_from'])) $filters['date_from'] = $_GET['date_from'];
        if (!empty($_GET['date_to'])) $filters['date_to'] = $_GET['date_to'];
        if (!empty($_GET['filter'])) $filters['quick_filter'] = $_GET['filter'];

        $sort = $_GET['sort'] ?? 'l.next_step_date';
        $direction = $_GET['direction'] ?? 'ASC';
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 25;
        $offset = ($page - 1) * $perPage;

        $result = $this->leadModel->findWithDetails($filters, $sort, $direction, $offset, $perPage);

        $totalLeads = $result['total'];
        $totalPages = max(1, (int) ceil($totalLeads / $perPage));

        $statuses = $this->settingsModel->getStatuses();
        $stages = $this->settingsModel->getStages();
        $priorities = $this->settingsModel->getPriorities();
        $sources = $this->settingsModel->getSources();
        $models = $this->settingsModel->getModels();

        $activePage = 'leads';
        Response::view('leads.index', compact(
            'activePage', 'result', 'totalLeads', 'totalPages', 'page', 'sort', 'direction',
            'statuses', 'stages', 'priorities', 'sources', 'models'
        ));
    }

    public function create(): void
    {
        Security::requireAuth();

        $statuses = $this->settingsModel->getStatuses();
        $stages = $this->settingsModel->getStages();
        $priorities = $this->settingsModel->getPriorities();
        $sources = $this->settingsModel->getSources();
        $models = $this->settingsModel->getModels();
        $colors = $this->settingsModel->getColors();
        $newLeadId = $this->leadModel->generateLeadId();

        $activePage = 'leads';
        Response::view('leads.create', compact(
            'activePage', 'statuses', 'stages', 'priorities', 'sources', 'models', 'colors', 'newLeadId'
        ));
    }

    public function store(): void
    {
        Security::requireAuth();

        if (!Security::verifyCSRFToken($_POST['_csrf_token'] ?? '')) {
            Response::error('Invalid form submission.');
            return;
        }

        $data = $this->validateLeadInput();
        if (isset($data['errors'])) {
            Response::error('Validation failed.', $data['errors']);
            return;
        }

        $leadId = $this->leadModel->generateLeadId();
        $data['lead_id'] = $leadId;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['archived'] = 0;

        try {
            $id = $this->leadModel->create($data);
            
            if (!empty($data['next_step_date'])) {
                $activityModel = new ActivityModel();
                $activityModel->create([
                    'lead_id' => $id,
                    'activity_type' => 'Other',
                    'activity_date' => date('Y-m-d H:i:s'),
                    'notes' => 'Lead created',
                    'next_step' => $data['next_step'] ?? null,
                    'next_step_date' => $data['next_step_date'] ?? null,
                    'created_by' => Security::userId(),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                Response::success('Lead created successfully.', ['id' => $id, 'lead_id' => $leadId]);
            } else {
                Url::redirect('leads/' . $id);
            }
        } catch (Exception $e) {
            error_log("Lead creation error: " . $e->getMessage());
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                Response::error('Unable to create lead. Please try again.');
            } else {
                $_SESSION['flash_error'] = 'Unable to create lead. Please try again.';
                Url::redirect('leads/create');
            }
        }
    }

    public function show(): void
    {
        Security::requireAuth();

        $id = (int) ($_GET['params'][0] ?? 0);
        $lead = $this->leadModel->findById($id);

        if (!$lead) {
            Url::redirect('leads');
            return;
        }

        $activityModel = new ActivityModel();
        $activities = $activityModel->getByLeadId($id);

        $followupStatus = FollowUpService::calculateStatus($lead['next_step_date']);
        $daysSinceContact = FollowUpService::daysSinceContact($lead['last_contact_date']);

        $statuses = $this->settingsModel->getStatuses();
        $stages = $this->settingsModel->getStages();
        $priorities = $this->settingsModel->getPriorities();
        $activityTypes = ['Call', 'Message', 'Meeting', 'Test Drive', 'Quote', 'Financing', 'Follow-up', 'Other'];

        $activePage = 'leads';
        Response::view('leads.show', compact(
            'activePage', 'lead', 'activities', 'followupStatus', 'daysSinceContact',
            'statuses', 'stages', 'priorities', 'activityTypes'
        ));
    }

    public function edit(): void
    {
        Security::requireAuth();

        $id = (int) ($_GET['params'][0] ?? 0);
        $lead = $this->leadModel->findById($id);

        if (!$lead) {
            Url::redirect('leads');
            return;
        }

        $statuses = $this->settingsModel->getStatuses();
        $stages = $this->settingsModel->getStages();
        $priorities = $this->settingsModel->getPriorities();
        $sources = $this->settingsModel->getSources();
        $models = $this->settingsModel->getModels();
        $colors = $this->settingsModel->getColors();

        $activePage = 'leads';
        Response::view('leads.edit', compact(
            'activePage', 'lead', 'statuses', 'stages', 'priorities', 'sources', 'models', 'colors'
        ));
    }

    public function update(): void
    {
        Security::requireAuth();

        if (!Security::verifyCSRFToken($_POST['_csrf_token'] ?? '')) {
            Response::error('Invalid form submission.');
            return;
        }

        $id = (int) ($_GET['params'][0] ?? 0);
        $data = $this->validateLeadInput();
        if (isset($data['errors'])) {
            Response::error('Validation failed.', $data['errors']);
            return;
        }

        $data['updated_at'] = date('Y-m-d H:i:s');

        try {
            $this->leadModel->updateById($id, $data);

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                Response::success('Lead updated successfully.');
            } else {
                Url::redirect('leads/' . $id);
            }
        } catch (Exception $e) {
            error_log("Lead update error: " . $e->getMessage());
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                Response::error('Unable to update lead.');
            } else {
                Url::redirect('leads/' . $id);
            }
        }
    }

    public function archived(): void
    {
        Security::requireAuth();

        $filters = [];
        if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];
        if (!empty($_GET['status_id'])) $filters['status_id'] = (int) $_GET['status_id'];
        if (!empty($_GET['opportunity_stage_id'])) $filters['opportunity_stage_id'] = (int) $_GET['opportunity_stage_id'];
        if (!empty($_GET['priority_id'])) $filters['priority_id'] = (int) $_GET['priority_id'];
        if (!empty($_GET['source_id'])) $filters['source_id'] = (int) $_GET['source_id'];

        $sort = $_GET['sort'] ?? 'l.archived_at';
        $direction = $_GET['direction'] ?? 'DESC';
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 25;
        $offset = ($page - 1) * $perPage;

        $result = $this->leadModel->findArchived($filters, $sort, $direction, $offset, $perPage);

        $totalLeads = $result['total'];
        $totalPages = max(1, (int) ceil($totalLeads / $perPage));

        $statuses = $this->settingsModel->getStatuses();
        $stages = $this->settingsModel->getStages();
        $priorities = $this->settingsModel->getPriorities();
        $sources = $this->settingsModel->getSources();

        $activePage = 'leads_archived';
        Response::view('leads.archived', compact(
            'activePage', 'result', 'totalLeads', 'totalPages', 'page', 'sort', 'direction',
            'statuses', 'stages', 'priorities', 'sources'
        ));
    }

    public function archive(): void
    {
        Security::requireAuth();

        if (!Security::verifyCSRFToken($_POST['_csrf_token'] ?? '')) {
            Response::error('Invalid form submission.');
            return;
        }

        $id = (int) ($_GET['params'][0] ?? 0);

        try {
            $this->leadModel->archive($id);
            
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                Response::success('Lead archived.');
            } else {
                Url::redirect('leads');
            }
        } catch (Exception $e) {
            error_log("Lead archive error: " . $e->getMessage());
            Response::error('Unable to archive lead.');
        }
    }

    public function restore(): void
    {
        Security::requireAuth();

        if (!Security::verifyCSRFToken($_POST['_csrf_token'] ?? '')) {
            Response::error('Invalid form submission.');
            return;
        }

        $id = (int) ($_GET['params'][0] ?? 0);

        try {
            $this->leadModel->restore($id);
            
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                Response::success('Lead restored.');
            } else {
                Url::redirect('leads/archived');
            }
        } catch (Exception $e) {
            error_log("Lead restore error: " . $e->getMessage());
            Response::error('Unable to restore lead.');
        }
    }

    public function forceDelete(): void
    {
        Security::requireAuth();

        if (!Security::verifyCSRFToken($_POST['_csrf_token'] ?? '')) {
            Response::error('Invalid form submission.');
            return;
        }

        $confirm = trim($_POST['confirm_delete'] ?? '');
        if ($confirm !== 'DELETE') {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                Response::error('Type DELETE to confirm.');
            } else {
                Url::redirect('leads/archived');
            }
            return;
        }

        $id = (int) ($_GET['params'][0] ?? 0);

        try {
            $this->leadModel->forceDelete($id);
            
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                Response::success('Lead permanently deleted.');
            } else {
                Url::redirect('leads/archived');
            }
        } catch (Exception $e) {
            error_log("Lead permanent delete error: " . $e->getMessage());
            Response::error('Unable to delete lead.');
        }
    }

    public function export(): void
    {
        Security::requireAuth();

        $filters = [];
        if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];
        if (!empty($_GET['status_id'])) $filters['status_id'] = (int) $_GET['status_id'];

        $result = $this->leadModel->findWithDetails($filters, 'l.created_at', 'DESC', 0, 10000);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="leads_export_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Lead ID', 'Name', 'Company', 'Phone', 'Email', 'Status', 'Stage', 'Priority', 'Source', 'Model', 'Color', 'Initial Contact', 'Last Contact', 'Next Step', 'Next Step Date', 'Location', 'Notes']);

        foreach ($result['leads'] as $lead) {
            fputcsv($output, [
                $lead['lead_id'],
                $lead['lead_name'],
                $lead['company'] ?? '',
                $lead['phone'] ?? '',
                $lead['email'] ?? '',
                $lead['status_name'] ?? '',
                $lead['stage_name'] ?? '',
                $lead['priority_name'] ?? '',
                $lead['source_name'] ?? '',
                $lead['model_name'] ?? '',
                $lead['color_name'] ?? '',
                $lead['initial_contact_date'] ?? '',
                $lead['last_contact_date'] ?? '',
                $lead['next_step'] ?? '',
                $lead['next_step_date'] ?? '',
                $lead['location'] ?? '',
                $lead['notes'] ?? '',
            ]);
        }

        fclose($output);
        exit;
    }

    private function validateLeadInput(): array
    {
        $data = [];
        $errors = [];

        $leadName = trim($_POST['lead_name'] ?? '');
        if ($leadName === '') {
            $errors['lead_name'] = 'Lead name is required.';
        } elseif (mb_strlen($leadName) > 255) {
            $errors['lead_name'] = 'Lead name must not exceed 255 characters.';
        }
        $data['lead_name'] = $leadName;

        $company = trim($_POST['company'] ?? '');
        $data['company'] = $company ?: null;

        $phone = trim($_POST['phone'] ?? '');
        $data['phone'] = $phone ?: null;

        $email = trim($_POST['email'] ?? '');
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }
        $data['email'] = $email ?: null;

        $data['status_id'] = !empty($_POST['status_id']) ? (int) $_POST['status_id'] : null;
        $data['opportunity_stage_id'] = !empty($_POST['opportunity_stage_id']) ? (int) $_POST['opportunity_stage_id'] : null;
        $data['priority_id'] = !empty($_POST['priority_id']) ? (int) $_POST['priority_id'] : null;
        $data['source_id'] = !empty($_POST['source_id']) ? (int) $_POST['source_id'] : null;
        $data['model_id'] = !empty($_POST['model_id']) ? (int) $_POST['model_id'] : null;
        $data['color_id'] = !empty($_POST['color_id']) ? (int) $_POST['color_id'] : null;

        $initialContact = trim($_POST['initial_contact_date'] ?? '');
        if ($initialContact && !$this->isValidDate($initialContact)) {
            $errors['initial_contact_date'] = 'Please enter a valid date.';
        }
        $data['initial_contact_date'] = $initialContact ?: null;

        $lastContact = trim($_POST['last_contact_date'] ?? '');
        $data['last_contact_date'] = $lastContact ?: null;

        $nextStep = trim($_POST['next_step'] ?? '');
        $data['next_step'] = $nextStep ?: null;

        $nextStepDate = trim($_POST['next_step_date'] ?? '');
        if ($nextStepDate && !$this->isValidDate($nextStepDate)) {
            $errors['next_step_date'] = 'Please enter a valid date.';
        }
        $data['next_step_date'] = $nextStepDate ?: null;

        $location = trim($_POST['location'] ?? '');
        $data['location'] = $location ?: null;

        $notes = trim($_POST['notes'] ?? '');
        $data['notes'] = $notes ?: null;

        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        return $data;
    }

    private function isValidDate(string $date): bool
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
