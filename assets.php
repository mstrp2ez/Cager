<?php	if(!isset($_POST['t'])){echo json_encode(array('result'=>0,'reason'=>'Invalid parameters'));return;}	$res=@file_get_contents('assets/'.$_POST['t']);	if($res==false){echo json_encode(array('result'=>0,'reason'=>'Invalid parameters'));return;}	echo $res;?>