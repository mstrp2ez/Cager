<?php


define('db_host','127.0.0.1');
define('db_user','cager');
define('db_password','asd');
define('DB_NAME','Cager');

define('BASE_PATH','/IsometricTest/');

define('IMAGE_DIR', '/upload');
define('IMAGE_DIR_THUMBS', '/upload/thumbnail');


function build_image_tag($p_Item,$p_Res,array $p_Dim=array()){
	$tag='<img src=" 1" width=" 2" height=" 3" class="" alt />';
	if(!isset($p_Item[$p_Res])){return $tag;}
	
	$img=$p_Item[$p_Res]['attrib'];
	$replace=array();
	if($p_Res=='Resize'){
		if(count($p_Dim)<=0){return $tag;}
		$resize='w=' . $p_Dim[0] . '&h=' . $p_Dim[1];
		$new_size=str_replace('w=xxx&h=xxx',$resize,$img['path']);
		$replace=array($new_size,$p_Dim[0],$p_Dim[1]);
	}else{
		$replace=array($img['path'],$img['width'],$img['height']);
	}
	
	$iC=count($replace);
	for($i=0;$i<$iC;$i++){
		$idx=' ' . ($i+1);
		$tag=str_replace($idx,$replace[$i],$tag);
	}
	return $tag;
}
function build_image_tag_ref($p_Path,array $p_Size){
	$tag='<img src=" 1" width=" 2" height=" 3" class="" alt />';
	if(count($p_Size)<=0){return $tag;}
	$resize='w=' . $p_Size[0] . '&h=' . $p_Size[1];
	$new_size=str_replace('w=xxx&h=xxx',$resize,$p_Path);
	$replace=array($new_size,$p_Size[0],$p_Size[1]);
	$iC=count($replace);
	for($i=0;$i<$iC;$i++){
		$idx=' ' . ($i+1);
		$tag=str_replace($idx,$replace[$i],$tag);
	}
	return $tag;
}
function format_currency($p_Num){
	return number_format(doubleval($p_Num), 2, '.', ' ');
}
function Gisset($p_O,$p_D){
	return (isset($_GET[$p_O]))?$_GET[$p_O]:$p_D;
}
function Pisset($p_O,$p_D){
	return (isset($_POST[$p_O]))?$_POST[$p_O]:$p_D;
}

function base_redirect(){
	if(strpos($_SERVER['PHP_SELF'], 'index.php')===false){	
		header('Location: ' . 'http://' . $_SERVER['SERVER_ADDR'] . '/' . PROJECT_NAME);
	}
}

function silent_file_get_contents($p_Url, $p_Context)
{
	$headers=get_headers($p_Url);
	$code=substr($headers[0], 9, 3);
	
	if($code!='404')
	{
		return file_get_contents($p_Url, false, $p_Context);
	}
	return false;
}

function nextPowerOf2($p_N)
{
	$n=$p_N;

	$n--;
	$n |= $n >> 1;   
	$n |= $n >> 2;   
	$n |= $n >> 4;
	$n |= $n >> 8;
	$n |= $n >> 16;
	$n++;       

	return $n;    
}

function json_encodeUU($data)
{
	return preg_replace_callback('/\\\u(\w\w\w\w)/', 
    function($matches)
    {
        return '&#'.hexdec($matches[1]).';';
    }
    , json_encode($data));
}

function errorRedirect($p_Loc)
{
	header('Location: ' . $p_Loc);
}

?>