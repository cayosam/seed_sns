<?php
  session_start();//セッション変数を使用するときは必ず必要、必ず一番上に記述


  //DB接続する(５行おまとめ、同じ階層にdbconnectがあるため../が不要)
  require('dbconnect.php');

    //likeボタンが押された時
     if(isset($_GET["like_tweet_id"])){
      //like情報をlikesテーブルに登録
      //下記は関数の呼び出しを行うことで処理が可能。書いただけでは実行されない。
      like($_GET["like_tweet_id"],$_SESSION["id"],$_GET["page"]);
      //$sql = "INSERT INTO `likes`(`tweet_id`, `member_id`)
      //        VALUES (".$_GET["like_tweet_id"].",".$_SESSION["id"].");";

      ////SQL文の実行
      //stmt = $dbh->prepare($sql);
      //stmt->execute();

      ///一覧ページに戻る
      //eader("Location: index.php");

     }
    //unlikeボタンが押された時
     if(isset($_GET["unlike_tweet_id"])){
      //登録されているlike情報をlikesテーブルから削除
      unlike($_GET["unlike_tweet_id"],$_SESSION["id"],$_GET["page"]);
      // $sql = "DELETE FROM `likes`
      //         WHERE tweet_id=".$_GET["unlike_tweet_id"]." AND member_id=".$_SESSION["id"];

      // //SQL文の実行
      //$stmt = $dbh->prepare($sql);
      //$stmt->execute();

      ////一覧ページに戻る
      //header("Location: index.php");

        }

        //like関数
        //引数 like_tweet_id,login_member_id,$page
        function like($like_tweet_id,$login_member_id,$page){
          //DB接続は関数内で行う必要がある
          require('dbconnect.php');

          $sql = "INSERT INTO `likes`(`tweet_id`, `member_id`)
                  VALUES (".$like_tweet_id.",".$login_member_id.");";

           //SQL文の実行
          $stmt = $dbh->prepare($sql);
          $stmt->execute();

          //一覧ページに戻る
          header("Location: index.php?page=".$page);
          }

        //unlike関数
        function unlike($unlike_tweet_id,$login_member_id,$page){
          //DB接続は関数内で行う必要がある
          require('dbconnect.php');

          $sql = "DELETE FROM `likes`
                  WHERE tweet_id=".$unlike_tweet_id." AND member_id=".$login_member_id;

           //SQL文の実行
          $stmt = $dbh->prepare($sql);
          $stmt->execute();

          //一覧ページに戻る
          header("Location: index.php?page=".$page);
          }

  ?>