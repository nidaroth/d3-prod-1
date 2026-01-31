<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/grade_scale.php");
require_once("check_access.php");

if(check_access('SETUP_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$GRADE_SCALE_MASTER['GRADE_SCALE'] = $_POST['GRADE_SCALE'];
	if($_GET['id'] == ''){
		$GRADE_SCALE_MASTER['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$GRADE_SCALE_MASTER['CREATED_BY']  = $_SESSION['PK_USER'];
		$GRADE_SCALE_MASTER['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('S_GRADE_SCALE_MASTER', $GRADE_SCALE_MASTER, 'insert');
		$PK_GRADE_SCALE_MASTER = $db->insert_ID();
	} else {
		$GRADE_SCALE_MASTER['ACTIVE'] 	 = $_POST['ACTIVE'];
		$GRADE_SCALE_MASTER['EDITED_BY'] = $_SESSION['PK_USER'];
		$GRADE_SCALE_MASTER['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('S_GRADE_SCALE_MASTER', $GRADE_SCALE_MASTER, 'update'," PK_GRADE_SCALE_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		$PK_GRADE_SCALE_MASTER = $_GET['id'];
	}
	
	$i = 0;
	foreach($_POST['COUNT'] as $COUNT){
		$GRADE_SCALE_DETAIL = array();
		$GRADE_SCALE_DETAIL['MIN_PERCENTAGE'] 	= $_POST['MIN_PERCENTAGE'][$i];
		$GRADE_SCALE_DETAIL['MAX_PERCENTAGE'] 	= $_POST['MAX_PERCENTAGE'][$i];
		$GRADE_SCALE_DETAIL['PK_GRADE'] 		= $_POST['PK_GRADE'][$i];
		
		if($_POST['PK_GRADE_SCALE_DETAIL'][$i] == ''){
			$GRADE_SCALE_DETAIL['PK_GRADE_SCALE_MASTER']  	= $PK_GRADE_SCALE_MASTER;
			$GRADE_SCALE_DETAIL['PK_ACCOUNT']  				= $_SESSION['PK_ACCOUNT'];
			$GRADE_SCALE_DETAIL['CREATED_BY']  				= $_SESSION['PK_USER'];
			$GRADE_SCALE_DETAIL['CREATED_ON']  				= date("Y-m-d H:i");
			db_perform('S_GRADE_SCALE_DETAIL', $GRADE_SCALE_DETAIL, 'insert');
			$PK_GRADE_SCALE_DETAIL = $db->insert_ID;
			
			$PK_GRADE_SCALE_DETAIL_ARR[] = $PK_GRADE_SCALE_DETAIL;
			
		} else {
			$PK_GRADE_SCALE_DETAIL = $_POST['PK_GRADE_SCALE_DETAIL'][$i];
			$GRADE_SCALE_DETAIL['EDITED_BY'] = $_SESSION['PK_USER'];
			$GRADE_SCALE_DETAIL['EDITED_ON'] = date("Y-m-d H:i");
			db_perform('S_GRADE_SCALE_DETAIL', $GRADE_SCALE_DETAIL, 'update'," PK_GRADE_SCALE_DETAIL = '$PK_GRADE_SCALE_DETAIL' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_GRADE_SCALE_MASTER = '$PK_GRADE_SCALE_MASTER' ");
			
			$PK_GRADE_SCALE_DETAIL_ARR[] = $PK_GRADE_SCALE_DETAIL;
		}
		
		$i++;
	}

	$cond = '';
	if(!empty($PK_GRADE_SCALE_DETAIL_ARR))
		$cond = " AND PK_GRADE_SCALE_DETAIL NOT IN (".implode(",",$PK_GRADE_SCALE_DETAIL_ARR).")";
	$db->Execute("DELETE from S_GRADE_SCALE_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_GRADE_SCALE_MASTER = '$PK_GRADE_SCALE_MASTER' $cond");

	header("location:manage_grade_scale");
}
if($_GET['id'] == ''){
	$GRADE_SCALE = '';
	$ACTIVE	 	 = '';	
} else {
	$res = $db->Execute("SELECT * FROM S_GRADE_SCALE_MASTER WHERE PK_GRADE_SCALE_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_grade_scale");
		exit;
	}
	$GRADE_SCALE = $res->fields['GRADE_SCALE'];
	$ACTIVE  	 = $res->fields['ACTIVE'];
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
	<title><?=GRADE_SCALE_PAGE_TITLE?> | <?=$title?></title>
	<style>
		.no-records-found{display:none;}
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
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=GRADE_SCALE_PAGE_TITLE?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
									<div class="row">
                                        <div class="col-md-3">
											<div class="form-group m-b-40">
												<input type="text" class="form-control" placeholder="" name="GRADE_SCALE" id="GRADE_SCALE<?=$count?>" value="<?=$GRADE_SCALE?>" />
												<span class="bar"></span> 
												<label for="GRADE_SCALE"><?=GRADE_SCALE?></label>
											</div>
										</div>
										
										<div class="col-md-9">
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
									
									<table data-toggle="table" data-mobile-responsive="true" class="table-striped" id="grade_table" >
										<thead>
											<tr>
												<th ><?=MIN_PERCENTAGE?></th>
												<th ><?=MAX_PERCENTAGE?></th>
												<th ><?=GRADE_CALC?></th>
												<th ><?=DELETE?></th>
											</tr>
										</thead>
										<tbody>
											<? $count = 1; 
											$result1 = $db->Execute("SELECT PK_GRADE_SCALE_DETAIL FROM S_GRADE_SCALE_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_GRADE_SCALE_MASTER = '$_GET[id]' ORDER BY MAX_PERCENTAGE DESC ");
											$reccnt = $result1->RecordCount();
											while (!$result1->EOF) {
												$_REQUEST['PK_GRADE_SCALE_DETAIL'] 	= $result1->fields['PK_GRADE_SCALE_DETAIL'];
												$_REQUEST['count']  				= $count;
												
												include('ajax_grade_scale.php');
												
												$count++;	
												$result1->MoveNext();
											} ?>
										</tbody>
									</table>
									<br />
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="button" class="btn btn-primary" onClick="add_fields()" /><?=ADD ?></button>
												
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_grade_scale'" ><?=CANCEL?></button>
												
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
   
   <div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="exampleModalLabel1"><?=DELETE_CONFIRMATION?></h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">
					<div class="form-group" id="delete_message" ></div>
					<input type="hidden" id="DELETE_ID" value="0" />
					<input type="hidden" id="DELETE_TYPE" value="0" />
				</div>
				<div class="modal-footer">
					<button type="button" onclick="conf_delete(1)" class="btn waves-effect waves-light btn-info"><?=YES?></button>
					<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete(0)" ><?=NO?></button>
				</div>
			</div>
		</div>
	</div>
	
	<? require_once("js.php"); ?>
	
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');
		
		var cunt = '<?=$count?>';
		function add_fields(){
			jQuery(document).ready(function($) {
				var data = 'count='+cunt+'&ACTION=<?=$_GET['act']?>';
				var value = $.ajax({
					url: "ajax_grade_scale",	
					type: "POST",
					data: data,		
					async: false,
					cache :false,
					success: function (data) {
						$('#grade_table tbody').append(data);
						cunt++;
						
					}		
				}).responseText;
			});
		}
		
		function delete_row(id,type){
			jQuery(document).ready(function($) {
				if(type == 'grade')
					document.getElementById('delete_message').innerHTML = '<?=DELETE_MESSAGE_GENERAL?>?';
		
				$("#deleteModal").modal()
				$("#DELETE_ID").val(id)
				$("#DELETE_TYPE").val(type)
			});
		}
		function conf_delete(val,id){
			jQuery(document).ready(function($) {
				if(val == 1) {
					if($("#DELETE_TYPE").val() == 'grade'){
						var iid = $("#DELETE_ID").val()
						$("#table_"+iid).remove()
					}
				}
				$("#deleteModal").modal("hide");
			});
		}
	</script>
	<link href="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.css" rel="stylesheet" type="text/css" />
	<script src="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.js"></script>
</body>

</html>