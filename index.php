<?php
  // session_start();//セッション変数を使用するときは必ず必要、必ず一番上に記述

  require('function.php');

  //ログインチェック(function.phpから呼び出し)
  login_check();

  //DB接続する(５行おまとめ、同じ階層にdbconnectがあるため../が不要)
  require('dbconnect.php');


//  //ログインチェック
//  if(isset($_SESSION['id'])){
//      //$_SESSION['id']が存在している＝ログインしている
//
//  }else{
//    //ログインしていない
//    //ログイン画面に移動する
//    header("Location: login.php");
//    exit();
//  }

//-----------------POST送信された時、つぶやきをINSERTで保存-------------------------
//$_POST["tweet"] => "" $_POST　空だと認識されていない
//$_POST["tweet"] => "" $_POST["tweet"]　空だと認識される

  if (isset($_POST) && !empty($_POST)) {

    // var_dump("postされてる");

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
    header("Location: index.php");

  }
}
// ------ ページング処理（表示用データのSQL文を使用のため、その上に記述する） -----
  $page = "";

  //パラメータが存在していたらページ番号を代入
  if(isset($_GET["page"])){
     $page = $_GET["page"];
  }else{
    //存在しないときはページ番号を１とする
    $page = 1;
  }

      //1以下のイレギュラーな数字が入ってきた時、ページ番号を強制的に1とする
      //max カンマ区切りで羅列された数字の中から最大の数字を取得
      $page = max($page,1);

      //iページ分の表示件数
      $page_row = 5;

     //データの件数から最大件数を計算する
      $sql = "SELECT COUNT(*) AS `cnt`
              FROM `tweets`
              WHERE `delete_flag` = 0";

      $page_stmt = $dbh->prepare($sql);
      $page_stmt ->execute();

      $record_count = $page_stmt->fetch(PDO::FETCH_ASSOC);

      //ceil 小数点の切り上げを行う
      $all_page_number = ceil($record_count['cnt'] / $page_row);

      //パラメータのページ番号が最大ページを超えて入れば、強制的に最後のページとする
      //min カンマ区切りの数字の羅列の中から、最小の数字を取得する
      $page = min($page,$all_page_number);
      //表示するデータの取得開始場所
      $start = ($page -1) * $page_row;
// -------------------------------------------------------------



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
    //論理削除に対応 delete_flag = 0 のものだけを取得(0は表示させ、1は論理削除で非表示にさせる)
    //ORDER BY `tweets`.`modified` DESCは最新順の並べ替え
    //LIMIT 0,5→ ０番目からデータを取り出す、５件のデータを取り出す→0-4番目のデータが表示される
    $sql = "SELECT `tweets`.*,`members`.`nick_name`,`members`.`picture_path`
            FROM `tweets`
            INNER JOIN `members` 
            ON `tweets`.`member_id` = `members`.`member_id`
            WHERE `delete_flag`= 0
            ORDER BY `tweets`.`modified` DESC
            LIMIT ".$start.",".$page_row;
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
          //Like数を求めるSQL文(上部で$sqlを使用しているので、名前を変更して$like_〇〇にしている)
          $like_sql = "SELECT COUNT(*) AS `like_count`
                       FROM `likes`
                       WHERE `tweet_id`=".$one_tweet["tweet_id"];

          //SQL文実行
          $like_stmt = $dbh->prepare($like_sql);
          $like_stmt->execute();

          //$like_munberにlike数がいくつかカウントしている
          $like_member = $like_stmt->fetch(PDO::FETCH_ASSOC);

          //$one_tweetの中身
          //$one_tweet["tweet"]つぶやき
          //$one_tweet["member_id"]つぶやいた人のid
          //$one_tweet["nick_name"]つぶやいた人のニックネーム
          //$one_tweet["picture_path"]つぶやいた人のプロフィール画像
          //$one_tweet["mofrfied"]つぶやいた日時
          //上記の内容に加え、$one_tweet["like_count"]を加えてまとめている。
          //1行文おデータに新しいキーを用意して、like数を代入
          $one_tweet["like_count"] = $like_member["like_count"];


          //ログインしている人がlikeしているかどうかの情報を取得する
          $login_like_sql = "SELECT COUNT(*) AS `like_count`
                             FROM `likes`
                             WHERE `tweet_id`=".$one_tweet["tweet_id"]."
                             AND `member_id`=".$_SESSION["id"];

          //SQL文実行(今ログインしている人がlikeしているかどうかのデータ)
          $login_like_stmt = $dbh->prepare($login_like_sql);
          $login_like_stmt->execute();

          //フェッチして取得
          $login_like_number = $login_like_stmt->fetch(PDO::FETCH_ASSOC);

          $one_tweet["login_like_flag"] = $login_like_number["like_count"];
          //ログインしているか、していないか、などの二択の場合はflagを使う
          //0の時likeボタン　1の時unlikeのボタン

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
                <?php if($page == 1){ ?>
                <li>前</li>
                <?php }else{ ?>
                <li><a href="index.php?page=<?php echo $page - 1; ?>" class="btn btn-default">前</a></li>
                <?php } ?>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <?php if($page == $all_page_number){ ?>
                <li>次</li>
                <?php }else{ ?>
                <li><a href="index.php?page=<?php echo $page + 1; ?>" class="btn btn-default">次</a></li>
                <li><?php echo $page; ?> / <?php echo $all_page_number; ?>Page</li>
              <?php } ?>
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
            [<a href="reply.php?tweet_id=<?php echo $one_tweet["tweet_id"]; ?>">Re</a>]
              <?php if($one_tweet["login_like_flag"] == 0){?>
             <a href="like.php?like_tweet_id=<?php echo $one_tweet["tweet_id"]; ?>"><i class="fa fa-thumbs-o-up" aria-hidden="true"></i>Like</a> 
            <?php }else{ ?>
            <a href="like.php?unlike_tweet_id=<?php echo $one_tweet["tweet_id"]; ?>"><i class="fa fa-thumbs-o-up" aria-hidden="true"></i>UnLike</a> 
            <?php } ?>
             <?php if($one_tweet["like_count"] > 0){ echo $one_tweet["like_count"];} ?>
          </p>
          <p class="day">
            <!-- tweet_id何番かで判断する -->
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
            <?php if($_SESSION["id"] == $one_tweet["member_id"]){ ?>
            [<a href="#" style="color: #00994C;">編集</a>]
            [<a onclick="return confirm('削除します、よろしいですか？');" href="delete.php?tweet_id=<?php echo $one_tweet["tweet_id"]; ?>" style="color: #F33;">削除</a>]
            <?php } ?>
            <?php if($one_tweet["reply_tweet_id"] > 0){ ?>
            [<a href="view.php?tweet_id=<?php echo $one_tweet["reply_tweet_id"]; ?>" style="color: #a9a9a9;">返信元のメッセージを表示</a>]
            <?php } ?>

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
