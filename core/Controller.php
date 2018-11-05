<?php

/**
 * モデルやビューの制御を行うコントローラ。
 */
abstract class Controller
{
  protected $controller_name;
  protected $action_name;
  protected $application;
  protected $request;
  protected $response;
  protected $session;
  protected $db_manager;
  /**
   * ログインが必要なアクションを指定する。
   * @var Array $auth_actions
   */
  protected $auth_actions = array(); 

  public function __construct($application)
  {
    // UserControllerとして継承されていたら以下の処理でcontroller_nameはuserとなる。
    $this->controller_name = strtolower(substr(get_class($this), 0, -10));

    $this->application = $application;
    $this->request     = $application->getRequest();
    $this->response    = $application->getResponse();
    $this->session     = $application->getSession();
    $this->db_manager  = $application->getDbManager();
  }

  /**
   * Applicationクラスから呼び出され、実際にアクションの実行を行うメソッド。
   */
  public function run($action, $params = array())
  {
    $this->action_name = $action;

    $action_method = $action . 'Action';
    if (!method_exists($this, $action_method)) {
      $this->forward404();
    }

    /**
     * アクションを呼び出す前に、ログインが必要なアクションかどうかのチェックをする。
    */
    if ($this->needsAuthentication($action) && !$this->session->isAuthenticated())
    {
      throw new UnauthorizedActionException();
    }

    $content = $this->$action_method($params);

    return $content;
  }

  /**
   * ビュークラスにおけるビューファイルの読み込み処理をラッピングした処理。
   */
  protected function render($variables = array(), $template = null, $layout = 'layout')
  {
    // ビューでデフォルトで扱える変数の設定
    $defaults = array(
      'request'  => $this->request,
      'base_url' => $this->request->getBaseUrl(),
      'session'  => $this->session,
    );

    $view = new View($this->application->getViewDir(), $defaults);

    //ビューファイルは指定しなければアクション名と同じとして読み込まれる。
    //Viewクラスのrenderで最終的にアクション名.phpとなって読み込まれる。
    if (is_null($template)) {
      $template = $this->action_name;
    }

    $path = $this->controller_name . '/' . $template;

    return $view->render($path, $variables, $layout);
  }

  /**
   * 404エラー画面へリダイレクトする。
   * Applicationクラスのrunにて例外がキャッチされる。
   */
  protected function forward404()
  {
    throw new HttpNotFoundException('Forwarded 404 page from '
      . $this->controller_name . '/' . $this->action_name);
  }

  /**
   * 任意のURLへリダイレクトする。
   * ちなみに、本来は303のステータスコードを返すのが正しい。
   */
  protected function redirect($url)
  {
    if (!preg_match('#https?://#', $url)) {
      $protocol = $this->request->isSsl() ? 'https://' : 'http://';
      $host     = $this->request->getHost();
      $base_url = $this->request->getBaseUrl();

      $url = $protocol . $host . $base_url . $url;
    }

    $this->response->setStatusCode(302, 'Found');
    $this->response->setHttpHeader('Location', $url);
  }

  /**
   * トークンを生成し、セッションに格納した上でトークンを返す。
   *
   * 複数画面を開いた場合に備えて、トークンは10個保持できるようにし、
   * 既に10個保持している場合は古いものから消していく。
   */
  protected function generateCsrfToken($form_name)
  {
    $key = 'csrf_tokens/' . $form_name;
    $tokens = $this->session->get($key, array());
    if (count($tokens) >= 10) {
      array_shift($tokens);
    }

    $token = sha1($form_name . session_id() . microtime());
    $tokens[] = $token;

    $this->session->set($key, $tokens);

    return $token;
  }

  /**
   * リクエストされてきたトークンとセッションに格納されたトークンを
   * 比較した結果を返し、同時にセッションからトークンを削除する。
   */
  protected function checkCsrfToken($form_name, $token)
  {
    $key = 'csrf_tokens/' . $form_name;
    $tokens = $this->session->get($key, array());

    if (($pos = array_search($token, $tokens, true)) !== false) {
      unset($tokens[$pos]);
      $this->session->set($key, $tokens);

      return true;
    }

    return false;
  }

  /**
   * ログインが必要かどうかの判定を行うメソッド。
   * アクション名を指定し、$auth_actionsプロパティ値を元に指定したアクションが
   * ログインが必須かどうかの判定をする。
   */
  protected function needsAuthentication($action)
  {
    if ($this->auth_actions === true
        || (is_array($this->auth_actions) && in_array($action, $this->auth_actions))
    ) { return true; }

    return false;
  }

}
