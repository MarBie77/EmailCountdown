<?php
namespace EmailCountdown;

class DefaultCountdown
{
    // we create only 60 frames/seconds to create the fake counter
    const MAX_FRAMES = 60;

    // currently fixed, if changed to dynamic size, don't forget to change the
    // positioning below!
    protected $_width = 400;

    protected $_height = 100;

    protected $_font_file = __DIR__ . '/../fonts/ARIAL.TTF';

    // 100 ticks in gif -> 1 second in realtime
    protected $_gif_ticks = 100;

    protected $_gif_loops = 10;

    protected $_destination_time = null;

    protected $_background_color = array(
        'red' => 255,
        'green' => 255,
        'blue' => 255
    );

    protected $_text_color = array(
        'red' => 80,
        'green' => 80,
        'blue' => 80
    );

    protected $_show_text_label = true;

    protected $_text_label = array(
        'days' => 'TAGE',
        'hours' => 'STUNDEN',
        'minutes' => 'MINUTEN',
        'seconds' => 'SEKUNDEN'
    );

    public function __construct($destination_time = null)
    {
        $this->setDestinationTime($destination_time);
    }

    public function setDestinationTime($destination_time)
    {
        if ($destination_time instanceof DateTime) {
            $this->_destination_time = $destination_time;
        } else {
            $datetime = \DateTime::createFromFormat('dmYHi', $destination_time);
            if (empty($destination_time) || $datetime === false) {
                $this->_destination_time = new \DateTime();
                $this->_destination_time->modify('+1 day');
            } else {
                $this->_destination_time = $datetime;
            }
        }
        return $this;
    }

    public function setTextColor($text_color)
    {
        if (! empty($text_color) && preg_match('/[0-9a-fA-F]{6}/', $text_color) == 1) {
            $this->_text_color = self::convertHexToRGB($text_color);
        }
        return $this;
    }

    public function setBackgroundColor($background_color)
    {
        if (! empty($background_color) && preg_match('/[0-9a-fA-F]{6}/', $background_color) == 1) {
            $this->_background_color = self::convertHexToRGB($background_color);
        }
        return $this;
    }

    static function convertHexToRGB($color)
    {
        $int = hexdec($color);
        return array(
            'red' => 0xFF & ($int >> 0x10),
            'green' => 0xFF & ($int >> 0x8),
            'blue' => 0xFF & $int
        );
    }

    protected function _createFrame()
    {
        // create frame
        $frame = imagecreatetruecolor($this->_width, $this->_height);

        // background color again
        $background_color = imagecolorallocate($frame, $this->_background_color['red'], $this->_background_color['green'], $this->_background_color['blue']);
        imagefilledrectangle($frame, 0, 0, $this->_width, $this->_height, $background_color);

        return $frame;
    }

    protected function _addText($frame, $days, $hours, $minutes, $seconds)
    {
        $text_color = imagecolorallocate($frame, $this->_text_color['red'], $this->_text_color['green'], $this->_text_color['blue']);

        imagettftext($frame, 30, 0, 27, 62, $text_color, $this->_font_file, sprintf('%02d', $days));
        imagettftext($frame, 30, 0, 127, 62, $text_color, $this->_font_file, sprintf('%02d', $hours));
        imagettftext($frame, 30, 0, 227, 62, $text_color, $this->_font_file, sprintf('%02d', $minutes));
        imagettftext($frame, 30, 0, 327, 62, $text_color, $this->_font_file, sprintf('%02d', $seconds));

        if ($this->_show_text_label) {
            imagettftext($frame, 7, 0, 38, 75, $text_color, $this->_font_file, $this->_text_label['days']);
            imagettftext($frame, 7, 0, 126, 75, $text_color, $this->_font_file, $this->_text_label['hours']);
            imagettftext($frame, 7, 0, 228, 75, $text_color, $this->_font_file, $this->_text_label['minutes']);
            imagettftext($frame, 7, 0, 323, 75, $text_color, $this->_font_file, $this->_text_label['seconds']);
        }

        return $frame;
    }

    protected function _buildFrame($days, $hours, $minutes, $seconds)
    {
        return $this->_addText($this->_createFrame(), $days, $hours, $minutes, $seconds);
    }

    public function getGIFAnimation()
    {
        $frames = array();
        $current_time = new \DateTime();

        for ($i = 0; $i < self::MAX_FRAMES; $i ++) {
            if ($current_time > $this->_destination_time) {
                $seconds = $minutes = $hours = $days = 0;
            } else {
                $current_time->modify('+1 second');
                $time_left = $current_time->diff($this->_destination_time, true);
                $seconds = $time_left->s;
                $minutes = $time_left->i;
                $hours = $time_left->h;
                $days = $time_left->days;
            }

            // $curTime = microtime(true);

            $frames[] = $this->_buildFrame($days, $hours, $minutes, $seconds);

            // $timeConsumed = round(microtime(true) - $curTime,3)*1000;
            // error_log(__METHOD__.': '.($i+1).', took '.$timeConsumed.' ms');

            if ($seconds == 0 && $minutes == 0 && $hours == 0 && $days == 0) {
                // we don't need any more frames if already at zero time left
                break;
            }
        }

        // use GIFCreator to create the gif animation
        $animation = new \GifCreator\GifCreator();

        $animation->create($frames, array_fill(0, count($frames), $this->_gif_ticks), $this->_gif_loops);
        return $animation->getGIF();
    }
}