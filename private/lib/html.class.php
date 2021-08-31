<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HtmlHelper
 *
 * @author anatoly
 */
class html {

    private static function attributes($a) {
        $str = "";
        foreach ($a as $key => $value) {
            if (is_array($value)) {
                $value = implode(" ", $value);
            }
            $str .= sprintf(' %s="%s"', $key, $value);
        }
        return $str;
    }

    public static function view_date($data, $time = FALSE) {
        if (preg_match("/^\d{2}:\d{2}:\d{2}$/", $time)) {
            $data .= ' ' . $time;
        }
        if (preg_match("/^\d{4}-\d{2}-\d{2}$/", $data)) {
            $data .= ' 10:00:00';
        }
        $date = strtotime($data);
        if ($time) {
            return date("d.m.Y H:i", $date);
        }
        return date("d.m.Y", $date);
    }

    public static function ul($items, $attributes = array()) {
        $html = sprintf("<ul %s>", self::attributes($attributes));
        foreach ($items as $key => $value) {
            $html .= "<li>";
            if (is_array($value)) {
                $html .= self::ul($value);
            } else {
                $html .= $value;
            }
            $html .= "</li>";
        }
        $html .= sprintf("</ul>");
        return $html;
    }

    public static function ol($items, $attributes = array()) {
        $html = sprintf("<ol %s>", self::attributes($attributes));
        foreach ($items as $key => $value) {
            $html .= "<li>";
            if (is_array($value)) {
                $html .= self::ol($value);
            } else {
                $html .= $value;
            }
            $html .= "</li>";
        }
        $html .= sprintf("</ol>");
        return $html;
    }

    public static function a($value, $attr) {
        return self::tag('a', $value, $attr);
    }

    public static function tag($name, $value, $attributes = array()) {
        return sprintf("%s%s%s", self::stag($name, $attributes), $value, self::etag($name));
    }

    private static function stag($name, $attributes) {
        return sprintf("<%s %s>", $name, self::attributes($attributes));
    }

    private static function etag($name) {
        return sprintf("</%s>", $name);
    }

    public static function empty_tag($name, $attributes) {
        return sprintf("<%s %s/>", $name, self::attributes($attributes));
    }

    public static function img($attributes) {
        return self::empty_tag("img", $attributes);
    }

    public static function h($level, $value) {
        return sprintf("<h%s>%s</h%s>", $level, $value, $level);
    }

    public static function h1($value) {
        return self::h(1, $value);
    }

    public static function h2($value) {
        return self::h(2, $value);
    }

    public static function h3($value) {
        return self::h(3, $value);
    }

    public static function p($value) {
        return self::tag('p', $value);
    }

    public static function span($value, $attr = array()) {
        return self::tag('span', $value, $attr);
    }

    public static function pre($value, $attr = array()) {
        return self::tag('pre', $value, $attr);
    }

    public static function print_r($value, $f = FALSE) {
        if (!$f) {
            echo Html::pre(print_r($value, TRUE));
        } else {
            return Html::pre(print_r($value, TRUE));
        }
    }

    /*
     * Элементы заголовков    
     */

    public static function head($value) {
        return self::tag('head', $value);
    }

    public static function title($value) {
        return self::tag('title', $value);
    }

    public static function meta($attributes = array()) {
        return self::stag('meta', $attributes);
    }

    /*
     * Элементы тела страницы    
     */

    public static function header($value, $attribute = array()) {
        return self::tag('header', $value, $attribute);
    }

    public static function footer($value, $attribute = array()) {
        return self::tag('footer', $value, $attribute);
    }

    public static function nav($value, $attribute = array()) {
        return self::tag('nav', $value, $attribute);
    }

    public static function fielset($name, $value, $attributes = array()) {
        return Html::tag('fieldset', Html::tag('legend', $name) . $value, $attributes);
    }

    public static function div($value, $attributes = array()) {
        return self::tag('div', $value, $attributes);
    }

    public static function blockquote($value, $attributes = array()) {
        return self::tag('blockquote', $value, $attributes);
    }

    public static function button($name, $type, $command, $vars = array(), $title = NULL, $confirm = FALSE) {
        $attributes = array(
            'class' => $type,
            'value' => t($name),
            'type' => 'button');
        if (!preg_match("/white|gray|warn/u", $attributes['class'])) {
            $attributes['class'] .= " white";
        }
        if ($confirm) {
            $attributes['onclick'] = AJAX::exec_confirm($command, $vars);
        } else {
            $attributes['onclick'] = AJAX::exec_remote($command, $vars);
        }
        if (!empty($command)) {
            
        }
        if (!empty($title)) {
            $attributes['title'] = $title;
        }
        return self::empty_tag('input', $attributes);
    }

