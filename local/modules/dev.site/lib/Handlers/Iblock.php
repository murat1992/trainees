<?php

namespace Dev\Site\Handlers;


class Iblock
{
    /*
    * Рекурсивное заполнение пути
    */
    private static function getSectionsPath($blockID, $sectionID, $path = "", $sections = null)
    {
        if (empty($sectionID)) {
            return $path;
        }
        
        if (empty($sections)) {
            $dbSections = \Bitrix\Iblock\SectionTable::getList([
                'select' => ['ID', 'NAME', 'SECTION_ID' => 'IBLOCK_SECTION_ID'],
                'filter' => ['=IBLOCK_ID' => $blockID]
            ]);
            while ($section = $dbSections->fetch()) {
                $sections[$section['ID']] = [
                    'NAME' => $section['NAME'],
                    'SECTION_ID' => $section['SECTION_ID']
                ];
            }
        }
        
        $path = $sections[$sectionID]['NAME'] . '->' . $path;
        $parentID = $sections[$sectionID]['SECTION_ID'];
        
        return self::getSectionsPath($blockID, $parentID, $path, $sections);
    }
    
    /*
    * Обработчик событий
    */
    public static function addLog($arFields)
    {
        $dbIblocks = \Bitrix\Iblock\iblockTable::getList(['select' => ['ID', 'NAME', 'CODE']]);
        while ($iblock = $dbIblocks->fetch()) {
            if ($iblock['CODE'] == 'LOG') {
                $logBlockID = $iblock['ID'];
            }
            if ($iblock['ID'] == $arFields['IBLOCK_ID']) {
                $blockID = $iblock['ID'];
                $blockName = $iblock['NAME'];
                $blockCode = $iblock['CODE'];
            }
        }
        
        if (empty($logBlockID)) {
            AddMessage2Log('Ошибка: Не найден инфоблок с символьным кодом "LOG"', "dev.site");
            return;
        } elseif ($blockID == $logBlockID) {
            /*
            * Инфоблок является логером. Пропускаем.
            */
            return;
        }
        
        /*
        * Получаем нужный раздел инфоблока логов.
        * Создаем если отсутствует.
        */
        $section = \Bitrix\Iblock\SectionTable::getRow([
            'filter' => ['=NAME' => $blockName, '=CODE' => $blockCode],
        ]);
        if ($section) {
            $sectionID = $section['ID'];
        } else {
            $result = \Bitrix\Iblock\SectionTable::add([
                'IBLOCK_ID' => $logBlockID,
                'NAME' => $blockName,
                'CODE' => $blockCode,
                'TIMESTAMP_X' => new \Bitrix\Main\Type\Date,
            ]);
            if ($result->isSuccess()) {
                $sectionID = $result->getId();
            } else {
                AddMessage2Log(
                    'Ошибка: Не удалось создать раздел в инфоблоке логов. '
                    .print_r($result->getErrorMessages(), true),
                    'dev.site'
                );
                return;
            }
        }
        
        /*
        * Записываем событие в логах.
        */
        $name = $arFields['NAME'];
        $path = self::getSectionsPath($blockID, $arFields['IBLOCK_SECTION'][0]);
        $previewText = "$blockName->$path$name\n";
        
        $el = new \CIBlockElement();
        $result = $el->Add([
            'IBLOCK_ID' => $logBlockID,
            'IBLOCK_SECTION_ID' => $sectionID,
            'NAME' => $arFields['ID'],
            'ACTIVE' => 'Y',
            'ACTIVE_FROM' => date('d.m.Y'),
            'PREVIEW_TEXT_TYPE' => "text",
            'PREVIEW_TEXT' => $previewText,
        ]);
        if (!$result) {
            AddMessage2Log(
                'Ошибка: Не удалось сделать запись в инфоблоке логов. ' . $el->LAST_ERROR,
                'dev.site'
            );
            return;
        }
    }
    
