

<?php if(!check_bitrix_sessid()) return; ?>
<?php IncludeModuleLangFile(__FILE__); ?>

<form action="<?echo $APPLICATION->GetCurPage()?>">
    <?= bitrix_sessid_post();?>
    <input type="hidden" name="lang" value="<?php echo LANG; ?>">
    <input type="hidden" name="id" value="voyadger.seo">
    <input type="hidden" name="uninstall" value="Y">
    <input type="hidden" name="step" value="2" />
    <p>
        <input type="checkbox" name="savedata" id="savedata" value="Y" checked>
        <label><?=GetMessage("VOYADGER_SEO_SAVE_DB")?></label>
    </p>
    <input type="submit" name="" value="УДАЛИТЬ">
<form>
