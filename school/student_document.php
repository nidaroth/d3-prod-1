<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php"); //ticket #1064
require_once("../language/student_document.php");
require_once("get_department_from_t.php");
require_once("check_access.php");
require_once("../global/s3-client-wrapper/s3-client-wrapper.php");

$ADMISSION_ACCESS 	= check_access('ADMISSION_ACCESS');
$REGISTRAR_ACCESS 	= check_access('REGISTRAR_ACCESS');
$FINANCE_ACCESS 	= check_access('FINANCE_ACCESS');
$ACCOUNTING_ACCESS 	= check_access('ACCOUNTING_ACCESS');
$PLACEMENT_ACCESS 	= check_access('PLACEMENT_ACCESS');

$edit_access = 0;
if(($ADMISSION_ACCESS == 2 || $ADMISSION_ACCESS == 3) && $_GET['t'] == 1)
	$edit_access = 1;
else if(($REGISTRAR_ACCESS == 2 || $REGISTRAR_ACCESS == 3) && $_GET['t'] == 2)
	$edit_access = 1;
else if(($FINANCE_ACCESS == 2 || $FINANCE_ACCESS == 3) && $_GET['t'] == 3)
	$edit_access = 1;
else if(($ACCOUNTING_ACCESS == 2 || $ACCOUNTING_ACCESS == 3) && $_GET['t'] == 5)
	$edit_access = 1;
else if(($PLACEMENT_ACCESS == 2 || $PLACEMENT_ACCESS == 3) && $_GET['t'] == 6)
	$edit_access = 1;

if($edit_access == 1 || $_SESSION['PK_ROLES'] == 2){
} else {
	header("location:../index");
	exit;
}

