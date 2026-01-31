<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/academic_calendar.php");
require_once("check_access.php");

if(check_access('SETUP_SCHOOL') == 0 ){
	header("location:../index");
	exit;
}

function displayDates($date1, $date2, $format = 'm/d/Y' ) {
	$dates = array();
	$current = strtotime($date1);
	$date2 	 = strtotime($date2);
	$stepVal = '+1 day';
	while( $current <= $date2 ) {
		$dates[] = date($format, $current);
		$current = strtotime($stepVal, $current);
	}
	return $dates;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$PK_SESSION_ARR = $_POST['PK_SESSION'];
	unset($_POST['PK_SESSION']);
	
	/* Ticket # 1299 */
	$PK_CAMPUS_ARR = $_POST['PK_CAMPUS'];
	unset($_POST['PK_CAMPUS']);
	/* Ticket # 1299 */
	
	$ACADEMIC_CALENDAR = $_POST;
	if($ACADEMIC_CALENDAR['START_DATE'] != '')
		$ACADEMIC_CALENDAR['START_DATE'] = date("Y-m-d",strtotime($ACADEMIC_CALENDAR['START_DATE']));
		
	if($ACADEMIC_CALENDAR['END_DATE'] != '')
		$ACADEMIC_CALENDAR['END_DATE'] = date("Y-m-d",strtotime($ACADEMIC_CALENDAR['END_DATE']));
		
	if($_POST['LEAVE_TYPE'] == 1)
		$ACADEMIC_CALENDAR['END_DATE'] = $ACADEMIC_CALENDAR['START_DATE'];
		
	if($_GET['id'] == ''){
		$ACADEMIC_CALENDAR['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$ACADEMIC_CALENDAR['CREATED_BY']  = $_SESSION['PK_USER'];
		$ACADEMIC_CALENDAR['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('M_ACADEMIC_CALENDAR', $ACADEMIC_CALENDAR, 'insert');
		$PK_ACADEMIC_CALENDAR = $db->insert_ID();
	} else {
		$PK_ACADEMIC_CALENDAR = $_GET[id];
		$ACADEMIC_CALENDAR['EDITED_BY'] = $_SESSION['PK_USER'];
		$ACADEMIC_CALENDAR['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('M_ACADEMIC_CALENDAR', $ACADEMIC_CALENDAR, 'update'," PK_ACADEMIC_CALENDAR = '$PK_ACADEMIC_CALENDAR' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	}
	
	$db->Execute("DELETE FROM M_ACADEMIC_CALENDAR_SESSION WHERE PK_ACADEMIC_CALENDAR = '$PK_ACADEMIC_CALENDAR' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	
	if($_POST['LEAVE_TYPE'] == 1)
		$SCHEDULE_DATES[] = $ACADEMIC_CALENDAR['START_DATE'];
	else {
		if($ACADEMIC_CALENDAR['START_DATE'] != '' && $ACADEMIC_CALENDAR['END_DATE'] != '')
			$SCHEDULE_DATES = displayDates($ACADEMIC_CALENDAR['START_DATE'], $ACADEMIC_CALENDAR['END_DATE'],'Y-m-d');
	}

	foreach($SCHEDULE_DATES as $SCHEDULE_DATE){ 
		foreach($PK_SESSION_ARR as $PK_SESSION) {
			$ACADEMIC_CALENDAR_SESSION['PK_ACADEMIC_CALENDAR']  = $PK_ACADEMIC_CALENDAR;
			$ACADEMIC_CALENDAR_SESSION['ACADEMY_DATE']  		= $SCHEDULE_DATE;
			$ACADEMIC_CALENDAR_SESSION['PK_SESSION']  			= $PK_SESSION;
			$ACADEMIC_CALENDAR_SESSION['PK_ACCOUNT']  			= $_SESSION['PK_ACCOUNT'];
			$ACADEMIC_CALENDAR_SESSION['CREATED_BY'] 			= $_SESSION['PK_USER'];
			$ACADEMIC_CALENDAR_SESSION['CREATED_ON']  			= date("Y-m-d H:i");
			db_perform('M_ACADEMIC_CALENDAR_SESSION', $ACADEMIC_CALENDAR_SESSION, 'insert');
		}
	}
	
	foreach($PK_CAMPUS_ARR as $PK_CAMPUS) {
		$ACADEMIC_CALENDAR_CAMPUS['PK_CAMPUS'] = $PK_CAMPUS;
		
		$res = $db->Execute("SELECT PK_ACADEMIC_CALENDAR_CAMPUS FROM M_ACADEMIC_CALENDAR_CAMPUS WHERE PK_ACADEMIC_CALENDAR = '$PK_ACADEMIC_CALENDAR' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS = '$PK_CAMPUS' "); 
		if($res->RecordCount() == 0) {
			$ACADEMIC_CALENDAR_CAMPUS['PK_ACADEMIC_CALENDAR']  = $PK_ACADEMIC_CALENDAR;
			$ACADEMIC_CALENDAR_CAMPUS['PK_ACCOUNT']  			= $_SESSION['PK_ACCOUNT'];
			$ACADEMIC_CALENDAR_CAMPUS['CREATED_BY'] 			= $_SESSION['PK_USER'];
			$ACADEMIC_CALENDAR_CAMPUS['CREATED_ON']  			= date("Y-m-d H:i");
			db_perform('M_ACADEMIC_CALENDAR_CAMPUS', $ACADEMIC_CALENDAR_CAMPUS, 'insert');
			$PK_ACADEMIC_CALENDAR_CAMPUS_ARR[] = $db->insert_ID();
		} else
			$PK_ACADEMIC_CALENDAR_CAMPUS_ARR[] = $res->fields['PK_ACADEMIC_CALENDAR_CAMPUS'];
	}
	
	$cond = "";
	if(!empty($PK_ACADEMIC_CALENDAR_CAMPUS_ARR))
		$cond = " AND PK_ACADEMIC_CALENDAR_CAMPUS NOT IN (".implode(",", $PK_ACADEMIC_CALENDAR_CAMPUS_ARR).") ";
	$db->Execute("DELETE FROM M_ACADEMIC_CALENDAR_CAMPUS WHERE PK_ACADEMIC_CALENDAR = '$PK_ACADEMIC_CALENDAR' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond "); 
	
	header("location:manage_academic_calendar");
}
if($_GET['id'] == ''){
	$TITLE		= '';
	$LEAVE_TYPE = '';
	$START_DATE = '';
	$END_DATE 	= '';
	$ACTIVE	 	= '';	
} else {
	$res = $db->Execute("SELECT * FROM M_ACADEMIC_CALENDAR WHERE PK_ACADEMIC_CALENDAR = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_academic_calendar");
		exit;
	}
	$TITLE 		= $res->fields['TITLE'];
	$LEAVE_TYPE = $res->fields['LEAVE_TYPE'];
	$START_DATE = $res->fields['START_DATE'];
	$END_DATE 	= $res->fields['END_DATE'];
	$ACTIVE  	= $res->fields['ACTIVE'];
	
	if($START_DATE != '0000-00-00')
		$START_DATE = date("m/d/Y",strtotime($START_DATE));
	else
		$START_DATE = '';
		
	if($END_DATE != '0000-00-00')
		$END_DATE = date("m/d/Y",strtotime($END_DATE));
	else
		$END_DATE = '';
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
	<title><?=ACADEMIC_CALENDAR_PAGE_TITLE?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		#advice-required-entry-PK_CAMPUS{position: absolute;top: 30px;width: 140px}
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
                        <h4 class="text-themecolor">
							<? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=ACADEMIC_CALENDAR_PAGE_TITLE?>
							<? /*if($_GET['id'] != '') { ?> <a target="_blank" href="academic_calendar_pdf.php?id=<?=$_GET['id']?>" title="<?=VIEW_IN_PDF?>" ><i class="mdi mdi-file-pdf" style="font-size:25px" ></i> </a> <? }*/ ?>
						</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
									<div class="row">
										<div class="col-md-6">
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input class="form-control" type="text" value="<?=$TITLE?>" id="TITLE" name="TITLE">
														<span class="bar"></span> 
														<label for="TITLE"><?=TITLE?></label>
													</div>
												</div>
												
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="LEAVE_TYPE" name="LEAVE_TYPE" class="form-control" onclick="show_fields(this.value)" >
															<option value=""></option>
															<option value="1" <? if($LEAVE_TYPE == 1) echo "selected"; ?> >Holiday</option>
															<option value="2" <? if($LEAVE_TYPE == 2) echo "selected"; ?> >Break</option>
															<option value="3" <? if($LEAVE_TYPE == 3) echo "selected"; ?> >Closure</option>
														</select>
														<span class="bar"></span> 
														<label for="LEAVE_TYPE"><?=LEAVE_TYPE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6 form-group">
													<input class="form-control date1 date-inputmask" type="text" value="<?=$START_DATE?>" name="START_DATE" id="START_DATE" >
													
													<span class="bar"></span> 
													<label for="START_DATE" id="START_DATE_DIV" >
														<?  if($LEAVE_TYPE == 1) echo DATE; else echo START_DATE; ?>
													</label>
												</div>
												
												<? if($LEAVE_TYPE == 1) $style = "display:none" ?>
												<div class="col-md-6 form-group" style="<?=$style?>" id="END_DATE_DIV" >
													<input class="form-control date2 date-inputmask" type="text" value="<?=$END_DATE?>" name="END_DATE" id="END_DATE" >
													
													<span class="bar"></span> 
													<label for="END_DATE"><?=END_DATE?></label>
												</div>
											</div>
											
											<div class="row">
												<div class="col-2 col-sm-2">
													<span class="bar"></span> 
													<label for="SESSION"><?=SESSION?></label>
												</div>
												<div class="col-6 col-sm-6">
													<input type="checkbox" class="custom-control-input" id="SELECT_ALL" onclick="select_all()" >
													<label class="custom-control-label" for="SELECT_ALL" ><?=SELECT_ALL?></label>
													<br /><br />
												</div>
											</div>
											
											<div class="row">
												<div class="col-12 col-sm-12 form-group row" id="PK_CAMPUS_DIV" >
													<? $res_type = $db->Execute("select PK_SESSION,SESSION from M_SESSION WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by SESSION ASC"); //Ticket # 1577
													while (!$res_type->EOF) { ?>
														<div class="form-group col-4 col-sm-4">
															<div class="custom-control custom-checkbox mr-sm-2">
																<? $checked = '';
																$PK_SESSION = $res_type->fields['PK_SESSION'];
																$res = $db->Execute("select PK_ACADEMIC_CALENDAR_SESSION FROM M_ACADEMIC_CALENDAR_SESSION WHERE PK_SESSION = '$PK_SESSION' AND PK_ACADEMIC_CALENDAR = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
																if($res->RecordCount() > 0)
																	$checked = 'checked';
																?>
																<input type="checkbox" class="custom-control-input" id="PK_SESSION_<?=$PK_SESSION?>" name="PK_SESSION[]" value="<?=$PK_SESSION?>" <?=$checked?> >
																<label class="custom-control-label" for="PK_SESSION_<?=$PK_SESSION?>" ><?=$res_type->fields['SESSION']?></label>
															</div>
														</div>
													<?	$res_type->MoveNext();
													} ?>
												</div>
											</div>
										</div>
										
										<div class="col-md-6">
											<div class="row">
												<div class="col-12 col-sm-12">
													<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="required-entry" >
														<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
														while (!$res_type->EOF) { 
															$selected = '';
															$PK_CAMPUS = $res_type->fields['PK_CAMPUS'];
															$res = $db->Execute("select PK_ACADEMIC_CALENDAR_CAMPUS FROM M_ACADEMIC_CALENDAR_CAMPUS WHERE PK_CAMPUS = '$PK_CAMPUS' AND PK_ACADEMIC_CALENDAR = '$_GET[id]' AND PK_ACADEMIC_CALENDAR > 0 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
															if($res->RecordCount() > 0 || ($res_type->RecordCount() == 1 && $_GET['id'] == ''))
																$selected = 'selected'; ?>
															<option value="<?=$res_type->fields['PK_CAMPUS']?>" <?=$selected?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
										</div>
									</div>
																	
									<? if($_GET['id'] != ''){ ?>
									<div class="row">
										<div class="col-md-6">
											<div class="row form-group">
												<div class="custom-control col-md-2"><?=ACTIVE?></div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="customRadio11" name="ACTIVE" value="1" <? if($ACTIVE == 1) echo "checked"; ?> class="custom-control-input">
													<label class="custom-control-label" for="customRadio11"><?=YES?></label>
												</div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="customRadio22" name="ACTIVE" value="0" <? if($ACTIVE == 0) echo "checked"; ?>  class="custom-control-input">
													<label class="custom-control-label" for="customRadio22"><?=NO?></label>
												</div>
											</div>
										</div>
									</div>
									<? } ?>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_academic_calendar'" ><?=CANCEL?></button>
												
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
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript">
		jQuery(document).ready(function($) { 
			jQuery('.date1').datepicker({
				todayHighlight: true,
				orientation: "bottom auto",
				autoclose: true,
			});
			
			$('.date1').datepicker().on('hide', function(e) {
				if(document.getElementById('START_DATE').value != '') {
					var minDate = $("#START_DATE").val();
					$('#END_DATE').datepicker('setStartDate', minDate);
					
					document.getElementById('END_DATE').focus();
					$("#START_DATE").parent().addClass("focused")
				} else
					$("#START_DATE").parent().removeClass("focused")
			});
			
			jQuery('.date2').datepicker({
				todayHighlight: true,
				orientation: "bottom auto"
			});
			
			<? if($START_DATE != ''){ ?>
				var minDate = $("#START_DATE").val();
				$('#END_DATE').datepicker('setStartDate', minDate);
			<? } ?>
		});
	</script>
	
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');
		
		function show_fields(val){
			if(val == 1) {
				document.getElementById('END_DATE').value 				= '';
				document.getElementById('END_DATE_DIV').style.display 	= 'none';
				document.getElementById('START_DATE_DIV').innerHTML		= '<?=DATE?>';
			} else if(val == 2 || val == 3) {
				document.getElementById('END_DATE_DIV').style.display 	= 'block';
				document.getElementById('START_DATE_DIV').innerHTML		= '<?=START_DATE?>';
			}
		}
		
		function select_all(){
			var str = '';
			if(document.getElementById('SELECT_ALL').checked == true)
				str = true;
			else
				str = false;
				
			var PK_SESSION = document.getElementsByName('PK_SESSION[]')
			for(var i = 0 ; i < PK_SESSION.length ; i++){
				PK_SESSION[i].checked = str
			}	
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
			numberDisplayed: 3,
			nSelectedText: '<?=CAMPUS?> selected'
		});
	});
	</script>

</body>

</html>