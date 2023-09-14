<?
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Bitrix\Main\Page;
use Bitrix\Main\Config;
use Voyadger\Seo\RedirectsTable;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$selfFolderUrl = $adminPage->getSelfFolderUrl();
$listUrl = $selfFolderUrl."voyadger_seo_redirects_list.php?lang=" . $lang;
$listUrl = $adminSidePanelHelper->editUrlToPublicPage($listUrl);

$saleModulePermissions = $APPLICATION->GetGroupRight("slv.seo");
if ($saleModulePermissions < "W")
  $APPLICATION->AuthForm(GetMessage("VOYADGER_SEO_ACCESS_DENIED"));

Loc::loadMessages(__FILE__);

$listSite = array();
$sitesQueryObject = CSite::getList($bySite = "sort", $orderSite = "asc", array("ACTIVE" => "Y"));
while ($site = $sitesQueryObject->fetch())
{
  $listSite[$site["LID"]] = $site["NAME"]." [".$site["LID"]."]";
}

\Bitrix\Main\Loader::includeModule('slv.seo');

$instance = Application::getInstance();
$context = $instance->getContext();
$request = $context->getRequest();
$server = $context->getServer();
$lang = $context->getLanguage();
$documentRoot = Application::getDocumentRoot();


$id = (int)$request->get('ID');

$seoTag = array();
$errorMessage = '';

if ($server->getRequestMethod() == "POST"
  && ($request->get('save') !== null || $request->get('apply') !== null)
  && $saleModulePermissions == "W"
  && check_bitrix_sessid()
)
{
  $adminSidePanelHelper->decodeUriComponent($request);

    var_dump($request->getPost('redirect_type'));
    echo '<br>';
    var_dump($request->getPost('url_from'));
    echo '<br>';
    var_dump($request->getPost('url_to'));
    echo '<br>';
    var_dump($request->getPost('ACTIVE'));

  $seoTag = array(
      'active' => ($request->get('active') == 'Y') ? 'Y' : 'N',
    'url_from' => $request->getPost('url_from'),
    'url_to' => $request->getPost('url_to'),
    'redirect_type' => $request->getPost('redirect_type'),
  );


  if ($errorMessage === '')
  {
    if ($id > 0)
    {
      $result = RedirectsTable::update($id, $seoTag);
    }
    else
    {
      $result = RedirectsTable::add($seoTag);
      $id = $result->getId();
    }

    if ($result->isSuccess())
    {
      if ($adminSidePanelHelper->isAjaxRequest())
      {
        $adminSidePanelHelper->sendSuccessResponse("base", array("ID" => $id));
      }
      else
      {
        if (strlen($request->getPost("apply")) == 0)
        {
          $adminSidePanelHelper->localRedirect($listUrl);
          LocalRedirect($listUrl);
        }
        else
        {
          $applyUrl = $selfFolderUrl."voyadger_seo_redirects_edit.php?lang=".$lang."&ID=".$id;
          $applyUrl = $adminSidePanelHelper->setDefaultQueryParams($applyUrl);
          LocalRedirect($applyUrl);
        }
      }
    }
    else
    {
      $errorMessage .= implode("\n", $result->getErrorMessages());
    }
  }
  else
  {
    $adminSidePanelHelper->sendJsonErrorResponse($errorMessage);
  }
}

require($documentRoot."/bitrix/modules/main/include/prolog_admin_after.php");


$APPLICATION->SetTitle(($id > 0) ? Loc::GetMessage("VOYADGER_SEO_EDIT_URL", array("#URL#" => $id)) : Loc::GetMessage("VOYADGER_SEO_NEW_URL"));

$aTabs = array(
  array(
    "DIV" => "edit1",
    "TAB" => GetMessage("VOYADGER_SALE_TAB_URL"),
    "ICON" => "sale",
    "TITLE" => GetMessage("VOYADGER_SALE_TAB_URL"),
  )
);

