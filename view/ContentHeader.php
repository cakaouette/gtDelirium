<?php ob_start(); ?>
<div class="container-fluid">
  <div class="row mb-2">
    <div class="col-sm-6">
      <blockquote class="quote-<?php echo $_SESSION["guild"]["color"] ?? "info" ?>"> 
        <h1><?= $v_content_header  ?? "" ?></h1>
      </blockquote>
    </div>
    <div class="col-sm-6">
      <ol class="breadcrumb float-sm-right">
        <?php $size = sizeof($_SESSION["url"]);
        for ($i = 0; $i < $size; $i++) {
            $pr = $_SESSION["url"][$i]["print"];
            $url = $_SESSION["url"][$i]["url"];
            $active = ($i == ($size -1)) ? "active" : ""; 
            $print = ($i == ($size -1)) ? "$pr" : "<a href=\"$url\">$pr</a>"; ?>
        <li class="breadcrumb-item <?=$active?>"><?=$print?></li>
        <?php } ?>
      </ol>
    </div>
  </div>
</div><!-- /.container-fluid -->

<?php $content_header = ob_get_clean(); ?>

