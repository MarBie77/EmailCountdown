<?php

namespace EmailCountdown;

use GifCreator\GifCreator;

class DefaultCountdown
{
    /** @var int we create only 60 frames/seconds to create the fake counter */
    const MAX_FRAMES = 60;

    /** @var int currently fixed, if changed to dynamic size, don't forget to change the
     * positioning below! */
    protected $width = 400;

    /** @var int */
    protected $height = 100;

    /** @var string */
    protected $fontFile = __DIR__ . '/../fonts/ARIAL.TTF';

    /** @var int 100 ticks in gif -> 1 second in real time */
    protected $gifTicks = 100;

    /** @var int */
    protected $gifLoops = 10;

    /** @var \DateTime */
    protected $destinationTime = null;

    /** @var array */
    protected $backgroundColor = [
        'red'   => 255,
        'green' => 255,
        'blue'  => 255
    ];

    /** @var array */
    protected $textColor = [
        'red'   => 80,
        'green' => 80,
        'blue'  => 80
    ];


    protected $textData = [
        'days'    => [
            'textSize'       => 32,
            'textPositionX'  => 50,
            'textPositionY'  => 62,
            'label'          => 'TAGE',
            'labelSize'      => 7,
            'labelPositionX' => 52,
            'labelPositionY' => 75
        ],
        'hours'   => [
            'textSize'       => 30,
            'textPositionX'  => 152,
            'textPositionY'  => 62,
            'label'          => 'STUNDEN',
            'labelSize'      => 7,
            'labelPositionX' => 152,
            'labelPositionY' => 75
        ],
        'minutes' => [
            'textSize'       => 30,
            'textPositionX'  => 252,
            'textPositionY'  => 62,
            'label'          => 'MINUTEN',
            'labelSize'      => 7,
            'labelPositionX' => 252,
            'labelPositionY' => 75
        ],
        'seconds' => [
            'textSize'       => 30,
            'textPositionX'  => 352,
            'textPositionY'  => 62,
            'label'          => 'SEKUNDEN',
            'labelSize'      => 7,
            'labelPositionX' => 352,
            'labelPositionY' => 75
        ]
    ];

    /** @var bool */
    protected $showTextLabel = true;

    /**
     * DefaultCountdown constructor.
     * @param \DateTime $destinationTime
     */
    public function __construct($destinationTime = null)
    {
        $this->setDestinationTime($destinationTime);
    }

    /**
     * set text and position for labels and texts
     *
     * @param string $part
     * @param string $label
     * @param int|null $textSize
     * @param int|null $textPositionX
     * @param int|null $textPositionY
     * @param int|null $labelSize
     * @param int|null $labelPositionX
     * @param int|null $labelPositionY
     * @return $this
     */
    public function setTextData(
        string $part,
        string $label = null,
        int $textSize = null,
        int $textPositionX = null,
        int $textPositionY = null,
        int $labelSize = null,
        int $labelPositionX = null,
        int $labelPositionY = null
    ) {
        if (array_key_exists($part, $this->textData)) {
            $this->textData[$part]['label'] = $label ?? $this->textData[$part]['label'];
            $this->textData[$part]['textSize'] = $textSize ?? $this->textData[$part]['textSize'];
            $this->textData[$part]['textPositionX'] = $textPositionX ?? $this->textData[$part]['textPositionX'];
            $this->textData[$part]['textPositionY'] = $textPositionY ?? $this->textData[$part]['textPositionY'];
            $this->textData[$part]['labelSize'] = $labelSize ?? $this->textData[$part]['labelSize'];
            $this->textData[$part]['labelPositionX'] = $labelPositionX ?? $this->textData[$part]['labelPositionX'];
            $this->textData[$part]['labelPositionY'] = $labelPositionY ?? $this->textData[$part]['labelPositionY'];
        }
        return $this;
    }

    /**
     * set the destination time for the fake countdown
     *
     * @param \DateTime $destinationTime
     * @return $this
     */
    public function setDestinationTime($destinationTime)
    {
        if ($destinationTime instanceof \DateTime) {
            $this->destinationTime = $destinationTime;
        } else {
            $datetime = \DateTime::createFromFormat('dmYHi', $destinationTime);
            if (empty($destinationTime) || $datetime === false) {
                $this->destinationTime = new \DateTime();
                $this->destinationTime->modify('+1 day');
            } else {
                $this->destinationTime = $datetime;
            }
        }
        return $this;
    }

