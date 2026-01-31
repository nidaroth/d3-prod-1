<? /*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

require_once("../global/config.php");
require_once('../global/phpmailer/class.phpmailer.php');
require_once('../global/phpmailer/class.smtp.php');
require_once("../language/smtp_settings.php");
require_once("check_access.php");

if(check_access('SETUP_COMMUNICATION') == 0 ){
	header("location:../index");
	exit;
}

$msg = '';	
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;

	$EMAIL_ACCOUNT = $_POST;
	$EMAIL_ACCOUNT['PASSWORD'] = my_encrypt('',$EMAIL_ACCOUNT['PASSWORD']);
	if($_GET['id'] == ''){
		$EMAIL_ACCOUNT['PK_ACCOUNT'] = $_SESSION['PK_ACCOUNT'];
		$EMAIL_ACCOUNT['CREATED_BY'] = $_SESSION['PK_USER'];
		$EMAIL_ACCOUNT['CREATED_ON'] = date("Y-m-d H:i:s");
		db_perform('Z_EMAIL_ACCOUNT', $EMAIL_ACCOUNT, 'insert');
		$PK_EMAIL_ACCOUNT = $db->insert_ID();
	} else {
		$EMAIL_ACCOUNT['EDITED_BY'] = $_SESSION['PK_USER'];
		$EMAIL_ACCOUNT['EDITED_ON'] = date("Y-m-d H:i:s");
		db_perform('Z_EMAIL_ACCOUNT', $EMAIL_ACCOUNT, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMAIL_ACCOUNT = '$_GET[id]' ");
		$PK_EMAIL_ACCOUNT = $_GET['id'];
	}
	
	// $EMAIL = 'balaji@codingdesk.in';
	$EMAIL = 'brandon.osborne@coderpro.net';

	$res_broad = $db->Execute("SELECT * FROM Z_EMAIL_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMAIL_ACCOUNT = '$PK_EMAIL_ACCOUNT' ");
	$mail      = new PHPMailer(true);

	$mail->IsSMTP(); // telling the class to use SMT_broad);
	$mail->SMTPDebug  = 4;                     // enables SMTP debug information (for testing)
											   // 1 = errors and messages
											   // 2 = messages only
	$mail->SMTPOptions = array(
                  'ssl' => array(
                      'verify_peer' => false,
                      'verify_peer_name' => false,
                      'allow_self_signed' => true
                  )
              );
	
	//$mail->SMTPSecure = $res_broad->fields['ENCRYPTION_TYPE'];                  // sets the prefix to the servier
	//$mail->SMTPSecure = 'tls';
	$mail->SMTPAutoTLS = true; 
	$mail->Host       = $res_broad->fields['HOST'];      // sets GMAIL as the SMTP server
	$mail->Port	  = $res_broad->fields['PORT'];
	$mail->Username   = $res_broad->fields['USER_NAME'];    // GMAIL username
	$mail->Password   = my_decrypt('',$res_broad->fields['PASSWORD']);            // GMAIL password
	
	$mail->SetFrom($res_broad->fields['USER_NAME'], '');

	$mail->Subject = 'Mail From newdiamondsis.com';
	$mail->MsgHTML('This is a test mail from newdiamondsis.com');
	$mail->AddAddress($EMAIL,'');
	
        var_dump($res_broad);
        echo "<br><br>";
        var_dump($mail);
        echo "<br><br>";
	echo "Mailer Error: " . $mail->ErrorInfo;

	phpinfo();

	if(!$mail->Send()) {
		echo "aaa";exit;
		// header("location:smtp_settings?id=".$PK_EMAIL_ACCOUNT.'&e=1');
	} else {
		$msg = 'SMTP Configured Successfully!!!';
		header("location:manage_smtp_settings");
	}
}

if($_GET['id'] == ''){
	$HOST 				= '';
	$PORT 				= '';
	$USER_NAME 			= '';
	$PASSWORD			= '';
	$ENCRYPTION_TYPE	= '';
} else {
	$res = $db->Execute("select * from Z_EMAIL_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMAIL_ACCOUNT = '$_GET[id]' ");
	if($res->RecordCount() == 0) {
		header("location:manage_smtp_settings");
		exit;
	}
	$HOST 				= $res->fields['HOST'];
	$PORT 				= $res->fields['PORT'];
	$USER_NAME 			= $res->fields['USER_NAME'];
	$PASSWORD			= $res->fields['PASSWORD'];
	$ENCRYPTION_TYPE	= $res->fields['ENCRYPTION_TYPE'];
	
	$PASSWORD = my_decrypt('',$PASSWORD);
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
	<title><?=SMTP_SETTINGS_PAGE_TITLE ?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=SMTP_SETTINGS_PAGE_TITLE ?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" >
								<div class="p-20">
									<? if($_GET['e'] == 1){ ?>
										<div class="form-group">
											<label for="input-text" class="col-sm-2 control-label"></label>
											<div class="col-sm-10" style="color:red">
												Something is wrong. We are not able to connect to the mail client. Please check the configuration and try again.
											</div>
										</div>
									<? } ?>
								
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group focused" id="HOST_LBL">
											<input type="text" class="form-control required-entry" id="HOST" name="HOST" value="<?=$HOST?>"  placeholder="smtp.gmail.com"  >
											<span class="bar"></span> 
											<label for="HOST"><?=HOST?></label>
										</div>
									</div>
								
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group focused" id="PORT_LBL">
											<input type="text" class="required-entry form-control" id="PORT" name="PORT" value="<?=$PORT?>" placeholder="465" >
											<span class="bar"></span> 
											<label for="PORT"><?=PORT?></label>
										</div>
									</div>
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group focused" >
											<select id="ENCRYPTION_TYPE" name="ENCRYPTION_TYPE" class="form-control">
												<option value="tls" <? if($ENCRYPTION_TYPE == 'tls') echo "selected"; ?> >TLS</option>
												<option value="ssl" <? if($ENCRYPTION_TYPE == 'ssl') echo "selected"; ?> >SSL</option>
											</select>
											<span class="bar"></span> 
											<label for="USER_NAME"><?=ENCRYPTION_TYPE?></label>
										</div>
									</div>
									
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group focused" id="USER_NAME_LBL">
											<input type="text" class="required-entry form-control" id="USER_NAME" name="USER_NAME" value="<?=$USER_NAME?>" placeholder="john@gmail.com" >
											<span class="bar"></span> 
											<label for="USER_NAME"><?=USER_NAME?></label>
										</div>
									</div>
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group focused" id="PASSWORD_LBL">
											<input type="password" class="required-entry form-control" id="PASSWORD" name="PASSWORD" value="<?=$PASSWORD?>" placeholder="Your Email Password" >
											<span class="bar"></span> 
											<label for="PASSWORD"><?=PASSWORD?></label>
										</div>
									</div>
									
									<div class="row">
										<div class="col-3 col-sm-3">
										</div>
										
										<div class="col-9 col-sm-9">
											<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
											<button type="button" onclick="window.location.href='manage_smtp_settings'"  class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
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
		
		jQuery(document).ready(function($) {
			document.getElementById('HOST_LBL').classList.add("focused");
			document.getElementById('PORT_LBL').classList.add("focused");
			document.getElementById('USER_NAME_LBL').classList.add("focused");
			document.getElementById('PASSWORD_LBL').classList.add("focused");
		});
	</script>

</body>

</html>
