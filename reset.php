<?php
//DB接続
require('dbconnect.php');

$error = array();

echo "<br>";
echo "<br>";
echo "<br>";
echo "<br>";
echo "<br>";
var_dump($error);

//POST送信されていたら
 if(isset($_POST) && !empty($_POST)){
  //URLの不正チェック
  //GET送信されているcodeと、現在DBに保存されているpasswordが一致しているか確認
  //membersテーブルから入力されたメールと合致するデータを取得
  $sql = "SELECT * FROM `members`
          WHERE `email` = ?";

  $data = array($_POST["email"]);

  //SQL実行
  $stmt = $dbh->prepare($sql);
  $stmt->execute($data);

  //一行取得
  $member = $stmt->fetch(PDO::FETCH_ASSOC);

  if($_GET["code"] == $member["password"]){
    //チェックOK
    // 新しいパスワードでリセット
    // 文字列を暗号化してUODATE
    $update_sql = "UPDATE `members` SET `password` = ? WHERE `email`=?";
    $update_data = array(sha1($_POST["password"]),$member["email"]);
    //SQL文実行
    $update_stmt = $dbh->prepare($update_sql);
    $update_stmt->execute($update_data);

    header("Location: thanksreset.php");
  }else{
    //不正
    $error["url"] = "invalid";
  }

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
              <a class="navbar-brand" href="index.html"><span class="strong-title"><i class="fa fa-twitter-square"></i> Seed SNS</span></a>
          </div>
          <!-- Collect the nav links, forms, and other content for toggling -->
          <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
              <ul class="nav navbar-nav navbar-right">
              </ul>
          </div>
          <!-- /.navbar-collapse -->
      </div>
      <!-- /.container-fluid -->
  </nav>

  <div class="container">
    <div class="row">
      <div class="col-md-6 col-md-offset-3 content-margin-top">
        <legend>受け取ったメールアドレスと新しいパスワードを入力して下さい</legend>
        <form method="post" action="" class="form-horizontal" role="form">
          <!-- メールアドレス -->
          <div class="form-group">
            <label class="col-sm-4 control-label">メールアドレス</label>
            <div class="col-sm-8">
              <input type="email" name="email" class="form-control" placeholder="例： seed@nex.com">
            </div>
            <?php if(isset($error["url"]) && !empty($error["url"])){?>
            <p class="error">URLリンクが不正です。再度確認をお願いいたします。</p>
            <?php } ?>
          </div>
          <!-- パスワード -->
          <div class="form-group">
            <label class="col-sm-4 control-label">パスワード</label>
            <div class="col-sm-8">
              <input type="password" name="password" class="form-control" placeholder="">
            </div>
          </div>
          <input type="submit" class="btn btn-default" value="リセット">
        </form>
      </div>
    </div>
  </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="assets/js/jquery-3.1.1.js"></script>
    <script src="assets/js/jquery-migrate-1.4.1.js"></script>
    <script src="assets/js/bootstrap.js"></script>
  </body>
</html>
