<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/title_iv.php");
require_once("check_access.php");

if (check_access('REPORT_FINANCE') == 0) {
	header("location:../index");
	exit;
}

if (!empty($_POST)) {
	//header("location:projected_funds_pdf?st=".$_POST['START_DATE'].'&et='.$_POST['END_DATE'].'&dt='.$_POST['DATE_TYPE'].'&e='.$_POST['PK_EMPLOYEE_MASTER'].'&tc='.$_POST['TASK_COMPLETED']);

	$cond = "";
	if ($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '') {
		$ST = date("Y-m-d", strtotime($_POST['START_DATE']));
		$ET = date("Y-m-d", strtotime($_POST['END_DATE']));
		$cond .= " AND TRANSACTION_DATE BETWEEN '$ST' AND '$ET' ";
	} else if ($_POST['START_DATE'] != '') {
		$ST = date("Y-m-d", strtotime($_POST['START_DATE']));
		$cond .= " AND TRANSACTION_DATE >= '$ST' ";
	} else if ($_POST['END_DATE'] != '') {
		$ET = date("Y-m-d", strtotime($_POST['END_DATE']));
		$cond .= " AND TRANSACTION_DATE <= '$ET' ";
	}

	/* Ticket # 1318  */
	$campus_name  = "";
	$campus_cond  = "";
	$campus_cond1 = "";
	$campus_id	  = "";
	if (!empty($_POST['PK_CAMPUS'])) {
		$PK_CAMPUS 	  = implode(",", $_POST['PK_CAMPUS']);
		$campus_cond  = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
		$campus_cond1 = " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
	}

	$res_campus = $db->Execute("select PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $campus_cond order by CAMPUS_CODE ASC");
	while (!$res_campus->EOF) {
		if ($campus_name != '')
			$campus_name .= ', ';
		$campus_name .= $res_campus->fields['CAMPUS_CODE'];

		if ($campus_id != '')
			$campus_id .= ',';
		$campus_id .= $res_campus->fields['PK_CAMPUS'];

		$res_campus->MoveNext();
	}
	/* Ticket # 1318  */

	//echo $cond;exit;
	$query = "select S_STUDENT_LEDGER.PK_STUDENT_ENROLLMENT    
	from 
	S_STUDENT_MASTER, S_STUDENT_ENROLLMENT, S_STUDENT_LEDGER, M_AR_LEDGER_CODE, S_STUDENT_CAMPUS  
	WHERE 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
	S_STUDENT_LEDGER.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND 
	S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND 
	S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_LEDGER.PK_AR_LEDGER_CODE AND M_AR_LEDGER_CODE.TYPE = 1 AND TITLE_IV = 1 $cond $campus_cond1  
	GROUP BY S_STUDENT_LEDGER.PK_STUDENT_ENROLLMENT ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME) ";

	$query2 = "select CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d' )) AS  BEGIN_DATE_1, SSN, PK_STUDENT_LEDGER, IF(EXPECTED_GRAD_DATE = '0000-00-00','', DATE_FORMAT(EXPECTED_GRAD_DATE, '%Y-%m-%d' )) AS EXPECTED_GRAD_DATE, SSN ,IF(DETERMINATION_DATE = '0000-00-00','', DATE_FORMAT(DETERMINATION_DATE, '%Y-%m-%d' )) AS DETERMINATION_DATE ,IF(GRADE_DATE = '0000-00-00','', DATE_FORMAT(GRADE_DATE, '%Y-%m-%d' )) AS GRADE_DATE, M_CAMPUS_PROGRAM.CODE as PROGRAM_CODE, FUNDING, STUDENT_STATUS,  IF(LDA = '0000-00-00','', DATE_FORMAT(LDA, '%Y-%m-%d' )) AS LDA, STUDENT_ID, IF(TRANSACTION_DATE = '0000-00-00','', DATE_FORMAT(TRANSACTION_DATE, '%Y-%m-%d' )) AS TRANSACTION_DATE_1, IF(CREDIT != 0, CREDIT, DEBIT ) AS AMOUNT, M_AR_LEDGER_CODE.CODE AS LEDGER_CODE, CAMPUS_CODE  
	from 
	S_STUDENT_MASTER 
	LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
	, S_STUDENT_ENROLLMENT 
	LEFT JOIN M_STUDENT_STATUS On M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING 
	, S_STUDENT_LEDGER, M_AR_LEDGER_CODE 
	, S_STUDENT_CAMPUS 
	LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
	WHERE 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
	S_STUDENT_LEDGER.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND 
	S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND 
	S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_LEDGER.PK_AR_LEDGER_CODE AND M_AR_LEDGER_CODE.TYPE = 1 AND TITLE_IV = 1 $cond $campus_cond1  ";

	$query3 = "select M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE, M_AR_LEDGER_CODE.CODE AS LEDGER_CODE      
	from 
	S_STUDENT_MASTER, S_STUDENT_ENROLLMENT, S_STUDENT_LEDGER, M_AR_LEDGER_CODE, S_STUDENT_CAMPUS  
	WHERE 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
	S_STUDENT_LEDGER.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND 
	S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND 
	S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_LEDGER.PK_AR_LEDGER_CODE AND M_AR_LEDGER_CODE.TYPE = 1 AND TITLE_IV = 1 $cond  $campus_cond1  
	GROUP BY S_STUDENT_LEDGER.PK_AR_LEDGER_CODE ORDER BY M_AR_LEDGER_CODE.CODE";

	$query4 = "select S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, IF(CREDIT != 0, CREDIT, DEBIT ) AS AMOUNT    
	from 
	S_STUDENT_MASTER, S_STUDENT_ENROLLMENT, S_STUDENT_LEDGER, M_AR_LEDGER_CODE, S_STUDENT_CAMPUS   
	WHERE 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
	S_STUDENT_LEDGER.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND 
	S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND 
	S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_LEDGER.PK_AR_LEDGER_CODE AND M_AR_LEDGER_CODE.TYPE = 1 AND TITLE_IV = 1 $cond $campus_cond1  ";

	if ($_POST['FORMAT'] == 1) {
		/////////////////////////////////////////////////////////////////
		$browser = '';
		if (stripos($_SERVER['HTTP_USER_AGENT'], "chrome") != false)
			$browser =  "chrome";
		else if (stripos($_SERVER['HTTP_USER_AGENT'], "Safari") != false)
			$browser = "Safari";
		else
			$browser = "firefox";
		require_once('../global/tcpdf/config/lang/eng.php');
		require_once('../global/tcpdf/tcpdf.php');


		class MYPDF extends TCPDF
		{
			public function Header()
			{
				global $db, $campus_name;

				$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

				if ($res->fields['PDF_LOGO'] != '') {
					$ext = explode(".", $res->fields['PDF_LOGO']);
					$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
				}

				$this->SetFont('helvetica', '', 15);
				$this->SetY(8);
				$this->SetX(55);
				$this->SetTextColor(000, 000, 000);
				$this->MultiCell(55, 5, $res->fields['SCHOOL_NAME'], 0, 'L', 0, 0, '', '', true);

				$this->SetFont('helvetica', 'I', 20);
				$this->SetY(8);
				$this->SetX(210);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, "Title IV Recipients - Detail", 0, false, 'L', 0, '', 0, false, 'M', 'L');


				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(185, 13, 292, 13, $style);

				$str = "Transaction Dates: ";
				if ($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '')
					$str .= $_POST['START_DATE'] . ' - ' . $_POST['END_DATE'];
				else if ($_POST['START_DATE'] != '')
					$str .= "From " . $_POST['START_DATE'];
				else if ($_POST['END_DATE'] != '')
					$str .= "As of Date: " . $_POST['END_DATE'];

				$this->SetFont('helvetica', 'I', 10);
				$this->SetY(16);
				$this->SetX(190);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');

				$this->SetY(18);
				$this->SetX(190);
				$this->SetTextColor(000, 000, 000);
				//$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				$this->MultiCell(102, 5, "Campus(es): " . $campus_name, 0, 'R', 0, 0, '', '', true);
			}
			public function Footer()
			{
				global $db;

				$this->SetY(-15);
				$this->SetX(270);
				$this->SetFont('helvetica', 'I', 7);
				$this->Cell(30, 10, 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

				$this->SetY(-15);
				$this->SetX(10);
				$this->SetFont('helvetica', 'I', 7);

				$timezone = $_SESSION['PK_TIMEZONE'];
				if ($timezone == '' || $timezone == 0) {
					$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
					$timezone = $res->fields['PK_TIMEZONE'];
					if ($timezone == '' || $timezone == 0)
						$timezone = 4;
				}

				$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
				$date = convert_to_user_date(date('Y-m-d H:i:s'), 'l, F d, Y h:i A', $res->fields['TIMEZONE'], date_default_timezone_get());

				$this->Cell(30, 10, $date, 0, false, 'C', 0, '', 0, false, 'T', 'M');
			}
		}

		$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . ' 001', PDF_HEADER_STRING, array(0, 64, 255), array(0, 64, 128));
		$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(7, 30, 7);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, 25);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->setLanguageArray($l);
		$pdf->setFontSubsetting(true);
		$pdf->SetFont('helvetica', '', 7, '', true);
		$pdf->AddPage();

		$total 	= 0;
		$txt 	= '';

		$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<thead>
						<tr>
							<td width="11%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />Student</td>
							<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />SSN</td>
							<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />Student ID</td>
							<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />First Term</td>
							<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />Program</td>
							<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />Status</td>
							<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Expected<br />Grad Date</td>
							<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Determination<br />Date</td>
							<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Grad<br />Date</td>
							<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />LDA</td>
							<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Transaction<br />Date</td>
							<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Ledger Code</td>
							<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Amount</td>
						</tr>
					</thead>';

		$sno   = 0;
		$TOTAL = 0;
		$res_en = $db->Execute($query);
		while (!$res_en->EOF) {
			$sno++;
			$PK_STUDENT_ENROLLMENT = $res_en->fields['PK_STUDENT_ENROLLMENT'];
			$SUB_TOTAL = 0;

			$k = 0;
			$res_ledger = $db->Execute($query2 . " AND S_STUDENT_LEDGER.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ORDER BY TRANSACTION_DATE ASC");
			while (!$res_ledger->EOF) {
				$k++;
				$SSN 		= $res_ledger->fields['SSN'];
				$SSN_DE  	= my_decrypt('', $SSN);

				$SUB_TOTAL  += $res_ledger->fields['AMOUNT'];
				$TOTAL  	+= $res_ledger->fields['AMOUNT'];
				$AMOUNT 	= $res_ledger->fields['AMOUNT'];
				if ($AMOUNT < 0)
					$AMOUNT = '(' . number_format_value_checker(($AMOUNT * -1), 2) . ')';
				else
					$AMOUNT = number_format_value_checker($AMOUNT, 2);

				if ($k == 1)
					$sno1 = $sno . '. ';
				else
					$sno1 = "";
				$txt .= '<tr>
							<td width="2%" >' . $sno1 . '</td>
							<td width="9%" >' . $res_ledger->fields['NAME'] . '</td>
							<td width="8%" >' . $SSN_DE . '</td>
							<td width="8%" >' . $res_ledger->fields['STUDENT_ID'] . '</td>
							<td width="7%" >' . $res_ledger->fields['BEGIN_DATE_1'] . '</td>
							<td width="8%" >' . $res_ledger->fields['PROGRAM_CODE'] . '</td>
							<td width="8%" >' . $res_ledger->fields['STUDENT_STATUS'] . '</td>
							<td width="7%" >' . $res_ledger->fields['EXPECTED_GRAD_DATE'] . '</td>
							<td width="7%" >' . $res_ledger->fields['DETERMINATION_DATE'] . '</td>
							<td width="7%" >' . $res_ledger->fields['GRADE_DATE'] . '</td>
							<td width="7%" >' . $res_ledger->fields['LDA'] . '</td>
							<td width="7%" >' . $res_ledger->fields['TRANSACTION_DATE_1'] . '</td>
							<td width="8%" >' . $res_ledger->fields['LEDGER_CODE'] . '</td>
							<td width="8%" align="right" >$ ' . $AMOUNT . '</td>
							
						</tr>';
				$res_ledger->MoveNext();
			}

			$txt 	.= '<tr>
							<td width="85%" ></td>
							
							<td width="8%" style="border-top:1px solid #000" ><i>Total</i></td>
							<td width="8%" style="border-top:1px solid #000" align="right" ><i>$ ' . number_format_value_checker($SUB_TOTAL, 2) . '</i></td>
						</tr>
						<tr>
							<td width="100%" ><br /></td>
						</tr>';

			$res_en->MoveNext();
		}

		$txt 	.= '<tr>
						<td width="82%" ></td>
						
						<td width="10%" style="border-top:1px solid #000" ><i>Title IV Grand Total</i></td>
						<td width="8%" style="border-top:1px solid #000" align="right" ><i>$ ' . number_format_value_checker($TOTAL, 2) . '</i></td>
					</tr>
				</table>';

		//echo $txt;exit;
		$pdf->writeHTML($txt, $ln = true, $fill = false, $reseth = true, $cell = true, $align = '');

		$pdf->AddPage();
		$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<thead>
						<tr>
							<td width="15%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Ledger Code</td>
							<td width="10%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Student Count</td>
							<td width="10%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Amount</td>
						</tr>
					</thead>';

		$count = 0;
		$total = 0;


		$res_ledger = $db->Execute($query3);
		while (!$res_ledger->EOF) {
			$PK_AR_LEDGER_CODE = $res_ledger->fields['PK_AR_LEDGER_CODE'];

			$EN_ARR = array();
			$AMOUNT = 0;
			$res_ledger1 = $db->Execute($query4 . " AND S_STUDENT_LEDGER.PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' ");
			while (!$res_ledger1->EOF) {
				$EN_ARR[$res_ledger1->fields['PK_STUDENT_ENROLLMENT']] = $res_ledger1->fields['PK_STUDENT_ENROLLMENT'];

				$AMOUNT += $res_ledger1->fields['AMOUNT'];
				$total	+= $res_ledger1->fields['AMOUNT'];
				$res_ledger1->MoveNext();
			}

			if ($AMOUNT < 0)
				$AMOUNT = '(' . number_format_value_checker(($AMOUNT * -1), 2) . ')';
			else
				$AMOUNT = number_format_value_checker($AMOUNT, 2);

			$count += count($EN_ARR);

			$txt .= '<tr>
						<td width="15%" >' . $res_ledger->fields['LEDGER_CODE'] . '</td>
						<td width="10%" align="right" >' . number_format_value_checker(count($EN_ARR)) . '</td>
						<td width="10%" align="right" >$ ' . $AMOUNT . '</td>
					</tr>';

			$res_ledger->MoveNext();
		}

		if ($total < 0)
			$total = '(' . number_format_value_checker(($total * -1), 2) . ')';
		else
			$total = number_format_value_checker($total, 2);
		$txt .= '<tr>
						<td width="15%" style="border-top:1px solid #000;" >Grand Total</td>
						<td width="10%" style="border-top:1px solid #000;" align="right" >' . number_format_value_checker($count) . '</td>
						<td width="10%" style="border-top:1px solid #000;" align="right" >$ ' . $total . '</td>
					</tr>
				</table>';
		$pdf->writeHTML($txt, $ln = true, $fill = false, $reseth = true, $cell = true, $align = '');

		$file_name = 'Title IV Recipients Detail.pdf';
		/*if($browser == 'Safari')
			$pdf->Output('temp/'.$file_name, 'FD');
		else	
			$pdf->Output($file_name, 'I');*/

		$pdf->Output('temp/' . $file_name, 'FD');
		return $file_name;
		/////////////////////////////////////////////////////////////////
	} else if ($_POST['FORMAT'] == 2) {

		include '../global/excel/Classes/PHPExcel/IOFactory.php';
		$cell1  = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
		define('EOL', (PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

		$total_fields = 120;
		for ($i = 0; $i <= $total_fields; $i++) {
			if ($i <= 25)
				$cell[] = $cell1[$i];
			else {
				$j = floor($i / 26) - 1;
				$k = ($i % 26);
				//echo $j."--".$k."<br />";
				$cell[] = $cell1[$j] . $cell1[$k];
			}
		}

		$dir 			= 'temp/';
		$inputFileType  = 'Excel2007';
		$file_name 		= 'Title IV Recipients Detail.xlsx';
		$outputFileName = $dir . $file_name;
		$outputFileName = str_replace(
			pathinfo($outputFileName, PATHINFO_FILENAME),
			pathinfo($outputFileName, PATHINFO_FILENAME) . "_" . $_SESSION['PK_USER'] . "_" . time(),
			$outputFileName
		);

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

		$line 	= 1;
		$index 	= -1;

		$heading[] = 'Student';
		$width[]   = 20;
		$heading[] = 'SSN';
		$width[]   = 20;
		$heading[] = 'Student ID';
		$width[]   = 20;
		$heading[] = 'Campus';
		$width[]   = 20;
		$heading[] = 'First Term';
		$width[]   = 20;
		$heading[] = 'Program';
		$width[]   = 20;
		$heading[] = 'Status';
		$width[]   = 20;
		$heading[] = 'Expected Grad Date';
		$width[]   = 20;
		$heading[] = 'Determination Date';
		$width[]   = 20;
		$heading[] = 'Grad Date';
		$width[]   = 20;
		$heading[] = 'LDA';
		$width[]   = 20;
		$heading[] = 'Transaction Date';
		$width[]   = 20;
		$heading[] = 'Ledger Code';
		$width[]   = 20;
		$heading[] = 'Amount';
		$width[]   = 20;


		$i = 0;
		foreach ($heading as $title) {
			$index++;
			$cell_no = $cell[$index] . $line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
		}

		$objPHPExcel->getActiveSheet()->freezePane('A1');

		$TOTAL = 0;
		$res_en = $db->Execute($query);
		while (!$res_en->EOF) {
			$sno++;
			$PK_STUDENT_ENROLLMENT = $res_en->fields['PK_STUDENT_ENROLLMENT'];
			$SUB_TOTAL = 0;

			$k = 0;
			$res_ledger = $db->Execute($query2 . " AND S_STUDENT_LEDGER.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ORDER BY TRANSACTION_DATE ASC");
			while (!$res_ledger->EOF) {
				$k++;
				$SSN 		= $res_ledger->fields['SSN'];
				$SSN_DE  	= my_decrypt('', $SSN);

				$SUB_TOTAL  += $res_ledger->fields['AMOUNT'];
				$TOTAL  	+= $res_ledger->fields['AMOUNT'];
				$AMOUNT 	= $res_ledger->fields['AMOUNT'];

				$line++;
				$index = -1;

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['NAME']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($SSN_DE);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['STUDENT_ID']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['CAMPUS_CODE']);

				$index++;
				$cell_no = $cell[$index] . $line;
				if ($res_ledger->fields['BEGIN_DATE_1'] != '') {
					$dateValue = floor(PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res_ledger->fields['BEGIN_DATE_1'])));
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
					$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
				}

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['PROGRAM_CODE']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['STUDENT_STATUS']);

				$index++;
				$cell_no = $cell[$index] . $line;
				if ($res_ledger->fields['EXPECTED_GRAD_DATE'] != '') {
					$dateValue = floor(PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res_ledger->fields['EXPECTED_GRAD_DATE'])));
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
					$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
				}

				$index++;
				$cell_no = $cell[$index] . $line;
				if ($res_ledger->fields['DETERMINATION_DATE'] != '') {
					$dateValue = floor(PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res_ledger->fields['DETERMINATION_DATE'])));
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
					$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
				}

				$index++;
				$cell_no = $cell[$index] . $line;
				if ($res_ledger->fields['GRADE_DATE'] != '') {
					$dateValue = floor(PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res_ledger->fields['GRADE_DATE'])));
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
					$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
				}

				$index++;
				$cell_no = $cell[$index] . $line;
				if ($res_ledger->fields['LDA'] != '') {
					$dateValue = floor(PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res_ledger->fields['LDA'])));
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
					$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
				}

				$index++;
				$cell_no = $cell[$index] . $line;
				if ($res_ledger->fields['TRANSACTION_DATE_1'] != '') {
					$dateValue = floor(PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res_ledger->fields['TRANSACTION_DATE_1'])));
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
					$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
				}

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['LEDGER_CODE']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($AMOUNT);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

				$res_ledger->MoveNext();
			}

			$res_en->MoveNext();
		}

		$objPHPExcel->createSheet(1);
		$objPHPExcel->setActiveSheetIndex(1);
		$objPHPExcel->getActiveSheet()->setTitle("Summary");

		$heading = array();
		$width 	 = array();

		$heading[] = 'Ledger Code';
		$width[]   = 30;
		$heading[] = 'Student Count';
		$width[]   = 20;
		$heading[] = 'Amount';
		$width[]   = 20;

		$line = 1;
		$index = -1;

		$i = 0;
		foreach ($heading as $title) {
			$index++;
			$cell_no = $cell[$index] . $line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
		}

		$res_ledger = $db->Execute($query3);
		while (!$res_ledger->EOF) {
			$PK_AR_LEDGER_CODE = $res_ledger->fields['PK_AR_LEDGER_CODE'];

			$AMOUNT = 0;
			$res_ledger1 = $db->Execute($query4 . " AND S_STUDENT_LEDGER.PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' ");
			while (!$res_ledger1->EOF) {
				$EN_ARR[$res_ledger1->fields['PK_STUDENT_ENROLLMENT']] = $res_ledger1->fields['PK_STUDENT_ENROLLMENT'];

				$AMOUNT += $res_ledger1->fields['AMOUNT'];
				$res_ledger1->MoveNext();
			}

			$line++;
			$index = -1;

			$index++;
			$cell_no = $cell[$index] . $line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['LEDGER_CODE']);

			$index++;
			$cell_no = $cell[$index] . $line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(count($EN_ARR));

			$index++;
			$cell_no = $cell[$index] . $line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($AMOUNT);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

			$res_ledger->MoveNext();
		}
		$objPHPExcel->setActiveSheetIndex(0);

		$objWriter->save($outputFileName);
		$objPHPExcel->disconnectWorksheets();
		header("location:" . $outputFileName);
	}
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
	<title><?= MNU_TITLE_IV_RECIPIENTS_DETAIL ?> | <?= $title ?></title>
	<style>
		li>a>label {
			position: unset !important;
		}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
	<? require_once("pre_load.php"); ?>
	<div id="main-wrapper">
		<? require_once("menu.php"); ?>
		<div class="page-wrapper">
			<div class="container-fluid">
				<div class="row page-titles">
					<div class="col-md-5 align-self-center">
						<h4 class="text-themecolor">
							<?= MNU_TITLE_IV_RECIPIENTS_DETAIL ?>
						</h4>
					</div>
				</div>

				<form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off">
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<!-- Ticket # 1318  -->
										<div class="col-md-2">
											<?= CAMPUS ?>
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control required-entry">
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?= $res_type->fields['PK_CAMPUS'] ?>" <? if ($res_type->RecordCount() == 1) echo "selected"; ?>><?= $res_type->fields['CAMPUS_CODE'] ?></option>
												<? $res_type->MoveNext();
												} ?>
											</select>
										</div>
										<!-- Ticket # 1318  -->

										<div class="col-md-2">
											<?= START_DATE ?>
											<input type="text" class="form-control date required-entry" id="START_DATE" name="START_DATE" value="">
										</div>
										<div class="col-md-2">
											<?= END_DATE ?>
											<input type="text" class="form-control date required-entry" id="END_DATE" name="END_DATE" value="">
										</div>

										<div class="col-md-2">
											<br />
											<button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info"><?= PDF ?></button>
											<button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info"><?= EXCEL ?></button>
											<input type="hidden" name="FORMAT" id="FORMAT">
										</div>
									</div>
									<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
		<? require_once("footer.php"); ?>
	</div>

	<? require_once("js.php"); ?>
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />

	<script type="text/javascript">
		jQuery(document).ready(function($) {
			jQuery('.date').datepicker({
				todayHighlight: true,
				orientation: "bottom auto"
			});
		});
	</script>

	<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.4.1.min.js"></script>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		function submit_form(val) {
			jQuery(document).ready(function($) {
				var valid = new Validation('form1', {
					onSubmit: false
				});
				var result = valid.validate();
				if (result == true) {
					document.getElementById('FORMAT').value = val
					document.form1.submit();
				}
			});
		}
	</script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css" />
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('#PK_STUDENT_STATUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= STUDENT_STATUS ?>',
				nonSelectedText: '',
				numberDisplayed: 2,
				nSelectedText: '<?= STUDENT_STATUS ?> selected'
			});

			/* Ticket # 1318  */
			$('#PK_CAMPUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= CAMPUS ?>',
				nonSelectedText: '',
				numberDisplayed: 2,
				nSelectedText: '<?= CAMPUS ?> selected'
			});
			/* Ticket # 1318  */
		});
	</script>
</body>

</html>