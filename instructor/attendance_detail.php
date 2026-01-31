<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/dashboard.php");
require_once("../language/instructor_attendance_detail.php");
//echo "<pre>";print_r($_SESSION);exit;
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
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
	<title><?=ATTENDANCE_REVIEW_PAGE_TITLE?> | <?=$title?></title>
</head>
<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=ATTENDANCE_REVIEW_PAGE_TITLE?></h4>
                    </div>
                </div>				
				<div class="card-group">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
								<form class="floating-labels w-100 m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off">
									<div class="row">
										<div class="col-2 form-group">
											<select id="PK_TERM_MASTER" name="PK_TERM_MASTER" class="form-control required-entry" onchange="get_course_offering(this.value)">
												<option value=""></option>
												<? $res_type = $db->Execute("select S_TERM_MASTER.PK_TERM_MASTER,IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00', DATE_FORMAT( S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') AS TERM_BEGIN_DATE, IF(S_TERM_MASTER.END_DATE != '0000-00-00', DATE_FORMAT(S_TERM_MASTER.END_DATE,'%m/%d/%Y'),'') AS TERM_END_DATE, TERM_DESCRIPTION from S_COURSE_OFFERING LEFT JOIN S_COURSE_OFFERING_ASSISTANT ON S_COURSE_OFFERING_ASSISTANT.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING  , S_TERM_MASTER WHERE S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND (INSTRUCTOR = '$_SESSION[PK_EMPLOYEE_MASTER]' OR S_COURSE_OFFERING_ASSISTANT.ASSISTANT = '$_SESSION[PK_EMPLOYEE_MASTER]') AND  S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER GROUP BY S_TERM_MASTER.PK_TERM_MASTER ORDER BY BEGIN_DATE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_TERM_MASTER']?>"  ><?=$res_type->fields['TERM_BEGIN_DATE'].' - '.$res_type->fields['TERM_END_DATE'].' - '.$res_type->fields['TERM_DESCRIPTION'] ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
											<span class="bar"></span> 
											<label for="PK_TERM_MASTER"><?=SELECT_TERM?></label>
										</div>
										<div class="col-3 form-group" id="PK_COURSE_OFFERING_LABEL" >
											<div id="PK_COURSE_OFFERING_DIV" >
												<select id="PK_COURSE_OFFERING" name="PK_COURSE_OFFERING" class="form-control required-entry" >
													<option value=""></option>
												</select>
											</div>
											<span class="bar"></span> 
											<label for="PK_COURSE_OFFERING"><?=SELECT_COURSE_OFFERING?></label>
										</div>
										
										<div class="col-md-3">
											<div class="row form-group">
												<div class="col-md-1"></div>
												<div class="custom-control custom-radio col-md-5">
													<input type="radio" id="SUMMARY" name="VIEW" value="1" class="custom-control-input" checked >
													<label class="custom-control-label" for="SUMMARY"><?=SUMMARY?></label>
												</div>
												<div class="custom-control custom-radio col-md-6">
													<input type="radio" id="DETAIL_VIEW" name="VIEW" value="2" class="custom-control-input" >
													<label class="custom-control-label" for="DETAIL_VIEW"><?=DETAIL_VIEW?></label>
												</div>
											</div>
										</div>

										<div class="col-3 form-group ">
											<button type="button" onclick="get_attendance(0)" class="btn waves-effect waves-light btn-info"><?=SHOW?></button>
										</div>
									</div>
									<div class="row">
										<div class="col-sm-12 pt-25 " >
											<div id="STUDENT_DIV">
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
	<script type="text/javascript">
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
		function get_attendance(start){
			if(document.getElementById('SUMMARY').checked == true)
				get_attendance_summary()
			else
				get_attendance_detail(start)
		}
		function get_attendance_detail(start){
			jQuery(document).ready(function($) { 
				if(document.getElementById('PK_COURSE_OFFERING').value != '') {
					var data  = 'val='+document.getElementById('PK_COURSE_OFFERING').value+'&tid='+document.getElementById('PK_TERM_MASTER').value+'&start='+start;
					//alert(data)
					var value = $.ajax({
						url: "ajax_get_student_attendance_detail",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							//alert(data)
							document.getElementById('STUDENT_DIV').innerHTML = data;
						}		
					}).responseText;
				}
			});
		}
		function get_attendance_summary(){
			jQuery(document).ready(function($) { 
				if(document.getElementById('PK_COURSE_OFFERING').value != '') {
					var data  = 'val='+document.getElementById('PK_COURSE_OFFERING').value+'&tid='+document.getElementById('PK_TERM_MASTER').value;
					//alert(data)
					var value = $.ajax({
						url: "ajax_get_student_attendance_summary",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							//alert(data)
							document.getElementById('STUDENT_DIV').innerHTML = data;
						}		
					}).responseText;
				}
			});
		}
		function get_schedule(val){
		}
	</script>
</body>
</html>