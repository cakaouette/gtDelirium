<?php ob_start(); ?>
<div class="row">
  <div class="col-md-6">
    <h4>Quelques perturbations pour les joueurs suivants</h4>
      <div class="card-body table-responsive p-0">
        <table class="table table-head-fixed table-hover">
          <tr>
            <th>Pseudo</th>
            <th>Nombre d'attaques</th>
          </tr>
          <?php foreach ($v_meteo as $count) { if ($count["count"] >= 3) {continue;} ?>
          <tr>
            <td><?= $count["memberName"]?></td>
            <td><?= $count["count"]?></td>
          </tr>
          <?php } ?>
        </table>
      </div>
  </div>
  <div class="col-md-6">
    <h4>Récap des scores</h4>
      <div class="card-body table-responsive p-0">
        <table class="table table-head-fixed table-hover">
          <tr>
            <th></th>
            <th>Pseudo</th>
            <th>Attaques</th>
            <th>Totaux</th>
          </tr>
          <?php $i = 0; foreach ($v_meteo as $count) { ?>
          <tr>
            <td><?= ++$i ?></td>
            <td><?= $count["memberName"]?></td>
            <td><?= $count["count"]?>/3</td>
            <td align="right"><?= number_format($count["dailyDamage"], 0, ',', ' ')?></td>
          </tr>
          <?php } ?>
        </table>
      </div>
  </div>
</div>
<?php $content = ob_get_clean(); ?>

<?php require('view/template.php'); ?>