if($_GET['act'] == 'del')	{
	
	$res = $db->Execute("SELECT DOCUMENT_PATH FROM S_STUDENT_DOCUMENTS WHERE PK_STUDENT_DOCUMENTS = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	unlink($res->fields['DOCUMENT_PATH']);
	$db->Execute("UPDATE S_STUDENT_DOCUMENTS SET DOCUMENT_PATH = '', DOCUMENT_NAME = '', DATE_RECEIVED = '',RECEIVED=0  WHERE PK_STUDENT_DOCUMENTS = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		
	header("location:student_document?sid=".$_GET['sid'].'&id='.$_GET['id'].'&eid='.$_GET['eid']);
}	

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$PK_DEPARTMENT_ARR   = $_POST['PK_DEPARTMENT'];
	$PK_DEPARTMENT_ARR[] = get_department_from_t($_GET['t']);
	unset($_POST['PK_DEPARTMENT']);

	$STUDENT_DOCUMENTS 							= $_POST;
	$STUDENT_DOCUMENTS['RECEIVED'] 				= $_POST['RECEIVED'];
	$STUDENT_DOCUMENTS['PK_STUDENT_ENROLLMENT'] = $_POST['PK_STUDENT_ENROLLMENT'];
	
	// $file_dir_1 = '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/student/';
	$file_dir_1 = '../backend_assets/tmp_upload/';
	if($_FILES['IMAGE']['name'] != ''){
		
		$DOCUMENT_TYPE = str_replace("/","-",$_POST['DOCUMENT_TYPE']);
		$DOCUMENT_TYPE = str_replace("\\","-",$DOCUMENT_TYPE);
		$DOCUMENT_TYPE = str_replace("&","-",$DOCUMENT_TYPE);
		$DOCUMENT_TYPE = str_replace("*","-",$DOCUMENT_TYPE);
		$DOCUMENT_TYPE = str_replace(":","-",$DOCUMENT_TYPE);
		$DOCUMENT_TYPE = str_replace("?","-",$DOCUMENT_TYPE);
		$DOCUMENT_TYPE = str_replace("<","-",$DOCUMENT_TYPE);
		$DOCUMENT_TYPE = str_replace(">","-",$DOCUMENT_TYPE);
		$DOCUMENT_TYPE = str_replace("|","-",$DOCUMENT_TYPE);
		$DOCUMENT_TYPE = str_replace(" ","_",$DOCUMENT_TYPE);
		$DOCUMENT_TYPE = str_replace("=","_",$DOCUMENT_TYPE);

		$extn 			= explode(".",$_FILES['IMAGE']['name']);
		$iindex			= count($extn) - 1;
		$rand_string 	= time()."_".rand(10000,99999);
		$file11			= $_GET['sid'].'_'.$DOCUMENT_TYPE.'_'.$rand_string.".".$extn[$iindex];	
		$extension   	= strtolower($extn[$iindex]);
		
		if($extension != "php" && $extension != "js" && $extension != "html" && $extension != "htm"  ){ 
			$newfile1    = $file_dir_1.$file11;
					
			move_uploaded_file($_FILES['IMAGE']['tmp_name'], $newfile1);

			// Upload file to S3 bucket
			$key_file_name = 'backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/student/'.$file11;
			$s3ClientWrapper = new s3ClientWrapper();
			$url = $s3ClientWrapper->uploadFile($key_file_name, $newfile1);
			
			// $STUDENT_DOCUMENTS['DOCUMENT_PATH'] = $newfile1;
			$STUDENT_DOCUMENTS['DOCUMENT_PATH'] = $url;
			$STUDENT_DOCUMENTS['DOCUMENT_NAME'] = $_FILES['IMAGE']['name'];
			$STUDENT_DOCUMENTS['RECEIVED'] 		= 1;

			// delete tmp file
			unlink($newfile1);
			
			if($_POST['DATE_RECEIVED'] != '')
				$STUDENT_DOCUMENTS['DATE_RECEIVED'] = $_POST['DATE_RECEIVED'];
			else
				$STUDENT_DOCUMENTS['DATE_RECEIVED'] = date("Y-m-d");
		}
	}
	
	if($STUDENT_DOCUMENTS['RECEIVED'] != 1)
		$STUDENT_DOCUMENTS['DATE_RECEIVED'] = '';
	
	if($STUDENT_DOCUMENTS['DATE_RECEIVED'] != '')
		$STUDENT_DOCUMENTS['DATE_RECEIVED'] = date("Y-m-d",strtotime($STUDENT_DOCUMENTS['DATE_RECEIVED']));
	else
		$STUDENT_DOCUMENTS['DATE_RECEIVED'] = '';
		
	
	if($STUDENT_DOCUMENTS['REQUESTED_DATE'] != '')
		$STUDENT_DOCUMENTS['REQUESTED_DATE'] = date("Y-m-d",strtotime($STUDENT_DOCUMENTS['REQUESTED_DATE']));
	else
		$STUDENT_DOCUMENTS['REQUESTED_DATE'] = '';
		
	/*if($STUDENT_DOCUMENTS['DATE_RECEIVED'] != '')
		$STUDENT_DOCUMENTS['DATE_RECEIVED'] = date("Y-m-d",strtotime($STUDENT_DOCUMENTS['DATE_RECEIVED']));
	else
		$STUDENT_DOCUMENTS['DATE_RECEIVED'] = '';*/
		
	if($STUDENT_DOCUMENTS['FOLLOWUP_DATE'] != '')
		$STUDENT_DOCUMENTS['FOLLOWUP_DATE'] = date("Y-m-d",strtotime($STUDENT_DOCUMENTS['FOLLOWUP_DATE']));
	else
		$STUDENT_DOCUMENTS['FOLLOWUP_DATE'] = '';
	
	if($_GET['id'] == ''){
		$res_type = $db->Execute("select DOCUMENT_TYPE from M_DOCUMENT_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DOCUMENT_TYPE = '$_POST[PK_DOCUMENT_TYPE]' ");
		
		$STUDENT_DOCUMENTS['DOCUMENT_TYPE']  		= $res_type->fields['DOCUMENT_TYPE'];
		$STUDENT_DOCUMENTS['PK_ACCOUNT']  			= $_SESSION['PK_ACCOUNT'];
		$STUDENT_DOCUMENTS['PK_STUDENT_MASTER'] 	= $_GET['sid'];
		$STUDENT_DOCUMENTS['CREATED_BY']  			= $_SESSION['PK_USER'];
		$STUDENT_DOCUMENTS['CREATED_ON']  			= date("Y-m-d H:i");
		db_perform('S_STUDENT_DOCUMENTS', $STUDENT_DOCUMENTS, 'insert');
		$PK_STUDENT_DOCUMENTS = $db->insert_ID();
	} else {
		$STUDENT_DOCUMENTS['EDITED_BY'] = $_SESSION['PK_USER'];
		$STUDENT_DOCUMENTS['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('S_STUDENT_DOCUMENTS', $STUDENT_DOCUMENTS, 'update'," PK_STUDENT_DOCUMENTS = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$PK_STUDENT_DOCUMENTS = $_GET['id'];
	}
	
	foreach($PK_DEPARTMENT_ARR as $PK_DEPARTMENT) {
		$res = $db->Execute("SELECT PK_STUDENT_DOCUMENTS_DEPARTMENT FROM S_STUDENT_DOCUMENTS_DEPARTMENT WHERE PK_STUDENT_DOCUMENTS = '$PK_STUDENT_DOCUMENTS' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DEPARTMENT = '$PK_DEPARTMENT' "); 
		if($res->RecordCount() == 0) {
			$STUDENT_DOCUMENTS_DEPARTMENT['PK_DEPARTMENT']   		= $PK_DEPARTMENT;
			$STUDENT_DOCUMENTS_DEPARTMENT['PK_STUDENT_DOCUMENTS'] 	= $PK_STUDENT_DOCUMENTS;
			$STUDENT_DOCUMENTS_DEPARTMENT['PK_ACCOUNT'] 			= $_SESSION['PK_ACCOUNT'];
			$STUDENT_DOCUMENTS_DEPARTMENT['CREATED_BY']  			= $_SESSION['PK_USER'];
			$STUDENT_DOCUMENTS_DEPARTMENT['CREATED_ON']  			= date("Y-m-d H:i");
			db_perform('S_STUDENT_DOCUMENTS_DEPARTMENT', $STUDENT_DOCUMENTS_DEPARTMENT, 'insert');
			$PK_STUDENT_DOCUMENTS_DEPARTMENT_ARR[] = $db->insert_ID();
		} else {
			$PK_STUDENT_DOCUMENTS_DEPARTMENT_ARR[] = $res->fields['PK_STUDENT_DOCUMENTS_DEPARTMENT'];
		}
	}
	
	$cond = "";
	if(!empty($PK_STUDENT_DOCUMENTS_DEPARTMENT_ARR))
		$cond = " AND PK_STUDENT_DOCUMENTS_DEPARTMENT NOT IN (".implode(",",$PK_STUDENT_DOCUMENTS_DEPARTMENT_ARR).") ";
	$db->Execute("DELETE FROM S_STUDENT_DOCUMENTS_DEPARTMENT WHERE PK_STUDENT_DOCUMENTS = '$PK_STUDENT_DOCUMENTS' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond "); 
		
	//echo "<pre>";print_r($STUDENT_DOCUMENTS);exit;
	header("location:student?id=".$_GET['sid'].'&tab=documentsTab&eid='.$_GET['eid'].'&t='.$_GET['t']);
}
if($_GET['id'] == ''){
	$PK_DOCUMENT_TYPE 	= '';
	$DOCUMENT_TYPE 		= '';
	$REQUESTED_DATE 	= date("m/d/Y");
	$RECEIVED			= 0;
	$DATE_RECEIVED	 	= '';
	$FOLLOWUP_DATE	 	= '';
	$DOCUMENT_NAME	 	= '';
	$NOTES	 			= '';
	$PK_EMPLOYEE_MASTER 	= $_SESSION['PK_EMPLOYEE_MASTER'];
	$PK_STUDENT_ENROLLMENT 	= $_GET['eid'];
} else {
	$res = $db->Execute("SELECT * FROM S_STUDENT_DOCUMENTS WHERE PK_STUDENT_DOCUMENTS = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	if($res->RecordCount() == 0){
		header("location:student?id=".$_GET['sid'].'&tab=documentsTab&eid='.$_GET['eid'].'&t='.$_GET['t']);
		exit;
	}
	$PK_EMPLOYEE_MASTER 	= $res->fields['PK_EMPLOYEE_MASTER'];
	$PK_DOCUMENT_TYPE 		= $res->fields['PK_DOCUMENT_TYPE'];
	$DOCUMENT_TYPE 			= $res->fields['DOCUMENT_TYPE'];
	$REQUESTED_DATE 		= $res->fields['REQUESTED_DATE'];
	$RECEIVED  				= $res->fields['RECEIVED'];
	$DATE_RECEIVED  		= $res->fields['DATE_RECEIVED'];
	$FOLLOWUP_DATE  		= $res->fields['FOLLOWUP_DATE'];
	$DOCUMENT_NAME  		= $res->fields['DOCUMENT_NAME'];
	$DOCUMENT_PATH  		= $res->fields['DOCUMENT_PATH'];
	$NOTES  				= $res->fields['NOTES'];
	$PK_STUDENT_ENROLLMENT  = $res->fields['PK_STUDENT_ENROLLMENT'];
	
	if($REQUESTED_DATE != '0000-00-00')
		$REQUESTED_DATE = date("m/d/Y",strtotime($REQUESTED_DATE));
	else
		$REQUESTED_DATE = '';
		
	if($FOLLOWUP_DATE != '0000-00-00')
		$FOLLOWUP_DATE = date("m/d/Y",strtotime($FOLLOWUP_DATE));
	else
		$FOLLOWUP_DATE = '';
		
	if($DATE_RECEIVED != '0000-00-00')
		$DATE_RECEIVED = date("m/d/Y",strtotime($DATE_RECEIVED));
	else
		$DATE_RECEIVED = '';
}

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
	<title><?=STUDENT_DOCUMENT_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles" <? if($has_warning_notes == 1){ ?> style="background-color: #d12323 !important;color: #fff;" <? } ?> >
					<!-- ticket 1116 -->
                    
					<div class="col-md-9 align-self-center">
						<table width="100%" >
							<tr>
								<td width="13%" >
									<h4 class="text-themecolor" <? if($has_warning_notes == 1){ ?> style="color: #fff;" <? } ?> ><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=STUDENT_DOCUMENT_PAGE_TITLE?> </h4>
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
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<select id="PK_STUDENT_ENROLLMENT" name="PK_STUDENT_ENROLLMENT" class="form-control" >
													<? /* Ticket # 1691 */
													$res_type = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IS_ACTIVE_ENROLLMENT, CAMPUS_CODE FROM S_STUDENT_ENROLLMENT LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$_GET[sid]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_STUDENT_ENROLLMENT']?>" <? if($res_type->fields['PK_STUDENT_ENROLLMENT'] == $PK_STUDENT_ENROLLMENT) echo "selected"; ?> <? if($res_type->fields['IS_ACTIVE_ENROLLMENT'] == 1) echo "class='option_red'";  ?> ><?=$res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['CODE'].' - '.$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['CAMPUS_CODE']?></option>
													<?	$res_type->MoveNext();
													} /* Ticket # 1691 */ ?>
												</select>
												<span class="bar"></span>
												<label for="PK_STUDENT_ENROLLMENT"><?=ENROLLMENT?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
										<div class="col-12 col-sm-6 focused">
											<span class="bar"></span> 
											<label for="DEPARTMENT"><?=DEPARTMENT?></label>
										</div>
									</div>
									
									<div class="row">
										<div class="form-group row d-flex" >
											<? $PK_DEPARTMENT11 = get_department_from_t($_GET['t']);
											$res_type = $res_type = $db->Execute("select * from M_DEPARTMENT WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DEPARTMENT_MASTER IN (1,2,4,6,7) order by DEPARTMENT ASC");
											while (!$res_type->EOF) { ?>
												<div class="col-12 col-sm-6">
													<div class="custom-control custom-checkbox mr-sm-2 ml-sm-2">
														<? $checked = '';
														$PK_DEPARTMENT = $res_type->fields['PK_DEPARTMENT'];
														$res = $db->Execute("select PK_STUDENT_DOCUMENTS_DEPARTMENT FROM S_STUDENT_DOCUMENTS_DEPARTMENT WHERE PK_DEPARTMENT = '$PK_DEPARTMENT' AND PK_STUDENT_DOCUMENTS = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
														if($res->RecordCount() > 0 || $PK_DEPARTMENT11 == $PK_DEPARTMENT)
															$checked = 'checked'; ?>
														<input type="checkbox" class="custom-control-input" id="PK_DEPARTMENT_<?=$PK_DEPARTMENT?>" name="PK_DEPARTMENT[]" value="<?=$PK_DEPARTMENT?>" <?=$checked?> <? if($PK_DEPARTMENT11 == $PK_DEPARTMENT) echo "disabled"; ?> >
														<label class="custom-control-label" for="PK_DEPARTMENT_<?=$PK_DEPARTMENT?>" ><?=$res_type->fields['DEPARTMENT']?></label>
													</div>
												</div>
											<?	$res_type->MoveNext();
											} ?>
										</div>
									</div>
									
									<? if($PK_DOCUMENT_TYPE == '' || $PK_DOCUMENT_TYPE == 0) { ?>
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<select id="PK_DOCUMENT_TYPE" name="PK_DOCUMENT_TYPE" class="form-control required-entry">
													<option></option>
													<? /* Ticket # 1691 */
													$res_type = $db->Execute("select PK_DOCUMENT_TYPE, CONCAT(CODE, ' - ', DOCUMENT_TYPE) as DOCUMENT_TYPE, ACTIVE from M_DOCUMENT_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY ACTIVE DESC, DOCUMENT_TYPE ASC");
													while (!$res_type->EOF) { 
														$option_label = $res_type->fields['DOCUMENT_TYPE'];
														if($res_type->fields['ACTIVE'] == 0)
															$option_label .= " (Inactive)"; ?>
														<option value="<?=$res_type->fields['PK_DOCUMENT_TYPE']?>" <? if($PK_DOCUMENT_TYPE == $res_type->fields['PK_DOCUMENT_TYPE']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
													<?	$res_type->MoveNext();
													} /* Ticket # 1691 */ ?>
												</select>
												
												
												<!--<input type="text" class="form-control required-entry" id="DOCUMENT_TYPE" name="DOCUMENT_TYPE" value="<?=$DOCUMENT_TYPE?>" >
												<span class="bar"></span> -->
												<label for="PK_DOCUMENT_TYPE"><?=DOCUMENT?></label>
											</div>
										</div>
                                    </div>
									<? } else { ?>
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40 focused">
												<?=$DOCUMENT_TYPE?>
												<span class="bar"></span> 
												<label for="PK_DOCUMENT_TYPE"><?=DOCUMENT?></label>
												<input type="hidden" id="DOCUMENT_TYPE" name="DOCUMENT_TYPE" value="<?=$DOCUMENT_TYPE?>" >
											</div>
										</div>
                                    </div>
									<? } ?>
									
									<div class="row">
                                        <div class="col-md-3">
											<div class="form-group m-b-40">
												<input type="text" class="form-control required-entry date" id="REQUESTED_DATE" name="REQUESTED_DATE" value="<?=$REQUESTED_DATE?>" >
												<span class="bar"></span>
												<label for="REQUESTED_DATE"><?=REQUESTED?></label>
											</div>
										</div>
										
										<div class="col-md-3">
											<div class="form-group m-b-40">
												<input type="text" class="form-control date" id="FOLLOWUP_DATE" name="FOLLOWUP_DATE" value="<?=$FOLLOWUP_DATE?>" >
												<span class="bar"></span>
												<label for="FOLLOWUP_DATE"><?=FOLLOW_UP?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
										<div class="col-sm-3">
											<div class="d-flex">
												<div class="col-12 col-sm-3 custom-control custom-checkbox form-group" >
													<input type="checkbox" class="custom-control-input" id="RECEIVED" name="RECEIVED" value="1" <? if($RECEIVED == 1) echo "checked"; ?> onclick="show_received_date()" >
													<label class="custom-control-label" for="RECEIVED"><?=RECEIVED?></label>
												</div>
											</div>
										</div>
										<? $style = "display:none";
										if($RECEIVED == 1) 
											$style = "display:block"; ?>
										<div class="col-sm-3" style="<?=$style?>" id="DATE_RECEIVED_DIV" >
											<div class="form-group m-b-40">
												<input type="text" class="form-control date" id="DATE_RECEIVED" name="DATE_RECEIVED" value="<?=$DATE_RECEIVED?>" >
												<span class="bar"></span>
												<label for="DATE_RECEIVED"><?=DATE_RECEIVED?></label>
											</div>
										</div>
									</div>
									
									
									<!--
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control date" id="DATE_RECEIVED" name="DATE_RECEIVED" value="<?=$DATE_RECEIVED?>" >
												<span class="bar"></span>
												<label for="DATE_RECEIVED"><?=DATE_RECEIVED?></label>
											</div>
										</div>
                                    </div>
									-->
									
									<div class="row">
										<div class="col-md-6">
											<div class="form-group m-b-40">
												<select id="PK_EMPLOYEE_MASTER" name="PK_EMPLOYEE_MASTER" class="form-control">
													<option></option>
													<? $PK_DEPARTMENT11 = get_department_from_t($_GET['t']);
													$emp_cond = " AND S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT = '$PK_DEPARTMENT11' ";
													/* Ticket # 1691 */
													$res_type = $db->Execute("SELECT * FROM (select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER,CONCAT(LAST_NAME,', ',FIRST_NAME,' [',DEPARTMENT, ']') AS NAME, '1' AS TEMP, S_EMPLOYEE_MASTER.ACTIVE from S_EMPLOYEE_MASTER,S_EMPLOYEE_DEPARTMENT,M_DEPARTMENT WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_EMPLOYEE_DEPARTMENT.PK_EMPLOYEE_MASTER AND M_DEPARTMENT.PK_DEPARTMENT = S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT $emp_cond  
													UNION 
													select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER,CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, '2' AS TEMP, S_EMPLOYEE_MASTER.ACTIVE from S_EMPLOYEE_MASTER WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ) AS TEMP GROUP BY PK_EMPLOYEE_MASTER ORDER BY ACTIVE DESC, NAME ASC ");
													while (!$res_type->EOF) { 
														$option_label = $res_type->fields['NAME'];
														if($res_type->fields['ACTIVE'] == 0)
															$option_label .= " (Inactive)"; ?>
														<option value="<?=$res_type->fields['PK_EMPLOYEE_MASTER']?>" <? if($PK_EMPLOYEE_MASTER == $res_type->fields['PK_EMPLOYEE_MASTER']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
													<?	$res_type->MoveNext();
													} /* Ticket # 1691 */ ?>
												</select>
												<span class="bar"></span> 
												<label for="PK_EMPLOYEE_MASTER">
													<?=EMPLOYEE?>
												</label>
											</div>
										</div>
									</div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<textarea class="form-control" rows="2" id="NOTES" name="NOTES"><?=$NOTES?></textarea>
												<span class="bar"></span>
												<label for="NOTES"><?=NOTES?></label>
											</div>
										</div>
                                    </div>
								
									<div class="form-group">
										<label class="col-sm-2 position-relative"><?=UPLOAD?></label>
										<div class="col-sm-5">
											<? if($DOCUMENT_PATH == '') { ?>
												<input type="file" name="IMAGE" id="IMAGE" class="btn btn-default" title="Select file">
											<? } else { ?>
											<table>
												<tr>
													<td>
														<a href="<?=aws_url($DOCUMENT_PATH)?>" target="_blank" >view</a>
													</td>
													<td>
														<a data-toggle="modal" data-target="#confirm-modal" >
															<i class="icon-trash"></i>
														</a>
													</td>
												</tr>
											</table>
											<? } ?>
										</div>
									</div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='student?id=<?=$_GET['sid']?>&tab=documentsTab&eid=<?=$_GET['eid']?>&t=<?=$_GET['t']?>'" ><?=CANCEL?></button>
												
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
		
		<div id="confirm-modal" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><?=DELETE_CONFIRMATION?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <p><?=DELETE_MESSAGE.' '.DOCUMENT?></p>
                        </form>
                    </div>
                    <div class="modal-footer">
						<button type="button" onclick="conf_delete()" class="btn btn-danger waves-effect waves-light"><?=YES?></button>
                        <button type="button" class="btn btn-default waves-effect" data-dismiss="modal"><?=NO?></button>
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
	});
	</script>
	
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');
		function conf_delete(){
			jQuery(document).ready(function($) {
				window.location.href = 'student_document?act=del&id=<?=$_GET['id']?>&sid=<?=$_GET['sid']?>&eid=<?=$_GET['eid']?>';
			});	
		}
		
		function show_received_date(){
			var str = '';
			if(document.getElementById('RECEIVED').checked == true)
				str = 'block';
			else
				str = 'none';
				
			document.getElementById('DATE_RECEIVED_DIV').style.display = str
		}
	</script>

	<script src="../backend_assets/node_modules/Magnific-Popup-master/dist/jquery.magnific-popup.js"></script>
    <script src="../backend_assets/node_modules/Magnific-Popup-master/dist/jquery.magnific-popup-init.js"></script>
</body>

</html>