<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/consolidation_tool.php");
require_once("check_access.php");

if(check_access('SETUP_CONSOLIDATION_TOOL') == 0){
	header("location:../index");
	exit;
}
$CONSOLIDATE = trim($_REQUEST['CONSOLIDATE']);
$type 		 = $_REQUEST['type'];
$OLD_VALUE   = $_REQUEST['OLD_VALUE'];

$cond = "";
	
if($CONSOLIDATE != '') {
	$res_con = $db->Execute("SELECT MASTER_TABLE_NAME, TABLE_PK_COLUMN_NAME, DISPLAY_VALUES_AS FROM M_CONSOLIDATE WHERE trim(CONSOLIDATE) = '$CONSOLIDATE' AND ACTIVE =1 AND CONSOLIDATE_MASTER = 1");
	$table 		= $res_con->fields['MASTER_TABLE_NAME'];
	$PK_ID 		= $res_con->fields['TABLE_PK_COLUMN_NAME'];
	$DISP_NAME 	= $res_con->fields['DISPLAY_VALUES_AS'];

	if($type == 1) {
		if(strtolower($CONSOLIDATE) == "event type") {
			$cond .= " AND TYPE = '2' ";
		} else if(strtolower($CONSOLIDATE) == "event status") {
			$cond .= " AND TYPE = '3' ";
		} else if(strtolower($CONSOLIDATE) == "note type") {
			$cond .= " AND TYPE = '1' ";
		} else if(strtolower($CONSOLIDATE) == "note status") {
			$cond .= " AND TYPE = '2' ";
		} 
		
		$HYBRID_TABLE =  array("PK_COURSE_OFFERING_STUDENT_STATUS", "PK_DOCUMENT_TYPE", "PK_DROP_REASON", "PK_EMPLOYEE_NOTE_TYPE", "PK_EVENT_OTHER", "PK_NOTE_STATUS", "PK_NOTE_TYPE", "PK_FUNDING", "PK_GRADE_BOOK_TYPE", "PK_GUARANTOR", "PK_LEAD_CONTACT_SOURCE", "PK_LEAD_SOURCE", "PK_LEAD_SOURCE_GROUP", "PK_PLACEMENT_STATUS", "PK_SESSION", "PK_STUDENT_STATUS", "PK_TASK_STATUS", "PK_TASK_TYPE", "PK_CREDIT_TRANSFER_STATUS");
		if(in_array($PK_ID, $HYBRID_TABLE) ) {
			$PK_ID_MASTER = $PK_ID."_MASTER";
			$cond .= " AND $PK_ID_MASTER = 0 ";
		}
		
	} else if($type == 2) {
		$cond .= " AND $PK_ID != '$OLD_VALUE' ";
		
		$res = $db->Execute("SELECT * FROM  $table WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND $PK_ID = '$OLD_VALUE' ");
		if(strtolower($CONSOLIDATE) == "note type") {
			$cond .= " AND TYPE = '".$res->fields['TYPE']."' AND PK_DEPARTMENT = '".$res->fields['PK_DEPARTMENT']."' ";
		} else if(strtolower($CONSOLIDATE) == "note status") {
			$cond .= " AND TYPE = '".$res->fields['TYPE']."' AND PK_DEPARTMENT = '".$res->fields['PK_DEPARTMENT']."' ";
		}  else if(strtolower($CONSOLIDATE) == "task status") {
			$cond .= " AND  PK_DEPARTMENT = '".$res->fields['PK_DEPARTMENT']."' ";
		} else if(strtolower($CONSOLIDATE) == "task type") {
			$cond .= " AND  PK_DEPARTMENT = '".$res->fields['PK_DEPARTMENT']."' ";
		} else if(strtolower($CONSOLIDATE) == "event other") {
			$cond .= " AND TYPE = '".$res->fields['TYPE']."' AND PK_DEPARTMENT = '".$res->fields['PK_DEPARTMENT']."' ";
		} else if(strtolower($CONSOLIDATE) == "custom field") {
			$cond .= " AND SECTION = '".$res->fields['SECTION']."' AND PK_DEPARTMENT = '".$res->fields['PK_DEPARTMENT']."' AND PK_DATA_TYPES = '".$res->fields['PK_DATA_TYPES']."' AND TAB = '".$res->fields['TAB']."' ";
		} else if(strtolower($CONSOLIDATE) == "user defined field") {
			$cond .= " AND PK_DATA_TYPES = '".$res->fields['PK_DATA_TYPES']."' ";
		} else if(strtolower($CONSOLIDATE) == "event status") {
			$cond .= " AND TYPE = '".$res->fields['TYPE']."' AND PK_DEPARTMENT = '".$res->fields['PK_DEPARTMENT']."' ";
		} else if(strtolower($CONSOLIDATE) == "event type") {
			$cond .= " AND TYPE = '".$res->fields['TYPE']."' AND PK_DEPARTMENT = '".$res->fields['PK_DEPARTMENT']."' ";
		} else if(strtolower($CONSOLIDATE) == "event type") {
			$cond .= " AND TYPE = '".$res->fields['TYPE']."' AND PK_DEPARTMENT = '".$res->fields['PK_DEPARTMENT']."' ";
		} else if(strtolower($CONSOLIDATE) == "event status") {
			$cond .= " AND TYPE = '".$res->fields['TYPE']."' AND PK_DEPARTMENT = '".$res->fields['PK_DEPARTMENT']."' ";
		} else if(strtolower($CONSOLIDATE) == "note type") {
			$cond .= " AND TYPE = '".$res->fields['TYPE']."' AND PK_DEPARTMENT = '".$res->fields['PK_DEPARTMENT']."' ";
		} else if(strtolower($CONSOLIDATE) == "note status") {
			$cond .= " AND TYPE = '".$res->fields['TYPE']."' AND PK_DEPARTMENT = '".$res->fields['PK_DEPARTMENT']."' ";
		} else if(strtolower($CONSOLIDATE) == "ledger code") {
			$cond .= " AND TYPE = '".$res->fields['TYPE']."' ";
		} else if(strtolower($CONSOLIDATE) == "room") {
			$cond .= " AND PK_CAMPUS = '".$res->fields['PK_CAMPUS']."' ";
		}   else if(strtolower($CONSOLIDATE) == "user defined field value") {
			$cond .= " AND PK_USER_DEFINED_FIELDS = '".$res->fields['PK_USER_DEFINED_FIELDS']."' ";
		} else if(strtolower($CONSOLIDATE) == "student status") {
			$cond .= " AND ADMISSIONS = '".$res->fields['ADMISSIONS']."' ";
		}
	}
		
	$query = "SELECT $PK_ID as ID_1, $DISP_NAME as NAME_1 FROM $table WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond ORDER BY $DISP_NAME ASC ";
}
//echo $query."<br />";

