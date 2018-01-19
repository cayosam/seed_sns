<?php
session_start();
//ログインチェックを行う関数
//関数とは、一定の処理をまとめて名前をつけておいているプログラムの塊
//何度も同じ処理を実行したい場合、便利である。
//プログラミング言語が時線に用意している関数：組み込み関数
//自分で定義して作成るす関数：自作関数
//login_check;関数名。呼び出すときに指定するもの

function login_check(){

    //ログインチェック
  if(isset($_SESSION['id'])){
      //$_SESSION['id']が存在している＝ログインしている

  }else{
    //ログインしていない
    //ログイン画面に移動
    header("Location: login.php");
    exit();
  }
}

function delete_tweet(){
  //論理削除用のUPDATE文(DB接続が必要)
   require('dbconnect.php');

     $delete_tweet_id = $_GET['tweet_id'];



   $sql = "UPDATE `tweets`
           SET `delete_flag` = '1'
           WHERE `tweets`.`tweet_id` = ".$delete_tweet_id;

  // SQL文実行
    $stmt = $dbh->prepare($sql);
    $stmt->execute();

  //一覧に戻る
     header("Location: index.php");
     exit();
}

 ?>