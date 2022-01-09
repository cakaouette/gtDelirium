<?php
require('ApiControler.php');

if (!isset($_GET['subpage'])) { exit(); }

$controller = new ApiControler();

if ($_GET['subpage'] == 'alarm') {
    $controller->sendAlarm();
    
    echo "200";
    exit();
} elseif ($_GET['subpage'] == 'helpCrew') {
    $id = getVar($_GET, "id");
    $controller->sendTeamRaid($id, "");
    
    echo "200";
    exit();
}
