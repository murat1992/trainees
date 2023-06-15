<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

//Подготовим параметры полей ввода.
//Поля с типом textarea будут размещены отдельно
$inputs = array();
$textareas = array();
foreach ($arResult["QUESTIONS"] as $key => $quest) {
    $answer = $quest["STRUCTURE"][0];
    if ($answer["ACTIVE"] == "N") {
        continue;
    }
    
    $input = array(
        "ID"        => $key,
        "CAPTION"   => $quest["CAPTION"].($quest["REQUIRED"] == 'Y' ? "*" : ""),
        "ERROR"     => "",
        "HTML_CODE" => ""
    );
    
    if ($arParams['USE_EXTENDED_ERRORS'] == 'Y' && isset($arResult["FORM_ERRORS"][$key])) {
        $input["ERROR"] = htmlspecialcharsbx($arResult["FORM_ERRORS"][$key]);
    }
    
    $args = 'class="input__input"';
    $args .= ' id="'.$input["ID"].'"';
    $args .= $quest["REQUIRED"] == "Y" ? " required" : "";
    switch ($answer["FIELD_TYPE"]) {
        case "textarea":
            $value = CForm::GetTextAreaValue($answer["ID"], $answer, $arResult["arrVALUES"]);
            $input["HTML_CODE"] = CForm::GetTextAreaField($answer["ID"], $value, "", $args);
            $textareas[] = $input;
            break;
        case "email":
            $value = CForm::GetEmailValue($answer["ID"], $answer, $arResult["arrVALUES"]);
            $input["HTML_CODE"] = CForm::GetEmailField($answer["ID"], $value, "", $args);
            $inputs[] = $input;
            break;
        case "text":
            $value = CForm::GetTextValue($answer["ID"], $answer, $arResult["arrVALUES"]);
            if ($answer["FIELD_PARAM"] == "tel") {
                $args .= " data-inputmask=\"'mask': '+79999999999', 'clearIncomplete': 'true'\"";
                $args .= ' x-autocompletetype="phone-full"';
                $input["HTML_CODE"] = sprintf(
                    '<input type="tel" name="%s" value="%s" maxlength="12" %s>',
                    "form_text_".$answer["ID"],
                    $value,
                    $args
                );
            } else {
                $input["HTML_CODE"] = CForm::GetTextField($answer["ID"], $value, "", $args);
            }
            $inputs[] = $input;
            break;
    }
}
?>

<div class="contact-form">
    <div class="contact-form__head">
        <div class="contact-form__head-title"><?=$arResult["FORM_TITLE"]?></div>
        <div class="contact-form__head-text"><?=$arResult["FORM_DESCRIPTION"]?></div>
    </div>

    <form class="contact-form__form" name="<?=$arResult["arForm"]["SID"]?>"
            action="<?=POST_FORM_ACTION_URI?>" method="POST">
        <?=bitrix_sessid_post()?>
        <input type="hidden" name="WEB_FORM_ID" value="<?=$arParams['WEB_FORM_ID']?>">
        <input type="hidden" name="web_form_submit" value="Y">
        <input type="hidden" name="web_form_submit" value="Y">

        <div class="contact-form__form-inputs">
        <?foreach($inputs as $input):?>
            <div class="input contact-form__input">
                <label class="input__label" for="<?=$input["ID"]?>">
                    <div class="input__label-text">
                        <?=$input["CAPTION"]?>
                    </div>
                    <?=$input["HTML_CODE"]?>
                    <div class="input__notification"><?=$input["ERROR"]?></div>
                </label>
            </div>
        <?endforeach;?>
        </div>

        <div class="contact-form__form-message">
        <?foreach($textareas as $input):?>
            <div class="input">
                <label class="input__label" for="<?=$input["ID"]?>">
                    <div class="input__label-text">
                        <?=$input["CAPTION"]?>
                    </div>
                    <?=$input["HTML_CODE"]?>
                    <div class="input__notification"><?=$input["ERROR"]?></div>
                </label>
            </div>
        <?endforeach;?>
        </div>

        <div class="contact-form__bottom">
            <div class="contact-form__bottom-policy"><?=GetMessage("FORM_BOTTOM_POLICY")?>
            </div>
            <button class="form-button contact-form__bottom-button"
                    data-success="<?=GetMessage("FORM_DATA_SUCCESS")?>"
                    data-error="<?=GetMessage("FORM_DATA_ERROR")?>">
                <div class="form-button__title">
                <?=$arResult["arForm"]["BUTTON"] == "" ? GetMessage("FORM_SUBMIT") : $arResult["arForm"]["BUTTON"]?>
                </div>
            </button>
        </div>

    </form>
</div>