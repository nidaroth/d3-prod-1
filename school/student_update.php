<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
require_once("check_access.php");

$ADMISSION_ACCESS 	= check_access('ADMISSION_ACCESS');
$REGISTRAR_ACCESS 	= check_access('REGISTRAR_ACCESS');
$FINANCE_ACCESS 	= check_access('FINANCE_ACCESS');
$ACCOUNTING_ACCESS 	= check_access('ACCOUNTING_ACCESS');
$PLACEMENT_ACCESS 	= check_access('PLACEMENT_ACCESS');

if($_GET['t'] == 1 && $ADMISSION_ACCESS == 0) {
	header("location:../index");
	exit;
} else if($_GET['t'] == 2 && $REGISTRAR_ACCESS 	 == 0) {
	header("location:../index");
	exit;
} else if($_GET['t'] == 3 && $FINANCE_ACCESS == 0) {
	header("location:../index");
	exit;
} else if($_GET['t'] == 5 && $ACCOUNTING_ACCESS == 0) {
	header("location:../index");
	exit;
} else if($_GET['t'] == 6 && $PLACEMENT_ACCESS == 0) {
	header("location:../index");
	exit;
}

function track_field_change($data){
	global $db;
	
	$STUDENT_TRACK_CHANGES['PK_ACCOUNT'] 			= $_SESSION['PK_ACCOUNT'];
	$STUDENT_TRACK_CHANGES['PK_STUDENT_MASTER'] 	= $data['PK_STUDENT_MASTER'];
	$STUDENT_TRACK_CHANGES['PK_STUDENT_ENROLLMENT'] = $data['PK_STUDENT_ENROLLMENT'];
	$STUDENT_TRACK_CHANGES['ID'] 					= $data['ID'];
	$STUDENT_TRACK_CHANGES['FIELD_NAME'] 			= $data['FIELD_NAME'];
	$STUDENT_TRACK_CHANGES['OLD_VALUE'] 			= $data['OLD_VALUE'];
	$STUDENT_TRACK_CHANGES['NEW_VALUE'] 			= $data['NEW_VALUE'];
	$STUDENT_TRACK_CHANGES['CHANGED_BY'] 			= $_SESSION['PK_USER'];
	$STUDENT_TRACK_CHANGES['CHANGED_ON'] 			= date("Y-m-d H:i:s");
	
	db_perform('S_STUDENT_TRACK_CHANGES', $STUDENT_TRACK_CHANGES, 'insert');
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	if($_POST['UPDATE_TYPE'] == 1 || $_POST['UPDATE_TYPE'] == 13){
		//Assign		
		
		$res_tra = $db->Execute("select CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME from S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$_POST[UPDATE_VALUE]' ");
		$NEW_REP = $res_tra->fields['NAME'];
		if($ADMISSION_ACCESS == 3){
			$ids = explode(",",$_GET['id']);
			foreach($ids as $id){
				$id1 = explode("-",$id);
				
				$res_tra = $db->Execute("select CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME from S_EMPLOYEE_MASTER, S_STUDENT_ENROLLMENT  WHERE S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$id1[1]' ");
				$CUR_REP = $res_tra->fields['NAME'];
					
				$db->Execute("UPDATE S_STUDENT_ENROLLMENT SET PK_REPRESENTATIVE = '$_POST[UPDATE_VALUE]' WHERE PK_STUDENT_MASTER = '$id1[0]' AND PK_STUDENT_ENROLLMENT = '$id1[1]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
				
				$track_data['ID'] 			 			= '';
				$track_data['GLOBAL_CHANGE'] 			= 0;
				$track_data['PK_STUDENT_MASTER'] 		= $id1[0];
				$track_data['PK_STUDENT_ENROLLMENT'] 	= $id1[1];
				$track_data['FIELD_NAME']		 		= REPRESENTATIVE;
				$track_data['OLD_VALUE'] 	 			= $CUR_REP;
				$track_data['NEW_VALUE'] 	 			= $NEW_REP;
				track_field_change($track_data);
			}
		}
		
	} else if($_POST['UPDATE_TYPE'] == 2){
		//BULK_ARCHIVE
		if($ADMISSION_ACCESS == 3 || $REGISTRAR_ACCESS == 3){ 
			$ids = explode(",",$_GET['id']);
			foreach($ids as $id){
				$id1 = explode("-",$id);
				$db->Execute("UPDATE S_STUDENT_MASTER SET ARCHIVED = '1' WHERE PK_STUDENT_MASTER = '$id1[0]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
			}
		}
	} else if($_POST['UPDATE_TYPE'] == 3){
		//BULK_UNARCHIVE
		if($ADMISSION_ACCESS == 3 || $REGISTRAR_ACCESS == 3){ 
			$ids = explode(",",$_GET['id']);
			foreach($ids as $id){
				$id1 = explode("-",$id);
				$db->Execute("UPDATE S_STUDENT_MASTER SET ARCHIVED = '0' WHERE PK_STUDENT_MASTER = '$id1[0]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
			}
		}
	} else if($_POST['UPDATE_TYPE'] == 4){
		//BULK_APPROVE
		if($ADMISSION_ACCESS == 2 || $ADMISSION_ACCESS == 3 || $REGISTRAR_ACCESS == 2 || $REGISTRAR_ACCESS == 3 ){ //Ticket # 1928 
			
			$res = $db->Execute("SELECT PK_STUDENT_STATUS, CONCAT(STUDENT_STATUS,' - ',DESCRIPTION) AS NAME FROM M_STUDENT_STATUS WHERE PK_STUDENT_STATUS_MASTER = '13' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
			$NEW_STS = $res->fields['NAME'];
			
			$ids = explode(",",$_GET['id']);
			foreach($ids as $id){
				$id1 = explode("-",$id);
				
				$res_tra = $db->Execute("select CONCAT(STUDENT_STATUS,' - ',DESCRIPTION) AS NAME from M_STUDENT_STATUS, S_STUDENT_ENROLLMENT WHERE M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS AND  PK_STUDENT_ENROLLMENT = '$id1[1]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
				$CUR_STS = $res_tra->fields['NAME'];
				
				$STUDENT_ENROLLMENT['PK_STUDENT_STATUS'] = $res->fields['PK_STUDENT_STATUS'];
				$STUDENT_ENROLLMENT['STATUS_DATE'] 		 = date("Y-m-d");
				db_perform('S_STUDENT_ENROLLMENT', $STUDENT_ENROLLMENT, 'update'," PK_STUDENT_ENROLLMENT = '$id1[1]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
				
				$STUDENT_STATUS_LOG['PK_STUDENT_STATUS'] 		= $STUDENT_ENROLLMENT['PK_STUDENT_STATUS'];
				$STUDENT_STATUS_LOG['PK_STUDENT_MASTER'] 		= $id1[0];
				$STUDENT_STATUS_LOG['PK_STUDENT_ENROLLMENT'] 	= $id1[1];
				$STUDENT_STATUS_LOG['PK_ACCOUNT']  				= $_SESSION['PK_ACCOUNT'];
				$STUDENT_STATUS_LOG['CHANGED_BY']  				= $_SESSION['PK_USER'];
				$STUDENT_STATUS_LOG['CHANGED_ON']  				= date("Y-m-d H:i");
				db_perform('S_STUDENT_STATUS_LOG', $STUDENT_STATUS_LOG, 'insert');
				
				$track_data['ID'] 			 			= '';
				$track_data['PK_STUDENT_MASTER'] 		= $id1[0];
				$track_data['PK_STUDENT_ENROLLMENT'] 	= $id1[1];
				$track_data['GLOBAL_CHANGE'] 			= 0;
				$track_data['FIELD_NAME']	 			= STATUS;
				$track_data['OLD_VALUE'] 	 			= $CUR_STS;
				$track_data['NEW_VALUE'] 	 			= $NEW_STS;
				track_field_change($track_data);
			}
		}
		
	} else if($_POST['UPDATE_TYPE'] == 5){
		//CHANGE_STATUS
		if($ADMISSION_ACCESS == 2 || $ADMISSION_ACCESS == 3){
			$res_tra = $db->Execute("select CONCAT(STUDENT_STATUS,' - ',DESCRIPTION) AS NAME from M_STUDENT_STATUS WHERE PK_STUDENT_STATUS = '$_POST[UPDATE_VALUE]' ");
			$NEW_STS = $res_tra->fields['NAME'];
			
			$ids = explode(",",$_GET['id']);
			foreach($ids as $id){
				$id1 = explode("-",$id);
				
				$res_tra = $db->Execute("select CONCAT(STUDENT_STATUS,' - ',DESCRIPTION) AS NAME from M_STUDENT_STATUS, S_STUDENT_ENROLLMENT WHERE M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS AND  PK_STUDENT_ENROLLMENT = '$id1[1]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
				$CUR_STS = $res_tra->fields['NAME'];
				
				$STUDENT_ENROLLMENT['PK_STUDENT_STATUS'] = $_POST['UPDATE_VALUE'];
				//$STUDENT_ENROLLMENT['STATUS_DATE'] 		 = date("Y-m-d"); Ticket # 1513
				db_perform('S_STUDENT_ENROLLMENT', $STUDENT_ENROLLMENT, 'update'," PK_STUDENT_ENROLLMENT = '$id1[1]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
				
				$STUDENT_STATUS_LOG['PK_STUDENT_STATUS'] 		= $STUDENT_ENROLLMENT['PK_STUDENT_STATUS'];
				$STUDENT_STATUS_LOG['PK_STUDENT_MASTER'] 		= $id1[0];
				$STUDENT_STATUS_LOG['PK_STUDENT_ENROLLMENT'] 	= $id1[1];
				$STUDENT_STATUS_LOG['PK_ACCOUNT']  				= $_SESSION['PK_ACCOUNT'];
				$STUDENT_STATUS_LOG['CHANGED_BY']  				= $_SESSION['PK_USER'];
				$STUDENT_STATUS_LOG['CHANGED_ON']  				= date("Y-m-d H:i");
				db_perform('S_STUDENT_STATUS_LOG', $STUDENT_STATUS_LOG, 'insert');
				
				$track_data['ID'] 			 			= '';
				$track_data['PK_STUDENT_MASTER'] 		= $id1[0];
				$track_data['PK_STUDENT_ENROLLMENT'] 	= $id1[1];
				$track_data['GLOBAL_CHANGE'] 			= 0;
				$track_data['FIELD_NAME']	 			= STATUS;
				$track_data['OLD_VALUE'] 	 			= $CUR_STS;
				$track_data['NEW_VALUE'] 	 			= $NEW_STS;
				track_field_change($track_data);
				
				/* Ticket # 1513 */
				
				$res_tra = $db->Execute("select STATUS_DATE from S_STUDENT_ENROLLMENT WHERE PK_STUDENT_ENROLLMENT = '$id1[1]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
				$CUR_STATUS_DATE = $res_tra->fields['STATUS_DATE'];
				
				if($CUR_STATUS_DATE != '' && $CUR_STATUS_DATE != '0000-00-00')
					$CUR_STATUS_DATE = date("m/d/Y",strtotime($CUR_STATUS_DATE));
				else
					$CUR_STATUS_DATE = "";
				
				$track_data['ID'] 			 			= '';
				$track_data['PK_STUDENT_MASTER'] 		= $id1[0];
				$track_data['PK_STUDENT_ENROLLMENT'] 	= $id1[1];
				$track_data['GLOBAL_CHANGE'] 			= 0;
				$track_data['FIELD_NAME']	 			= STATUS_DATE;
				$track_data['OLD_VALUE'] 	 			= $CUR_STATUS_DATE;
				$track_data['NEW_VALUE'] 	 			= date("m/d/Y");
				track_field_change($track_data);
				
				$STUDENT_ENROLLMENT['STATUS_DATE'] = date("Y-m-d");
				db_perform('S_STUDENT_ENROLLMENT', $STUDENT_ENROLLMENT, 'update'," PK_STUDENT_ENROLLMENT = '$id1[1]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
					
				if($_POST['END_DATE_TYPE'] == 2){
					$res_tra = $db->Execute("select LDA from S_STUDENT_ENROLLMENT WHERE PK_STUDENT_ENROLLMENT = '$id1[1]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
					$CUR_LDA = $res_tra->fields['LDA'];
					
					if($CUR_LDA != '' && $CUR_LDA != '0000-00-00')
						$CUR_LDA = date("m/d/Y",strtotime($CUR_LDA));
					else
						$CUR_LDA = "";
					
					$track_data['ID'] 			 			= '';
					$track_data['PK_STUDENT_MASTER'] 		= $id1[0];
					$track_data['PK_STUDENT_ENROLLMENT'] 	= $id1[1];
					$track_data['GLOBAL_CHANGE'] 			= 0;
					$track_data['FIELD_NAME']	 			= LDA;
					$track_data['OLD_VALUE'] 	 			= $CUR_LDA;
					$track_data['NEW_VALUE'] 	 			= $_POST['LDA'];
					track_field_change($track_data);
							
					$STUDENT_ENROLLMENT['LDA'] = date("Y-m-d",strtotime($_POST['LDA']));
					db_perform('S_STUDENT_ENROLLMENT', $STUDENT_ENROLLMENT, 'update'," PK_STUDENT_ENROLLMENT = '$id1[1]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
				} else if($_POST['END_DATE_TYPE'] == 3){
					$res_tra = $db->Execute("select DROP_DATE from S_STUDENT_ENROLLMENT WHERE PK_STUDENT_ENROLLMENT = '$id1[1]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
					$CUR_DROP_DATE = $res_tra->fields['DROP_DATE'];
					
					if($CUR_DROP_DATE != '' && $CUR_DROP_DATE != '0000-00-00')
						$CUR_DROP_DATE = date("m/d/Y",strtotime($CUR_DROP_DATE));
					else
						$CUR_DROP_DATE = "";
					
					$track_data['ID'] 			 			= '';
					$track_data['PK_STUDENT_MASTER'] 		= $id1[0];
					$track_data['PK_STUDENT_ENROLLMENT'] 	= $id1[1];
					$track_data['GLOBAL_CHANGE'] 			= 0;
					$track_data['FIELD_NAME']	 			= DROP_DATE;
					$track_data['OLD_VALUE'] 	 			= $CUR_DROP_DATE;
					$track_data['NEW_VALUE'] 	 			= $_POST['DROP_DATE'];
					track_field_change($track_data);
							
					$STUDENT_ENROLLMENT['DROP_DATE'] = date("Y-m-d",strtotime($_POST['DROP_DATE']));
					db_perform('S_STUDENT_ENROLLMENT', $STUDENT_ENROLLMENT, 'update'," PK_STUDENT_ENROLLMENT = '$id1[1]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
					
					$res_tra = $db->Execute("select DROP_REASON from M_DROP_REASON, S_STUDENT_ENROLLMENT WHERE S_STUDENT_ENROLLMENT.PK_DROP_REASON = M_DROP_REASON.PK_DROP_REASON AND  PK_STUDENT_ENROLLMENT = '$id1[1]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
					$CUR_DROP_REASON = $res_tra->fields['DROP_REASON'];
					
					$res_tra = $db->Execute("select DROP_REASON from M_DROP_REASON WHERE PK_DROP_REASON = '$_POST[PK_DROP_REASON]' ");
					$NEW_DROP_REASON = $res_tra->fields['DROP_REASON'];
					
					$track_data['ID'] 			 			= '';
					$track_data['PK_STUDENT_MASTER'] 		= $id1[0];
					$track_data['PK_STUDENT_ENROLLMENT'] 	= $id1[1];
					$track_data['GLOBAL_CHANGE'] 			= 0;
					$track_data['FIELD_NAME']	 			= DROP_REASON;
					$track_data['OLD_VALUE'] 	 			= $CUR_DROP_REASON;
					$track_data['NEW_VALUE'] 	 			= $NEW_DROP_REASON;
					track_field_change($track_data);
							
					$STUDENT_ENROLLMENT['PK_DROP_REASON'] = $_POST['PK_DROP_REASON'];
					db_perform('S_STUDENT_ENROLLMENT', $STUDENT_ENROLLMENT, 'update'," PK_STUDENT_ENROLLMENT = '$id1[1]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
				} else if($_POST['END_DATE_TYPE'] == 4){
					$res_tra = $db->Execute("select GRADE_DATE from S_STUDENT_ENROLLMENT WHERE PK_STUDENT_ENROLLMENT = '$id1[1]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
					$CUR_GRADE_DATE = $res_tra->fields['GRADE_DATE'];
					
					if($CUR_GRADE_DATE != '' && $CUR_GRADE_DATE != '0000-00-00')
						$CUR_GRADE_DATE = date("m/d/Y",strtotime($CUR_GRADE_DATE));
					else
						$CUR_GRADE_DATE = "";
					
					$track_data['ID'] 			 			= '';
					$track_data['PK_STUDENT_MASTER'] 		= $id1[0];
					$track_data['PK_STUDENT_ENROLLMENT'] 	= $id1[1];
					$track_data['GLOBAL_CHANGE'] 			= 0;
					$track_data['FIELD_NAME']	 			= GRADE_DATE;
					$track_data['OLD_VALUE'] 	 			= $CUR_GRADE_DATE;
					$track_data['NEW_VALUE'] 	 			= $_POST['GRAD_DATE'];
					track_field_change($track_data);
							
					$STUDENT_ENROLLMENT['GRADE_DATE'] = date("Y-m-d",strtotime($_POST['GRAD_DATE']));
					db_perform('S_STUDENT_ENROLLMENT', $STUDENT_ENROLLMENT, 'update'," PK_STUDENT_ENROLLMENT = '$id1[1]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
				} else if($_POST['END_DATE_TYPE'] == 5){
					$res_tra = $db->Execute("select DETERMINATION_DATE from S_STUDENT_ENROLLMENT WHERE PK_STUDENT_ENROLLMENT = '$id1[1]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
					$CUR_DETERMINATION_DATE = $res_tra->fields['DETERMINATION_DATE'];
					
					if($CUR_DETERMINATION_DATE != '' && $CUR_DETERMINATION_DATE != '0000-00-00')
						$CUR_DETERMINATION_DATE = date("m/d/Y",strtotime($CUR_DETERMINATION_DATE));
					else
						$CUR_DETERMINATION_DATE = "";
					
					$track_data['ID'] 			 			= '';
					$track_data['PK_STUDENT_MASTER'] 		= $id1[0];
					$track_data['PK_STUDENT_ENROLLMENT'] 	= $id1[1];
					$track_data['GLOBAL_CHANGE'] 			= 0;
					$track_data['FIELD_NAME']	 			= DETERMINATION_DATE;
					$track_data['OLD_VALUE'] 	 			= $CUR_DETERMINATION_DATE;
					$track_data['NEW_VALUE'] 	 			= $_POST['DETERMINATION_DATE'];
					track_field_change($track_data);
							
					$STUDENT_ENROLLMENT['DETERMINATION_DATE'] = date("Y-m-d",strtotime($_POST['DETERMINATION_DATE']));
					db_perform('S_STUDENT_ENROLLMENT', $STUDENT_ENROLLMENT, 'update'," PK_STUDENT_ENROLLMENT = '$id1[1]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
				}
			}
			/* Ticket # 1513 */
		}
	} else if($_POST['UPDATE_TYPE'] == 6){
		//Student Group
		$ids = explode(",",$_GET['id']);
		
		$res_tra = $db->Execute("select STUDENT_GROUP from M_STUDENT_GROUP WHERE PK_STUDENT_GROUP = '$_POST[UPDATE_VALUE]' ");
		$NEW_STUD_GROUP = $res_tra->fields['STUDENT_GROUP'];
		foreach($ids as $id){
			$id1 = explode("-",$id);
			
			$res_tra = $db->Execute("select STUDENT_GROUP from M_STUDENT_GROUP, S_STUDENT_ENROLLMENT WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP = M_STUDENT_GROUP.PK_STUDENT_GROUP AND  PK_STUDENT_ENROLLMENT = '$id1[1]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$CUR_STUD_GROUP = $res_tra->fields['STUDENT_GROUP'];
			
			$track_data['ID'] 			 			= '';
			$track_data['PK_STUDENT_MASTER'] 		= $id1[0];
			$track_data['PK_STUDENT_ENROLLMENT'] 	= $id1[1];
			$track_data['GLOBAL_CHANGE'] 			= 0;
			$track_data['FIELD_NAME']	 			= STUDENT_GROUP;
			$track_data['OLD_VALUE'] 	 			= $CUR_STUD_GROUP;
			$track_data['NEW_VALUE'] 	 			= $NEW_STUD_GROUP;
			track_field_change($track_data);
					
			$STUDENT_ENROLLMENT['PK_STUDENT_GROUP'] = $_POST['UPDATE_VALUE'];
			db_perform('S_STUDENT_ENROLLMENT', $STUDENT_ENROLLMENT, 'update'," PK_STUDENT_ENROLLMENT = '$id1[1]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
		}
	} else if($_POST['UPDATE_TYPE'] == 7){
		//change campus
		$ids = explode(",",$_GET['id']);
		
		$res_tra = $db->Execute("select OFFICIAL_CAMPUS_NAME from S_CAMPUS WHERE PK_CAMPUS = '$_POST[UPDATE_VALUE]' ");
		$NEW_CAMPUS = $res_tra->fields['OFFICIAL_CAMPUS_NAME'];
		foreach($ids as $id){
			$id1 = explode("-",$id);
			
			$res_tra = $db->Execute("select OFFICIAL_CAMPUS_NAME FROM S_STUDENT_CAMPUS,S_CAMPUS WHERE S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS AND S_STUDENT_CAMPUS.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND  PK_STUDENT_ENROLLMENT = '$id1[1]' ORDER BY OFFICIAL_CAMPUS_NAME ASC");
			$CUR_CAMP = $res_tra->fields['OFFICIAL_CAMPUS_NAME'];
				
			if($_POST['UPDATE_VALUE'] > 0) {
				$res = $db->Execute("SELECT PK_STUDENT_CAMPUS FROM S_STUDENT_CAMPUS WHERE PK_STUDENT_ENROLLMENT = '$id1[1]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
				$STUDENT_CAMPUS['PK_CAMPUS']  = $_POST['UPDATE_VALUE'];
				if($res->RecordCount() == 0) {
					$STUDENT_CAMPUS['PK_STUDENT_MASTER'] 		= $id1[0];
					$STUDENT_CAMPUS['PK_STUDENT_ENROLLMENT'] 	= $id1[1];
					$STUDENT_CAMPUS['PK_ACCOUNT'] 				= $_SESSION['PK_ACCOUNT'];
					$STUDENT_CAMPUS['CREATED_BY']  				= $_SESSION['PK_USER'];
					$STUDENT_CAMPUS['CREATED_ON']  				= date("Y-m-d H:i");
					db_perform('S_STUDENT_CAMPUS', $STUDENT_CAMPUS, 'insert');
				} else {
					$PK_STUDENT_CAMPUS = $res->fields['PK_STUDENT_CAMPUS'];
					db_perform('S_STUDENT_CAMPUS', $STUDENT_CAMPUS, 'update'," PK_STUDENT_CAMPUS = '$PK_STUDENT_CAMPUS' ");
				}
			} else {
				$db->Execute("DELETE FROM S_STUDENT_CAMPUS WHERE PK_STUDENT_ENROLLMENT = '$id1[1]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
			}
			
			$track_data['ID'] 			 			= '';
			$track_data['PK_STUDENT_MASTER'] 		= $id1[0];
			$track_data['PK_STUDENT_ENROLLMENT'] 	= $id1[1];
			$track_data['GLOBAL_CHANGE'] 			= 0;
			$track_data['FIELD_NAME']	 			= CAMPUS;
			$track_data['OLD_VALUE'] 	 			= $CUR_CAMP;
			$track_data['NEW_VALUE'] 	 			= $NEW_CAMPUS;
			track_field_change($track_data);
		}
	} else if($_POST['UPDATE_TYPE'] == 8){
		//CHANGE_DISTANCE_LEARNING
		$ids = explode(",",$_GET['id']);
		
		$res_tra = $db->Execute("select DISTANCE_LEARNING from M_DISTANCE_LEARNING WHERE PK_DISTANCE_LEARNING = '$_POST[UPDATE_VALUE]' ");
		$NEW_DISTANCE_LEARNING = $res_tra->fields['DISTANCE_LEARNING'];
		foreach($ids as $id){
			$id1 = explode("-",$id);
			
			$res_tra = $db->Execute("select DISTANCE_LEARNING from M_DISTANCE_LEARNING, S_STUDENT_ENROLLMENT WHERE S_STUDENT_ENROLLMENT.PK_DISTANCE_LEARNING = M_DISTANCE_LEARNING.PK_DISTANCE_LEARNING AND  PK_STUDENT_ENROLLMENT = '$id1[1]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$CUR_DISTANCE_LEARNING = $res_tra->fields['DISTANCE_LEARNING'];
			
			$track_data['ID'] 			 			= '';
			$track_data['PK_STUDENT_MASTER'] 		= $id1[0];
			$track_data['PK_STUDENT_ENROLLMENT'] 	= $id1[1];
			$track_data['GLOBAL_CHANGE'] 			= 0;
			$track_data['FIELD_NAME']	 			= DISTANCE_LEARNING;
			$track_data['OLD_VALUE'] 	 			= $CUR_DISTANCE_LEARNING;
			$track_data['NEW_VALUE'] 	 			= $NEW_DISTANCE_LEARNING;
			track_field_change($track_data);
					
			$STUDENT_ENROLLMENT['PK_DISTANCE_LEARNING'] = $_POST['UPDATE_VALUE'];
			db_perform('S_STUDENT_ENROLLMENT', $STUDENT_ENROLLMENT, 'update'," PK_STUDENT_ENROLLMENT = '$id1[1]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
		}
	} else if($_POST['UPDATE_TYPE'] == 9){
		//CHANGE_EXPECTED_GRAD_DATE
		
		$ids = explode(",",$_GET['id']);

		foreach($ids as $id){
			$id1 = explode("-",$id);
			
			$res_tra = $db->Execute("select EXPECTED_GRAD_DATE from S_STUDENT_ENROLLMENT WHERE PK_STUDENT_ENROLLMENT = '$id1[1]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$CUR_EXPECTED_GRAD_DATE = $res_tra->fields['EXPECTED_GRAD_DATE'];
			
			if($CUR_EXPECTED_GRAD_DATE != '' && $CUR_EXPECTED_GRAD_DATE != '0000-00-00')
				$CUR_EXPECTED_GRAD_DATE = date("m/d/Y",strtotime($CUR_EXPECTED_GRAD_DATE));
			else
				$CUR_EXPECTED_GRAD_DATE = "";
			
			$track_data['ID'] 			 			= '';
			$track_data['PK_STUDENT_MASTER'] 		= $id1[0];
			$track_data['PK_STUDENT_ENROLLMENT'] 	= $id1[1];
			$track_data['GLOBAL_CHANGE'] 			= 0;
			$track_data['FIELD_NAME']	 			= EXPECTED_GRAD_DATE;
			$track_data['OLD_VALUE'] 	 			= $CUR_EXPECTED_GRAD_DATE;
			$track_data['NEW_VALUE'] 	 			= $_POST['UPDATE_VALUE'];
			track_field_change($track_data);
					
			$STUDENT_ENROLLMENT['EXPECTED_GRAD_DATE'] = date("Y-m-d",strtotime($_POST['UPDATE_VALUE']));
			db_perform('S_STUDENT_ENROLLMENT', $STUDENT_ENROLLMENT, 'update'," PK_STUDENT_ENROLLMENT = '$id1[1]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
		}
		
	} else if($_POST['UPDATE_TYPE'] == 10){
		//CHANGE_FULL_PART_TIME
		
		$ids = explode(",",$_GET['id']);
		
		$res_tra = $db->Execute("select CONCAT(CODE,' - ',DESCRIPTION) AS NAME from M_ENROLLMENT_STATUS WHERE PK_ENROLLMENT_STATUS = '$_POST[UPDATE_VALUE]' ");
		$NEW_ENROLLMENT_STATUS = $res_tra->fields['NAME'];
		foreach($ids as $id){
			$id1 = explode("-",$id);
			
			$res_tra = $db->Execute("select CONCAT(CODE,' - ',DESCRIPTION) AS NAME from M_ENROLLMENT_STATUS, S_STUDENT_ENROLLMENT WHERE S_STUDENT_ENROLLMENT.PK_ENROLLMENT_STATUS = M_ENROLLMENT_STATUS.PK_ENROLLMENT_STATUS AND  PK_STUDENT_ENROLLMENT = '$id1[1]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$CUR_ENROLLMENT_STATUS = $res_tra->fields['NAME'];
			
			$track_data['ID'] 			 			= '';
			$track_data['PK_STUDENT_MASTER'] 		= $id1[0];
			$track_data['PK_STUDENT_ENROLLMENT'] 	= $id1[1];
			$track_data['GLOBAL_CHANGE'] 			= 0;
			$track_data['FIELD_NAME']	 			= FULL_PART_TIME;
			$track_data['OLD_VALUE'] 	 			= $CUR_ENROLLMENT_STATUS;
			$track_data['NEW_VALUE'] 	 			= $NEW_ENROLLMENT_STATUS;
			track_field_change($track_data);
					
			$STUDENT_ENROLLMENT['PK_ENROLLMENT_STATUS'] = $_POST['UPDATE_VALUE'];
			db_perform('S_STUDENT_ENROLLMENT', $STUDENT_ENROLLMENT, 'update'," PK_STUDENT_ENROLLMENT = '$id1[1]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
		}
		
	} else if($_POST['UPDATE_TYPE'] == 11){
		//CHANGE_MID_POINT_DATE
		
		$ids = explode(",",$_GET['id']);

		foreach($ids as $id){
			$id1 = explode("-",$id);
			
			$res_tra = $db->Execute("select MIDPOINT_DATE from S_STUDENT_ENROLLMENT WHERE PK_STUDENT_ENROLLMENT = '$id1[1]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$CUR_MIDPOINT_DATE = $res_tra->fields['MIDPOINT_DATE'];
			
			if($CUR_MIDPOINT_DATE != '' && $CUR_MIDPOINT_DATE != '0000-00-00')
				$CUR_MIDPOINT_DATE = date("m/d/Y",strtotime($CUR_MIDPOINT_DATE));
			else
				$CUR_MIDPOINT_DATE = "";
			
			$track_data['ID'] 			 			= '';
			$track_data['PK_STUDENT_MASTER'] 		= $id1[0];
			$track_data['PK_STUDENT_ENROLLMENT'] 	= $id1[1];
			$track_data['GLOBAL_CHANGE'] 			= 0;
			$track_data['FIELD_NAME']	 			= MIDPOINT_DATE;
			$track_data['OLD_VALUE'] 	 			= $CUR_MIDPOINT_DATE;
			$track_data['NEW_VALUE'] 	 			= $_POST['UPDATE_VALUE'];
			track_field_change($track_data);
					
			$STUDENT_ENROLLMENT['MIDPOINT_DATE'] = date("Y-m-d",strtotime($_POST['UPDATE_VALUE']));
			db_perform('S_STUDENT_ENROLLMENT', $STUDENT_ENROLLMENT, 'update'," PK_STUDENT_ENROLLMENT = '$id1[1]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
		}
		
	} else if($_POST['UPDATE_TYPE'] == 12){
		//CHANGE_SESSION
	
		$ids = explode(",",$_GET['id']);
		
		$res_tra = $db->Execute("select SESSION from M_SESSION WHERE PK_SESSION = '$_POST[UPDATE_VALUE]' ");
		$NEW_SESSION = $res_tra->fields['SESSION'];
		foreach($ids as $id){
			$id1 = explode("-",$id);
			
			$res_tra = $db->Execute("select SESSION from M_SESSION, S_STUDENT_ENROLLMENT WHERE S_STUDENT_ENROLLMENT.PK_SESSION = M_SESSION.PK_SESSION AND  PK_STUDENT_ENROLLMENT = '$id1[1]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$CUR_SESSION = $res_tra->fields['SESSION'];
			
			$track_data['ID'] 			 			= '';
			$track_data['PK_STUDENT_MASTER'] 		= $id1[0];
			$track_data['PK_STUDENT_ENROLLMENT'] 	= $id1[1];
			$track_data['GLOBAL_CHANGE'] 			= 0;
			$track_data['FIELD_NAME']	 			= SESSION;
			$track_data['OLD_VALUE'] 	 			= $CUR_SESSION;
			$track_data['NEW_VALUE'] 	 			= $NEW_SESSION;
			track_field_change($track_data);
					
			$STUDENT_ENROLLMENT['PK_SESSION'] = $_POST['UPDATE_VALUE'];
			db_perform('S_STUDENT_ENROLLMENT', $STUDENT_ENROLLMENT, 'update'," PK_STUDENT_ENROLLMENT = '$id1[1]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
		}
	} else if($_POST['UPDATE_TYPE'] == 14){
		//CHANGE_FIRST_TERM
		$ids = explode(",",$_GET['id']);

		$res_tra = $db->Execute("select IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_TERM_MASTER WHERE PK_TERM_MASTER = '$_POST[UPDATE_VALUE]' ");
		$NEW_BEGIN_DATE_1 = $res_tra->fields['BEGIN_DATE_1'];
		foreach($ids as $id){
			$id1 = explode("-",$id);
			
			$res_tra = $db->Execute("select IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_TERM_MASTER, S_STUDENT_ENROLLMENT WHERE S_STUDENT_ENROLLMENT.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER AND  PK_STUDENT_ENROLLMENT = '$id1[1]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$CUR_BEGIN_DATE_1 = $res_tra->fields['BEGIN_DATE_1'];
			
			$track_data['ID'] 			 			= '';
			$track_data['PK_STUDENT_MASTER'] 		= $id1[0];
			$track_data['PK_STUDENT_ENROLLMENT'] 	= $id1[1];
			$track_data['GLOBAL_CHANGE'] 			= 0;
			$track_data['FIELD_NAME']	 			= FIRST_TERM_DATE;
			$track_data['OLD_VALUE'] 	 			= $CUR_BEGIN_DATE_1;
			$track_data['NEW_VALUE'] 	 			= $NEW_BEGIN_DATE_1;
			track_field_change($track_data);
					
			$STUDENT_ENROLLMENT['PK_TERM_MASTER'] = $_POST['UPDATE_VALUE'];
			db_perform('S_STUDENT_ENROLLMENT', $STUDENT_ENROLLMENT, 'update'," PK_STUDENT_ENROLLMENT = '$id1[1]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
		}
	} else if($_POST['UPDATE_TYPE'] == 15){
		//CHANGE_PROGRAM
		$ids = explode(",",$_GET['id']);

		$res_tra = $db->Execute("select CONCAT(CODE,' - ',DESCRIPTION) AS NAME from M_CAMPUS_PROGRAM WHERE PK_CAMPUS_PROGRAM = '$_POST[UPDATE_VALUE]' ");
		$NEW_PROGRAM = $res_tra->fields['NAME'];
		foreach($ids as $id){
			$id1 = explode("-",$id);
			
			$res_tra = $db->Execute("select CONCAT(CODE,' - ',DESCRIPTION) AS NAME, S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM from M_CAMPUS_PROGRAM, S_STUDENT_ENROLLMENT WHERE S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM = M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM AND  PK_STUDENT_ENROLLMENT = '$id1[1]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$CUR_PROGRAM 			= $res_tra->fields['NAME'];
			$OLD_PK_CAMPUS_PROGRAM 	= $res_tra->fields['PK_CAMPUS_PROGRAM'];
			
			$track_data['ID'] 			 			= '';
			$track_data['PK_STUDENT_MASTER'] 		= $id1[0];
			$track_data['PK_STUDENT_ENROLLMENT'] 	= $id1[1];
			$track_data['GLOBAL_CHANGE'] 			= 0;
			$track_data['FIELD_NAME']	 			= PROGRAM;
			$track_data['OLD_VALUE'] 	 			= $CUR_PROGRAM;
			$track_data['NEW_VALUE'] 	 			= $NEW_PROGRAM;
			track_field_change($track_data);
					
			$STUDENT_ENROLLMENT['PK_CAMPUS_PROGRAM'] = $_POST['UPDATE_VALUE'];
			db_perform('S_STUDENT_ENROLLMENT', $STUDENT_ENROLLMENT, 'update'," PK_STUDENT_ENROLLMENT = '$id1[1]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
			
			//check on student.php
			
			$PK_STUDENT_MASTER 	= $id1[0];
			$EID 				= $id1[1];
			if($OLD_PK_CAMPUS_PROGRAM != $STUDENT_ENROLLMENT['PK_CAMPUS_PROGRAM']) {
				//check on student_update.php
				$db->Execute("DELETE from S_STUDENT_REQUIREMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_STUDENT_ENROLLMENT = '$EID' AND TYPE = 2 ");
				
				$res_req = $db->Execute("select * from M_CAMPUS_PROGRAM_REQUIREMENT WHERE PK_ACCOUNT='$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS_PROGRAM = '$STUDENT_ENROLLMENT[PK_CAMPUS_PROGRAM]' AND ACTIVE = 1");
				while (!$res_req->EOF) {
				
					$STUDENT_REQUIREMENT1['PK_STUDENT_MASTER'] 			= $PK_STUDENT_MASTER;
					$STUDENT_REQUIREMENT1['PK_STUDENT_ENROLLMENT'] 		= $EID;
					$STUDENT_REQUIREMENT1['TYPE'] 				  		= 2;
					$STUDENT_REQUIREMENT1['ID'] 				  		= $res_req->fields['PK_CAMPUS_PROGRAM_REQUIREMENT'];
					$STUDENT_REQUIREMENT1['PK_REQUIREMENT_CATEGORY'] 	= $res_req->fields['PK_REQUIREMENT_CATEGORY'];
					$STUDENT_REQUIREMENT1['REQUIREMENT'] 				= $res_req->fields['REQUIREMENT'];
					$STUDENT_REQUIREMENT1['MANDATORY'] 					= $res_req->fields['MANDATORY'];
					$STUDENT_REQUIREMENT1['PK_ACCOUNT']  				= $_SESSION['PK_ACCOUNT'];
					$STUDENT_REQUIREMENT1['CREATED_BY']  				= $_SESSION['PK_USER'];
					$STUDENT_REQUIREMENT1['CREATED_ON']  				= date("Y-m-d H:i");
					db_perform('S_STUDENT_REQUIREMENT', $STUDENT_REQUIREMENT1, 'insert');
				
					$res_req->MoveNext();
				}
				
				$res_req = $db->Execute("select USE_PROGRAM_GRADE from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS_PROGRAM = '$STUDENT_ENROLLMENT[PK_CAMPUS_PROGRAM]' ");
				if($res_req ->fields['USE_PROGRAM_GRADE'] == 1) {
					$res_req = $db->Execute("select * from S_PROGRAM_GRADE_BOOK WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS_PROGRAM = '$STUDENT_ENROLLMENT[PK_CAMPUS_PROGRAM]' AND ACTIVE = 1 ");
					while (!$res_req->EOF) {
					
						$STUDENT_PROGRAM_GRADE_BOOK_INPUT['PK_STUDENT_MASTER'] 		= $PK_STUDENT_MASTER;
						$STUDENT_PROGRAM_GRADE_BOOK_INPUT['PK_STUDENT_ENROLLMENT'] 	= $EID;
						$STUDENT_PROGRAM_GRADE_BOOK_INPUT['PK_CAMPUS_PROGRAM'] 		= $STUDENT_ENROLLMENT['PK_CAMPUS_PROGRAM'];
						$STUDENT_PROGRAM_GRADE_BOOK_INPUT['PK_PROGRAM_GRADE_BOOK'] 	= $res_req->fields['PK_PROGRAM_GRADE_BOOK'];
						$STUDENT_PROGRAM_GRADE_BOOK_INPUT['PK_GRADE_BOOK_CODE'] 	= $res_req->fields['PK_GRADE_BOOK_CODE'];
						$STUDENT_PROGRAM_GRADE_BOOK_INPUT['DESCRIPTION'] 			= $res_req->fields['DESCRIPTION'];
						$STUDENT_PROGRAM_GRADE_BOOK_INPUT['PK_GRADE_BOOK_TYPE'] 	= $res_req->fields['PK_GRADE_BOOK_TYPE'];
						$STUDENT_PROGRAM_GRADE_BOOK_INPUT['SESSION_REQUIRED'] 		= $res_req->fields['SESSION'];
						$STUDENT_PROGRAM_GRADE_BOOK_INPUT['HOUR_REQUIRED'] 			= $res_req->fields['HOUR'];
						$STUDENT_PROGRAM_GRADE_BOOK_INPUT['POINTS_REQUIRED'] 		= $res_req->fields['POINTS'];
						$STUDENT_PROGRAM_GRADE_BOOK_INPUT['PK_ACCOUNT']  			= $_SESSION['PK_ACCOUNT'];
						$STUDENT_PROGRAM_GRADE_BOOK_INPUT['CREATED_BY']  			= $_SESSION['PK_USER'];
						$STUDENT_PROGRAM_GRADE_BOOK_INPUT['CREATED_ON']  			= date("Y-m-d H:i");
						db_perform('S_STUDENT_PROGRAM_GRADE_BOOK_INPUT', $STUDENT_PROGRAM_GRADE_BOOK_INPUT, 'insert');

						$res_req->MoveNext();
					}
				}
			}
		}
	} else if($_POST['UPDATE_TYPE'] == 16){
		//CHANGE_FUNDING
		$ids = explode(",",$_GET['id']);

		$res_tra = $db->Execute("select CONCAT(FUNDING,' - ',DESCRIPTION) AS NAME from M_FUNDING WHERE PK_FUNDING = '$_POST[UPDATE_VALUE]' ");
		$NEW_FUNDING = $res_tra->fields['NAME'];
		foreach($ids as $id){
			$id1 = explode("-",$id);
			
			$res_tra = $db->Execute("select CONCAT(FUNDING,' - ',DESCRIPTION) AS NAME from M_FUNDING, S_STUDENT_ENROLLMENT WHERE S_STUDENT_ENROLLMENT.PK_FUNDING = M_FUNDING.PK_FUNDING AND  PK_STUDENT_ENROLLMENT = '$id1[1]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$CUR_FUNDING = $res_tra->fields['NAME'];
			
			$track_data['ID'] 			 			= '';
			$track_data['PK_STUDENT_MASTER'] 		= $id1[0];
			$track_data['PK_STUDENT_ENROLLMENT'] 	= $id1[1];
			$track_data['GLOBAL_CHANGE'] 			= 0;
			$track_data['FIELD_NAME']	 			= FUNDING;
			$track_data['OLD_VALUE'] 	 			= $CUR_FUNDING;
			$track_data['NEW_VALUE'] 	 			= $NEW_FUNDING;
			track_field_change($track_data);
					
			$STUDENT_ENROLLMENT['PK_FUNDING'] = $_POST['UPDATE_VALUE'];
			db_perform('S_STUDENT_ENROLLMENT', $STUDENT_ENROLLMENT, 'update'," PK_STUDENT_ENROLLMENT = '$id1[1]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
		}
	} else if($_POST['UPDATE_TYPE'] == 17){
		//CHANGE_1098T_REPORTING
		$ids = explode(",",$_GET['id']);

		$res_tra = $db->Execute("select REPORTING_TYPE from Z_1098T_REPORTING_TYPE WHERE PK_1098T_REPORTING_TYPE = '$_POST[UPDATE_VALUE]' ");
		$NEW_REPORTING_TYPE = $res_tra->fields['REPORTING_TYPE'];
		foreach($ids as $id){
			$id1 = explode("-",$id);
			
			$res_tra = $db->Execute("select REPORTING_TYPE from Z_1098T_REPORTING_TYPE, S_STUDENT_ENROLLMENT WHERE S_STUDENT_ENROLLMENT.PK_1098T_REPORTING_TYPE = Z_1098T_REPORTING_TYPE.PK_1098T_REPORTING_TYPE AND  PK_STUDENT_ENROLLMENT = '$id1[1]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$CUR_REPORTING_TYPE = $res_tra->fields['REPORTING_TYPE'];
			
			$track_data['ID'] 			 			= '';
			$track_data['PK_STUDENT_MASTER'] 		= $id1[0];
			$track_data['PK_STUDENT_ENROLLMENT'] 	= $id1[1];
			$track_data['GLOBAL_CHANGE'] 			= 0;
			$track_data['FIELD_NAME']	 			= _1098T_REPORTING_TYPE;
			$track_data['OLD_VALUE'] 	 			= $CUR_REPORTING_TYPE;
			$track_data['NEW_VALUE'] 	 			= $NEW_REPORTING_TYPE;
			track_field_change($track_data);
					
			$STUDENT_ENROLLMENT['PK_1098T_REPORTING_TYPE'] = $_POST['UPDATE_VALUE'];
			db_perform('S_STUDENT_ENROLLMENT', $STUDENT_ENROLLMENT, 'update'," PK_STUDENT_ENROLLMENT = '$id1[1]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
		}
	} else if($_POST['UPDATE_TYPE'] == 18){
		//CHANGE_STRF_PAID_DATE
		
		$ids = explode(",",$_GET['id']);

		foreach($ids as $id){
			$id1 = explode("-",$id);
			
			$res_tra = $db->Execute("select STRF_PAID_DATE from S_STUDENT_ENROLLMENT WHERE PK_STUDENT_ENROLLMENT = '$id1[1]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$CUR_STRF_PAID_DATE = $res_tra->fields['STRF_PAID_DATE'];
			
			if($CUR_STRF_PAID_DATE != '' && $CUR_STRF_PAID_DATE != '0000-00-00')
				$CUR_STRF_PAID_DATE = date("m/d/Y",strtotime($CUR_STRF_PAID_DATE));
			else
				$CUR_STRF_PAID_DATE = "";
			
			$track_data['ID'] 			 			= '';
			$track_data['PK_STUDENT_MASTER'] 		= $id1[0];
			$track_data['PK_STUDENT_ENROLLMENT'] 	= $id1[1];
			$track_data['GLOBAL_CHANGE'] 			= 0;
			$track_data['FIELD_NAME']	 			= STRF_PAID_DATE;
			$track_data['OLD_VALUE'] 	 			= $CUR_STRF_PAID_DATE;
			$track_data['NEW_VALUE'] 	 			= $_POST['UPDATE_VALUE'];
			track_field_change($track_data);
					
			$STUDENT_ENROLLMENT['STRF_PAID_DATE'] = date("Y-m-d",strtotime($_POST['UPDATE_VALUE']));
			db_perform('S_STUDENT_ENROLLMENT', $STUDENT_ENROLLMENT, 'update'," PK_STUDENT_ENROLLMENT = '$id1[1]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
		}
	} else if($_POST['UPDATE_TYPE'] == 19){
		//CHANGE_SPECIAL
		$ids = explode(",",$_GET['id']);

		$res_tra = $db->Execute("select SPECIAL from Z_SPECIAL WHERE PK_SPECIAL = '$_POST[UPDATE_VALUE]' ");
		$NEW_SPECIAL = $res_tra->fields['SPECIAL'];
		foreach($ids as $id){
			$id1 = explode("-",$id);
			
			$res_tra = $db->Execute("select SPECIAL from Z_SPECIAL, S_STUDENT_ENROLLMENT WHERE S_STUDENT_ENROLLMENT.PK_SPECIAL = Z_SPECIAL.PK_SPECIAL AND  PK_STUDENT_ENROLLMENT = '$id1[1]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$CUR_SPECIAL = $res_tra->fields['SPECIAL'];
			
			$track_data['ID'] 			 			= '';
			$track_data['PK_STUDENT_MASTER'] 		= $id1[0];
			$track_data['PK_STUDENT_ENROLLMENT'] 	= $id1[1];
			$track_data['GLOBAL_CHANGE'] 			= 0;
			$track_data['FIELD_NAME']	 			= SPECIAL;
			$track_data['OLD_VALUE'] 	 			= $CUR_SPECIAL;
			$track_data['NEW_VALUE'] 	 			= $NEW_SPECIAL;
			track_field_change($track_data);
					
			$STUDENT_ENROLLMENT['PK_SPECIAL'] = $_POST['UPDATE_VALUE'];
			db_perform('S_STUDENT_ENROLLMENT', $STUDENT_ENROLLMENT, 'update'," PK_STUDENT_ENROLLMENT = '$id1[1]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
		}
	} else if($_POST['UPDATE_TYPE'] == 20){
		//CHANGE_PLACEMENT_STATUS
		$ids = explode(",",$_GET['id']);

		$res_tra = $db->Execute("select PLACEMENT_STATUS from M_PLACEMENT_STATUS WHERE PK_PLACEMENT_STATUS = '$_POST[UPDATE_VALUE]' ");
		$NEW_PLACEMENT_STATUS = $res_tra->fields['PLACEMENT_STATUS'];
		foreach($ids as $id){
			$id1 = explode("-",$id);
			
			$res_tra = $db->Execute("select PLACEMENT_STATUS from M_PLACEMENT_STATUS, S_STUDENT_ENROLLMENT WHERE S_STUDENT_ENROLLMENT.PK_PLACEMENT_STATUS = M_PLACEMENT_STATUS.PK_PLACEMENT_STATUS AND  PK_STUDENT_ENROLLMENT = '$id1[1]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$CUR_PLACEMENT_STATUS = $res_tra->fields['PLACEMENT_STATUS'];
			
			$track_data['ID'] 			 			= '';
			$track_data['PK_STUDENT_MASTER'] 		= $id1[0];
			$track_data['PK_STUDENT_ENROLLMENT'] 	= $id1[1];
			$track_data['GLOBAL_CHANGE'] 			= 0;
			$track_data['FIELD_NAME']	 			= PLACEMENT_STATUS;
			$track_data['OLD_VALUE'] 	 			= $CUR_PLACEMENT_STATUS;
			$track_data['NEW_VALUE'] 	 			= $NEW_PLACEMENT_STATUS;
			track_field_change($track_data);
					
			$STUDENT_ENROLLMENT['PK_PLACEMENT_STATUS'] = $_POST['UPDATE_VALUE'];
			db_perform('S_STUDENT_ENROLLMENT', $STUDENT_ENROLLMENT, 'update'," PK_STUDENT_ENROLLMENT = '$id1[1]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
		}
	} else if($_POST['UPDATE_TYPE'] == 21){ /* Ticket # 1905 */
		//TERM_BLOCK
		$ids = explode(",",$_GET['id']);

		$res_tra = $db->Execute("select PK_TERM_BLOCK, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS END_DATE_1, DESCRIPTION from S_TERM_BLOCK WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_BLOCK = '$_POST[UPDATE_VALUE]' ");
		$NEW_TERM_BLOCK = $res_tra->fields['BEGIN_DATE_1'].' - '.$res_tra->fields['END_DATE_1'].' - '.$res_tra->fields['DESCRIPTION'];
		foreach($ids as $id){
			$id1 = explode("-",$id);
			
			$res_tra = $db->Execute("select IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS END_DATE_1, DESCRIPTION from S_TERM_BLOCK, S_STUDENT_ENROLLMENT WHERE S_STUDENT_ENROLLMENT.ENROLLMENT_PK_TERM_BLOCK = S_TERM_BLOCK.PK_TERM_BLOCK AND  PK_STUDENT_ENROLLMENT = '$id1[1]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$CUR_TERM_BLOCK = $res_tra->fields['BEGIN_DATE_1'].' - '.$res_tra->fields['END_DATE_1'].' - '.$res_tra->fields['DESCRIPTION'];
			
			$track_data['ID'] 			 			= '';
			$track_data['PK_STUDENT_MASTER'] 		= $id1[0];
			$track_data['PK_STUDENT_ENROLLMENT'] 	= $id1[1];
			$track_data['GLOBAL_CHANGE'] 			= 0;
			$track_data['FIELD_NAME']	 			= TERM_BLOCK;
			$track_data['OLD_VALUE'] 	 			= $CUR_TERM_BLOCK;
			$track_data['NEW_VALUE'] 	 			= $NEW_TERM_BLOCK;
			track_field_change($track_data);
					
			$STUDENT_ENROLLMENT['ENROLLMENT_PK_TERM_BLOCK'] = $_POST['UPDATE_VALUE'];
			db_perform('S_STUDENT_ENROLLMENT', $STUDENT_ENROLLMENT, 'update'," PK_STUDENT_ENROLLMENT = '$id1[1]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
		}  /* Ticket # 1905 */
	}else if($_POST['UPDATE_TYPE'] == 42){ // DIAM-2366
		
		$ids = explode(",",$_GET['id']);
		
		$res_tra = $db->Execute("select CONCAT(CODE,' - ',DESCRIPTION) AS NAME from M_ENROLLMENT_STATUS WHERE PK_ENROLLMENT_STATUS = '$_POST[UPDATE_VALUE]' ");
		$NEW_ENROLLMENT_STATUS = $res_tra->fields['NAME'];
		foreach($ids as $id){
			$id1 = explode("-",$id);
			
			$res_tra = $db->Execute("select CONCAT(CODE,' - ',DESCRIPTION) AS NAME from M_ENROLLMENT_STATUS, S_STUDENT_ENROLLMENT WHERE S_STUDENT_ENROLLMENT.ORIGINAL_ENROLLMENT_STATUS = M_ENROLLMENT_STATUS.PK_ENROLLMENT_STATUS AND  PK_STUDENT_ENROLLMENT = '$id1[1]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$CUR_ENROLLMENT_STATUS = $res_tra->fields['NAME'];
			
			$track_data['ID'] 			 			= '';
			$track_data['PK_STUDENT_MASTER'] 		= $id1[0];
			$track_data['PK_STUDENT_ENROLLMENT'] 	= $id1[1];
			$track_data['GLOBAL_CHANGE'] 			= 0;
			$track_data['FIELD_NAME']	 			= ORIGINAL_ENROLLMENT_STATUS;
			$track_data['OLD_VALUE'] 	 			= $CUR_ENROLLMENT_STATUS;
			$track_data['NEW_VALUE'] 	 			= $NEW_ENROLLMENT_STATUS;
			track_field_change($track_data);
					
			$STUDENT_ENROLLMENT['ORIGINAL_ENROLLMENT_STATUS'] = $_POST['UPDATE_VALUE'];
			db_perform('S_STUDENT_ENROLLMENT', $STUDENT_ENROLLMENT, 'update'," PK_STUDENT_ENROLLMENT = '$id1[1]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
		}
		
	} // End DIAM-2366
	else if($_POST['UPDATE_TYPE'] == 43){ // DIAM-2370
		
		$ids = explode(",",$_GET['id']);
		
		$res_tra = $db->Execute("SELECT CONCAT(CODE,' - ',DESCRIPTION) AS NAME, CODE, DESCRIPTION FROM M_RESIDENCY_TUITION_STATUS WHERE PK_RESIDENCY_TUITION_STATUS = '$_POST[UPDATE_VALUE]' ");
		$Name = "";
		if($res_tra->fields['NAME'] != "")
		{
			$Name = $res_tra->fields['DESCRIPTION']. " (".$res_tra->fields['CODE'].")";
		}
		$NEW_ENROLLMENT_STATUS = $Name ? $Name : 'Not Set';
		
		foreach($ids as $id){
			$id1 = explode("-",$id);
			
			$res_tra = $db->Execute("SELECT CONCAT(CODE,' - ',DESCRIPTION) AS NAME, CODE, DESCRIPTION FROM M_RESIDENCY_TUITION_STATUS, S_STUDENT_ENROLLMENT WHERE S_STUDENT_ENROLLMENT.PK_RESIDENCY_TUITION_STATUS = M_RESIDENCY_TUITION_STATUS.PK_RESIDENCY_TUITION_STATUS AND  PK_STUDENT_ENROLLMENT = '$id1[1]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

			$Name = "";
			if($res_tra->fields['NAME'] != "")
			{
				$sName = $res_tra->fields['DESCRIPTION']. " (".$res_tra->fields['CODE'].")";
			}

			$CUR_ENROLLMENT_STATUS = $sName ? $sName : 'Not Set';
			
			$track_data['ID'] 			 			= '';
			$track_data['PK_STUDENT_MASTER'] 		= $id1[0];
			$track_data['PK_STUDENT_ENROLLMENT'] 	= $id1[1];
			$track_data['GLOBAL_CHANGE'] 			= 0;
			$track_data['FIELD_NAME']	 			= RESIDENCY_TUITION_STATUS;
			$track_data['OLD_VALUE'] 	 			= $CUR_ENROLLMENT_STATUS;
			$track_data['NEW_VALUE'] 	 			= $NEW_ENROLLMENT_STATUS;
			track_field_change($track_data);
					
			$STUDENT_ENROLLMENT['PK_RESIDENCY_TUITION_STATUS'] = $_POST['UPDATE_VALUE'];
			db_perform('S_STUDENT_ENROLLMENT', $STUDENT_ENROLLMENT, 'update'," PK_STUDENT_ENROLLMENT = '$id1[1]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
		}
		
	} // End DIAM-2370
	?>
	<script type="text/javascript">window.opener.refresh_win(this)</script>
<? }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
	<? require_once("css.php"); ?>
	<title><?=BULK_UPDATE ?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? //require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-3 align-self-center">
                        <h4 class="text-themecolor">
							<?=BULK_UPDATE.' '; ?>
							<? if($_GET['t'] == 1) echo LEAD_PAGE_TITLE; else echo STUDENT_PAGE_TITLE; ?>
						</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<form class="floating-labels" method="post" name="form1" id="form1" >
									<div class="row">
										<div class="col-6 form-group" >
											<select id="UPDATE_TYPE" name="UPDATE_TYPE" class="form-control required-entry" onchange="get_values(this.value)" >
												<option value="" ><?=SELECT_UPDATE_TYPE ?></option>
												<? if($_GET['t'] == 1){ ?>
													<option value="1" ><?=BULK_ASSIGN?></option>
													<? if($_GET['SHOW_ARCHIVED'] == 0){ ?>
														<option value="2" ><?=BULK_ARCHIVE?></option>
													<? } ?>
													
													<? if($_GET['SHOW_ARCHIVED'] == 1){ ?>
														<option value="3" ><?=BULK_UNARCHIVE?></option>
													<? } ?>
													
													<option value="13" ><?=CHANGE_ADMIN_REP?></option>
													
													<option value="7" ><?=CHANGE_CAMPUS?></option>
													
													<option value="14" ><?=CHANGE_FIRST_TERM?></option>
													<option value="15" ><?=CHANGE_PROGRAM?></option>
													<option value="12" ><?=CHANGE_SESSION ?></option>
													
													<option value="5" ><?=CHANGE_STATUS?></option>
													<option value="6" >Change Student Group</option>
												<? } else if($_GET['t'] == 2){ ?>
													<? if($_GET['SHOW_ENROLLED_ONLY'] == 1){ ?>
														<option value="4" ><?=BULK_APPROVE?></option>
													<? } ?>
													<? if($_GET['SHOW_ARCHIVED'] == 0){ ?>
														<option value="2" ><?=BULK_ARCHIVE?></option>
													<? }
													if($_GET['SHOW_ARCHIVED'] == 1){ ?>
														<option value="3" ><?=BULK_UNARCHIVE?></option>
													<? } ?>
													<option value="7" ><?=CHANGE_CAMPUS?></option>
													
													<option value="8" ><?=CHANGE_DISTANCE_LEARNING?></option>
													<option value="9" ><?=CHANGE_EXPECTED_GRAD_DATE?></option>
													<option value="10" ><?=CHANGE_FULL_PART_TIME?></option>
													<option value="11" ><?=CHANGE_MID_POINT_DATE ?></option>
													<option value="42" ><?=CHANGE_ORIGINAL_ENROLLMENT_STATUS?></option> <!--DIAM-2366 -->
													<option value="15" ><?=CHANGE_PROGRAM?></option>
													<option value="43" ><?=CHANGE_RESIDENCY_TUITION_STATUS?></option> <!--DIAM-2370 -->
													<option value="12" ><?=CHANGE_SESSION ?></option>
													
													<option value="5" ><?=CHANGE_STATUS?></option>
													<option value="6" >Change Student Group</option>
												<? } else if($_GET['t'] == 3){ ?>
													<option value="16" ><?=CHANGE_FUNDING ?></option>
													<option value="11" ><?=CHANGE_MID_POINT_DATE ?></option>
												<? } else if($_GET['t'] == 5){ ?>
													<option value="16" ><?=CHANGE_FUNDING ?></option> <!-- Ticket # 1906 -->
													<option value="17" ><?=CHANGE_1098T_REPORTING ?></option>
													<option value="18" ><?=CHANGE_STRF_PAID_DATE ?></option>
													<option value="21" ><?=CHANGE.' '.TERM_BLOCK ?></option> <!-- Ticket # 1906 -->
												<? } else if($_GET['t'] == 6){ ?>
													<option value="19" ><?=CHANGE_SPECIAL ?></option>
													<option value="20" ><?=CHANGE_PLACEMENT_STATUS ?></option>
													
												<? } ?>
											</select>
										</div>
										
										<div class="col-6 form-group" id="UPDATE_VALUE_DIV" >
											<select id="UPDATE_VALUE" name="UPDATE_VALUE" class="form-control required-entry" >
												<option value="" ><?=SELECT_VALUE ?></option>
											</select>
										</div>
									</div>
									
									<!-- Ticket # 1513 -->
									<div class="row">
										<div class="col-6 form-group" >
										</div>
										<div class="col-6 form-group" id="END_DATE_DIV" >
											
										</div>
									</div>
									<div class="row">
										<div class="col-6 form-group" >
										</div>
										<div class="col-6 form-group" id="DROP_REASON_DIV" style="display:none" >
											<select id="PK_DROP_REASON" name="PK_DROP_REASON" class="form-control required-entry" >
												<option value="" >Drop Reason</option>
												<? $res_type = $db->Execute("select PK_DROP_REASON, CONCAT(DROP_REASON, ' - ', DESCRIPTION) as DROP_REASON, ACTIVE from M_DROP_REASON WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, DROP_REASON ASC");
												while (!$res_type->EOF) { 
													$option_label = $res_type->fields['DROP_REASON'];
													if($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)"; ?>
													<option value="<?=$res_type->fields['PK_DROP_REASON']?>" <? if($PK_DROP_REASON == $res_type->fields['PK_DROP_REASON']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
									</div>
									<!-- Ticket # 1513 -->
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=UPDATE?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="javascript:window.close()" ><?=CANCEL?></button>
												
											</div>
										</div>
									</div>
								</form>
                            </div>
                        </div>
					</div>
				</div>
				
            </div>
        </div>
        <? require_once("footer.php"); ?>
    </div>
   
	<? require_once("js.php"); ?>

	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	
	<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.4.1.min.js"></script>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
	var form1 = new Validation('form1');
	
	function get_values(val){
		jQuery(document).ready(function($) { 
			var data  = 'type='+val+'&t=<?=$_GET['t']?>';
			var value = $.ajax({
				url: "ajax_get_student_update_value",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {
					document.getElementById('UPDATE_VALUE_DIV').innerHTML = data;
					
					/* Ticket # 1513 */
					document.getElementById('END_DATE_DIV').innerHTML 			= ''
					document.getElementById('DROP_REASON_DIV').style.display 	= 'none'
					/* Ticket # 1513 */
					
					jQuery('.date').datepicker({
						todayHighlight: true,
						orientation: "bottom auto"
					});
				}		
			}).responseText;
		});
	}
	
	/* Ticket # 1513 */
	function show_end_date(){
		jQuery(document).ready(function($) { 
			var END_DATE_TYPE = $("#UPDATE_VALUE option:selected").attr("att_end_date");
			var str = '<input type="hidden" id="END_DATE_TYPE" name="END_DATE_TYPE" value="'+END_DATE_TYPE+'" >'
			
			document.getElementById('DROP_REASON_DIV').style.display = 'none'
			
			if(END_DATE_TYPE == 2) {
				//LDA
				str += '<input type="text" id="LDA" name="LDA" class="form-control required-entry date" value="" placeholder="LDA" >'; 
			} else if(END_DATE_TYPE == 3) {
				//drop
				str += '<input type="text" id="DROP_DATE" name="DROP_DATE" class="form-control required-entry date" value="" placeholder="Drop Date" >'; 
				document.getElementById('DROP_REASON_DIV').style.display = 'block'
			} else if(END_DATE_TYPE == 4) {
				//grad
				str += '<input type="text" id="GRAD_DATE" name="GRAD_DATE" class="form-control required-entry date" value="" placeholder="Grad Date" >'; 
			} else if(END_DATE_TYPE == 5) {	
				//DETERMINATION_DATE
				str += '<input type="text" id="DETERMINATION_DATE" name="DETERMINATION_DATE" class="form-control required-entry date" value="" placeholder="Determination Date" >'; 
			}
			
			document.getElementById('END_DATE_DIV').innerHTML = str
			
			jQuery('.date').datepicker({
				todayHighlight: true,
				orientation: "bottom auto"
			});
		});
	}
	/* Ticket # 1513 */
	
	</script>
</body>

</html>
