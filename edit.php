<?php
//session_start();
  //個別ページの表示
  require('function.php');

  //ログインチェック(function.phpから呼び出し)
  login_check();

//DB接続
  require('dbconnect.php');
//--------------POST送信された時、つぶやきをINSERTで保存-----------------
//$_POST["tweet"] => "" $_POST　空だと認識されていない
//$_POST["tweet"] => "" $_POST["tweet"]　空だと認識される

  if (isset($_POST) && !empty($_POST)) {

    // var_dump("postされてる");

      if ($_POST["tweet"] == ""){
         $error["tweet"] = "blank";
      }

      if (!isset($error)){

        //SQL文作成
        //Update文
        $sql = "UPDATE `tweets` SET `tweet` = ? WHERE `tweets`.`tweet_id` = ?;";

        //SQL文実行
        //上記SQL文内の?の数と下記の$dataの格納されている数は同じになる
        $data = array($_POST["tweet"],$_GET["tweet_id"]);
        $stmt = $dbh->prepare($sql);
        $stmt->execute($data);

         //一覧ページ画面に移動する
        header("Location: index.php");

        }
  }


    //$_GET["tweet_id"] の中に、表示したいつぶやきのtweet_idが格納
    $sql = "SELECT `tweets`.*,`members`.`nick_name`,`members`.`picture_path` FROM `tweets` INNER JOIN `members` ON `tweets`.`member_id` = `members`.`member_id` WHERE`tweets`.`tweet_id`=".$_GET["tweet_id"];
    $stmt = $dbh->prepare($sql);
    $stmt->execute();

    //送信されたtweet_idを使用してSQL文でデータベースからデータを一件取得
    //個別ページに表示するデータを取得
    $tweet_pick = $stmt->fetch(PDO::FETCH_ASSOC);

//    $dbh = null;
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
      <div class="col-md-6 col-md-offset-3 content-margin-top">
        <h4>つぶやき編集</h4>

        <div class="msg">
          <form method="post" action="" class="form-horizontal" role="form">
            <!-- つぶやき -->
            <div class="form-group">
              <label class="col-sm-4 control-label">つぶやき</label>
              <div class="col-sm-8">
                <!--textareaで改行すると画面でもそのまま改行されてしまう  -->
                <textarea name="tweet" cols="50" rows="5" class="form-control" placeholder="例：Hello World!"><?php echo $tweet_pick["tweet"]; ?></textarea>
                <?php if (isset($error) && ($error["tweet"] == "blank")){
                   ?>
                   <p class="error">何かつぶやいてください。</p>
                <?php  } ?> 
              </div>
            </div>
          <ul class="paging">
            <input type="submit" class="btn btn-info" value="変更を保存">
        </form>
        </div>
        <a href="index.php">&laquo;&nbsp;一覧へ戻る</a>
      </div>
    </div>
  </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="assets/js/jquery-3.1.1.js"></script>
    <script src="assets/js/jquery-migrate-1.4.1.js"></script>
    <script src="assets/js/bootstrap.js"></script>
  </body>
</html>
