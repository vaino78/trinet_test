<?php

global $DBType;

CModule::AddAutoloadClasses("trinet_test", array(
	"ITTModuleSettings" => "classes/general/module_settings.php",
	"CTTPriceManager"   => "classes/general/price_manager.php",
	"ATTSectionManage"  => "classes/general/section_manage.php",
	"CTTSectionManage"  => sprintf("classes/%s/section_manage.php", $DBType)
));
