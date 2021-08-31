<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require "/var/www/clients/client0/web4/private/config.php";
$theme = new THEME(TRUE,'index');
session_start();
$lang = filter_input(INPUT_GET, 'lng');
$page = filter_input(INPUT_GET, 'page');
$user = new User([], 0);
if(in_array($lang, ['en','ru'])){
    $_SESSION['lang'] = $lang;
    $user->set_lang($lang);
    if (!empty($page)) {
        header('location: /?page=' . $page);
    } else {
        header('location: /');
    }
    exit();
}
if($user->id > 0){
    $sel_lang = $user->lang;
}else{
    $sel_lang = ifisset($_SESSION, 'lang', 'ru');
}
$lng_tt = new lang_t($sel_lang);
$theme->title(t('@Price free BOT'));
?>
<div class="lng">
<?php
$tolang = 'en';
if($sel_lang == 'en'){
    $tolang = 'ru';
}
$arg = [];
if(!empty($page)){
    $arg[] = 'page='.$page;
}
$arg[] = 'lng='.$tolang;
printf('<a class="lang_%s" href="?%s"></a>', $tolang, implode("&", $arg));

?>
</div>
<?php
    if(empty($page)){
        $page = 'index';
    }
    $page_file = __DIR__ .'/pages/'.$page.'.tpl.php';
    if(file_exists($page_file)){
        include $page_file;
    }else{
        echo html::h1(t('404<br/>Not Found'));
        header("HTTP/1.0 404 Not Found");
        
    }
?>
<div class="center"><a  href="tg://resolve?domain=price_free_bot" class="botlink"><?= t('Open PriceBot in Telegram') ?></a></div>
<?php if($user->id == 0){ ?>
<div class="login"><div><?= t('Authorization via Telegram') ?></div>
    <a href="tg://resolve?domain=price_free_bot&start=auth" class="acl" title="<?= t('Send authorization code') ?>"></a>
    <input type="text" id="ac" placeholder="<?= t('Authorization code') ?>" size="12" maxlength="6"></div>
<?php
}
$theme->create();