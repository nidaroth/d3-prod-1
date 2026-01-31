<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/course_offering.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	if($_POST['UPDATE_TYPE'] == 1){
		//COURSE_OFFERING_STATUS_1
		$ids = explode(",",$_GET['id']);
		foreach($ids as $id) {
			$COURSE_OFFERING['PK_COURSE_OFFERING_STATUS'] = $_POST['UPDATE_VALUE'];
			db_perform('S_COURSE_OFFERING', $COURSE_OFFERING, 'update'," PK_COURSE_OFFERING = '$id' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		}
		
	} else if($_POST['UPDATE_TYPE'] == 2){
		//INSTRUCTOR
		$ids = explode(",",$_GET['id']);
		foreach($ids as $id) {
			$COURSE_OFFERING['INSTRUCTOR'] = $_POST['UPDATE_VALUE'];
			db_perform('S_COURSE_OFFERING', $COURSE_OFFERING, 'update'," PK_COURSE_OFFERING = '$id' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		}
	} else if($_POST['UPDATE_TYPE'] == 3){
		//LMS_ACTIVE
		$ids = explode(",",$_GET['id']);
		foreach($ids as $id) {
			$COURSE_OFFERING['LMS_ACTIVE'] = $_POST['UPDATE_VALUE'];
			db_perform('S_COURSE_OFFERING', $COURSE_OFFERING, 'update'," PK_COURSE_OFFERING = '$id' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		}
	} else if($_POST['UPDATE_TYPE'] == 4){
		//ROOM
		$ids = explode(",",$_GET['id']);
		foreach($ids as $id) {
			$COURSE_OFFERING['PK_CAMPUS_ROOM'] = $_POST['UPDATE_VALUE'];
			db_perform('S_COURSE_OFFERING', $COURSE_OFFERING, 'update'," PK_COURSE_OFFERING = '$id' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		}
	} else if($_POST['UPDATE_TYPE'] == 5){
		//TOOL_BUILD_GRADE_BOOKS
		$ids = explode(",",$_GET['id']);
		foreach($ids as $id) {
			$result12 = $db->Execute("SELECT PK_COURSE FROM S_COURSE_OFFERING WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$id' ");
			$PK_COURSE = $result12->fields['PK_COURSE'];
		
			$SORT_ORDER = 0;
			$result12 = $db->Execute("SELECT * FROM S_COURSE_GRADE_BOOK WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE = '$PK_COURSE' ");
			while (!$result12->EOF) {
			
				$CODE 				= $result12->fields['CODE'];
				$PK_GRADE_BOOK_TYPE = $result12->fields['PK_GRADE_BOOK_TYPE'];
				$res1 = $db->Execute("SELECT PK_COURSE_OFFERING_GRADE FROM S_COURSE_OFFERING_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND CODE = '$CODE' AND PK_GRADE_BOOK_TYPE = '$PK_GRADE_BOOK_TYPE' AND PK_COURSE_OFFERING = '$id' ");
				if($res1->RecordCount() == 0) {
					$SORT_ORDER++;
					$COURSE_OFFERING_GRADE = array();
					$COURSE_OFFERING_GRADE['CODE'] 				 	= $result12->fields['CODE'];
					$COURSE_OFFERING_GRADE['DESCRIPTION'] 		 	= $result12->fields['DESCRIPTION'];
					$COURSE_OFFERING_GRADE['PK_GRADE_BOOK_TYPE'] 	= $result12->fields['PK_GRADE_BOOK_TYPE'];
					$COURSE_OFFERING_GRADE['POINTS'] 				= $result12->fields['POINTS'];
					$COURSE_OFFERING_GRADE['WEIGHT'] 				= $result12->fields['WEIGHT'];
					$COURSE_OFFERING_GRADE['WEIGHTED_POINTS'] 	 	= $result12->fields['POINTS'] * $result12->fields['WEIGHT'];
					$COURSE_OFFERING_GRADE['SORT_ORDER'] 		 	= $SORT_ORDER;
					$COURSE_OFFERING_GRADE['PK_COURSE_OFFERING']  	= $id;
					$COURSE_OFFERING_GRADE['PK_ACCOUNT']  			= $_SESSION['PK_ACCOUNT'];
					$COURSE_OFFERING_GRADE['CREATED_BY']  			= $_SESSION['PK_USER'];
					$COURSE_OFFERING_GRADE['CREATED_ON']  			= date("Y-m-d H:i");
					db_perform('S_COURSE_OFFERING_GRADE', $COURSE_OFFERING_GRADE, 'insert');
				}
				
				$result12->MoveNext();
			}
			
			//insert new grade to student - check on course_offering.php too
			$recal_grade = 0;
			$res_grade = $db->Execute("SELECT PK_COURSE_OFFERING_GRADE FROM S_COURSE_OFFERING_GRADE WHERE PK_COURSE_OFFERING = '$id' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
			while (!$res_grade->EOF) {
				$PK_COURSE_OFFERING_GRADE = $res_grade->fields['PK_COURSE_OFFERING_GRADE'];
				
				$res_stu = $db->Execute("select PK_STUDENT_MASTER,PK_STUDENT_ENROLLMENT FROM S_STUDENT_COURSE WHERE PK_COURSE_OFFERING = '$id' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
				while (!$res_stu->EOF) {
					$PK_STUDENT_MASTER 		= $res_stu->fields['PK_STUDENT_MASTER'];
					$PK_STUDENT_ENROLLMENT 	= $res_stu->fields['PK_STUDENT_ENROLLMENT'];
					
					$res_stu_grade = $db->Execute("select PK_STUDENT_GRADE FROM S_STUDENT_GRADE WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_GRADE = '$PK_COURSE_OFFERING_GRADE' ");
					if($res_stu_grade->RecordCount() == 0) {
						$STUDENT_GRADE['PK_COURSE_OFFERING_GRADE'] 	= $PK_COURSE_OFFERING_GRADE;
						$STUDENT_GRADE['PK_COURSE_OFFERING']		= $id;
						$STUDENT_GRADE['PK_STUDENT_ENROLLMENT'] 	= $PK_STUDENT_ENROLLMENT;
						$STUDENT_GRADE['PK_STUDENT_MASTER'] 	 	= $PK_STUDENT_MASTER;
						$STUDENT_GRADE['PK_ACCOUNT'] 			 	= $_SESSION['PK_ACCOUNT'];
						$STUDENT_GRADE['CREATED_BY']  			 	= $_SESSION['PK_USER'];
						$STUDENT_GRADE['CREATED_ON']  			 	= date("Y-m-d H:i");
						db_perform('S_STUDENT_GRADE', $STUDENT_GRADE, 'insert');
						$recal_grade = 1;
					}
					
					$res_stu->MoveNext();
				}
				
				$res_grade->MoveNext();
			}
			
			//check on course_offering.php too
			if($recal_grade == 1) {
				require_once("function_calc_student_grade.php"); 
				$res_stu = $db->Execute("select PK_STUDENT_COURSE, PK_STUDENT_MASTER FROM S_STUDENT_COURSE WHERE PK_STUDENT_MASTER = S_STUDENT_COURSE.PK_STUDENT_MASTER AND PK_COURSE_OFFERING = '$id' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
				while (!$res_stu->EOF) {
					$PK_STUDENT_COURSE = $res_stu->fields['PK_STUDENT_COURSE'];
					$PK_STUDENT_MASTER = $res_stu->fields['PK_STUDENT_MASTER'];
					
					$PK_STUDENT_GRADE 	= '';
					$POINTS 			= '';
					 $res_grade = $db->Execute("SELECT PK_COURSE_OFFERING_GRADE FROM S_COURSE_OFFERING_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$id' ORDER BY PK_COURSE_OFFERING_GRADE ASC ");
					while (!$res_grade->EOF) { 
						$PK_COURSE_OFFERING_GRADE = $res_grade->fields['PK_COURSE_OFFERING_GRADE']; 
						$res_stu_grade = $db->Execute("SELECT PK_STUDENT_GRADE,POINTS FROM S_STUDENT_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_GRADE = '$PK_COURSE_OFFERING_GRADE' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' "); 
						if($res_stu_grade->fields['POINTS'] != ''){
							if($PK_STUDENT_GRADE != '')
								$PK_STUDENT_GRADE .= ',';
							
							$PK_STUDENT_GRADE .= $res_stu_grade->fields['PK_STUDENT_GRADE'];
							
							if($POINTS != '')
								$POINTS .= ',';
							
							$POINTS .= $res_stu_grade->fields['POINTS'];
						}
						
						$res_grade->MoveNext();
					}
					
					calc_stu_grade($POINTS,$PK_STUDENT_GRADE,$PK_STUDENT_COURSE,$PK_STUDENT_MASTER,1);
					
					$res_stu->MoveNext();
				}
			}
		}
	} else if($_POST['UPDATE_TYPE'] == 6){
		//ROOM
		$ids = explode(",",$_GET['id']);
		foreach($ids as $id) {
			$COURSE_OFFERING['LMS_CODE'] = $_POST['UPDATE_VALUE'];
			db_perform('S_COURSE_OFFERING', $COURSE_OFFERING, 'update'," PK_COURSE_OFFERING = '$id' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		}
	} ?>
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
	<title><?=UPDATE.' '.COURSE_OFFERING_PAGE_TITLE?> | <?=$title?></title>
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
							<?=UPDATE.' '.COURSE_OFFERING_PAGE_TITLE?>
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
												<option value="1" ><?=COURSE_OFFERING_STATUS_1?></option>
												<? if($_GET['campus'] != ''){ ?>
												<option value="2" ><?=INSTRUCTOR?></option>
												<? } ?>
												<option value="3" ><?=LMS_ACTIVE?></option>
												<option value="6" ><?=LMS_CODE?></option>
												<? if($_GET['campus'] != ''){ ?>
												<option value="4" ><?=ROOM?></option>
												<? } ?>
												<option value="5" ><?=TOOL_BUILD_GRADE_BOOKS?></option>
											</select>
										</div>
										
										<div class="col-6 form-group" id="UPDATE_VALUE_DIV" >
											<select id="UPDATE_VALUE" name="UPDATE_VALUE" class="form-control required-entry" >
												<option value="" ><?=SELECT_VALUE ?></option>
											</select>
										</div>
									</div>
									
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

	<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.4.1.min.js"></script>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
	var form1 = new Validation('form1');
	
	function get_values(val){
		jQuery(document).ready(function($) { 
			var data  = 'type='+val+'&campus=<?=$_GET['campus']?>';
			var value = $.ajax({
				url: "ajax_get_course_offering_update_value",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {
					document.getElementById('UPDATE_VALUE_DIV').innerHTML = data;
				}		
			}).responseText;
		});
	}
	</script>
</body>

</html>