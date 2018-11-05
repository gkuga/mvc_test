<?php $this->setLayoutVar('title', 'Top') ?>

<h2>Wellcome</h2>

<?php //エラーがあった場合の処理 ?>
<?php if (isset($errors) && count($errors) > 0): ?>
  <?php echo $this->render('errors', array('errors' => $errors)); ?>
<?php endif; ?>

<?php if($session->isAuthenticated()): ?>
<form action="/tweet/post" method="post">
  <p><input type="text" name="body" value="<?php echo $this->escape($body); ?>"></p>
  <p><input type="submit" value="ツイート"></p>
  <input type="hidden" name="_token" value="<?php echo $this->escape($_token); ?>" />
</form>
<?php endif; ?>

<div id="tweets">
<?php foreach ($tweets as $tweet): ?>
  <?php echo $this->render('tweet/tweet', array('tweet' => $tweet)); ?>
<?php endforeach; ?>
</div>
