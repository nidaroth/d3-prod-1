<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/acics.php");
require_once("check_access.php");

// Edited By 
$ress = $db->Execute("SELECT EDITED_ON, EDITED_BY FROM ACCET_SETUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if ($ress->RecordCount() == 0) {
    header("location:accet_report_setup");
    exit;
}
$EDITED_ON_1        = '';
if($ress->fields['EDITED_ON'] == '0000-00-00 00:00:00')
{
    $EDITED_ON_1    = '';
}
else{
    $EDITED_ON_1    = date("m/d/Y",strtotime($ress->fields['EDITED_ON']));
}
$EDITED_ON    	    = $ress->fields['EDITED_BY'];

$res_usr_name = $db->Execute("SELECT FIRST_NAME,LAST_NAME FROM S_EMPLOYEE_MASTER,Z_USER WHERE Z_USER.PK_USER = '$EDITED_ON' AND Z_USER.ID = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER");
$Edited_Name_1 = "";
if ($res_usr_name->RecordCount() == 1) {
    $Edited_Name_1 = $res_usr_name->fields['LAST_NAME'].', '.$res_usr_name->fields['FIRST_NAME'];
}
// End - Edited By 

// Campus 
$res_type_campus = $db->Execute("select OFFICIAL_CAMPUS_NAME,PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by OFFICIAL_CAMPUS_NAME ASC");
// End Campus

if(check_access('MANAGEMENT_ACCREDITATION') == 0 ){
	header("location:../index");
	exit;
}

