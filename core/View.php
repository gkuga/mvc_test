<?php

class View
{
  /**
   * viewディレクトリの絶対パスが入る
   */
  protected $base_dir;
  /**
   * テンプレートファイルからデフォルトで呼べる変数を設定する
   */
  protected $defaults;
  protected $layout_variables = array();

  public function __construct($base_dir, $defaults = array())
  {
    $this->base_dir = $base_dir;
    $this->defaults = $defaults;
  }

  /**
   * レイアウトファイル側に値を渡したい場合、ビューファイル内からこのメソッドを
   * 呼び出せば、例えばページタイトルなどを渡すことができる。
   */
  public function setLayoutVar($name, $value)
  {
    $this->layout_variables[$name] = $value;
  }

  /**
   * extract変数で連想配列を展開するので、変数名衝突回避のため、
   * ここで定義する変数の先頭にアンダースコアを付けている。
   */
  public function render($_path, $_variables = array(), $_layout = false)
  {
    // コントローラから呼ばれると、$_pathは"controller名/アクション名となるが、
    // テンプレートファイルから呼ばれるとcontroller名が入らず、自由にできる。
    $_file = $this->base_dir . '/' . $_path . '.php';

    extract(array_merge($this->defaults, $_variables));

    //アウトプットバッファリングを開始
    ob_start();
    //バッファの自動フラッシュを無効にする。
    ob_implicit_flush(0);

    //テンプレートファイルを呼び出し
    require $_file;

    //バッファの取り出しとバッファリング終了
    $content = ob_get_clean();

    if ($_layout) {
      $content = $this->render($_layout,
        array_merge($this->layout_variables, array(
          '_content' => $content,
        )
      ));
    }

    return $content;
  }

  public function escape($string)
  {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
  }

}
