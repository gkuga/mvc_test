<?php $this->setLayoutVar('title', 'Login') ?>

<?php //エラーがあった場合の処理 ?>
<?php if (isset($errors) && count($errors) > 0): ?>
  <?php echo $this->render('errors', array('errors' => $errors)); ?>
<?php endif; ?>

<form action="/account/authenticate" method="post">
  <p><label>ユーザID:<input type="text" name="user_name" value="<?php echo $this->escape($user_name); ?>"></label></p>
  <p><label>パスワード:<input type="password" name="password" value="<?php echo $this->escape($password); ?>"></label></p>
  <p><input type="submit" value="ログイン"></p>
  <input type="hidden" name="_token" value="<?php echo $this->escape($_token); ?>" />
</form>

