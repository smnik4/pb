#!/usr/bin/php
<?php

include 'config.php';
if(ob_get_status()){
    ob_end_clean();
}
$lu = [];
$sl = db("SELECT U.id, U.chat_id, U.lang, LU.link_id FROM `links_user` LU, `users` U WHERE U.id=LU.user_id U.enable > 0 AND U.active > 0");
while($i = $sl -> fetch()){
    if(!isset($lu[$i['link_id']])){
        $lu[$i['link_id']] = [];
    }
    if(!isset($lu[$i['link_id']][$i['id']])){
        $lu[$i['link_id']][$i['id']] = [
            'chat_id'=>$i['chat_id'],
            'lang'=>$i['lang'],
        ];
    }
}
$sel = db("SELECT L.* FROM `links` L, `links_user` LU, `users` U WHERE L.id=LU.link_id AND U.id=LU.user_id AND L.lu<:lu GROUP BY L.id LIMIT 30",['lu'=>(time() - 60*60)]);
$p = new price();
$lang_ru = new lang_t('ru');
$lng_tt = new lang_t();
while($link = $sel -> fetch()){
    $d = $p->domain($link['link']);
    if($p->active > 0 AND count($p->blanks) > 0){
        printf("Start %s %s \n",$link['id'],$link['link']);
        $headers = get_headers($link['link'],1);
        $md = FALSE;
        $tmd = intval($link['lm']);
        if (isset($headers['Date'])) {
            $md = strtotime($headers['Date']);
        } elseif (isset($headers['date'])) {
            $md = strtotime($headers['date']);
        }
        if ($md AND $md > $tmd){
            db("UPDATE `links` SET `lm`=:lm WHERE `id`=:id", [
                'lm' => $md,
                'id' => $link['id'],
            ]);
        }elseif($md AND $md <= $tmd){
            continue;
        }
        $cur = $p->last_price($link['id']);
        $new = $p->get_price();
        //continue;
        $cost = floatval(preg_replace(["/[^\d\.]*/ui","/\.\,/ui"], ["",","], $new['price']));
        if($cost == 0){
            User::send_to_adminns(FALSE, sprintf($lang_ru->t('Link does not receive a price').' #'.$link['id'].' '.$link['link']));
        }
        printf(" - COST: %s     PRICE:%s\n",$cost,$new['price']);
        if (empty($link['name']) AND!empty($new['name'])) {
            db("UPDATE `links` SET `name`=:name,`lu`=:lu WHERE `id`=:id", [
                'name' => $new['name'],
                'lu' => time(),
                'id' => $link['id'],
            ]);
            $link['name'] = $new['name'];
        } else {
            db("UPDATE `links` SET `lu`=:lu WHERE `id`=:id", [
                'lu' => time(),
                'id' => $link['id'],
            ]);
        }
        if($cur != $new['price'] AND $cost > 0){
            echo " - write price\n";
            $p->write_price($link['id'], $new['price']);
            if (isset($lu[$link['id']])) {
                foreach ($lu[$link['id']] as $user_id => $user) {
                    $b = new tlgbot($user['chat_id']);
                    $t = 'Product price updated:';
                    if ($user['lang'] == 'ru') {
                        $t = $lang_ru->t($t);
                    }
                    $b->sendMessage($t . "\n" . $link['name'] . "\n<b> -= " . $new['price'] . " =- </b>\n" . $link['link'],
                            ['parse_mode' => 'HTML']);
                }
            }
        }
        sleep(8);
    }
}