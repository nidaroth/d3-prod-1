<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/transaction_summary.php");
require_once("check_access.php");
include '../global/excel/Classes/PHPExcel/IOFactory.php';

if (check_access('REPORT_ACCOUNTING') == 0) {
	header("location:../index");
	exit;
}

if (!empty($_POST)) {
	//header("location:projected_funds_pdf?st=".$_POST['START_DATE'].'&et='.$_POST['END_DATE'].'&dt='.$_POST['DATE_TYPE'].'&e='.$_POST['PK_EMPLOYEE_MASTER'].'&tc='.$_POST['TASK_COMPLETED']);
	// echo "<pre>";print_r($_REQUEST);exit;
	if ($_POST['FORMAT'] == 2) {
		$cell1  = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
		define('EOL', (PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
		$dir 			= 'temp/';
		$inputFileType  = 'Excel2007';
		$file_name 		= 'Past Due.xlsx';
		$outputFileName = $dir . $file_name;
		$outputFileName = str_replace(pathinfo($outputFileName, PATHINFO_FILENAME), pathinfo($outputFileName, PATHINFO_FILENAME) . "_" . $_SESSION['PK_USER'] . "_" . time(), $outputFileName);

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	}
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
					// $this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
				}

				$this->SetFont('helvetica', '', 15);
				$this->SetY(8);
				$this->SetX(55);
				$this->SetTextColor(000, 000, 000);
				//$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
				$this->MultiCell(55, 5, $res->fields['SCHOOL_NAME'], 0, 'L', 0, 0, '', '', true);

				$this->SetFont('helvetica', 'I', 20);
				$this->SetY(8);
				$this->SetX(135);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, "Transaction Summary", 0, false, 'L', 0, '', 0, false, 'M', 'L');


				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(120, 13, 202, 13, $style);

				$str = "";
				if ($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '')
					$str = "Between " . $_POST['START_DATE'] . ' - ' . $_POST['END_DATE'];
				else if ($_POST['START_DATE'] != '')
					$str = "From " . $_POST['START_DATE'];
				else if ($_POST['END_DATE'] != '')
					$str = "To " . $_POST['END_DATE'];

				$this->SetFont('helvetica', 'I', 8);
				$this->SetY(16);
				$this->SetX(100);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(102, 5, "Transaction Dates: " . $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');

				$report_option = "";
				if ($_POST['REPORT_OPTIONS'] == 1)
					$report_option = "All Transactions";
				else if ($_POST['REPORT_OPTIONS'] == 2)
					$report_option = "Positive Credits or Debits";
				else if ($_POST['REPORT_OPTIONS'] == 3)
					$report_option = "Negative Credits or Debits";
				else if ($_POST['REPORT_OPTIONS'] == 4)
					$report_option = "Positive Credits Only";
				else if ($_POST['REPORT_OPTIONS'] == 5)
					$report_option = "Negative Credits Only";
				else if ($_POST['REPORT_OPTIONS'] == 6)
					$report_option = "Positive Debits Only";
				else if ($_POST['REPORT_OPTIONS'] == 7)
					$report_option = "Negative Debits Only";

				$this->SetFont('helvetica', 'I', 8);
				$this->SetY(18);
				$this->SetX(100);
				$this->SetTextColor(000, 000, 000);
				//$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				$this->MultiCell(102, 5, $report_option, 0, 'R', 0, 0, '', '', true);

				$this->SetFont('helvetica', 'I', 8);
				$this->SetY(22);
				$this->SetX(100);
				$this->SetTextColor(000, 000, 000);
				//$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				$this->MultiCell(102, 5, "Campus(es): " . $campus_name, 0, 'R', 0, 0, '', '', true);


				$group_by = "";
				if ($_POST['GROUP_BY'] == 1)
					$group_by = "Ledger Code";
				else
					$group_by = "Program";

				$this->SetY(28);
				$this->SetX(100);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(102, 5, "Group By: " . $group_by, 0, false, 'R', 0, '', 0, false, 'M', 'L');
			}
			public function Footer()
			{
				global $db;

				$this->SetY(-15);
				$this->SetX(180);
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

		$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . ' 001', PDF_HEADER_STRING, array(0, 64, 255), array(0, 64, 128));
		$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(7, 35, 7);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, 30);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->setLanguageArray($l);
		$pdf->setFontSubsetting(true);
		$pdf->SetFont('helvetica', '', 7, '', true);
		$pdf->AddPage();
	}
	#for pdf $txt 
	$txt 	= '';
	#init vars for L-C-G
	$ar_ledger_codes_header_str  = $db->Execute("SELECT GROUP_CONCAT(LEDGER_CODE_GROUP) AS LEDGER_CODE_GROUPS FROM S_LEDGER_CODE_GROUP WHERE PK_LEDGER_CODE_GROUP IN (" . implode(',', $_REQUEST['PK_LEDGER_CODE_GROUP']) . ") ");
	$counter_index = 0;
	$final_group_summary = [];
	foreach ($_REQUEST['PK_LEDGER_CODE_GROUP'] as $key1 => $PK_LEDGER_CODE_GROUP1) {

		$imploded =  $PK_LEDGER_CODE_GROUP1;
		$ar_ledger_codes = $db->Execute("SELECT GROUP_CONCAT(PK_AR_LEDGER_CODES) AS CONCATED_RES , LEDGER_CODE_GROUP FROM S_LEDGER_CODE_GROUP WHERE PK_LEDGER_CODE_GROUP IN ($imploded) ");
		$ar_ledger_codes_exploded = explode(',', $ar_ledger_codes->fields['CONCATED_RES']);
		$ar_ledger_codes_exploded = array_unique($ar_ledger_codes_exploded);
		$_POST['PK_AR_LEDGER_CODE'] = [];
		$_POST['PK_AR_LEDGER_CODE'] = $ar_ledger_codes_exploded;

		$campus_name = "";
		$campus_cond = "";
		$campus_id	 = "";
		if (!empty($_POST['PK_CAMPUS'])) {
			$PK_CAMPUS 	 = implode(",", $_POST['PK_CAMPUS']);
			$campus_cond = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
		}

		$cond = "";
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
		$cond .= " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($campus_id) ";

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

		if (!empty($_POST['PK_AR_LEDGER_CODE'])) {
			$cond .= " AND S_STUDENT_LEDGER.PK_AR_LEDGER_CODE IN (" . implode(",", $_POST['PK_AR_LEDGER_CODE']) . ") ";
		}

		//echo $cond;exit;
		$query = "select SUM(CREDIT) as CREDIT, SUM(DEBIT) as DEBIT, CONCAT(CODE,' - ',LEDGER_DESCRIPTION) as LEDGER 
	from 
	S_STUDENT_MASTER,S_STUDENT_ENROLLMENT ,S_STUDENT_LEDGER
	LEFT JOIN M_AR_LEDGER_CODE On M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_LEDGER.PK_AR_LEDGER_CODE 
	, S_STUDENT_CAMPUS  
	WHERE 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
	S_STUDENT_LEDGER.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND 
	S_STUDENT_LEDGER.PK_STUDENT_ENROLLMENT = S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT AND 
	S_STUDENT_LEDGER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND (PK_PAYMENT_BATCH_DETAIL > 0 OR PK_MISC_BATCH_DETAIL > 0 OR PK_TUITION_BATCH_DETAIL > 0 ) $cond  ";
	$PK_CAMPUS_PROGRAM_ARR = [];
	$PROGRAM_CODE_ARR = [] ;
		if ($_POST['GROUP_BY'] == 1) {
			$PK_CAMPUS_PROGRAM_ARR[] = -1;
			$PROGRAM_CODE_ARR[] 	 = '';
		} else {
			$res  = $db->Execute("SELECT M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM, M_CAMPUS_PROGRAM.CODE, M_CAMPUS_PROGRAM.DESCRIPTION   
		FROM 
		S_STUDENT_MASTER
		LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
		LEFT JOIN S_STUDENT_LEDGER ON S_STUDENT_LEDGER.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT
		LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_LEDGER.PK_AR_LEDGER_CODE 
		LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM
		LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_LEDGER.PK_STUDENT_ENROLLMENT
		WHERE 
		S_STUDENT_LEDGER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND (PK_PAYMENT_BATCH_DETAIL > 0 OR PK_MISC_BATCH_DETAIL > 0 OR PK_TUITION_BATCH_DETAIL > 0 ) $cond
		GROUP BY S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM ORDER BY M_CAMPUS_PROGRAM.CODE ASC ");
		
		while (!$res->EOF) {
				$PK_CAMPUS_PROGRAM_ARR[] = $res->fields['PK_CAMPUS_PROGRAM'];
				$PROGRAM_CODE_ARR[] 	 = $res->fields['CODE'] . ' - ' . $res->fields['DESCRIPTION'];

				$res->MoveNext();
			}
		}

		if ($_POST['FORMAT'] == 1) {
			

			//DIAM-2093
			$rep_cond = "";
			if ($_POST['REPORT_OPTIONS'] == 1) {
			} else if ($_POST['REPORT_OPTIONS'] == 2) {
				$rep_cond = " (DEBIT > 0 OR CREDIT > 0) ";
			} else if ($_POST['REPORT_OPTIONS'] == 3) {
				$rep_cond = " (DEBIT < 0 OR CREDIT < 0) ";
			} else if ($_POST['REPORT_OPTIONS'] == 4) {
				$rep_cond = " CREDIT > 0 ";
			} else if ($_POST['REPORT_OPTIONS'] == 5) {
				$rep_cond = " CREDIT < 0 ";
			} else if ($_POST['REPORT_OPTIONS'] == 6) {
				$rep_cond = " DEBIT > 0 ";
			} else if ($_POST['REPORT_OPTIONS'] == 7) {
				$rep_cond = " DEBIT < 0 ";
			}
			if ($rep_cond != '') {
				$rep_cond  = " AND " . $rep_cond;
			}
			//DIAM-2093

			$total 	= 0;
			if ($counter_index > 0) {
				$pagebreak = ' style="page-break-before: always" ';
			}
			$counter_index++;
			$txt .= '<br><h2 ' . $pagebreak . '>' . $ar_ledger_codes->fields['LEDGER_CODE_GROUP'] . '</h2>';

			// echo '<br><h2 ' . $pagebreak . '>' . $ar_ledger_codes->fields['LEDGER_CODE_GROUP'] . '</h2><br><br>';
			$GRAND_TOT_CREDIT 	= 0;
			$GRAND_TOT_DEBIT 	= 0;
			// dump($PK_CAMPUS_PROGRAM_ARR);
			foreach ($PK_CAMPUS_PROGRAM_ARR as $ind => $PK_CAMPUS_PROGRAM) {
				
				$SUB_TOT_CREDIT = 0;
				$SUB_TOT_DEBIT 	= 0;

				$cond1 = "";
				if ($PK_CAMPUS_PROGRAM > 0)
					$cond1 = " AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' ";

				$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<thead>';

				if ($PK_CAMPUS_PROGRAM > 0) {
					$txt .= '<tr>
									<td width="100%" style="font-size:40px;" >' . $PROGRAM_CODE_ARR[$ind] . '</td>
								</tr>';
				}

				$txt .= '<tr>
								<td width="40%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Ledger Code</td>
								<td width="15%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Debit</td>
								<td width="15%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Credit</td>
							</tr>
						</thead>';



				$having  = "";
				$having1 = "";
				if ($_POST['REPORT_OPTIONS'] == 1) {
				} else if ($_POST['REPORT_OPTIONS'] == 2) {
					$having  = " ( (
								SELECT SUM(DEBIT) 
								FROM S_STUDENT_LEDGER 
								WHERE PK_AR_LEDGER_CODE = S_STUDENT_LEDGER.PK_AR_LEDGER_CODE
							) > 0 OR  (
								SELECT SUM(CREDIT) 
								FROM S_STUDENT_LEDGER 
								WHERE PK_AR_LEDGER_CODE = S_STUDENT_LEDGER.PK_AR_LEDGER_CODE
							) > 0) ";
					$having1 = " (DEBIT > 0 OR CREDIT > 0) ";
				} else if ($_POST['REPORT_OPTIONS'] == 3) {
					$having  = " (  (
								SELECT SUM(DEBIT) 
								FROM S_STUDENT_LEDGER 
								WHERE PK_AR_LEDGER_CODE = S_STUDENT_LEDGER.PK_AR_LEDGER_CODE
							) < 0 OR  (
								SELECT SUM(CREDIT) 
								FROM S_STUDENT_LEDGER 
								WHERE PK_AR_LEDGER_CODE = S_STUDENT_LEDGER.PK_AR_LEDGER_CODE
							) < 0) ";
					$having1 = " (DEBIT < 0 OR CREDIT < 0) ";
				} else if ($_POST['REPORT_OPTIONS'] == 4) {
					$having  = "  (
								SELECT SUM(CREDIT) 
								FROM S_STUDENT_LEDGER 
								WHERE PK_AR_LEDGER_CODE = S_STUDENT_LEDGER.PK_AR_LEDGER_CODE
							) > 0 ";
					$having1 = " CREDIT > 0 ";
				} else if ($_POST['REPORT_OPTIONS'] == 5) {
					$having  = "  (
								SELECT SUM(CREDIT) 
								FROM S_STUDENT_LEDGER 
								WHERE PK_AR_LEDGER_CODE = S_STUDENT_LEDGER.PK_AR_LEDGER_CODE
							) < 0 ";
					$having1 = " CREDIT < 0 ";
				} else if ($_POST['REPORT_OPTIONS'] == 6) {
					$having  = "  (
								SELECT SUM(DEBIT) 
								FROM S_STUDENT_LEDGER 
								WHERE PK_AR_LEDGER_CODE = S_STUDENT_LEDGER.PK_AR_LEDGER_CODE
							) > 0 ";
					$having1 = " DEBIT > 0 ";
				} else if ($_POST['REPORT_OPTIONS'] == 7) {
					$having  = "  (
								SELECT SUM(DEBIT) 
								FROM S_STUDENT_LEDGER 
								WHERE PK_AR_LEDGER_CODE = S_STUDENT_LEDGER.PK_AR_LEDGER_CODE
							) < 0 ";
					$having1 = " DEBIT < 0 ";
				}
				$cond_having = ' ';
				if ($having != '') {
					$cond_having .= " AND " . $having1 . " ";
				}

				$res_ledger = $db->Execute($query . " $rep_cond $cond1 $cond_having  GROUP BY S_STUDENT_LEDGER.PK_AR_LEDGER_CODE ORDER BY CONCAT(CODE,' - ',LEDGER_DESCRIPTION) ASC "); //DIAM-2093
				while (!$res_ledger->EOF) {
					// echo $query . " $rep_cond $cond1 $cond_having  GROUP BY S_STUDENT_LEDGER.PK_AR_LEDGER_CODE ORDER BY CONCAT(CODE,' - ',LEDGER_DESCRIPTION) ASC <br><br>";
					$BALANCE 	= $res_ledger->fields['DEBIT'] - $res_ledger->fields['CREDIT'];
					$flag 		= 0;
					if ($_POST['REPORT_OPTIONS'] == 1) {
						$flag = 1;
					} else if ($_POST['REPORT_OPTIONS'] == 2) {
						if ($res_ledger->fields['DEBIT'] > 0 || $res_ledger->fields['CREDIT'] > 0)
							$flag = 1;
					} else if ($_POST['REPORT_OPTIONS'] == 3) {
						if ($res_ledger->fields['DEBIT'] < 0 || $res_ledger->fields['CREDIT'] < 0)
							$flag = 1;
					} else if ($_POST['REPORT_OPTIONS'] == 4) {
						if ($res_ledger->fields['CREDIT'] > 0)
							$flag = 1;
					} else if ($_POST['REPORT_OPTIONS'] == 5) {
						if ($res_ledger->fields['CREDIT'] < 0)
							$flag = 1;
					} else if ($_POST['REPORT_OPTIONS'] == 6) {
						if ($res_ledger->fields['DEBIT'] > 0)
							$flag = 1;
					} else if ($_POST['REPORT_OPTIONS'] == 7) {
						if ($res_ledger->fields['DEBIT'] < 0)
							$flag = 1;
					}

					if ($flag == 1) {
						$SUB_TOT_CREDIT += $res_ledger->fields['CREDIT'];
						$SUB_TOT_DEBIT  += $res_ledger->fields['DEBIT'];

						$GRAND_TOT_CREDIT += $res_ledger->fields['CREDIT'];
						$GRAND_TOT_DEBIT  += $res_ledger->fields['DEBIT'];

						$CREDIT = $res_ledger->fields['CREDIT'];
						$DEBIT  = $res_ledger->fields['DEBIT'];

						if ($CREDIT < 0) {
							$CREDIT = $CREDIT * -1;
							$CREDIT = '(' . number_format_value_checker($CREDIT, 2) . ')';
						} else
							$CREDIT = number_format_value_checker($CREDIT, 2);

						if ($DEBIT < 0) {
							$DEBIT = $DEBIT * -1;
							$DEBIT = '(' . number_format_value_checker($DEBIT, 2) . ')';
						} else
							$DEBIT = number_format_value_checker($DEBIT, 2);

						$txt 	.= '<tr>
								<td width="40%" >' . $res_ledger->fields['LEDGER'] . '</td>
								<td width="15%" align="right" >$ ' . $DEBIT . '</td>
								<td width="15%" align="right" >$ ' . $CREDIT . '</td>
							</tr>';
					}

					$res_ledger->MoveNext();
				}

				if ($SUB_TOT_DEBIT < 0) {
					$SUB_TOT_DEBIT = $SUB_TOT_DEBIT * -1;
					$SUB_TOT_DEBIT = '(' . number_format_value_checker($SUB_TOT_DEBIT, 2) . ')';
				} else
					$SUB_TOT_DEBIT = number_format_value_checker($SUB_TOT_DEBIT, 2);

				if ($SUB_TOT_CREDIT < 0) {
					$SUB_TOT_CREDIT = $SUB_TOT_CREDIT * -1;
					$SUB_TOT_CREDIT = '(' . number_format_value_checker($SUB_TOT_CREDIT, 2) . ')';
				} else
					$SUB_TOT_CREDIT = number_format_value_checker($SUB_TOT_CREDIT, 2);

				$txt 	.= '<tr>
							<td width="40%" align="right" style="font-size:40px;" align="right" ><i>Grand Total </i></td>
							<td width="15%" align="right" style="font-size:40px;" align="right" ><i>$ ' . $SUB_TOT_DEBIT . '</i></td>
							<td width="15%" align="right" style="font-size:40px;" align="right" ><i>$ ' . $SUB_TOT_CREDIT . '</i></td>
						</tr>
					</table>';
				$final_group_summary[$ar_ledger_codes->fields['LEDGER_CODE_GROUP']]['SUB_TOT_DEBIT'] = $SUB_TOT_DEBIT;
				$final_group_summary[$ar_ledger_codes->fields['LEDGER_CODE_GROUP']]['SUB_TOT_CREDIT'] = $SUB_TOT_CREDIT;
	
			}

			if ($_POST['GROUP_BY'] == 2) {
				if ($GRAND_TOT_DEBIT < 0) {
					$GRAND_TOT_DEBIT = $GRAND_TOT_DEBIT * -1;
					$GRAND_TOT_DEBIT = '(' . number_format_value_checker($GRAND_TOT_DEBIT, 2) . ')';
				} else
					$GRAND_TOT_DEBIT = number_format_value_checker($GRAND_TOT_DEBIT, 2);

				if ($GRAND_TOT_CREDIT < 0) {
					$GRAND_TOT_CREDIT = $GRAND_TOT_CREDIT * -1;
					$GRAND_TOT_CREDIT = '(' . number_format_value_checker($GRAND_TOT_CREDIT, 2) . ')';
				} else
					$GRAND_TOT_CREDIT = number_format_value_checker($GRAND_TOT_CREDIT, 2);

				$txt 	.= '<br /><br />
						<table border="0" cellspacing="0" cellpadding="3" width="100%">
							<tr>
								<td width="40%" align="right" style="font-size:40px;" align="right" ><i>Report Total </i></td>
								<td width="15%" align="right" style="font-size:40px;" align="right" ><i>$ ' . $GRAND_TOT_DEBIT . '</i></td>
								<td width="15%" align="right" style="font-size:40px;" align="right" ><i>$ ' . $GRAND_TOT_CREDIT . '</i></td>
							</tr>
						</table>';
			}

		
			/////////////////////////////////////////////////////////////////
		} else if ($_POST['FORMAT'] == 2) {
			$file_name = "Transaction Summary - ";
			if ($_POST['REPORT_OPTIONS'] == 1)
				$file_name .= "All Transactions.xlsx";
			else if ($_POST['REPORT_OPTIONS'] == 2)
				$file_name .= "Positive Credits or Debits.xlsx";
			else if ($_POST['REPORT_OPTIONS'] == 3)
				$file_name .= "Negative Credits or Debits.xlsx";
			else if ($_POST['REPORT_OPTIONS'] == 4)
				$file_name .= "Positive Credits Only.xlsx";
			else if ($_POST['REPORT_OPTIONS'] == 5)
				$file_name .= "Negative Credits Only.xlsx";
			else if ($_POST['REPORT_OPTIONS'] == 6)
				$file_name .= "Positive Debits Only.xlsx";
			else if ($_POST['REPORT_OPTIONS'] == 7)
				$file_name .= "Negative Debits Only.xlsx";

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
			$outputFileName = $dir . $file_name;
			$outputFileName = str_replace(
				pathinfo($outputFileName, PATHINFO_FILENAME),
				pathinfo($outputFileName, PATHINFO_FILENAME) . "_" . $_SESSION['PK_USER'] . "_" . time(),
				$outputFileName
			);

		try {
				//check if sheet exists 
				$sheety = $objPHPExcel->setActiveSheetIndex($counter_index);
				$sheety->setTitle(substr(clean_sheet_title($ar_ledger_codes->fields['LEDGER_CODE_GROUP']) , 0 , 31));
			} catch (\Throwable $th) {
				//throw $th;
				//else create new sheet and set to active
				$newsheety = $objPHPExcel->createSheet($counter_index);
				$newsheety->setTitle(substr(clean_sheet_title($ar_ledger_codes->fields['LEDGER_CODE_GROUP']) , 0 , 31));
				$objPHPExcel->setActiveSheetIndex($counter_index);
			}
			$cell_no = 'A1';
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue("Campus(es): " . $campus_name);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);

			$line 	= 2;
			$index 	= -1;
			$width = false;
			$heading = false;
			if ($_POST['GROUP_BY'] == 2) {
				$heading[] = 'Program';
				$width[]   = 20;
			}
			$heading[] = 'Ledger Code';
			$width[]   = 20;
			$heading[] = 'Debit';
			$width[]   = 20;
			$heading[] = 'Credit';
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

			//DIAM-2093
			$rep_cond = "";
			if ($_POST['REPORT_OPTIONS'] == 1) {
			} else if ($_POST['REPORT_OPTIONS'] == 2) {
				$rep_cond = " (DEBIT > 0 OR CREDIT > 0) ";
			} else if ($_POST['REPORT_OPTIONS'] == 3) {
				$rep_cond = " (DEBIT < 0 OR CREDIT < 0) ";
			} else if ($_POST['REPORT_OPTIONS'] == 4) {
				$rep_cond = " CREDIT > 0 ";
			} else if ($_POST['REPORT_OPTIONS'] == 5) {
				$rep_cond = " CREDIT < 0 ";
			} else if ($_POST['REPORT_OPTIONS'] == 6) {
				$rep_cond = " DEBIT > 0 ";
			} else if ($_POST['REPORT_OPTIONS'] == 7) {
				$rep_cond = " DEBIT < 0 ";
			}
			if ($rep_cond != '') {
				$rep_cond  = " AND " . $rep_cond;
			}
			//DIAM-2093

			foreach ($PK_CAMPUS_PROGRAM_ARR as $ind => $PK_CAMPUS_PROGRAM) {

				$cond1 = "";
				if ($PK_CAMPUS_PROGRAM > 0)
					$cond1 = " AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' ";

				$GRAND_TOT_CREDIT = 0;
				$GRAND_TOT_DEBIT 	= 0;
				$res_ledger = $db->Execute($query . " $rep_cond $cond1 GROUP BY S_STUDENT_LEDGER.PK_AR_LEDGER_CODE ORDER BY CONCAT(CODE,' - ',LEDGER_DESCRIPTION) ASC ");

				while (!$res_ledger->EOF) {

					$BALANCE 	= $res_ledger->fields['DEBIT'] - $res_ledger->fields['CREDIT'];
					$flag 		= 0;
					if ($_POST['REPORT_OPTIONS'] == 1) {
						$flag = 1;
					} else if ($_POST['REPORT_OPTIONS'] == 2) {
						if ($res_ledger->fields['DEBIT'] > 0 || $res_ledger->fields['CREDIT'] > 0)
							$flag = 1;
					} else if ($_POST['REPORT_OPTIONS'] == 3) {
						if ($res_ledger->fields['DEBIT'] < 0 || $res_ledger->fields['CREDIT'] < 0)
							$flag = 1;
					} else if ($_POST['REPORT_OPTIONS'] == 4) {
						if ($res_ledger->fields['CREDIT'] > 0)
							$flag = 1;
					} else if ($_POST['REPORT_OPTIONS'] == 5) {
						if ($res_ledger->fields['CREDIT'] < 0)
							$flag = 1;
					} else if ($_POST['REPORT_OPTIONS'] == 6) {
						if ($res_ledger->fields['DEBIT'] > 0)
							$flag = 1;
					} else if ($_POST['REPORT_OPTIONS'] == 7) {
						if ($res_ledger->fields['DEBIT'] < 0)
							$flag = 1;
					}

					if ($flag == 1) {
						$GRAND_TOT_CREDIT += $res_ledger->fields['CREDIT'];
						$GRAND_TOT_DEBIT  += $res_ledger->fields['DEBIT'];

						$CREDIT = $res_ledger->fields['CREDIT'];
						$DEBIT  = $res_ledger->fields['DEBIT'];

						$line++;
						$index = -1;

						if ($_POST['GROUP_BY'] == 2) {
							$index++;
							$cell_no = $cell[$index] . $line;
							$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($PROGRAM_CODE_ARR[$ind]);
						}

						$index++;
						$cell_no = $cell[$index] . $line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['LEDGER']);

						$index++;
						$cell_no = $cell[$index] . $line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['DEBIT']);
						$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getNumberFormat()->setFormatCode("0.00");
						$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getNumberFormat()->applyFromArray(
							array(
								'code' => PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE
							)
						);
						$final_group_summary[$ar_ledger_codes->fields['LEDGER_CODE_GROUP']]['TOT_DEBIT'] += $res_ledger->fields['DEBIT'];

						$index++;
						$cell_no = $cell[$index] . $line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['CREDIT']);
						$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getNumberFormat()->setFormatCode("0.00");
						$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getNumberFormat()->applyFromArray(
							array(
								'code' => PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE
							)
						);
						$final_group_summary[$ar_ledger_codes->fields['LEDGER_CODE_GROUP']]['TOT_CREDIT'] += $res_ledger->fields['CREDIT'];
					
					}

					$res_ledger->MoveNext();
				}
			}
			foreach ($objPHPExcel->getWorksheetIterator() as $sheet) {
				// Get the highest column number (e.g., ZZ) and last row number for the current sheet
				$highestColumn = $sheet->getHighestColumn();
				$lastRow = $sheet->getHighestRow();
			
				// Set the horizontal alignment for the range A1:ZZ(last row)
				$sheet->getStyle('A1:' . $highestColumn . $lastRow)
					  ->getAlignment()
					  ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			}

			// $objWriter->save($outputFileName);
			// $objPHPExcel->disconnectWorksheets();
			// header("location:" . $outputFileName);
		}
		$counter_index++;
	}
	if($_POST['FORMAT'] == 1){
		
## ADD GROUP CODE SUMMARY 

$txt .= '
<div style="page-break-before: always"></div>
<br>
<br>
<br>
<br>
<br>
<h2>Ledger Code Group Summary</h2>
<br>
<br>
<table border="0" cellspacing="0" cellpadding="3" width="100%" >
<tr><td width="15%"></td><td>
<table  style="border-collapse: collapse;" border="0" cellspacing="0" cellpadding="3" width="100%">
	<thead>
		<tr>
			<th style="border: 1px solid black;"><b>Ledger Code Group</b></th>
			<th style="border: 1px solid black;text-align:right"><b>Total Debit</b></th>
			<th style="border: 1px solid black;text-align:right"><b>Total Credit</b></th>
		</tr>
	</thead>
<tbody>';
foreach ($final_group_summary as $key => $value) {
	$txt .= 
	'<tr>
		<td style="border: 1px solid black;">'.$key.'</td>
		<td style="border: 1px solid black;text-align:right">$'.$value['SUB_TOT_DEBIT'].'</td>
		<td style="border: 1px solid black;text-align:right">$'.$value['SUB_TOT_CREDIT'].'</td>
	</tr>';
	// dd($value ,number_format_value_checker($value ) );
}
$txt .= "</tbody></table> </td></tr></table>";
		//echo $txt;exit;
			$pdf->writeHTML($txt, $ln = true, $fill = false, $reseth = true, $cell = true, $align = '');
			$file_name = 'Transaction_Summary_' . uniqid() . '.pdf';
			/*if($browser == 'Safari')
			$pdf->Output('temp/'.$file_name, 'FD');
			else	
			$pdf->Output($file_name, 'I');*/
			$pdf->Output('temp/' . $file_name, 'FD');
			return $file_name;
			/////////////////////////////////////////////////////////////////
		 
	}
	if($_POST['FORMAT'] == 2){
		// dd($final_group_summary);
		#iterate for summary sheet 
		$newsheety = $objPHPExcel->createSheet($counter_index);
		$newsheety->setTitle('Summary');
		$objPHPExcel->setActiveSheetIndex($counter_index);
		$line = 1;
		$index = -1;
		#header 
		$index++;
		$cell_no = $cell[$index] . $line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue("Ledger Code Group");
		$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(60);
		
		
		$index++;
		$cell_no = $cell[$index] . $line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue("Total Debit");
		$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(40);

		$index++;
		$cell_no = $cell[$index] . $line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue("Total Credit");
		$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(40);
		
		
		foreach ($final_group_summary as $key => $value) {
			$line++;
			# code...
			$index = -1;
		
			$index++;
			$cell_no = $cell[$index] . $line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($key);
		
		
			$index++;
			$cell_no = $cell[$index] . $line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($value['TOT_DEBIT']);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getNumberFormat()->setFormatCode('#,###,##0.00');
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getNumberFormat()->applyFromArray(
				array(
					'code' => PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE
				)
			);
			$index++;
			$cell_no = $cell[$index] . $line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($value['TOT_CREDIT']);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getNumberFormat()->setFormatCode('#,###,##0.00');
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getNumberFormat()->applyFromArray(
				array(
					'code' => PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE
				)
			);
		}
		#normal code below 
		$objWriter->save($outputFileName);
		$objPHPExcel->disconnectWorksheets();
		header("location:" . $outputFileName);
			}
}


?>