<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php"); //ticket #1064
require_once("../language/student_task.php");
require_once("get_department_from_t.php");
require_once("check_access.php");
require_once("../global/s3-client-wrapper/s3-client-wrapper.php");

$ADMISSION_ACCESS 	= check_access('ADMISSION_ACCESS');
$REGISTRAR_ACCESS 	= check_access('REGISTRAR_ACCESS');
$FINANCE_ACCESS 	= check_access('FINANCE_ACCESS');
$ACCOUNTING_ACCESS 	= check_access('ACCOUNTING_ACCESS');
$PLACEMENT_ACCESS 	= check_access('PLACEMENT_ACCESS');

$MANAGEMENT_BULK_UPDATE 	= check_access('MANAGEMENT_BULK_UPDATE');

if($ADMISSION_ACCESS != 2 && $ADMISSION_ACCESS != 3 && $REGISTRAR_ACCESS != 2 && $REGISTRAR_ACCESS != 3 && $FINANCE_ACCESS != 2 && $FINANCE_ACCESS != 3 && $ACCOUNTING_ACCESS != 2 && $ACCOUNTING_ACCESS != 3 && $PLACEMENT_ACCESS != 2 && $PLACEMENT_ACCESS != 3 && $MANAGEMENT_BULK_UPDATE != 1 ){
	header("location:../index");
	exit;
}

