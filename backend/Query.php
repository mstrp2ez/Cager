<?php

include_once('DB.php');


class Query
{
	var $m_Query;
	var $m_Table;
	var $m_DB;
	var $m_SortExt;
	var $m_LimExt;
	var $m_Ext;
	var $m_Error;
	var $m_QueryResult;
	var $m_TimeTokens;
	var $m_LastQueryType;
	
	function __construct($p_DB)
	{

		if(!isset($GLOBALS['db'])){return;}
		$this->m_DB=$GLOBALS['db'];
		
		$this->Table='';
		$this->m_SortExt='';
		$this->m_LimExt='';
		$this->m_Ext='';
		$this->m_Error=array();
		$this->m_QueryResult=null;
		$this->m_TimeTokens=array('%NOW'=>'NOW()','%CURDATE'=>'CURDATE()','%CURTIME'=>'CURTIME()');
		$this->m_LastQueryType='';
		
		$this->m_DB->SelectDB($p_DB);
		mysql_query("SET NAMES 'utf8'");
	}
	
	function __get($p_Key)
	{
		if(strtolower($p_Key)==='db')
		{
			return $this->m_DB;
		}
		else if(strtolower($p_Key)==='result')
		{
			return $this->m_QueryResult;
		}
	}
	
	function __set($p_Key, $p_Val)
	{
		$p_Val=$this->EscapeVals($p_Val);
		
		if($p_Key==='Ext')
		{
			$this->m_Ext=$p_Val;
		}
		else if(strtolower($p_Key)==='error')
		{
			return $this->m_Error;
		}
	}
	
	function SortOn($p_Sort)
	{
		$p_Sort=$this->EscapeVals($p_Sort);
		
		$this->m_SortExt=$p_Sort;
	}
	
	function Limit($p_Limit)
	{
		$p_Limit=mysql_real_escape_string($p_Limit);
		$this->m_LimExt=$p_Limit;
	}
	
	function ClearQuery()
	{
		$this->m_Query='';
		$this->m_Ext='';
		$this->m_SortExt='';
		$this->LimExt='';
		$this->m_QueryResult=null;
	}
	
	function GetMysqlError()
	{
		return mysql_error($this->m_DB->db);
	}
	
	function GetQuery()
	{
		return $this->m_Query;
	}

	/*
		Fields - String or Zero based array of field strings e.g. array('id','name','points') 
		KeyVal - Array as such array(key=>val)
	*/
	function Select($p_Fields, $p_Table, $p_KeyVal, $p_AndOr=array(), $p_Op=array())
	{
		$sQ='SELECT ';
		
		if(!is_array($p_Fields))
		{
			$p_Fields=explode(",", $p_Fields);
		}
		
		$iC=count($p_Fields);
		for($i=0;$i<$iC;$i++)
		{
			$part=mysql_real_escape_string($p_Fields[$i]);
			
			if($part==='*'){$sQ .= $part; break;}
			$sQ .= "`" . $part . "`";
			if($i!==($iC-1)){$sQ.=', ';}
		}

		$p_Op=$this->EscapeVals($p_Op);
		
		$p_AndOr=$this->EscapeVals($p_AndOr);
		
		$p_Table=mysql_real_escape_string($p_Table);
		$this->m_Table=$p_Table;
		
		$sQ.=" FROM `" . $p_Table . "`";
		
		$keys=array_keys($p_KeyVal);
		$vals=array_values($p_KeyVal);
		
		$keys=$this->EscapeVals($keys);
		$vals=$this->EscapeVals($vals);
		
		if(count($keys)!==count($vals)){$this->m_Error[]='Key value count missmatch (SELECT)';return;}
		
		$sQ.=" WHERE ";
		$iC=count($keys);
		if($iC==0)
		{
			$sQ.=" '1'='1' ";
		}
		for($i=0;$i<$iC;$i++)
		{
			$part=$vals[$i];
			if(is_array($part))
			{
				foreach($part as $k=>$xI)
				{
					if(is_string($xI))
					{
						$part[$k]="'" . $xI . "'";
					}
				}
				$sQ.= "`" . $keys[$i] . "` IN (" . implode(", ", $part) . ") ";
			}
			else
			{
				if(substr($part,1,0)=='%'){$part=substr($part,strlen($part)+1);}
				if(is_string($part)){$part="'" . $part . "'";}
				if(is_numeric($keys[$i])){$keys[$i]='`' . $keys[$i] . '`';}
				$op=($i>=count($p_Op))?"=":$p_Op[$i];
				$sQ.="`" . $keys[$i] . "`" . $op . $part;
			}
			$AndOr=($i>=count($p_AndOr))?" AND ":" " . $p_AndOr[$i] . " ";
			if($i!==($iC-1)){$sQ.=$AndOr;}
		}
		$this->m_Query=$sQ;
		$this->m_LastQueryType='SELECT';
	}
	