    function OnBeforeIBlockElementAddHandler(&$arFields)
    {
        $iQuality = 95;
        $iWidth = 1000;
        $iHeight = 1000;
        /*
         * Получаем пользовательские свойства
         */
        $dbIblockProps = \Bitrix\Iblock\PropertyTable::getList(array(
            'select' => array('*'),
            'filter' => array('IBLOCK_ID' => $arFields['IBLOCK_ID'])
        ));
        /*
         * Выбираем только свойства типа ФАЙЛ (F)
         */
        $arUserFields = [];
        while ($arIblockProps = $dbIblockProps->Fetch()) {
            if ($arIblockProps['PROPERTY_TYPE'] == 'F') {
                $arUserFields[] = $arIblockProps['ID'];
            }
        }
        /*
         * Перебираем и масштабируем изображения
         */
        foreach ($arUserFields as $iFieldId) {
            foreach ($arFields['PROPERTY_VALUES'][$iFieldId] as &$file) {
                if (!empty($file['VALUE']['tmp_name'])) {
                    $sTempName = $file['VALUE']['tmp_name'] . '_temp';
                    $res = \CAllFile::ResizeImageFile(
                        $file['VALUE']['tmp_name'],
                        $sTempName,
                        array("width" => $iWidth, "height" => $iHeight),
                        BX_RESIZE_IMAGE_PROPORTIONAL_ALT,
                        false,
                        $iQuality);
                    if ($res) {
                        rename($sTempName, $file['VALUE']['tmp_name']);
                    }
                }
            }
        }

        if ($arFields['CODE'] == 'brochures') {
            $RU_IBLOCK_ID = \Only\Site\Helpers\IBlock::getIblockID('DOCUMENTS', 'CONTENT_RU');
            $EN_IBLOCK_ID = \Only\Site\Helpers\IBlock::getIblockID('DOCUMENTS', 'CONTENT_EN');
            if ($arFields['IBLOCK_ID'] == $RU_IBLOCK_ID || $arFields['IBLOCK_ID'] == $EN_IBLOCK_ID) {
                \CModule::IncludeModule('iblock');
                $arFiles = [];
                foreach ($arFields['PROPERTY_VALUES'] as $id => &$arValues) {
                    $arProp = \CIBlockProperty::GetByID($id, $arFields['IBLOCK_ID'])->Fetch();
                    if ($arProp['PROPERTY_TYPE'] == 'F' && $arProp['CODE'] == 'FILE') {
                        $key_index = 0;
                        while (isset($arValues['n' . $key_index])) {
                            $arFiles[] = $arValues['n' . $key_index++];
                        }
                    } elseif ($arProp['PROPERTY_TYPE'] == 'L' && $arProp['CODE'] == 'OTHER_LANG' && $arValues[0]['VALUE']) {
                        $arValues[0]['VALUE'] = null;
                        if (!empty($arFiles)) {
                            $OTHER_IBLOCK_ID = $RU_IBLOCK_ID == $arFields['IBLOCK_ID'] ? $EN_IBLOCK_ID : $RU_IBLOCK_ID;
                            $arOtherElement = \CIBlockElement::GetList([],
                                [
                                    'IBLOCK_ID' => $OTHER_IBLOCK_ID,
                                    'CODE' => $arFields['CODE']
                                ], false, false, ['ID'])
                                ->Fetch();
                            if ($arOtherElement) {
                                /** @noinspection PhpDynamicAsStaticMethodCallInspection */
                                \CIBlockElement::SetPropertyValues($arOtherElement['ID'], $OTHER_IBLOCK_ID, $arFiles, 'FILE');
                            }
                        }
                    } elseif ($arProp['PROPERTY_TYPE'] == 'E') {
                        $elementIds = [];
                        foreach ($arValues as &$arValue) {
                            if ($arValue['VALUE']) {
                                $elementIds[] = $arValue['VALUE'];
                                $arValue['VALUE'] = null;
                            }
                        }
                        if (!empty($arFiles && !empty($elementIds))) {
                            $rsElement = \CIBlockElement::GetList([],
                                [
                                    'IBLOCK_ID' => \Only\Site\Helpers\IBlock::getIblockID('PRODUCTS', 'CATALOG_' . $RU_IBLOCK_ID == $arFields['IBLOCK_ID'] ? '_RU' : '_EN'),
                                    'ID' => $elementIds
                                ], false, false, ['ID', 'IBLOCK_ID', 'NAME']);
                            while ($arElement = $rsElement->Fetch()) {
                                /** @noinspection PhpDynamicAsStaticMethodCallInspection */
                                \CIBlockElement::SetPropertyValues($arElement['ID'], $arElement['IBLOCK_ID'], $arFiles, 'FILE');
                            }
                        }
                    }
                }
            }
        }
    }
}
