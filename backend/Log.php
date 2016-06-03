<?php

include_once('mysqlfunctions.php');

class Log
{
	public function __construct()
	{
	}
	
	static public function log($p_Message)
	{
		MysqlShort::InsertSimple(LOG_TABLE,array('message'=>serialize($p_Message),'ip'=>$_SERVER['REMOTE_ADDR'], 'created'=>date('Y-m-d h:i:s')));
	}
	
	static public function logAdv($p_Params) //logs key=>vals in param as string
	{
		if(is_null($p_Params)){return false;}
		
		$out='(';
		foreach($p_Params as $key=>$val){
			if(is_array($val)){
				$out.=self::Unwind($val);
			}else{
				$out.=$key . '=>' . $val . ' ';
			}
		}
		$out.=')';
		MysqlShort::InsertSimple(LOG_TABLE,array('message'=>$out,'ip'=>$_SERVER['REMOTE_ADDR'], 'created'=>date('Y-m-d h:i:s')));
	}
	
	static private function Unwind($p_P)
	{
		if(!is_array($p_P)){return $p_P;}
		$str=' (';
		foreach($p_P as $key=>$val)
		{
			if(is_array($val))
			{
				$str.=self::Unwind($val);
			}
			else
			{
				$str.= $key . '=>' . $val . ' ';
			}
		}
		$str.=') ';
		
		return $str;
	}
	
	static public function SetMessage($p_Message, $p_Status) {
		if(!isset($_SESSION["messages"])) {
			$_SESSION["messages"] = array();
		}
		$_SESSION["messages"][] = array("message" => $p_Message, "status" => $p_Status);
	}
	
	static public function RenderMessages() {
		$output = '';
		if(isset($_SESSION["messages"])) {
			$output .= '<ul class="messages">';
			foreach($_SESSION["messages"] as $message) {
				$output .= '<li class="'.$message["status"].'">'.$message["message"].'</li>';
			}
			$output .= '</ul>';
			unset($_SESSION["messages"]);
		}
		print($output);
	}
}


?>