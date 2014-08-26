<?php

abstract class ATTSectionManage 
{
	protected $parents = array();

	protected $value   = array();

	protected $user_id;

	protected $error;

	function __construct(array $parents, $value, $user_id = false)
	{
		$this->parents = $parents;
		$this->value   = $value;

		if($user_id === false)
		{
			global $USER;

			$this->user_id = $USER->GetID();
		}
	}

	public function validate()
	{
		
	}

	public function process()
	{
	}
}
