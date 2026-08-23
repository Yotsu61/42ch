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
if (!isset($_SESSION["user_id"]) and isset($_COOKIE["42ch_Cookie"])) {
    $conn = new mysqli(DB_SERVERNAME, DB_USERNAME, DB_PASSWORD, DB_DBNAME);
    // 接続確認
    if ($conn->connect_error) {
        die("データベース接続エラー: " . $conn->connect_error);
    }
    $sql = "SELECT * FROM session_keys WHERE session_key = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $_COOKIE["42ch_Cookie"]);
    // 結果を表示
    $stmt->execute();
    $result = $stmt->get_result();
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


$board_name = "";
$board_idg = 0;
$board_idg = (int) $_GET['board_id'];



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



if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['thread_title_post'])) {
    //echo $_GET['thread_title'];
    $thread_title = $_POST['thread_title_post'];
    // $board_idg = (int) $_POST['board_id'];
    $board_idg = isset($_POST['board_id']) ? (int) $_POST['board_id'] : 0;


    // POSTされたboard_idを取得
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
    $sql = $pdo->prepare("INSERT into threads (thread_title, thread_id, `timestamp`,board_id) values (:thread_title, :thread_id, now(), :board_id)");

    //# bindParamで:nameなどを変数$nameに設定、PARAM_でデータ型を指定
    $sql->bindParam(':thread_title', $thread_title, PDO::PARAM_STR);
    $sql->bindParam(':thread_id', $max_thread_id, PDO::PARAM_INT);
    $sql->bindParam(':board_id', $board_idg, PDO::PARAM_INT);

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

    <title>42ch</title <?php include(dirname(__FILE__) . '/GoogleAnalitycs.php');
    echo $board_idg;
    ?> </head>


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

    <!-- <p><a href="../42ch_v1.1 Unsecured/42ch.php">旧42ch v1.1 XSS未対策Ver<a></p> -->


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


                $board_idg = (int) $_GET['board_id'];

                // パスワード取得処理
                $board_sql = "SELECT password FROM boards WHERE board_id = ?";
                $stmt = $conn->prepare($board_sql);
                $stmt->bind_param("i", $board_idg);
                $stmt->execute();
                $result_pw = $stmt->get_result();

                if ($row_pw = $result_pw->fetch_assoc()) {
                    $board_password = $row_pw['password'];

                    // パスワード保護された板の場合
                    if ($board_password !== null && $board_password !== '') {
                        $input_password = $_POST['input_password'] ?? null;
                        $error_message = '';

                        if (!isset($input_password) || !password_verify($input_password, $board_password)) {
                            if (isset($input_password)) {
                                // パスワードは入力されたが照合に失敗
                                $error_message = '<p style="color: red;">パスワードが間違っています。</p>';
                            }


                            echo '<form method="post" action="?board_id=' . h($board_idg) . '">
                              <p>この板はパスワードで保護されています。</p>';
                            echo $error_message;
                            echo '<input type="password" name="input_password" placeholder="パスワードを入力">
                              <input type="submit" value="送信">
                              </form>';
                            exit;
                        }
                    }
                } else {
                    echo "板が見つかりません。";
                    exit;
                }

                $board_idg = $_GET['board_id'];

                // SELECTクエリを実行
                $sql = "SELECT t.thread_title, t.thread_id, 
                    COUNT(m.message_id) AS count_message, 
                    MAX(m.write_timestamp) AS last_message_timestamp
                    FROM threads t
                    LEFT JOIN messages m ON t.thread_id = m.thread_id
                    WHERE t.board_id = $board_idg
                    GROUP BY t.thread_id
                    ORDER BY t.thread_id DESC";



                $result = $conn->query($sql);



                // 結果を表示
                if ($result->num_rows > 0) {
                    // データがある場合
                    while ($row = $result->fetch_assoc()) {
                        // echo "スレッド: " . $row["title"]. " " . $row["id"]. "<br>";
                
                        echo '<div class="threads-list"><a href="thread.php?thread_id=' . h($row['thread_id']) . '">' . h($row['thread_title']) . '</a></br>';
                        // echo 'レス数:<div class="thread_info">' . $row['count_message'] . '</div> 更新: <div class="thread_info">' . $row['last_message_timestamp'] . '</div></div>';
                        echo '<span class="tik">レス数：</span><span class="thread_info">' . $row['count_message'] . '</span> ';
                        echo '<span class="tik">更新：</span><span class="thread_info">' . $row['last_message_timestamp'] . '</span></div>';

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

    <?php
    $board_id = isset($_GET['board_id']) ? (int) $_GET['board_id'] : 0;
    ?>

    <div style="padding: 10px; margin-bottom: 10px; border: 1px solid #333333;">
        <form id="messPost" method="POST">
            <h4>スレッド作成フォーム</h4>

            <input type="text" name="thread_title_post" placeholder="スレッドタイトルを入力して下さい"
                style="width: 250px; height: 25px; margin: 10px 0 10px 0;">

            <input type="hidden" name="board_id" value="<?php echo $board_id; ?>">
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
        fetch('board.php', {
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