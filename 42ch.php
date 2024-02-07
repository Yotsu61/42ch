<?php 
require_once(dirname(__FILE__) ."/secret.php");
?>



<style>

</style>



<?php

// データベース接続情報



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //echo $_GET['thread_title'];
    $thread_title = $_POST['thread_title_post'];
    //一番上
    var_dump($_POST);




    $conn = new mysqli(DB_SERVERNAME, DB_USERNAME, DB_PASSWORD, DB_DBNAME);
    // 接続確認
    if ($conn->connect_error) {
        die("データベース接続エラー: " . $conn->connect_error);
    }

    // SELECTクエリを実行
    $sql = "SELECT MAX(thread_id) as max_id FROM `threads`";
    $result = $conn->query($sql);

    // 結果を表示
    if ($result->num_rows > 0) {
        // データがある場合
        $row = $result->fetch_assoc();
        $curr_thread_id = $row["max_id"];
        $max_thread_id = $curr_thread_id + 1;
        echo "スレッド: " . $curr_thread_id . "<br>";
    } else {
        // データがない場合は1から始める
        $max_thread_id = 1;
    }

    # connect mysql PDO
    $dsn = 'mysql:dbname='.DB_DBNAME.';host='.DB_SERVERNAME;

    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD);
    $sql = $pdo->prepare("INSERT into threads (thread_title, thread_id) values (:thread_title, :thread_id)");

    //# bindParamで:nameなどを変数$nameに設定、PARAM_でデータ型を指定
    $sql->bindParam(':thread_title', $thread_title, PDO::PARAM_STR);
    $sql->bindParam(':thread_id', $max_thread_id, PDO::PARAM_INT);

    $res = $sql->execute();

    // データベース接続を閉じる
    $conn->close();
    echo "";
    exit();
}


function h($str)
{
    return htmlspecialchars($str, ENT_QUOTES,"");
}
?>



<!DOCTYPE html>
<html lang="ja" dir="ltr">
<!-- <link rel="stylesheet" href="mobile-style.css"> -->
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel=”icon” href=“/favicon.ico”>

    <title>42ch</title>

</head>


<script>
const userAgent = navigator.userAgent;
if (/iPhone|Android/.test(userAgent)) {
    document.write('<link rel="stylesheet" href="mobile_style.css">');
} else {
    document.write('<link rel="stylesheet" href="desktop_style.css">');
}
</script>




<h2>42ch</h2>

<h5>Opps! XSSの脆弱性は対策されました</h5>
<!-- <p><a href="../42ch_v1.1 Unsecured/42ch.php">旧42ch v1.1 XSS未対策Ver<a></p> -->

<a href="https://yotsunoserver.yotsu.cc/ajtest/ajax.php">ズミchat<a></p>


<h3>スレッド一覧</h3>

<div class="thread-box">
<?php

ini_set("display_errors", "On");
error_reporting(E_ALL);

// データベースに接続
$conn = new mysqli(DB_SERVERNAME, DB_USERNAME, DB_PASSWORD, DB_DBNAME);

// 接続確認
if ($conn->connect_error) {
    die("データベース接続エラー: " . $conn->connect_error);
}

// SELECTクエリを実行
$sql = "SELECT thread_title,thread_id from threads";
$result = $conn->query($sql);

// 結果を表示
if ($result->num_rows > 0) {
    // データがある場合
    while ($row = $result->fetch_assoc()) {
        // echo "スレッド: " . $row["title"]. " " . $row["id"]. "<br>";

        echo '<a href="thread.php?thread_id=' . h($row['thread_id']) . '">' . h($row['thread_title']) . '</a><br>';
    }
} else {
    // データがない場合
    echo "データがありません";
}

// データベース接続を閉じる
$conn->close();
?>
</div>









<body>
<!-- <h3>スレッド一覧</h3> -->
    <form id="messPost">
        <textarea name="thread_title_post" placeholder="スレッドタイトルを入力して下さい"></textarea>

        <input type="submit" value="投稿">
    </form>

</body>

</html>






<!-- スレッド投稿フォームからPHPへPOSTするJavascript処理 -->

<script>
    const myFormElm = document.forms.messPost;

    myFormElm.addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(myFormElm);

        // フォームが空かチェック
        if (formData.get('thread_title_post') === '') {
            // エラーを表示
            alert('スレッドタイトルを入力してください。');
            return;
        }

        // フォームを送信
        fetch('42ch.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(res => {
                console.log(res);
                console.log("1");
                window.location.reload();
                console.log("2"); // この行が実行されるはず
            })
            .catch(error => {
                console.log("er");
                console.log(error);
                window.location.reload();
            });
    });

</script>