<?php

/**
 * Данный абстрактный класс содержит независящие от БД методы, организующие обновление цен
 * по переданным идентификаторам секций инфоблока. Методы, предполагаюшие взаимодействие с БД,
 * объявлены абстрактными
 * 
 * @abstract
 */
abstract class ATTSectionManage
{
	/**
	 * Значение для битовой маски настроек:
	 *  — затрагивать ли дочерние секции
	 */
	const AFFECT_CHILDREN_SECTIONS = 1;

	/**
	 * Значение для битовой маски настроек:
	 *  — отключает логгирование данных о произведенном изменении
	 */
	const NO_LOG_DATA              = 2;

	/**
	 * Массив идентификаторов секций
	 * @var array
	 */
	protected $parents = array();

	/**
	 * Значение изменения цены
	 * @var float
	 */
	protected $value;

	/**
	 * Битовая маска настроек
	 * @var int
	 */
	protected $settings;

	/**
	 * Идентификатор пользователя, проводящего изменение
	 * @var int
	 */
	protected $user_id;

	/**
	 * Информация о последней ошибке
	 * @var string
	 */
	protected $error;

	/**
	 * Идентификатор сеанса изменения цены
	 * @var int
	 */
	protected $id;

	/**
	 * Идентификаторы секций, затронутых изменением
	 * @var array
	 */
	protected $sections = array();

	/**
	 * Идентификаторы продуктов, цены которых затронуты изменением
	 * @var array
	 */
	protected $products = array();

	/**
	 * Количество записей цен, затронутых изменением
	 * @var int
	 */
	protected $affected;

	/**
	 * Конструктор.
	 * 
	 * @param array $parents Список родительских секций, товары внутри которых должно затронуть изменение цены
	 * @param double $value Значение изменения цены в процентах
	 * @param int $settings (optional) Битовая маска настроек
	 * @param int $user_id (optional) Идентификатор пользователя
	 */
	function __construct(array $parents, $value, $settings = 0, $user_id = false)
	{
		IncludeModuleLangFile(__FILE__);

		$this->parents  = $parents;
		$this->value    = $value;
		$this->settings = $settings;

		if($user_id === false)
		{
			global $USER;

			$this->user_id = $USER->GetID();
		}
	}

	/**
	 * Метод осуществляет выполнение изменения цены, перед этим валидируя данные
	 * 
	 * @return int|false Возвращается количество затронутых цен или ложь в случае ошибки
	 * 
	 * @uses self::startTransaction()
	 * @uses self::getSections()
	 * @uses self::getProducts()
	 * @uses self::startSession()
	 * @uses self::processUpdate()
	 * @uses self::logSections()
	 * @uses self::logProducts()
	 * @uses self::commitTransaction()
	 * @uses self::rollbackTransaction()
	 */
	public function process()
	{
		try
		{
			if(empty($this->parents))
				throw new Exception(GetMessage('TRINET_TEST_PARENT_SECTION_IS_NOT_SET'));

			if(empty($this->user_id))
				throw new Exception(GetMessage('TRINET_TEST_USER_ID_IS_NOT_SET'));

			if(empty($this->value) || !is_numeric($this->value))
				throw new Exception(GetMessage('TRINET_TEST_INCORRECT_VALUE'));

			$iblock_id = CTTPriceManager::getOption('catalog_iblock_id');
			if(empty($iblock_id))
				throw new Exception(GetMessage('TRINET_TEST_IBLOCK_IS_NOT_SET'));

			$this->startTransaction();

			$this->getSections($iblock_id);

			if(empty($this->sections))
				throw new Exception(GetMessage('TRINET_TEST_NO_SECTIONS_TO_AFFECT'), 1);

			$this->getProducts($iblock_id);

			if(empty($this->products))
				throw new Exception(GetMessage('TRINET_TEST_NO_PRODUCTS_TO_AFFECT'), 1);

			$this->startSession();

			if(empty($this->id))
				throw new Exception(GetMessage('TRINET_TEST_NO_SESSION_ID'), 1);

			$this->processUpdate();

			if($this->affected && $this->settings & ~static::NO_LOG_DATA)
			{
				$this->logSections();

				$this->logProducts();
			}

			$this->commitTransaction();

		}
		catch(Exception $e)
		{
			if($e->getCode())
				$this->rollbackTransaction();
			$this->error = $e->getMessage();
			return false;
		}

		return $this->affected;
	}

	/**
	 * Возвращает сообщение о последней ошибке
	 * @return string
	 */
	public function getError()
	{
		if(empty($this->error))
			return false;
		return $this->error;
	}

	/**
	 * Возвращает список идентификаторов секций, товары в которых будут затронуты изменением цены
	 * @return array|false
	 */
	public function getAffectedSections()
	{
		if(!isset($this->id))
			return false;
		return $this->sections;
	}

	/**
	 * Возвращает список идентификаторов товаров, затронутых изменением цены
	 * @return array|false
	 */
	public function getAffectedProducts()
	{
		if(!isset($this->id))
			return false;
		return $this->products;
	}

	/**
	 * Начинает транзакцию или блокирует таблицы в БД
	 * @abstract
	 * @protected
	 * @return void
	 */
	abstract protected function startTransaction();

	/**
	 * Подтверждает транзакцию или высвобождает блокировку таблиц
	 * @abstract
	 * @protected
	 * @return void
	 */
	abstract protected function commitTransaction();

	/**
	 * Отменяет транзакцию или высвобождает блокировку таблиц
	 * @abstract
	 * @protected
	 * @return void
	 */
	abstract protected function rollbackTransaction();

	/**
	 * Инициализирует сеанс изменения цены, и идентификатор сохраняет в self::$id
	 * @abstract
	 * @protected
	 * @return int self::$id
	 */
	abstract protected function startSession();

	/**
	 * Осуществляет выборку идентификаторов секций с учетом настроек (выбирает или нет дочерние секции),
	 * товары привязанные к которым будут затронуты изменением цены. Заполняет self::$sections
	 * @abstract
	 * @protected
	 * @param int $iblock_id Идентификатор инфоблока-каталога
	 * @return array self::$sections
	 */
	abstract protected function getSections($iblock_id);

	/**
	 * Осуществляет выборку идентификаторов продуктов, привязанных к секциям из self::$sections
	 * @protected
	 * @abstract
	 * @param int $iblock_id Идентификатор инфоблока-каталога
	 * @return array self::$products
	 */
	abstract protected function getProducts($iblock_id);

	/**
	 * Осуществляет непосредственное обновление цены по товарам, идентификаторы которых сохранены
	 * в self::$products
	 * @abstract
	 * @protected
	 * @return int Количество записей, затронутых обновлением
	 */
	abstract protected function processUpdate();

	/**
	 * Записывает в таблицу лога данные о секциях, затронутых изменением цены
	 * @protected
	 * @abstract
	 * @return void
	 */
	abstract protected function logSections();

	/**
	 * Записывает в таблицу лога данные о продуктых, затронутых изменением цены
	 * @protected
	 * @abstract
	 * @return void
	 */
	abstract protected function logProducts();
}