<?php

/**
 * ユーザがアクセスしてきたURLをRequestクラスから受け取り、
 * どのコントローラを呼び出すかを決定する。
 * これにより物理的なディレクトリ構造に縛られないURLの制御を可能にする。
 *
 * ```
 * array(
 *   '/user/edit' => arrray('controller' => 'user', 'action' => 'edit'),
 * );
 * ```
 *
 * のように連想配列のキーにはPATH_INFO部分を、値にはコントローラとアクションの連想配列とする。
 * コロンで始まる文字列を指定すると、その部分を動的なパラメータとして扱えるようにする。
 *
 * ```
 * array(
 *   '/user/:id' => array('controller' => 'user', 'action' => 'show'),
 * );
 * ```
 */
class Router
{
  /**
   * ルーティング定義配列が格納される。
   * キーは'/account/signup/のようなパスで、値であるルーティング定義も配列で、
   * array('controller'=>'controller name', 'action'=>'action name')のような形
   */
  protected $routes;

  public function __construct($definitions)
  {
    $this->routes = $this->compileRoutes($definitions);
  }

  /**
   * 渡されたルーティング定義配列のそれぞれのキーに含まれる動的パラメータを
   * 正規表現でキャプチャできる形式に変換する。
   *
   * /account/:actionなどは/account/(?P<action>[^/]+)に変換される。
   */
  public function compileRoutes($definitions)
  {
    $routes = array();

    foreach ($definitions as $url => $params) {
      $tokens = explode('/', ltrim($url, '/'));
      foreach ($tokens as $i => $token) {
        // コロンで始まる場合は正規表現の形式にする
        if (strpos($token, ':') === 0) {
          $name = substr($token, 1);
          $token = '(?P<' . $name . '>[^/]+)';
           
        }
        $tokens[$i] = $token;
      }
      //コロンを含まない場合は$urlと$patternが等しくなる。
      $pattern = '/' . implode('/', $tokens);
      $routes[$pattern] = $params;
    }

    return $routes;
  }

  /**
   * マッチングを行う。
   *
   * preg_match()ではパターンの一部の正規表現に中にサブパターンを含めることができる。
   * /account/(?P<action>[^/]+)が登録されていると/account/signupがマッチング。
   * サブパターンにマッチングするのはsignupの部分で、actionをキーに格納される。
   * @param string $path_info PATH_INFOを受け取る
   */
  public function resolve($path_info)
  {
    if ('/' !== substr($path_info, 0, 1)) {
      $path_info = '/' . $path_info;
    }

    foreach ($this->routes as $pattern => $params) {
      // パターンは下では#^patern$#となり、完全一致を指定している。
      if (preg_match('#^' . $pattern . '$#', $path_info, $matches)) {
        $params = array_merge($params, $matches);

        return $params;
      }
    }

    return false;
  }

  /**
   * テスト用
   *
   * @deprecated
   */
  public function getRoutes() { return $this->routes; }

} 
