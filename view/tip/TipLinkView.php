<?php ob_start(); ?>
<div class="card card-primary card-outline">
  <div class="card-header">
    <h3 class="card-title">
      <i class="fas fa-edit"></i>
      Liens
    </h3>
    <div class="card-tools">
      <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
      </button>
    </div>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col-3 col-sm-2">
        <div class="nav flex-column nav-tabs h-100" id="vert-tabs-tab" role="tablist" aria-orientation="vertical">
          <a class="nav-link active"
             id="google_sheet-tab" data-toggle="pill"
             href="#google_sheet" role="tab"
             aria-controls="google_sheet" aria-selected="true">Google Sheet</a>
          <a class="nav-link"
             id="story-tab" data-toggle="pill"
             href="#story" role="tab"
             aria-controls="story" aria-selected="false">Histoire</a>
          <a class="nav-link"
             id="tier_list-tab" data-toggle="pill"
             href="#tier_list" role="tab"
             aria-controls="tier_list" aria-selected="false">Tier liste & Conseils</a>
          <a class="nav-link"
             id="team_build-tab" data-toggle="pill"
             href="#team_build" role="tab"
             aria-controls="team_build" aria-selected="false">Construction Team</a>
          <a class="nav-link"
             id="colise-tab" data-toggle="pill"
             href="#colise" role="tab"
             aria-controls="colise" aria-selected="false">Colisé</a>
      <?php if ($_SESSION["grade"] <= $_SESSION["Officier"]) { ?>
          <a class="nav-link"
             id="ressources-tab" data-toggle="pill"
             href="#ressources" role="tab"
             aria-controls="ressources" aria-selected="false">Ressources</a>
      <?php } ?> 
        </div>
      </div>
      <div class="col-4 col-sm-8">
        <div class="tab-content" id="vert-tabs-tabContent">
          <div class="tab-pane text-left fade show active"
               id="google_sheet" role="tabpanel"
               aria-labelledby="google_sheet-tab">
            <a href="https://docs.google.com/spreadsheets/d/1iOvl5EESU6KYjCikLnNPXIs7tX_Ax9SvHx4yKNB-n-I/htmlview#">Google Sheet</a>
            <br>Vous retrouverz ici toutes les informations sur les objets, les infos des héros, les marchandises, la kama-zone, etc
          </div>
          <div class="tab-pane fade"
               id="story" role="tabpanel"
               aria-labelledby="story-tab">
            Go to Youtube ^^
          </div>
          <div class="tab-pane fade"
               id="tier_list" role="tabpanel"
               aria-labelledby="tier_list-tab">
            <a href="https://heavenhold.com/tier-list/">Tier list des perso</a>
            <br>Si vous avez un doute sur l'utilité d'un perso, vérifier un petit coup ici
          </div>
          <div class="tab-pane fade"
               id="team_build" role="tabpanel"
               aria-labelledby="team_build-tab">
            <a href="https://gt-team-planner.herokuapp.com/">Construisez vos teams</a>
            <br>Moyen rapide pour savoir si votre team est en symbiose.
          </div>
          <div class="tab-pane fade"
               id="colise" role="tabpanel"
               aria-labelledby="colise-tab">
            <a href="https://thewwworm.github.io/">Placement en colisé</a>
            <br>
            <a href="https://www.youtube.com/watch?v=936HEjWxHhA">Explication des targets et des resets</a>
          </div>
      <?php if ($_SESSION["grade"] <= $_SESSION["Officier"]) { ?>
          <div class="tab-pane fade"
               id="ressources" role="tabpanel"
               aria-labelledby="ressources-tab">
            <a href="https://drive.google.com/drive/folders/1uVv14F9PyBq1MuznpFR3Bf8gfG-jE9dZ" target="_blank">Ressources Fan Art officiel</a>
            <a href="https://guardiantalesguides.com/game/raids/bosses" target="_blank">Icones boss</a>
          </div>
      <?php } ?> 
        </div>
      </div>
    </div>
  </div>
  <!-- /.card -->
</div>
<!-- /.card -->

