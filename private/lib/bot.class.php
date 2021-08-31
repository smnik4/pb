<?php

class tlgbot {
    private $name = 'Price BOT';
    private $username = '@price_free_bot';
    private $apiurl = 'https://api.telegram.org/bot';
    private $token = '1913046435:AAFikfBb607lhevpMikTJ368gJp_trjpuT8';
    private $input = 0;
    private $chat_id = 0;
    private $chat = NULL;
    private $action = NULL;
    private $dumppath = __DIR__ .'/dump';
    private $debugset = FALSE;

    public function __construct($chat_id = 0) {
        global $CONFIG;
        if(isset($CONFIG['PATH']['temp'])){
            $this->dumppath = $CONFIG['PATH']['temp'].'/bot/';
            if(!file_exists($this->dumppath)){
                mkdir($this->dumppath);
            }
        }
        
        if($chat_id != 0){
            $this->chat_id = $chat_id;
        }
        if($chat_id == 0){
            $data = file_get_contents('php://input');
            $this->input = json_decode($data, true);
            $this->chat = $this->chat_data();
            $this->action = $this->action_data();
        }
        file_put_contents($this->dumppath . time() . '_inputs.text', print_r([
                'INPUT' => json_decode(file_get_contents('php://input'))
            ], true));
    }
    
    public function get_chat_id() {
        return $this->chat_id;
    }
    
    public function get_chat() {
        return $this->chat;
    }
    
    public function get_action() {
        return $this->action;
    }
    
    private function action_data() {
        $res = [
            'message_id'=>0,
            'query_id'=>0,
            'type'=>NULL,
            'text'=>NULL,
            'data'=>NULL,
            'lang'=>'en',
        ];
        if(isset($this->input['message']['message_id'])){
            $res['message_id'] = $this->input['message']['message_id'];
        }
        if(isset($this->input['message']['from']['language_code'])){
            $res['lang'] = $this->input['message']['from']['language_code'];
            /*if(in_array($res['lang'], ['en','ru'])){
                
            }*/
        }
        if(isset($this->input['message']['text'])){
            $res['text'] = $this->input['message']['text'];
            $res['type'] = 'text';
        }
        if(isset($this->input['message']['entities'])){
            $e = $this->input['message']['entities'];
            $e = array_shift($e);
            if(isset($e['type'])){
                if($e['type'] == 'bot_command'){
                    $res['type'] = 'command';
                }elseif($e['type'] == 'url'){
                    $res['type'] = 'link';
                }
            }
            $tf = [];
            if(preg_match("/^\/(.*)$/ui", $res['text'],$tf)){
                $res['text'] = str_replace($this->username, "", $tf[1]);
                if($res['type'] !== 'command'){
                    $res['type'] = 'command';
                }
            }
        }
        if(isset($this->input['message']['document'])){
            $res['type'] = 'document';
            $res['data'] = $this->input['message']['document'];
            if(isset($this->input['message']['caption'])){
                $res['text'] = $this->input['message']['caption'];
            }
        }
        if(isset($this->input['message']['photo'])){
            $res['type'] = 'photo';
            $res['data'] = $this->input['message']['photo'];
            if(isset($this->input['message']['caption'])){
                $res['text'] = $this->input['message']['caption'];
            }
        }
        if(isset($this->input['my_chat_member']['new_chat_member']['status'])){
            $res['type'] = 'chat';
            $res['text'] = $this->input['my_chat_member']['new_chat_member']['status'];
            $res['data'] = $this->input['my_chat_member'];
        }
        if(isset($this->input['callback_query']['message'])){
            $res['type'] = 'callback';
            $res['query_id'] = $this->input['callback_query']['id'];
            $d = $this->input['callback_query']['data'];
            $res['data'] = json_decode($d, TRUE);
            $res['text'] = $res['data']['action'];
        }
        if($res['type'] == 'text' AND preg_match("/^http(s)?:\/\/.*$/ui", $res['text'])){
            $res['type'] = 'link';
        }
        return $res;
    }
    
