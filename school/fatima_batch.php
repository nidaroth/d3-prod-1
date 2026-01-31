<? 

//$db_uat=mysqli_connect('184.73.75.183','root','DSISMySQLPa$$1!','DSIS');

$db= mysqli_connect('diamondsis-d3-prod-instance-1.cj8aalmd5rsa.us-east-1.rds.amazonaws.com','root','DSISMySQLPa$$1!','DSIS');
if($db){
	echo "connected";
}else{
	echo "failed";
}


//$sql="SELECT M_AR_LEDGER_CODE.CODE,S_STUDENT_DISBURSEMENT.* FROM S_STUDENT_DISBURSEMENT LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE=S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE WHERE PK_STUDENT_MASTER ='1275163' AND PK_STUDENT_ENROLLMENT ='3466942' AND S_STUDENT_DISBURSEMENT.PK_ACCOUNT=83";

//$sql="SELECT S_STUDENT_DISBURSEMENT.*,S_PAYMENT_BATCH_MASTER.PK_PAYMENT_BATCH_MASTER FROM S_STUDENT_DISBURSEMENT LEFT JOIN S_PAYMENT_BATCH_MASTER ON S_PAYMENT_BATCH_MASTER.BATCH_NO = S_STUDENT_DISBURSEMENT.BATCH WHERE  PK_STUDENT_MASTER ='774736' AND PK_STUDENT_ENROLLMENT ='1022098' AND BATCH_NO IN ('F-26576-495','F-26576-495','F-26345-1443')";
$sql="SELECT * FROM S_STUDENT_DISBURSEMENT LEFT JOIN S_PAYMENT_BATCH_MASTER ON S_PAYMENT_BATCH_MASTER.BATCH_NO = S_STUDENT_DISBURSEMENT.BATCH WHERE PK_STUDENT_MASTER ='774736' AND PK_STUDENT_ENROLLMENT ='985991' AND BATCH_NO in('F-26117-2','F-26526-1446','F-26576-495','F-26576-495','F-26522-1443','F-26637-495','F-26637-495','F-26446-495','F-26504-495','F-26504-495','F-26426-1443','F-26428-1446','F-26366-1446','F-26363-1443','F-26384-495','F-26345-1443','F-26344-1446','F-26317-1446','F-26314-1443','F-26245-495','F-26211-1446','F-26208-1443','F-26300-495','F-26175-1446','F-26173-1443')";
//exit;
$uat_query=mysqli_query($db,$sql)or die(mysqli_error($db));

