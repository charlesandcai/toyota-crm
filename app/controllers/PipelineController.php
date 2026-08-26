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
        $db = Database::getConnection();

        $stages = $settingsModel->getStages();

        $stageLeads = [];
        foreach ($stages as $stage) {
            $leads = $leadModel->fetchAll(
                "SELECT l.*, 
                        ls.name as status_name, ls.color as status_color,
                        p.name as priority_name, p.color as priority_color, p.level as priority_level,
                        src.name as source_name,
                        vm.name as model_name
                 FROM leads l
                 LEFT JOIN lead_statuses ls ON l.status_id = ls.id
                 LEFT JOIN priorities p ON l.priority_id = p.id
                 LEFT JOIN lead_sources src ON l.source_id = src.id
                 LEFT JOIN vehicle_models vm ON l.model_id = vm.id
                 WHERE l.archived = 0 AND l.opportunity_stage_id = ?
                 ORDER BY p.level ASC, CASE WHEN l.next_step_date IS NULL THEN 1 ELSE 0 END, l.next_step_date ASC",
                [$stage['id']]
            );
            $stageLeads[$stage['id']] = $leads;
        }

        $activePage = 'pipeline';
        Response::view('pipeline.index', compact('activePage', 'stages', 'stageLeads'));
    }
}
