<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
require_once("check_access.php");

if(check_access('ADMISSION_ACCESS') == 0 && check_access('REGISTRAR_ACCESS') == 0 && check_access('FINANCE_ACCESS') == 0 && check_access('ACCOUNTING_ACCESS') == 0 && check_access('PLACEMENT_ACCESS') == 0 ){ 
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
	<title><?=TRACK_CHANGES?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? //require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-3 align-self-center">
                        <h4 class="text-themecolor">
							<?=TRACK_CHANGES ?> 
							
							<a target="_blank" href="track_changes_excel" title="<?=EXCEL?>" class="btn waves-effect waves-light btn-info" ><?=EXCEL?></a>
						</h4>
                    </div>
					<div class="col-md-3 align-self-center text-right">
						<select id="FIELD_NAME" name="FIELD_NAME" class="form-control" onchange="doSearch()">
							<option value="" ><?=FIELD_NAME?></option>
							<? $res_type = $db->Execute("select DISTINCT(FIELD_NAME) from S_STUDENT_TRACK_CHANGES WHERE PK_STUDENT_MASTER = '$_GET[id]' and (PK_STUDENT_ENROLLMENT = '$_GET[eid]' or GLOBAL_CHANGE = 1) ORDER BY FIELD_NAME ASC");
							while (!$res_type->EOF) { ?>
								<option value="<?=$res_type->fields['FIELD_NAME']?>" ><?=$res_type->fields['FIELD_NAME']?></option>
							<?	$res_type->MoveNext();
							} ?>
						</select>
					</div>
					<div class="col-md-2 align-self-center text-right">
						<input class="form-control date" type="text" value="" name="FROM_DATE" id="FROM_DATE" placeholder="<?=FROM_DATE?>" onchange="doSearch()" />
					</div>
					<div class="col-md-2 align-self-center text-right">
						<input class="form-control date" type="text" value="" name="TO_DATE" id="TO_DATE" placeholder="<?=TO_DATE?>" onchange="doSearch()" />
					</div>
					<div class="col-md-2 align-self-center text-right">
						<select id="CHANGED_BY" name="CHANGED_BY" class="form-control" onchange="doSearch()">
							<option value="" ><?=CHANGED_BY?></option>
							<? $res_type = $db->Execute("SELECT CHANGED_BY,CONCAT(S_EMPLOYEE_MASTER.LAST_NAME,', ',S_EMPLOYEE_MASTER.FIRST_NAME) AS NAME FROM S_STUDENT_TRACK_CHANGES, Z_USER, S_EMPLOYEE_MASTER WHERE PK_STUDENT_MASTER = '$_REQUEST[id]' and (PK_STUDENT_ENROLLMENT = '$_REQUEST[eid]' or GLOBAL_CHANGE = 1) AND Z_USER.PK_USER = S_STUDENT_TRACK_CHANGES.CHANGED_BY AND S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID GROUP BY CHANGED_BY");
							while (!$res_type->EOF) { ?>
								<option value="<?=$res_type->fields['CHANGED_BY']?>" ><?=$res_type->fields['NAME']?></option>
							<?	$res_type->MoveNext();
							} ?>
						</select>
					</div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div id="res_div">
									<? $_REQUEST['id'] 	= $_GET['id'];
									$_REQUEST['eid'] 	= $_GET['eid'];
									
									include("ajax_track_changes.php"); ?>
								</div>
                                
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
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
	});
	
	function doSearch(){
		jQuery(document).ready(function($) { 
			var data  = 'FIELD_NAME='+$('#FIELD_NAME').val()+'&FROM_DATE='+$('#FROM_DATE').val()+'&TO_DATE='+$('#TO_DATE').val()+'&CHANGED_BY='+$('#CHANGED_BY').val()+'&id=<?=$_GET['id']?>&eid=<?=$_GET['eid']?>';
			var value = $.ajax({
				url: "ajax_track_changes",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {
					document.getElementById('res_div').innerHTML = data;
				}		
			}).responseText;
		});
	}
	</script>
</body>

</html>