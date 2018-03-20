<?php
  session_start();
  // DBの接続
  require('dbconnect.php');
  // ログインしている人のプロフィール情報をmembersテーブルから取得
  $sql = "SELECT * FROM `members` WHERE `member_id`=".$_SESSION["id"];
  $stmt = $dbh->prepare($sql);
  $stmt->execute();

  $profile_member = $stmt->fetch(PDO::FETCH_ASSOC);

  //一覧データを取得
  $sql = "SELECT * FROM `members` INNER JOIN `follows` ON `members`.`member_id` = `follows`.`member_id` WHERE `follows`.`follower_id` = ".$_SESSION["id"]." ORDER BY `follows`.`created`DESC";

  $stmt = $dbh->prepare($sql);
  $stmt->execute();

  // 一覧表示用の配列を用意
  $tweet_list = array();
  //　複数行のデータを取得するためループ
  while (1) {
    $one_tweet = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($one_tweet == false){
      break;
    }else{
      //following_flagを用意して、自分をフォローしていたら１、していなかったら０を代入
      //自分をフォローしているか確認する、また、その逆（フォローしてくれている人を探す）
      //COUNT() テーブル内のレコードの件数を取得するSQL文の関数。ヒットした件数を取得。これがない場合は、件数ではなくデータ全部取得となる
      //as `cnt`　は列名を cnt としている
      $fl_flag_sql = "SELECT COUNT(*) as `cnt` FROM `follows` WHERE `member_id`=".$_SESSION["id"]." AND `follower_id`=".$one_tweet["member_id"];
      $fl_stmt = $dbh->prepare($fl_flag_sql);
      $fl_stmt->execute();
      $fl_flag = $fl_stmt->fetch(PDO::FETCH_ASSOC);

       //下記は一覧データのループ表示が必要なため記述
      $one_tweet["following_flag"] = $fl_flag["cnt"];
      //データが取得できていたら$one_tweetに代入
      $tweet_list[] = $one_tweet;
    }
  }
//フォロー処理
// profile.php?follow_id=7 というリンクが推された＝フォローボタンが押された
  if (isset($_GET["follow_id"])){
    //follow情報を記録するSQL文を作成
    //member_idは自分の事、follower_idはフォローしてくれている人
    $sql = "INSERT INTO `follows` (`member_id`, `follower_id`) VALUES (?, ?);";
    $data = array($_SESSION["id"],$_GET["follow_id"]);
    $fl_stmt = $dbh->prepare($sql);
    $fl_stmt->execute($data);

     //フォローを押す前の状態に戻す（再読み込み時に、再度データの送信するのを防ぐ）
    header("Location: follow.php");
  }
  //フォロー解除
  if(isset($_GET["unfollow_id"])){
    //フォロー情報を削除するSQL文を作成
    //member_idは自分の事、follower_idはフォロー解除したい人
    $sql = "DELETE FROM`follows` WHERE `member_id`=? AND `follower_id`=?";
    $data = array($_SESSION["id"],$_GET["unfollow_id"]);
    $unfl_stmt = $dbh->prepare($sql);
    $unfl_stmt->execute($data);

    //フォロー解除を押す前の状態に戻す
    header("Location: follow.php");

  }


?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>SeedSNS</title>

    <!-- Bootstrap -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="assets/css/form.css" rel="stylesheet">
    <link href="assets/css/timeline.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">

  </head>
  <body>
  <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
          <!-- Brand and toggle get grouped for better mobile display -->
          <div class="navbar-header page-scroll">
              <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                  <span class="sr-only">Toggle navigation</span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
              </button>
              <a class="navbar-brand" href="index.php"><span class="strong-title"><i class="fa fa-twitter-square"></i> Seed SNS</span></a>
          </div>
          <!-- Collect the nav links, forms, and other content for toggling -->
          <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
              <ul class="nav navbar-nav navbar-right">
                <li><a href="logout.php">ログアウト</a></li>
              </ul>
          </div>
          <!-- /.navbar-collapse -->
      </div>
      <!-- /.container-fluid -->
  </nav>

  <div class="container">
    <div class="row">
      <div class="col-md-3 content-margin-top">
        <img src="picture_path/<?php echo $profile_member["picture_path"]; ?>" width="250" height="250">
        <h3><?php echo $profile_member["nick_name"]; ?></h3>
        <?php if($_SESSION["id"] != $profile_member["member_id"]){ ?>
        <a href="profile.php?member_id=<?php echo $profile_member["member_id"]; ?>&follow_id=<?php echo $profile_member["member_id"]; ?>">
        <button class="btn btn-block btn-default">フォロー</button></a>
        <?php } ?>
        <br>
        <a href="index.php">&laquo;&nbsp;一覧へ戻る</a>
      </div>
      <div class="col-md-9 content-margin-top">
        <div class="msg_header">
        <a href="#">Followers<span class="badge badge-pill badge-default"><?php
         // count関数：配列にいくつデータが存在しているか数えてくれる関数
         echo count($tweet_list); ?></span></a>
        </div>
        <?php foreach ($tweet_list as $one_tweet) { ?>  
        <div class="msg">
          <img src="picture_path/<?php echo $one_tweet["picture_path"]; ?>" width="48" height="48">
          <p> <span class="name"> <?php echo $one_tweet["nick_name"]; ?> </span></p>

          <?php if($one_tweet["following_flag"] == 0) { ?>
          <a href="follow.php?follow_id=<?php echo $one_tweet["member_id"]; ?>">
        <button class="btn btn-default">フォロー</button></a>
        <?php }else{ ?>
          <a href="follow.php?unfollow_id=<?php echo $one_tweet["member_id"]; ?>">
        <button class="btn btn-default">フォロー解除</button></a>
        <?php } ?>
        </div>
        <?php } ?>
      </div>
    </div>
  </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="assets/js/jquery-3.1.1.js"></script>
    <script src="assets/js/jquery-migrate-1.4.1.js"></script>
    <script src="assets/js/bootstrap.js"></script>
  </body>
</html>
