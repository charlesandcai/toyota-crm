<?php
declare(strict_types=1);

session_start();

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/app/helpers/Security.php';
require_once dirname(__DIR__) . '/app/helpers/Url.php';
require_once dirname(__DIR__) . '/app/helpers/Validation.php';
require_once dirname(__DIR__) . '/app/helpers/Response.php';

date_default_timezone_set(Config::get('APP_TIMEZONE', 'Asia/Manila'));
