<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require "/var/www/clients/client0/web4/private/config.php";

$b = new tlgbot();
$b->debug();
$a = $b->get_action();
$c = $b->get_chat();
$user = new User($c);
$lng_tt = new lang_t($user->lang);
$lang_ru = new lang_t('ru');
$p = new price();
/*if($c['type'] == 'group'){
    $b->sendMessage(t('Sorry, group work is not supported.'));
}else*/
if($c['type'] == 'private' OR $c['type'] == 'group'){
    if($user->new){
        $b->sendMessage(t('Hello!').' '.$user->fn.' '.$user->ln);
    }
    switch ($a['type']){
        case 'chat':
            switch($a['text']){
                case 'kicked':
                    if($user->id > 0){
                        db("UPDATE `users` SET `enable`=0,`active`=0 WHERE `id`=:id", ['id'=>$user->id]);
                    }elseif($c['id'] > 0){
                        db("UPDATE `users` SET `enable`=0,`active`=0 WHERE `chat_id`=:chat_id", ['chat_id'=>$c['id']]);
                    }
                    User::send_to_adminns($user, 'Пользователь вышел', $user->lang);
                    break;
                default:
                    User::send_to_adminns($user, 'Неизвестная команда chat: '.$a['text'], $user->lang);
            }
            break;
        case 'link':
            $p->write_new($user->id, $a['text']);
            if(!empty($p->message)){
                $b->sendMessage($p->message);
                $p->message = '';
            }
            break;
        case 'text':
            //$b->sendMessage($a['text']);
            switch($a['text']){
                default :
                    if($c['type'] == 'private'){
                        $user->write_message($a['message_id'], $a['text'], $a);
                        User::send_to_adminns($user, $a['text'], $user->lang);
                        $b->sendMessage(t('I\'m sorry, I did not understand you.'));
                    }
            }
            break;
        case 'command':
            switch($a['text']){
                case 'start auth':
                    $code = '';
                    for($i = 1;$i <= 6;$i++){
                        $code .= random_int(0, 9);
                    }
                    $b->sendMessage(t('Your authorization code:').' '.$code);
                    $b->sendMessage(t('Code is valid for 5 minutes'));
                    db("UPDATE `users` SET `ac`=:ac,`acv`=:acv WHERE `id`=:id",[
                        'ac'=>$code,
                        'acv'=>time() + 60*5,
                        'id'=>$user->id,
                    ]);
                    break;
                case 'menu':
                case 'start':
                    if($user->enable == 0){
                        if($user->id > 0){
                            db("UPDATE `users` SET `enable`=1 WHERE `id`=:id", ['id'=>$user->id]);
                        }elseif($c['id'] > 0){
                            db("UPDATE `users` SET `enable`=1 WHERE `chat_id`=:chat_id", ['chat_id'=>$c['id']]);
                        }
                    }
                    if($user->active == 0){
                        if($user->id > 0){
                            db("UPDATE `users` SET `active`=1 WHERE `id`=:id", ['id'=>$user->id]);
                        }elseif($c['id'] > 0){
                            db("UPDATE `users` SET `active`=1 WHERE `chat_id`=:chat_id", ['chat_id'=>$c['id']]);
                        }
                    }
                    $b->menu();
                    break;
                case 'info':
                    $b->info();
                    break;
                case 'get':
                    $b->link(t('My links'), $user->topage('ml'));
                    break;
                case 'stop':
                    if($user->id > 0){
                        db("UPDATE `users` SET `active`=0 WHERE `id`=:id", ['id'=>$user->id]);
                    }elseif($c['id'] > 0){
                        db("UPDATE `users` SET `active`=0 WHERE `chat_id`=:chat_id", ['chat_id'=>$c['id']]);
                    }
                    $b->sendMessage(t('Price updates will no longer be sent.To resume write /start'));
                    break;
                case 'clear':
                    if($user->id > 0){
                        db("DELETE FROM `links_user` WHERE `user_id`=:user_id", ['user_id'=>$user->id]);
                    }
                    $b->sendMessage(t('All your links have been removed'));
                    break;
                case 'bye':
                    if($user->id > 0){
                        db("DELETE FROM `users` WHERE `id`=:user_id", ['user_id'=>$user->id]);
                        db("DELETE FROM `links_user` WHERE `user_id`=:user_id", ['user_id'=>$user->id]);
                        db("DELETE FROM `messages` WHERE `user_id`=:user_id", ['user_id'=>$user->id]);
                    }
                    $b->sendMessage(t('All your data has been deleted. Come to us again.'));
                    break;
                default :
                    $b->sendMessage(t('Sorry, the command is not defined.'));
            }
            break;
        case 'callback':
            $answer = NULL;
            switch($a['text']){
                case 'info':
                    $b->info();
                    $answer = t('Information');
                    break;
                case 'menu':
                    $b->menu();
                    $answer = t('Menu');
                    break;
                case 'lang':
                    $b->langs();
                    $answer = t('Select language');
                    break;
                case 'lang_en':
                case 'lang_ru':
                    if(preg_match("/^lang_(.*)$/ui", $a['text'],$atf)){
                        $lang = $atf[1];
                        $user->set_lang($lang);
                        $lng_tt->set_lang($lang);
                        $answer = t('Ready');
                    }
                    $b->menu();
                    break;
                default :
                    $b->sendMessage(t('Sorry, the command is not defined.'));
                    $answer = t('Oops');
            }
            if(!empty($answer)){
                $b->callback($a['query_id'],$answer);
            }
            break;
        case 'document':
            $b->sendMessage(t('Sorry, no file uploads required.'));
            break;
        case 'photo':
            $b->sendMessage(t('Sorry, no photo upload required.'));
            break;
        default :
            $b->sendMessage(t('Sorry, I didn\'t understand the command.'));
    }
}else{
    $b->sendMessage(t('Sorry, I did not understand who I am talking to.'));
}