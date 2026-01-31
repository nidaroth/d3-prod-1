<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");

require_once("../global/mail.php"); 
require_once("../global/texting.php"); 
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$res = $db->Execute("SELECT * FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	$STU_DEFAULT_PASSWORD = $res->fields['STU_DEFAULT_PASSWORD'];
	
	$i = 0;
	$PK_STUDENT_ENROLLMENT_IDS = '';
	foreach($_POST['PK_STUDENT_MASTER_1'] as $PK_STUDENT_MASTER){
		
		$res = $db->Execute("SELECT CONCAT(LAST_NAME,', ',FIRST_NAME) as NAME, STUDENT_ID FROM S_STUDENT_MASTER, S_STUDENT_ACADEMICS WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND LOGIN_CREATED = '0' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER "); 
		if($res->RecordCount() > 0) {
			
			$K = 0;
			do {
				$STUDENT_ID = $res->fields['STUDENT_ID'];
				if($K > 0)
					$STUDENT_ID .= $K;
					
				$res_key = $db->Execute("SELECT USER_ID FROM Z_USER where USER_ID = '$STUDENT_ID'");
				$K++;
			} while ($res_key->RecordCount() > 0);
			
			do {
				$USER_API_KEY = generateRandomString(60);
				$res_key = $db->Execute("SELECT PK_USER FROM Z_USER where USER_API_KEY = '$USER_API_KEY'");
			} while ($res_key->RecordCount() > 0);

			$STUDENT_MASTER['LOGIN_CREATED'] = 1;
			db_perform('S_STUDENT_MASTER', $STUDENT_MASTER, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ");
			
			$salt = substr(strtr(base64_encode(openssl_random_pseudo_bytes(22)),'+','.'),0,22);
			$hash = crypt($STU_DEFAULT_PASSWORD, '$2y$12$' . $salt);
			$USER['PASSWORD']  	 	= $hash;
			$USER['ID']  	 	 	= $PK_STUDENT_MASTER;
			$USER['USER_API_KEY']  	= $USER_API_KEY;
			$USER['USER_ID']  		= $STUDENT_ID;
			$USER['PK_USER_TYPE']  	= 3;
			$USER['PK_LANGUAGE']  	= 1;
			$USER['FIRST_LOGIN']  	= 1;
			$USER['PK_ACCOUNT']  	= $_SESSION['PK_ACCOUNT'];
			$USER['CREATED_BY']  	= $_SESSION['PK_USER'];
			$USER['CREATED_ON']  	= date("Y-m-d H:i");
			db_perform('Z_USER', $USER, 'insert');
			
			$res_noti = $db->Execute("SELECT PK_EMAIL_TEMPLATE,PK_TEXT_TEMPLATE FROM S_NOTIFICATION_SETTINGS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EVENT_TYPE = 10");
			if($res_noti->RecordCount() > 0) {
				if($res_noti->fields['PK_EMAIL_TEMPLATE'] > 0) {
					send_portal_access_mail($PK_STUDENT_MASTER,$res_noti->fields['PK_EMAIL_TEMPLATE'],$USER['USER_ID'],$STU_DEFAULT_PASSWORD);
				}
				
				if($res_noti->fields['PK_TEXT_TEMPLATE'] > 0) {
					send_portal_access_text($PK_STUDENT_MASTER,$res_noti->fields['PK_TEXT_TEMPLATE'],$USER['USER_ID'],$STU_DEFAULT_PASSWORD);
				}
			}
			
			if($PK_STUDENT_ENROLLMENT_IDS != '')
				$PK_STUDENT_ENROLLMENT_IDS .= ',';
			$PK_STUDENT_ENROLLMENT_IDS .= $_POST['PK_STUDENT_ENROLLMENT_1'][$i];
		}
		$i++;
	}
	
	$STUDENT_BULK_LOGIN['PK_STUDENT_ENROLLMENT']  	= $PK_STUDENT_ENROLLMENT_IDS;
	$STUDENT_BULK_LOGIN['PK_ACCOUNT']  				= $_SESSION['PK_ACCOUNT'];
	$STUDENT_BULK_LOGIN['CREATED_BY']  				= $_SESSION['PK_USER'];
	$STUDENT_BULK_LOGIN['CREATED_ON']  				= date("Y-m-d H:i");
	db_perform('S_STUDENT_BULK_LOGIN', $STUDENT_BULK_LOGIN, 'insert');
	$id = $db->insert_ID();
	
	header("location:student_bulk_login_data_sheet?id=".$id);
	
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
	<title><?=MNU_CREATE_STUDENT_LOGIN?> | <?=$title?></title>
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
						<? echo BULK.' - '.MNU_CREATE_STUDENT_LOGIN ?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
									<div class="row" style="padding-bottom:10px;" >
										<div class="col-md-2 ">
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control">
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS, DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by STUDENT_STATUS ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
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
													<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>  ><?=$str ?></option>
												<?	$res_type->MoveNext();
												} /* Ticket #1149 - term */ ?>
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
											<select id="PK_STUDENT_GROUP" name="PK_STUDENT_GROUP[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select PK_STUDENT_GROUP,STUDENT_GROUP from M_STUDENT_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by STUDENT_GROUP ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_GROUP']?>" ><?=$res_type->fields['STUDENT_GROUP']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
									</div>
									<div class="row">
										
										<div class="col-md-2 ">
											<select id="PK_COURSE" name="PK_COURSE[]" multiple class="form-control" onchange="get_course_offering(this.value)" >
												<? /* Ticket # 1740  */
												$res_type = $db->Execute("select PK_COURSE, COURSE_CODE, TRANSCRIPT_CODE, COURSE_DESCRIPTION from S_COURSE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by COURSE_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_COURSE']?>" ><?=$res_type->fields['COURSE_CODE'].' - '.$res_type->fields['TRANSCRIPT_CODE'].' - '.$res_type->fields['COURSE_DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} /* Ticket # 1740  */ ?>
											</select>
										</div>
										
										<div class="col-md-2 " id="PK_COURSE_OFFERING_DIV" >
											<select id="PK_COURSE_OFFERING" name="PK_COURSE_OFFERING[]" multiple class="form-control" >
											</select>
										</div>
										
										<div class="col-md-5 align-self-center "></div>
										<div class="col-md-3 " style="text-align:right" >
											<button type="button" class="btn waves-effect waves-light btn-info" id="btn" style="display:none" onclick="add_to_update_list()" ><?=ADD_TO_BULK_CREATE_LIST?></button>
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
													<th><?=STATUS?></th>
													<th><?=FIRST_TERM?></th>
													<th><?=PROGRAM?></th>
													<th><?=GROUP_CODE?></th>
													<th><?=ACTION?></th>
												</tr>
											</thead>
											<tbody>
											</tbody>
										</table>
									</div>
									<div id="action_div" >
										<div class="d-flex">
											<div class="col-12 col-sm-6 form-group">&nbsp;</div>
											<div class="col-12 col-sm-3 form-group">
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=CREATE_LOGIN?></button>
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
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');
		
		function search(){
			jQuery(document).ready(function($) {
				var data  = 'PK_STUDENT_GROUP='+$('#PK_STUDENT_GROUP').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&PK_STUDENT_COURSE=<?=$_GET['id']?>'+'&PK_COURSE='+$('#PK_COURSE').val()+'&PK_CAMPUS='+$('#PK_CAMPUS').val()+'&PK_COURSE_OFFERING='+$('#PK_COURSE_OFFERING').val()+'&type=bulk_create_login';
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
				document.getElementById('btn').style.display = 'inline';
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
				
				var data  = 'str='+str+'&str1='+str1+'&type=bulk_create_login';;
				var value = $.ajax({
					url: "ajax_get_student_details_from_id",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						$("#student_update_table tbody").append(data)
					}		
				}).responseText;
			});
		}
		
		function delete_row(id){
			jQuery(document).ready(function($) { 
				$("#stu_tr_"+id).remove()
			});
		}
		
		function get_course_offering_session(){
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