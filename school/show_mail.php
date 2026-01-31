<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
require_once("check_access.php");
$ADMISSION_ACCESS 	= check_access('ADMISSION_ACCESS');
$REGISTRAR_ACCESS 	= check_access('REGISTRAR_ACCESS');
$FINANCE_ACCESS 	= check_access('FINANCE_ACCESS');
$ACCOUNTING_ACCESS 	= check_access('ACCOUNTING_ACCESS');
$PLACEMENT_ACCESS 	= check_access('PLACEMENT_ACCESS');

if($ADMISSION_ACCESS == 0 && $REGISTRAR_ACCESS == 0 && $FINANCE_ACCESS == 0 && $ACCOUNTING_ACCESS == 0 && $PLACEMENT_ACCESS == 0 ){ 
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
	<title><?=MAIL?> | <?=$title?></title>
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
							<?=MAIL ?> 
						</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<? $res_1 = $db->Execute("select SUBJECT, SENT_ON, EMAIL_ID, MAIL_CONTENT FROM S_EMAIL_LOG WHERE PK_EMAIL_LOG = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); ?>
								<div class="row">
									<div class="col-md-2">
										<b><?=SUBJECT?></b>
									</div>
									<div class="col-md-102">
										<?=$res_1->fields['SUBJECT']?>
									</div>
								</div>
								
								<div class="row">
									<div class="col-md-2">
										<b><?=SENT_TO_MAIL?></b>
									</div>
									<div class="col-md-102">
										<?=$res_1->fields['EMAIL_ID']?>
									</div>
								</div>
								
								<div class="row">
									<div class="col-md-2">
										<b><?=SENT_ON?></b>
									</div>
									<div class="col-md-102">
										<? if($res_1->fields['SENT_ON'] != '0000-00-00 00:00:00')
											echo date("m/d/Y h:i A",strtotime($res_1->fields['SENT_ON'])); ?>
									</div>
								</div>
								
								<div class="row">
									<div class="col-md-2">
										<b><?=CONTENT?></b>
									</div>
									<div class="col-md-10">
										<?=$res_1->fields['MAIL_CONTENT']?>
									</div>
								</div>
								
								<div class="row">
									<div class="col-md-2">
										<b><?=ATTACHMENTS?></b>
									</div>
									<div class="col-md-10">
										<? $res_1 = $db->Execute("select FILE_NAME, FILE_LOCATION FROM S_EMAIL_LOG_ATTACHMENT WHERE PK_EMAIL_LOG = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
										while (!$res_1->EOF) { ?>
											<a href="<?=$res_1->fields['FILE_LOCATION']?>" target="_blank" ><?=$res_1->fields['FILE_NAME']?></a><br />
										<?	$res_1->MoveNext();
										} ?>
									</div>
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
	
</body>

</html>