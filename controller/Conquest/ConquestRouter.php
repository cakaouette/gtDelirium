<?php
//require('MemberControler.php');
//
//$controller = new MemberControler();
if (!isset($_GET['subpage'])) {return; }

if ($_GET['subpage'] == 'priority') {
    $v_content_header = "Conquête en cours";
    $v_title = "Conquête";
    require('view/conquest/ConquestStrategyView.php');
    return;
    
} elseif ($_GET['subpage'] == 'old') {
    $v_content_header = "Stratégie de conquête";
    $v_title = "Conquête";
    require('view/conquest/ConquestStrategy2View.php');
    return;
    
} elseif ($_GET['subpage'] == 'tremens') {
    $v_content_header = "Stratégie des Tremens";
    $v_title = "Conquête";
    require('view/conquest/ConquestStrategyTremensView.php');
    return;
    
} elseif ($_GET['subpage'] == 'nocturnum') {
    $v_content_header = "Stratégie des Nocturnums";
    $v_title = "Conquête";
    require('view/conquest/ConquestStrategyNocturnumView.php');
    return;
    
}