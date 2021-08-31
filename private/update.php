<?php

/*function update_1_0_to_1_1(){
    $sel = db("SHOW COLUMNS FROM `users` WHERE `Field`='tlg_name'");
    if($sel ->rowCount() > 0){
        return 1.1;
    }
    $sql = [];
    $sql[] = "ALTER TABLE `divs`  ADD `botname` VARCHAR(200) NULL DEFAULT NULL  AFTER `reg_date`,  ADD `botlogin` VARCHAR(200) NULL DEFAULT NULL  AFTER `botname`, "
            . "ADD `bottoken` TEXT NULL DEFAULT NULL  AFTER `botlogin`,  ADD `botdefgroup` INT NOT NULL DEFAULT '0'  AFTER `bottoken`;";
    $sql[] = "ALTER TABLE `tickets` CHANGE `korpus` `korpus` INT(11) NULL DEFAULT NULL;";
    $sql[] = "ALTER TABLE `tickets_event` ADD `send` INT(1) NOT NULL DEFAULT '0' AFTER `status`;";
    $sql[] = "ALTER TABLE `users` DROP `pass_crypt`;";
    $sql[] = "ALTER TABLE `users` ADD `tlg_name` VARCHAR(200) NULL DEFAULT NULL AFTER `time_zone`, ADD `tlg_chat_id` INT NOT NULL DEFAULT '0' AFTER `tlg_name`, "
            . "ADD `tlg_auth` INT(1) NOT NULL DEFAULT '0' AFTER `tlg_chat_id`, ADD `tlg_la` VARCHAR(100) NULL DEFAULT NULL AFTER `tlg_auth`, ADD `tlg_laid` INT NOT NULL DEFAULT '0' AFTER `tlg_la`;";
    
    foreach ($sql as $i){
        if(!empty($i)){
            db($i);
        }
    }
    return 1.1;
}
function update_1_1_to_1_2(){
    $sel = db("SHOW COLUMNS FROM `menu` WHERE `Field`='sys'");
    if($sel ->rowCount() > 0){
        return 1.2;
    }
    $sql = [];
    $sql[] = "ALTER TABLE `menu` ADD `sys` INT(1) NOT NULL DEFAULT '0' AFTER `group`;";
    $sql[] = "ALTER TABLE `menu` CHANGE `visible` `visible` TINYINT(1) NOT NULL DEFAULT '1';";
    $sql[] = "ALTER TABLE `menu` CHANGE `group` `group` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
    $sql[] = "UPDATE `menu` SET `sys`=1 WHERE `href`='/'";
    $sql[] = "UPDATE `menu` SET `sys`=1 WHERE `href`='/?action=stat_work'";
    $sql[] = "UPDATE `menu` SET `sys`=1, `visible` = '0' WHERE `href`='/?action=stat_kab'";
    $sql[] = "UPDATE `menu` SET `sys`=1 WHERE `href`='/?page=admin'";
    $sql[] = "UPDATE `menu` SET `sys`=1 WHERE `href`='/?page=admin&action=group'";
    $sql[] = "UPDATE `menu` SET `sys`=1 WHERE `href`='/?page=admin&action=subscribe'";
    $sql[] = "UPDATE `menu` SET `sys`=1 WHERE `href`='/?page=admin&action=users'";
    $sql[] = "UPDATE `menu` SET `sys`=1 WHERE `href`='/?page=admin&action=areas'";
    $sql[] = "UPDATE `menu` SET `sys`=1 WHERE `href`='/?page=admin&action=divs'";
    $sql[] = "UPDATE `menu` SET `sys`=1 WHERE `href`='/?page=admin&action=org'";
    
    $sql[] = "ALTER TABLE `divs` ADD `token` VARCHAR(200) NULL DEFAULT NULL AFTER `botdefgroup`;";
    $sql[] = "UPDATE `divs` SET `token`=MD5(`id`) WHERE `token` IS NULL;";
    $sql[] = "ALTER TABLE `divs` ADD `timezone` TEXT NULL DEFAULT NULL AFTER `token`;";
    
    $sql[] = "ALTER TABLE `tickets_attachment` ADD `comment` TEXT NULL DEFAULT NULL AFTER `path`;";
    $sql[] = "ALTER TABLE `tickets_attachment` ADD `cache` VARCHAR(200) NOT NULL AFTER `comment`;";
    
    $sql[] = "CREATE TABLE `vlans` (`id` int(11) NOT NULL, `div_id` int(11) NOT NULL, `num` int(11) NOT NULL, `name` varchar(100) NOT NULL, "
            . "`mask` varchar(18) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    $sql[] = "ALTER TABLE `vlans` ADD PRIMARY KEY (`id`);";
    $sql[] = "ALTER TABLE `vlans` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT; COMMIT;";
    
    $sql[] = "CREATE TABLE `hosts` ( `id` int(11) NOT NULL AUTO_INCREMENT, `div_id` int(11) NOT NULL, `name` varchar(200) NOT NULL, `num` varchar(200) DEFAULT NULL, "
            . "`korpus` int(11) NOT NULL DEFAULT 0, `description` text DEFAULT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    $sql[] = "CREATE TABLE `hosts_eth` ( `id` int(11) NOT NULL AUTO_INCREMENT, `host_id` int(11) NOT NULL, `eth` varchar(10) NOT NULL DEFAULT 'eth', `vlan` int(11) NOT NULL, "
            . "`mac` varchar(17) NOT NULL, `ip` varchar(15) NOT NULL, `lastview` timestamp NOT NULL DEFAULT current_timestamp(), PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8";
    $sql[] = "CREATE TABLE `hosts_com` ( `id` int(11) NOT NULL AUTO_INCREMENT, `host_id` int(11) NOT NULL, `user_id` int(11) NOT NULL, "
            . "`text` text NOT NULL, `dt` timestamp NOT NULL DEFAULT current_timestamp(), PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8";
    $sql[] = "ALTER TABLE `hosts_eth` ADD `ethnum` INT(1) NOT NULL DEFAULT '0' AFTER `eth`;";
    $sql[] = "ALTER TABLE `hosts` ADD `control` INT(1) NOT NULL DEFAULT '0' AFTER `description`, ADD `control_ethid` INT NOT NULL DEFAULT '0' AFTER `control`;";
    $sql[] = "";
    $sql[] = "";
    $sql[] = "";
    
    foreach ($sql as $i){
        if(!empty($i)){
            db($i);
        }
    }
    $ins = db("INSERT INTO `menu` (`num`, `name`, `href`, `parent_id`, `sys`) VALUES ('9', 'Утилиты', '/?page=util', '0', 1);");
    if($ins > 0){
        db("INSERT INTO `menu` (`num`, `name`, `href`, `parent_id`, `sys`) VALUES ('1', 'Генератор паролей', '/?page=util&action=pass', :parent_id, 1);",['parent_id'=>$ins]);
        db("INSERT INTO `menu` (`num`, `name`, `href`, `parent_id`, `sys`) VALUES ('2', 'IP калькулятор', '/?page=util&action=SubnetCalc', :parent_id, 1);",['parent_id'=>$ins]);
        db("INSERT INTO `menu` (`num`, `name`, `href`, `parent_id`, `sys`) VALUES ('3', 'IP утилиты', '/?page=util&action=ip', :parent_id, 1);",['parent_id'=>$ins]);
        db("INSERT INTO `menu` (`num`, `name`, `href`, `parent_id`, `sys`) VALUES ('4', 'UNIXTIME', '/?page=util&action=unixtime', :parent_id, 1);",['parent_id'=>$ins]);
        db("INSERT INTO `menu` (`num`, `name`, `href`, `parent_id`, `sys`) VALUES ('5', 'En/Decoder', '/?page=util&action=encoder', :parent_id, 1);",['parent_id'=>$ins]);
    }
    $ins2 = db("INSERT INTO `menu` (`num`, `name`, `href`, `parent_id`, `visible`, `group`, `sys`) VALUES ('3', 'Хосты', '/?page=hosts', '0', '1', 'root,admin', '1');");
    if($ins2 > 0){
        db("INSERT INTO `menu` (`num`, `name`, `href`, `parent_id`, `sys`) VALUES ('1', 'VLANs', '/?page=hosts&action=vlans', :parent_id, 1);",['parent_id'=>$ins]);
    }
    return 1.2;
}

function update_1_2_to_1_3(){
    $sel = db("SHOW COLUMNS FROM `tickets_event` WHERE `Field`='subticket'");
    if($sel ->rowCount() > 0){
        return 1.3;
    }
    $sql = [];
    $sql[] = "ALTER TABLE `tickets_event` ADD `subticket` INT(1) NOT NULL DEFAULT '0' AFTER `send`, ADD `subticket_status` INT(1) NOT NULL DEFAULT '0' AFTER `subticket`;";
    
    foreach ($sql as $i){
        if(!empty($i)){
            db($i);
        }
    }
    return 1.3;
}

function update_1_3_to_1_4(){
    $sql = [];
    $sql[] = "ALTER TABLE `tickets_attachment` CHANGE `cache` `cache` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;";
    
    foreach ($sql as $i){
        if(!empty($i)){
            db($i);
        }
    }
    return 1.4;
}
 */