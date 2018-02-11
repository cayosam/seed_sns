<?php
  session_start();

  //DB接続
  require('dbconnect.php');

  //GET送信された、member_idを使って、プロフィール情報をmembersテーブルから取得
    $sql = "SELECT *
            FROM `members`
            WHERE `member_id`=".$_GET["member_id"]; 

    $stmt = $dbh->prepare($sql);
    $stmt->execute();

         //1件数取得
    $profile_member = $stmt->fetch(PDO::FETCH_ASSOC);

      //一覧データを取得
    $sql = "SELECT `tweets`.*,`members`.`nick_name`,`members`.`picture_path`
            FROM `tweets`
            INNER JOIN `members` 
            ON `tweets`.`member_id` = `members`.`member_id`
            WHERE `delete_flag`= 0
            AND `tweets`.`member_id`=".$_GET["member_id"]."
            ORDER BY `tweets`.`modified` DESC ";

        $nstmt = $dbh->prepare($sql);
        $nstmt->execute();
       
       // 一覧表示用の配列を用意
        $tweet_list = array();
       // 複数行データを取得するためループ
        while(1){
          $one_tweet = $nstmt->fetch(PDO::FETCH_ASSOC);

          if($one_tweet == false){
            break;
          }else{
            // データ取得できている
            $tweet_list[] = $one_tweet;
          }
        }

      //following_flagを用意して、自分をフォローしていたら１、していなかったら０を取得
      $fl_flag_sql = "SELECT COUNT(*) as `cnt`
                      FROM `follows`
                      WHERE `member_id`=".$_SESSION["id"]."
                      AND `follower_id`=".$_GET["member_id"];
      $fl_stmt = $dbh->prepare($fl_flag_sql);
      $fl_stmt->execute();
      $fl_flag = $fl_stmt->fetch(PDO::FETCH_ASSOC);


    //フォロー処理
    //profile.php?follow_id=5 というリンクが押された=フォローボタンが押された
    //下記のfollow_id　はフォローされようとしている人のid
    if(isset($_GET["follow_id"])){
      //follow情報を記録するSQL文を作成
        $sql = "INSERT INTO `follows` (`member_id`, `follower_id`)
                VALUES (?, ?);";

        //上記のSQL文で?を使用している数と同じ数だけ配列に入れる
        //今ログインしている人のログインidとフォローされようとしている人のid
        $data = array($_SESSION["id"],$_GET["follow_id"]);

        $fl_stmt = $dbh->prepare($sql);
        $fl_stmt->execute($data);

     //自分の画面に移動する（データの再送信を防止する）
       header("Location: profile.php?member_id".$_GET["member_id"]);

    }
     //フォロー解除
     if(isset($_GET["unfollow_id"])){
       //フォロー情報を削除するSQL文を作成
       //member_idは自分の事、follower_idはフォロー解除したい人
       $sql = "DELETE FROM`follows`
               WHERE `member_id`=?
               AND `follower_id`=?";

       $data = array($_SESSION["id"],$_GET["unfollow_id"]);
       $unfl_stmt = $dbh->prepare($sql);
       $unfl_stmt->execute($data);

       //フォロー解除を押す前の状態に戻す
       header("Location: profile.php?member_id".$_GET["member_id"]);

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
          //1行文をデータに新しいキーを用意して、like数を代入
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
      //Followingの数
      $following_sql = "SELECT COUNT(*) as `cnt`
                        FROM `follows`
                        WHERE `member_id`=".$_SESSION["id"];
      //SQL文実行
      $following_stmt = $dbh->prepare($following_sql);
      $following_stmt->execute();
      //フォローしている人の数がいくつかカウントしている
      $following = $following_stmt->fetch(PDO::FETCH_ASSOC);


       //Followerの数
      $follower_sql = "SELECT COUNT(*) as `cnt`
                        FROM `follows`
                        WHERE `follower_id`=".$_SESSION["id"];
      //SQL文実行
      $follower_stmt = $dbh->prepare($follower_sql);
      $follower_stmt->execute();
      //フォローしてくれている人の数がいくつかカウントしている
      $follower = $follower_stmt->fetch(PDO::FETCH_ASSOC);

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
      <div class="col-md-3 content-margin-top">
        <form method="post" action="" class="form-horizontal" role="form">

        <img src="picture_path/<?php echo $profile_member["picture_path"]; ?>" width="250" height="250">
        <h3><?php echo $profile_member["nick_name"]; ?>さん</h3>
        <?php if($_SESSION["id"] != $profile_member["member_id"]){ ?>


          <?php if($fl_flag['cnt'] == 0){ ?>
          <a href="profile.php?member_id=<?php echo $profile_member["member_id"]; ?>&follow_id=<?php echo $profile_member["member_id"]; ?>">
          <button class="btn btn-block btn-default">フォロー</button>
          </a>
          <?php }else{ ?>


          <a href="profile.php?member_id=<?php echo $profile_member["member_id"]; ?>&unfollow_id=<?php echo $profile_member["member_id"]; ?>">
          <button class="btn btn-block btn-default">フォロー解除</button>
          </a>
          <?php } ?>

        <?php } ?>
        <br>
        <a href="index.php">&laquo;&nbsp;一覧へ戻る</a>

        <div class="form-group">
        <ul class="paging">
              &nbsp;&nbsp;&nbsp;&nbsp;
              <?php if($page == 1){ ?>
              <li>前</li>
              <?php }else{ ?>
              <li><a href="profile.php?page=<?php echo $page - 1; ?>" class="btbtn-default">前</a></li>
              <?php } ?>
              &nbsp;&nbsp;|&nbsp;&nbsp;
              <?php if($page == $all_page_number){ ?>
              <li>次</li>
              <?php }else{ ?>
              <li><a href="profile.php?page=<?php echo $page + 1; ?>" class="btbtn-default">次</a></li>
              <li><?php echo $page; ?> / <?php echo $all_page_number; ?>Page</li>
            <?php } ?>
        </ul>
        </form>
       </div>
      </div>
    

      <div class="col-md-9 content-margin-top">
      <div class="msg_header">
        <a href="#">Followers<span class="badge badge-pill badge-default"><?php echo $follower["cnt"]; ?></span></a><a href="#">Following<span class="badge badge-pill badge-default"><?php echo $following["cnt"]; ?></span></a>
      </div>

      <?php foreach ($tweet_list as $one_tweet) { ?>

      <!-- 繰り返すタグが描かれる場所 -->
        <div class="msg">
        <a href="profile.php?member_id=<?php echo $one_tweet["member_id"]; ?>">
          <img src="picture_path/<?php echo $one_tweet["picture_path"]; ?>" width="100" height="100"></a>
          <p>投稿者 :  <span class="name"><?php echo $one_tweet["nick_name"]; ?></span></p>
          <p>
            つぶやき : <br>
            <?php echo $one_tweet["tweet"];?>

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

            <?php if($_SESSION["id"] == $one_tweet["member_id"]){ ?>
             [<a onclick="return confirm('削除します、よろしいですか？');" href="delete.php?tweet_id=<?php echo $one_tweet["tweet_id"]; ?>" style="color: #F33;">削除</a>]
             <?php }?>
            <!--[<a onclick="return confirm('削除します、よろしいですか？');" href="delete.php?tweet_id=<?php //echo $one_tweet["tweet_id"]; ?>" style="color: #F33;">削除</a>]
            <?php// } ?>-->
          </p>
        </div>
             <?php }?>
      </div>
    </div>
  </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="assets/js/jquery-3.1.1.js"></script>
    <script src="assets/js/jquery-migrate-1.4.1.js"></script>
    <script src="assets/js/bootstrap.js"></script>
  </body>
</html>