<?php

class lang_t{
    private $lang_select = 'ru';
    private $lang_allow = ['en','ru'];
    private $lang_replace = array();
    private $err = FALSE;
    
    public function __construct($select = 'en') {
        $this->lang_select = $select;
        $this->load();
    }
    
    public function set_lang($select) {
        $this->lang_select = $select;
        $this->load();
    }
    
    private function load() {
        if(!in_array($this->lang_select, $this->lang_allow)){
            $this->lang_select = 'en';
        }
        $this->lang_replace = [];
        $sel = db("SELECT * FROM `lng`");
        while($i = $sel ->fetch()){
            if(!empty($i[$this->lang_select])){
                $this->lang_replace[$i['en']] = $i[$this->lang_select];
            }else{
                $this->lang_replace[$i['en']] = $i['en'];
            }
        }
    }
    
    public function t($text) {
        if(isset($this->lang_replace[$text])){
            return $this->lang_replace[$text];
        }else{
            db("INSERT INTO `lng`(`en`) VALUES (:en)", ['en'=>$text]);
            return $text;
        }
    }
}

$lng_tt = FALSE;

function t($text) {
    global $lng_tt;
    if($lng_tt){
        return $lng_tt->t($text);
    }
    return $text;
}


/*function yt($text,$in,$out){
    $key = 'trnsl.1.1.20180106T205232Z.e33b3dbdc813cb2a.5c90de512e2c834ab07650c7a18340153680703e';
    $d = get_url('https://translate.yandex.net/api/v1.5/tr.json/translate', 'post', [
        'key'=>$key,
        'text'=>$text,
        'lang' => $in.'-'.$out,
        'format'=>'plain',
        'options' => 0,
    ]);
    debug($d);
}*/