$i=0;
$disbusement=array();
while ($row=mysqli_fetch_assoc($uat_query)) 
{ 

		$PAYMENT_BATCH_DETAIL = array();
		$res_query = mysqli_query($db,"SELECT RECEIPT_NO FROM Z_ACCOUNT WHERE PK_ACCOUNT = '81'");
		$res_bat=mysqli_fetch_assoc($res_query) ;
		$RECEIPT_NO = $res_bat['RECEIPT_NO'];
		
		$RECEIPT_NO1 = $RECEIPT_NO + 1;
		//exit;
		mysqli_query($db,"UPDATE Z_ACCOUNT SET RECEIPT_NO = '$RECEIPT_NO1' WHERE PK_ACCOUNT = '81'"); 
		
		$PAYMENT_BATCH_DETAIL['PK_BATCH_PAYMENT_STATUS'] = 3;

		$batch_query="SELECT * FROM S_PAYMENT_BATCH_DETAIL WHERE PK_PAYMENT_BATCH_MASTER=".$row['PK_PAYMENT_BATCH_MASTER']." order by PK_PAYMENT_BATCH_DETAIL ASC limit 1";		
		$batch_details_query=mysqli_query($db,$batch_query);
		$batch_details=mysqli_fetch_assoc($batch_details_query);
		
		
		$PAYMENT_BATCH_DETAIL['RECEIPT_NO'] 				= $RECEIPT_NO;
		$PAYMENT_BATCH_DETAIL['PK_STUDENT_MASTER']  		= 774736;
		$PAYMENT_BATCH_DETAIL['PK_STUDENT_ENROLLMENT']  	= 985991;
		$PAYMENT_BATCH_DETAIL['PK_PAYMENT_BATCH_MASTER']  	= $row['PK_PAYMENT_BATCH_MASTER'];
		$PAYMENT_BATCH_DETAIL['PK_STUDENT_DISBURSEMENT']  	= $row['PK_STUDENT_DISBURSEMENT'];
		$PAYMENT_BATCH_DETAIL['DUE_AMOUNT']  				= $row['DISBURSEMENT_AMOUNT'];
		$PAYMENT_BATCH_DETAIL['BATCH_TRANSACTION_DATE']  	= $batch_details['BATCH_TRANSACTION_DATE'];
		$PAYMENT_BATCH_DETAIL['RECEIVED_AMOUNT']  			= $row['DISBURSEMENT_AMOUNT'];
		$PAYMENT_BATCH_DETAIL['PRIOR_YEAR']  				= $row['PRIOR_YEAR'];
		$PAYMENT_BATCH_DETAIL['PK_TERM_BLOCK']  			= $row['PK_TERM_BLOCK'];
		$PAYMENT_BATCH_DETAIL['CHECK_NO']  					= $batch_details['CHECK_NO'];
		$PAYMENT_BATCH_DETAIL['BATCH_DETAIL_DESCRIPTION']  	= $batch_details['BATCH_DETAIL_DESCRIPTION'];
		$PAYMENT_BATCH_DETAIL['PK_ACCOUNT']  				= '81';
		$PAYMENT_BATCH_DETAIL['CREATED_BY']  				= $batch_details['CREATED_BY'];
		$PAYMENT_BATCH_DETAIL['CREATED_ON']  				= $batch_details['CREATED_ON'];
		
		if($PAYMENT_BATCH_DETAIL['BATCH_TRANSACTION_DATE'] != '')
		{
			$PAYMENT_BATCH_DETAIL['BATCH_TRANSACTION_DATE'] = $batch_details['BATCH_TRANSACTION_DATE'];
		}
		
		// if($PAYMENT_BATCH_DETAIL['RECEIVED_AMOUNT'] != $PAYMENT_BATCH_DETAIL['DUE_AMOUNT'])
		// {
		// 	$PAYMENT_BATCH_DETAIL['DISBURSEMENT_TYPE'] = $row['DISBURSEMENT_AMOUNT'];
		// }

		// echo "<pre>";

		// print_r($PAYMENT_BATCH_DETAIL);
		$batch_query1="SELECT * FROM S_PAYMENT_BATCH_DETAIL WHERE PK_STUDENT_DISBURSEMENT=".$row['PK_STUDENT_DISBURSEMENT']." limit 1";		
		$batch_details_query1=mysqli_query($db,$batch_query1);
		$num_rows=mysqli_num_rows($batch_details_query1);
		if($num_rows==0){
		$insert_statment= my_insert('S_PAYMENT_BATCH_DETAIL',$PAYMENT_BATCH_DETAIL);
		mysqli_query($db, $insert_statment) or die(mysqli_error($db));
		$PK_PAYMENT_BATCH_DETAIL =mysqli_insert_id($db);
		$PK_STUDENT_DISBURSEMENT=$row['PK_STUDENT_DISBURSEMENT'];
		mysqli_query($db,"UPDATE S_STUDENT_DISBURSEMENT SET PK_PAYMENT_BATCH_DETAIL = '$PK_PAYMENT_BATCH_DETAIL' WHERE PK_ACCOUNT = '81' AND PK_STUDENT_DISBURSEMENT='$PK_STUDENT_DISBURSEMENT'"); 
		}

}
//mysqli_close($db_uat);

function my_insert($tbl,$dbHash)
{
	global $db;
	$dbInsVar = array();
  $dbInsVal = array();
  foreach ($dbHash as $dbVar=>$dbVal) {
    array_push($dbInsVar,$dbVar);
    if (strtolower($dbVal) == 'null') {
	    array_push($dbInsVal,'null');
    }
    else { // else encapsulate in quotes

    		array_push($dbInsVal,"'".$dbVal."'");
	}
  }
  //$insert = mysql_query("INSERT INTO ".$tbl." (".implode(", ",$dbInsVar).") VALUES (".implode(", ",$dbInsVal).")",$db);
  $insert="INSERT INTO ".$tbl." (".implode(", ",$dbInsVar).") VALUES (".implode(", ",$dbInsVal).")";
 // mysqli_query($db, $insert) or die(mysqli_error($db));	 
  return $insert;

}

/*foreach($disbusement as $val)
{
	//echo "<br><hr>";
	$sql_n="SELECT PK_AR_LEDGER_CODE FROM M_AR_LEDGER_CODE WHERE CODE='".$val['CODE']."' AND PK_ACCOUNT ='81'";
	$AR_query=mysqli_query($db,$sql_n);
	$rows=mysqli_fetch_assoc($AR_query);

	$val['PK_AR_LEDGER_CODE']=$rows['PK_AR_LEDGER_CODE'];
	$val['PK_STUDENT_MASTER']='774736';
	$val['PK_ACCOUNT']='81';
	$val['PK_STUDENT_ENROLLMENT']='1022098';
	$val['PK_STUDENT_ENROLLMENT']='985991';

	unset($val['FUND_SRC']);
	unset($val['FUND_ID']);
	unset($val['SCHOOL_ACID']);
	unset($val['CLASS_ADAID']);
	unset($val['PAY_PERIOD']);
	unset($val['FILE_EXT']);
	unset($val['BATCH_ID']);
	unset($val['REENTRY_DATE']);
	unset($val['DISBURSEMENT_NUMBER']);
	unset($val['BUGDET_ID']);
	unset($val['SHEDULED_AID_ID']);
	unset($val['REV_ON']);
	unset($val['PK_STUDENT_DISBURSEMENT']);
	unset($val['CODE']);
    echo "<br>";
	echo my_insert('S_STUDENT_DISBURSEMENT',$val).';';
	echo "<br>";



}*/




?>

