<?php

class dev_mymodule extends CModule
{
    var $MODULE_ID = "dev.mymodule";
    var $MODULE_NAME;
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;

    function __construct()
    {
        $arModuleVersion = array();
        include __DIR__ . '/version.php';

        $this->MODULE_NAME = getMessage('MYMOD_NAME');
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_DESCRIPTION = getMessage('MYMOD_DESCRIPTION');
    }

    function DoInstall()
    {
        RegisterModule($this->MODULE_ID);

        RegisterModuleDependences(
            'main',
            'OnUserTypeBuildList',
            $this->MODULE_ID,
            '\Dev\Mymodule\UserField\Types\ComplexType',
            'GetUserTypeDescription'
        );
        RegisterModuleDependences(
            'iblock',
            'OnIBlockPropertyBuildList',
            $this->MODULE_ID,
            '\Dev\Mymodule\IBlockProp\Types\ComplexType',
            'GetUserTypeDescription'
        );

        CopyDirFiles(__DIR__."/components", $_SERVER["DOCUMENT_ROOT"]."/local/components", true, true);
    }

    function DoUninstall()
    {
        UnRegisterModuleDependences(
            'main',
            'OnUserTypeBuildList',
            $this->MODULE_ID,
            '\Dev\Mymodule\UserField\Types\ComplexType',
            'GetUserTypeDescription'
        );
        UnRegisterModuleDependences(
            'iblock',
            'OnIBlockPropertyBuildList',
            $this->MODULE_ID,
            '\Dev\Mymodule\IBlockProp\Types\ComplexType',
            'GetUserTypeDescription'
        );

        DeleteDirFilesEx("/local/components/dev/mymodule.field.complex");

        UnRegisterModule($this->MODULE_ID);
    }
}