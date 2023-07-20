<?php
namespace Dev\Mymodule\Helpers;

/*
* Своя реализация метода CFileMan::AddHTMLEditorFrame
* Изначальная не подходит, потому что опускает квадратные скобки в имени поля
*/

class HTMLHelper
{
    public static function AddHTMLEditorFrame(
        $strTextFieldName,
        $strTextValue,
        $strTextTypeFieldName,
        $strTextTypeValue,
        $height = 350
    )
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && !check_bitrix_sessid()) {
            return;
        }

        $strID = preg_replace("/[^a-zA-Z0-9_:\.]/is", "", $strTextFieldName);
        $strTextValue = htmlspecialcharsback($strTextValue);

        $textType = \CFileMan::ShowTypeSelector(array(
            'name' => $strID,
            'strTextTypeFieldName' => $strTextTypeFieldName,
            'strTextTypeValue' => $strTextTypeValue,
        ));

        self::setEditorEventHandlers($strID);
        ?>
        <textarea class="typearea" style="width:100%;height:80px;" name="<?=$strTextFieldName?>" id="bxed_<?=$strID?>" wrap="virtual">
            <?= htmlspecialcharsbx($strTextValue)?>
        </textarea>
        <?php

        $arTaskbars = Array("BXPropertiesTaskbar", "BXSnippetsTaskbar");
        $arParams = Array(
            "bUseOnlyDefinedStyles"=>\COption::GetOptionString("fileman", "show_untitled_styles", "N")!="Y",
            "bFromTextarea" => true,
            "bDisplay" => false,
            "bWithoutPHP" => true,
            "arTaskbars" => $arTaskbars,
            "height" => $height
        );

        \CFileMan::ShowHTMLEditControl($strTextFieldName, $strTextValue, $arParams);
    }

    private static function setEditorEventHandlers($name)
    {
        ?>
        <script>
            function onContextMenu_<?= $name;?>(e){GLOBAL_pMainObj['<?= $name;?>'].OnContextMenu(e);}
            function onClick_<?= $name;?>(e){GLOBAL_pMainObj['<?= $name;?>'].OnClick(e);}
            function onDblClick_<?= $name;?>(e){GLOBAL_pMainObj['<?= $name;?>'].OnDblClick(e);}
            function onMouseUp_<?= $name;?>(e){GLOBAL_pMainObj['<?= $name;?>'].OnMouseUp(e);}
            function onDragDrop_<?= $name;?>(e){GLOBAL_pMainObj['<?= $name;?>'].OnDragDrop(e);}
            function onKeyPress_<?= $name;?>(e){GLOBAL_pMainObj['<?= $name;?>'].OnKeyPress(e);}
            function onKeyDown_<?= $name;?>(e){GLOBAL_pMainObj['<?= $name;?>'].OnKeyDown(e);}
            function onPaste_<?= $name;?>(e){GLOBAL_pMainObj['<?= $name;?>'].OnPaste(e);}
            
            function OnSubmit_<?= $name;?>(e){GLOBAL_pMainObj['<?= $name;?>'].onSubmit(e);}
            
            function OnDispatcherEvent_pDocument_<?= $name;?>(e){pBXEventDispatcher.OnEvent(GLOBAL_pMainObj['<?= $name;?>'].pDocument, e);}
            function OnDispatcherEvent_pEditorDocument_<?= $name;?>(e){pBXEventDispatcher.OnEvent(GLOBAL_pMainObj['<?= $name;?>'].pEditorDocument, e);}
        </script>
        <?php
    }
}