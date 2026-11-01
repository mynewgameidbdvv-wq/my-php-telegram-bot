<?php
// আপনার টেলিগ্রাম বটের টোকেন এখানে বসান
$botToken = "9145012922:AAEgk3A7-zDwe1BlZw8x9TQ1hluCLNTNBlM";
$website = "https://api.telegram.org/bot" . $botToken;

// টেলিগ্রাম থেকে আসা ডেটা রিড করা
$content = file_get_contents("php://input");
$update = json_decode($content, TRUE);

if(!$update) {
    exit;
}

$chatId = $update["message"]["chat"]["id"];
$message = $update["message"]["text"];

// বটের সিম্পল রিপ্লাই লজিক
if($message == "/start") {
    $reply = "স্বাগতম! আপনার টেলিগ্রাম পিএইচপি বট সফলভাবে কাজ করছে।";
} else {
    $reply = "আপনি লিখেছেন: " . $message;
}

// টেলিগ্রাম এপিআই-এর মাধ্যমে মেসেজ পাঠানো
$url = $website . "/sendMessage?chat_id=" . $chatId . "&text=" . urlencode($reply);
file_get_contents($url);
?>
