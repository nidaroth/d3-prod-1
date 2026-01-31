<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/bppe_report_setup.php");
require_once("check_access.php");
require_once("get_department_from_t.php");

$res = $db->Execute("SELECT BPPE FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
if($res->fields['BPPE'] == 0 || $res->fields['BPPE'] == '') {
	header("location:../index");
	exit;
}

if(check_access('MANAGEMENT_ACCREDITATION') == 0 ){
	header("location:../index");
	exit;
}
$msg = '';	
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$REPORT_TYPE = "";
	$REPORT_NAME = "";
	if($_POST['REPORT_TYPE'] == 1) {
		$REPORT_TYPE 	= "Completion Rates";
		$REPORT_NAME 	= "BPPE Completion Rates"; // DIAM-1061 -->
		$SP_NAME	 	= "COMP30001";
		$REPORT_TITLE 	= "Completion Rates (Graduation Rates)";
	} else if($_POST['REPORT_TYPE'] == 2) {
		$REPORT_TYPE 	= "Placement Rates";
		$REPORT_NAME 	= "BPPE Job Placement Rates"; // DIAM-1061 -->
		$SP_NAME	 	= "COMP30002";
		$REPORT_TITLE 	= "";
	} else if($_POST['REPORT_TYPE'] == 3) {
		$REPORT_TYPE = "Federal Student Loan Debt - Graduated Students";
		$REPORT_NAME = "BPPE Federal Student Loan Debt - Graduated Students"; // DIAM-1061 -->
		$SP_NAME	 = "COMP30006";
	} else if($_POST['REPORT_TYPE'] == 4) {
		$REPORT_TYPE = "Federal Student Loan Debt - Enrolled Students";
		$REPORT_NAME = "BPPE Federal Student Loan Debt - Enrolled Students"; // DIAM-1061 -->
		$SP_NAME	 = "COMP30005";
	} else if($_POST['REPORT_TYPE'] == 5) {
		$REPORT_TYPE = "Salary and Wage Information";
		$REPORT_NAME = "BPPE Salary and Wage Information"; // DIAM-1061 -->
		$SP_NAME	 = "COMP30004";
	} else if($_POST['REPORT_TYPE'] == 6) {
		$REPORT_TYPE = "License Rates";
		$REPORT_NAME = "BPPE License Examination Passage Rates"; // DIAM-1061 -->
		$SP_NAME	 = "COMP30003";
	}
	
	if($_POST['START_DATE'] != ''){
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
	}
	if($_POST['END_DATE'] != ''){
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
	}
	
	if($_POST['FORMAT'] == 1) {
		$browser = '';
		if(stripos($_SERVER['HTTP_USER_AGENT'],"chrome") != false)
			$browser =  "chrome";
		else if(stripos($_SERVER['HTTP_USER_AGENT'],"Safari") != false)
			$browser = "Safari";
		else
			$browser = "firefox";
		
		require_once '../global/mpdf/vendor/autoload.php';
		
		$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		$SCHOOL_NAME = $res->fields['SCHOOL_NAME'];
		$PDF_LOGO 	 = $res->fields['PDF_LOGO'];

		$logo = "";
		if($PDF_LOGO != '')
			$logo = '<img src="'.$PDF_LOGO.'" height="50px" />';
	
		$header = '<table width="100%" >
						<tr>
							<td width="20%" valign="top" >'.$logo.'</td>
							<td width="30%" valign="top" style="font-size:20px" >'.$SCHOOL_NAME.'</td>
							<td width="50%" valign="top" >
								<table width="100%" >
									<tr>
										<td width="100%" align="right" style="font-size:18px;border-bottom:1px solid #000;" ><b>BPPE School Performance Fact Sheet</b></td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:13px;" >'.$REPORT_NAME.'</td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:13px;" >Between: '.$_POST['START_DATE'].' - '.$_POST['END_DATE'].'</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td width="100%" align="center" colspan="3" ><b style="font-size:15px;" >'.$REPORT_TITLE.'</b></td>
						</tr>
					</table>';
					
		$timezone = $_SESSION['PK_TIMEZONE'];
		if($timezone == '' || $timezone == 0) {
			$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$timezone = $res->fields['PK_TIMEZONE'];
			if($timezone == '' || $timezone == 0)
				$timezone = 4;
		}
		
		$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
		$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$res->fields['TIMEZONE'],date_default_timezone_get());
					
		$footer = '<table width="100%" >
						<tr>
							<td width="33%" valign="top" style="font-size:10px;" ><i>'.$date.'</i></td>
							<td width="33%" valign="top" style="font-size:10px;" align="center" ><i>'.$SP_NAME.'</i></td>
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
			'default_font_size' => 9
		]);
		$mpdf->autoPageBreak = true;
		
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLFooter($footer);

		$res = $db->Execute("CALL ".$SP_NAME."(".$_SESSION['PK_ACCOUNT'].",'".$ST."','".$ET."',TRUE)");
		$BATCH_ID = $res->fields['vThisBatchID'];
	
		$db->close();
		$db->connect($db_host,'root',$db_pass,$db_name);
		
		$txt = "";
		if($_POST['REPORT_TYPE'] == 1) {			
			$PROGRAM_ARR 		= array();
			$DESC_ARR 	 		= array();
			$PROGRAM_LENGTH_ARR = array();
			$res = $db->Execute("SELECT PROGRAM_CODE, PROGRAM_DESCRIPTION, PROGRAM_LENGTH FROM S_TEMP_COMP30001 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' GROUP BY PROGRAM_CODE ORDER BY PROGRAM_CODE ASC");
			while (!$res->EOF) {
				$PROGRAM_ARR[] 			= $res->fields['PROGRAM_CODE'];
				$DESC_ARR[] 			= $res->fields['PROGRAM_DESCRIPTION'];
				$PROGRAM_LENGTH_ARR[] 	= $res->fields['PROGRAM_LENGTH'];
				
				$res->MoveNext();
			}
			
			$mpdf->AddPage();
			$i = 0;
			foreach($PROGRAM_ARR as $PROGRAM) {
				$txt .= '<table border="0" cellspacing="0" cellpadding="4" width="100%">
							<tr>
								<td width="100%"><b style="font-size:15px;">'.$PROGRAM.' - '.$DESC_ARR[$i].' - '.$PROGRAM_LENGTH_ARR[$i].'</b></td>
							</tr>
						</table>
						<table cellspacing="0" cellpadding="4" width="100%" >
							<tr>
								<td width="10%" align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>Calendar Year</i>
								</td>
								<td width="15%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>Number of Students Who Began Program</i>
								</td>
								<td width="15%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>Students Available for Graduation</i>
								</td>
								<td width="15%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>Number of On-Time Graduates</i>
								</td>
								<td width="15%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>On-Time Completion Rate</i>
								</td>
								<td width="15%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>150% Graduates</i>
								</td>
								<td width="15%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;border-right:1px solid #000;background-color:#c9d0d7;" >
									<i>150% Completion Rate</i>
								</td>
							</tr>';

				$res = $db->Execute("SELECT * FROM S_TEMP_COMP30001 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' AND PROGRAM_CODE = '$PROGRAM' ");
				while (!$res->EOF) { 
					$txt .= '<tr>
								<td align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['CALENDAR_YEAR'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['NEW_START'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['AVAILABLE_FOR_GRADUATION'].'</td>
								
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['GRADUATED'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.number_format_value_checker(($res->fields['ONTIME_COMPLETION_RATE'] * 100),2).' %</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['GRADUATED_150'].'</td>
								<td align="right" style="border-left:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;" >'.number_format_value_checker(($res->fields['150_COMPLETION_RATE'] * 100),2).' %</td>
							</tr>';
							
					$res->MoveNext();
				}
				$txt .= '</table><br /><br />';

				$i++;
			}
			$mpdf->WriteHTML($txt);
			
			$db->Execute("DELETE FROM S_TEMP_COMP30001 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' ");
		} else if($_POST['REPORT_TYPE'] == 2) {
			
			$PROGRAM_ARR 		= array();
			$DESC_ARR 	 		= array();
			$PROGRAM_LENGTH_ARR = array();
			$res = $db->Execute("SELECT PROGRAM_CODE, PROGRAM_DESCRIPTION, PROGRAM_LENGTH FROM S_TEMP_COMP30002 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' GROUP BY PROGRAM_CODE ORDER BY PROGRAM_CODE ASC");
			while (!$res->EOF) {
				$PROGRAM_ARR[] 			= $res->fields['PROGRAM_CODE'];
				$DESC_ARR[] 			= $res->fields['PROGRAM_DESCRIPTION'];
				$PROGRAM_LENGTH_ARR[] 	= $res->fields['PROGRAM_LENGTH'];
				
				$res->MoveNext();
			}
			
			$i = 0;
			foreach($PROGRAM_ARR as $PROGRAM) {
				$mpdf->AddPage();
				$txt = '<table border="0" cellspacing="0" cellpadding="4" width="100%">
							<tr>
								<td width="100%" align="center"><b style="font-size:15px;" >'.$PROGRAM.' - '.$DESC_ARR[$i].' - '.$PROGRAM_LENGTH_ARR[$i].'</b></td>
							</tr>
							
							<tr>
								<td width="100%" align="center"><b style="font-size:13px;" ><br />Job Placement Rates</b></td>
							</tr>
						</table>
						
						<table cellspacing="0" cellpadding="4" width="100%" >
							<tr>
								<td width="10%" align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>Calendar Year</i>
								</td>
								<td width="18%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>Number of Students Who Began Program</i>
								</td>
								<td width="18%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>Number of Graduates</i>
								</td>
								<td width="18%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>Graduates Available for Employment</i>
								</td>
								<td width="18%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>Graduates Employed in Field</i>
								</td>
								<td width="18%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;border-right:1px solid #000;background-color:#c9d0d7;" >
									<i>Placement Rate % Employed In Field</i>
								</td>
							</tr>';

				$res = $db->Execute("SELECT * FROM S_TEMP_COMP30002 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' AND PROGRAM_CODE = '$PROGRAM' ");
				while (!$res->EOF) { 
					$txt .= '<tr>
								<td align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['CALENDAR_YEAR'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['NEW_START'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['GRADUATES'].'</td>
								
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['AVAILABLE_FOR_PLACEMENT'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['IN_FIELD'].'</td>
								<td align="right" style="border-left:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;" >'.number_format_value_checker(($res->fields['PLACEMENT_RATE'] * 100),2).' %</td>
							</tr>';
					$res->MoveNext();
				}
				$txt .= '</table><br /><br />';
				
				//////////////////////////
				$txt .= '<table border="0" cellspacing="0" cellpadding="4" width="100%">
							<tr>
								<td width="100%" align="center"><b style="font-size:13px;" >Gainfully Employed Categories</b></td>
							</tr>
							<tr>
								<td width="100%" align="center"><b style="font-size:13px;" >Part-Time vs. Full-Time Employment</b></td>
							</tr>
						</table>
						
						<table cellspacing="0" cellpadding="4" width="100%" >
							<tr>
								<td width="10%" align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>Calendar Year</i>
								</td>
								<td width="30%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>Graduates Employed In Field 20-29 Hours Per Week</i>
								</td>
								<td width="30%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>Graduates Employed In Field at Least 30 Hours Per Week</i>
								</td>
								<td width="30%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;border-right:1px solid #000;background-color:#c9d0d7;" >
									<i>Total Graduates Employed In Field</i>
								</td>
							</tr>';

				$res = $db->Execute("SELECT * FROM S_TEMP_COMP30002 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' AND PROGRAM_CODE = '$PROGRAM' ");
				while (!$res->EOF) { 
					$txt .= '<tr>
								<td align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['CALENDAR_YEAR'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['IN_FIELD_PART_TIME'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['IN_FIELD_FULL_TIME'].'</td>
								<td align="right" style="border-left:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['TOTAL_GRADUATES_IF'].'</td>
							</tr>';
					$res->MoveNext();
				}
				$txt .= '</table><br /><br />';
				
				//////////////////////////
				$txt .= '<table border="0" cellspacing="0" cellpadding="4" width="100%">
							<tr>
								<td width="100%" align="center"><b style="font-size:13px;" >Single Position vs. Concurrent Aggregated Positions</b></td>
							</tr>
						</table>
						
						<table cellspacing="0" cellpadding="4" width="100%" >
							<tr>
								<td width="10%" align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>Calendar Year</i>
								</td>
								<td width="30%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>Graduates Employed In Field in a Single Position</i>
								</td>
								<td width="30%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>Graduates Employed In Field in Concurrent Aggregated Positions</i>
								</td>
								<td width="30%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;border-right:1px solid #000;background-color:#c9d0d7;" >
									<i>Total Graduates Employed In Field</i>
								</td>
							</tr>';

				$res = $db->Execute("SELECT * FROM S_TEMP_COMP30002 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' AND PROGRAM_CODE = '$PROGRAM' ");
				while (!$res->EOF) { 
					$txt .= '<tr>
								<td align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['CALENDAR_YEAR'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['GRADS_SINGLE_POSITION'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['GRADS_CONCURRENT_POSITIONS'].'</td>
								<td align="right" style="border-left:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['TOTAL_GRADS_POSITIONS'].'</td>
							</tr>';
					$res->MoveNext();
				}
				$txt .= '</table><br /><br />';
				
				//////////////////////////
				$txt .= '<table border="0" cellspacing="0" cellpadding="4" width="100%">
							<tr>
								<td width="100%" align="center"><b style="font-size:13px;" >Self Employed / Freelance Positions</b></td>
							</tr>
						</table>
						
						<table cellspacing="0" cellpadding="4" width="100%" >
							<tr>
								<td width="10%" align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>Calendar Year</i>
								</td>
								<td width="60%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>Graduates Employed Who Are Self-Employed or Working Freelance</i>
								</td>
								<td width="40%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;border-right:1px solid #000;background-color:#c9d0d7;" >
									<i>Total Graduates Employed In Field</i>
								</td>
							</tr>';

				$res = $db->Execute("SELECT * FROM S_TEMP_COMP30002 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' AND PROGRAM_CODE = '$PROGRAM' ");
				while (!$res->EOF) { 
					$txt .= '<tr>
								<td align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['CALENDAR_YEAR'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['SELF_EMPLOYED'].'</td>
								<td align="right" style="border-left:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['TOTAL_GRADUATES_SE'].'</td>
							</tr>';
					$res->MoveNext();
				}
				$txt .= '</table><br /><br />';
				
				//////////////////////////
				$txt .= '<table border="0" cellspacing="0" cellpadding="4" width="100%">
							<tr>
								<td width="100%" align="center"><b style="font-size:13px;" >Institutional Employment</b></td>
							</tr>
						</table>
						
						<table cellspacing="0" cellpadding="4" width="100%" >
							<tr>
								<td width="10%" align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>Calendar Year</i>
								</td>
								<td width="65%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>Graduates Employed In Field Who Are Employed by the Institution, an Employer Owned by the Institution, or an Employer who Shares Ownership with the Institution</i>
								</td>
								<td width="35%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;border-right:1px solid #000;background-color:#c9d0d7;" >
									<i>Total Graduates Employed In Field</i>
								</td>
							</tr>';

				$res = $db->Execute("SELECT * FROM S_TEMP_COMP30002 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' AND PROGRAM_CODE = '$PROGRAM' ");
				while (!$res->EOF) { 
					$txt .= '<tr>
								<td align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['CALENDAR_YEAR'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['INSTITUTIONAL_EMPLOYMENT'].'</td>
								<td align="right" style="border-left:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['TOTAL_GRADUATES_IE'].'</td>
							</tr>';
					$res->MoveNext();
				}
				$txt .= '</table><br /><br />';
				
				$mpdf->WriteHTML($txt);
				
				$i++;
			}
			
			$db->Execute("DELETE FROM S_TEMP_COMP30002 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' ");
		} else if($_POST['REPORT_TYPE'] == 3) {			

			$mpdf->AddPage();
			$i = 0;

				$txt = '<table border="0" cellspacing="0" cellpadding="4" width="100%">
						<tr>
							<td width="100%" align="center" ><b style="font-size:15px;">Federal Student Loan Debt - Graduated Students</b></td>
						</tr>
					</table>
					<table cellspacing="0" cellpadding="4" width="100%" >
						<tr>
							<td width="15%" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
								<i>Program Code</i>
							</td>
							<td width="25%" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
								<i>Program Description</i>
							</td>
							<td width="10%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
								<i>Calendar Year</i>
							</td>
							<td width="25%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
								<i>The percentage of graduates who took out federal student loans to pay for this program.</i>
							</td>
							<td width="25%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;border-right:1px solid #000;background-color:#c9d0d7;" >
								<i>The average amount of federal student loan debt of graduates who took out federal student loans at this institution.</i>
							</td>
						</tr>';

			$res = $db->Execute("SELECT * FROM S_TEMP_COMP30006 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' ");
			while (!$res->EOF) { 
				$txt .= '<tr>
							<td style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['PROGRAM_CODE'].'</td>
							<td style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['PROGRAM_DESCRIPTION'].'</td>
							<td align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['CALENDAR_YEAR'].'</td>
							<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.number_format_value_checker(($res->fields['GRADS_WITH_LOANS_PERCENTAGE'] * 100),2).' %</td>
							<td align="right" style="border-left:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;" >$ '.number_format_value_checker($res->fields['AVERAGE_DEBT'],2).' </td>
						</tr>';
				$res->MoveNext();
			}
			$txt .= '<tr>
						<td colspan="5">* Most recent three year cohort default rate may be obtained from the United States Department of Education</td>
					</tr>
				</table>';

			$mpdf->WriteHTML($txt);
			
			$db->Execute("DELETE FROM S_TEMP_COMP30006 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' ");
		} else if($_POST['REPORT_TYPE'] == 4) {			

			$mpdf->AddPage();
			$i = 0;

				$txt = '<table border="0" cellspacing="0" cellpadding="4" width="100%">
						<tr>
							<td width="100%" align="center" ><b style="font-size:15px;">Federal Student Loan Debt - Enrolled Students</b></td>
						</tr>
					</table>
					<table cellspacing="0" cellpadding="4" width="100%" >
						<tr>
							<td width="15%" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
								<i>Program Code</i>
							</td>
							<td width="25%" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
								<i>Program Description</i>
							</td>
							<td width="10%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
								<i>Calendar Year</i>
							</td>
							<td width="50%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;border-right:1px solid #000;background-color:#c9d0d7;" >
								<i>The percentage of enrolled students receiving federal student loans to pay for this program</i>
							</td>
						</tr>';

			$res = $db->Execute("SELECT * FROM S_TEMP_COMP30005 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' ");
			while (!$res->EOF) { 
				$txt .= '<tr>
							<td style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['PROGRAM_CODE'].'</td>
							<td style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['PROGRAM_DESCRIPTION'].'</td>
							<td align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['CALENDAR_YEAR'].'</td>
							<td align="right" style="border-left:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;" >'.number_format_value_checker(($res->fields['ENROLLEE_WITH_LOANS_PERCENTAGE'] * 100),2).' %</td>
						</tr>';
				$res->MoveNext();
			}
			$txt .= '<tr>
						<td colspan="4">* Most recent three year cohort default rate may be obtained from the United States Department of Education</td>
					</tr>
				</table>';

			$mpdf->WriteHTML($txt);
			
			$db->Execute("DELETE FROM S_TEMP_COMP30005 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' ");
		} else if($_POST['REPORT_TYPE'] == 5) {
			$PROGRAM_ARR 		= array();
			$DESC_ARR 	 		= array();
			$PROGRAM_LENGTH_ARR = array();
			$res = $db->Execute("SELECT PROGRAM_CODE, PROGRAM_DESCRIPTION, PROGRAM_LENGTH FROM S_TEMP_COMP30004 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' GROUP BY PROGRAM_CODE ORDER BY PROGRAM_CODE ASC");
			while (!$res->EOF) {
				$PROGRAM_ARR[] 			= $res->fields['PROGRAM_CODE'];
				$DESC_ARR[] 			= $res->fields['PROGRAM_DESCRIPTION'];
				$PROGRAM_LENGTH_ARR[] 	= $res->fields['PROGRAM_LENGTH'];
				
				$res->MoveNext();
			}
			
			$txt = '<table border="0" cellspacing="0" cellpadding="4" width="100%">=
							<tr>
								<td width="100%" align="center" ><b style="font-size:15px;">Salary and Wage Information</b><br /></td>
							</tr>
						</table>';
			
			$mpdf->AddPage();
			$i = 0;
			foreach($PROGRAM_ARR as $PROGRAM) {
				$txt .= '<table border="0" cellspacing="0" cellpadding="4" width="100%">
							<tr>
								<td width="100%"><b style="font-size:15px;">'.$PROGRAM.' - '.$DESC_ARR[$i].' - '.$PROGRAM_LENGTH_ARR[$i].'</b></td>
							</tr>
						</table>
						<table cellspacing="0" cellpadding="4" width="100%" >
							<tr>
								<td width="4%" align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>Calendar Year</i>
								</td>
								<td width="4%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>Grads Avail for Employment</i>
								</td>
								<td width="4%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>Grads Employed In Field</i>
								</td>
								<td width="4%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>$0 - $5k</i>
								</td>
								<td width="4%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>$5k - $10k</i>
								</td>
								<td width="4%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>$10k - $15k</i>
								</td>
								<td width="4%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>$15k - $20k</i>
								</td>
								<td width="4%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>$20k - $25k</i>
								</td>
								<td width="4%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>$25k - $30k</i>
								</td>
								<td width="4%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>$30k - $35k</i>
								</td>
								<td width="4%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>$35k - $405k</i>
								</td>
								<td width="4%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>$40k - $45k</i>
								</td>
								<td width="4%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>$45k - $50k</i>
								</td>
								<td width="4%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>$50k - $55k</i>
								</td>
								<td width="4%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>$55k - $60k</i>
								</td>
								<td width="4%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>$60k - $65k</i>
								</td>
								<td width="4%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>$65k - $70k</i>
								</td>
								<td width="4%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>$70k - $75k</i>
								</td>
								<td width="4%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>$75k - $80k</i>
								</td>
								<td width="4%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>$80k - $85k</i>
								</td>
								<td width="4%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>$85k - $90k</i>
								</td>
								<td width="4%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>$90k - $955k</i>
								</td>
								<td width="4%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>$95k - $100k</i>
								</td>
								<td width="4%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>Over $100K</i>
								</td>
								<td width="4%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;border-right:1px solid #000;background-color:#c9d0d7;" >
									<i>No Salary</i>
								</td>
							</tr>';

				$res = $db->Execute("SELECT * FROM S_TEMP_COMP30004 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' AND PROGRAM_CODE = '$PROGRAM' ");
				while (!$res->EOF) { 
					$txt .= '<tr>
								<td align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['CALENDAR_YEAR'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['AVAILABLE_FOR_EMPLOYMENT'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['GRADUATES_IN_FIELD'].'</td>
								
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['AS_0to5000'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['AS_5001to10000'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['AS_10001to15000'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['AS_15001to20000'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['AS_20001to25000'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['AS_25001to30000'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['AS_30001to35000'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['AS_35001to40000'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['AS_40001to45000'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['AS_45001to50000'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['AS_50001to55000'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['AS_55001to60000'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['AS_60001to65000'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['AS_65001to70000'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['AS_70001to75000'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['AS_75001to80000'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['AS_80001to85000'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['AS_85001to90000'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['AS_90001to95000'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['AS_95001to100000'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['AS_Over100000'].'</td>
								<td align="right" style="border-left:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['NO_SALARY'].'</td>
							</tr>';
							
					$res->MoveNext();
				}
				$txt .= '</table><br /><br />';

				$i++;
			} 
			$mpdf->WriteHTML($txt);
			
			$db->Execute("DELETE FROM S_TEMP_COMP30004 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' ");
		} else if($_POST['REPORT_TYPE'] == 6) {
			$PROGRAM_ARR 		= array();
			$DESC_ARR 	 		= array();
			$PROGRAM_LENGTH_ARR = array();
			$res = $db->Execute("SELECT PROGRAM_CODE, PROGRAM_DESCRIPTION, PROGRAM_LENGTH FROM S_TEMP_COMP30003 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' GROUP BY PROGRAM_CODE ORDER BY PROGRAM_CODE ASC");
			while (!$res->EOF) {
				$PROGRAM_ARR[] 			= $res->fields['PROGRAM_CODE'];
				$DESC_ARR[] 			= $res->fields['PROGRAM_DESCRIPTION'];
				$PROGRAM_LENGTH_ARR[] 	= $res->fields['PROGRAM_LENGTH'];
				
				$res->MoveNext();
			}
			
			$txt = '<table border="0" cellspacing="0" cellpadding="4" width="100%">=
							<tr>
								<td width="100%" align="center" ><b style="font-size:15px;">License Examination Passage Rates</b><br /></td>
							</tr>
						</table>';
			
			$mpdf->AddPage();
			$i = 0;
			foreach($PROGRAM_ARR as $PROGRAM) {
				$txt .= '<table border="0" cellspacing="0" cellpadding="4" width="100%">
							<tr>
								<td width="100%"><b style="font-size:15px;">'.$PROGRAM.' - '.$DESC_ARR[$i].' - '.$PROGRAM_LENGTH_ARR[$i].'</b></td>
							</tr>
						</table>
						<table cellspacing="0" cellpadding="4" width="100%" >
							<tr>
								<td width="10%" align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>Calendar Year</i>
								</td>
								<td width="18%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>Number of Graduates in Calendar Year</i>
								</td>
								<td width="18%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>Number of Graduates Taking Exam</i>
								</td>
								<td width="18%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>Number Who Passed First Available Exam </i>
								</td>
								<td width="18%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;background-color:#c9d0d7;" >
									<i>Number Who Failed First Available Exam</i>
								</td>
								<td width="18%"  align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;border-top:1px solid #000;border-right:1px solid #000;background-color:#c9d0d7;" >
									<i>Passage Rate</i>
								</td>
							</tr>';

				$res = $db->Execute("SELECT * FROM S_TEMP_COMP30003 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' AND PROGRAM_CODE = '$PROGRAM' ");
				while (!$res->EOF) { 
					$txt .= '<tr>
								<td align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['CALENDAR_YEAR'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['GRADUATES'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['NUMBER_TAKING_EXAM'].'</td>
								
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['NUMBER_PASSED_FIRST_EXAM'].'</td>
								<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >'.$res->fields['NUMBER_FAILED_FIRST_EXAM'].'</td>
								<td align="right" style="border-left:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;" >'.number_format_value_checker(($res->fields['PASS_RATE'] * 100) ,2).'</td>
							</tr>';
							
					$res->MoveNext();
				}
				$txt .= '</table><br /><br />';

				$i++;
			} 
			$mpdf->WriteHTML($txt);
			
			$db->Execute("DELETE FROM S_TEMP_COMP30003 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' ");
		}
		//echo $txt;exit;
	
		$mpdf->Output($REPORT_NAME.'.pdf', 'D');
		
	} else if($_POST['FORMAT'] == 2) {
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
		$file_name 		= $REPORT_NAME.'.xlsx';
		$outputFileName = $dir.$file_name; 
		$outputFileName = str_replace(
		pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName);  

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		
		if($_POST['REPORT_TYPE'] == 1) {
			//DIAM-1061
			$line 	= 0;	
			$index 	= 0;

			$line++;	
			$index 	= -1;
			$heading[] = 'Calendar Year';
			$width[]   = 20;
			$heading[] = 'Program Code';
			$width[]   = 20;
			$heading[] = 'Program Description';
			$width[]   = 20;
			$heading[] = 'Student';
			$width[]   = 20;
			$heading[] = 'Student ID';
			$width[]   = 20;
			$heading[] = 'Campus';
			$width[]   = 20;
			$heading[] = 'First Term Date';
			$width[]   = 20;			
			$heading[] = 'Session';
			$width[]   = 20;
			$heading[] = 'Student Status';
			$width[]   = 20;
			$heading[] = 'Drop Reason';
			$width[]   = 20;
			$heading[] = 'Student End Date';
			$width[]   = 20;
			$heading[] = 'End Date Type';
			$width[]   = 20;
			$heading[] = 'Program Length';
			$width[]   = 20;
			$heading[] = 'Break';
			$width[]   = 20;
			$heading[] = 'Extended Program Length';
			$width[]   = 20;
			$heading[] = 'Extended Program Date';
			$width[]   = 20;		
			$heading[] = 'Number of Students Who Began Program';
			$width[]   = 20;
			$heading[] = 'Students Available for Graduation';
			$width[]   = 20;
			$heading[] = 'Number of On-Time Graduates';
			$width[]   = 20;
			$heading[] = '150% Graduates';
			$width[]   = 20;
			$heading[] = 'Beyond 150% Graduates';
			$width[]   = 20;

			$i = 0;
			foreach($heading as $title) {
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
			}	

			$objPHPExcel->getActiveSheet()->freezePane('A1');
			$res = $db->Execute("CALL ".$SP_NAME."(".$_SESSION['PK_ACCOUNT'].",'".$ST."','".$ET."',FALSE)");
			while (!$res->EOF) {
				$line++;
				$index = -1;

				$FirstTermDate = '';
				if($res->fields['FirstTermDate'] != '' && $res->fields['FirstTermDate'] != '0000-00-00')
					$FirstTermDate = date("Y-m-d",strtotime($res->fields['FirstTermDate']));
					
				$StudentEndDate = '';
				if($res->fields['StudentEndDate'] != '' && $res->fields['StudentEndDate'] != '0000-00-00')
					$StudentEndDate = date("Y-m-d",strtotime($res->fields['StudentEndDate']));
				
				
				if(date("Y-m-d",strtotime($res->fields['StudentEndDate'])) =='2222-02-02'){ 
					//DIAM-1063 Student End Date (should not show default date of 2222-02-02).
					$StudentEndDate = '';
				}	

					
				$ExtendedProgramDate = '';
				if($res->fields['ExtendedProgramDate'] != '' && $res->fields['ExtendedProgramDate'] != '0000-00-00')
					$ExtendedProgramDate = date("Y-m-d",strtotime($res->fields['ExtendedProgramDate']));

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CalendarYear']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ProgramCode']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ProgramDescription']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Student']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['StudentID']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Campus']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($FirstTermDate);				
				
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['SessionDescription']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['StudentStatus']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['DropReason']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($StudentEndDate);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EndDateType']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ProgramLength']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Break']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ExtendedProgramLength']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($ExtendedProgramDate);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Number of Students Who Began Program']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Students Available for Graduation']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Number of On-Time Graduates']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['_150% Graduates']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Beyond 150% Graduates']);
				
				$res->MoveNext();
			}
		} else if($_POST['REPORT_TYPE'] == 2) {

			$line 	= 0;	
			$index 	= 0;

			$line++;	
			$index 	= -1;

			$heading[] = 'Calendar Year';
			$width[]   = 20;
			$heading[] = 'Program Code';
			$width[]   = 20;
			$heading[] = 'Program Description';
			$width[]   = 20;
			$heading[] = 'Program Length';
			$width[]   = 20;
			$heading[] = 'Student';
			$width[]   = 20;
			$heading[] = 'Student ID';
			$width[]   = 20;
			$heading[] = 'Campus';
			$width[]   = 20;
			$heading[] = 'First Term Date';
			$width[]   = 20;			
			$heading[] = 'Student Status';
			$width[]   = 20;
			$heading[] = 'Drop Reason';
			$width[]   = 20;
			$heading[] = 'Student End Date';
			$width[]   = 20;
			$heading[] = 'End Date Type';
			$width[]   = 20;
			$heading[] = 'Students Who Began Program';
			$width[]   = 20;
			$heading[] = 'Graduates';
			$width[]   = 20;
			$heading[] = 'Available for Employment';
			$width[]   = 20;
			$heading[] = 'Employed In Field';
			$width[]   = 20;
			$heading[] = 'In Field Part Time';
			$width[]   = 20;
			$heading[] = 'In Field Full Time';
			$width[]   = 20;
			$heading[] = 'Single Position';
			$width[]   = 20;
			$heading[] = 'Concurrent Positions';
			$width[]   = 20;
			$heading[] = 'Self-Employed/Freelance';
			$width[]   = 20;
			$heading[] = 'Institutional Employment';
			$width[]   = 20;

			$i = 0;
			foreach($heading as $title) {
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
			}	

			$objPHPExcel->getActiveSheet()->freezePane('A1');
			$res = $db->Execute("CALL ".$SP_NAME."(".$_SESSION['PK_ACCOUNT'].",'".$ST."','".$ET."',FALSE)");
			while (!$res->EOF) {
				$line++;
				$index = -1;

				$FirstTermDate = '';
				if($res->fields['FirstTermDate'] != '' && $res->fields['FirstTermDate'] != '0000-00-00')
					$FirstTermDate = date("Y/m/d",strtotime($res->fields['FirstTermDate']));

				
				$StudentEndDate = '';
				if($res->fields['StudentEndDate'] != '' && $res->fields['StudentEndDate'] != '0000-00-00')
					$StudentEndDate = date("Y/m/d",strtotime($res->fields['StudentEndDate']));
				
				
				if(date("Y-m-d",strtotime($res->fields['StudentEndDate'])) =='2222-02-02'){ 
					//DIAM-1063 Student End Date (should not show default date of 2222-02-02).
					$StudentEndDate = '';
				}	

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CalendarYear']);				
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ProgramCode']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ProgramDescription']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ProgramLength']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Student']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['StudentID']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Campus']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($FirstTermDate);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['StudentStatus']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['DropReason']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($StudentEndDate);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EndDateType']);		
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Students Who Began Program']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Graduates']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Available for Employment']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Employed In Field']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['In Field Part Time']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['In Field Full Time']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Single Position']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Concurrent Positions']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Self-Employed/Freelance']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Institutional Employment']);
				
				$res->MoveNext();
			}
		} else if($_POST['REPORT_TYPE'] == 3) {

			$line 	= 0;	
			$index 	= 0;

			$line++;	
			$index 	= -1;
			$heading[] = 'Calendar Year';
			$width[]   = 20;
			$heading[] = 'Program Code';
			$width[]   = 20;
			$heading[] = 'Program Description';
			$width[]   = 20;
			$heading[] = 'Student';
			$width[]   = 20;
			$heading[] = 'Student ID';
			$width[]   = 20;
			// $heading[] = 'SSN';
			// $width[]   = 20;
			$heading[] = 'Campus';
			$width[]   = 20;
			$heading[] = 'First Term Date';
			$width[]   = 20;
			$heading[] = 'Student Status';
			$width[]   = 20;
			$heading[] = 'Student End Date';
			$width[]   = 20;
			$heading[] = 'End Date Type';
			// $heading[] = 'Grad Date';
			// $width[]   = 20;
			$width[]   = 20;
			$heading[] = 'Graduate';
			$width[]   = 20;
			$heading[] = 'Graduate With Loans';
			$width[]   = 20;
			$heading[] = 'Student Loan Debt';
			$width[]   = 20;			
			$heading[] = 'CIP';
			$width[]   = 20;
			$heading[] = 'Program Hours';
			$width[]   = 20;
			$heading[] = 'Program Weeks';
			$width[]   = 20;
			$heading[] = 'Program Months';
			$width[]   = 20;
			$heading[] = 'Program Units';
			$width[]   = 20;			
			$heading[] = 'Credential Level';
			$width[]   = 20;
			$heading[] = 'Credential Level Description';
			$width[]   = 20;
			
			
			
			
			
			
			
			$i = 0;
			foreach($heading as $title) {
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
			}	

			$objPHPExcel->getActiveSheet()->freezePane('A1');
			$res = $db->Execute("CALL ".$SP_NAME."(".$_SESSION['PK_ACCOUNT'].",'".$ST."','".$ET."',FALSE)");
			while (!$res->EOF) {
				$line++;
				$index = -1;

				$FIRST_TERM_DATE = '';
				if($res->fields['FIRST_TERM_DATE'] != '' && $res->fields['FIRST_TERM_DATE'] != '0000-00-00')
					$FIRST_TERM_DATE = date("Y/m/d",strtotime($res->fields['FIRST_TERM_DATE']));
					
				// $GRAD_DATE = '';
				// if($res->fields['GRAD_DATE'] != '' && $res->fields['GRAD_DATE'] != '0000-00-00')
				// 	$GRAD_DATE = date("Y-m-d",strtotime($res->fields['GRAD_DATE']));

				$StudentEndDate = '';
				if($res->fields['StudentEndDate'] != '' && $res->fields['StudentEndDate'] != '0000-00-00')
					$StudentEndDate = date("Y/m/d",strtotime($res->fields['StudentEndDate']));
				
				
				if(date("Y-m-d",strtotime($res->fields['StudentEndDate'])) =='2222-02-02'){ 
					//DIAM-1063 Student End Date (should not show default date of 2222-02-02).
					$StudentEndDate = '';
				}	

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CALENDAR_YEAR']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_CODE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_DESCRIPTION']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENTS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ID']);
				
				// $SSN = $res->fields['SSN'];
				// if($SSN != '') {
				// 	$SSN 	 = my_decrypt($_SESSION['PK_ACCOUNT'],$SSN);
				// 	$SSN_ORG = $SSN;
				// 	$SSN_ARR = explode("-",$SSN);
				// 	$SSN 	 = 'xxx-xx-'.$SSN_ARR[2];
				// }
				
				// $index++;
				// $cell_no = $cell[$index].$line;
				// $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($SSN);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CAMPUS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($FIRST_TERM_DATE);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_STATUS']);

				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($StudentEndDate);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EndDateType']);	

				// $index++;
				// $cell_no = $cell[$index].$line;
				// $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($GRAD_DATE);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['GRADUATE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['GRADUATE_WITH_LOANS']);

				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_LOAN_DEBT']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CIP']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_HOURS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_WEEKS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_MONTHS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_UNITS']);
					
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CREDENTIAL_LEVEL']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CREDENTIAL_LEVEL_DESCRIPTION']);
									

				$res->MoveNext();
			}
		} else if($_POST['REPORT_TYPE'] == 4) {

			$line 	= 0;	
			$index 	= 0;

			$line++;	
			$index 	= -1;

			$heading[] = 'Calendar Year';
			$width[]   = 20;
			$heading[] = 'Program Code';
			$width[]   = 20;
			$heading[] = 'Program Description';
			$width[]   = 20;
			$heading[] = 'Program Length';
			$width[]   = 20;		
			$heading[] = 'Student';
			$width[]   = 20;
			$heading[] = 'Student ID';
			$width[]   = 20;
			$heading[] = 'Campus';
			$width[]   = 20;
			$heading[] = 'First Term Date';
			$width[]   = 20;			
			$heading[] = 'Student Status';
			$width[]   = 20;			
			$heading[] = 'Student End Date';
			$width[]   = 20;
			$heading[] = 'End Date Type';
			$width[]   = 20;
			$heading[] = 'Student Loan Debt';
			$width[]   = 20;
			$heading[] = 'Enrolled';
			$width[]   = 20;
			$heading[] = 'Enrolled With Loans';
			$width[]   = 20;

			$i = 0;
			foreach($heading as $title) {
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
			}	

			$objPHPExcel->getActiveSheet()->freezePane('A1');
			$res = $db->Execute("CALL ".$SP_NAME."(".$_SESSION['PK_ACCOUNT'].",'".$ST."','".$ET."',FALSE)");
			while (!$res->EOF) {
				$line++;
				$index = -1;

				$FirstTermDate = '';
				if($res->fields['FirstTermDate'] != '' && $res->fields['FirstTermDate'] != '0000-00-00')
					$FirstTermDate = date("Y/m/d",strtotime($res->fields['FirstTermDate']));
					
				$StudentEndDate = '';
				if($res->fields['StudentEndDate'] != '' && $res->fields['StudentEndDate'] != '0000-00-00')
					$StudentEndDate = date("Y/m/d",strtotime($res->fields['StudentEndDate']));


				if(date("Y-m-d",strtotime($res->fields['StudentEndDate'])) =='2222-02-02'){ 
					//DIAM-1063 Student End Date (should not show default date of 2222-02-02).
					$StudentEndDate = '';
				}		

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CalendarYear']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ProgramCode']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ProgramDescription']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ProgramLength']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Student']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['StudentID']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Campus']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($FirstTermDate);				
							
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['StudentStatus']);				
								
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($StudentEndDate);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EndDateType']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['StudentLoanDebt']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Enrollee']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EnrolleeWithLoans']);

				$res->MoveNext();
			}
		} else if($_POST['REPORT_TYPE'] == 5) {

			$line 	= 0;	
			$index 	= 0;

			$line++;	
			$index 	= -1;
			
			$heading[] = 'Calendar Year';
			$width[]   = 20;
			$heading[] = 'Program Code';
			$width[]   = 20;
			$heading[] = 'Program Description';
			$width[]   = 20;
			$heading[] = 'Program Length';
			$width[]   = 20;
			$heading[] = 'Student';
			$width[]   = 20;
			$heading[] = 'Student ID';
			$width[]   = 20;
			$heading[] = 'Campus';
			$width[]   = 20;
			$heading[] = 'First Term Date';
			$width[]   = 20;		
			$heading[] = 'Student Status';
			$width[]   = 20;			
			$heading[] = 'Student End Date';
			$width[]   = 20;
			$heading[] = 'End Date Type';
			$width[]   = 20;
			$heading[] = 'Grads Avail Employment';
			$width[]   = 20;
			$heading[] = 'Grads In Field';
			$width[]   = 20;
			$heading[] = 'Combined Salary';
			$width[]   = 20;			
			$heading[] = 'AS 0 to 5000';
			$width[]   = 20;
			$heading[] = 'AS 5001 to 10000';
			$width[]   = 20;
			$heading[] = 'AS 10001 to 15000';
			$width[]   = 20;
			$heading[] = 'AS 15001 to 20000';
			$width[]   = 20;
			$heading[] = 'AS 20001 to 25000';
			$width[]   = 20;
			$heading[] = 'AS 25001 to 30000';
			$width[]   = 20;
			$heading[] = 'AS 30001 1to 35000';
			$width[]   = 20;
			$heading[] = 'AS 35001 to 40000';
			$width[]   = 20;
			$heading[] = 'AS 40001 1to 45000';
			$width[]   = 20;
			$heading[] = 'AS 45001 to 50000';
			$width[]   = 20;
			$heading[] = 'AS 50001 1to 55000';
			$width[]   = 20;
			$heading[] = 'AS 55001 to 60000';
			$width[]   = 20;
			$heading[] = 'AS 60001 1to 65000';
			$width[]   = 20;
			$heading[] = 'AS 65001 to 70000';
			$width[]   = 20;
			$heading[] = 'AS 70001 1to 75000';
			$width[]   = 20;
			$heading[] = 'AS 75001 to 80000';
			$width[]   = 20;
			$heading[] = 'AS 80001 1to 85000';
			$width[]   = 20;
			$heading[] = 'AS 85001 to 90000';
			$width[]   = 20;
			$heading[] = 'AS 90001 1to 95000';
			$width[]   = 20;
			$heading[] = 'AS 95001 to 100000';
			$width[]   = 20;
			$heading[] = 'AS Over 100000';
			$width[]   = 20;
			$heading[] = 'No Salary Reported';
			$width[]   = 20;

			$i = 0;
			foreach($heading as $title) {
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
			}	

			$objPHPExcel->getActiveSheet()->freezePane('A1');
			$res = $db->Execute("CALL ".$SP_NAME."(".$_SESSION['PK_ACCOUNT'].",'".$ST."','".$ET."',FALSE)");
			while (!$res->EOF) {
				$line++;
				$index = -1;
				
				$FirstTermDate = '';
				if($res->fields['FirstTermDate'] != '' && $res->fields['FirstTermDate'] != '0000-00-00')
					$FirstTermDate = date("Y/m/d",strtotime($res->fields['FirstTermDate']));
					
				$StudentEndDate = '';
				if($res->fields['StudentEndDate'] != '' && $res->fields['StudentEndDate'] != '0000-00-00')
					$StudentEndDate = date("Y/m/d",strtotime($res->fields['StudentEndDate']));

			
				if(date("Y-m-d",strtotime($res->fields['StudentEndDate'])) =='2222-02-02'){ 
					//DIAM-1063 Student End Date (should not show default date of 2222-02-02).
					$StudentEndDate = '';
				}			

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CalendarYear']);			
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ProgramCode']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ProgramDescription']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ProgramLength']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Student']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['StudentID']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Campus']);			
								
										
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($FirstTermDate);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['StudentStatus']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($StudentEndDate);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EndDateType']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['GradsAvailEmployment']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['GradsInField']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CombinedSalary']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['AS0to5000']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['AS5001to10000']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['AS10001to15000']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['AS15001to20000']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['AS20001to25000']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['AS25001to30000']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['AS300011to35000']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['AS35001to40000']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['AS400011to45000']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['AS45001to50000']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['AS500011to55000']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['AS55001to60000']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['AS60001to65000']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['AS65001to70000']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['AS700011to75000']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['AS75001to80000']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['AS800011to85000']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['AS85001to90000']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['AS900011to95000']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['AS95001to100000']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ASOver100000']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NoSalaryReported']);
				
				$res->MoveNext();
			}
		} else if($_POST['REPORT_TYPE'] == 6) {

			$line 	= 0;	
			$index 	= 0;

			$line++;	
			$index 	= -1;

			$heading[] = 'Calendar Year';
			$width[]   = 20;
			$heading[] = 'Program Code';
			$width[]   = 20;
			$heading[] = 'Program Description';
			$width[]   = 20;
			$heading[] = 'Program Length';
			$width[]   = 20;			
			$heading[] = 'Student';
			$width[]   = 20;
			$heading[] = 'Student ID';
			$width[]   = 20;
			$heading[] = 'Campus';
			$width[]   = 20;
			$heading[] = 'First Term Date';
			$width[]   = 20;		
			$heading[] = 'Student Status';
			$width[]   = 20;			
			$heading[] = 'Student End Date';
			$width[]   = 20;
			$heading[] = 'End Date Type';
			$width[]   = 20;
			$heading[] = 'Graduates';
			$width[]   = 20;
			$heading[] = 'Taking Exam';
			$width[]   = 20;
			$heading[] = 'Exam Name';
			$width[]   = 20;
			$heading[] = 'Passed Exam';
			$width[]   = 20;
			$heading[] = 'Failed Exam';
			$width[]   = 20;

			$i = 0;
			foreach($heading as $title) {
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
			}	

			$objPHPExcel->getActiveSheet()->freezePane('A1');
			$res = $db->Execute("CALL ".$SP_NAME."(".$_SESSION['PK_ACCOUNT'].",'".$ST."','".$ET."',FALSE)");
			while (!$res->EOF) {
				$line++;
				$index = -1;

				$FirstTermDate = '';
				if($res->fields['FirstTermDate'] != '' && $res->fields['FirstTermDate'] != '0000-00-00')
					$FirstTermDate = date("Y/m/d",strtotime($res->fields['FirstTermDate']));
					
				$StudentEndDate = '';
				if($res->fields['StudentEndDate'] != '' && $res->fields['StudentEndDate'] != '0000-00-00')
					$StudentEndDate = date("Y/m/d",strtotime($res->fields['StudentEndDate']));


					if(date("Y-m-d",strtotime($res->fields['StudentEndDate'])) =='2222-02-02'){ 
						//DIAM-1063 Student End Date (should not show default date of 2222-02-02).
						$StudentEndDate = '';
					}		
	
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CalendarYear']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ProgramCode']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ProgramDescription']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ProgramLength']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Student']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['StudentID']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Campus']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($FirstTermDate);				

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['StudentStatus']);
								
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($StudentEndDate);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EndDateType']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Graduates']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TAKING_EXAM']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EXAM_NAME']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PASSED_EXAM']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FAILED_EXAM']);
				
				$res->MoveNext();
			}
		}
		
		$objWriter->save($outputFileName);
		$objPHPExcel->disconnectWorksheets();
		header("location:".$outputFileName);
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
	<title><?=BPPE_TITLE ?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
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
                        <h4 class="text-themecolor"><?=BPPE_TITLE ?></h4>
                    </div>
                </div>
                <form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col-md-3">
											<?=REPORT_TYPE?>
											<select id="REPORT_TYPE" name="REPORT_TYPE" class="form-control" >
												<option value="1">Completion Rates</option>
												<option value="2">Placement Rates</option>
												<option value="3">Federal Student Loan Debt - Graduated Students</option>
												<option value="4">Federal Student Loan Debt - Enrolled Students</option>
												<option value="5">Salary and Wage Information</option>
												<option value="6">License Examination Passage Rates</option> <!--DIAM-1061 -->
											</select>
										</div>
										<div class="col-md-2">
											<?=START_DATE?>
											<input type="text" class="form-control date required-entry" id="START_DATE" name="START_DATE" value="" >
										</div>
										<div class="col-md-2">
											<?=END_DATE?>
											<input type="text" class="form-control date required-entry" id="END_DATE" name="END_DATE" value="" >
										</div>
								
										<div class="col-md-2" style="padding: 0;" >
											<br />
											<button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
											<button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button>
											<input type="hidden" name="FORMAT" id="FORMAT" >
										</div>
										
										<div class="col-md-3 text-right">
											<button type="button" onclick="window.location.href='bppe_report_setup'" class="btn waves-effect waves-light btn-info"><?=REPORT_SETUP?></button>
											<br />
											<? $res = $db->Execute("select * from S_BPPE_SETUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
											if($res->fields['EDITED_ON'] != '' && $res->fields['EDITED_ON'] != '0000-00-00 00:00:00'){
												$EDITED_BY	= $res->fields['EDITED_BY'];
												$EDITED_ON	= $res->fields['EDITED_ON'];
												$res_user = $db->Execute("SELECT CONCAT(LAST_NAME,', ', FIRST_NAME) as NAME FROM S_EMPLOYEE_MASTER, Z_USER WHERE PK_USER = '$EDITED_BY' AND Z_USER.ID =  S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER AND PK_USER_TYPE IN (1,2) "); 

												$EDITED_BY	= $res_user->fields['NAME']; 
												echo "Edited: ".$EDITED_BY." ".date("m/d/Y",strtotime($EDITED_ON));
											} ?>
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
	function submit_form(val){
		jQuery(document).ready(function($) {
			var valid = new Validation('form1', {onSubmit:false});
			var result = valid.validate();
			if(result == true){ 
				document.getElementById('FORMAT').value = val
				document.form1.submit();
			}
		});
	}
	</script>
</body>

</html>
