<?php

class AccountController extends Controller
{
  protected $auth_actions = array('logout');

  /**
   * アカウント登録画面
   */
  public function signupAction()
  {
    return $this->render(array(
      'user_name' => '',
      'password'  => '',
      '_token'    => $this->generateCsrfToken('account/signup'),
    ));
  }

  /**
   * アカウント登録処理
   */
  public function registerAction()
  {
    // POSTじゃなかったらはじく
    if (!$this->request->isPost()) {
      $this->forward404();
    }

    // CSRF対策
    $token = $this->request->getPost('_token');
    if (!$this->checkCsrfToken('account/signup', $token)) {
      return $this->redirect('/account/signup');
    }

    $user_name = $this->request->getPost('user_name');
    $password = $this->request->getPost('password');

    // バリデーションはUserモデルに記述。
    $errors = $this->db_manager->get('User')->register($user_name, $password);
    // エラーがない場合はアカウントが登録されているので、ログイン状態にする。
    if (count($errors) === 0) {
      $this->session->setAuthenticated(true);
      $user = $this->db_manager->get('User')->fetchByUserName($user_name);
      $this->session->set('user', $user);
      return $this->redirect('/');
    }

    // エラーがある場合はアカウント登録画面へ
    return $this->render(array(
      'user_name' => $user_name,
      'password'  => $password,
      'errors'    => $errors,
      '_token'    => $this->generateCsrfToken('account/signup'),
    ), 'signup');
  }

  /**
   * ログイン画面の表示
   */
  public function loginAction()
  {
    // すでにログインしていたらトップページへ
    if ($this->session->isAuthenticated()) {
      return $this->redirect('/');
    }

    return $this->render(array(
      'user_name' => '',
      'password'  => '',
      '_token'    => $this->generateCsrfToken('account/login'),
    ));
  }

  /**
   * ログアウト処理をする
   */
  public function logoutAction()
  {
    $this->session->clear();
    $this->session->setAuthenticated(false);

    return $this->redirect('/');
  }

  /**
   * ログイン処理をする
   */
  public function AuthenticateAction()
  {
 
    // POSTじゃなかったらはじく
    if (!$this->request->isPost()) {
      $this->forward404();
    }

    // すでにログインしていたらトップページへ
    if ($this->session->isAuthenticated()) {
      return $this->redirect('/');
    }

    // CSRF対策
    $token = $this->request->getPost('_token');
    if (!$this->checkCsrfToken('account/login', $token)) {
      return $this->redirect('/account/login');
    }

    $user_name = $this->request->getPost('user_name');
    $password = $this->request->getPost('password');

    // バリデーションはUserモデルに記述。
    $errors = $this->db_manager->get('User')->authenticate($user_name, $password);
    // エラーがない場合はアカウントが登録されているので、ログイン状態にする。
    if (count($errors) === 0) {
      $this->session->setAuthenticated(true);
      $user = $this->db_manager->get('User')->fetchByUserName($user_name);
      $this->session->set('user', $user);
      return $this->redirect('/');
    }

    // エラーがある場合はアカウント登録画面へ
    return $this->render(array(
      'user_name' => $user_name,
      'password'  => $password,
      'errors'    => $errors,
      '_token'    => $this->generateCsrfToken('account/login'),
    ), 'login');
  }


}
