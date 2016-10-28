<?php
// include external php-libraries
$loader = require 'vendor/autoload.php';
$loader->add('EmailCountdown', __DIR__ . '/../src/');

$email_countdown = (new EmailCountdown\CircleCountdown())->setDestinationTime(! empty($_GET['dest_time']) ? $_GET['dest_time'] : null)
    ->setTextColor(! empty($_GET['text_color']) ? $_GET['text_color'] : null)
    ->setBackgroundColor(! empty($_GET['background_color']) ? $_GET['background_color'] : null);

// content type gif
header('Content-Type: image/gif');
// no caching of gif, so it gets reloaded every time
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
echo $email_countdown->getGIFAnimation();
