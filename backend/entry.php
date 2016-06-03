<?php

include_once('backend/Query.php');

class Entry
{
	var $m_Fields;
	var $m_Query;
	var $m_Table;
	
	function __construct($p_DB, $p_Table)
	{
		$this->m_Fields=array();
		
		$this->m_Query=new Query($p_DB);
		$this->m_Query->TableFields($p_Table);
		$this->m_Query->Execute();
		
		$this->m_Table=$p_Table;
		$data=$this->m_Query->FetchAssoc();
		
		//echo $this->m_Query->GetMysqlError();
		
		if(count($data)<=0){return;}
		
		foreach($data as $item)
		{
			foreach($item as $key => $val)
			{
				if($key==='Field')
				{
					$this->m_Fields[$val]=false;
				}
			}
		}
	}
	
	function GetA()
	{
		return $this->m_Fields;
	}
	
	function __set($p_Key,$p_Val)
	{
		if(isset($this->m_Fields[$p_Key])){$this->m_Fields[$p_Key]=$p_Val;}
	}
	
	function __get($p_Key)
	{
		return (isset($this->m_Fields[$p_Key]))?$this->m_Fields[$p_Key]:null;
	}
	
	function get($p_Key)
	{
		return (isset($this->m_Fields[$p_Key]))?$this->m_Fields[$p_Key]:null;	
	}
	
	function set($p_Key, $p_Val)
	{
		if(isset($this->m_Fields[$p_Key])){$this->m_Fields[$p_Key]=$p_Val;}
	}
	
	private function ToSQLString()
	{
		$ret='';
		$keys=array_keys($this->m_Fields);
		//print_r($keys);
		$ret=implode(',',$keys);
		
		return $ret;
		
	}
	
	function Load($p_Keys)
	{
		$this->m_Query->ClearQuery();
		$this->m_Query->Select($this->ToSQLString(), $this->m_Table, $p_Keys);
		$this->m_Query->Execute();
		
		$data=$this->m_Query->FetchAssoc();
		
		
		//print_r($data);
		if(count($data)<=0){return;}
	//	print_r($data);
		foreach($data as $item)
		{
			foreach($item as $key=>$val)
			{
				$this->m_Fields[$key]=$val;
			}
		}
	}
	
	function Save($p_ID='id')
	{
		if(count($this->m_Fields)<=0){return;}
		
		$this->m_Query->ClearQuery();
		if(!isset($this->m_Fields[$p_ID])){return;}
		$this->m_Query->Select($this->ToSQLString(), $this->m_Table, array($p_ID=>$this->m_Fields[$p_ID]));
		$this->m_Query->Execute();
		$data=$this->m_Query->FetchAssoc();
		
		$this->m_Query->ClearQuery();
		if(count($data)<=0)
		{
		//	print_r($this->m_Fields);
			if($this->m_Fields[$p_ID]===false){$this->m_Fields[$p_ID]=0;}
			foreach($this->m_Fields as $item => $val)
			{
				if($val===false)
				{
					$this->m_Fields[$item]='null';
				}
			}
	//		print_r($this->m_Fields);
			$this->m_Query->Insert($this->m_Table, $this->m_Fields);
		}
		else
		{
			if(isset($this->m_Fields[$p_ID]))
			{
				$this->m_Query->Update($this->m_Table, $this->m_Fields, array($p_ID=>$this->m_Fields[$p_ID]));
			}
		}
		$this->m_Query->Execute();
	}
}


?>