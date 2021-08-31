<?php

$index = [
    t('Ease') => t('Track the prices of online stores. To do this, just select a product on the store\'s website and share the link with @PriceBot in Telegram.'),
    //Простота - Отслеживайте цены на товары интернет магазинов. Для этого достаточно выбрать товар на сайте магазина и поделиться ссылкой с @PriceBot в Telegram.
    t('Regularity') => t('@PriceBot is sending information about the price change for an item soon.'),
    //Регулярность - @PriceBot отправляет информацию о изменении цены на товар в ближайшее время.
    t('Dynamic') => t('Track the prices of the necessary goods in dynamics. Price information is stored from the moment you first receive a link to the product.'),
    //Динамика - Отслеживайте цены на необходимые  товары в динамике. Информация о цене  хранится с момента первого получения ссылки на товар.
    t('Unlimited') => t('You can track an unlimited number of products absolutely free.'),
    //Безлимит - Вы можете отслеживать неограниченное количество товаров абсолютно бесплатно.
    t('Robot') => t('The robot is constantly learning new things, if your online store is not yet known to him, then he will soon learn, and will send you relevant information.'),
    //Робот - Робот постоянно учится новому, если Ваш интернет магазин ему еще не известен, то скоро он обучится, и будет присылать Вам актуальную информацию.
    t('Group') => t('@PriceBot also supports working in groups, the only limitation is that it only reads links to online stores and management teams.'),
    //Группа - @PriceBot так же поддерживает работу в группах, единственным ограничением является то, что он читает только ссылки на интрнет магазины и команды управления.
    t('Support') => t('Send a private message to @PriceBot for suggestions and wishes.'),
        //Поддержка - Отправьте личное сообщение @PriceBot для предложений и пожеланий.
];
$c1 = $d1 = [];
$nn = 0;
foreach ($index as $t => $i) {
    $nn++;
    $c1a = ['el' => $nn];
    if ($nn == 1) {
        $c1a['class'] = 'sel';
    }
    $c1[] = html::div($t, $c1a);
    $d1a = ['class' => 'hide', 'id' => 'di' . $nn];
    if ($nn == 1) {
        $d1a['class'] = 'block';
    }
    $d1[] = html::div($i, $d1a);
}
?>
<div class="rb">
    <div id="c1"><?= implode("", $c1) ?></div>
</div>
<div class="lb">
    <div id="d1"><?= implode("", $d1) ?></div>
</div>