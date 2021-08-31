<?php

class THEME {

    private $template = '';
    private $title = '';
    private $vars = array();
    private $replace = array();
    private $access = TRUE;

    public function __construct($cache = TRUE,$tamplate = 'theme') {
        $thp = dirname(__DIR__) . "/html/".$tamplate.".tpl.html";
        if (file_exists($thp)) {
            $this->template = file_get_contents($thp);
            $this->get_vars();
            $this->get_meta();
            if ($cache) {
                $this->get_cache();
            } else {
                $this->get_css();
                $this->get_js();
            }
            $this->get_local_script_ajax();
        }
    }

    private function get_vars() {
        if (preg_match_all("/\[\[(\w*)\]\]/", $this->template, $f)) {
            $this->vars = $f[0];
            foreach ($f[1] as $v) {
                $this->replace[$v] = "";
            }
        }
    }

    public function create() {
        global $lng_tt;
        $lng_tt = NULL;
        $this->filter();
        $body = ob_get_clean();
        if ($this->access) {
            $this->add("body", $body);
        } else {
            $this->title("Не достаточно прав");
        }
        $this->get_message();
        $this->update_image_path();
        $this->get_debug();
        echo str_replace($this->vars, $this->replace, $this->template);
    }

    public function access($val = TRUE) {
        $this->access = $val;
    }

    public function title($text = NULL, $add = FALSE) {
        global $CONFIG;
        if (!empty($text)) {
            if (!$add) {
                $this->title = $text;
                $this->set("header", '<a href="/"><h1>'.$text.'</h1></a>');
                if (isset($CONFIG['name'])) {
                    $text .= " | " . $CONFIG['name'];
                } else {
                    set_error("Site name not in config.ini");
                }
            } else {
                $this->set("header", $text);
                $c = $this->get("title");
                if (!empty($c)) {
                    $text .= " | " . $c;
                }
            }
            $this->set("title", $text);
        }
    }

    public function action($text = NULL) {
        if (!empty($text)) {
            $this->set("ACTION", $text);
        }
    }

    public function attention($text = '') {
        if (!empty($text)) {
            $this->set("ATTENTION", $text);
        }
    }

    public function menu($text = NULL) {
        if (!empty($text)) {
            $this->set("menu", $text);
        }
    }

    private function get_meta() {
        $meta = [];
        $meta[] = '<meta charset="utf-8" />';
        $meta[] = '<link rel="manifest" href="/manifest.json">';
        $meta[] = '<meta name="viewport" content="width=device-width, initial-scale=1" />';
        $meta[] = '<meta name="description" content="'.t('Free price tracker').'" />';
        $meta[] = '<link rel="apple-touch-icon" sizes="57x57" href="/img/apple-icon-57x57.png">
            <link rel="apple-touch-icon" sizes="60x60" href="/img/apple-icon-60x60.png">
            <link rel="apple-touch-icon" sizes="72x72" href="/img/apple-icon-72x72.png">
            <link rel="apple-touch-icon" sizes="76x76" href="/img/apple-icon-76x76.png">
            <link rel="apple-touch-icon" sizes="114x114" href="/img/apple-icon-114x114.png">
            <link rel="apple-touch-icon" sizes="120x120" href="/img/apple-icon-120x120.png">
            <link rel="apple-touch-icon" sizes="144x144" href="/img/apple-icon-144x144.png">
            <link rel="apple-touch-icon" sizes="152x152" href="/img/apple-icon-152x152.png">
            <link rel="apple-touch-icon" sizes="180x180" href="/img/apple-icon-180x180.png">
            <link rel="icon" type="image/png" sizes="192x192"  href="/img/android-icon-192x192.png">
            <link rel="icon" type="image/png" sizes="32x32" href="/img/favicon-32x32.png">
            <link rel="icon" type="image/png" sizes="96x96" href="/img/favicon-96x96.png">
            <link rel="icon" type="image/png" sizes="16x16" href="/img/favicon-16x16.png">
            <link rel="manifest" href="/manifest.json">
            <meta name="msapplication-TileColor" content="#ffffff">
            <meta name="msapplication-TileImage" content="/img/ms-icon-144x144.png">
            <meta name="theme-color" content="#000000">';
        $this->set("META", implode("\n",$meta));
    }

