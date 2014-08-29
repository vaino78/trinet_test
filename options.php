<?php

if(!CModule::IncludeModule('trinet_test'))
	die('Module is not installed');

if($_SERVER['REQUEST_METHOD'] == 'POST' && check_bitrix_sessid())
{
	$result = CTTPriceManager::saveSettings($_POST);

	if(!$result)
	{
		ShowError(CTTPriceManage::getError());
	}
}

IncludeModuleLangFile(__FILE__);

$tabControl = new CAdminTabControl(
	'tabControl',
	array(
		array(
			'DIV'   => 'tt_tab1',
			'TAB'   => GetMessage('TRINET_TEST_OPTS_GENERAL_TAB_NAME'),
			'TITLE' => GetMessage('TRINET_TEST_OPTS_GENERAL_TAB_TITLE')
		)
	)
);

$tabControl->Begin();
?>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialchars($mid)?>&amp;lang=<?echo LANG?>">
<?=bitrix_sessid_post();?>

<?php
$tabControl->BeginNextTab();
?>
	<tr>
		<td width="50%" valign="top"><?=GetMessage('TRINET_TEST_OPTS_GENERAL_CATALOG_IBLOCK_ID')?> <span style="color:red;">*</span></td>
		<td><select name="catalog_iblock_id">
			<option value="0"><?=GetMessage('TRINET_TEST_OPTS_GENERAL_CATALOG_IBLOCK_NOT_SELECTED')?></option>
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
</script>
<input type="submit" name="Update" value="<?echo GetMessage("MAIN_SAVE")?>">
<input type="hidden" name="Update" value="Y">
<input type="reset" name="reset" value="<?echo GetMessage("MAIN_RESET")?>">
<?
$tabControl->End();
?>

</form>