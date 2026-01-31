<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php"); //ticket #1064
require_once("../language/notes.php");
require_once("get_department_from_t.php");
require_once("check_access.php");
require_once("../global/s3-client-wrapper/s3-client-wrapper.php");

$ADMISSION_ACCESS 	= check_access('ADMISSION_ACCESS');
$REGISTRAR_ACCESS 	= check_access('REGISTRAR_ACCESS');
$FINANCE_ACCESS 	= check_access('FINANCE_ACCESS');
$ACCOUNTING_ACCESS 	= check_access('ACCOUNTING_ACCESS');
$PLACEMENT_ACCESS 	= check_access('PLACEMENT_ACCESS');

$MANAGEMENT_BULK_UPDATE 	= check_access('MANAGEMENT_BULK_UPDATE');

if($ADMISSION_ACCESS != 2 && $ADMISSION_ACCESS != 3 && $REGISTRAR_ACCESS != 2 && $REGISTRAR_ACCESS != 3 && $FINANCE_ACCESS != 2 && $FINANCE_ACCESS != 3 && $ACCOUNTING_ACCESS != 2 && $ACCOUNTING_ACCESS != 3 && $PLACEMENT_ACCESS != 2 && $PLACEMENT_ACCESS != 3 && $MANAGEMENT_BULK_UPDATE != 1){
	header("location:../index");
	exit;
}

