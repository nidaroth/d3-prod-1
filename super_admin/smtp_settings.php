<? require_once("../global/config.php");
require_once('../global/phpmailer/class.phpmailer.php');

if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}

$msg = '';	
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$res = $db->Execute("select * from Z_EMAIL_ACCOUNT WHERE PK_ACCOUNT = 1");
	
	$EMAIL_ACCOUNT = $_POST;
	$EMAIL_ACCOUNT['PASSWORD'] = my_encrypt('',$EMAIL_ACCOUNT['PASSWORD']);
	if($res->RecordCount() == 0){
		$EMAIL_ACCOUNT['CREATED_BY'] = $_SESSION['PK_USER_MASTER'];
		$EMAIL_ACCOUNT['CREATED_ON'] = date("Y-m-d H:i:s");
		db_perform('Z_EMAIL_ACCOUNT', $EMAIL_ACCOUNT, 'insert');
		$PK_EMAIL_ACCOUNT = $db->insert_ID();
	} else {
		$EMAIL_ACCOUNT['EDITED_BY'] = $_SESSION['PK_USER_MASTER'];
		$EMAIL_ACCOUNT['EDITED_ON'] = date("Y-m-d H:i:s");
		db_perform('Z_EMAIL_ACCOUNT', $EMAIL_ACCOUNT, 'update'," PK_ACCOUNT = 1 ");
		$PK_EMAIL_ACCOUNT = $_GET['id'];
	}
	
	$EMAIL = 'balaji@codingdesk.in';

	$res_broad = $db->Execute("SELECT * FROM Z_EMAIL_ACCOUNT WHERE PK_ACCOUNT = 1");
	$mail      = new PHPMailer();

	$mail->IsSMTP(); // telling the class to use SMTP
	$mail->SMTPDebug  = 1;                     // enables SMTP debug information (for testing)
											   // 1 = errors and messages
											   // 2 = messages only
	$mail->SMTPAuth   = true;                  // enable SMTP authentication
	$mail->SMTPSecure = $res_broad->fields['ENCRYPTION_TYPE'];       // sets the prefix to the servier
	$mail->Host       = $res_broad->fields['HOST'];      // sets GMAIL as the SMTP server
	$mail->Port       = $res_broad->fields['PORT'];                   // set the SMTP port for the GMAIL server
	$mail->Username   = $res_broad->fields['USER_NAME'];    // GMAIL username
	$mail->Password   = my_decrypt('',$res_broad->fields['PASSWORD']);             // GMAIL password

	$mail->SetFrom($res_broad->fields['USER_NAME'], '');

	$mail->Subject = 'Mail From DSIS';
	$mail->MsgHTML('This is a test mail from SPMS');
	$mail->AddAddress($EMAIL,'');

	if(!$mail->Send()) {
		$msg = 'Something is Wrong. We are not able to send the Mail. Please check the Configuration Again';
	} else {
		$msg = 'SMTP Configured Successfully!!!';
	}
}
$res = $db->Execute("select * from Z_EMAIL_ACCOUNT WHERE PK_ACCOUNT = 1 ");
$HOST 		= $res->fields['HOST'];
$PORT 		= $res->fields['PORT'];
$USER_NAME 	= $res->fields['USER_NAME'];
$PASSWORD	= $res->fields['PASSWORD'];
$ENCRYPTION_TYPE	= $res->fields['ENCRYPTION_TYPE'];
$PASSWORD = my_decrypt('',$PASSWORD);
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
	<title>SMTP Settings | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">SMTP Settings</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" >
								<div class="p-20">
									<? if($msg != ''){ ?>
										<div class="form-group">
											<label for="input-text" class="col-sm-2 control-label"></label>
											<div class="col-sm-10" style="color:red">
												<?=$msg?>
											</div>
										</div>
									<? } ?>
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<input type="text" class="form-control required-entry" id="HOST" name="HOST" value="<?=$HOST?>"  placeholder="smtp.gmail.com"  >
											<span class="bar"></span> 
											<label for="HOST">Host</label>
										</div>
									</div>
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<input type="text" class="required-entry form-control" id="PORT" name="PORT" value="<?=$PORT?>" placeholder="465"  >
											<span class="bar"></span> 
											<label for="PORT">Port</label>
										</div>
									</div>
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group focused" >
											<select id="ENCRYPTION_TYPE" name="ENCRYPTION_TYPE" class="form-control">
												<option value="tls" <? if($ENCRYPTION_TYPE == 'tls') echo "selected"; ?> >TLS</option>
												<option value="ssl" <? if($ENCRYPTION_TYPE == 'ssl') echo "selected"; ?> >SSL</option>
											</select>
											<span class="bar"></span> 
											<label for="USER_NAME">Encryption Type</label>
										</div>
									</div>
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<input type="text" class="required-entry form-control" id="USER_NAME" name="USER_NAME" value="<?=$USER_NAME?>" placeholder="john@gmail.com" >
											<span class="bar"></span> 
											<label for="USER_NAME">User Name</label>
										</div>
									</div>
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<input type="password" class="required-entry form-control" id="PASSWORD" name="PASSWORD" value="<?=$PASSWORD?>" placeholder="Your Email Password" >
											<span class="bar"></span> 
											<label for="USER_NAME">Password</label>
										</div>
									</div>
									
									<div class="row">
										<div class="col-3 col-sm-3">
										</div>
										
										<div class="col-9 col-sm-9">
											<button type="submit" class="btn waves-effect waves-light btn-info">Save</button>
											<button type="button" onclick="window.location.href='index'"  class="btn waves-effect waves-light btn-dark">Cancel</button>
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
    </div>
   
	<? require_once("js.php"); ?>

	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	
	<script type="text/javascript">
		var form1 = new Validation('form1');
	</script>

</body>

</html>