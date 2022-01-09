<?php ob_start(); ?>
<!--<div class="card">
<div class="card-header">
  <h3 class="card-title">Team Nocturnum</h3>
</div>-->
<!-- /.card-header -->
<!--<div class="card-body">-->
  <!-- we are adding the accordion ID so Bootstrap's collapse plugin detects it -->
  <div id="accordion">
    <div class="card card-red">
      <div class="card-header">
        <h4 class="card-title w-100">
          <a class="d-block w-100" data-toggle="collapse" href="#step1">
            étape 1
          </a>
        </h4>
      </div>
      <div id="step1" class="collapse show" data-parent="#accordion">
        <div class="card-body">
<p>Coucou tout le monde demain commence la nouvelle semaine de la conquête de guilde. On va refaire un point sur la stratégie à adopter c'est important. 
    Pour l'étape 1 :</p>

<ol>
<li>Dans un premier temps le centre de communication doit être focus avec de préférence des teams pas trop fortes qui sont suffisantes pour passer les passages.
</li>
<li>Dans un second temps l'armurerie doit être focus toujours avec des teams pas trop fortes qui sont suffisantes pour passer les passages. 
</li>
<li>Ensuite, les boss des camps peuvent être focus cette fois-ci avec toutes les teams l'objectif étant de tous les tuer pour débloquer la tour de l'étape 1.
</li>
<li>Enfin, le boss de l'étape 1 doit être focus et tuer pour pouvoir passer à l'étape 2.
</li>
</ol>

<p>Pour ce qui est de camions, <strong>ils ne doivent pas être focus</strong> surtout pas ils sont à réserver à la fin. En effet, les seuls attaques sur les camions qui sont valables sont celles avec des teams faibles qui se font one shoot par le boss de la tour et qui ne sont donc pas utile dessus et qui donc peuvent taper les camions plutôt que de taper le boss de la tour.
</p>
<strong>ATTENTION : cette stratégie doit être au maximum respecté pour faire mieux que la semaine dernière. C'est pourquoi si on voit des joueurs ne pas du tout la respecter et par exemple focus les camions alors qu'il y a d'autres choses à faire on prendra des sanctions.
</strong>
<p>Voilà vous avez toute la stratégie de l'étape 1. Je reviendrai vers vous lorsque l'on sera à l'étape 2. 
</p>
<p>Et enfin amusez vous bien cette semaine pour cette nouvelle conquête et hésitez pas à poser des questions sur la stratégie si jamais des points ne vous paraissent pas clairs😄
</p>
        </div>
      </div>
    </div>
    <div class="card card-red">
      <div class="card-header">
        <h4 class="card-title w-100">
          <a class="d-block w-100" data-toggle="collapse" href="#step2">
            étape 2
          </a>
        </h4>
      </div>
      <div id="step2" class="collapse" data-parent="#accordion">
        <div class="card-body">
<p>L'étape 2 est disponible et voici la stratégie.
</p>
<ol>
<li>Premièrement, les centres de communication doivent être focus.
</li>
<li>Deuxièmement, on se concentreras sur le dépôt de munitions qui est relié à l'armurerie et au centre de commandement. 
</li>
<li>Troisièmement, on attaquera les deux armureries pour être buff en dégâts.
</li>
<li>Quatrièmement, on pourra focus les boss des camps avec nos teams les plus fortes et qui font le plus de dégâts et avec les teams les plus faibles on pourra faire les passages du centre de commandement.
</li>
<li>Cinquièmement, on pourra finir les boss des camps.
</li>
</ol>
<p>Ensuite, on pourra focus le boss de la tour et essayer de le battre cette fois-ci.
</p>
<p><strong>ATTENTION : les camions sont à focus uniquement à la fin au moment où il n'y plus que le boss de la tour avec les teams qui prennent trop cher pour faire de réels dégâts sur le boss de la tour.
</strong></p>
<p>Voilà la stratégie pour l'étape 2.<br>
Hésitez pas à demander si quelque n'est pas clair pour vous😄
</p>
        </div>
      </div>
    </div>
    <div class="card card-red">
      <div class="card-header">
        <h4 class="card-title w-100">
          <a class="d-block w-100" data-toggle="collapse" href="#step3">
            étape 3
          </a>
        </h4>
      </div>
      <div id="step3" class="collapse" data-parent="#accordion">
        <div class="card-body">
          TODO
        </div>
      </div>
    </div>
  </div>
<!--</div>-->
<!-- /.card-body -->
<!--</div>-->
<?php $content = ob_get_clean(); ?>

<?php require('view/template.php');
