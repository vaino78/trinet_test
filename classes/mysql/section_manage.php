<?php

class CTTSectionManage extends ATTSectionManage
{
	const TABLE_LOG = 'trinet_test_log';

	const TABLE_LOG_SECTIONS = 'trinet_test_log_section';

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
			(
				'SELECT B.`ID` '
				. ' FROM `b_iblock_section` A '
				. ' LEFT JOIN `b_iblock_section` B ON('
				. '      A.`IBLOCK_ID`=B.`IBLOCK_ID` '
				. (
					($this->settings & static::AFFECT_CHILDREN_SECTIONS)
					? (
						'     && B.`LEFT_MARGIN` >= A.`LEFT_MARGIN` '
						. '   && B.`RIGHT_MARGIN` <= A.`RIGHT_MARGIN`'
					)
					: '&& A.`ID`=B.`ID`'
				)
				. ')'
				. ' WHERE    A.`IBLOCK_ID`=%u '
				. '       && A.`ID` IN(%s)'
				. ' ORDER BY B.`LEFT_MARGIN`'
			),
			$iblock_id,
			implode(',', array_map('intval', $this->parents))
		));

		while($d = $q->Fetch())
			$this->sections[] = $d['ID'];

		return $this->sections;
	}

	protected function getProducts($iblock_id)
	{
		$sku = array();
		$q = $this->getDb()->Query(sprintf(
			(
				'SELECT CIB.`IBLOCK_ID`, CIB.`SKU_PROPERTY_ID`, B.`VERSION` '
				. ' FROM `b_catalog_iblock` CIB '
				. ' INNER JOIN `b_iblock`   B   ON(CIB.`IBLOCK_ID`=B.`ID`) '
				. ' WHERE CIB.`PRODUCT_IBLOCK_ID`=%u'
			),
			$iblock_id
		));
		while($d = $q->Fetch())
			$sku[] = $d;

		if(empty($sku))
		{
			// каталог без торговых предложений
			$q = $this->getDb()->Query(sprintf(
				(
					'SELECT E.`ID` '
					. ' FROM       `b_iblock_section_element` SE '
					. ' INNER JOIN `b_iblock_element`         E  ON(SE.`IBLOCK_ELEMENT_ID`=E.`ID`) '
					. ' INNER JOIN `b_catalog_product`        P  ON(E.`ID`=P.`ID`) '
					. ' INNER JOIN `b_catalog_price`          PP ON(P.`ID`=PP.`PRODUCT_ID`) '
					. ' WHERE E.`IBLOCK_ID`=%u && SE.`IBLOCK_SECTION_ID` IN(%s) '
					. ' GROUP BY E.`ID`'
				),
				$iblock_id,
				implode(',', $this->sections)
			));

			while($d = $q->Fetch())
				$this->products[] = $d['ID'];

		}
		else
		{
			// для инфоблока-каталога есть инфоблоки, содержащие торговые предложения

			// выбираем родительские товары, привязанные к секциям, для которых запущена обработка
			$elements = array();
			$q = $this->getDb()->Query(sprintf(
				(
					'SELECT E.`ID` '
					. ' FROM       `b_iblock_section_element` SE '
					. ' INNER JOIN `b_iblock_element`         E  ON(SE.`IBLOCK_ELEMENT_ID`=E.`ID`) '
					. ' WHERE E.`IBLOCK_ID`=%u && SE.`IBLOCK_SECTION_ID` IN(%s) '
					. ' GROUP BY E.`ID`'
				),
				$iblock_id,
				implode(',', $this->sections)
			));

			while($d = $q->Fetch())
				$elements[] = $d['ID'];

			if(empty($elements))
				return $this->products;

			// поскольку теоретически к инфоблоку могут быть привязаны несколько
			// инфоблоков с товарными предложениями, формируем список запросов
			// на получение списка конечных товаров (текст запроса зависит 
			// от «версии» инфоблока)

			$queries = array();
			foreach($sku as $s)
			{
				if($s['VERSION'] == 1 && $s['VERSION'] != 2)
				{
					// если свойства хранятся в общей таблице

					$queries[] = sprintf(
						(
							'SELECT IBEP.`IBLOCK_ELEMENT_ID` '
							. ' FROM `b_iblock_element_property` IBEP '
							. '   INNER JOIN `b_iblock_element`  IBE  ON(IBEP.`IBLOCK_ELEMENT_ID`=IBE.`ID`) '
							. '   INNER JOIN `b_catalog_product` CP   ON(IBEP.`IBLOCK_ELEMENT_ID`=CP.`ID`) '
							. '   INNER JOIN `b_catalog_price`   CPP  ON(CP.`ID`=CPP.`PRODUCT_ID`) '
							. ' WHERE IBE.`IBLOCK_ID`=%u '
							. '   && IBEP.`IBLOCK_PROPERTY_ID`=%u '
							. '   && IBEP.`VALUE` IN(%s)'
							. ' GROUP BY IBEP.`IBLOCK_ELEMENT_ID`'
						),
						$s['IBLOCK_ID'],
						$s['SKU_PROPERTY_ID'],
						implode(',', $elements)
					);
				}
				elseif($s['VERSION'] == 2)
				{
					// если свойства хранятся в отдельной таблице

					$queries[] = sprintf(
						(
							'SELECT IBEPS.`IBLOCK_ELEMENT_ID` '
							. ' FROM `b_iblock_element_prop_s%u` IBEPS '
							. '   INNER JOIN `b_iblock_element`  IBE   ON(IBEPS.`IBLOCK_ELEMENT_ID`=IBE.`ID`) '
							. '   INNER JOIN `b_catalog_product` CP    ON(IBEPS.`IBLOCK_ELEMENT_ID`=CP.`ID`) '
							. '   INNER JOIN `b_catalog_price`   CPP   ON(CP.`ID`=CPP.`PRODUCT_ID`) '
							. ' WHERE IBE.`IBLOCK_ID`=%1$u '
							. '   && IBEPS.`PROPERTY_%u` IN(%s)'
							. ' GROUP BY IBEPS.`IBLOCK_ELEMENT_ID`'
						),
						$s['IBLOCK_ID'],
						$s['SKU_PROPERTY_ID'],
						implode(',', $elements)
					);
				}
				else
				{
					// неизвестная версия инфоблока

					trigger_error(
						sprintf(
							'Unknown version %s of iblock #%u',
							$s['VERSION'],
							$s['IBLOCK_ID']
						),
						E_USER_WARNING
					);

					continue;
				}

			}

			if(!empty($queries))
			{
				$q = $this->getDb()->Query(implode(' UNION ', $queries));
				while($d = $q->Fetch())
					$this->products[] = $d['IBLOCK_ELEMENT_ID'];
			}
		}

		return $this->products;
	}

	protected function processUpdate()
	{
		$q = $this->getDb()->Query(sprintf(
			'UPDATE `b_catalog_price` SET `PRICE` = (`PRICE` + (`PRICE` * %.02f)) WHERE `PRODUCT_ID` IN(%s)',
			($this->value / 100),
			implode(',', $this->products)
		));

		return $this->affected = $q->AffectedRowsCount();
	}

	protected function logSections()
	{
		$log_id =& $this->id;
		$this->getDb()->Query(sprintf(
			'INSERT INTO `%s`(`log_id`, `iblock_section_id`) VALUES %s',
			self::TABLE_LOG_SECTIONS,
			implode(', ', array_map(
				function($section_id) use($log_id)
				{
					return sprintf('(%u, %u)', $log_id, $section_id);
				},
				$this->sections
			))
		));
	}

	protected function logProducts()
	{
		$log_id =& $this->id;
		$this->getDb()->Query(sprintf(
			'INSERT INTO `%s`(`log_id`, `product_id`) VALUES %s',
			self::TABLE_LOG_PRODUCTS,
			implode(', ', array_map(
				function($product_id) use($log_id)
				{
					return sprintf('(%u, %u)', $log_id, $product_id);
				},
				$this->products
			))
		));
	}

	/**
	 * @protected
	 * return CDatabase
	 */
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
