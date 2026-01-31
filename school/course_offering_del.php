<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/course_offering.php");
require_once("../global/mail.php");
require_once("../global/texting.php");
require_once("check_access.php");
require_once("function_make_attendance_inactive.php");

if (check_access('MANAGEMENT_REGISTRAR') == 0) {
	header("location:../index");
	exit;
} else {
	#Validate Incoming parameters 
	//V1. validate REQUEST PARAMS (DEL_PK_COURSE_OFFERING_GRADE)
	//V2. validate DEL_PK_COURSE_OFFERING_GRADE is and not empty 
	#After validation  
	//Act1. Delete course offering grade
	//Act2. Delete the same grade from students
	#End of algo
	//V1
	$res = [];
	if (isset($_REQUEST['PK_COURSE_OFFERING_GRADE'])) {
		//V2
		if (trim($_REQUEST['PK_COURSE_OFFERING_GRADE']) != '') {

			try {
				$PK_COURSE_OFFERING_GRADE = $_REQUEST['PK_COURSE_OFFERING_GRADE'];
				//Act1. Delete course offering grade
				$db_exec_1 = $db->Execute("DELETE from S_COURSE_OFFERING_GRADE WHERE PK_ACCOUNT='$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_GRADE='$PK_COURSE_OFFERING_GRADE'");
				//Act2. Delete the same grade from students
				$db_exec_2 = $db->Execute("DELETE from S_STUDENT_GRADE WHERE PK_ACCOUNT='$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_GRADE='$PK_COURSE_OFFERING_GRADE'");
				if ($db_exec_1->error || $db_exec_2->error) {
					// echo "<pre>";
					// var_dump($db_exec_1);
					// var_dump($db_exec_2);
					header('HTTP/1.1 500 Internal Server Booboo');
					$res['error'] = "Something went wronge! Please try again.";
				} else {
					$res['success'] = 'success';
				}
			} catch (\Throwable $th) {
				header('HTTP/1.1 500 Internal Server Booboo');
				$res['error'] = "Something went wronge! Please try again.";
			}
		}
	}  
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($res); 
}
