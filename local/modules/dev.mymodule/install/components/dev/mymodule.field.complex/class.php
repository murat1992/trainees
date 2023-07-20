<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
use Dev\Mymodule\UserField\Types\ComplexType;
use \Dev\Mymodule\Helpers\HTMLHelper;


class ComplexUfComponent extends Bitrix\Main\Component\BaseUfComponent
{
    protected static function getUserTypeId(): string
    {
        return ComplexType::USER_TYPE_ID;
    }

    protected function prepareResult(): void
    {
        $result= '';
        $arOption = ComplexType::getOptionList();
        foreach ($arOption as $code => $name){
            $s = '';
            if($code === $selected){
                $s = 'selected';
            }

            $result .= '<option value="'.$code.'" '.$s.'>'.$name.'</option>';
        }
        $this->arResult['OptionList'] = $result;


        $arSetting = $this->arParams['userField']['SETTINGS'];
        $items = array();
        foreach ($arSetting as $key => $value) {
            if(strstr($key, '_TITLE') !== false) {
                $code = str_replace('_TITLE', '', $key);
                $items[$code]['TITLE'] = $value;
            }
            else if(strstr($key, '_SORT') !== false) {
                $code = str_replace('_SORT', '', $key);
                $items[$code]['SORT'] = $value;
            }
            else if(strstr($key, '_TYPE') !== false) {
                $code = str_replace('_TYPE', '', $key);
                $items[$code]['TYPE'] = $value;
            }
        }

        uasort($items, function ($a, $b) {
            if ($a['SORT'] == $b['SORT']) {
                return 0;
            }
            return ($a['SORT'] < $b['SORT']) ? -1 : 1;
        });

        foreach ($items as $code => &$arItem) {
            $result = $this->arResult;
            $strHTMLControlName = $result['additionalParameters']['NAME'];
            if($arItem['TYPE'] === 'string'){
                $arItem['HTML_INPUT'] = self::showString($code, $arItem['TITLE'], $result, $strHTMLControlName);
            } elseif ($arItem['TYPE'] === 'html') {
                $arItem['HTML_INPUT'] = self::showHTML($code, $arItem['TITLE'], $result, $strHTMLControlName);
            } elseif($arItem['TYPE'] === 'file') {
                $arItem['HTML_INPUT'] = self::showFile($code, $arItem['TITLE'], $result, $strHTMLControlName);
            } elseif($arItem['TYPE'] === 'text') {
                $arItem['HTML_INPUT'] = self::showTextarea($code, $arItem['TITLE'], $result, $strHTMLControlName);
            } elseif($arItem['TYPE'] === 'date') {
                $arItem['HTML_INPUT'] = self::showDate($code, $arItem['TITLE'], $result, $strHTMLControlName);
            } elseif($arItem['TYPE'] === 'element') {
                $arItem['HTML_INPUT'] = self::showBindElement($code, $arItem['TITLE'], $result, $strHTMLControlName);
            }
        }

        $this->arResult['ITEMS'] = $items;
    }

    private static function showString($code, $title, $arValue, $strHTMLControlName)
    {
        $result = '';

        $v = !empty($arValue['value'][$code]) ? $arValue['value'][$code] : '';
        $result .= '<tr>
                    <td align="right">'.$title.': </td>
                    <td><input type="text" value="'.$v.'" name="'.$strHTMLControlName.'['.$code.']"/></td>
                </tr>';

        return $result;
    }

    private static function showHTML($code, $title, $arValue, $strHTMLControlName)
    {
        $result = '';

        ob_start();
        
        //\CFileMan::AddHTMLEditorFrame не подходит, так как убирает квадратные скобки
        HTMLHelper::AddHTMLEditorFrame(
            $strHTMLControlName.'['.$code.']',
            $arValue['value'][$code],
            $strHTMLControlName.'['.$code.'_TYPE]',
            strlen($arValue['value'][$code])?"html":"text",
        );

        $html = ob_get_contents();
		ob_end_clean();

        $v = !empty($arValue['value'][$code]) ? $arValue['value'][$code] : '';
        $result .= '<tr>
                    <td align="right">'.$title.': </td>
                    <td>'.$html.'</td>
                </tr>';

        return $result;
    }

