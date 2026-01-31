<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/profile.php");
require_once("../language/student.php");

//echo "<pre>";print_r($_SESSION);exit;
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || $_SESSION['PK_USER_TYPE'] != 3 ){ 
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
	<title><?=PROFILE_PAGE_TITLE?> | <?=$title?></title>
</head>
<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=PROFILE_PAGE_TITLE?></h4>
                    </div>
                </div>	
				
				<div class="card-group">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
								<div class="col-md-6">
									<? $res = $db->Execute("SELECT IMAGE,FIRST_NAME,LAST_NAME,MIDDLE_NAME,OTHER_NAME FROM S_STUDENT_MASTER WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
									$IMAGE					= $res->fields['IMAGE'];
									$FIRST_NAME 			= $res->fields['FIRST_NAME'];
									$LAST_NAME 				= $res->fields['LAST_NAME'];
									$MIDDLE_NAME	 		= $res->fields['MIDDLE_NAME'];
									$OTHER_NAME	 			= $res->fields['OTHER_NAME']; 
									
									$res = $db->Execute("SELECT CODE,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, STUDENT_STATUS,IF(EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(EXPECTED_GRAD_DATE, '%m/%d/%Y' )) AS EXPECTED_GRAD_DATE FROM S_STUDENT_ENROLLMENT LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND IS_ACTIVE_ENROLLMENT = 1");
									$CAMPUS_PROGRAM  	= $res->fields['CODE'];
									$FIRST_TERM_DATE 	= $res->fields['BEGIN_DATE_1'];
									$STUDENT_STATUS  	= $res->fields['STUDENT_STATUS'];
									$EXPECTED_GRAD_DATE = $res->fields['EXPECTED_GRAD_DATE'];
									
									$res = $db->Execute("SELECT CONCAT(ADDRESS,' ',ADDRESS_1) AS ADDRESS, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL, EMAIL_OTHER  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' ");  ?>
									<div class="row">
										<div class="col-md-12">
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group"  >
													<table style="width:100%">
														<tr>
															<td style="width:25%"><?=FULL_NAME?>:</td>
															<td><?=$LAST_NAME.', '.$FIRST_NAME.' '.$MIDDLE_NAME?></td>
														</tr>
														<tr>
															<td style="width:25%"><?=CURRENT_ADDRESS?>:</td>
															<td><?=$res->fields['ADDRESS'].'<br />'.$res->fields['CITY'].', '.$res->fields['STATE_CODE'].' '.$res->fields['ZIP'] ?></td>
														</tr>
													</table>
												</div> 
											</div> 
										</div> 
									</div> 
							
									<div class="row">
										<div class="col-md-12">
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group"  >
													<h4 class="card-title" style="margin-bottom: 0px;" ><?=ENROLLMENT_INFO?></h4>
													<table style="width:100%">
														<tr>
															<td style="width:25%"><?=STATUS?>:</td>
															<td><?=$STUDENT_STATUS ?></td>
														</tr>
														<tr>
															<td style="width:25%"><?=PROGRAM?>:</td>
															<td><?=$CAMPUS_PROGRAM ?></td>
														</tr>
														<tr>
															<td style="width:25%"><?=FIRST_TERM_DATE?>:</td>
															<td><?=$FIRST_TERM_DATE?></td>
														</tr>
														<tr>
															<td style="width:25%"><?=EXPECTED_GRAD_DATE?>:</td>
															<td><?=$EXPECTED_GRAD_DATE?></td>
														</tr>
													</table>
												</div>
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-12">
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group"  >
													<h4 class="card-title" style="margin-bottom: 0px;" ><?=CONTACT_INFO?></h4>
													<table style="width:100%">
														<tr>
															<td style="width:25%"><?=HOME_PHONE_SHORT?>:</td>
															<td><?=$res->fields['HOME_PHONE'] ?></td>
														</tr>
														<tr>
															<td style="width:25%"><?=CELL_PHONE_SHORT?>:</td>
															<td><?=$res->fields['CELL_PHONE'] ?></td>
														</tr>
														<tr>
															<td style="width:25%"><?=WORK_PHONE_SHORT?>:</td>
															<td><?=$res->fields['WORK_PHONE'] ?></td>
														</tr>
														<tr>
															<td style="width:25%"><?=EMAIL?>:</td>
															<td><?=$res->fields['EMAIL']?></td>
														</tr>
														<tr>
															<td style="width:25%"><?=EMAIL_OTHER?>:</td>
															<td><?=$res->fields['EMAIL_OTHER']?></td>
														</tr>
													</table>
												</div>
											</div>
										</div>
									</div>
									
									<? $res = $db->Execute("SELECT CONCAT(ADDRESS,' ',ADDRESS_1) AS ADDRESS, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL, EMAIL_OTHER, CONTACT_NAME FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '2' "); ?>
									<div class="row">
										<div class="col-md-12">
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group"  >
													<h4 class="card-title" style="margin-bottom: 0px;" ><?=EMERGENCY_CONTACT?></h4>
													<table style="width:100%">
														<tr>
															<td style="width:25%"><?=FULL_NAME?>:</td>
															<td><?=$res->fields['CONTACT_NAME'] ?></td>
														</tr>
														<tr>
															<td style="width:25%"><?=CURRENT_ADDRESS?>:</td>
															<td><?=$res->fields['ADDRESS'].'<br />'.$res->fields['CITY'].', '.$res->fields['STATE_CODE'].' '.$res->fields['ZIP'] ?></td>
														</tr>
														<tr>
															<td style="width:25%"><?=HOME_PHONE_SHORT?>:</td>
															<td><?=$res->fields['HOME_PHONE'] ?></td>
														</tr>
														<tr>
															<td style="width:25%"><?=CELL_PHONE_SHORT?>:</td>
															<td><?=$res->fields['CELL_PHONE'] ?></td>
														</tr>
														<tr>
															<td style="width:25%"><?=WORK_PHONE_SHORT?>:</td>
															<td><?=$res->fields['WORK_PHONE'] ?></td>
														</tr>
														<tr>
															<td style="width:25%"><?=EMAIL?>:</td>
															<td><?=$res->fields['EMAIL']?></td>
														</tr>
														<tr>
															<td style="width:25%"><?=EMAIL_OTHER?>:</td>
															<td><?=$res->fields['EMAIL_OTHER']?></td>
														</tr>
													</table>
												</div>
											</div>
										</div>
									</div>
								</div> 
								<div class="col-md-6">
									<? if($IMAGE != ''){ ?>
										<img src="<?=$IMAGE?>" width="150px" /><br /><br />
									<? } ?>
									<button type="button" onclick="javascript:window.location.href='../school/create_student_id?s=1'" class="btn waves-effect waves-light btn-info"><?=DIGITAL_STUDENT_ID ?></button>
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