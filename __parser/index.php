<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

if (!$USER->IsAdmin()) {
    LocalRedirect('/');
}

\Bitrix\Main\Loader::includeModule('iblock');
$el = new CIBlockElement;
$IBLOCK_ID = 8;


//Собираем все значения свойств списочного типа.
//Если есть значение по умолчанию, запоминаем.
$arProps = [];
$arPropsDefaultValue = [
    "SEX" => "",
    "EDUCATION" => "",
    "EXPERIENCE" => "",
    "SCHEDULE" => "",
];
$rsProp = CIBlockPropertyEnum::GetList(
    ["SORT" => "ASC", "VALUE" => "ASC"],
    ['IBLOCK_ID' => $IBLOCK_ID]
);
while ($arProp = $rsProp->Fetch()) {
    $key = trim($arProp['VALUE']);
    $arProps[$arProp['PROPERTY_CODE']][$key] = $arProp['ID'];
    if ($arProp['DEF'] == "Y") {
        $arPropsDefaultValue[$arProp['PROPERTY_CODE']] = $key;
    }
}

//Очищаем существующие вакансии
$rsElements = CIBlockElement::GetList([], ['IBLOCK_ID' => $IBLOCK_ID], false, false, ['ID']);
while ($element = $rsElements->GetNext()) {
    CIBlockElement::Delete($element['ID']);
}

//Для читаемости вынес в отдельные функции
function findExperience($str)
{
    $str = str_replace("-", " ", $str);
    $parts = explode(' ', $str);
    
    $result = "";
    if (in_array("1", $parts)) {
        $result = "1 год";
    } elseif (in_array("от года", $parts)) {
        $result = "1 год";
    } elseif (in_array("2", $parts)) {
        $result = "2 года";
    } elseif (in_array("3", $parts)) {
        $result = "3 года";
    } elseif (in_array("4", $parts)) {
        $result = "4 года";
    } elseif (in_array("5", $parts)) {
        $result = "более 5 лет";
    }
    
    return $result;
}
function findEducation($str)
{
    $str = str_replace(['/', '-', '(', ')'], " ", $str);
    $parts = explode(' ', $str);
    
    $isStudent = false;
    $isMiddle = false;
    $isHigher = false;
    $isProfessional = false;
    
    $result = "";
    
    foreach ($parts as $part) {
        switch ($part) {
            case "среднее":
            case "среднего":
                $isMiddle = true;
                break;
            case "студент":
                $isStudent = true;
                break;
            case "высшее":
                $isHigher = true;
                break;
            case "специальное":
            case "специального":
            case "профессиональное":
            case "профессионального":
            case "техническое":
                $isProfessional = true;
                break;
        }
    }
    
    if ($isMiddle) {
        if($isProfessional) {
            $result = "Среднее специальное";
        } else {
            $result = "Среднее";
        }
    } elseif ($isStudent) {
        $result = "Неполное высшее";
    } elseif ($isHigher) {
        $result = "Высшее";
    }
    
    return $result;
}
function findShedule($str)
{
    $result = "";
    switch($str) {
        case "Полная занятость":
            $result = "Полный рабочий день";
            break;
        case "Проектная/Временная работа":
            $result = "Неполная занятость";
            break;
        case "Стажировка":
            $result = "Свободный график";
            break;
    }
    return $result;
}
//

$handle = fopen("vacancy.csv", "r");
if ($handle !== false) {
    $head = fgetcsv($handle, 1000, ',');
    while (($data = fgetcsv($handle, 1000, ',')) !== false ) {
        $PROP['SEX'] = $arPropsDefaultValue['SEX'];
        $PROP['AGE'] = '';
        $PROP['EDUCATION'] = $arPropsDefaultValue['EDUCATION'];
        $PROP['EXPERIENCE'] = $arPropsDefaultValue['EXPERIENCE'];
        $PROP['SCHEDULE'] = $arPropsDefaultValue['SCHEDULE'];
        $PROP['REMUNERATION'] = $data[7];
        $PROP['SKILLS'] = $data[4];
        $PROP['FIRM'] = $data[1];
        $PROP['PERSON'] = '';
        $PROP['PHONE'] = '';
        $PROP['EMAIL'] = $data[12];
        $PROP['URL'] = '';
        
        //Пытаемся из колонки "Требования" найти подходящее значения для EXPERIENCE и EDUCATION
        $requires = explode("\n", $data[4]);
        foreach($requires as $value) {
            $value = trim($value, " \n\r\t\v\x00•.-");
            $value = mb_strtolower($value);
            
            if (stripos($value, 'опыт') !== false) {
                $value = findExperience($value);
                if (!empty($value)) {
                    $PROP['EXPERIENCE'] = $value;
                }
            } elseif (stripos($value, 'образование') || stripos($value, 'студент')) {
                $value = findEducation($value);
                if (!empty($value)) {
                    $PROP['EDUCATION'] = $value;
                }
            }
        }
        
        switch (trim($data[9])) {
            case "Полная занятость":
                $PROP['SCHEDULE'] = "Полный рабочий день";
                break;
            case "Проектная/Временная работа":
                $PROP['SCHEDULE'] = "Неполная занятость";
                break;
            case "Стажировка":
                $PROP['SCHEDULE'] = "Свободный график";
                break;
        }
        
        //Свойства списочного типа заполняем идентификатором значения.
        foreach ($PROP as $key => &$value) {
            $value = trim($value);
            $value = str_replace('\n', '', $value);
            if ($arProps[$key]) {
                $value = $arProps[$key][$value];
            }
        }
        
        
        $arLoadProductArray = [
            "MODIFIED_BY" => $USER->GetID(),
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