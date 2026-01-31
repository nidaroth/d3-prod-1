<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_BULK_UPDATE') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	if($_GET['t'] == 1){
		$i = 0;
		$PK_CAMPUS = $_POST['PK_CAMPUS'];
		foreach($_POST['PK_STUDENT_ENROLLMENT_1'] as $PK_STUDENT_ENROLLMENT){
			$PK_STUDENT_MASTER = $_POST['PK_STUDENT_MASTER_1'][$i];
			
			$res = $db->Execute("SELECT PK_STUDENT_CAMPUS FROM S_STUDENT_CAMPUS WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS = '$PK_CAMPUS' "); 
			if($res->RecordCount() == 0) {
				$STUDENT_CAMPUS['PK_CAMPUS']   				= $PK_CAMPUS;
				$STUDENT_CAMPUS['PK_STUDENT_MASTER'] 		= $PK_STUDENT_MASTER;
				$STUDENT_CAMPUS['PK_STUDENT_ENROLLMENT'] 	= $PK_STUDENT_ENROLLMENT;
				$STUDENT_CAMPUS['PK_ACCOUNT'] 				= $_SESSION['PK_ACCOUNT'];
				$STUDENT_CAMPUS['CREATED_BY']  				= $_SESSION['PK_USER'];
				$STUDENT_CAMPUS['CREATED_ON']  				= date("Y-m-d H:i");
				db_perform('S_STUDENT_CAMPUS', $STUDENT_CAMPUS, 'insert');
			}
			$i++;
		}
		header("location:student_bulk_update?t=".$_GET['t']);
	} else if($_GET['t'] == 2){
		$_SESSION['BULK_EN'] = implode(",",$_POST['PK_STUDENT_ENROLLMENT_1']);
		header("location:student_notes?p=m&event=1");
	} else if($_GET['t'] == 3){
		$_SESSION['BULK_EN'] = implode(",",$_POST['PK_STUDENT_ENROLLMENT_1']);
		header("location:student_notes?p=m&event=0&t=".$_POST['t']);
	} else if($_GET['t'] == 4){
		$_SESSION['BULK_EN'] = implode(",",$_POST['PK_STUDENT_ENROLLMENT_1']);
		header("location:student_task?p=m");
	}
	
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
	<title><?=MNU_MANAGEMENT?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		
		/* Ticket # 1149 - term */
		.dropdown-menu>li>a { white-space: nowrap; }
		.option_red > a > label{color:red !important}
		/* Ticket # 1149 - term */
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
						<? if($_GET['t'] == 1)
							echo BULK_UPDATE.' - '.CAMPUS; 
						else if($_GET['t'] == 2)
							echo MNU_BULK_CREATE_EVENT; 
						else if($_GET['t'] == 3)
							echo MNU_BULK_CREATE_NOTES;
						else if($_GET['t'] == 4)
							echo MNU_BULK_CREATE_TASK; ?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
									<? if($_GET['t'] == 2 || $_GET['t'] == 3 || $_GET['t'] == 4){ ?>
									<div class="row" style="margin-left: 0px;" >
										<div class="col-md-4">
											<div class="row form-group">
												<div class="custom-control custom-radio col-md-3">
													<input type="radio" id="STUDENT_TYPE_1" name="STUDENT_TYPE" value="1" <? if($STUDENT_TYPE == 1) echo "checked"; ?> class="custom-control-input" checked onchange="get_status()" >
													<label class="custom-control-label" for="STUDENT_TYPE_1"><?=STUDENT?></label>
												</div>
												<div class="custom-control custom-radio col-md-3">
													<input type="radio" id="STUDENT_TYPE_2" name="STUDENT_TYPE" value="2" <? if($STUDENT_TYPE == 2) echo "checked"; ?>  class="custom-control-input" onchange="get_status()" >
													<label class="custom-control-label" for="STUDENT_TYPE_2"><?=LEAD?></label>
												</div>
											</div>
										</div>
									</div>
									<? } ?>
									<div class="row" style="padding-bottom:10px;" >
										<div class="col-md-2" id="PK_STUDENT_STATUS_DIV" >
											<? if($_GET['t'] == 2 || $_GET['t'] == 3 || $_GET['t'] == 4) $sts_cond = " AND ADMISSIONS = 0 "; else $sts_cond = ""; ?>
											<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control">
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS, DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 $sts_cond order by STUDENT_STATUS ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_TERM_MASTER" name="PK_TERM_MASTER[]" multiple class="form-control" >
												<? /* Ticket #1149 - term */
												$res_type = $db->Execute("select PK_TERM_MASTER, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION, ACTIVE from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, BEGIN_DATE DESC");
												while (!$res_type->EOF) { 
													$str = $res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['END_DATE_1'].' - '.$res_type->fields['TERM_DESCRIPTION'];
													if($res_type->fields['ACTIVE'] == 0)
														$str .= ' (Inactive)'; ?>
													<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$str ?></option>
												<?	$res_type->MoveNext();
												} /* Ticket #1149 - term */ ?>
											</select>
										</div>
										
										<? if($_GET['t'] == 2 || $_GET['t'] == 3 || $_GET['t'] == 4){ ?>
											<div class="col-md-2 ">
												<select id="PK_SESSION" name="PK_SESSION[]" class="form-control" multiple >
													<? $res_type = $db->Execute("select PK_SESSION,SESSION from M_SESSION WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by DISPLAY_ORDER ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_SESSION'] ?>" ><?=$res_type->fields['SESSION']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
											</div>
										<? } ?>
										
										<div class="col-md-2 ">
											<select id="PK_STUDENT_GROUP" name="PK_STUDENT_GROUP[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select PK_STUDENT_GROUP,STUDENT_GROUP from M_STUDENT_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by STUDENT_GROUP ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_GROUP']?>" ><?=$res_type->fields['STUDENT_GROUP']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<? if($_GET['t'] == 2 || $_GET['t'] == 3 || $_GET['t'] == 4){ ?>
											<div class="col-md-2 ">
												<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" >
													<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
											</div>
										<? } ?>
										
										<? if($_GET['t'] != 2 && $_GET['t'] != 3 && $_GET['t'] != 4){ ?>
										<div class="col-md-2 ">
											<select id="PK_COURSE" name="PK_COURSE[]" multiple class="form-control" onchange="get_course_offering(this.value)" >
												<? $res_type = $db->Execute("select PK_COURSE,COURSE_CODE,COURSE_DESCRIPTION from S_COURSE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by COURSE_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_COURSE']?>" ><?=$res_type->fields['COURSE_CODE'].' - '.$res_type->fields['COURSE_DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 " id="PK_COURSE_OFFERING_DIV" >
											<select id="PK_COURSE_OFFERING" name="PK_COURSE_OFFERING" class="form-control" >
												<option value=""><?=COURSE_OFFERING_PAGE_TITLE?></option>
											</select>
										</div>
										<? } ?>
									</div>
									<div class="row">
										<div class="col-md-2 align-self-center ">
											<button type="button" class="btn waves-effect waves-light btn-info" id="btn" style="display:none" onclick="add_to_update_list()" ><?=ADD_TO_UPDATE_LIST?></button>
										</div>
										<div class="col-md-8 align-self-center "></div>
										<div class="col-md-2 ">
											<button type="button" class="btn waves-effect waves-light btn-dark" onclick="search()" ><?=SEARCH?></button>
										</div>
									</div>
									<br />
									<div id="student_div" style="max-height:300px;overflow: auto;"></div>
									
									<div class="row page-titles">
										<div class="col-md-5 align-self-center">
											<h4 class="text-themecolor">
												<?=UPDATE_LIST?>
											</h4>
										</div>
									</div>
									<div id="student_update_div" style="max-height:300px;overflow: auto;">
										<table class="table table-hover" id="student_update_table" >
											<thead>
												<tr>
													<th><?=STUDENT?></th>
													<th><?=GROUP_CODE?></th>
													<th><?=FIRST_TERM?></th>
													<th><?=PROGRAM?></th>
													<th><?=STATUS?></th>
													<th><?=ACTION?></th>
												</tr>
											</thead>
											<tbody>
											</tbody>
										</table>
									</div>
									<div id="action_div" >
									<? if($_GET['t'] == 1){ ?>
										<div class="d-flex">
											<div class="col-12 col-sm-3 form-group">&nbsp;</div>
											<div class="col-12 col-sm-3 form-group">
												<select id="PK_CAMPUS" name="PK_CAMPUS" class="form-control required-entry">
													<option></option>
													<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE'] ?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span> 
												<label for="PK_CAMPUS"><?=CAMPUS?></label>
											</div>
											<div class="col-12 col-sm-3 form-group">
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=UPDATE?></button>
											</div>
										</div>
									<? } else if($_GET['t'] == 2 || $_GET['t'] == 3){ ?>
										<div class="row" style="display:none" id="action_btn" >
											<div class="col-12 col-sm-5 form-group">&nbsp;</div>
											<div class="col-12 col-sm-3 form-group">
												<button type="submit" class="btn waves-effect waves-light btn-info">
													<? if($_GET['t'] == 2)
														echo CREATE_EVENTS; 
													else if($_GET['t'] == 3)
														echo CREATE_NOTES; ?>
												</button>
											</div>
										</div>
									<? } else if($_GET['t'] == 4) { ?>
										<div class="row">
											<div class="col-md-12 " style="text-align: center;" >
												<button type="submit" class="btn waves-effect waves-light btn-dark" style="display:none" id="action_btn" >
													<?=CREATE_TASKS ?>
												</button>
											</div>
										</div>
									<? } ?>
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
		var form1 = new Validation('form1');
		
		function search(){
			jQuery(document).ready(function($) {
				var LEAD = 0;
				if(document.getElementById('STUDENT_TYPE_2')){
					if(document.getElementById('STUDENT_TYPE_2').checked == true)
						LEAD = 1;
				}
				
				var PK_STUDENT_GROUP 	= '';
				var PK_TERM_MASTER 		= '';
				var PK_CAMPUS_PROGRAM 	= '';
				var PK_STUDENT_STATUS 	= '';
				var PK_COURSE 			= '';
				var PK_COURSE_OFFERING 	= '';
				var PK_SESSION 			= '';
				var PK_CAMPUS 			= '';
				
				if(document.getElementById('PK_STUDENT_GROUP'))
					PK_STUDENT_GROUP = $('#PK_STUDENT_GROUP').val();
					
				if(document.getElementById('PK_TERM_MASTER'))
					PK_TERM_MASTER = $('#PK_TERM_MASTER').val();
					
				if(document.getElementById('PK_CAMPUS_PROGRAM'))
					PK_CAMPUS_PROGRAM = $('#PK_CAMPUS_PROGRAM').val();
					
				if(document.getElementById('PK_STUDENT_STATUS'))
					PK_STUDENT_STATUS = $('#PK_STUDENT_STATUS').val();
					
				if(document.getElementById('PK_COURSE'))
					PK_COURSE = $('#PK_COURSE').val();
					
				if(document.getElementById('PK_COURSE_OFFERING'))
					PK_COURSE_OFFERING = $('#PK_COURSE_OFFERING').val();
					
				if(document.getElementById('PK_SESSION'))
					PK_SESSION = $('#PK_SESSION').val();
					
				if(document.getElementById('PK_CAMPUS'))
					PK_CAMPUS = $('#PK_CAMPUS').val();

				var data  = 'PK_STUDENT_GROUP='+PK_STUDENT_GROUP+'&PK_TERM_MASTER='+PK_TERM_MASTER+'&PK_CAMPUS_PROGRAM='+PK_CAMPUS_PROGRAM+'&PK_STUDENT_STATUS='+PK_STUDENT_STATUS+'&PK_STUDENT_COURSE=<?=$_GET['id']?>'+'&PK_COURSE='+PK_COURSE+'&PK_COURSE_OFFERING='+PK_COURSE_OFFERING+'&PK_SESSION='+PK_SESSION+'&PK_CAMPUS='+PK_CAMPUS+'&LEAD='+LEAD+'&type=stu_bulk_update';
				
				<? if($_GET['t'] == 2 || $_GET['t'] == 3 || $_GET['t'] == 4) { ?>
					data += '&active_enroll=1'
				<? } ?>
				
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
		function show_btn(){
			
			var flag = 0;
			var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
			for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
				if(PK_STUDENT_ENROLLMENT[i].checked == true) {
					flag++;
					break;
				}
			}
			
			if(flag == 1)
				document.getElementById('btn').style.display = 'block';
			else
				document.getElementById('btn').style.display = 'none';
			
		}
		
		function get_course_offering(val){
			jQuery(document).ready(function($) { 
				var data  = 'val='+$('#PK_COURSE').val()+'&multiple=0';
				var value = $.ajax({
					url: "ajax_get_course_offering",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						document.getElementById('PK_COURSE_OFFERING_DIV').innerHTML = data;
						document.getElementById('PK_COURSE_OFFERING').setAttribute('multiple', true);
						document.getElementById('PK_COURSE_OFFERING').name = "PK_COURSE_OFFERING[]"
						$("#PK_COURSE_OFFERING option[value='']").remove();
						
						$('#PK_COURSE_OFFERING').multiselect({
							includeSelectAllOption: true,
							allSelectedText: 'All <?=COURSE_OFFERING_PAGE_TITLE?>',
							nonSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?>',
							numberDisplayed: 2,
							nSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?> selected'
						});
					}		
				}).responseText;
			});
		}
		function get_course_offering_session(){
		}
		function fun_select_all(){
			var str = '';
			if(document.getElementById('SEARCH_SELECT_ALL').checked == true)
				str = true;
			else
				str = false;
				
			var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
			for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
				PK_STUDENT_ENROLLMENT[i].checked = str
			}
			show_btn()
		}
		
		function add_to_update_list(){
			jQuery(document).ready(function($) { 
				var str = '';
				var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
				for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
					if(PK_STUDENT_ENROLLMENT[i].checked == true) {
						if(str != '')
							str += ',';
							
						str += PK_STUDENT_ENROLLMENT[i].value;
					}
				}
				
				var str1 = '';
				var PK_STUDENT_ENROLLMENT_1 = document.getElementsByName('PK_STUDENT_ENROLLMENT_1[]')
				for(var i = 0 ; i < PK_STUDENT_ENROLLMENT_1.length ; i++){
					if(str1 != '')
						str1 += ',';
						
					str1 += PK_STUDENT_ENROLLMENT_1[i].value;
				}
				
				var data  = 'str='+str+'&str1='+str1;
				var value = $.ajax({
					url: "ajax_get_student_details_from_id",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						$("#student_update_table tbody").append(data)
						check_tr_count()
					}		
				}).responseText;
			});
		}
		function check_tr_count(){
			if(document.getElementById('action_btn')){
				var rowCount = document.getElementById('student_update_table').rows.length; 
				if(rowCount > 1) {
					<? if($_GET['t'] == 2 || $_GET['t'] == 3){ ?>
						document.getElementById('action_btn').style.display = 'flex'
					<? } else { ?>
						document.getElementById('action_btn').style.display = 'inline'
					<? }?>
				} else
					document.getElementById('action_btn').style.display = 'none'
			}
		}
		
		function delete_row(id){
			jQuery(document).ready(function($) { 
				$("#stu_tr_"+id).remove()
				check_tr_count()
			});
		}
		
		function get_status(){
			jQuery(document).ready(function($) { 
				var SHOW_LEAD = 0;
				if(document.getElementById('STUDENT_TYPE_2').checked == true)
					SHOW_LEAD = 1;
					
				var data  = 'SHOW_LEAD='+SHOW_LEAD+'&t=<?=$_GET['t']?>';
				var value = $.ajax({
					url: "ajax_get_status",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						document.getElementById('PK_STUDENT_STATUS_DIV').innerHTML = data;
						
						$('#PK_STUDENT_STATUS').multiselect({
							includeSelectAllOption: true,
							allSelectedText: 'All <?=STATUS?>',
							nonSelectedText: '<?=STATUS?>',
							numberDisplayed: 1,
							nSelectedText: '<?=STATUS?> selected'
						});
					}		
				}).responseText;
			});
		}
	</script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_COURSE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=COURSE_CODE?>',
			nonSelectedText: '<?=COURSE_CODE?>',
			numberDisplayed: 2,
			nSelectedText: '<?=COURSE_CODE?> selected'
		});
		$('#PK_STUDENT_GROUP').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=GROUP_CODE?>',
			nonSelectedText: '<?=GROUP_CODE?>',
			numberDisplayed: 2,
			nSelectedText: '<?=GROUP_CODE?> selected'
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
		$('#PK_COURSE_OFFERING').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=COURSE_OFFERING_PAGE_TITLE?>',
			nonSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?>',
			numberDisplayed: 2,
			nSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?> selected'
		});
		$('#PK_SESSION').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=SESSION?>',
			nonSelectedText: '<?=SESSION?>',
			numberDisplayed: 2,
			nSelectedText: '<?=SESSION?> selected'
		});
		
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