<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
require_once("../language/mail.php");

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

$res_pk   = $db->Execute("select PK_INTERNAL_EMAIL, SUBJECT from Z_INTERNAL_EMAIL WHERE INTERNAL_ID = '$_GET[id]' ORDER BY PK_INTERNAL_EMAIL DESC"); 
$PK_INTERNAL_EMAIL 	= $res_pk->fields['PK_INTERNAL_EMAIL'];
$SUBJECT 			= $res_pk->fields['SUBJECT'];	
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
	<title><?=EMAIL_TITLE?> | <?=$title?></title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/pages/inbox.css">
	<style>
		h2 { width:100%; text-align:center; border-bottom: 1px solid #000; line-height:0.1em; margin:10px 0 20px; font-size:15px;padding-top:10px;} 
		h2 span { background:#fff; padding:0 10px; }
	</style>
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
							<?=$SUBJECT  ?> 
						</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<? $res = $db->Execute("select PK_INTERNAL_EMAIL_RECEPTION,Z_INTERNAL_EMAIL.PK_INTERNAL_EMAIL, CONTENT,IF(REMINDER_DATE != '0000-00-00', DATE_FORMAT(REMINDER_DATE, '%m/%d/%Y'),'' ) AS REMINDER_DATE, IF(PK_USER_TYPE = 2, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) , IF(PK_USER_TYPE = 3, CONCAT(S_STUDENT_MASTER.FIRST_NAME,' ',S_STUDENT_MASTER.LAST_NAME) , IF(PK_USER_TYPE = 1, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME),'') )) AS NAME ,IF(DUE_DATE != '0000-00-00', DATE_FORMAT(DUE_DATE, '%m/%d/%Y'),'' ) AS DUE_DATE, IF(Z_INTERNAL_EMAIL_RECEPTION.CREATED_ON != '0000-00-00', DATE_FORMAT(Z_INTERNAL_EMAIL_RECEPTION.CREATED_ON, '%m/%d/%Y %r'),'' ) AS CREATED_ON, IF(PK_USER_TYPE = 2, S_EMPLOYEE_MASTER.IMAGE , IF(PK_USER_TYPE = 3, S_STUDENT_MASTER.IMAGE , IF(PK_USER_TYPE = 1, S_EMPLOYEE_MASTER.IMAGE,'') )) AS IMAGE 
								from 
								Z_INTERNAL_EMAIL, 
								Z_INTERNAL_EMAIL_RECEPTION, 
								Z_USER 
								LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID AND PK_USER_TYPE IN (1,2)  
								LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = Z_USER.ID AND PK_USER_TYPE = 3 
								WHERE 
								Z_INTERNAL_EMAIL.INTERNAL_ID = '$_GET[id]' AND 
								Z_INTERNAL_EMAIL_RECEPTION.PK_INTERNAL_EMAIL = Z_INTERNAL_EMAIL.PK_INTERNAL_EMAIL AND 
								Z_USER.PK_USER = Z_INTERNAL_EMAIL.CREATED_BY 
								GROUP BY Z_INTERNAL_EMAIL.PK_INTERNAL_EMAIL ORDER BY PK_INTERNAL_EMAIL_RECEPTION DESC ");
								
								$i = 0;
								while (!$res->EOF) { 
									$PK_INTERNAL_EMAIL = $res->fields['PK_INTERNAL_EMAIL'];
									$style = '';
									if($i > 0)
										$style = 'display:none;'; ?>
										
									<h2 onclick="show_div(<?=$i?>)" ><span><?=$res->fields['CREATED_ON']?></span></h2>
									<div id="content_div_<?=$i?>"  style="border:1px dashed #000; padding:5px;border-radius: 7px;<?=$style?>" >
										<div class="d-flex m-b-40">
											<? if($res->fields['IMAGE'] != ''){ ?>
											<div>
												<a href="javascript:void(0)">
													<img src="<?=$res->fields['IMAGE']?>" alt="user" width="40" class="img-circle" />
												</a>
											</div>
											<? } ?>
											<div class="p-l-10">
												<h4 class="m-b-0"><?=$res->fields['NAME']?></h4>
											</div>
										</div>
										<div class="d-flex m-b-40">
											<div class="p-l-10">
												<h4 class="m-b-0">
													<b>To: 
													<? $k = 0;
													$res_rep = $db->Execute("SELECT IF(PK_USER_TYPE = 2, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) , IF(PK_USER_TYPE = 3, CONCAT(S_STUDENT_MASTER.FIRST_NAME,' ',S_STUDENT_MASTER.LAST_NAME) , IF(PK_USER_TYPE = 1, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME),'') )) AS NAME, PK_USER_TYPE, ID  
													FROM 
													Z_INTERNAL_EMAIL_RECEPTION,
													Z_USER 
													LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID AND PK_USER_TYPE IN (1,2)  
													LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = Z_USER.ID AND PK_USER_TYPE = 3 
													WHERE 
													Z_INTERNAL_EMAIL_RECEPTION.PK_USER = Z_USER.PK_USER AND 
													PK_INTERNAL_EMAIL = '$PK_INTERNAL_EMAIL' ");
													while (!$res_rep->EOF) { 
														if($k > 0)
															echo ", ";
														echo $res_rep->fields['NAME'];
														
														$dep = "";
														if($res_rep->fields['PK_USER_TYPE'] == 2) {
															$PK_EMPLOYEE_MASTER = $res_rep->fields['ID'];
				
															$dep = '';
															$res2 = $db->Execute("select DEPARTMENT FROM M_DEPARTMENT,S_EMPLOYEE_DEPARTMENT WHERE S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT = M_DEPARTMENT.PK_DEPARTMENT AND PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' ");
															while (!$res2->EOF) {
																if($dep != '')
																	$dep .= ', ';
																	
																$dep .= $res2->fields['DEPARTMENT'];
																$res2->MoveNext();
															}
														} else if($res_rep->fields['PK_USER_TYPE'] == 3) {
															$dep = "Student";
														}
														if($dep != '')
															echo ' ['.$dep.']';
														
														$res_rep->MoveNext();
														$k++;
													} ?>
													</b>
												</h4>
											</div>
										</div>
										<p><?=$res->fields['CONTENT']?></p>
							
										<? $res_att = $db->Execute("SELECT * FROM Z_INTERNAL_EMAIL_ATTACHMENT WHERE PK_INTERNAL_EMAIL = '$PK_INTERNAL_EMAIL' AND ACTIVE = 1");
										if($res_att->RecordCount() > 0){ ?>
											<h4><i class="fa fa-paperclip m-r-10 m-b-10"></i> <?=ATTACHMENT?> <span>(<?=$res_att->RecordCount()?>)</span></h4>
											<? while (!$res_att->EOF) {  ?>
												<a href="<?=$res_att->fields['LOCATION']?>" target="_blank" ><?=$res_att->fields['FILE_NAME']?></a><br />
											<? $res_att->MoveNext();
											} ?>
										<? }
										?>
									</div>
								<?	$i++;
									$res->MoveNext();
								}?>
                            </div>
                        </div>
					</div>
				</div>
				
            </div>
        </div>
        <? require_once("footer.php"); ?>
    </div>
   
	<? require_once("js.php"); ?>
	
	<script type="text/javascript">
		function show_div(id){
			jQuery(document).ready(function($) {
				$('#content_div_'+id).slideToggle(200);
			});
			/*if(document.getElementById('content_div_'+id).style.display == 'block')
				document.getElementById('content_div_'+id).style.display = 'none';
			else
				document.getElementById('content_div_'+id).style.display = 'block';*/
		}
	</script>
	
</body>

</html>