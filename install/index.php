<?php

if class_exists('trinet_test')
	return;

$PathInstall = str_replace('\\', '/', dirname(__FILE__));
global $MESS;
IncludeModuleLangFile($PathInstall . '/install.php');

class trinet_test extends CModule
{

	public $MODULE_ID = 'trinet_test';
	public $MODULE_NAME;
	public $MODULE_DESCRIPTION;
	public $MODULE_VERSION;
	public $MODULE_VERSION_DATE;
	public $MODULE_GROUP_RIGHTS = 'N';

	public function __construct()
	{
		$PathInstall = str_replace('\\', '/', dirname(__FILE__));
		include($PathInstall . '/version.php');
		$this->MODULE_NAME = GetMessage('TRINET_TESTMODULE_NAME');
		$this->MODULE_DESCRIPTION = GetMessage('TRINET_TESTMODULE_DESCRIPTION');
		if(is_array($arModuleVersion) && !empty($arModuleVersion['VERSION']))
		{
			$this->MODULE_VERSION = $arModuleVersion['VERSION'];
			$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		}
	}

	public function DoInstall()
	{
		$this->InstallFiles();
		$this->RunSQL('install');
		$this->InstallEvents();
		$this->InstallAgents();
		RegisterModule($this->MODULE_ID);
	}

	public function DoInstall()
	{
		$this->UninstallFiles();
		$this->RunSQL('unstall');
		$this->UninstallEvents();
		$this->UninstallAgents();
		UnRegisterModule($this->MODULE_ID);
	}

	private function InstallFiles()
	{
	}

	private function InstallEvents()
	{
	}

	private function InstallAgents()
	{
	}

	private function UninstallFiles()
	{
	}

	private function UninstallEvents()
	{
	}

	private function UninstallAgents()
	{
	}

	private function RunSQL($filename)
	{
		global $APPLICATION, $DBType, $DB;
		$filename = sprintf(
			'%s/db/%s/%s.sql',
			dirname(__FILE__),
			$DBType,
			$filename
		);
		if(!file_exists($filename))
			return false;
		$errors = $DB->RunSQLBatch($filename);
		if(!empty($errors))
		{
			$APPLICATION->ThrowException(implode('', $errors));
			return false;
		}
		return true;
	}

}
