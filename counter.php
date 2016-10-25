<?php
	// include external php-libraries
	require_once 'vendor/autoload.php';

	// default params
	$destination_time = new DateTime();
	$destination_time->modify('+1 day');
	$background_color = 'FFFFFF';
	$text_color = '505050';

	$circle_background_color = 'FFCCCC';
 	$circle_foreground_color = 'FF0000';

	// use GET-parameters
	if (!empty($_GET['dest_time'])) {
		$day = (int)mb_substr($_GET['dest_time'], 0, 2);
		$month = (int)mb_substr($_GET['dest_time'], 2, 2);
		$year = (int)mb_substr($_GET['dest_time'], 4, 4);
		$hour = (int)mb_substr($_GET['dest_time'], 8, 2);
		$minute = (int)mb_substr($_GET['dest_time'], 10, 2);

		if ($day >= 1 && $day <= 31
			&& $month >= 1 && $month <=12
			&& $year >= date('Y') && $year <= date('Y')+1
			&& $hour >= 0 && $hour <= 23
			&& $minute >= 0 && $minute <=59) {

			$destination_time = (DateTime::createFromFormat('d.m.Y H:i', mb_substr($_GET['dest_time'], 0, 2).'.'.mb_substr($_GET['dest_time'], 2, 2).'.'.mb_substr($_GET['dest_time'], 4, 4).' '.mb_substr($_GET['dest_time'], 8, 2).':'.mb_substr($_GET['dest_time'], 10, 2)));
		}
	}
	if (!empty($_GET['background_color'])) {
		$background_color = mb_ereg_replace('[^0-9a-fA-F]', '', $_GET['background_color']);
	}
	if (!empty($_GET['text_color'])) {
		$text_color = mb_ereg_replace('[^0-9a-fA-F]', '', $_GET['text_color']);
	}
	if (!empty($_GET['circle_background_color'])) {
		$circle_background_color = mb_ereg_replace('[^0-9a-fA-F]', '', $_GET['circle_background_color']);
	}
	if (!empty($_GET['circle_foreground_color'])) {
		$circle_foreground_color = mb_ereg_replace('[^0-9a-fA-F]', '', $_GET['circle_foreground_color']);
	}

	$int = hexdec($background_color);
	$background_color_arr = array(
				'red' => 0xFF & ($int >> 0x10),
				'green' => 0xFF & ($int >> 0x8),
				'blue' => 0xFF & $int);

	$int = hexdec($text_color);
	$text_color_arr = array(
				'red' => 0xFF & ($int >> 0x10),
				'green' => 0xFF & ($int >> 0x8),
				'blue' => 0xFF & $int);

	$int = hexdec($circle_background_color);
	$circle_background_color_arr = array(
				'red' => 0xFF & ($int >> 0x10),
				'green' => 0xFF & ($int >> 0x8),
				'blue' => 0xFF & $int);

	$int = hexdec($circle_foreground_color);
	$circle_foreground_color_arr = array(
				'red' => 0xFF & ($int >> 0x10),
				'green' => 0xFF & ($int >> 0x8),
				'blue' => 0xFF & $int);

	$frames = array();
	$current_time = new DateTime();

	// store to cache the circles, don't draw them twice !
	$before_hours = null;
	$before_minutes = null;
	$before_days = null;

	// currently fixed, if changed to dynamic size, don't forget to change the
	// positioniong below!
	$width = 400;
	$height = 100;

	$circle_width = 100;
	$circle_height = 100;

	$scale = 3.0;
	$zoomWidth  = $circle_width  * $scale;
	$zoomHeight = $circle_height * $scale;

	$circle_image_width = $width * $scale;
	$circle_image_height = $height * $scale;

	$circle_image = imagecreatetruecolor($circle_image_width, $circle_image_height);
	// background
	$background = imagecolorallocate($circle_image, $background_color_arr['red'], $background_color_arr['green'], $background_color_arr['blue']);
	imagefilledrectangle($circle_image, 0,0, $circle_image_width, $circle_image_height, $background);
	imagecolortransparent($circle_image, $background);

	imagesetthickness($circle_image, $scale*2);

	$circle_background = imagecolorallocate($circle_image, $circle_background_color_arr['red'], $circle_background_color_arr['green'], $circle_background_color_arr['blue']);
	$circle_foreground = imagecolorallocate($circle_image, $circle_foreground_color_arr['red'], $circle_foreground_color_arr['green'], $circle_foreground_color_arr['blue']);

	for ($i = 0; $i < 60; $i++) {
		if ($current_time > $destination_time) {
			$seconds = $minutes = $hours = $days = 0;
		} else {
			$current_time->modify('+1 second');
			$time_left = $current_time->diff($destination_time, true);
			$seconds = $time_left->s;
			$minutes = $time_left->i;
			$hours = $time_left->h;
			$days = $time_left->days;
		}

		//$curTime = microtime(true);

		// draw seconds circle
		imagearc($circle_image, ($zoomWidth/2)+900,$zoomHeight/2, $zoomWidth-20*$scale,$zoomHeight-20*$scale, 0, 359.99, $circle_background);
		imagearc($circle_image, ($zoomWidth/2)+900,$zoomHeight/2, $zoomWidth-20*$scale,$zoomHeight-20*$scale, 0, 6*$seconds, $circle_foreground);

		if (empty($before_minutes)
			|| $minutes <> $before_minutes) {
			imagearc($circle_image, ($zoomWidth/2)+600,$zoomHeight/2, $zoomWidth-20*$scale,$zoomHeight-20*$scale, 0, 359.99, $circle_background);
			imagearc($circle_image, ($zoomWidth/2)+600,$zoomHeight/2, $zoomWidth-20*$scale,$zoomHeight-20*$scale, 0, 6*$minutes, $circle_foreground);
			$before_minutes = $minutes;
		}

		if (empty($before_hours)
			|| $hours <> $before_hours) {
			imagearc($circle_image, $zoomWidth/2+300,$zoomHeight/2, $zoomWidth-20*$scale,$zoomHeight-20*$scale, 0, 359.99, $circle_background);
			imagearc($circle_image, $zoomWidth/2+300,$zoomHeight/2, $zoomWidth-20*$scale,$zoomHeight-20*$scale, 0, 15*$hours, $circle_foreground);
			$before_hours = $hours;
		}

		if (empty($before_days)
			|| $days <> $before_days) {
			imagearc($circle_image, $zoomWidth/2,$zoomHeight/2, $zoomWidth-20*$scale,$zoomHeight-20*$scale, 0, 359.99, $circle_background);
			imagearc($circle_image, $zoomWidth/2,$zoomHeight/2, $zoomWidth-20*$scale,$zoomHeight-20*$scale, 0, 1*$days, $circle_foreground);
			$before_days = $days;
		}

		$image = imagecreatetruecolor($width, $height);
		imagealphablending($image,true);

		// background color again
		$background = imagecolorallocate($image, $background_color_arr['red'], $background_color_arr['green'], $background_color_arr['blue']);
		imagefilledrectangle($image, 0,0, $width, $height, $background);
  		imagecolortransparent($image, $background);

		// copy circle to
		// downsampling
		imagecopyresampled($image, $circle_image, 0,0, 0,0, $width,$height, $circle_image_width,$circle_image_height);

		$black = imagecolorallocate($image, $text_color_arr['red'], $text_color_arr['green'], $text_color_arr['blue']);
		imagettftext($image, 30, 0, 27, 62, $black, 'fonts/ARIAL.TTF', sprintf('%02d', $days));
		imagettftext($image, 30, 0, 127, 62, $black, 'fonts/ARIAL.TTF', sprintf('%02d', $hours));
		imagettftext($image, 30, 0, 227, 62, $black, 'fonts/ARIAL.TTF', sprintf('%02d', $minutes));
		imagettftext($image, 30, 0, 327, 62, $black, 'fonts/ARIAL.TTF', sprintf('%02d', $seconds));

		imagettftext($image, 7, 0, 38, 75, $black, 'fonts/ARIAL.TTF', 'TAGE');
		imagettftext($image, 7, 0, 126, 75, $black, 'fonts/ARIAL.TTF', 'STUNDEN');
		imagettftext($image, 7, 0, 228, 75, $black, 'fonts/ARIAL.TTF', 'MINUTEN');
		imagettftext($image, 7, 0, 323, 75, $black, 'fonts/ARIAL.TTF', 'SEKUNDEN');

		$frames[] = $image;

		if ($seconds == 0
			&& $minutes == 0
			&& $hours == 0
			&& $days == 0) {
			// we don't need any more frames if already at zero time left
			break;
		}
	    //$timeConsumed = round(microtime(true) - $curTime,3)*1000;
		//error_log(__METHOD__.': '.($i+1).', took '.$timeConsumed.' ms');
	}

	// content type gif
	header('Content-Type: image/gif');
	// no caching of gif, so it gets reloaded every time
	header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Pragma: no-cache');

	// use GIFCreator project to make the gif animation
	$animation = new GifCreator\GifCreator();
	// 100 ticks in gif -> 1 second in realtime
	$gif_ticks = 100;
	$gif_loops = 10;
	$animation->create($frames, array_fill(0, count($frames), $gif_ticks), $gif_loops);

	echo $animation->getGIF();
