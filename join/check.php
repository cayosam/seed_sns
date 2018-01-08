<?php
  session_start();



  //DBに接続(五行おまとめ)
  //前回作ったdbconnect.phoと場所かちがうので注意
  require('../dbconnect.php');

//会員ボタンが押された時
  if (isset($_POST) && !empty($_POST)) {
    //変数に入力された値を代入して扱いやすいようにする
    $nick_name = $_SESSION['join']['nick_name'];
    $email = $_SESSION['join']['email'];
    $password = $_SESSION['join']['password'];
    $picture_path = $_SESSION['join']['picture_path'];



    try {
    //DBに会員情報を登録するSQL文を作成(三行の決まり文句t+$data)
    //下記の$sql文はphpMyAdminのSQL>INSERTからコビペして修正して使用
    //noe() とはMySQLが用意してくれている関数。現在日時を取得できる
    $sql = "INSERT INTO `members`(`nick_name`, `email`, `password`, `picture_path`, `created`, `modified`) VALUES (?,?,?,?,now(),now())";
    //SQL文実行
    //shl1 暗号化を行う関数の一つ
    $data = array($nick_name,$email,sha1($password),$picture_path);

    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);

    //$_SESSIONの情報を削除
    //unset 指定した変数を削除するという意味。SESSIOMじゃなくても使える。
    unset($_SESSION["join"]);

    //thanks.php へ遷移
    header('Location: thanks.php');
    exit();
  //Exceptionは例外という意味
    } catch (Exception $e) {
      //tryで囲まれた処理でエラーが発生した時にやりたい処理を記述する場所（$e のところに表示させたいことを書く）

      echo 'SQL文実行エラー:'.$e->getMessage();
      exit();

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
    <link href="../assets/css/bootstrap.css" rel="stylesheet">
    <link href="./assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="../assets/css/form.css" rel="stylesheet">
    <link href="../assets/css/timeline.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <!--
      designフォルダ内では2つパスの位置を戻ってからcssにアクセスしていることに注意！
     -->

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
      <div class="col-md-4 col-md-offset-4 content-margin-top">
        <form method="post" action="" class="form-horizontal" role="form">
          <input type="hidden" name="action" value="submit">
          <div class="well">ご登録内容をご確認ください。</div>
            <table class="table table-striped table-condensed">
              <tbody>
                <!-- 登録内容を表示 -->
                <tr>
                  <td><div class="text-center">ニックネーム</div></td>
                  <td><div class="text-center"><?php echo $_SESSION['join']['nick_name']; ?></div></td>
                </tr>
                <tr>
                  <td><div class="text-center">メールアドレス</div></td>
                  <td><div class="text-center"><?php echo $_SESSION['join']['email']; ?></div></td>
                </tr>
                <tr>
                  <td><div class="text-center">パスワード</div></td>
                  <td><div class="text-center">●●●●●●●●</div></td>
                </tr>
                <tr>
                  <td><div class="text-center">プロフィール画像</div></td>
                  <td><div class="text-center"><!--<img src="http://c85c7a.medialib.glogster.com/taniaarca/media/71/71c8671f98761a43f6f50a282e20f0b82bdb1f8c/blog-images-1349202732-fondo-steve-jobs-ipad.jpg" width="100" height="100">-->
                    <img src="../picture_path/<?php echo $_SESSION['join']['picture_path']; ?>" width="100" height="100">
                  </div></td>
                </tr>
              </tbody>
            </table>

             <!--index.php内のボタンを押すことで移動するので、それ自体が相対パスとなる。そのため、index.phpと書くだけでもどのindex.phpか正しく判断される。 -->
            <a href="index.php?action=rewrite">&laquo;&nbsp;書き直す</a> |
            <input type="submit" class="btn btn-default" value="会員登録">
          </div>
        </form>
      </div>
    </div>
  </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="../assets/js/jquery-3.1.1.js"></script>
    <script src="../assets/js/jquery-migrate-1.4.1.js"></script>
    <script src="../assets/js/bootstrap.js"></script>
  </body>
</html>
