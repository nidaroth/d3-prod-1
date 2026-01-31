<?php require_once("../global/config.php"); 
require_once('../global/tcpdf/config/lang/eng.php');
require_once('../global/tcpdf/tcpdf.php');
class MYPDF extends TCPDF {
	public $SITENAME,$BUSINESS_LOGO, $POLE_ID,$PK_CLIENT_MASTER,$SITE_ID,$INSP_TYPE;
	public function set_data($sitename,$logo,$pole_id,$client_mast,$site_id,$insp_type) {
		$this->SITENAME 		= $sitename;
		$this->BUSINESS_LOGO 	= $logo;
		$this->POLE_ID 			= $pole_id;
		$this->PK_CLIENT_MASTER = $client_mast;
		$this->SITE_ID 			= $site_id;
		$this->INSP_TYPE 		= $insp_type;
	}
	
	public function Header() {
		global $db;
		
		/*$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		if($res->fields['PDF_LOGO'] != '') {
			$ext = explode(".",$res->fields['PDF_LOGO']);
			$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
		}
		
		$this->SetFont('helvetica', '', 15);
		$this->SetY(8);
		$this->SetX(55);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');*/

    }
	public function Footer() {
		global $db;
		
		$timezone = $_SESSION['PK_TIMEZONE'];
		if($timezone == '' || $timezone == 0) {
			$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$timezone = $res->fields['PK_TIMEZONE'];
			if($timezone == '' || $timezone == 0)
				$timezone = 4;
		}
		
		$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
		$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$res->fields['TIMEZONE'],date_default_timezone_get());
		
