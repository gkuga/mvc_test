<?php $this->setLayoutVar('title', 'AllUsers') ?>

<h2>All Users</h2>

<div id="tweets">
<?php foreach ($tweets as $tweet): ?>
  <?php echo $this->render('tweet/tweet', array('tweet' => $tweet)); ?>
<?php endforeach; ?>
</div>
