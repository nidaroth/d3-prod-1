<?php session_start();
$browser = '';
if(stripos($_SERVER['HTTP_USER_AGENT'],"chrome") != false)
	$browser =  "chrome";
else if(stripos($_SERVER['HTTP_USER_AGENT'],"Safari") != false)
	$browser = "Safari";
else
	$browser = "firefox";
require_once('../global/tcpdf/config/lang/eng.php');
require_once('../global/tcpdf/tcpdf.php');
require_once('../global/config.php');
require_once("check_access.php");
require_once("get_department_from_t.php");
$PK_DEPARTMENT = get_department_from_t($_GET['t']);

$notes_cond 	= "";
$loa_cond		= "";
$probation_cond = "";
if($_GET['t'] == 6) {
	$notes_cond 		= " AND (S_STUDENT_NOTES.PK_DEPARTMENT = '$PK_DEPARTMENT' OR S_STUDENT_NOTES.PK_DEPARTMENT = -1) ";
	//$loa_cond 			= " AND (S_STUDENT_LOA.PK_DEPARTMENT = '$PK_DEPARTMENT' OR S_STUDENT_LOA.PK_DEPARTMENT = -1) ";
	//$probation_cond 	= " AND (S_STUDENT_PROBATION.PK_DEPARTMENT = '$PK_DEPARTMENT' OR S_STUDENT_PROBATION.PK_DEPARTMENT = -1) ";
}

