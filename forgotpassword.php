<?php
//DB接続
require("dbconnect.php");
//POST送信されていたら
if(isset($_POST) && !empty($_POST)){
  //membersテーブルから入力されたメールと合致するデータを取得
  $sql = "SELECT * FROM `members`
          WHERE `email` = ?";

  $data = array($_POST["email"]);

  //SQL実行
  $stmt = $dbh->prepare($sql);
  $stmt->execute($data);

  //一行取得
  $member = $stmt->fetch(PDO::FETCH_ASSOC);

  // パスワードを上書きするための文字列を作成(email + modeified)
  $str_update_pw = $member["email"] + $member["modified"];
  // 文字列を暗号化してUPDATE
  $update_sql = "UPDATE `members`
                 SET `password` = ?
                 WHERE `email`";
  $update_data = array(sha1($str_update_pw),$member["email"]);
  //SQL文実行
  $update_stmt = $dbh->prepare($update_sql);
  $update_stmt->execute($update_data);
  // 暗号化した文字列を使用してURLを作成
  $reset_url = "http://localhost/seed_sns/reset.php/code=".sha1($str_update_pw);
  // メール文章作成
  $mail_body = "パスワードを設定するため、下記リンクを押してください。
  ";
  $mail_body .=$reset_url;

  // メールの送信先
  $email = $member["email"];
  // タイトル設定
  $title = "SeedSNSからパスワード忘れのお知らせ"
  // メール送信
  $mail_head = "From:seedkun@seedsns.com";
  $mail_body = html_entity_decode($email_body,ENT_QUOTES,"UTF_8");//文字化け対策

  mb_language("Japanese");
  mb_internal_encoding("UTF_8")
  mb_send_mail($email,$title,$mail_body,$mail_head);

  //Thanksページに移動
  header("Location: thanksforgotpass.php");
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
        <legend>パスワードをリセットするための情報を送信します。</legend>
        <form method="post" action="" class="form-horizontal" role="form">
          <!-- メールアドレス -->
          <div class="form-group">
            <label class="col-sm-4 control-label">メールアドレス</label>
            <div class="col-sm-8">
              <input type="email" name="email" class="form-control" placeholder="例： seed@nex.com">
            </div>
          </div>
          
          <input type="submit" class="btn btn-default" value="送信">
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