$count = 0;
if($type == 1){
	$NAME 	= "OLD_VALUE";
	$ID 	= "OLD_VALUE";
	$label	= OLD_VALUE;
} else if($type == 2) {
	$NAME 	= "NEW_VALUE";
	$ID 	= "NEW_VALUE";
	$label	= NEW_VALUE;
	
	$res_type = $db->Execute("SELECT TABLE_NAME, COLUMN_NAME, DATA_TYPE FROM M_CONSOLIDATE WHERE CONSOLIDATE = '$CONSOLIDATE' AND ACTIVE = 1 ");
	while (!$res_type->EOF) {
		$TABLE_NAME 	= $res_type->fields['TABLE_NAME'];
		$COLUMN_NAME 	= $res_type->fields['COLUMN_NAME'];
		$DATA_TYPE 		= strtolower($res_type->fields['DATA_TYPE']);
		
		$cond11 = " AND $COLUMN_NAME = '$OLD_VALUE' ";
		if($DATA_TYPE == "varchar") {
			$COLUMN_NAME_1 = "CONCAT(',' , $COLUMN_NAME, ',') ";
			$cond11 = " AND $COLUMN_NAME_1 LIKE '%,$OLD_VALUE,%' ";
			
			//echo "SELECT COUNT($COLUMN_NAME) as NO FROM $TABLE_NAME WHERE 1=1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond11 <br /><br />";
		}
		
		$res_type1 = $db->Execute("SELECT COUNT($COLUMN_NAME) as NO FROM $TABLE_NAME WHERE 1=1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond11 ");
		$count += $res_type1->fields['NO'];

		$res_type->MoveNext();
	}
}
?>

<select id="<?=$NAME?>" name="<?=$NAME?>" class="form-control required-entry" <? if($type == 1){ ?> onchange="get_values(2)" <? } ?> >
	<option selected></option>
	<? $res_type = $db->Execute($query);
	while (!$res_type->EOF) { ?>
		<option value="<?=$res_type->fields['ID_1'] ?>" ><?=$res_type->fields['NAME_1']?></option>
	<?	$res_type->MoveNext();
	} ?>
</select>
<span class="bar"></span> 
<label for="<?=$NAME?>"><?=$label ?></label>|||<?=$count?>