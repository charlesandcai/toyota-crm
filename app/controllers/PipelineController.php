<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/models/Model.php';
require_once dirname(__DIR__, 2) . '/app/models/LeadModel.php';
require_once dirname(__DIR__, 2) . '/app/models/SettingsModel.php';

class PipelineController
{
    public function index(): void
    {
        Security::requireAuth();

        $leadModel = new LeadModel();
        $settingsModel = new SettingsModel();
        $userId = Security::isAdmin() ? null : Security::userId();

        $stages = $settingsModel->getStages();

        $stageLeads = [];
        foreach ($stages as $stage) {
            $leads = $leadModel->getStageLeads($stage['id'], $userId);
            $stageLeads[$stage['id']] = $leads;
        }

        $activePage = 'pipeline';
        Response::view('pipeline.index', compact('activePage', 'stages', 'stageLeads'));
    }
}