    private function get_css() {
        global $CONFIG;
        if (isset($CONFIG['PATH']['www']['cssdir'])) {
            $files = array();
            foreach ($CONFIG['PATH']['www']['cssdir'] as $file) {
                $files[] = sprintf('<link rel="stylesheet" type="text/css" href="%s">', $file);
            }
            $this->set("STYLE", implode("\n", $files));
        } else {
            set_error('CSS ww path not config: $CONFIG[\'PATH\'][\'www\'][\'cssdir\']');
        }
    }

    private function get_js() {
        global $CONFIG;
        if (isset($CONFIG['PATH']['www']['jsdir'])) {
            $files = array();
            foreach ($CONFIG['PATH']['www']['jsdir'] as $file) {
                $files[] = sprintf('<script type="text/javascript" src="%s"></script>', $file);
            }
            //local folder js
            $local = dirname($_SERVER['SCRIPT_FILENAME']) . "/script.js";
            $local_web = dirname($_SERVER['SCRIPT_NAME']) . "/script.js";

            if (file_exists($local)) {
                $files[] = sprintf('<script type="text/javascript" src="%s"></script>', $local_web);
            }
            $this->set("SCRIPT", implode("\n", $files));
        } else {
            set_error('JS ww path not config: $CONFIG[\'PATH\'][\'www\'][\'jsdir\']');
        }
    }

    public function set_js($script) {
        if (!empty($script)) {
            $script = sprintf('<script type="text/javascript" charset="UTF-8">%s</script>', $script);
            $this->add("SCRIPT", $script);
        }
    }

    public function set_css($css) {
        if (!empty($css)) {
            $script = sprintf('<link rel="stylesheet" type="text/css" href="%s">', $css);
            $this->add("STYLE", $script);
        }
    }

