<?php

/**
 * セッションを管理するクラス。
 * $_SESSION変数のラッパークラスに相当する。
 */
class Session
{
  protected static $sessionStarted = false;
  protected static $sessionIdRegenerated = false;
  const AUTHENTICATED_FLAG_NAME = '_authenticated';

  public function __construct()
  {
    if (!self::$sessionStarted) {
      /**
       * セッションの作成や、受け取ったセッションIDを元にセッションを復元などをする。
       * 多重に行われないように$sessionStartedでチェックしている。
       */
      session_start();

      self::$sessionStarted = true;
    }
  }

  public function set($name, $value)
  {
    $_SESSION[$name] = $value;
  }

  /**
   * $default は $nameのキーが見つからなかった場合のreturn値となる。
   */
  public function get($name, $default = null)
  {
    if (isset($_SESSION[$name])) {
      return $_SESSION[$name];
    }

    return $default;
  }

  public function remove($name)
  {
    unset($_SESSION[$name]);
  }

  public function clear()
  {
    $_SESSION = array();
  }

  /**
   * セッションIDを新しく発行する。
   * これも1度のリクエストで複数呼び出されないよう静的プロパティでチェックしている。
   * @param bool デフォルトでtrueで、古いセッションを削除する。
   */
  public function regenerate($destroy = true)
  {
    if (!self::$sessionIdRegenerated) {
      session_regenerate_id($destroy);

      self::$sessionIdRegenerated = true;
    }
  }

  public function setAuthenticated($bool)
  {
    /**
     * _authenticatedというキーで、ログインしているかどうかのフラグを格納して、
     * ログイン状態の判定に使う。
     */
    $this->set('_authenticated', (bool)$bool);

    // セッション固定攻撃対策
    $this->regenerate();
  }

  public function isAuthenticated()
  {
    return $this->get('_authenticated', false);
  }

}



