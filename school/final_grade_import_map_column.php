<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/course_offering.php");
require_once("../language/final_grade_input.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

include '../global/excel/Classes/PHPExcel/IOFactory.php'; 

$msg 	= '';
$error 	= array();
$flag 	= 0;
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$res = $db->Execute("select MATCH_BY FROM S_FINAL_GRADE_IMPORT WHERE PK_FINAL_GRADE_IMPORT = '$_GET[iid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$MATCH_BY = $res->fields['MATCH_BY'];

	$i 		= 0;
	$flag 	= 1;
	foreach($_POST['FIELDS'] as $FIELDS ){
		$EXCEL_COLUMN = $_POST['EXCEL_COLUMN'][$i];
		if($FIELDS != '') {
			$MAP_DETAIL['TABLE_COLUMN'] = $FIELDS;
			db_perform('Z_EXCEL_MAP_DETAIL', $MAP_DETAIL, 'update'," PK_MAP_MASTER = '$_GET[id]' AND EXCEL_COLUMN = '$EXCEL_COLUMN' ");
		} else {
			$db->Execute("DELETE FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND EXCEL_COLUMN = '$EXCEL_COLUMN' ");
		}		
		$i++;
	}
	
	$db->Execute("DELETE FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = '' ");
		
	$res = $db->Execute("SELECT FILE_LOCATION,HEADING_ROW_NO FROM Z_EXCEL_MAP_MASTER WHERE PK_MAP_MASTER = '$_GET[id]' ");
	$newfile1 = $res->fields['FILE_LOCATION'];

	if ($newfile1 != ""){
		$extn = explode(".",$newfile1);
		$ii = count($extn) - 1;

		if(strtolower($extn[$ii]) == 'xlsx' || strtolower($extn[$ii]) == 'xls' || strtolower($extn[$ii]) == 'csv'){
			$inputFileName = $newfile1;
			
			if(strtolower($extn[$ii]) == 'csv'){
				$inputFileType = 'CSV';
				$objReader = PHPExcel_IOFactory::createReader($inputFileType);
				$objPHPExcel = $objReader->load($inputFileName);
				$objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
			}else{
				//echo $inputFileName.'--';exit;	
				$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
			}
			$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
		}
		
		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'CAMPUS' ");
		$CAMPUS_COL = $res->fields['EXCEL_COLUMN'];
		
		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'TERM' ");
		$TERM_COL = $res->fields['EXCEL_COLUMN'];
		
		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'COURSE_CODE' ");
		$COURSE_CODE_COL = $res->fields['EXCEL_COLUMN'];
		
		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'EXTERNAL_ID' ");
		$EXTERNAL_ID_COL = $res->fields['EXCEL_COLUMN'];
		
		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'SESSION' ");
		$SESSION_COL = $res->fields['EXCEL_COLUMN'];
		
		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'SESSION_NO' ");
		$SESSION_NO_COL = $res->fields['EXCEL_COLUMN'];
		
		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'STUDENT_ID' ");
		$STUDENT_ID_COL = $res->fields['EXCEL_COLUMN'];
		
		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'BADGE_ID' ");
		$BADGE_ID_COL = $res->fields['EXCEL_COLUMN'];
		
		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'FINAL_GRADE' ");
		$FINAL_GRADE_COL = $res->fields['EXCEL_COLUMN'];
		
		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'FINAL_NUMERIC_GRADE' ");
		$FINAL_NUMERIC_GRADE_COL = $res->fields['EXCEL_COLUMN'];
		
		$i = 0;
		$imported_count = 0;
		$total_count	= 0;
		foreach($sheetData as $row ){
			if($_POST['EXCLUDE_FIRST_ROW'] == 1){
				if($i == 0) {
					$i++;
					continue;
				}
			}

			$CAMPUS 		= trim($row[$CAMPUS_COL]);
			$STUDENT_ID 	= trim($row[$STUDENT_ID_COL]);
			$BADGE_ID 		= trim($row[$BADGE_ID_COL]);
			
			$MESSAGE = "";
		
			$COURSE_CODE 			= trim($row[$COURSE_CODE_COL]);
			$SESSION 				= trim($row[$SESSION_COL]);
			$SESSION_NO 			= trim($row[$SESSION_NO_COL]);
			$TERM 					= trim($row[$TERM_COL]);
			$FINAL_GRADE 			= trim($row[$FINAL_GRADE_COL]);
			$FINAL_NUMERIC_GRADE	= trim($row[$FINAL_NUMERIC_GRADE_COL]);
			$EXTERNAL_ID			= trim($row[$EXTERNAL_ID_COL]);
			
			$PK_CAMPUS					= '';
			$PK_COURSE					= '';
			$PK_SESSION					= '';
			$PK_TERM_MASTER				= '';
			$PK_COURSE_OFFERING 		= '';
			$PK_STUDENT_MASTER			= '';
			$PK_STUDENT_ENROLLMENT		= '';
			$PK_STUDENT_COURSE			= '';
			
			$stud_cond = "";
			if($STUDENT_ID != '')
				$stud_cond = " AND STUDENT_ID = '$STUDENT_ID' AND STUDENT_ID != '' ";
			else
				$stud_cond = " AND BADGE_ID = '$BADGE_ID' AND BADGE_ID != ''  ";
				
			if($MATCH_BY == 1){
				if($CAMPUS != '') {
					$res = $db->Execute("select PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TRIM(CAMPUS_CODE) = '$CAMPUS' ");
					if($res->RecordCount() == 0)
						$MESSAGE .= CAMPUS.' <b>'.$CAMPUS.'</b> Not Found<br />';
					else
						$PK_CAMPUS = $res->fields['PK_CAMPUS'];
				} else
					$MESSAGE .= ' <b>'.CAMPUS.' Empty</b><br />';
					
				if($TERM != '') {
					$PK_TERM_MASTER = str_replace("/","-",$TERM);
					$PK_TERM_MASTER = explode("-",$PK_TERM_MASTER);
					if($PK_TERM_MASTER[2] < 2000)
						$year = 2000 + $PK_TERM_MASTER[2];
					else
						$year = $PK_TERM_MASTER[2];
					
					$PK_TERM_MASTER = date("Y-m-d",strtotime($year.'-'.$PK_TERM_MASTER[0].'-'.$PK_TERM_MASTER[1]));
				
					$res_l = $db->Execute("select PK_TERM_MASTER from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BEGIN_DATE = '$PK_TERM_MASTER' ");
					if($res_l->RecordCount() == 0) {
						$MESSAGE .= TERM.' <b>'.$TERM.' Not Found</b><br />';
					} else {
						$PK_TERM_MASTER_ARR = array();
						while (!$res_l->EOF) { 
							$PK_TERM_MASTER_ARR[] = $res_l->fields['PK_TERM_MASTER'];
							$res_l->MoveNext();
						}
						$PK_TERM_MASTER = implode(",",$PK_TERM_MASTER_ARR);
					}
				} else 
					$MESSAGE .= 'Term Empty<br />';
				
				if($COURSE_CODE != '') {
					$res = $db->Execute("select PK_COURSE from S_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TRIM(COURSE_CODE) = '$COURSE_CODE' ");
					if($res->RecordCount() == 0)
						$MESSAGE .= COURSE_CODE.' <b>'.$COURSE_CODE.' Not Found</b><br />';
					else
						$PK_COURSE = $res->fields['PK_COURSE'];
				} else
					$MESSAGE .= ' <b>'.COURSE_CODE.' Empty</b><br />';

				if($SESSION != '') {
					$res = $db->Execute("select PK_SESSION from M_SESSION WHERE (TRIM(SESSION) = '$SESSION' OR SUBSTRING(TRIM(SESSION), 1, 1) = '$SESSION') AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
					if($res->RecordCount() == 0)
						$MESSAGE .= SESSION.' <b>'.$SESSION.' Not Found</b><br />';
					else
						$PK_SESSION = $res->fields['PK_SESSION'];
				} else
					$MESSAGE .= ' <b>'.SESSION.' Empty</b><br />';
					
				if($SESSION_NO == '') {
					$MESSAGE .= ' <b>'.SESSION_NO.' Empty</b><br />';
				}
					
				if($PK_CAMPUS > 0 && $PK_TERM_MASTER != '' && $PK_COURSE != '' && $PK_SESSION > 0  && $SESSION_NO != '') {
					$res = $db->Execute("select PK_COURSE_OFFERING from S_COURSE_OFFERING WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS = '$PK_CAMPUS' AND PK_TERM_MASTER IN ($PK_TERM_MASTER) AND PK_COURSE = '$PK_COURSE' AND PK_SESSION = '$PK_SESSION'  AND SESSION_NO = '$SESSION_NO' ");

					if($res->RecordCount() == 0)
						$MESSAGE .= COURSE_OFFERING.' Not Found<br />';
					else
						$PK_COURSE_OFFERING = $res->fields['PK_COURSE_OFFERING'];
				} else {
					$MESSAGE .= COURSE_OFFERING.' Not Found<br />';
				}
			} else {
				if($EXTERNAL_ID == '') {
					$MESSAGE .= ' <b>'.EXTERNAL_ID.' Empty</b><br />';
				} else {
					$res = $db->Execute("select PK_COURSE_OFFERING from S_COURSE_OFFERING WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND CO_EXTERNAL_ID = '$EXTERNAL_ID' ");
					if($res->RecordCount() == 0)
						$MESSAGE .= EXTERNAL_ID.' <b>'.$EXTERNAL_ID.' Not Found</b><br />';
					else
						$PK_COURSE_OFFERING = $res->fields['PK_COURSE_OFFERING'];
				}
			}	

			if($STUDENT_ID == "" && $BADGE_ID == "")
				$MESSAGE .= "Student ID/ Badge ID Not Found<br />";
			else {
				$res = $db->Execute("select S_STUDENT_MASTER.PK_STUDENT_MASTER from S_STUDENT_MASTER, S_STUDENT_ACADEMICS WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER $stud_cond ");
				if($res->RecordCount() == 0) {
					if($STUDENT_ID_COL != '')
						$MESSAGE .= STUDENT_ID.' <b>'.$STUDENT_ID.' Not Found</b><br />';
					else
						$MESSAGE .= BADGE_ID.' <b>'.$STUDENT_ID.' Not Found</b><br />';
				} else if($res->RecordCount() > 1) {
					if($STUDENT_ID_COL != '')
						$MESSAGE .= "Multiple ".STUDENT_ID.' <b>'.$STUDENT_ID.' Found</b><br />';
					else
						$MESSAGE .= "Multiple ".BADGE_ID.' <b>'.$STUDENT_ID.' Found</b><br />';
				} else {
					$PK_STUDENT_MASTER = $res->fields['PK_STUDENT_MASTER'];
				}
			}
		
			if($PK_COURSE_OFFERING > 0 && $PK_STUDENT_MASTER > 0) {
				$res = $db->Execute("select PK_STUDENT_COURSE, PK_STUDENT_ENROLLMENT from S_STUDENT_COURSE, S_STUDENT_MASTER WHERE S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_COURSE_OFFERING > 0 AND S_STUDENT_COURSE.PK_STUDENT_MASTER > 0  AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_COURSE.PK_STUDENT_MASTER ");
				if($res->RecordCount() == 0)
					$MESSAGE .= 'Student Not Associated with this Course Offering<br />';
				else {
					$PK_STUDENT_COURSE 			= $res->fields['PK_STUDENT_COURSE'];
					$PK_STUDENT_ENROLLMENT 	 	= $res->fields['PK_STUDENT_ENROLLMENT'];
					
					$res_grade_check = $db->Execute("SELECT FINAL_GRADE, IS_DEFAULT FROM S_STUDENT_COURSE LEFT JOIN S_GRADE ON FINAL_GRADE = PK_GRADE WHERE PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND PK_STUDENT_COURSE > 0 AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
					if($res_grade_check->fields['FINAL_GRADE'] > 0 && $res_grade_check->fields['IS_DEFAULT'] == 0) {
						$MESSAGE .= 'Final Grade Posted<br />';
						$PK_STUDENT_COURSE = 0;
					}
				}
				
			}
			
			/*if($GRADE == '') {
				$MESSAGE .= ' <b>'.GRADE.' Empty</b><br />';
			}*/
			
			$res_gr = $db->Execute("SELECT PK_GRADE FROM S_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TRIM(GRADE) = '$FINAL_GRADE' ORDER BY ACTIVE DESC ");
			
			$FINAL_GRADE_IMPORT_DETAIL = array();
			$FINAL_GRADE_IMPORT_DETAIL['PK_CAMPUS']   				= $PK_CAMPUS;
			$FINAL_GRADE_IMPORT_DETAIL['PK_COURSE']   				= $PK_COURSE;
			$FINAL_GRADE_IMPORT_DETAIL['PK_SESSION']   				= $PK_SESSION;
			$FINAL_GRADE_IMPORT_DETAIL['PK_TERM_MASTER']   			= $PK_TERM_MASTER;
			$FINAL_GRADE_IMPORT_DETAIL['PK_COURSE_OFFERING']   		= $PK_COURSE_OFFERING;
			$FINAL_GRADE_IMPORT_DETAIL['PK_STUDENT_MASTER']   		= $PK_STUDENT_MASTER;
			$FINAL_GRADE_IMPORT_DETAIL['PK_STUDENT_ENROLLMENT']   	= $PK_STUDENT_ENROLLMENT;
			$FINAL_GRADE_IMPORT_DETAIL['PK_STUDENT_COURSE']   		= $PK_STUDENT_COURSE;
			$FINAL_GRADE_IMPORT_DETAIL['PK_FINAL_GRADE']   			= $res_gr->fields['PK_GRADE'];
			$FINAL_GRADE_IMPORT_DETAIL['FINAL_NUMERIC_GRADE']   	= $FINAL_NUMERIC_GRADE;
			$FINAL_GRADE_IMPORT_DETAIL['EXTERNAL_ID']   			= $EXTERNAL_ID;
			$FINAL_GRADE_IMPORT_DETAIL['STUDENT_ID'] 	  			= $STUDENT_ID;
			$FINAL_GRADE_IMPORT_DETAIL['BADGE_ID'] 	  				= $BADGE_ID;
			$FINAL_GRADE_IMPORT_DETAIL['MESSAGE'] 	  				= $MESSAGE;
			$FINAL_GRADE_IMPORT_DETAIL['PK_FINAL_GRADE_IMPORT'] 	= $_GET['iid'];
			$FINAL_GRADE_IMPORT_DETAIL['PK_ACCOUNT'] 				= $_SESSION['PK_ACCOUNT'];
			db_perform('S_FINAL_GRADE_IMPORT_DETAIL', $FINAL_GRADE_IMPORT_DETAIL, 'insert');
			$imported_count++;
		}
		
		$total_count = $imported_count;
		
		$res = $db->Execute("SELECT PK_COURSE_OFFERING FROM S_FINAL_GRADE_IMPORT_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_FINAL_GRADE_IMPORT = '$_GET[iid]' AND PK_COURSE_OFFERING > 0 GROUP By PK_COURSE_OFFERING ");
		while (!$res->EOF) {
			$PK_COURSE_OFFERING = $res->fields['PK_COURSE_OFFERING'];

			$res_course_det = $db->Execute("SELECT S_STUDENT_COURSE.PK_COURSE_OFFERING, PK_STUDENT_ENROLLMENT, S_STUDENT_COURSE.PK_STUDENT_MASTER    
			FROM 
			S_STUDENT_COURSE, S_STUDENT_MASTER   
			WHERE S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
			S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_COURSE.PK_STUDENT_MASTER AND ARCHIVED = 0 AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ");
			
			while (!$res_course_det->EOF) {
				$PK_STUDENT_MASTER		= $res_course_det->fields['PK_STUDENT_MASTER'];
				$PK_STUDENT_ENROLLMENT 	= $res_course_det->fields['PK_STUDENT_ENROLLMENT'];
				$PK_COURSE_OFFERING 	= $res_course_det->fields['PK_COURSE_OFFERING'];
				
				$res_temp = $db->Execute("SELECT PK_FINAL_GRADE_IMPORT_DETAIL FROM S_FINAL_GRADE_IMPORT_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_FINAL_GRADE_IMPORT = '$_GET[iid]' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ");
				if($res_temp->RecordCount() == 0) {
					
					$res_co = $db->Execute("SELECT PK_CAMPUS, PK_COURSE, PK_SESSION, S_COURSE_OFFERING.PK_TERM_MASTER, S_COURSE_OFFERING.PK_COURSE_OFFERING, PK_STUDENT_COURSE, FINAL_GRADE, IS_DEFAULT, S_GRADE.GRADE, S_STUDENT_COURSE.NUMERIC_GRADE     
					FROM 
					S_COURSE_OFFERING, S_STUDENT_COURSE  
					LEFT JOIN S_GRADE ON FINAL_GRADE = PK_GRADE 
					WHERE 
					S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
					S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND 
					S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND  
					S_COURSE_OFFERING.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ");

					if($res_co->fields['FINAL_GRADE'] == 0 || $res_co->fields['FINAL_GRADE'] == '' || ($res_co->fields['FINAL_GRADE'] > 0 && $res_co->fields['IS_DEFAULT'] == 1) ){
						$total_count++;
						
						$FINAL_GRADE_IMPORT_DETAIL = array();
						$FINAL_GRADE_IMPORT_DETAIL['PK_CAMPUS']   				= $res_co->fields['PK_CAMPUS'];
						$FINAL_GRADE_IMPORT_DETAIL['PK_COURSE']   				= $res_co->fields['PK_COURSE'];
						$FINAL_GRADE_IMPORT_DETAIL['PK_SESSION']   				= $res_co->fields['PK_SESSION'];
						$FINAL_GRADE_IMPORT_DETAIL['PK_TERM_MASTER']   			= $res_co->fields['PK_TERM_MASTER'];
						$FINAL_GRADE_IMPORT_DETAIL['PK_COURSE_OFFERING']   		= $res_co->fields['PK_COURSE_OFFERING'];
						$FINAL_GRADE_IMPORT_DETAIL['PK_STUDENT_MASTER']   		= $PK_STUDENT_MASTER;
						$FINAL_GRADE_IMPORT_DETAIL['PK_STUDENT_ENROLLMENT']   	= $PK_STUDENT_ENROLLMENT;
						$FINAL_GRADE_IMPORT_DETAIL['PK_STUDENT_COURSE']   		= $res_co->fields['PK_STUDENT_COURSE'];;
						
						$FINAL_GRADE_IMPORT_DETAIL['PK_FINAL_GRADE']   			= $res_co->fields['FINAL_GRADE'];;
						$FINAL_GRADE_IMPORT_DETAIL['FINAL_NUMERIC_GRADE']   	= $res_co->fields['NUMERIC_GRADE'];;
						$FINAL_GRADE_IMPORT_DETAIL['STUDENT_ID'] 	  			= '';
						$FINAL_GRADE_IMPORT_DETAIL['BADGE_ID'] 	  				= '';
						$FINAL_GRADE_IMPORT_DETAIL['MESSAGE'] 	  				= 'Student not found in file';
						$FINAL_GRADE_IMPORT_DETAIL['PK_FINAL_GRADE_IMPORT'] 	= $_GET['iid'];
						$FINAL_GRADE_IMPORT_DETAIL['PK_ACCOUNT'] 				= $_SESSION['PK_ACCOUNT'];
						$FINAL_GRADE_IMPORT_DETAIL['NOT_FOUND_ON_FILE'] 		= 1;
						db_perform('S_FINAL_GRADE_IMPORT_DETAIL', $FINAL_GRADE_IMPORT_DETAIL, 'insert');
					}
				}
				
				$res_course_det->MoveNext();
			}
			
			$res->MoveNext();
		}
		
		$S_FINAL_GRADE_IMPORT_2['IMPORTED_COUNT'] 	= $imported_count;
		$S_FINAL_GRADE_IMPORT_2['TOTAL_COUNT'] 		= $total_count;
		db_perform('S_FINAL_GRADE_IMPORT', $S_FINAL_GRADE_IMPORT_2, 'update'," PK_FINAL_GRADE_IMPORT = '$_GET[iid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		
		$db->Execute("UPDATE S_FINAL_GRADE_IMPORT_DETAIL SET FOUND = 1 WHERE PK_FINAL_GRADE_IMPORT = '$_GET[iid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_COURSE > 0");

		header("location:final_grade_import_map_result?id=".$_GET['iid'].'&exclude='.$_POST['EXCLUDE_FIRST_ROW'].'&t='.$MATCH_BY);
		exit;
	}
}
$res = $db->Execute("select MATCH_BY FROM S_FINAL_GRADE_IMPORT WHERE PK_FINAL_GRADE_IMPORT = '$_GET[iid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res->RecordCount() == 0){
	header("location:management");
	exit;
}
$MATCH_BY = $res->fields['MATCH_BY'];
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
	<title><?=MNU_FINAL_GRADE_IMPORT.' '.MAPPING?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><?=MNU_FINAL_GRADE_IMPORT.' '.MAPPING?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data">
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="CAMPUS" >
														<select id="CAMPUS" name="EXCEL_COLUMN[]" class="form-control required-entry">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",CAMPUS))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')' ?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="CAMPUS"><?=CAMPUS?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="TERM" >
														<select id="SESSION" name="EXCEL_COLUMN[]" class="form-control required-entry">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",TERM))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="TERM"><?=TERM?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="COURSE_CODE" >
														<select id="COURSE_CODE" name="EXCEL_COLUMN[]" class="form-control required-entry">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",COURSE_CODE)) || strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == "coursecode") echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="COURSE_CODE"><?=COURSE_CODE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="EXTERNAL_ID" >
														<select id="EXTERNAL_ID" name="EXCEL_COLUMN[]" class="form-control required-entry">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",EXTERNAL_ID))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')' ?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="EXTERNAL_ID"><?=EXTERNAL_ID?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="SESSION" >
														<select id="SESSION" name="EXCEL_COLUMN[]" class="form-control required-entry">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",SESSION))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="SESSION"><?=SESSION?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="SESSION_NO" >
														<select id="SESSION_NO" name="EXCEL_COLUMN[]" class="form-control required-entry">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",SESSION_NO)) || strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == "sessionno" || strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == "sessionnumber" ) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="SESSION_NO"><?=SESSION_NO?></label>
													</div>
												</div>
											</div>
										
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="STUDENT_ID" >
														<select id="STUDENT_ID" name="EXCEL_COLUMN[]" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",STUDENT_ID))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')' ?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="STUDENT_ID"><?=STUDENT_ID?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="BADGE_ID" >
														<select id="BADGE_ID" name="EXCEL_COLUMN[]" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",BADGE_ID))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')' ?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="BADGE_ID"><?=BADGE_ID?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="FINAL_GRADE" >
														<select id="FINAL_GRADE" name="EXCEL_COLUMN[]" class="form-control required-entry ">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == "finalgrade" ) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')' ?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="FINAL_GRADE">Final Grade</label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="FINAL_NUMERIC_GRADE" >
														<select id="FINAL_NUMERIC_GRADE" name="EXCEL_COLUMN[]" class="form-control ">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == "finalnumericgrade" ) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')' ?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="FINAL_NUMERIC_GRADE">Final Numeric Grade</label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="d-flex" >
														<div class="col-12 col-sm-12 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input" id="EXCLUDE_FIRST_ROW" name="EXCLUDE_FIRST_ROW" value="1" >
															<label class="custom-control-label" for="EXCLUDE_FIRST_ROW"><?=EXCLUDE_FIRST_ROW ?></label>
														</div>
													</div>
												</div>
											</div>
											
										</div>
										
										<div class="col-md-6">
											<div class="row">
												<div class="col-md-2">
													<label for="MATCH_BY"><?=MATCH_ON?></label>
												</div>
												<div class="col-md-10">
													<div class="custom-control custom-radio col-md-3">
														<input type="radio" id="MATCH_BY_1" name="MATCH_BY" value="1" class="custom-control-input" <? if($MATCH_BY == 1) echo "checked"; ?> disabled >
														<label class="custom-control-label" for="MATCH_BY_1"><?=COURSE_OFFERING?></label>
													</div>
													<div class="custom-control custom-radio col-md-3">
														<input type="radio" id="MATCH_BY_2" name="MATCH_BY" value="2" class="custom-control-input" <? if($MATCH_BY == 2) echo "checked"; ?> disabled >
														<label class="custom-control-label" for="MATCH_BY_2"><?=EXTERNAL_ID?></label>
													</div>
												</div>
											</div>
										</div>
										
									</div>
									
									
									<br />
									<div class="row">
                                        <div class="col-md-4">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="button" onclick="validate_form()" name="btn" class="btn waves-effect waves-light btn-info"><?=IMPORT?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_final_grade_import_review'" ><?=CANCEL?></button>
												
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
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		
		function validate_form(){
			var valid = new Validation('form1', {onSubmit:false});
			var result = valid.validate();
			if(result == true) {
				if(document.getElementById('STUDENT_ID').value == '' && document.getElementById('BADGE_ID').value == '')
					alert('Please Select Student ID or Badge ID')
				else
					document.form1.submit();
			}
		}
	</script>

</body>

</html>
