<?php

require_once dirname(__DIR__, 5) . '/wp-config.php';
require_once __DIR__ . '/database.new.php';
$database = new database(DB_NAME, DB_USER, DB_PASSWORD, DB_HOST);
$siteName = strtoupper(DB_NAME);
