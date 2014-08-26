<?php

class CTTPriceManager implements ITTModuleSettings
{
	protected static $error;

	private static $opts_int = array(
		'catalog_iblock_id'
	);

	private static $opts_req = array(); 

	public static function manageBySection(array $parentSection, $value, $settings = 0, $userID = false)
	{
		$action = new CTTSectionManage($parentSection, $value, $settings, $userID);

		$result = $action->execute();

		if($result === false)
		{
			self::$error = $action->getError();
			return false;
		}

		return $result;
	}

	public static function getOption($name)
	{
		if(in_array($name, self::$opts_int))
			return COption::GetOptionInt(self::MODULE_ID, $name);
		return COption::GetOptionString(self::MODULE_ID, $name);
	}

	public static function setOption($name, $value)
	{
		if(in_array($name, self::$opts_int))
			return COption::SetOptionInt(self::MODULE_ID, $name, $value);
		return COption::SetOptionString(self::MODULE_ID, $name, $value);
	}

	public static function isOptionRequired($name)
	{
		return in_array($name, self::$opts_req);
	}
	
	public static function isOptionsInt($name)
	{
		return in_array($name, self::$opts_int);
	}

}