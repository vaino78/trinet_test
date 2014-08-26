<?php

class CTTPriceManager
{
	protected static $error;

	public static function manageBySection($parentSection, $value, $userID = false)
	{
		$action = new CTTSectionManage($parentSection, $value, $userID);

		$result = $action->execute();

		if($result === false)
		{
			self::$error = $action->getError();
			return false;
		}

		return $result;
	}
}