<?phpfunction Query($p_Query){	try	{	$db = new PDO('sqlite:main.db');	$res=$db->query($p_Query);	$db=null;	return $res;	}	catch (PDOException $e)	{		echo 'Exception: ' . $e->getMessage();	}}?>