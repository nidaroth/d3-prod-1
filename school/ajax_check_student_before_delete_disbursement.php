<? require_once("../global/config.php");
//DIAM-950
$sql="SELECT S_PAYMENT_BATCH_MASTER.BATCH_NO FROM `S_PAYMENT_BATCH_DETAIL` LEFT JOIN S_PAYMENT_BATCH_MASTER ON S_PAYMENT_BATCH_MASTER.PK_PAYMENT_BATCH_MASTER=S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_MASTER WHERE PK_STUDENT_DISBURSEMENT=".$_REQUEST['id'];
$res_disb = $db->Execute($sql);
if($res_disb->RecordCount()>0){
	echo $res_disb->fields['BATCH_NO'];
}else{
	echo 0;
}


?>
