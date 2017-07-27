<?php

namespace EmailCountdown;

class CircleCountdown extends DefaultCountdown
{

    /** @var int */
    private $circleWidth = 100;

    /** @var int */
    private $circleHeight = 100;

    /** @var float */
    private $circleScale = 3.0;

    /** @var array */
    private $circleBackgroundColor = [
        'red'   => 255,
        'green' => 204,
        'blue'  => 204
    ];

    /** @var array */
    private $circleForegroundColor = [
        'red'   => 255,
        'green' => 0,
        'blue'  => 0
    ];

    // cache the circle image so don't have to draw it again
    /** @var resource */
    private $circleImage = null;

    /** @var int */
    private $lastDays = null;

    /** @var int */
    private $lastHours = null;

    /** @var int  */
    private $lastMinutes = null;

    /** @var array */
    private $circleBackgroundColorAll = null;

    /** @var array */
    private $circleForegroundColorAll = null;

    /**
     * set the circle background color
     *
     * @param string $circleBackgroundColor
     * @return $this
     */
    public function setCircleBackgroundColor($circleBackgroundColor)
    {
        if (!empty($circleBackgroundColor) && preg_match('/[0-9a-fA-F]{6}/', $circleBackgroundColor) == 1) {
            $this->circleBackgroundColor = self::convertHexToRGB($circleBackgroundColor);
        }
        return $this;
    }

    /**
     * set the circle foreground color
     *
     * @param string $circleForegroundColor
     * @return $this
     */
    public function setCircleForegroundColor($circleForegroundColor)
    {
        if (!empty($circleForegroundColor) && preg_match('/[0-9a-fA-F]{6}/', $circleForegroundColor) == 1) {
            $this->circleForegroundColor = self::convertHexToRGB($circleForegroundColor);
        }
        return $this;
    }

    /**
     * get the circle image for the fake countdown
     *
     * @param int $days
     * @param int $hours
     * @param int $minutes
     * @param int $seconds
     * @return resource
     */
    private function getCircleImage($days, $hours, $minutes, $seconds)
    {
        if (empty($this->circleImage)) {
            $circle_image_width = $this->width * $this->circleScale;
            $circle_image_height = $this->height * $this->circleScale;

            // create the circle image once
            $this->circleImage = imagecreatetruecolor($circle_image_width, $circle_image_height);

            // background
            $background_color = imagecolorallocate($this->circleImage, $this->backgroundColor['red'],
                $this->backgroundColor['green'], $this->backgroundColor['blue']);
            imagefilledrectangle($this->circleImage, 0, 0, $circle_image_width, $circle_image_height,
                $background_color);

            imagesetthickness($this->circleImage, $this->circleScale * 2);

            $this->circleBackgroundColorAll = imagecolorallocate($this->circleImage,
                $this->circleBackgroundColor['red'], $this->circleBackgroundColor['green'],
                $this->circleBackgroundColor['blue']);
            $this->circleForegroundColorAll = imagecolorallocate($this->circleImage,
                $this->circleForegroundColor['red'], $this->circleForegroundColor['green'],
                $this->circleForegroundColor['blue']);
        }

        $zoomWidth = $this->circleWidth * $this->circleScale;
        $zoomHeight = $this->circleHeight * $this->circleScale;

        // draw seconds circle
        imagearc($this->circleImage, ($zoomWidth / 2) + 900, $zoomHeight / 2, $zoomWidth - 20 * $this->circleScale,
            $zoomHeight - 20 * $this->circleScale, 0, 359.99, $this->circleBackgroundColorAll);
        imagearc($this->circleImage, ($zoomWidth / 2) + 900, $zoomHeight / 2, $zoomWidth - 20 * $this->circleScale,
            $zoomHeight - 20 * $this->circleScale, -90, -90 - (6 * $seconds), $this->circleForegroundColorAll);

        if (empty($this->lastMinutes) || $minutes != $this->lastMinutes) {
            imagearc($this->circleImage, ($zoomWidth / 2) + 600, $zoomHeight / 2,
                $zoomWidth - 20 * $this->circleScale, $zoomHeight - 20 * $this->circleScale, 0, 359.99,
                $this->circleBackgroundColorAll);
            imagearc($this->circleImage, ($zoomWidth / 2) + 600, $zoomHeight / 2,
                $zoomWidth - 20 * $this->circleScale, $zoomHeight - 20 * $this->circleScale, -90, -90 - (6 * $minutes),
                $this->circleForegroundColorAll);
            $this->lastMinutes = $minutes;
        }

        if (empty($this->lastHours) || $hours != $this->lastHours) {
            imagearc($this->circleImage, $zoomWidth / 2 + 300, $zoomHeight / 2, $zoomWidth - 20 * $this->circleScale,
                $zoomHeight - 20 * $this->circleScale, 0, 359.99, $this->circleBackgroundColorAll);
            imagearc($this->circleImage, $zoomWidth / 2 + 300, $zoomHeight / 2, $zoomWidth - 20 * $this->circleScale,
                $zoomHeight - 20 * $this->circleScale, -90, -90 - (15 * $hours), $this->circleForegroundColorAll);
            $this->lastHours = $hours;
        }

        if (empty($this->lastDays) || $days != $this->lastDays) {
            imagearc($this->circleImage, $zoomWidth / 2, $zoomHeight / 2, $zoomWidth - 20 * $this->circleScale,
                $zoomHeight - 20 * $this->circleScale, 0, 359.99, $this->circleBackgroundColorAll);
            imagearc($this->circleImage, $zoomWidth / 2, $zoomHeight / 2, $zoomWidth - 20 * $this->circleScale,
                $zoomHeight - 20 * $this->circleScale, -90, -90 - (1 * $days), $this->circleForegroundColorAll);
            $this->lastDays = $days;
        }

        return $this->circleImage;
    }

    /**
     * {@inheritdoc}
     */
    protected function buildFrame($days, $hours, $minutes, $seconds)
    {
        $frame = $this->createFrame();

        $circle_image = $this->getCircleImage($days, $hours, $minutes, $seconds);

        // copy circle to
        // downsampling
        imagecopyresampled($frame, $circle_image, 0, 0, 0, 0, $this->width, $this->height,
            $this->width * $this->circleScale, $this->height * $this->circleScale);

        return $this->addText($frame, $days, $hours, $minutes, $seconds);
    }
}
