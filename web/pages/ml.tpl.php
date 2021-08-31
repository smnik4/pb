<?php

/* 
 * ссылки пользователя
 */
//$user = new User([], 0);
$p = new price();
$links = $p->get_my_links($user->id);
$rows = [];
///debug($links);
foreach ($links as $link) {
    $name = (!empty($link['name']))?$link['name']:$link['link'];
    $name_attr = [];
    if($link['lu'] > 0){
        $name_attr['href'] = '/?page=ld&l='. md5($link['id']);
    }
    $rows[] = html::tr([
        html::td($name, $name_attr),
        html::td((!empty($link['price']))?$link['price']:'-', ['class'=>'price right']),
        html::td('', ['href'=>$link['link'],'target'=>'_blank','class'=>'link out']),
        html::td('', ['href'=>'/?ac=dl&l='. md5($link['id']),'class'=>'link delete']),
        //html::a($name, ['href'=>$link['link'],'target'=>'_blank']),
    ], ['class'=>$link['class']]);
}
echo html::table([], $rows, 1, ['class'=>'price_list','cellspacing'=>5]);
//debug($links);