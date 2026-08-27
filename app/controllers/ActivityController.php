<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/models/Model.php';
require_once dirname(__DIR__, 2) . '/app/models/ActivityModel.php';
require_once dirname(__DIR__, 2) . '/app/models/SettingsModel.php';
require_once dirname(__DIR__, 2) . '/app/models/LeadModel.php';

class ActivityController
{
    private ActivityModel $activityModel;

    public function __construct()
    {
        $this->activityModel = new ActivityModel();
    }

    public function index(): void
    {
        Security::requireAuth();

        $userId = Security::isAdmin() ? null : Security::userId();
        $month = (int) ($_GET['month'] ?? date('n'));
        $year = (int) ($_GET['year'] ?? date('Y'));
        $activities = $this->activityModel->getByMonth($year, $month, $userId);

        $activePage = 'activities';
        Response::view('activities.index', compact('activePage', 'activities', 'month', 'year'));
    }

    public function createForLead(): void
    {
        Security::requireAuth();
        $leadId = (int) ($_GET['params'][0] ?? 0);

        // Verify lead ownership
        $userId = Security::isAdmin() ? null : Security::userId();
        if ($userId !== null) {
            $leadModel = new LeadModel();
            if (!$leadModel->ownsLead($leadId, $userId)) {
                Url::redirect('leads');
                return;
            }
        }

        Url::redirect('leads/' . $leadId);
    }

    public function storeForLead(): void
    {
        Security::requireAuth();

        if (!Security::verifyCSRFToken($_POST['_csrf_token'] ?? '')) {
            Response::error('Invalid form submission.');
            return;
        }

        $leadId = (int) ($_GET['params'][0] ?? 0);

        // Verify lead ownership
        $userId = Security::isAdmin() ? null : Security::userId();
        if ($userId !== null) {
            $leadModel = new LeadModel();
            if (!$leadModel->ownsLead($leadId, $userId)) {
                Response::error('Access denied.');
                return;
            }
        }

        $activityType = trim($_POST['activity_type'] ?? '');
        $activityDate = trim($_POST['activity_date'] ?? date('Y-m-d\TH:i'));
        $notes = trim($_POST['notes'] ?? '');
        $nextStep = trim($_POST['next_step'] ?? '');
        $nextStepDate = trim($_POST['next_step_date'] ?? '');

        if ($activityType === '') {
            Response::error('Activity type is required.');
            return;
        }

        try {
            $this->activityModel->create([
                'lead_id' => $leadId,
                'activity_type' => $activityType,
                'activity_date' => $activityDate ? date('Y-m-d H:i:s', strtotime($activityDate)) : date('Y-m-d H:i:s'),
                'notes' => $notes ?: null,
                'next_step' => $nextStep ?: null,
                'next_step_date' => $nextStepDate ?: null,
                'created_by' => Security::userId(),
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $leadModel = $leadModel ?? new LeadModel();
            $updateData = [
                'last_contact_date' => date('Y-m-d'),
            ];
            if ($nextStep) $updateData['next_step'] = $nextStep;
            if ($nextStepDate) $updateData['next_step_date'] = $nextStepDate;
            $leadModel->updateById($leadId, $updateData);

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                Response::success('Activity added.');
            } else {
                Url::redirect('leads/' . $leadId);
            }
        } catch (Exception $e) {
            error_log("Activity creation error: " . $e->getMessage());
            Response::error('Unable to add activity.');
        }
    }
}
