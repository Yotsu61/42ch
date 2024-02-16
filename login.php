<?php
require_once(dirname(__FILE__) ."/secret.php");

ini_set("display_errors", "On");
error_reporting(E_ALL);

// データベース接続
$conn = new mysqli(DB_SERVERNAME, DB_USERNAME, DB_PASSWORD, DB_DBNAME);

// 接続確認
if ($conn->connect_error) {
  die("データベース接続エラー: " . $conn->connect_error);
}

// フォーム送信時処理
if (isset($_POST['user_name_post']) && isset($_POST['password_post'])) {
  $username = $_POST['user_name_post'];
  $password = $_POST['password_post'];

  // ユーザー情報取得
  $sql = "SELECT user_id, password FROM users WHERE user_name = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $user_data = $result->fetch_assoc();
    // パスワード照合
    if (password_verify($password, $user_data['password'])) {
      // ログイン成功
      session_start();
      $_SESSION['user_id'] = $user_data['user_id'];
      header("Location: 42ch.php");
      exit;
    } else {
      // パスワード不一致
      echo "パスワードが間違っています。";
    }
  } else {
    // ユーザーが存在しない
    echo "ユーザー名が存在しません。";
  }
}

// データベース接続を閉じる
$conn->close();

function h($str)
{
  if ($str === null || $str === "") {
    return "";
  }
  return htmlspecialchars($str, ENT_QUOTES,"");
}
?>

<!DOCTYPE html>
<html lang="ja" dir="ltr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel=”icon” href=“favicon.ico”>

<title>42ch ログイン</title>

</head>

<body>


<form id="messPost" enctype="multipart/form-data" method="POST">
<<<<<<< HEAD
<textarea name="user_name_post" <?php if (isset($_POST['user_name_post']) && $_POST['user_name_post'] !== "") { ?>value="<?= h($_POST['user_name_post'])?>"<?php } ?> placeholder="ユーザ名を入力して下さい" style="width : 210px; height: 25px; margin: 10px 0 10px 0;"></textarea><br>
<textarea name="password_post" placeholder="パスワードを入力して下さい" style="width : 250px; height: 25px; margin: 10px 0 10px 0;"></textarea>
<br> 
=======
<textarea name="user_name_post" <?php if (isset($_POST['user_name_post']) && $_POST['user_name_post'] !== "") { ?>value="<?= h($_POST['user_name_post'])?>"<?php } ?> placeholder="ユーザ名を入力して下さい"></textarea><br>
<textarea name="password_post" placeholder="パスワードを入力して下さい"></textarea>
  
>>>>>>> 78311bb54cceb1614aa3a50813d82b1f19e64b02
<input type="submit" value="ログイン">
</form>

  

</body>
</html>
