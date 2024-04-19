<?php
// エラーを出力する
ini_set("display_errors", "On");
error_reporting(E_ALL);

// セッション開始
session_start();

if (isset($_SESSION['user_id'])) {
    // セッション変数を全て削除
    $_SESSION = array();
    // セッションクッキーを削除
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 1800, '/');
    }
    // セッションの登録データを削除
    session_destroy();
    setcookie("42ch_Cookie", '', time() - 3600, "/");
    echo "ログアウト処理完了";
    //exit(); // リダイレクト前にスクリプトの実行を終了
} else {
    echo "ログインしていません";
    // ログイン画面などへリダイレクトする処理を記述する
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="refresh" content="1;url=./42ch.php">
<title>Logout</title>
</head>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-L3BGZCNTS8"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-L3BGZCNTS8');
</script>
<body>
<!-- <button onclick="location.href='./42ch.php'">戻る</button> -->
</body>
</html>