if($_GET['act'] == 'document_del'){
	$db->Execute("DELETE FROM S_STUDENT_TASK_DOCUMENTS WHERE PK_STUDENT_TASK_DOCUMENTS = '$_GET[iid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	header("location:student_task?id=".$_GET['id'].'&sid='.$_GET['sid'].'&eid='.$_GET['eid']);
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$SHOW_ON_ALL_DEP 	= $_POST['SHOW_ON_ALL_DEP'];
	unset($_POST['SHOW_ON_ALL_DEP']);
	
	if($_GET['sid'] != '') {
		$PK_STUDENT_MASTER 		= $_GET['sid'];
		$PK_STUDENT_ENROLLMENT 	= $_GET['eid'];
	} else {
		$PK_STUDENT_MASTER 		= $_POST['PK_STUDENT_MASTER'];
		$PK_STUDENT_ENROLLMENT 	= $_POST['PK_STUDENT_ENROLLMENT'];
	}
	
	$PK_PAYMENT_FREQUENCY 	= $_POST['PK_PAYMENT_FREQUENCY'];
	$NO_OF_TIMES 			= $_POST['NO_OF_TIMES'];
	$t 						= $_POST['t'];
	unset($_POST['PK_STUDENT_MASTER']);
	unset($_POST['PK_STUDENT_ENROLLMENT']);
	unset($_POST['PK_PAYMENT_FREQUENCY']);
	unset($_POST['NO_OF_TIMES']);
	unset($_POST['t']);
	
	$STUDENT_TASK = $_POST;
	$STUDENT_TASK['COMPLETED'] = $_POST['COMPLETED'];
	
	if($STUDENT_TASK['TASK_DATE'] != '') {
		$STUDENT_TASK['TASK_DATE'] 	= date("Y-m-d",strtotime($STUDENT_TASK['TASK_DATE']));
		$TASK_DATE					= date("Y-m-d",strtotime($STUDENT_TASK['TASK_DATE']));
	} else {
		$STUDENT_TASK['TASK_DATE'] 	= '';
		$TASK_DATE					= date("Y-m-d");
	}
	
	if($STUDENT_TASK['TASK_TIME'] != '')
		$STUDENT_TASK['TASK_TIME'] = date("H:i:s",strtotime($STUDENT_TASK['TASK_TIME']));
	else
		$STUDENT_TASK['TASK_TIME'] = '';
		
	if($STUDENT_TASK['FOLLOWUP_DATE'] != '') {
		$STUDENT_TASK['FOLLOWUP_DATE']  = date("Y-m-d",strtotime($STUDENT_TASK['FOLLOWUP_DATE']));
		$FOLLOWUP_DATE					= $STUDENT_TASK['FOLLOWUP_DATE'];
	} else {
		$STUDENT_TASK['FOLLOWUP_DATE'] 	= '';
		$FOLLOWUP_DATE					=  '';
	}
		
	if($STUDENT_TASK['FOLLOWUP_TIME'] != '')
		$STUDENT_TASK['FOLLOWUP_TIME'] = date("H:i:s",strtotime($STUDENT_TASK['FOLLOWUP_TIME']));
	else
		$STUDENT_TASK['FOLLOWUP_TIME'] = '';
		
	if($_GET['p'] == 'm'){
		$PK_DEPARTMENT = get_department_from_t($t);	
	} else {
		$PK_DEPARTMENT = get_department_from_t($_GET['t']);	
	}
	
	if($SHOW_ON_ALL_DEP == 1)
		$PK_DEPARTMENT = -1;
		
	$STUDENT_TASK['PK_DEPARTMENT'] = $PK_DEPARTMENT;
	
	$cond = "";

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
					$STUDENT_TASK['TASK_DATE'] = date("Y-m-d",strtotime($TASK_DATE));
				else {
					$STUDENT_TASK['TASK_DATE'] = date("Y-m-d",strtotime($STUDENT_TASK['TASK_DATE']." +".$ADD_FREQUENCY));
				}
				
				if($FOLLOWUP_DATE != ''){
					if($i == 1)
						$STUDENT_TASK['FOLLOWUP_DATE'] = date("Y-m-d",strtotime($FOLLOWUP_DATE));
					else {
						$STUDENT_TASK['FOLLOWUP_DATE'] = date("Y-m-d",strtotime($STUDENT_TASK['FOLLOWUP_DATE']." +".$ADD_FREQUENCY));
					}
				}
		
				$STUDENT_TASK['PK_STUDENT_ENROLLMENT']  = $PK_STUDENT_ENROLLMENT;
				$STUDENT_TASK['PK_STUDENT_MASTER']  	= $res_stud->fields['PK_STUDENT_MASTER'];
				$STUDENT_TASK['PK_ACCOUNT']   			= $_SESSION['PK_ACCOUNT'];
				$STUDENT_TASK['CREATED_BY']  			= $_SESSION['PK_USER'];
				$STUDENT_TASK['CREATED_ON']  			= date("Y-m-d H:i");
				db_perform('S_STUDENT_TASK', $STUDENT_TASK, 'insert');
			}
		}

		$_SESSION['BULK_EN'] = '';
	} else {
		if($_GET['id'] == ''){
			$STUDENT_TASK['PK_STUDENT_ENROLLMENT']  = $PK_STUDENT_ENROLLMENT;
			$STUDENT_TASK['PK_STUDENT_MASTER']  	= $PK_STUDENT_MASTER;
			$STUDENT_TASK['PK_ACCOUNT']   			= $_SESSION['PK_ACCOUNT'];
			$STUDENT_TASK['CREATED_BY']  			= $_SESSION['PK_USER'];
			$STUDENT_TASK['CREATED_ON']  			= date("Y-m-d H:i");
			db_perform('S_STUDENT_TASK', $STUDENT_TASK, 'insert');
			$PK_STUDENT_TASK = $db->insert_ID();;
		} else {
			$PK_STUDENT_TASK = $_GET['id'];
			$STUDENT_TASK['EDITED_BY']  = $_SESSION['PK_USER'];
			$STUDENT_TASK['EDITED_ON']  = date("Y-m-d H:i");
			db_perform('S_STUDENT_TASK', $STUDENT_TASK, 'update'," PK_STUDENT_TASK = '$PK_STUDENT_TASK' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		}
	}
	//echo "<pre>";print_r($_FILES);exit;
	$i = 0;
	// $file_dir_1 = '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/student/';
	$file_dir_1 = '../backend_assets/tmp_upload/';
	for($k = 0 ; $k < count($_FILES['ATTACHMENT']['name']) ; $k++){
		if($_FILES['ATTACHMENT']['name'][$i] != '') {
			$extn 			= explode(".",$_FILES['ATTACHMENT']['name'][$i]);
			$iindex			= count($extn) - 1;
			$rand_string 	= time()."_".rand(10000,99999);
			$file11			= $PK_STUDENT_MASTER.'_task_'.$rand_string.".".$extn[$iindex];	
			$extension   	= strtolower($extn[$iindex]);
			
			if($extension != "php" && $extension != "js" && $extension != "html" && $extension != "htm"  ){ 
				$newfile1    = $file_dir_1.$file11;
						
				move_uploaded_file($_FILES['ATTACHMENT']['tmp_name'][$i], $newfile1);

				// Upload file to S3 bucket
				$key_file_name = 'backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/student/'.$file11;
				$s3ClientWrapper = new s3ClientWrapper();
				$url = $s3ClientWrapper->uploadFile($key_file_name, $newfile1);
				
				// $STUDENT_TASK_DOCUMENTS['DOCUMENT_PATH'] 	= $newfile1;
				$STUDENT_TASK_DOCUMENTS['DOCUMENT_PATH'] 	= $url;
				$STUDENT_TASK_DOCUMENTS['DOCUMENT_NAME'] 	= $_FILES['ATTACHMENT']['name'][$i];
				$STUDENT_TASK_DOCUMENTS['PK_ACCOUNT']  		= $_SESSION['PK_ACCOUNT'];
				$STUDENT_TASK_DOCUMENTS['PK_STUDENT_TASK'] 	= $PK_STUDENT_TASK;
				$STUDENT_TASK_DOCUMENTS['CREATED_BY']  		= $_SESSION['PK_USER'];
				$STUDENT_TASK_DOCUMENTS['CREATED_ON']  		= date("Y-m-d H:i");
				db_perform('S_STUDENT_TASK_DOCUMENTS', $STUDENT_TASK_DOCUMENTS, 'insert');

				// delete tmp file
				unlink($newfile1);
			}
		}
		
		$i++;
	}
	
	//echo "<pre>";print_r($STUDENT_TASK);exit;
	if($_GET['p'] == 'i')
		header("location:index.php");
	else if($_GET['p'] == 'm')
		header("location:management");
	else
		header("location:student?id=".$_GET['sid'].'&tab=taskTab&t='.$_GET['t'].'&eid='.$_GET['eid']);
}
if($_GET['id'] == ''){
	$PK_DEPARTMENT		= '';
	$TASK_DATE 			= '';
	$TASK_TIME			= '';
	$PK_TASK_TYPE	 	= '';
	$PK_TASK_STATUS	 	= '';
	$PK_EMPLOYEE_MASTER	= $_SESSION['PK_EMPLOYEE_MASTER'];
	$FOLLOWUP_DATE	 	= '';
	$FOLLOWUP_TIME		= '';
	$COMPLETED	 		= '';
	$NOTES	 			= '';
	$PK_EVENT_OTHER		= '';
	$PK_NOTES_PRIORITY_MASTER = '';
} else {
	$cond = "";
	
	$res = $db->Execute("SELECT * FROM S_STUDENT_TASK WHERE PK_STUDENT_TASK = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond "); 
	if($res->RecordCount() == 0){
		header("location:student?id=".$_GET['sid'].'&tab=taskTab&t='.$_GET['t'].'&eid='.$_GET['eid']);
		exit;
	}
	
	$PK_DEPARTMENT 		= $res->fields['PK_DEPARTMENT'];
	$TASK_DATE 			= $res->fields['TASK_DATE'];
	$TASK_TIME 			= $res->fields['TASK_TIME'];
	$PK_TASK_TYPE  		= $res->fields['PK_TASK_TYPE'];
	$PK_TASK_STATUS  	= $res->fields['PK_TASK_STATUS'];
	$PK_EMPLOYEE_MASTER = $res->fields['PK_EMPLOYEE_MASTER'];
	$FOLLOWUP_DATE  	= $res->fields['FOLLOWUP_DATE'];
	$FOLLOWUP_TIME  	= $res->fields['FOLLOWUP_TIME'];
	$COMPLETED  		= $res->fields['COMPLETED'];
	$NOTES  			= $res->fields['NOTES'];
	$PK_EVENT_OTHER		= $res->fields['PK_EVENT_OTHER'];
	$PK_NOTES_PRIORITY_MASTER = $res->fields['PK_NOTES_PRIORITY_MASTER'];
	
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
	
	if($TASK_DATE != '0000-00-00')
		$TASK_DATE = date("m/d/Y",strtotime($TASK_DATE));
	else
		$TASK_DATE = '';
		
	if($FOLLOWUP_DATE != '0000-00-00')
		$FOLLOWUP_DATE = date("m/d/Y",strtotime($FOLLOWUP_DATE));
	else
		$FOLLOWUP_DATE = '';
		
	if($TASK_TIME != '00:00:00')
		$TASK_TIME = date("h:i A",strtotime($TASK_TIME));
	else
		$TASK_TIME = '';
		
	if($FOLLOWUP_TIME != '00:00:00')
		$FOLLOWUP_TIME = date("h:i A",strtotime($FOLLOWUP_TIME));
	else
		$FOLLOWUP_TIME = '';
		
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
		header("location:student?id=".$_GET['sid'].'&tab=taskTab&t='.$_GET['t'].'&eid='.$_GET['eid']);
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

	$res_note = $db->Execute("select NOTES,DEPARTMENT FROM S_STUDENT_NOTES LEFT JOIN M_DEPARTMENT ON M_DEPARTMENT.PK_DEPARTMENT = S_STUDENT_NOTES.PK_DEPARTMENT, M_NOTE_TYPE WHERE PK_NOTE_TYPE_MASTER = 1 AND S_STUDENT_NOTES.PK_NOTE_TYPE = M_NOTE_TYPE.PK_NOTE_TYPE AND S_STUDENT_NOTES.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_NOTES.PK_STUDENT_MASTER = '$_GET[sid]' AND PK_STUDENT_ENROLLMENT = '$_GET[eid]' AND SATISFIED = 0 ");
		
	if($res_note->RecordCount() > 0) {
		$has_warning_notes = 1;
		$warning_notes = '';
		while (!$res_note->EOF){
			if($warning_notes != '')
				$warning_notes .= ', ';
				
			$warning_notes .= 'See '.$res_note->fields['DEPARTMENT'];
			$res_note->MoveNext();
		}
		$warning_notes = 'Warning - '.$warning_notes;
	}	
	$res_probation = $db->Execute("select PK_STUDENT_PROBATION FROM S_STUDENT_PROBATION WHERE PK_PROBATION_STATUS = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_GET[sid]' AND PK_STUDENT_ENROLLMENT = '$_GET[eid]' ");
	if($res_probation->RecordCount() > 0) {
		$has_warning_notes = 1;
		if($warning_notes != '')
			$warning_notes .= '<br />';
			
		$warning_notes .= 'On Probation';
	}
}
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
	<title><?=TASK_PAGE_TITLE?> | <?=$title?></title>
	
	<!-- Ticket # 1615 -->
	<style>
	option.option_red {
		color: red !important;
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
                 <div class="row page-titles" <? if($has_warning_notes == 1){ ?> style="background-color: #d12323 !important;color: #fff;" <? } ?> >
					<? if($_GET['sid'] == ''){ ?>
					<div class="col-md-1 align-self-center">
                        <h4 class="text-themecolor" <? if($has_warning_notes == 1){ ?> style="color: #fff;" <? } ?> ><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=TASK_PAGE_TITLE?> </h4>
                    </div>
					<? } else { ?>
					<div class="col-md-8 align-self-center" style="flex: 0 0 65.0%;max-width: 65.0%;" > <!-- ticket #1534 -->
						<table width="100%" >
							<tr>
								<td width="13%" >
									<h4 class="text-themecolor" <? if($has_warning_notes == 1){ ?> style="color: #fff;" <? } ?> ><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=TASK_PAGE_TITLE?> </h4>
									<br />
								</td>
								<td ><b ><?=$LAST_NAME.', '.$FIRST_NAME.' '.$MIDDLE_NAME?></b><br /><br /></td><!-- Ticket # 1715 -->
								<td colspan="3" valign="top" ><?=$warning_notes?></td>
							</tr>
							<!-- ticket #1537 -->
							<tr>
								<td rowspan="5" >
									<? if($IMAGE != '') { ?>
										<div class="row el-element-overlay" style="width: 100%;" >
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
					
					<? } ?>
					<div class="col-md-4 align-self-center" style="flex: 0 0 35.0%;max-width: 35.0%;" > <!-- ticket #1534 -->
						<? if($_GET['p'] == 'i'){ ?>
						<div class="row">
							<!-- Ticket # 1271 -->
							<div class="col-md-12 align-self-center" style="text-align:right" >
								<? if($ADMISSION_ACCESS != 0){ ?>
									<a href="student?t=1&id=<?=$_GET['sid']?>&eid=<?=$_GET['eid']?>&tab=noteTab" class="btn btn-info m-l-5" style="margin-bottom: 10px;" ><?=MNU_ADMISSION?></a>
								<? } ?>
								<? if($REGISTRAR_ACCESS != 0){ ?>
									<a href="student?t=2&id=<?=$_GET['sid']?>&eid=<?=$_GET['eid']?>&tab=noteTab" class="btn btn-info m-l-5" style="margin-bottom: 10px;" ><?=MNU_REGISTRAR?></a>
								<? } ?>
								<? if($FINANCE_ACCESS != 0){ ?>
									<a href="student?t=3&id=<?=$_GET['sid']?>&eid=<?=$_GET['eid']?>&tab=noteTab" class="btn btn-info m-l-5" style="margin-bottom: 10px;" ><?=MNU_FINANCIAL_AID?></a>
								<? } ?>
								<? if($ACCOUNTING_ACCESS != 0){ ?>
									<a href="student?t=5&id=<?=$_GET['sid']?>&eid=<?=$_GET['eid']?>&tab=noteTab" class="btn btn-info m-l-5" style="margin-bottom: 10px;" ><?=MNU_ACCOUNTING?></a>
								<? } ?>
								<? if($PLACEMENT_ACCESS != 0){ ?>
									<a href="student?t=6&id=<?=$_GET['sid']?>&eid=<?=$_GET['eid']?>&tab=noteTab" class="btn btn-info m-l-5" style="margin-bottom: 10px;" ><?=MNU_PLACEMENT?></a>
								<? } ?>
							</div>
							<!-- Ticket # 1271 -->
						</div>
						<? } ?>
					</div>
					<!-- ticket 1116 -->
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
									<div class="row">
                                        <div class="col-md-12">
											<? if($_GET['sid'] == '' && $_GET['p'] != 'm'){ ?>
											<div class="row">
												<div class="col-md-3">
													<div class="col-12 col-sm-6 focused">
														<span class="bar"></span> 
														<label for="CAMPUS"><?=STUDENT?></label>
													</div>
													<div class="form-group m-b-40">
														<select id="PK_STUDENT_MASTER" name="PK_STUDENT_MASTER" class="form-control required-entry select2" onchange="get_enrollment(this.value)" >
															<option></option>
															<? $res_type = $db->Execute("select PK_STUDENT_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME from S_STUDENT_MASTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ARCHIVED = 0 order by CONCAT(FIRST_NAME,' ',LAST_NAME) ASC");
															while (!$res_type->EOF) { ?>
																<option value="<?=$res_type->fields['PK_STUDENT_MASTER']?>" ><?=$res_type->fields['NAME']?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
													</div>
												</div>
												
												<div class="col-md-3">
													<div class="form-group m-b-40" id="PK_STUDENT_ENROLLMENT_LBL" >
														<div id="PK_STUDENT_ENROLLMENT_DIV" >
															<select id="PK_STUDENT_ENROLLMENT" name="PK_STUDENT_ENROLLMENT" class="form-control required-entry" >
																<option></option>
															</select>
														</div>
														<span class="bar"></span> 
														<label for="PK_STUDENT_ENROLLMENT">
															<?=ENROLLMENT?>
														</label>
													</div>
												</div>
												
											</div>
											<? } 
											if($_GET['p'] == 'm'){ ?>
											<div class="row">
												<div class="col-md-3">
													<div class="form-group m-b-40">
														<select id="t" name="t" class="form-control required-entry" onchange="get_task_type(this.value); get_task_other(this.value); get_task_status(this.value); get_employee(this.value);" >
															<option selected ></option>
															<option value="1" >Admissions</option>
															<option value="2" >Registrar</option>
															<option value="3" >Finance</option>
															<option value="5" >Accounting</option>
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
												<div class="col-md-3">
													<div class="form-group m-b-40">
														<div id="PK_TASK_TYPE_DIV" >
															<select id="PK_TASK_TYPE" name="PK_TASK_TYPE" class="form-control required-entry">
																<option></option>
																<? $PK_DEPARTMENT = get_department_from_t($_GET['t']);	
																$cond = " AND (PK_DEPARTMENT = '$PK_DEPARTMENT' OR PK_DEPARTMENT = -1) ";
																
																/* Ticket #1690  */
																$union = "";
																if($PK_TASK_TYPE > 0)
																	$union = " UNION select PK_TASK_TYPE, CONCAT(TASK_TYPE, ' - ' ,DESCRIPTION) as TASK_TYPE, ACTIVE from M_TASK_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TASK_TYPE = '$PK_TASK_TYPE' ";
																	
																$res_type = $db->Execute("SELECT * FROM (select PK_TASK_TYPE, CONCAT(TASK_TYPE, ' - ' ,DESCRIPTION) as TASK_TYPE, ACTIVE from M_TASK_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond $union ) AS TEMP order by ACTIVE DESC, TASK_TYPE ASC");
																while (!$res_type->EOF) { 
																	$option_label = $res_type->fields['TASK_TYPE'];
																	if($res_type->fields['ACTIVE'] == 0)
																		$option_label .= " (Inactive)"; ?>
																	<option value="<?=$res_type->fields['PK_TASK_TYPE']?>" <? if($PK_TASK_TYPE == $res_type->fields['PK_TASK_TYPE']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
																<?	$res_type->MoveNext();
																} /* Ticket #1690  */ ?>
															</select>
														</div>
														<span class="bar"></span> 
														<label for="PK_TASK_TYPE">
															<?=TASK_TYPE?>
														</label>
													</div>
												</div>
												
												<div class="col-md-3">
													<div class="form-group m-b-40">
														<div id="PK_TASK_STATUS_DIV" >
															<? $PK_DEPARTMENT = get_department_from_t($_GET['t']);	
															$cond = " AND (PK_DEPARTMENT = '$PK_DEPARTMENT' OR PK_DEPARTMENT = -1) "; 
															
															/* Ticket #1690  */
															$union = "";
															if($PK_TASK_STATUS > 0)
																$union = " UNION select PK_TASK_STATUS, CONCAT(TASK_STATUS,' - ',DESCRIPTION) as TASK_STATUS, ACTIVE from M_TASK_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TASK_STATUS = '$PK_TASK_STATUS' "; ?>
															<select id="PK_TASK_STATUS" name="PK_TASK_STATUS" class="form-control required-entry">
																<option></option>
																<? $res_type = $db->Execute("SELECT * FROM (select PK_TASK_STATUS, CONCAT(TASK_STATUS,' - ',DESCRIPTION) as TASK_STATUS, ACTIVE from M_TASK_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond $union ) AS TEMP order by ACTIVE DESC, TASK_STATUS ASC");
																while (!$res_type->EOF) { 
																	$option_label = $res_type->fields['TASK_STATUS'];
																	if($res_type->fields['ACTIVE'] == 0)
																		$option_label .= " (Inactive)"; ?>
																	<option value="<?=$res_type->fields['PK_TASK_STATUS']?>" <? if($PK_TASK_STATUS == $res_type->fields['PK_TASK_STATUS']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
																<?	$res_type->MoveNext();
																} 
																/* Ticket #1690  */ ?>
															</select>
														</div>
														<span class="bar"></span> 
														<label for="PK_TASK_STATUS">
															<?=TASK_STATUS?>
														</label>
													</div>
												</div>
												
												<div class="col-md-3">
													<div class="form-group m-b-40">
														<div id="PK_EVENT_OTHER_DIV" >
															<? $PK_DEPARTMENT = get_department_from_t($_GET['t']);	
															$cond = " AND (PK_DEPARTMENT = '$PK_DEPARTMENT' OR PK_DEPARTMENT = -1) "; 
															
															/* Ticket #1690  */
															$union = "";
															if($PK_EVENT_OTHER > 0)
																$union = " UNION select  PK_EVENT_OTHER, CONCAT(EVENT_OTHER, ' - ' ,DESCRIPTION) as EVENT_OTHER, ACTIVE from M_EVENT_OTHER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EVENT_OTHER = '$PK_EVENT_OTHER' "; ?>
															<select id="PK_EVENT_OTHER" name="PK_EVENT_OTHER" class="form-control">
																<option></option>
																<? $res_type = $db->Execute("SELECT * FROM(select PK_EVENT_OTHER, CONCAT(EVENT_OTHER, ' - ' ,DESCRIPTION) as EVENT_OTHER, ACTIVE from M_EVENT_OTHER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 $cond $union ) as TEMP order by ACTIVE DESC, EVENT_OTHER ASC");
																while (!$res_type->EOF) { 
																	$option_label = $res_type->fields['EVENT_OTHER'];
																	if($res_type->fields['ACTIVE'] == 0)
																		$option_label .= " (Inactive)"; ?>
																	<option value="<?=$res_type->fields['PK_EVENT_OTHER']?>" <? if($PK_EVENT_OTHER == $res_type->fields['PK_EVENT_OTHER']) echo "selected"; ?>  <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$res_type->fields['EVENT_OTHER'].' - '.$option_label ?></option>
																<?	$res_type->MoveNext();
																} /* Ticket #1690  */ ?>
															</select>
														</div>
														<span class="bar"></span> 
														<label for="PK_EVENT_OTHER">
															<?=TASK_OTHER?>
														</label>
													</div>
												</div>
												
												<div class="col-md-3">
													<div class="form-group m-b-40">
														<select id="PK_NOTES_PRIORITY_MASTER" name="PK_NOTES_PRIORITY_MASTER" class="form-control">
															<option></option>
															<? /* Ticket #1149  */
															$act_type_cond = " AND ACTIVE = 1 ";
															if($PK_NOTES_PRIORITY_MASTER > 0)
																$act_type_cond = " AND (ACTIVE = 1 OR PK_NOTES_PRIORITY_MASTER = '$PK_NOTES_PRIORITY_MASTER' ) ";
																
															$res_type = $db->Execute("select PK_NOTES_PRIORITY_MASTER,NOTES_PRIORITY from M_NOTES_PRIORITY_MASTER WHERE 1 = 1 $act_type_cond ");
															while (!$res_type->EOF) { ?>
																<option value="<?=$res_type->fields['PK_NOTES_PRIORITY_MASTER']?>" <? if($res_type->fields['PK_NOTES_PRIORITY_MASTER'] == $PK_NOTES_PRIORITY_MASTER) echo "selected"; ?> ><?=$res_type->fields['NOTES_PRIORITY']?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														<label for="PK_NOTES_PRIORITY_MASTER">
															<?=PRIORITY?>
														</label>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col-md-3">
													<div class="form-group m-b-40 " id="TASK_DATE_LABEL">
														<input type="text" class="form-control required-entry date" id="TASK_DATE" name="TASK_DATE" value="<?=$TASK_DATE?>" onchange="check_date()" >
														<span class="bar"></span>
														<label for="TASK_DATE"><?=TASK_DATE?></label>
														<div id="date_error" style="color:red" ></div>
													</div>
													
												</div>
										   
												<div class="col-md-3">
													<div class="form-group m-b-40 " id="TASK_TIME_LABEL" >
														<input type="text" class="form-control required-entry timepicker" id="TASK_TIME" name="TASK_TIME" value="<?=$TASK_TIME?>" >
														<span class="bar"></span>
														<label for="TASK_TIME"><?=TASK_TIME?></label>
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
														<label for="FOLLOWUP_TIME"><?=TASK_TIME?></label>
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
													<div class="form-group m-b-40">
														<!-- Ticket # 1615 -->
														<!-- Ticket # 1690 -->
														<? 
														if($_GET['t'] != '') {
															$PK_DEPARTMENT11 = get_department_from_t($_GET['t']);
															$emp_cond = " AND S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT = '$PK_DEPARTMENT11' ";
														}
														
														$res_type = $db->Execute("SELECT * FROM (select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER,CONCAT(LAST_NAME,', ',FIRST_NAME,' [',DEPARTMENT, ']') AS NAME, '1' AS TEMP, S_EMPLOYEE_MASTER.ACTIVE from S_EMPLOYEE_MASTER,S_EMPLOYEE_DEPARTMENT,M_DEPARTMENT WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_EMPLOYEE_DEPARTMENT.PK_EMPLOYEE_MASTER AND M_DEPARTMENT.PK_DEPARTMENT = S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT $emp_cond  
														UNION 
														select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER,CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, '2' AS TEMP, S_EMPLOYEE_MASTER.ACTIVE from S_EMPLOYEE_MASTER WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ) AS TEMP GROUP BY PK_EMPLOYEE_MASTER ORDER BY ACTIVE DESC, NAME ASC ");?>
														
														<select id="PK_EMPLOYEE_MASTER" name="PK_EMPLOYEE_MASTER" class="form-control">
															<option></option>
															<? while (!$res_type->EOF) { 
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
																<option value="<?=$PK_EMPLOYEE_MASTER1?>" <? if($PK_EMPLOYEE_MASTER == $PK_EMPLOYEE_MASTER1) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'";  ?>  ><?=$NAME?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
														<!-- Ticket # 1690 -->
														<!-- Ticket # 1615 -->
														<span class="bar"></span> 
														<label for="PK_EMPLOYEE_MASTER">
															<?=EMPLOYEE?>
														</label>
													</div>
												</div>
											</div>
										</div>
									</div>
									
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
											
									<div class="row">
										<div class="col-md-9">
											<? if($_GET['p'] != 'm' ){ ?>
												<div class="row">
													<div class="col-md-12">
														<a href="javascript:void(0)" onclick="add_attachment()" ><b><?=ADD_ATTACHMENTS?></b></a>
														<div id="attachments_div"> </div>
													</div>
												</div>
												<? if($_GET['id'] != ''){
													$res_type = $db->Execute("select PK_STUDENT_TASK_DOCUMENTS,DOCUMENT_NAME,DOCUMENT_PATH from S_STUDENT_TASK_DOCUMENTS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_TASK = '$_GET[id]' ");
													while (!$res_type->EOF) { ?>
														<div class="row">
															<div class="col-md-10">
																<a href="<?=aws_url($res_type->fields['DOCUMENT_PATH'])?>" target="_blank" ><?=$res_type->fields['DOCUMENT_NAME']?></a>
															</div>
															<div class="col-md-2">
																<a href="javascript:void(0);" onclick="delete_row('<?=$res_type->fields['PK_STUDENT_TASK_DOCUMENTS']?>','document')" title="<?=DELETE?>" class="btn"><i class="icon-trash"></i></a>
															</div>
														</div>
													<?	$res_type->MoveNext();
													}
												} 
											}?>
										</div>
										
										<div class="col-md-3 custom-control custom-checkbox form-group">
											<input type="checkbox" class="custom-control-input" id="COMPLETED" name="COMPLETED" value="1" <? if($COMPLETED == 1) echo "checked"; ?> >
											<label class="custom-control-label" for="COMPLETED"><?=COMPLETED?></label>
										</div>
									</div>
								
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												
												<? if($_GET['p'] == 'i') { ?>
													<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='index.php'" ><?=CANCEL?></button>
												<? } else if($_GET['p'] == 'm') { ?>
													<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='student_bulk_update?t=4'" ><?=CANCEL?></button>
												<? } else { ?>
													<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='student?id=<?=$_GET['sid']?>&tab=taskTab&t=<?=$_GET['t']?>&eid=<?=$_GET['eid']?>'" ><?=CANCEL?></button>
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
			
			<div class="modal" id="emailModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title" id="exampleModalLabel1"><?=EMAIL?></h4>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						</div>
						<div class="modal-body">
							<div class="row form-group">
								<div class="col-md-4 align-self-center"><?=SUBJECT?></div>
								<div class="col-md-8 align-self-center">
									<input type="text" id="EMAIL_SUBJECT" name="EMAIL_SUBJECT" value="" class="form-control required-entry">
								</div>
							</div>
							
							<div class="row form-group">
								<div class="col-md-12 align-self-center"><?=MESSAGE?></div>
								<div class="col-md-12 align-self-center">
									<textarea id="EMAIL_MESSAGE" name="EMAIL_MESSAGE" class="form-control required-entry"></textarea>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<input type="hidden" id="FORM_NAME" name="FORM_NAME" value="TAKE_PAYMENT" >
							<button type="submit" class="btn waves-effect waves-light btn-info"><?=SEND?></button>
							<button type="button" class="btn waves-effect waves-light btn-dark" onclick="close_popup('emailModal')" ><?=CANCEL?></button>
						</div>
					</div>
				</div>
			</div>
			
			<div class="modal" id="textModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title" id="exampleModalLabel1"><?=TEXT?></h4>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						</div>
						<div class="modal-body">
							<div class="row form-group">
								<div class="col-md-12 align-self-center"><?=MESSAGE?></div>
								<div class="col-md-12 align-self-center">
									<textarea id="TEXT_MESSAGE" name="TEXT_MESSAGE" class="form-control required-entry"></textarea>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<input type="hidden" id="FORM_NAME" name="FORM_NAME" value="TAKE_PAYMENT" >
							<button type="submit" class="btn waves-effect waves-light btn-info"><?=SEND?></button>
							<button type="button" class="btn waves-effect waves-light btn-dark" onclick="close_popup('textModal')" ><?=CANCEL?></button>
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
						window.location.href = 'student_task?act=document_del&id=<?=$_GET['id']?>&sid=<?=$_GET['sid']?>&eid=<?=$_GET['eid']?>&iid='+$("#DELETE_ID").val();
											
				} else
					$("#deleteModal").modal("hide");
			});
		}
		
		function check_date(){
			jQuery(document).ready(function($) {
				if(document.getElementById('TASK_DATE').value != "") {
					var data  = 'date='+document.getElementById('TASK_DATE').value
					var value = $.ajax({
						url: "ajax_check_date_for_leave",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							document.getElementById('date_error').innerHTML = data;
						}		
					}).responseText;
				} else
					document.getElementById('date_error').innerHTML = '';
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
			
			document.getElementById('TASK_DATE').value = t[1]+'/'+t[0]+'/'+t[2]
			document.getElementById('TASK_TIME').value = time
			
			document.getElementById('TASK_DATE_LABEL').classList.add("focused");
			document.getElementById('TASK_TIME_LABEL').classList.add("focused");
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
		
		function get_enrollment(val){
			jQuery(document).ready(function($) {
				var data  = 'id='+val
				var value = $.ajax({
					url: "ajax_get_student_enrollment",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						document.getElementById('PK_STUDENT_ENROLLMENT_DIV').innerHTML = data;
						document.getElementById('PK_STUDENT_ENROLLMENT_LBL').classList.add("focused");
					}		
				}).responseText;
			});
		}
		function mail_popup(){
			jQuery(document).ready(function($) {
				$("#emailModal").modal()
			});
		}
		function text_popup(){
			jQuery(document).ready(function($) {
				$("#textModal").modal()
			});
		}
		function close_popup(id){
			jQuery(document).ready(function($) {
				$("#"+id).modal("hide");
			});
		}
		
		function show_no_times(val){
			if(val == -1) {
				document.getElementById('NO_OF_TIMES_DIV').style.display = 'none'
				document.getElementById('NO_OF_TIMES').value 			 = 1
			} else
				document.getElementById('NO_OF_TIMES_DIV').style.display = 'block'
		}
		
		function get_task_type(val){
			jQuery(document).ready(function($) {
				var data  = 't='+val+'&event=<?=$_GET['event']?>';
				var value = $.ajax({
					url: "ajax_get_task_type_from_department",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						document.getElementById('PK_TASK_TYPE_DIV').innerHTML = data
						document.getElementById('PK_TASK_TYPE').className = 'form-control required-entry';
						$('.floating-labels .form-control').on('focus blur', function (e) {
							$(this).parents('.form-group').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
						}).trigger('blur');
					}		
				}).responseText;
			});
		}
		function get_task_status(val){
			jQuery(document).ready(function($) {
				var data  = 't='+val+'&event=<?=$_GET['event']?>';
				var value = $.ajax({
					url: "ajax_get_task_status_from_department",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						document.getElementById('PK_TASK_STATUS_DIV').innerHTML = data
						document.getElementById('PK_TASK_STATUS').className = 'form-control required-entry';
						$('.floating-labels .form-control').on('focus blur', function (e) {
							$(this).parents('.form-group').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
						}).trigger('blur');
					}		
				}).responseText;
			});
		}
		
		function get_task_other(val){
			jQuery(document).ready(function($) {
				var data  = 't='+val;
				var value = $.ajax({
					url: "ajax_get_task_other_from_department",	
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
			$('.select2').select2();
			<? /* Ticket # 1593 */
			if($_GET['p'] == 'm'){ ?>
				$('#PK_EMPLOYEE_MASTER').select2();
			<? }
			/* Ticket # 1593 */ ?>
		});
	</script>
</body>

</html>