		$res = $db->Execute("SELECT PRINT_ORIENTATION FROM S_PDF_TEMPLATE WHERE PK_PDF_TEMPLATE = '$PK_PDF_TEMPLATE' AND S_PDF_TEMPLATE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		if($res->fields['PRINT_ORIENTATION'] == '' || $res->fields['PRINT_ORIENTATION'] == 'P'){
			$this->SetY(-15);
			$this->SetX(180);
			$this->SetFont('helvetica', 'I', 7);
			$this->Cell(30, 10, 'Page '.$this->getAliasNumPage().' of '.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
			
			$this->SetY(-15);
			$this->SetX(10);
			$this->SetFont('helvetica', 'I', 7);

			$this->Cell(30, 10, $date, 0, false, 'C', 0, '', 0, false, 'T', 'M');
		} else {
			$this->SetY(-15);
			$this->SetX(270);
			$this->SetFont('helvetica', 'I', 7);
			$this->Cell(30, 10, 'Page '.$this->getAliasNumPage().' of '.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
			
			$this->SetY(-15);
			$this->SetX(10);
			$this->SetFont('helvetica', 'I', 7);
			
			$this->Cell(30, 10, $date, 0, false, 'C', 0, '', 0, false, 'T', 'M');
		}
    }
}
	
function generate_pdf($PK_PDF_TEMPLATE,$PK_STUDENT_MASTER,$PK_STUDENT_ENROLLMENT,$save){
	global $db;
	
	$DB_FIELD = "";
	$res = $db->Execute("SELECT DB_FIELD, DB_FIELD_1 FROM Z_DOCUMENT_TEMPLATE_TAG WHERE ACTIVE = 1 AND PK_DOCUMENT_TEMPLATE_SUB_CATEGORY NOT IN (5) AND trim(DB_FIELD) != '' ");
	while (!$res->EOF) {
		if($DB_FIELD != '')
			$DB_FIELD  .= ', ';
			
		$DB_FIELD  .= $res->fields['DB_FIELD'];
		
		$res->MoveNext();
	}
	
	$SERVCER_DB_FIELD = "";
	$res = $db->Execute("SELECT DB_FIELD, DB_FIELD_1 FROM Z_DOCUMENT_TEMPLATE_TAG WHERE ACTIVE = 1 AND PK_DOCUMENT_TEMPLATE_SUB_CATEGORY = 5 AND trim(DB_FIELD) != '' ");
	while (!$res->EOF) {
		if($SERVCER_DB_FIELD != '')
			$SERVCER_DB_FIELD  .= ', ';
			
		$SERVCER_DB_FIELD  .= $res->fields['DB_FIELD'];
		
		$res->MoveNext();
	}
	
	$res = $db->Execute("SELECT CONTENT,PRINT_ORIENTATION FROM S_PDF_TEMPLATE WHERE PK_PDF_TEMPLATE = '$PK_PDF_TEMPLATE' AND S_PDF_TEMPLATE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	$ORG_TEXT = $res->fields['CONTENT'];
	$ORG_TEXT = str_replace("<p>","<br />",$ORG_TEXT);
	$ORG_TEXT = str_replace("</p>","",$ORG_TEXT);
	
	if($res->fields['PRINT_ORIENTATION'] == '' || $res->fields['PRINT_ORIENTATION'] == 'P'){
		$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(7, 10, 7);
		$pdf->SetAutoPageBreak(TRUE, 30);
	} else {
		$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(7, 10, 7);
		$pdf->SetAutoPageBreak(TRUE, 20);
	}
	$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFont('helvetica', '', 8, '', true);
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	$pdf->setLanguageArray($l);
	$pdf->setFontSubsetting(true);
	
	$pdf->AddPage();
	$txt = $ORG_TEXT;
	
	/* Ticket # 1762  */
	$res_stu = $db->Execute("SELECT $DB_FIELD FROM 
	Z_ACCOUNT 
	LEFT JOIN Z_STATES AS SCHOOL_STATE ON SCHOOL_STATE.PK_STATES = Z_ACCOUNT.PK_STATES 
	LEFT JOIN Z_COUNTRY AS SCHOOL_COUNTRY ON SCHOOL_COUNTRY.PK_COUNTRY = Z_ACCOUNT.PK_COUNTRY 
	, S_STUDENT_MASTER 
	LEFT JOIN S_STUDENT_CONTACT ON S_STUDENT_CONTACT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND PK_STUDENT_CONTACT_TYPE_MASTER = '1'
	LEFT JOIN Z_STATES AS STUDENT_STATE ON STUDENT_STATE.PK_STATES = S_STUDENT_CONTACT.PK_STATES 
	LEFT JOIN Z_COUNTRY AS STUDENT_COUNTRY ON STUDENT_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY 
	LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
	LEFT JOIN M_HIGHEST_LEVEL_OF_EDU ON M_HIGHEST_LEVEL_OF_EDU.PK_HIGHEST_LEVEL_OF_EDU = S_STUDENT_ACADEMICS.PK_HIGHEST_LEVEL_OF_EDU 
	LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
	LEFT JOIN M_LEAD_CONTACT_SOURCE ON M_LEAD_CONTACT_SOURCE.PK_LEAD_CONTACT_SOURCE = S_STUDENT_ENROLLMENT.PK_LEAD_CONTACT_SOURCE 
	LEFT JOIN Z_SPECIAL ON Z_SPECIAL.PK_SPECIAL = S_STUDENT_ENROLLMENT.PK_SPECIAL
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER
	LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
	LEFT JOIN M_FIRST_TERM ON M_FIRST_TERM.PK_FIRST_TERM = S_STUDENT_ENROLLMENT.FIRST_TERM 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM
	LEFT JOIN M_DROP_REASON ON M_DROP_REASON.PK_DROP_REASON = S_STUDENT_ENROLLMENT.PK_DROP_REASON
	LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE 
	LEFT JOIN M_LEAD_SOURCE ON M_LEAD_SOURCE.PK_LEAD_SOURCE = S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE  
	LEFT JOIN M_ENROLLMENT_STATUS ON M_ENROLLMENT_STATUS.PK_ENROLLMENT_STATUS = S_STUDENT_ENROLLMENT.PK_ENROLLMENT_STATUS 
	LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_STUDENT_ENROLLMENT.PK_SESSION 
	LEFT JOIN M_DISTANCE_LEARNING ON M_DISTANCE_LEARNING.PK_DISTANCE_LEARNING = S_STUDENT_ENROLLMENT.PK_DISTANCE_LEARNING 
	LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING 
	LEFT JOIN M_PLACEMENT_STATUS ON M_PLACEMENT_STATUS.PK_PLACEMENT_STATUS = S_STUDENT_ENROLLMENT.PK_PLACEMENT_STATUS 
	LEFT JOIN M_STUDENT_GROUP ON M_STUDENT_GROUP.PK_STUDENT_GROUP = S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP  
	LEFT JOIN M_SPECIAL_PROGRAM_INDICATOR ON M_SPECIAL_PROGRAM_INDICATOR.PK_SPECIAL_PROGRAM_INDICATOR = S_STUDENT_ENROLLMENT.PK_SPECIAL_PROGRAM_INDICATOR  
	LEFT JOIN Z_STATES AS Z_STATES_DRIVERS_LICENSE ON Z_STATES_DRIVERS_LICENSE.PK_STATES = S_STUDENT_MASTER.PK_DRIVERS_LICENSE_STATE 
	LEFT JOIN Z_MARITAL_STATUS AS Z_MARITAL_STATUS_STUD ON Z_MARITAL_STATUS_STUD.PK_MARITAL_STATUS = S_STUDENT_MASTER.PK_MARITAL_STATUS 
	LEFT JOIN Z_COUNTRY AS Z_COUNTRY_CITIZEN ON Z_COUNTRY_CITIZEN.PK_COUNTRY = S_STUDENT_MASTER.PK_COUNTRY_CITIZEN 
	LEFT JOIN Z_CITIZENSHIP ON Z_CITIZENSHIP.PK_CITIZENSHIP = S_STUDENT_MASTER.PK_CITIZENSHIP 
	LEFT JOIN Z_STATES AS Z_STATES_OF_RESIDENCY ON Z_STATES_OF_RESIDENCY.PK_STATES = S_STUDENT_MASTER.PK_STATE_OF_RESIDENCY 
	WHERE 
	S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	Z_ACCOUNT.PK_ACCOUNT = S_STUDENT_MASTER.PK_ACCOUNT AND 
	S_STUDENT_MASTER.ACTIVE = 1 AND S_STUDENT_MASTER.ARCHIVED = 0 
	AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ");
	/* Ticket # 1762  */
	
	$res = $db->Execute("SELECT TAGS, DB_FIELD, DB_FIELD_1 FROM Z_DOCUMENT_TEMPLATE_TAG WHERE ACTIVE = 1 AND PK_DOCUMENT_TEMPLATE_SUB_CATEGORY NOT IN (5) ");
	while (!$res->EOF) {
		$field = $res->fields['DB_FIELD_1'];
		
		if($field == 'SCHOOL_LOGO' || $field == 'SCHOOL_PDF_LOGO' || $field == 'STUD_IMG'){
			$IMG = "";
			if($res_stu->fields[$field] != '') {
				$IMG = '<img src="'.$res_stu->fields[$field].'" style="width:120px" >';
				//echo $IMG.'<br />';
				$txt = str_replace($res->fields['TAGS'],$IMG,$txt);	
			}
		} else {
			if($field == 'SSN') {
				$SSN_1 	 		= my_decrypt($_SESSION['PK_ACCOUNT'],$res_stu->fields[$field]);
				$SSN_ARR 		= explode("-",$SSN_1);
				$SSNX 	 		= 'xxx-xx-'.$SSN_ARR[2];
				$FORMATTED_SSN  = $SSN_1;
				$SSN			= preg_replace( '/[^0-9]/', '',$SSN_1);
				
				$txt = str_replace($res->fields['TAGS'],$SSNX,$txt);	
			} else 
				$txt = str_replace($res->fields['TAGS'],$res_stu->fields[$field],$txt);	
		}
		
		$res->MoveNext();
	}
	
	$res_servicer = $db->Execute("SELECT $SERVCER_DB_FIELD FROM M_SERVICER LEFT JOIN Z_STATES AS SERVICER_STATE ON SERVICER_STATE.PK_STATES = M_SERVICER.PK_STATES , S_STUDENT_APPROVED_AWARD_SUMMARY WHERE M_SERVICER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_APPROVED_AWARD_SUMMARY.PK_SERVICER = M_SERVICER.PK_SERVICER AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_APPROVED_AWARD_SUMMARY.PK_SERVICER > 0");
	$res = $db->Execute("SELECT TAGS, DB_FIELD, DB_FIELD_1 FROM Z_DOCUMENT_TEMPLATE_TAG WHERE ACTIVE = 1 AND PK_DOCUMENT_TEMPLATE_SUB_CATEGORY = 5 ");
	while (!$res->EOF) {
		$field = $res->fields['DB_FIELD_1'];
		
		$txt = str_replace($res->fields['TAGS'],$res_servicer->fields[$field],$txt);	
		
		$res->MoveNext();
	}

	$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
	
	$stud_name = $res_stu->fields['STUD_LAST_NAME'].' '.$res_stu->fields['STUD_FIRST_NAME'];
	$stud_name = str_replace("/","_",$stud_name);
	$stud_name = str_replace(":","_",$stud_name);
	$stud_name = str_replace("?","_",$stud_name);
	$stud_name = str_replace("*","_",$stud_name);
	$stud_name = str_replace("<","_",$stud_name);
	$stud_name = str_replace(">","_",$stud_name);
	$stud_name = str_replace("|","_",$stud_name);
	
	$file_name = $stud_name.'.pdf';
	
	if($save == '1')
		$pdf->Output("temp/".$file_name, 'F');
	else if($browser == 'Safari')
		$pdf->Output("temp/".$file_name, 'FD');
	else	
		$pdf->Output($file_name, 'I');

	$filename_array = "temp/".$file_name;		
		
	return $filename_array;
}