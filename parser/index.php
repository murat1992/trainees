<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
if (!$USER->IsAdmin()) {
    LocalRedirect('/');
}

require_once("parser.php");
\Bitrix\Main\Loader::includeModule('iblock');

$IBLOCK_ID = 20;
$el = new CIBlockElement;

$rsElements = CIBlockElement::GetList([], ['IBLOCK_ID' => $IBLOCK_ID], false, false, ['ID']);
while ($element = $rsElements->GetNext()) {
    CIBlockElement::Delete($element['ID']);
}

$parser = new Parser($IBLOCK_ID);
//$parser->printPropertyHTML();

$funcForMultipleString = function ($value, $values) {
    $value = str_replace('\n', '', $value);
    if (stripos($value, '•') !== false) {
        $value = explode('•', $value);
        array_splice($value, 0, 1);
        foreach ($value as &$str) {
            $str = trim($str);
        }
    }
    return $value;
};
$parser->addProcessor('REQUIRE', $funcForMultipleString);
$parser->addProcessor('DUTY', $funcForMultipleString);
$parser->addProcessor('CONDITIONS', $funcForMultipleString);

$parser->addProcessor('SALARY_VALUE', function ($value, &$values) {
    if ($value == '-') {
        $value = "";
    } elseif ($value == "по договоренности") {
        $values['SALARY_TYPE'] = "договорная";
        $value = "";
    }else {
        $arSalary = explode(' ', $value);
        if ($arSalary[0] == 'от' || $arSalary[0] == 'до') {
            $value = $arSalary[0];
            $values['SALARY_TYPE'] = $arSalary[0];
            array_splice($arSalary, 0, 1);
            $value = implode(' ', $arSalary);
        } else {           
            $values['SALARY_TYPE'] = "=";
        }
    }
    return $value;
});

if (($handle = fopen("vacancy.csv", "r")) !== false) {
    $head = fgetcsv($handle, 1000, ",");
    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
        $PROP['ACTIVITY'] = $data[9];
        $PROP['FIELD'] = $data[11];
        $PROP['OFFICE'] = $data[1];
        $PROP['LOCATION'] = $data[2];
        $PROP['REQUIRE'] = $data[4];
        $PROP['DUTY'] = $data[5];
        $PROP['CONDITIONS'] = $data[6];
        $PROP['EMAIL'] = $data[12];
        $PROP['DATE'] = date('d.m.Y');
        $PROP['TYPE'] = $data[8];
        $PROP['SALARY_TYPE'] = '';
        $PROP['SALARY_VALUE'] = $data[7];
        $PROP['SCHEDULE'] = $data[10];
        
        $PROP = $parser->parse($PROP);
        
        $arLoadProductArray = [
            "MODIFIED_BY" => $USER->GetID(),
            "IBLOCK_SECTION_ID" => false,
            "IBLOCK_ID" => $IBLOCK_ID,
            "PROPERTY_VALUES" => $PROP,
            "NAME" => $data[3],
            "ACTIVE" => end($data) ? 'Y' : 'N',
        ];
        
        if ($PRODUCT_ID = $el->Add($arLoadProductArray)) {
            echo "Добавлен элемент с ID : " . $PRODUCT_ID . "<br>";
        } else {
            echo "Error: " . $el->LAST_ERROR . '<br>';
        }
    }
    
    fclose($handle);
}