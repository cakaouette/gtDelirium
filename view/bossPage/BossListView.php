<?php ob_start(); ?>
<div class="row">
<?php foreach ($v_bosses as $bossId => $boss) { 
?>
<div class="col-lg-3 col-sm-4 col-6">
  <div class="info-box">
    <div class=" col-4">
      <a href="?page=boss&subpage=info&id=<?=$bossId?>">
        <img src="dist/image/bosses/<?=$bossId?>.png"
             class="img-fluid img-thumbnail"
             style="padding: 0"
             width="200" height="200"
             alt="<?=$boss->getName()?>">
      </a>
    </div>
    <div class="info-box-content col-6">
        <?=$boss->getName()?>
    </div>
  </div>
</div>
<?php } ?>
</div>
<?php $content = ob_get_clean(); ?>

<?php require('view/template.php');
