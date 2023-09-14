<?
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Bitrix\Main\Page;
use Bitrix\Main\Config;
//use Slv\SeoTags\UrlsTable;
use Voyadger\Seo\UrlsTable;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$selfFolderUrl = $adminPage->getSelfFolderUrl();
$listUrl = $selfFolderUrl."voyadger_seo_tags_overwrite_list.php?lang=" . $lang;
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

  $seoTag = array(
    'URL' => $request->get('URL'),
    'KEYWORDS' => $request->getPost('KEYWORDS'),
    'SITE_ID' => $request->getPost('SITE_ID'),
    'DESCRIPTION' => $request->getPost('DESCRIPTION'),
    'H1' => $request->getPost('H1'),
    'TITLE' => $request->getPost('TITLE'),
    'ACTIVE' => ($request->get('ACTIVE') == 'Y') ? 'Y' : 'N',
    'TEXT' => $request->getPost('TEXT'),
  );


  if ($errorMessage === '')
  {
    if ($id > 0)
    {
      $result = UrlsTable::update($id, $seoTag);
    }
    else
    {
      $result = UrlsTable::add($seoTag);
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
          $applyUrl = $selfFolderUrl."voyadger_seo_tags_overwrite_edit.php?lang=".$lang."&ID=".$id;
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
  $res = UrlsTable::getList(array('filter' => array('ID' => $id)));
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

  $deleteUrl = $selfFolderUrl."voyadger_seo_tags_overwrite_list.php?action=delete&ID[]=".$id."&lang=".$context->getLanguage()."&".bitrix_sessid_get()."#tb";
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
$tabControl->AddCheckBoxField("ACTIVE", GetMessage("VOYADGER_SEO_URL_ACTIVE").':', false, 'Y', $active === 'Y');


$url = $request->get('URL') ? $request->get('URL') : $seoTag['url'];
$tabControl->AddEditField('URL', "URL".':', true, array('size' => 60), $url);

$h1 = $request->get('H1') ? $request->get('H1') : $seoTag['h1'];
$tabControl->AddEditField('H1', "H1".':', false, array('size' => 60), $h1);

$title = $request->get('TITLE') ? $request->get('TITLE') : $seoTag['title'];
$tabControl->AddEditField('TITLE', "TITLE".':', false, array('size' => 60), $title);

$keywords = $request->get('KEYWORDS') ? $request->get('KEYWORDS') : $seoTag['keywords'];
$tabControl->AddEditField('KEYWORDS', "KEYWORDS".':', false, array('size' => 80), $keywords);

$description = $request->get('DESCRIPTION') ? $request->get('DESCRIPTION') : $seoTag['description'];
$tabControl->AddTextField('DESCRIPTION', "DESCRIPTION".':', $description, array('cols' => 80, 'rows' => 20));

$text = $request->get('TEXT') ? $request->get('DESCRIPTION') : $seoTag['text'];
$tabControl->AddTextField('TEXT', "TEXT".':', $text, array('cols' => 80, 'rows' => 20));



$tabControl->Buttons(array("disabled" => ($saleModulePermissions < "W"), "back_url" => $listUrl));

$tabControl->Show();
?>
<?
require($documentRoot."/bitrix/modules/main/include/epilog_admin.php");
?>
<?php
