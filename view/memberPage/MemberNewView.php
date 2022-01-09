<?php ob_start(); ?>
<form action="?page=member&subpage=new" method="post">
    <p>
        <input type="text" name="nameForm" placeholder="Pseudo Guardian Tales"/>
        <select name="guildForm">
            <option value="0"></option>
          <?php foreach ($v_guilds as $guild) { ?>
            <option value="<?=$guild->getId()?>"><?=$guild->getName()?></option>
          <?php } ?>
        </select>
        <input type="date" name="dateStartForm">(*)
        <input type="submit" name="addMemberForm" value="Ajouter" />
    </p>
    (*) date autorisée à raid = lendemain de la d'arrivée dans la guilde
</form>
<?php $content = ob_get_clean(); ?>

<?php require('view/template.php'); ?>
