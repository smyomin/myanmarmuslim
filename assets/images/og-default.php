<?php
header('Content-Type: image/png');

$width  = 1200;
$height = 630;
$img    = imagecreatetruecolor($width, $height);

// Background color (Islamic green)
$bgColor   = imagecolorallocate($img, 26, 107, 60);
$textColor = imagecolorallocate($img, 255, 255, 255);
$goldColor = imagecolorallocate($img, 201, 168, 76);

imagefill($img, 0, 0, $bgColor);

// Add text (basic)
$font = 5; // built-in font
imagestring($img, $font, 400, 250, 'Myanmar Muslims', $textColor);
imagestring($img, $font, 350, 290, 'Islamic Online Library', $goldColor);

imagepng($img);
imagedestroy($img);