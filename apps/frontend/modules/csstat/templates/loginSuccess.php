<div class="cside c_full">
  <div class="padding">
    <div id="content" class="content_full">
      <div style="margin: 40px">
        <?php include_partial('global/flashes') ?>
        <form action="<?php echo url_for('csstat/login') ?>" method="post">
          <table class="cs_login">
              <tr><th colspan="2"><h2>Вход в систему управления</h2></th></tr>
              <tr><td>Введите логин:</td><td><?php echo $form['login'] ?></td></tr>
              <tr><td>Введите пароль:</td><td><?php echo $form['pass'] ?></td></tr>
              <tr><td></td><td><?php echo $form['ref'] ?><input type="submit" value="Вход" /></td></tr>
          </table>
        </form>
      </div>
    </div>
  </div>
</div>