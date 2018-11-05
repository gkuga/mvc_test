<?php $this->setLayoutVar('title', 'User') ?>

<h2><?php echo 'this is ' . $user['user_name'] . '\'s timeline' ?></h2>

<?php if (!is_null($following)): ?>
  <form action="<?php echo $base_url; ?>/tweet/follow" method="post">
    <?php if ($following): ?>
      <input type="submit" value="リムーブ" />
    <?php else: ?>
      <input type="submit" value="フォロー" />
    <?php endif; ?>
    <input type="hidden" name="following_name" value="<?php echo $this->escape($user['user_name']); ?>" />
    <input type="hidden" name="_token" value="<?php echo $this->escape($_token); ?>" />
  </form>
<?php endif; ?>

<div id="tweets">
<?php foreach ($tweets as $tweet): ?>
  <?php echo $this->render('tweet/tweet', array('tweet' => $tweet)); ?>
<?php endforeach; ?>
</div>


