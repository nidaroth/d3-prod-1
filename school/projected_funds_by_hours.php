<?php 

function fetch_data($cond,$ledger_cond,$campus_id,$format){
	global $db;
		///PREREQUISIT FOR ATTENDANCE 
		$result = array();
		$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PRESENT = 1");
		$present_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];

		$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ABSENT = 1");
		$absent_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];

		$excluded_att_code  = "";
		$exc_att_code_arr = array();
		$res_exc_att_code = $db->Execute("select M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND CANCELLED = 1");
		while (!$res_exc_att_code->EOF) {
			$exc_att_code_arr[] = $res_exc_att_code->fields['PK_ATTENDANCE_CODE'];
			$res_exc_att_code->MoveNext();
		}

		
		//END OF PREQ
		
	$query = "SELECT M_ENROLLMENT_STATUS.DESCRIPTION ,S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT,S_STUDENT_DISBURSEMENT.PK_PAYMENT_BATCH_DETAIL,S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_DETAIL as D2,S_STUDENT_FINANCIAL.EFC_NO,S_STUDENT_DISBURSEMENT.PK_STUDENT_ENROLLMENT,S_STUDENT_DISBURSEMENT.PK_STUDENT_MASTER, S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT,CONCAT(LAST_NAME,', ',FIRST_NAME,' ', SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) AS NAME, S_STUDENT_DISBURSEMENT.ACADEMIC_YEAR, S_STUDENT_DISBURSEMENT.ACADEMIC_PERIOD, IF(DISBURSEMENT_DATE = '0000-00-00','', DATE_FORMAT(DISBURSEMENT_DATE, '%m/%d/%Y' )) AS DISBURSEMENT_DATE, DISBURSEMENT_AMOUNT, DISBURSEMENT_STATUS ,IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, SSN, M_CAMPUS_PROGRAM.CODE as PROGRAM_CODE, IF(EXPECTED_GRAD_DATE = '0000-00-00','', DATE_FORMAT(EXPECTED_GRAD_DATE, '%m/%d/%Y' )) AS EXPECTED_GRAD_DATE, IF(LDA = '0000-00-00','', DATE_FORMAT(LDA, '%m/%d/%Y' )) AS LDA_DATE,  STUDENT_STATUS , S_PAYMENT_BATCH_MASTER.BATCH_NO , M_BATCH_STATUS.BATCH_STATUS,S_STUDENT_DISBURSEMENT.HOURS_REQUIRED
	FROM  
	S_STUDENT_DISBURSEMENT 
	LEFT JOIN S_PAYMENT_BATCH_DETAIL ON
	S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_DETAIL = S_STUDENT_DISBURSEMENT.PK_PAYMENT_BATCH_DETAIL
	LEFT JOIN S_PAYMENT_BATCH_MASTER ON
		S_PAYMENT_BATCH_MASTER.PK_PAYMENT_BATCH_MASTER = S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_MASTER
	LEFT JOIN M_BATCH_STATUS ON
		M_BATCH_STATUS.PK_BATCH_STATUS = S_PAYMENT_BATCH_MASTER.PK_BATCH_STATUS
	LEFT JOIN M_AWARD_YEAR ON
		M_AWARD_YEAR.PK_AWARD_YEAR = S_STUDENT_DISBURSEMENT.PK_AWARD_YEAR
	LEFT JOIN M_DISBURSEMENT_STATUS ON M_DISBURSEMENT_STATUS.PK_DISBURSEMENT_STATUS = S_STUDENT_DISBURSEMENT.PK_DISBURSEMENT_STATUS
	LEFT JOIN S_STUDENT_FINANCIAL ON S_STUDENT_FINANCIAL.PK_STUDENT_ENROLLMENT=S_STUDENT_DISBURSEMENT.PK_STUDENT_ENROLLMENT AND S_STUDENT_FINANCIAL.ACADEMIC_YEAR=S_STUDENT_DISBURSEMENT.ACADEMIC_YEAR,  
	S_STUDENT_MASTER, S_STUDENT_ENROLLMENT 
	LEFT JOIN S_STUDENT_ACADEMICS on S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER
	LEFT JOIN M_STUDENT_STATUS On M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM
	LEFT JOIN M_ENROLLMENT_STATUS ON M_ENROLLMENT_STATUS.PK_ENROLLMENT_STATUS = S_STUDENT_ENROLLMENT.PK_ENROLLMENT_STATUS
	WHERE 
	S_STUDENT_DISBURSEMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
	S_STUDENT_DISBURSEMENT.PK_STUDENT_ENROLLMENT IN (SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS IN ($campus_id) ) AND 
	S_STUDENT_DISBURSEMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND M_DISBURSEMENT_STATUS.PK_DISBURSEMENT_STATUS = 2 AND IS_ACTIVE_ENROLLMENT=1";

	$res_ledger = $db->Execute("SELECT PK_AR_LEDGER_CODE,CODE FROM M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 $ledger_cond ORDER BY CODE ASC ");



	while (!$res_ledger->EOF) {
		$PK_AR_LEDGER_CODE = $res_ledger->fields['PK_AR_LEDGER_CODE'];
		// if($res_ledger->fields['CODE']=="Cash"){
		// echo $query . " $cond AND S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC, STUDENT_ID ASC,DISBURSEMENT_DATE ASC ";
		// exit;
		// }
		$res_disp = $db->Execute($query . " $cond AND S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' GROUP BY S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC, STUDENT_ID ASC,DISBURSEMENT_DATE ASC ");
			while (!$res_disp->EOF) 
			{
							$PK_STUDENT_ENROLLMENT = $res_disp->fields['PK_STUDENT_ENROLLMENT'];
							$PK_STUDENT_MASTER = $res_disp->fields['PK_STUDENT_MASTER'];
							$SCHEDULED_HOURS = $res_disp->fields['HOURS_REQUIRED'];

							$res_campus = $db->Execute("SELECT CAMPUS_CODE FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS AND PK_STUDENT_ENROLLMENT > 0  $campus_cond1 ");

							$SSN = $res_disp->fields['SSN'];
							if ($SSN != '') {
								$SSN 	 = my_decrypt($_SESSION['PK_ACCOUNT'], $SSN);
								$SSN_ORG = $SSN;
								$SSN_ARR = explode("-", $SSN);
								$SSN 	 = 'xxx-xx-' . $SSN_ARR[2];
							}

							#get attendance code & etc 
							///START OF ATTENADANCE SQLS


							$stud_cond 	= " AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ";
							$tc_cond	= " AND S_STUDENT_CREDIT_TRANSFER.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ";

							$complete_cond = " AND S_COURSE_OFFERING_SCHEDULE_DETAIL.COMPLETED = 1  ";
							$att_com_cond  = " AND S_STUDENT_ATTENDANCE.COMPLETED = 1 ";


							$res_att = $db->Execute("SELECT SUM(ATTENDANCE_HOURS) as ATTENDANCE_HOURS FROM S_STUDENT_SCHEDULE, S_STUDENT_ATTENDANCE WHERE PK_SCHEDULE_TYPE = 1 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE  $att_com_cond $stud_cond"); //Ticket # 1247

							$res_ns = $db->Execute("SELECT SUM(ATTENDANCE_HOURS) as ATTENDANCE_HOURS FROM S_STUDENT_SCHEDULE, S_STUDENT_ATTENDANCE WHERE PK_SCHEDULE_TYPE = 2 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE $stud_cond "); //Ticket # 1247

							$cond1 = "";
							$res_balance = $db->Execute("select SUM(CREDIT) as CREDIT, SUM(DEBIT) as DEBIT from S_STUDENT_LEDGER WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND (PK_PAYMENT_BATCH_DETAIL > 0 OR PK_MISC_BATCH_DETAIL > 0 OR PK_TUITION_BATCH_DETAIL > 0 ) AND PK_STUDENT_ENROLLMENT = ".$PK_STUDENT_ENROLLMENT);
				
							$BALANCE = $res_balance->fields['DEBIT'] - $res_balance->fields['CREDIT'];
							if($format==1)
							{
									$remaining=number_format_value_checker(($SCHEDULED_HOURS-($res_att->fields['ATTENDANCE_HOURS'] + $res_ns->fields['ATTENDANCE_HOURS'])), 2);
									$rm_status=0;
									if($remaining>0){
										$result['incomplete'][$res_ledger->fields['CODE']][]=array(
											'CAMPUS'=>$res_campus->fields['CAMPUS_CODE'],
											'NAME'=>$res_disp->fields['NAME'],
											'SSN'=>$SSN_ORG,
											'EFC_NO'=>$res_disp->fields['EFC_NO'],
											'STUDENT_STATUS'=>$res_disp->fields['STUDENT_STATUS'],
											'PROGRAM_CODE'=>$res_disp->fields['PROGRAM_CODE'],
											'BEGIN_DATE_1'=>$res_disp->fields['BEGIN_DATE_1'],
											'EXPECTED_GRAD_DATE'=>$res_disp->fields['EXPECTED_GRAD_DATE'],
											'LDA_DATE'=>$res_disp->fields['LDA_DATE'],
											'LDA_DATE'=>$res_disp->fields['LDA_DATE'],
											'AR_BALANCE'=>number_format_value_checker($BALANCE, 2),
											'DISBURSEMENT_DATE'=>$res_disp->fields['DISBURSEMENT_DATE'],
											'ACADEMIC_YEAR'=>$res_disp->fields['ACADEMIC_YEAR'],
											'ACADEMIC_PERIOD'=>$res_disp->fields['ACADEMIC_PERIOD'],
											'DISBURSEMENT_AMOUNT'=>$res_disp->fields['DISBURSEMENT_AMOUNT'],
											'HOURS_COMPLETED'=>number_format_value_checker(($res_att->fields['ATTENDANCE_HOURS'] + $res_ns->fields['ATTENDANCE_HOURS']), 2),
											'HOURS_REQUIRED'=>number_format_value_checker($SCHEDULED_HOURS, 2),
											'HOURS_REMAINING'=> number_format_value_checker(($SCHEDULED_HOURS-($res_att->fields['ATTENDANCE_HOURS'] + $res_ns->fields['ATTENDANCE_HOURS'])), 2)
											);
									}else{
										$result['complete'][$res_ledger->fields['CODE']][]=array(
											'CAMPUS'=>$res_campus->fields['CAMPUS_CODE'],
											'NAME'=>$res_disp->fields['NAME'],
											'SSN'=>$SSN_ORG,
											'EFC_NO'=>$res_disp->fields['EFC_NO'],
											'STUDENT_STATUS'=>$res_disp->fields['STUDENT_STATUS'],
											'PROGRAM_CODE'=>$res_disp->fields['PROGRAM_CODE'],
											'BEGIN_DATE_1'=>$res_disp->fields['BEGIN_DATE_1'],
											'EXPECTED_GRAD_DATE'=>$res_disp->fields['EXPECTED_GRAD_DATE'],
											'LDA_DATE'=>$res_disp->fields['LDA_DATE'],
											'LDA_DATE'=>$res_disp->fields['LDA_DATE'],
											'AR_BALANCE'=>number_format_value_checker($BALANCE, 2),
											'DISBURSEMENT_DATE'=>$res_disp->fields['DISBURSEMENT_DATE'],
											'ACADEMIC_YEAR'=>$res_disp->fields['ACADEMIC_YEAR'],
											'ACADEMIC_PERIOD'=>$res_disp->fields['ACADEMIC_PERIOD'],
											'DISBURSEMENT_AMOUNT'=>$res_disp->fields['DISBURSEMENT_AMOUNT'],
											'HOURS_COMPLETED'=>number_format_value_checker(($res_att->fields['ATTENDANCE_HOURS'] + $res_ns->fields['ATTENDANCE_HOURS']), 2),
											'HOURS_REQUIRED'=>number_format_value_checker($SCHEDULED_HOURS, 2),
											'HOURS_REMAINING'=> number_format_value_checker(($SCHEDULED_HOURS-($res_att->fields['ATTENDANCE_HOURS'] + $res_ns->fields['ATTENDANCE_HOURS'])), 2)
											);
									}
						}else{
							$result[$res_ledger->fields['CODE']][]=array(
								'CAMPUS'=>$res_campus->fields['CAMPUS_CODE'],
								'NAME'=>$res_disp->fields['NAME'],
								'SSN'=>$SSN_ORG,
								'EFC_NO'=>$res_disp->fields['EFC_NO'],
								'STUDENT_STATUS'=>$res_disp->fields['STUDENT_STATUS'],
								'PROGRAM_CODE'=>$res_disp->fields['PROGRAM_CODE'],
								'BEGIN_DATE_1'=>$res_disp->fields['BEGIN_DATE_1'],
								'EXPECTED_GRAD_DATE'=>$res_disp->fields['EXPECTED_GRAD_DATE'],
								'LDA_DATE'=>$res_disp->fields['LDA_DATE'],
								'LDA_DATE'=>$res_disp->fields['LDA_DATE'],
								'AR_BALANCE'=>number_format_value_checker($BALANCE, 2),
								'DISBURSEMENT_DATE'=>$res_disp->fields['DISBURSEMENT_DATE'],
								'ACADEMIC_YEAR'=>$res_disp->fields['ACADEMIC_YEAR'],
								'ACADEMIC_PERIOD'=>$res_disp->fields['ACADEMIC_PERIOD'],
								'DISBURSEMENT_AMOUNT'=>$res_disp->fields['DISBURSEMENT_AMOUNT'],
								'HOURS_COMPLETED'=>number_format_value_checker(($res_att->fields['ATTENDANCE_HOURS'] + $res_ns->fields['ATTENDANCE_HOURS']), 2),
								'HOURS_REQUIRED'=>number_format_value_checker($SCHEDULED_HOURS, 2),
								'HOURS_REMAINING'=> number_format_value_checker(($SCHEDULED_HOURS-($res_att->fields['ATTENDANCE_HOURS'] + $res_ns->fields['ATTENDANCE_HOURS'])), 2)
								);
						}
							
							$res_disp->MoveNext();


			}

			$res_ledger->MoveNext();
		}