if($_GET['act'] == 'document_del'){
	$db->Execute("DELETE FROM S_STUDENT_NOTES_DOCUMENTS WHERE PK_STUDENT_NOTES_DOCUMENTS = '$_GET[iid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	header("location:student_notes?id=".$_GET['id'].'&sid='.$_GET['sid'].'&eid='.$_GET['eid'].'&event='.$_GET['event']);
}

if($_GET['event'] == 1)
	$tab = "eventTab";
else
	$tab = "noteTab";
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$SHOW_ON_ALL_DEP 	= $_POST['SHOW_ON_ALL_DEP'];
	unset($_POST['SHOW_ON_ALL_DEP']);
	
	$PK_PAYMENT_FREQUENCY 	= $_POST['PK_PAYMENT_FREQUENCY'];
	$NO_OF_TIMES 			= $_POST['NO_OF_TIMES'];
	$t 						= $_POST['t'];
	unset($_POST['PK_PAYMENT_FREQUENCY']);
	unset($_POST['NO_OF_TIMES']);
	unset($_POST['t']);
	
	$STUDENT_NOTES 				= $_POST;
	$STUDENT_NOTES['SATISFIED'] = $_POST['SATISFIED'];
	
	if($_GET['event'] == 1)
		$STUDENT_NOTES['IS_EVENT'] = 1;
	else
		$STUDENT_NOTES['IS_EVENT'] = 0;
		
	if($STUDENT_NOTES['FOLLOWUP_DATE'] != '') {
		$STUDENT_NOTES['FOLLOWUP_DATE'] = date("Y-m-d",strtotime($STUDENT_NOTES['FOLLOWUP_DATE']));
		$FOLLOWUP_DATE					= $STUDENT_NOTES['FOLLOWUP_DATE'];
	} else
		$FOLLOWUP_DATE = '';
		
	if($STUDENT_NOTES['FOLLOWUP_TIME'] != '')
		$STUDENT_NOTES['FOLLOWUP_TIME'] = date("H:i:s",strtotime($STUDENT_NOTES['FOLLOWUP_TIME']));
	else
		$STUDENT_NOTES['FOLLOWUP_TIME'] = '';
	
	if($_GET['p'] == 'm'){
		$PK_DEPARTMENT = get_department_from_t($t);	
	} else {
		$PK_DEPARTMENT = get_department_from_t($_GET['t']);	
	}
	$PK_DEPARTMENT_SOURCE = $PK_DEPARTMENT; //DIAM-1543
	if($SHOW_ON_ALL_DEP == 1)
		$PK_DEPARTMENT = -1;
		
	if($STUDENT_NOTES['NOTE_DATE'] != '') {
		$STUDENT_NOTES['NOTE_DATE'] = date("Y-m-d",strtotime($STUDENT_NOTES['NOTE_DATE']));
		$NOTE_DATE					= date("Y-m-d",strtotime($STUDENT_NOTES['NOTE_DATE']));
	} else {
		$STUDENT_NOTES['NOTE_DATE'] = '';
		$NOTE_DATE					= date("Y-m-d");
	}
	
	if($STUDENT_NOTES['NOTE_TIME'] != '')
		$STUDENT_NOTES['NOTE_TIME'] = date("H:i:s",strtotime($STUDENT_NOTES['NOTE_TIME']));
	else
		$STUDENT_NOTES['NOTE_TIME'] = '';
	
	$STUDENT_NOTES['PK_DEPARTMENT'] = $PK_DEPARTMENT;
	$STUDENT_NOTES['PK_DEPARTMENT_SOURCE'] = $PK_DEPARTMENT_SOURCE; //DIAM-1543
	
	if($_GET['p'] == 'm'){
		
		$PK_STUDENT_ENROLLMENT_ARR = explode(",",$_SESSION['BULK_EN']);
		
		$ADD_FREQUENCY = '';	
		if($PK_PAYMENT_FREQUENCY == 1 )
			$ADD_FREQUENCY = " 1 weeks";
		else if($PK_PAYMENT_FREQUENCY == 2 )
			$ADD_FREQUENCY = " 14 days";
		else if($PK_PAYMENT_FREQUENCY == 3 )
			$ADD_FREQUENCY = " 5 weeks";
		else if($PK_PAYMENT_FREQUENCY == 4 )
			$ADD_FREQUENCY = " 1 Months";
		else if($PK_PAYMENT_FREQUENCY == 5 )
			$ADD_FREQUENCY = " 2 Months";
		else if($PK_PAYMENT_FREQUENCY == 6 )
			$ADD_FREQUENCY = " 60 days";
		else if($PK_PAYMENT_FREQUENCY == 7 )
			$ADD_FREQUENCY = " 12 weeks";
		else if($PK_PAYMENT_FREQUENCY == 8 )
			$ADD_FREQUENCY = " 13 weeks";
		else if($PK_PAYMENT_FREQUENCY == 9 )
			$ADD_FREQUENCY = " 20 weeks";
		else if($PK_PAYMENT_FREQUENCY == 10 )
			$ADD_FREQUENCY = " 3 Months";
		else if($PK_PAYMENT_FREQUENCY == 12 )
			$ADD_FREQUENCY = " 1 year";
		
		if($PK_PAYMENT_FREQUENCY == -1 || $PK_PAYMENT_FREQUENCY == '')
			$NO_OF_TIMES = 1;

		foreach($PK_STUDENT_ENROLLMENT_ARR as $PK_STUDENT_ENROLLMENT){
			$res_stud = $db->Execute("SELECT PK_STUDENT_MASTER FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
			
			for($i = 1 ; $i <= $NO_OF_TIMES ; $i++){
				if($i == 1)
					$STUDENT_NOTES['NOTE_DATE'] = date("Y-m-d",strtotime($NOTE_DATE));
				else {
					$STUDENT_NOTES['NOTE_DATE'] = date("Y-m-d",strtotime($STUDENT_NOTES['NOTE_DATE']." +".$ADD_FREQUENCY));
				}
				
				if($FOLLOWUP_DATE != ''){
					if($i == 1)
						$STUDENT_NOTES['FOLLOWUP_DATE'] = date("Y-m-d",strtotime($FOLLOWUP_DATE));
					else {
						$STUDENT_NOTES['FOLLOWUP_DATE'] = date("Y-m-d",strtotime($STUDENT_NOTES['FOLLOWUP_DATE']." +".$ADD_FREQUENCY));
					}
				}
		
				$STUDENT_NOTES['PK_ACCOUNT']   			= $_SESSION['PK_ACCOUNT'];
				$STUDENT_NOTES['PK_STUDENT_MASTER'] 	= $res_stud->fields['PK_STUDENT_MASTER'];
				$STUDENT_NOTES['PK_STUDENT_ENROLLMENT'] = $PK_STUDENT_ENROLLMENT;
				$STUDENT_NOTES['PK_NOTE_TYPE'] 			= $_POST['PK_NOTE_TYPE'];
				$STUDENT_NOTES['NOTES'] 				= $_POST['NOTES'];
				
				$STUDENT_NOTES['CREATED_BY']  		= $_SESSION['PK_USER'];
				$STUDENT_NOTES['CREATED_ON']  		= date("Y-m-d H:i");
				db_perform('S_STUDENT_NOTES', $STUDENT_NOTES, 'insert');
			}
		}

		$_SESSION['BULK_EN'] = '';
	} else {
		if($_GET['id'] == ''){
			$STUDENT_NOTES['PK_ACCOUNT']   			= $_SESSION['PK_ACCOUNT'];
			$STUDENT_NOTES['PK_STUDENT_MASTER'] 	= $_GET['sid'];
			$STUDENT_NOTES['PK_STUDENT_ENROLLMENT'] = $_GET['eid'];
			$STUDENT_NOTES['PK_NOTE_TYPE'] 			= $_POST['PK_NOTE_TYPE'];
			$STUDENT_NOTES['NOTES'] 				= $_POST['NOTES'];
			$STUDENT_NOTES['PK_COMPANY'] 			= $_POST['PK_COMPANY'];
			
			$STUDENT_NOTES['CREATED_BY']  		= $_SESSION['PK_USER'];
			$STUDENT_NOTES['CREATED_ON']  		= date("Y-m-d H:i");
			db_perform('S_STUDENT_NOTES', $STUDENT_NOTES, 'insert');
			
			$PK_STUDENT_NOTES = $db->insert_ID();
		} else {
			$PK_STUDENT_NOTES = $_GET['id'];
			$cond = "";
			$STUDENT_NOTES['PK_DEPARTMENT_SOURCE'] = $PK_DEPARTMENT_SOURCE; //DIAM-1543
			$STUDENT_NOTES['EDITED_BY']  = $_SESSION['PK_USER'];
			$STUDENT_NOTES['EDITED_ON']  = date("Y-m-d H:i");
			db_perform('S_STUDENT_NOTES', $STUDENT_NOTES, 'update', " PK_STUDENT_NOTES = '$PK_STUDENT_NOTES' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond " );
			
			//echo "<pre> K_STUDENT_NOTES = '$PK_STUDENT_NOTES' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$_GET[eid]' $cond  ";print_r($STUDENT_NOTES);exit;
		}
	}
	//echo "<pre>";print_r($STUDENT_DOCUMENTS);exit;
	
	//echo "<pre>";print_r($_FILES);exit;
	$i = 0;
	// $file_dir_1 = '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/student/';
	$file_dir_1 = '../backend_assets/tmp_upload/';
	for($k = 0 ; $k < count($_FILES['ATTACHMENT']['name']) ; $k++){
		if($_FILES['ATTACHMENT']['name'][$i] != '') {
			$extn 			= explode(".",$_FILES['ATTACHMENT']['name'][$i]);
			$iindex			= count($extn) - 1;
			$rand_string 	= time()."_".rand(10000,99999);
			$file11			= $_GET['sid'].'_task_'.$rand_string.".".$extn[$iindex];	
			$extension   	= strtolower($extn[$iindex]);
			
			if($extension != "php" && $extension != "js" && $extension != "html" && $extension != "htm"  ){ 
				$newfile1    = $file_dir_1.$file11;
						
				move_uploaded_file($_FILES['ATTACHMENT']['tmp_name'][$i], $newfile1);

				// Upload file to S3 bucket
				$key_file_name = 'backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/student/'.$file11;
				$s3ClientWrapper = new s3ClientWrapper();
				$url = $s3ClientWrapper->uploadFile($key_file_name, $newfile1);
				
				// $STUDENT_NOTES_DOCUMENTS['DOCUMENT_PATH'] 		= $newfile1;
				$STUDENT_NOTES_DOCUMENTS['DOCUMENT_PATH'] 		= $url;
				$STUDENT_NOTES_DOCUMENTS['DOCUMENT_NAME'] 		= $_FILES['ATTACHMENT']['name'][$i];
				$STUDENT_NOTES_DOCUMENTS['PK_ACCOUNT']  		= $_SESSION['PK_ACCOUNT'];
				$STUDENT_NOTES_DOCUMENTS['PK_STUDENT_NOTES'] 	= $PK_STUDENT_NOTES;
				$STUDENT_NOTES_DOCUMENTS['CREATED_BY']  		= $_SESSION['PK_USER'];
				$STUDENT_NOTES_DOCUMENTS['CREATED_ON']  		= date("Y-m-d H:i");
				db_perform('S_STUDENT_NOTES_DOCUMENTS', $STUDENT_NOTES_DOCUMENTS, 'insert');

				// delete tmp file
				unlink($newfile1);
			}
		}
		
		$i++;
	}
	if($_GET['batch'] != '')
	{
		header("location:batch_payment?id=".$_GET['batch']);
	}
	else if($_GET['p'] == 'm')
	{
		header("location:management");
	}
	else
	{
		header("location:student?id=".$_GET['sid'].'&tab='.$tab.'&eid='.$_GET['eid'].'&t='.$_GET['t']);
	}
}
if($_GET['id'] == ''){
	$PK_DEPARTMENT 				= '';
	$PK_EMPLOYEE_MASTER			= $_SESSION['PK_EMPLOYEE_MASTER'];
	$PK_NOTE_TYPE				= '';
	$FOLLOWUP_DATE 				= '';
	$FOLLOWUP_TIME				= '';
	$PK_NOTE_STATUS 			= '';
	$PK_NOTES_PRIORITY_MASTER 	= '';
	$IS_EVENT 					= '';
	$SATISFIED					= '';
	$NOTES	 					= '';
	$NOTE_DATE 					= '';
	$NOTE_TIME 					= '';
	$PK_EVENT_OTHER				= '';
	$PK_COMPANY_ARR				= '';
	if($_GET['event'] == 1)
		$IS_EVENT = 1;
} else {
	$cond = "";

	$res = $db->Execute("SELECT * FROM S_STUDENT_NOTES WHERE PK_STUDENT_NOTES = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond "); 
	if($res->RecordCount() == 0){
		header("location:student?id=".$_GET['sid'].'&tab='.$tab.'&eid='.$_GET['eid'].'&t='.$_GET['t']);
		exit;
	}

	$PK_DEPARTMENT 				= $res->fields['PK_DEPARTMENT'];
	$PK_EMPLOYEE_MASTER			= $res->fields['PK_EMPLOYEE_MASTER'];
	$PK_NOTE_TYPE 				= $res->fields['PK_NOTE_TYPE'];
	$NOTES  					= $res->fields['NOTES'];
	$FOLLOWUP_DATE 				= $res->fields['FOLLOWUP_DATE'];
	$FOLLOWUP_TIME  			= $res->fields['FOLLOWUP_TIME'];
	$PK_NOTE_STATUS 			= $res->fields['PK_NOTE_STATUS'];
	$PK_NOTES_PRIORITY_MASTER 	= $res->fields['PK_NOTES_PRIORITY_MASTER'];
	$IS_EVENT 					= $res->fields['IS_EVENT'];
	$SATISFIED 					= $res->fields['SATISFIED'];
	$NOTE_DATE 					= $res->fields['NOTE_DATE'];
	$NOTE_TIME 					= $res->fields['NOTE_TIME'];
	$PK_EVENT_OTHER 			= $res->fields['PK_EVENT_OTHER'];
	$PK_COMPANY_ARR				= $res->fields['PK_COMPANY'];
	
	/* Ticket # 1749 */
	$CREATED_BY	= $res->fields['CREATED_BY'];
	$CREATED_ON	= $res->fields['CREATED_ON'];
	$res_user = $db->Execute("SELECT CONCAT(LAST_NAME,', ', FIRST_NAME) as NAME FROM S_EMPLOYEE_MASTER, Z_USER WHERE PK_USER = '$CREATED_BY' AND Z_USER.ID =  S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER AND PK_USER_TYPE IN (1,2) "); 
	$CREATED_BY	= $res_user->fields['NAME'];
	/* Ticket # 1749 */
	
	if($PK_DEPARTMENT == -1)
		$SHOW_ON_ALL_DEP = 1;
	else
		$SHOW_ON_ALL_DEP = 0;
	
	if($FOLLOWUP_DATE != '0000-00-00')
		$FOLLOWUP_DATE = date("m/d/Y",strtotime($FOLLOWUP_DATE));
	else
		$FOLLOWUP_DATE = '';
		
	if($FOLLOWUP_TIME != '00:00:00')
		$FOLLOWUP_TIME = date("h:i A",strtotime($FOLLOWUP_TIME));
	else
		$FOLLOWUP_TIME = '';
		
	if($NOTE_DATE != '0000-00-00')
		$NOTE_DATE = date("m/d/Y",strtotime($NOTE_DATE));
	else
		$NOTE_DATE = '';

	if($NOTE_TIME != '00:00:00')
		$NOTE_TIME = date("h:i A",strtotime($NOTE_TIME));
	else
		$NOTE_TIME = '';
		
	/* Ticket #1468  */
	$res = $db->Execute("SELECT PK_DEPARTMENT_MASTER FROM M_DEPARTMENT WHERE PK_DEPARTMENT = '$PK_DEPARTMENT' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	$PK_DEPARTMENT_MASTER = $res->fields['PK_DEPARTMENT_MASTER'];
	
	$edit_flag = 0;
	if($_SESSION['ADMIN_PK_ROLES'] == 1 || $_SESSION['PK_ROLES'] == 2 || $PK_DEPARTMENT == -1) {
		$edit_flag = 1;
	} else if($PK_DEPARTMENT_MASTER == 2) {
		//admission
		if(($ADMISSION_ACCESS == 2 || $ADMISSION_ACCESS == 3) && $_GET['t'] == 1)
			$edit_flag = 1;
	} else if($PK_DEPARTMENT_MASTER == 7) {
		//Registrar
		if(($REGISTRAR_ACCESS == 2 || $REGISTRAR_ACCESS == 3) && $_GET['t'] == 2)
			$edit_flag = 1;
	} else if($PK_DEPARTMENT_MASTER == 4) {
		//Finance
		if(($FINANCE_ACCESS == 2 || $FINANCE_ACCESS == 3) && $_GET['t'] == 3)
			$edit_flag = 1;
	} else if($PK_DEPARTMENT_MASTER == 1) {
		//Accounting
		if(($ACCOUNTING_ACCESS == 2 || $ACCOUNTING_ACCESS == 3) && $_GET['t'] == 5)
			$edit_flag = 1;
	} else if($PK_DEPARTMENT_MASTER == 6) {
		//Placement
		if(($PLACEMENT_ACCESS == 2 || $PLACEMENT_ACCESS == 3) && $_GET['t'] == 6)
			$edit_flag = 1;
	}
	if($edit_flag == 0){
		header("location:student?id=".$_GET['sid'].'&tab='.$tab.'&eid='.$_GET['eid'].'&t='.$_GET['t']);
		exit;
	}
	/* Ticket #1468  */
}
if($_GET['sid'] != ''){
	$res = $db->Execute("SELECT IMAGE,FIRST_NAME,LAST_NAME,MIDDLE_NAME,OTHER_NAME FROM S_STUDENT_MASTER WHERE PK_STUDENT_MASTER = '$_GET[sid]' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	$IMAGE					= $res->fields['IMAGE'];
	$FIRST_NAME 			= $res->fields['FIRST_NAME'];
	$LAST_NAME 				= $res->fields['LAST_NAME'];
	$MIDDLE_NAME	 		= $res->fields['MIDDLE_NAME'];
	$OTHER_NAME	 			= $res->fields['OTHER_NAME'];

	/* ticket #1116 */
	$res = $db->Execute("SELECT STUDENT_ID, STATUS_DATE,STUDENT_STATUS,CODE, IF(EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(EXPECTED_GRAD_DATE, '%m/%d/%Y' )) AS EXPECTED_GRAD_DATE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IF(DETERMINATION_DATE = '0000-00-00','',DATE_FORMAT(DETERMINATION_DATE, '%m/%d/%Y' )) AS DETERMINATION_DATE, IF(DROP_DATE = '0000-00-00','',DATE_FORMAT(DROP_DATE, '%m/%d/%Y' )) AS DROP_DATE , IF(GRADE_DATE = '0000-00-00','',DATE_FORMAT(GRADE_DATE, '%m/%d/%Y' )) AS GRADE_DATE, IF(LDA = '0000-00-00','',DATE_FORMAT(LDA, '%m/%d/%Y' )) AS LDA, IF(ORIGINAL_EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(ORIGINAL_EXPECTED_GRAD_DATE, '%m/%d/%Y' )) AS ORIGINAL_EXPECTED_GRAD_DATE FROM S_STUDENT_ENROLLMENT LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$_GET[sid]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$_GET[eid]' "); //Ticket # 1537
	$STATUS_DATE 	 	= $res->fields['STATUS_DATE'];
	$STUDENT_STATUS	 	= $res->fields['STUDENT_STATUS'];
	$CAMPUS_PROGRAM  	= $res->fields['CODE'];
	$FIRST_TERM_DATE 	= $res->fields['BEGIN_DATE_1'];
	$EXPECTED_GRAD_DATE = $res->fields['EXPECTED_GRAD_DATE'];
	$STUDENT_ID 		= $res->fields['STUDENT_ID'];//Ticket # 1537

	$ORIGINAL_EXPECTED_GRAD_DATE 	= $res->fields['ORIGINAL_EXPECTED_GRAD_DATE'];
	$DETERMINATION_DATE 			= $res->fields['DETERMINATION_DATE'];
	$DROP_DATE 						= $res->fields['DROP_DATE'];
	$GRADE_DATE 					= $res->fields['GRADE_DATE'];
	$LDA 							= $res->fields['LDA'];
	/* ticket #1116 */

	/* Ticket # 1534 */
	$res = $db->Execute("SELECT CAMPUS_CODE FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE PK_STUDENT_ENROLLMENT = '$_GET[eid]' AND S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS AND PK_STUDENT_ENROLLMENT > 0 ");
	$HEADER_CAMPUS_CODE = $res->fields['CAMPUS_CODE'];
	/* Ticket # 1534 */
	
	if($STATUS_DATE != '0000-00-00')
		$STATUS_DATE = date("m/d/Y",strtotime($STATUS_DATE));
	else
		$STATUS_DATE = '';
		
	$has_warning_notes 	= 0;
	$warning_notes 		= '';
	//DIAM-1543
	$res_note = $db->Execute("select NOTES,DEPARTMENT FROM S_STUDENT_NOTES LEFT JOIN M_DEPARTMENT ON M_DEPARTMENT.PK_DEPARTMENT = S_STUDENT_NOTES.PK_DEPARTMENT OR M_DEPARTMENT.PK_DEPARTMENT = S_STUDENT_NOTES.PK_DEPARTMENT_SOURCE, M_NOTE_TYPE WHERE PK_NOTE_TYPE_MASTER = 1 AND S_STUDENT_NOTES.PK_NOTE_TYPE = M_NOTE_TYPE.PK_NOTE_TYPE AND S_STUDENT_NOTES.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_NOTES.PK_STUDENT_MASTER = '$_GET[sid]' AND PK_STUDENT_ENROLLMENT = '$_GET[eid]' AND SATISFIED = 0 ");
		
	if($res_note->RecordCount() > 0) {
		$has_warning_notes = 1;
		$warning_notes = '';
		while (!$res_note->EOF){
			if(!empty($res_note->fields['DEPARTMENT'])){
			if($warning_notes != '')
				$warning_notes .= ', ';
				
			$warning_notes .= $res_note->fields['DEPARTMENT']; //DIAM-1543
			}
			$res_note->MoveNext();
		}
		//DIAM-1543
		if(!empty($warning_notes)){ 
		$warning_notes = 'Warning - See '.$warning_notes;
		}else{
			//$warning_notes = 'Warning - See Registrar';
		}
		//DIAM-1543
	}

	$res_probation = $db->Execute("select PK_STUDENT_PROBATION FROM S_STUDENT_PROBATION WHERE PK_PROBATION_STATUS = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_GET[sid]' AND PK_STUDENT_ENROLLMENT = '$_GET[eid]' ");
	if($res_probation->RecordCount() > 0) {
		$has_warning_notes = 1;
		if($warning_notes != '')
			$warning_notes .= '<br />';
			
		$warning_notes .= 'On Probation';
	}
}

$title1 = NOTES_PAGE_TITLE;
if($_GET['event'] == 1)
	$title1 = EVENT_PAGE_TITLE;
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
	<link href="../backend_assets/node_modules/Magnific-Popup-master/dist/magnific-popup.css" rel="stylesheet">
	<link href="../backend_assets/dist/css/pages/user-card.css" rel="stylesheet">
	<title><?=$title1?> | <?=$title?></title>
	
	<!-- Ticket # 1615 -->
	<style>
	option.option_red {
		color: red !important;
	}

	/* #PK_COMPANY_DIV .select2-container--default .select2-selection--single {
		border: none !important;
	} */

	.select2-container--default .select2-selection--single {
		border: none !important;
	}

	/* .select2-results__option{
		height: 30px !important;
	} */
	#select2-PK_EMPLOYEE_MASTER-results li:first-child{
		height: 30px !important;
	}
	</style>
	<!-- Ticket # 1615 -->
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles"  <? if($has_warning_notes == 1){ ?> style="background-color: #d12323 !important;color: #fff;" <? } ?> >
					<!-- ticket 1116 -->
                    <? if($_GET['sid'] == ''){ ?>
					<div class="col-md-1 align-self-center">
                        <h4 class="text-themecolor" <? if($has_warning_notes == 1){ ?> style="color: #fff;" <? } ?> ><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=$title1?> </h4>
                    </div>
					<? } else { ?>
					<div class="col-md-8 align-self-center" style="flex: 0 0 65.0%;max-width: 65.0%;"> <!-- ticket #1534 -->
						<table width="100%" >
							<tr>
								<td width="13%" >
									 <h4 class="text-themecolor" <? if($has_warning_notes == 1){ ?> style="color: #fff;" <? } ?> ><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=$title1?> </h4>
									<br />
								</td>
								<td ><b ><?=$LAST_NAME.', '.$FIRST_NAME.' '.$MIDDLE_NAME?></b><br /><br /></td><!-- Ticket # 1715 -->
								<td colspan="3" valign="top" ><?=$warning_notes?></td>
							</tr>
							<!-- Ticket # 1537 -->
							<tr>
								<td rowspan="5" >
									<? if($IMAGE != '') { ?>
										<div class="row el-element-overlay" style="width: 85%;" >
											<div class="card" style="margin-bottom: 0;margin-left: 10px;" >
												<div class="el-card-item" style="padding-bottom:0" >
													<div class="el-card-avatar el-overlay-1" style="margin-bottom: 0;" > 
														<img src="<?=$IMAGE?>" alt="user" />
														<div class="el-overlay">
															<ul class="el-info">
																<li><a class="btn default btn-outline image-popup-vertical-fit" href="<?=$IMAGE?>"><i class="icon-magnifier"></i></a></li>
															</ul>
														</div>
													</div>
												</div>
											</div>
										</div>
										<!--<img src="<?=$IMAGE?>" style="height: 80px;" />-->
									<? } ?>
								</td>
								<td width="19%" ><b ><?=STUDENT_ID.':' ?></b></td>
								<td width="29%" ><?=$STUDENT_ID; ?></td> 
								<td width="18%" ></b></td>
								<td width="11%" ></td>
							</tr>
							<!-- Ticket # 1715 -->
							<tr>
								<td ><b  ><?=ENROLLMENT.':' ?></b></td>
								<td ><?=$FIRST_TERM_DATE.' - '.$CAMPUS_PROGRAM.' - '.$STUDENT_STATUS.' - '.$HEADER_CAMPUS_CODE; ?></td>
								<td >&nbsp;&nbsp;<b ><?=DETERMINATION_DATE.':' ?></b></td>
								<td ><?=$DETERMINATION_DATE ?></td>
							</tr>
							<tr>
								<td ><b  ><?=STATUS_DATE.':' ?></b></td>
								<td ><?=$STATUS_DATE ?></td>
								<td >&nbsp;&nbsp;<b ><?=DROP_DATE.':' ?></b></td>
								<td ><?=$DROP_DATE ?></td>
							</tr>
							<!-- Ticket # 1715 -->
							<tr>
								<td ><b  ><?=EXPECTED_GRAD_DATE.':' ?></b></td>
								<td ><?=$EXPECTED_GRAD_DATE ?></td>
								<td >&nbsp;&nbsp;<b ><?=GRADE_DATE.':' ?></b></td>
								<td ><?=$GRADE_DATE ?></td>
							</tr>
							<tr>
								<td ><b  ><?=ORIGINAL_EXPECTED_GRAD_DATE_1.':' ?></b></td>
								<td ><?=$ORIGINAL_EXPECTED_GRAD_DATE ?></td>
								<td >&nbsp;&nbsp;<b ><?=LDA.':' ?></b></td>
								<td ><?=$LDA ?></td>
							</tr>
						</table>
					</div>
					<!-- ticket 1116 -->
					<? } ?>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
									<? if($_GET['p'] == 'm'){ 
										
										// DIAM-1423
										if($_GET['batch'] != '')
										{
											$selected = 'selected';
											$ReadOnly = 'style="pointer-events: none;opacity: 0.7;pointer-events: none;"';
										}
										else{
											$selected = '';
											$ReadOnly = '';
										}
										// End DIAM-1423
										
										?>
									<div class="row">
										<div class="col-md-3">
											<div class="form-group m-b-40">
												<select id="t" name="t" class="form-control required-entry" onchange="get_note_type(this.value); get_event_other(this.value); get_note_status(this.value); get_employee(this.value);" <?=$ReadOnly?> >
													<option ></option>
													<option value="1" >Admissions</option>
													<option value="2" >Registrar</option>
													<option value="3" >Finance</option>
													<option value="5" <?=$selected?> >Accounting</option>
													<option value="6" >Placement</option>
												</select>
												<span class="bar"></span>
												<label for="t"><?=DEPARTMENT?></label>
											</div>
										</div>
										
										<div class="col-md-3">
											<div class="form-group m-b-40">
												<select id="PK_PAYMENT_FREQUENCY" name="PK_PAYMENT_FREQUENCY" class="form-control required-entry" onclick="show_no_times(this.value)" >
													<option value="-1" selected>One Time</option>
													 <? $res_type = $db->Execute("select PK_PAYMENT_FREQUENCY,PAYMENT_FREQUENCY from M_PAYMENT_FREQUENCY WHERE PK_PAYMENT_FREQUENCY IN (1,2,4,10,12) order by DISPLAY_ORDER ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_PAYMENT_FREQUENCY']?>" ><?=$res_type->fields['PAYMENT_FREQUENCY']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="PK_PAYMENT_FREQUENCY"><?=RECURRING_TYPE?></label>
											</div>
										</div>
										
										<div class="col-md-3" style="display:none" id="NO_OF_TIMES_DIV" >
											<div class="form-group m-b-40 "  >
												<input type="text" class="form-control required-entry" id="NO_OF_TIMES" name="NO_OF_TIMES" value="1" >
												<span class="bar"></span>
												<label for="NO_OF_TIMES"><?=NO_OF_TIMES?></label>
											</div>
										</div>
									</div>
									<? } ?>
									<div class="row">
                                        <div class="col-md-12">
											<div class="row">
												<div class="col-md-3">
													<div class="form-group m-b-40">
														<?  $cond = " AND TYPE = 1 ";
														if($_GET['event'] == 1)
															$cond = " AND TYPE = 2 ";
															
														$PK_DEPARTMENT = get_department_from_t($_GET['t']);	
														$cond .= " AND (PK_DEPARTMENT = '$PK_DEPARTMENT' OR PK_DEPARTMENT = -1) ";
														
														/* Ticket # 1690  */
														$union = "";
														if($PK_NOTE_TYPE > 0)
															$union = " UNION select PK_NOTE_TYPE, CONCAT(NOTE_TYPE, ' - ', DESCRIPTION) as NOTE_TYPE, ACTIVE from M_NOTE_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_NOTE_TYPE = '$PK_NOTE_TYPE' ";
														
														$res_type = $db->Execute("SELECT * FROM (select PK_NOTE_TYPE, CONCAT(NOTE_TYPE, ' - ', DESCRIPTION) as NOTE_TYPE, ACTIVE from M_NOTE_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond $union) as TEMP order by ACTIVE DESC, NOTE_TYPE ASC"); ?>
														<div id="PK_NOTE_TYPE_DIV" >
															<select id="PK_NOTE_TYPE" name="PK_NOTE_TYPE" class="form-control required-entry">
																<option></option>
																<? while (!$res_type->EOF) { 
																	$option_label = $res_type->fields['NOTE_TYPE'];
																	if($res_type->fields['ACTIVE'] == 0)
																		$option_label .= " (Inactive)"; ?>
																	<option value="<?=$res_type->fields['PK_NOTE_TYPE']?>" <? if($res_type->fields['PK_NOTE_TYPE'] == $PK_NOTE_TYPE) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
																<?	$res_type->MoveNext();
																} /* Ticket # 1690  */?>
															</select>
														</div>
														<span class="bar"></span> 
														<label for="PK_NOTE_TYPE">
															<? if($_GET['event'] == 1) echo EVENT_TYPE; else echo NOTES_TYPE; ?>
														</label>
													</div>
												</div>
												
												<div class="col-md-3">
													<div class="form-group m-b-40">
														<div id="PK_NOTE_STATUS_DIV" >
															<select id="PK_NOTE_STATUS" name="PK_NOTE_STATUS" class="form-control required-entry">
																<option></option>
																<? $cond = " AND TYPE = 2 ";
																if($_GET['event'] == 1)
																	$cond = " AND TYPE = 3 ";
																	
																$PK_DEPARTMENT = get_department_from_t($_GET['t']);	
																$cond .= " AND (PK_DEPARTMENT = '$PK_DEPARTMENT' OR PK_DEPARTMENT = -1) ";
																
																/* Ticket # 1690  */
																$union = "";
																if($PK_NOTE_STATUS > 0)
																	$union = " UNION select PK_NOTE_STATUS, NOTE_STATUS, ACTIVE from M_NOTE_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_NOTE_STATUS = '$PK_NOTE_STATUS' ";
																	
																$res_type = $db->Execute("SELECT * FROM( select PK_NOTE_STATUS, NOTE_STATUS, ACTIVE from M_NOTE_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond $union ) as TEMP order by ACTIVE DESC, NOTE_STATUS ASC");
																while (!$res_type->EOF) { 
																	$option_label = $res_type->fields['NOTE_STATUS'];
																	if($res_type->fields['ACTIVE'] == 0)
																		$option_label .= " (Inactive)"; ?>
																	<option value="<?=$res_type->fields['PK_NOTE_STATUS']?>" <? if($res_type->fields['PK_NOTE_STATUS'] == $PK_NOTE_STATUS) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
																<?	$res_type->MoveNext();
																} /* Ticket # 1690  */ ?>
															</select>
														</div>
														<span class="bar"></span> 
														<label for="PK_NOTE_STATUS">
															<? if($_GET['event'] == 1) echo EVENT_STATUS; else echo NOTE_STATUS;?>
														</label>
													</div>
												</div>
													
												<? if($_GET['event'] == 1) { ?>
												<div class="col-md-3">
													<div class="form-group m-b-40">
														<div id="PK_EVENT_OTHER_DIV" >
															<select id="PK_EVENT_OTHER" name="PK_EVENT_OTHER" class="form-control">
																<option></option>
																<? //Ticket # 901
																$cond = " AND (PK_DEPARTMENT = '$PK_DEPARTMENT' OR PK_DEPARTMENT = -1) ";
																
																/* Ticket #1149  */
																$union = "";
																if($PK_EVENT_OTHER > 0)
																	$union = " UNION select PK_EVENT_OTHER, CONCAT(EVENT_OTHER, ' - ',DESCRIPTION) as EVENT_OTHER, ACTIVE from M_EVENT_OTHER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EVENT_OTHER = '$PK_EVENT_OTHER' ";
																	
																$res_type = $db->Execute("SELECT * FROM(select PK_EVENT_OTHER, CONCAT(EVENT_OTHER, ' - ',DESCRIPTION) as EVENT_OTHER, ACTIVE from M_EVENT_OTHER WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 2 $cond $union) as TEMP order by ACTIVE DESC, EVENT_OTHER ASC");
																while (!$res_type->EOF) { 
																	$option_label = $res_type->fields['EVENT_OTHER'];
																	if($res_type->fields['ACTIVE'] == 0)
																		$option_label .= " (Inactive)"; ?>
																	<option value="<?=$res_type->fields['PK_EVENT_OTHER']?>" <? if($PK_EVENT_OTHER == $res_type->fields['PK_EVENT_OTHER']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
																<?	$res_type->MoveNext();
																} ?>
															</select>
														</div>
														<span class="bar"></span> 
														<label for="PK_EVENT_OTHER">
															<?=EVENT_OTHER?>
														</label>
													</div>
												</div>
												<? } ?>
												
												
												<? 
												/* DIAM - 1183 */
												if($_GET['event'] == 1) { ?>
												<div class="col-md-3">
													<div class="form-group m-b-40" id="click_id">
														<div id="PK_COMPANY_DIV" style="border-bottom: 1px solid #e9ecef !important;height: 39px; border-radius: 0px;" >
															<select id="PK_COMPANY" name="PK_COMPANY" class="form-control click_class" >
																<option></option>
																<? $res_type = $db->Execute("SELECT S_COMPANY.PK_COMPANY,S_COMPANY.COMPANY_NAME,S_COMPANY.CITY,Z_STATES.STATE_CODE,S_COMPANY.ACTIVE FROM S_COMPANY LEFT JOIN Z_STATES ON S_COMPANY.PK_STATES = Z_STATES.PK_STATES WHERE S_COMPANY.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY S_COMPANY.ACTIVE DESC, S_COMPANY.COMPANY_NAME ASC ");
																while (!$res_type->EOF) 
																{ 
																	$selected 		= "";
																	$PK_COMPANY 	= $res_type->fields['PK_COMPANY']; 
																	if($PK_COMPANY_ARR == $PK_COMPANY) {
																		$selected = 'selected';
																	}

																	$option_labels = $res_type->fields['COMPANY_NAME'];

																	if($res_type->fields['CITY'] != '')
																	{
																		$option_labels .= ' - '.$res_type->fields['CITY'];
																	}
																	if($res_type->fields['STATE_CODE'] != '')
																	{
																		$option_labels .= ', '.$res_type->fields['STATE_CODE'];
																	}

																	if($res_type->fields['ACTIVE'] == 0)
																	{
																		$option_labels .= " (Inactive)";
																	}
																	?>
																	<option value="<?=$res_type->fields['PK_COMPANY']?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) {echo "class='option_red'"; } ?>  ><?=$option_labels?></option>
																<?	$res_type->MoveNext();
																} ?>
															</select>
														</div>
														<span class="bar"></span> 
														<label for="PK_COMPANY">
															<?=COMPANY?>
														</label>
													</div>
												</div>
												<? } 
												/* End DIAM - 1183 */
												?>

											</div>
											
											<div class="row">
												
												<!--<div class="col-md-3">
													<div class="form-group m-b-40">
														<select id="PK_NOTES_PRIORITY_MASTER" name="PK_NOTES_PRIORITY_MASTER" class="form-control">
															<option></option>
															<? /*$res_type = $db->Execute("select PK_NOTES_PRIORITY_MASTER,NOTES_PRIORITY from M_NOTES_PRIORITY_MASTER WHERE ACTIVE = 1 ");
															while (!$res_type->EOF) { ?>
																<option value="<?=$res_type->fields['PK_NOTES_PRIORITY_MASTER']?>" <? if($res_type->fields['PK_NOTES_PRIORITY_MASTER'] == $PK_NOTES_PRIORITY_MASTER) echo "selected"; ?> ><?=$res_type->fields['NOTES_PRIORITY']?></option>
															<?	$res_type->MoveNext();
															}*/ ?>
														</select>
														<span class="bar"></span> 
														<label for="PK_NOTES_PRIORITY_MASTER">
															<?=NOTES_PRIORITY?>
														</label>
													</div>
												</div>-->
											</div>
											
											<div class="row">
												<div class="col-md-3">
													<div class="form-group m-b-40" id="NOTE_DATE_LABEL" >
														<input type="text" class="form-control date required-entry" id="NOTE_DATE" name="NOTE_DATE" value="<?=$NOTE_DATE?>" >
														<span class="bar"></span>
														<label for="NOTE_DATE">
															<? if($_GET['event'] == 1) echo EVENT_DATE; else echo NOTE_DATE;?>
														</label>
													</div>
												</div>
												
												<div class="col-md-3">
													<div class="form-group m-b-40" id="NOTE_TIME_LABEL" >
														<input type="text" class="form-control timepicker required-entry" id="NOTE_TIME" name="NOTE_TIME" value="<?=$NOTE_TIME?>" >
														<span class="bar"></span>
														<label for="NOTE_TIME">
															<? if($_GET['event'] == 1) echo EVENT_TIME; else echo NOTE_TIME;?>
														</label>
													</div>
												</div>
											
												<div class="col-md-3">
													<div class="form-group m-b-40">
														<input type="text" class="form-control date" id="FOLLOWUP_DATE" name="FOLLOWUP_DATE" value="<?=$FOLLOWUP_DATE?>" >
														<span class="bar"></span>
														<label for="FOLLOWUP_DATE"><?=FOLLOWUP_DATE?></label>
													</div>
												</div>
												
												<div class="col-md-3">
													<div class="form-group m-b-40">
														<input type="text" class="form-control timepicker" id="FOLLOWUP_TIME" name="FOLLOWUP_TIME" value="<?=$FOLLOWUP_TIME?>" >
														<span class="bar"></span>
														<label for="FOLLOWUP_TIME"><?=TIME?></label>
													</div>
												</div>
												
											</div>
											
											<div class="row">
												<div class="col-md-9">
													<div class="form-group m-b-40">
														<textarea class="form-control" rows="2" id="NOTES" name="NOTES"><?=$NOTES?></textarea>
														<span class="bar"></span>
														<label for="NOTES"><?=COMMENTS?></label>
													</div>
												</div>
												
												<div class="col-md-3">
													<div class="form-group m-b-40" id="click_id_pk_emp" <?=$ReadOnly?>>
														<div id="PK_EMPLOYEE_MASTER_DIV" style="border-bottom: 1px solid #e9ecef !important;height: 39px; border-radius: 0px;margin-top: 19px;" >
															<!-- Ticket # 1615 -->
															<select id="PK_EMPLOYEE_MASTER" name="PK_EMPLOYEE_MASTER" class="form-control"  for="PK_EMPLOYEE_MASTER" >
																<option></option>
																<? $PK_DEPARTMENT11 = get_department_from_t($_GET['t']);
																$emp_cond = " AND S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT = '$PK_DEPARTMENT11' ";
																
																/* Ticket # 1690  */
																$res_type = $db->Execute("SELECT * FROM (select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER,CONCAT(LAST_NAME,', ',FIRST_NAME,' [',DEPARTMENT, ']') AS NAME, '1' AS TEMP, S_EMPLOYEE_MASTER.ACTIVE from S_EMPLOYEE_MASTER,S_EMPLOYEE_DEPARTMENT,M_DEPARTMENT WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_EMPLOYEE_DEPARTMENT.PK_EMPLOYEE_MASTER AND M_DEPARTMENT.PK_DEPARTMENT = S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT $emp_cond  
																UNION 
																select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER,CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, '2' AS TEMP, S_EMPLOYEE_MASTER.ACTIVE from S_EMPLOYEE_MASTER WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ) AS TEMP GROUP BY PK_EMPLOYEE_MASTER ORDER BY ACTIVE DESC, NAME ASC ");
																while (!$res_type->EOF) { 
																	$PK_EMPLOYEE_MASTER1 = $res_type->fields['PK_EMPLOYEE_MASTER']; 
																	$NAME 				 = $res_type->fields['NAME']; 
																	
																	if($res_type->fields['TEMP'] == 2) {
																		$dep = '';
																		$res = $db->Execute("select DEPARTMENT FROM M_DEPARTMENT,S_EMPLOYEE_DEPARTMENT WHERE S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT = M_DEPARTMENT.PK_DEPARTMENT AND PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER1' ");
																		while (!$res->EOF) {
																			if($dep != '')
																				$dep .= ', ';
																				
																			$dep .= $res->fields['DEPARTMENT'];
																			$res->MoveNext();
																		}
																		
																		if($dep != '')
																			$NAME .= '['.$dep.']';
																			
																		if($res_type->fields['ACTIVE'] == 0)
																			$NAME .= ' (Inactive)';
																	} ?>
																	<option value="<?=$res_type->fields['PK_EMPLOYEE_MASTER']?>" <? if($PK_EMPLOYEE_MASTER == $res_type->fields['PK_EMPLOYEE_MASTER']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'";  ?> ><?=$NAME?></option>
																<?	$res_type->MoveNext();
																} /* Ticket # 1690  */ ?>
															</select>
															<!-- Ticket # 1615 -->
														</div>
														<span class="bar"></span> 
														<label for="PK_EMPLOYEE_MASTER">
															<?=EMPLOYEE?>
														</label>
													</div>
												</div>
											</div>
											
											<? //if($_GET['event'] != 1) { ?>
											<div class="row">
												<div class="col-md-9">
													<div class="d-flex">
														<div class="col-12 col-sm-8 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input" id="SHOW_ON_ALL_DEP" name="SHOW_ON_ALL_DEP" value="1" <? if($SHOW_ON_ALL_DEP == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="SHOW_ON_ALL_DEP"><?=SHOW_ON_ALL_DEP?></label>
														</div>
													</div>
												</div>
												
												<!-- Ticket # 1749  -->
												<? if($_GET['id'] != ''){ ?>
												<div class="col-md-2" style="flex: 0 0 12.5%; max-width: 12.5%;" >
													<div class="form-group m-b-40">
														<input type="text" class="form-control" id="CREATED_BY" value="<?=$CREATED_BY?>" disabled >
														<span class="bar"></span> 
														<label for="CREATED_BY">
															<?=CREATED_BY?>
														</label>
													</div>
												</div>
												
												<? $timezone = $_SESSION['PK_TIMEZONE'];
												if($timezone == '' || $timezone == 0) {
													$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
													$timezone = $res->fields['PK_TIMEZONE'];
													if($timezone == '' || $timezone == 0)
														$timezone = 4;
												}
												
												$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
												$date = convert_to_user_date($CREATED_ON,'m/d/Y h:i A',$res->fields['TIMEZONE'],date_default_timezone_get()); ?>
												<div class="col-md-1" style="flex: 0 0 12.5%; max-width: 12.5%;" >
													<div class="form-group m-b-40">
														<input type="text" class="form-control" id="CREATED_ON" value="<?=$date?>" disabled >
														<span class="bar"></span> 
														<label for="CREATED_ON">
															Created On
														</label>
													</div>
												</div>
												<? } ?>
												<!-- Ticket # 1749  -->
											</div>
											<? //} ?>
											
											<div class="row">	
												<? if($_GET['p'] != 'm' ){ ?>
												<div class="col-md-9">
													<div class="row">
														<div class="col-md-12">
															<a href="javascript:void(0)" onclick="add_attachment()" ><b><?=ADD_ATTACHMENTS?></b></a>
															<div id="attachments_div"> </div>
														</div>
													</div>
													<? if($_GET['id'] != ''){
														$res_type = $db->Execute("select PK_STUDENT_NOTES_DOCUMENTS,DOCUMENT_NAME,DOCUMENT_PATH from S_STUDENT_NOTES_DOCUMENTS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_NOTES = '$_GET[id]' ");
														while (!$res_type->EOF) { ?>
															<div class="row">
																<div class="col-md-10">
																	<a href="<?=aws_url($res_type->fields['DOCUMENT_PATH'])?>" target="_blank" ><?=$res_type->fields['DOCUMENT_NAME']?></a>
																</div>
																<div class="col-md-2">
																	<a href="javascript:void(0);" onclick="delete_row('<?=$res_type->fields['PK_STUDENT_NOTES_DOCUMENTS']?>','document')" title="<?=DELETE?>" class="btn"><i class="icon-trash"></i></a>
																</div>
															</div>
														<?	$res_type->MoveNext();
														}
													} ?>
												</div>
												<? } ?>
										
												<div class="col-sm-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input" id="SATISFIED" name="SATISFIED" value="1" <? if($SATISFIED == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="SATISFIED"><?=COMPLETE?></label>
														</div>
													</div>
												</div>
											</div>
										</div>
									 </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												<? 
												   if($_GET['batch'] != '')
												   {
													   ?>
															<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='batch_payment?id=<?=$_GET['batch']?>'" ><?=CANCEL?></button>
													   <?
												   }
												   else if($_GET['p'] == 'm') 
												   { 
													if($_GET['event'] == 1) 
														$t1 = 2;
													else
														$t1 = 3; ?>
													<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='student_bulk_update?t=<?=$t1?>'" ><?=CANCEL?></button>
												<? } else { ?>
													<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='student?id=<?=$_GET['sid']?>&tab=<?=$tab?>&eid=<?=$_GET['eid']?>&t=<?=$_GET['t']?>'" ><?=CANCEL?></button>
												<? } ?>
											</div>
										</div>
									</div>
                                </form>
                            </div>
                        </div>
					</div>
				</div>
				
            </div>
			
			<div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title" id="exampleModalLabel1"><?=DELETE_CONFIRMATION?></h4>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						</div>
						<div class="modal-body">
							<div class="form-group" id="delete_message" ></div>
							<input type="hidden" id="DELETE_ID" value="0" />
							<input type="hidden" id="DELETE_TYPE" value="0" />
						</div>
						<div class="modal-footer">
							<button type="button" onclick="conf_delete(1)" class="btn waves-effect waves-light btn-info"><?=YES?></button>
							<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete(0)" ><?=NO?></button>
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
	
	<script type="text/javascript">
	jQuery(document).ready(function($) { 
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
		
		$('.timepicker').inputmask(
			"hh:mm t", {
				placeholder: "HH:MM AM/PM", 
				insertMode: false, 
				showMaskOnHover: false,
				hourFormat: 12
			}
		);
		
		<? if($_GET['id'] == ''){ ?>
		timenow()
		<? } ?>

		

	});
	</script>
	
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');
		
		function add_attachment(){
			var name  =  'ATTACHMENT[]';
			var data  =  '<div class="row" >';
				data += 	'<div class="col-lg-8">';
				data += 	 	'<input type="file" name="'+name+'" multiple />';
				data += 	 '</div>';
				data += '</div>';
			jQuery(document).ready(function($) {
				$("#attachments_div").append(data);
			});
		}
		
		function delete_row(id,type){
			jQuery(document).ready(function($) {
				if(type == 'document')
					document.getElementById('delete_message').innerHTML = '<?=DELETE_MESSAGE.DOCUMENT?>?';
				
				$("#deleteModal").modal()
				$("#DELETE_ID").val(id)
				$("#DELETE_TYPE").val(type)
			});
		}
		function conf_delete(val,id){
			jQuery(document).ready(function($) {
				if(val == 1) {
					if($("#DELETE_TYPE").val() == 'document')
						window.location.href = 'student_notes?act=document_del&event=<?=$_GET['event']?>&id=<?=$_GET['id']?>&eid=<?=$_GET['eid']?>&sid=<?=$_GET['sid']?>&iid='+$("#DELETE_ID").val();
											
				} else
					$("#deleteModal").modal("hide");
			});
		}
		function timenow(){
			var now= new Date(), 
			ampm= 'am', 
			h= now.getHours(), 
			m= now.getMinutes(), 
			s= now.getSeconds();
			if(h >= 12){
				if(h > 12) h -= 12;
					ampm= 'pm';
			}

			if(m<10) m= '0'+m;
			if(s<10) s= '0'+s;
			//var t = now.toLocaleDateString('en-GB')
			var t = FixLocaleDateString(now.toLocaleDateString('en-GB'))
			var time = h + ':' + m + ' ' + ampm;
			t = t.split("/");
			//var t1 = t[2]+'-'+t[1]+'-'+t[0]+' '+time;
			//return t1; 
			
			document.getElementById('NOTE_DATE').value = t[1]+'/'+t[0]+'/'+t[2]
			document.getElementById('NOTE_TIME').value = time
			
			document.getElementById('NOTE_DATE_LABEL').classList.add("focused");
			document.getElementById('NOTE_TIME_LABEL').classList.add("focused");
		}
		function FixLocaleDateString(localeDate) {
			var newStr = "";
			for (var i = 0; i < localeDate.length; i++) {
				var code = localeDate.charCodeAt(i);
				if (code >= 47 && code <= 57) {
					newStr += localeDate.charAt(i);
				}
			}
			return newStr;
		}
		function show_no_times(val){
			if(val == -1) {
				document.getElementById('NO_OF_TIMES_DIV').style.display = 'none'
				document.getElementById('NO_OF_TIMES').value 			 = 1
			} else
				document.getElementById('NO_OF_TIMES_DIV').style.display = 'block'
		}
		
		function get_note_type(val){
			jQuery(document).ready(function($) {
				var data  = 't='+val+'&event=<?=$_GET['event']?>';
				var value = $.ajax({
					url: "ajax_get_note_type_from_department",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						document.getElementById('PK_NOTE_TYPE_DIV').innerHTML = data
						document.getElementById('PK_NOTE_TYPE').className = 'form-control required-entry';
						$('.floating-labels .form-control').on('focus blur', function (e) {
							$(this).parents('.form-group').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
						}).trigger('blur');
					}		
				}).responseText;
			});
		}
		function get_event_other(val){
			jQuery(document).ready(function($) {
				var data  = 't='+val;
				var value = $.ajax({
					url: "ajax_get_event_other_from_department",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						document.getElementById('PK_EVENT_OTHER_DIV').innerHTML = data
						$('.floating-labels .form-control').on('focus blur', function (e) {
							$(this).parents('.form-group').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
						}).trigger('blur');
					}		
				}).responseText;
			});
		}
		function get_note_status(val){
			jQuery(document).ready(function($) {
				var data  = 't='+val+'&event=<?=$_GET['event']?>';
				var value = $.ajax({
					url: "ajax_get_note_status_from_department",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						document.getElementById('PK_NOTE_STATUS_DIV').innerHTML = data
						document.getElementById('PK_NOTE_STATUS').className = 'form-control required-entry';
						$('.floating-labels .form-control').on('focus blur', function (e) {
							$(this).parents('.form-group').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
						}).trigger('blur');
					}		
				}).responseText;
			});
		}
		function get_employee(val){
			jQuery(document).ready(function($) {
				var data  = 't='+val+'&event=<?=$_GET['event']?>';
				var value = $.ajax({
					url: "ajax_get_employee_from_department",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						document.getElementById('PK_EMPLOYEE_MASTER_DIV').innerHTML = data
						document.getElementById('PK_EMPLOYEE_MASTER').className = 'form-control required-entry';
						$('.floating-labels .form-control').on('focus blur', function (e) {
							$(this).parents('.form-group').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
						}).trigger('blur');
					}		
				}).responseText;
			});
		}
	</script>

	<script src="../backend_assets/node_modules/Magnific-Popup-master/dist/jquery.magnific-popup.js"></script>
    <script src="../backend_assets/node_modules/Magnific-Popup-master/dist/jquery.magnific-popup-init.js"></script>
	
	
	<link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" rel="stylesheet" />
	<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/js/select2.min.js"></script>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			<? /* Ticket # 1593 */
			if($_GET['p'] == 'm')
			{ 
			?>
				$('#PK_EMPLOYEE_MASTER').select2();

				$(".select2").click(function(){
					$('#click_id_pk_emp').addClass("focused");
								
				});

				<? 
			}
			?>
			/* DIAM - 1183 */	
			$('#PK_COMPANY').select2();
			
			$(".select2").click(function(){
				$('#click_id').addClass("focused");
							
			});

			// var check_value_data =  $('#select2-PK_COMPANY-container span').text();
			// if(check_value_data.length == 0){
			// 	$('#click_id').removeClass("focused");
			// }

			/* End DIAM - 1183 */


		});
	</script>


</body>

</html>
