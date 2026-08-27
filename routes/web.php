<?php
declare(strict_types=1);

// Auth
Router::get('/auth/login', 'AuthController@showLogin');
Router::post('/auth/login', 'AuthController@login');
Router::get('/auth/logout', 'AuthController@logout');

// Dashboard
Router::get('/', 'DashboardController@index');
Router::get('/dashboard', 'DashboardController@index');

// Leads
Router::get('/leads', 'LeadController@index');
Router::get('/leads/archived', 'LeadController@archived');
Router::get('/leads/create', 'LeadController@create');
Router::post('/leads/store', 'LeadController@store');
Router::get('/leads/{id}', 'LeadController@show');
Router::get('/leads/{id}/edit', 'LeadController@edit');
Router::post('/leads/{id}/update', 'LeadController@update');
Router::post('/leads/{id}/archive', 'LeadController@archive');
Router::post('/leads/{id}/restore', 'LeadController@restore');
Router::post('/leads/{id}/force-delete', 'LeadController@forceDelete');
Router::get('/leads/{id}/activity/add', 'ActivityController@createForLead');
Router::post('/leads/{id}/activity/store', 'ActivityController@storeForLead');
Router::get('/leads/export', 'LeadController@export');

// Pipeline
Router::get('/pipeline', 'PipelineController@index');

// Calendar
Router::get('/calendar', 'CalendarController@index');

// Activities
Router::get('/activities', 'ActivityController@index');

// Reports
Router::get('/reports', 'ReportController@index');
Router::get('/reports/monthly-summary', 'ReportController@monthlySummary');
Router::get('/reports/lead-performance', 'ReportController@leadPerformance');
Router::get('/reports/sales-performance', 'ReportController@salesPerformance');
Router::get('/reports/followup-performance', 'ReportController@followupPerformance');

// Settings
Router::get('/settings', 'SettingsController@index');
Router::post('/settings/sources/store', 'SettingsController@storeSource');
Router::post('/settings/sources/update', 'SettingsController@updateSource');
Router::post('/settings/statuses/store', 'SettingsController@storeStatus');
Router::post('/settings/statuses/update', 'SettingsController@updateStatus');
Router::post('/settings/stages/store', 'SettingsController@storeStage');
Router::post('/settings/stages/update', 'SettingsController@updateStage');
Router::post('/settings/priorities/store', 'SettingsController@storePriority');
Router::post('/settings/priorities/update', 'SettingsController@updatePriority');
Router::post('/settings/models/store', 'SettingsController@storeModel');
Router::post('/settings/models/update', 'SettingsController@updateModel');
Router::post('/settings/colors/store', 'SettingsController@storeColor');
Router::post('/settings/colors/update', 'SettingsController@updateColor');
Router::post('/settings/targets/store', 'SettingsController@storeTarget');
Router::post('/settings/targets/update', 'SettingsController@updateTarget');
Router::post('/settings/targets/delete', 'SettingsController@deleteTarget');
Router::post('/settings/lead-targets/store', 'SettingsController@storeLeadTarget');
Router::post('/settings/lead-targets/update', 'SettingsController@updateLeadTarget');
Router::post('/settings/lead-targets/delete', 'SettingsController@deleteLeadTarget');
Router::post('/settings/working-days/update', 'SettingsController@updateWorkingDays');
Router::post('/settings/holidays/store', 'SettingsController@storeHoliday');
Router::post('/settings/holidays/delete', 'SettingsController@deleteHoliday');

// Imports
Router::get('/imports', 'ImportController@index');
Router::post('/imports/upload', 'ImportController@upload');
Router::post('/imports/process', 'ImportController@process');

// User Management (admin only)
Router::get('/settings/users', 'UserController@index');
Router::post('/settings/users/store', 'UserController@store');
Router::post('/settings/users/update', 'UserController@update');
Router::post('/settings/users/password', 'UserController@updatePassword');
