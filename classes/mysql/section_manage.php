<?php

class CTTSectionManage extends ATTSectionManage
{
	protected $db;

	protected function startTransaction()
	{}

	protected function commitTransaction()
	{}

	protected function rollbackTransaction()
	{}

	protected function startSession()
	{}

	protected function getSections($iblock_id)
	{}

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
