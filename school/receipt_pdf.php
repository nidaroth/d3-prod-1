<? require_once("../global/config.php"); 
require_once("receipt_pdf_function.php"); 
require_once("check_access.php");
//diam-777
// if(check_access('MANAGEMENT_ACCOUNTING') == 0 ){
// 	header("location:../index");
// 	exit;
// }

$save = 0;
if($_GET['save'] == 1)
	$save = 1;
$file = generate_invoice_pdf($_GET['did'], $_GET['mid'], $_GET['misc_id'], $save);

/*if($save == 1)
	header("location:".$file);*/
