<?php

namespace Puggan\Ibn\D;

require_once dirname(__DIR__, 5) . '/wp-config.php';
require_once __DIR__ . '/Database.php';
new Database(DB_NAME, DB_USER, DB_PASSWORD, DB_HOST);
