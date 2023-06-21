<!DOCTYPE html>
<html lang="ru">
<head><title></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="utf-8">
    <link href="<?php echo SITE_TEMPLATE_PATH ?>/css/common.css" rel="stylesheet">
    <link rel="shortcut icon" href="<?php echo SITE_TEMPLATE_PATH ?>/images/favicon.604825ed.ico" type="image/x-icon">
    <?$APPLICATION->ShowHead();?>
    <title><?$APPLICATION->ShowTitle()?></title>
</head>
<body>
    <div id="panel"><?$APPLICATION->ShowPanel();?></div>
    <div id="header">
        <div id="main-menu">
            <?$APPLICATION->IncludeComponent("bitrix:menu", "horizontal_multilevel", array(
            "ROOT_MENU_TYPE" => "top",
            "MENU_CACHE_TYPE" => "A",
            "MENU_CACHE_TIME" => "36000000",
            "MENU_CACHE_USE_GROUPS" => "N",
            "MENU_CACHE_GET_VARS" => array(
            ),
            "MAX_LEVEL" => "1",
            "CHILD_MENU_TYPE" => "top",
            "USE_EXT" => "Y",
            "DELAY" => "N",
            "ALLOW_MULTI_SELECT" => "N"
            ),
            false
            );?>
        </div>
    </div>
    <div id="barba-wrapper">