<?php

//$name = $price = '';
if (preg_match("/\/(\d{1,15})\//ui", $link, $lf)) {
    $id = $lf[1];
    $api_data = get_url('https://api.detmir.ru/v2/products/'.$id);
    if($api_data AND $data = json_decode($api_data, TRUE)){
        $item = array_shift($data);
        if($item){
            if(isset($item['title'])){
                $name = $item['title'];
            }
            if(isset($item['price']['price'])){
                $price = $item['price']['price'];
            }
            if(isset($item['price']['currency'])){
                $currency = $item['price']['currency'];
                $price .= ' '.str_replace(['RUB'], ['₽'], $currency);
            }
            /*$count = 0;
            if(isset($item['available']['online']['warehouse_codes'])){
                if(is_array($item['available']['online']['warehouse_codes'])){
                    $count += count($item['available']['online']['warehouse_codes']);
                }
            }
            if(isset($item['available']['offline']['stores'])){
                if(is_array($item['available']['offline']['stores'])){
                    $count += count($item['available']['offline']['stores']);
                }
            }
            if($count == 0){
                $price .= ' - НЕТ В НАЛИЧИИ';
            }*/
        }
    }
}
if(empty($name) AND empty($price)){
    //debug(' - TO MAIN');
    $link_data = get_url($link);
    $link_data = preg_replace("/\r\n|\n/ui", "", $link_data);
    if (preg_match("/<meta name=\"description\" content=\".*цене ([\d\,\.\s]{1,10}) руб.*\">/mui", $link_data, $f)) {
        $price = $f[1] . ' ₽';
    } elseif (preg_match("/<div class=\"As\"><div class=\"At\">([\d\,\.\s]{1,10})(&nbsp;|\s)(₽|.)<\/div><\/div>/mui", $link_data, $f)) {
        $price = $f[1] . " " . $f['3'];
    } elseif (preg_match("/<div class=\"At\">([\d\,\.\s]{1,10})(&nbsp;|\s)(₽|.)<\/div>/mui", $link_data, $f)) {
        $price = $f[1] . " " . $f['3'];
    }
    if (preg_match("/<title>(.*) - купить в.*<\/title>/mui", $link_data, $f)) {
        $name = $f[1];
    }
}