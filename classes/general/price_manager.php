<?php

class CTTPriceManager implements ITTModuleSettings
{
	protected static $error;

	protected static $opts_int = array(
		'catalog_iblock_id'
	);

	protected static $opts_req = array(); 

	public static function manageBySection($parentSection, $value, $settings = 0, $userID = false)
	{
		if(empty($parentSection))
			$parentSection = array();
		if(!is_array($parentSection))
			$parentSection = (array)$parentSection;

		$action = new CTTSectionManage($parentSection, $value, $settings, $userID);

		$result = $action->process();

		if($result === false)
		{
			static::$error = $action->getError();
			return false;
		}

		return $result;
	}


	public static function getCatalogList()
	{
		if(!CModule::IncludeModule('catalog'))
			return false;

		$q = CCatalog::GetList();
		$result = array();
		while($d = $q->Fetch())
			$result[$d['IBLOCK_ID']] = $d['NAME'];

		return $result;
	}


	public static function saveSettings(&$settings)
	{
		if(!static::validateSettings($settings))
			return false;

		static::setOption('catalog_iblock_id', $settings['catalog_iblock_id']);

		return true;
	}


	public static function validateSettings(&$settings)
	{
		try
		{
			if(empty($settings['catalog_iblock_id']))
				throw new Exception(GetMessage('VALIDATE_CATALOG_IBLOCK_EMPTY'));

			if(!CModule::IncludeModule('catalog') || !CCatalog::GetByID($settings['catalog_iblock_id']))
				throw new Exception(GetMessage('VALIDATE_IBLOCK_IS_NOT_CATALOG'));

		}
		catch(Exception $e)
		{
			static::$error = $e->getMessage();
			return false;
		}

		return true;
	}


	public static function getError()
	{
		return static::$error;
	}


	public static function getOption($name)
	{
		if(in_array($name, static::$opts_int))
			return COption::GetOptionInt(static::MODULE_ID, $name);
		return COption::GetOptionString(static::MODULE_ID, $name);
	}

	public static function setOption($name, $value)
	{
		if(in_array($name, static::$opts_int))
			return COption::SetOptionInt(static::MODULE_ID, $name, $value);
		return COption::SetOptionString(static::MODULE_ID, $name, $value);
	}

	public static function isOptionRequired($name)
	{
		return in_array($name, static::$opts_req);
	}
	
	public static function isOptionsInt($name)
	{
		return in_array($name, static::$opts_int);
	}

}