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
require_once("get_department_from_t.php");

require_once("check_access.php");
if(check_access('ADMISSION_ACCESS') == 0 && check_access('REGISTRAR_ACCESS') == 0){
	header("location:../index");
	exit;
}

require_once("pdf_custom_header.php"); //Ticket # 1588
class MYPDF extends TCPDF {
    public function Header() {
		global $db;
		
		if($_SESSION['temp_id'] != $this->PK_STUDENT_MASTER){
			$_SESSION['temp_id'] = $this->PK_STUDENT_MASTER;
			
			/* Ticket # 1588 */
			$CONTENT = pdf_custom_header($this->PK_STUDENT_MASTER, $this->PK_STUDENT_ENROLLMENT, 1);
			$this->MultiCell(150, 20, $CONTENT, 0, 'L', 0, 0, '', '', true,'',true,true);
			$this->SetMargins('', 45, '');
			/* Ticket # 1588 */
			
		} else {
			$this->SetFont('helvetica', 'I', 15);
			$this->SetY(8);
			$this->SetX(10);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(75, 8, $this->STUD_NAME , 0, false, 'L', 0, '', 0, false, 'M', 'L');
			$this->SetMargins('', 15, '');
		}

		$this->SetFont('helvetica', 'I', 20);
		$this->SetY(8);
		$this->SetTextColor(000, 000, 000);
		$this->SetX(160);
		$this->Cell(55, 8, 'Requirements', 0, false, 'L', 0, '', 0, false, 'M', 'L');

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
		$this->Cell(30, 10, 'Page '.$this->getPageNumGroupAlias().' of '.$this->getPageGroupAlias(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		
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

$_SESSION['temp_id'] = '';
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
$pdf->SetFont('helvetica', '', 8, '', true);

$PK_STUDENT_ENROLLMENT_ARR = explode(",",$_GET['eid']);
foreach($PK_STUDENT_ENROLLMENT_ARR as $PK_STUDENT_ENROLLMENT){
	
	$res = $db->Execute("SELECT  S_STUDENT_MASTER.* FROM S_STUDENT_MASTER,S_STUDENT_ENROLLMENT WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER"); 
	$PK_STUDENT_MASTER 	= $res->fields['PK_STUDENT_MASTER'];
	$SSN	 			= $res->fields['SSN'];
	$SSN_VERIFIED		= $res->fields['SSN_VERIFIED'];
	$DATE_OF_BIRTH	 	= $res->fields['DATE_OF_BIRTH'];
	$pdf->STUD_NAME 	= $res->fields['LAST_NAME'].", ".$res->fields['FIRST_NAME'];
	
	if($DATE_OF_BIRTH != '0000-00-00')
		$DATE_OF_BIRTH = date("m/d/Y",strtotime($DATE_OF_BIRTH));
	else
		$DATE_OF_BIRTH = '';

	if($SSN != '') {
		$SSN 	 = my_decrypt($_SESSION['PK_ACCOUNT'].$PK_STUDENT_MASTER,$SSN);
		$SSN_ORG = $SSN;
		$SSN_ARR = explode("-",$SSN);
		$SSN 	 = 'xxx-xx-'.$SSN_ARR[2];
	}

	if($SSN_VERIFIED == 1)
		$SSN_VERIFIED = 'Verified';
	else
		$SSN_VERIFIED = '';
		
	$res_address = $db->Execute("SELECT ADDRESS,ADDRESS_1, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 	

	$res_enroll = $db->Execute("SELECT S_STUDENT_ENROLLMENT.*,PROGRAM_TRANSCRIPT_CODE,STUDENT_STATUS,PK_STUDENT_STATUS_MASTER, LEAD_SOURCE, FUNDING, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS TERM_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS EMP_NAME FROM S_STUDENT_ENROLLMENT LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING LEFT JOIN M_LEAD_SOURCE ON M_LEAD_SOURCE.PK_LEAD_SOURCE = S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS WHERE S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' "); 
	
	$pdf->PK_STUDENT_MASTER 	= $PK_STUDENT_MASTER;
	$pdf->PK_STUDENT_ENROLLMENT = $PK_STUDENT_ENROLLMENT;
	$pdf->startPageGroup();
	$pdf->AddPage();

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
								<td width="40%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Notes</b></td>
								<td width="60%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;border-right:0.5px solid #000;" ></td>
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
								<td width="55%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;" >'.$res_enroll->fields['PROGRAM_TRANSCRIPT_CODE'].'</td>
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
			<h1>'.$title.' Documents</h1>
			<br />';
			
			$doc_dep = get_department_from_t($_GET['t']);
			$res = $db->Execute("select S_STUDENT_DOCUMENTS.PK_STUDENT_DOCUMENTS,PROGRAM_TRANSCRIPT_CODE,IF(BEGIN_DATE = '0000-00-00','', DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, DOCUMENT_TYPE, S_STUDENT_DOCUMENTS.NOTES, IF(REQUESTED_DATE = '0000-00-00', '', DATE_FORMAT(REQUESTED_DATE,'%m/%d/%Y')) AS REQUESTED_DATE_1, IF(RECEIVED = 1,'Yes', 'No') as RECEIVED, IF(DATE_RECEIVED = '0000-00-00', '',  DATE_FORMAT(DATE_RECEIVED,'%m/%d/%Y')) AS DATE_RECEIVED, IF(FOLLOWUP_DATE = '0000-00-00', '',  DATE_FORMAT(FOLLOWUP_DATE,'%m/%d/%Y')) AS FOLLOWUP_DATE, CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME FROM S_STUDENT_DOCUMENTS LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_DOCUMENTS.PK_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_DOCUMENTS.PK_EMPLOYEE_MASTER , S_STUDENT_DOCUMENTS_DEPARTMENT WHERE S_STUDENT_DOCUMENTS.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_DOCUMENTS.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_DOCUMENTS.PK_STUDENT_DOCUMENTS = S_STUDENT_DOCUMENTS_DEPARTMENT.PK_STUDENT_DOCUMENTS AND PK_DEPARTMENT = '$doc_dep' ORDER BY REQUESTED_DATE ASC ");

			if($res->RecordCount() == 0) {
				$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
							<tr>
								<td width="100%" ><b>No Documents</b></td>
							</tr>
						</table>';
			} else {
				$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
							<thead>
								<tr>
									<td width="12.5%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Enrollment</b></td>
									<td width="12.5%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Department</b></td>
									<td width="12.5%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Requested</b></td>
									<td width="12.5%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Document</b></td>
									
									<td width="12.5%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Employee</b></td>
									<td width="12.5%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Follow Up Date</b></td>
									<td width="12.5%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Received</b></td>
									
									<td width="12.5%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Date Received</b></td>
								</tr>
							</thead>';
			
				$i = 1;
				while (!$res->EOF) {
					$PK_STUDENT_DOCUMENTS 	= $res->fields['PK_STUDENT_DOCUMENTS']; 
					$DEPARTMENT_NAME		= '';
					$res_dep = $db->Execute("SELECT DEPARTMENT FROM S_STUDENT_DOCUMENTS_DEPARTMENT, M_DEPARTMENT WHERE M_DEPARTMENT.PK_DEPARTMENT = S_STUDENT_DOCUMENTS_DEPARTMENT.PK_DEPARTMENT AND PK_STUDENT_DOCUMENTS = '$PK_STUDENT_DOCUMENTS'  ORDER BY DEPARTMENT ASC "); 
					while (!$res_dep->EOF) { 
						if($DEPARTMENT_NAME != '')
							$DEPARTMENT_NAME .= ', ';
							
						$DEPARTMENT_NAME .= $res_dep->fields['DEPARTMENT'];
						$res_dep->MoveNext();
					}
					$txt .= '<tr>
							<td width="12.5%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['PROGRAM_TRANSCRIPT_CODE'].' - '.$res->fields['BEGIN_DATE_1'].'</td>
							<td width="12.5%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$DEPARTMENT_NAME.'</td>
							<td width="12.5%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['REQUESTED_DATE_1'].'</td>
							<td width="12.5%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['DOCUMENT_TYPE'].'</td>
							<td width="12.5%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['NAME'].'</td>
							<td width="12.5%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['FOLLOWUP_DATE'].'</td>
							<td width="12.5%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['RECEIVED'].'</td>
							
							<td width="12.5%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['DATE_RECEIVED'].' '.$FOLLOWUP_TIME.'</td>
						</tr>';
					
					$i++;
					$res->MoveNext();
				}
				$txt .= '</table>';
			}
			
			$txt .= '<h1>'.$title.' Enrollment Requirements</h1>
			<br />'; 
			
			$res = $db->Execute("SELECT PK_STUDENT_REQUIREMENT, MANDATORY AS MANDATORY_1, REQUIREMENT,IF(MANDATORY = 1,'Yes','No') as MANDATORY, IF(COMPLETED = 1,'Yes','No') as  COMPLETED, COMPLETED_ON, IF(TYPE = 1,'School', IF(TYPE = 2,'Program','') ) as TYPE, IF(COMPLETED_ON = '0000-00-00', '', DATE_FORMAT(COMPLETED_ON,'%m/%d/%Y')) AS COMPLETED_ON FROM S_STUDENT_REQUIREMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ORDER BY REQUIREMENT ASC");
			if($res->RecordCount() == 0) {
				$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
							<tr>
								<td width="100%" ><b>No Enrollment Requirements</b></td>
							</tr>
						</table>';
			} else {
				$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
							<thead>
								<tr>
									<td width="45%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Requirement</b></td>
									<td width="20%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Type</b></td>
									<td width="10%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Mandatory</b></td>
									<td width="10%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Completed</b></td>
									<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;" ><b>Completed On</b></td>
								</tr>
							</thead>';
			
				$i = 1;
				while (!$res->EOF) {
					$txt .= '<tr>
								<td width="45%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['REQUIREMENT'].'</td>
								<td width="20%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['TYPE'].'</td>
								<td width="10%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['MANDATORY'].'</td>
								<td width="10%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['COMPLETED'].'</td>
								<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;" >'.$res->fields['COMPLETED_ON'].'</td>
							</tr>';
					$res->MoveNext();
				}
				$txt .= '</table>';
			}
			
			////////////////////////////////
			
		//echo $txt;exit;
	$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
}

$file_name = 'Requirements_'.uniqid().'.pdf';
/*
if($browser == 'Safari')
	$pdf->Output('temp/'.$file_name, 'FD');
else	
	$pdf->Output($file_name, 'I');
*/	
$pdf->Output('temp/'.$file_name, 'FD');
return $file_name;	