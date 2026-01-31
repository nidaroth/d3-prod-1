<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/daily_roster.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || $_SESSION['PK_ROLES'] != 3 ){ 
	header("location:../index");
	exit;
} ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
	<? require_once("css.php"); ?>
	<title><?=DAILY_ROSTER_PAGE_TITLE?> | <?=$title?></title>
</head>
<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=DAILY_ROSTER_PAGE_TITLE?></h4>
                    </div>
                </div>				
				<div class="card-group">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
								<form class="floating-labels w-100 m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off">
									<div class="row">
										<div class="col-sm-3 pt-25">
											<div class="col-12 form-group">
												<select id="PK_TERM_MASTER" name="PK_TERM_MASTER" class="form-control required-entry" onchange="get_course_offering(this.value)">
													<option value=""></option>
													<? $res_type = $db->Execute("select S_TERM_MASTER.PK_TERM_MASTER,IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00', DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') AS TERM_BEGIN_DATE, IF(S_TERM_MASTER.END_DATE != '0000-00-00', DATE_FORMAT(S_TERM_MASTER.END_DATE,'%m/%d/%Y'),'') AS TERM_END_DATE, TERM_DESCRIPTION from S_COURSE_OFFERING LEFT JOIN S_COURSE_OFFERING_ASSISTANT ON S_COURSE_OFFERING_ASSISTANT.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING , S_TERM_MASTER WHERE S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND (INSTRUCTOR = '$_SESSION[PK_EMPLOYEE_MASTER]' OR S_COURSE_OFFERING_ASSISTANT.ASSISTANT = '$_SESSION[PK_EMPLOYEE_MASTER]') AND  S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER GROUP BY S_TERM_MASTER.PK_TERM_MASTER ORDER BY BEGIN_DATE DESC ");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" <? if($_GET['t'] == $res_type->fields['PK_TERM_MASTER']) echo "selected" ?> ><?=$res_type->fields['TERM_BEGIN_DATE'].' - '.$res_type->fields['TERM_END_DATE'].' - '.$res_type->fields['TERM_DESCRIPTION'] ?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span> 
												<label for="PK_TERM_MASTER">Select Term</label>
											</div>
										</div>
										
										<div class="col-sm-3 pt-25">
											<div class="col-12 form-group" id="PK_COURSE_OFFERING_LABEL"  >
												<div id="PK_COURSE_OFFERING_DIV" >
													<? $_REQUEST['val'] = $_GET['t'];
													$_REQUEST['def'] 	= $_GET['co'];
													include("ajax_get_course_offering.php"); ?>
												</div>
												<span class="bar"></span> 
												<label for="PK_COURSE_OFFERING"><?=SELECT_COURSE_OFFERING?></label>
											</div>
										</div>
										
										<div class="col-sm-3 pt-25">
											<div class="col-12 form-group">
												<div id="PK_COURSE_OFFERING_SCHEDULE_DETAIL_DIV" >
													<select id="PK_COURSE_OFFERING_SCHEDULE_DETAIL" name="PK_COURSE_OFFERING_SCHEDULE_DETAIL" class="form-control" >
														<option value="" ></option>
													</select>
												</div>
												<span class="bar"></span> 
												<label for="PK_COURSE_OFFERING_SCHEDULE_DETAIL"><?=SCHEDULED_CLASS_MEETING?></label>
											</div>
										</div>
										<div class="col-sm-3 pt-25">
											<button type="button" onclick="get_daily_roster()" class="btn waves-effect waves-light btn-info"><?=RUN ?></button>
										</div>
									</div>
									<div id="DAILY_ROSTER_DIV" >
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
		function get_schedule(val){
			jQuery(document).ready(function($) { 
				var data  = 'val='+val;
				var value = $.ajax({
					url: "ajax_get_course_schedule",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						document.getElementById('PK_COURSE_OFFERING_SCHEDULE_DETAIL_DIV').innerHTML = data;
						$('.floating-labels .form-control').on('focus blur', function (e) {
							$(this).parents('.form-group').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
						}).trigger('blur');
						
					}		
				}).responseText;
			});
		}
		function clear_div(){
		}
		function get_daily_roster(){
			jQuery(document).ready(function($) { 
				if(document.getElementById('PK_COURSE_OFFERING_SCHEDULE_DETAIL').value != '') {
					var data  = 'val='+document.getElementById('PK_COURSE_OFFERING_SCHEDULE_DETAIL').value;
					//alert(data)
					var value = $.ajax({
						url: "ajax_get_daily_roster",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							//alert(data)
							document.getElementById('DAILY_ROSTER_DIV').innerHTML 	= data;
						}		
					}).responseText;
				}
			});
		}
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
	</script>
</body>
</html>