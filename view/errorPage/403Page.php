<?php ob_start(); ?>
<h1>403 Forbidden</h1>
<?php $content = ob_get_clean(); ?>

<?php require('view/template.php'); ?>
