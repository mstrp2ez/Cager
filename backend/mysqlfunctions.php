<?php

include_once('backend/Query.php');


class MysqlShort
{
	static private $m_Query=null;

	static private function Query()
	{
		if(self::$m_Query==null)
		{
			self::$m_Query=new Query(DB_NAME);
		}
		return self::$m_Query;
	}
	
	static public function GetLastQuery()
	{
		return self::$m_Query->GetQuery();
	}
	
	static public function Select($p_Indices,$p_Table,$p_Selectors=array(),$p_AO=array(),$p_OP=array())
	{
		$query=self::Query();
		$query->ClearQuery();
		
		$query->Select($p_Indices,$p_Table,$p_Selectors,$p_AO,$p_OP);
		//echo $query->GetQuery();
		$query->Execute();
		
		$result=$query->FetchAssoc();

		if(count($result)<=0){return false;}
		return $result;
	}
	
	static public function Raw($p_Query, array $p_Values)
	{
		$query=self::Query();
		$query->ClearQuery();
		
		$query->Raw($p_Query, $p_Values);
		$data=$query->FetchAssoc();
		
		if(count($data)<=0){return false;}
		
		return $data;
	}
	
	static public function Update($p_Table,$p_Set, $p_Selector)
	{
		$query=self::Query();
		$query->ClearQuery();
		
		$query->Update($p_Table, $p_Set, $p_Selector);
		/* echo $query->GetQuery(); */
		$query->Execute();
		
		return true;
	}
	
	static public function InsertSimple($p_Table,$p_KeyVal)
	{
		$query=self::Query();
		$query->ClearQuery();
		
		$query->Insert($p_Table, $p_KeyVal);
		$query->Execute();
			
		return true;
	}
	
	static public function Insert($p_Table,$p_Selector,$p_KeyVal,$p_Token='*', $p_UpdateOnExist=false)
	{
		$query=self::Query();
		$query->ClearQuery();
		
		if($p_UpdateOnExist)
		{
			return $query->InsertIfNotExist($p_Table,$p_KeyVal,$p_KeyVal,$p_Token);
		}
		else
		{
			$query->Insert($p_Table, $p_KeyVal);
		/* 	echo $query->GetQuery(); */
			$query->Execute();
			
			return true;
		}
	}
	
	static public function Delete($p_Table,$p_Selector)
	{
		$query=self::Query();
		$query->ClearQuery();
		
		$query->Delete($p_Table,$p_Selector);
		
		return true;
	}
}



?>