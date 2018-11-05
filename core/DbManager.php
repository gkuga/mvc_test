<?php

/**
 * データベースへの接続情報や次に説明するDbRepositoryを管理するクラス。
 */
class DbManager
{
  /**
   * 接続情報であるPDOクラスのインスタンスの配列
   * 今回１つのデータベースしか使わないであろうので配列にする意味はそれほどない
   */
  protected $connections = array();
  protected $repository_connection_map = array();
  protected $repositories = array();
  /**
   * 接続を行う
   *
   * @param string $name $connectionsプロパティのキーになる値
   * @param array $params PDOクラスのコンストラクタに渡す接続に必要な情報
   */
  public function connect($name, $params)
  {
    $params = array_merge(array(
      'dsn'      => null,
      'user'     => '',
      'password' => '',
      'options'  => array(),
    ), $params);

    $con = new PDO(
      $params['dsn'],
      $params['user'],
      $params['password'],
      $params['options']
    );

    /**
     * PDO::ATTR_ERRMODE属性をPDO::ERRMODE_EXCEPTIONにすると、
     * PDOの内部でエラーが起きた場合に例外を発生させるようにする
     */
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $this->connections[$name] = $con;
  }

  public function getConnection($name = null)
  {
    if (is_null($name)) {
      /**
       * current()は配列の内部ポインタが示す値を取得する.
       * 指定がなければここでは最初のPDOクラスのインスタンスを返す
       */
      return current($this->connections);
    }

    return $this->connections[$name];
  }

  /**
   * 1つのアプリが複数のデータベースを使う場合、
   * データベースとDbRepositoryクラス（モデル）の対応を登録する。
   */
  public function setRepositoryConnectionMap($repository_name, $name)
  {
    $this->repository_connection_map[$repository_name] = $name;
  }

  public function getConnectionForRepository($repository_name)
  {
    if (isset($this->repository_connection_map[$repository_name])) {
      $name = $this->repository_connection_map[$repository_name];
      $con = $this->getConnection($name);
    } else {
      $con = $this->getConnection();
    }

    return $con;
  }

  /**
   * 指定されたRepository名が$repositoriesに入ってない場合生成をし、
   * 入っている場合はインスタンスを返す。
   * 例えばDbRepositoryクラスを継承させて、UserRepositoryを作成し、
   * get("User")などとする。1度インスタンスを生成したらそれをずっと使う。
   */
  public function get($repository_name)
  {
    if (!isset($this->repositories[$repository_name])) {
      $repository_class = $repository_name . 'Repository';
      /**
       * 例えばsetRepositoryConnectionMap("User", "DB1");みたいなことを
       * していなければ、$this->connectionsの最初のPDOクラスのインスタンスが
       * 返ってくる。このアプリではたぶんPDOクラスのインスタンスは１つだけ。
       */
      $con = $this->getConnectionForRepository($repository_name);

      $repository = new $repository_class($con);

      $this->repositories[$repository_name] = $repository;
    }

    return $this->repositories[$repository_name];
  }

  /**
   * PDOのインスタンスを破棄すると接続を閉じるようになっている。
   */
  public function __destruct()
  {
    foreach ($this->repositories as $repository) {
      unset($repository);
    }

    foreach ($this->connections as $con) {
      unset($con);
    }
  }

}
