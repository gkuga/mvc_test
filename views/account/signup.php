<?php $this->setLayoutVar('title', 'Signup') ?>

<?php //エラーがあった場合の処理 ?>
<?php if (isset($errors) && count($errors) > 0): ?>
  <?php echo $this->render('errors', array('errors' => $errors)); ?>
<?php endif; ?>

<form action="/account/register" method="post">
  <p><label>ユーザID:<input type="text" name="user_name" value="<?php echo $user_name ?>"></label></p>
  <p><label>パスワード:<input type="password" name="password" value="<?php echo $password ?>"></label></p>
  <p><input type="submit" value="登録"></p>
  <input type="hidden" name="_token" value="<?php echo $this->escape($_token); ?>" />
</form>

