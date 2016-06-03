<?php

include_once('backend/Query.php');
include_once('backend/Log.php');

class DBItem
{
	protected $m_Properties;
	protected $m_TableName;
	protected $m_Type;
	protected $m_ID;
	public function __construct($p_TableName, $p_ID){
		$this->m_Properties=array();
		$this->m_TableName=$p_TableName;
		$this->m_ID=$p_ID;
		$this->m_Type=get_class($this);
	}
	
	public function Load(array $p_Properties){
		if($p_Properties===null){return false;}
		$query=new Query(DB_NAME);
		$query->Select('*',$this->m_TableName,$p_Properties);
		$query->Execute();
		
		$data=$query->FetchAssoc();
		if(count($data)<=0){
			return false;
		}
		
		$this->m_Properties=$data[0];
		return true;
	}
	
	public function Create(array $p_Properties){
		if($p_Properties===null){return false;}
		$id=$this->m_ID;
		if(!isset($p_Properties[$id])){
			return false;
		}
		$query=new Query(DB_NAME);
		if($query->InsertIfNotExist($this->m_TableName, array($id=>$p_Properties[$id]), $p_Properties, $id)===false){
			Log::logAdv('Could not create db entry: ' . $this->m_Type . ', Params: ' . $p_Properties);
			return false;
		}
		
		$this->m_Properties=$p_Properties;
	}
	
	public function Save(){
		$id=$this->m_ID;
		$query=new Query(DB_NAME);
		$query->InsertIfNotElseUpdate($this->m_TableName, array($id=>$p_Properties), $this->m_Properties, $id);
	}
	
	public function __get($p_Key){
		return isset($this->m_Properties[$p_Key])?$this->m_Properties[$p_Key]:null;
	}
	
	public function __set($p_Key,$p_Val){
		if(isset($this->m_Properties[$p_Key])){
			$this->m_Properties[$p_Key]=$p_Val;
		}
	}
}



?>