if ($id > 0 && !$request->isPost())
{
  $res = RedirectsTable::getList(array('filter' => array('ID' => $id)));
  $seoTag = $res->fetch();
}




$tabControl = new CAdminForm("tabControl", $aTabs);

$restrictionsHtml = '';

$aMenu = array(
  array(
    "TEXT" => Loc::GetMessage("VOYADGER_SEO_LIST_URL"),
    "LINK" => $listUrl,
    "ICON" => "btn_list"
  )
);

if ($id > 0 && $saleModulePermissions >= "W")
{
  $aMenu[] = array("SEPARATOR" => "Y");

  $deleteUrl = $selfFolderUrl."voyadger_seo_redirects_list.php?action=delete&ID[]=".$id."&lang=".$context->getLanguage()."&".bitrix_sessid_get()."#tb";
  $buttonAction = "LINK";
  if ($adminSidePanelHelper->isPublicFrame())
  {
    $deleteUrl = $adminSidePanelHelper->editUrlToPublicPage($deleteUrl);
    $buttonAction = "ONCLICK";
  }
  $aMenu[] = array(
    "TEXT" => Loc::GetMessage("VOYADGER_SEO_URL_DELETE_ICON"),
    $buttonAction => "javascript:if(confirm('".Loc::GetMessage("VOYADGER_SEO_URL_DELETE_ADD")."')) top.window.location.href='".$deleteUrl."';",
    "WARNING" => "Y",
    "ICON" => "btn_delete"
  );
}
$contextMenu = new CAdminContextMenu($aMenu);
$contextMenu->Show();

if ($errorMessage !== '')
  CAdminMessage::ShowMessage(array("DETAILS"=>$errorMessage, "TYPE"=>"ERROR", "MESSAGE"=>Loc::GetMessage("VOYADGER_SEO_ERROR"), "HTML"=>true));

CAdminMessage::ShowNote(Loc::getMessage('ADD_INFO'));
$tabControl->BeginEpilogContent();
echo GetFilterHiddens("filter_");
echo bitrix_sessid_post();


?>
  <input type="hidden" name="Update" value="Y">
  <input type="hidden" name="lang" value="<?=$context->getLanguage();?>">
  <input type="hidden" name="ID" value="<?=$id;?>" id="ID">

<?
$tabControl->EndEpilogContent();
$actionUrl = $APPLICATION->GetCurPage()."?ID=".$id."&lang=".$lang;
$actionUrl = $adminSidePanelHelper->setDefaultQueryParams($actionUrl);
$tabControl->Begin(array("FORM_ACTION" => $actionUrl));
$tabControl->BeginNextFormTab();
if ($id > 0)
  $tabControl->AddViewField("ID", "ID:", $id);


$active = isset($seoTag['active']) ? $seoTag['active'] : 'Y';
$tabControl->AddCheckBoxField("active", GetMessage("VOYADGER_SEO_URL_ACTIVE").':', false, 'Y', $active === 'Y');


$url = $request->get('URL FROM') ? $request->get('url_from') : $seoTag['url_from'];
$tabControl->AddEditField('url_from', "URL FROM".':', true, array('size' => 60), $url);

$url = $request->get('URL TO') ? $request->get('url_to') : $seoTag['url_to'];
$tabControl->AddEditField('url_to', "URL TO".':', true, array('size' => 60), $url);

$url = $request->get('REDIRECT TYPE') ? $request->get('redirect_type') : $seoTag['redirect_type'];
//$tabControl->AddEditField('redirect_type', "REDIRECT TYPE".':', true, array('size' => 60), $url);

$selections = array(
    '301' => '301',
    '302' => '302',
);
$tabControl->AddDropDownField('redirect_type', "REDIRECT TYPE".':', true, $selections, $url);



$tabControl->Buttons(array("disabled" => ($saleModulePermissions < "W"), "back_url" => $listUrl));

$tabControl->Show();
?>
<?
require($documentRoot."/bitrix/modules/main/include/epilog_admin.php");
?>
<?php
