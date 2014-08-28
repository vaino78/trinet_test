<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

CModule::IncludeModule('trinet_test');
IncludeModuleLangFile(__FILE__);

if($_SERVER['REQUEST_METHOD'] == 'POST' && check_bitrix_sessid())
{
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

?>
<form method="POST">
	<?=bitrix_sessid_post();?>

	

</form>
<?

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); 
?>