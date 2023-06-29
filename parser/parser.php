<?php
class Parser
{
    private $arrValuesOfList;
    private $fields;
    private $processors;
    private $name;
    private $iBlockID;
    
    public function __construct($id)
    {
        $this->iBlockID = $id;
        $this->arrValuesOfList = [];
        
        $this->fields = [];
        $this->values = [];
        $this->arrValuesOfList = [];
        
        $rsProp = CIBlockProperty::GetList([], ['IBLOCK_ID' => $this->iBlockID]);
        while ($arProp = $rsProp->Fetch()) {
            $this->fields[$arProp['CODE']] = $arProp;
            if ($arProp['PROPERTY_TYPE'] == 'L') {
                $this->arrValuesOfList[$arProp['CODE']] = [];
            }
            $this->processors[$arProp['CODE']] = [];
        }
        
        $rsProp = CIBlockPropertyEnum::GetList([], ['IBLOCK_ID' => $this->iBlockID]);
        while ($arProp = $rsProp->Fetch()) {
            $this->arrValuesOfList[$arProp['PROPERTY_CODE']][$arProp['ID']] = $arProp['VALUE'];
            if($arProp['DEF'] == 'Y') {
                $this->fields[$arProp['PROPERTY_CODE']]['DEFAULT_VALUE'] = $arProp['VALUE'];
            }
        }
    }
    
    public function addProcessor($code, $callable) {
        $this->processors[$code][] = $callable;
    }
    
    private function commonProcess($str) {
        if (!is_string($str)) {
            return $str;
        }
        return trim($str);
    }
    
    private function getIdValue($code, $value) {
        if (empty($value)) {
            return $value;
        }
        
        $arSimilar = [];
        foreach ($this->arrValuesOfList[$code] as $id => $vl) {
            $similar = similar_text(mb_strtolower($value), mb_strtolower($vl));
            $arSimilar[$similar] = $id;
        }
        ksort($arSimilar);
        $id = array_pop($arSimilar);
        
        return $id;
    }
    
    public function parse($values, $printHTML = false) {
        $copyValues = [];
        
        foreach ($values as $code => &$value) {
            $copyValues[$code] = $value;
            
            if (empty($value)) {
                $value = $this->fields[$code]['DEFAULT_VALUE'];
            }
            
            $value = $this->commonProcess($value);
            
            foreach ($this->processors[$code] as $processor) {
                $value = $processor($value, $values);
            }
        }
        
        //Значение у списочных свойств заменяем на id значения
        foreach ($values as $code => &$value) {
            if ($this->fields[$code]["PROPERTY_TYPE"] == 'L') {
                $value = $this->getIdValue($code, $value);
            }
        }
        
        if ($printHTML) {
            echo "<table border=1>";
            echo "<tr>";
            echo "<th>Свойство</th>";
            echo "<th>Было</th>";
            echo "<th>Стало</th>";
            echo "</tr>";
            
            foreach ($values as $code => &$value) {
                echo "<tr>";
                echo "<td>$code</td>";
                echo "<td>".$copyValues[$code]."</td>";
                if (is_array($value)) {
                    echo "<td>";
                    print_r($value);
                    echo "</td>";
                } else {
                    echo "<td>".$value."</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        }
        
        return $values;
    }
    
    public function printPropertyHTML()
    {
        echo "<h2>Свойства инфоблока</h2>";
        echo "<table border=1>";
        echo "<tr>";
        echo "<th>Наименование</th>";
        echo "<th>CODE</th>";
        echo "<th>Значение по умолчанию</th>";
        echo "<th>Список значений</th>";
        echo "</tr>";
        
        foreach ($this->fields as $key => $value) {
            echo "<tr>";
            echo "<td>".$value['NAME']."</td>";
            echo "<td>$key</td>";
            echo "<td>".$value['DEFAULT_VALUE']."</td>";
            echo "<td>";
            foreach ($this->arrValuesOfList[$key] as $kl => $vl) {
                echo "$vl [$kl]<br>";
            }
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
};