    private static function showFile($code, $title, $arValue, $strHTMLControlName)
    {
        $result = '';

        if(!empty($arValue['value'][$code]) && !is_array($arValue['value'][$code])){
            $fileId = $arValue['value'][$code];
        }
        else if(!empty($arValue['value'][$code]['OLD'])){
            $fileId = $arValue['value'][$code]['OLD'];
        }
        else{
            $fileId = '';
        }

        if(!empty($fileId))
        {
            $arPicture = \CFile::GetByID($fileId)->Fetch();
            if($arPicture)
            {
                $strImageStorePath = \COption::GetOptionString('main', 'upload_dir', 'upload');
                $sImagePath = '/'.$strImageStorePath.'/'.$arPicture['SUBDIR'].'/'.$arPicture['FILE_NAME'];
                $fileType = self::getExtension($sImagePath);

                if(in_array($fileType, ['png', 'jpg', 'jpeg', 'gif'])){
                    $content = '<img src="'.$sImagePath.'">';
                }
                else{
                    $content = '<div class="mf-file-name">'.$arPicture['FILE_NAME'].'</div>';
                }

                $result = '<tr>
                        <td align="right" valign="top">'.$title.': </td>
                        <td>
                            <table class="mf-img-table">
                                <tr>
                                    <td>'.$content.'<br>
                                        <div>
                                            <label><input name="'.$strHTMLControlName.'['.$code.'][DEL]" value="Y" type="checkbox"> '. getMessage("MYMOD_CPROP_FILE_DELETE") . '</label>
                                            <input name="'.$strHTMLControlName.'['.$code.'][OLD]" value="'.$fileId.'" type="hidden">
                                        </div>
                                    </td>
                                </tr>
                            </table>                      
                        </td>
                    </tr>';
            }
        }
        else{
            $result .= '<tr>
                    <td align="right">'.$title.': </td>
                    <td><input type="file" value="" name="'.$strHTMLControlName.'['.$code.']"/></td>
                </tr>';
        }

        return $result;
    }

    public static function showTextarea($code, $title, $arValue, $strHTMLControlName)
    {
        $result = '';

        $v = !empty($arValue['value'][$code]) ? $arValue['value'][$code] : '';
        $result .= '<tr>
                    <td align="right" valign="top">'.$title.': </td>
                    <td><textarea rows="8" name="'.$strHTMLControlName.'['.$code.']">'.$v.'</textarea></td>
                </tr>';

        return $result;
    }

    public static function showDate($code, $title, $arValue, $strHTMLControlName)
    {
        $result = '';

        $v = !empty($arValue['value'][$code]) ? $arValue['value'][$code] : '';
        $result .= '<tr>
                        <td align="right" valign="top">'.$title.': </td>
                        <td>
                            <table>
                                <tr>
                                    <td style="padding: 0;">
                                        <div class="adm-input-wrap adm-input-wrap-calendar">
                                            <input class="adm-input adm-input-calendar" type="text" name="'.$strHTMLControlName.'['.$code.']" size="23" value="'.$v.'">
                                            <span class="adm-calendar-icon"
                                                  onclick="BX.calendar({node: this, field:\''.$strHTMLControlName.'['.$code.']\', form: \'\', bTime: true, bHideTime: false});"></span>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>';

        return $result;
    }

    public static function showBindElement($code, $title, $arValue, $strHTMLControlName)
    {
        $result = '';

        $v = !empty($arValue['value'][$code]) ? $arValue['value'][$code] : '';

        $elUrl = '';
        if(!empty($v)){
            $arElem = \CIBlockElement::GetList([], ['ID' => $v],false, ['nPageSize' => 1], ['ID', 'IBLOCK_ID', 'IBLOCK_TYPE_ID', 'NAME'])->Fetch();
            if(!empty($arElem)){
                $elUrl .= '<a target="_blank" href="/bitrix/admin/iblock_element_edit.php?IBLOCK_ID='.$arElem['IBLOCK_ID'].'&ID='.$arElem['ID'].'&type='.$arElem['IBLOCK_TYPE_ID'].'">'.$arElem['NAME'].'</a>';
            }
        }

        $result .= '<tr>
                    <td align="right">'.$title.': </td>
                    <td>
                        <input name="'.$strHTMLControlName.'['.$code.']" id="'.$strHTMLControlName.'['.$code.']" value="'.$v.'" size="8" type="text" class="mf-inp-bind-elem">
                        <input type="button" value="..." onClick="jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?lang=ru&IBLOCK_ID=0&n='.$strHTMLControlName.'&k='.$code.'\', 900, 700);">&nbsp;
                        <span>'.$elUrl.'</span>
                    </td>
                </tr>';

        return $result;
    }

    private static function getExtension($filePath)
    {
        return array_pop(explode('.', $filePath));
    }

    protected function getFieldValue(): array
    {
        $decode = json_decode($this->userField['VALUE']);
        $result = array();
        foreach ($decode as $code => $value) {
            $result[$code] = $value;
        }

        return $result;
    }
}