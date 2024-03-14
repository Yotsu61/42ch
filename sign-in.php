<?php
require_once(dirname(__FILE__) . "/secret.php");

ini_set("display_errors", "On");
error_reporting(E_ALL);

// データベース接続
$conn = new mysqli(DB_SERVERNAME, DB_USERNAME, DB_PASSWORD, DB_DBNAME);

// 接続確認
if ($conn->connect_error) {
  die("データベース接続エラー: " . $conn->connect_error);
}

// フォーム送信時処理
if (isset($_POST['user_name_post']) && isset($_POST['password_post']) && isset($_POST['password_confirm_post'])) {
  $username = $_POST['user_name_post'];
  $password = $_POST['password_post'];
  $passwordConfirm = $_POST['password_confirm_post'];

  // ユーザー名重複チェック
  $sql = "SELECT user_id FROM users WHERE user_name = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    // ユーザー名重複
    echo "エラー: ユーザー名が既に存在します。";
  } else if ($password !== $passwordConfirm) {
    // パスワード不一致
    echo "エラー: パスワードが一致しません。";
  } else {
    // パスワードハッシュ化
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // ユーザー登録
    $sql = "INSERT INTO users (user_name, password, clearance_level) VALUES (?, ?, 0)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $hashedPassword);
    $stmt->execute();

    // 登録成功
    echo "ユーザー登録が完了しました。";
  }
}

// データベース接続を閉じる
$conn->close();

function h($str)
{
  return htmlspecialchars($str, ENT_QUOTES, "");
}
?>

<!DOCTYPE html>
<html lang="ja" dir="ltr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel=”icon” href=“favicon.ico”>

  <title>42ch サインイン</title>

</head>

<body>

  サインイン
  <form id="messPost" enctype="multipart/form-data" method="POST">
    <p>Username: <input name="user_name_post" <?php if (isset($_POST['user_name_post']) && $_POST['user_name_post'] !== "") { ?>value="<?= h($_POST['user_name_post']) ?>" <?php } ?> placeholder="ユーザ名を入力して下さい"
        style="width : 210px; height: 25px; margin: 10px 0 10px 0;"></p>
    <p>Password: <input type="password" name="password_post" placeholder="パスワードを入力して下さい"
        style="width : 250px; height: 25px; margin: 10px 0 10px 0;"></p>
    <p>Password: <input type="password" name="password_confirm_post" placeholder="パスワードを再入力して下さい"
        style="width : 250px; height: 25px; margin: 10px 0 10px 0;"></p>
    <input type="submit" value="サインイン">
  </form>



</body>

</html>