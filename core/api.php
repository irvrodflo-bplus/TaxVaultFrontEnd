<?php

$envPath = __DIR__ . '';
require_once $envPath . '/../environment/env-singleton.php';

$env = Environment::getInstance();

$API_BASE_URL = $env->get('API');
$API_KEY = $env->get('API_KEY');