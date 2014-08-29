<?php

IncludeModuleLangFile(__FILE__);

/**
 * Данный класс является «главным» в модуле, реализует функциональность 
 * по сохранению и чтению информации о настройках, а также фасадные методы 
 * для доступа к более конкретной функциональности, предоставляемой модулем.
 * Пока что такой метод только один — CTTPriceManager::manageBySection(), 
 * однако, их вполне может быть больше.
 */
class CTTPriceManager implements ITTModuleSettings
{
	/**
	 * Информация о последней ошибке
	 * @see self::getError()
	 */
	protected static $error;

	/**
	 * Список опций, имеющих числовое значение
	 * @see self::isOptionInt()
	 */
	protected static $opts_int = array(
		'catalog_iblock_id'
	);

	/**
	 * Фасадная обертка над взаимодействием с классом CTTSectionManage. Впоследствии в этом 
	 * методе возможно разместить дополнительную логику, связанную, например, с определением
	 * конкретного класса, осуществляющего полезную работу, в зависимости от внешних условий
	 * 
	 * @static
	 * 
	 * @param array|int $parentSection Секция или список секций, для товаров которых, нужно 
	 *                                 осуществить пересчет цен
	 * @param float $value Значение в процентах для изменения цены
	 * @param int $settings Битовая маска настроек действия (@see ATTSectionManage)
	 * @param int|false $userID Идентификатор пользователя, запустившего изменение. По умолчанию — текущий пользователь
	 * 
	 * @return int|false Возвращает количество цен, затронутых изменением, или ложь
	 * 
	 * @uses CTTSectionManage::process()
	 * @uses self::getError() В случае ошибки, делает доступной информацию о ней
	 */
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


	/**
	 * Вспомогательный метод, возвращающий список инфоблоков-каталогов
	 * 
	 * @static
	 * @return array|false Ассоциативный массив, ключи которого — идентификаторы, значения — наименования
	 */
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


	/**
	 * Осуществляет сохранение настроек модуля, предварительно осуществляя валидацию
	 * передаваемых данных
	 * 
	 * @static
	 * 
	 * @return bool
	 * @uses self::validateSettings()
	 */
	public static function saveSettings(&$settings)
	{
		if(!static::validateSettings($settings))
			return false;

		static::setOption('catalog_iblock_id', $settings['catalog_iblock_id']);

		return true;
	}


	/**
	 * Осуществляет валидацию данных настроек
	 * 
	 * @static
	 * 
	 * @return bool
	 * @uses self::getError() При ошибке валидации, информация о ней доступна через этот метод
	 */
	public static function validateSettings(&$settings)
	{
		try
		{
			if(empty($settings['catalog_iblock_id']))
				throw new Exception(GetMessage('TRINET_TEST_VALIDATE_CATALOG_IBLOCK_EMPTY'));

			if(!CModule::IncludeModule('catalog') || !CCatalog::GetByID($settings['catalog_iblock_id']))
				throw new Exception(GetMessage('TRINET_TEST_VALIDATE_IBLOCK_IS_NOT_CATALOG'));

		}
		catch(Exception $e)
		{
			static::$error = $e->getMessage();
			return false;
		}

		return true;
	}

	/**
	 * Получение информации о последней произошедшей ошибке
	 * @return string
	 */
	public static function getError()
	{
		return static::$error;
	}


	/**
	 * Возвращает значение опции по символьному идентификатору
	 * 
	 * @static
	 * 
	 * @param string $name Символьный идентификатор опции для данного модуля
	 * 
	 * @return string|int Значение опции
	 * 
	 * @uses COption::GetOptionString()
	 * @uses COption::GetOptionInt()
	 * @uses self::isOptionsInt()
	 */
	public static function getOption($name)
	{
		if(static::isOptionsInt($name))
			return COption::GetOptionInt(static::MODULE_ID, $name);
		return COption::GetOptionString(static::MODULE_ID, $name);
	}

	/**
	 * Устанавливает значение опции по символьному идентификатору
	 * 
	 * @static
	 * 
	 * @param string $name
	 * @param string|int $value
	 * 
	 * @uses COption::SetOptionInt()
	 * @uses COption::SetOptionString()
	 * @uses self::isOptionsInt()
	 */
	public static function setOption($name, $value)
	{
		if(static::isOptionsInt($name))
			return COption::SetOptionInt(static::MODULE_ID, $name, $value);
		return COption::SetOptionString(static::MODULE_ID, $name, $value);
	}
	
	/**
	 * Определяет, сохраняется ли опция с данным символьным идентификатором, в виде числа
	 * 
	 * @param string $name
	 * @return bool
	 * @static
	 */
	public static function isOptionsInt($name)
	{
		return in_array($name, static::$opts_int);
	}

}