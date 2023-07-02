<?php

namespace Dev\Site\Agents;


class Iblock
{
    public static function clearOldLogs()
    {
        if (\Bitrix\Main\Loader::includeModule('iblock')) {
            $iblock = \Bitrix\Iblock\iblockTable::getRow([
                'select' => ['ID'],
                'filter' => ['=CODE' => 'LOG'],
            ]);
            if ($iblock) {
                $blockID = $iblock['ID'];
                
                $dbElements = \Bitrix\Iblock\ElementTable::getList([
                    'select' => ['ID'],
                    'filter' => ['=IBLOCK_ID' => $blockID],
                    'order' => ['DATE_CREATE' => 'DESC'],
                ]);
                
                $rows = $dbElements->fetchAll();
                foreach ($rows as $key => $row) {
                    if ($key < 10) {
                        continue;
                    }
                    \CIBlockElement::Delete($row['ID']);
                }
            }
        }
        
        return '\\' . __CLASS__ . '::' . __FUNCTION__ . '();';
    }

    public static function example()
    {
        global $DB;
        if (\Bitrix\Main\Loader::includeModule('iblock')) {
            $iblockId = \Only\Site\Helpers\IBlock::getIblockID('QUARRIES_SEARCH', 'SYSTEM');
            $format = $DB->DateFormatToPHP(\CLang::GetDateFormat('SHORT'));
            $rsLogs = \CIBlockElement::GetList(['TIMESTAMP_X' => 'ASC'], [
                'IBLOCK_ID' => $iblockId,
                '<TIMESTAMP_X' => date($format, strtotime('-1 months')),
            ], false, false, ['ID', 'IBLOCK_ID']);
            while ($arLog = $rsLogs->Fetch()) {
                \CIBlockElement::Delete($arLog['ID']);
            }
        }
        return '\\' . __CLASS__ . '::' . __FUNCTION__ . '();';
    }
}