    /**
     * set the color for the text labels
     *
     * @param string $textColor must be in hex code i.e. 00ff00
     * @return $this
     */
    public function setTextColor($textColor)
    {
        if (!empty($textColor) && preg_match('/[0-9a-fA-F]{6}/', $textColor) == 1) {
            $this->textColor = self::convertHexToRGB($textColor);
        }
        return $this;
    }

    /**
     * set the background color
     *
     * @param string $backgroundColor must be in hex code i.e. ff0000
     * @return $this
     */
    public function setBackgroundColor($backgroundColor)
    {
        if (!empty($backgroundColor) && preg_match('/[0-9a-fA-F]{6}/', $backgroundColor) == 1) {
            $this->backgroundColor = self::convertHexToRGB($backgroundColor);
        }
        return $this;
    }

    /**
     * convert hex color code to array
     *
     * @param string $color
     * @return array
     */
    static function convertHexToRGB($color)
    {
        $int = hexdec($color);
        return [
            'red'   => 0xFF & ($int >> 0x10),
            'green' => 0xFF & ($int >> 0x8),
            'blue'  => 0xFF & $int
        ];
    }

    /**
     * create a new gif frame image with gd-library
     *
     * @return resource
     */
    protected function createFrame()
    {
        // create frame
        $frame = imagecreatetruecolor($this->width, $this->height);

        // background color again
        $background_color = imagecolorallocate($frame, $this->backgroundColor['red'],
            $this->backgroundColor['green'], $this->backgroundColor['blue']);
        imagefilledrectangle($frame, 0, 0, $this->width, $this->height, $background_color);

        return $frame;
    }

    /**
     * adding texts for each frame
     *
     * @param resource $frame
     * @param int $days
     * @param int $hours
     * @param int $minutes
     * @param int $seconds
     * @return resource
     */
    protected function addText($frame, $days, $hours, $minutes, $seconds)
    {
        $text_color = imagecolorallocate($frame, $this->textColor['red'], $this->textColor['green'],
            $this->textColor['blue']);

        // calculate center of bounding box, so text is centered
        $daysBBox = imagettfbbox($this->textData['days']['textSize'], 0, $this->fontFile, sprintf('%02d', $days));
        $daysPositionX = $this->textData['days']['textPositionX'] - ($daysBBox [4] / 2);

        $hoursBBox = imagettfbbox($this->textData['hours']['textSize'], 0, $this->fontFile, sprintf('%02d', $hours));
        $hoursPositionX = $this->textData['hours']['textPositionX'] - ($hoursBBox [4] / 2);

        $minutesBBox = imagettfbbox($this->textData['minutes']['textSize'], 0, $this->fontFile, sprintf('%02d', $minutes));
        $minutesPositionX = $this->textData['minutes']['textPositionX'] - ($minutesBBox [4] / 2);

        $secondsBBox = imagettfbbox($this->textData['seconds']['textSize'], 0, $this->fontFile, sprintf('%02d', $seconds));
        $secondsPositionX = $this->textData['seconds']['textPositionX'] - ($secondsBBox [4] / 2);

        imagettftext($frame, $this->textData['days']['textSize'], 0, $daysPositionX,
            $this->textData['days']['textPositionY'], $text_color, $this->fontFile,
            sprintf('%02d', $days));
        imagettftext($frame, $this->textData['hours']['textSize'], 0, $hoursPositionX,
            $this->textData['hours']['textPositionY'], $text_color, $this->fontFile, sprintf('%02d', $hours));
        imagettftext($frame, $this->textData['minutes']['textSize'], 0, $minutesPositionX,
            $this->textData['minutes']['textPositionY'], $text_color, $this->fontFile, sprintf('%02d', $minutes));
        imagettftext($frame, $this->textData['seconds']['textSize'], 0, $secondsPositionX,
            $this->textData['seconds']['textPositionY'], $text_color, $this->fontFile, sprintf('%02d', $seconds));

        if ($this->showTextLabel) {

            $daysLabelBBox = imagettfbbox($this->textData['days']['labelSize'], 0, $this->fontFile, $this->textData['days']['label']);
            $daysLabelPositionX = $this->textData['days']['labelPositionX'] - ($daysLabelBBox [4] / 2);

            $hoursLabelBBox = imagettfbbox($this->textData['hours']['labelSize'], 0, $this->fontFile, $this->textData['hours']['label']);
            $hoursLabelPositionX = $this->textData['hours']['labelPositionX'] - ($hoursLabelBBox [4] / 2);

            $minutesLabelBBox = imagettfbbox($this->textData['minutes']['labelSize'], 0, $this->fontFile, $this->textData['minutes']['label']);
            $minutesLabelPositionX = $this->textData['minutes']['labelPositionX'] - ($minutesLabelBBox [4] / 2);

            $secondsLabelBBox = imagettfbbox($this->textData['seconds']['labelSize'], 0, $this->fontFile, $this->textData['seconds']['label']);
            $secondsLabelPositionX = $this->textData['seconds']['labelPositionX'] - ($secondsLabelBBox [4] / 2);

            imagettftext($frame, $this->textData['days']['labelSize'], 0, $daysLabelPositionX,
                $this->textData['days']['labelPositionY'], $text_color,
                $this->fontFile, $this->textData['days']['label']);
            imagettftext($frame, $this->textData['hours']['labelSize'], 0,$hoursLabelPositionX,
                $this->textData['hours']['labelPositionY'],
                $text_color, $this->fontFile, $this->textData['hours']['label']);
            imagettftext($frame, $this->textData['minutes']['labelSize'], 0,
                $minutesLabelPositionX, $this->textData['minutes']['labelPositionY'],
                $text_color, $this->fontFile, $this->textData['minutes']['label']);
            imagettftext($frame, $this->textData['seconds']['labelSize'], 0,
                $secondsLabelPositionX, $this->textData['seconds']['labelPositionY'],
                $text_color, $this->fontFile, $this->textData['seconds']['label']);
        }

        return $frame;
    }

