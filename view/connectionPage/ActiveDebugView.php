<?php ob_start(); ?>
    <form action="?page=debug" method="post"><p>
        <input type="hidden" name="debugForm"/>
<?php if ($_SESSION["debug"] ?? false) {?>
        <input type="submit" name="desactiveDebugForm" value="Desactive" />
<?php } else { ?>
        <input type="submit" name="activeDebugForm" value="Active" />
<?php } ?>
    </form>
<?php $content = ob_get_clean(); ?>

<?php require('view/template.php'); ?>