<div class="card card-primary card-outline collapsed-card">
  <div class="card-header">
    <h3 class="card-title">
      <i class="fas fa-edit"></i>
      Conseils
    </h3>
    <div class="card-tools">
      <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i>
      </button>
    </div>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col-3 col-sm-2">
        <div class="nav flex-column nav-tabs h-100" id="vert-tabs-tab" role="tablist" aria-orientation="vertical">
          <a class="nav-link active"
             id="good_start-tab" data-toggle="pill"
             href="#good_start" role="tab"
             aria-controls="good_start" aria-selected="true">Bien débuter - les crystaux de héros</a>
          <a class="nav-link"
             id="shop-tab" data-toggle="pill"
             href="#shop" role="tab"
             aria-controls="shop" aria-selected="false">La boutique</a>
          <a class="nav-link"
             id="raid_team-tab" data-toggle="pill"
             href="#raid_team" role="tab"
             aria-controls="raid_team" aria-selected="false">Team de raid</a>
        </div>
      </div>
      <div class="col-4 col-sm-8">
        <div class="tab-content" id="vert-tabs-tabContent">
          <div class="tab-pane text-left fade show active"
               id="good_start" role="tabpanel"
               aria-labelledby="good_start-tab">
            <p> Les cristaux de héros 
                <img src="dist/image/resources/hero-crystals.png" alt="hero crystal">
                         <!--class="img-fluid img-thumbnail"-->
                         <!--style="border: 5px solid <?=$charac["color"]["frame"]?>; padding: 0; background-color: #a56e957a"-->
                ne sont vraiment pas simple à obtenir. L'objectif est d'optimiser leurs consomations :
            <ol>
                <li>
                    Choisir un perso 3 étoiles à monter 5* : vous pouvez obtenir des pierres d'évolution pendant les évènements,
                    faire des demande de pierre dans le chat de guilde ou simplement en faisant les donjons d'évolutions.
                    <strong>ils sont à farmer dès que possible</strong>.
                </li>
                <li>
                    Une fois 5*, le personnage gagne une aptitude passive. il faut <strong>MLB</strong> (Max Break Limite ou rupture de limite max) 
                    pour à la fois augmenter le niveau max et débloquer des noeuds d’éveil supplémentaires.<br>
                    Pour l'utilisation des pierres d'éveils, il faut prioriser sur atk en général, ou def et hp pour les tanks.
                </li>
            </ol>
            <br>TODO
            <br>Un des intérêts à faire l'aventure qui farm les fragments, c'est que tu vas aussi en avoir pour les 2. Même si certains sont bons, l'objectif est surtout qu'une fois 5 étoiles, les fragments supplémentaires  se cumulent et tu peux les échanger contre des cristaux. ça te permet d'aller plus vite sur la rupture de limite étant donné le coût (100 cristaux pour le 1er niveau, 110 pour le suivant etc jusqu'à 5 fois).
