<?php require_once('../global/config.php');
require_once("check_access.php");

if(check_access('REPORT_ADMISSION') == 0 ){
	header("location:../index");
	exit;
}

require_once '../global/mpdf/vendor/autoload.php';

$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
$SCHOOL_NAME = $res->fields['SCHOOL_NAME'];
$PDF_LOGO 	 = $res->fields['PDF_LOGO'];

$logo = "";
if($PDF_LOGO != '')
	$logo = '<img src="'.$PDF_LOGO.'" height="50px" />';
	
$timezone = $_SESSION['PK_TIMEZONE'];
if($timezone == '' || $timezone == 0) {
	$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$timezone = $res->fields['PK_TIMEZONE'];
	if($timezone == '' || $timezone == 0)
		$timezone = 4;
}

$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$res->fields['TIMEZONE'],date_default_timezone_get());

$campus_name  = "";
$campus_cond  = "";
$campus_cond1 = "";
$campus_id	  = "";
if($_GET['campus'] != ''){
	$PK_CAMPUS 	  = $_GET['campus'];
	$campus_cond  = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
	$campus_cond1 = " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
}

$res_campus = $db->Execute("select PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $campus_cond order by CAMPUS_CODE ASC");
while (!$res_campus->EOF) {
	if($campus_name != '')
		$campus_name .= ', ';
	$campus_name .= $res_campus->fields['CAMPUS_CODE'];
	
	if($campus_id != '')
		$campus_id .= ',';
	$campus_id .= $res_campus->fields['PK_CAMPUS'];
	
	$res_campus->MoveNext();
}

if($_GET['campus'] == ''){
	$campus_name = "";
	$res_campus = $db->Execute("SELECT CAMPUS_CODE FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE PK_STUDENT_ENROLLMENT IN ($_GET[eid]) AND S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS GROUP BY S_CAMPUS.PK_CAMPUS order by CAMPUS_CODE ASC ");
	while (!$res_campus->EOF) {
		if($campus_name != '')
			$campus_name .= ', ';
			
		$campus_name .= $res_campus->fields['CAMPUS_CODE'];
		
		$res_campus->MoveNext();
	}
}

$date_type = "Task";
if($_GET['type'] == 'FD')
	$date_type = "Follow Up";
	
$task_type = "";
if($_GET['tc'] == 0)
	$task_type = "All Tasks";
else if($_GET['tc'] == 1)
	$task_type = "Completed Tasks";
else if($_GET['tc'] == 2)
	$task_type = "Uncompleted Tasks";
	
$date_range = "";
if($_GET['st'] != '' && $_GET['et'] != '')
	$date_range = "Between: ".$_GET['st'].' - '.$_GET['et'];
else if($_GET['st'] != '')
	$date_range = "From: ".$_GET['st'];
else if($_GET['et'] != '')
	$date_range = "To: ".$_GET['et'];

$header = '<table width="100%" >
				<tr>
					<td width="20%" valign="top" >'.$logo.'</td>
					<td width="40%" valign="top" style="font-size:18px" >'.$SCHOOL_NAME.'</td>
					<td width="40%" valign="top" >
						<table width="100%" >
							<tr>
								<td width="100%" align="right" style="font-size:18px;border-bottom:1px solid #000;" ><b>Lead Tasks</b></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td width="100%" align="right" colspan="3">Campus(es): '.$campus_name.'</td>
				</tr>
				<tr>
					<td width="100%" align="right" colspan="3">Date Type: '.$date_type.'</td>
				</tr>
				<tr>
					<td width="100%" align="right" colspan="3">Task Type: '.$task_type.'</td>
				</tr>
				<tr>
					<td width="100%" align="right" colspan="3">'.$date_range.'</td>
				</tr>
			</table>';
			
$footer = '<table width="100%" >
				<tr>
					<td width="33%" valign="top" style="font-size:10px;" ><i>'.$date.'</i></td>
					<td width="33%" valign="top" style="font-size:10px;" align="center" ><i></i></td>
					<td width="33%" valign="top" style="font-size:10px;" align="right" ><i>Page {PAGENO} of {nb}</i></td>
				</tr>
			</table>';

