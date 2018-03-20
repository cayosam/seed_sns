<?php
  require('dbconnect.php');

  $tag_id = $_GET["tag_id"];
  //tag名の取得
  $get_tag_sql = "SELECT * FROM `tags`
                  WHERE `id`=".$tag_id;
  //sql文実行
  $stmt = $dbh->prepare($get_tag_sql);
  $stmt->execute();
  //フェッチ
  $one_tag = $stmt->fetch(PDO::FETCH_ASSOC);
  //tagを含んだ一覧の取得
  $tag_search_sql = "SELECT `tweets`.*,`members`.`nick_name`,`members`.`picture_path` FROM `tweets`
                     INNER JOIN `tweet_tags`
                     ON `tweets`.`tweet_id` = `tweet_tags`.`tweet_id`
                     INNER JOIN `members`
                     ON `tweets`.`member_id` = `members`.`member_id`
                     WHERE `tag_id` = ".$tag_id;

  //SQL実行
  $tags_stmt = $dbh->prepare($tag_search_sql);
  $tags_stmt->execute();
  //一覧表示用配列
  $tag_search_list = array();
  //フェッチ
  while(1){
    $one_tweet = $tags_stmt->fetch(PDO::FETCH_ASSOC);

    if($one_tweet == false){
      break;
    }
    $tag_search_list[] = $one_tweet;
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
      <div class="col-md-6 col-md-offset-3 content-margin-top">
        <h4>#<?php echo $one_tag["tag"]; ?></h4>
        <a href="index.php">&laquo;&nbsp;一覧へ戻る</a>
          <?php foreach ($tag_search_list as $tweet){ ?>
            <div class="msg">
            <img src="picture_path/<?php echo $tweet["picture_path"]; ?>" width="48" height="48">
          <p>
           <?php echo $tweet["tweet"]; ?>
            <span class="name">(<?php echo $tweet["nick_name"];?>)
            </span>
            [<a href="reply.php?tweet_id=<?php echo $tweet["tweet_id"]; ?>">Re</a>]
          </p>
          <p class="day">
            <a href="view.php?tweet_id=<?php echo $one_tweet["tweet_id"]; ?>">
              <?php
              $modefy_date = $tweet["modified"];
              //strtotime 文字型のデータを日時型に変換できる
              //(Y年m月d日 と記述することも可能)(H24時間表記、h12時間表記)
              $modefy_date = date("Y-m-d H:i",strtotime($modefy_date));
              echo $modefy_date;
              ?>            </a>
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