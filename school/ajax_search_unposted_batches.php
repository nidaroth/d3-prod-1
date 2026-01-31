<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/bulk_text.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$cond 		= "";
$group_by 	= "";
$table 		= "";
$s_student_track_table=""; //DIAM-1017

if(!empty($_REQUEST)){
	$timezone = $_SESSION['PK_TIMEZONE'];
	if($timezone == '' || $timezone == 0) {
		$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$timezone = $res->fields['PK_TIMEZONE'];
		if($timezone == '' || $timezone == 0)
			$timezone = 4;
	}
	
	$res_tz = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
	$cond = "";
	if($_REQUEST['START_DATE'] != '' && $_REQUEST['END_DATE'] != '') {
		$ST = date("Y-m-d",strtotime($_REQUEST['START_DATE']));
		$ET = date("Y-m-d",strtotime($_REQUEST['END_DATE']));

		$cond .= " AND DATE_FORMAT(UNPOSTED_ON, '%Y-%m-%d') BETWEEN '$ST' AND '$ET' ";
	} else if($_REQUEST['START_DATE'] != ''){
		$ST = date("Y-m-d",strtotime($_REQUEST['START_DATE']));
		$cond .= " AND DATE_FORMAT(UNPOSTED_ON, '%Y-%m-%d') >= '$ST' ";
	} else if($_REQUEST['END_DATE'] != ''){
		$ET = date("Y-m-d",strtotime($_REQUEST['END_DATE']));
		$cond .= " AND DATE_FORMAT(UNPOSTED_ON, '%Y-%m-%d') <= '$ET' ";
	}

	if($_REQUEST['PK_CAMPUS']!=""){
		$PK_CAMPUS_IDS= $_REQUEST['PK_CAMPUS'];
		$cond_payment .= " AND FIND_IN_SET(BATCH_PK_CAMPUS,'".$PK_CAMPUS_IDS."') > 0";
		$cond_misc .= "  AND FIND_IN_SET(MISC_BATCH_PK_CAMPUS,'".$PK_CAMPUS_IDS."') > 0";
		$cond_tuition .= " AND FIND_IN_SET(TUITION_BATCH_PK_CAMPUS,'".$PK_CAMPUS_IDS."') > 0 ";
		
	}

	


	$batch_type=explode(',',$_REQUEST['BATCH_TYPES']);



	// PAYMENT BATCH
	$query_payment = "select S_PAYMENT_BATCH_UNPOSTED_HISTORY.PK_PAYMENT_BATCH_UNPOSTED_HISTORY, CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, BATCH_NO, UNPOSTED_ON , S_PAYMENT_BATCH_MASTER.BATCH_PK_CAMPUS
	FROM S_PAYMENT_BATCH_UNPOSTED_HISTORY 
	LEFT JOIN Z_USER ON Z_USER.PK_USER = S_PAYMENT_BATCH_UNPOSTED_HISTORY.UNPOSTED_BY 
	LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID 
	, S_PAYMENT_BATCH_MASTER 
	WHERE 
	S_PAYMENT_BATCH_MASTER.ACTIVE = 1  AND 
	S_PAYMENT_BATCH_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	S_PAYMENT_BATCH_MASTER.PK_PAYMENT_BATCH_MASTER = S_PAYMENT_BATCH_UNPOSTED_HISTORY.PK_PAYMENT_BATCH_MASTER $cond $cond_payment ";

	
	
	// MISC PAYMENT BATCH
	$query_misc = "select S_MISC_BATCH_UNPOSTED_HISTORY.PK_MISC_BATCH_UNPOSTED_HISTORY, CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, BATCH_NO, UNPOSTED_ON, S_MISC_BATCH_MASTER.MISC_BATCH_PK_CAMPUS
	FROM S_MISC_BATCH_UNPOSTED_HISTORY 
	LEFT JOIN Z_USER ON Z_USER.PK_USER = S_MISC_BATCH_UNPOSTED_HISTORY.UNPOSTED_BY 
	LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID
	, S_MISC_BATCH_MASTER 
	WHERE 
	S_MISC_BATCH_MASTER.ACTIVE = 1  AND 
	S_MISC_BATCH_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	S_MISC_BATCH_MASTER.PK_MISC_BATCH_MASTER = S_MISC_BATCH_UNPOSTED_HISTORY.PK_MISC_BATCH_MASTER $cond $cond_misc ";
	//exit;
	
	// TUITION BATCH
	 $query_tuition = "select S_TUITION_BATCH_UNPOSTED_HISTORY.PK_TUITION_BATCH_UNPOSTED_HISTORY, CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, BATCH_NO, UNPOSTED_ON , S_TUITION_BATCH_MASTER.TUITION_BATCH_PK_CAMPUS
	FROM  S_TUITION_BATCH_UNPOSTED_HISTORY 
	LEFT JOIN Z_USER ON Z_USER.PK_USER = S_TUITION_BATCH_UNPOSTED_HISTORY.UNPOSTED_BY 
	LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID
	, S_TUITION_BATCH_MASTER
	WHERE 
	S_TUITION_BATCH_MASTER.ACTIVE = 1  AND 
	S_TUITION_BATCH_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	S_TUITION_BATCH_MASTER.PK_TUITION_BATCH_MASTER = S_TUITION_BATCH_UNPOSTED_HISTORY.PK_TUITION_BATCH_MASTER $cond $cond_tuition ";
	//exit;

		$result = array();
		// payment batch
		if(in_array('2',$batch_type))
		{

			$res_payment = $db->Execute($query_payment);
			while (!$res_payment->EOF) {
				$UNPOSTED_ON = convert_to_user_date($res_payment->fields['UNPOSTED_ON'],'m/d/Y h:i A',$res_tz->fields['TIMEZONE'],date_default_timezone_get());
				$result[]=array('PK_BATCH_UNPOSTED_HISTORY'=>$res_payment->fields['PK_PAYMENT_BATCH_UNPOSTED_HISTORY'], 'BATCH_TYPE'=>'Payment','UNPOSTED_ON'=>$UNPOSTED_ON,'BATCH_NO'=>$res_payment->fields['BATCH_NO'],'NAME'=>$res_payment->fields['NAME'],'CAMPUS'=>$res_payment->fields['BATCH_PK_CAMPUS']);
				$res_payment->MoveNext();
			}

		}
		// misc batch
		if(in_array('1',$batch_type))
		{
			$res_misc = $db->Execute($query_misc);
			while (!$res_misc->EOF) {
				$UNPOSTED_ON = convert_to_user_date($res_misc->fields['UNPOSTED_ON'],'m/d/Y h:i A',$res_tz->fields['TIMEZONE'],date_default_timezone_get());
				$result[]=array('PK_BATCH_UNPOSTED_HISTORY'=>$res_misc->fields['PK_MISC_BATCH_UNPOSTED_HISTORY'],'BATCH_TYPE'=>'Miscellaneous','UNPOSTED_ON'=>$UNPOSTED_ON,'BATCH_NO'=>$res_misc->fields['BATCH_NO'],'NAME'=>$res_misc->fields['NAME'],'CAMPUS'=>$res_misc->fields['MISC_BATCH_PK_CAMPUS']);
				$res_misc->MoveNext();
			}
		}
		// misc batch
		if(in_array('3',$batch_type))
		{
			$res_tuition = $db->Execute($query_tuition);
			while (!$res_tuition->EOF) {
				$UNPOSTED_ON = convert_to_user_date($res_tuition->fields['UNPOSTED_ON'],'m/d/Y h:i A',$res_tz->fields['TIMEZONE'],date_default_timezone_get());
				$result[]=array('PK_BATCH_UNPOSTED_HISTORY'=>$res_tuition->fields['PK_TUITION_BATCH_UNPOSTED_HISTORY'],'BATCH_TYPE'=>'Tuition','UNPOSTED_ON'=>$UNPOSTED_ON,'BATCH_NO'=>$res_tuition->fields['BATCH_NO'],'NAME'=>$res_tuition->fields['NAME'],'CAMPUS'=>$res_tuition->fields['TUITION_BATCH_PK_CAMPUS']);
				$res_tuition->MoveNext();
			}
		}

}

