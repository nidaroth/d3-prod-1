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

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$PK_COURSE_OFFERING = implode(",",$_POST['PK_COURSE_OFFERING']);
	
	include '../global/excel/Classes/PHPExcel/IOFactory.php';
	$cell1  = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");		
	define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

	$total_fields = 120;
	for($i = 0 ; $i <= $total_fields ; $i++){
		if($i <= 25)
			$cell[] = $cell1[$i];
		else {
			$j = floor($i / 26) - 1;
			$k = ($i % 26);
			//echo $j."--".$k."<br />";
			$cell[] = $cell1[$j].$cell1[$k];
		}	
	}
	
	$dir 			= 'temp/';
	$inputFileType  = 'Excel2007';
	$file_name 		= 'Final Grade Import Template.xlsx';
	$outputFileName = $dir.$file_name ;

	$outputFileName = str_replace(pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName);

	$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
	$objReader->setIncludeCharts(TRUE);
	//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
	$objPHPExcel = new PHPExcel();
	$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

	$line 	= 1;	
	$index 	= -1;

	$heading[] = 'Campus';
	$width[]   = 20;
	$heading[] = 'Term';
	$width[]   = 20;
	$heading[] = 'External ID';
	$width[]   = 20;
	$heading[] = 'Course Code';
	$width[]   = 20;
	$heading[] = 'Session';
	$width[]   = 20;
	$heading[] = 'Session Number';
	$width[]   = 20;
	$heading[] = 'Instructor';
	$width[]   = 20;
	$heading[] = 'Student ID';
	$width[]   = 20;
	$heading[] = 'Badge ID';
	$width[]   = 20;
	$heading[] = 'Final Grade';
	$width[]   = 20;
	$heading[] = 'Final Numeric Grade';
	$width[]   = 20;
	$heading[] = 'Student';
	$width[]   = 20;
	
	$i = 0;
	foreach($heading as $title) {
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
		$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
		$i++;
	}	

	$objPHPExcel->getActiveSheet()->freezePane('A1');
	$res = $db->Execute("select S_STUDENT_COURSE.PK_STUDENT_COURSE, S_STUDENT_MASTER.PK_STUDENT_MASTER, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, S_STUDENT_MASTER.LAST_NAME, S_STUDENT_MASTER.FIRST_NAME, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ',S_STUDENT_MASTER.FIRST_NAME, ' ', SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) AS STUD_NAME, STUDENT_ID, BADGE_ID, COURSE_CODE, COURSE_DESCRIPTION, SUBSTRING(TRIM(SESSION), 1, 1) SESSION, SESSION_NO,   IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION, CAMPUS_CODE, CO_EXTERNAL_ID, CONCAT(S_EMPLOYEE_MASTER_INST.FIRST_NAME,', ',S_EMPLOYEE_MASTER_INST.LAST_NAME) AS INSTRUCTOR_NAME, S_GRADE.GRADE, S_STUDENT_COURSE.NUMERIC_GRADE           
	from 
	S_STUDENT_MASTER 
	LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
	, S_STUDENT_ENROLLMENT 
	LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
	LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
	, S_STUDENT_COURSE 
	LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = FINAL_GRADE 
	, S_COURSE_OFFERING 
	LEFT JOIN S_EMPLOYEE_MASTER AS S_EMPLOYEE_MASTER_INST ON S_EMPLOYEE_MASTER_INST.PK_EMPLOYEE_MASTER = INSTRUCTOR 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER 
	LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
	LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
	WHERE 
	S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
	S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT AND 
	S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND S_STUDENT_COURSE.PK_COURSE_OFFERING IN ($PK_COURSE_OFFERING) 
	ORDER BY CAMPUS_CODE ASC, BEGIN_DATE ASC, COURSE_CODE ASC, SESSION ASC, SESSION_NO ASC, STUD_NAME ASC ");
	while (!$res->EOF) { 
		
		$line++;
		$index = -1;
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CAMPUS_CODE']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['BEGIN_DATE_1']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CO_EXTERNAL_ID']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['COURSE_CODE']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['SESSION']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['SESSION_NO']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['INSTRUCTOR_NAME']);

		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ID']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['BADGE_ID']);
		
		$PK_COURSE_OFFERING_GRADE 	= $res->fields['PK_COURSE_OFFERING_GRADE'];
		$PK_STUDENT_MASTER 			= $res->fields['PK_STUDENT_MASTER'];
	
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['GRADE']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NUMERIC_GRADE']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUD_NAME']);
		
		$res->MoveNext();
	}
	
	$objWriter->save($outputFileName);
	$objPHPExcel->disconnectWorksheets();
	header("location:".$outputFileName);
	exit;
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
	<title><?=FINAL_GRADE_IMPORT_TEMPLATE ?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		#advice-required-entry-PK_COURSE_OFFERING, #advice-required-entry-PK_TERM_MASTER, #advice-required-entry-PK_CAMPUS {position: absolute;top: 38px;}
		
		.dropdown-menu>li>a { white-space: nowrap; } /* Ticket # 1607 */
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><?=FINAL_GRADE_IMPORT_TEMPLATE ?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data">
							
									<div class="row">
										<div class="col-md-2" >
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control required-entry" onchange="get_term_from_campus()" >
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 " id="PK_TERM_MASTER_DIV" >
											<select id="PK_TERM_MASTER" name="PK_TERM_MASTER" class="form-control required-entry" onclick="validate_campus()" >
												<option value=""><?=TERM?></option>
											</select>
										</div>
										
										<div class="col-md-2 " id="PK_COURSE_OFFERING_DIV" >
											<select id="PK_COURSE_OFFERING" name="PK_COURSE_OFFERING" class="form-control required-entry" onclick="validate_term()" >
												<option value=""><?=COURSE_OFFERING_PAGE_TITLE?></option>
											</select>
										</div>
										
										<div class="col-md-2" >
											<div class="form-group m-b-5" >
												<button type="submit" name="btn" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button>
											</div>
										</div>
										
										<div class="col-md-4">
											<div class="row">
												<div class="col-md-12 " style="text-align:right" >
													<button type="button" onclick="window.location.href='final_grade_import'" name="btn" class="btn waves-effect waves-light btn-info"><?=GO_TO_IMPORT?></button>
												</div>
											</div>
										</div>
										
									</div>
											
									<br /><br /><br /><br /><br /><br /><br /><br /><br />
								
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
		var form1 = new Validation('form1');
		
		jQuery(document).ready(function($) {
			//get_term_from_campus()
		});
		
		function get_term_from_campus(){
			jQuery(document).ready(function($) {
				var data  = 'PK_CAMPUS='+$('#PK_CAMPUS').val();
			
				var value = $.ajax({
					url: "ajax_get_term_from_campus",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						var term_id = 'PK_TERM_MASTER';
				
						data = data.replace('id="PK_TERM_MASTER"', 'id="'+term_id+'"');
						document.getElementById(term_id+'_DIV').innerHTML 	= data;
						document.getElementById(term_id).className 			= 'required-entry';
						document.getElementById(term_id).name 				= term_id+"[]"
						document.getElementById(term_id).setAttribute('multiple', true);
						
						document.getElementById(term_id).setAttribute("onchange", "get_course_offering()");
						
						$("#"+term_id+" option[value='']").remove();
						
						$('#'+term_id).multiselect({
							includeSelectAllOption: true,
							allSelectedText: 'All <?=TERM?>',
							nonSelectedText: '<?=TERM?>',
							numberDisplayed: 2,
							nSelectedText: '<?=TERM?> selected'
						});
						
					}		
				}).responseText;
			});
		}
		
		function get_course_offering(val){
			jQuery(document).ready(function($) { 
				var data  = 'PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&dont_show_term=2'+'&PK_CAMPUS='+$('#PK_CAMPUS').val();
				var url	  = "ajax_get_course_offering_from_term"; 
				
				var value = $.ajax({
					url: url,	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						
						document.getElementById('PK_COURSE_OFFERING_DIV').innerHTML = data;
						document.getElementById('PK_COURSE_OFFERING').className 	  = 'required-entry';
						document.getElementById('PK_COURSE_OFFERING').setAttribute('multiple', true);
						document.getElementById('PK_COURSE_OFFERING').name = "PK_COURSE_OFFERING[]"
						$("#PK_COURSE_OFFERING option[value='']").remove();
						
						document.getElementById('PK_COURSE_OFFERING').setAttribute("onclick", "validate_term()");
						
						$('#PK_COURSE_OFFERING').multiselect({
							includeSelectAllOption: true,
							allSelectedText: 'All <?=COURSE_OFFERING_PAGE_TITLE?>',
							nonSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?>',
							numberDisplayed: 2,
							nSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?> selected'
						});
						
						var dd = document.getElementsByClassName('multiselect-native-select');
						for(var i = 0 ; i < dd.length ; i++){
							dd[i].style.width = '100%' ;
						}
					}		
				}).responseText;
			});
		}
		
		function get_course_details(){
		}
		
		function validate_campus(){
			jQuery(document).ready(function($) {
				if($('#PK_CAMPUS').val() == '')
					alert("Please Select Campus");
			});
		}
		
		function validate_term(){
			jQuery(document).ready(function($) {
				if($('#PK_TERM_MASTER').val() == '')
					alert("Please Select Term");
			});
		}
	</script>
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '<?=CAMPUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
	});
	</script>

</body>

</html>