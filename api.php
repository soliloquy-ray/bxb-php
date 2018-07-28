<?php
$http_origin = $_SERVER['HTTP_ORIGIN'];

if ($http_origin == "http://localhost:8100" || $http_origin == "https://bxb-app.azurewebsites.net")
{  
    header("Access-Control-Allow-Origin: $http_origin");
    header("Access-Control-Allow-Headers: 'Origin, X-Requested-With, Content-Type, Accept'");
}


include_once "index.php";

$req = $_REQUEST['q'];
$p = json_decode(file_get_contents('php://input'), true);

function login($username = '', $password = '', $out = ''){
	$db = DB::getInstance();
	$conn = $db->getConnection();
	$sth = $conn->prepare("EXEC uspLogin ?, ?");
	$sth->bindParam(1, $username);
	$sth->bindParam(2, $password);
	$sth->execute();
	
	$arr = array();
	if($i = $sth->fetch(PDO::FETCH_ASSOC)){
		return $i['master_id'];
	}
	else{
		return false;
	}

}

function getUserDetailsById($id = 0){
	$db = DB::getInstance();
	$conn = $db->getConnection();
	$sth = $conn->prepare("EXEC uspEmpDetailsGet ?");
	$sth->bindParam(1, $id);
	$sth->execute();

	$arr = array();
	while($i = $sth->fetch(PDO::FETCH_ASSOC)){
		$arr[] = $i;
	}

	echo json_encode($arr);
}

function addUser($user = array()){
	$db = DB::getInstance();
	$conn = $db->getConnection();
	$loginName = $user["login"];
	$pass = $user["password"];
	$mobile = $user["mobile"];
	$email = $user["email"];
	$id = $user["id"];
	$resp = "";

	$sth = $conn->prepare("EXEC uspUserAdd ?, ?, ?, ?, ?, ?");
	$sth->bindParam(1, $loginName);
	$sth->bindParam(2, $pass);
	$sth->bindParam(3, $mobile);
	$sth->bindParam(4, $email);
	$sth->bindParam(5, $id);
	$sth->bindParam(6, $resp, PDO::PARAM_STR|PDO::PARAM_INPUT_OUTPUT, 4000);

	$sth->execute();

	echo $resp;
}

function getUserByTIN($tin = 0, $birth = '0000-00-00', $ccode = ''){
	$db = DB::getInstance();
	$conn = $db->getConnection();

	$sth = $conn->prepare("uspUserVerify ?, ?"); // AND Company = ? 
	$sth->bindParam(1, $tin);
	$sth->bindParam(2, $birth);
	//$sth->bindParam(3, $ccode);
	$sth->execute();
	$arr = array();
	while($i = $sth->fetch(PDO::FETCH_ASSOC)){
		$arr[] = $i;
	}

	echo !empty($arr) ? json_encode($arr) : json_encode(array(false));
}

switch ($req) {
	case 'login':
		$usr = isset($p['username']) ? $p['username'] : "";
		$pass = isset($p['pass']) ? $p['pass'] : "";
		$id = login($usr,$pass);
		if($id){
			getUserDetailsById($id);
		}else{
			echo [false];
		}
		break;
	case 'signup':
		addUser($p);
		break;
	case 'get_by_tin':
		getUserByTIN($p['tin'],$p['birth']);
		break;
	default:
		# code...
		break;
}

/*$uname = "blu";
$upass = "password";
$out = "";
$sth = $conn->prepare("exec uspLogin ?, ?, ?");
$sth->bindParam(1, $uname);
$sth->bindParam(2, $upass);
$sth->bindParam(3, $out, PDO::PARAM_STR|PDO::PARAM_INPUT_OUTPUT, 4000);
$sth->execute();

print_r($out);*/


?>