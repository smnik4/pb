<?php

$dsn = sprintf("mysql:host=%s;dbname=%s;charset=utf8mb4", $CONFIG['DB']['host'], $CONFIG['DB']['base']);
$opt = array(
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
);
$DB = new PDO($dsn, $CONFIG['DB']['user'], $CONFIG['DB']['pass'], $opt);
$DB_PDO = $DB;

function db($sql, $arg = array()) {
    global $DB;
    $sel = $DB->prepare($sql);
    $sel->execute($arg);
    if (preg_match("/insert|INSERT/ui", $sql)) {
        return $DB->lastInsertId();
    }
    return $sel;
}
