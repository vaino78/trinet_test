<?php

abstract class ATTSectionManage
{
	const AFFECT_CHILDREN_SECTIONS = 1;

	const NO_LOG_DATA              = 2;

	protected $parents = array();

	protected $value   = array();

	protected $settings;

	protected $user_id;

	protected $error;

	protected $id;

	protected $sections = array();

	protected $products = array();

	protected $affected;

	function __construct(array $parents, $value, $settings = 0, $user_id = false)
	{
		IncludeModuleLangFile(__FILE__);

		$this->parents = $parents;
		$this->value   = $value;

		if($user_id === false)
		{
			global $USER;

			$this->user_id = $USER->GetID();
		}
	}

	public function process()
	{
		try
		{
			if(empty($this->parents))
				throw new Exception(GetMessage('PARENT_SECTION_IS_NOT_SET'));

			if(empty($this->user_id))
				throw new Exception(GetMessage('USER_ID_IS_NOT_SET'));

			if(empty($this->value) || !is_numeric($this->value))
				throw new Exception(GetMessage('INCORRECT_VALUE'));

			$iblock_id = CTTPriceManager::getOption('catalog_iblock_id');
			if(empty($iblock_id))
				throw new Exception(GetMessage('IBLOCK_IS_NOT_SET'));

			$this->startTransaction();

			$this->getSections($iblock_id);

			if(empty($this->sections))
				throw new Exception(GetMessage('NO_SECTIONS_TO_AFFECT'), 1);

			$this->getProducts($iblock_id);

			if(empty($this->products))
				throw new Exception(GetMessage('NO_PRODUCTS_TO_AFFECT'), 1);

			$this->startSession();

			if(empty($this->id))
				throw new Exception(GetMessage('NO_SESSION_ID'), 1);

			$this->processUpdate();

			if($this->affected && $this->settings ^ static::NO_LOG_DATA)
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

	public function getError()
	{
		if(empty($this->error))
			return false;
		return $this->error;
	}

	public function getAffectedSections()
	{
		if(!isset($this->id))
			return false;
		return $this->sections;
	}

	public function getAffectedProducts()
	{
		if(!isset($this->id))
			return false;
		return $this->products;
	}

	abstract protected function startTransaction();

	abstract protected function commitTransaction();

	abstract protected function rollbackTransaction();

	abstract protected function startSession();

	abstract protected function getSections($iblock_id);

	abstract protected function getProducts($iblock_id);

	abstract protected function processUpdate();

	abstract protected function logSections();

	abstract protected function logProducts();
}