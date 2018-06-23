<?php

function getMD5() {
	$str = '';
	$chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ01234567899876543210abcdefghijklmnopqrstuvwxyza9876543210';
    for ($p = 0; $p < 28; $p++) {
        $str .= $chars[mt_rand(0, 92)];
    }
    return md5(uniqid()).$str;
}

function nGradient($from, $to, $n, $max) {
	$rgb1 = array(hexdec(substr($from, 1, 2)), hexdec(substr($from, 3, 2)), hexdec(substr($from, 5, 2)));
	$rgb2 = array(hexdec(substr($to,   1, 2)), hexdec(substr($to,   3, 2)), hexdec(substr($to,   5, 2)));

	$range = ($n < $max) ? $n / $max : 1;
	$rgb = array();
	for ($i = 0; $i < 3; $i++) {
		$diff = $range * (max($rgb1[$i], $rgb2[$i]) - min($rgb1[$i], $rgb2[$i]));
		array_push($rgb, $rgb1[$i] + (($rgb1[$i] > $rgb2[$i]) ? -$diff : $diff));
	}

	return "rgb(".(int)$rgb[0].", ".(int)$rgb[1].", ".(int)$rgb[2].")";
}
