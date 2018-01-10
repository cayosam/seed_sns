<?php
  session_start();//セッション変数を使用するときは必ず必要、必ず一番上に記述

  //セッショッンの中身を空の配列で上書きする
  $_SESSION = array();

  //セッション情報を有効期限切れにする、サーバー側のクッキー情報を削除
  if(ini_get("session.use_cookies")){
     $params = session_get_cookie_params();
     setcookie(session_name(),'',time() - 42000,$params['path'],$params["domain"],$params['secure'],$params['httponly']);
     //上記の文は決まり文句のようになっている。-42000は適当に入れた数字

  }

  //セッション情報の破棄
  session_destroy();

  //COOKIE情報の削除（クライアント側のクッキー情報を削除）
  //setcookie(削除したい名前,削除したい値,削除したい期間；秒数);
  setcookie('email','',time() -3000);
  setcookie('password','',time() -3000);
 
  //ログイン後の画面に戻る
  header("Location: login.php");
  exit();

  //ログイン後の画面に、ログインチェックの機能を実装
  //ログイン後の画面に行くことによって、確実にログアウトしていること確認できる




?>