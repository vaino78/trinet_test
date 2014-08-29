<?php

IncludeModuleLangFile(__FILE__);

$aMenu = array(
	'parent_menu' => 'global_menu_store',
	'text'        => GetMessage('TRINET_TEST_PARENT_MENU_TEXT'),
	'title'       => GetMessage('TRINET_TEST_PARENT_MENU_TITLE'),
	'sort'        => 100,
	'url'         => '#',
	'items_id'    => 'trinet_test_menu',
	'items' => array(
		array(
			'text'     => GetMessage('TRINET_TEST_SECTION_UPGRADE_TEXT'),
			'url'      => ('trinet_test_section_update.php?lang=' . LANG),
			'more_url' => array(),
			'title'    => GetMessage('TRINET_TEST_SECTION_UPGRADE_TITLE')
		)
	)
);

return $aMenu;

