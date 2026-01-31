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

require_once("pdf_custom_header.php"); //Ticket # 1588
class MYPDF extends TCPDF {
    public function Header() {
		global $db;
		
		/* Ticket # 1588 */
		if($_GET['id'] != ''){
			if($this->PageNo() > 0) {
				$CONTENT = pdf_custom_header($_GET['id'], $_GET['eid'], 1);
				$CONTENT = '<b>'.str_replace("<br /><br />", "<br />", $CONTENT).'</b>'; //DAIM-1161 
				$this->MultiCell(150, 20, $CONTENT, 0, 'L', 0, 0, '', '', true,'',true,true);
				$this->SetMargins('', 45, '');
			} else {
				$res = $db->Execute("SELECT FIRST_NAME, LAST_NAME FROM S_STUDENT_MASTER WHERE PK_STUDENT_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
				
				$STUD_NAME = $res->fields['LAST_NAME'].', '.$res->fields['FIRST_NAME'];
				$this->SetFont('helvetica', 'I', 15);
				$this->SetY(8);
				$this->SetX(10);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(75, 8, $STUD_NAME , 0, false, 'L', 0, '', 0, false, 'M', 'L');
				$this->SetMargins('', 25, '');
			}
			
		} else {
			$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
			
			if($res->fields['PDF_LOGO'] != '') {
				$ext = explode(".",$res->fields['PDF_LOGO']);
				$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
			}
			
			$this->SetFont('helvetica', '', 15);
			$this->SetY(8);
			$this->SetX(55);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
			
			$this->SetFont('helvetica', '', 8);
			$this->SetY(13);
			$this->SetX(55);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(55, 8,$res->fields['ADDRESS'].' '.$res->fields['ADDRESS_1'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
			
			$this->SetY(17);
			$this->SetX(55);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(55, 8,$res->fields['CITY'].', '.$res->fields['STATE_CODE'].' '.$res->fields['ZIP'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
			
			$this->SetY(21);
			$this->SetX(55);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(55, 8,$res->fields['PHONE'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
		}
		/* Ticket # 1588 */
		
		$this->SetFont('helvetica', 'I', 18);
		$this->SetY(15);
		$this->SetX(137);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(65, 8, "Financial Aid Estimate", 0, false, 'R', 0, '', 0, false, 'M', 'L'); //DIAM-1161

		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(130, 19, 202, 19, $style);
		
		$this->SetFont('helvetica', 'I', 15);
		$this->SetY(22);
		$this->SetX(137);
		$this->SetTextColor(000, 000, 000);
		
		if($_REQUEST['ay'] != '')
			$ay = "Academic Year: ".$_REQUEST['ay'];
		else
			$ay = "All Academic Years";
			
		//$this->Cell(65, 8, 'Notification', 0, false, 'R', 0, '', 0, false, 'M', 'L');
		
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
$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setLanguageArray($l);
$pdf->setFontSubsetting(true);
$pdf->SetFont('helvetica', '', 9, '', true);
//$pdf->AddPage();

$res = $db->Execute("SELECT S_STUDENT_MASTER.*,STUDENT_ID FROM S_STUDENT_MASTER LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = '$_GET[id]' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
if($res->RecordCount() == 0){
	header("location:manage_student?t=".$_GET['t']);
	exit;
}

$ay_cond = "";
if($_REQUEST['ay'] != '')
	$ay_cond = " AND ACADEMIC_YEAR = '$_REQUEST[ay]' ";
	
$ap_cond = "";
if ($_REQUEST['ap'] != '')
	$ap_cond = " AND ACADEMIC_PERIOD = '$_REQUEST[ap]' ";
	
if($_REQUEST['PLACEMENT_EN_FILTER'] != ""){
	$arr_of_enid = $_REQUEST['PLACEMENT_EN_FILTER'];
}
else{
	$arr_of_enid = $_REQUEST['eid'];
}
$arr_enid = explode(",",$arr_of_enid);	

$txt = '';
foreach ($arr_enid as $enroll_id) 
{
    $enroll_cond = "  AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ='$enroll_id' ";
    $enroll_cond1 = "  AND PK_STUDENT_ENROLLMENT ='$enroll_id' ";

    //$res_fin = $db->Execute("select ACADEMIC_YEAR, IF(ACADEMIC_YEAR_BEGIN = '0000-00-00','',DATE_FORMAT(ACADEMIC_YEAR_BEGIN, '%m/%d/%Y' )) AS ACADEMIC_YEAR_BEGIN, IF(ACADEMIC_YEAR_END = '0000-00-00','',DATE_FORMAT(ACADEMIC_YEAR_END, '%m/%d/%Y' )) AS ACADEMIC_YEAR_END, EFC_NO, IF(COA = '', 0, COA) AS COA FROM S_STUDENT_FINANCIAL WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_GET[id]' $ay_cond ");

    $res_fin = $db->Execute("SELECT ACADEMIC_YEAR,AWARD_YEAR, IF(ACADEMIC_YEAR_BEGIN = '0000-00-00','',DATE_FORMAT(ACADEMIC_YEAR_BEGIN, '%m/%d/%Y' )) AS ACADEMIC_YEAR_BEGIN, IF(ACADEMIC_YEAR_END = '0000-00-00','',DATE_FORMAT(ACADEMIC_YEAR_END, '%m/%d/%Y' )) AS ACADEMIC_YEAR_END, EFC_NO, IF(COA = '', 0, COA) AS COA,S_STUDENT_FINANCIAL.NEED FROM S_STUDENT_FINANCIAL LEFT JOIN M_AWARD_YEAR ON M_AWARD_YEAR.PK_AWARD_YEAR = S_STUDENT_FINANCIAL.PK_AWARD_YEAR WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND PK_STUDENT_MASTER = '$_GET[id]' $ay_cond ");

    $res_address = $db->Execute("SELECT ADDRESS,ADDRESS_1, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 	

    $res_enroll = $db->Execute("SELECT S_STUDENT_ENROLLMENT.*,CODE,STUDENT_STATUS,PK_STUDENT_STATUS_MASTER, LEAD_SOURCE, FUNDING,UNITS,HOURS,MONTHS,WEEKS,FA_UNITS,EXPECTED_GRAD_DATE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS TERM_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS EMP_NAME ,CAMPUS_CODE,M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM FROM S_STUDENT_ENROLLMENT LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING LEFT JOIN M_LEAD_SOURCE ON M_LEAD_SOURCE.PK_LEAD_SOURCE = S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$_GET[id]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  $enroll_cond ");

    $res_prog_fee = $db->Execute("select SUM(FEE_AMOUNT) as FEE_AMOUNT from S_STUDENT_FEE_BUDGET LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_FEE_BUDGET.PK_AR_LEDGER_CODE WHERE PK_STUDENT_MASTER = '$_GET[id]' $enroll_cond1  AND M_AR_LEDGER_CODE.TYPE = 2 AND NEED_ANALYSIS = 1 AND S_STUDENT_FEE_BUDGET.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $ay_cond $ap_cond");

    $EXPECTED_GRAD_DATE = $res_enroll->fields['EXPECTED_GRAD_DATE'];
    if($EXPECTED_GRAD_DATE != '0000-00-00' && $EXPECTED_GRAD_DATE != '')
        $EXPECTED_GRAD_DATE = date("m/d/Y",strtotime($EXPECTED_GRAD_DATE));
    else
        $EXPECTED_GRAD_DATE = '';

    if ($_REQUEST['ay'] != '') {
        //$ACADEMIC_YEAR = $res_fin->fields['ACADEMIC_YEAR'];
        $ACADEMIC_YEAR = $_REQUEST['ay'];
    }else{
        $ACADEMIC_YEAR = 'ALL';
    }

    if ($_REQUEST['ap'] != '') {
        $ACADEMIC_PERIODS = $_REQUEST['ap'];
    }else{
        $ACADEMIC_PERIODS = 'ALL';
    }

    $pdf->AddPage();
    // Remaining Need formula Cost of Attendance - EFC/SAI = Remaining Need
    $REMAINING_NEED = $res_prog_fee->fields['FEE_AMOUNT'] - $res_fin->fields['EFC_NO'];
    $txt = '';

    $txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
                <tr>
                    <td width="50%">
                        <table border="0" cellspacing="0" cellpadding="3" width="100%">
                            <tr>
                                <td width="100%"><b>'.$res->fields['FIRST_NAME'].' '.$res->fields['MIDDLE_NAME'].' '.$res->fields['LAST_NAME'].'</b></td>
                            </tr>
                            <tr>
                                <td width="100%"><b>'.$res_address->fields['ADDRESS'].' '.$res_address->fields['ADDRESS_1'].'</b></td>
                            </tr>
                            <tr>
                                <td width="100%"><b>'.$res_address->fields['CITY'].', '.$res_address->fields['STATE_CODE'].' '.$res_address->fields['ZIP'].'</b></td>
                            </tr>
                            <tr>
                                <td width="100%"><b>'.$res_address->fields['COUNTRY'].'</b></td>
                            </tr>
                        </table>';
                    
            $txt .= '</td>
                        <td width="50%">
                            <table border="0" cellspacing="0" cellpadding="3" width="100%">
                                <tr>
                                    <td width="35%"><b>Student ID</b></td>
                                    <td width="65%" align="left"><b>' . $res->fields['STUDENT_ID'] . '</b></td>
                                </tr>
                                <tr>
                                    <td width="35%"><b>Student Status</b></td>
                                    <td width="65%" align="left"><b>' . $res_enroll->fields['STUDENT_STATUS'] . '</b></td>
                                </tr>
                                <tr>
                                    <td width="35%"><b>Program</b></td>
                                    <td width="65%" align="left"><b>' . $res_enroll->fields['CODE'] . '</b></td>
                                </tr>
                                <tr>
                                    <td width="35%"><b>Program Hours</b></td>
                                    <td width="65%" align="left"><b>' .$res_enroll->fields['HOURS']. '</b></td>
                                </tr>
                                <tr>
                                    <td width="35%"><b>Program Units</b></td>
                                    <td width="65%" align="left"><b>' . $res_enroll->fields['UNITS'] . '</b></td>
                                </tr>
                                <tr>
                                    <td width="35%"><b>First Term</b></td>
                                    <td width="65%" align="left"><b>' . $res_enroll->fields['TERM_MASTER'] . '</b></td>
                                </tr>
                                <tr>
                                    <td width="35%"><b>Expected Grad</b></td>
                                    <td width="65%" align="left"><b>' . $EXPECTED_GRAD_DATE . '</b></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <br /><br />';
    
                  
            $txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
                    <tr>
                        <td width="100%" style="border-bottom:2px solid #a5a5a5;"></td>							
                    </tr>';

                $ACADEMIC_PERIOD_ARR = array();
                $ACADEMIC_PERIOD_TOTAL_ARR = array();
                $ACADEMIC_PERIOD_AID_TOTAL_ARR = array();
                $res_ap = $db->Execute("select ACADEMIC_PERIOD from S_STUDENT_DISBURSEMENT WHERE PK_STUDENT_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $enroll_cond1 $ay_cond $ap_cond GROUP BY ACADEMIC_PERIOD");
                while (!$res_ap->EOF) { 
                
                        $ACADEMIC_PERIOD_ARR[] = $res_ap->fields['ACADEMIC_PERIOD'];
                        
                    $res_ap->MoveNext();
                }
                sort($ACADEMIC_PERIOD_ARR);
                
                $width = 60 / count($ACADEMIC_PERIOD_ARR);
                $txt .= '<tr>
                            <td width="25%" ><b>Estimated Fees</b></td>';
                            foreach($ACADEMIC_PERIOD_ARR as $ACADEMIC_PERIOD)
                            {
                                $txt .= '<td width="'.$width.'%" style="text-align:right;" ><b>AY '.$ACADEMIC_PERIOD.'</b></td>'; //DIAM-2221
                            }
                            $txt .= '<td width="15%" style="text-align:right;" ><b>Total</b></td>
                        </tr>';
                
                foreach($ACADEMIC_PERIOD_ARR as $ACADEMIC_PERIOD) {
                    $ACADEMIC_PERIOD_TOTAL_ARR[$ACADEMIC_PERIOD] = 0;
                    $ACADEMIC_PERIOD_AID_TOTAL_ARR[$ACADEMIC_PERIOD] = 0;
                }

                // DIAM-1409  

                // Fees
                $res_prog_awarded = $db->Execute("SELECT 
                                                    CONCAT(M_AR_LEDGER_CODE.CODE, ' - ', M_AR_LEDGER_CODE.LEDGER_DESCRIPTION) AS LEDGER_DESC, 
                                                    M_AR_LEDGER_CODE.CODE AS LEDGER,
                                                    S_STUDENT_FEE_BUDGET.PK_AR_LEDGER_CODE 
                                                FROM 
                                                    S_STUDENT_FEE_BUDGET 
                                                    LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_FEE_BUDGET.PK_AR_LEDGER_CODE 
                                                WHERE 
                                                    PK_STUDENT_MASTER = '$_GET[id]' 
                                                    AND S_STUDENT_FEE_BUDGET.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $ay_cond $ap_cond $enroll_cond1 
                                                    AND TYPE = 2
                                                    AND PK_ESTIMATE_FEE_STATUS IN (1, 2, 3, 4) 
                                                GROUP BY 
                                                    S_STUDENT_FEE_BUDGET.PK_AR_LEDGER_CODE
                                                ORDER BY
                                                    M_AR_LEDGER_CODE.CODE ASC ");
                $LEDGER_CODE_AW_ARR = array(); 
                $PK_AR_LEDGER_CODE_AW_ARR = array(); 
                while (!$res_prog_awarded->EOF) { 
                    $LEDGER_CODE_AW_ARR[] 		 = $res_prog_awarded->fields['LEDGER'];
                    $PK_AR_LEDGER_CODE_AW_ARR[] = $res_prog_awarded->fields['PK_AR_LEDGER_CODE'];
                    
                    $res_prog_awarded->MoveNext();
                }
            
                $i = 0;
                $grand_right_total_aw = 0;
                foreach($PK_AR_LEDGER_CODE_AW_ARR as $PK_AR_LEDGER_CODE) {
                    $txt .= '<tr>
                                <td width="25%" >'.$LEDGER_CODE_AW_ARR[$i].'</td>';
                    
                    $right_tot_aw = 0;
                    foreach($ACADEMIC_PERIOD_ARR as $ACADEMIC_PERIOD) {
                        //Added enroll condition
                        $res_prog_fee = $db->Execute("select SUM(FEE_AMOUNT) AS FEE_AMOUNT from S_STUDENT_FEE_BUDGET WHERE PK_STUDENT_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $ay_cond $ap_cond $enroll_cond1 AND PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' AND ACADEMIC_PERIOD = '$ACADEMIC_PERIOD' AND PK_ESTIMATE_FEE_STATUS IN (1,2,3,4) ");
                        
                        $ACADEMIC_PERIOD_TOTAL_ARR[$ACADEMIC_PERIOD] += $res_prog_fee->fields['FEE_AMOUNT'];
                        $right_tot_aw								 += $res_prog_fee->fields['FEE_AMOUNT'];
                        
                        $txt .= '<td width="'.$width.'%" style="text-align:right;" >$'.number_format_value_checker($res_prog_fee->fields['FEE_AMOUNT'],2).'</td>';
                    }		
                    $txt .= '	<td width="15%" style="text-align:right;" >$'.number_format_value_checker($right_tot_aw,2).' </td>
                            </tr>';
                            
                    $grand_right_total_aw += $right_tot_aw;
                    $i++;
                }
                $txt .= '<tr>
                        <td width="25%" style="border-top:0.5px solid #000;"><b>Estimated Fees Total</b></td>';
                        foreach($ACADEMIC_PERIOD_ARR as $ACADEMIC_PERIOD) {
                            $txt .= '<td width="'.$width.'%" style="border-top:0.5px solid #000;text-align:right;" ><b>$'.number_format_value_checker($ACADEMIC_PERIOD_TOTAL_ARR[$ACADEMIC_PERIOD],2).'</b></td>';
                        }
                        
                $txt .= '<td width="15%" style="border-top:0.5px solid #000;text-align:right;" ><b>$'.number_format_value_checker($grand_right_total_aw,2).'</b></td>
                    </tr>
                </table>
                <br /><br />';


                 //Award
                $txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
                <tr>
                    <td width="100%" style="border-bottom:2px solid #a5a5a5;"></td>							
                </tr>';

                $txt .= '<tr>
                            <td width="25%" ><b>Awards</b></td>';
                            foreach($ACADEMIC_PERIOD_ARR as $ACADEMIC_PERIOD)
                            {
                                $txt .= '<td width="'.$width.'%" style="text-align:right;" ><b>AY '.$ACADEMIC_PERIOD.'</b></td>'; //DIAM-2221
                            }
                            $txt .= '<td width="15%" style="text-align:right;" ><b>Total</b></td>
                        </tr>';

                $res_prog_fee = $db->Execute("SELECT 
                                                    CONCAT(M_AR_LEDGER_CODE.CODE, ' - ', M_AR_LEDGER_CODE.LEDGER_DESCRIPTION) AS LEDGER_DESC, 
                                                    M_AR_LEDGER_CODE.CODE AS LEDGER,
                                                    S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE 
                                                FROM 
                                                    S_STUDENT_DISBURSEMENT 
                                                    LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE 
                                                WHERE 
                                                    PK_STUDENT_MASTER = '$_GET[id]' 
                                                    AND S_STUDENT_DISBURSEMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $ay_cond $ap_cond $enroll_cond1 
                                                    AND TYPE = 1
                                                    AND PK_DISBURSEMENT_STATUS IN (1, 2, 3, 4) 
                                                GROUP BY 
                                                    S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE 
                                                ORDER BY
                                                    M_AR_LEDGER_CODE.CODE ASC");
                $LEDGER_CODE_ARR = array(); 
                $PK_AR_LEDGER_CODE_ARR = array(); 
                while (!$res_prog_fee->EOF) { 
                    $LEDGER_CODE_ARR[] 		 = $res_prog_fee->fields['LEDGER'];
                    $PK_AR_LEDGER_CODE_ARR[] = $res_prog_fee->fields['PK_AR_LEDGER_CODE'];
                    
                    $res_prog_fee->MoveNext();
                }
            
                $j = 0;
                $grand_right_total = 0;
                foreach($PK_AR_LEDGER_CODE_ARR as $PK_AR_LEDGER_CODE) {
                    $txt .= '<tr>
                                <td width="25%" >'.$LEDGER_CODE_ARR[$j].'</td>';
                    
                    $right_tot = 0;
                    foreach($ACADEMIC_PERIOD_ARR as $ACADEMIC_PERIOD) {
                        //Added enroll condition
                        $res_prog_fee = $db->Execute("select SUM(DISBURSEMENT_AMOUNT) AS DISBURSEMENT_AMOUNT from S_STUDENT_DISBURSEMENT WHERE PK_STUDENT_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $ay_cond $ap_cond $enroll_cond1 AND PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' AND ACADEMIC_PERIOD = '$ACADEMIC_PERIOD' AND PK_DISBURSEMENT_STATUS IN (1,2,3,4) ");
                        
                        $ACADEMIC_PERIOD_AID_TOTAL_ARR[$ACADEMIC_PERIOD] += $res_prog_fee->fields['DISBURSEMENT_AMOUNT'];
                        $right_tot 									     += $res_prog_fee->fields['DISBURSEMENT_AMOUNT'];
                        
                        $txt .= '<td width="'.$width.'%" style="text-align:right;" >$'.number_format_value_checker($res_prog_fee->fields['DISBURSEMENT_AMOUNT'],2).'</td>';
                    }		
                    $txt .= '	<td width="15%" style="text-align:right;" >$'.number_format_value_checker($right_tot,2).'</td>
                        </tr>';
                            
                    $grand_right_total += $right_tot;
                    $j++;
                }
                $txt .= '<tr>
                        <td width="25%" style="border-top:0.5px solid #000;"><b>Awards Total</b></td>';
                        foreach($ACADEMIC_PERIOD_ARR as $ACADEMIC_PERIOD) {
                            $txt .= '<td width="'.$width.'%" style="border-top:0.5px solid #000;text-align:right;" ><b>$'.number_format_value_checker($ACADEMIC_PERIOD_AID_TOTAL_ARR[$ACADEMIC_PERIOD],2).'</b></td>';
                        }
                        
                $txt .= '<td width="15%" style="border-top:0.5px solid #000;text-align:right;" ><b>$'.number_format_value_checker($grand_right_total,2).'</b></td>
                    </tr>
                    <br><br>
                    </table>
                    <br /><br />
                    <table border="0" cellspacing="0" cellpadding="3" width="100%">
                    <tr>
                        <td width="100%" style="border-bottom:2px solid #a5a5a5;"></td>							
                    </tr>
                    <tr>
                          <td width="25%"><b>Fees - Awards</b></td>';
                            foreach($ACADEMIC_PERIOD_ARR as $ACADEMIC_PERIOD) {
                                $final_ay_ap = $ACADEMIC_PERIOD_TOTAL_ARR[$ACADEMIC_PERIOD] - $ACADEMIC_PERIOD_AID_TOTAL_ARR[$ACADEMIC_PERIOD];
                                $txt .= '<td width="'.$width.'%" style="text-align:right;" ><b>$'.number_format_value_checker($final_ay_ap,2).'</b></td>';
                            }
                            $final_total = $grand_right_total_aw - $grand_right_total;
                            $txt .= '<td width="15%" style="text-align:right;" ><b>$'.number_format_value_checker($final_total,2).'</b></td>
                    </tr>
                    </table>
                    <br /><br />';
                // DIAM-1409
            
            
        $res_acc = $db->Execute("SELECT * FROM S_PDF_FOOTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE=1 AND PDF_FOR = 18 ");
        //$txt .= nl2br(str_replace(" ","&nbsp;",$res->fields['CONTENT']));
        $txt .= '<table><tr><td style="white-space: pre-wrap;">'.nl2br($res_acc->fields['CONTENT']).'</td></tr></table>';
        

        // echo $txt;exit;
    $pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

} // END FOR

$file_name = 'Financial_Aid_Estimate.pdf';
if($browser == 'Safari')
	$pdf->Output('temp/'.$file_name, 'FD');
else	
	$pdf->Output($file_name, 'I');
	
//$pdf->Output('temp/'.$file_name, 'FD');
return $file_name;	
