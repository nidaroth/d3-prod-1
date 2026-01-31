<? 
require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
require_once("check_access.php");

if(check_access('REPORT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	if($_POST['REPORT_TYPE'] == 1) {
		//FORMAT
		header("location:course_offering_roster?tm=".$_POST['PK_TERM_MASTER'].'&p=r&FORMAT='.$_POST['FORMAT'].'&co_id='.implode(",",$_POST['PK_COURSE_OFFERING']));
	} else if($_POST['REPORT_TYPE'] == 2) {
		header("location:course_offering_by_term?tm=".implode(",",$_POST['PK_TERM_MASTER']).'&p=r&FORMAT='.$_POST['FORMAT'].'&INCLUDE_STUDENTS='.$_POST['INCLUDE_STUDENTS'].'&co_id='.implode(",",$_POST['PK_COURSE_OFFERING']).'&campus='.implode(",",$_POST['PK_CAMPUS'])); //Ticket # 1352
	} else if($_POST['REPORT_TYPE'] == 3) {
		header("location:course_offering_roster_with_pictures?tm=".implode(",",$_POST['PK_TERM_MASTER']).'&p=r&FORMAT='.$_POST['FORMAT'].'&co_id='.implode(",",$_POST['PK_COURSE_OFFERING']).'&campus='.implode(",",$_POST['PK_CAMPUS']));
	} else if($_POST['REPORT_TYPE'] == 4) {
		header("location:course_offering_daily?tm=".implode(",",$_POST['PK_TERM_MASTER']).'&p=r&FORMAT='.$_POST['FORMAT'].'&co_id='.implode(",",$_POST['PK_COURSE_OFFERING']).'&campus='.implode(",",$_POST['PK_CAMPUS']).'&start_date='.$_POST['START_DATE'].'&end_date='.$_POST['END_DATE']);
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
	<title><?=MNU_COURSE?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		#advice-required-entry-PK_COURSE_OFFERING, #advice-required-entry-PK_TERM_MASTER {position: absolute;top: 38px;}
		/* Ticket # 1703 */
		.dropdown-menu>li>a { white-space: nowrap; } 
		.option_red > a > label{color:red !important}
		/* Ticket # 1703 */
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
                        <h4 class="text-themecolor">
							<?=MNU_COURSE ?>
						</h4>
                    </div>
                </div>
				
				<form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row" style="padding-bottom:10px;" >
										<div class="col-md-3 ">
											<b><?=COURSE_REPORT_TYPE?></b>
											<select id="REPORT_TYPE" name="REPORT_TYPE"  class="form-control" onchange="show_filters(this.value);" >
												<option value="1"><?=COURSE_OFFERING_ROSTER?></option>
												<option value="3">Course Offering Roster With Pictures</option>
												<option value="2"><?=MNU_COURSE_OFFERING_BY_TERM?></option>
												<option value="4">Daily Course Offerings</option>
											</select>
										</div>
									</div>
									
									<div class="row" style="margin-bottom:20px" >
										<!-- Ticket # 1352   -->
										<div class="col-md-2" id="PK_CAMPUS_DIV"  >
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" onchange="get_course_term_from_campus();" ><!-- Ticket # 1703 -->
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										<!-- Ticket # 1352   -->
									</div>
									
									<div class="row">
										
										<div class="col-md-3" id="PK_TERM_MASTER_DIV" >
											<!-- Ticket # 1352   -->
											<select id="PK_TERM_MASTER" name="PK_TERM_MASTER[]" multiple class="form-control " onchange="get_course_offering();" >
												<? $res_type = $db->Execute("select PK_TERM_MASTER, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by BEGIN_DATE DESC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" ><?=$res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['END_DATE_1']." - ".$res_type->fields['TERM_DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
											<!-- Ticket # 1352   -->
										</div>
										
										<div class="col-md-2 " id="PK_COURSE_OFFERING_DIV" >
											<select id="PK_COURSE_OFFERING" name="PK_COURSE_OFFERING" class="form-control" >
												<option value=""><?=COURSE_OFFERING ?></option>
											</select>
										</div>
										
										<div class="col-md-2" id="INCLUDE_STUDENTS_DIV" >
											<br />
											<input type="checkbox" id="INCLUDE_STUDENTS" name="INCLUDE_STUDENTS" value="1" >
											<?=INCLUDE_STUDENTS ?>
										</div>

										<div class="col-md-2 focused" id="START_DATE_DIV">
											<input type="text" id="START_DATE" name="START_DATE" value="" class="form-control date required-entry">
											<span class="bar"></span> 
											<label for="START_DATE"><?=START_DATE?></label>
										</div>
										<div class="col-md-2 focused" id="END_DATE_DIV">
											<input type="text" id="END_DATE" name="END_DATE" value="" class="form-control date required-entry">
											<span class="bar"></span> 
											<label for="END_DATE"><?=END_DATE?></label>
										</div>
									
										<div class="col-md-2" style="padding: 0;" >
											<button type="button" onclick="submit_form(1)" id="btn_1" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
											<input type="hidden" name="FORMAT" id="FORMAT" >
											<!-- New -->
											<button type="button" onclick="submit_form(2)" id="btn_2" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button>
											<button type="button" onclick="submit_form(3)" id="btn_3" style="display:none" class="btn waves-effect waves-light btn-info">ZIP</button>
										</div>
									</div>
									<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
								</div>
							</div>
						</div>
					</div>
				</form>
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
		show_filters(1);

	});

	function get_course_offering(){
		jQuery(document).ready(function($) { 
			var data  = 'PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&dont_show_term=1&PK_CAMPUS='+$('#PK_CAMPUS').val();
			var url	  = "ajax_get_course_offering_from_term";
		
			//alert(data)
			var value = $.ajax({
				url: url,	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					//alert(data)
					document.getElementById('PK_COURSE_OFFERING_DIV').innerHTML = data;
					document.getElementById('PK_COURSE_OFFERING').setAttribute('multiple', true);
					document.getElementById('PK_COURSE_OFFERING').name = "PK_COURSE_OFFERING[]"
					//document.getElementById('PK_COURSE_OFFERING').className = "required-entry"
					$("#PK_COURSE_OFFERING option[value='']").remove();
					
					$('#PK_COURSE_OFFERING').multiselect({
						includeSelectAllOption: true,
						allSelectedText: 'All <?=COURSE_OFFERING?>',
						nonSelectedText: '<?=COURSE_OFFERING?>',
						numberDisplayed: 2,
						nSelectedText: '<?=COURSE_OFFERING?> selected'
					});
				}		
			}).responseText;
		});
	}
	function get_course_details(){
	}
	
	function show_filters(val){
		
		//document.getElementById('btn_2').style.display = 'none'; Ticket # 1801
		document.getElementById('INCLUDE_STUDENTS_DIV').style.display 	= 'none';
		document.getElementById('PK_CAMPUS_DIV').style.display 			= 'inline'; //Ticket # 1352 Ticket # 2046 
		document.getElementById('btn_2').style.display 					= 'inline';
		document.getElementById('btn_3').style.display 					= 'none';
		document.getElementById('START_DATE_DIV').style.display 		= 'none';
		document.getElementById('END_DATE_DIV').style.display 			= 'none';
		
		if(val == 2) {
			document.getElementById('INCLUDE_STUDENTS_DIV').style.display = 'inline';
			document.getElementById('PK_CAMPUS_DIV').style.display 		  = 'inline'; //Ticket # 1352
			document.getElementById('btn_2').style.display = 'inline';
		} 
		
		if(val == 3) {
			document.getElementById('btn_2').style.display	= 'none';
			document.getElementById('btn_3').style.display	= 'inline';
		}

		if(val == 4) {
			document.getElementById('START_DATE_DIV').style.display  = 'inline';
			document.getElementById('END_DATE_DIV').style.display 	 = 'inline';
			document.getElementById('btn_2').style.display	         = 'none';
		}
	}
	
	function get_course_term_from_campus(){
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
						document.getElementById(term_id).className 			= '';
						document.getElementById(term_id).name 				= term_id+"[]"
						document.getElementById(term_id).setAttribute('multiple', true);
						document.getElementById(term_id).setAttribute("onchange", "get_course_offering()");
						
						$("#"+term_id+" option[value='']").remove();
						
						$('#'+term_id).multiselect({
							includeSelectAllOption: true,
							allSelectedText: 'All <?=COURSE_TERM?>',
							nonSelectedText: '<?=COURSE_TERM?>',
							numberDisplayed: 2,
							nSelectedText: '<?=COURSE_TERM?> selected',
							enableCaseInsensitiveFiltering: true,
						});
						
					}		
				}).responseText;
			});
		}
	</script>
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	
	<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.4.1.min.js"></script>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
	function submit_form(val){
		jQuery(document).ready(function($) {
			var valid = new Validation('form1', {onSubmit:false});
			var result = valid.validate();
			if(result == true){ 
				document.getElementById('FORMAT').value = val
				document.form1.submit();
			}
		});
	}
	</script>
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		/* Ticket # 1352 */
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '<?=CAMPUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		$('#PK_TERM_MASTER').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=COURSE_TERM?>',
			nonSelectedText: '<?=COURSE_TERM?>',
			numberDisplayed: 2,
			nSelectedText: '<?=COURSE_TERM?> selected',
			enableCaseInsensitiveFiltering: true,
		});
		/* Ticket # 1352 */
	});
	</script>
	
</body>

</html>