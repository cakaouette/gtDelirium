<?php
function printSelectHero($teamNb, $heroNb, $characters, $elements) {
    $prevElement = "0";
    $prevLevel = "0";
    $heroFunc = 'getHero'.$heroNb.'Id';
    foreach ($characters as $charId => $charInfo) {
        $charName = ($charId == 0) ? $charInfo->getName() : $charInfo["level"]."* ".$charInfo->getName();
        if ($prevLevel != $charInfo->getGrade()) {
            $prevLevel = $charInfo->getGrade();
            echo "<optgroup label='Level $prevLevel*'>";
        }
        if ($prevElement != $charInfo->getElementInfo()["id"]) {
            $prevElement = $charInfo->getElementInfo()["id"];
            echo '<optgroup label='.$elements[$prevElement].'>';
        }
        $sel = ($charId == $teamNb->$heroFunc()) ? "selected" : "";
        echo "<option value=$charId $sel>$charName</option>";
    }
    echo '</optgroup>';
}
?>

<?php ob_start(); ?>
<p>Team 1</p>
<form action="?page=member&subpage=team&id=<?= $_team1->getMemberInfo()["id"] ?>" method="post">
    <p>
        <input type="hidden" name="idForm" value="<?= $_team1->getId() ?>"/>
        <input type="hidden" name="memberForm" value="<?= $_team1->getMemberInfo()["id"] ?>"/>
        <input type="hidden" name="teamForm" value="<?= $_team1->getTeamNumber() ?>"/>
        <select name="hero1Form">
            <?php
            echo printSelectHero($_team1,1, $v_characters, $elements);
            ?>
        </select>
        <select name="hero2Form">
            <?php
            echo printSelectHero($_team1,2, $v_characters, $elements);
            ?>
        </select>
        <select name="hero3Form">
            <?php
            echo printSelectHero($_team1,3, $v_characters, $elements);
            ?>
        </select>
        <select name="hero4Form">
            <?php
            echo printSelectHero($_team1,4, $v_characters, $elements);
            ?>
        </select>
        <?php if ($_team1->getId() == 0) {
            echo '<input type="submit" name="team1AddForm" value="Save" />';
        } else {
            echo '<input type="submit" name="team1UpdateForm" value="Save" />';
        }?>
    </p>
</form>
<p>Team 2</p>
<form action="?page=member&subpage=team&id=<?= $_team2->getMemberInfo()["id"] ?>" method="post">
    <p>
        <input type="hidden" name="idForm" value="<?= $_team2->getId() ?>"/>
        <input type="hidden" name="memberForm" value="<?= $_team2->getMemberInfo()["id"] ?>"/>
        <input type="hidden" name="teamForm" value="<?= $_team2->getTeamNumber() ?>"/>
        <select name="hero1Form">
            <?php
            echo printSelectHero($_team2,1, $v_characters, $elements);
            ?>
        </select>
        <select name="hero2Form">
            <?php
            echo printSelectHero($_team2,2, $v_characters, $elements);
            ?>
        </select>
        <select name="hero3Form">
            <?php
            echo printSelectHero($_team2,3, $v_characters, $elements);
            ?>
        </select>
        <select name="hero4Form">
            <?php
            echo printSelectHero($_team2,4, $v_characters, $elements);
            ?>
        </select>
        <?php if ($_team2->getId() == 0) {
            echo '<input type="submit" name="team2AddForm" value="Save" />';
        } else {
            echo '<input type="submit" name="team2UpdateForm" value="Save" />';
        }?>
    </p>
</form>
<p>Team 3</p>
<form action="?page=member&subpage=team&id=<?= $_team3->getMemberInfo()["id"] ?>" method="post">
    <p>
        <input type="hidden" name="idForm" value="<?= $_team3->getId() ?>"/>
        <input type="hidden" name="memberForm" value="<?= $_team3->getMemberInfo()["id"] ?>"/>
        <input type="hidden" name="teamForm" value="<?= $_team3->getTeamNumber() ?>"/>
        <select name="hero1Form">
            <?php
            echo printSelectHero($_team3,1, $v_characters, $elements);
            ?>
        </select>
        <select name="hero2Form">
            <?php
            echo printSelectHero($_team3,2, $v_characters, $elements);
            ?>
        </select>
        <select name="hero3Form">
            <?php
            echo printSelectHero($_team3,3, $v_characters, $elements);
            ?>
        </select>
        <select name="hero4Form">
            <?php
            echo printSelectHero($_team3,4, $v_characters, $elements);
            ?>
        </select>
        <?php if ($_team3->getId() == 0) {
            echo '<input type="submit" name="team3AddForm" value="Save" />';
        } else {
            echo '<input type="submit" name="team3UpdateForm" value="Save" />';
        }?>
    </p>
</form>
<?php $content = ob_get_clean(); ?>

<?php require('view/template.php'); ?>
