<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

CModule::IncludeModule('voyadger.seo');

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page;
use Bitrix\Sale\Cashbox;
use Voyadger\Seo\RedirectsTable;

$publicMode = $adminPage->publicMode;
$selfFolderUrl = $adminPage->getSelfFolderUrl();

$saleModulePermissions = $APPLICATION->GetGroupRight("voyadger.seo");
if ($saleModulePermissions < "W")
  $APPLICATION->AuthForm(GetMessage("VOYADGER_SEO_ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
CJSCore::Init(array('clipboard'));

$tableId = RedirectsTable::getTableName();
$instance = \Bitrix\Main\Application::getInstance();
$context = $instance->getContext();
$lang = $context->getLanguage();
$request = $context->getRequest();

$oSort = new CAdminSorting($tableId, "ID", "asc");
$lAdmin = new CAdminUiList($tableId, $oSort);
$listSite = array();
$sitesQueryObject = CSite::getList($bySite = "sort", $orderSite = "asc", array("ACTIVE" => "Y"));
while ($site = $sitesQueryObject->fetch())
{
  $listSite[$site["LID"]] = $site["NAME"]." [".$site["LID"]."]";
}
$filterFields = array(
  array(
    "id" => "URL",
    "name" => "URL",
    "filterable" => "%",
    "quickSearch" => "%",
    "default" => true
  ),
  array(
    "id" => "ACTIVE",
    "name" => GetMessage("VOYADGER_SEO_URL_ACTIVE"),
    "type" => "list",
    "items" => array(
      "Y" => "��",
      "N" => "���"
    ),
    "filterable" => "",
    "default" => true
  )
);

$filter = array();

$lAdmin->AddFilter($filterFields, $filter);

if (($ids = $lAdmin->GroupAction()) && $saleModulePermissions >= "W")
{
  if ($request->get('action_target')=='selected')
  {
    $ids = array();
    $dbRes = RedirectsTable::getList(
      array(
        'select' => array('ID'),
        'filter' => $filter,
        'order' => array(ToUpper($by) => ToUpper($order))
      )
    );

    while ($arResult = $dbRes->fetch())
      $ids[] = $arResult['ID'];
  }

  foreach ($ids as $id)
  {
    if ((int)$id <= 0)
      continue;

    switch ($_REQUEST['action'])
    {
      case "delete":

        $result = RedirectsTable::delete($id);
        if (!$result->isSuccess())
        {
          if ($result->getErrorMessages())
            $lAdmin->AddGroupError(join(', ', $result->getErrorMessages()), $id);
        }

        break;

      case "activate":
      case "deactivate":

        $arFields = array(
          "ACTIVE" => ($_REQUEST['action'] == 'activate') ? 'Y' : 'N'
        );

        $result = RedirectsTable::update($id, $arFields);
        if (!$result->isSuccess())
        {
          if ($result->getErrorMessages())
            $lAdmin->AddGroupError(join(', ', $result->getErrorMessages()), $id);
        }

        break;
    }
  }
  if ($lAdmin->hasGroupErrors())
  {
    $adminSidePanelHelper->sendJsonErrorResponse($lAdmin->getGroupErrors());
  }
  else
  {
    $adminSidePanelHelper->sendSuccessResponse();
  }
}

$params = array(
  'select' => array('*'),
  'filter' => $filter
);

global $by, $order;
$by = isset($by) ? $by : "ID";
$order = isset($order) ? $order : "ASC";
$params['order'] = array($by => $order);

$dbResultList = new CAdminUiResult(RedirectsTable::getList($params), $tableId);
$dbResultList->NavStart();

$headers = array(
  array("id" => "ID", "content" => 'ID', "sort" => "ID", "default" => true),
  array("id" => "active", "content" => GetMessage("VOYADGER_SEO_URL_ACTIVE"), "sort" => "active", "default" => true),
  array("id" => "url_from", "content" => 'URL FROM', "sort" => "url_from", "default" => true),
  array("id" => "url_to", "content" => "URL TO", "sort" => "url_to", "default" => true),
  array("id" => "redirect_type", "content" => "REDIRECT TYPE", "sort" => "redirect_type", "default" => true),
);


$lAdmin->SetNavigationParams($dbResultList, array("BASE_LINK" => $selfFolderUrl."voyadger_seo_redirects_edit.php"));

$lAdmin->AddHeaders($headers);

$visibleHeaders = $lAdmin->GetVisibleHeaderColumns();

while ($seoTag = $dbResultList->Fetch())
{
  $editUrl = $selfFolderUrl."voyadger_seo_redirects_edit.php?ID=".$seoTag['ID']."&lang=".LANGUAGE_ID;
  $editUrl = $adminSidePanelHelper->editUrlToPublicPage($editUrl);
  $row =& $lAdmin->AddRow($seoTag['ID'], $seoTag, $editUrl, GetMessage("SALE_EDIT_DESCR"));

  $row->AddField("ID", "<a href=\"".$editUrl."\">".$seoTag['ID']."</a>");
  $row->AddField("ACTIVE", (($seoTag['ACTIVE']=="Y") ? GetMessage("SALE_YES") : GetMessage("SALE_NO")));
  $row->AddField("URL FROM", htmlspecialcharsbx($seoTag['NAME']));
  $row->AddField("URL TO", htmlspecialcharsbx($seoTag['NAME']));
  $row->AddField("REDIRECT TYPE", htmlspecialcharsbx($seoTag['NAME']));



  $arActions = array(
    array(
      "ICON" => "edit",
      "TEXT" => GetMessage("VOYADGER_SEO_URL_CHANGE_ICON"),
      "TITLE" => GetMessage("VOYADGER_SEO_URL_CHANGE_ICON"),
      "LINK" => $editUrl,
      "DEFAULT" => true,
    ),
  );
  if ($saleModulePermissions >= "W")
  {
    $arActions[] = array("SEPARATOR" => true);
    $arActions[] = array(
      "ICON" => "delete",
      "TEXT" =>  GetMessage("VOYADGER_SEO_URL_DELETE_ICON"),
      "TITLE" => GetMessage("VOYADGER_SEO_URL_DELETE_ICON"),
      "ACTION" => "if(confirm('".GetMessage('SEO_URL_DELETE_CONFIRM', array('#URL#' => $seoTag['URL']))."')) ".$lAdmin->ActionDoGroup($seoTag['ID'], "delete"),
    );
  }

  $row->AddActions($arActions);
}

if ($saleModulePermissions == "W")
{
  $lAdmin->AddGroupActionTable(
    array(
      "delete" => GetMessage("VOYADGER_SEO_URL_DELETE"),
      "activate" => GetMessage("VOYADGER_SEO_URL_ACTIVATE"),
      "deactivate" => GetMessage("VOYADGER_SEO_URL_DEACTIVATE"),
    )
  );
  $addUrl = $selfFolderUrl."voyadger_seo_redirects_edit.php?lang=".$lang;
  $addUrl = $adminSidePanelHelper->editUrlToPublicPage($addUrl);
  $aContext = array(
    array(
      "TEXT" => GetMessage("VOYADGER_SEO_URL_ADD_NEW"),
      "LINK" => $addUrl,
      "ICON" => "btn_new",
    )
  );
  $lAdmin->setContextSettings(array("pagePath" => $selfFolderUrl."voyadger_seo_redirects_edit.php"));
  $lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("VOYADGER_SEO_URL_LIST_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<?

$lAdmin->DisplayFilter($filterFields);
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");

?>
