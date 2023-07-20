<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$hideText = getMessage('MYMOD_CPROP_HIDE_TEXT');
$showText = getMessage('MYMOD_CPROP_SHOW_TEXT');
$clearText = getMessage('MYMOD_CPROP_CLEAR_TEXT');

\CJSCore::Init(array("jquery"));
?>

<style>
    .cl {cursor: pointer;}
    .mf-gray {color: #797777;}
    .mf-fields-list {display: none; padding-top: 10px; margin-bottom: 10px!important; margin-left: -300px!important; border-bottom: 1px #e0e8ea solid!important;}
    .mf-fields-list.active {display: block;}
    .mf-fields-list td {padding-bottom: 5px;}
    .mf-fields-list td:first-child {width: 300px; color: #616060;}
    .mf-fields-list td:last-child {padding-left: 5px;}
    .mf-fields-list input[type="text"] {width: 350px!important;}
    .mf-fields-list textarea {min-width: 350px; max-width: 650px; color: #000;}
    .mf-fields-list img {max-height: 150px; margin: 5px 0;}
    .mf-img-table {background-color: #e0e8e9; color: #616060; width: 100%;}
    .mf-fields-list input[type="text"].adm-input-calendar {width: 170px!important;}
    .mf-file-name {word-break: break-word; padding: 5px 5px 0 0; color: #101010;}
    .mf-fields-list input[type="text"].mf-inp-bind-elem {width: unset!important;}
</style>

<script>
    $(document).on('click', 'a.mf-toggle', function (e) {
        e.preventDefault();

        var table = $(this).closest('tr').find('table.mf-fields-list');
        $(table).toggleClass('active');
        if ($(table).hasClass('active')) {
            $(this).text('<?=$hideText?>');
        } else {
            $(this).text('<?=$showText?>');
        }
    });

    $(document).on('click', 'a.mf-delete', function (e) {
        e.preventDefault();

        var textInputs = $(this).closest('tr').find('input[type="text"]');
        $(textInputs).each(function (i, item) {
            $(item).val('');
        });

        var textarea = $(this).closest('tr').find('textarea');
        $(textarea).each(function (i, item) {
            $(item).text('');
        });

        var checkBoxInputs = $(this).closest('tr').find('input[type="checkbox"]');
        $(checkBoxInputs).each(function (i, item) {
            $(item).attr('checked', 'checked');
        });

        $(this).closest('tr').hide('slow');
    });
</script>

<div class="mf-gray">
    <a class="cl mf-toggle"><?=$hideText?></a>
<div>
<table class="mf-fields-list active">
<?php foreach($arResult['ITEMS'] as $code => $value): ?>
    <?=$value['HTML_INPUT']?>
<?php endforeach; ?>
</table>