$mpdf = new \Mpdf\Mpdf([
	'margin_left' => 7,
	'margin_right' => 5,
	'margin_top' => 35,
	'margin_bottom' => 15,
	'margin_header' => 3,
	'margin_footer' => 10,
	'default_font_size' => 8,
	'format' => [210, 296],
	'orientation' => 'L'
]);
$mpdf->autoPageBreak = true;

$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLFooter($footer);

//echo $_SESSION['task_report_query']." AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN ($_GET[eid]) GROUP BY PK_STUDENT_TASK ".$_SESSION['task_report_order_by'];exit;
$res = $db->Execute($_SESSION['task_report_query']." AND S_STUDENT_TASK.PK_STUDENT_MASTER IN ($_GET[sid]) GROUP BY PK_STUDENT_TASK ".$_SESSION['task_report_order_by']);

$EMP_NAME = '';

$txt = '';
$i = 0;
while (!$res->EOF) { 
	$TASK_TIME = '';
	if($res->fields['TASK_TIME'] != '00-00-00') 
		$TASK_TIME = date("h:i A", strtotime($res->fields['TASK_TIME']));
	
	$i++;
	if($EMP_NAME != $res->fields['EMP_NAME'] || $i == 1){
		if($txt != '')
			$txt .= "</table><br />";
			
		$EMP_NAME = $res->fields['EMP_NAME'];
		
		$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<thead>
						<tr>
							<td width="100%" style="font-size:20px" colspan="12" ><i><b>'.$EMP_NAME.'</b></i></td>
						</tr>
						<tr>
							<td width="12%" style="border-bottom:1px solid #000;" ><b><i>Lead</i></b></td>
							<td width="9%" style="border-bottom:1px solid #000;" ><b><i>Campus</i></b></td>
							<td width="6%" style="border-bottom:1px solid #000;" ><b><i>First Term</i></b></td>
							<td width="10%" style="border-bottom:1px solid #000;" ><b><i>Program</i></b></td>
							<td width="9%" style="border-bottom:1px solid #000;" ><b><i>Status</i></b></td>
							<td width="6%" style="border-bottom:1px solid #000;" ><b><i>Task Date</i></b></td>
							<td width="6%" style="border-bottom:1px solid #000;" ><b><i>Task Time</i></b></td>
							<td width="10%" style="border-bottom:1px solid #000;" ><b><i>Task Type</i></b></td>
							<td width="10%" style="border-bottom:1px solid #000;" ><b><i>Task Status</i></b></td>
							<td width="9%" style="border-bottom:1px solid #000;" ><b><i>Task Other</i></b></td>
							<td width="7%" style="border-bottom:1px solid #000;" ><b><i>Priority</i></b></td>
							<td width="6%" style="border-bottom:1px solid #000;" ><b><i>Completed</i></b></td>
						</tr>
					</thead>';
	}
	$txt .= '<tr>
				<td ><a href="'.$http_path.'school/student?id='.$res->fields['PK_STUDENT_MASTER'].'&eid='.$res->fields['PK_STUDENT_ENROLLMENT'].'&tab=taskTab&t=1" target="_blank" >'.$res->fields['STU_NAME'].'</a></td>
				<td >'.$res->fields['CAMPUS_CODE'].'</td>
				<td >'.$res->fields['BEGIN_DATE_1'].'</td>
				<td >'.$res->fields['CODE'].'</td>
				<td >'.$res->fields['STUDENT_STATUS'].'</td>
				<td >'.$res->fields['TASK_DATE_1'].'</td>
				<td >'.$TASK_TIME.'</td>
				<td >'.$res->fields['TASK_TYPE'].'</td>
				<td >'.$res->fields['TASK_STATUS'].'</td>
				<td >'.$res->fields['EVENT_OTHER'].'</td>
				<td >'.$res->fields['NOTES_PRIORITY'].'</td>
				<td >'.$res->fields['COMPLETED'].'</td>
			</tr>';
	
	$res->MoveNext();
}

if($txt != '')
	$txt .= "</table>";

//echo $txt;exit;

$mpdf->WriteHTML($txt);
$file_name = 'Task Report'.'.pdf';
$mpdf->Output($file_name, 'D');

return $file_name;	