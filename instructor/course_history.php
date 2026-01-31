<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/dashboard.php");
require_once("../language/instructor_course_history.php");
//echo "<pre>";print_r($_SESSION);exit;
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
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
	<title><?=COURSE_HISTORY_PAGE_TITLE?> | <?=$title?></title>
</head>
<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=COURSE_HISTORY_PAGE_TITLE?></h4>
                    </div>
                </div>				
				<div class="card-group">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
								<form class="floating-labels w-100 m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off">
									<div class="row">
										<div class="col-12 form-group">
											<table class="table table-bordered">
												<thead>
													<tr>
														<th ><?=INSTRUCTOR?></th>
														<th ><?=TERM_BEGIN_DATE?></th>
														<th ><?=COURSE?></th>
														<th ><?=COURSE_DESCRIPTION?></th>
														<th ><?=STUDENT_IN_PROGRESS?></th>
														<th ><?=TOTAL_STUDENTS?></th>
													</tr>
												</thead>
												<tbody>
													<? $res = $db->Execute("SELECT CONCAT(FIRST_NAME,' ',LAST_NAME) as NAME FROM S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' ");
													
													$res_stu = $db->Execute("select S_COURSE_OFFERING.PK_COURSE_OFFERING, COURSE_DESCRIPTION, IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00', DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') AS BEGIN_DATE1, COURSE_CODE, SESSION, SESSION_NO from 

													S_COURSE_OFFERING
													LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
													LEFT JOIn M_SESSION On M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
													LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER 
													LEFT JOIN S_COURSE_OFFERING_ASSISTANT ON S_COURSE_OFFERING_ASSISTANT.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING
													
													WHERE 
													S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
													(INSTRUCTOR = '$_SESSION[PK_EMPLOYEE_MASTER]' OR S_COURSE_OFFERING_ASSISTANT.ASSISTANT = '$_SESSION[PK_EMPLOYEE_MASTER]') 
													GROUP BY PK_COURSE_OFFERING ORDER BY BEGIN_DATE ASC ");
													while (!$res_stu->EOF) { 
														$PK_COURSE_OFFERING = $res_stu->fields['PK_COURSE_OFFERING']; 
													
														$res1 = $db->Execute("SELECT PK_STUDENT_COURSE FROM S_STUDENT_COURSE WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ");
														$res2 = $db->Execute("SELECT FINAL_GRADE FROM S_STUDENT_COURSE WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND FINAL_GRADE = 0 
														UNION 
														SELECT FINAL_GRADE FROM S_STUDENT_COURSE, S_GRADE WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND FINAL_GRADE != 0 AND  S_GRADE.PK_GRADE = FINAL_GRADE AND (IS_DEFAULT = 1 OR UNITS_IN_PROGRESS = 1) "); ?>
														<tr>
															<td >
																<?=$res->fields['NAME']?>
															</td>
															<td >
																<?=$res_stu->fields['BEGIN_DATE1'];?>
															</td>
															<td >
																<?=$res_stu->fields['COURSE_CODE'].' ('.$res_stu->fields['SESSION'].' - '.$res_stu->fields['SESSION_NO'].')' ?>
															</td>
															<td >
																<?=$res_stu->fields['COURSE_DESCRIPTION'];?>
															</td>
															<td >
																<?=$res2->RecordCount() ?>
															</td>
															<td >
																<?=$res1->RecordCount() ?>
															</td>
														</tr>
														<?	$res_stu->MoveNext();
													} ?>
												</tbody>
											</table>
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
    <? require_once("js.php"); ?>
</body>
</html>