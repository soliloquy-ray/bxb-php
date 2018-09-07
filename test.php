<?php
require_once("index.php");
require_once("mail.php");
require_once("twilio.php");

/**/
	$db = DB::getInstance();
	$conn = $db->getConnection();

	$tin = 102843275;
	$user = "agvil";
	$pass = "agvilpass";
	$sth = $conn->prepare("SELECT * From tblMasterList");
	//$sth = $conn->prepare("uspEnvironmentApi twilio");
	//$sth->bindParam(1, $tin);
	//$sth->bindParam(1, $user);
	//$sth->bindParam(2, $pass);
	$sth->execute();
	$arr = array();
	while($i = $sth->fetch(PDO::FETCH_ASSOC)){
		//$i['applicationDate'] = date('Y-m-d H:i:s',strtotime($i['applicationDate']));
		$arr[] = $i;
	}

	echo json_encode($arr);

?>