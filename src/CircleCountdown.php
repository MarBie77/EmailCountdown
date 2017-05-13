<?php
namespace EmailCountdown;

class CircleCountdown extends DefaultCountdown
{

    private $_circle_width = 100;

    private $_circle_height = 100;

    private $_circle_scale = 3.0;

    private $_circle_background_color = array(
        'red' => 255,
        'green' => 204,
        'blue' => 204
    );

    private $_circle_foreground_color = array(
        'red' => 255,
        'green' => 0,
        'blue' => 0
    );

    public function setCircleBackgroundColor($circle_background_color)
    {
        if (! empty($circle_background_color) && preg_match('/[0-9a-fA-F]{6}/', $circle_background_color) == 1) {
            $this->_circle_background_color = self::convertHexToRGB($circle_background_color);
        }
        return $this;
    }

    public function setCircleForegroundColor($circle_foreground_color)
    {
        if (! empty($circle_foreground_color) && preg_match('/[0-9a-fA-F]{6}/', $circle_foreground_color) == 1) {
            $this->_circle_foreground_color = self::convertHexToRGB($circle_foreground_color);
        }
        return $this;
    }

    // cache the circle image so don't have to draw it again
    private $_circle_image = null;

    private $_last_days = null;

    private $_last_hours = null;

    private $_last_minutes = null;

    private $_circle_background_color_all = null;

    private $_circle_foreground_color_all = null;

    private function _getCircleImage($days, $hours, $minutes, $seconds)
    {
        if (empty($this->_circle_image)) {
            $circle_image_width = $this->_width * $this->_circle_scale;
            $circle_image_height = $this->_height * $this->_circle_scale;

            // create the circle image once
            $this->_circle_image = imagecreatetruecolor($circle_image_width, $circle_image_height);

            // background
            $background_color = imagecolorallocate($this->_circle_image, $this->_background_color['red'], $this->_background_color['green'], $this->_background_color['blue']);
            imagefilledrectangle($this->_circle_image, 0, 0, $circle_image_width, $circle_image_height, $background_color);

            imagesetthickness($this->_circle_image, $this->_circle_scale * 2);

            $this->_circle_background_color_all = imagecolorallocate($this->_circle_image, $this->_circle_background_color['red'], $this->_circle_background_color['green'], $this->_circle_background_color['blue']);
            $this->_circle_foreground_color_all = imagecolorallocate($this->_circle_image, $this->_circle_foreground_color['red'], $this->_circle_foreground_color['green'], $this->_circle_foreground_color['blue']);
        }

        $zoomWidth = $this->_circle_width * $this->_circle_scale;
        $zoomHeight = $this->_circle_height * $this->_circle_scale;

        // draw seconds circle
        imagearc($this->_circle_image, ($zoomWidth / 2) + 900, $zoomHeight / 2, $zoomWidth - 20 * $this->_circle_scale, $zoomHeight - 20 * $this->_circle_scale, 0, 359.99, $this->_circle_background_color_all);
        imagearc($this->_circle_image, ($zoomWidth / 2) + 900, $zoomHeight / 2, $zoomWidth - 20 * $this->_circle_scale, $zoomHeight - 20 * $this->_circle_scale, 0, 6 * $seconds, $this->_circle_foreground_color_all);

        if (empty($this->_last_minutes) || $minutes != $this->_last_minutes) {
            imagearc($this->_circle_image, ($zoomWidth / 2) + 600, $zoomHeight / 2, $zoomWidth - 20 * $this->_circle_scale, $zoomHeight - 20 * $this->_circle_scale, 0, 359.99, $this->_circle_background_color_all);
            imagearc($this->_circle_image, ($zoomWidth / 2) + 600, $zoomHeight / 2, $zoomWidth - 20 * $this->_circle_scale, $zoomHeight - 20 * $this->_circle_scale, 0, 6 * $minutes, $this->_circle_foreground_color_all);
            $this->_last_minutes = $minutes;
        }

        if (empty($this->_last_hours) || $hours != $this->_last_hours) {
            imagearc($this->_circle_image, $zoomWidth / 2 + 300, $zoomHeight / 2, $zoomWidth - 20 * $this->_circle_scale, $zoomHeight - 20 * $this->_circle_scale, 0, 359.99, $this->_circle_background_color_all);
            imagearc($this->_circle_image, $zoomWidth / 2 + 300, $zoomHeight / 2, $zoomWidth - 20 * $this->_circle_scale, $zoomHeight - 20 * $this->_circle_scale, 0, 15 * $hours, $this->_circle_foreground_color_all);
            $this->_last_hours = $hours;
        }

        if (empty($this->_last_days) || $days != $this->_last_days) {
            imagearc($this->_circle_image, $zoomWidth / 2, $zoomHeight / 2, $zoomWidth - 20 * $this->_circle_scale, $zoomHeight - 20 * $this->_circle_scale, 0, 359.99, $this->_circle_background_color_all);
            imagearc($this->_circle_image, $zoomWidth / 2, $zoomHeight / 2, $zoomWidth - 20 * $this->_circle_scale, $zoomHeight - 20 * $this->_circle_scale, 0, 1 * $days, $this->_circle_foreground_color_all);
            $this->_last_days = $days;
        }

        return $this->_circle_image;
    }

    protected function _buildFrame($days, $hours, $minutes, $seconds)
    {
        $frame = $this->_createFrame();

        $circle_image = $this->_getCircleImage($days, $hours, $minutes, $seconds);

        // copy circle to
        // downsampling
        imagecopyresampled($frame, $circle_image, 0, 0, 0, 0, $this->_width, $this->_height, $this->_width * $this->_circle_scale, $this->_height * $this->_circle_scale);

        return $this->_addText($frame, $days, $hours, $minutes, $seconds);
    }
}
