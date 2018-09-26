<?php
$http_origin = $_SERVER['HTTP_ORIGIN'];

if ($http_origin == "http://localhost:8100" || $http_origin == "https://bxb-app.azurewebsites.net" || $http_origin == "https://test.bxbesc.com")
{  
    header("Access-Control-Allow-Origin: $http_origin");
    header("Access-Control-Allow-Headers: 'Origin, X-Requested-With, Content-Type, Accept'");
}
if($_SERVER['REQUEST_METHOD'] == "OPTIONS") exit;

require_once("index.php");
require_once("mail.php");
require_once("twilio.php");

$req = $_REQUEST['q'];
$p = json_decode(file_get_contents('php://input'), true);

function getSendgridConfig(){
	$db = DB::getInstance();
	$conn = $db->getConnection();
	$sth = $conn->prepare("EXEC uspEnvironmentApi sendgrid");
	$sth->execute();
	$ret = "";
	while($i = $sth->fetch(PDO::FETCH_ASSOC)){
		if($i['name']=="sendgrid"){
			$ret = $i['value'];
			break;
		}
	}
	return $ret;
}

function getTwilioConfig(){
	$db = DB::getInstance();
	$conn = $db->getConnection();
	$sth = $conn->prepare("EXEC uspEnvironmentApi twilio");
	$sth->execute();
	$arr = array();
	while($i = $sth->fetch(PDO::FETCH_ASSOC)){
		$arr[$i['name']] = $i['value'];
	}
	return $arr;
}

function loginAdmin($username = '', $password = '', $out = ''){
	$db = DB::getInstance();
	$conn = $db->getConnection();
	$sth = $conn->prepare("EXEC uspLogin ?, ?");
	$sth->bindParam(1, $username);
	$sth->bindParam(2, $password);
	$sth->execute();
	
	try{
		$i = $sth->fetch(PDO::FETCH_ASSOC);
		return $i;
	}catch(Exception $e){
		return false;
	}

}

function login($username = '', $password = '', $out = ''){
	$db = DB::getInstance();
	$conn = $db->getConnection();
	$sth = $conn->prepare("EXEC uspLogin ?, ?");
	$sth->bindParam(1, $username);
	$sth->bindParam(2, $password);
	$sth->execute();
	
	try{
		$i = $sth->fetch(PDO::FETCH_ASSOC);
		return $i['master_id'];
	}catch(Exception $e){
		return false;
	}

}

function updateLoanStatus($loanId = 0,$status = 0){
	$db = DB::getInstance();
	$conn = $db->getConnection();
	$sth = $conn->prepare("EXEC uspLoanUpdateStatus ?, ?");
	$sth->bindParam(1, $loanId);
	$sth->bindParam(2, $status);
	$sth->execute();
	
	echo $sth->rowCount();
}

