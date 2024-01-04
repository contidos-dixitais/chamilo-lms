<?php

require_once __DIR__.'/../../../vendor/autoload.php';
require_once __DIR__.'/../../../app/AppKernel.php';

$kernel = new AppKernel('', '');

$alreadyInstalled = false;
if (file_exists($kernel->getConfigurationFile())) {
    require_once $kernel->getConfigurationFile();
    $alreadyInstalled = true;
}

require_once $_configuration['root_sys'].'main/inc/lib/api.lib.php';

session_name('ch_sid');
session_start();

$session = new ChamiloSession();

$endTime =  0;
$isExpired = false;
$timeLeft = -1;

$currentTime = time();

if ($alreadyInstalled && api_get_user_id()) {
    $endTime = $session->end_time();
    $isExpired = $session->is_expired();
} else {
    $endTime = $currentTime + 315360000; // add ten years
    $isExpired = false;
}

$timeLeft = $endTime - $currentTime;

if($endTime > 0) {
    echo json_encode(['sessionEndDate' => $endTime, 'sessionTimeLeft' => $timeLeft, 'sessionExpired' => $isExpired]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error retrieving data from the current session']);
}