if(check_access('ADMISSION_ACCESS') == 0 && check_access('REGISTRAR_ACCESS') == 0 && check_access('FINANCE_ACCESS') == 0 && check_access('ACCOUNTING_ACCESS') == 0 && check_access('PLACEMENT_ACCESS') == 0){
	header("location:../index");
	exit;
}
class MYPDF extends TCPDF {
    public function Header() {
		global $db;
		
		if($this->PageNo() == 1) {
			$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			if($res->fields['PDF_LOGO'] != '') {
				$ext = explode(".",$res->fields['PDF_LOGO']);
				$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
			}
			
			$this->SetFont('helvetica', '', 15);
			$this->SetY(8);
			$this->SetX(55);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(75, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
		} else {
			$this->SetFont('helvetica', 'I', 15);
			$this->SetY(8);
			$this->SetX(10);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(75, 8, $this->STUD_NAME , 0, false, 'L', 0, '', 0, false, 'M', 'L');
		}
		
		$this->SetFont('helvetica', 'I', 20);
		$this->SetY(8);
		$this->SetTextColor(000, 000, 000);
		if($_GET['t'] == 1) {
			$this->SetX(150);
			$sts = "Lead Information";
		} else {
			$this->SetX(140);
			$sts = "Student Information";
		}
		$this->Cell(55, 8, $sts, 0, false, 'L', 0, '', 0, false, 'M', 'L');

		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		if($_GET['t'] == 1) {
			$this->Line(140, 13, 203, 13, $style);
		} else {
			$this->Line(130, 13, 202, 13, $style);
		}
		
		
    }
    public function Footer() {
		global $db;
		
		$this->SetY(-15);
		$this->SetX(180);
		$this->SetFont('helvetica', 'I', 7);
		$this->Cell(30, 10, 'Page '.$this->getAliasNumPage().' of '.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		
		$this->SetY(-15);
		$this->SetX(10);
		$this->SetFont('helvetica', 'I', 7);
		
		$timezone = $_SESSION['PK_TIMEZONE'];
		if($timezone == '' || $timezone == 0) {
			$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$timezone = $res->fields['PK_TIMEZONE'];
			if($timezone == '' || $timezone == 0)
				$timezone = 4;
		}
		
		$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
		$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$res->fields['TIMEZONE'],date_default_timezone_get());
			
		$this->Cell(30, 10, $date, 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(7, 31, 7);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, 30);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setLanguageArray($l);
$pdf->setFontSubsetting(true);
$pdf->SetFont('helvetica', '', 10, '', true);
$pdf->AddPage();

$res = $db->Execute("SELECT  * FROM S_STUDENT_MASTER WHERE PK_STUDENT_MASTER = '$_GET[id]' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
if($res->RecordCount() == 0){
	header("location:manage_student?t=".$_GET['t']);
	exit;
}

$SSN	 				= $res->fields['SSN'];
$SSN_VERIFIED			= $res->fields['SSN_VERIFIED'];
$DATE_OF_BIRTH	 		= $res->fields['DATE_OF_BIRTH'];
$pdf->STUD_NAME 		= $res->fields['LAST_NAME'].", ".$res->fields['FIRST_NAME'];

if($DATE_OF_BIRTH != '0000-00-00')
	$DATE_OF_BIRTH = date("m/d/Y",strtotime($DATE_OF_BIRTH));
else
	$DATE_OF_BIRTH = '';

if($SSN != '') {
	$SSN 	 = my_decrypt($_SESSION['PK_ACCOUNT'].$_GET['id'],$SSN);
	$SSN_ORG = $SSN;
	$SSN_ARR = explode("-",$SSN);
	$SSN 	 = 'xxx-xx-'.$SSN_ARR[2];
}

if($SSN_VERIFIED == 1)
	$SSN_VERIFIED = 'Verified';
else
	$SSN_VERIFIED = '';
	
$res_address = $db->Execute("SELECT ADDRESS,ADDRESS_1, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 	

$res_enroll = $db->Execute("SELECT S_STUDENT_ENROLLMENT.*,CODE,STUDENT_STATUS,PK_STUDENT_STATUS_MASTER, LEAD_SOURCE, FUNDING, IF(BEGIN_DATE = '0000-00-00','', DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS TERM_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS EMP_NAME FROM S_STUDENT_ENROLLMENT LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING LEFT JOIN M_LEAD_SOURCE ON M_LEAD_SOURCE.PK_LEAD_SOURCE = S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS WHERE PK_STUDENT_MASTER = '$_GET[id]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$_GET[eid]' ");

$EXPECTED_GRAD_DATE = $res_enroll->fields['EXPECTED_GRAD_DATE'];
if($EXPECTED_GRAD_DATE != '0000-00-00')
	$EXPECTED_GRAD_DATE = date("m/d/Y",strtotime($EXPECTED_GRAD_DATE));
else
	$EXPECTED_GRAD_DATE = '';
	
if($_GET['t'] == 1) {
	$title = "Lead ";
} else {
	$title = "Student ";
}

$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
			<tr>
				<td width="55%">
					<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<tr>
							<td width="40%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;" ><b>First / Middle Initial</b></td>
							<td width="40%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;" >'.$res->fields['FIRST_NAME'].'</td>
							<td width="20%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;" >'.$res->fields['MIDDLE_NAME'].'</td>
						</tr>
						<tr>
							<td width="40%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;" ><b>Last Name</b></td>
							<td width="60%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;" >'.$res->fields['LAST_NAME'].'</td>
						</tr>
						<tr>
							<td width="40%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;" ><b>SSN / Status</b></td>
							<td width="40%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;" >'.$SSN.'</td>
							<td width="20%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;" >'.$SSN_VERIFIED.'</td>
						</tr>
						<tr>
							<td width="40%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;" ><b>Address</b></td>
							<td width="60%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;" >'.$res_address->fields['ADDRESS'].' '.$res_address->fields['ADDRESS_1'].'</td>
						</tr>
						<tr>
							<td width="40%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;" ><b>City / State / Zip</b></td>
							<td width="25%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;" >'.$res_address->fields['CITY'].'</td>
							<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;" >'.$res_address->fields['STATE_CODE'].'</td>
							<td width="20%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;" >'.$res_address->fields['ZIP'].'</td>
						</tr>
						<tr>
							<td width="40%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;" ><b>2nd Address</b></td>
							<td width="60%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;" ></td>
						</tr>
						<tr>
							<td width="40%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;" ><b>Phone / Ext</b></td>
							<td width="40%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;" >'.$res_address->fields['CELL_PHONE'].'</td>
							<td width="20%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;" ></td>
						</tr>
						<tr>
							<td width="40%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;" ><b>Fax</b></td>
							<td width="60%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;" ></td>
						</tr>
						<tr>
							<td width="40%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;" ><b>Email</b></td>
							<td width="60%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;" >'.$res_address->fields['EMAIL'].'</td>
						</tr>
						<tr>
							<td width="40%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Comments</b></td>
							<td width="60%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;border-right:0.5px solid #000;" >'.$res->fields['COMMENTS'].'</td>
						</tr>
					</table>
				</td>
				<td width="5%"> </td>
				<td width="40%">
					<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<tr>
							<td width="45%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;" ><b>Representative</b></td>
							<td width="55%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;" >'.$res_enroll->fields['EMP_NAME'].'</td>
						</tr>
						<tr>
							<td width="45%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;" ><b>Source Code</b></td>
							<td width="55%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;" >'.$res_enroll->fields['LEAD_SOURCE'].'</td>
						</tr>
						<tr>
							<td width="45%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;" ><b>Program</b></td>
							<td width="55%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;" >'.$res_enroll->fields['CODE'].'</td>
						</tr>
						<tr>
							<td width="45%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;" ><b>Funding</b></td>
							<td width="55%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;" >'.$res_enroll->fields['FUNDING'].'</td>
						</tr>
						<tr>
							<td width="45%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;" ><b>First Term Date</b></td>
							<td width="55%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;" >'.$res_enroll->fields['TERM_MASTER'].'</td>
						</tr>
						<tr>
							<td width="45%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;" ><b>Date</b></td>
							<td width="55%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;" >'.date("m/d/Y").'</td>
						</tr>
						<tr>
							<td width="45%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;" ><b>Time</b></td>
							<td width="55%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;" >'.date('h:i A').'</td>
						</tr>
						<tr>
							<td width="45%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;" ><b>Date Of Birth</b></td>
							<td width="55%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;" >'.$DATE_OF_BIRTH.'</td>
						</tr>
						<tr>
							<td width="45%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Expected Grad Date</b></td>
							<td width="55%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$EXPECTED_GRAD_DATE.'</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<h1>'.$title.' Tasks</h1>
		<br />';
		
		$res = $db->Execute("select TASK_TIME, TASK_TYPE, TASK_STATUS, S_STUDENT_TASK.NOTES ,IF(TASK_DATE = '0000-00-00', '',  DATE_FORMAT(TASK_DATE,'%m/%d/%Y')) AS TASK_DATE, IF(FOLLOWUP_DATE = '0000-00-00', '',  DATE_FORMAT(FOLLOWUP_DATE,'%m/%d/%Y')) AS FOLLOWUP_DATE, FOLLOWUP_TIME, IF(COMPLETED = 1,'Yes','No') as COMPLETED, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME,' ',S_EMPLOYEE_MASTER.MIDDLE_NAME) AS EMP_NAME, NOTES_PRIORITY
		FROM S_STUDENT_TASK 
		LEFT JOIN M_NOTES_PRIORITY_MASTER ON M_NOTES_PRIORITY_MASTER.PK_NOTES_PRIORITY_MASTER = S_STUDENT_TASK.PK_NOTES_PRIORITY_MASTER 
		LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_TASK.PK_EMPLOYEE_MASTER 
		LEFT JOIN M_TASK_TYPE ON M_TASK_TYPE.PK_TASK_TYPE = S_STUDENT_TASK.PK_TASK_TYPE 
		LEFT JOIN M_TASK_STATUS ON M_TASK_STATUS.PK_TASK_STATUS = S_STUDENT_TASK.PK_TASK_STATUS 
		WHERE 
		S_STUDENT_TASK.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
		S_STUDENT_TASK.PK_STUDENT_MASTER = '$_GET[id]'
		ORDER BY S_STUDENT_TASK.TASK_DATE ASC");
		if($res->RecordCount() == 0) {
			$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<tr>
							<td width="100%" ><b>No Tasks</b></td>
						</tr>
					</table>';
		} else {
			$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<tr>
						<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Task Date</b></td>
						<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Task Time</b></td>
						<td width="35%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Task</b></td>
						<td width="20%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Rep</b></td>
						<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Follow Up</b></td>
					</tr>
				</table>';
		
			$i = 1;
			while (!$res->EOF) {
				$TASK_TIME = '';
				if($res->fields['TASK_TIME'] != '00-00-00') 
					$TASK_TIME = date("h:i A", strtotime($res->fields['TASK_TIME']));
					
				$FOLLOWUP_TIME = '';
				if($res->fields['FOLLOWUP_TIME'] != '00-00-00') 
					$FOLLOWUP_TIME = date("h:i A", strtotime($res->fields['FOLLOWUP_TIME']));
					
				$txt .= '<table border="0" cellspacing="0" cellpadding="0" width="100%">
					<tr nobr="true" >
						<td>
							<table border="0" cellspacing="0" cellpadding="3" width="100%">
								<tr>
									<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['TASK_DATE'].'</td>
									<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$TASK_TIME.'</td>
									<td width="35%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['TASK_TYPE'].'</td>
									<td width="20%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['EMP_NAME'].'</td>
									<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['FOLLOWUP_DATE'].' '.$FOLLOWUP_TIME.'</td>
								</tr>
								<tr>
									<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Notes</b></td>
									<td width="85%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['NOTES'].'</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>';
				
				if($i != $res->RecordCount())
					$txt .= '<br /><br />';
				
				$i++;
				$res->MoveNext();
			}
		}
		
		$txt .= '<h1>'.$title.' Notes</h1>
		<br />'; 
		
		$res = $db->Execute("select DATE_FORMAT(NOTE_DATE,'%m/%d/%Y') AS NOTE_DATE,NOTE_TIME,  NOTES, NOTE_TYPE, CONCAT(EMP.FIRST_NAME,' ',EMP.LAST_NAME) AS EMP_NAME, IF(IS_EVENT = 1,'Yes', 'No') AS IS_EVENT, NOTES_PRIORITY, NOTE_STATUS, IF(FOLLOWUP_DATE = '0000-00-00', '',  DATE_FORMAT(FOLLOWUP_DATE,'%m/%d/%Y')) AS FOLLOWUP_DATE, PK_STUDENT_NOTES  
		FROM 
		S_STUDENT_NOTES 
		LEFT JOIN S_EMPLOYEE_MASTER AS EMP ON EMP.PK_EMPLOYEE_MASTER = S_STUDENT_NOTES.PK_EMPLOYEE_MASTER 
		LEFT JOIN M_NOTES_PRIORITY_MASTER ON M_NOTES_PRIORITY_MASTER.PK_NOTES_PRIORITY_MASTER = S_STUDENT_NOTES.PK_NOTES_PRIORITY_MASTER 
		LEFT JOIN M_NOTE_STATUS ON M_NOTE_STATUS.PK_NOTE_STATUS = S_STUDENT_NOTES.PK_NOTE_STATUS
		, M_NOTE_TYPE 
		WHERE 
		S_STUDENT_NOTES.PK_NOTE_TYPE = M_NOTE_TYPE.PK_NOTE_TYPE AND 
		S_STUDENT_NOTES.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
		S_STUDENT_NOTES.PK_STUDENT_MASTER = '$_GET[id]' AND 
		IS_EVENT = 0 $notes_cond ORDER BY S_STUDENT_NOTES.NOTE_DATE ASC ");
		if($res->RecordCount() == 0) {
			$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<tr>
							<td width="100%" ><b>No Notes</b></td>
						</tr>
					</table>';
		} else {
			$NOTE_TIME = '';
			if($res->fields['NOTE_TIME'] != '00:00:00')
				$NOTE_TIME = date("h:i A",strtotime($res->fields['NOTE_TIME']));
					
			$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<tr>
							<td width="12%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Note Date</b></td>
							<td width="12%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Note Time</b></td>
							<td width="26%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Note Type</b></td>
							<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Note Status</b></td>
							<td width="20%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Rep</b></td>
							<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Follow Up</b></td>
						</tr>
					</table>';
		
			$i = 1;
			while (!$res->EOF) {
				$txt .= '<table border="0" cellspacing="0" cellpadding="0" width="100%">
					<tr nobr="true" >
						<td>
							<table border="0" cellspacing="0" cellpadding="3" width="100%">
								<tr>
									<td width="12%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['NOTE_DATE'].'</td>
									<td width="12%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$NOTE_TIME.'</td>
									<td width="26%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['NOTE_TYPE'].'</td>
									<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['NOTE_STATUS'].'</td>
									<td width="20%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['EMP_NAME'].'</td>
									<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['FOLLOWUP_DATE'].'</td>
								</tr>
								<tr>
									<td width="12%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Notes</b></td>
									<td width="88%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['NOTES'].'</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>';
						
					if($i != $res->RecordCount())
					$txt .= '<br /><br />';
				
				$i++;
				$res->MoveNext();
			}
		}
		
		$txt .= '<h1>'.$title.' Events</h1>
		<br />'; 
		
		$res = $db->Execute("select DATE_FORMAT(S_STUDENT_NOTES.NOTE_DATE,'%m/%d/%Y') AS NOTE_DATE, NOTE_TIME,  NOTES, NOTE_TYPE, CONCAT(EMP.FIRST_NAME,' ',EMP.LAST_NAME) AS EMP_NAME, IF(IS_EVENT = 1,'Yes', 'No') AS IS_EVENT, NOTES_PRIORITY, NOTE_STATUS, IF(FOLLOWUP_DATE = '0000-00-00', '',  DATE_FORMAT(FOLLOWUP_DATE,'%m/%d/%Y')) AS FOLLOWUP_DATE, PK_STUDENT_NOTES FROM S_STUDENT_NOTES LEFT JOIN S_EMPLOYEE_MASTER AS EMP ON EMP.PK_EMPLOYEE_MASTER = S_STUDENT_NOTES.PK_EMPLOYEE_MASTER LEFT JOIN M_NOTES_PRIORITY_MASTER ON M_NOTES_PRIORITY_MASTER.PK_NOTES_PRIORITY_MASTER = S_STUDENT_NOTES.PK_NOTES_PRIORITY_MASTER LEFT JOIN M_NOTE_STATUS ON M_NOTE_STATUS.PK_NOTE_STATUS = S_STUDENT_NOTES.PK_NOTE_STATUS, M_NOTE_TYPE WHERE S_STUDENT_NOTES.PK_NOTE_TYPE = M_NOTE_TYPE.PK_NOTE_TYPE AND S_STUDENT_NOTES.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_NOTES.PK_STUDENT_MASTER = '$_GET[id]' AND IS_EVENT = 1 $notes_cond ORDER BY S_STUDENT_NOTES.NOTE_DATE ASC ");
		if($res->RecordCount() == 0) {
			$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<tr>
							<td width="100%" ><b>No Notes</b></td>
						</tr>
					</table>';
		} else {
			$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<tr>
							<td width="12%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Note Date</b></td>
							<td width="12%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Note Time</b></td>
							<td width="26%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Note Type</b></td>
							<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Note Status</b></td>
							<td width="20%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Rep</b></td>
							<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Follow Up</b></td>
						</tr>
					</table>';
		
			$i = 1;
			while (!$res->EOF) {
				$NOTE_TIME = '';
				if($res->fields['NOTE_TIME'] != '00:00:00')
					$NOTE_TIME = date("h:i A",strtotime($res->fields['NOTE_TIME']));
					
				$txt .= '<table border="0" cellspacing="0" cellpadding="0" width="100%">
							<tr nobr="true" >
								<td>
									<table border="0" cellspacing="0" cellpadding="3" width="100%">
										<tr>
											<td width="12%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['NOTE_DATE'].'</td>
											<td width="12%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$NOTE_TIME.'</td>
											<td width="26%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['NOTE_TYPE'].'</td>
											<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['NOTE_STATUS'].'</td>
											<td width="20%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['EMP_NAME'].'</td>
											<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['FOLLOWUP_DATE'].'</td>
										</tr>
										<tr>
											<td width="12%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Notes</b></td>
											<td width="88%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['NOTES'].'</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>';
							
					if($i != $res->RecordCount())
					$txt .= '<br /><br />';
				
				$i++;
				$res->MoveNext();
			}
		}
		
		////////////////////////////////
		$txt .= '<h1>'.$title.' LOA</h1>
		<br />'; 
		
		$res = $db->Execute("select PK_STUDENT_LOA,CODE,IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, S_STUDENT_LOA.NOTES, REASON,IF(S_STUDENT_LOA.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_LOA.BEGIN_DATE, '%m/%d/%Y' )) AS LOA_BEGIN_DATE ,IF(S_STUDENT_LOA.END_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_LOA.END_DATE, '%m/%d/%Y' )) AS LOA_END_DATE, DATEDIFF(S_STUDENT_LOA.END_DATE, S_STUDENT_LOA.BEGIN_DATE) AS NO_OF_DAYS FROM S_STUDENT_LOA LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_LOA.PK_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_LOA.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_LOA.PK_STUDENT_MASTER = '$_GET[id]' $loa_cond ORDER BY LOA_BEGIN_DATE ASC ");
		if($res->RecordCount() == 0) {
			$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<tr>
							<td width="100%" ><b>No LOA</b></td>
						</tr>
					</table>';
		} else {
			$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<tr>
							<td width="12%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Begin Date</b></td>
							<td width="12%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>End Date</b></td>
							<td width="12%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>No. of Days</b></td>
							<td width="64%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Reason</b></td>
						</tr>
					</table>';
		
			$i = 1;
			while (!$res->EOF) {
				$NO_OF_DAYS = '';
				if($res->fields['NO_OF_DAYS'] > 0)
					$NO_OF_DAYS = ($res->fields['NO_OF_DAYS'] + 1);
				$txt .= '<table border="0" cellspacing="0" cellpadding="0" width="100%">
							<tr nobr="true" >
								<td>
									<table border="0" cellspacing="0" cellpadding="3" width="100%">
									<tr>
										<td width="12%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['LOA_BEGIN_DATE'].'</td>
										<td width="12%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['LOA_END_DATE'].'</td>
										<td width="12%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$NO_OF_DAYS.'</td>
										<td width="64%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['REASON'].'</td>
									</tr>
									<tr>
										<td width="12%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Notes</b></td>
										<td width="88%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;" >'.nl2br($res->fields['NOTES']).'</td>
									</tr>
								</table>
							</tr>
						</table>';
						
					if($i != $res->RecordCount())
					$txt .= '<br /><br />';
				
				$i++;
				$res->MoveNext();
			}
		}
		////////////////////////////////
		
		$txt .= '<h1>'.$title.' Probation</h1>
		<br />'; 
		
		$res = $db->Execute("select PK_STUDENT_PROBATION,CODE,IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, S_STUDENT_PROBATION.NOTES, REASON,IF(S_STUDENT_PROBATION.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_PROBATION.BEGIN_DATE, '%m/%d/%Y' )) AS PROBATION_BEGIN_DATE ,IF(S_STUDENT_PROBATION.END_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_PROBATION.END_DATE, '%m/%d/%Y' )) AS PROBATION_END_DATE, PROBATION_TYPE, PROBATION_LEVEL, PROBATION_STATUS FROM S_STUDENT_PROBATION LEFT JOIN M_PROBATION_TYPE ON M_PROBATION_TYPE.PK_PROBATION_TYPE = S_STUDENT_PROBATION.PK_PROBATION_TYPE LEFT JOIN M_PROBATION_LEVEL ON M_PROBATION_LEVEL.PK_PROBATION_LEVEL = S_STUDENT_PROBATION.PK_PROBATION_LEVEL LEFT JOIN M_PROBATION_STATUS ON M_PROBATION_STATUS.PK_PROBATION_STATUS = S_STUDENT_PROBATION.PK_PROBATION_STATUS LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_PROBATION.PK_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_PROBATION.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_PROBATION.PK_STUDENT_MASTER = '$_GET[id]' $probation_cond ORDER BY S_STUDENT_PROBATION.BEGIN_DATE ASC ");
		
		if($res->RecordCount() == 0) {
			$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<tr>
							<td width="100%" ><b>No Probation</b></td>
						</tr>
					</table>';
		} else {
			$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<tr>
							<td width="12%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Begin Date</b></td>
							<td width="12%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>End Date</b></td>
							<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Probation Type</b></td>
							<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Probation Level</b></td>
							<td width="16%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Probation Status</b></td>
							<td width="30%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Reason</b></td>
						</tr>
					</table>';
		
			$i = 1;
			while (!$res->EOF) {
				$txt .= '<table border="0" cellspacing="0" cellpadding="0" width="100%">
							<tr nobr="true" >
								<td>
									<table border="0" cellspacing="0" cellpadding="3" width="100%">
									<tr>
										<td width="12%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['PROBATION_BEGIN_DATE'].'</td>
										<td width="12%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['PROBATION_END_DATE'].'</td>
										<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['PROBATION_TYPE'].'</td>
										<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['PROBATION_LEVEL'].'</td>
										<td width="16%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['PROBATION_STATUS'].'</td>
										<td width="30%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['REASON'].'</td>
									</tr>
									<tr>
										<td width="12%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Notes</b></td>
										<td width="88%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;" >'.nl2br($res->fields['NOTES']).'</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>';
						
					if($i != $res->RecordCount())
					$txt .= '<br /><br />';
				
				$i++;
				$res->MoveNext();
			}
		}
		
		if($_GET['t'] == 6) {
			////////////////////////////////
			$txt .= '<h1>Student Jobs</h1>
			<br />'; 
			
			$res = $db->Execute("SELECT S_COMPANY.COMPANY_NAME, M_PAY_TYPE.PAY_TYPE, M_PLACEMENT_TYPE.TYPE AS JOB_TYPE, M_PLACEMENT_STATUS.PLACEMENT_STATUS AS JOB_STATUS, PK_STUDENT_ENROLLMENT,  S_STUDENT_JOB.PK_STUDENT_JOB,S_COMPANY_JOB.JOB_TITLE, S_COMPANY_JOB.PK_COMPANY_JOB, S_COMPANY_JOB.PAY_AMOUNT,IF(S_STUDENT_JOB.CURRENT_JOB = 1,'Yes', 'No') as CURRENT_JOB, IF(S_STUDENT_JOB.START_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_JOB.START_DATE, '%m/%d/%Y' )) AS START_DATE, IF(S_STUDENT_JOB.END_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_JOB.END_DATE, '%m/%d/%Y' )) AS END_DATE FROM S_STUDENT_JOB LEFT JOIN S_COMPANY_JOB ON S_STUDENT_JOB.PK_COMPANY_JOB = S_COMPANY_JOB.PK_COMPANY_JOB LEFT JOIN S_COMPANY ON S_STUDENT_JOB.PK_COMPANY = S_COMPANY.PK_COMPANY LEFT JOIN M_PAY_TYPE ON S_STUDENT_JOB.PK_PAY_TYPE = M_PAY_TYPE.PK_PAY_TYPE LEFT JOIN M_PLACEMENT_TYPE ON M_PLACEMENT_TYPE.PK_PLACEMENT_TYPE = S_STUDENT_JOB.PK_PLACEMENT_TYPE  LEFT JOIN M_PLACEMENT_STATUS ON M_PLACEMENT_STATUS.PK_PLACEMENT_STATUS = S_STUDENT_JOB.PK_PLACEMENT_STATUS WHERE S_STUDENT_JOB.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_GET[id]'  ORDER BY S_STUDENT_JOB.CREATED_ON DESC");
			
			if($res->RecordCount() == 0) {
				$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
							<tr>
								<td width="100%" ><b>No Student Jobs</b></td>
							</tr>
						</table>';
			} else {
				$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
							<tr>
								<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Enrollment</b></td>
								<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Company Name</b></td>
								<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Job Title</b></td>
								<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Job Type</b></td>
								<td width="10%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Job Status</b></td>
								
								<td width="10%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Start Date</b></td>
								<td width="10%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>End Date</b></td>
								
								<td width="10%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Current Job</b></td>
							</tr>';
			
				$i = 1;
				while (!$res->EOF) {
					$PK_STUDENT_ENROLLMENT = $res->fields['PK_STUDENT_ENROLLMENT'];
					$res_en = $db->Execute("SELECT PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IS_ACTIVE_ENROLLMENT FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE  S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ");
													
					$txt .= '<tr nobr="true" >
								<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res_en->fields['BEGIN_DATE_1'].' - '.$res_en->fields['CODE'].' - '.$res_en->fields['STUDENT_STATUS'].'</td>
								<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['COMPANY_NAME'].'</td>
								<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['JOB_TITLE'].'</td>
								<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['JOB_TYPE'].'</td>
								<td width="10%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['JOB_STATUS'].'</td>
								<td width="10%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['START_DATE'].'</td>
								<td width="10%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['END_DATE'].'</td>
								<td width="10%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['CURRENT_JOB'].'</td>
							</tr>';
				
					$res->MoveNext();
				}
				$txt .= '</table><br /><br />';
			}
			////////////////////////////////
			
			////////////////////////////////
			$txt .= '<h1>Post Secondary</h1>
			<br />'; 
			
			$res = $db->Execute("select PK_ENROLLMENT_STATUS, PLACEMENT_STATUS, POST_SEC_INSTITUTION, PROGRAM_MAJOR, MILITARY_BRANCH, WAIVER_PHONE, WAIVER_CITY, WAIVER_NOTES from 
			S_STUDENT_WAIVER 
			LEFT JOIN M_PLACEMENT_STATUS ON M_PLACEMENT_STATUS.PK_PLACEMENT_STATUS = S_STUDENT_WAIVER.PK_PLACEMENT_STATUS 
			WHERE S_STUDENT_WAIVER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_WAIVER.PK_STUDENT_MASTER = '$_GET[id]' AND PK_WAIVER_TYPE = 1");
			
			if($res->RecordCount() == 0) {
				$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
							<tr>
								<td width="100%" ><b>No Post Secondary</b></td>
							</tr>
						</table>';
			} else {
				$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
							<tr>
								<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Enrollment</b></td>
								<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Placement Status</b></td>
								<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Post Secondary Institution</b></td>
								<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Program/Major</b></td>
								<td width="10%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Phone</b></td>
								
								<td width="10%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>City</b></td>
								
								<td width="20%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Notes</b></td>
							</tr>';
			
				$i = 1;
				while (!$res->EOF) {
					$PK_STUDENT_ENROLLMENT = $res->fields['PK_ENROLLMENT_STATUS'];
					$res_en = $db->Execute("SELECT PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IS_ACTIVE_ENROLLMENT FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE  S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ");
					
					$PHONE1 = preg_replace( '/[^0-9]/', '',$res->fields['WAIVER_PHONE']);
					$PHONE1 = '('.$PHONE1[0].$PHONE1[1].$PHONE1[2].') '.$PHONE1[3].$PHONE1[4].$PHONE1[5].'-'.$PHONE1[6].$PHONE1[7].$PHONE1[8].$PHONE1[9];
													
					$txt .= '<tr nobr="true">
								<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res_en->fields['BEGIN_DATE_1'].' - '.$res_en->fields['CODE'].' - '.$res_en->fields['STUDENT_STATUS'].'</td>
								<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['PLACEMENT_STATUS'].'</td>
								<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['POST_SEC_INSTITUTION'].'</td>
								<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['PROGRAM_MAJOR'].'</td>
								<td width="10%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$PHONE1.'</td>
								<td width="10%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['WAIVER_CITY'].'</td>
								<td width="20%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;" >'.nl2br($res->fields['WAIVER_NOTES']).'</td>
							</tr>';
				
					$res->MoveNext();
				}
				$txt .= '</table><br /><br />';
			}
			////////////////////////////////
			
			////////////////////////////////
			$txt .= '<h1>Military</h1>
			<br />'; 
			
			$res = $db->Execute("select PK_ENROLLMENT_STATUS, PLACEMENT_STATUS, POST_SEC_INSTITUTION, PROGRAM_MAJOR, MILITARY_BRANCH, WAIVER_PHONE, WAIVER_CITY, WAIVER_NOTES from 
			S_STUDENT_WAIVER 
			LEFT JOIN M_PLACEMENT_STATUS ON M_PLACEMENT_STATUS.PK_PLACEMENT_STATUS = S_STUDENT_WAIVER.PK_PLACEMENT_STATUS 
			WHERE S_STUDENT_WAIVER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_WAIVER.PK_STUDENT_MASTER = '$_GET[id]' AND PK_WAIVER_TYPE = 2");
			
			if($res->RecordCount() == 0) {
				$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
							<tr>
								<td width="100%" ><b>No Military</b></td>
							</tr>
						</table>';
			} else {
				$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
							<tr>
								<td width="20%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Enrollment</b></td>
								<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Placement Status</b></td>
								<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Military Branch</b></td>
								<td width="10%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Phone</b></td>
								
								<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>City</b></td>
								
								<td width="25%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Notes</b></td>
							</tr>';
			
				$i = 1;
				while (!$res->EOF) {
					$PK_STUDENT_ENROLLMENT = $res->fields['PK_ENROLLMENT_STATUS'];
					$res_en = $db->Execute("SELECT PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IS_ACTIVE_ENROLLMENT FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE  S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ");

					$PHONE1 = preg_replace( '/[^0-9]/', '',$res->fields['WAIVER_PHONE']);
					$PHONE1 = '('.$PHONE1[0].$PHONE1[1].$PHONE1[2].') '.$PHONE1[3].$PHONE1[4].$PHONE1[5].'-'.$PHONE1[6].$PHONE1[7].$PHONE1[8].$PHONE1[9];
					
					$txt .= '<tr nobr="true" >
								<td width="20%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res_en->fields['BEGIN_DATE_1'].' - '.$res_en->fields['CODE'].' - '.$res_en->fields['STUDENT_STATUS'].'</td>
								<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['PLACEMENT_STATUS'].'</td>
								<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['MILITARY_BRANCH'].'</td>
								<td width="10%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$PHONE1.'</td>
								<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['WAIVER_CITY'].'</td>
								<td width="25%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;" >'.nl2br($res->fields['WAIVER_NOTES']).'</td>
							</tr>';
				
					$res->MoveNext();
				}
				$txt .= '</table>';
			}
			////////////////////////////////
		}
		
	//echo $txt;exit;
$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

if($_GET['t'] == 1)
	$file_name = 'Lead Info.pdf';
else
	$file_name = 'Student Info.pdf';

/*if($browser == 'Safari')
	$pdf->Output('temp/'.$file_name, 'FD');
else	
	$pdf->Output($file_name, 'I');*/
	
$pdf->Output('temp/'.$file_name, 'FD');
return $file_name;	
		
return $file_name;	