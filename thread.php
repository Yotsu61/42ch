<?php
require_once(dirname(__FILE__) . "/secret.php");

error_reporting(E_ALL);

$user_name = $_POST['user_name_post'];

session_start();

$anonymous_username = ' <input type="text" name="user_name_post" placeholder="ユーザー名を入力して下さい" style="width: 250px; height: 25px; margin: 10px 0 10px 0;">';

$user_id = 0;

<<<<<<< HEAD


// ログインしていない場合はログイン画面へリダイレクト
if (isset($_SESSION['user_id'])) {
    $anonymous_username = '';
    //   header("Location: index.php");
//   exit;
    $user_id = $_SESSION['user_id'];
    // $thread_id = $_GET['thread_id'];

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
            // $user_id = $row['user_id'];
            echo "<br>ユーザ:" . $row['user_name'] . "<br>";
        }
    }

}



// スレッド処理



$max_message_id = 0;
$thread_title = "";
$IP_Address = $_SERVER["REMOTE_ADDR"];

// $thread_idg = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = $_POST['message_post'];
    $thread_id = (int) $_POST['thread_id'];
    // $user_name = $_POST['user_name_post'];
    $image_name = null;
    $timestamp_usec = (int) round(microtime(true) * 1000);

    $conn = new mysqli(DB_SERVERNAME, DB_USERNAME, DB_PASSWORD, DB_DBNAME);
    // 接続確認
    if ($conn->connect_error) {
        die("データベース接続エラー: " . $conn->connect_error);
    }

    // SELECTクエリを実行
    $sql = "SELECT count(*) as max_id FROM `messages` where thread_id = $thread_id";
    $result = $conn->query($sql);

    // 結果を表示
    if ($result->num_rows > 0) {
        // データがある場合
        $row = $result->fetch_assoc();
        $curr_message_id = $row["max_id"];
        $max_message_id = $curr_message_id + 1;
        //    echo "メッセージID: " . $curr_message_id . "<br>";
    } else {
        // データがない場合は1から始める
        $max_message_id = 1;
    }


    var_dump($_FILES);

    // ファイルが正常にアップロードされたか確認
    if (isset($_FILES['image_post']) && $_FILES['image_post']['error'] == UPLOAD_ERR_OK) {
        $image_tmp_name = $_FILES['image_post']['tmp_name'];
        $image_name = $timestamp_usec . "_" . $_FILES['image_post']['name'];

        // アップロードされたファイルを指定の場所に移動
        move_uploaded_file($image_tmp_name, IMAGE_FILE_PATH . $image_name);

        // これで、$image_nameを必要に応じてデータベースクエリで使用したり保存できます。
    }

    // 動画が正常にアップロードされたか確認
    if (isset($_FILES['video_post']) && $_FILES['video_post']['error'] == UPLOAD_ERR_OK) {
        $video_tmp_name = $_FILES['video_post']['tmp_name'];
        $video_name = $timestamp_usec . "_" . $_FILES['video_post']['name'];

        // アップロードされた動画ファイルを指定の場所に移動
        move_uploaded_file($video_tmp_name, VIDEO_FILE_PATH . $video_name);

        // これで、$video_nameを必要に応じてデータベースクエリで使用したり保存できます。
    }


    # connect mysql PDO
    $dsn = 'mysql:dbname=' . DB_DBNAME . ';host=' . DB_SERVERNAME;
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD);


    // $sql = $pdo->prepare("INSERT INTO messages (`thread_id`, `user_id`, `message`,`user_name`,`write_timestamp`,`good_cnt`,`bat_cnt`,`message_id`) VALUES (:thread_id,'anonymous',:message,:user_name,now(),0,0,:message_id)");
    // SQLクエリを変更して、アップロードされた画像ファイル名を含めるようにする
    $sql = $pdo->prepare("INSERT INTO messages (`thread_id`, `user_id`, `message`,`user_name`,`write_timestamp`,`good_cnt`,`bat_cnt`,`message_id`,`image_path`,`video_path`,`IP_Address`) VALUES (:thread_id, :user_id, :message, :user_name, now(), 0, 0, :message_id, :image_path, :video_path, :IP_Address)");

    // bindParamで:nameなどを変数$nameに設定、PARAM_でデータ型を指定
    $sql->bindParam(':thread_id', $thread_id, PDO::PARAM_INT);
    $sql->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $sql->bindParam(':message', $message, PDO::PARAM_STR);
    $sql->bindParam(':user_name', $user_name, PDO::PARAM_STR);
    $sql->bindParam(':message_id', $max_message_id, PDO::PARAM_INT);
    // パラメータをバインド
    $sql->bindParam(':image_path', $image_name, PDO::PARAM_STR); // アップロードされた場合はここで画像ファイル名を使用
    $sql->bindParam(':video_path', $video_name, PDO::PARAM_STR); // アップロードされた場合はここで動画ファイル名を使用
    $sql->bindParam(':IP_Address', $IP_Address, PDO::PARAM_STR); //IPアドレス


    $res = $sql->execute();

    //デバッグ用
    if ($res === false) {
        echo "エラー: " . $sql->errorInfo()[2]; // エラーを表示
    } else {
        echo "データベースに挿入されました。";
    }
    $message = $_POST['message_post'];
    var_dump($message); // デバッグ用

    if (!(isset($_SESSION['user_id']))) {
        
    }




    // データベース接続を閉じる
    $conn->close();
    echo "";
    exit();
}



function h($str)
{
    return htmlspecialchars($str, ENT_QUOTES, "");
}