    public static function button_img($name, $type, $command, $vars = array(), $title = NULL, $confirm = FALSE) {
        $attributes = array('class' => 'image ' . $type, 'value' => $name, 'type' => 'button');
        if ($confirm) {
            $attributes['onclick'] = AJAX::exec_confirm($command, $vars);
        } else {
            $attributes['onclick'] = AJAX::exec_remote($command, $vars);
        }
        if (!empty($command)) {
            
        }
        if (!empty($title)) {
            $attributes['title'] = $title;
        }
        return self::empty_tag('input', $attributes);
    }

    public static function table($th, $td, $border = 0, $attributes = array()) {
        if(!isset($attributes['cellspacing'])){
            $attributes['cellspacing'] = '0';
        }
        if(!isset($attributes['cellpadding'])){
            $attributes['cellpadding'] = '4';
        }
        $attributes['border'] = $border;
        $value = '';
        if ($th !== FALSE AND count($th) > 0) {
            $multy = FALSE;
            if (isset($th[0])) {
                $t = $th[0];
                if (is_array($t)) {
                    $multy = TRUE;
                }
            }
            if ($multy) {
                foreach ($th as $t) {
                    $value .= self::stag('tr', array());
                    foreach ($t as $v) {
                        $value .= self::tag('th', $v);
                    }
                    $value .= self::etag('tr');
                }
            } else {
                $value .= self::stag('tr', array());
                foreach ($th as $v) {
                    $value .= self::tag('th', $v);
                }
                $value .= self::etag('tr');
            }
        }
        
        foreach ($td as $row) {
            $attr = array();
            if (isset($row['attributes'])) {
                $attr = $row['attributes'];
                if (isset($row['values'])) {
                    $row = $row['values'];
                } else {
                    unset($row['attributes']);
                }
            }
            $value .= self::stag('tr', $attr);
            foreach ($row as $v) {
                $row_attr = array();
                if (is_array($v)) {
                    if (isset($v['attributes'])) {
                        $row_attr = $v['attributes'];
                    }
                    $v = $v['values'];
                }
                $value .= self::tag('td', $v, $row_attr);
            }
            $value .= self::etag('tr');
        }
        return self::tag('table', $value, $attributes);
    }

    public static function td($value, $attributes = array()) {
        if (count($attributes) > 0) {
            return array('attributes' => $attributes, 'values' => $value);
        } else {
            return $value;
        }
    }

    public static function tr($value, $attributes = array()) {
        if (count($attributes) > 0) {
            return array('attributes' => $attributes, 'values' => $value);
        } else {
            return $value;
        }
    }

    public static function action($type, $command, $vars = array(), $title = NULL, $confirm = FALSE, $custom = FALSE) {
        $attr = array('class' => 'action ' . $type);
        if ($custom) {
            $attr['onclick'] = $custom;
        } elseif ($confirm) {
            $attr['onclick'] = AJAX::exec_confirm($command, $vars);
        } else {
            $attr['onclick'] = AJAX::exec_remote($command, $vars);
        }
        if (!empty($title)) {
            $attr['title'] = $title;
        }
        return self::tag('span', '', $attr);
    }

    /*
     * Элементы формы    
     */

    public static function form($value, $id, $attributes = array()) {
        $attributes['id'] = $id;
        $attributes['method'] = 'POST';
        if(is_array($value)){
            $value = implode("",$value);
        }
        return self::tag('form', $value, $attributes);
    }

    public static function form_item($name, $input, $fix = 1, $help = '', $attr = array(), $inline = FALSE) {
        global $CFG;
        switch ($fix) {
            case 0: $fix_type = '';
                break;
            case 1: $fix_type = 'form_fix';
                break;
            case 2: $fix_type = 'form_fix_free';
                break;
            case 3: $fix_type = 'form_fix_or';
                break;
            default : $fix_type = 'form_fix';
        }
        $for = '';
        if (preg_match("/name=\"([\w\d\-\_]{2,20})\"/ui", $input,$if)){
            $for = $if[1];
        }
        if (preg_match("/type=\"file\"/ui", $input)) {
            $types = 'PDF';
            if (isset($CFG->allowed_file_attach)) {
                $types = mb_strtoupper(implode(", ", $CFG->allowed_file_attach));
            }
            $help .= html::div('<b>Максимальный размер:</b> ' . MFS)
                    . html::div('<b>Допустимые форматы:</b> ' . $types)
                    . html::div('<b>Рекомендуется:</b> сканировние в формат PDF с разрешением не более 300 dpi')
                    . html::div('<b>При сохранении файлов дождитесь окончания отправки файла, форма закроется через несколько секунд</b>');
        }
        if (!empty($help)) {
            $input .= self::div($help, array('class' => 'form_help'));
        }
        $attr['class'] = 'form_item';
        $f = (!empty($fix_type)) ? self::span('*', array('class' => $fix_type)) : '';
        $vars_class = array('form_vars');
        if ($inline) {
            $vars_class[] = 'inline';
        }
        $label_attr = array('class' => 'form_label');
        if(!empty($for)){
            $label_attr['for']=$for;
        }
        return self::div(
                        self::label($name . $f, $label_attr) .
                        self::div($input, array('class' => $vars_class)), $attr);
    }

