<?php
	require_once '../dompdf_0-8-2/autoload.inc.php';
	require_once("../index.php");
	//header('Content-disposition: attachment; filename="mytablea.xlsx"');
	//header("Content-type: application/vnd.ms-excel");
	//header('Cache-Control: max-age=0');

	$cid = $_GET['cid'];
	$d1 = $_GET['d1'];
	$d2 = $_GET['d2'];

	$db = DB::getInstance();
	$conn = $db->getConnection();

	$sth = $conn->prepare("EXEC uspSOAbyCompany ?, ?, ?");

	$sth->bindParam(1,  $d1);
	$sth->bindParam(2,  $d2);
	$sth->bindParam(3,  $cid);
	$sth->execute();
	$arr = array();
	while($i = $sth->fetch(PDO::FETCH_ASSOC)){
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
		$arr = $j;
	}

	$sth = $conn->prepare("EXEC uspSOAbyCompanyDetail ?, ?");

	$sth->bindParam(1,  $arr['billPeriod']);
	$sth->bindParam(2,  $cid);
	$sth->execute();
	$sched = array();
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
			'status'=>'Active',
			'term'=>$i['numberPaydays']
		);
		$sched[] = $j;
	}


	$sth = $conn->prepare("EXEC uspCompanyGet ?");

	$sth->bindParam(1,  $cid);
	$sth->execute();
	$comp = $sth->fetch(PDO::FETCH_ASSOC);

	
