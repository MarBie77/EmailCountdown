# EmailCountdown
Just some simple PHP classes to generate a countdown (60 seconds GIF animation) to be used as fake counter in an email

The image itself works with Outlook, but it shows only the first frame of the GIF animation. 

## Installation
Use [Composer](https://getcomposer.org) to install it
```
composer require marbie77/emailcountdown
```

## Usage
Create the class, use the options and output the GIF.
```php
$emailCountdown = (new EmailCountdown\CircleCountdown())->setDestinationTime(! empty($_GET['dest_time']) ? $_GET['dest_time'] : null)
    ->setTextColor(! empty($_GET['text_color']) ? $_GET['text_color'] : null)
    ->setBackgroundColor(! empty($_GET['background_color']) ? $_GET['background_color'] : null);

// content type gif
header('Content-Type: image/gif');
// no caching of gif, so it gets reloaded every time
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
echo $emailCountdown->getGIFAnimation();
```

## Examples
To check the examples, first install dependencies with ```composer install```

Then navigate to _example.php_ or _example_wallpoet.php_

## Changelog

2.1.0 
* adding PR for setting max frames from simoheinonen

2.0.1 
* adding new function to customize shown text and position
* adding font Wallpoet-Regular to show additional example (example_wallpoet.php)

2.0.0 
* added PHPDocs and new coding standards i.e. 
    * removed all underscores from properties and methods
    * using camelCase now
* includes PR from @igumenov - thank you!

1.0.0
* Initial version
