<?php

/**
 * ツイートに関する処理をするコントローラ
 */
class TweetController extends Controller
{
  protected $auth_actions = array('post','follow');

  /**
   * トップページ。
   * ログインしていたらユーザのホーム。
   * ツイートもここでできる。
   */
  public function indexAction()
  {
    $user = $this->session->get('user');

    if ($user) {
      $tweets = $this->db_manager->get('Tweet')
        ->fetchAllPersonalTweetsByUserId($user['id']);
    } else {
      $tweets = $this->db_manager->get('Tweet')->fetchAllTweets();
    }

    return $this->render(array(
      'user'   => $user,
      'tweets' => $tweets,
      'body'   => '',
      '_token' => $this->generateCsrfToken('tweet/index'),
    ));
  }

  /**
   * 全体のタイムライン
   */
  public function allAction()
  {
    $user = $this->session->get('user');
    $tweets = $this->db_manager->get('Tweet')->fetchAllTweets();

    return $this->render(array(
      'user'   => $user,
      'tweets' => $tweets,
    ));
  }


  /**
   * ツイート処理をする
   */
  public function postAction()
  { 
    // POSTじゃなかったらはじく
    if (!$this->request->isPost()) {
      $this->forward404();
    }

    // CSRF対策
    $token = $this->request->getPost('_token');
    if (!$this->checkCsrfToken('tweet/index', $token)) {
      return $this->redirect('/tweet/index');
    }

    $body = $this->request->getPost('body');
    $user = $this->session->get('user');
    $tweets = $this->db_manager->get('Tweet')
      ->fetchAllPersonalTweetsByUserId($user['id']);

    // バリデーションはモデルに記述。
    $errors = $this->db_manager->get('Tweet')->post($user['id'], $body);
    // エラーがない場合はTopページにリダイレクト。
    if (count($errors) === 0) {
      return $this->redirect('/');
    }

    // エラーがある場合はTopページを再描画
    return $this->render(array(
      'user'      => $user,
      'errors'    => $errors,
      'body'      => $body,
      'tweets'    => $tweets,
      '_token'    => $this->generateCsrfToken('tweet/index'),
    ), 'index');
  }

  /**
   * 各ユーザのタイムライン
   */
  public function userAction($params)
  {
    $user = $this->db_manager->get('User')
      ->fetchByUserName($params['user_name']);
    if (!$user) {
      $this->forward404();
    }

    $tweets = $this->db_manager->get('Tweet')
      ->fetchAllByUserId($user['id']);

    $following = null;
    if ($this->session->isAuthenticated()) {
      $me = $this->session->get('user');
      if ($me['id'] !== $user['id']) {
        $following = $this->db_manager->get('Follow')
          ->isFollowing($me['id'], $user['id']);
      }
    }

    return $this->render(array(
      'user'   => $user,
      'tweets' => $tweets,
      'following' => $following,
      '_token' => $this->generateCsrfToken('tweet/follow'),
    ));
  }

  /**
   * 個別のツイートを表示
   */
  public function showAction($params)
  {
    $tweet = $this->db_manager->get('Tweet')
      ->fetchByIdAndUserName($params['id'], $params['user_name']);

    if (!$tweet) {
      $this->forward404();
    }

    return $this->render(array('tweet' => $tweet));
  }

  /**
   * フォロー処理をする
   */
  public function followAction()
  {
    // POSTじゃなかったらはじく
    if (!$this->request->isPost()) {
      $this->forward404();
    }

    $following_name = $this->request->getPost('following_name');
    if (!$following_name) {
      $this->forward404();
    }

    // CSRF対策
    $token = $this->request->getPost('_token');
    if (!$this->checkCsrfToken('tweet/follow', $token)) {
      return $this->redirect('/tweet/show/' . $following_name);
    }

    $follow_user = $this->db_manager->get('User')
      ->fetchByUserName($following_name);
    if (!$follow_user) {
      $this->forward404();
    }

    $user = $this->session->get('user');

    $this->db_manager->get('Follow')->follow($user['id'], $follow_user['id']);

    return $this->redirect('/tweet/show/' . $following_name);
  }

} 
