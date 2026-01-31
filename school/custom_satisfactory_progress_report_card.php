<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
require_once("check_access.php");

if(check_access('REPORT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){

	$PK_STUDENT_MASTER_ARR = array();
		foreach($_POST['PK_STUDENT_ENROLLMENT'] as $PK_STUDENT_ENROLLMENT) {
			$PK_STUDENT_MASTER_ARR[$_POST['PK_STUDENT_MASTER_'.$PK_STUDENT_ENROLLMENT]] = $_POST['PK_STUDENT_MASTER_'.$PK_STUDENT_ENROLLMENT];
		}
		$PK_STUDENT_MASTER		= implode(",",$PK_STUDENT_MASTER_ARR);
		$PK_STUDENT_ENROLLMENT 	= implode(",",$_POST['PK_STUDENT_ENROLLMENT']);

		$_GET['eid']=$PK_STUDENT_ENROLLMENT;
		$_GET['id']=$PK_STUDENT_MASTER;
		$_GET['campus']=implode(",",$_POST['PK_CAMPUS']);
		$_GET['exclude_tc']=$_POST['EXCLUDE_TRANSFERS_COURSE'];
		$_GET['download_via_js']='yes';
		require_once('custom_satisfactory_progress_report_card_pdf.php');			
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
	<title><?=MNU_SATISFACTORY_PROGRESS_REPORT?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		.dropdown-menu>li>a { white-space: nowrap; max-width: 90%} /* Ticket # 1740  */
		#advice-required-entry-FGA_COURSE_PK_TERM, #advice-required-entry-FGA_COURSE_PK_COURSE_OFFERING, #advice-required-entry-GS_PK_COURSE_OFFERING, #advice-required-entry-PK_COURSE_OFFERING_GPA1, #advice-required-entry-FGA_FINAL_GRADE{position: absolute;top: 38px;}
		#advice-required-entry-term_begin_start_date,#advice-required-entry-term_begin_end_date{position: absolute;top: 60px;}
		
		.lds-ring {
			position: absolute;
			left: 0;
			top: 0;
			right: 0;
			bottom: 0;
			margin: auto;
			width: 64px;
			height: 64px;
		}

		.lds-ring div {
			box-sizing: border-box;
			display: block;
			position: absolute;
			width: 51px;
			height: 51px;
			margin: 6px;
			border: 6px solid #0066ac;
			border-radius: 50%;
			animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
			border-color: #007bff transparent transparent transparent;
		}

		.lds-ring div:nth-child(1) {
			animation-delay: -0.45s;
		}

		.lds-ring div:nth-child(2) {
			animation-delay: -0.3s;
		}

		.lds-ring div:nth-child(3) {
			animation-delay: -0.15s;
		}

		@keyframes lds-ring {
			0% {
				transform: rotate(0deg);
			}

			100% {
				transform: rotate(360deg);
			}
		}

		#loaders {
			position: fixed;
			width: 100%;
			z-index: 9999;
			bottom: 0;
			background-color: #2c3e50;
			display: block;
			left: 0;
			top: 0;
			right: 0;
			bottom: 0;
			opacity: 0.6;
			display: none;
		}	
		/* DIAM-1753 */
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
   <div id="loaders" style="display: none;"> <!--DIAM-1753-->
		<div class="lds-ring">
			<div></div>
			<div></div>
			<div></div>
			<div></div>
		</div>
	</div>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">
							<?=MNU_SATISFACTORY_PROGRESS_REPORT?>
						</h4>
                    </div>
                </div>
				
				<div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels" method="post" name="form1" id="form1" >
																
									<div class="row" style="padding-bottom:10px;" >
										<!-- Ticket # 1472  -->
										<div class="col-md-2" id="PK_CAMPUS_DIV"  >
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" onchange="clear_search()" >
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>										
										
										<!-- Ticket # 1472  -->										
										<div class="col-md-2 "  id="PK_TERM_MASTER_DIV" >
											<select id="PK_TERM_MASTER" name="PK_TERM_MASTER[]" multiple class="form-control" onchange="clear_search()" >
												<? $res_type = $db->Execute("select PK_TERM_MASTER,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by BEGIN_DATE DESC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" ><?=$res_type->fields['BEGIN_DATE_1']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 " id="PK_CAMPUS_PROGRAM_DIV" >
											<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control" onchange="clear_search()" >
												<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 " id="PK_STUDENT_STATUS_DIV" >
											<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control" onchange="clear_search()" >
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS, DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ADMISSIONS = 0 order by STUDENT_STATUS ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 " id="PK_STUDENT_GROUP_DIV" >
											<select id="PK_STUDENT_GROUP" name="PK_STUDENT_GROUP[]" multiple class="form-control" onchange="clear_search()" >
												<? $res_type = $db->Execute("select PK_STUDENT_GROUP,STUDENT_GROUP from M_STUDENT_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by STUDENT_GROUP ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_GROUP']?>" ><?=$res_type->fields['STUDENT_GROUP']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
									</div>
									

									<div class="row">	
										<div class="col-md-2 " id="PK_COURSE_DIV" >
											<select id="PK_COURSE" name="PK_COURSE[]" multiple class="form-control" onchange="get_course_offering(this.value);clear_search()" >
												<? /* Ticket # 1740  */
												$res_type = $db->Execute("select PK_COURSE, COURSE_CODE, TRANSCRIPT_CODE, COURSE_DESCRIPTION from S_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by COURSE_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_COURSE']?>" ><?=$res_type->fields['COURSE_CODE'].' - '.$res_type->fields['TRANSCRIPT_CODE'].' - '.$res_type->fields['COURSE_DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} /* Ticket # 1740  */ ?>
											</select>
										</div>
										
										<div class="col-md-2 " id="PK_COURSE_OFFERING_DIV" >
											<select id="PK_COURSE_OFFERING" name="PK_COURSE_OFFERING" class="form-control" >
												<option value=""><?=COURSE_OFFERING_PAGE_TITLE?></option>
											</select>
										</div>
																
										
										<div class="col-md-2" id="EXCLUDE_TRANSFERS_COURSE_DIV" style="display:none;">
											<div class="custom-control custom-checkbox mr-sm-12">
												<input type="checkbox" class="custom-control-input" id="EXCLUDE_TRANSFERS_COURSE" name="EXCLUDE_TRANSFERS_COURSE" value="1" >
												<label class="custom-control-label" for="EXCLUDE_TRANSFERS_COURSE" ><?=EXCLUDE_TRANSFERS_COURSE?></label>
											</div>
										</div>
																			
										
										<div class="col-md-2 align-self-center ">
											<button type="button" onclick="search()" class="btn waves-effect waves-light btn-info" id="search_btn" ><?=SEARCH?></button>
										
											<button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info" id="btn_1" style="display:none" ><?=PDF?></button>
											<!-- <button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info" id="btn_4" style="display:none" ><?//=EXCEL?></button> -->
											<input type="hidden" name="FORMAT" id="FORMAT" >
										</div>
									</div>
									
									<!-- Ticket # 1552 -->
									<div class="row" style="margin-top:10px" >
										<div class="col-md-2 " id="SEARCH_TXT_DIV" style="display:none" >
											<input type="text" class="form-control" id="SEARCH_TXT" name="SEARCH_TXT" placeholder="&#xF002; <?=SEARCH?>" style="font-family: FontAwesome;" onkeypress="do_search(event)" >
										</div>
									</div>
									<!-- Ticket # 1552 -->
									
									<br /><br />
									<div id="student_div" >
								
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
		function do_search(e){			
			if (e.keyCode == 13) {
				search();
				e.preventDefault();
     			return false;
			}
		}	

		function clear_search(){
			document.getElementById('student_div').innerHTML = '';
			show_btn()
		}		
		
		function get_course_offering(val){
			var set_notification=false;
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
						
						document.getElementById('PK_COURSE_OFFERING').setAttribute("onchange", "clear_search()");
						
						$('#PK_COURSE_OFFERING').multiselect({
							includeSelectAllOption: true,
							allSelectedText: 'All <?=COURSE_OFFERING_PAGE_TITLE?>',
							nonSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?>',
							numberDisplayed: 2,
							nSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?> selected'
						});
						set_notification=true;
					}		
				}).responseText;
			});
		}
			
		/* Ticket # 1472 */
		function search(){
			jQuery(document).ready(function($) {	
					var set_notification=false;			
					var data  = 'PK_STUDENT_GROUP='+$('#PK_STUDENT_GROUP').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&PK_COURSE='+$('#PK_COURSE').val()+'&PK_COURSE_OFFERING='+$('#PK_COURSE_OFFERING').val()+'&PK_CAMPUS='+$('#PK_CAMPUS').val()+'&show_check=1&show_count=1&group_by=&ENROLLMENT=1'+'&SEARCH_TXT='+$('#SEARCH_TXT').val(); //Ticket # 1552;
					var value = $.ajax({
						url: "ajax_search_student_for_reports",	
						type: "POST",		 
						data: data,		
						async: true,
						cache: false,
						beforeSend: function() {
						  document.getElementById('loaders').style.display = 'block';							
						},
						success: function (data) {								
							document.getElementById('student_div').innerHTML = data
							show_btn()
							document.getElementById('loaders').style.display = 'none'; //
							document.getElementById('SEARCH_TXT_DIV').style.display = 'block';		
							
						}		
					}).responseText;
					set_notification=true;
			});
		}
		/* Ticket # 1472 */		
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
						
			get_count()
		}
		function get_count(){
			var tot = 0
		
				var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
				for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
					if(PK_STUDENT_ENROLLMENT[i].checked == true)
						tot++;
				}
			
			document.getElementById('SELECTED_COUNT').innerHTML = tot
			show_btn()
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
			
			
			if(flag == 1) {						
				document.getElementById('btn_1').style.display = 'inline';
			} else {
				document.getElementById('btn_1').style.display = 'none';
			}
		}
		
	
				
		function submit_form(val){
			var set_notification=false;	
			jQuery(document).ready(function($) {
				var valid = new Validation('form1', {onSubmit:false});
				var result = valid.validate();
				document.getElementById('FORMAT').value = val
				show_only_selected();
				if(result == true){ 
						var serialized_array = $('#form1').serialize();
							console.log(serialized_array);
							var value = $.ajax({
							url: "custom_satisfactory_progress_report_card",	
							type: "POST",		 
							data: serialized_array,		
							async: true,
							cache: false,
							beforeSend: function() {
								document.getElementById('loaders').style.display = 'block'; 
							},
							success: function (data) {	
								/* Ticket # 1979 */
								// console.log("success");
								// console.log(data);
								const text = window.location.href;
								const word = '/school';
								const textArray = text.split(word); // ['This is ', ' text...']
								const result = textArray.shift();

								downloadDataUrlFromJavascript(data.filename, result + '/school/' + data.path);
								//console.log(data.filename);
								search();
								document.getElementById('loaders').style.display = 'none'; 

							}		
						}).responseText;
					return;
				
					
					document.form1.submit();
					search();
				}
				set_notification=true;	
			});
		}
		function downloadDataUrlFromJavascript(filename, dataUrl) {

			// Construct the 'a' element
			var link = document.createElement("a");
			link.download = filename;
			link.target = "_blank";

			// Construct the URI
			link.href = dataUrl;
			document.body.appendChild(link);
			link.click();

			// Cleanup the DOM
			document.body.removeChild(link);
			delete link;
		}
		/* Ticket # 1472 */
				
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
		/* Ticket # 1472 */
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS ?>',
			nonSelectedText: '<?=CAMPUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		/* Ticket # 1472 */				
	});

	function show_only_selected(){
		//RUN DELETE ONLY IF ANY SINGLE IS SELECTED  
		//alert(jQuery(".delete_if_not_selected:checked").length);
		if( jQuery(".delete_if_not_selected:checked").length> 0)
		{
			jQuery(".delete_if_not_selected:not(:checked)").parent().parent().remove();
		} 
	}	
	</script>
</body>
</html>
