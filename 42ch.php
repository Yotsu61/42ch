<?php
session_start();

// エラーを出力する
ini_set('display_errors', "On");

require_once(dirname(__FILE__) . "/secret.php");

$user_name = "ログインしていません";

if (!isset($_SESSION["user_id"]) and isset($_COOKIE["42ch_Cookie"])) {
    $conn = new mysqli(DB_SERVERNAME, DB_USERNAME, DB_PASSWORD, DB_DBNAME);
    if ($conn->connect_error) {
        die("データベース接続エラー: " . $conn->connect_error);
    }

    $sql = "SELECT * FROM session_keys WHERE session_key = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $_COOKIE["42ch_Cookie"]);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
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

    if ($conn->connect_error) {
        die("データベース接続エラー: " . $conn->connect_error);
    }

    $sql = "SELECT * FROM users WHERE user_id = $user_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $user_name = $row['user_name'];
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $thread_title = $_POST['thread_title_post'];

    $conn = new mysqli(DB_SERVERNAME, DB_USERNAME, DB_PASSWORD, DB_DBNAME);

    if ($conn->connect_error) {
        die("データベース接続エラー: " . $conn->connect_error);
    }

    $sql = "SELECT MAX(thread_id) as max_id FROM `threads`";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $curr_thread_id = $row["max_id"];
        $max_thread_id = $curr_thread_id + 1;
    } else {
        $max_thread_id = 1;
    }

    $dsn = 'mysql:dbname=' . DB_DBNAME . ';host=' . DB_SERVERNAME;
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD);

    $sql = $pdo->prepare(
        "INSERT into threads
        (thread_title, thread_id, `timestamp`, board_id)
        values
        (:thread_title, :thread_id, now(), 0)"
    );

    $sql->bindParam(':thread_title', $thread_title, PDO::PARAM_STR);
    $sql->bindParam(':thread_id', $max_thread_id, PDO::PARAM_INT);

    $res = $sql->execute();

    $conn->close();

    exit();
}

function h($str)
{
    return htmlspecialchars($str, ENT_QUOTES, "");
}
?>

<!DOCTYPE html>
<html lang="ja" dir="ltr">

<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <link rel="icon" href="favicon.ico">

    <title>42ch</title>

    <?php include(dirname(__FILE__) . '/GoogleAnalitycs.php'); ?>

    <script>
        const userAgent = navigator.userAgent;

        if (/iPhone|Android/.test(userAgent)) {
            document.write(
                '<link rel="stylesheet" href="mobile_style.css">'
            );
        } else {
            document.write(
                '<link rel="stylesheet" href="desktop_style.css">'
            );
        }
    </script>
</head>

<body>

