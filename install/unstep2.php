<?php if(!check_bitrix_sessid()) return; ?>
<?php IncludeModuleLangFile(__FILE__); ?>
<form action="<?php echo $APPLICATION->GetCurPage() ?>">
    <input type="hidden" name="lang" value="<?php echo LANG; ?>">
    <?=GetMessage("VOYADGER_SEO_DEL_TEXT")?><br>
    <input type="submit" name="" value="Ok">
</form>