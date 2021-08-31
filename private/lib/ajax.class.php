<?php

class AJAX{
    private $message_contaner = '#message';
    private $swindows_contaner = '#initial_window';
    private $awindows_contaner = '#parent_window';
    private $set_var = array();
    private $comands = array();
    
    private function add($action = FALSE,$element = '',$value ='',$title = ''){
        $this->comands[] = array(
            'action'=>$action,
            'element'=>$element,
            'value'=>$value,
            'window'=>$title,
        );
    }
    
    public function show($name){
        $this -> add("show",$name);
    }
    
    public function hide($name){
        $this -> add("hide",$name);
    }
    
    public function set_var($name,$value){
        $this -> add("set_var",$name,$value);
    }
    
    public function message($text){
        $this -> add("message","",$text);
    }
    
    public function alert($text){
        $this -> add("alert","",$text);
    }
    
    public function redirect($value){
        $value = trim($value);
        if(!empty($value)){
            $this -> add("redirect",null,$value);
        }
    }
    
    public function reload(){
        $this -> add("reload",null,null);
    }
    
    public function window($url){
        $this -> add("window","",$url);
    }
    
    public function swindow($url){
        $this -> add("swindow",$this->swindows_contaner,$url);
    }
    
    public function awindow($title,$content){
        $this -> add("awindow",$this->awindows_contaner,$content,$title);
    }
    
    public function awindow_update(){
        $this -> add("awindowupdate",$this->awindows_contaner);
    }
    
    public function fwindow($title,$content){
        $this -> add("fwindow",'body',$content,$title);
    }
    
    public function close_awindow(){
        $this -> add("close_awindow");
    }
    
    public function close_fwindow(){
        $this -> add("close_fwindow");
    }
    
    public function replace($element,$value){
        $element = trim($element);
        $value = trim($value);
        if(!empty($element)){
            $this -> add("replace",$element,$value);
        }else{
            $debug_info = debug_backtrace()[0];
            $this -> add("debug","","Error: empty replace oblect on: ".$debug_info['line']);
        }
    }
    
    public function value($element,$value){
        $element = trim($element);
        $value = trim($value);
        if(!empty($element)){
            $this -> add("value",$element,$value);
        }else{
            $debug_info = debug_backtrace()[0];
            $this -> add("debug","","Error: empty value oblect on: ".$debug_info['line']);
        }
    }
    public function append($element,$value){
        $element = trim($element);
        $value = trim($value);
        if(!empty($element)){
            $this -> add("append",$element,$value);
        }else{
            $debug_info = debug_backtrace()[0];
            $this -> add("debug","","Error: empty append oblect on: ".$debug_info['line']);
        }
    }
    
    public function remove($element){
        $element = trim($element);
        if(!empty($element)){
            $this -> add("remove",$element,"","");
        }else{
            $debug_info = debug_backtrace()[0];
            $this -> add("debug","","Error: empty remove oblect on: ".$debug_info['line']);
        }
    }
    
    public function attribute($element,$name,$value){
        $element = trim($element);
        $this->add("attribute",$element,array($name,$value));
    }
    public function removeAttr($element,$name){
        $element = trim($element);
        $this->add("removeAttr",$element,$name);
    }
    
    public function prop($element,$name,$value){
        $element = trim($element);
        $this->add("prop",$element,array($name,$value));
    }
    
    public function add_class($element,$value){
        $element = trim($element);
        $this->add("class",$element,$value);
    }
    
