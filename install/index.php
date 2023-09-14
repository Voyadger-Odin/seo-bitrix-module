<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
    die();
}


use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Application;
use \Bitrix\Main\Entity\Base;
use \Bitrix\Main\EventManager;

Loc::loadMessages(__FILE__);
Class voyadger_seo extends CModule
{
    var $MODULE_ID = 'voyadger.seo'; // NOTE using "var" for bitrix rules

    var $MODULE_VERSION;

    var $MODULE_VERSION_DATE;

    var $MODULE_NAME;

    var $MODULE_DESCRIPTION;

    var $MODULE_GROUP_RIGHTS;

    var $PARTNER_NAME;

    var $PARTNER_URI;

    public function __construct()
    {
        $this->MODULE_ID = 'voyadger.seo'; // NOTE for showing module in /bitrix/admin/partner_modules.php?lang=ru

        $arModuleVersion = [];
        include __DIR__ . '/version.php';
        if (!empty($arModuleVersion['VERSION']))
        {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }

        $this->MODULE_NAME = Loc::getMessage('VOYADGER_SEO_REDIRECT_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('VOYADGER_SEO_REDIRECT_MODULE_DESCRIPTION');
        $this->MODULE_GROUP_RIGHTS = 'Y';

        $this->PARTNER_NAME = 'Voyadger';
        $this->PARTNER_URI = 'https://t.me/rosetomorrow';
    }

    public function GetPath($notDocumentRoot=false){
        if ($notDocumentRoot){
            return str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__));
        }
        return dirname(__DIR__);
    }

    public function isVersionD7(){
        return CheckVersion(\Bitrix\Main\ModuleManager::getVersion('main'), '14.00.00');
    }

    // DB
    public function InstallDB()
    {
        Loader::includeModule($this->MODULE_ID);

        // URLS
        if (!Application::getConnection(\Voyadger\Seo\UrlsTable::getConnectionName())->isTableExists(
            Base::getInstance('\Voyadger\Seo\UrlsTable')->getDBTableName()
        )){
            Base::getInstance('\Voyadger\Seo\UrlsTable')->createDbTable();
        }

        // REDIRECTS
        if (!Application::getConnection(\Voyadger\Seo\RedirectsTable::getConnectionName())->isTableExists(
            Base::getInstance('\Voyadger\Seo\RedirectsTable')->getDBTableName()
        )){
            Base::getInstance('\Voyadger\Seo\RedirectsTable')->createDbTable();
        }
    }

    function UnInstallDB()
    {
        Loader::includeModule($this->MODULE_ID);

        // URLS
        Application::getConnection(\Voyadger\Seo\UrlsTable::getConnectionName())->
            queryExecute(
                'drop table if exists ' . Base::getInstance('\Voyadger\Seo\UrlsTable')->getDBTableName()
        );

        // REDIRECTS
        Application::getConnection(\Voyadger\Seo\RedirectsTable::getConnectionName())->
        queryExecute(
            'drop table if exists ' . Base::getInstance('\Voyadger\Seo\RedirectsTable')->getDBTableName()
        );
    }

    // Events
    public function InstallEvents()
    {
        EventManager::getInstance()->registerEventHandler(
            'main',
            'OnPageStart',
            $this->MODULE_ID,
            '\Voyadger\Seo\Event',
            'eventHandlerTagsOverwrite'
        );

        RegisterModuleDependences('main', 'OnProlog', $this->MODULE_ID, '\Voyadger\Seo\Event', 'eventhandlerRedirect');
    }

    public function UnInstallEvents()
    {
        EventManager::getInstance()->unRegisterEventHandler(
            'main',
            'OnPageStart',
            $this->MODULE_ID,
            '\Voyadger\Seo\Event',
            'eventHandlerTagsOverwrite'
        );

        UnRegisterModuleDependences('main', 'OnProlog', $this->MODULE_ID, '\Voyadger\Seo\Event', 'eventhandlerRedirect');
    }

    public function DoInstall()
    {

        global $APPLICATION;
        if (version_compare(PHP_VERSION, '8.0.0', '<'))
        {
            $APPLICATION->ThrowException(Loc::getMessage('VOYADGER_SEO_REQUIREMENTS_PHP_VERSION'));
            return false;
        }

        if (!$this->isVersionD7())
        {
            $APPLICATION->ThrowException(Loc::getMessage('VOYADGER_SEO_REQUIREMENTS_D7'));
            return false;
        }

        RegisterModule($this->MODULE_ID);
        RegisterModuleDependences('main', 'OnPageStart', $this->MODULE_ID);

        $cresult = CopyDirFiles($this->GetPath() . "/install/admin", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin", true, true);

        $this->InstallDB();
        $this->InstallEvents();

        $APPLICATION->IncludeAdminFile(GetMessage("VOYADGER_SEO_INSTALL"), $this->getPath() . '/install/step1.php');
    }

    public function DoUninstall()
    {
        global $APPLICATION;


        if ($_REQUEST['step'] < 2){
            $APPLICATION->IncludeAdminFile(GetMessage("VOYADGER_SEO_DEL"), $this->GetPath() . '/install/unstep1.php');
        }
        elseif ($_REQUEST['step'] == 2){
            $this->UninstallFiles();

            if ($_REQUEST['savedata'] != 'Y'){
                $this->UninstallDB();
            }

            $this->UnInstallEvents();

            DeleteDirFiles($this->GetPath() . "/install/admin", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin");

            UnRegisterModuleDependences('main', 'OnPageStart', $this->MODULE_ID);
            UnRegisterModule($this->MODULE_ID);

            $APPLICATION->IncludeAdminFile(GetMessage("VOYADGER_SEO_DEL"), $this->GetPath() . '/install/unstep2.php');
        }
    }
}