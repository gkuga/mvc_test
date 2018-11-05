<?php

class UserRepository extends DbRepository
{
  /**
   * アカウント登録処理。
   *
   * バリデーションも行い、エラー文章の配列を返す。
   */
  public function register($user_name, $password)
  {
    $errors = $this->validateUserName($user_name);
    if(count($errors) === 0 && !$this->isUniqueUserName($user_name)) {
      $errors[] = 'ユーザIDは既に使用されています';
    }

    $errors = array_merge($errors, $this->validatePassword($password));

    if(count($errors) === 0) {
      $this->insert($user_name, $password);
    }

    return $errors;
  }

  /**
   * ログイン判定
   */
  public function authenticate($user_name, $password)
  {
    $errors = array_merge($this->validateUserName($user_name),
      $this->validatePassword($password));

    if (count($errors) === 0) {
      $user = $this->fetchByUserName($user_name);
      if (!$user || !$this->checkPassword($password, $user['password'])) {
        $errors[] = "ユーザIDまたはパスワードが不正です。";
      }
    }

    return $errors;
  }

  /**
   * ユーザIDのバリデーション処理
   */
  public function validateUserName($user_name)
  {
    $errors = array();

    if (!strlen($user_name)) {
      $errors[] = 'ユーザIDを入力してください。';
    } else if (!preg_match('/^\w{4,20}$/', $user_name)) {
      $errors[] = 'ユーザIDは半角英数字およびアンダースコアを4~20文字以内で入力してください';
    }
    return $errors;
  }

  /**
   * パスワードのバリデーション処理
   */
  public function validatePassword($password)
  {
    $errors = array();

    if (!strlen($password)) {
      $errors[] = 'パスワードを入力してください。';
    } else if (!preg_match('/^[\w!@\?]{8,16}$/', $password)) {
      $errors[] = 'パスワードは半角英数字および記号(_!@?)を8~16文字以内で入力してください';
    }

    return $errors;
  }

  private function insert($user_name, $password)
  {
    $password = $this->hashPassword($password);
    $now = new DateTime();

    $sql = "
      insert into user(user_name, password, created_at, updated_at)
      values(:user_name, :password, :created_at, :updated_at)
    ";

    $stmt = $this->execute($sql, array(
      ':user_name'  => $user_name,
      ':password'   => $password,
      ':created_at' => $now->format('Y-m-d H:i:s'),
      ':updated_at' => $now->format('Y-m-d H:i:s'),
    ));
  }

  public function fetchByUserName($user_name)
  {
    $sql = "select * from user where user_name = :user_name";

    return $this->fetch($sql, array(':user_name' => $user_name));
  }

  public function isUniqueUserName($user_name)
  {
    $sql = "select count(id) as count from user where user_name = :user_name";

    $row = $this->fetch($sql, array(':user_name' => $user_name));
    if ($row['count'] === '0') {
      return true;
    }

    return false;
  }

  /**
   * Blowfishでパスワードをハッシュする。
   */
  public function hashPassword($password, $cost=7)
  {
    // Blowfishのソルトに使用できる文字種
    $chars = array_merge(range('a', 'z'), range('A', 'Z'), array('.', '/'));

    // ソルトを生成（上記文字種からなるランダムな22文字）
    $salt = '';
    for ($i = 0; $i < 22; $i++) {
        $salt .= $chars[mt_rand(0, count($chars) - 1)];
    };

    // コストの前処理
    $costInt = intval($cost);
    if ($costInt < 4) {
        $costInt = 4;
    } elseif ($costInt > 31) {
        $costInt = 31;
    }

    // 指定されたコストで Blowfish ハッシュを得る
    return crypt($password, '$2y$' . sprintf('%02d', $costInt) . '$' . $salt);
  }

  /**
   * hashPassword()で生成したパスワードをチェックする。
   */
  public function checkPassword($raw, $hashed)
  {
    $hashinfo = substr($hashed, 0, 29);

    // パスワードを検証する
    if (crypt($raw, $hashinfo) === $hashed) {
      return true;
    }

    return false;
  }

}