?>
<html>
<body style="padding:5px 10px;margin:0;font-family:'Calibri',sans-serif;background-color: #fff;">
	<table style="height:100%;width:100%;display: table; margin: auto;border:0;">
		<tr>
			<td colspan="9">
				<img src="https://bxb-app.azurewebsites.net/assets/imgs/logo.png" style="height:125px;">
			</td>
		</tr>
		<tr>
			<th colspan="6" style="text-align:left;">STATEMENT OF ACCOUNT</th>
			<th colspan="1" style="text-align:left;">Company Code</th>
			<td colspan="2"><?=substr('00'.$cid,-3)?></td>
		</tr>

		<tr>
			<td colspan="6">SOA Ref# 201831D4</td>
			<td colspan="1">Billing Period</td>
			<td colspan="2">08-31-2018 to <?=$arr['billPeriod']?></td>
		</tr>
		<tr>
			<td colspan="6"><?=$comp['company_name']?></td>
			<td colspan="1">Due Date</td>
			<td colspan="2"><?=$arr['billPeriod']?></td>
		</tr>
		<tr>
			<td colspan="6">&nbsp;</td>
			<td colspan="1">Amount Due</td>
			<td colspan="2"><?=number_format((float)$arr['amt'], 2, '.', '')?></td>
		</tr>
		<tr>
			<td colspan="6">&nbsp;</td>
			<td colspan="1">Previous Unpaid Balance</td>
			<td colspan="2">0</td>
		</tr>
		<tr>
			<td colspan="6">&nbsp;</td>
			<td colspan="1">Total Amount Due</td>
			<td colspan="2"><?=number_format((float)$arr['amt'], 2, '.', '')?></td>
		</tr>
		
		<tr>
			<td colspan="9">&nbsp;</td>
		</tr>

		<tr>
			<th colspan="9">CURRENT BALANCE</th>
		</tr>
		<tr>
			<th colspan="9" style="background-color:#333;height:3px;"></th>
		</tr>
		<tr>
			<th style="text-align:left;">Transaction Date</th>
			<th style="text-align:left;">Credit Availment Number</th>
			<th style="text-align:left;">Member ID</th>
			<th style="text-align:left;">First Name</th>
			<th style="text-align:left;">Last Name</th>
			<th style="text-align:left;">Seq. No.</th>
			<th style="text-align:left;">Employee ID</th>
			<th style="text-align:left;">Transaction Type</th>
			<th style="text-align:left;">Repayment Amount</th>
		</tr>

		<?php
			foreach($sched as $s):
		?>
		<tr>
			<td><?=$s['transDate']?></td>
			<td><?=$s['creditAvailmentNumber']?></td>
			<td><?=$s['memberID']?></td>
			<td><?=$s['firstName']?></td>
			<td><?=$s['lastName']?></td>
			<td><?=$s['seqNo']."/".$s['term']?></td>
			<td><?=$s['empID']?></td>
			<td><?=$s['transType']?></td>
			<td><?=number_format((float)$s['repaymentAmt'], 2, '.', '')?></td>
		</tr>
		<?php endforeach; ?>
		<!--tr>
			<td>06-24-2018</td>
			<td>MEM01</td>
			<td>20</td>
			<td>Dwight Ebenezer</td>
			<td>Santos</td>
			<td>3</td>
			<td>TOD6</td>
			<td>CREDIT AVAILMENT</td>
			<td>2,395.83</td>
		</tr>
		<tr>
			<td>07-01-2018</td>
			<td>MEM02</td>
			<td>26</td>
			<td>Mark Anthony</td>
			<td>Cadag</td>
			<td>2</td>
			<td>TOD9</td>
			<td>CREDIT AVAILMENT</td>
			<td>5,125.00</td>
		</tr>
		<tr>
			<td>07-05-2018</td>
			<td>MEM03</td>
			<td>27</td>
			<td>Alyssa Mae</td>
			<td>Caluya</td>
			<td>2</td>
			<td>TOD13</td>
			<td>CREDIT AVAILMENT</td>
			<td>2,025.00</td>
		</tr-->

		<tr>
			<td colspan="9">&nbsp;</td>
		</tr>

		<tr>
			<th colspan="9">PREVIOUS BALANCE</th>
		</tr>
		<tr>
			<th colspan="9" style="background-color:#333;height:3px;"></th>
		</tr>
		<tr>
			<th>Transaction Date</th>
			<th>Credit Availment Number</th>
			<th>Member ID</th>
			<th>First Name</th>
			<th>Last Name</th>
			<th>Seq. No.</th>
			<th>Employee ID</th>
			<th>Transaction Type</th>
			<th>Repayment Amount</th>
		</tr>
		<!--tr>
			<td>07-18-2018</td>
			<td>MEM04</td>
			<td>33</td>
			<td>Kristoffer</td>
			<td>Cuevas</td>
			<td>1</td>
			<td>TOD15</td>
			<td>CREDIT AVAILMENT</td>
			<td>1,312.50</td>
		</tr>
		<tr>
			<td>07-26-2018</td>
			<td>MEM05</td>
			<td>37</td>
			<td>Krystel</td>
			<td>Manuel</td>
			<td>1</td>
			<td>TOD16</td>
			<td>CREDIT AVAILMENT</td>
			<td>895.83</td>
		</tr>
		<tr>
			<td>06-24-2018</td>
			<td>MEM01</td>
			<td>20</td>
			<td>Dwight Ebenezer</td>
			<td>Santos</td>
			<td>4</td>
			<td>TOD6</td>
			<td>CREDIT AVAILMENT</td>
			<td>2,395.83</td>
		</tr>
		<tr>
			<td>07-05-2018</td>
			<td>MEM03</td>
			<td>27</td>
			<td>Alyssa Mae</td>
			<td>Caluya</td>
			<td>3</td>
			<td>TOD13</td>
			<td>CREDIT AVAILMENT</td>
			<td>2,025.00</td>
		</tr>
		<tr>
			<td>07-18-2018</td>
			<td>MEM04</td>
			<td>33</td>
			<td>Kristoffer</td>
			<td>Cuevas</td>
			<td>2</td>
			<td>TOD15</td>
			<td>CREDIT AVAILMENT</td>
			<td>1,312.50</td>
		</tr>
		<tr>
			<td>07-26-2018</td>
			<td>MEM05</td>
			<td>37</td>
			<td>Krystel</td>
			<td>Manuel</td>
			<td>2</td>
			<td>TOD16</td>
			<td>CREDIT AVAILMENT</td>
			<td>895.83</td>
		</tr-->
		<tr>
			<td colspan="9">&nbsp;</td>
		</tr>
		<tr>
			<th colspan="9" style="background-color:#333;height:3px;"></th>
		</tr>
		<tr>
			<th colspan="9">END OF STATEMENT</th>
		</tr>

	</table>
</body>
</html>
<?php
//print($str);
exit;
?>