    private function chat_data() {
        if(isset($this->input['message']['chat'])){
            $this->chat_id = $this->input['message']['chat']['id'];
            return $this->input['message']['chat'];
        }elseif(isset($this->input['my_chat_member']['chat'])){
            $this->chat_id = $this->input['my_chat_member']['chat']['id'];
            return $this->input['my_chat_member']['chat'];
        }elseif(isset($this->input['callback_query']['message']['chat'])){
            $this->chat_id = $this->input['callback_query']['message']['chat']['id'];
            return $this->input['callback_query']['message']['chat'];
        }
        return FALSE;
    }
    
    public function debug() {
        $this->debugset = TRUE;
    }
    
    public function info() {
        $c = "<b>".$this->name."</b>\n";
        $c .= t("Send me a link to the product and I will start tracking the price for it, if there are any changes, I will definitely inform you about it, if I do not know the store with the goods, I will definitely figure it out soon and I will also inform you)");
        $keys = [];
        $keys[] = ['text' => t('Menu'), 'callback_data' => json_encode(['action' => 'menu'])];
        $attr = ['parse_mode' => 'HTML'];
        if (count($keys) > 0) {
            $attr['disable_web_page_preview'] = false;
            $attr['reply_markup'] = json_encode(array('inline_keyboard' => [$keys]));
        }
        $this->sendMessage($c, $attr);
    }
    
    public function menu() {
        global $user;
        $keyboard = array(
            array(
                array('text' => t('Information'), 'callback_data' => '{"action":"info"}'),
                array('text' => t('Language'), 'callback_data' => '{"action":"lang"}'),
            ),
            array(
                array('text' => t('My links'), 'url' =>$user->topage('ml')),
            )
        );
        $this->sendMessage(t('Choose an action'), [
            'disable_web_page_preview' => false,
            'reply_markup' => json_encode(array('inline_keyboard' => $keyboard))
        ]);
    }
    
    public function langs() {
        $keyboard = array(
            array(
                array('text' => t('English'), 'callback_data' => '{"action":"lang_en"}'),
                array('text' => t('Russian'), 'callback_data' => '{"action":"lang_ru"}'),
            )
        );
        $this->sendMessage(t('Choose an action'), [
            'disable_web_page_preview' => false,
            'reply_markup' => json_encode(array('inline_keyboard' => $keyboard))
        ]);
    }
    
    public function link($text,$link) {
        $keyboard = array(
            array(
                array('text' => $text, 'url' =>$link),
            )
        );
        $this->sendMessage(t('Follow the link'), [
            'disable_web_page_preview' => false,
            'reply_markup' => json_encode(array('inline_keyboard' => $keyboard))
        ]);
    }
    
    public function sendMessage($message, $params = array(),$reply_id = 0) {
        if($this->chat_id == 0){
            return FALSE;
        }
        $response = array(
            'chat_id' => $this->chat_id,
            'text' => $message
        );
        if($reply_id > 0){
            $response['reply_to_message_id'] = $reply_id;
        }
        if (count($params) > 0) {
            foreach ($params as $k => $i) {
                $response[$k] = $i;
            }
        }
        $this->request('sendMessage', $response);
    }
    
    public function callback($qid,$text) {
        if($qid > 0){
            $this->request("answerCallbackQuery", array(
                        'callback_query_id' => $qid,
                        'text' => $text,
                    ));
        }
        
    }

    private function request($method, $params = array(),$type='POST') {
        $ch = curl_init('https://api.telegram.org/bot' . $this->token . '/' . $method);
        if($type === 'POST'){
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $res = curl_exec($ch);
        $ansver = json_decode($res, TRUE);
        if($this->debugset){
            $info = curl_getinfo($ch);
            file_put_contents($this->dumppath . time() . '_outdata.text', print_r($params, true));
            file_put_contents($this->dumppath . time() . '_outinfo.text', print_r($info, true));
            file_put_contents($this->dumppath . time() . '_outansver.text', print_r($ansver, true));
        }
        curl_close($ch);
        return $ansver;
    }
    
    public function __destruct() {
        if($this->debugset){
            file_put_contents($this->dumppath . time() . '_input.text', print_r([
                'INPUT' => $this->input,
                'CHAT' => $this->get_chat(),
                'ACTION' => $this->get_action(),
            ], true));
        }
    }
}
