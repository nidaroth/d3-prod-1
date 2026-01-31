<? require_once("../global/config.php"); 
require_once("../global/image_fun.php");
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;

	$EMPLOYEE_MASTER['FIRST_NAME']  = $_POST['FIRST_NAME'];
	$EMPLOYEE_MASTER['LAST_NAME']  	= $_POST['LAST_NAME'];
	$USER['PK_ROLES']  				= 1;
	$USER['PK_LANGUAGE']  			= 1;
	
	if($_GET['id'] == ''){
		
		$EMPLOYEE_MASTER['PK_ACCOUNT']  = 1;
		$EMPLOYEE_MASTER['CREATED_BY']  = $_SESSION['ADMIN_PK_USER'];
		$EMPLOYEE_MASTER['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('S_EMPLOYEE_MASTER', $EMPLOYEE_MASTER, 'insert');
		$PK_EMPLOYEE_MASTER = $db->insert_ID();
		
		do {
			$USER_API_KEY = generateRandomString(60);
			$res_key = $db->Execute("SELECT PK_USER FROM Z_USER where USER_API_KEY = '$USER_API_KEY'");
		} while ($res_key->RecordCount() > 0);

		$salt = substr(strtr(base64_encode(openssl_random_pseudo_bytes(22)),'+','.'),0,22);
		$hash = crypt($_POST['PASSWORD'], '$2y$12$' . $salt);
		$USER['PASSWORD']  	 			= $hash;
		$USER['PASSWORD_CHANGED_ON']  	= date("Y-m-d"); //Ticket # 873
		$USER['ID']  	 	 			= $PK_EMPLOYEE_MASTER;
		$USER['USER_API_KEY']  			= $USER_API_KEY;
		$USER['USER_ID']  				= $_POST['USER_ID'];
		$USER['PK_USER_TYPE']  			= 1;
		$USER['PK_ACCOUNT']  			= 1;
		$USER['CREATED_BY']  			= $_SESSION['ADMIN_PK_USER'];
		$USER['CREATED_ON']  			= date("Y-m-d H:i");
		db_perform('Z_USER', $USER, 'insert');
		$PK_USER = $db->insert_ID();

	} else {
		$res = $db->Execute("SELECT S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER  FROM S_EMPLOYEE_MASTER , Z_USER WHERE Z_USER.PK_USER = '$_GET[id]' AND Z_USER.ID = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER "); 
		$PK_EMPLOYEE_MASTER = $res->fields['PK_EMPLOYEE_MASTER'];
		
		$EMPLOYEE_MASTER['ACTIVE']  	= $_POST['ACTIVE'];
		$EMPLOYEE_MASTER['EDITED_BY']   = $_SESSION['ADMIN_PK_USER'];
		$EMPLOYEE_MASTER['EDITED_ON']   = date("Y-m-d H:i");
		db_perform('S_EMPLOYEE_MASTER', $EMPLOYEE_MASTER, 'update'," PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' ");

		$USER['ACTIVE']    = $_POST['ACTIVE'];
		$USER['EDITED_BY'] = $_SESSION['ADMIN_PK_USER'];
		$USER['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('Z_USER', $USER, 'update'," PK_USER = '$_GET[id]' AND PK_USER_TYPE = 1");
	}
//echo "<pre>";print_r($CAMPUS);exit;	
	header("location:manage_admin_users");
}

if($_GET['id'] == ''){
	$PK_ROLES	= '';
	$FIRST_NAME = '';
	$LAST_NAME 	= '';
	$EMAIL	 	= '';
	$CELL_PHONE	= '';
	$USER_ID	= '';
} else {
	$res = $db->Execute("SELECT FIRST_NAME,LAST_NAME,EMAIL,PK_ROLES,S_EMPLOYEE_MASTER.ACTIVE,USER_ID,PK_LANGUAGE  FROM S_EMPLOYEE_MASTER , Z_USER WHERE Z_USER.PK_USER = '$_GET[id]' AND Z_USER.ID = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER "); 

	if($res->RecordCount() == 0){
		header("location:manage_admin_users");
		exit;
	}
	
	$PK_ROLES 		= $res->fields['PK_ROLES'];
	$USER_ID 		= $res->fields['USER_ID'];
	$FIRST_NAME 	= $res->fields['FIRST_NAME'];
	$LAST_NAME 		= $res->fields['LAST_NAME'];
	$EMAIL  		= $res->fields['EMAIL'];
	$CELL_PHONE 	= $res->fields['CELL_PHONE'];
	$PK_LANGUAGE 	= $res->fields['PK_LANGUAGE'];
	$ACTIVE			= $res->fields['ACTIVE'];
	//echo $PRIMARY_CAMPUS;exit;
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
	<title>Admin User | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">Admin User</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" >
								<div class="p-20">
									<div class="d-flex">
										<div class="col-12 col-sm-3 form-group">
											<input id="FIRST_NAME" name="FIRST_NAME" type="text" class="form-control required" value="<?=$FIRST_NAME?>">
											<span class="bar"></span> 
											<label for="FIRST_NAME">First Name</label>
										</div>
										<div class="col-12 col-sm-3 form-group">
											<input id="LAST_NAME" name="LAST_NAME" type="text" class="form-control" value="<?=$LAST_NAME?>">
											<span class="bar"></span> 
											<label for="LAST_NAME">Last Name</label>
										</div>
									</div>
									
									<? if($_GET['id'] == ''){ ?>
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<input id="USER_ID" name="USER_ID" type="text" class="form-control required" value="<?=$USER_ID?>" onBlur="duplicate_check()" >
											<span class="bar"></span> 
											<label for="USER_ID">User ID</label>
											<div id="already_exit" style="display:none;color:#ff0000;" >User ID already exists. Try with another.</div>
										</div>											
									</div>
									
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<input id="PASSWORD" name="PASSWORD" type="PASSWORD" class="form-control required" value="">
											<span class="bar"></span> 
											<label for="EMAIL">Password</label>
										</div>											
									</div>
									<? } else { ?>
										<div class="d-flex">
											<div class="col-12 col-sm-6 form-group focused">
												<label for="" class="position-relative1">User ID</label>
												<div class="form-control hover-not-allowed"><?=$USER_ID?></div>
												<span class="bar"></span> 
											</div>
										</div>
									
										<div class="row">
											<div class="col-md-6">
												<div class="row form-group">
													<div class="custom-control col-md-4">Active</div>
													<div class="custom-control custom-radio col-md-2">
														<input type="radio" id="customRadio11" name="ACTIVE" value="1" <? if($ACTIVE == 1) echo "checked"; ?> class="custom-control-input">
														<label class="custom-control-label" for="customRadio11">Yes</label>
													</div>
													<div class="custom-control custom-radio col-md-2">
														<input type="radio" id="customRadio22" name="ACTIVE" value="0" <? if($ACTIVE == 0) echo "checked"; ?>  class="custom-control-input">
														<label class="custom-control-label" for="customRadio22">No</label>
													</div>
												</div>
											</div>
										</div>
									<? } ?>
									
									<div class="row">
										<div class="col-3 col-sm-3">
										</div>
										
										<div class="col-9 col-sm-9">
											<button type="submit" class="btn waves-effect waves-light btn-info">Save</button>
											
											<button type="button" onclick="window.location.href='manage_admin_users'"  class="btn waves-effect waves-light btn-dark">Cancel</button>
											
										</div>
									</div>
								</div>
							</form>
                        </div>
					</div>
				</div>
				
            </div>
        </div>
        <? require_once("footer.php"); ?>
		
		<div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1">Delete Confirmation</h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group" id="delete_message" ></div>
						<input type="hidden" id="DELETE_ID" value="0" />
						<input type="hidden" id="DELETE_TYPE" value="0" />
					</div>
					<div class="modal-footer">
						<button type="button" onclick="conf_delete(1)" class="btn waves-effect waves-light btn-info">Yes</button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete(0)" >No</button>
					</div>
				</div>
			</div>
		</div>
    </div>
   
	<? require_once("js.php"); ?>

	<!-- <script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script> -->
	<script src="../backend_assets/dist/jquery-validation/dist/jquery.validate.min.js"></script>
<script src="../backend_assets/dist/jquery-validation/dist/additional-methods.min.js"></script>

<script type="text/javascript">
$(function () {
  $.validator.addMethod("strong_password", function (value, element) {
    let password = value;
    if (!(/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[@#$%&])(.{8,20}$)/.test(password))) {
        return false;
    }
    return true;
}, function (value, element) {
    let password = $(element).val();
    if (!(/^(.{8,20}$)/.test(password))) {
        return 'Password must be between 8 and 20 characters long.';
    }
    else if (!(/^(?=.*[A-Z])/.test(password))) {
        return 'Password must contain atleast one uppercase.';
    }
    else if (!(/^(?=.*[a-z])/.test(password))) {
        return 'Password must contain atleast one lowercase.';
    }
    else if (!(/^(?=.*[0-9])/.test(password))) {
        return 'Password must contain atleast one digit.';
    }
    else if (!(/^(?=.*[@#$%&])/.test(password))) {
        return "Password must contain special characters from @#$%&.";
    }
    return false;
});
  $.validator.setDefaults({
    submitHandler: function () {
      document.form1.submit();
    }
  });
  $('#form1').validate({
    rules: {
      PASSWORD: {
        required: true,
        minlength: 8,
        strong_password:true
      }	
    },
    messages: {
      PASSWORD: {
        required: "Please enter new password",
        minlength: "Your password must be at least 8 characters long"

      }	
    },
    errorElement: 'span',
    errorPlacement: function (error, element) {
      error.addClass('invalid-feedback');
      var name = $(element).attr("name");
      //error.appendTo($("#" + name + "_validate"));
	  element.closest('.form-group').append(error);
   
    },
    highlight: function (element, errorClass, validClass) {
      $(element).addClass('is-invalid');
    },
    unhighlight: function (element, errorClass, validClass) {
      $(element).removeClass('is-invalid');
    }
  });
});
</script>	
	<script type="text/javascript">
		//var form1 = new Validation('form1');
		
		function duplicate_check(){
			jQuery(document).ready(function($) {
				if (document.form1.USER_ID.value  != ""){
					var USER_ID = document.form1.USER_ID.value;
					var data="USER_ID="+USER_ID+'&type=USER_ID';
					$.ajax({
						type: "POST",
						url:"../check_duplicate",
						data:data,
						success: function(result1){ 
							if(result1==1){
								document.getElementById('already_exit').style.display="block";
								document.getElementById('USER_ID').value="";
								return false;
							}else{
								document.getElementById('already_exit').style.display="none";
							}
						}
					});
				}
			});	
		}
		function delete_row(id,type){
			jQuery(document).ready(function($) {
				if(type == 'logo')
					document.getElementById('delete_message').innerHTML = 'Are you sure you want to Delete this Logo?';
					
				$("#deleteModal").modal()
				$("#DELETE_ID").val(id)
				$("#DELETE_TYPE").val(type)
			});
		}
		function conf_delete(val,id){
			jQuery(document).ready(function($) {
				if(val == 1) {
					if($("#DELETE_TYPE").val() == 'logo')
						window.location.href = 'campus?act=logo&id=<?=$_GET['id']?>';
				} else
					$("#deleteModal").modal("hide");
			});
		}
	</script>

</body>

</html>
