<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
require_once("check_access.php");

if(check_access('REPORT_REGISTRAR') == 0 && check_access('REGISTRAR_ACCESS') == 0){
	header("location:../index");
	exit;
}

$_GET['group_by']=$_POST['GROUP_BY'];
$_GET['eid']=implode(",",$_POST['PK_STUDENT_ENROLLMENT']);

include '../global/excel/Classes/PHPExcel/IOFactory.php';
$cell1  = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");		
define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

$total_fields = 120;
for($i = 0 ; $i <= $total_fields ; $i++){
	if($i <= 25)
		$cell[] = $cell1[$i];
	else {
		$j = floor($i / 26) - 1;
		$k = ($i % 26);
		//echo $j."--".$k."<br />";
		$cell[] = $cell1[$j].$cell1[$k];
	}	
}

$dir 			= 'temp/';
$inputFileType  = 'Excel2007';
$file_name 		= 'Last Day In Class.xlsx';
$outputFileName = $dir.$file_name; 
$outputFileName = str_replace(
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
$objReader->setIncludeCharts(TRUE);
//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
$objPHPExcel = new PHPExcel();
$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

	
$table 		= "";
$cond 		= "";
$FIELD 		= "";
$ID			= array();
$ID_NAME	= array();
if($_GET['group_by'] == 1){
	$FIELD = " S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM ";
	
	$res = $db->Execute("select S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM, M_CAMPUS_PROGRAM.CODE as PROGRAM_CODE FROM 
	S_STUDENT_MASTER
	LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
	, S_STUDENT_ENROLLMENT 
	LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.ARCHIVED = 0 AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN ($_GET[eid]) GROUP BY S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM ORDER BY M_CAMPUS_PROGRAM.CODE ASC");

	while (!$res->EOF) {
		$ID[] 		= $res->fields['PK_CAMPUS_PROGRAM'];
		$ID_NAME[] 	= "Program: ".$res->fields['PROGRAM_CODE'];
		$res->MoveNext();
	}
} else if($_GET['group_by'] == 2){
	$FIELD = " S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS ";
	
	$res = $db->Execute("select S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS, STUDENT_STATUS FROM 
	S_STUDENT_MASTER
	LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
	, S_STUDENT_ENROLLMENT 
	LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.ARCHIVED = 0 AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN ($_GET[eid]) GROUP BY S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS ORDER BY STUDENT_STATUS ASC ");

	while (!$res->EOF) {
		$ID[] 		= $res->fields['PK_STUDENT_STATUS'];
		$ID_NAME[] 	= "Status: ".$res->fields['STUDENT_STATUS'];
		$res->MoveNext();
	}
} else if($_GET['group_by'] == 3){
	$FIELD = " S_STUDENT_ENROLLMENT.PK_TERM_MASTER ";
	
	$res = $db->Execute("select S_STUDENT_ENROLLMENT.PK_TERM_MASTER, IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%Y-%m-%d' )) AS BEGIN_DATE_1 FROM 
	S_STUDENT_MASTER
	LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
	, S_STUDENT_ENROLLMENT 
	LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.ARCHIVED = 0 AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN ($_GET[eid]) GROUP BY S_STUDENT_ENROLLMENT.PK_TERM_MASTER ORDER BY S_TERM_MASTER.BEGIN_DATE ");

	while (!$res->EOF) {
		$ID[] 		= $res->fields['PK_TERM_MASTER'];
		$ID_NAME[] 	= "First Term: ".$res->fields['BEGIN_DATE_1'];
		$res->MoveNext();
	}
} else if($_GET['group_by'] == 4){
	$FIELD 		= " 1 ";
	$ID[] 		= 1;
	$ID_NAME[] 	= '';

} else if($_GET['group_by'] == 5){
	//$res = $db->Execute("select MAX(SCHEDULE_DATE) as SCHEDULE_DATE FROM S_STUDENT_SCHEDULE, S_STUDENT_ATTENDANCE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT IN ($_GET[eid]) AND  S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_ATTENDANCE_CODE = 14 AND S_STUDENT_ATTENDANCE.COMPLETED = 1 GROUP BY S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT ORDER BY SCHEDULE_DATE ASC ");

	/* DIAM-621, query changes as suggested by Barre */
	$res = $db->Execute("SELECT MAX(S_STUDENT_SCHEDULE.SCHEDULE_DATE) as SCHEDULE_DATE FROM S_STUDENT_SCHEDULE, S_STUDENT_ATTENDANCE, S_ATTENDANCE_CODE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE = S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.PRESENT = 1 AND S_STUDENT_ATTENDANCE.COMPLETED = 1 AND S_ATTENDANCE_CODE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT IN ($_GET[eid]) GROUP BY S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT ORDER BY SCHEDULE_DATE ASC ");
	/* End DIAM-621 */
	
	while (!$res->EOF) {
		$date1 = new DateTime($res->fields['SCHEDULE_DATE']);
		$date2 = new DateTime(date("Y-m-d"));
		$interval = $date1->diff($date2);

		$SCHEDULE_DATE 	= date("Y-m-d",strtotime($SCHEDULE_DATE));
		$days_ago 		= $interval->format('%a');
		if($interval->format('%R') == "-")
			$days_ago = "-".$days_ago;
		
		$flag = 1;
		foreach($ID as $ID1){
			if($ID1 == $res->fields['SCHEDULE_DATE']) {
				$flag = 0;
				break;
			}
		}
		if($flag == 1) {
			$ID[] 		= $res->fields['SCHEDULE_DATE'];
			$ID_NAME[] 	= "Days Ago: ".$days_ago;
		}
		
		$res->MoveNext();
	}

	$group_by   = "GROUP BY STUDENT_ID";
	$FIELD 		= " SCHEDULE_DATE ";
	$table 		= ",S_STUDENT_SCHEDULE, S_STUDENT_ATTENDANCE, S_ATTENDANCE_CODE"; // DIAM-621, Add new table S_ATTENDANCE_CODE
	//$cond 		= " AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND PK_ATTENDANCE_CODE = 14 AND S_STUDENT_ATTENDANCE.COMPLETED = 1 AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ";
	$cond 		= " AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE = S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.PRESENT = 1 AND S_ATTENDANCE_CODE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_ATTENDANCE.COMPLETED = 1 AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT "; // DIAM-621, as changes suggested by Barre
}

$line++;
$index 	= -1;
$heading[] = 'Student';
$width[]   = 30;
$heading[] = 'ID';
$width[]   = 30;
$heading[] = 'Student Status';
$width[]   = 15;
$heading[] = 'Student Program';
$width[]   = 15;
$heading[] = 'First Term';
$width[]   = 15;
$heading[] = 'Last Date In Class';
$width[]   = 20;
$heading[] = 'Next Course Start';
$width[]   = 15;
$heading[] = 'Days Ago';
$width[]   = 15;

$i = 0;
foreach($heading as $title) {
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
	$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
	
	$i++;
}

$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PRESENT = 1");
$present_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];

$k = 0;
$txt = '';
foreach($ID as $schedule_date1){

	$i = 1;
	$j = 0;
	$days_array=array(); // DIAM - 57

	if($k > 0)
		$txt .= "<br /><br /><br />";
		
	$res_att = $db->Execute("select CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) AS STU_NAME, STUDENT_ID, STUDENT_STATUS, M_CAMPUS_PROGRAM.CODE as PROGRAM_CODE, IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%Y-%m-%d' )) AS BEGIN_DATE, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT FROM 
	S_STUDENT_MASTER
	LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
	, S_STUDENT_ENROLLMENT 
	LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	$table 
	WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.ARCHIVED = 0 AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN ($_GET[eid]) $cond AND $FIELD = '$schedule_date1' $group_by ORDER BY CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) ASC");

	while (!$res_att->EOF) {
		$PK_STUDENT_ENROLLMENT = $res_att->fields['PK_STUDENT_ENROLLMENT'];

		$res_sch = $db->Execute("select MAX(SCHEDULE_DATE) as SCHEDULE_DATE FROM S_STUDENT_SCHEDULE, S_STUDENT_ATTENDANCE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND  S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_ATTENDANCE_CODE in ($present_att_code) AND S_STUDENT_ATTENDANCE.COMPLETED = 1");
		
		if($_GET['group_by'] == 5) // DIAM - 57
		{

			if($i == 1)
			{
				if($schedule_date1 == $res_sch->fields['SCHEDULE_DATE'])
				{
					
			
				}
				
			}
		} // End DIAM - 57
		else
		{
			if($i == 1){
				if($ID_NAME[$k] != '') {
					/*$line++;
					$index = 0;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($ID_NAME[$k]);
					$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);*/
				}
			}
		}
				
		
		$SCHEDULE_DATE = $res_sch->fields['SCHEDULE_DATE'];
		if($SCHEDULE_DATE != '' && $SCHEDULE_DATE != '0000-00-00') {
			$date1 = new DateTime($SCHEDULE_DATE);
			$date2 = new DateTime(date("Y-m-d"));
			$interval = $date1->diff($date2);

			$SCHEDULE_DATE 	= date("Y-m-d",strtotime($SCHEDULE_DATE));
			$days_ago 		= $interval->format('%a');
			if($interval->format('%R') == "-")
				$days_ago = "-".$days_ago;
		} else {
			$SCHEDULE_DATE 	= '';
			$days_ago 		= '';
		}
		
		$flag = 1;
		if($_GET['group_by'] == 5) {
			if($schedule_date1 != $res_sch->fields['SCHEDULE_DATE'])
				$flag = 0;
			else
				$j++;
		} else
			$j++;


		if($_GET['group_by'] == 5) { // DIAM - 57

			if($schedule_date1 == $res_sch->fields['SCHEDULE_DATE']) {

				if($j==1){

					if($ID_NAME[$k] != '') {
						/*$line++;
						$index = 0;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($ID_NAME[$k]);
						$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);*/
					}
				}
				// echo $flag; 
				// echo "Hello".$days_ago;
				// echo"<br>";
				array_push($days_array,$j);

				/* Ticket #1185 */
				$today 					= date("Y-m-d");
				$NEXT_COURSE_ST_DATE 	= '';
				$res_next_term = $db->Execute("select S_STUDENT_COURSE.PK_STUDENT_COURSE from S_STUDENT_COURSE, S_COURSE_OFFERING, S_TERM_MASTER WHERE S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND  S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER AND BEGIN_DATE >= '$today' ORDER BY BEGIN_DATE ASC ");
				if($res_next_term->RecordCount() > 0) {
					$PK_STUDENT_COURSE11 = $res_next_term->fields['PK_STUDENT_COURSE'];
					$res_next_sch = $db->Execute("select SCHEDULE_DATE FROM S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND  S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_COURSE = '$PK_STUDENT_COURSE11' AND SCHEDULE_DATE != '0000-00-00' ORDER BY SCHEDULE_DATE ASC ");
					if($res_next_sch->RecordCount() > 0) {
						$NEXT_COURSE_ST_DATE = date("Y-m-d",strtotime($res_next_sch->fields['SCHEDULE_DATE']));
					}
				}
				/* Ticket #1185 */
				
				$line++;
				$index = -1;
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_att->fields['STU_NAME']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_att->fields['STUDENT_ID']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_att->fields['STUDENT_STATUS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_att->fields['PROGRAM_CODE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_att->fields['BEGIN_DATE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($SCHEDULE_DATE);
				
				/* Ticket #1185 */
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($NEXT_COURSE_ST_DATE);
				/* Ticket #1185 */
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($days_ago);
			}


		} //  End DIAM - 57
		else{
		
			if($flag == 1) {
				/* Ticket #1185 */
				$today 					= date("Y-m-d");
				$NEXT_COURSE_ST_DATE 	= '';
				$res_next_term = $db->Execute("select S_STUDENT_COURSE.PK_STUDENT_COURSE from S_STUDENT_COURSE, S_COURSE_OFFERING, S_TERM_MASTER WHERE S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND  S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER AND BEGIN_DATE >= '$today' ORDER BY BEGIN_DATE ASC ");
				if($res_next_term->RecordCount() > 0) {
					$PK_STUDENT_COURSE11 = $res_next_term->fields['PK_STUDENT_COURSE'];
					$res_next_sch = $db->Execute("select SCHEDULE_DATE FROM S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND  S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_COURSE = '$PK_STUDENT_COURSE11' AND SCHEDULE_DATE != '0000-00-00' ORDER BY SCHEDULE_DATE ASC ");
					if($res_next_sch->RecordCount() > 0) {
						$NEXT_COURSE_ST_DATE = date("Y-m-d",strtotime($res_next_sch->fields['SCHEDULE_DATE']));
					}
				}
				/* Ticket #1185 */
				
				$line++;
				$index = -1;
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_att->fields['STU_NAME']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_att->fields['STUDENT_ID']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_att->fields['STUDENT_STATUS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_att->fields['PROGRAM_CODE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_att->fields['BEGIN_DATE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($SCHEDULE_DATE);
				
				/* Ticket #1185 */
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($NEXT_COURSE_ST_DATE);
				/* Ticket #1185 */
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($days_ago);
			}
		}
	
		$i++;
		$res_att->MoveNext();
	}
		
	$k++;
}

$objWriter->save($outputFileName);
$objPHPExcel->disconnectWorksheets();
header("location:".$outputFileName);