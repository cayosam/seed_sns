<?php
  session_start();//セッション変数を使用するときは必ず必要、必ず一番上に記述

  //DB接続する(５行おまとめ、同じ階層にdbconnectがあるため../が不要)
  require('dbconnect.php');

  //ログインチェック
  if(isset($_SESSION['id'])){
      //$_SESSION['id']が存在している＝ログインしている

  }else{
    //ログインしていない
    //ログイン画面に移動する
    header("Location: login.php");
    exit();
  }


  //-----------------POST送信された時、つぶやきをINSERTで保存-------------------------
//$_POST["tweet"] => "" $_POST　空だと認識されていない
//$_POST["tweet"] => "" $_POST["tweet"]　空だと認識される

  if (isset($_POST) && !empty($_POST)) {

     if ($_POST["tweet"] == ""){
        $error["tweet"] = "blank";
       }

  if (!isset($error)){

//SQL文作成
//tweet=つぶやいた内容
//member_id=ログインした人のid
//reply_tweet_id=-1(→変更予定返信されたものに対してid)
//created=現在日時（now()を使用）
//modified=現在日時（now()を使用）（→なくてもいい。現在日時が自動で入るtimestampという設定になっている）
  $sql = "INSERT INTO `tweets`(`tweet`, `member_id`, `reply_tweet_id`, `created`)
         VALUES (?,?,?,now())";
//SQL文実行
    $data = array($_POST["tweet"],$_SESSION["id"],-1);

    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);

     //自分の画面に移動する（データの再送信を防止する）
    header("Location; index.php");

  }
}



  //---------------------表示用のデータ取得---------------------

if(isset($_SESSION['id'])){

  try{
    //ログインしている人の情報を取得
    $sql = "SELECT *
            FROM `members`
            WHERE `member_id`=".$_SESSION["id"]; 

    $stmt = $dbh->prepare($sql);
    $stmt->execute();

         //1件数取得
    $login_menber = $stmt->fetch(PDO::FETCH_ASSOC);

    //一覧用のデータを取得
    //テーブル結合
    $sql = "SELECT `tweets`.*,`members`.`nick_name`,`members`.`picture_path`
            FROM `tweets`
            INNER JOIN `members` 
            ON `tweets`.`member_id` = `members`.`member_id`
            ORDER BY `tweets`.`modified` DESC";
            //ORDER BY `tweets`.`modified` DESCは最新順の並べ替え

    $stmt = $dbh->prepare($sql);
    $stmt->execute();


    //一覧表示用の配列を用意
    $tweet_list = array();
    //複数行データを取得するためループ
    while(1){
        $one_tweet = $stmt->fetch(PDO::FETCH_ASSOC);

        if($one_tweet == false){
          break;
        }else{
          //データが取得できている
          $tweet_list[] = $one_tweet;
        }
      }
    }catch(Exception $e){

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
                <li><a href="logout.php">ログアウト</a></li>
              </ul>
          </div>
          <!-- /.navbar-collapse -->
      </div>
      <!-- /.container-fluid -->
  </nav>

  <div class="container">
    <div class="row">
      <div class="col-md-4 content-margin-top">
        <legend>ようこそ<?php echo $login_menber["nick_name"];?>さん！</legend>
        <form method="post" action="" class="form-horizontal" role="form">
            <!-- つぶやき -->
            <div class="form-group">
              <label class="col-sm-4 control-label">つぶやき</label>
              <div class="col-sm-8">
                <textarea name="tweet" cols="50" rows="5" class="form-control" placeholder="例：Hello World!"></textarea>
                <?php if (isset($error) && ($error["tweet"] == "blank")){
                   ?>
                   <p class="error">何かつぶやいてください。</p>
                <?php  } ?> 
              </div>
            </div>
          <ul class="paging">
            <input type="submit" class="btn btn-info" value="つぶやく">
                &nbsp;&nbsp;&nbsp;&nbsp;
                <li><a href="index.html" class="btn btn-default">前</a></li>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <li><a href="index.html" class="btn btn-default">次</a></li>
          </ul>
        </form>
      </div>

      <div class="col-md-8 content-margin-top">
      <?php foreach ($tweet_list as $one_tweet) { ?>

      <!-- 繰り返すタグが描かれる場所 -->
        <div class="msg">
          <img src="picture_path/<?php echo $one_tweet["picture_path"]; ?>" width="48" height="48">
          <p>
           <?php echo $one_tweet["tweet"];?>
            <span class="name">(<?php echo $one_tweet["nick_name"];?>)
            </span>
            [<a href="#">Re</a>]
          </p>
          <p class="day">
            <a href="view.php?tweet_id=<?php echo $one_tweet["tweet_id"]; ?>">

              <!-- echo $one_tweet["modified"];だと秒まで表示される -->
              <?php 
              $modefy_date = $one_tweet["modified"];
              //strtotime 文字型のデータを日時型に変換できる
              //(Y年m月d日 と記述することも可能)(H24時間表記、h12時間表記)
              $modefy_date = date("Y-m-d H:i",strtotime($modefy_date));
              echo $modefy_date;
              ?>
            </a>
            [<a href="#" style="color: #00994C;">編集</a>]
            [<a href="#" style="color: #F33;">削除</a>]
          </p>
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
