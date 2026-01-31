<? require_once("../global/config.php"); 
require_once("../language/student.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
} 
$PK_STUDENT_FINANCIAL_ACADEMY  	= $_REQUEST['PK_STUDENT_FINANCIAL_ACADEMY'];
$period_count 					= $_REQUEST['period_count']; 
$PK_STUDENT_ENROLLMENT 			= $_REQUEST['eid']; 
$PK_STUDENT_MASTER 				= $_REQUEST['sid'];

if($PK_STUDENT_FINANCIAL_ACADEMY == '') {
	$PERIOD 		= '';
	$PERIOD_BEGIN 	= '';
	$TEST_DATE 	 	= '';
} else {
	$res_dd = $db->Execute("select * FROM S_STUDENT_FINANCIAL_ACADEMY WHERE PK_STUDENT_FINANCIAL_ACADEMY = '$PK_STUDENT_FINANCIAL_ACADEMY' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	$PERIOD 		= $res_dd->fields['PERIOD'];
	$PERIOD_BEGIN   = $res_dd->fields['PERIOD_BEGIN'];
	$PERIOD_END 	= $res_dd->fields['PERIOD_END'];
	
	if($PERIOD_BEGIN != '0000-00-00')
		$PERIOD_BEGIN = date("m/d/Y",strtotime($PERIOD_BEGIN));
	else
		$PERIOD_BEGIN = '';
		
	if($PERIOD_END != '0000-00-00')
		$PERIOD_END = date("m/d/Y",strtotime($PERIOD_END));
	else
		$PERIOD_END = '';
}
?>
<div class="d-flex" id="period_div_<?=$period_count?>" >
	<input type="hidden" name="PK_STUDENT_FINANCIAL_ACADEMY[]" value="<?=$PK_STUDENT_FINANCIAL_ACADEMY?>" />
	<input type="hidden" name="period_count[]" value="<?=$period_count?>" />
	<div class="col-md-4">
		<input type="text" class="form-control" placeholder="" name="PERIOD[]" id="PERIOD_<?=$period_count?>" value="<?=$PERIOD?>" />
	</div>
	
	<div class="col-sm-4" >
		<input type="text" class="form-control date" placeholder="" name="PERIOD_BEGIN[]" id="PERIOD_BEGIN_<?=$period_count?>" value="<?=$PERIOD_BEGIN?>" onchange="validate_fa_period_date('PERIOD_BEGIN', '<?=$period_count?>')" /><!-- Ticket # 1977  -->
	</div>
	
	<div class="col-sm-3" >
		<input type="text" class="form-control date" placeholder="" name="PERIOD_END[]" id="PERIOD_END_<?=$period_count?>" value="<?=$PERIOD_END?>" onchange="validate_fa_period_date('PERIOD_END', '<?=$period_count?>')" /><!-- Ticket # 1977  -->
	</div>
	
	<div class="col-sm-1" >
		<a href="javascript:void(0)" onclick="delete_row(<?=$period_count?>,'period')" ><i class="far fa-trash-alt"></i></a>
	</div>
</div>