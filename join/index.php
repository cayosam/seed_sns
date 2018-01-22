<?php
session_start();//SETTION変数を使うときは、絶対必要で、必ず一番上に記述

  //DB接続(5行おまとめ)
  require('../dbconnect.php');

  //書き直し処理（check.phpで書き直し、というボタンが押された時）
  if(isset($_GET['action']) && $_GET['action'] == 'rewrite'){

    //書き直すために初期表示する情報を変数に格納
     //入力チェック
    $nick_name = $_SESSION['join']['nick_name'];
    $email = $_SESSION['join']['email'];
    $password = $_SESSION['join']['password'];
  }else{
    $nick_name = '';
    $email = '';
    $password = '';
    }

//POST送信された時(if文)
//$_POSTという変数が存在している、かつ、$_POSTという変数の中身が空っぽではない↓という意味
//empty..は中身が空か判定　例えば、0、null、false というものをすべて空と認識する
//$_POSTの中身はname,nick-name,passwordなどキーの名前がついてる。キーがついて存在しているだけ。
//空文字（’’）というものが存在している。一つのキーのみ空文字だった場合、$_POSTが存在していて空でもない。
//しかし、中身を見てみると、空文字が存在しているそのため以下のif文が必要
if (isset($_POST) && !empty($_POST)){


    //nick_nameが空っぽだったら$errorという、エラーの情報を格納する変数に
    //nick_nameはblankだったというマークを保存しとく
    if ($_POST["nick_name"] == ''){
      $error["nick_name"] = 'blank';
    }

    if ($_POST["email"] == ''){
      $error["email"] = 'blank';
    }

    //strlen 文字の長さと（文字数）を数字で返してくれる関数
    //blankとlengthは自分でつくった関数
    if ($_POST["password"] == ''){
      $error["password"] = 'blank';
    }elseif (strlen($_POST["password"]) < 4) {
      $error["password"] = 'length';
    }

    //入力チェック後、エラーが何もなければcheck.phpに移動
    //emptyではなくissetなのか、$errorという変数が存在していなかった場合、入力が正常と認識
    if (!isset($error)) {

      //emailの重複チェック
      //DBにアクセスし、同じemailの登録があるか確認
       try {
        //検索条件にヒットした件数を取得するSQL文
        //COUNT()SQL文の関数。ヒットした件数を取得。これがない場合は、件数ではなくデータ全部取得となる
        //as `別名` で取得したデータに別な名前をつけて扱いやすいようにする
         $sql = "SELECT COUNT(*) as `cnt` FROM `members` WHERE `email`=?";

         //sql実行
         $data = array($_POST["email"]);
         $stmt = $dbh->prepare($sql);
         $stmt ->execute($data);

         //件数取得
         $count = $stmt->fetch(PDO::FETCH_ASSOC);

           if($count['cnt'] > 0){
            //重複エラー
            $error['email'] = "duplicated";
           }

       } catch (Exception $e) {
       }

  if(!isset($error)){
      //画像の拡張子チェック
      // .jpg .png .gif はOK
      //substr...文字列から範囲指定して一部分の文字を切り出す関数
      //substr （文字列、切り出す文字のスタートの数）マイナスの場合は末尾からn文字目となる
      //例）i.png がファイル名の場合、$extpng

      $ext = substr($_FILES['picture_path']['name'], -3);

     if(($ext == 'png')||($ext == 'jpg')||($ext == 'gif')){

    //画像のアップロード処理
    //例）eriko1.pngを指定した時、$_picture_nameの中身は20171222142530eriko1.pngというような文字列が代入される。
    //（eriko1.pngは元々のファイルの名前、201712221425は日時）$_FILES['picture_path']['name']はファイル専用のグローバル変数。二次元配列になっている。
    //YmdHisは年、月、日、24時間表記、分、秒を表している。
    //ファイル名の決定
      $picture_name = date('YmdHis') . $_FILES['picture_path']['name'];

    //アップロード（フォルダに書き込み権限がないと保存されない。）
      //move_uploded_file(firenameアップロードしたいもの,destinationサーバのどこにどういう名前でアップロードするかパス指定。)
      move_uploaded_file($_FILES['picture_path']['tmp_name'], '../picture_path/'.$picture_name);


    //SESSHION変数に入力された値を保存(どこの画面からでも使用できる！)
    //注意※必ず、ファイルの一番上に、session_

      $_SESSION['join'] = $_POST;
      $_SESSION['join']['picture_path'] = $picture_name;

    //check.php
      header('Location: check.php');

      //これ以下のコードを無駄に処理しないように、ページの処理を終了させる
      exit();


    }else{
      $error["image"] = 'type';
    }
   }
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
    <link href="../assets/font-awesome/css/font-awesome.css" rel="stylesheet">
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
              <a class="navbar-brand" href="index.php"><span class="strong-title"><i class="fa fa-twitter-square"></i> Seed SNS</span></a>
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
        <legend>会員登録</legend>
        <form method="post" action="" class="form-horizontal" role="form" role="form" enctype="multipart/form-data">
          <!-- ニックネーム -->
          <div class="form-group">
            <label class="col-sm-4 control-label">ニックネーム</label>
            <div class="col-sm-8">
              <input type="text" name="nick_name" class="form-control" placeholder="例： Seed kun" value="<?php echo $nick_name; ?>">
              <?php if ((isset($error["nick_name"]) && ($error["nick_name"]) == 'blank')){ ?>
              <p class="error">* ニックネームを入力してください。</p>
              <?php } ?>
            </div>
          </div>

          <!-- メールアドレス -->
          <div class="form-group">
            <label class="col-sm-4 control-label">メールアドレス</label>
            <div class="col-sm-8">
              <input type="email" name="email" class="form-control" placeholder="例： seed@nex.com" value="<?php echo $email; ?>">
            <?php if ((isset($error["email"]) && ($error["email"]) == 'blank')){ ?>
              <p class="error">* Emailを入力してください。</p>
              <?php } ?>

               <?php if ((isset($error["email"]) && ($error["email"]) == 'duplicated')){ ?>
              <p class="error">* 入力されたEmailは登録済みです。</p>
              <?php } ?>
            </div>
          </div>

          <!-- パスワード -->
          <div class="form-group">
            <label class="col-sm-4 control-label">パスワード</label>
            <div class="col-sm-8">
              <input type="password" name="password" class="form-control" placeholder="" value="<?php echo $password; ?>">
              <?php if ((isset($error["password"]) && ($error["password"]) == 'blank')){ ?>
              <p class="error">* パスワードを入力してください。</p>
              <?php } ?>
              <?php if ((isset($error["password"]) && ($error["password"]) == 'length')){ ?>
              <p class="error">* パスワードは4文字以上を入力してください。</p>
              <?php } ?> 
             </div>
          </div>

          <!-- プロフィール写真 -->
          <div class="form-group">
            <label class="col-sm-4 control-label">プロフィール写真</label>
            <div class="col-sm-8">
              <input type="file" name="picture_path" class="form-control">
              <?php if ((isset($error["image"]) && ($error["image"]) == 'type')){ ?>
               <p class="error">* 画像ファイルを選択してください。</p>
             <?php } ?> 
            </div>
          </div>

          <input type="submit" class="btn btn-default" value="確認画面へ">
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
