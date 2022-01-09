<?php ob_start(); ?>
<div class="card card-primary card-outline">
  <div class="card-header">
    <h3 class="card-title">
      <i class="fas fa-chess-knight"></i>
      Stratégie
    </h3>
    <div class="card-tools">
      <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
      </button>
    </div>
  </div>
  <div class="card-body">
    <p>Suivez l'ordre d'attaque du mieux que vous pouvez :</p>
    <div class="row">
      <div class="col-3 col-sm-2">
        <div class="nav flex-column nav-tabs h-100" id="vert-tabs-tab" role="tablist" aria-orientation="vertical">
          <a class="nav-link"
             id="stage_1-tab" data-toggle="pill"
             href="#stage_1" role="tab"
             aria-controls="stage_1" aria-selected="true">Etape 1</a>
          <a class="nav-link active"
             id="stage_2-tab" data-toggle="pill"
             href="#stage_2" role="tab"
             aria-controls="stage_2" aria-selected="false">Etape 2</a>
          <a class="nav-link"
             id="stage_3-tab" data-toggle="pill"
             href="#stage_3" role="tab"
             aria-controls="stage_3" aria-selected="false">Etape 3</a>
          <a class="nav-link"
             id="extra-tab" data-toggle="pill"
             href="#extra" role="tab"
             aria-controls="extra" aria-selected="false">Les Camions</a>
        </div>
      </div>
      <div class="col-9 col-sm-10">
        <div class="tab-content" id="vert-tabs-tabContent">
          <div class="tab-pane fade"
               id="stage_1" role="tabpanel"
               aria-labelledby="stage_1-tab">
<table class="table table-striped">
    <thead>
      <tr>
        <th style="width: 10px">#</th>
        <th>Zone</th>
        <th>nombre</th>
        <th>Condition totale de complétion</th>
        <th>Effet</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1.</td>
        <td>Centre de communication</td>
        <td>1</td>
        <td>3 run</td>
        <td>debuff lv ennemis</td>
      </tr>
      <tr>
        <td>2.</td>
        <td>Armurerie</td>
        <td>1</td>
        <td>3 run</td>
        <td>buff attaque</td>
      </tr>
      <tr>
        <td>3.</td>
        <td>Camps (boss)</td>
        <td>7</td>
        <td>162M HP - level 75 à 73</td>
        <td>accès boss final</td>
      </tr>
      <tr>
        <td>4.</td>
        <td>Tour de contrôle (boss final)</td>
        <td>1</td>
        <td>50M HP - level 76</td>
        <td>accès zone suivante</td>
      </tr>
    </tbody>
</table>
<h5>Liste des boss</h5>
<ul>
    <li>basique - Minotaure - 18M</li>
    <li>basique - Minotaure - 24M</li>
    <li>basique - Minotaure - 30M</li>
    <li>water - Général bonhomme de neige Gast - 18M</li>
    <li>water - Général bonhomme de neige Gast - 24M</li>
    <li>ténèbre - Bête obscure - 18M</li>
    <li>ténèbre - Bête obscure - 30M</li>
</ul>
<h5>Boss final</h5>
<ul>
    <li>Basique - Chrome - 50M</li>
</ul>
          </div>
          <div class="tab-pane text-left fade show active"
              id="stage_2" role="tabpanel"
               aria-labelledby="story-stage_2">
