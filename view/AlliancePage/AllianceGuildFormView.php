<?php ob_start(); ?>
<div class="col-md-12">
<div class="card card-info">
  <!-- form start -->
  <form class="form-horizontal" action="?page=alliance" method="post">
    <div class="card-body">
    <!-- Name -->
    <?php $id = $f_guild->getId();
          $name = $id == 0 ? NULL : $f_guild->getName();
          $color = $id == 0 ? NULL : $f_guild->getColor();
          if ($id == 0) { ?>
      <div class="form-group">
        <input type="hidden"
               name="idForm"
               value="<?=$id?>">
      </div>
          <?php } ?>
      <div class="form-group">
        <label for="nameFormId">Nom</label></small>
        <input type="text"
               class="form-control form-control-border border-width-2" 
               name="nameForm"
               id="nameFormId"
               placeholder="Nom de la guilde"
               value="<?=$name?>">
      </div>
      <div class="form-group">
        <label for="colorFormId">Couleur</label>
        <input type="text"
               class="form-control form-control-border border-width-2" 
               name="colorForm"
               id="colorFormId"
               placeholder="Couleur sur le site">
      </div>
    </div>
      
    <!-- /.card-body -->
    <div class="card-footer">
      <?php if ($id == 0) { ?>
        <button type="submit" name="addGuildForm" class="btn btn-info">Créer</button>
      <?php } else { ?>
        <button type="submit" name="editGuildForm" class="btn btn-info">Sauvegarder</button>
      <?php } ?>
<!--      <button type="submit" class="btn btn-default float-right">Cancel</button>-->
    <!-- /.card-footer -->
    </div>
  </form>
</div>
</div>
    
<?php $content = ob_get_clean(); ?>

<?php require('view/template.php'); ?>
