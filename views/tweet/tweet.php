<div class="tweet">
  <a href="<?php echo $base_url; ?>/tweet/show/<?php echo $this->escape($tweet['user_name']); ?>">
  <?php echo $this->escape($tweet['user_name']); ?>
  </a>
  <?php echo $this->escape($tweet['body']); ?>
  <a href="<?php echo $base_url; ?>/tweet/show/<?php echo $this->escape($tweet['user_name']);
  ?>/<?php echo $this->escape($tweet['id']); ?>">
  <?php echo $this->escape($tweet['created_at']); ?>
  </a>
</div>
 
