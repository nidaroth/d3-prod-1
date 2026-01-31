<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/dashboard.php");
require_once("../language/instructor_select_students.php");
//echo "<pre>";print_r($_SESSION);exit;
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}
if($_GET['act'] == 'del'){
	$ids = explode(",",$_GET['id']);
	foreach($ids as $PK_INSTRUCTOR_STUDENT) {
		$db->Execute("DELETE FROM S_INSTRUCTOR_STUDENT WHERE PK_INSTRUCTOR_STUDENT = '$PK_INSTRUCTOR_STUDENT' AND PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' "); 
	}
	
	header("location:select_students");
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$i = 0;
	foreach($_POST['PK_STUDENT_ENROLLMENT'] as $PK_STUDENT_ENROLLMENT){

		$res_tc = $db->Execute("SELECT PK_INSTRUCTOR_STUDENT FROM S_INSTRUCTOR_STUDENT WHERE PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' "); 
		if($res_tc->RecordCount() == 0) {
			$INSTRUCTOR_STUDENT['PK_ACCOUNT'] 			 = $_SESSION['PK_ACCOUNT'];
			$INSTRUCTOR_STUDENT['PK_EMPLOYEE_MASTER'] 	 = $_SESSION['PK_EMPLOYEE_MASTER'];
			$INSTRUCTOR_STUDENT['CREATED_BY'] 			 = $_SESSION['PK_USER'];
			$INSTRUCTOR_STUDENT['CREATED_ON'] 			 = date("Y-m-d H:i:s");
			$INSTRUCTOR_STUDENT['PK_STUDENT_ENROLLMENT'] = $PK_STUDENT_ENROLLMENT;
			db_perform('S_INSTRUCTOR_STUDENT', $INSTRUCTOR_STUDENT, 'insert' );
		}
		$i++;
	}
	header("location:select_students");
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
	<title><?=SELECT_STUDENT_PAGE_TITLE?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
	</style>
</head>
<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=SELECT_STUDENT_PAGE_TITLE?></h4>
                    </div>
                </div>				
				<div class="card-group">
                    <div class="card">
                        <div class="card-body">
							<form class="floating-labels w-100 m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off">
								<div class="row" style="padding-bottom:10px;" >
									
									<div class="col-md-2 ">
										<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control"  >
											<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS, DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by STUDENT_STATUS ASC");
											while (!$res_type->EOF) { ?>
												<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
											<?	$res_type->MoveNext();
											} ?>
										</select>
									</div>
									
									<div class="col-md-2 ">
										<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control"  >
											<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
											while (!$res_type->EOF) { ?>
												<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
											<?	$res_type->MoveNext();
											} ?>
										</select>
									</div>
								
									<div class="col-md-2 ">
										<select id="PK_TERM_MASTER" name="PK_TERM_MASTER[]" multiple class="form-control"  >
											<? $res_type = $db->Execute("select S_TERM_MASTER.PK_TERM_MASTER,IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00', DATE_FORMAT( S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') AS TERM_BEGIN_DATE from S_COURSE_OFFERING LEFT JOIN S_COURSE_OFFERING_ASSISTANT ON S_COURSE_OFFERING_ASSISTANT.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING , S_TERM_MASTER WHERE S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND (INSTRUCTOR = '$_SESSION[PK_EMPLOYEE_MASTER]' OR S_COURSE_OFFERING_ASSISTANT.ASSISTANT = '$_SESSION[PK_EMPLOYEE_MASTER]') AND  S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER GROUP BY S_TERM_MASTER.PK_TERM_MASTER ORDER BY BEGIN_DATE ASC");
											while (!$res_type->EOF) { ?>
												<option value="<?=$res_type->fields['PK_TERM_MASTER']?>"  ><?=$res_type->fields['TERM_BEGIN_DATE'] ?></option>
											<?	$res_type->MoveNext();
											} ?>
										</select>
									</div>
									
									<div class="col-md-2 ">
										<select id="PK_STUDENT_GROUP" name="PK_STUDENT_GROUP[]" multiple class="form-control"  >
											<? $res_type = $db->Execute("select PK_STUDENT_GROUP,STUDENT_GROUP from M_STUDENT_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by STUDENT_GROUP ASC");
											while (!$res_type->EOF) { ?>
												<option value="<?=$res_type->fields['PK_STUDENT_GROUP']?>" ><?=$res_type->fields['STUDENT_GROUP']?></option>
											<?	$res_type->MoveNext();
											} ?>
										</select>
									</div>
									<div class="col-md-2 ">
										<button type="button" onclick="search()" class="btn waves-effect waves-light btn-dark" ><?=SEARCH?></button>
										<button type="submit" onclick="search()" class="btn waves-effect waves-light btn-dark" ><?=ADD?></button>
									</div>
								</div>
								
								<div id="student_div">
								</div>
								
								 <div class="row page-titles">
									<div class="col-md-5 align-self-center">
										<h4 class="text-themecolor"><?=SELECTED_STUDENTS?></h4>
									</div>
									<div class="col-md-5 align-self-center">
										<button type="button" onclick="bulk_delete()" style="display:none" id="bulk_delete_id" class="btn waves-effect waves-light btn-dark" ><?=UNSELECT?></button>
									</div>
								</div>
								
								<table class="table table-bordered">
									<thead>
										<tr>
											<th >
												<input type="checkbox" id="CHECK_ALL_1" onclick="fun_check_all_1()" >
											</th>
											<th ><?=LAST_NAME?></th>
											<th ><?=FIRST_NAME?></th>
											<th ><?=OPTION?></th>
										</tr>
									</thead>
									<tbody>
										<? $res_cs = $db->Execute("select PK_INSTRUCTOR_STUDENT,LAST_NAME, FIRST_NAME 
										FROM 
										S_INSTRUCTOR_STUDENT, S_STUDENT_MASTER,S_STUDENT_ENROLLMENT
										WHERE 
										S_INSTRUCTOR_STUDENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
										S_INSTRUCTOR_STUDENT.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND 
										S_INSTRUCTOR_STUDENT.PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' AND 
										ARCHIVED = 0 AND 
										S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
										S_INSTRUCTOR_STUDENT.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
										ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME)"); 
										while (!$res_cs->EOF) { ?>
											<tr>
												<td>
													<input type="checkbox" id="PK_INSTRUCTOR_STUDENT_<?=$res_cs->fields['PK_INSTRUCTOR_STUDENT']?>" name="PK_INSTRUCTOR_STUDENT[]" value="<?=$res_cs->fields['PK_INSTRUCTOR_STUDENT']?>" onclick="show_bulk_delete()" >
												</td>
												
												<td><?=$res_cs->fields['LAST_NAME']?></td>
												<td><?=$res_cs->fields['FIRST_NAME']?></td>
												<td>
													<a href="javascript:void(0);" onclick="delete_row(<?=$res_cs->fields['PK_INSTRUCTOR_STUDENT']?>)" title="<?=DELETE?>" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>
												</td>
											</tr>
										<?	$res_cs->MoveNext();
										} ?>
									</tbody>
								</table>
							</form>
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
							<div class="form-group" id="delete_message" ><?=DELETE_MESSAGE_GENERAL?></div>
							<input type="hidden" id="DELETE_ID" value="0" />
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
	<script type="text/javascript">
		function search(){
			jQuery(document).ready(function($) {
				var data  = 'PK_STUDENT_GROUP='+$('#PK_STUDENT_GROUP').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&type=select_stu';
				var value = $.ajax({
					url: "ajax_search_student",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						document.getElementById('student_div').innerHTML = data
					}		
				}).responseText;
			});
		}
		function fun_check_all(){
			var str = '';
			if(document.getElementById('CHECK_ALL').checked == true)
				str = true;
			else
				str = false;
				
			var dd = document.getElementsByName('PK_STUDENT_ENROLLMENT[]');
			for(var i = 0 ; i < dd.length ; i++){
				dd[i].checked = str
			}
		}
		function fun_check_all_1(){
			var str = '';
			if(document.getElementById('CHECK_ALL_1').checked == true)
				str = true;
			else
				str = false;
				
			var dd = document.getElementsByName('PK_INSTRUCTOR_STUDENT[]');
			for(var i = 0 ; i < dd.length ; i++){
				dd[i].checked = str
			}
			show_bulk_delete()
		}
		function show_bulk_delete(){
			var flag = 0;
			var dd = document.getElementsByName('PK_INSTRUCTOR_STUDENT[]');
			for(var i = 0 ; i < dd.length ; i++){
				if(dd[i].checked == true){
					flag = 1;
					break;
				}
			}
			
			if(flag == 1) {
				document.getElementById('bulk_delete_id').style.display = 'block'
			} else {
				document.getElementById('bulk_delete_id').style.display = 'none'
			}
		}
		
		function bulk_delete(){
			var str = '';
			var dd = document.getElementsByName('PK_INSTRUCTOR_STUDENT[]');
			for(var i = 0 ; i < dd.length ; i++){
				if(dd[i].checked == true){
					if(str != '')
						str += ',';
					str += dd[i].value
				}
			}
			
			if(str != '') {
				delete_row(str)
			} else {
				alert('Please Select At Least One Student');
			}
		}
		
		function delete_row(id){
			jQuery(document).ready(function($) {		
				$("#deleteModal").modal()
				$("#DELETE_ID").val(id)
			});
		}
		function conf_delete(val,id){
			jQuery(document).ready(function($) {
				if(val == 1) {
					window.location.href = 'select_students?act=del&id='+$("#DELETE_ID").val();
				}
				$("#deleteModal").modal("hide");
			});
		}
		
		
	</script>
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_STUDENT_GROUP').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=GROUPS?>',
			nonSelectedText: '<?=GROUPS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=GROUPS?> selected'
		});
		$('#PK_TERM_MASTER').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=FIRST_TERM?>',
			nonSelectedText: '<?=FIRST_TERM?>',
			numberDisplayed: 2,
			nSelectedText: '<?=FIRST_TERM?> selected'
		});
		$('#PK_CAMPUS_PROGRAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PROGRAM?>',
			nonSelectedText: '<?=PROGRAM?>',
			numberDisplayed: 2,
			nSelectedText: '<?=PROGRAM?> selected'
		});
		$('#PK_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STATUS?>',
			nonSelectedText: '<?=STATUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=STATUS?> selected'
		});
	});
	</script>
</body>
</html>