<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>
<?php if (isset($title)): ?>
  <?php echo $this->escape($title) . "-"; ?>
<?php endif ?>
Awitter
</title>
<link rel="stylesheet" href="/css/style.css">
</head>

<body class="" dir="ltr">

  <div id="doc">

    <header id="header-container">
      <div class="header">
        <div class="header-top">
          <div class="header-img" style="background-image: url(/images/header.png);"></div>
          <div class="header-buttons">
            <h1 class="reset"><a href="/" class="icon">Awitter</a></h1>
            <?php if($session->isAuthenticated()): ?>
            <a class="button" href="<?php echo $base_url; ?>/account/logout">ログアウト</a>
            <?php else: ?>
            <a class="button" href="<?php echo $base_url; ?>/account/signup">新規登録</a>
            <button class="login button" type="submit">ログイン</button>
            <?php endif; ?>
          </div>
          <div class="header-content">
            <h2 class="header-title">「いま」起きていることを見つけよう。</h2>
              <p class="header-blurb">好きなものについてのコミュニティや会話、ひらめきを見つけよう。</p>
          </div>
        </div>
        <nav id="nav">
          <ul>
            <li class="nav-item">
              <div class="nav-itemInner">
                <a class="nav-itemName" href="<?php echo $base_url; ?>/">ホーム</a>
              </div>
            </li>
            <li class="nav-item">
              <div class="nav-itemInner">
                <a class="nav-itemName" href="<?php echo $base_url; ?>/tweet/all">全体のタイムライン</a>
              </div>
            </li>
            <?php if($session->isAuthenticated()): ?>
            <li class="nav-item">
              <div class="nav-itemInner">
                <a class="nav-itemName" href="<?php echo $base_url; ?>/account/logout">ログアウト</a>
              </div>
            </li>
            <?php else: ?>
            <li class="nav-item">
              <div class="nav-itemInner">
                <a class="nav-itemName" href="<?php echo $base_url; ?>/account/login">ログイン</a>
              </div>
            </li>
            <li class="nav-item">
              <div class="nav-itemInner">
                <a class="nav-itemName" href="<?php echo $base_url; ?>/account/signup">アカウント登録</a>
              </div>
            </li>
            <?php endif; ?>
          </ul>
        </nav>
      </div>
    </header>

    <div id="main" class="content" id="timeline">
      <?php echo $_content; ?>
    </div>

    <div class="footer">
      <ul class="footer-list">
        <li class="footer-item"><a href="/about">Awitterについて</a></li>
        <li class="footer-item footer-copyright">© 2016 Awitter</li>
      </ul>
    </div>

  </div>

</body>
</html>