    /**
     * build a new frame (one image in the countdown)
     *
     * @param int $days
     * @param int $hours
     * @param int $minutes
     * @param int $seconds
     * @return resource
     */
    protected function buildFrame($days, $hours, $minutes, $seconds)
    {
        return $this->addText($this->createFrame(), $days, $hours, $minutes, $seconds);
    }

    /**
     * get the fake countdown as gif
     *
     * @return string
     */
    public function getGIFAnimation()
    {
        $frames = [];
        $current_time = new \DateTime();

        for ($i = 0; $i < self::MAX_FRAMES; $i++) {
            if ($current_time > $this->destinationTime) {
                $seconds = $minutes = $hours = $days = 0;
            } else {
                $current_time->modify('+1 second');
                $time_left = $current_time->diff($this->destinationTime, true);
                $seconds = $time_left->s;
                $minutes = $time_left->i;
                $hours = $time_left->h;
                $days = $time_left->days;
            }

            // $curTime = microtime(true);

            $frames[] = $this->buildFrame($days, $hours, $minutes, $seconds);

            // $timeConsumed = round(microtime(true) - $curTime,3)*1000;
            // error_log(__METHOD__.': '.($i+1).', took '.$timeConsumed.' ms');

            if ($seconds == 0 && $minutes == 0 && $hours == 0 && $days == 0) {
                // we don't need any more frames if already at zero time left
                break;
            }
        }

        // use GIFCreator to create the gif animation
        $animation = new GifCreator();

        $animation->create($frames, array_fill(0, count($frames), $this->gifTicks), $this->gifLoops);
        return $animation->getGIF();
    }

    /**
     * use a different true type font file
     *
     * @param string $fontFile
     * @return $this
     */
    public function setFontFile(string $fontFile)
    {
        if (file_exists($fontFile)) {
            $this->fontFile = $fontFile;
        }
        return $this;
    }

    /**
     * hide/show text labels
     *
     * @param bool $showTextLabel
     * @return $this
     */
    public function setShowTextLabel(bool $showTextLabel)
    {
        $this->showTextLabel = $showTextLabel;
        return $this;
    }
}