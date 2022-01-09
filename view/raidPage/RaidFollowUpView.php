<?php ob_start(); ?>
<style>
    .withoutLineBreak { white-space:nowrap; }
</style>
<div class="card-body table-responsive p-0" style="height: 500px;">
<table class="table table-head-fixed table-hover">
    <tr>
        <th border="none"></th>
        <th>Pseudo</th>
        <th>Total j=<?=$v_prevDayNumber?></th>
        <?php for ($i = 0; $i < 14; $i++) {
            echo "<th>".($i+1)."</th>";
        } ?>
        <th>Total</th>
    </tr>
    <?php foreach ($v_damagesByMemberByDay as $key => $damagesByDay) {?>
    <tr>
        <td><?php echo ($key == 0) ? "1er" : ($key+1)."ème"; ?></td>
        <td><?= $damagesByDay["memberName"]?></td>
        <td><?php
$sum = $damagesByDay["yesterdaySum"]; $prev = $damagesByDay["yesterdaySumPrev"]; $diff = !is_null($prev) ? $sum-$prev : NULL;
$sign = $diff > 0 ? "+" : "";?>
            <div class="withoutLineBreak">
                <?= number_format($sum, 0, ',', ' ')?>
            </div>
            <div class="withoutLineBreak">
                <?php echo (!is_null($diff) ? " (".$sign.number_format($diff, 0, ',', ' ').")" : "");
                ?>
            </div>
        </td>
<?php for ($i = 0; $i < 14; $i++) {
    $day = $damagesByDay["day$i"];
    $prev = $damagesByDay["day$i"."Prev"];
    $diff = (!is_null($day) and !is_null($prev)) ? $day-$prev : NULL;
    $sign = $diff > 0 ? "+" : "";
    $dayPrint = !is_null($day) ? number_format($day, 0, ',', ' ') : "";
    $dayPrevPrint = (!is_null($diff) ? " (".$sign.number_format($diff, 0, ',', ' ').")" : "");
?>
        <td>
            <div class="withoutLineBreak">
                <?= $dayPrint ?>
            </div>
            <div class="withoutLineBreak">
                <?= $dayPrevPrint ?>
            </div>
        </td>
<?php } ?>
        <td><?php
$sum = $damagesByDay["daysSum"]; $prev = $damagesByDay["daysSumPrev"]; $diff = !is_null($prev) ? $sum-$prev : NULL;
$sign = $diff > 0 ? "+" : "";?>
            <div class="withoutLineBreak">
                <?= number_format($sum, 0, ',', ' ')?>
            </div>
            <div class="withoutLineBreak">
                <?php echo (!is_null($diff) ? " (".$sign.number_format($diff, 0, ',', ' ').")" : "");
                ?>
            </div>
        </td>
    </tr>
    <?php } ?>
</table>
</div>
<?php $content = ob_get_clean(); ?>

<?php require('view/template.php'); ?>
