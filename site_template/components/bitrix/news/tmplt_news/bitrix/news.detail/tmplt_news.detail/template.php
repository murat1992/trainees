<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$detailText = "";
if ($arResult["NAV_RESULT"]) {
    if ($arParams["DISPLAY_TOP_PAGER"]) {
        $detailText .= $arResult["NAV_STRING"]."<br />";
    }
    $detailText .= $arResult["NAV_TEXT"];
    if ($arParams["DISPLAY_BOTTOM_PAGER"]) {
        $detailText .= "<br />".$arResult["NAV_STRING"];
    }
} else {
    $detailText = $arResult["DETAIL_TEXT"];
}

?>
<div class="article-card">
    <div class="article-card__title"><?=$arResult["NAME"]?></div>
    <div class="article-card__date"><?=$arResult["DISPLAY_ACTIVE_FROM"]?></div>
    <div class="article-card__content">
        <div class="article-card__image sticky"><img 
                                                     src="<?=$arResult["DETAIL_PICTURE"]["SRC"]?>"
                                                     alt="" data-object-fit="cover"/>
        </div>
        <div class="article-card__text">
            <div class="block-content" data-anim="anim-3"><?=$detailText?></div>
            <a class="article-card__button" href="<?=$arResult["LIST_PAGE_URL"]?>">
                <?=GetMessage("T_NEWS_DETAIL_BACK")?>
            </a>
        </div>
    </div>
</div>