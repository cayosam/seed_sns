<?php
require ("function .php");

//ログインチェック
login_check();

//つぶやきを削除
delete_tweet();

//削除したいtweet_id
//$delete_tweet_id = $_GET['tweet_id'];
//
//
////論理削除用のUPDATE文(DB接続が必要)
//  require('dbconnect.php');
//
//  $sql = "UPDATE `tweets` 
//          SET `delete_flag` = '1'
//          WHERE `tweets`.`tweet_id` = ".$delete_tweet_id;
//
//// SQL文実行
//  $stmt = $dbh->prepare($sql);
//  $stmt->execute();
//
////一覧に戻る
//  header("Location: index.php");
//  exit();
?>