	function Insert($p_Table, $p_KeyVals, $p_Mult=false)
	{
		$sQ='';
		
		$keys=array_keys($p_KeyVals);
		$vals=array_values($p_KeyVals);
		
		$keys=$this->EscapeVals($keys);
		$vals=$this->EscapeVals($vals);
		
		$bFirst=(strlen($this->m_Query)<=0)?true:false;
		
		if($p_Mult===false||$bFirst)
		{
			$sQ="INSERT INTO " . mysql_real_escape_string($p_Table);		
				
			if(count($keys)!==count($vals)){$this->m_Error[]='Key value count missmatch (INSERT)';return;}
			if(count($keys)<=0){$this->m_Error[]='No key/val pairs (INSERT)';return;}
			
			
			$sQ.=" (";
			$iC=count($keys);
			for($i=0;$i<$iC;$i++)
			{
				$sQ.= "`" . $keys[$i] . "`";
				if($i<($iC-1)){$sQ.=", ";}
			}
		
			$sQ.=") VALUES ";
		}
		$sQ.=($p_Mult&&!$bFirst)?", (":"(";
		
		$iC=count($keys);
		for($i=0;$i<$iC;$i++)
		{
			$part=$vals[$i];
			if(is_string($part))
			{
				if(isset($this->m_TimeTokens[$part]))
				{
					$part=$this->m_TimeTokens[$part];
				}
				else
				{
					$part="'" . $part . "'";
				}
			}
			$sQ.=$part;
			if($i<($iC-1)){$sQ.=", ";}
		}
		$sQ.=")";
		
		$this->m_Query.=$sQ;
		$this->m_LastQueryType='INSERT';
	}
	
	function Update($p_Table, $p_Set, $p_Selector, $p_AndOr=null, $p_SetOp=array(), $p_Operators=array())
	{
		$sQ='UPDATE ' . mysql_real_escape_string($p_Table) . ' SET ';
		
		$keys=array_keys($p_Set);
		$vals=array_values($p_Set);
		
		$keys=$this->EscapeVals($keys);
		$vals=$this->EscapeVals($vals);
		
		if(count($keys)!==count($vals)){$this->m_Error[]='Key value count missmatch (UPDATE)';return;}
		if(count($keys)<=0){$this->m_Error[]='No key/val pairs (UPDATE)';return;}
		
		$iC=count($keys);
		for($i=0;$i<$iC;$i++)
		{
			$part=$vals[$i];
			if(is_string($part))
			{
				$part="'" . $part . "'";
			}
			$op=(isset($p_SetOp[$i]))?$p_SetOp[$i]:'=';
			$sQ.=$keys[$i] . $op . $part;
			if($i!=($iC-1)){$sQ.=', ';}
		}
		
		$keys=array_keys($p_Selector);
		$vals=array_values($p_Selector);
		
		$keys=$this->EscapeVals($keys);
		$vals=$this->EscapeVals($vals);
		
		$sQ.=" WHERE ";
		$iC=count($keys);
		if($iC==0)
		{
			$sQ.=" '1'='1' ";
		}
		for($i=0;$i<$iC;$i++)
		{
			$part=$vals[$i];
			if(is_array($part))
			{
				foreach($part as $xI)
				{
					if(is_string($xI))
					{
						$xI="'" . $xI . "'";
					}
				}
				$sQ.=$keys[$i] . " IN (" . implode(", ", $vals[$i]) . ") ";
			}
			else
			{
				if(is_string($part))
				{
					if(isset($this->m_TimeTokens[$part]))
					{
						$part=$this->m_TimeTokens[$part];
					}
					else
					{
						$part="'" . $part . "'";
					}
				}
				$op=(isset($p_Operators[$i]))?$p_Operators[$i]:'=';
				$sQ.=$keys[$i] . $op . $part;
			}
			
			$AndOr=($p_AndOr===null||$i>=count($p_AndOr))?" AND ":" OR ";
			if($i!==($iC-1)){$sQ.=$AndOr;}
		}
		$this->m_Query=$sQ;
		$this->m_LastQueryType='UPDATE';
	}
	
