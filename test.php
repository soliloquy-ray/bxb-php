<?php
require_once("index.php");
require_once("mail.php");


	$db = DB::getInstance();
	$conn = $db->getConnection();

	$tin = 102843275;
	$user = "agvil";
	$pass = "agvilpass";
	$sth = $conn->prepare("SELECT * From tblMasterList");
	//$sth->bindParam(1, $tin);
	//$sth->bindParam(1, $user);
	//$sth->bindParam(2, $pass);
	$sth->execute();
	$arr = array();
	while($i = $sth->fetch(PDO::FETCH_ASSOC)){
		$arr[] = $i;
	}

	echo json_encode($arr);
/*
$api = "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";
	$mail = Mail::getInstance();

	$succ = $mail->send('rsantos@bxbesc.com','santos.ray.rommel@gmail.com','Subject','Predicate <b>bold</b>');
	if($succ){
		echo "Successful!";
	}
	else{
		echo "Failed!";
	}*/
?>