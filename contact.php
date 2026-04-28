<?php
declare(strict_types=1);

mb_language("uni");
mb_internal_encoding("UTF-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ./contact.html", true, 302);
    exit;
}

header("Content-Type: application/json; charset=UTF-8");

$name = trim((string)($_POST["name"] ?? ""));
$email = trim((string)($_POST["email"] ?? ""));
$message = trim((string)($_POST["message"] ?? ""));

$errors = [];
$fieldErrors = [
    "name" => "",
    "email" => "",
    "message" => "",
];

if ($name === "") {
    $errors[] = "お名前を入力してください。";
    $fieldErrors["name"] = "お名前を入力してください。";
} elseif (mb_strlen($name) > 100) {
    $errors[] = "お名前は100文字以内で入力してください。";
    $fieldErrors["name"] = "お名前は100文字以内で入力してください。";
}

if ($email === "") {
    $errors[] = "メールアドレスを入力してください。";
    $fieldErrors["email"] = "メールアドレスを入力してください。";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "メールアドレスの形式が正しくありません。";
    $fieldErrors["email"] = "メールアドレスの形式が正しくありません。";
}

if ($message === "") {
    $errors[] = "メッセージを入力してください。";
    $fieldErrors["message"] = "メッセージを入力してください。";
} elseif (mb_strlen($message) > 3000) {
    $errors[] = "メッセージは3000文字以内で入力してください。";
    $fieldErrors["message"] = "メッセージは3000文字以内で入力してください。";
}

if ($errors !== []) {
    http_response_code(400);
    echo json_encode([
        "ok" => false,
        "message" => "入力内容を確認してください。",
        "errors" => $errors,
        "fieldErrors" => $fieldErrors,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$to = "postmaster@ech-schoen.sakura.ne.jp";
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

$from = "postmaster@ech-schoen.sakura.ne.jp";
$headers = [
    "From: Euro Car Haus Schoen <{$from}>",
    "Reply-To: {$email}",
    "Content-Type: text/plain; charset=UTF-8",
];

$sent = mb_send_mail($to, $subject, $mailBody, implode("\r\n", $headers), "-f{$from}");

if (!$sent) {
    http_response_code(500);
    echo json_encode([
        "ok" => false,
        "message" => "送信に失敗しました。時間をおいて再度お試しください。",
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

echo json_encode([
    "ok" => true,
    "message" => "送信が完了しました。お問い合わせありがとうございます。",
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);