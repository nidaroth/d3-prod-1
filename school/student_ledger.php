<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/student_balance.php");
require_once("check_access.php");

if(check_access('REPORT_ACCOUNTING') == 0 ){
	header("location:../index");
	exit;
}

$msg = "";
if(!empty($_POST)){
	$PK_CAMPUS = implode(",",$_POST['PK_CAMPUS']);
	
	if($_POST['LEDGER_TYPE'] == 1) {
		if($_POST['FORMAT'] == 1)
			header("location:ar_ledger_report_pdf?id=".implode(",",$_POST['PK_STUDENT_MASTER'])."&t=1&desc_type=".$_POST['DESCRIPTION'].'&campus='.$PK_CAMPUS."&do=".$_POST['DETAIL_OPTION']);
		else if($_POST['FORMAT'] == 3)
			header("location:ar_ledger_report_pdf?id=".implode(",",$_POST['PK_STUDENT_MASTER'])."&t=2&desc_type=".$_POST['DESCRIPTION'].'&campus='.$PK_CAMPUS."&do=".$_POST['DETAIL_OPTION']);
	} else if($_POST['LEDGER_TYPE'] == 2) {
		if($_POST['FORMAT'] == 1)
			header("location:ar_ledger_title_iv_report_pdf?id=".implode(",",$_POST['PK_STUDENT_MASTER'])."&t=1&en_type=".$_POST['ENROLLMENT_TYPE'].'&campus='.$PK_CAMPUS."&do=".$_POST['DETAIL_OPTION']);
		else if($_POST['FORMAT'] == 3)
			header("location:ar_ledger_title_iv_report_pdf?id=".implode(",",$_POST['PK_STUDENT_MASTER'])."&t=2&en_type=".$_POST['ENROLLMENT_TYPE'].'&campus='.$PK_CAMPUS."&do=".$_POST['DETAIL_OPTION']);
	}
	
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
	<title><?=MNU_STUDENT_LEDGER?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		.multiselect-container{
			width: max-content;
		}
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
							<?=MNU_STUDENT_LEDGER?> 
							<? if($msg != ''){ ?>
								- <span style="color:red" ><?=$msg ?></span>
							<? } ?>
						</h4>
                    </div>
                </div>
				
				<form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row ">
										<div class="col-md-3">
											<?=LEDGER_TYPE?>
											<select id="LEDGER_TYPE" name="LEDGER_TYPE"  class="form-control" onchange="show_fields(this.value)" >
												<option value="1">Student Ledger</option>
												<option value="2">Student Ledger - Title IV Balance</option>
											</select>
										</div>
									</div>
									<hr style="border-top: 1px solid #ccc;" />
									
									<div class="row form-group">
										<div class="col-md-2 ">
											<?=CAMPUS?>
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control required-entry" onchange="clear_search()" >
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<?=FIRST_TERM ?>
											<select id="PK_TERM_MASTER" name="PK_TERM_MASTER[]" multiple class="form-control" onchange="clear_search()" >
												<? $res_type = $db->Execute("select PK_TERM_MASTER,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_TERM_MASTER WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by BEGIN_DATE DESC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" ><?=$res_type->fields['BEGIN_DATE_1']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<?=PROGRAM ?>
											<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control" onchange="clear_search()" >
												<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2">
											<?=STATUS?>
											<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control" onchange="clear_search()" >
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND (ADMISSIONS = 0) order by STUDENT_STATUS ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<?=STUDENT_GROUP ?>
											<select id="PK_STUDENT_GROUP" name="PK_STUDENT_GROUP[]" multiple class="form-control" onchange="clear_search()" >
												<? $res_type = $db->Execute("select PK_STUDENT_GROUP,STUDENT_GROUP from M_STUDENT_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by STUDENT_GROUP ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_GROUP']?>" ><?=$res_type->fields['STUDENT_GROUP']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<br />
											<button type="button" onclick="search()" class="btn waves-effect waves-light btn-info"><?=SEARCH?></button>
										</div>
										
										<!-- Ticket # 1552 -->
										<div class="col-md-2 " id="SEARCH_TXT_DIV" style="display:none" >
											<br />
											<input type="text" class="form-control" id="SEARCH_TXT" name="SEARCH_TXT" placeholder="&#xF002; <?=SEARCH?>" style="font-family: FontAwesome;" onkeypress="do_search(event)" >
										</div>
										<!-- Ticket # 1552 -->
										
										
									</div>
									
									<div class="row" >
										<div class="col-md-2" id="ENROLLMENT_TYPE_DIV" style="display:none" >
											Enrollment
											<select id="ENROLLMENT_TYPE" name="ENROLLMENT_TYPE"  class="form-control" >
												<option value="1">All Enrollments</option>
												<option value="2">Current Enrollment</option>
											</select>
										</div>
										
										<div class="col-md-3" id="REPORT_OPTIONS_DIV" style="display:none" >
											<?=REPORT_OPTIONS?>
											<select id="REPORT_OPTIONS" name="REPORT_OPTIONS"  class="form-control" onchange="clear_search()" >
												<option value="1">All Balances</option>
												<option value="2">Positive Balances</option>
												<option value="3">Zero Balances</option>
												<option value="4">Negative Balances</option>
												<option value="5">Non-Zero Balances</option>
												<option value="6">Positive and Zero Balances Only</option>
												<option value="7">Negative and Zero Balances Only</option>
											</select>
										</div>
										
										<div class="col-md-2" >
											<?=DESCRIPTION?>
											<select id="DESCRIPTION" name="DESCRIPTION"  class="form-control" >
												<option value="1">Batch Description</option>
												<option value="2">Ledger Code Description</option>
											</select>
										</div>
										
										<div class="col-md-2" >
											<?=DETAIL_OPTION ?>
											<select id="DETAIL_OPTION" name="DETAIL_OPTION"  class="form-control" >
												<option value="1">Award Year</option>
												<option value="2">AY/AP</option>
												<option value="3">Description</option>
												<option value="4">Fee/Payment Type</option>
												<option value="5">Loan Gross & Fee</option>
												<option value="6">PYA</option>
												<option value="7" selected >Receipt # & Check #</option>
												<option value="8">Term Block</option>
											</select>
										</div>
										
										<div class="col-md-2" id="INCLUDE_ALL_LEADS_DIV" style="display:none" >
											<br />
											<div class="custom-control custom-checkbox mr-sm-12">
												<!-- Ticket #974 -->
												<input type="checkbox" class="custom-control-input" id="INCLUDE_ALL_LEADS" name="INCLUDE_ALL_LEADS" value="1" onclick="clear_search()" >
												<label class="custom-control-label" for="INCLUDE_ALL_LEADS" ><?=INCLUDE_ALL_LEADS?></label>
											</div>
										</div>
										
										<div class="col-md-2" >
											<br />
											<button type="button" onclick="submit_form(1)" id="btn_1" style="display:none" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
											<button type="button" onclick="submit_form(3)" id="btn_2" style="display:none" class="btn waves-effect waves-light btn-info">ZIP</button>
											
											<!--<button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button>-->
											<input type="hidden" name="FORMAT" id="FORMAT" >
										</div>
									</div>
									<div id="student_div" >
										<? /*$_REQUEST['REPORT_OPTIONS'] 	= 1;
										$_REQUEST['INCLUDE_ALL_LEADS'] 	= 0;
										$_REQUEST['show_count'] 		= 1;
										require_once('ajax_search_student_for_student_ledger.php');*/ ?>
									</div>
									<br />
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
		
		show_fields(1)
	});
	
	function show_fields(val){
		document.getElementById('REPORT_OPTIONS_DIV').style.display 		= 'none';
		document.getElementById('INCLUDE_ALL_LEADS_DIV').style.display 		= 'none';
		document.getElementById('ENROLLMENT_TYPE_DIV').style.display 		= 'none';
		
		if(val == 1) {
			document.getElementById('REPORT_OPTIONS_DIV').style.display 		= 'inline';
			document.getElementById('INCLUDE_ALL_LEADS_DIV').style.display 		= 'inline';
		} else if(val == 2) {
			document.getElementById('ENROLLMENT_TYPE_DIV').style.display 		= 'inline';
		}
	}
	
	/* Ticket # 1552 */
	function do_search(e){
		if (e.keyCode == 13) {
			event.preventDefault();
			search();
			return false;
		}
	}
	/* Ticket # 1552 */
	
	function clear_search(){
		document.getElementById('student_div').innerHTML = ''
	}
	
	function search(){
		jQuery(document).ready(function($) {
			var INCLUDE_ALL_LEADS = 0;
			if(document.getElementById('INCLUDE_ALL_LEADS').checked == true) {
				INCLUDE_ALL_LEADS = 1;
			}
			var data  = 'PK_CAMPUS='+$('#PK_CAMPUS').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_STUDENT_GROUP='+$('#PK_STUDENT_GROUP').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM').val()+'&INCLUDE_ALL_LEADS='+INCLUDE_ALL_LEADS+'&REPORT_OPTIONS='+$('#REPORT_OPTIONS').val()+'&show_count=1'+'&SEARCH_TXT='+$('#SEARCH_TXT').val(); //Ticket # 1552;;
			var value = $.ajax({
				url: "ajax_search_student_for_student_ledger",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					document.getElementById('student_div').innerHTML = data
					show_btn()
				}		
			}).responseText;
		});
	}
	
	function submit_form(val){
		document.getElementById('FORMAT').value = val
		document.form1.submit();
	}
	
	function fun_select_all(){
		var str = '';
		if(document.getElementById('SEARCH_SELECT_ALL').checked == true)
			str = true;
		else
			str = false;
			
		var PK_STUDENT_MASTER = document.getElementsByName('PK_STUDENT_MASTER[]')
		for(var i = 0 ; i < PK_STUDENT_MASTER.length ; i++){
			PK_STUDENT_MASTER[i].checked = str
		}
		get_count()
	}
	function get_count(){
		var tot = 0
		var PK_STUDENT_MASTER = document.getElementsByName('PK_STUDENT_MASTER[]')
		for(var i = 0 ; i < PK_STUDENT_MASTER.length ; i++){
			if(PK_STUDENT_MASTER[i].checked == true)
				tot++;
		}
		document.getElementById('SELECTED_COUNT').innerHTML = tot
		show_btn()
	}
	
	function show_btn(){
		
		document.getElementById('btn_1').style.display = 'none';
		document.getElementById('btn_2').style.display = 'none';
		
		var flag = 0;
		var PK_STUDENT_MASTER = document.getElementsByName('PK_STUDENT_MASTER[]')
		for(var i = 0 ; i < PK_STUDENT_MASTER.length ; i++){
			if(PK_STUDENT_MASTER[i].checked == true) {
				flag++;
				break;
			}
		}
		
		if(flag == 1) {
			document.getElementById('btn_1').style.display = 'inline';
			document.getElementById('btn_2').style.display = 'inline';
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
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		
		$('#PK_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: '<?=STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=STATUS?> selected'
		});
		
		$('#PK_TERM_MASTER').multiselect({
			includeSelectAllOption: true,
			allSelectedText: '<?=ALL_FIRST_TERM?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=FIRST_TERM?> selected'
		});
		
		$('#PK_STUDENT_GROUP').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=GROUP_CODE?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=GROUP_CODE?> selected'
		});
		
		$('#PK_CAMPUS_PROGRAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: '<?=ALL_PROGRAM?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=PROGRAM?> selected'
		});
	});
	</script>
</body>

</html>