    public static function label($value, $attributes = array()) {
        return self::tag('label', $value, $attributes);
    }

    public static function input($type, $name, $value, $attributes = array()) {
        $attributes['type'] = $type;
        //$attributes['id']=$name;
        $attributes['name'] = $name;
        if(!isset($attributes['id'])){
            $attributes['id'] = $name;
        }
        $attributes['value'] = $value;
        return self::empty_tag('input', $attributes);
    }

    public static function filter($name, $input, $inline = FALSE) {
        $name_attr = array('class' => 'filter_name');
        $input_attr = array('class' => 'filter_input');
        if ($inline) {
            $name_attr['class'] .= ' inline';
            $input_attr['class'] .= ' inline';
        }
        return self::div($name, $name_attr) . self::div($input, $input_attr);
    }

    public static function filter_input($type, $class_name, $param_name, $execute, $values = array(), $arguments = array()) {
        global $USER;
        $value = NULL;
        if (isset($USER->filter[$class_name][$param_name])) {
            $value = $USER->filter[$class_name][$param_name];
        }
        $html = '';
        foreach ($arguments as $key => $val) {
            $arguments[$key] = sprintf("'%s':'%s'", $key, $val);
        }
        switch ($type) {
            case 'text':
                $html = self::input($type, $class_name . '_' . $param_name, $value, array(
                            'onkeyup' => sprintf('set_var_enter(event,this,\'%s\',\'%s\',\'%s\');', 'filter_' . $class_name, $param_name, $execute),
                            'arg' => '{' . implode(",", $arguments) . '}'
                ));
                if (!empty($value)) {
                    $html .= self::empty_tag('input', array(
                                'value' => '',
                                'type' => 'button',
                                'class' => 'clear',
                                'onclick' => sprintf('set_var(this,\'%s\',\'%s\',\'%s\');', 'filter_' . $class_name, $param_name, $execute),
                                'arg' => '{' . implode(",", $arguments) . '}'
                    ));
                }

                break;
            case 'number':
                $html = self::input($type, $class_name . '_' . $param_name, $value, array(
                            'onkeyup' => sprintf('set_var_enter(event,this,\'%s\',\'%s\',\'%s\');', 'filter_' . $class_name, $param_name, $execute),
                            'class' => 'inline mini',
                            'arg' => '{' . implode(",", $arguments) . '}'
                ));
                break;
            case 'date':
                $html = self::input($type, $class_name . '_' . $param_name, $value, array(
                            'onchange' => sprintf('set_var(this,\'%s\',\'%s\',\'%s\');', 'filter_' . $class_name, $param_name, $execute),
                            'arg' => '{' . implode(",", $arguments) . '}'
                ));
                break;
            case 'select':
                $html = self::select($class_name . '_' . $param_name, $values, $value, array(
                            'onchange' => sprintf('set_var(this,\'%s\',\'%s\',\'%s\');', 'filter_' . $class_name, $param_name, $execute),
                            'class' => 'inline',
                            'arg' => '{' . implode(",", $arguments) . '}'
                                ), 'Все');
                break;
            case 'radios':
                $html = self::radios($class_name . '_' . $param_name, $values, $value, array(
                            'onchange' => sprintf('set_var(this,\'%s\',\'%s\',\'%s\');', 'filter_' . $class_name, $param_name, $execute),
                            'class' => 'inline',
                            'arg' => '{' . implode(",", $arguments) . '}'
                ));
                break;
        }
        return $html;
    }

    public static function text($name, $value = '', $attributes = array()) {
        return self::input('text', $name, $value, $attributes);
    }

    public static function textarea($name, $value, $placeholder = '', $attributes = array()) {
        $attributes['name'] = $name;
        $attributes['cols'] = 59;
        $attributes['rows'] = 5;
        $attributes['placeholder'] = $placeholder;
        return self::tag('textarea', $value, $attributes);
    }

