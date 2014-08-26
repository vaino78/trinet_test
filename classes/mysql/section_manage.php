<?php

class CTTSectionManage extends ATTSectionManage
{
	const TABLE_LOG = 'trinet_test_log';

	const TABLE_LOG_SECTIONS = 'trinet_test_log_sections';

	const TABLE_LOG_PRODUCTS = 'trinet_test_log_product';

	protected $db;

	protected function startTransaction()
	{
		return $this->getDb()->StartTransaction();
	}

	protected function commitTransaction()
	{
		return $this->getDb()->Commit();
	}

	protected function rollbackTransaction()
	{
		return $this->getDb()->Rollback();
	}

	protected function startSession()
	{
		$this->getDb()->Query(sprintf(
			(
				'INSERT INTO `%s`(`started_at`,`value`,`user_id`,`settings`) '
				. ' VALUES(CURRENT_TIMESTAMP, %.02f, %u, %u)'
			),
			self::TABLE_LOG,
			$this->value,
			$this->user_id,
			$this->settings
		));

		return $this->id = $this->getDb()->LastID();
	}

	protected function getSections($iblock_id)
	{
		$q = $this->getDb()->Query(sprintf(

		));
	}

	protected function getProducts($iblock_id)
	{}

	protected function processUpdate()
	{}

	protected function logSections()
	{}

	protected function logProducts()
	{}

	protected function getDb()
	{
		if(!isset($this->db))
		{
			global $DB;
			$this->db = $DB;
		}

		return $this->db;
	}
}
