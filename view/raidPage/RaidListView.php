<?php ob_start(); ?>
<table>
    <tr>
        <th>Pseudo</th>
        <th>Guilde</th>
        <th>Date</th>
        <th>Team</th>
        <th>Boss</th>
        <th>Dégâts</th>
        <th>Hero1</th>
        <th>Hero2</th>
        <th>Hero3</th>
        <th>Hero4</th>
    </tr>
    <?php foreach ($fights as $fight) {?>
            <tr>
                <td><?= $fight->getPseudoInfo()["name"]?></td>
                <td><?= $fight->getGuildInfo()["name"]?></td>
                <td><?= $fight->getDate()?></td>
                <td><?= $fight->getTeamNumber()?></td>
                <td><?= $fight->getBossInfo()["name"]?></td>
                <td><?= $fight->getDamage()?></td>
                <td><?= $fight->getHero1Info()["name"]?></td>
                <td><?= $fight->getHero2Info()["name"]?></td>
                <td><?= $fight->getHero3Info()["name"]?></td>
                <td><?= $fight->getHero4Info()["name"]?></td>
            </tr>
    <?php } ?>
</table>
<?php $content = ob_get_clean(); ?>

<?php require('view/template.php'); ?>
