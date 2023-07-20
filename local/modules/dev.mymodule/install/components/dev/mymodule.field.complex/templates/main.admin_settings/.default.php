<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$name = $arResult['additionalParameters']['NAME'];

\CJSCore::Init(array("jquery"));

?>

<script>
    function addNewRows() {
        $("#many-fields-table").append('' +
            '<tr valign="top">' +
            '<td><input type="text" class="inp-code" size="20"></td>' +
            '<td><input type="text" class="inp-title" size="35"></td>' +
            '<td><input type="text" class="inp-sort" size="5" value="500"></td>' +
            '<td><select class="inp-type"><?=$arResult['OptionList']?></select></td>' +
            '</tr>');
    }

    $(document).on('change', '.inp-code', function(){
        var code = $(this).val()
        if(code.length <= 0){
            $(this).closest('tr').find('input.inp-title').removeAttr('name');
            $(this).closest('tr').find('input.inp-sort').removeAttr('name');
            $(this).closest('tr').find('select.inp-type').removeAttr('name');
        }
        else{
            $(this).closest('tr').find('input.inp-title').attr('name', '<?=$name?>[' + code + '_TITLE]');
            $(this).closest('tr').find('input.inp-sort').attr('name', '<?=$name?>[' + code + '_SORT]');
            $(this).closest('tr').find('select.inp-type').attr('name', '<?=$name?>[' + code + '_TYPE]');
        }
    });

    $(document).on('input', '.inp-sort', function(){
        var num = $(this).val();
        $(this).val(num.replace(/[^0-9]/gim,''));
    });

</script>

<style>
    .many-fields-table {margin: 0 auto; /*display: inline;*/}
    .mf-setting-title td {text-align: center!important; border-bottom: unset!important;}
    .many-fields-table td {text-align: center;}
    .many-fields-table > input, .many-fields-table > select{width: 90%!important;}
    .inp-sort{text-align: center;}
    .inp-type{min-width: 125px;}
</style>

<tr>
    <td colspan="2" align="center">
        <table id="many-fields-table" class="many-fields-table internal">
            <tr valign="top" class="heading mf-setting-title">
                <td>XML_ID</td>
                <td><?=getMessage('MYMOD_CPROP_SETTING_FIELD_TITLE')?></td>
                <td><?=getMessage('MYMOD_CPROP_SETTING_FIELD_SORT')?></td>
                <td><?=getMessage('MYMOD_CPROP_SETTING_FIELD_TYPE')?></td>
            </tr>

            <?php foreach($arResult['ITEMS'] as $code => $value): ?>
                <tr valign="top">
                    <td><input type="text" class="inp-code" size="20" value="<?=$code?>"></td>
                    <td><input type="text" class="inp-title" size="35" name="<?=$name?>[<?=$code?>_TITLE]" value="<?=$value['TITLE']?>"></td>
                    <td><input type="text" class="inp-sort" size="5" name="<?=$name?>[<?=$code?>_SORT]" value="<?=$value['SORT']?>"></td>
                    <td>
                        <select class="inp-type" name="<?=$name?>[<?=$code?>_TYPE]">
                            <?=$arResult['OptionList']?>
                        </select>                        
                    </td>
                </tr>
            <?php endforeach; ?>

            <tr valign="top">
                <td><input type="text" class="inp-code" size="20"></td>
                <td><input type="text" class="inp-title" size="35"></td>
                <td><input type="text" class="inp-sort" size="5" value="500"></td>
                <td>
                    <select class="inp-type"> <?=$arResult['OptionList']?></select>
                </td>
            </tr>
        </table>

        <tr>
            <td colspan="2" style="text-align: center;">
                <input type="button" value="<?=getMessage('MYMOD_CPROP_SETTING_BTN_ADD')?>" onclick="addNewRows()">
            </td>
        </tr>
    </td>
</tr>