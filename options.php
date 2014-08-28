<?php

if(!CModule::IncludeModule('trinet_test'))
	die('Module is not installed');

if($_SERVER['REQUEST_METHOD'] == 'POST' && check_bitrix_sessid())
{
	$result = CTTPriceManager::saveSettings($_POST);


}

$tabCtrl = new CAdminTabControl(
	'tabControl',
	array(
		'DIV' => 'tt_tab1',
		'TAB' => GetMessage('OPTS_GENERAL_TAB_NAME'),
		'TITLE' => GetMessage('OPTS_GENERAL_TAB_TITLE')
	)
);

$tabCtrl->Begin();
?>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialchars($mid)?>&amp;lang=<?echo LANG?>">
<?=bitrix_sessid_post();?>

<?php
$tabControl->BeginNextTab();
?>
	<tr>
		<td width="50%" valign="top"><?=GetMessage('OPTS_GENERAL_CATALOG_IBLOCK_ID')?> <span style="color:red;">*</span></td>
		<td><select name="catalog_iblock_id">
			<option value="0"><?=GetMessage('OPTS_GENERAL_CATALOG_IBLOCK_NOT_SELECTED')?></option>
			<?php
			$catalogs = CTTPriceManager::getCatalogList();
			$selected = CTTPriceManager::getOption('catalog_iblock_id');
			foreach($catalogs as $iblock_id => $name)
			{
				?><option value="<?=$iblock_id?>"<?if($iblock_id == $selected):?> selected="selected"<?endif?>><?=$name?></option><?php
			}
			?>
		</select></td>
	</tr>
<?php
$tabControl->Buttons();?>

<script language="JavaScript">
function confirmRestoreDefaults()
{
	return confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>');
}
</script>
<input type="submit" name="Update" value="<?echo GetMessage("MAIN_SAVE")?>">
<input type="hidden" name="Update" value="Y">
<input type="reset" name="reset" value="<?echo GetMessage("MAIN_RESET")?>">
<!-- input type="submit" name="RestoreDefaults" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="return confirmRestoreDefaults();" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>" -->

<?
$tabControl->End();
?>

</form>