return $result;
}
if (!empty($_POST)) {
	$cond = "";
	if ($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '') {
		$ST = date("Y-m-d", strtotime($_POST['START_DATE']));
		$ET = date("Y-m-d", strtotime($_POST['END_DATE']));
		$cond .= " AND DISBURSEMENT_DATE BETWEEN '$ST' AND '$ET' ";
	} else if ($_POST['START_DATE'] != '') {
		$ST = date("Y-m-d", strtotime($_POST['START_DATE']));
		$cond .= " AND DISBURSEMENT_DATE >= '$ST' ";
	} else if ($_POST['END_DATE'] != '') {
		$ET = date("Y-m-d", strtotime($_POST['END_DATE']));
		$cond .= " AND DISBURSEMENT_DATE <= '$ET' ";
	}

	/** DIAM - 601 **/
	$INCLUDE_ALL_LEADS = "No";
	if ($_POST['INCLUDE_ALL_LEADS'] == 1) {
		$INCLUDE_ALL_LEADS = "Yes";
	}

	if (!empty($_POST['PK_STUDENT_STATUS'])) {
		//$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS in (".implode(",",$_POST['PK_STUDENT_STATUS']).") ";
		$sts = implode(",", $_POST['PK_STUDENT_STATUS']);
	} else {
		$sts = "";
		$res_type = $db->Execute("select PK_STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by STUDENT_STATUS ASC"); // remove - AND (ADMISSIONS = 0) - 13 June 2023 - DIAM-635
		while (!$res_type->EOF) {
			if ($sts != '')
				$sts .= ',';
			$sts .= $res_type->fields['PK_STUDENT_STATUS'];
			$res_type->MoveNext();
		}

		// if($sts != '')
		// 	$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS in (".$sts.") ";
	}

	if ($_POST['INCLUDE_ALL_LEADS'] == 1) {
		$sts = "";
		$res_type = $db->Execute("select PK_STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by STUDENT_STATUS ASC"); // remove - AND (ADMISSIONS = 1) - 13 June 2023 - DIAM-635
		while (!$res_type->EOF) {
			if ($sts != '') {
				$sts .= ',';
			}
			$sts .= $res_type->fields['PK_STUDENT_STATUS'];
			$res_type->MoveNext();
		}
		// if($sts != '')
		// 	$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS in (".$sts.") ";
	}

	if (!empty($_POST['PK_STUDENT_STATUS']) || $_POST['INCLUDE_ALL_LEADS'] == 1) {
		$final_sts = implode(',', array_unique(explode(',', $sts)));
		$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS in (" . $final_sts . ") ";
		//echo $cond;exit;
	}
	/** End DIAM - 601 **/

	$ledger_cond = "";
	if (!empty($_POST['PK_AR_LEDGER_CODE'])) {
		$ledger_cond = " AND PK_AR_LEDGER_CODE in (" . implode(",", $_POST['PK_AR_LEDGER_CODE']) . ") ";
	}

	if (!empty($_POST['PK_CAMPUS_PROGRAM'])) {
		$cond .= " AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM in (" . implode(",", $_POST['PK_CAMPUS_PROGRAM']) . ") ";
	}

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

	if ($_POST['FORMAT'] == 1) {
		/////////////////////////////////////////////////////////////////
		$projected_fund_data=fetch_data($cond,$ledger_cond,$campus_id,$_POST['FORMAT']);
		
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
				$this->SetY(5);
				$this->SetX(55);
				$this->SetTextColor(000, 000, 000);
				$this->MultiCell(55, 5, $res->fields['SCHOOL_NAME'], 0, 'L', 0, 0, '', '', true);

				$this->SetFont('helvetica', 'I', 20);
				$this->SetY(8);
				$this->SetX(185);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(104, 8, "Projected Funds", 0, false, 'R', 0, '', 0, false, 'M', 'L');


				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(180, 13, 290, 13, $style);

				$this->SetFont('helvetica', 'I', 8);
				$this->SetY(14);
				$this->SetX(140);
				$this->SetTextColor(000, 000, 000);
				//$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				$this->MultiCell(150, 5, "Campus(es): " . $campus_name, 0, 'R', 0, 0, '', '', true);

				$str = "";
				if (empty($_POST['PK_STUDENT_STATUS'])) {
					$str = "All Student Statuses";
				} else {
					$str = "";
					$res_type = $db->Execute("select STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND PK_STUDENT_STATUS IN (" . implode(",", $_POST['PK_STUDENT_STATUS']) . ") order by STUDENT_STATUS ASC");
					while (!$res_type->EOF) {
						if ($str != '')
							$str .= ',';
						$str .= $res_type->fields['STUDENT_STATUS'];
						$res_type->MoveNext();
					}
					$str = "Status(es): " . $str;
				}

				$str = substr($str, 0, 95);
				if (strlen($str) >= 95) {
					$str .= '...';
				}
				$this->SetY(18);
				$this->SetX(130);
				$this->SetTextColor(000, 000, 000);
				$this->MultiCell(160, 8, $str, 0, 'R', 0, 0, '', '', true);

				$str = "";
				if (empty($_POST['PK_AR_LEDGER_CODE'])) {
					$str = "All Ledger Codes";
				} else {
					$str = "";
					$res_type_all = $db->Execute("select PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION from M_AR_LEDGER_CODE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 ");

					$PK_AR_LEDGER_CODE_SELECTED = implode(",", $_POST['PK_AR_LEDGER_CODE']);
					$res_type = $db->Execute("select CODE from M_AR_LEDGER_CODE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 AND PK_AR_LEDGER_CODE IN ($PK_AR_LEDGER_CODE_SELECTED) ");

					if ($res_type_all->RecordCount() == $res_type->RecordCount())
						$str = "All Ledger Codes";
					else {
						while (!$res_type->EOF) {
							if ($str != '')
								$str .= ', ';
							$str .= $res_type->fields['CODE'];
							$res_type->MoveNext();
						}
						if ($str != '')
							$str = "Ledger Code(s): " . $str;
					}
				}
				$str = substr($str, 0, 95);
				if (strlen($str) >= 95) {
					$str .= '...';
				}
				$this->SetY(22);
				$this->SetX(140);
				$this->SetTextColor(000, 000, 000);
				$this->MultiCell(150, 15, $str, 0, 'R', 0, 0, '', '', true);

				$str = "";
				if ($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '')
					$str = " " . $_POST['START_DATE'] . ' - ' . $_POST['END_DATE'];
				else if ($_POST['START_DATE'] != '')
					$str = " from " . $_POST['START_DATE'];
				else if ($_POST['END_DATE'] != '')
					$str = " to " . $_POST['END_DATE'];

				//$this->SetFont('helvetica', 'I', 10);
				$this->SetY(28);
				$this->SetX(140);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(150, 5, "Disbursement Dates: " . $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
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
		$pdf->SetMargins(7, 35, 7);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, 15);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->setLanguageArray($l);
		$pdf->setFontSubsetting(true);
		$pdf->SetFont('helvetica', '', 7, '', true);
		$pdf->AddPage();

		$total 	= 0;
		$txt 	= '';
		$summary_arr = [];

		foreach ($projected_fund_data['complete'] as $key=>$items) {
			$sub_total = 0;

		if (count($items)>0) {
				$txt .= '<br><br>
				<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<thead>
							<tr>
								<td width="50%" ><h1><i>' . $key . '</i></h1></td>
								<td width="50%" align="right"><h2><i>Required Hours Completed</i></h2></td>
							</tr>
							<tr>
								<td width="26%" align="center" style="border-top:1px solid #000;border-left:1px solid #000; font-size:28px;" ><b>Student</b> </td>
								<td width="29%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;font-size:28px;" ><b>Current Enrollment</b> </td>
								<td width="25%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;font-size:28px;" ><b>Disbursement</b> </td>
								<td width="20%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;font-size:28px;" ><b>Attendance Hours</b> </td>
							</tr>
							<tr nobr="true" >
								<td width="13%" style="border-left:1px solid #000;border-bottom:1px solid #000;" ><b>Name</b></td>
								<td width="8%" style="border-bottom:1px solid #000;" ><b>SSN</b></td>
								<td width="5%" style="border-bottom:1px solid #000;" ><b>EFC/SAI</b></td>

								<td width="5%" style="border-left:1px solid #000;border-bottom:1px solid #000;"><b>Status</b></td>
								<td width="5%" style="border-bottom:1px solid #000;" ><b>Program</b></td>
								<td width="6%" style="border-bottom:1px solid #000;" ><b>First Term</b></td>
								<td width="6%" style="border-bottom:1px solid #000;" ><b>Exp. Grad</b></td>
								<td width="7%" style="border-bottom:1px solid #000;" ><b>LDA</b></td>

								<td width="7%" style="border-left:1px solid #000;border-bottom:1px solid #000;" ><b>AR Balance</b></td>
								<td width="7%" style="border-bottom:1px solid #000;" ><b>Date</b></td>
								<td width="2%" style="border-bottom:1px solid #000;" align="right" ><b>AY</b></td>
								<td width="2%" style="border-bottom:1px solid #000;" align="right" ><b>AP</b></td>
								<td width="7%" align="right" style="border-right:1px solid #000;border-bottom:1px solid #000;" ><b>Amount</b></td>

								<td width="7%" align="right" style="border-bottom:1px solid #000;" ><b>Completed</b></td>
								<td width="7%" align="right" style="border-bottom:1px solid #000;" ><b>Required</b></td>
								<td width="6%" align="right" style="border-right:1px solid #000;border-bottom:1px solid #000;" ><b>Remaining</b></td>

							</tr>
						</thead>';

						$i=1;
						foreach ($items as $row){

							$txt 	.= '<tr nobr="true">
							<td width="13%"> '.$i.'&nbsp;&nbsp;&nbsp;' . $row['NAME'].'</td>
							<td width="8%">' . $row['SSN'] . '</td>
							<td width="5%">' . $row['EFC_NO'] . '</td>
							<td width="5%">' . $row['STUDENT_STATUS'] . '</td>
							<td width="5%">' . $row['PROGRAM_CODE'] . '</td>
							<td width="6%">' . $row['BEGIN_DATE_1'] . '</td>
							<td width="6%">' . $row['EXPECTED_GRAD_DATE'] . '</td>
							<td width="7%">' . $row['LDA_DATE'] . '</td>
							<td width="7%">$'.$row['AR_BALANCE'].'</td>					
							<td width="7%">' . $row['DISBURSEMENT_DATE'] . '</td>
							<td width="2%" align="right">' . $row['ACADEMIC_YEAR'] . '</td>
							<td width="2%" align="right">' . $row['ACADEMIC_PERIOD'] . '</td>
							<td width="7%" align="right">$ ' . $row['DISBURSEMENT_AMOUNT'] . '</td>
							<td width="7%" align="right">' .$row['HOURS_COMPLETED']. '</td>
							<td width="7%" align="right">' . $row['HOURS_REQUIRED'] . '</td>
							<td width="6%" align="right">' . $row['HOURS_REMAINING'] . '</td>
						</tr>';
					$sub_total += $row['DISBURSEMENT_AMOUNT'];
					$i++;

						}
				$total += $sub_total;
				$txt 	.= '<tr>
							<td width="60%"></td>
							<td width="40%" style="font-size:30px;" align="right"><b>' . $key . '&nbsp;&nbsp;&nbsp; $ ' . number_format_value_checker($sub_total, 2) . '</b></td>
						</tr>
					</table>';
				$summary_arr[$key] = $sub_total;
			}
			
			//$res_ledger->MoveNext();
		}


		$total 	= 0;
		$summary_arr = [];
		foreach ($projected_fund_data['incomplete'] as $key=>$items) {
			$sub_total = 0;

			if (count($items)>0) {
					$txt .= '<br><br>
					<table border="0" cellspacing="0" cellpadding="3" width="100%">
							<thead>
								<tr>
									<td width="50%" ><h1><i>' . $key . '</i></h1></td>
									<td width="50%" align="right"><h2><i>Required Hours Incompleted</i></h2></td>
								</tr>
								<tr>
									<td width="26%" align="center" style="border-top:1px solid #000;border-left:1px solid #000; font-size:28px;" ><b>Student</b> </td>
									<td width="29%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;font-size:28px;" ><b>Current Enrollment</b> </td>
									<td width="25%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;font-size:28px;" ><b>Disbursement</b> </td>
									<td width="20%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;font-size:28px;" ><b>Attendance Hours</b> </td>
								</tr>
								<tr nobr="true" >
									<td width="13%" style="border-left:1px solid #000;border-bottom:1px solid #000;" ><b>Name</b></td>
									<td width="8%" style="border-bottom:1px solid #000;" ><b>SSN</b></td>
									<td width="5%" style="border-bottom:1px solid #000;" ><b>EFC/SAI</b></td>
	
									<td width="5%" style="border-left:1px solid #000;border-bottom:1px solid #000;"><b>Status</b></td>
									<td width="5%" style="border-bottom:1px solid #000;" ><b>Program</b></td>
									<td width="6%" style="border-bottom:1px solid #000;" ><b>First Term</b></td>
									<td width="6%" style="border-bottom:1px solid #000;" ><b>Exp. Grad</b></td>
									<td width="7%" style="border-bottom:1px solid #000;" ><b>LDA</b></td>
	
									<td width="7%" style="border-left:1px solid #000;border-bottom:1px solid #000;" ><b>AR Balance</b></td>
									<td width="7%" style="border-bottom:1px solid #000;" ><b>Date</b></td>
									<td width="2%" style="border-bottom:1px solid #000;" align="right" ><b>AY</b></td>
									<td width="2%" style="border-bottom:1px solid #000;" align="right" ><b>AP</b></td>
									<td width="7%" align="right" style="border-right:1px solid #000;border-bottom:1px solid #000;" ><b>Amount</b></td>
	
									<td width="7%" align="right" style="border-bottom:1px solid #000;" ><b>Completed</b></td>
									<td width="7%" align="right" style="border-bottom:1px solid #000;" ><b>Required</b></td>
									<td width="6%" align="right" style="border-right:1px solid #000;border-bottom:1px solid #000;" ><b>Remaining</b></td>
	
								</tr>
							</thead>';
	
							$i=1;
							foreach ($items as $row){
	
								$txt 	.= '<tr nobr="true">
								<td width="13%"> '.$i.'&nbsp;&nbsp;&nbsp;' . $row['NAME'].'</td>
								<td width="8%">' . $row['SSN'] . '</td>
								<td width="5%">' . $row['EFC_NO'] . '</td>
								<td width="5%">' . $row['STUDENT_STATUS'] . '</td>
								<td width="5%">' . $row['PROGRAM_CODE'] . '</td>
								<td width="6%">' . $row['BEGIN_DATE_1'] . '</td>
								<td width="6%">' . $row['EXPECTED_GRAD_DATE'] . '</td>
								<td width="7%">' . $row['LDA_DATE'] . '</td>
								<td width="7%">$'.$row['AR_BALANCE'].'</td>					
								<td width="7%">' . $row['DISBURSEMENT_DATE'] . '</td>
								<td width="2%" align="right">' . $row['ACADEMIC_YEAR'] . '</td>
								<td width="2%" align="right">' . $row['ACADEMIC_PERIOD'] . '</td>
								<td width="7%" align="right">$ ' . $row['DISBURSEMENT_AMOUNT'] . '</td>
								<td width="7%" align="right">' .$row['HOURS_COMPLETED']. '</td>
								<td width="7%" align="right">' . $row['HOURS_REQUIRED'] . '</td>
								<td width="6%" align="right">' . $row['HOURS_REMAINING'] . '</td>
							</tr>';
						$sub_total += $row['DISBURSEMENT_AMOUNT'];
						$i++;
	
							}
					$total += $sub_total;
					$txt 	.= '<tr>
								<td width="60%"></td>
								<td width="40%" style="font-size:30px;" align="right"><b>' . $key . '&nbsp;&nbsp;&nbsp; $ ' . number_format_value_checker($sub_total, 2) . '</b></td>
							</tr>
						</table>';
					$summary_arr[$key] = $sub_total;
				}
				
				//$res_ledger->MoveNext();
			}
	



		

		// $fp_test = fopen('temp/test_13405.html', 'w');
		// fwrite($fp_test, $txt);

		//echo $txt;exit;

		$pdf->writeHTML($txt, $ln = true, $fill = false, $reseth = true, $cell = true, $align = '');

		$file_name = 'Projected Funds Attendance.pdf';
		$file_name = str_replace(
			pathinfo($file_name, PATHINFO_FILENAME),
			pathinfo($file_name, PATHINFO_FILENAME) . "_" . $_SESSION['PK_USER'] . "_" . floor(microtime(true) * 1000),
			$file_name
		);


		$pdf->Output('temp/' . $file_name, 'FD');
		return $file_name;
	} else if ($_POST['FORMAT'] == 2) {

		$projected_fund_data=fetch_data($cond,$ledger_cond,$campus_id,$_POST['FORMAT']);


		//END OF PREQ
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
		$file_name 		= 'Projected Funds .xlsx';
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

		$line++;
		$index 	= -1;
		//students details
		$heading[] = 'Campus';
		$width[]   = 20;
		$heading[] = 'Ledger Code';
		$width[]   = 20;
		$heading[] = 'Student';
		$width[]   = 15;
		$heading[] = 'SSN';
		$width[]   = 15;
		$heading[] = 'EFC/SAI';
		$width[]   = 15;
		//enrrollment details
		$heading[] = 'Status';
		$width[]   = 15;
		$heading[] = 'Program';
		$width[]   = 20;
		$heading[] = 'First Term';
		$width[]   = 15;
		$heading[] = 'Exp. Grad';
		$width[]   = 15;
		$heading[] = 'LDA';
		$width[]   = 15;
		// Disbursement detail
		$heading[] = 'AR Balance';
		$width[]   = 15;
		$heading[] = 'Disbursement Date';
		$width[]   = 15;
		$heading[] = 'AY';
		$width[]   = 15;
		$heading[] = 'AP';
		$width[]   = 15;
		$heading[] = 'Disbursement Amount';
		$width[]   = 15;
		// required hours completed
		$heading[] = 'Hours Completed';
		$width[]   = 15;
		$heading[] = 'Hours Required';
		$width[]   = 15;
		$heading[] = 'Hours Remaining';
		$width[]   = 15;


		$i = 0;
		foreach ($heading as $title) {
			$index++;
			$cell_no = $cell[$index] . $line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);

			$i++;
		}

		foreach ($projected_fund_data as $key=>$items) 
		{
				if (count($items)>0) 
				{				
					foreach ($items as $row){
									$line++;
									$index = -1;

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($row['CAMPUS']);

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($key);

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($row['NAME']);

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($row['SSN']);

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($row['EFC_NO']);

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($row['STUDENT_STATUS']);

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($row['PROGRAM_CODE']);

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($row['BEGIN_DATE_1']);

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($row['EXPECTED_GRAD_DATE']);

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($row['LDA_DATE']);

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($row['AR_BALANCE']);

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($row['DISBURSEMENT_DATE']);

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($row['ACADEMIC_YEAR']);

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($row['ACADEMIC_PERIOD']);

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($row['DISBURSEMENT_AMOUNT']);

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($row['HOURS_COMPLETED']);

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($row['HOURS_REQUIRED']);

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($row['HOURS_REMAINING']);
						}
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
		$objWriter->save($outputFileName);
		$objPHPExcel->disconnectWorksheets();
		header("location:" . $outputFileName);
	}


	
}
?>
