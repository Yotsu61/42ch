<?php
require_once(dirname(__FILE__) ."/secret.php");



error_reporting(E_ALL);

$max_message_id = 0;


// $thread_idg = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = $_POST['message_post'];
    $thread_id = (int) $_POST['thread_id'];
    $user_name = $_POST['user_name_post'];
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
        move_uploaded_file($image_tmp_name, '../uploads/' . $image_name);

        // これで、$image_nameを必要に応じてデータベースクエリで使用したり保存できます。
    }

    # connect mysql PDO
    $dsn = 'mysql:dbname=' . DB_DBNAME . ';host=' . DB_SERVERNAME;
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD);


    // $sql = $pdo->prepare("INSERT INTO messages (`thread_id`, `user_id`, `message`,`user_name`,`write_timestamp`,`good_cnt`,`bat_cnt`,`message_id`) VALUES (:thread_id,'anonymous',:message,:user_name,now(),0,0,:message_id)");
    // SQLクエリを変更して、アップロードされた画像ファイル名を含めるようにする
    $sql = $pdo->prepare("INSERT INTO messages (`thread_id`, `user_id`, `message`,`user_name`,`write_timestamp`,`good_cnt`,`bat_cnt`,`message_id`,`image_path`) VALUES (:thread_id, 'anonymous', :message, :user_name, now(), 0, 0, :message_id, :image_path)");

    // bindParamで:nameなどを変数$nameに設定、PARAM_でデータ型を指定
    $sql->bindParam(':thread_id', $thread_id, PDO::PARAM_INT);
    $sql->bindParam(':message', $message, PDO::PARAM_STR);
    $sql->bindParam(':user_name', $user_name, PDO::PARAM_STR);
    $sql->bindParam(':message_id', $max_message_id, PDO::PARAM_INT);
    // パラメータをバインド
    $sql->bindParam(':image_path', $image_name, PDO::PARAM_STR); // アップロードされた場合はここで画像ファイル名を使用


    $res = $sql->execute();

    //デバッグ用
    if ($res === false) {
        echo "エラー: " . $sql->errorInfo()[2]; // エラーを表示
    } else {
        echo "データベースに挿入されました。";
    }
    $message = $_POST['message_post'];
    var_dump($message); // デバッグ用




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
function makeClickableLinks($str) {
    // URLを検出し、<a>タグで囲んで返す
    return preg_replace_callback('/https?:\/\/[^\s<]+/',
        function($matches) {
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
            echo "" . $row['message_id'] . " " . "<span class='username'>" . h($row['user_name']) . "</span>" . " : " . $row['write_timestamp'] . "<br>";
            echo "" . nl2br(makeClickableLinks(h($row['message']))) . "<br>";
            if ($row['image_path'] !== null) {
                $imagePath = '../uploads/' . $row['image_path'];
                echo "<img src='$imagePath' alt='Uploaded Image' style='max-width: 100%; height: 200px;'><br>";
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




<!DOCTYPE html>
<html lang="ja" dir="ltr">

<head>
    <meta charset="utf-8">
    <title>メッセージ作成フォーム</title>
    <style>
        body {
            background-color: #f0e68c;
        }

        .message-box {
            background-color: #c0c0c0;
            border: 1px solid #ccc;
            margin: 10px;
            padding: 10px;

            border: 1px solid black;
        }

        .username {
            color: green;
        }

        .separator {
            color: black;
        }

        .timestamp {
            color: black;
        }
    </style>
</head>

<body>



    フォーム
    <form id="messPost" enctype="multipart/form-data" method="POST">
        <textarea name="user_name_post" value="<?= $_POST['user_name_post']?>" placeholder="ユーザ名を入力して下さい"></textarea><br>
        <textarea name="message_post" placeholder="メッセージを入力して下さい" style="width : 500px; margin: 10px 0 10px 0;"
            rows="10"></textarea>
        <input type="file" name="image_post" accept="image/*"> <!-- 画像アップロードのために追加 -->
        <input type="text" value="<?= $_GET['thread_id'] ?>" name="thread_id" hidden />
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
        if (formData.get('user_name_post') === '' || formData.get('message_post') === '') {
            // エラーを表示
            alert('スレッドタイトルを入力してください。');
            return;
        }



        // // メッセージ内のURLを自動的にハイパーリンク化する処理を追加
        // const message = formData.get('message_post');
        // const urlRegex = /(https?:\/\/[^\s]+)/g;
        // const urls = message.match(urlRegex);
        // if (urls) {
        //     for (const url of urls) {
        //         const linkedMessage = message.replace(url, `<a href="${url}" target="_blank">${url}</a>`);
        //         formData.set('message_post', linkedMessage);
        //     }
        // }



        // フォームを送信
        fetch('thread.php', {
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