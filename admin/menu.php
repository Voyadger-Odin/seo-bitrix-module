<?
/**
 * Bitrix vars
 * @global CMain $APPLICATION
 * @global CAdminMenu $this
 */

use \Bitrix\Seo\Adv;
use \Bitrix\Seo\Engine;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\ModuleManager;

if($APPLICATION->GetGroupRight('voyadger.seo') > "D")
{
    if(ModuleManager::isModuleInstalled('voyadger.seo'))
    {
        Loc::loadMessages(__FILE__);
        $aMenu = array(
            /*
            array(
                'parent_menu' => 'global_menu_services',
                'sort' => 0,
                'text' => Loc::getMessage('VOYADGER_SEO_NAME_MENU'),
                'title' => Loc::getMessage('VOYADGER_SEO_TITLE_MENU'),
                'url' => 'voyadger_seo_tags_overwrite_list.php',
                'items_id' => 'menu_references',
            ),
            */
            array(
                'parent_menu' => 'global_menu_services',
                'sort' => 0,
                'text' => Loc::getMessage('VOYADGER_SEO_NAME_MENU'),
                'title' => Loc::getMessage('VOYADGER_SEO_TITLE_MENU'),
                'items_id' => 'menu_references',
                "items" => array(
                    array(
                        'text' => Loc::getMessage('VOYADGER_SEO_NAME_MENU_TAGS_OVERWRITE'),
                        'title' => Loc::getMessage('VOYADGER_SEO_TITLE_MENU_TAGS_OVERWRITE'),
                        'url' => 'voyadger_seo_tags_overwrite_list.php',
                    ),
                    array(
                        'text' => Loc::getMessage('VOYADGER_SEO_NAME_MENU_REDIRECTS'),
                        'title' => Loc::getMessage('VOYADGER_SEO_TITLE_MENU_REDIRECTS'),
                        'url' => 'voyadger_seo_redirects_list.php',
                    ),
                ),
            ),
        );
        return $aMenu;
    }
}
return false;