    public function set_js_timer($name, $command, $time, $update_time = FALSE) {
        global $CONFIG;
        if ($time > 0) {
            $time = $time * 1000;
        } else {
            return FALSE;
        }
        if ($update_time) {
            $update_time = 'true';
        } else {
            $update_time = 'false';
        }
        if (!empty($command) AND ! empty($name)) {
            $script = sprintf('<script type="text/javascript" charset="UTF-8">
                    var %s = window.setInterval(function(){
                        exec_remote("%s",%s);
                        }, %s);
                    </script>', $name, $command, $update_time, $time);
            $this->add("SCRIPT", $script);
        }
    }

    private function get_cache() {
        global $CONFIG;
        if (isset($CONFIG['PATH']['www']['cachedir'])) {
            $files_css = $files_js = array();
            foreach ($CONFIG['PATH']['www']['cachedir'] as $file) {
                if (preg_match("/\.css$/", $file)) {
                    $files_css[] = sprintf('<link rel="stylesheet" type="text/css" href="%s">', $file);
                }
                if (preg_match("/\.js$/", $file)) {
                    $files_js[] = sprintf('<script type="text/javascript" src="%s"></script>', $file);
                }
            }
            //local folder js
            $page = filter_input(INPUT_GET, 'page');
            if (!empty($page)) {
                $local = dirname($_SERVER['SCRIPT_FILENAME']) . '/' . $page . "/script.js";
                $local_web = dirname($_SERVER['SCRIPT_NAME']) . $page . "/script.js";
            } else {
                $local = dirname($_SERVER['SCRIPT_FILENAME']) . "/script.js";
                $local_web = dirname($_SERVER['SCRIPT_NAME']) . "/script.js";
            }
            if (file_exists($local)) {
                $files_js[] = sprintf('<script type="text/javascript" src="%s"></script>', $local_web);
            }
            $this->set("SCRIPT", implode("\n", $files_js));
            $this->set("STYLE", implode("\n", $files_css));
        } else {
            set_error('CACHE ww path not config: $CONFIG[\'PATH\'][\'www\'][\'cachedir\']');
        }
    }

    private function get_local_script_ajax() {
        global $CONFIG;

        $script = "\n<script>\n";
        $local = dirname($_SERVER['SCRIPT_FILENAME']) . "ajax.php";
        $local_web = dirname($_SERVER['SCRIPT_NAME']) . "ajax.php";
        if (isset($CONFIG['name'])) {
            $script .= sprintf("var sitename='%s';\n", $CONFIG['name']);
        }
        $url = 'http://';
        if (isset($_SERVER['HTTPS'])) {
            if ($_SERVER['HTTPS'] == "on") {
                $url = 'https://';
            }
        }
        if (isset($_SERVER['REQUEST_SCHEME'])) {
            if (mb_strtolower($_SERVER['REQUEST_SCHEME']) == "https") {
                $url = 'https://';
            }
        }
        $url .= ifisset($_SERVER, 'HTTP_HOST', 'ticket.smnik.ru');
        $script .= sprintf("var siteurl='%s';\n", $url);
        /*if (isset($CONFIG['PATH']['www']['paths'])) {
            $script .= sprintf("var sitelocal='%s';\n", $CONFIG['PATH']['www']['paths']);
        }*/
        if (isset($_SERVER['REQUEST_URI'])) {
            $script .= sprintf("var REQUEST_URI='%s';\n", $_SERVER['REQUEST_URI']);
        }
        if (isset($_SERVER['SCRIPT_NAME'])) {
            $script .= sprintf("var SCRIPT_NAME='%s';\n", $_SERVER['SCRIPT_NAME']);
        }

        $script .= sprintf("var ajax_path='%s';\n", $local_web);
        $script .= "\n</script>\n";
        $this->add("SCRIPT", $script);
    }

    private function get_debug() {
        global $DEBUG;
        if (isset($DEBUG)) {
            if (is_array($DEBUG)) {
                if (count($DEBUG) > 0) {
                    $this->set("DEBUG", "<pre>" . implode("\n", $DEBUG) . "</pre>");
                }
            }
        } else {
            set_error('Var DEBUG not found');
        }
    }

    private function get_message() {
        $this->add("MESSAGE", view_messages(TRUE));
    }

    private function set($var, $value) {
        $var = strtoupper($var);
        if (isset($this->replace[$var])) {
            $this->replace[$var] = $value;
        } else {
            set_error("Var not found: " . $var);
        }
    }

    private function get($var) {
        $var = strtoupper($var);
        if (isset($this->replace[$var])) {
            return $this->replace[$var];
        }
        return FALSE;
    }

    private function add($var, $value) {
        $var = strtoupper($var);
        if (isset($this->replace[$var])) {
            $this->replace[$var] .= $value;
        } else {
            set_error("Var not found: " . $var);
        }
    }

    private function filter() {
        global $CONFIG;
        $url = filter_input(INPUT_SERVER, "REQUEST_URI");
        $start_path = '';
        if (isset($CONFIG['PATH']['www']['paths'])) {
            $start_path = $CONFIG['PATH']['www']['paths'];
        }
        $mobile = preg_match("/Android|Mobile|android|mobile/ui", $_SERVER['HTTP_USER_AGENT']);
        if (isset($CONFIG['PAGE'])) {
            if (is_array($CONFIG['PAGE'])) {
                foreach ($CONFIG['PAGE'] as $block => $pages) {
                    foreach ($pages as $page) {
                        $reg = "/^" . addcslashes($start_path . $page, "/?=") . "$/";
                        if (preg_match($reg, $url)) {
                            switch ($block) {
                                case "non_att":
                                    $this->replace['ATTENTION'] = '';
                                    break;
                                case "non_menu":
                                    if(!$mobile){
                                        $this->replace['MENU'] = '';
                                    }
                                    break;
                                case "non_action":
                                    $this->replace['ACTION'] = '';
                                    break;
                                case "non_header":
                                    $this->replace['HEADER'] = '';
                                    break;
                            }
                        }
                    }
                }
            }
        }
    }

    private function update_image_path() {
        global $CONFIG;
        if (isset($CONFIG['PATH']['base']['imagedir'])) {
            foreach ($this->replace as $repl => $val) {
                foreach ($CONFIG['PATH']['base']['imagedir'] as $key => $image) {
                    if (isset($CONFIG['PATH']['www']['imagedir'][$key])) {
                        $reg = "/src=\"" . addcslashes($image, ".") . "\"/ui";
                        $rep = sprintf('src="%s"', $CONFIG['PATH']['www']['imagedir'][$key]);
                        if (preg_match($reg, $val)) {
                            $val = preg_replace($reg, $rep, $val);
                        }else{
                            /*debug($val);
                            debug($reg);
                            debug($rep);*/
                        }
                    }
                }
                $this->replace[$repl] = $val;
            }
        }
    }

    public function error($text = NULL) {
        if (!empty($text)) {
            set_error($text);
        }
    }

}
