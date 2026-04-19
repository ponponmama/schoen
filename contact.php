<?php
declare(strict_types=1);

mb_language("Japanese");
mb_internal_encoding("UTF-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ./contact.html");
    exit;
}

$name = trim((string)($_POST["name"] ?? ""));
$email = trim((string)($_POST["email"] ?? ""));
$message = trim((string)($_POST["message"] ?? ""));

$errors = [];

if ($name === "") {
    $errors[] = "お名前を入力してください。";
} elseif (mb_strlen($name) > 100) {
    $errors[] = "お名前は100文字以内で入力してください。";
}

if ($email === "") {
    $errors[] = "メールアドレスを入力してください。";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "メールアドレスの形式が正しくありません。";
}

if ($message === "") {
    $errors[] = "メッセージを入力してください。";
} elseif (mb_strlen($message) > 3000) {
    $errors[] = "メッセージは3000文字以内で入力してください。";
}

if ($errors !== []) {
    http_response_code(400);
    ?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>入力エラー</title>
    <link rel="stylesheet" href="./assets/css/contact.css">
</head>

<body class="content-body">
    <main class="content-wrapper">
        <h1 class="contact-title">入力内容を確認してください</h1>
        <div class="contact-form-item-wrapper">
            <ul>
                <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?></li>
                <?php endforeach; ?>
            </ul>
            <p><a href="./contact.html">お問い合わせフォームに戻る</a></p>
        </div>
    </main>
</body>

</html>
<?php
    exit;
}

// $to = "schoen.service.0241@gmail.com"; // 本番用
$to = "buti1024@gmail.com"; // テスト送信先
$subject = "【Euro Car Haus Schoen】お問い合わせ";

$mailBody = <<<EOT
お問い合わせを受け付けました。

お名前:
{$name}

メールアドレス:
{$email}

メッセージ:
{$message}
EOT;

$headers = [
    "From: {$email}",
    "Reply-To: {$email}",
    "Content-Type: text/plain; charset=UTF-8",
];

$sent = mb_send_mail($to, $subject, $mailBody, implode("\r\n", $headers));
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>お問い合わせ結果</title>
    <link rel="stylesheet" href="./assets/css/contact.css">
</head>

<body class="content-body">
    <main class="content-wrapper">
        <?php if ($sent): ?>
        <h1 class="contact-title">送信が完了しました</h1>
        <p class="contact-content">お問い合わせありがとうございます。</p>
        <?php else: ?>
        <h1 class="contact-title">送信に失敗しました</h1>
        <p class="contact-content">恐れ入りますが、時間をおいて再度お試しください。</p>
        <?php endif; ?>
        <p><a href="./contact.html">お問い合わせフォームに戻る</a></p>
    </main>
</body>

</html>