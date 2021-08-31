<?php

function ifisset($data,$element,$default = NULL){
    if(is_object($data)){
        $data = (array)$data;
    }
    if(isset($data[$element])){
        return $data[$element];
    }else{
        return $default;
    }
}

function view_messages($is_html = TRUE){
    //вывод всех типов сообщений
    global $errors,$messages,$infos,$helpes;
    $res = array();
    $GB = array(
        'error'=>$errors,
        'message'=>$messages,
        'info'=>$infos,
        'help'=>$helpes,
    );
    foreach($GB as $type=>$vars){
        if(count($vars) > 0){
            foreach($vars as $var){
                if(is_string($var)){
                    $res[] = sprintf('<div class="%s">%s</div>',$type,$var);
                }elseif(is_array($var)){
                    if(isset($var['value'])){
                        $noclose = FALSE;
                        $id = '';
                        if(isset($var['noclose'])){
                            $noclose = $var['noclose'];
                        }
                        if(isset($var['id'])){
                            $id = $var['id'];
                        }
                        $res[] = sprintf('<div class="%s%s"%s>%s</div>',
                                $type,
                                ($noclose)?" ".$var['noclose']:"",
                                (!empty($id))? sprintf(' id="%s"',$id):"",
                                $var['value']);
                    }
                }
            }
        }
    }
    if($is_html){
        return implode("",$res);
    }else{
        return $res;
    }
}
