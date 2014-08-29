<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

CModule::IncludeModule('trinet_test');
IncludeModuleLangFile(__FILE__);

if($_SERVER['REQUEST_METHOD'] == 'POST' && check_bitrix_sessid())
{
	$settings = 0;
	if(!empty($_POST['affect_children']))
		$settings |= CTTSectionManage::AFFECT_CHILDREN_SECTIONS;

	$result = CTTPriceManager::manageBySection($POST['section'], $_POST['value'], $settings);
	if(!$result)
		ShowError(CTTPriceManager::getError());
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$iblock_id = CTTPriceManager::getOption('catalog_iblock_id');
if(!$iblock_id)
	ShowError(GetMessage('TRINET_TEST_CATALOG_IBLOCK_IS_NOT_SET'));

$tabControl = new CAdminTabControl(
	'section_update',
	array(
		array(
			'DIV' => 'tt_su_tab1',
			'TAB' => GetMessage('TRINET_TEST_SECTION_UPDATE_TAB'),
			'TITLE' => GetMessage('TRINET_TEST_SECTION_UPDATE_TITLE')
		)
	)
);

$tabControl->Begin();

?>
<form method="POST">
	<?=bitrix_sessid_post();?>

<? $tabControl->BeginNextTab(); ?>

	<tr>
		<td width="50%"><?=GetMessage('TRINET_TEST_SECTION_UPDATE_SECTION')?> <span style="color:red;">*</span></td>
		<td><select name="section[]" multiple="multiple" size="5"><?php 
		if(CModule::IncludeModule('iblock') && $iblock_id)
		{
			$q = CIBlockSection::GetList(
				array(
					'left_margin' => 'asc'
				),
				array(
					'IBLOCK_ID' => $iblock_id
				)
			);

			while($d = $q->GetNext(1,0))
			{
				?><option value="<?=$d['ID']?>"><?=str_repeat('&mdash;&nbsp;', $d['DEPTH_LEVEL']-1)?><?=$d['NAME']?></option><?php
			}
		}
		?></select></td>
	</tr>

	<tr>
		<td width="50%"><?=GetMessage('TRINET_TEST_SECTION_UPDATE_AFFECT_CHILDREN')?></td>
		<td><input type="checkbox" name="affect_children" value="Y" /></td>
	</tr>

	<tr>
		<td width="50%"><?=GetMessage('TRINET_TEST_SECTION_UPDATE_VALUE')?> <span style="color:red;">*</span></td>
		<td><input type="text" name="value" value="" size="7" />&nbsp;%</td>
	</tr>

<? $tabControl->Buttons(); ?>

<input type="submit" name="Update" value="<?echo GetMessage("MAIN_SAVE")?>">

<? $tabControl->End(); ?>
</form>
<?

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); 
?>