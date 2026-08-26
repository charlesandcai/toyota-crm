<?php
declare(strict_types=1);

Router::apiGet('/api/dashboard/kpi', 'ApiController@dashboardKpi');
Router::apiGet('/api/dashboard/needs-attention', 'ApiController@needsAttention');
Router::apiGet('/api/dashboard/release-watch', 'ApiController@releaseWatch');
Router::apiGet('/api/dashboard/warm-leads', 'ApiController@warmLeads');
Router::apiGet('/api/dashboard/pipeline-summary', 'ApiController@pipelineSummary');
Router::apiGet('/api/dashboard/sales-target', 'ApiController@salesTarget');
Router::apiGet('/api/dashboard/lead-generation', 'ApiController@leadGeneration');
Router::apiGet('/api/dashboard/daily-leads', 'ApiController@dailyLeads');
Router::apiGet('/api/leads', 'ApiController@leadsList');

Router::apiPost('/api/leads/{id}/status', 'ApiController@updateLeadStatus');
Router::apiPost('/api/leads/{id}/stage', 'ApiController@updateLeadStage');
Router::apiPost('/api/leads/{id}/priority', 'ApiController@updateLeadPriority');
Router::apiPost('/api/leads/{id}/activity', 'ApiController@addActivity');
Router::apiPost('/api/leads/{id}/followup', 'ApiController@updateFollowup');
Router::apiPost('/api/pipeline/{id}/stage', 'ApiController@pipelineUpdateStage');
