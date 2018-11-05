<?php $this->setLayoutVar('title', $tweet['user_name']) ?>

<?php echo $this->render('tweet/tweet', array('tweet' => $tweet)); ?>
