<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("check_access.php");

$res_add_on = $db->Execute("SELECT COE,ECM,_1098T,_90_10,IPEDS,POPULATION_REPORT FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res_add_on->fields['_90_10'] == 0 || check_access('MANAGEMENT_90_10') == 0){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	$campus_code = ""; //DIAM-1680	
	$campus_name = "";
	$campus_cond = "";
	$campus_id	 = "";
	if(!empty($_POST['PK_CAMPUS'])){
		$PK_CAMPUS 	 = implode(",",$_POST['PK_CAMPUS']);
		$campus_cond = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
	}
	
	$ST = '';
	$ET = '';
	if($_POST['START_DATE'] != '')
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
		
	if($_POST['END_DATE'] != '')
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
	
	$res_campus = $db->Execute("select PK_CAMPUS,CAMPUS_NAME,CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $campus_cond order by CAMPUS_NAME ASC"); //DIAM-1680
	while (!$res_campus->EOF) {
		if($campus_name != '')
			$campus_name .= ', ';
		$campus_name .= $res_campus->fields['CAMPUS_NAME'];
		
		if($campus_id != '')
			$campus_id .= ',';
		$campus_id .= $res_campus->fields['PK_CAMPUS'];
		//DIAM-1680
		if($campus_code != '')
			$campus_code .= ', ';
		$campus_code .= $res_campus->fields['CAMPUS_CODE'];
		//DIAM-1680
		
		$res_campus->MoveNext();
	}

	//TRUE - Program Group
	//False - Program
	if($_POST['RUN_BY'] == 1)
		$REPORT_TYPE = 'By Program Code';
	else if($_POST['RUN_BY'] == 2)
		$REPORT_TYPE = 'By Program Group';
	else if($_POST['RUN_BY'] == 3)
		$REPORT_TYPE = 'Combine All Programs';	
	else if($_POST['RUN_BY'] == 4) // DIAM-84
		$REPORT_TYPE = 'By Program Code Combined Campuses';
	else if($_POST['RUN_BY'] == 5) // DIAM-84
		$REPORT_TYPE = 'By Program Group Combined Campuses';
	else if($_POST['RUN_BY'] == 6) // DIAM-84
		$REPORT_TYPE = 'Combine All Programs And Campuses';
	
	if($_POST['FORMAT'] == 1) {
		$browser = '';
		if(stripos($_SERVER['HTTP_USER_AGENT'],"chrome") != false)
			$browser =  "chrome";
		else if(stripos($_SERVER['HTTP_USER_AGENT'],"Safari") != false)
			$browser = "Safari";
		else
			$browser = "firefox";
		require_once('../global/tcpdf/config/lang/eng.php');
		require_once('../global/tcpdf/tcpdf.php');
		
		class MYPDF extends TCPDF {
			public function Header() {
				global $db,$campus_name,$campus_code;//DIAM-1680
				
				$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
				
				if($res->fields['PDF_LOGO'] != '') {
					$ext = explode(".",$res->fields['PDF_LOGO']);
					$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
				}
				
				$this->SetFont('helvetica', '', 15);
				$this->SetY(8);
				$this->SetX(55);
				$this->SetTextColor(000, 000, 000);
				$this->MultiCell(55, 5, $res->fields['SCHOOL_NAME'], 0, 'L', 0, 0, '', '', true);
				
				$this->SetFont('helvetica', 'I', 13);
				$this->SetY(10);
				$this->SetX(144);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, "90/10 Calculation Disclosure", 0, false, 'L', 0, '', 0, false, 'M', 'L');
				
				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(120, 13, 202, 13, $style);
				
				$str = "Transactions between: ".$_POST['START_DATE'].' - '.$_POST['END_DATE'];

				$this->SetFont('helvetica', 'I', 10);
				$this->SetY(16);
				$this->SetX(100);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				
				if($_POST['RUN_BY'] == 3){
					$this->SetFont('helvetica', 'I', 8);
					$this->SetY(18);
					$this->SetX(100);
					$this->SetTextColor(000, 000, 000);
					//$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
					$this->MultiCell(102, 5, $campus_code, 0, 'R', 0, 0, '', '', true);
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
				
				$this->SetY(-15);
				$this->SetX(100);
				$this->Cell(30, 10, 'ACCT90101', 0, false, 'C', 0, '', 0, false, 'T', 'M');
			}
		}

		$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		if($_POST['RUN_BY'] == 3)
			$pdf->SetMargins(7, 30, 7);
		else
			$pdf->SetMargins(7, 17, 7);
			
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, 30);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->setLanguageArray($l);
		$pdf->setFontSubsetting(true);
		$pdf->SetFont('helvetica', '', 7, '', true);
		
		$res = $db->Execute("CALL ACCT90101(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".$ST."','".$ET."', '".$REPORT_TYPE."')");
		$BATCH_ID = $res->fields['vThisBatchID'];

		$db->close();
		$db->connect($db_host,'root',$db_pass,$db_name);
	
		if($_POST['RUN_BY'] == 1 || $_POST['RUN_BY'] == 2){
			$res = $db->Execute("SELECT DISTINCT(PROGRAM) as PROGRAM FROM S_TEMP_ACCT90101 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' ORDER BY PROGRAM ASC");
			
			while (!$res->EOF) {
				$PROGRAM_ARR[] = $res->fields['PROGRAM'];
				$res->MoveNext();
			}
		} 
		else if($_POST['RUN_BY'] == 4 || $_POST['RUN_BY'] == 5) // DIAM-84
		{ 
			$res = $db->Execute("SELECT DISTINCT PROGRAM FROM S_TEMP_ACCT90101 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' ORDER BY PROGRAM ASC");
			
			while (!$res->EOF) {
				$PROGRAM_ARRS[] = $res->fields['PROGRAM'];
				$res->MoveNext();
			}

			$PROGRAM_ARR = array_unique($PROGRAM_ARRS);
		} // End DIAM-84
		else {
			$PROGRAM_ARR[] = '';
		}

		$total 	= 0;
		$txt 	= '';
		//print_r($PROGRAM_ARR);exit;
		foreach($PROGRAM_ARR as $PROGRAM){
			
			$selecting_param = '*';
			if($_POST['RUN_BY'] == 1 || $_POST['RUN_BY'] == 2)
			{
				$prog_cond = " AND PROGRAM = '$PROGRAM' ";
			}
			else if($_POST['RUN_BY'] == 4 || $_POST['RUN_BY'] == 5) // DIAM-84
			{
				$prog_cond = " AND PROGRAM = '$PROGRAM'  group by PROGRAM ";

				$selecting_param = " 
				CAMPUS, 
				PROGRAM,
				  SUM(`Prior_Ledger_Balance`) AS Prior_Ledger_Balance,
				   SUM(`_1`) AS _1,
				   SUM(`_2`) AS _2,
				   SUM(`_3`) AS _3,
				   SUM(`_4`) AS _4,
				   SUM(`_5`) AS _5,
				   SUM(`_6`) AS _6,
				   SUM(`_7`) AS _7,
				   SUM(`_8`) AS _8,
				   SUM(`_9`) AS _9,
				   SUM(`_10`) AS _10,
				   SUM(`_11`) AS _11,
				   SUM(`_12`) AS _12,
				   SUM(`_13`) AS _13,
				   SUM(`_14`) AS _14,
				   SUM(`_15`) AS _15,
				   SUM(`_16`) AS _16,
				   SUM(`_17`) AS _17,
				   SUM(`_18`) AS _18,
				   SUM(`_19`) AS _19,
				   SUM(`_20`) AS _20,
				  SUM(`StudentTitleIVRevenue`) AS StudentTitleIVRevenue,
				  SUM(`AdjustedTitleIVRevenue`) AS AdjustedTitleIVRevenue,
				  SUM(`StudentNonTitleIVRevenue`) AS StudentNonTitleIVRevenue,
				  SUM(`RevenueFromOtherSources`) AS RevenueFromOtherSources,
				  SUM(`TotalNonTitleIVRevenue`) AS TotalNonTitleIVRevenue,
				  SUM(`TotalRevenue`) AS TotalRevenue,
				  SUM(`TotalNonTitleIVRevenue`)/SUM(`TotalRevenue`) AS Ratio_NonTitle_IV,
				  1 - SUM(`TotalNonTitleIVRevenue`)/SUM(`TotalRevenue`) AS Ratio_Title_IV ";
			}
			else if($_POST['RUN_BY'] == 3 || $_POST['RUN_BY'] == 6) // DIAM-1680, DIAM-84
			{
				$selecting_param = " 
				CAMPUS, 
				PROGRAM,
				  SUM(`Prior_Ledger_Balance`) AS Prior_Ledger_Balance,
				   SUM(`_1`) AS _1,
				   SUM(`_2`) AS _2,
				   SUM(`_3`) AS _3,
				   SUM(`_4`) AS _4,
				   SUM(`_5`) AS _5,
				   SUM(`_6`) AS _6,
				   SUM(`_7`) AS _7,
				   SUM(`_8`) AS _8,
				   SUM(`_9`) AS _9,
				   SUM(`_10`) AS _10,
				   SUM(`_11`) AS _11,
				   SUM(`_12`) AS _12,
				   SUM(`_13`) AS _13,
				   SUM(`_14`) AS _14,
				   SUM(`_15`) AS _15,
				   SUM(`_16`) AS _16,
				   SUM(`_17`) AS _17,
				   SUM(`_18`) AS _18,
				   SUM(`_19`) AS _19,
				   SUM(`_20`) AS _20,
				  SUM(`StudentTitleIVRevenue`) AS StudentTitleIVRevenue,
				  SUM(`AdjustedTitleIVRevenue`) AS AdjustedTitleIVRevenue,
				  SUM(`StudentNonTitleIVRevenue`) AS StudentNonTitleIVRevenue,
				  SUM(`RevenueFromOtherSources`) AS RevenueFromOtherSources,
				  SUM(`TotalNonTitleIVRevenue`) AS TotalNonTitleIVRevenue,
				  SUM(`TotalRevenue`) AS TotalRevenue,
				  SUM(`TotalNonTitleIVRevenue`)/SUM(`TotalRevenue`) AS Ratio_NonTitle_IV,
				  1 - SUM(`TotalNonTitleIVRevenue`)/SUM(`TotalRevenue`) AS Ratio_Title_IV ";
			} // End DIAM-84
			else
			{
				$prog_cond = " ";
			}
			
			$sql_pg = "SELECT $selecting_param FROM S_TEMP_ACCT90101 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' $prog_cond ";
			//echo $sql_pg;exit;
			$res = $db->Execute($sql_pg);

			while (!$res->EOF) {
				$pdf->AddPage();
				$txt = '';
				if($_POST['RUN_BY'] == 1 || $_POST['RUN_BY'] == 2)
				{ 
					$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
								<tr>
									<td width="100%" align="right" ><i style="font-size:30px" >'.$campus_code.'</i></td>
								</tr>
								<tr>
									<td width="100%" align="right" ><i style="font-size:30px" >'.$res->fields['CAMPUS'].'</i><br /><br /></td>
								</tr>
								<tr>
									<td width="100%" align="right" ><b style="font-size:40px" >'.$PROGRAM.'</b></td>
								</tr>
							</table>'; //DIAM-1680
				}
				if($_POST['RUN_BY'] == 4 || $_POST['RUN_BY'] == 5) // DIAM-84
				{ 
					$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
								<tr><br /><br />
									<td width="100%" align="right" ><b style="font-size:40px" >'.$PROGRAM.'</b></td>
								</tr>
							</table>';
				} // End DIAM-84
				
				$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
							<tr>
								<td width="100%" ><b style="font-size:30px" >Student Title IV Revenue</b></td>
							</tr>
							<tr>
								<td width="15%" ></td>
								<td width="65%" style="border-top:0.5px solid #000;border-left:0.5px solid #000;" >Federal Subsidized Loan</td>
								<td width="20%" style="border-top:0.5px solid #000;border-right:0.5px solid #000;" align="right" >$'.number_format_value_checker($res->fields['_6'],2).'</td>
							</tr>
							<tr>
								<td width="15%" ></td>
								<td width="65%" style="border-top:0.5px solid #000;border-left:0.5px solid #000;" >Federal Unsubsidized Loan</td>
								<td width="20%" style="border-top:0.5px solid #000;border-right:0.5px solid #000;" align="right"  >$'.number_format_value_checker($res->fields['_7'],2).'</td>
							</tr>
							<tr>
								<td width="15%" ></td>
								<td width="65%" style="border-top:0.5px solid #000;border-left:0.5px solid #000;" >Federal Parent Plus Loan</td>
								<td width="20%" style="border-top:0.5px solid #000;border-right:0.5px solid #000;" align="right"  >$'.number_format_value_checker($res->fields['_3'],2).'</td>
							</tr>
							<tr>
								<td width="15%" ></td>
								<td width="65%" style="border-top:0.5px solid #000;border-left:0.5px solid #000;" >Federal Pell Grant</td>
								<td width="20%" style="border-top:0.5px solid #000;border-right:0.5px solid #000;" align="right"  >$'.number_format_value_checker($res->fields['_4'],2).'</td>
							</tr>
							<tr>
								<td width="15%" ></td>
								<td width="65%" style="border-top:0.5px solid #000;border-left:0.5px solid #000;" >FSEOG</td>
								<td width="20%" style="border-top:0.5px solid #000;border-right:0.5px solid #000;" align="right"  >$'.number_format_value_checker($res->fields['_5'],2).'</td>
							</tr>
							<tr>
								<td width="15%" ></td>
								<td width="65%" style="border-top:0.5px solid #000;border-bottom:0.5px solid #000;border-left:0.5px solid #000;" >Title IV Other</td>
								<td width="20%" style="border-top:0.5px solid #000;border-bottom:0.5px solid #000;border-right:0.5px solid #000;" align="right"  >$'.number_format_value_checker($res->fields['_17'],2).'</td>
							</tr>
							<tr>
								<td width="50%" ></td>
								<td width="30%" style="border-bottom:0.5px solid #000;" >Student Title IV Revenue</td>
								<td width="20%" style="border-bottom:0.5px solid #000;" align="right" >$'.number_format_value_checker($res->fields['StudentTitleIVRevenue'],2).'</td>
							</tr>
							<tr>
								<td width="50%" ></td>
								<td width="30%" style="border-bottom:0.5px solid #000;" >Return to Title IV Funds</td>
								<td width="20%" style="border-bottom:0.5px solid #000;" align="right" >$'.number_format_value_checker($res->fields['_12'],2).'</td>
							</tr>
							<tr>
								<td width="50%" ></td>
								<td width="30%" style="border-bottom:0.5px solid #000;" >Adjusted Title IV Revenue</td>
								<td width="20%" style="border-bottom:0.5px solid #000;" align="right" >$'.number_format_value_checker($res->fields['AdjustedTitleIVRevenue'],2).'</td>
							</tr>
							
							<tr>
								<td width="100%" ><b style="font-size:30px" >Student Non-Title IV Revenue</b></td>
							</tr>
							<tr>
								<td width="15%" ></td>
								<td width="65%" style="border-top:0.5px solid #000;border-left:0.5px solid #000;" >Grant funds for the student from non-Federal public agencies or private sources independent of the school</td>
								<td width="20%" style="border-top:0.5px solid #000;border-right:0.5px solid #000;" align="right" >$'.number_format_value_checker($res->fields['_9'],2).'</td>
							</tr>
							<tr>
								<td width="15%" ></td>
								<td width="65%" style="border-top:0.5px solid #000;border-left:0.5px solid #000;" >Funds provided for the student under a contractual arrangement with Federal, State, or local government agency for the purpose of providing job training to low-income individuals</td>
								<td width="20%" style="border-top:0.5px solid #000;border-right:0.5px solid #000;" align="right" >$'.number_format_value_checker($res->fields['_2'],2).'</td>
							</tr>
							<tr>
								<td width="15%" ></td>
								<td width="65%" style="border-top:0.5px solid #000;border-left:0.5px solid #000;" >Funds used by a student from savings plans for educational expenses  established by or on behalf of the student that qualify for special tax treatment under the Internal Revenue Code</td>
								<td width="20%" style="border-top:0.5px solid #000;border-right:0.5px solid #000;" align="right" >$'.number_format_value_checker($res->fields['_8'],2).'</td>
							</tr>
							<tr>
								<td width="15%" ></td>
								<td width="65%" style="border-top:0.5px solid #000;border-left:0.5px solid #000;" >School scholarships disbursed to the student</td>
								<td width="20%" style="border-top:0.5px solid #000;border-right:0.5px solid #000;" align="right" >$'.number_format_value_checker($res->fields['_18'],2).'</td>
							</tr>
							<tr>
								<td width="15%" ></td>
								<td width="65%" style="border-top:0.5px solid #000;border-left:0.5px solid #000;" >Cash Payments from Students</td>
								<td width="20%" style="border-top:0.5px solid #000;border-right:0.5px solid #000;" align="right" >$'.number_format_value_checker($res->fields['_1'],2).'</td>
							</tr>
							<tr>
								<td width="15%" ></td>
								<td width="65%" style="border-top:0.5px solid #000;border-bottom:0.5px solid #000;border-left:0.5px solid #000;" >Non-Title IV Other</td>
								<td width="20%" style="border-top:0.5px solid #000;border-bottom:0.5px solid #000;border-right:0.5px solid #000;" align="right" >$'.number_format_value_checker($res->fields['_11'],2).'</td>
							</tr>
							<tr>
								<td width="50%" ></td>
								<td width="30%" style="border-bottom:0.5px solid #000;" >Student Non-Title IV Revenue</td>
								<td width="20%" style="border-bottom:0.5px solid #000;" align="right" >$'.number_format_value_checker($res->fields['StudentNonTitleIVRevenue'],2).'</td>
							</tr>
							
							<tr>
								<td width="100%" ><b style="font-size:30px" >Revenue from Other Sources</b></td>
							</tr>
							<tr>
								<td width="15%" ></td>
								<td width="65%" style="border-top:0.5px solid #000;border-left:0.5px solid #000;" >Activities conducted by the school that are necessary for education and training</td>
								<td width="20%" style="border-top:0.5px solid #000;border-right:0.5px solid #000;" align="right" >$'.number_format_value_checker($res->fields['_14'],2).'</td>
							</tr>
							<tr>
								<td width="15%" ></td>
								<td width="65%" style="border-top:0.5px solid #000;border-left:0.5px solid #000;" >Funds paid by a student, or on behalf of a student by a party other than the school for education or training program that is not eligible</td>
								<td width="20%" style="border-top:0.5px solid #000;border-right:0.5px solid #000;" align="right" >$'.number_format_value_checker($res->fields['_16'],2).'</td>
							</tr>
							<tr>
								<td width="15%" ></td>
								<td width="65%" style="border-top:0.5px solid #000;border-left:0.5px solid #000;" >Allowable student payments + allowable amounts from account receivable or institutional loan sales - any required payments under a recourse agreement</td>
								<td width="20%" style="border-top:0.5px solid #000;border-right:0.5px solid #000;" align="right" >$'.number_format_value_checker($res->fields['_15'],2).'</td>
							</tr>
							<tr>
								<td width="15%" ></td>
								<td width="65%" style="border-top:0.5px solid #000;border-bottom:0.5px solid #000;border-left:0.5px solid #000;" >Revenue Other</td>
								<td width="20%" style="border-top:0.5px solid #000;border-bottom:0.5px solid #000;border-right:0.5px solid #000;" align="right" >$'.number_format_value_checker($res->fields['_13'],2).'</td>
							</tr>
							<tr>
								<td width="50%" ></td>
								<td width="30%" style="border-bottom:0.5px solid #000;" >Revenue from Other Sources</td>
								<td width="20%" style="border-bottom:0.5px solid #000;" align="right"  >$'.number_format_value_checker($res->fields['RevenueFromOtherSources'],2).'</td>
							</tr>
							
							<tr>
								<td width="100%" ><b style="font-size:30px" >Report Totals</b></td>
							</tr>
							<tr>
								<td width="15%" ></td>
								<td width="65%" style="border-top:0.5px solid #000;border-left:0.5px solid #000;" >Total Non-Title IV Revenue (Student Non-Title IV Revenue <br />+ Revenue from Other Sources)</td>
								<td width="20%" style="border-top:0.5px solid #000;border-right:0.5px solid #000;" align="right" >$'.number_format_value_checker($res->fields['TotalNonTitleIVRevenue'],2).'</td>
							</tr>
							<tr>
								<td width="15%" ></td>
								<td width="65%" style="border-top:0.5px solid #000;border-bottom:0.5px solid #000;border-left:0.5px solid #000;" >Total Revenue (Adjusted Student Title IV Revenue + R2T4 <br />+ Student Non-Title IV Revenue + Revenue from Other Sources)</td>
								<td width="20%" style="border-top:0.5px solid #000;border-bottom:0.5px solid #000;border-right:0.5px solid #000;" align="right" >$'.number_format_value_checker($res->fields['TotalRevenue'],2).'</td>
							</tr>';
							
							$Ratio_NonTitle_IV 	= $res->fields['Ratio_NonTitle_IV'] * 100;
							$Ratio_Title_IV 	= $res->fields['Ratio_Title_IV'] * 100;
							
							$Ratio_NonTitle_IV_style = "";
							$Ratio_Title_IV_style 	 = "";
							
							if($Ratio_NonTitle_IV < 10)
								$Ratio_NonTitle_IV_style = "color:#FF0000;";
								
							if($Ratio_Title_IV > 90)
								$Ratio_Title_IV_style = "color:#FF0000;";
							
							$txt .= '<tr>
								<td width="50%" ></td>
								<td width="30%" style="border-bottom:0.5px solid #000;" >90/10 Ratio Non-Title IV</td>
								<td width="20%" style="border-bottom:0.5px solid #000;'.$Ratio_NonTitle_IV_style.'" align="right" >'.number_format_value_checker($Ratio_NonTitle_IV,2).' %</td>
							</tr>
							<tr>
								<td width="50%" ></td>
								<td width="30%" style="border-bottom:0.5px solid #000;" >90/10 Ratio Title IV</td>
								<td width="20%" style="border-bottom:0.5px solid #000;'.$Ratio_Title_IV_style.'" align="right" >'.number_format_value_checker($Ratio_Title_IV,2).' %</td>
							</tr>
						</table>';
				
				//echo $txt;exit;
				$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
				$res->MoveNext();
			}
		}
		
		$db->Execute("DELETE FROM S_TEMP_ACCT90101 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' ");
		
		$dir 			= 'temp/';
		$file_name		= '90_10 Calculation Disclosure.pdf';
		$outputFileName = $dir.$file_name; 
		$outputFileName = str_replace(
		pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName ); 
		/*if($browser == 'Safari')
			$pdf->Output('temp/'.$file_name, 'FD');
		else	
			$pdf->Output($file_name, 'I');*/
			
		$pdf->Output($outputFileName, 'FD');
		return $outputFileName;	
	} 
	else if($_POST['FORMAT'] == 2) 
	{
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
		$file_name 		= '90_10 Calculation Disclosure.xlsx';
		$outputFileName = $dir.$file_name; 
		$outputFileName = str_replace(
		pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName );  

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

		$line 	= 1;	
		$index 	= 0;
		
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue("Campus: ".$campus_name);
		$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);

		$line++;	
		$index 	= -1;
		$heading[] = 'Last Name';
		$width[]   = 20;
		$heading[] = 'First Name';
		$width[]   = 20;
		$heading[] = 'Student ID';
		$width[]   = 20;
		$heading[] = 'Campus';
		$width[]   = 20;
		$heading[] = 'Program Code';
		$width[]   = 20;
		$heading[] = 'Program Description';
		$width[]   = 20;
		$heading[] = 'Program Group';
		$width[]   = 20;
		$heading[] = 'Student Status';
		$width[]   = 20;
		$heading[] = 'Prior Ledger Balance';
		$width[]   = 20;
		$heading[] = 'Subsidized Loans';
		$width[]   = 20;
		$heading[] = 'Unsubsidized Loans';
		$width[]   = 20;
		$heading[] = 'Parent Plus Loans';
		$width[]   = 20;
		$heading[] = 'Pell Grants';
		$width[]   = 20;
		$heading[] = 'Federal SEOG';
		$width[]   = 20;
		$heading[] = 'Title IV Other';
		$width[]   = 20;
		// $heading[] = 'Student Title IV Revenue'; //DIAM-2321
		// $width[]   = 20;
		$heading[] = 'R2T4';
		$width[]   = 20;
		$heading[] = 'Adjusted Title IV Revenue';
		$width[]   = 20;
		$heading[] = 'Non-Federal Grant';
		$width[]   = 20;
		$heading[] = 'Job Training';
		$width[]   = 20;
		$heading[] = 'Savings Plans';
		$width[]   = 20;
		$heading[] = 'Institutional Scholarships';
		$width[]   = 20;
		$heading[] = 'Cash Payments';
		$width[]   = 20;
		$heading[] = 'Non-Title IV Other';
		$width[]   = 20;
		$heading[] = 'Student Non-Title IV Revenue';
		$width[]   = 20;
		$heading[] = 'Revenue Other: Activities';
		$width[]   = 20;
		$heading[] = 'Revenue Other: Non-Eligible Funds';
		$width[]   = 20;
		$heading[] = 'Revenue Other: Allowable Payments';
		$width[]   = 20;
		$heading[] = 'Revenue Other';
		$width[]   = 20;
		$heading[] = 'Revenue From Other Sources';
		$width[]   = 20;
		$heading[] = 'Total Non-Title IV Revenue';
		$width[]   = 20;
		$heading[] = 'Total Revenue';
		$width[]   = 20;
		$heading[] = '90/10 Ratio Non-Title IV';
		$width[]   = 20;
		$heading[] = '90/10 Ratio Title IV';
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


		$res = $db->Execute("CALL ACCT90101(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".$ST."','".$ET."', 'EXCEL')");
		// CALL ACCT90101(97, '175', '2025-01-01','2025-11-25', 'Combine All Programs And Campuses')
		while (!$res->EOF) {
			$line++;
			$index = -1;
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_LAST_NAME']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_FIRST_NAME']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ID']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CAMPUS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_CODE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_DESCRIPTION']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_GROUP']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_STATUS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Prior_Ledger_Balance']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FederalSubsidizedLoans']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FederalUnsubsidizedLoans']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FederalParentPlusLoan']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FederalPellGrants']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FederalShareofSEOG']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TitleIVOther']);
			
			// $index++;
			// $cell_no = $cell[$index].$line;
			// $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['StudentTitleIVRevenue']); //DIAM-2321
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ReturntoTitleIVFunds']);//DIAM-2321
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['AdjustedTitleIVRevenue']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['GrantFundsfromNonFederalPublicAgenciesorPrivateSources']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ContractualArrangementsforJobTraningtoLowIncomeIndividuals']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FundsfromSavingPlansforEducationalExpenses']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['InstitutionalScholarships']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CashPaymentsFromStudents']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NonTitleIVOther']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['StudentNonTitleIVRevenue']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['RevenueOtherActivities']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['RevenueOtherNonEligibleFunds']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['RevenueOtherAllowablePayments']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['RevenueOther']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['RevenueFromOtherSources']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TotalNonTitleIVRevenue']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TotalRevenue']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Ratio_NonTitle_IV']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Ratio_Title_IV']);
			
			$res->MoveNext();
		}

		$objWriter->save($outputFileName);
		$objPHPExcel->disconnectWorksheets();
		header("location:".$outputFileName);

	}
	
	exit;
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
	<title><?=MNU_90_10_CALCULATION_DISCLOSURE ?> | <?=$title?></title>
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
                        <h4 class="text-themecolor">
							<?=MNU_90_10_CALCULATION_DISCLOSURE ?>
						</h4>
                    </div>
                </div>
				
				<form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col-md-3 ">
											<?=CAMPUS?>
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple >
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-3">
											<?=RUN_BY?>
											<select id="RUN_BY" name="RUN_BY" class="form-control" >
												<option value="6" >All Campuses - All Programs and Campuses combined On One Page</option>
												<option value="3" >All Programs - All Programs Combined On One Page</option>
												<option value="4" >Campus - One Program Per Page (Selected Campuses Combined)</option>
												<option value="5" >Campus - One Program Group Per Page (Selected Campuses Combined)</option>
												<option value="1" >Program - One Program Per Page</option>
												<option value="2" >Program - One Program Group Per Page</option>
											</select>
										</div>
										
										<div class="col-md-2 ">
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
	<script src="../backend_assets/dist/js/jquery-migrate-1.0.0.js"></script>
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
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '',
			numberDisplayed: 1,
			nSelectedText: '<?=CAMPUS?> selected'
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
