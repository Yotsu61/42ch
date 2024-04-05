<?php
session_start();

// エラーを出力する
ini_set('display_errors', "On");
// ini_set('session.gc_maxlifetime',"1814400");

require_once(dirname(__FILE__) . "/secret.php");

$user_name = "ログインしていません";

// セッションIDがCookieに保存されている場合は取得して設定
// if (isset($_COOKIE["42ch_Cookie"])) {
//     session_id($_COOKIE["42ch_Cookie"]);
// }
if (!isset($_SESSION["user_id"]) and isset($_COOKIE["42ch_Cookie"])){
    $conn = new mysqli(DB_SERVERNAME, DB_USERNAME, DB_PASSWORD, DB_DBNAME);
    // 接続確認
    if ($conn->connect_error) {
        die("データベース接続エラー: " . $conn->connect_error);
    }
    $sql = "SELECT * FROM session_keys WHERE session_key = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $_COOKIE["42ch_Cookie"]);
    // 結果を表示
     $stmt ->execute();
     $result = $stmt ->get_result();
    if ($result->num_rows > 0) {
        // データがある場合
        while ($row = $result->fetch_assoc()) {
            $_SESSION['user_id'] = $row['user_id'];
        }
    }
}



$mobile = false;

$login_button = '<button onclick="location.href=\'./login.php\'">ログイン</button>';
$logout_button = '';
$sign_in_button = '<button onclick="location.href=\'./sign-in.php\'">サインイン</button>';





if (isset($_SESSION['user_id'])) {

    $user_id = $_SESSION['user_id'];

    $login_button = "";
    $sign_in_button = "";
    $logout_button = '<button onclick="location.href=\'./logout.php\'">ログアウト</button>';



    $conn = new mysqli(DB_SERVERNAME, DB_USERNAME, DB_PASSWORD, DB_DBNAME);
    // 接続確認
    if ($conn->connect_error) {
        die("データベース接続エラー: " . $conn->connect_error);
    }
    $sql = "SELECT * FROM users WHERE user_id = $user_id";
    $result = $conn->query($sql);
    // 結果を表示
    if ($result->num_rows > 0) {
        // データがある場合
        while ($row = $result->fetch_assoc()) {
            $user_name = $row['user_name'];
            // echo "<br>ユーザ:" . $row['user_name'] . "<br>";
        }
    }
}
echo "ユーザー名:", $user_name;
// echo $_SERVER['REMOTE_ADDR'];
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
    $dsn = 'mysql:dbname=' . DB_DBNAME . ';host=' . DB_SERVERNAME;

    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD);
    $sql = $pdo->prepare("INSERT into threads (thread_title, thread_id, `timestamp`) values (:thread_title, :thread_id, now())");

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
    return htmlspecialchars($str, ENT_QUOTES, "");
}
?>



<!DOCTYPE html>
<html lang="ja" dir="ltr">
<!-- <link rel="stylesheet" href="mobile-style.css"> -->

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel=”icon” href=“favicon.ico”>

    <title>42ch</title>

</head>


<script>
    const userAgent = navigator.userAgent;
    if (/iPhone|Android/.test(userAgent)) {
        document.write('<link rel="stylesheet" href="mobile_style.css">');
        $mobile = true;
    } else {
        document.write('<link rel="stylesheet" href="desktop_style.css">');
    }
</script>




<h2>42ch</h2>

<h4>
Update!2024/04/05 v3.5.1<br>
ご不便おかけし申し訳ございません<br>
ログアウトできないバグを修正しました<br>
2024/03/25 v3.5<br>
Cookieでログイン状態を保持できるようになりました<br>
<!-- 2024/03/14 v3.0<br>
動画が投稿できるようになりました（300MBまで）<br>
ログインしなくても投稿できるようになりました<br>
レス数と更新日時が表示されるようになりました<br>
その他修正<br> -->

</h4>
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
            $sql = "SELECT t.thread_title, t.thread_id, 
            COUNT(m.message_id) AS count_message, 
            MAX(m.write_timestamp) AS last_message_timestamp
            FROM threads t
            LEFT JOIN messages m ON t.thread_id = m.thread_id
            GROUP BY t.thread_id";

            $result = $conn->query($sql);

            // 結果を表示
            if ($result->num_rows > 0) {
                // データがある場合
                while ($row = $result->fetch_assoc()) {
                    // echo "スレッド: " . $row["title"]. " " . $row["id"]. "<br>";


                    echo '<div class="threads-list"><a href="thread.php?thread_id=' . h($row['thread_id']) . '">' . h($row['thread_title']) . '</a>';
                    echo '<div class="thread_info">レス数:' . $row['count_message'] . ' 更新: ' . $row['last_message_timestamp'] . '</div></div>';
                    if ($mobile === true) {
                        echo '<br>';
                    }

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
        <div style="padding: 10px; margin-bottom: 10px; border: 1px solid #333333;">
            <form id="messPost">
            <h4>スレッド作成フォーム</h4>
            <input type="text" name="thread_title_post" placeholder="スレッドタイトルを入力して下さい" style="width: 250px; height: 25px; margin: 10px 0 10px 0;">
                <!-- <br> -->
                <input type="submit" value="投稿">
            </form>
        </div>


            <!-- <button onclick="location.href='./login.php'">ログイン</button>
            <button onclick="location.href='./logout.php'">ログアウト</button>
            <button onclick="location.href='./sign-in.php'">サインイン</button> -->

            <?php
            echo $login_button;
            echo $sign_in_button;
            echo $logout_button;
            ?>



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