	function Delete($p_Table,$p_Selector)
	{
		$p_Table=mysql_real_escape_string($p_Table);
		
		$sQ='DELETE FROM ' . $p_Table;
		
		$keys=array_keys($p_Selector);
		$vals=array_values($p_Selector);
		
		$keys=$this->EscapeVals($keys);
		$vals=$this->EscapeVals($vals);
		
		if(count($keys)!==count($vals)){$this->m_Error[]='Key value count missmatch (UPDATE)';return;}
		if(count($keys)<=0){$this->m_Error[]='No key/val pairs (UPDATE)';return;}
		
		$sQ.=' WHERE ';
		
		$iC=count($keys);
		for($i=0;$i<$iC;$i++)
		{
			$part=$vals[$i];
			if(is_string($part))
			{
				$part="'" . $part . "'";
			}
			$sQ.=$keys[$i] . "=" . $part;
			if($i!=($iC-1)){$sQ.=', ';}
		}
		
		$this->m_Query=$sQ;
		$this->LastQueryType='DELETE';
	}
	
	/* function CreateTable($p_TableName, $p_Keys, $p_IfNotExist=true){
		$sQ='CREATE TABLE ';
		if($p_IfNotExist){$sQ.='IF NOT EXISTS';}
		
	} */
	
	function InsertIfNotExist($p_Table, $p_Indices, $p_KeyVal, $p_Token='*')
	{
		$this->Select($p_Token,$p_Table,$p_Indices);
		$this->Execute();
		$result=$this->FetchAssoc();
		
		if(count($result)>0){return false;}
		
		$this->ClearQuery();
		$this->Insert($p_Table,$p_KeyVal);
		$this->Execute();
	
		return true;
	}
	
	function InsertIfNotElseUpdate($p_Table, $p_Indices, $p_KeyVal, $p_Token='*')
	{
		$this->Select($p_Token,$p_Table,$p_Indices);
		$this->Execute();
		$result=$this->FetchAssoc();
		if(count($result)>0)		
		{
			$this->ClearQuery();
			$this->Update($p_Table,$p_KeyVal,$p_Indices);
			$this->Execute();
		}
		else
		{
			$this->ClearQuery();
			$this->Insert($p_Table, $p_KeyVal);
			$this->Execute();
		}
	}
	
