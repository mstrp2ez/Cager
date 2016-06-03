<?php

include_once('common.php');

class DB
{	
	var $m_DB;
	var $m_Debug;
	
	function __construct()
	{
		$this->m_DB=mysql_connect(db_host, db_user, db_password);
	}
	
	function __get($p_Key)
	{
		if($p_Key==='db')
		{
			return $this->m_DB;
		}
	}
	
	function SelectDB($p_Table)
	{
		if($this->m_DB===false){return;}

		return mysql_select_db($p_Table, $this->m_DB);
	}
}

$GLOBALS['db']=new DB();

?>