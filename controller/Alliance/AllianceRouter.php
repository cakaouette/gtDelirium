<?php
require('AllianceControler.php');

$controller = new AllianceControler();
if (in_array(($_GET['subpage'] ?? null), Array("addguild", "editguild"))) {
    $_SESSION["url"][] = Array("print" => "Nouvelle guilde", "url" => "index.php?page=alliance&subpage=addguild");
    $id = getVar($_GET, "id");
    $controller->guildFormView($id);
} else {
    $isAddGuild = isset($_POST["addGuildForm"]);
    $isEditGuild = isset($_POST["editGuildForm"]);
    if ($isAddGuild) {
        $controller->addGuild(
            $name = getVar($_POST, "nameForm"),
            $color = getVar($_POST, "colorForm"),
        );
    } elseif ($isEditGuild) {
        //TODO
    }
    $controller->listGuildInfo();
}
return;
