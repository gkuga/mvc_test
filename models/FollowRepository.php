<?php

class FollowRepository extends DbRepository
{
  /**
   * フォロー処理をする
   *
   * フォロー前のチェックも行う。
   * フォローしていなかったらフォローをして、
   * フォロー中ならばリムーブをする。
   */
  public function follow($user_id, $following_id)
  {
    if ($user_id === $following_id) {
      return false;
    }

    $res = $this->getDeleteFlag($user_id, $following_id);
    if (empty($res)) {
      $this->insert($user_id, $following_id);
    } else if (intval($res['delete_flag'])) {
      $this->updateDeleteFlag($user_id, $following_id, 0); 
    } else {
      $this->updateDeleteFlag($user_id, $following_id, 1); 
    }
    error_log($res['delete_flag']);
    return true;
  }    

  /**
   * delete_flagを更新する
   */
  private function updateDeleteFlag($user_id, $following_id, $delete_flag)
  {
    $now = new DateTime();

    $sql = "
       update follow set delete_flag = :delete_flag, updated_at = :updated_at
       where user_id = :user_id and following_id = :following_id
    ";

    $stmt = $this->execute($sql, array(
      ':user_id'      => $user_id,
      ':following_id' => $following_id,
      ':updated_at'   => $now->format('Y-m-d H:i:s'),
      ':delete_flag'  => $delete_flag,
    ));
  }

  private function insert($user_id, $following_id)
  {
    $now = new DateTime();

    $sql = "
      insert into follow(user_id, following_id, delete_flag, created_at, updated_at)
      values(:user_id, :following_id, :delete_flag, :created_at, :updated_at)
    ";

    $stmt = $this->execute($sql, array(
      ':user_id'      => $user_id,
      ':following_id' => $following_id,
      ':delete_flag'  => 0,
      ':created_at' => $now->format('Y-m-d H:i:s'),
      ':updated_at' => $now->format('Y-m-d H:i:s'),
    ));
  }

  /**
   * 指定したフォローのデリートフラグを調べる。
   * レコードがない場合は空の配列が返る。
   */
  public function getDeleteFlag($user_id, $following_id)
  {
    $sql = "
      select delete_flag from follow
      where user_id = :user_id and following_id = :following_id
    ";

    $res = $this->fetch($sql, array(
      ':user_id'      => $user_id,
      ':following_id' => $following_id,
    ));

    return $res;
  }

  /**
   * フォローの判定
   */
  public function isFollowing($user_id, $following_id)
  {

    if ($user_id === $following_id) {
      return null;
    }

    $res = $this->getDeleteFlag($user_id, $following_id);
    if (empty($res) || intval($res['delete_flag'])) {
      return false;
    }

    return true;
  }

  public function fetchAllFolowingsByUserID($user_id)
  {
    $sql = "
      select u.* from user u join following f on f.flollowing_id = u.id
      where f.user_id = :user_id and f.delete_flag = 0
    ";

    return $this->fetchAll($sql, array(':user_id' => $user_id));
  }

}