function cmp($a, $b) {
    if ($a['UNPOSTED_ON'] == $b['UNPOSTED_ON']) return 0;
    return (strtotime($a['UNPOSTED_ON']) < strtotime($b['UNPOSTED_ON'])) ? 1 : -1;
}

usort($result, "cmp");

?>

<table class="table table-hover" id="student_update_table" >
	<thead>
		<tr>
			<th>
				<input type="checkbox" name="SEARCH_SELECT_ALL" id="SEARCH_SELECT_ALL" value="1" onclick="fun_select_all()" />
			</th>
			<th>Batch Types</th>
			<th>Campus</th>
			<th>Batch Number</th><!-- Ticket # 1371 -->

			<th>Unposted Date and Time</th><!-- Ticket # 1371 -->
			<th>Unposted By</th>
			<th>	
				<?=TOTAL_COUNT.': <span id="TOTAL_COUNT"> '.count($result).'</span>' ?>				
				<? if($_REQUEST['bulk_text'] == 1 || $_REQUEST['show_count'] == 1 || $_REQUEST['page'] == 'letter_gen') { ?>
					<?php } ?>
				<br /><?=SELECTED_COUNT.': ' ?><span id="SELECTED_COUNT"></span>
				
			</th>
		</tr>
	</thead>
	<tbody id="statusDiv"> <!-- // DIAM-757 -->
	<? 
	
	foreach ($result as $key => $value) {
		# code...
	 ?>
		<tr>
			<th>
				<input type="checkbox" name="PK_BATCH_UNPOSTED_HISTORY[]" id="PK_BATCH_UNPOSTED_HISTORY" onclick="get_count()"  value="<?=$value['PK_BATCH_UNPOSTED_HISTORY']?>" />			
				<input type="hidden" name="PK_BATCH_TYPE_<?=$value['PK_BATCH_UNPOSTED_HISTORY']?>" value="<?=$value['BATCH_TYPE']?>" >
			</th>
			<td >
				<?=$value['BATCH_TYPE']?>
			</td>
			<td>
				<?php 
				$CAMPUS = '';
				$B_PK_CAMPUS = $value['CAMPUS'];
				if($B_PK_CAMPUS != '') {
					$rs_camp = mysql_query("SELECT CAMPUS_CODE from S_CAMPUS WHERE PK_CAMPUS IN ($B_PK_CAMPUS) ORDER BY CAMPUS_CODE ASC ");	
					while($row_camp = mysql_fetch_array($rs_camp)){
						if($CAMPUS != '')
							$CAMPUS .= ', ';
						$CAMPUS .= $row_camp['CAMPUS_CODE'];
					}
					echo $CAMPUS;
				}
				?>

			</td>
			<td  > <!-- Ticket # 1371 -->
				<?=$value['BATCH_NO']?>
			</td>
			<!-- Ticket # 1371 -->
			<td >
				<?=$value['UNPOSTED_ON'];
				/* Ticket # 1371  */ ?>
			</td>
			<td  > <!-- Ticket # 1247 -->
				<?=$value['NAME']?>
			</td>
			<!-- Ticket # 1247 -->

		</tr>
	<?	
	} ?>
	<tr class='notfound'>
     <td colspan='4'>No record found</td>
   </tr>
	</tbody>

</table>

