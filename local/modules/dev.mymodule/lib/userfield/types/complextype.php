<?php

namespace Dev\Mymodule\UserField\Types;

class ComplexType extends \Bitrix\Main\UserField\Types\BaseType
{
    public const USER_TYPE_ID = 'mymod_complex';
    public const RENDER_COMPONENT = 'dev:mymodule.field.complex';

    protected static function getDescription() : array
    {
        return array(
            'DESCRIPTION' => getMessage('MYMOD_USERTYPE_COMPLEX_DESCRIPTION'),
            'BASE_TYPE' => "string",
        );
    }

    public static function getDbColumnType() : string
    {
        return "text";
    }

    public static function prepareSettings(array $userField): array
    {
        return $userField['SETTINGS'];
    }

    
    public static function getOptionList($selected = 'string')
    {
        $arOption = [
            'string' => getMessage('MYMOD_CPROP_FIELD_TYPE_STRING'),
            'html' => getMessage('MYMOD_CPROP_FIELD_TYPE_HTML'),
            'file' => getMessage('MYMOD_CPROP_FIELD_TYPE_FILE'),
            'text' => getMessage('MYMOD_CPROP_FIELD_TYPE_TEXT'),
            'date' => getMessage('MYMOD_CPROP_FIELD_TYPE_DATE'),
            'element' => getMessage('MYMOD_CPROP_FIELD_TYPE_ELEMENT')
        ];

        return $arOption;
    }

    static function OnBeforeSave($arUserField, $arValue)
    {
        $arSetting = $arUserField['SETTINGS'];
        $arFields = array();
        foreach ($arSetting as $key => $value) {
            if(strstr($key, '_TITLE') !== false) {
                $code = str_replace('_TITLE', '', $key);
                $arFields[$code]['TITLE'] = $value;
            }
            else if(strstr($key, '_SORT') !== false) {
                $code = str_replace('_SORT', '', $key);
                $arFields[$code]['SORT'] = $value;
            }
            else if(strstr($key, '_TYPE') !== false) {
                $code = str_replace('_TYPE', '', $key);
                $arFields[$code]['TYPE'] = $value;
            }
        }

        foreach($arValue as $code => $arItem){
            if($arFields[$code]['TYPE'] === 'file'){
                $arValue[$code] = self::prepareFileToDB($arItem);
            }
        }

        $strValue = json_encode($arValue);
        return $strValue;
    }

    private static function prepareFileToDB($arValue)
    {
        $result = false;

        if(!empty($arValue['DEL']) && $arValue['DEL'] === 'Y' && !empty($arValue['OLD'])){
            \CFile::Delete($arValue['OLD']);
        }
        else if(!empty($arValue['OLD'])){
            $result = $arValue['OLD'];
        }
        else if(!empty($arValue['name'])){
            $result = \CFile::SaveFile($arValue, 'vote');
        }

        return $result;
    }

}