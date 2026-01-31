<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/sap_scale.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}
// $sap_pk_array=array('15','67','72','64');
// if(!in_array($_SESSION['PK_ACCOUNT'],$sap_pk_array))
// {   
// 	header("location:../school/index");
// 	exit;
// }

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
	<title><?=MNU_SAP_GLOBAL?> | <?=$title?></title>
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
						<?=MNU_SAP_GLOBAL ?> </h4>
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
												<? $res_type = $db->Execute("SELECT CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_TERM_MASTER" name="PK_TERM_MASTER[]" multiple class="form-control" >
												<? /* Ticket #1149 - term */
												$res_type = $db->Execute("SELECT PK_TERM_MASTER, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION, ACTIVE from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, BEGIN_DATE DESC");
												while (!$res_type->EOF) { 
													$str = $res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['END_DATE_1'];
													if($res_type->fields['ACTIVE'] == 0)
														$str .= ' (Inactive)'; ?>
													<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>  ><?=$str ?></option>
												<?	$res_type->MoveNext();
												} /* Ticket #1149 - term */ ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control" >
												<? $res_type = $db->Execute("SELECT PK_CAMPUS_PROGRAM,CODE, DESCRIPTION, ACTIVE from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC,CODE ASC");
												while (!$res_type->EOF) { 
													$option_label = $res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION'];
                                                    if($res_type->fields['ACTIVE'] == 0)
                                                    {
                                                        $option_label .= " (Inactive)"; 
                                                    }
													?>
													<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>

                                        <!-- <div class="col-md-2" >
                                            <select id="PK_COURSE" name="PK_COURSE[]" multiple class="form-control"  >
                                                <?
                                                // $res_type = $db->Execute("SELECT PK_COURSE, CONCAT(COURSE_CODE, ' - ', TRANSCRIPT_CODE, ' - ', COURSE_DESCRIPTION) as TRANSCRIPT_CODE, ACTIVE from S_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, TRANSCRIPT_CODE ASC");
                                                // while (!$res_type->EOF) { 
                                                //     $option_label = $res_type->fields['TRANSCRIPT_CODE'];
                                                //     if($res_type->fields['ACTIVE'] == 0)
                                                //     {
                                                //         $option_label .= " (Inactive)"; 
                                                //     }
                                                    ?>
                                                    <option value="<?=$res_type->fields['PK_COURSE']?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label?></option>
                                                <?	//$res_type->MoveNext();
                                                //} ?>
                                            </select>
                                        </div> -->
										
										<div class="col-md-2" >
											<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control">
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS, DESCRIPTION, ADMISSIONS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = '1' AND ADMISSIONS = 0 order by ADMISSIONS DESC, STUDENT_STATUS ASC");
												while (!$res_type->EOF) { 
													$option_label = $res_type->fields['STUDENT_STATUS'] . ' - ' . $res_type->fields['DESCRIPTION'];
													
													?>
													<option value="<?= $res_type->fields['PK_STUDENT_STATUS'] ?>" ><?=$option_label ?></option>
												<? $res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_STUDENT_GROUP" name="PK_STUDENT_GROUP[]" multiple class="form-control" >
												<? $res_type = $db->Execute("SELECT PK_STUDENT_GROUP,STUDENT_GROUP,ACTIVE from M_STUDENT_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, STUDENT_GROUP ASC");
												while (!$res_type->EOF) { 
													$option_label = $res_type->fields['STUDENT_GROUP'];
                                                    if($res_type->fields['ACTIVE'] == 0)
                                                    {
                                                        $option_label .= " (Inactive)"; 
                                                    }
													?>
													<option value="<?=$res_type->fields['PK_STUDENT_GROUP']?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>

									</div>
                                    <br /><br />
                                    <div class="row">
                                        <div class="col-md-2 focused">
                                            <label ><?=MIDPOINT_START_DATE?></label>
                                            <input type="text" name="MIDPOINT_START_DATE" id="MIDPOINT_START_DATE" class="form-control date" value="" >
                                            <span class="bar"></span>
                                        </div>
                                        <div class="col-md-2 focused">
                                            <label ><?=MIDPOINT_END_DATE?></label>
                                            <input type="text" name="MIDPOINT_END_DATE" id="MIDPOINT_END_DATE" class="form-control date" value="" >
                                            <span class="bar"></span>
                                        </div>
                                        <div class="col-md-2" style="text-align:right;">
											<button type="button" class="btn waves-effect waves-light btn-info" id="btn" style="display:none" onclick="submit_form(1)" ><?=EXCEL?></button>
											<button type="button" class="btn waves-effect waves-light btn-info" onclick="search()" ><?=SEARCH?></button>

                                            <input type="hidden" name="FORMAT" id="FORMAT">
										</div>
                                    </div>
									<br />
									<div class="row">
										<div class="col-sm-12 pt-25 " >
											<div id="student_div">
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
   
	<? 
	require_once("js.php"); 
	$file_name      = 'SAP Global Report.xlsx';
	$outputFileName = $file_name;
	$outputFileName = str_replace(
		pathinfo($outputFileName, PATHINFO_FILENAME),
		pathinfo($outputFileName, PATHINFO_FILENAME) . "_" . $_SESSION['PK_USER'] . "_" . time(),
		$outputFileName
	);

	?>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>

	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />

	<script type="text/javascript">
		jQuery(document).ready(function($) { 
			jQuery('.date').datepicker({
				todayHighlight: true,
				orientation: "bottom auto"
			});

		});
		
		function search(){
			jQuery(document).ready(function($) {

                var data  = 'PK_CAMPUS='+$('#PK_CAMPUS').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&PK_STUDENT_GROUP='+$('#PK_STUDENT_GROUP').val()+'&MIDPOINT_START_DATE='+$('#MIDPOINT_START_DATE').val()+'&MIDPOINT_END_DATE='+$('#MIDPOINT_END_DATE').val();
				var value = $.ajax({
					url: "ajax_sap_global_report",	
					type: "POST",		 
					data: data,		
					async: true,
					cache: false,
					success: function (data) {	
						document.getElementById('student_div').innerHTML = data
					}		
				}).responseText;
			});
		}

		function show_btn()
        {
			var flag = 0;
			var PK_STUDENT_ENROLLMENT = document.getElementsByClassName('stud_enr')
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
		
		function get_count(){
			var tot = 0
			var PK_STUDENT_ENROLLMENT = document.getElementsByClassName('stud_enr')
			for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
				if(PK_STUDENT_ENROLLMENT[i].checked == true)
                {
                    tot++;
                }
			}
			document.getElementById('SELECTED_COUNT').innerHTML = tot
			show_btn()
		}

		function fun_select_all(){
			var str = '';
			if(document.getElementById('SEARCH_SELECT_ALL').checked == true)
			{
				str = true;
			}
			else
			{
				str = false;
			}
				
			var PK_STUDENT_ENROLLMENT = document.getElementsByClassName('stud_enr');
			for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++)
			{
				PK_STUDENT_ENROLLMENT[i].checked = str
			}
			get_count()
		}
		
		function submit_form(val)
        {
            var valid = new Validation('form1', {
                onSubmit: false
            });
            var result = valid.validate();
            if (result == true) {
                document.getElementById('FORMAT').value = val;
                if(val==1)
                {
					var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
					var PK_STUDENT_MASTER = document.getElementsByName('PK_STUDENT_MASTER[]')
					var data = { 'PK_STUDENT_ENROLLMENT[]' : [], 'PK_STUDENT_MASTER[]' : []};
					for (var i = 0; i < PK_STUDENT_ENROLLMENT.length; i++) {
							if (PK_STUDENT_ENROLLMENT[i].checked == true) {
								var id = PK_STUDENT_ENROLLMENT[i].value;
								data['PK_STUDENT_ENROLLMENT[]'].push(id);
								data['PK_STUDENT_MASTER[]'].push(PK_STUDENT_MASTER[i].value);
							}
					}
					//console.log(data);
                    // document.getElementById('form1').setAttribute('action','<?php echo $http_path; ?>school/sap_gloabl_report_excel.php');
					// const form = document.getElementById("form1");
					// let formData = new FormData(form);
					jQuery.ajax({
						url: "sap_gloabl_report_excel",	
						type: "POST",		 
						data: data,		
						async: true,
						cache: false,
						success: function (data) {	
							console.log(data.path);
							// document.form1.submit();
							downloadDataUrlFromJavascript('<?=$outputFileName?>',data)
						}		
					});

					// var data = { 'PK_STUDENT_ENROLLMENT[]' : [], 'PK_STUDENT_MASTER[]' : []};
					// $('input[name="PK_STUDENT_ENROLLMENT"]:checked').each(function() {
					// 	data['PK_STUDENT_ENROLLMENT[]'].push($(this).val());
					// });
					// $.post("sap_gloabl_report_excel.php", data);

					// let response = fetch('<?php echo $http_path; ?>school/sap_gloabl_report_excel.php', {
					// 	method: 'POST',
					// 	headers: {"Content-Type": "application/x-www-form-urlencoded"},
					// 	body: new FormData(form1)
					// });
                }
				
            }
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
		function show_only_selected() {
			//RUN DELETE ONLY IF ANY SINGLE IS SELECTED  
			if (jQuery(".delete_if_not_selected:checked").length > 0) {

					jQuery(".delete_if_not_selected:not(:checked)").parent().parent().remove();
			}
		}
		
	</script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {

		$('#PK_STUDENT_GROUP').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STUDENT_GROUP?>',
			nonSelectedText: '<?=STUDENT_GROUP?>',
			numberDisplayed: 2,
			nSelectedText: '<?=STUDENT_GROUP?> selected'
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
		$('#PK_COURSE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=COURSE?>',
			nonSelectedText: '<?=COURSE?>',
			numberDisplayed: 2,
			nSelectedText: '<?=COURSE?> selected'
		});
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '<?=CAMPUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		$('#PK_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STUDENT_STATUS ?>',
			nonSelectedText: '<?=STUDENT_STATUS ?>',
			numberDisplayed: 2,
			nSelectedText: '<?=STUDENT_STATUS ?> selected'
		});
		
	});
	</script>
</body>

</html>