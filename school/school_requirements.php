<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/school_requirements.php");
require_once("check_access.php");

if(check_access('SETUP_SCHOOL') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$query_1 = "select S_STUDENT_MASTER.PK_STUDENT_MASTER,PK_STUDENT_ENROLLMENT 
	FROM 
	S_STUDENT_MASTER, S_STUDENT_ACADEMICS, S_STUDENT_ENROLLMENT, M_STUDENT_STATUS  
	WHERE 
	S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
	S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
	M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS  AND ARCHIVED = 0 AND PK_STUDENT_STATUS_MASTER != 12 ";
	$i = 0;
	foreach($_POST['COUNT'] as $COUNT){
		$SCHOOL_REQUIREMENT = array();
		$SCHOOL_REQUIREMENT['REQUIREMENT'] 				= $_POST['REQUIREMENT'][$i];
		$SCHOOL_REQUIREMENT['ACTIVE'] 					= $_POST['ACTIVE_'.$COUNT];
		$SCHOOL_REQUIREMENT['MANDATORY'] 				= $_POST['MANDATORY_'.$COUNT];
		$SCHOOL_REQUIREMENT['PK_REQUIREMENT_CATEGORY'] 	= $_POST['PK_REQUIREMENT_CATEGORY'][$i];
		
		if($_POST['PK_SCHOOL_REQUIREMENT'][$i] == ''){
			$SCHOOL_REQUIREMENT['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
			$SCHOOL_REQUIREMENT['CREATED_BY']  = $_SESSION['PK_USER'];
			$SCHOOL_REQUIREMENT['CREATED_ON']  = date("Y-m-d H:i");
			db_perform('S_SCHOOL_REQUIREMENT', $SCHOOL_REQUIREMENT, 'insert');
			$PK_SCHOOL_REQUIREMENT = $db->insert_ID();
			
			$PK_SCHOOL_REQUIREMENT_ARR[] = $PK_SCHOOL_REQUIREMENT;
		
			if($SCHOOL_REQUIREMENT['PK_REQUIREMENT_CATEGORY'] == 1)
				$query = $query_1." AND ADMISSIONS = 1 GROUP BY PK_STUDENT_ENROLLMENT ";
			else if($SCHOOL_REQUIREMENT['PK_REQUIREMENT_CATEGORY'] == 2)
				$query = $query_1." AND COMPLETED = 0 GROUP BY PK_STUDENT_ENROLLMENT ";
		
			$res_stu = $db->Execute($query);
			while (!$res_stu->EOF) {
				$STUDENT_REQUIREMENT = array();
				$STUDENT_REQUIREMENT['PK_STUDENT_MASTER'] 		= $res_stu->fields['PK_STUDENT_MASTER'];
				$STUDENT_REQUIREMENT['PK_STUDENT_ENROLLMENT'] 	= $res_stu->fields['PK_STUDENT_ENROLLMENT'];;
				$STUDENT_REQUIREMENT['TYPE'] 				  	= 1;
				$STUDENT_REQUIREMENT['ID'] 				  		= $PK_SCHOOL_REQUIREMENT;
				$STUDENT_REQUIREMENT['REQUIREMENT'] 			= $SCHOOL_REQUIREMENT['REQUIREMENT'];
				$STUDENT_REQUIREMENT['MANDATORY'] 				= $SCHOOL_REQUIREMENT['MANDATORY'];
				$STUDENT_REQUIREMENT['PK_ACCOUNT']  			= $_SESSION['PK_ACCOUNT'];
				$STUDENT_REQUIREMENT['CREATED_BY']  			= $_SESSION['PK_USER'];
				$STUDENT_REQUIREMENT['CREATED_ON']  			= date("Y-m-d H:i");
				db_perform('S_STUDENT_REQUIREMENT', $STUDENT_REQUIREMENT, 'insert');

				$res_stu->MoveNext();
			}
			
		} else {
			$PK_SCHOOL_REQUIREMENT = $_POST['PK_SCHOOL_REQUIREMENT'][$i];
			$SCHOOL_REQUIREMENT['EDITED_BY'] = $_SESSION['PK_USER'];
			$SCHOOL_REQUIREMENT['EDITED_ON'] = date("Y-m-d H:i");
			db_perform('S_SCHOOL_REQUIREMENT', $SCHOOL_REQUIREMENT, 'update'," PK_SCHOOL_REQUIREMENT = '$PK_SCHOOL_REQUIREMENT' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
			
			$PK_SCHOOL_REQUIREMENT_ARR[] = $PK_SCHOOL_REQUIREMENT;
		
			if($SCHOOL_REQUIREMENT['PK_REQUIREMENT_CATEGORY'] == 1)
				$query = $query_1." AND ADMISSIONS = 1 GROUP BY PK_STUDENT_ENROLLMENT ";
			else if($SCHOOL_REQUIREMENT['PK_REQUIREMENT_CATEGORY'] == 2)
				$query = $query_1." AND COMPLETED = 0 GROUP BY PK_STUDENT_ENROLLMENT ";

			$res_stu = $db->Execute($query);
			while (!$res_stu->EOF) {
				$PK_STUDENT_ENROLLMENT 	= $res_stu->fields['PK_STUDENT_ENROLLMENT'];
				$STUDENT_REQUIREMENT 	= array();
				$STUDENT_REQUIREMENT['PK_REQUIREMENT_CATEGORY'] = $SCHOOL_REQUIREMENT['PK_REQUIREMENT_CATEGORY'];
				$STUDENT_REQUIREMENT['REQUIREMENT'] 			= $SCHOOL_REQUIREMENT['REQUIREMENT'];
				$STUDENT_REQUIREMENT['MANDATORY'] 				= $SCHOOL_REQUIREMENT['MANDATORY'];
				db_perform('S_STUDENT_REQUIREMENT', $STUDENT_REQUIREMENT, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ID = '$PK_SCHOOL_REQUIREMENT' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND TYPE = 1 ");
							
				$res_stu->MoveNext();
			}
				
		}
		
		$i++;
	}

	$cond = '';
	if(!empty($PK_SCHOOL_REQUIREMENT_ARR))
		$cond = " AND PK_SCHOOL_REQUIREMENT NOT IN (".implode(",",$PK_SCHOOL_REQUIREMENT_ARR).")";
		
	$res = $db->Execute("SELECT PK_REQUIREMENT_CATEGORY, PK_SCHOOL_REQUIREMENT from S_SCHOOL_REQUIREMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond");
	while (!$res->EOF) {
		$PK_SCHOOL_REQUIREMENT = $res->fields['PK_SCHOOL_REQUIREMENT'];
		
		if($res->fields['PK_REQUIREMENT_CATEGORY'] == 1)
			$query = $query_1." AND ADMISSIONS = 1 GROUP BY PK_STUDENT_ENROLLMENT ";
		else if($res->fields['PK_REQUIREMENT_CATEGORY'] == 2)
			$query = $query_1." AND COMPLETED = 0 GROUP BY PK_STUDENT_ENROLLMENT ";
	
		$res_stu = $db->Execute($query);
		while (!$res_stu->EOF) {
			$PK_STUDENT_ENROLLMENT = $res_stu->fields['PK_STUDENT_ENROLLMENT'];
			$db->Execute("DELETE from S_STUDENT_REQUIREMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ID = '$PK_SCHOOL_REQUIREMENT' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND TYPE = 1 AND COMPLETED = 0");
			
			$res_stu->MoveNext();
		}
		
		$res->MoveNext();
	}
	
	$db->Execute("DELETE from S_SCHOOL_REQUIREMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond");
	
	header("location:school_requirements");
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
	<title><?=SCHOOL_REQUIREMENTS_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"> <?=SCHOOL_REQUIREMENTS_PAGE_TITLE?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
									<div class="row">
										<div class="col-md-6">
											<b><?=REQUIREMENT?></b>
										</div> 
										<div class="col-md-2">
											<b><?=CATEGORY?></b>
										</div> 
										<div class="col-md-1">
											<b><?=MANDATORY?></b>
										</div> 
										<div class="col-md-1">
											<b><?=ACTIVE?></b>
										</div> 
										<div class="col-md-1">
											<b><?=DELETE?></b>
										</div> 
									</div> 
								
									<div id="form_div" >
										<? $fetch_fields_count = 1; 
										$result1 = $db->Execute("SELECT * FROM S_SCHOOL_REQUIREMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY ACTIVE DESC, REQUIREMENT ASC ");
										$reccnt = $result1->RecordCount();
										while (!$result1->EOF) {
											$_REQUEST['PK_SCHOOL_REQUIREMENT'] 	= $result1->fields['PK_SCHOOL_REQUIREMENT'];
											$_REQUEST['count']  				= $fetch_fields_count;
											
											include('ajax_school_requirements.php');
											
											$fetch_fields_count++;	
											$result1->MoveNext();
										} ?>
										
									</div>
									
									<div class="row">
										<div class="col-md-8">&nbsp;</div>
										<div class="col-md-2">
											<button type="button" class="btn btn-primary" onClick="add_fields()" /><?=ADD ?></button>
										</div>
									</div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='setup'" ><?=CANCEL?></button>
												
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
		function add_fields(){
			jQuery(document).ready(function($) {
				var data = 'count='+cunt+'&ACTION=<?=$_GET['act']?>';
				var value = $.ajax({
					url: "ajax_school_requirements",	
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
	</script>

</body>

</html>