    public function execute(){
        global $DEBUG,$err_fiedls,$errors,$messages,$infos,$helpes;
        $ob = ob_get_status();
        if(isset($ob['status'])){
            if($ob['status'] == 0){
                ob_clean();
            }
        }
        /*$mess = page::view_messages(TRUE);
        foreach($mess as $m){
            $this -> add('message',$this->message_contaner,$m);
        }*/
        if(count($err_fiedls) > 0){
            foreach($err_fiedls as $field){
                $element = sprintf('*[name=%s]',$field);
                $this -> add_class($element,'error_field');
            }
        }
        
        if(count($errors)>0){
            foreach($errors as $key=>$m){
                if(is_string($m)){
                    $this -> add("message",$this->message_contaner, sprintf('<div class="error"%s>%s</div>',
                            (is_int($key))?'':sprintf(' id="%s"',$key),
                            $m
                            ));
                }else{
                    $this -> add("message",$this->message_contaner, sprintf('<div class="error %s"%s>%s</div>',
                                ($m['noclose'])?'noclose':'',
                                ($m['id'])? sprintf(' id="%s"',$m['id']):'',
                                $m['value']));
                }
            }
        }
        if(count($messages)>0){
            foreach($messages as $key=>$m){
                if(is_string($m)){
                    $this -> add("message",$this->message_contaner, sprintf('<div class="message"%s>%s</div>',
                            (is_int($key))?'':sprintf(' id="%s"',$key),
                            $m
                            ));
                }else{
                    $this -> add("message",$this->message_contaner, sprintf('<div class="message %s"%s>%s</div>',
                                ($m['noclose'])?'noclose':'',
                                ($m['id'])? sprintf(' id="%s"',$m['id']):'',
                                $m['value']));
                }
            }
            /*foreach($messages as $m){
                $this -> add("message",$this->message_contaner, sprintf('<div class="message">%s</div>',$m));
            }*/
        }
        if(count($infos)>0){
            foreach($infos as $m){
                if(is_string($m)){
                    $this -> add("message",$this->message_contaner, sprintf('<div class="info"%s>%s</div>',
                            (is_int($key))?'':sprintf(' id="%s"',$key),
                            $m
                            ));
                }else{
                    $this -> add("message",$this->message_contaner, sprintf('<div class="info %s"%s>%s</div>',
                                ($m['noclose'])?'noclose':'',
                                ($m['id'])? sprintf(' id="%s"',$m['id']):'',
                                $m['value']));
                }
            }
        }
        if(count($helpes)>0){
            foreach($helpes as $m){
                if(is_string($m)){
                    $this -> add("message",$this->message_contaner, sprintf('<div class="help"%s>%s</div>',
                            (is_int($key))?'':sprintf(' id="%s"',$key),
                            $m
                            ));
                }else{
                    $this -> add("message",$this->message_contaner, sprintf('<div class="help %s"%s>%s</div>',
                                ($m['noclose'])?'noclose':'',
                                ($m['id'])? sprintf(' id="%s"',$m['id']):'',
                                $m['value']));
                }
            }
        }
        if(count($DEBUG)>0){
            $this -> add("debug","",$DEBUG);
        }
        print json_encode($this->comands);
    }
    
    static public function exec_confirm($command,$vars = array()) {
        $c = '';
        if(count($vars) == 0){
            $c = 'null';
        }else{
            $i = array();
            foreach ($vars as $name=>$value){
                if(is_bool($value)){
                    if($value){
                        $value = 'true';
                    }else{
                        $value = 'false';
                    }
                }elseif(is_integer($value)){
                    $value = (int)$value;
                }elseif(is_null($value)){
                    $value = 'null';
                }else{
                    $value = "'".$value."'";
                }
                $i[] = sprintf("'%s':%s",$name,$value);
            }
            $c = sprintf("{%s}",implode(",",$i));
        }
        return sprintf("confirm_exec(this,'%s',%s)",
                $command,
                $c);
    }
    
    static public function exec_remote($command,$vars = array()) {
        $c = '';
        if(count($vars) == 0){
            $c = 'null';
        }else{
            $i = array();
            foreach ($vars as $name=>$value){
                if(is_bool($value)){
                    if($value){
                        $value = 'true';
                    }else{
                        $value = 'false';
                    }
                }elseif(is_integer($value)){
                    $value = (int)$value;
                }elseif(is_null($value)){
                    $value = 'null';
                }else{
                    $value = "'".$value."'";
                }
                $i[] = sprintf("'%s':%s",$name,$value);
            }
            $c = sprintf("{%s}",implode(",",$i));
        }
        return sprintf("exec_remote('%s',false,%s)",
                $command,
                $c);
    }
    
    static public function on_event($element = "this",$param,$sub_param = 'false',$execute = 'false',$el_id = 'false') {
        if($element !== "this"){
            $element = "'".$element."'";
        }
        if(!$execute){
            $execute = 'false';
        }else{
            $execute = "'".$execute."'";
        }
        if(!$sub_param){
            $sub_param = 'false';
        }
        return sprintf("set_var(%s,'%s','%s',%s,%s)",
                $element,$param,$sub_param,$execute,$el_id);
    }
    
}