<table class="table table-striped">
    <thead>
      <tr>
        <th style="width: 10px">#</th>
        <th>Zone</th>
        <th>nombre</th>
        <th>Condition totale de complétion</th>
        <th>Effet</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1.</td>
        <td>Centre de communication</td>
        <td>2</td>
        <td>20 run</td>
        <td>debuff lv ennemis</td>
      </tr>
      <tr>
        <td>2.</td>
        <td>Dépôt de munitions</td>
        <td>1</td>
        <td>3 run</td>
        <td>fait des dégâts aux boss alentour ou diminue le nb de run nécessaire pour les failles</td>
      </tr>
      <tr>
        <td>3.</td>
        <td>Armurerie</td>
        <td>2</td>
        <td>17 run</td>
        <td>buff attaque</td>
      </tr>
      <tr>
        <td>4.</td>
        <td>Centre de commande (/!\ à voir si c'est utile ou si on ignore)</td>
        <td>1</td>
        <td>7 run</td>
        <td>enlève certains effets</td>
      </tr>
      <tr>
        <td>5.</td>
        <td>Camps (boss)</td>
        <td>7</td>
        <td>724M HP - level 79 à 75</td>
        <td>accès boss final</td>
      </tr>
      <tr>
        <td>6.</td>
        <td>Tour de contrôle (boss final)</td>
        <td>1</td>
        <td>150M HP - level 80</td>
        <td>accès zone suivante</td>
      </tr>
    </tbody>
</table>
<h5>Liste des boss</h5>
<ul>
    <li>water - Général bonhomme de neige Gast - 76M</li>
    <li>water - Général bonhomme de neige Gast - 100M</li>
    <li>water - Général bonhomme de neige Gast - 124M</li>
    <li>terre - Ver taureau du désert - 100M</li>
    <li>terre - Ver taureau du désert - 124M</li>
    <li>ténèbre - Bête obscure - 76M</li>
    <li>ténèbre - Bête obscure - 124M</li>
</ul>
<h5>Boss final</h5>
<ul>
    <li>Feu - Elvira - 150M</li>
</ul>          
          </div>
          <div class="tab-pane fade"
               id="stage_3" role="tabpanel"
               aria-labelledby="stage_3-tab">
<table class="table table-striped">
    <thead>
      <tr>
        <th style="width: 10px">#</th>
        <th>Zone</th>
        <th>nombre</th>
        <th>Condition totale de complétion</th>
        <th>Effet</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1.</td>
        <td>Dépôt de munitions</td>
        <td>2</td>
        <td>10 run</td>
        <td>fait des dégâts aux boss alentour ou diminue le nb de run nécessaire pour les failles</td>
      </tr>
      <tr>
        <td>2.</td>
        <td>Centre de communication (/!\ ne pas faire Cc++, cf ligne 6)</td>
        <td>3</td>
        <td>30 run</td>
        <td>debuff lv ennemis</td>
      </tr>
      <tr>
        <td>3.</td>
        <td>Armurerie</td>
        <td>4</td>
        <td>40 run</td>
        <td>buff attaque</td>
      </tr>
      <tr>
        <td>4.</td>
        <td>Centre de commandement</td>
        <td>1</td>
        <td>10 run</td>
        <td>enlève certains effets</td>
      </tr>
      <tr>
        <td>5.</td>
        <td>Camps (boss)</td>
        <td>7</td>
        <td>1 324M HP - level 82 à 75</td>
        <td>accès boss final</td>
      </tr>
      <tr>
        <td>6.</td>
        <td>Centre de communication++ (près de la tour de contrôle)</td>
        <td>1</td>
        <td>???</td>
        <td>debuff lv ennemis</td>
      </tr>
      <tr>
        <td>7.</td>
        <td>Tour de contrôle (boss final)</td>
        <td>1</td>
        <td>450M HP - level 83</td>
        <td>accès zone suivante</td>
      </tr>
    </tbody>
</table>
<h5>Liste des boss</h5>
<ul>
    <li>eau - Général bonhomme de neige Gast - 124M</li>
    <li>terre - Ver taureau du désert - 160M</li>
    <li>terre - Ver taureau du désert - 240M</li>
    <li>ténèbre - Bête obscure - 160M</li>
    <li>ténèbre - Bête obscure - 200M</li>
    <li>basique - Minotaure - 200M</li>
    <li>basique - Minotaure - 240M</li>
</ul>
<h5>Boss final</h5>
<ul>
    <li>Ténèbre - Arabelle - 450M</li>
</ul>
          </div>
          <div class="tab-pane fade"
               id="extra" role="tabpanel"
               aria-labelledby="extra-tab">
              <h5>Les camions</h5>
              <p>
                  Les camions peuvent être fait à n'importe quel moment, même si le boss de zone sont fait.
                  <strong>Ils sont à donc à faire à la toute fin.</strong><br>
                  Ces petits boss apportent des récompenses.
              </p>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- /.card -->
</div>
<!-- /.card -->

<?php $content = ob_get_clean(); ?>

<?php require('view/template.php'); ?>
