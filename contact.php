<?php
declare(strict_types=1);

/**
 * メール送信設定（本番）
 *
 * $mailTo … 問い合わせを届けたい受信アドレス（Gmail 等）
 * $mailFrom … 送信元From（さくらのドメインメール必須。迷惑判定・SMTP認証との整合）
 * Gmail だけ $to にしても SPF/DKIM 未設定だと Gmail 側でブロックされます。
 * さくらのサーバパネルでドメインの SPF / DKIM を有効にしてから運用すること。
 */

/** 主に確認する受信箱（シェーン・Gmail） */
$mailTo = "schoen.service.0241@gmail.com";

/** さくらWebメールにも同文面を送る場合の宛先（任意）。空なら $mailTo のみ。
 *  Note: FromとToが同一になりやすい宛先へ2通目を別送すると捨てられる環境があるため、
 *  宛先はカンマ区切り1通で送る（両方のメールに双方アドレスが見える） */
$mailCcInternal = "postmaster@ech-schoen.sakura.ne.jp";

/** サイトのドメイン上に存在する送信元（postmaster でなくても可） */
$mailFrom = "postmaster@ech-schoen.sakura.ne.jp";

/**
 * @param array<string, mixed> $context
 */
function contactMailDebugLog(string $event, array $context = []): void
{
    $dir = __DIR__ . "/logs";
    if (!is_dir($dir)) {
        @mkdir($dir, 0750, true);
    }
    $payload = array_merge(["event" => $event, "ts" => date("c")], $context);
    $line = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
    @file_put_contents($dir . "/contact-mail.log", $line, FILE_APPEND | LOCK_EX);
}

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

$headerLines = implode("\r\n", [
    "MIME-Version: 1.0",
    "From: Euro Car Haus Schoen <{$mailFrom}>",
    "Reply-To: {$email}",
    "Content-Type: text/plain; charset=UTF-8",
]);

$toEnvelope = $mailTo;
if ($mailCcInternal !== "" && strcasecmp($mailCcInternal, $mailTo) !== 0) {
    $toEnvelope = "{$mailTo}, {$mailCcInternal}";
}

$sent = mb_send_mail($toEnvelope, $subject, $mailBody, $headerLines, "-f{$mailFrom}");

contactMailDebugLog("mb_send_mail", [
    "ok" => $sent,
    "to_envelope" => $toEnvelope,
    "from" => $mailFrom,
    "sendmail_path" => ini_get("sendmail_path") ?: "",
]);

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