// メッセージのハイパーリンク化
function makeClickableLinks($str)
{
    // URLを検出し、<a>タグで囲んで返す
    return preg_replace_callback(
        '/https?:\/\/[^\s<]+/',
        function ($matches) {
            return '<a href="' . $matches[0] . '" target="_blank">' . $matches[0] . '</a>';
        },
        $str
    );
}
?>





<!-- スレッドタイトル表示 -->
<h2>42ch</h2>



<?php
$thread_idg = $_GET['thread_id'];
$conn = new mysqli(DB_SERVERNAME, DB_USERNAME, DB_PASSWORD, DB_DBNAME);
// SELECTクエリを実行
$sql = "SELECT * from threads WHERE thread_id = $thread_idg";
$result = $conn->query($sql);
// 結果を表示
if ($result->num_rows > 0) {
    // データがある場合
    while ($row = $result->fetch_assoc()) {
        $thread_title = $row['thread_title'];
        echo "<h2>" . $row['thread_title'] . "</h2><br>";
    }
}


?>




<div class="message-box">
    <?php
    // データベースに接続
    $conn = new mysqli(DB_SERVERNAME, DB_USERNAME, DB_PASSWORD, DB_DBNAME);

    // 接続確認
    if ($conn->connect_error) {
        die("データベース接続エラー: " . $conn->connect_error);
    }


    $thread_idg = $_GET['thread_id'];
    // echo $thread_idg;
    
    // SELECTクエリを実行
    $sql = "SELECT * from messages WHERE thread_id = $thread_idg ORDER BY message_id ASC";
    $result = $conn->query($sql);




    // 結果を表示
    if ($result->num_rows > 0) {
        // データがある場合
        while ($row = $result->fetch_assoc()) {
            $user_id_hash;
            if(!($row['user_id'] == 0)){
                $user_id_hash = substr(md5($row['user_id']),0,7);
            }else{
                $user_id_hash = "匿名ユーザ";
            }
            echo "" . $row['message_id'] . " : " . "<span class='username'>" . h($row['user_name']) . "</span>" . "　userID:" . $user_id_hash . "　" . $row['write_timestamp'] . "<br>";
            echo "" . nl2br(makeClickableLinks(h($row['message']))) . "<br>";
            if ($row['image_path'] !== null) {
                $imagePath = IMAGE_FILE_PATH . $row['image_path'];
                echo "<img src='$imagePath' alt='Uploaded Image' style='max-width: 100%; height: 200px;'><br>";
            }
            if ($row['video_path'] !== null) {
                $videoPath = VIDEO_FILE_PATH . $row['video_path'];
                echo "<video controls width='480' height='270' src='$videoPath'></video>";
            }
            echo '<br>';
        }
    } else {
        // データがない場合
        echo "データがありません";
    }

    // データベース接続を閉じる
    $conn->close();
    ?>
</div>




<!DOCTYPE html>
<html lang="ja" dir="ltr">

<script>
    const userAgent = navigator.userAgent;
    if (/iPhone|Android/.test(userAgent)) {
        document.write('<link rel="stylesheet" href="mobile_style.css">');
    } else {
        document.write('<link rel="stylesheet" href="desktop_style.css">');
    }
</script>

<header>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel=”icon” href=“favicon.ico”>

    <title>42ch :
        <?= $thread_title ?>
    </title>
    <style>
        body {
            background-color: #f0e68c;
        }
    </style>
</header>

<body>



    書き込み欄
    <form id="messPost" enctype="multipart/form-data" method="POST">
        <?php echo $anonymous_username; ?><br>
        <!-- <textarea name="user_name_post" value="<?= $_POST['user_name_post'] ?>" placeholder="ユーザ名を入力して下さい"></textarea><br> -->
        <textarea name="message_post" placeholder="メッセージを入力して下さい" style="width : 500px; margin: 10px 0 10px 0;"
            rows="10"></textarea>
        <br>
        画像ファイル<input type="file" name="image_post" accept="image/*" id="file_post"><br> <!-- 画像アップロード -->
        動画ファイル<input type="file" name="video_post" accept="video/*" id="file_post"> <!-- 動画アップロード -->

        <input type="text" value="<?= $_GET['thread_id'] ?>" name="thread_id" hidden />
        <progress id="progressBar" value="0" max="100"></progress><!-- プログレスバー -->
        <span id="progressValue">　</span>

        <input type="submit" value="投稿">
        
        
    </form>

    <!-- index.htmlへ遷移 -->
    <!-- <button onclick="location.href='./login.php'">ログイン</button>
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
    const progressBar = document.getElementById('progressBar');
    const progressValue = document.getElementById('progressValue');

    // プログレスバーの値が変更されたときに発火するイベントリスナーを追加
    progressBar.addEventListener('input', function () {
        // プログレスバーの値を取得
        const value = progressBar.value;
        // ％表示要素を更新
        progressValue.textContent = value + '%';
    });


    myFormElm.addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(myFormElm);

        // フォームが空かチェック
        if (formData.get('message_post') === '') {
            // エラーを表示
            alert('スレッドタイトルを入力してください。');
            return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'thread.php');

        // アップロードの進行状況を監視
        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                progressBar.value = percentComplete;
                progressValue.textContent = percentComplete.toFixed(0) + '%'; // ％を表示する
            }
        });

        // アップロードが完了した際の処理
        xhr.onload = function () {
            if (xhr.status === 200) {
                console.log('ファイルのアップロードが完了しました。');
                window.location.reload(); // ページをリロード
            } else {
                console.error('ファイルのアップロード中にエラーが発生しました。');
                window.location.reload(); // エラーが発生した場合もページをリロード
            }
        };

        // フォームを送信
        xhr.send(formData);
    });

</script>