$res = $db->Execute("SELECT ACCET FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
if($res->fields['ACCET'] == 0 || $res->fields['ACCET'] == '') {
	header("location:../index");
	exit;
}

$report_error="";
// Reports
if(!empty($_POST))
{
    $REPORT_OPTION = $_POST['REPORT_OPTION'];
    $campus_cond = "";
    if(!empty($_POST['PK_CAMPUS'])){
		$PK_CAMPUS 	 = implode(",",$_POST['PK_CAMPUS']);
		//$campus_cond = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
	}

    	
	$ST = '';
	$ET = '';

	if($_POST['START_DATE'] != ''){
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
    }
		
	if($_POST['END_DATE'] != ''){
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
    }

  //Begin Reports 
    if($REPORT_OPTION == 1){    
    //Completion placement     
    include('accet_completion_placement_report_excel.php');

    }else if($REPORT_OPTION == 2){

    //Placement Data 
    include('placement_data_excel.php');

    }

 //End Reports
            
} // POST IF END
// End Reports
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
	<title><?=MNU_ACCET ?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		#advice-required-entry-PK_CAMPUS{position: absolute;top: 57px; width:150px}
        .title-adjustment{padding-bottom: 12px;padding-top: 15px;}
        .adjust-sub-menu{padding-left:10px;padding-top:2px;}
        .button-adjustment{text-align: right;}
        .edited-by{font-weight: 500;padding-top: 7px;}
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
							<?=MNU_ACCET ?>
						</h4>
                    </div>
                </div>
				
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
                                  <form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" > 
                                    
                                        <div class="row">
                                            <div class="col-md-6"></div>
                                            <div class="col-md-6">    
                                                <div class="button-adjustment">
                                                    <button type="button" onclick="window.location.href='accet_report_setup'" class="btn waves-effect waves-light btn-info">Report Setup</button>
                                                    <div class="edited-by">Edited : 
                                                    <? 
                                                    if($EDITED_ON_1 != ''){
                                                        echo $Edited_Name_1.' '.$EDITED_ON_1;
                                                    }
                                                    else{
                                                        echo 'N/A';
                                                    }?>
                                                    </div>
                                                </div>                                            
                                            </div>
                                        </div>
                                        <!-- <div class="row">
                                            <div class="col-md-6"></div>
                                            <div class="col-md-6">    
                                                <div class="button-adjustment">
                                                    <button type="button" onclick="window.location.href='oeds_report_curriculum_setup'" class="btn waves-effect waves-light btn-info">Curriculum Setup</button>
                                                    <div class="edited-by">Edited : 
                                                    <? 
                                                    // if($EDITED_ON_1 != ''){
                                                    //     echo $Edited_Name_1.' '.$EDITED_ON_1;
                                                    // }
                                                    // else{
                                                    //     echo 'N/A';
                                                    // }?>
                                                    </div>
                                                </div>                                            
                                            </div>
                                        </div> -->
                                        <div class="row">
                                         
                                            <div class="col-md-3 " >   
                                                <div id="report_option_div"> 
                                                    <b><?=REPORT_OPTION?></b>
                                                    <select id="REPORT_OPTION" name="REPORT_OPTION" onchange="ReportFilters(this.value)" class="form-control">
                                                        <?php
                                                         switch ($REPORT_OPTION) {
                                                            case '1':
                                                                $ro_selected="selected";
                                                                break;
                                                            case '2':
                                                                $ro_selected="selected";
                                                                break;
                                                            default:
                                                                $ro_selected="";
                                                                break;
                                                         }
                                                         $ro_selected="";
                                                         ?>
                                                        <option value="1" <?=$ro_selected?>>Doc 28.1 Completion & Placement Statistics</option>
                                                        <option value="2"  <?=$ro_selected?>>Placement Data</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2 ">
                                                <?=CAMPUS?>
                                                <select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control required-entry" >
                                                    <? 
                                                    while (!$res_type_campus->EOF) { ?>
                                                        <option value="<?=$res_type_campus->fields['PK_CAMPUS']?>" ><?=$res_type_campus->fields['CAMPUS_CODE']?></option>
                                                    <?	$res_type_campus->MoveNext();
                                                    } ?>
                                                </select>
                                            </div>
                                            <div class="col-md-2 ">
                                                <?=START_DATE?>
                                                <input type="text" class="form-control date required-entry" id="START_DATE" name="START_DATE" value="" >
                                            </div>
                                            <div class="col-md-2 ">
                                                <?=END_DATE?>
                                                <input type="text" class="form-control date required-entry" id="END_DATE" name="END_DATE" value="" >
                                            </div>
                                            <!-- <div class="col-md-2 align-self-center" id="GROUP_PROGRAM_CODE_DIV">
                                                <div class="col-12 col-sm-12 custom-control custom-checkbox form-group">
                                                    <input type="checkbox" class="custom-control-input" id="GROUP_PROGRAM_CODE" name="GROUP_PROGRAM_CODE" value="1">
                                                    <label class="custom-control-label" for="GROUP_PROGRAM_CODE"><?//= GROUP_PROGRAM_CODE ?></label>
                                                </div>
                                            </div> -->
                                            <div class="col-md-3">
                                                <br />
                                                <button type="button" onclick="submit_form()" id="btn_1" class="btn waves-effect waves-light btn-info" style="display:inline-block"><?=PDF?></button>
                                                <button type="button" onclick="submit_form()" id="btn_2" class="btn waves-effect waves-light btn-info" style="display:inline-block"><?=EXCEL?></button>
                                                <input type="hidden" name="FORMAT" id="FORMAT" >
                                            </div>

                                         
                                        </div>
                                        <br /><br /><br />
                                        <!-- <div class="row"></div> -->
                                  </form>  
          
								  <br /><br />
									
								</div>
							</div>
						</div>
					</div>
            </div>
        </div>
        <? require_once("footer.php"); ?>

        <?php if($report_error!="") {?>
		<div class="modal" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1">Warning</h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group" style="color: red;font-size: 15px;">
							<b><?php echo $report_error; ?></b>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" data-dismiss="modal" class="btn waves-effect waves-light btn-info">Cancel</button>
					</div>
				</div>
			</div>
		</div>
		<?php } ?>
    </div>
   
	<? require_once("js.php"); ?>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	
	<script src="../backend_assets/dist/js/jquery-migrate-1.0.0.js"></script>
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />

	<script type="text/javascript">
        
	jQuery(document).ready(function($) { 

        ReportFilters(1);

		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
	});

    var error= '<?php echo  $report_error; ?>';
    jQuery(document).ready(function($) {
        if(error!=""){
        jQuery('#errorModal').modal();
        }
    });

    function ReportFilters(report_value) {

        if (report_value == 1 || report_value == 2) {
            document.getElementById('btn_1').style.display = 'none';
        } 
        else 
        {
            document.getElementById('btn_2').style.display = 'none';
            document.getElementById('btn_1').style.display = 'inline';
        }
    }

    function submit_form(){
        jQuery(document).ready(function($) {
            var valid = new Validation('form1', {onSubmit:false});
			var result = valid.validate();
			if(result == true){ 
				//document.getElementById('FORMAT').value = val
				document.form1.submit();
			}
        });
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
			numberDisplayed: 1,
			nSelectedText: '<?=CAMPUS?> selected'
		});
	});
	</script>

    <?php $report_error=""; ?>
</body>

</html>