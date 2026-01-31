<? require_once("../global/config.php"); 
require_once("../language/common.php");
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}

$ledgend_count  	= $_REQUEST['ledgend_count'];
$det_count  		= $_REQUEST['det_count'];
$DSIS_FIELD_NAME   	= $_REQUEST['DSIS_FIELD_NAME'];

$TEXT_ARR = array();
if($DSIS_FIELD_NAME == "S_STUDENT_MASTER.GENDER") {
	$TEXT_ARR[] = "Male";
	$TEXT_ARR[] = "Female";
} else if($DSIS_FIELD_NAME == "S_STUDENT_FINANCIAL.SELECTED_FOR_VERIFICATION" || $DSIS_FIELD_NAME == "S_STUDENT_FINANCIAL.OVERRIDE" || $DSIS_FIELD_NAME == "S_STUDENT_FINANCIAL.AUTOMATIC_ZERO_EFC" || $DSIS_FIELD_NAME == "S_STUDENT_FINANCIAL.PROFESSIONAL_JUDGEMENT" || $DSIS_FIELD_NAME == "S_STUDENT_FINANCIAL.STUDENT_DEGREE") {
	$TEXT_ARR[] = "Yes";
	$TEXT_ARR[] = "No";
} else {
	if($DSIS_FIELD_NAME == "S_STUDENT_MASTER.PK_CITIZENSHIP") {
		$res_look = $db->Execute("select PK_CITIZENSHIP, CITIZENSHIP from Z_CITIZENSHIP WHERE ACTIVE = 1 ");
		while (!$res_look->EOF) { 
			$TEXT_ARR[] = trim($res_look->fields['CITIZENSHIP']);
			
			$res_look->MoveNext();
		}
	} else if($DSIS_FIELD_NAME == "S_STUDENT_MASTER.PK_MARITAL_STATUS") {
		$res_look = $db->Execute("select PK_MARITAL_STATUS, MARITAL_STATUS from Z_MARITAL_STATUS WHERE ACTIVE = 1 ");
		while (!$res_look->EOF) { 
			$TEXT_ARR[] = trim($res_look->fields['MARITAL_STATUS']);
			
			$res_look->MoveNext();
		}
	} else if($DSIS_FIELD_NAME == "S_STUDENT_FINANCIAL.PK_DEPENDENT_STATUS") {
		$res_look = $db->Execute("select PK_DEPENDENT_STATUS, DESCRIPTION from M_DEPENDENT_STATUS WHERE ACTIVE = 1 ");
		while (!$res_look->EOF) { 
			$TEXT_ARR[] = trim($res_look->fields['DESCRIPTION']);
			
			$res_look->MoveNext();
		}
	} else if($DSIS_FIELD_NAME == "S_STUDENT_FINANCIAL.PK_COA_CATEGORY") {
		$res_look = $db->Execute("select PK_COA_CATEGORY, DESCRIPTION from M_COA_CATEGORY WHERE ACTIVE = 1 ");
		while (!$res_look->EOF) { 
			$TEXT_ARR[] = trim($res_look->fields['DESCRIPTION']);
			
			$res_look->MoveNext();
		}
	} else if($DSIS_FIELD_NAME == "S_STUDENT_FINANCIAL.PK_DEGREE_CERT") {
		$res_look = $db->Execute("select PK_DEGREE_CERT, CODE from M_DEGREE_CERT WHERE ACTIVE = 1 ");
		while (!$res_look->EOF) { 
			$TEXT_ARR[] = trim($res_look->fields['CODE']);
			
			$res_look->MoveNext();
		}
	}
}
foreach($TEXT_ARR as $TEXT){ ?>
<div id="ledgend_table_<?=$ledgend_count?>" >
	<div class="row" >
		<div class="col-md-1">
			<input type="hidden" name="PK_ISIR_SETUP_LEGEND_<?=$ledgend_count?>"  value="" />
			<input type="hidden" name="ledgend_count_<?=$det_count?>[]"  value="<?=$ledgend_count?>" />
			<input type="text" name="LEGEND_<?=$ledgend_count?>" placeholder="" id="LEGEND_<?=$ledgend_count?>"  class="required-entry form-control" value="" />
		</div>
		<div class="col-md-3">
			<input type="text" name="TEXT_<?=$ledgend_count?>" placeholder="" id="TEXT_<?=$ledgend_count?>"  class="required-entry form-control" value="<?=$TEXT?>" />
		</div>
		<div class="col-md-5">
			<a href="javascript:void(0)" onclick="delete_row('<?=$ledgend_count?>','ledgend')" class="btn delete-color btn-circle" ><i class="far fa-trash-alt"></i></a>
		</div> 
	</div>	
</div>
<? $ledgend_count++;
} ?>
|||<?=$ledgend_count?>