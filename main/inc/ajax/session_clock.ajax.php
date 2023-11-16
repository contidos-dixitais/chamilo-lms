<?php

require_once __DIR__.'/../global.inc.php';

use ChamiloSession as Session;

api_block_anonymous_users();

$session = new Session();

$endTime = $session->end_time();
$isExpired = $session->is_expired();

echo json_encode(['timeLeft' => $endTime, 'expired' => $isExpired]);