/**
@master_id int,
@principal float,
@interest float,
@paydays int,
@purpose varchar(1000),
@applicationDate datetime,
@processFund float,
@collectionFund float,
@documentFee float   **/
function addLoan($loan){
	$dt = date('Y-m-d H:i:s');
	$db = DB::getInstance();
	$conn = $db->getConnection();
	$sth = $conn->prepare("EXEC uspLoanApply ?, ?, ?, ?, ?, ?, ?, ?, ? ");
	$sth->bindParam(1, $loan['id']);
	$sth->bindParam(2, $loan['principal']);
	$sth->bindParam(3, $loan['interest']);
	$sth->bindParam(4, $loan['paydays']);
	$sth->bindParam(5, $loan['purpose']);
	$sth->bindParam(6, $dt);
	$sth->bindParam(7, $loan['processFund']);
	$sth->bindParam(8, $loan['collectionFund']);
	$sth->bindParam(9, $loan['documentFee']);
	$sth->execute();
	
	var_dump($sth->rowCount());
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

	return $arr[count($arr)-1];
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

function addHr($user = array()){
	$db = DB::getInstance();
	$conn = $db->getConnection();
	$loginName = $user["login"];
	$pass = $user["password"];
	$mobile = $user["mobile"];
	$email = $user["email"];
	$id = 0;
	$resp = "";

	$sth = $conn->prepare("EXEC uspUserAdd ?, ?, ?, ?, ?, ?");
	$sth->bindParam(1, $loginName);
	$sth->bindParam(2, $pass);
	$sth->bindParam(3, $mobile);
	$sth->bindParam(4, $email);
	$sth->bindParam(5, $id);
	$sth->bindParam(6, $resp, PDO::PARAM_STR|PDO::PARAM_INPUT_OUTPUT, 4000);

	$sth->execute();
	
	$i = $sth->fetch();
	return $i[0];
}

function assignRole($loginId = 0, $compId = 0, $roleId = 2){
	$db = DB::getInstance();
	$conn = $db->getConnection();

	$sth = $conn->prepare("EXEC uspRoleAssignment ?, ?, ?");
	$sth->bindParam(1, $loginId);
	$sth->bindParam(2, $roleId);
	$sth->bindParam(3, $compId);
	$sth->bindParam(6, $resp, PDO::PARAM_STR|PDO::PARAM_INPUT_OUTPUT, 4000);

	$sth->execute();
	echo $sth->rowCount();
}

function getUserByTIN($tin = 0, $birth = '0000-00-00', $ccode = ''){
	$db = DB::getInstance();
	$conn = $db->getConnection();

	$sth = $conn->prepare("EXEC uspUserVerify ?, ?"); // AND Company = ? 
	$sth->bindParam(1, $tin);
	$sth->bindParam(2, $birth);
	//$sth->bindParam(3, $ccode);
	$sth->execute();
	$arr = array();
	while($i = $sth->fetch(PDO::FETCH_ASSOC)){
		if(isset($i['userid']) && $i['userid'] != ""){
			$i['error'] = "Exists";
		}
		$arr[] = $i;
	}

	echo !empty($arr) ? json_encode($arr) : json_encode(array(false));
}

function checkUnameExists($uname){
	$db = DB::getInstance();
	$conn = $db->getConnection();

	$sth = $conn->prepare("EXEC uspUserVerifyExist ?"); // AND Company = ? 
	$sth->bindParam(1, $uname);
	$sth->execute();
	$arr = array();
	while($i = $sth->fetch(PDO::FETCH_ASSOC)){
		$arr[] = $i;
	}

	echo !empty($arr) ? json_encode($arr) : false;
}

function changePass($id,$pass,$newPass){
	$db = DB::getInstance();
	$conn = $db->getConnection();

	$sth = $conn->prepare("uspUserUpdatePassword ?, ?, ?"); // AND Company = ? 
	$sth->bindParam(1, $id);
	$sth->bindParam(2, $pass);
	$sth->bindParam(3, $newPass);
	$res = $sth->execute();

	echo json_encode($res);
}

function sendMailForgotPw($email){

	$api = getSendgridConfig();
	$mail = Mail::getInstance($api);

	$succ = $mail->send('rsantos@bxbesc.com',$email,'Reset Password',@file_get_contents('./templates/forgotpw.html'));
	if($succ){
		echo "Successful!";
	}
	else{
		echo "Failed!";
	}
}

function sendSMSOTP($mobile,$otp){

	$api = getTwilioConfig();
	$tw = Twilio::getInstance($api['twilio_service_id'],$api['twilio_auth_token'],$api['twilio_number']);

	$tw->sms($mobile,"Your One time Password (OTP) for bxbesc is ".$otp);

}

function sendLoanApproval($mobile,$amt){

	$api = getTwilioConfig();
	$tw = Twilio::getInstance($api['twilio_service_id'],$api['twilio_auth_token'],$api['twilio_number']);

	$tw->sms($mobile,"Your application for PhP ".$amt." has been sent. If you do not receive confirmation within 24 hrs, kindly contact your HR. Thank you");
}

function sendLoanApproved($mobile){

	$api = getTwilioConfig();
	$tw = Twilio::getInstance($api['twilio_service_id'],$api['twilio_auth_token'],$api['twilio_number']);

	$tw->sms($mobile,"Congratulations! Your BxB Credit availment has been approved. The funds net of fees have been credited to your payroll account. Thank you for availing.");

}

function getUserLoanByStatus($stat,$userId = 0){
	$db = DB::getInstance();
	$conn = $db->getConnection();

	//$sth = $conn->prepare("SELECT * FROM tblLoan WHERE status = ? "); // AND Company = ? 
	$sth = $conn->prepare("EXEC uspLoanGetEmployeebyStatus ?, ?"); // AND Company = ? 
	$sth->bindParam(1, $userId);
	$sth->bindParam(2, $stat);
	$sth->execute();

	$arr = array();
	while($i = $sth->fetch(PDO::FETCH_ASSOC)){
		$i['applicationDate'] = date('m-d-Y',strtotime($i['applicationDate']));
		$i['term'] = $i['numberPaydays'];
		//$i['accept'] = $GLOBALS['kval'];
		$arr[] = $i;
	}

	echo json_encode($arr);

}

function getLoanDetailsByStatus($stat, $cid){

	$db = DB::getInstance();
	$conn = $db->getConnection();

	//$sth = $conn->prepare("SELECT * FROM tblLoan WHERE status = ? "); // AND Company = ? 
	$sth = $conn->prepare("EXEC uspLoanGetbyStatus ?, ?"); // AND Company = ? 
	$sth->bindParam(1, $cid);
	$sth->bindParam(2, $stat);
	$sth->execute();

	$arr = array();
	while($i = $sth->fetch(PDO::FETCH_ASSOC)){
		$ud = getUserDetailsById($i['master_id']);
		$i['employeeName'] = $ud['Name_First']." ".$ud['Name_Last'];
		$i['applicationDate'] = date('m-d-Y',strtotime($i['applicationDate']));
		$i['term'] = $i['numberPaydays'];
		$i['userData'] = $ud;
		$arr[] = $i;
	}

	echo json_encode($arr);
}

function manualAddEmp($emp){
	$db = DB::getInstance();
	$conn = $db->getConnection();

	$sth = $conn->prepare("EXEC uspMasterListUpload ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?");
	$sth->bindParam(1,  $emp['lastName']);
	$sth->bindParam(2,  $emp['firstName']);
	$sth->bindParam(3,  $emp['middleName']);
	$sth->bindParam(4,  $emp['email']);
	$sth->bindParam(5,  $emp['mobile']);
	$sth->bindParam(6,  $emp['company']);
	$sth->bindParam(7,  $emp['hiredDate']);
	$sth->bindParam(8,  $emp['gender']);
	$sth->bindParam(9,  $emp['birthday']);
	$sth->bindParam(10, $emp['position']);
	$sth->bindParam(11, $emp['entity']);
	$sth->bindParam(12, $emp['type']);
	$sth->bindParam(13, $emp['division']);
	$sth->bindParam(14, $emp['netSalary']);
	$sth->bindParam(15, $emp['grossSalary']);
	$sth->bindParam(16, $emp['payrollAccount']);
	$sth->bindParam(17, $emp['bankName']);
	$sth->bindParam(18, $emp['vacationLeave']);
	$sth->bindParam(19, $emp['sickLeave']);
	$sth->bindParam(20, $emp['maternityLeave']);
	$sth->bindParam(21, $emp['paternityLeave']);
	$sth->bindParam(22, $emp['tin']);
	$sth->bindParam(23, $emp['companyId']);
	$res = $sth->execute();

	echo json_encode($res);
}

/**
@company_name, @country, @address, @city, @zipcode, @phone, @mobile, @maxLoan, @minLoan, @maxRate, @minRate, @bank varchar(100),
    @bankBranch varchar(100),
    @bankAddress varchar(300),
    @bankAccountNumber varchar(100),
    @swiftCode varchar(100),
    @docSubmit bit,
    @docSEC bit,
    @docBIR bit,
    @docFinStatement bit,
    @docGenInformation bit
*/
function addCompany($c){
	$db = DB::getInstance();
	$conn = $db->getConnection();

	$docSecRegist = $c['docs']['secRegist'];
	$docBir2307 = $c['docs']['bir2307'];
	$docFinStat = $c['docs']['finStat'];
	$docGis = $c['docs']['gis'];

	if(!$c['docSubmitted']){
		$docSecRegist = 0;
		$docBir2307 = 0;
		$docFinStat = 0;
		$docGis = 0;
	}

	$sth = $conn->prepare("EXEC uspCompanyAdd ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?");
	$sth->bindParam(1,  $c['companyName']);
	$sth->bindParam(2,  $c['country']);
	$sth->bindParam(3,  $c['address']);
	$sth->bindParam(4,  $c['city']);
	$sth->bindParam(5,  $c['zip']);
	$sth->bindParam(6,  $c['phone']);
	$sth->bindParam(7,  $c['mobile']);
	$sth->bindParam(8,  $c['maxLoan']);
	$sth->bindParam(9,  $c['minLoan']);
	$sth->bindParam(10, $c['maxRate']);
	$sth->bindParam(11, $c['minRate']);
	$sth->bindParam(12, $c['bankName']);
	$sth->bindParam(13, $c['bankBranch']);
	$sth->bindParam(14, $c['bankAddress']);
	$sth->bindParam(15, $c['accountNumber']);
	$sth->bindParam(16, $c['swiftCode']);
	$sth->bindParam(17, $c['docSubmitted']);
	$sth->bindParam(18, $docSecRegist);
	$sth->bindParam(19, $docBir2307);
	$sth->bindParam(20, $docFinStat);
	$sth->bindParam(21, $docGis);
	$res = $sth->execute();

	echo json_encode($res);
}

function addPaymentSchedule($loan, $id){
	$db = DB::getInstance();
	$conn = $db->getConnection();

	$sth = $conn->prepare("EXEC uspPaymentEntryAdd ?, ?, ?, ?, ?");

	foreach($loan as $l){
		$sth->bindParam(1,  $id);
		$sth->bindParam(2,  $l['paymentDate']);
		$sth->bindParam(3,  $l['paymentNum']);
		$sth->bindParam(4,  $l['amt']);
		$sth->bindParam(5,  $l['bal']);
		$res = $sth->execute();
	}

	echo json_encode($res);
}

function getPaymentSchedByLoanID($loanID){
	$db = DB::getInstance();
	$conn = $db->getConnection();

	$sth = $conn->prepare("EXEC uspPaymentGetbyLoanID ?");

	$sth->bindParam(1,  $loanID);
	$sth->execute();
	$arr = array();
	while($i = $sth->fetch(PDO::FETCH_ASSOC)){
		//$i['applicationDate'] = date('Y-m-d H:i:s',strtotime($i['applicationDate']));
		$arr[] = $i;
	}

	echo json_encode($arr);
}

function getLoanCountByStatus($companyId, $status){
	$db = DB::getInstance();
	$conn = $db->getConnection();

	$sth = $conn->prepare("EXEC uspLoanGetCountbyStatus ?, ?");

	$sth->bindParam(1,  $companyId);
	$sth->bindParam(2,  $status);
	$sth->execute();
	$arr = array();
	$i = $sth->fetch(PDO::FETCH_ASSOC);

	return $i;
}

function updatePaymentSchedule($loan, $id){
	$db = DB::getInstance();
	$conn = $db->getConnection();

	$sth = $conn->prepare("EXEC uspPaymentUpdate ?, ?, ?, ?, ?");

	foreach($loan as $l){
		$sth->bindParam(1,  $id);
		$sth->bindParam(2,  $l['paymentDate']);
		$sth->bindParam(3,  $l['paymentNum']);
		$sth->bindParam(4,  $l['amt']);
		$sth->bindParam(5,  $l['bal']);
		$res = $sth->execute();
	}

	echo json_encode($res);
}

function genSOA($soa){
	$db = DB::getInstance();
	$conn = $db->getConnection();

	$sth = $conn->prepare("EXEC uspGenSOA ?");

	$sth->bindParam(1,  $loanID);
	$sth->execute();
	$arr = array();
	while($i = $sth->fetch(PDO::FETCH_ASSOC)){
		//$i['applicationDate'] = date('Y-m-d H:i:s',strtotime($i['applicationDate']));
		$arr[] = $i;
	}

	echo json_encode($arr);
}

function getSOAByDate($soa){
	$db = DB::getInstance();
	$conn = $db->getConnection();

	$sth = $conn->prepare("EXEC uspSOAbyCompany ?, ?, ?");

	$sth->bindParam(1,  $soa['date1']);
	$sth->bindParam(2,  $soa['date2']);
	$sth->bindParam(3,  $soa['cid']);
	$sth->execute();
	$arr = array();
	while($i = $sth->fetch(PDO::FETCH_ASSOC)){
		//$i['applicationDate'] = date('Y-m-d H:i:s',strtotime($i['applicationDate']));
		$j = array(
			'pdf'=>'PDF',
			'company'=>$i['company_name'],
			'billPeriod'=>date('Y-m-d',strtotime($i['payDate'])),
			'amt'=>$i['payAmount'],
			'soaNo'=>$i['soaref'],
			'refNo'=>'',
			'companyAct'=>($i['status']==1 ? 'paid' : 'unpaid'),
			'status'=>($i['status']==1 ? 'paid' : 'unpaid'),
			'mgtAct'=>($i['status']==1 ? 'confirmed' : 'confirm'),
			'CompanyID'=>$i['CompanyID']
		);
		$arr[] = $j;
	}

	echo json_encode($arr);
}

function getSOAPaymentsByDate($soa){
	$db = DB::getInstance();
	$conn = $db->getConnection();

	$sth = $conn->prepare("EXEC uspSOAbyCompanyDetail ?, ?");

	$sth->bindParam(1,  $soa['date']);
	$sth->bindParam(2,  $soa['cid']);
	$sth->execute();
	$arr = array();
	while($i = $sth->fetch(PDO::FETCH_ASSOC)){
		//$i['applicationDate'] = date('Y-m-d H:i:s',strtotime($i['applicationDate']));
		$j = array(
			'transDate'=>date('Y-m-d',strtotime($i['payDate'])),
			'creditAvailmentNumber'=>$i['paymentID'],
			'memberID'=>$i['master_id'],
			'firstName'=>$i['Name_First'],
			'lastName'=>$i['Name_Last'],
			'seqNo'=>$i['payCount'],
			'empID'=>$i['master_id'],
			'transType'=>'Credit Availment',
			'repaymentAmt'=>$i['payAmount'],
			'status'=>'Active'
		);
		$arr[] = $j;
	}

	//"transDate":"2018-08-07",
	//"creditAvailmentNumber":41,
	//"memberID":"00252",
	//"firstName":"Ray",
	//"lastName":"Santos",
	//"seqNo":"1/12",
	//"empID":"123",
	//"transType":"Credit Availment",
	//"repaymentAmt":2395.83,
	//"status":"Active"

	echo json_encode($arr);

}

function getCompaniesByID($id = NULL){
	$db = DB::getInstance();
	$conn = $db->getConnection();

	$sth = $conn->prepare("EXEC uspCompanyGet ?");

	$sth->bindParam(1,  $id);
	$sth->execute();
	$arr = array();
	while($i = $sth->fetch(PDO::FETCH_ASSOC)){
		$i['companyName'] = $i['company_name'];
		$i['companyCode'] = substr('00'.$i['CompanyID'],-3);
		$arr[] = $i;
	}

	echo json_encode($arr);

}

switch ($req) {
	case 'login':
		$usr = isset($p['username']) ? $p['username'] : "";
		$pass = isset($p['pass']) ? $p['pass'] : "";
		$id = login($usr,$pass);
		if($id){
			echo json_encode(getUserDetailsById($id));
		}else{
			echo json_encode(array(false));
		}
		break;
	case 'admin-login':
		$usr = isset($p['username']) ? $p['username'] : "";
		$pass = isset($p['pass']) ? $p['pass'] : "";
		$id = loginAdmin($usr,$pass);
		if($id){
			echo json_encode($id);
		}else{
			echo json_encode(array(false));
		}
		break;
	case 'signup':
		addUser($p);
		break;
	case 'get_by_tin':
		getUserByTIN($p['tin'],$p['birth']);
		break;
	case 'check_user_exists':
		checkUnameExists($p['uname']);
		break;
	case 'forgotpassmail':
		sendMailForgotPw($p['email']);
		break;
	case 'changepass':
		changePass($p['id'],$p['pass'],$p['newpass']);
		break;
	case 'applyloan':
		//echo json_encode($p['loan']);
		//break;
		addLoan($p['loan']);
		break;
	case 'get_loan_by_status':
		getUserLoanByStatus($p['status'],$p['id']);
		break;
	case 'hr_get_loan_by_status':
		getLoanDetailsByStatus($p['status'], $p['cid']);
		break;
	case 'update_loan_status':
		updateLoanStatus($p['id'],$p['status']);
		break;
	case 'gen_otp':
		$hash = preg_replace('/[0-9]+/', '', $p['h']);
		$val = preg_replace('/[0-9]+/', '', $p['otp']);
  		$otp = "";
	  	for($i = 0; $i<strlen($val);$i++){
	  		$otp .= strpos($hash,$val[$i]);
	  	}
		sendSMSOTP($p['mobile'],$otp);
		echo json_encode(array($hash,$val,$otp));
		break;
	case 'send_sms_loan_approval':
		sendLoanApproval($p['mobile'],$p['amt']);
		break;
	case 'send_sms_loan_approved':
		sendLoanApproved($p['mobile']);
		break;
	case 'uploadcsv':
		//echo json_encode($p['data']);
		foreach ($p['data'] as $key => $value) {
			echo json_encode(explode(",", $value));
		}
		break;
	case 'manual_add_employee':
		//print_r($p['emp']);
		manualAddEmp($p['emp']);
		break;
	case 'new_company':
		//print_r($p);
		addCompany($p);
		//manualAddEmp($p['emp']);
		break;
	case 'add_payment_sched':
		addPaymentSchedule($p['paysched'],$p['id']);
		break;
	case 'get_payment_sched_by_id':
		getPaymentSchedByLoanID($p['loanID']);
		break;
	case 'generate_soa':
		genSOA($p['soa']);
		break;
	case 'get_soa_by_date':
		getSOAByDate($p['soa']);
		break;
	case 'get_soa_details_by_date':
		getSOAPaymentsByDate($p['soa']);
		break;
	case 'get_company_by_id':
		getCompaniesByID($p['cid']);
		break;
	case 'add_hr':
		$logId = addHr($p);
		assignRole($logId, $p['companyId'], 2);
		break;
	case 'get_pending_loan_count':
		echo getLoanCountByStatus($p['cid'], 1)['pending'];
		break;
	case 'test':
		echo json_encode(getallheaders());
		break;
	default:
		echo json_encode(getallheaders());
		# code...
		break;
}

?>