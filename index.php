<?php
// সিকিউরিটি সেশন শুরু
session_start();

// আপনার ইচ্ছামতো একটি শক্তিশালী পাসওয়ার্ড এখানে সেট করুন
$admin_password = "Ortho.com"; 

$configFile = 'bot_config.json';
$codeFile = 'bot_code.php';

// লগআউট হ্যান্ডেল
if (isset($_GET['logout'])) {
    unset($_SESSION['logged_in']);
    header("Location: index.php");
    exit;
}

// লগইন হ্যান্ডেল
if (isset($_POST['password'])) {
    if ($_POST['password'] === $admin_password) {
        $_SESSION['logged_in'] = true;
    } else {
        $error = "ভুল পাসওয়ার্ড! আবার চেষ্টা করুন।";
    }
}

// যদি লগইন করা না থাকে, তবে লগইন পেজ দেখাবে
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    ?>
    <!DOCTYPE html>
    <html lang="bn">
    <head>
        <meta charset="UTF-8">
        <title>Login - Telegram Panel</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f4f4f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
            .login-box { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 320px; text-align: center; }
            input[type="password"] { width: 100%; padding: 12px; margin: 15px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 16px; }
            button { background: #4CAF50; color: white; padding: 12px; border: none; border-radius: 4px; width: 100%; cursor: pointer; font-size: 16px; font-weight: bold; }
            button:hover { background: #45a049; }
            .error { color: red; font-size: 14px; margin-bottom: 10px; }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h3>Admin Login</h3>
            <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
            <form method="POST">
                <input type="password" name="password" placeholder="পাসওয়ার্ড দিন" required>
                <button type="submit">লগইন করুন</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// প্যানেল সেভ করার লজিক
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token'])) {
    file_put_contents($configFile, json_encode(['token' => $_POST['token']]));
    file_put_contents($codeFile, $_POST['code']);
    
    // অটো টেলিগ্রাম ওয়েবহুক সেটআপ
    $token = $_POST['token'];
    $webhookUrl = "https://" . $_SERVER['HTTP_HOST'] . "/index.php?webhook=1";
    @file_get_contents("https://api.telegram.org/bot{$token}/setWebhook?url=" . urlencode($webhookUrl));
    
    $success = "বট সফলভাবে কানেক্ট এবং কোড সেভ হয়েছে!";
}

// টেলিগ্রাম থেকে মেসেজ আসলে সেটি প্রসেস করার অংশ
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
    <title>Telegram PHP Hosting Panel</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f9; margin: 0; padding: 20px; }
        .container { max-width: 750px; background: #fff; padding: 25px; margin: auto; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: center; }
        h2 { color: #333; margin: 0; }
        .logout-btn { background: #d9534f; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-size: 14px; }
        .logout-btn:hover { background: #c9302c; }
        input[type="text"], textarea { width: 100%; padding: 12px; margin: 8px 0 15px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 14px; }
        textarea { height: 220px; font-family: monospace; }
        button[type="submit"] { background: #4CAF50; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; font-weight: bold; }
        button[type="submit"]:hover { background: #45a049; }
        .success { color: green; text-align: center; font-weight: bold; margin-bottom: 15px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2>Telegram PHP Hosting Panel</h2>
        <a href="?logout=1" class="logout-btn">লগআউট</a>
    </div>
    <hr style="border: 0; border-top: 1px solid #eee; margin: 15px 0;">
    
    <?php if(isset($success)) echo "<p class='success'>$success</p>"; ?>
    
    <form method="POST">
        <label><b>Telegram Bot Token:</b></label>
        <input type="text" name="token" value="<?php echo htmlspecialchars($savedToken); ?>" placeholder="123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ" required>
        
        <label><b>Bot PHP Code:</b></label>
        <textarea name="code" placeholder="<?php echo htmlspecialchars('// এখানে আপনার কোড লিখুন
$content = file_get_contents("php://input");
$update = json_decode($content, TRUE);
if(isset($update["message"])) {
    $chatId = $update["message"]["chat"]["id"];
    $text = $update["message"]["text"];
    file_get_contents("https://api.telegram.org/bot" . $GLOBALS["botToken"] . "/sendMessage?chat_id=" . $chatId . "&text=" . urlencode("রিপ্লাই: " . $text));
}'); ?>"><?php echo htmlspecialchars($savedCode); ?></textarea>
        
        <button type="submit">Save & Connect Bot</button>
    </form>
</div>
</body>
</html>
