<?php ob_start(); ?>
<form action="?page=member&subpage=edit&id=<?=$f_member->getId()?>" method="post">
    <p>
        <input type="hidden" name="idForm" value="<?=$f_member->getId()?>">
        <input type="text" name="nameForm" value="<?=$f_member->getName()?>">
        <select name="guildForm">
                <option value="0" <?php echo $f_member->getGuildInfo()["id"] == 0 ? "selected" : "" ?>></option>
            <?php foreach ($v_guilds as $guild) {
                $sel = $guild->getId() == $f_member->getGuildInfo()["id"] ? "selected" : ""; ?>
                <option value="<?=$guild->getId()?>" <?=$sel?>><?=$guild->getName()?></option>
            <?php } ?>
        </select>
        <input type="date" name="dateStartForm" value="<?=$f_member->getDateStart()?>">
        <input type="submit" name="editMemberForm" value="Sauvegarder" />
    </p>
</form>
<a href="?page=member&subpage=alliance">Retour</a>
<?php $content = ob_get_clean(); ?>

<?php require('view/template.php'); ?>