	function CountRows($p_Table, $p_Selector)
	{
		if(!is_string($p_Table)){return -1;}
		
		$keys=array_keys($p_Selector);
		$vals=array_values($p_Selector);
		
		$keys=$this->EscapeVals($keys);
		$vals=$this->EscapeVals($vals);
		
		if(count($keys)!==count($vals)){$this->m_Error[]='Key value count missmatch (UPDATE)';return;}
		if(count($keys)<=0){$this->m_Error[]='No key/val pairs (UPDATE)';return;}
		
		$sQ='';
		$iC=count($keys);
		for($i=0;$i<$iC;$i++)
		{
			$part=$vals[$i];
			if(is_string($part))
			{
				$part="'" . $part . "'";
			}
			$sQ.=$keys[$i] . "=" . $part;
			if($i!=($iC-1)){$sQ.=', ';}
		}
		
		$this->m_Query="SELECT count(*) FROM " . $p_Table . " WHERE " . $sQ;
		$this->m_LastQueryType='SELECT';
		$this->Execute();
		
		$d=$this->FetchAssoc();
		if(count($d)>0)
		{
			return $d[0]['count(*)'];
		}
		return 0;
	}
	
	function TableFields($p_Table)
	{
		$this->m_Query.="SHOW COLUMNS FROM " . $p_Table;
		$this->m_LastQueryType='SELECT';
	}
	
	function Execute()
	{
		if(strlen($this->m_Query)<=0){$this->m_Error[]='Query string is empty. (Execute)';return;}
		if($this->m_QueryResult!==null){$this->m_Error[]='Warning: Query result not empty. (Execute)';}
		
		$this->m_Ext=$this->m_SortExt . " " . $this->m_LimExt;
		
		$this->m_Query.= " " . $this->m_Ext;
		
	//	print_r($this->m_Query . '<br/>');
		$this->m_QueryResult=mysql_query($this->m_Query, $this->m_DB->db);
		
	}
	
	function Raw($p_Query, array $p_Vals)
	{
		if(count($p_Vals)<=0){return false;}
		
		$p_Vals=$this->EscapeVals($p_Vals);
		
		$token='%';
		$count=1;
		foreach($p_Vals as $item)
		{
			$p_Query=str_replace($token . $count, $item, $p_Query);
			$count++;
		}
		
		$this->m_QueryResult=mysql_query($p_Query, $this->m_DB->db);
		//echo $p_Query;
		$this->m_LastQueryType='RAW';
	}
	
	function FetchField()
	{
		if($this->m_LastQueryType!=='SELECT'&&$this->m_LastQueryType!=='RAW'){return 1;}
		if($this->m_QueryResult===false||$this->m_QueryResult===null){$this->m_Error[]='Query result is invalid. (FetchAssoc)';return;}
		
		$ret=array();
		while(($data=mysql_fetch_field($this->m_QueryResult)))
		{
			$ret[]=$data;
		}
		//print_r($ret);
		return $ret;
	}
	
	function FetchAssoc()
	{
		if($this->m_LastQueryType!=='SELECT'&&$this->m_LastQueryType!=='RAW'){return 1;}
		if($this->m_QueryResult===false||$this->m_QueryResult===null){$this->m_Error[]='Query result is invalid. (FetchAssoc)';return;}
		
		
		$ret=array();
		while(($data=mysql_fetch_assoc($this->m_QueryResult))!==false)
		{
			$ret[]=$data;
		}
		
		return $ret;
	}
	
	function FetchRow()
	{
		if($this->m_LastQueryType!=='SELECT'&&$this->m_LastQueryType!=='RAW'){return 1;}
		if($this->m_QueryResult===false||$this->m_QueryResult===null){$this->m_Error[]='Query result is invalid. (FetchRow)';return;}
		
		$ret=array();
		while(($data=mysql_fetch_array($this->m_QueryResult))!==false)
		{
			$ret[]=$data;
		}
		
		return $ret;
	}
	
	private function EscapeVals($p_Val)
	{
		if(is_array($p_Val))
		{
			foreach($p_Val as $key=>$val)
			{
				if(!is_array($val))
				{
					$p_Val[$key]=mysql_real_escape_string($val);
				}
				else
				{
					$this->EscapeVals($val);
				}
			}
		}
		else
		{
			$p_Val=mysql_real_escape_string($p_Val);
		}
		return $p_Val;
	}
	
}

?>