<?php ob_start(); ?>
      <div id="accordion">
<div class="row">
  <?php foreach ($v_members as $guildName => $guild) { $isSel = ($_SESSION["guild"]["name"] == $guildName);?>
    <div class="col-md-4">
      <div class="card card-<?=$guild["color"]?>">
        <div class="card-header">
          <h3 class="card-title w-100">
              <a class="d-block w-100 <?php echo $isSel ? "" : "collapsed";?>" data-toggle="collapse" href="#collapse<?=$guild["guildId"]?>" aria-expanded="<?php echo $isSel ? "true" : "false";?>"><?=$guildName?></a>
          </h3>
        </div>
        <!-- /.card-header -->
        <div id="collapse<?=$guild["guildId"]?>" class="collapse <?php echo $isSel ? "show" : "";?>" data-parent="#accordion">
        <div class="card-body">
          <table>
            <tr>
              <th>Nom</th>
              <th>Date d'arrivée</th>
            <?php if ($_SESSION["grade"] <= $_SESSION["Officier"]) { ?>
              <th></th>
            <?php } ?> 
            </tr>
          <?php foreach ($guild["members"] as $member) { ?>
            <tr>
              <td><a href="?page=member&subpage=crew&id=<?=$member->getId()?>"><?=$member->getName()?></a></td>
              <td><?=$member->getDateStart()?></td>
            <?php if ($_SESSION["grade"] <= $_SESSION["Officier"]) { ?>
              <td>
                  <a href="?page=member&subpage=edit&id=<?=$member->getId()?>" style="padding-right: 15px"><i class="fas fa-edit"></i></a>
                  <a href="?page=member&subpage=delete&id=<?=$member->getId()?>" ><i class="fas fa-trash-alt"></i></a>
              </td>
            <?php } ?> 
            </tr>
          <?php } ?>
          </table>
        </div>
        </div>
         <!-- /.card-body -->
      </div>
    </div>
  <?php } ?>
</div>
      </div>

<?php $content = ob_get_clean(); ?>

<?php require('view/template.php'); ?>