    public static function texthtml($name, $value, $cfg = 0, $lines = 60) {
        $html = sprintf('<textarea name="%s" id="%s" cols="%s">%s</textarea>', $name, $name, $lines, $value);
        switch ($cfg) {
            case 1:
                $cfg = 'editor_config_1';
                break;
            case 2:
                $cfg = 'editor_config_2';
                break;
            default :$cfg = 'null';
        }
        $html .= sprintf('<script>CKEDITOR.replace("%s",%s);</script>', $name, $cfg);
        return $html;
    }

    public static function checkbox($name, $value = '', $title = '', $attributes = array()) {
        $attributes['id'] = 'item_' . $name;
        return self::input('checkbox', $name, $value, $attributes) .
                self::tag('label', ' ' . $title, array('for' => 'item_' . $name));
    }

    public static function checkboxes($name, $values, $selected = 0, $attributes = array(),$inline = false) {
        $html = '';
        $attr_div = array();
        if($inline){
            $attr_div['class'] = 'inline';
        }
        foreach ($values as $key => $value) {
            $attr = array();
            $attr['id'] = 'item' . $key;
            if (is_array($selected)) {
                if (in_array($key, $selected)) {
                    $attr['checked'] = 'checked';
                }
            } elseif (self::is_selected($key, $selected)) {
                $attr['checked'] = 'checked';
            }
            foreach ($attributes as $ak => $av) {
                if (isset($attr[$ak])) {
                    $attr[$ak] = $av;
                }
            }
            $html .= self::div(self::input('checkbox', $name . '[]', $key, $attr) .
                            self::tag('label', ' ' . $value, array('for' => 'item' . $key)),$attr_div);
        }
        return $html;
    }

    public static function radios($name, $values, $selected = 0, $attributes = array(),$inline = false) {
        $html = '';
        $attr_div = array();
        if($inline){
            $attr_div['class'] = 'inline';
        }
        foreach ($values as $key => $value) {
            $attr = array();
            $attr['id'] = $name.'_'.$key;
            if (is_array($selected)) {
                if (in_array($key, $selected)) {
                    $attr['checked'] = 'checked';
                }
            } elseif (self::is_selected($key, $selected)) {
                $attr['checked'] = 'checked';
            }
            foreach ($attributes as $ak => $av) {
                if (!isset($attr[$ak])) {
                    $attr[$ak] = $av;
                }
            }
            $html .= self::div(self::input('radio', $name, $key, $attr) .
                            self::tag('label', ' ' . $value, array('for' => $name.'_'.$key)),$attr_div);
        }
        return $html;
    }

    public static function hidden($name, $value = '', $attributes = array()) {
        return self::input('hidden', $name, $value, $attributes);
    }

    public static function file($name, $value = '', $attributes = array()) {
        return self::input('file', $name, $value, $attributes);
    }

    public static function password($name, $value = '', $attributes = array()) {
        return self::input('password', $name, $value, $attributes);
    }

    public static function submit($title, $form_id, $cancel = FALSE,$type= 'button') {
        $html = self::input($type, 'submit', $title, array(
                    'onclick' => sprintf("save_form('%s',false)", $form_id),
                    'class' => 'white ibutton'
        ));
        if ($cancel !== FALSE) {
            $html .= ' ' . $cancel;
        }
        return self::tag('p', $html, array('class' => 'center'));
    }

    public static function help_form($f1 = TRUE, $f2 = FALSE, $f3 = FALSE) {
        $html = '';
        if ($f1) {
            $html .= ' <span class="form_fix">*</span> - Обязательно к заполнению';
        }
        if ($f2) {
            $html .= ' <span class="form_fix_free">*</span> - Не обязательно к заполнению';
        }
        if ($f3) {
            $html .= ' <span class="form_fix_or">*</span> - Необходимо как минимум заполнить одно';
        }
        return self::div($html, array('class' => 'form_help'));
    }

