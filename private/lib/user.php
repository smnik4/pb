<?php

class User {

    public $id = 0;
    public $new = FALSE;
    public $chat_id = 0;
    public $lang = 'en';
    public $fn = NULL;
    public $ln = NULL;
    public $un = NULL;
    public $enable = TRUE;
    public $active = TRUE;
    public $roles = [];
    public $keyaw = 3600;
    public $ak = 'none';
    public $av = 0;

    public function __construct($chat,$id = 0) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($chat['id'])) {
            $this->chat_id = $chat['id'];
        }elseif ($id > 0) {
            $this->id = $id;
        }elseif(isset($_SESSION['uid'])){
            $this->id = $_SESSION['uid'];
            $page = filter_input(INPUT_GET, 'p');
            if(!empty($page)){
                header('location: /?page='.$page);
            }
        }else{
            $this->auth();
        }
        $this->fn = $fn = ifisset($chat, 'first_name', '');
        $this->ln = $ln = ifisset($chat, 'last_name', '');
        $this->un = $un = ifisset($chat, 'username', '');
        $title = ifisset($chat, 'title', '');
        if($this->id > 0){
            $sel = db("SELECT * FROM `users` WHERE `id`=:id", ['id' => $this->id]);
            if ($sel->rowCount() > 0) {
                $d = $sel->fetch();
                $this->chat_id = intval($d['chat_id']);
                $this->id = intval($d['id']);
                $this->fn = $d['fn'];
                $this->ln = $d['ln'];
                $this->lang = $d['lang'];
                $this->enable = boolval($d['enable']);
                $this->active = boolval($d['active']);
                $this->ak = $d['ak'];
                $this->av = intval($d['av']);
                if (!empty($d['roles'])) {
                    $this->roles = explode(",", $d['roles']);
                }
            }
        }elseif ($this->chat_id != 0) {
            $sel = db("SELECT * FROM `users` WHERE `chat_id`=:chat_id", ['chat_id' => $this->chat_id]);
            if ($sel->rowCount() > 0) {
                $d = $sel->fetch();
                $this->id = intval($d['id']);
                if($fn != $d['fn'] OR $ln != $d['ln'] OR $un != $d['un']){
                    db("UPDATE `users` SET `fn`=:fn,`ln`=:ln,`un`=:un WHERE `id`=:id",[
                        'fn'=>$fn,
                        'ln'=>$ln,
                        'un'=>$un,
                        'id'=>$this->id,
                    ]);
                    $d['fn'] = $fn;
                    $d['ln'] = $ln;
                    $d['un'] = $un;
                }
                $this->fn = $d['fn'];
                $this->ln = $d['ln'];
                $this->un = $d['un'];
                $this->lang = $d['lang'];
                $this->enable = boolval($d['enable']);
                $this->active = boolval($d['active']);
                $this->ak = $d['ak'];
                $this->av = intval($d['av']);
                if (!empty($d['roles'])) {
                    $this->roles = explode(",", $d['roles']);
                }
            } else {
                $this->new = TRUE;
                if($chat['type'] == 'group'){
                    $this->id = db("INSERT INTO `users`(`chat_id`, `fn`, `ln`, `un`) "
                            . "VALUES (:chat_id, :fn, :ln, :un)", [
                        'chat_id' => $this->chat_id,
                        'fn' => $title,
                        'ln' => 'GROUP',
                        'un' => $un,
                    ]);
                    $this->fn = $chat['title'];
                    $this->ln = 'GROUP';
                }else{
                    $this->id = db("INSERT INTO `users`(`chat_id`, `fn`, `ln`, `un`, `lang`) "
                            . "VALUES (:chat_id, :fn, :ln, :un, :lang)", [
                        'chat_id' => $this->chat_id,
                        'fn' => $fn,
                        'ln' => $ln,
                        'un' => $un,
                        'lang' => (isset($chat['lang'])) ? $chat['lang'] : 'en',
                    ]);
                }
            }
        }
    }
    
    private function auth() {
        $auth = filter_input(INPUT_GET, 'auth');
        $type = filter_input(INPUT_GET, 'type');
        $page = filter_input(INPUT_GET, 'p');
        $a = filter_input(INPUT_GET, 'a');
        if(empty($auth) AND !empty($a)){
            $auth = $a;
            $type = 'ak';
        }
        if(!empty($auth)){
            switch ($type){
                case 'code':
                    $sel = db("SELECT `id` FROM `users` WHERE `ac`=:ac AND `acv`>=:acv", ['ac'=>$auth,'acv'=> time()]);
                    if($sel -> rowCount() > 0){
                        $u = $sel -> fetch();
                        db("UPDATE `users` SET `ac`=NULL, `acv`=0 WHERE `id`=:id",['id'=>$u['id']]);
                        $_SESSION['uid'] = $u['id'];
                    }
                    break;
                case 'ak':
                    $sel = db("SELECT `id` FROM `users` WHERE `ak`=:ak AND `av`>=:av", ['ak'=>$auth,'av'=> time()]);
                    if($sel -> rowCount() > 0){
                        $u = $sel -> fetch();
                        db("UPDATE `users` SET `ak`=NULL, `av`=0 WHERE `id`=:id",['id'=>$u['id']]);
                        $_SESSION['uid'] = $u['id'];
                    }
                    break;
            }
            if(!empty($page)){
                header('location: /?page='.$page);
            }else{
                header('location: /');
            }
            exit();
        }
    }

    public function write_message($message_id, $text, $data = NULL) {
        if ($message_id > 0) {
            db("INSERT INTO `messages`(`user_id`, `message_id`, `text`, `data`) "
                    . "VALUES (:user_id, :message_id, :text, :data)", [
                'user_id' => $this->id,
                'message_id' => $message_id,
                'text' => $text,
                'data' => base64_encode(serialize($data)),
            ]);
        }
    }

    public function set_lang($lng) {
        db("UPDATE `users` SET `lang`=:lang WHERE `id`=:id", ['lang' => $lng, 'id' => $this->id]);
    }

    public function topage($page) {
        return sprintf('https://pricebot.smnik.ru/?a=%s&p=%s', $this->auth_key(), urlencode($page));
    }

    private function auth_key() {
        if ($this->ak == 'none' OR $this->av < time()) {
            $this->ak = sha1(time() . md5($this->id));
            $this->av = time() + $this->keyaw;
            db("UPDATE `users` SET `ak`=:ak,`av`=:av WHERE `id`=:id", [
                'ak' => $this->ak,
                'av' => $this->av,
                'id' => $this->id,
            ]);
        }
        return $this->ak;
    }

    static public function get_admins() {
        $chats = [];
        $sel = db("SELECT `chat_id`,`lang` FROM `users` WHERE FIND_IN_SET('admin',`roles`)");
        while ($i = $sel->fetch()) {
            $chats[] = $i;
        }
        return $chats;
    }

    static public function send_to_adminns($user, $text, $lang = 'en') {
        global $lang_ru,$lng_tt;
        $admins = User::get_admins();
        foreach ($admins as $a) {
            $lng = $lng_tt;
            if ($a['lang'] == 'ru') {
                $lng = $lang_ru;
            }
            if($user instanceof User){
                $ut = $user->ln.' '.$user->fn;
                if($lang != $a['lang']){
                    //перевести сообщение с $lang на $a['lang']
                }
            }else{
                $ut = $lng->t('System');
            }
            $sb = new tlgbot($a['chat_id']);
            $sb->sendMessage(sprintf("%s %s:\n%s",
                            $ut,
                            $lng->t('wrote'),
                            $text));
        }
    }

}
