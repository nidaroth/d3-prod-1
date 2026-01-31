<? 




$db_uat=mysqli_connect('184.73.75.183','root','DSISMySQLPa$$1!','DSIS');



$sql="SELECT malc.CODE,smbd.*,smbm.BATCH_NO FROM S_MISC_BATCH_DETAIL smbd LEFT JOIN M_AR_LEDGER_CODE malc ON malc.PK_AR_LEDGER_CODE=smbd.PK_AR_LEDGER_CODE LEFT JOIN S_MISC_BATCH_MASTER smbm ON smbm .PK_MISC_BATCH_MASTER  = smbd .PK_MISC_BATCH_MASTER  WHERE smbd.PK_STUDENT_MASTER ='1275163' AND smbd .PK_ACCOUNT =83";


$uat_query=mysqli_query($db_uat,$sql);
$i=0;
$disbusement=array();
while ($row=mysqli_fetch_assoc($uat_query)) { 

	//echo "<br><hr>";
	// echo "<pre>";
	// print_r($row);

	$disbusement[]=$row;
	//$sql_n="SELECT * FROM PK_AR_LEDGER_CODE WHERE CODE=".$row['CODE'];
	//echo $sql_n;

   //echo my_insert('S_STUDENT_DISBURSEMENT',$row);
	//echo $i;
	$i++;

}
mysqli_close($db_uat);
$db= mysqli_connect('localhost','root','root','DSIS');

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
  //mysqli_query($db, $insert) or die(mysqli_error($db));	 
  return $insert;

}

foreach($disbusement as $val)
{
	//echo "<br><hr>";
	$sql_n="SELECT PK_AR_LEDGER_CODE FROM M_AR_LEDGER_CODE WHERE CODE='".$val['CODE']."' AND PK_ACCOUNT ='81'";
	$AR_query=mysqli_query($db,$sql_n);
	$rows=mysqli_fetch_assoc($AR_query);


	$sql_n="SELECT PK_MISC_BATCH_MASTER FROM S_MISC_BATCH_MASTER WHERE BATCH_NO='".$val['BATCH_NO']."' AND PK_ACCOUNT ='81'";
	$T_query=mysqli_query($db,$sql_n);
	$t_rows=mysqli_fetch_assoc($T_query);

	$val['PK_AR_LEDGER_CODE']=$rows['PK_AR_LEDGER_CODE'];
	$val['PK_MISC_BATCH_MASTER']=$t_rows['PK_MISC_BATCH_MASTER'];
	$val['PK_STUDENT_MASTER']='774736';
	$val['PK_ACCOUNT']='81';
	//$val['PK_STUDENT_ENROLLMENT']='1022098';
	$val['PK_STUDENT_ENROLLMENT']='985991';

	
	unset($val['CODE']);
	unset($val['BATCH_NO']);
	unset($val['PK_MISC_BATCH_DETAIL']);
    echo "<br>";
	echo my_insert('S_MISC_BATCH_DETAIL',$val).';';
	echo "<br>";



}




?>

