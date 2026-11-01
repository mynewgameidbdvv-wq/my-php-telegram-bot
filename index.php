<?php
// ডাটা সংরক্ষণ করার জন্য একটি সিম্পল ফাইল বেসড প্যানেল
$configFile = 'bot_config.json';
$codeFile = 'bot_code.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['token']) && isset($_POST['code'])) {
        file_put_contents($configFile, json_encode(['token' => $_POST['token']]));
        file_put_contents($codeFile, $_POST['code']);
        
        // অটো ওয়েবহুক সেটআপ
        $token = $_POST['token'];
        $webhookUrl = "https://" . $_SERVER['HTTP_HOST'] . "/index.php?webhook=1";
        file_get_contents("https://api.telegram.org/bot{$token}/setWebhook?url=" . urlencode($webhookUrl));
        
        $success = "বট সফলভাবে কানেক্ট এবং কোড সেভ হয়েছে!";
    }
}

// টেলিগ্রাম থেকে আপডেট আসলে এই অংশ কাজ করবে
if (isset($_GET['webhook'])) {
    if (file_exists($configFile) && file_exists($codeFile)) {
        $config = json_decode(file_get_contents($configFile), true);
        $GLOBALS['botToken'] = $config['token'];
        include($codeFile);
    }
    exit;
}

$savedToken = '';
$savedCode = '';
if (file_exists($configFile)) {
    $config = json_decode(file_get_contents($configFile), true);
    $savedToken = $config['token'] ?? '';
}
if (file_exists($codeFile)) {
    $savedCode = file_get_contents($codeFile);
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>Telegram PHP Bot Panel</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f9; margin: 0; padding: 20px; }
        .container { max-width: 700px; background: #fff; padding: 20px; margin: auto; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { color: #333; text-align: center; }
        input[type="text"], textarea { width: 100%; padding: 10px; margin: 8px 0 15px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        textarea { height: 200px; font-family: monospace; }
        button { background: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; }
        button:hover { background: #45a049; }
        .success { color: green; text-align: center; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <h2>Telegram PHP Hosting Panel</h2>
    <?php if(isset($success)) echo "<p class='success'>$success</p>"; ?>
    <form method="POST">
        <label>Telegram Bot Token:</label>
        <input type="text" name="token" value="<?php echo htmlspecialchars($savedToken); ?>" placeholder="123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ" required>
        
        <label>Bot PHP Code (এখানে আপনার কোড দিন):</label>
        <textarea name="code" placeholder="<?php echo htmlspecialchars('// উদাহরণ:
$content = file_get_contents("php://input");
$update = json_decode($content, TRUE);
if(isset($update["message"])) {
    $chatId = $update["message"]["chat"]["id"];
    $text = $update["message"]["text"];
    file_get_contents("https://api.telegram.org/bot" . $GLOBALS["botToken"] . "/sendMessage?chat_id=" . $chatId . "&text=" . urlencode("আপনি বলেছেন: " . $text));
}'); ?>"><?php echo htmlspecialchars($savedCode); ?></textarea>
        
        <button type="submit">Save & Connect Bot</button>
    </form>
</div>
</body>
</html>
