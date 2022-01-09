<?php ob_start(); ?>
<div class="col-md-6">
<div class="card card-info">
  <!-- form start -->
  <form class="form-horizontal" actionpage=member&subpage=pending" method="post">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-sm-5">
                <div class="form-group">
                    <label for="memberFormId">Joueur :</label>
                    <select name="memberForm"
                           id="memberFormId"
                           class="custom-select form-control-border border-width-2">
                        <option value="0"></option>
                        <?php foreach ($v_members as $member) { ?>
                        <option value="<?=$member->getId()?>"><?=$member->getName()?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="col-sm-1">
                <i class="fas fa-arrow-left"></i>
            </div>
            <div class="col-sm-5">
                <div class="form-group">
                    <label for="pendingFormId">Compte :</label></small>
                    <select name="pendingForm"
                           id="pendingFormId"
                           class="custom-select form-control-border border-width-2">
                        <?php foreach ($v_pendings as $pending) { ?>
                        <option value="<?=$pending->getId()?>"><?=$pending->getPseudo()?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <!-- /.card-body -->
    <div class="card-footer">
      <button type="submit" name="linkAccount" class="btn btn-info">Linker le compte</button>
    <!-- /.card-footer -->
    </div>
  </form>
</div>
</div>
<?php $content = ob_get_clean(); ?>

<?php require('view/template.php'); ?>