    public static function select($name, $values, $selected = 0, $attributes = array(), $null = FALSE) {
        $html = '';
        $attributes['id'] = str_replace("[]", "", $name);
        $attributes['name'] = $name;
        if (!empty($null)) {
            $attr_null = array('value' => NULL);
            if (is_array($selected)) {
                if (in_array(0, $selected)) {
                    $attr_null['selected'] = 'selected';
                }
            } elseif (self::is_selected($selected, NULL)) {
                $attr_null['selected'] = 'selected';
            }
            $html .= Html::tag('option', $null, $attr_null);
        }
        foreach ($values as $key => $option) {
            $sel = false;
            if (is_array($selected)) {
                if (in_array($key, $selected)) {
                    $sel = TRUE;
                }
            } elseif (self::is_selected($selected, $key)) {
                $sel = TRUE;
            }
            $attr = array('value' => $key);
            if ($sel) {
                $attr['selected'] = 'selected';
            }
            if (is_array($option)) {
                $html .= sprintf('<optgroup label="%s">', $key);
                foreach ($option as $okey => $group) {
                    $sel = false;
                    if (is_array($selected)) {
                        if (in_array($okey, $selected)) {
                            $sel = TRUE;
                        }
                    } elseif (self::is_selected($selected, $okey)) {
                        $sel = TRUE;
                    }
                    $attr = array('value' => $okey);
                    if ($sel) {
                        $attr['selected'] = 'selected';
                    }
                    $html .= Html::tag('option', $group, $attr);
                }
                $html .= '</optgroup>';
            } else {
                $html .= Html::tag('option', $option, $attr);
            }
        }
        return Html::tag("select", $html, $attributes);
    }

    public static function input_variant($name, $value, $variant_group, $variant_name, $html = '') {
        return html::div(
                        html::input('text', $name, $value, array(
                            'class' => 'inline mini',
                            'readonly' => 'readonly',
                            'title' => 'ПКМ для выбора',
                            'oncontextmenu' => AJAX::on_event('this', $variant_group, $variant_name, 'false', "'" . $name . "'") . ';return false;'
                        )) . $html, array('id' => $name, 'style' => 'position: relative;'));
    }

    public static function form_variant($element, $values, $value) {
        $items = array();
        $items[] = html::hidden('action', 'set_value');
        $items[] = html::hidden('param', 'user_pos_div_variant');
        $items[] = html::hidden('subparam', 'set_variant');
        $items[] = html::hidden('element_name', $element);
        $items[] = html::checkboxes('form_variants', $values, $value);
        $items[] = html::submit('Выбрать', 'edit_form_variant', html::input('button', 'c_form_variant', 'Отмена', array(
                            'onclick' => 'close_form_variant();'
        )));
        return html::div(html::form(implode("", $items), 'edit_form_variant'), array('class' => 'form_variant'));
    }

    public static function action_menu($id, $items, $info,$preview = array()) {
        $id .= "_".rand(1, 999999);
        $pcount = 3;
        $c = count($preview);
        $preview = implode("",$preview);
        if($c < $pcount){
            $a = self::action_preview('null', "");
            $preview .= str_repeat($a, $pcount-$c);
        }
        if (is_array($items)) {
            $items = implode("", $items);
        }
        if (is_array($info)) {
            $info = implode("", $info);
        }
        if (!empty($info)) {
            $items = html::div($info, array('class' => 'action_info')) . $items;
        }
        return html::div(
                        html::div($preview, array(
                            'class' => 'open',
                            'id' => 'action_open_' . $id,
                            'onclick' => sprintf("switch_block('#action_open_%s', '#action_close_%s')", $id, $id))) .
                        html::div(
                                html::div($items, array('class' => "items"))
                                , array(
                            'class' => 'close',
                            'id' => 'action_close_' . $id,
                            'onclick' => sprintf("switch_block('#action_close_%s', '#action_open_%s')", $id, $id)))
                        , array('class' => 'action_menu'));
    }
    
    public static function action_preview($class,$title) {
        $attr = array();
        if(!empty($class)){
            $attr['class']='preview_'.$class;
        }
        if(!empty($title)){
            $attr['title']=$title;
        }
        return self::span("", $attr);
    }
    public static function is_selected($p1, $p2) {
        if (is_null($p1) AND is_null($p2)) {
            return TRUE;
        }
        if (mb_strlen($p1) == 0 AND mb_strlen($p2) == 0 AND ! is_null($p1) AND ! is_null($p2)) {
            return TRUE;
        }
        if (preg_match("/^[\d\,\.]{1,10}$/", $p1) AND preg_match("/^[\d\,\.]{1,10}$/", $p2)) {
            if (intval($p1) === intval($p2) AND ! is_null($p1) AND ! is_null($p2)) {
                return TRUE;
            }
            if (floatval($p1) === floatval($p2) AND ! is_null($p1) AND ! is_null($p2)) {
                return TRUE;
            }
        } else {
            if (strval($p1) === strval($p2) AND ! is_null($p1) AND ! is_null($p2)) {
                return TRUE;
            }
            if ($p1 === $p2 AND ! is_null($p1) AND ! is_null($p2)) {
                return TRUE;
            }
        }
        return FALSE;
    }
}
