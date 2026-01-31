<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/user_defined_fields.php");
require_once("check_access.php");

if(check_access('SETUP_SCHOOL') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$USER_DD['NAME'] 			= $_POST['NAME'];
	$USER_DD['PK_DATA_TYPES'] 	= $_POST['PK_DATA_TYPES'];
	
	if($_GET['id'] == ''){
		$USER_DD['CREATED_BY']  = $_SESSION['PK_USER'];
		$USER_DD['CREATED_ON']  = date("Y-m-d H:i:s");
		$USER_DD['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		db_perform('S_USER_DEFINED_FIELDS', db_prepare_input($USER_DD), 'insert');
		$PK_USER_DEFINED_FIELDS = $db->insert_ID();
	} else {
		$USER_DD['ACTIVE'] 	   = $_POST['ACTIVE'];
		$USER_DD['EDITED_BY']  = $_SESSION['PK_USER'];
		$USER_DD['EDITED_ON']  = date("Y-m-d H:i:s");
		db_perform('S_USER_DEFINED_FIELDS', db_prepare_input($USER_DD), 'update' , "PK_USER_DEFINED_FIELDS  = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$PK_USER_DEFINED_FIELDS = $_GET['id'];
	}
	
	$OPTION_NAMES = $_POST['OPTION_NAME'];
	if(!empty($OPTION_NAMES)){
		$i=0;
		foreach($OPTION_NAMES as $OPTION_NAME){
			if(!empty($OPTION_NAME)){
				$count  						= $_POST['COUNT'][$i];
				$PK_USER_DEFINED_FIELDS_DETAIL  = $_POST['PK_USER_DEFINED_FIELDS_DETAIL'][$i];
				$result = $db->Execute("select * from S_USER_DEFINED_FIELDS_DETAIL WHERE  PK_USER_DEFINED_FIELDS_DETAIL  = '$PK_USER_DEFINED_FIELDS_DETAIL'");
				$rowcnt = $result->RecordCount();
			
				$USER_DD_DETAIL = array();
				$USER_DD_DETAIL['PK_USER_DEFINED_FIELDS'] 	= $PK_USER_DEFINED_FIELDS;
				$USER_DD_DETAIL['OPTION_NAME'] 		 		= db_prepare_input($OPTION_NAME);
				$USER_DD_DETAIL['DISPLAY_ORDER'] 	 		= $i+1;
							
				if($rowcnt == 0){
					$USER_DD_DETAIL['CREATED_BY']  	= $_SESSION['PK_USER'];
					$USER_DD_DETAIL['CREATED_ON']  	= date("Y-m-d H:i:s");
					$USER_DD_DETAIL['PK_ACCOUNT']  	= $_SESSION['PK_ACCOUNT'];
					$USER_DD_DETAIL['ACTIVE'] 		= '1';
					db_perform('S_USER_DEFINED_FIELDS_DETAIL', $USER_DD_DETAIL, 'insert');
					$PK_USER_DEFINED_FIELDS_DETAIL_IDS[] = $db->insert_ID();					
				}else{
					if($_POST['ACTIVE_'.$count] == ''){
						$USER_DD_DETAIL['ACTIVE'] 	= 0;
					}else{
						$USER_DD_DETAIL['ACTIVE'] 	= 1;
					}
					$USER_DD_DETAIL['EDITED_BY']  = $_SESSION['PK_USER'];
					$USER_DD_DETAIL['EDITED_ON']  = date("Y-m-d H:i:s");
					db_perform('S_USER_DEFINED_FIELDS_DETAIL',$USER_DD_DETAIL, 'update' ," PK_USER_DEFINED_FIELDS_DETAIL  = '$PK_USER_DEFINED_FIELDS_DETAIL'");
					$PK_USER_DEFINED_FIELDS_DETAIL_IDS[] = $PK_USER_DEFINED_FIELDS_DETAIL;
				}
			}
			$i++;
		}
	}
	$cond = '';
	if(!empty($PK_USER_DEFINED_FIELDS_DETAIL_IDS))
		$cond = " AND PK_USER_DEFINED_FIELDS_DETAIL NOT IN (".implode(",",$PK_USER_DEFINED_FIELDS_DETAIL_IDS).")";
	$db->Execute("DELETE from S_USER_DEFINED_FIELDS_DETAIL WHERE PK_USER_DEFINED_FIELDS ='$_GET[id]' $cond");
	
	header("location:manage_user_defined_fields");
}
if($_GET['id'] == ''){
	$NAME			= '';
	$PK_DATA_TYPES 	= '';
} else {
	$res = $db->Execute("select * from S_USER_DEFINED_FIELDS WHERE PK_USER_DEFINED_FIELDS ='$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	if($res->RecordCount() == 0){
		header("location:manage_user_defined_fields");
	}
	$NAME 	 		 = $res->fields['NAME'];
	$PK_DATA_TYPES	 = $res->fields['PK_DATA_TYPES'];
	$ACTIVE1 		 = $res->fields['ACTIVE'];
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
	<title><?=USER_DEFINED_FIELDS_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=USER_DEFINED_FIELDS_PAGE_TITLE?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control required-entry" id="NAME" name="NAME" value="<?=$NAME?>" >
												<span class="bar"></span>
												<label for="NAME"><?=NAME?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<select id="PK_DATA_TYPES" name="PK_DATA_TYPES" class="form-control required-entry" >
													<option selected></option>
													<? $res_type = $db->Execute("select PK_DATA_TYPES, DATA_TYPES from M_DATA_TYPES WHERE ACTIVE = '1' AND PK_DATA_TYPES IN (2,3) ORDER BY DATA_TYPES ASC ");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_DATA_TYPES'] ?>" <? if($res_type->fields['PK_DATA_TYPES'] == $PK_DATA_TYPES) echo "selected"; ?> ><?=$res_type->fields['DATA_TYPES']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="PK_DATA_TYPES"><?=DATA_TYPE?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
										<div class="col-md-6">
											<b><?=OPTION?></b>
										</div> 
										<div class="col-md-1">
											<b><?=ACTIVE?></b>
										</div> 
										<div class="col-md-1">
											<b><?=ACTION?></b>
										</div> 
									</div> 
								
									<div  id="form_div" >
										<? $fetch_fields_count = 1; 
										$result1 = $db->Execute("select PK_USER_DEFINED_FIELDS_DETAIL from S_USER_DEFINED_FIELDS_DETAIL WHERE PK_USER_DEFINED_FIELDS = '$_GET[id]' ORDER BY DISPLAY_ORDER ASC ");
										$reccnt = $result1->RecordCount();
										while (!$result1->EOF) {
											$_REQUEST['PK_USER_DEFINED_FIELDS_DETAIL'] 	= $result1->fields['PK_USER_DEFINED_FIELDS_DETAIL'];
											$_REQUEST['ACTION'] 						= $_GET['act'];
											$_REQUEST['count']  						= $fetch_fields_count;
											
											include('fetch_user_defined_fields.php');
											
											$fetch_fields_count++;	
											$result1->MoveNext();
										} ?>
										
									</div>
									<div class="row">
										<div class="col-lg-6">&nbsp;</div>
										<div class="col-lg-2">
											<button type="button" class="btn btn-primary" onClick="fetch_fields()" style="float:right;"  /><?=ADD_OPTION?></button>
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
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_user_defined_fields'" ><?=CANCEL?></button>
												
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
		
		var cunt = '<?=$fetch_fields_count?>';
		function fetch_fields(){
			jQuery(document).ready(function($) {
				var data = 'count='+cunt+'&ACTION=<?=$_GET['act']?>';
				var value = $.ajax({
					url: "fetch_user_defined_fields",	
					type: "POST",
					data: data,		
					async: false,
					cache :false,
					success: function (data) {
						$("#form_div").append(data);
						cunt++;
					}		
				}).responseText;
			});
		}
		
		function delete_row(id,type){
			jQuery(document).ready(function($) {
				if(type == 'detail')
					document.getElementById('delete_message').innerHTML = '<?=DELETE_MESSAGE_GENERAL?>?';
		
				$("#deleteModal").modal()
				$("#DELETE_ID").val(id)
				$("#DELETE_TYPE").val(type)
			});
		}
		function conf_delete(val,id){
			jQuery(document).ready(function($) {
				if(val == 1) {
					if($("#DELETE_TYPE").val() == 'detail'){
						var iid = $("#DELETE_ID").val()
						$("#table_"+iid).remove()
					}
				}
				$("#deleteModal").modal("hide");
			});
		}
		
		<? if($_GET['id'] == '') { ?>
			jQuery(document).ready(function($) {
				fetch_fields();
			});
		<? } ?>
	</script>
</body>

</html>