<div class="container">

    <!-- サイドバー -->
    <aside class="sidebar">

        <div class="sidebar-title">
            42ch
        </div>

        <div class="sidebar-user">
            ユーザー：<br>
            <strong><?php echo h($user_name); ?></strong>
        </div>

        <div class="sidebar-menu">
            <a href="42ch.php">■ ホーム</a>
        </div>

        <hr>

        <div class="sidebar-section-title">
            ■ 板一覧
        </div>

        <?php
        ini_set("display_errors", "On");
        error_reporting(E_ALL);

        $conn = new mysqli(
            DB_SERVERNAME,
            DB_USERNAME,
            DB_PASSWORD,
            DB_DBNAME
        );

        if ($conn->connect_error) {
            die(
                "データベース接続エラー: "
                . $conn->connect_error
            );
        }

        $sql = "SELECT * FROM boards";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {

                echo '<div class="sidebar-board">';

                echo '<a href="board.php?board_id='
                    . h($row['board_id'])
                    . '">・'
                    . h($row['board_name'])
                    . '</a>';

                echo '</div>';
            }
        } else {
            echo "データがありません";
        }

        $conn->close();
        ?>

        <hr>

        <div class="sidebar-menu">
            <?php
            echo $login_button;
            echo $sign_in_button;
            echo $logout_button;
            ?>
        </div>

        <div class="sidebar-version">
            42ch v4.1.0<br>
            2026/08/23
        </div>

    </aside>

    <!-- メイン -->
    <div class="main-content">

        <h2>42ch</h2>

        <h4>
            <!-- Update!2025/05/25 v3.6.0<br>
            スレッド一覧が降順になりました<br>
            画像をハイパーリンク化！<br>
            画像をフルスクリーンで閲覧できるようになりました<br> -->

            <!-- Update!2025/08/23 v4.0.0<br>
            板機能が実装されました<br> -->

            <!-- Update!2025/08/23 v4.0.1<br> -->
            <!-- ホームでスレッドが作成できないバグを修正しました<br> -->

            <!-- Update!2026/08/23 v4.1.0<br>
            一部デザインを刷新しました -->
        </h4>

        <div class="update-text">
            <strong>
                Update!2026/08/23 v4.1.0
            </strong>
            <br>
            一部デザインを刷新しました
        </div>

        <h3>スレッド一覧</h3>

        <div class="thread-box">

            <?php
            ini_set("display_errors", "On");
            error_reporting(E_ALL);

            $conn = new mysqli(
                DB_SERVERNAME,
                DB_USERNAME,
                DB_PASSWORD,
                DB_DBNAME
            );

            if ($conn->connect_error) {
                die(
                    "データベース接続エラー: "
                    . $conn->connect_error
                );
            }

            $sql = "SELECT
                t.thread_title,
                t.thread_id,
                COUNT(m.message_id) AS count_message,
                MAX(m.write_timestamp) AS last_message_timestamp
                FROM threads t
                LEFT JOIN messages m
                    ON t.thread_id = m.thread_id
                LEFT JOIN boards b
                    ON t.board_id = b.board_id
                WHERE b.password IS NULL
                OR b.password = ''
                OR t.board_id = 0
                GROUP BY t.thread_id
                ORDER BY t.thread_id DESC";

            $result = $conn->query($sql);

            if ($result->num_rows > 0) {

                while ($row = $result->fetch_assoc()) {

                    echo '<div class="threads-list">';

                    echo '<a href="thread.php?thread_id='
                        . h($row['thread_id'])
                        . '">'
                        . h($row['thread_title'])
                        . '</a></br>';

                    echo '<span class="tik">レス数：</span>';

                    echo '<span class="thread_info">'
                        . $row['count_message']
                        . '</span> ';

                    echo '<span class="tik">更新：</span>';

                    echo '<span class="thread_info">'
                        . $row['last_message_timestamp']
                        . '</span>';

                    echo '</div>';

                    if ($mobile === true) {
                        echo '<br>';
                    }
                }

            } else {
                echo "データがありません";
            }

            $conn->close();
            ?>

        </div>

        <div
            style="
                padding: 10px;
                margin-bottom: 10px;
                border: 1px solid #333333;
            "
        >

            <form id="messPost">

                <h4>
                    スレッド作成フォーム
                </h4>

                <input
                    type="text"
                    name="thread_title_post"
                    placeholder="スレッドタイトルを入力して下さい"
                    style="
                        width: 250px;
                        height: 25px;
                        margin: 10px 0 10px 0;
                    "
                >

                <input
                    type="submit"
                    value="投稿"
                >

            </form>

        </div>

    </div>

</div>

<script>
    const myFormElm =
        document.forms.messPost;

    myFormElm.addEventListener(
        'submit',
        (e) => {

            e.preventDefault();

            const formData =
                new FormData(
                    myFormElm
                );

            if (
                formData.get(
                    'thread_title_post'
                ) === ''
            ) {

                alert(
                    'スレッドタイトルを入力してください。'
                );

                return;
            }

            fetch(
                window.location.pathname,
                {
                    method: 'POST',
                    body: formData
                }
            )
            .then(() => {

                window.location.reload();

            })
            .catch(error => {

                console.log(error);

                window.location.reload();

            });
        }
    );
</script>

</body>

</html>