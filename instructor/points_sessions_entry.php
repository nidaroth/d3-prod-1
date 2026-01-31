<? require_once("../global/config.php"); 
require_once("../school/function_calc_student_grade.php"); 
require_once("../language/common.php");
require_once("../language/dashboard.php");
require_once("../language/instructor_points_session_entry.php");

//echo "<pre>";print_r($_SESSION);exit;
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$COMPLETED_DATE = $_POST['COMPLETED_DATE'];
	if($COMPLETED_DATE != '') {
		$COMPLETED_DATE = date("Y-m-d",strtotime($COMPLETED_DATE));
	}

	$i = 0;
	foreach($_POST['PK_STUDENT_PROGRAM_GRADE_BOOK_INPUT'] as $PK_STUDENT_PROGRAM_GRADE_BOOK_INPUT){ 
		//make change on school panel student,php
		$HID = $_POST['PROGRAM_GRADE_HID'][$i];
		
		$STUDENT_PROGRAM_GRADE_BOOK_INPUT = array();
		if($_POST['VIEW'] == 2) {
			$STUDENT_PROGRAM_GRADE_BOOK_INPUT['COMPLETED_DATE'] = $_POST['PROGRAM_GRADE_COMPLETED_DATE'][$i];
			
			if($STUDENT_PROGRAM_GRADE_BOOK_INPUT['COMPLETED_DATE'] != '') {
				$STUDENT_PROGRAM_GRADE_BOOK_INPUT['COMPLETED_DATE'] = date("Y-m-d",strtotime($STUDENT_PROGRAM_GRADE_BOOK_INPUT['COMPLETED_DATE']));
			}
		} else {
			if($COMPLETED_DATE != '')
				$STUDENT_PROGRAM_GRADE_BOOK_INPUT['COMPLETED_DATE'] = $COMPLETED_DATE;
		}
		
		$STUDENT_PROGRAM_GRADE_BOOK_INPUT['SESSION_COMPLETED'] 		= $_POST['PROGRAM_GRADE_SESSION_COMPLETED'][$i];
		$STUDENT_PROGRAM_GRADE_BOOK_INPUT['HOUR_COMPLETED'] 		= $_POST['PROGRAM_GRADE_HOUR_COMPLETED'][$i];
		$STUDENT_PROGRAM_GRADE_BOOK_INPUT['POINTS_COMPLETED'] 		= $_POST['PROGRAM_GRADE_POINTS_COMPLETED'][$i];
		$STUDENT_PROGRAM_GRADE_BOOK_INPUT['SESSION_REQUIRED'] 		= $_POST['PROGRAM_GRADE_SESSION_REQUIRED'][$i];
		
		if($PK_STUDENT_PROGRAM_GRADE_BOOK_INPUT == '') {
			$PK_STUDENT_ENROLLMENT = $_POST['GRADE_PK_STUDENT_ENROLLMENT'][$i]; 
			if($PK_STUDENT_ENROLLMENT > 0) {
				
				if($_POST['VIEW'] == 2)
					$PK_GRADE_BOOK_CODE = $_POST['GRADE_PK_GRADE_BOOK_CODE'][$i];
				else
					$PK_GRADE_BOOK_CODE = $_POST['PK_GRADE_BOOK_CODE'];
					
				$res_grad = $db->Execute("select * FROM M_GRADE_BOOK_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_GRADE_BOOK_CODE = '$PK_GRADE_BOOK_CODE' ");
				$res_stud = $db->Execute("SELECT PK_STUDENT_MASTER, PK_CAMPUS_PROGRAM FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ");
				
				$STUDENT_PROGRAM_GRADE_BOOK_INPUT['PK_STUDENT_ENROLLMENT'] 	= $PK_STUDENT_ENROLLMENT; 
				$STUDENT_PROGRAM_GRADE_BOOK_INPUT['PK_STUDENT_MASTER'] 		= $res_stud->fields['PK_STUDENT_MASTER'];
				$STUDENT_PROGRAM_GRADE_BOOK_INPUT['PK_CAMPUS_PROGRAM'] 		= $res_stud->fields['PK_CAMPUS_PROGRAM'];
				$STUDENT_PROGRAM_GRADE_BOOK_INPUT['DESCRIPTION'] 			= $res_grad->fields['DESCRIPTION'];
				$STUDENT_PROGRAM_GRADE_BOOK_INPUT['PK_GRADE_BOOK_TYPE'] 	= $res_grad->fields['PK_GRADE_BOOK_TYPE'];
				$STUDENT_PROGRAM_GRADE_BOOK_INPUT['HOUR_REQUIRED'] 			= $res_grad->fields['HOUR'];
				$STUDENT_PROGRAM_GRADE_BOOK_INPUT['POINTS_REQUIRED'] 		= $res_grad->fields['POINTS'];
				$STUDENT_PROGRAM_GRADE_BOOK_INPUT['PK_GRADE_BOOK_CODE'] 	= $PK_GRADE_BOOK_CODE;
				
				$STUDENT_PROGRAM_GRADE_BOOK_INPUT['PK_ACCOUNT']  			= $_SESSION['PK_ACCOUNT'];
				$STUDENT_PROGRAM_GRADE_BOOK_INPUT['CREATED_BY']  			= $_SESSION['PK_USER'];
				$STUDENT_PROGRAM_GRADE_BOOK_INPUT['CREATED_ON']  			= date("Y-m-d H:i");
				db_perform('S_STUDENT_PROGRAM_GRADE_BOOK_INPUT', $STUDENT_PROGRAM_GRADE_BOOK_INPUT, 'insert');
			}
		} else {
			$STUDENT_PROGRAM_GRADE_BOOK_INPUT['EDITED_BY']  = $_SESSION['PK_USER'];
			$STUDENT_PROGRAM_GRADE_BOOK_INPUT['EDITED_ON']  = date("Y-m-d H:i");
			db_perform('S_STUDENT_PROGRAM_GRADE_BOOK_INPUT ', $STUDENT_PROGRAM_GRADE_BOOK_INPUT , 'update'," PK_STUDENT_PROGRAM_GRADE_BOOK_INPUT = '$PK_STUDENT_PROGRAM_GRADE_BOOK_INPUT' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		}
		$i++;
	}

	header("location:points_sessions_entry?tm=".$_POST['PK_TERM_MASTER']."&co=".$_POST['PK_COURSE_OFFERING']."&view=".$_POST['VIEW']."&pgbc=".$_POST['PK_GRADE_BOOK_CODE']."&eid=".$_POST['PK_STUDENT_ENROLLMENT']);
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
	<style>
		.table th, .table td {padding: 7px;}
		#advice-required-entry-PK_STUDENT_ENROLLMENT, #advice-required-entry-PK_GRADE_BOOK_CODE {position: absolute;top: 48px;}
	</style>
	<title><?=MNU_POINTS_SESSION_ENTRY?> | <?=$title?></title>
</head>
<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=MNU_POINTS_SESSION_ENTRY?></h4>
                    </div>
                </div>				
				<div class="card-group">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
								<form class="floating-labels w-100 m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off">
									<div class="row">
										<div class="col-sm-3 pt-25">
											<div class="row">
												<div class="col-12 form-group">
													<select id="PK_TERM_MASTER" name="PK_TERM_MASTER" class="form-control required-entry" onchange="get_course_offering(this.value)">
														<option value=""></option>
														<? $res_type = $db->Execute("select S_TERM_MASTER.PK_TERM_MASTER,IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00', DATE_FORMAT( S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') AS TERM_BEGIN_DATE, IF(S_TERM_MASTER.END_DATE != '0000-00-00', DATE_FORMAT(S_TERM_MASTER.END_DATE,'%m/%d/%Y'),'') AS TERM_END_DATE, TERM_DESCRIPTION from S_COURSE_OFFERING LEFT JOIN S_COURSE_OFFERING_ASSISTANT ON S_COURSE_OFFERING_ASSISTANT.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING  , S_TERM_MASTER WHERE S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND (INSTRUCTOR = '$_SESSION[PK_EMPLOYEE_MASTER]' OR S_COURSE_OFFERING_ASSISTANT.ASSISTANT = '$_SESSION[PK_EMPLOYEE_MASTER]') AND  S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER GROUP BY S_TERM_MASTER.PK_TERM_MASTER ORDER BY BEGIN_DATE ASC");
														while (!$res_type->EOF) { ?>
															<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" <? if($_GET['tm'] == $res_type->fields['PK_TERM_MASTER']) echo "selected"; ?> ><?=$res_type->fields['TERM_BEGIN_DATE'].' - '.$res_type->fields['TERM_END_DATE'].' - '.$res_type->fields['TERM_DESCRIPTION'] ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
													<span class="bar"></span> 
													<label for="PK_TERM_MASTER"><?=SELECT_TERM?></label>
												</div>
												<div class="col-12 form-group" id="PK_COURSE_OFFERING_LABEL" >
													<div id="PK_COURSE_OFFERING_DIV" >
														<? $_REQUEST['val'] = $_GET['tm']; 
														$_REQUEST['def'] 	= $_GET['co']; 
														include("ajax_get_course_offering.php"); ?>
													</div>
													<span class="bar"></span> 
													<label for="PK_COURSE_OFFERING"><?=SELECT_COURSE_OFFERING?></label>
												</div>
												<div class="col-12 form-group text-right">
													<button type="button" onclick="get_point_session_entry(2)" class="btn waves-effect waves-light btn-info"><?=SHOW?></button>
												</div>
											</div>
											<div id="course_details">
											</div>
										</div>
										<div class="col-sm-9 pt-25 theme-v-border" >
											<div id="FORM_DIV">
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-sm-12 pt-25" id="STUDENT_DIV">
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
	
	<script type="text/javascript">
	jQuery(document).ready(function($) { 
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
	});
	</script>
	
	<script type="text/javascript">
		jQuery(document).ready(function($) { 
			<? if($_GET['tm'] != ''){ ?>
			get_point_session_entry(<?=$_GET['view']?>)
			<? } ?>
			
			<? if($_GET['view'] != ''){ ?>
				get_point_session_entry_input(<?=$_GET['view']?>)
			<? } ?>
		});
		function get_course_offering(val){
			jQuery(document).ready(function($) { 
				var data  = 'val='+val;
				var value = $.ajax({
					url: "ajax_get_course_offering",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						document.getElementById('PK_COURSE_OFFERING_DIV').innerHTML = data;
						document.getElementById('PK_COURSE_OFFERING_LABEL').classList.add("focused");
						
					}		
				}).responseText;
			});
		}
		
		function get_point_session_entry(type){
			document.getElementById('STUDENT_DIV').innerHTML = '';
			jQuery(document).ready(function($) { 
				if(document.getElementById('PK_COURSE_OFFERING').value != '') {
					var data  = 'val='+document.getElementById('PK_COURSE_OFFERING').value+'&type='+type+'&cog=<?=$_GET['cog']?>&eid=<?=$_GET['eid']?>&pgbc=<?=$_GET['pgbc']?>';
					//alert(data)
					var value = $.ajax({
						url: "ajax_get_point_session_entry",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							//alert(data)
							document.getElementById('FORM_DIV').innerHTML = data;
							
							$('.floating-labels .form-control').on('focus blur', function (e) {
								$(this).parents('.form-group').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
							}).trigger('blur');
							
							$('.select2').select2();
							
							jQuery('.date').datepicker({
								todayHighlight: true,
								orientation: "bottom auto"
							});
						}		
					}).responseText;
				}
			});
		}
		
		function get_point_session_entry_input(type){
			jQuery(document).ready(function($) { 
				if(document.getElementById('PK_COURSE_OFFERING').value != '') {
					
					var data  = 'co='+document.getElementById('PK_COURSE_OFFERING').value+'&type='+type;
					if(type == 1)
						data += '&pgbc='+document.getElementById('PK_GRADE_BOOK_CODE').value
					else if(type == 3)
						data += '&pgbc='+document.getElementById('PK_GRADE_BOOK_CODE').value
					else if(type == 2)
						data += '&PK_STUDENT_ENROLLMENT='+document.getElementById('PK_STUDENT_ENROLLMENT').value
						
					//alert(data)
					var value = $.ajax({
						url: "ajax_get_point_session_entry_input",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							//alert(data)
							document.getElementById('STUDENT_DIV').innerHTML = data;
							
							jQuery('.date').datepicker({
								todayHighlight: true,
								orientation: "bottom auto"
							});
						}		
					}).responseText;
				}
			});
		}
		
		function get_schedule(){
			document.getElementById('FORM_DIV').innerHTML = ''
		}
		
		function calc_total(){
			var SESSION_REQUIRED 	= document.getElementsByName('PROGRAM_GRADE_SESSION_REQUIRED[]')
			var SESSION_COMPLETED 	= document.getElementsByName('PROGRAM_GRADE_SESSION_COMPLETED[]')
			var HOUR_REQUIRED 		= document.getElementsByName('PROGRAM_GRADE_HOUR_REQUIRED[]')
			var HOUR_COMPLETED 		= document.getElementsByName('PROGRAM_GRADE_HOUR_COMPLETED[]')
			var POINTS_REQUIRED 	= document.getElementsByName('PROGRAM_GRADE_POINTS_REQUIRED[]')
			var POINTS_COMPLETED 	= document.getElementsByName('PROGRAM_GRADE_POINTS_COMPLETED[]')
			
			var TOTAL_SESSION_REQUIRED 	= 0;
			var TOTAL_SESSION_COMPLETED = 0;
			var TOTAL_HOUR_REQUIRED 	= 0;
			var TOTAL_HOUR_COMPLETED 	= 0;
			var TOTAL_POINTS_REQUIRED 	= 0;
			var TOTAL_POINTS_COMPLETED 	= 0;
			
			for(var i = 0 ; i < SESSION_REQUIRED.length ; i++){
				if(SESSION_REQUIRED[i].value != '' )
					TOTAL_SESSION_REQUIRED += parseFloat(SESSION_REQUIRED[i].value)
					
				if(SESSION_COMPLETED[i].value != '' )
					TOTAL_SESSION_COMPLETED += parseFloat(SESSION_COMPLETED[i].value)
					
				if(HOUR_REQUIRED[i].value != '' )
					TOTAL_HOUR_REQUIRED += parseFloat(HOUR_REQUIRED[i].value)
					
				if(HOUR_COMPLETED[i].value != '' )
					TOTAL_HOUR_COMPLETED += parseFloat(HOUR_COMPLETED[i].value)
					
				if(POINTS_REQUIRED[i].value != '' )
					TOTAL_POINTS_REQUIRED += parseFloat(POINTS_REQUIRED[i].value)
					
				if(POINTS_COMPLETED[i].value != '' )
					TOTAL_POINTS_COMPLETED += parseFloat(POINTS_COMPLETED[i].value)
				
			}
			
			document.getElementById('SESSION_REQUIRED_DIV').innerHTML 	= TOTAL_SESSION_REQUIRED.toFixed(2);
			document.getElementById('SESSION_COMPLETED_DIV').innerHTML 	= TOTAL_SESSION_COMPLETED.toFixed(2);
			document.getElementById('HOUR_REQUIRED_DIV').innerHTML 		= TOTAL_HOUR_REQUIRED.toFixed(2);
			document.getElementById('HOUR_COMPLETED_DIV').innerHTML 	= TOTAL_HOUR_COMPLETED.toFixed(2);
			document.getElementById('POINTS_REQUIRED_DIV').innerHTML 	= TOTAL_POINTS_REQUIRED.toFixed(2);
			document.getElementById('POINTS_COMPLETED_DIV').innerHTML 	= TOTAL_POINTS_COMPLETED.toFixed(2);
		}
		
		/* Ticket # 1139  */
		var count = 1;
		function add_data(type){
			jQuery(document).ready(function($) { 
				if(type == 1 && document.getElementById('PK_GRADE_BOOK_CODE').value == ''){
					alert("Please Select Lab");
					return false
				} else if(type == 3 && document.getElementById('PK_GRADE_BOOK_CODE').value == ''){
					alert("Please Select Test");
					return false
				} else if(type == 2 && document.getElementById('PK_STUDENT_ENROLLMENT').value == ''){
					alert("Please Select Student");
					return false
				} else {
				
					var data  = 'co='+document.getElementById('PK_COURSE_OFFERING').value+'&type='+type+'&count=add_'+count;
					if(type == 1 || type == 3)
						data += '&pgbc='+document.getElementById('PK_GRADE_BOOK_CODE').value
					else if(type == 2)
						data += '&PK_STUDENT_ENROLLMENT='+document.getElementById('PK_STUDENT_ENROLLMENT').value
							
					//alert(data)
					var value = $.ajax({
						url: "ajax_add_points_sessions_entry",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							//alert(data)
							$('#program_grade_book_table tbody').append(data);
							
							jQuery('.date').datepicker({
								todayHighlight: true,
								orientation: "bottom auto"
							});
							
							count++;
						}		
					}).responseText;
				}
			});
			
		}
		function get_grade_book_code_value(val,id){
			jQuery(document).ready(function($) {
				var data = 'val='+val;
				var value = $.ajax({
					url: "../school/ajax_get_grade_book_code_value",	
					type: "POST",
					data: data,		
					async: false,
					cache :false,
					success: function (data) {
						data = data.split('|||');
						
						document.getElementById('GRADE_DESCRIPTION_DIV_'+id).innerHTML 			= data[1];
						document.getElementById('PROGRAM_GRADE_SESSION_REQUIRED_'+id).value 	= data[3];
						document.getElementById('PROGRAM_GRADE_HOUR_REQUIRED_'+id).value 		= data[2];
						document.getElementById('PROGRAM_GRADE_POINTS_REQUIRED_'+id).value 		= data[4];
						document.getElementById('GRADE_BOOK_TYPE_DIV_'+id).innerHTML 			= data[6];
					}		
				}).responseText;
			});
		}
		/* Ticket # 1139  */
	</script>
	
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');
	</script>	
	
	<link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" rel="stylesheet" />
	<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/js/select2.min.js"></script>
</body>
</html>