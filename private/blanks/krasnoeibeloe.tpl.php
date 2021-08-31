<?php

$link_data = get_url($link);
$link_data = preg_replace("/\r\n|\n/ui", "", $link_data);
if (preg_match("/<title>(.*)купить в.*<\/title>/mui", $link_data, $f)) {
    $name = trim($f[1]);
}
if (preg_match("/\"price\":\"([\d\.\,\s]{1,11})\"/mui", $link_data, $f)) {
    $price = $f[1] . ' ₽';
}