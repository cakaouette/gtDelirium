<?php ob_start(); ?>
<div class="col-md-12">
<div class="card card-info">
  <div class="card-header">
    <h3 class="card-title">Connexion</h3>
  </div>
  <!-- /.card-header -->
  <!-- form start -->
  <form class="form-horizontal" action="?page=connect" method="post">
    <?php if (!$_isFirstConnection) { ?>
    <div class="card-body">
      <!-- Login -->
      <div class="form-group row">
        <label for="loginFormId" class="col-sm-2 col-form-label">Login</label>
        <div class="col-sm-10">
          <input type="text"
                 class="form-control" 
                 name="loginForm"
                 id="loginFormId"
                 value="<?=$_savedLogin?>">
        </div>
      </div>
      
      <!-- Password -->
      <div class="form-group row">
        <label for="passwdFormId" class="col-sm-2 col-form-label">Mot de Passe</label>
        <div class="col-sm-10">
          <input type="password"
                 class="form-control" 
                 name="passwdForm"
                 id="passwdFormId">
        </div>
      </div>
    </div>
      
    <!-- /.card-body -->
    <div class="card-footer">
      <button type="submit" class="btn btn-info">Connexion</button>
      <div class="float-right">
        <p>Première connexion ? <a href="?page=connect&firstConnect">Cliquer ici</a></p>
      </div>
<!--      <button type="submit" class="btn btn-default float-right">Cancel</button>-->
    </div>
    <!-- /.card-footer -->
    
    <?php } else { ?>
    <div class="card-body">
        <p>Renseigner votre pseudo In Game et choisisser un login et mot de passe.<br>
            <strong>Un administrateur s'occupera du reste</strong></p>
        <p><?=$_errorPasswd?></p>
    <!-- Pseudo InGame -->
      <div class="form-group">
        <label for="pseudoFormId">Pseudo In Game</label>
        <input type="text"
               class="form-control form-control-border border-width-2" 
               name="pseudoForm"
               id="pseudoFormId"
               placeholder="pseudo"
               value="<?=$_savedPseudo?>">
      </div>
      
    <!-- Login -->
      <div class="form-group">
          <label for="loginFormId">Login</label><small>: Il peut être différent de votre pseudo</small>
        <input type="text"
               class="form-control form-control-border border-width-2" 
               name="loginForm"
               id="loginFormId"
               placeholder="login"
               value="<?=$_savedLogin?>">
      </div>
      
    <!-- Password -->
      <div class="form-group">
        <label for="passwdFormId">Mot de Passe</label><small>: taille >= 10, et doit contenir au moins une lettre minuscule, majuscule et un chiffre</small>
        <input type="password"
               class="form-control form-control-border border-width-2" 
               name="passwdForm"
               placeholder="password"
               id="passwdFormId">
      </div>
    </div>
      
    <!-- /.card-body -->
    <div class="card-footer">
      <button type="submit" class="btn btn-info">Créer</button>
<!--      <button type="submit" class="btn btn-default float-right">Cancel</button>-->
    </div>
    <?php } ?>
    <!-- /.card-footer -->
  </form>
</div>
</div>
    
<?php $content = ob_get_clean(); ?>

<?php require('view/template.php'); ?>
