<?php

class price{
    
    public $link = NULL;
    public $message = NULL;
    public $domain = NULL;
    public $domain_data = NULL;
    public $blanks = [];
    public $active = 0;


    public function write_new($user_id,$link) {
        $this->domain_get($link);
        if(!empty($this->domain)){
            $this->link_add($link, $user_id);
            $sel = db("SELECT * FROM `domines` WHERE `url`=:url", ['url'=>$this->domain]);
            if($sel ->rowCount() > 0){
                $d = $sel -> fetch();
                if($d['active'] > 0 AND !empty($d['blank'])){
                    $this->message .= t('Ready');
                }elseif($d['active'] < 0){
                    switch ($d['active']){
                        case -2:
                            $this->message .= t('Sorry, the store is using bot protection. I cannot find out anything about the product.');
                            break;
                        default :
                            $this->message .= t('Sorry, this is not a store, but a marketplace. We will not support such links.');
                    }
                }else{
                    $this->message .= t('I don\'t know this store yet, but I will soon learn)');
                }
            }else{
                $this->domain_add();
                $this->message .= t('I don\'t know this store yet, but I will soon learn)');
                $u = new User([], $user_id);
                User::send_to_adminns($u, sprintf("%s %s",
                        t('link to a new domain'),
                        $this->domain,
                        //$link,
                        ""));
            }
            
        }else{
            $this->message = t('Sorry, I did not recognize the link');
        }
    }
    
    public function domain($link) {
        $d = $this->domain_get($link);
        $res = FALSE;
        $sel = db("SELECT * FROM `domines` WHERE `url`=:url", ['url'=>$d]);
        if($sel -> rowCount() > 0){
            $this->domain_data = $sel -> fetch();
            $this->blanks = (!empty($this->domain_data['blank']))?explode(",", $this->domain_data['blank']):[];
            $this->active = intval($this->domain_data['active']);
            return $this->domain_data;
        }
        return $res;
    }
    
    private function domain_get($link) {
        $this->link = $link;
        $domain = '';
        $lf = [];
        if(preg_match("/^https?:\/\/([\w\d\.\-\_]{5,300}).*/ui", $link,$lf)){
            $domain = $lf[1];
        }
        $this->domain = $domain;
        return $domain;
    }
    
    private function domain_add() {
        db("INSERT INTO `domines`(`url`) VALUES (:url)", ['url'=>$this->domain]);
    }
    
    private function link_add($link,$user_id) {
        $link_id = 0;
        $sel = db("SELECT * FROM `links` WHERE `link`=:link", ['link'=>$link]);
        if($sel ->rowCount() > 0){
            $l = $sel -> fetch();
            $link_id = $l['id'];
        }else{
            $link_id = db("INSERT INTO `links`(`link`) VALUES (:link)", ['link'=>$link]);
        }
        if($link_id > 0){
            $sel = db("SELECT * FROM `links_user` WHERE `link_id`=:link_id AND `user_id`=:user_id", ['link_id'=>$link_id,'user_id'=>$user_id]);
            if($sel ->rowCount() == 0){
                db("INSERT INTO `links_user`(`link_id`, `user_id`) VALUES (:link_id, :user_id)", ['link_id'=>$link_id,'user_id'=>$user_id]);
            }
        }else{
            $this->message = t('An error has occurred');
        }
    }
    
    public function get_domines() {
        $domains = [];
        $sd = db("SELECT * FROM `domines`");
        while($i = $sd -> fetch()){
            $i['active'] = intval($i['active']);
            $i['blank'] = (!empty($i['blank']))?explode(",",$i['blank']):[];
            $i['info'] = 'Ok';
            if($i['active'] == 0){
                $i['info'] = t('The bot did not learn to analyze the store');
            }
            if($i['active'] == -1){
                $i['info'] = t('This is a marketplace, not a store');
            }
            if($i['active'] == -2){
                $i['info'] = t('The store uses protection from bots');
            }
            $domains[$i['url']] = $i;
        }
        return $domains;
    }
    
    public function get_my_links($user_id) {
        $sel = db("SELECT L.* FROM `links` L, `links_user` U WHERE L.id=U.link_id AND U.user_id=:user_id GROUP BY L.id",['user_id'=>$user_id]);
        $prices = $res = [];
        $domains = $this->get_domines();
        $sp = db("SELECT * FROM `links_price` ORDER BY `tm` DESC");
        while($i = $sp -> fetch()){
            if(!isset($prices[$i['link_id']])){
                $prices[$i['link_id']] = $i;
            }else{
                if($prices[$i['link_id']]['tm'] < $i['tm']){
                    $prices[$i['link_id']] = $i;
                }
            }
        }
        while($i = $sel -> fetch()){
            $i['domain'] = $this->domain_get($i['link']);
            $i['domain_data'] = ifisset($domains, $i['domain'], FALSE);
            $i['id'] = intval($i['id']);
            $i['lu'] = intval($i['lu']);
            $i['lm'] = intval($i['lm']);
            $i['lcode'] = intval($i['lcode']);
            if(isset($prices[$i['id']])){
                $i['price'] = $prices[$i['id']]['price']."\n";
            }else{
                $i['price'] = NULL;
            }
            $i['class'] = [];
            if(empty($i['price'])){
                $i['class'][] = 'noprice';
            }
            if($i['lu'] == 0){
                $i['class'][] = 'noupdate';
            }else{
                /*if($i['lcode'] != 200){
                    $i['class'][] = 'noupdate';
                }*/
            }
            $res[$i['id']] = $i;
        }
        //return implode("\n\n", $res);
        return $res;
    }
    
    public function last_price($link_id) {
        $res = FALSE;
        $s = db("SELECT * FROM `links_price` WHERE `link_id`=:link_id ORDER BY `tm` DESC LIMIT 1", ['link_id'=>$link_id]);
        if($s->rowCount() > 0){
            $d = $s->fetch();
            return $d['price'];
        }
        return $res;
    }
    
    public function get_price() {
        $path = dirname(__DIR__) .'/blanks/';
        $res = [
            'name' => NULL,
            'price' => 0,
        ];
        foreach ($this->blanks as $blank) {
            $fp = $path.$blank.'.tpl.php';
            if(file_exists($fp)){
                $link = $this->link;
                $name = $price = '';
                include($fp);
                if(!empty($name)){
                    $res['name'] = $name;
                }
                if(!empty($price)){
                    $res['price'] = $price;
                    break;
                }
            }
        }
        return $res;
    }
    
    public function write_price($link_id,$price) {
        db("INSERT INTO `links_price`(`link_id`, `price`, `tm`) VALUES (:link_id, :price, :tm)", [
            'link_id'=>$link_id,
            'price'=>$price,
            'tm'=>time(),
        ]);
    }
}