# EmailCountdown
Just some simple PHP classes to generate a countdown (60 seconds GIF animation) to be used as fake counter in an email

The image itself works with Outlook, but it shows only the first frame of the GIF animation. 

## Methods class DefaultCountdown
1. setDestinationTime($destination_time) (DateTime-object or format: ddmmyyyyhhmi)
2. setBackgroundColor($background_color) (default: FFFFFF)
3. setTextColor($text_color) (default: 505050)

## Methods class CircleCountdown (extends DefaultCountdown)
1. setCircleBackgroundColor($circle_background_color) (default: FFCCCC)
2. setCircleForegroundColor($circle_foreground_color) (default: FF0000)

## Installation
[Install Composer](https://getcomposer.org) and use it to install dependencies
```
composer require marbie77/emailcountdown
```

## Usage
Create the class, use the options and output the GIF.
```php
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
```
See the _example.php_