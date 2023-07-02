<?php

$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager->registerEventHandler(
    'iblock',
    'OnAfterIBlockElementAdd',
    'dev.site',
    '\Dev\Site\Handlers\Iblock',
    'addLog'
);
$eventManager->registerEventHandler(
    'iblock',
    'OnAfterIBlockElementUpdate',
    'dev.site',
    '\Dev\Site\Handlers\Iblock',
    'addLog'
);