A terme, tu vas commencer à avoir plusieurs persos bien montés qui vont être utiles autant en histoire qu'en PVP (colisée, arène etc). 

            </p>
          </div>
          <div class="tab-pane fade"
               id="shop" role="tabpanel"
               aria-labelledby="shop-tab">
              <p>
                Pack : des fois il y en a gratuits
              </p>
              <p>
                Ressource : certains prennent 50 cafés pour 100G (jamais fait perso)
              </p>
              <p>
                Croissance héros : 3 pierre d’éveil leg par semaine + boite pierre d’évo *grade haut uniquement* ! Ne pas dépenser de HC (cristaux de héros), réservés pour LB (limit break)
              </p>
              <p>
                Equipement : marteau journalier, équipement 3*. Les chers plus pour du end-game
              </p>
              <p>
                Costume de héros : quand vous aurez assez de perso, c’est bien pour la collection (et c’est beau) mais à prendre par lot
              </p>
              <p>
                Costume d’équipement : pareil, c’est beau
              </p>
              <p>
                Pièce pourpre : tout prendre, si vous êtes patient réserve de café pour des évênements où des lots vous intéressent
              </p>
              <p>
                Kilométrage
                Métal magique : boite verte à 300 et les 5 boites jaunes à 100 (chaque mois)
                Tickets kilo : arme pour compléter un perso 3*
              </p>
              <p>
                Médaille de bataille : collier mino (un de chaque élément, le rêve), bouclier mino n’est pas mal non plus. Broche de panda bien pour la robu. Pierre de vérouillage selon votre choix mais on peut faire sans
              </p>
              <p>
                Eclat de miroir : Bouclier miroir à MLB (viser vitesse regen, surtout pour FP) puis accessoire miroir en fonction de vos teams
              </p>
              <p>
                Bouchon de bouteille : priorité pierre leg et cristaux de héros. Dès que possible tout (41000 par mois) puis bonne source de marteau
              </p>
              <p>
                Heavenhold : avec les SP monter en priorité Tour de volonté et force, prendre Plitvice et Libera (puis arme chaque jour pour 750k). En boutique prendre pierre leg avec les gemmes, et ce qui s’achète avec les SP si possible. Enlever buissons et arbre rapporte pierres d’éveil et gemmes, sans coûter cher
              </p>
          </div>
          <div class="tab-pane fade"
               id="raid_team" role="tabpanel"
               aria-labelledby="raid_team-tab">
              <h4>Comment construire une teaM</h4>
                <ul>
                  <li>Pas de tank (bon j’avoue, tout le monde en a mis au début, surtout pour les fins de raid)
                  </li>
                  <li>Si manque de robu : carte anti-blessure, def, healeur
                  </li>
                  <li>Regarder le Party du perso, si lui ou son arme enlève de la défense ennemie, s'il ajoute chance ou multiplicateur critique...
                  </li>
                  <li>En lead le héros avec le meilleur dps (si chain skill possible)
                  </li>
                  <li>Team mono élément à viser (exception pour perso passe partout comme Nari, mais en adaptant l’arme si possible)
                  </li>
                </ul>
                <strong>Toujours faire des essais pour apprendre le pattern et voir quelle team fais plus de dégâts !</strong>
              <h4>Equipement</h4>
                <ul>
                  <li>Cartes attaques ou chance crit
                  </li>
                  <li>Collier mino (pour lead, avec regen comp au max), Tireur, Accessoire miroir
                  </li>
                  <li>Marchandise coussin Mayreel lv 30 pour le lead, élémentaires pour les autres (ou Statue/Vaisseau si 4*)
                  </li>
                </ul>
              <h4>2* qui dépannent ++</h4>
              <p>
                <ul>
                  <li>Elvira (team distance)
                  </li>
                  <li>Akayuki (team feu)
                  </li>
                  <li>Coco et Gremory (debuff def)
                  </li>
                  <li>Karina, Aoba (pour compléter team sombre ou terre)
                  </li>
                </ul>
              </p>
              <h4>Meilleurs teams du moment :</h4>
              (armes entre parenthèse pas obligatoire mais peuvent apporter du dps selon l'élément du boss)
              <p>
                  *Lumière*
                    <br>- MK Gab Eleanor Tinia (arc du dragon jaune)
                    <br>- MK Gab Eleanor Nari (arme lumière)

              </p>
              <p>
                  *Sombre*
                    <br>- Lilith Beth Yuze plage (arme de Yuze) Arabelle
                    <br>- Lilith Beth Arabelle Lupina

              </p>
              <p>
                  *Basique*
                    <br>- FK Eva Nari Lucy (arme basique)
                    <br>- FK Eva Nari Tinia
                    <br>- FK Eva Nari Gab (arc d’elfe ancien)

              </p>
              <p>
                  *Eau*
                    <br>- Garam Nari (arme Favi) Tinia (arme Catherine) Orca
                    <br>- Garam Kamael (arme Vero) Tinia (arme Catherine) Orca
                    <br>- Garam Kamael (arme Vero) Tinia (arme Catherine) Coco
                    <br>- Garam Lucy (arme Coco) Vero Nari (arme Favi)

              </p>
              <p>
                  *Terre*
                    <br>- Mayreel Tinia Kamael Bari
                    <br>- Mayreel Bari Rue Tinia

              </p>
              <p>
                  *Feu*
                    <br>- Akayuki Scintilla Plitvice Lilith (arme Vishu)
                    <br>- Akayuki Scintilla Plitvice Lynn
                    <br>- Lynn Plitvice Scintilla Akayuki

              </p>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- /.card -->
</div>
<!-- /.card -->

<div class="card card-primary collapsed-card">
  <div class="card-header">
    <h3 class="card-title">F.A.Q.</h3>
    <div class="card-tools">
      <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i>
      </button>
    </div>
    <!-- /.card-tools -->
  </div>
  <!-- /.card-header -->
  <div class="card-body">
    <div class="card card-outline card-primary">
      <div class="card-header">
        <div class="card-tools">
          <button type="button" class="btn btn-tool" data-card-widget="collapse">
            <i class="fas fa-minus"></i>
          </button>
        </div>
        <h3 class="card-title">Problèmes de mise à jour</h3>
        <!-- /.card-tools -->
      </div>
      <!-- /.card-header -->
      <div class="card-body">
          Une ou plusieurs de ces solutions peuvent fonctionner, n'hésitez pas à les tester ou de prendre la plus adaptée à votre situation.
          <dl>
              <dt>PlayStore, accéder directement à la page du jeu</dt>
              <dd>
                  Si en vous connectant au jeu, celui-ci vous demande de mettre à jour le jeu et vous propose un le lien du PlayStore, 
                  ce lien ne vous renvoie vers la bnne page. Vous pouvez essayer d'aller directement sur le PlayStore et de cherche 'Guardian Tales'.
              </dd>
              <dt>PlayStore, vider le cache du jeu</dt>
              <dd>
                  TODO
              </dd>
              <dt>Réinstaller le jeu</dt>
              <dd>
                  Solution plutôt extrème mais a de grande chance de fonctionner. Toutefois cela prend du temps et consomme beaucoup de données.
              </dd>
              <dt>...</dt>
          </dl>
      </div>
      <!-- /.card-body -->
    </div>
    <!-- /.card -->
  </div>
  <!-- /.card-body -->
</div>
<!-- /.card -->


<?php $content = ob_get_clean(); ?>

<?php require('view/template.php'); ?>
