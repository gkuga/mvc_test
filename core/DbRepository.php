<?php

/**
 * 実際にデータベースへのアクセスを伴う処理を管理するクラス。
 * 実際にはデータベース上のテーブルごとにDbRepositoryクラスの子クラスを作成する。
 * MVCにおけるモデルに相当する。
 */
abstract class DbRepository
{
  /** 
   * DbManagerクラスから渡されたPDOクラスのインスタンス
   * このPDOクラスのインスタンスに対してSQL文を実行する。
   */
  protected $con;

  public function __construct($con)
  {
    $this->setConnection($con);
  }

  public function setConnection($con)
  {
    $this->con = $con;
  }

  /**
   * プリペアドステートメントを実行し、PDOStatementクラスを取得する
   */
  public function execute($sql, $params = array())
  {
    /**
     * prepare()を実行するとPDOStatementクラスのインスタンスが返る
     */
    $stmt = $this->con->prepare($sql);
    /**
     * 実際にクエリがデータベースに発行される。
     * exute()の引数にプレースホルダに入る値を指定する。
     */
    $stmt->execute($params);

    return $stmt;
  }

  public function fetch($sql, $params = array())
  {
    //fetch()の引数は返り値の形式を指定する。
    //PDO::FETCH_ASSOCはカラム名で添え字を付けた配列を返す。
    return $this->execute($sql, $params)->fetch(PDO::FETCH_ASSOC);
  }

  public function fetchAll($sql, $params = array())
  {
    return $this->execute($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
  }

}
