<?php

/**
 * クラスを定義したファイルを自動的に読み込むためのオートロードという仕組みを管理するクラス。
 *
 * クラスを呼び出した際にそのクラスがPHP上に読み込まれていない場合、
 * PHPは指定されたオートロード関数を呼び出し、そのクラスの定義を試みる。
 * それでももしクラスがみつからないと、Fattal error: Class '~' not foundが発生する。
 */
class ClassLoader
{
  protected $dirs;

  /**
   * PHPにオートローダクラスを登録する
   */
  public function register()
  {
    spl_autoload_register(array($this, 'loadClass'));
  }

  /**
  * 検索対象にするときに使う
  */
  public function registerDir($dir)
  {
    $this->dirs[] = $dir;
  }

  /**
   * オートロード時にPHPから自動的に呼び出され、
   * クラスファイルの読み込みを行うメソッド
   *
   * `__qutoload()`ではなく`spl_autoload_register()`を使うことで、2つの利点がある。
   * * オートロードに使われる処理をコールバック関数の形式で指定できる。
   * * 複数のコールバック関数をオートロードキューに指定することができる。
   */  
  public function loadClass($class)
  {
    foreach ($this->dirs as $dir) {
      $file = $dir . '/' . $class . '.php';
      if (is_readable($file)) {
        require $file;
        return;
      }
    }
  }
}
