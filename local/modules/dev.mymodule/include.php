<?php

\Bitrix\Main\Loader::registerAutoLoadClasses('dev.mymodule', [
    '\Dev\Mymodule\IBlockProp\Types\ComplexType' => 'lib/iblockprop/types/complextype.php',
    '\Dev\Mymodule\UserField\Types\ComplexType' => 'lib/userfield/types/complextype.php',
    '\Dev\Mymodule\Helpers\HTMLHelper' => 'classes/htmlhelper.php'
]);