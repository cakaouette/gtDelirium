<?php ob_start(); ?>
<div class="card-body table-responsive p-0">
<table class="table table-head-fixed table-hover">
    <tr>
        <th>Pseudo</th>
        <?php for ($i = 0; $i < 14; $i++) {
            echo "<th>".($i+1)."</th>";
        } ?>
    </tr>
    <?php foreach ($v_missedByMemberByDay as $key => $missByDay) { if ($missByDay["noMiss"]) {continue;} ?>
    <tr>
        <td><?= $key ?></td>
        <?php for ($i = 0; $i < 14; $i++) {
            $bg = isset($missByDay["color$i"]) ? 'bgcolor="gray"' : '';
            echo "<td $bg>".$missByDay["day$i"]."</td>";
        } ?>
    </tr>
    <?php } ?>
</table>
</div>
<?php $content = ob_get_clean(); ?>

<?php require('view/template.php'); ?>
