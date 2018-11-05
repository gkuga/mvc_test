<?php

/**
 * アプリケーション全体を表し、フレームワークの中心となるクラス。
 *
 * Requestクラス、Sessionクラス、Responseクラス、Routerクラスのオブジェクトの管理をする。
 * コントローラの実行などアプリケーションの全体の流れを管理する。
 * アプリケーションごとにこのクラスを継承したクラスを定義し、アプリケーション固有の設定
 * （データベース接続情報、URLとコントローラの対応など）の定義を行う。
 */
abstract class Application
{
  protected $debug = false;
  protected $request;
  protected $response;
  protected $session;
  protected $db_manager;

  public function __construct($debug = false)
  {
    $this->setDebugMode($debug);
    $this->initialize();
    $this->configure();
  }

  /**
   * デバッグモードに応じてエラー表示処理を変更する。
   */
  protected function setDebugMode($debug)
  {
    if ($debug) {
      $this->debug = true;
      ini_set('display_errors', 1);
      error_reporting(-1);
    } else {
      $this->debug = false;
      ini_set('display_errors', 0);
    }
  }

  /**
   * クラスの初期化。Routerクラスはインスタンスを作成する前にルーティング定義配列を渡す。
   */
  protected function initialize()
  {
    $this->request = new Request();
    $this->response = new Response();
    $this->session = new Session();
    $this->db_manager = new DbManager();
    $this->router = new Router($this->registerRoutes());
  }

  /**
   * initialize()メソッドの直後に呼び出される。個別のアプリケーションで様々な設定をする。
   */
  protected function configure() {}

  abstract public function getRootDir();
  abstract protected function registerRoutes();

  public function isDebugMode()      { return $this->debug; }
  public function getRequest()       { return $this->request; }
  public function getResponse()      { return $this->response; }
  public function getSession()       { return $this->session; }
  public function getDbManager()     { return $this->db_manager; }
  public function getControllerDir() { return $this->getRootDir() . '/controllers'; }
  public function getViewDir()       { return $this->getRootDir() . '/views'; }
  public function getModelDir()      { return $this->getRootDir() . '/models'; }
  public function getWebDir()        { return $this->getRootDir() . '/web'; }

  /**
   * Routerからコントローラを特定しレスポンスの送信を行うまでを管理する。
   */
  public function run()
  {
    try {
      $params = $this->router->resolve($this->request->getPathInfo());
      if ($params === false) {
        throw new HttpNotFoundException('No route found for ' . $this->request->getPathInfo());
      }

      $controller = $params['controller'];
      $action = $params['action'];

      $this->runAction($controller, $action, $params);

    } catch (HttpNotFoundException $e) { 
      $this->render404Page($e);
    } catch (UnauthorizedActionException $e) {
      list($controller, $action) = $this->login_action;
      $this->runAction($controller, $action);
    }

    $this->response->send();
  }

  /**
   * 実際にアクションを実行する。
   */
  public function runAction($controller_name, $action, $params = array())
  {
    // ucfirstは最初のアルファベットを大文字にする。 
    $controller_class = ucfirst($controller_name) . 'Controller';

    $controller = $this->findController($controller_class);
    if ($controller == false) {
      throw new HttpNotFoundException($controller_class . ' controller is not found.');
    }

    $content = $controller->run($action, $params);

    $this->response->setContent($content);
  }

  /**
   * runAction()メソッドの中でコントローラクラスを生成する。
   */
  public function findController($controller_class)
  {
    // class_exists()はクラスが定義済みかどうかを確認する。
    if (!class_exists($controller_class)) {
      $controller_file = $this->getControllerDir() . '/' . $controller_class . '.php';
      // is_readable()はファイルが存在し、読み込み可能であるかどうかを確認する。 
      if (!is_readable($controller_file)) {
        return false;
      } else {
        require_once $controller_file;

        if (!class_exists($controller_class)) {
          return false;
        }
      }
    }

    return new $controller_class($this);
  }

  protected function render404Page($e)
  {
    $this->response->setStatusCode(404, 'Not Found');
    $message = $this->isDebugMode() ? $e->getMessage() : 'Page not found.';
    $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

    $this->response->setContent(<<<EOF
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title>404</title>
</head>
<body>
    {$message}
</body>
</html>
EOF
    );
  }

}
