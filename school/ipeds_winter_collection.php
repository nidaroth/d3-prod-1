<? 
// error_reporting(E_ALL);
// ini_set('display_errors',1);

require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/ipeds_winter_collection_setup.php");
require_once("../language/menu.php");
require_once("check_access.php");

$res_add_on = $db->Execute("SELECT COE,ECM,_1098T,_90_10,IPEDS,POPULATION_REPORT FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res_add_on->fields['IPEDS'] == 0 || check_access('MANAGEMENT_IPEDS') == 0){
	header("location:../index");
	exit;
}

$msg = '';	
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$timezone = $_SESSION['PK_TIMEZONE'];
	if($timezone == '' || $timezone == 0) {
		$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$timezone = $res->fields['PK_TIMEZONE'];
		if($timezone == '' || $timezone == 0)
			$timezone = 4;
	}
	
	$campus_name = "";
	$campus_cond = "";
	$campus_id	 = "";
	if(!empty($_POST['PK_CAMPUS'])){
		$PK_CAMPUS 	 = implode(",",$_POST['PK_CAMPUS']);
		$campus_cond = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
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
	
	$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
	$TIMEZONE = $res->fields['TIMEZONE'];
	
	if($_POST['FORMAT'] == 'FA'){
		if($_POST['STUDENT_FINANCIAL_AID'] == 2 || $_POST['STUDENT_FINANCIAL_AID'] == 3 || $_POST['STUDENT_FINANCIAL_AID'] == 4 || $_POST['STUDENT_FINANCIAL_AID'] == 6 || $_POST['STUDENT_FINANCIAL_AID'] == 8 || $_POST['STUDENT_FINANCIAL_AID'] == 9 || $_POST['STUDENT_FINANCIAL_AID'] == 11) {
			require_once '../global/mpdf/vendor/autoload.php';
			
			$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
			$SCHOOL_NAME = $res->fields['SCHOOL_NAME'];
			$PDF_LOGO 	 = $res->fields['PDF_LOGO'];
			
			$logo = "";
			if($PDF_LOGO != '')
				$logo = '<img src="'.$PDF_LOGO.'" height="50px" />';
				
			$res_type = $db->Execute("select AWARD_YEAR from M_AWARD_YEAR WHERE PK_AWARD_YEAR = '$_POST[PK_AWARD_YEAR]' ");
			$AWARD_YEAR = 'Collection: '.$res_type->fields['AWARD_YEAR'];
				
			if($_POST['STUDENT_FINANCIAL_AID'] == 2)
				$REPORT_NAME = "Student Financial Aid Section 1 : Part A";
			else if($_POST['STUDENT_FINANCIAL_AID'] == 3)
				$REPORT_NAME = "Student Financial Aid Section 1 : Part B";
			else if($_POST['STUDENT_FINANCIAL_AID'] == 4)
				$REPORT_NAME = "Student Financial Aid Section 1 : Part C";
			else if($_POST['STUDENT_FINANCIAL_AID'] == 6)
				$REPORT_NAME = "Student Financial Aid Section 1 : Part D";
			else if($_POST['STUDENT_FINANCIAL_AID'] == 8)
				$REPORT_NAME = "Student Financial Aid Section 1 : Part E";
			else if($_POST['STUDENT_FINANCIAL_AID'] == 9) 
				$REPORT_NAME = "Winter Section 1 Part Eb";
			else if($_POST['STUDENT_FINANCIAL_AID'] == 11)
				$REPORT_NAME = "Student Financial Aid Section 2 : Military";
				
			$header = '<table width="100%" >
							<tr>
								<td width="20%" valign="top" >'.$logo.'</td>
								<td width="35%" valign="top" style="font-size:20px" >'.$SCHOOL_NAME.'</td>
								<td width="45%" valign="top" >
									<table width="100%" >
										<tr>
											<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b><i>IPEDS Winter Collection</i></b></td>
										</tr>
										<tr>
											<td width="100%" align="right" style="font-size:13px;" ><i>'.$REPORT_NAME.'</i></td>
										</tr>
										<tr>
											<td width="100%" align="right" style="font-size:13px;" >'.$AWARD_YEAR.'</td>
										</tr>
										<tr>
											<td width="100%" align="right" style="font-size:13px;" >Campus(es): '.$campus_name.'</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>';
						
			
			$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$TIMEZONE,date_default_timezone_get());
						
			$footer = '<table width="100%" >
							<tr>
								<td width="33%" valign="top" style="font-size:10px;" ><i>'.$date.'</i></td>
								<td width="33%" valign="top" style="font-size:10px;" align="center" >COMP20004</td>
								<td width="33%" valign="top" style="font-size:10px;" align="right" ><i>Page {PAGENO} of {nb}</i></td>
							</tr>
						</table>';
			
			$mpdf = new \Mpdf\Mpdf([
				'margin_left' => 7,
				'margin_right' => 5,
				'margin_top' => 35,
				'margin_bottom' => 20,
				'margin_header' => 3,
				'margin_footer' => 10,
				'default_font' => 'helvetica',
				'default_font_size' => 10,
				'orientation' => 'P'
			]);
			$mpdf->autoPageBreak = true;
			
			$mpdf->SetHTMLHeader($header);
			$mpdf->SetHTMLFooter($footer);
			
			if($_POST['STUDENT_FINANCIAL_AID'] == 2) {
				$res = $db->Execute("CALL COMP20004(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].",'Section 1 Part A')");
				
				$txt = '<table border="1" cellspacing="0" cellpadding="1" style="font-size:10px;" width="100%">
							<tr>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="center" >01</td>
								<td colspan="2" style="border-bottom: 1px solid #CCC;" >Group 1 All undergraduate students</td>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['Section_1_Part_A_Group_1'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_A_Group_1'])).'</td>
							</tr>
							<tr>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="center" >&nbsp;</td>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="center" >01a</td>
								<td style="border-bottom: 1px solid #CCC;" >Of those in Group 1, those who are degree/certificate-seeking</td>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['Section_1_Part_A_Group_1'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_A_Group_1'])).'</td>
							</tr>
							<tr>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="center" >&nbsp;</td>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="center" >01b</td>
								<td style="border-bottom: 1px solid #CCC;" >Of those in Group 1, those who are non-degree/non-certificate-seeking</td>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['Section_1_Part_A_Group_1'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_A_Group_1'])).'</td>
							</tr>
							<tr>
							    <td width="10%" style="border-bottom: 1px solid #CCC;" align="center" >02</td>
								<td colspan="2" style="border-bottom: 1px solid #CCC;" >Group 2 Of those in Group 1, those who are full-time, first-time degree/certificate-seeking</td>
								<td width="10%" align="right" style="border-bottom: 1px solid #CCC;" >'.($res->fields['Section_1_Part_A_Group_2'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_A_Group_2'])).'</td>
							</tr>
							<tr>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="center" >&nbsp;</td>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="center" >02a</td>
								<td style="border-bottom: 1px solid #CCC;" >Of those in Group 2, those who were awarded any Federal Work Study, loans to students, or grant orscholarship aid from the federal government, state/local government, the institution, or other sources known to the institution</td>
								<td width="10%" align="right" style="border-bottom: 1px solid #CCC;" >'.($res->fields['Section_1_Part_A_Group_2a'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_A_Group_2a'])).'</td>
							</tr>
							<tr>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="center" >&nbsp;</td>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="center" >02b</td>
								<td style="border-bottom: 1px solid #CCC;" >Of those in Group 2, those who were awarded any loans to students or grant or scholarship aid from the federal government, state/local government, or the institution</td>
								<td width="10%" align="right" style="border-bottom: 1px solid #CCC;" >'.($res->fields['Section_1_Part_A_Group_2b'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_A_Group_2b'])).'</td>
							</tr>
							<tr>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="center" >03</td>
								<td colspan="2" style="border-bottom: 1px solid #CCC;" >Group 3 Of those in Group 2, those enrolled in your institution’s largest program who were awarded grant or scholarship aid from the following sources: the federal government, state/local government, or the institution</td>
								<td width="10%" align="right" style="border-bottom: 1px solid #CCC;" >'.($res->fields['Section_1_Part_A_Group_3'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_A_Group_3'])).'</td>
							</tr>
							<tr>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="center" >04</td>
								<td colspan="2" style="border-bottom: 1px solid #CCC;" >Group 4 Of those in Group 2, those enrolled in your institution’s largest program who were awarded any Title IV federal student aid</td>
								<td width="10%" align="right" style="border-bottom: 1px solid #CCC;" >'.($res->fields['Section_1_Part_A_Group_4'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_A_Group_4'])).'</td>
							</tr>
						</table>';
						
				$mpdf->WriteHTML($txt);
			} else if($_POST['STUDENT_FINANCIAL_AID'] == 3) {
				$res = $db->Execute("CALL COMP20004(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].",'Section 1 Part B')");
				
				$txt = '<table border="1" cellspacing="0" cellpadding="1" width="90%">

							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Information from Part A</td>
								<td width="15%" style="border-bottom: 1px solid #CCC;" align="center" >2021-2022</td>
							</tr>

							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Group 1: All undergraduate students<br>(This number is carried forward from Part A, Line 01.)</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['Section_1_Part_B_Group_2_COUNT'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_B_Group_2_COUNT'])).'</td>
							</tr>
							
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Of those in Group 1, those who are degree/certificate-seeking<br>(This number is carried forward from Part A, Line 01a.)</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['Section_1_Part_B_Group_2_COUNT'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_B_Group_2_COUNT'])).'</td>
							</tr>
							
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Of those in Group 1, those who are non-degree/non-certificate-seeking<br>(This number is carried forward from Part A, Line 01b.)</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['Section_1_Part_B_Group_3_COUNT'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_B_Group_3_COUNT'])).'</td>
							</tr>
						</table>
						<br><br>
						<table border="1" cellspacing="0" cellpadding="1" width="100%">
							<tr>
								<td colspan="14" style="border-bottom: 1px solid #CCC;" align="center" >2021-2022</td>
							</tr>

							<tr>
								<td width="6%" style="border-bottom: 1px solid #CCC;" >&nbsp;</td>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;</td>
								<td colspan="4" style="border-bottom: 1px solid #CCC;" align="center" >All undergraduate students (Group 1)</td>
								<td colspan="4" style="border-bottom: 1px solid #CCC;" align="center" >All degree/certificate-seeking undergraduates</td>
								<td colspan="4" style="border-bottom: 1px solid #CCC;" align="center" >All non-degree/non-certificate-seeking undergraduates</td>
							</tr>

							<tr>
								<td colspan="2" style="border-bottom: 1px solid #CCC;" align="center" >Aid Type</td>
								<td style="border-bottom: 1px solid #CCC;" >Number students who were awarded aid</td>
								<td style="border-bottom: 1px solid #CCC;" >Percentage of students who were awarded aid</td>
								<td style="border-bottom: 1px solid #CCC;" >Total amount of aid awarded</td>
								<td style="border-bottom: 1px solid #CCC;" >Average amount of aid awarded (Col.3/Col.1)</td>
								<td style="border-bottom: 1px solid #CCC;" >Number students who were awarded aid</td>
								<td style="border-bottom: 1px solid #CCC;" >Percentage of students who were awarded aid</td>
								<td style="border-bottom: 1px solid #CCC;" >Total amount of aid awarded</td>
								<td style="border-bottom: 1px solid #CCC;" >Average amount of aid awarded (Col.7/Col.5)</td>
								<td style="border-bottom: 1px solid #CCC;" >Number students who were awarded aid (Col.1-Col.5)</td>
								<td style="border-bottom: 1px solid #CCC;" >Percentage of students who were awarded aid</td>
								<td style="border-bottom: 1px solid #CCC;" >Total amount of aid awarded (Col.3-Col.7)</td>
								<td style="border-bottom: 1px solid #CCC;" >Average amount of aid awarded</td>
							</tr>

							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;</td>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >Col. 1</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >Col. 2</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >Col. 3</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >Col. 4</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >Col. 5</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >Col. 6</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >Col. 7</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >Col. 8</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >Col. 9</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >Col. 10</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >Col. 11</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >Col. 12</td>
							</tr>
							
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="center" >01</td>
								<td style="border-bottom: 1px solid #CCC;" >Grant or scholarship aid from the federal government, state/local government, the institution, and other sources known to the institution (Do NOT include federal student loans)</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['Section_1_Part_B_Group_1_COUNT'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_B_Group_1_COUNT'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								<td align="right" style="border-bottom: 1px solid #CCC;" >$ '.($res->fields['Section_1_Part_B_Group_1_Amount'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_B_Group_1_Amount'],2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['Section_1_Part_B_Group_1_COUNT'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_B_Group_1_COUNT'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								<td align="right" style="border-bottom: 1px solid #CCC;" >$ '.($res->fields['Section_1_Part_B_Group_1_Amount'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_B_Group_1_Amount'],2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['Section_1_Part_B_Group_1_COUNT'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_B_Group_1_COUNT'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								<td align="right" style="border-bottom: 1px solid #CCC;" >$ '.($res->fields['Section_1_Part_B_Group_1_Amount'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_B_Group_1_Amount'],2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								
							</tr>
							
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="center" >02</td>
								<td style="border-bottom: 1px solid #CCC;" >Federal Pell Grants</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['Section_1_Part_B_Group_2_COUNT'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_B_Group_2_COUNT'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								<td align="right" style="border-bottom: 1px solid #CCC;" >$ '.($res->fields['Section_1_Part_B_Group_2_Amount'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_B_Group_2_Amount'],2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['Section_1_Part_B_Group_2_COUNT'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_B_Group_2_COUNT'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								<td align="right" style="border-bottom: 1px solid #CCC;" >$ '.($res->fields['Section_1_Part_B_Group_2_Amount'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_B_Group_2_Amount'],2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['Section_1_Part_B_Group_2_COUNT'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_B_Group_2_COUNT'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								<td align="right" style="border-bottom: 1px solid #CCC;" >$ '.($res->fields['Section_1_Part_B_Group_2_Amount'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_B_Group_2_Amount'],2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								
							</tr>
							
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="center" >03</td>
								<td style="border-bottom: 1px solid #CCC;" >Federal student loans</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['Section_1_Part_B_Group_3_COUNT'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_B_Group_3_COUNT'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								<td align="right" style="border-bottom: 1px solid #CCC;" >$ '.($res->fields['Section_1_Part_B_Group_3_Amount'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_B_Group_3_Amount'],2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['Section_1_Part_B_Group_3_COUNT'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_B_Group_3_COUNT'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								<td align="right" style="border-bottom: 1px solid #CCC;" >$ '.($res->fields['Section_1_Part_B_Group_3_Amount'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_B_Group_3_Amount'],2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['Section_1_Part_B_Group_3_COUNT'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_B_Group_3_COUNT'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								<td align="right" style="border-bottom: 1px solid #CCC;" >$ '.($res->fields['Section_1_Part_B_Group_3_Amount'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_B_Group_3_Amount'],2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								
							</tr>
						</table>';
						
				$mpdf->WriteHTML($txt);
			} else if($_POST['STUDENT_FINANCIAL_AID'] == 4) {
				//$res_section1_parta = $db->Execute("CALL COMP20004(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].",'Section 1 Part A')");

				$res = $db->Execute("CALL COMP20004(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].",'Section 1 Part C')");
				
				$txt = '<table border="1" cellspacing="0" cellpadding="1" width="100%">

							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Information from Part A</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >2021-2022</td>
							</tr>

							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Group 2: Full-time, first-time degree/certificate-seeking undergraduates<br>(This number is carried forward from Part A, Line 02)
								</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['Section_1_Group_2_PART_01'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Group_2_PART_01'])).'</td>
							</tr>

							<tr>
								<td style="border-bottom: 1px solid #CCC;" >
									Group 2a (This number is carried forward from Part A, Line 02a)<br>
									&nbsp;&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp;&nbsp;&nbsp; Of those in Group 2, those who were awarded:<br>
									&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp; Federal Work Study<br>
									&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp; Loans to students<br>
									&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp; Grant or scholarship aid from the federal government, state/local government, or the institution<br>
									&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp; Grant or scholarship aid from other sources known to the institution
								</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['Section_1_Part_A_Group_2a'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_A_Group_2a'])).'</td>
							</tr>
							
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >
								  	Group 2b (This number is carried forward from Part A, Line 02b)<br>
									&nbsp;&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp;&nbsp;&nbsp; Of those in Group 2, those who were awarded:<br>
									&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp; Loans to students<br>
									&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp; Grant or scholarship aid from the federal government, state/local government, or the institution<br>
								</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['Section_1_Part_A_Group_2b'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_A_Group_2b'])).'</td>
							</tr>
							
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >
									Group 3 (This number is carried forward from Part A, Line 03)<br>
									&nbsp;&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp;&nbsp;&nbsp; Of those in Group 2, those who were awarded:<br>
									&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp; Grant or scholarship aid from the federal government, state/local government, or the institution<br>
								</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['Section_1_Part_A_Group_3'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_A_Group_3'])).'</td>
							</tr>
						</table>
						<br><br>
						<table border="1" cellspacing="0" cellpadding="1" width="100%">
							<tr>
								<td width="7%" style="border-bottom: 1px solid #CCC;" >&nbsp;</td>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;</td>
								<td colspan="4" style="border-bottom: 1px solid #CCC;" align="center" >2021-2022</td>
							</tr>

							<tr>
								<td colspan="2" style="border-bottom: 1px solid #CCC;" align="center" >Aid Type</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >Number of Group 2 students who were awarded aid</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >Percentage of Group 2 students who were awarded aid</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >Total amount of aid awarded to Group 2 students</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >Average amount of aid awarded to Group 2 students</td>
							</tr>
							
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >01</td>
								<td style="border-bottom: 1px solid #CCC;" >Grants or scholarships from the federal government, state/local government, or the institution</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								<td align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['Section_1_Part_A_Group_1'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_A_Group_1'])).'</td>
								
							</tr>
							
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >02</td>
								<td style="border-bottom: 1px solid #CCC;" >Federal Grants</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								<td align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['Section_1_Part_A_Group_2'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_A_Group_2'])).'</td>
								
							</tr>
							
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;</td>
								<td style="border-bottom: 1px solid #CCC;" >02a Federal Pell Grants</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['Section_1_Part_C_Group_2a_COUNT'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_C_Group_2a_COUNT'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								<td align="right" style="border-bottom: 1px solid #CCC;" >$ '.($res->fields['Section_1_Part_C_Group_2a_Amount'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_C_Group_2a_Amount'],2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								
							</tr>
							
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;</td>
								<td style="border-bottom: 1px solid #CCC;" >02b Other Federal Grants</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['Section_1_Part_C_Group_2b_COUNT'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_C_Group_2b_COUNT'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								<td align="right" style="border-bottom: 1px solid #CCC;" >$ '.($res->fields['Section_1_Part_C_Group_2b_Amount'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_C_Group_2b_Amount'],2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								
							</tr>
							
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >03</td>
								<td style="border-bottom: 1px solid #CCC;" >State/local government grants or scholarships<br />(includes fellowships/tuition waivers/exemptions</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['Section_1_Part_C_Group_3_COUNT'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_C_Group_3_COUNT'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								<td align="right" style="border-bottom: 1px solid #CCC;" >$ '.($res->fields['Section_1_Part_C_Group_3_Amount'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_C_Group_3_Amount'],2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								
							</tr>
							
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >04</td>
								<td style="border-bottom: 1px solid #CCC;" >Institutional grants or scholarships<br />(includes fellowships/tuition waivers/exemptions</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['Section_1_Part_C_Group_4_COUNT'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_C_Group_4_COUNT'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								<td align="right" style="border-bottom: 1px solid #CCC;" >$ '.($res->fields['Section_1_Part_C_Group_4_Amount'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_C_Group_4_Amount'],2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								
							</tr>
							
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >05</td>
								<td style="border-bottom: 1px solid #CCC;" >Loans to students</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['Section_1_Part_A_Group_5'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_A_Group_5'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								<td align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								
							</tr>
							
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;</td>
								<td style="border-bottom: 1px solid #CCC;" >05a Federal loans</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['Section_1_Part_C_Group_5a_COUNT'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_C_Group_5a_COUNT'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								<td align="right" style="border-bottom: 1px solid #CCC;" >$ '.($res->fields['Section_1_Part_C_Group_5a_Amount'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_C_Group_5a_Amount'],2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								
							</tr>
							
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;</td>
								<td style="border-bottom: 1px solid #CCC;" >05b Other loans (including private loans)</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['Section_1_Part_C_Group_5b_COUNT'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_C_Group_5b_COUNT'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								<td align="right" style="border-bottom: 1px solid #CCC;" >$ '.($res->fields['Section_1_Part_C_Group_5b_Amount'] == '' ? 0 : number_format_value_checker($res->fields['Section_1_Part_C_Group_5b_Amount'],2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >00</td>
								
							</tr>
							
						</table>';
						
				$mpdf->WriteHTML($txt);
			} else if($_POST['STUDENT_FINANCIAL_AID'] == 6) {
				$res = $db->Execute("CALL COMP20004(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].",'Section 1 Part D')");
				
				$txt = '<table border="1" cellspacing="0" cellpadding="1" width="100%">
							<tr>
								<td colspan="2" style="border-bottom: 1px solid #CCC;" >Information from Part A</td>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="center" >Prior year<br>2019-2020</td>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="center" >Prior year<br>2020-2021</td>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="center" >Current year<br>2021-2022</td>
							</tr>
							<tr>
								<td colspan="2" style="border-bottom: 1px solid #CCC;" >Group 3<br>Full-time, first-time degree/certificate-seeking undergraduate students enrolled in your institution’s largest program who were awarded grant or scholarship aid from the following sources: the federal government, state/local government, or the institution (This number is carried forward from Part A, Line 03)</td>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="right" ></td>
							</tr>
							<tr>
								<td width="7%" style="border-bottom: 1px solid #CCC;" align="center" >01</td>
								<td style="border-bottom: 1px solid #CCC;" >Report the number of Group 3 students with the following living arrangements</td>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="center" >Prior year<br>2019-2020</td>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="center" >Prior year<br>2020-2021</td>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="center" >Current year<br>2021-2022</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" >01a On-campus</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" >01b Off-campus (with family)</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" >01c Off-campus (not with family)</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" >01d Unknown</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="center" >02</td>
								<td style="border-bottom: 1px solid #CCC;" >Report the total amount of grant or scholarship aid from the federal government, state/local government, or the institution awarded to Group 3 students</td>
								<td style="border-bottom: 1px solid #CCC;" ></td>
								<td style="border-bottom: 1px solid #CCC;" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >{total_count}</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="center" >03</td>
								<td style="border-bottom: 1px solid #CCC;" >Average grant or scholarship aid from the federal government, state/local government, or the institution awarded to Group 3 students</td>
								<td style="border-bottom: 1px solid #CCC;" ></td>
								<td style="border-bottom: 1px solid #CCC;" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >{total_count}</td>
							</tr>';
							
							$total = 0;
							/*while (!$res->EOF) {
								$total += $res->fields['CountOfStudentNo'];
								$txt .= '<tr>
											<td style="border-bottom: 1px solid #CCC;" ></td>
											<td style="border-bottom: 1px solid #CCC;" >'.$res->fields['LivingArrangements'].'</td>
											<td style="border-bottom: 1px solid #CCC;" ></td>
											<td style="border-bottom: 1px solid #CCC;" ></td>
											<td style="border-bottom: 1px solid #CCC;" align="right" >'.number_format_value_checker($res->fields['CountOfStudentNo']).'</td>
										</tr>';
								$res->MoveNext();
							}*/
				$txt .= '</table>';
				
				$txt = str_replace("{total_count}",$total,$txt);
						
				$mpdf->WriteHTML($txt);
			} else if($_POST['STUDENT_FINANCIAL_AID'] == 8) {
				$res = $db->Execute("CALL COMP20004(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].",'Section 1 Part Ea')");
				
				$txt = '<table border="1" cellspacing="0" cellpadding="1" width="100%">
							<tr>
								<td colspan="2" style="border-bottom: 1px solid #CCC;" >Information from Part A</td>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="center" >Prior year<br>2019-2020</td>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="center" >Prior year<br>2020-2021</td>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="center" >Current year<br>2021-2022</td>
							</tr>
							<tr>
								<td colspan="2" style="border-bottom: 1px solid #CCC;" >Group 4<br>Full-time, first-time degree/certificate-seeking undergraduate students enrolled in your institution’s largest program who were awarded any Title IV federal student aid (This number is carried forward from Part A, Line 04)</td>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="right" ></td>
							</tr>
							<tr>
								<td width="7%" style="border-bottom: 1px solid #CCC;" align="center" >01</td>
								<td style="border-bottom: 1px solid #CCC;" >Report the number of Group 3 students with the following living arrangements</td>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="center" >Prior year<br>2019-2020</td>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="center" >Prior year<br>2020-2021</td>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="center" >Current year<br>2021-2022</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" >01a On-campus</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >{total_count}</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" >01b Off-campus (with family)</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >{total_count}</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" >01c Off-campus (not with family)</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >{total_count}</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" >01d Unknown</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >{total_count}</td>
							</tr>';
							
							$total = 0;
							/*while (!$res->EOF) {
								$total += $res->fields['CurrentYear'];
								$txt .= '<tr>
											<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
											<td style="border-bottom: 1px solid #CCC;" >'.$res->fields['LivingArrangements'].'</td>
											<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
											<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
											<td style="border-bottom: 1px solid #CCC;" align="right" >'.number_format_value_checker($res->fields['CurrentYear']).'</td>
										</tr>';
								$res->MoveNext();
							}*/
				$txt .= '</table>
						 <br>
						 <table border="1" cellspacing="0" cellpadding="1" width="100%">
							<tr>
								<td colspan="3" style="border-bottom: 1px solid #CCC;" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >Number of students who were awarded any Title IV aid (Group 4)</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >Of those in Column 1, the number who were awarded any grant or scholarship aid from the following sources: the federal government, state/local government, or the institution</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >Of those in Column 1, the total amount of grant or scholarship aid awarded from the following sources: the federal government, state/local government, or the institution</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >Average amount of federal, state/local, and institutional grant or scholarship aid awarded to Group 4 students</td>
							</tr>
							<tr>
								<td colspan="7" style="border-bottom: 1px solid #CCC;" align="center" >2019-2020</td>
							</tr>
							<tr>
								<td width="7%" style="border-bottom: 1px solid #CCC;" align="center" >02</td>
								<td colspan="2" style="border-bottom: 1px solid #CCC;" align="center" >Income Level</td>
								<td style="border-bottom: 1px solid #CCC;"align="center" >Col. 1</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >Col. 2</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >Col. 3</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >Col. 4</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="center" ></td>
								<td width="7%" style="border-bottom: 1px solid #CCC;" align="center" >02a</td>
								<td width="25%" style="border-bottom: 1px solid #CCC; padding-left:5px;" align="left" >$0 - $30,000</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="center" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >02b</td>
								<td style="border-bottom: 1px solid #CCC;padding-left:5px;" align="left" >$30,000 - $48,000</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="center" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >02c</td>
								<td style="border-bottom: 1px solid #CCC;padding-left:5px;" align="left" >$48,000 - $75,000</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="center" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >02d</td>
								<td style="border-bottom: 1px solid #CCC;padding-left:5px;" align="left" >$75,000 - $110,000</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="center" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >02e</td>
								<td style="border-bottom: 1px solid #CCC;padding-left:5px;" align="left" >$110,000 and more</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="center" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >02f</td>
								<td style="border-bottom: 1px solid #CCC;padding-left:5px;" align="left" >Total All Income Levels</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
							</tr>
							<tr>
								<td colspan="7" style="border-bottom: 1px solid #CCC;" align="center" >2020-2021</td>
							</tr>
							<tr>
								<td width="7%" style="border-bottom: 1px solid #CCC;" align="center" >03</td>
								<td colspan="2" style="border-bottom: 1px solid #CCC;" align="center" >Income Level</td>
								<td style="border-bottom: 1px solid #CCC;"align="center" >Col. 1</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >Col. 2</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >Col. 3</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >Col. 4</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="center" ></td>
								<td width="7%" style="border-bottom: 1px solid #CCC;" align="center" >03a</td>
								<td width="25%" style="border-bottom: 1px solid #CCC;padding-left:5px;" align="left" >$0 - $30,000</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="center" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >03b</td>
								<td style="border-bottom: 1px solid #CCC;padding-left:5px;" align="left" >$30,000 - $48,000</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="center" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >03c</td>
								<td style="border-bottom: 1px solid #CCC;padding-left:5px;" align="left" >$48,000 - $75,000</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="center" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >03d</td>
								<td style="border-bottom: 1px solid #CCC;padding-left:5px;" align="left" >$75,000 - $110,000</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="center" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >03e</td>
								<td style="border-bottom: 1px solid #CCC;padding-left:5px;" align="left" >$110,000 and more</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="center" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >03f</td>
								<td style="border-bottom: 1px solid #CCC;padding-left:5px;" align="left" >Total All Income Levels</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
							</tr>
							<tr>
								<td colspan="7" style="border-bottom: 1px solid #CCC;" align="center" >2021-2022</td>
							</tr>
							<tr>
								<td width="7%" style="border-bottom: 1px solid #CCC;" align="center" >04</td>
								<td colspan="2" style="border-bottom: 1px solid #CCC;" align="center" >Income Level</td>
								<td style="border-bottom: 1px solid #CCC;"align="center" >Col. 1</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >Col. 2</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >Col. 3</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >Col. 4</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="center" ></td>
								<td width="7%" style="border-bottom: 1px solid #CCC;" align="center" >04a</td>
								<td width="25%" style="border-bottom: 1px solid #CCC;padding-left:5px;" align="left" >$0 - $30,000</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="center" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >04b</td>
								<td style="border-bottom: 1px solid #CCC;padding-left:5px;" align="left" >$30,000 - $48,000</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="center" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >04c</td>
								<td style="border-bottom: 1px solid #CCC;padding-left:5px;" align="left" >$48,000 - $75,000</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="center" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >04d</td>
								<td style="border-bottom: 1px solid #CCC;padding-left:5px;" align="left" >$75,000 - $110,000</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="center" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >04e</td>
								<td style="border-bottom: 1px solid #CCC;padding-left:5px;" align="left" >$110,000 and more</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="center" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >04f</td>
								<td style="border-bottom: 1px solid #CCC;padding-left:5px;" align="left" >Total All Income Levels</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" ></td>
							</tr>

						 </table>';
				
				$txt = str_replace("{total_count}",$total,$txt);
						
				$mpdf->WriteHTML($txt);
			} else if($_POST['STUDENT_FINANCIAL_AID'] == 9) {
				$res = $db->Execute("CALL COMP20004(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].",'Section 1 Part Eb')");
				
				$total_1 = 0;
				$total_2 = 0;
				$total_3 = 0;
				$txt = '<table border="1" cellspacing="0" cellpadding="1" width="100%">
							<tr>
								<td width="15%" ><b>2020-2021</b></td>
								<td width="85%" >
									<table border="0" cellspacing="0" cellpadding="8" width="100%">
										<tr>
											<td width="40%" style="border-bottom: 1px solid #CCC;" valign="bottom" ><b>Income Level</b></td>
											<td width="20%" style="border-bottom: 1px solid #CCC;" valign="bottom" align="right" ><b>Number of students who were awarded any Title IV aid</b></td>
											<td width="20%" style="border-bottom: 1px solid #CCC;" valign="bottom" align="right"  ><b>Number who were awarded any grant or scholarship aid</b></td>
											<td width="20%" style="border-bottom: 1px solid #CCC;" valign="bottom" align="right"  ><b>Total amount of grant or scholarship aid awarded</b></td>
										</tr>';
										
										while (!$res->EOF) {
											$total_1 += $res->fields['StudentCount'];
											$total_2 += $res->fields['PartECount'];
											$total_3 += $res->fields['PartETotal'];
											$txt .= '<tr>
														<td style="border-bottom: 1px solid #CCC;" >'.$res->fields['IncomeLevelGroup'].'</td>
														<td style="border-bottom: 1px solid #CCC;" align="right" >'.number_format_value_checker($res->fields['StudentCount']).'</td>
														<td style="border-bottom: 1px solid #CCC;" align="right" >'.number_format_value_checker($res->fields['PartECount']).'</td>
														<td style="border-bottom: 1px solid #CCC;" align="right" >$ '.number_format_value_checker($res->fields['PartETotal'],2).'</td>
													</tr>';
											$res->MoveNext();
										}
										
								$txt .= '<tr>
											<td style="border-bottom: 1px solid #CCC;" ><b>Total All Income Levels</b></td>
											<td style="border-bottom: 1px solid #CCC;" align="right" >'.number_format_value_checker($total_1).'</td>
											<td style="border-bottom: 1px solid #CCC;" align="right" >'.number_format_value_checker($total_2).'</td>
											<td style="border-bottom: 1px solid #CCC;" align="right" >$ '.number_format_value_checker($total_3,2).'</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>';
						
				$mpdf->WriteHTML($txt);
			} else if($_POST['STUDENT_FINANCIAL_AID'] == 11) {
				$res = $db->Execute("CALL COMP20004(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].",'Section 2 Military')");
				
				$total_1 = 0;
				$total_2 = 0;
				$total_3 = 0;
				$txt = '
						Post-9/11 GI Bill Benefits
						<br><br>
						<table border="1" cellspacing="0" cellpadding="8" width="85%">
							<tr>
								<td style="border-bottom: 1px solid #CCC;padding-left:5px;" align="center">Type of benefit/assistance</td>
								<td style="border-bottom: 1px solid #CCC;padding-left:5px;" valign="bottom" align="center">Number of<br />students<br />receiving<br />benefits/assistance</td>
								<td style="border-bottom: 1px solid #CCC;padding-left:5px;" valign="bottom" align="center">Total dollar amount of benefits/assistance disbursed through the institution</td>
								<td style="border-bottom: 1px solid #CCC;padding-left:5px;" valign="bottom" align="center">Average dollar amount of benefits/assistance disbursed through the institution</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Undergraduate</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.number_format_value_checker($res->fields['P911UGC']).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >$ '.number_format_value_checker($res->fields['P911UGA'],2).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >0</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Graduate</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.number_format_value_checker($res->fields['P911GC']).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >$ '.number_format_value_checker($res->fields['P911GA'],2).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >0</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Total</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.number_format_value_checker(($res->fields['P911UGC'] + $res->fields['P911GC'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >$ '.number_format_value_checker(($res->fields['P911UGA'] + $res->fields['P911GA']),2).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >0</td>
							</tr>
						</table>
						<br><br>
						Department of Defense Tuition Assistance Program
						<br><br>
						<table border="1" cellspacing="0" cellpadding="8" width="85%">	
							<tr>
								<td style="border-bottom: 1px solid #CCC;padding-left:5px;" align="center">Type of benefit/assistance</td>
								<td style="border-bottom: 1px solid #CCC;padding-left:5px;" valign="bottom" align="center">Number of<br />students<br />receiving<br />benefits/assistance</td>
								<td style="border-bottom: 1px solid #CCC;padding-left:5px;" valign="bottom" align="center">Total dollar amount of benefits/assistance disbursed through the institution</td>
								<td style="border-bottom: 1px solid #CCC;padding-left:5px;" valign="bottom" align="center">Average dollar amount of benefits/assistance disbursed through the institution</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Undergraduate</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.number_format_value_checker($res->fields['DODUGC']).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >$ '.number_format_value_checker($res->fields['DODUGA'],2).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >0</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Graduate</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.number_format_value_checker($res->fields['DODGC']).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >$ '.number_format_value_checker($res->fields['DODGA'],2).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >0</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Total</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.number_format_value_checker(($res->fields['DODUGC'] + $res->fields['DODGC'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >$ '.number_format_value_checker(($res->fields['DODUGA'] + $res->fields['DODGA']),2).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >0</td>
							</tr>
						</table>';
						
				$mpdf->WriteHTML($txt);
			}
			
			$mpdf->Output($REPORT_NAME.'.pdf', 'D');
			
		} else if($_POST['STUDENT_FINANCIAL_AID'] == 1 || $_POST['STUDENT_FINANCIAL_AID'] == 5 || $_POST['STUDENT_FINANCIAL_AID'] == 7 || $_POST['STUDENT_FINANCIAL_AID'] == 10) {
			if($_POST['STUDENT_FINANCIAL_AID'] == 1) {
				$REPORT_NAME = "Section 1 Part A,B,C Datasheet";
			} else if($_POST['STUDENT_FINANCIAL_AID'] == 5) {
				$REPORT_NAME = "Section 1 Part D Datasheet";
			} else if($_POST['STUDENT_FINANCIAL_AID'] == 7) {
				$REPORT_NAME = "Section 1 Part E Datasheet";
			} else if($_POST['STUDENT_FINANCIAL_AID'] == 10) {
				$REPORT_NAME = "Section 2 Military Datasheet";
			}
			
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
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

			$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setIncludeCharts(TRUE);
			//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
			$objPHPExcel = new PHPExcel();
			$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			
			if($_POST['STUDENT_FINANCIAL_AID'] == 1) {
				$line 	= 1;	
				$index 	= -1;
		
				$res = $db->Execute("CALL COMP20004(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].",'Section 1 Part A,B,C Datasheet')");
				$heading = array_keys($res->fields);
				while (!$res->EOF) 
				{
					$index = -1;
					foreach ($heading as $key) 
					{
						if($key!='ROW_TYPE')
						{
							//Get Header column name and set styling 
							if($line==1)
							{
								
								$index++;
								$cell_no = $cell[$index].$line;
								$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($key);
								$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
								$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(20);
								$objPHPExcel->getActiveSheet()->freezePane('A1');
							}
							else
							{
								// Get data column value and set data
								$index++;
								$cell_no = $cell[$index].$line;
								$cellValue=$res->fields[$key];
								$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($cellValue);
							
							}	
						}
						else
						{
							echo 'Skip header';
						}
					}							
					$line++;
					
					$res->MoveNext();
				}
			} else if($_POST['STUDENT_FINANCIAL_AID'] == 5) {
				$line 	= 1;	
				$index 	= -1;
		
				$res = $db->Execute("CALL COMP20004(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].",'Section 1 Part D Datasheet')");
				$heading = array_keys($res->fields);
				while (!$res->EOF) 
				{
					$index = -1;
					foreach ($heading as $key) 
					{
						if($key!='ROW_TYPE')
						{
							//Get Header column name and set styling 
							if($line==1)
							{
								
								$index++;
								$cell_no = $cell[$index].$line;
								$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($key);
								$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
								$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(20);
								$objPHPExcel->getActiveSheet()->freezePane('A1');
							}
							else
							{
								// Get data column value and set data
								$index++;
								$cell_no = $cell[$index].$line;
								$cellValue=$res->fields[$key];
								$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($cellValue);
							
							}	
						}
						else
						{
							echo 'Skip header';
						}
					}							
					$line++;
					
					$res->MoveNext();
				}
			} else if($_POST['STUDENT_FINANCIAL_AID'] == 7) {
				$line 	= 1;	
				$index 	= -1;
				$res = $db->Execute("CALL COMP20004(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].",'Section 1 Part E Datasheet')");
				$heading = array_keys($res->fields);
				while (!$res->EOF) 
				{
					$index = -1;
					foreach ($heading as $key) 
					{
						if($key!='ROW_TYPE')
						{
							//Get Header column name and set styling 
							if($line==1)
							{
								
								$index++;
								$cell_no = $cell[$index].$line;
								$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($key);
								$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
								$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(20);
								$objPHPExcel->getActiveSheet()->freezePane('A1');
							}
							else
							{
								// Get data column value and set data
								$index++;
								$cell_no = $cell[$index].$line;
								$cellValue=$res->fields[$key];
								$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($cellValue);
							
							}	
						}
						else
						{
							echo 'Skip header';
						}
					}							
					$line++;
					
					$res->MoveNext();
				}
			} else if($_POST['STUDENT_FINANCIAL_AID'] == 10) {
				$line 	= 1;	
				$index 	= -1;

				$res = $db->Execute("CALL COMP20004(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].",'Section 2 Military Datasheet')");
				$heading = array_keys($res->fields);
				while (!$res->EOF) 
				{
					$index = -1;
					foreach ($heading as $key) 
					{
						if($key!='ROW_TYPE')
						{
							//Get Header column name and set styling 
							if($line==1)
							{
								
								$index++;
								$cell_no = $cell[$index].$line;
								$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($key);
								$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
								$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(20);
								$objPHPExcel->getActiveSheet()->freezePane('A1');
							}
							else
							{
								// Get data column value and set data
								$index++;
								$cell_no = $cell[$index].$line;
								$cellValue=$res->fields[$key];
								$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($cellValue);
							
							}	
						}
						else
						{
							echo 'Skip header';
						}
					}							
					$line++;
					
					$res->MoveNext();
				}
			}
			
			$objWriter->save($outputFileName);
			$objPHPExcel->disconnectWorksheets();
			header("location:".$outputFileName);
		}
	} else if($_POST['FORMAT'] == 'GR'){
		if($_POST['GRADUATION_RATES'] == 1) {
			if($_POST['GRADUATION_RATES'] == 1) {
				$REPORT_NAME = "Graduation Rates Datasheet";
			}
			
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
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

			$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setIncludeCharts(TRUE);
			//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
			$objPHPExcel = new PHPExcel();
			$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			
			if($_POST['GRADUATION_RATES'] == 1) {
				$line 	= 1;	
				$index 	= -1;
	
				$res = $db->Execute("CALL COMP20005(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".$_POST['GRADUATION_RATES_LENGTH']."', ".$_POST['PK_AWARD_YEAR'].",'Graduation Rates Datasheet')");
				$heading = array_keys($res->fields);
				while (!$res->EOF) 
				{
					$index = -1;
					foreach ($heading as $key) 
					{
						if($key!='ROW_TYPE')
						{
							//Get Header column name and set styling 
							if($line==1)
							{
								
								$index++;
								$cell_no = $cell[$index].$line;
								$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($key);
								$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
								$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(20);
								$objPHPExcel->getActiveSheet()->freezePane('A1');
							}
							else
							{
								// Get data column value and set data
								$index++;
								$cell_no = $cell[$index].$line;
								$cellValue=$res->fields[$key];
								$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($cellValue);
							
							}	
						}
						else
						{
							echo 'Skip header';
						}
					}							
					$line++;
					
					$res->MoveNext();
				}
			} 
			
			$objWriter->save($outputFileName);
			$objPHPExcel->disconnectWorksheets();
			header("location:".$outputFileName);
		} else if($_POST['GRADUATION_RATES'] == 2 || $_POST['GRADUATION_RATES'] == 3 || $_POST['GRADUATION_RATES'] == 4 || $_POST['GRADUATION_RATES'] == 5 || $_POST['GRADUATION_RATES'] == 6) {
			require_once '../global/mpdf/vendor/autoload.php';
			
			$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
			$SCHOOL_NAME = $res->fields['SCHOOL_NAME'];
			$PDF_LOGO 	 = $res->fields['PDF_LOGO'];
			
			$logo = "";
			if($PDF_LOGO != '')
				$logo = '<img src="'.$PDF_LOGO.'" height="50px" />';
				
			$res_type = $db->Execute("select AWARD_YEAR from M_AWARD_YEAR WHERE PK_AWARD_YEAR = '$_POST[PK_AWARD_YEAR]' ");
			$AWARD_YEAR = 'Collection: '.$res_type->fields['AWARD_YEAR'];
				
			if($_POST['GRADUATION_RATES'] == 2)
				$REPORT_NAME = "Winter Cohort";
			else if($_POST['GRADUATION_RATES'] == 3)
				$REPORT_NAME = "Winter Completers Within 150%";
			else if($_POST['GRADUATION_RATES'] == 4)
				$REPORT_NAME = "Winter Transfers/Exclusions";
			else if($_POST['GRADUATION_RATES'] == 5)
				$REPORT_NAME = "Winter Completers Within 100%";
			else if($_POST['GRADUATION_RATES'] == 6)
				$REPORT_NAME = "Winter FA Recipients";
				
			$header = '<table width="100%" >
							<tr>
								<td width="20%" valign="top" >'.$logo.'</td>
								<td width="35%" valign="top" style="font-size:20px" >'.$SCHOOL_NAME.'</td>
								<td width="45%" valign="top" >
									<table width="100%" >
										<tr>
											<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b><i>IPEDS - Winter Collection</i></b></td>
										</tr>
										<tr>
											<td width="100%" align="right" style="font-size:13px;" ><i>Graduation Rates : '.$REPORT_NAME.'</i></td>
										</tr>
										<tr>
											<td width="100%" align="right" style="font-size:13px;" >'.$AWARD_YEAR.'</td>
										</tr>
										<tr>
											<td width="100%" align="right" style="font-size:13px;" >Campus(es): '.$campus_name.'</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>';
						
			
			$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$TIMEZONE,date_default_timezone_get());
						
			$footer = '<table width="100%" >
							<tr>
								<td width="33%" valign="top" style="font-size:10px;" ><i>'.$date.'</i></td>
								<td width="33%" valign="top" style="font-size:10px;" align="center" >COMP20005</td>
								<td width="33%" valign="top" style="font-size:10px;" align="right" ><i>Page {PAGENO} of {nb}</i></td>
							</tr>
						</table>';
			
			$mpdf = new \Mpdf\Mpdf([
				'margin_left' => 7,
				'margin_right' => 5,
				'margin_top' => 35,
				'margin_bottom' => 20,
				'margin_header' => 3,
				'margin_footer' => 10,
				'default_font' => 'helvetica',
				'default_font_size' => 10,
				'orientation' => 'P'
			]);
			$mpdf->autoPageBreak = true;
			
			$mpdf->SetHTMLHeader($header);
			$mpdf->SetHTMLFooter($footer);
			
			if($_POST['GRADUATION_RATES'] == 2) {
				$men_1 		= 0;
				$men_2 		= 0;
				$men_3 		= 0;
				$men_4 		= 0;
				$men_5 		= 0;
				$men_6 		= 0;
				$men_7 		= 0;
				$men_9 		= 0;
				$men_99 	= 0;
				$men_tot 	= 0;

				$other_1 		= 0;
				$other_2 		= 0;
				$other_3 		= 0;
				$other_4 		= 0;
				$other_5 		= 0;
				$other_6 		= 0;
				$other_7 		= 0;
				$other_9 		= 0;
				$other_99 	= 0;
				$other_tot 	= 0;

				$unknown_1 		= 0;
				$unknown_2 		= 0;
				$unknown_3 		= 0;
				$unknown_4 		= 0;
				$unknown_5 		= 0;
				$unknown_6 		= 0;
				$unknown_7 		= 0;
				$unknown_9 		= 0;
				$unknown_99 	= 0;
				$unknown_tot 	= 0;
				
				$women_1 		= 0;
				$women_2 		= 0;
				$women_3 		= 0;
				$women_4 		= 0;
				$women_5 		= 0;
				$women_6 		= 0;
				$women_7 		= 0;
				$women_9 		= 0;
				$women_99 	= 0;
				$women_tot 	= 0;

				// Find previous 3 Year
				$res = $db->Execute("SELECT BEGIN_DATE FROM M_AWARD_YEAR where PK_AWARD_YEAR='".$_POST['PK_AWARD_YEAR']."' ");
				$aGetYear = $res->fields['BEGIN_DATE'];
				$aYear    = strtotime($aGetYear.'-3 year');
				$sGetFinalYear  = date("Y", $aYear);
				// End Find previous 3 Year

				$res = $db->Execute("CALL COMP20005(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".$_POST['GRADUATION_RATES_LENGTH']."', ".$_POST['PK_AWARD_YEAR'].",'Winter Cohort')");
				while (!$res->EOF) {
					if(strtolower($res->fields['GENDER']) == 'men') {
						if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'u.s. nonresident')
							$men_1 = $res->fields['C'];
						else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'hispanic/latino')
							$men_2 = $res->fields['C'];
						else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'american indian or alaska native')
							$men_3 = $res->fields['C'];
						else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'asian')
							$men_4 = $res->fields['C'];
						else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'black or african american')
							$men_5 = $res->fields['C'];
						else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'native hawaiian or other pacific islander')
							$men_6 = $res->fields['C'];
						else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'white')
							$men_7 = $res->fields['C'];
						else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'race and ethnicity unknown')
							$men_9 = $res->fields['C'];
						else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'two or more races')
							$men_99 = $res->fields['C'];
							
					} 
					else if(strtolower($res->fields['GENDER']) == 'women') {
						if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'u.s. nonresident')
							$women_1 = $res->fields['C'];
						else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'hispanic/latino')
							$women_2 = $res->fields['C'];
						else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'american indian or alaska native')
							$women_3 = $res->fields['C'];
						else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'asian')
							$women_4 = $res->fields['C'];
						else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'black or african american')
							$women_5 = $res->fields['C'];
						else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'native hawaiian or other pacific islander')
							$women_6 = $res->fields['C'];
						else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'white')
							$women_7 = $res->fields['C'];
						else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'race and ethnicity unknown')
							$women_9 = $res->fields['C'];
						else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'two or more races')
							$women_99 = $res->fields['C'];
					} else if(strtolower($res->fields['GENDER']) == 'other') {
						if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'u.s. nonresident')
							$other_1 = $res->fields['C'];
						else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'hispanic/latino')
							$other_2 = $res->fields['C'];
						else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'american indian or alaska native')
							$other_3 = $res->fields['C'];
						else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'asian')
							$other_4 = $res->fields['C'];
						else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'black or african american')
							$other_5 = $res->fields['C'];
						else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'native hawaiian or other pacific islander')
							$other_6 = $res->fields['C'];
						else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'white')
							$other_7 = $res->fields['C'];
						else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'race and ethnicity unknown')
							$other_9 = $res->fields['C'];
						else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'two or more races')
							$other_99 = $res->fields['C'];
					}
					elseif (strtolower($res->fields['GENDER']) == 'unknown') {
						if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'u.s. nonresident')
							$unknown_1 = $res->fields['C'];
						else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'hispanic/latino')
							$unknown_2 = $res->fields['C'];
						else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'american indian or alaska native')
							$unknown_3 = $res->fields['C'];
						else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'asian')
							$unknown_4 = $res->fields['C'];
						else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'black or african american')
							$unknown_5 = $res->fields['C'];
						else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'native hawaiian or other pacific islander')
							$unknown_6 = $res->fields['C'];
						else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'white')
							$unknown_7 = $res->fields['C'];
						else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'race and ethnicity unknown')
							$unknown_9 = $res->fields['C'];
						else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'two or more races')
							$unknown_99 = $res->fields['C'];
							
					}
					$res->MoveNext();
				}
				
				$men_tot 	= $men_1 + $men_2 + $men_3 + $men_4 + $men_5 + $men_6 + $men_7 + $men_9 + $men_99;
				$women_tot 	= $women_1 + $women_2 + $women_3 + $women_4 + $women_5 + $women_6 + $women_7 + $women_9 + $women_99;
				$tot		= $men_tot + $women_tot;

				$other_tot 	= $other_1 + $other_2 + $other_3 + $other_4 + $other_5 + $other_6 + $other_7 + $other_9 + $other_99;
				$unknown_tot       = $unknown_1 + $unknown_2 + $unknown_3 + $unknown_4 + $unknown_5 + $unknown_6 + $unknown_7 + $unknown_9 + $unknown_99;
				$other_unknown_tot = $other_tot + $unknown_tot;
				
				$txt = '
						Men
						<br><br>
						<table border="1" cellspacing="0" cellpadding="1" width="70%">

							<tr>
								<td width="55%" style="border-bottom: 1px solid #CCC;" ><b></b></td>
								<td width="15%" style="border-bottom: 1px solid #CCC;" align="center" >'.$sGetFinalYear.'</td>
							</tr>

							<tr>
								<td width="55%" style="border-bottom: 1px solid #CCC;" ><b></b></td>
								<td width="15%" style="border-bottom: 1px solid #CCC;" align="center" >(Column 01)</td>
							</tr>
							
							<tr>
								<td width="55%" style="border-bottom: 1px solid #CCC;" >U.S. Nonresident</td>
								<td width="15%" style="border-bottom: 1px solid #CCC;" align="right" >'.($men_1 == '' ? 0 : number_format_value_checker($men_1)).'</td>
							</tr>
							
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Hispanic/Latino</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_2 == '' ? 0 : number_format_value_checker($men_2)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >American Indian or Alaska Native</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_3 == '' ? 0 : number_format_value_checker($men_3)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Asian</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_4 == '' ? 0 : number_format_value_checker($men_4)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Black or African American</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_5 == '' ? 0 : number_format_value_checker($men_5)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Native Hawaiian or Other Pacific Islander</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_6 == '' ? 0 : number_format_value_checker($men_6)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >White</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_7 == '' ? 0 : number_format_value_checker($men_7)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Race and Ethnicity Unknown</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_9 == '' ? 0 : number_format_value_checker($men_9)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Two or more races</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_99 == '' ? 0 : number_format_value_checker($men_99)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Total Men</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_tot == '' ? 0 : number_format_value_checker($men_tot)).'</td>
							</tr>
						</table>
						<br>
						Women
						<br /><br />
						<table border="1" cellspacing="0" cellpadding="1" width="70%">
							<tr>
								<td width="55%" style="border-bottom: 1px solid #CCC;" ><b></b></td>
								<td width="15%" style="border-bottom: 1px solid #CCC;" align="center" >'.$sGetFinalYear.'</td>
							</tr>

							<tr>
								<td width="55%" style="border-bottom: 1px solid #CCC;" ><b></b></td>
								<td width="15%" style="border-bottom: 1px solid #CCC;" align="center" >(Column 01)</td>
							</tr>
							
							<tr>
								<td width="55%" style="border-bottom: 1px solid #CCC;" >U.S. Nonresident</td>
								<td width="15%" style="border-bottom: 1px solid #CCC;" align="right" >'.($women_1 == '' ? 0 : number_format_value_checker($women_1)).'</td>
							</tr>
							
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Hispanic/Latino</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_2 == '' ? 0 : number_format_value_checker($women_2)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >American Indian or Alaska Native</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_3 == '' ? 0 : number_format_value_checker($women_3)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Asian</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_4 == '' ? 0 : number_format_value_checker($women_4)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Black or African American</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_5 == '' ? 0 : number_format_value_checker($women_5)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Native Hawaiian or Other Pacific Islander</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_6 == '' ? 0 : number_format_value_checker($women_6)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >White</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_7 == '' ? 0 : number_format_value_checker($women_7)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Race and Ethnicity Unknown</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_9 == '' ? 0 : number_format_value_checker($women_9)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Two or more races</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_99 == '' ? 0 : number_format_value_checker($women_99)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Total Women</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_tot == '' ? 0 : number_format_value_checker($women_tot)).'</td>
							</tr>
						</table>
						<br /><br />
						<table border="1" cellspacing="0" cellpadding="1" width="70%">
							<tr>
								<td width="55%" style="border-bottom: 1px solid #CCC;" >Total Men + Women:</td>
								<td width="18%" style="border-bottom: 1px solid #CCC;" align="right" >'.($tot == '' ? 0 : number_format_value_checker($tot)).'</td>
							</tr>
						</table>
						<br /><br />
						<table border="1" cellspacing="0" cellpadding="1" width="70%">
							<tr>
								<td width="50%" style="border-bottom: 1px solid #CCC;" >Gender Unknown(i.e., gender information is not known or not collected).</td>
								<td width="20%" style="border-bottom: 1px solid #CCC;" align="right" >'.($other_tot == '' ? 0 : number_format_value_checker($other_tot)).'</td>
							</tr>
							<tr>
								<td width="50%" style="border-bottom: 1px solid #CCC;" >Another Gender(i.e. gender information is known but does not fall into either of the mutually exclusive binary categories provided [Men/Women])</td>
								<td width="20%" style="border-bottom: 1px solid #CCC;" align="right" >'.($unknown_tot == '' ? 0 : number_format_value_checker($unknown_tot)).'</td>
							</tr>
							<tr>
								<td width="50%" style="border-bottom: 1px solid #CCC;" >Total Gender unknown + Another gender</td>
								<td width="20%" style="border-bottom: 1px solid #CCC;" align="right" >'.($other_unknown_tot == '' ? 0 : number_format_value_checker($other_unknown_tot)).'</td>
							</tr>
						</table>';
				$mpdf->WriteHTML($txt);
			} else if($_POST['GRADUATION_RATES'] == 3) {
				$men_1_1 	= 0;
				$men_2_1 	= 0;
				$men_3_1 	= 0;
				$men_4_1 	= 0;
				$men_5_1 	= 0;
				$men_6_1 	= 0;
				$men_7_1 	= 0;
				$men_9_1 	= 0;
				$men_99_1 	= 0;
				$men_tot_1 	= 0;
				
				$men_1_2 	= 0;
				$men_2_2 	= 0;
				$men_3_2 	= 0;
				$men_4_2 	= 0;
				$men_5_2 	= 0;
				$men_6_2 	= 0;
				$men_7_2 	= 0;
				$men_9_2 	= 0;
				$men_99_2 	= 0;
				$men_tot_2 	= 0;
				
				$women_1_1 		= 0;
				$women_2_1 		= 0;
				$women_3_1 		= 0;
				$women_4_1 		= 0;
				$women_5_1 		= 0;
				$women_6_1 		= 0;
				$women_7_1 		= 0;
				$women_9_1 		= 0;
				$women_99_1 	= 0;
				$women_tot_1 	= 0;
				
				$women_1_2 		= 0;
				$women_2_2 		= 0;
				$women_3_2		= 0;
				$women_4_2 		= 0;
				$women_5_2 		= 0;
				$women_6_2 		= 0;
				$women_7_2 		= 0;
				$women_9_2 		= 0;
				$women_99_2 	= 0;
				$women_tot_2 	= 0;

				// Find previous 3 Year
				$res = $db->Execute("SELECT BEGIN_DATE FROM M_AWARD_YEAR where PK_AWARD_YEAR='".$_POST['PK_AWARD_YEAR']."' ");
				$aGetYear = $res->fields['BEGIN_DATE'];
				$aYear    = strtotime($aGetYear.'-3 year');
				$sGetFinalYear  = date("Y", $aYear);
				// End Find previous 3 Year
				
				$res = $db->Execute("CALL COMP20005(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".$_POST['GRADUATION_RATES_LENGTH']."', ".$_POST['PK_AWARD_YEAR'].",'Winter Completers within 150%')");
				while (!$res->EOF) {
					if(strtolower($res->fields['GENDER']) == 'men') {
						if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'u.s. nonresident') {
							$men_1_1 = $res->fields['LT2AY'];
							$men_1_2 = $res->fields['LT4AY'];
							$men_1_a = $res->fields['TOTAL'];
							$men_1_b = $res->fields['Full_time_cohert'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'hispanic/latino') {
							$men_2_1 = $res->fields['LT2AY'];
							$men_2_2 = $res->fields['LT4AY'];
							$men_2_a = $res->fields['TOTAL'];
							$men_2_b = $res->fields['Full_time_cohert'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'american indian or alaska native') {
							$men_3_1 = $res->fields['LT2AY'];
							$men_3_2 = $res->fields['LT4AY'];
							$men_3_a = $res->fields['TOTAL'];
							$men_3_b = $res->fields['Full_time_cohert'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'asian') {
							$men_4_1 = $res->fields['LT2AY'];
							$men_4_2 = $res->fields['LT4AY'];
							$men_4_a = $res->fields['TOTAL'];
							$men_4_b = $res->fields['Full_time_cohert'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'black or african american') {
							$men_5_1 = $res->fields['LT2AY'];
							$men_5_2 = $res->fields['LT4AY'];
							$men_5_a = $res->fields['TOTAL'];
							$men_5_b = $res->fields['Full_time_cohert'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'native hawaiian or other pacific islander') {
							$men_6_1 = $res->fields['LT2AY'];
							$men_6_2 = $res->fields['LT4AY'];
							$men_6_a = $res->fields['TOTAL'];
							$men_6_b = $res->fields['Full_time_cohert'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'white') {
							$men_7_1 = $res->fields['LT2AY'];
							$men_7_2 = $res->fields['LT4AY'];
							$men_7_a = $res->fields['TOTAL'];
							$men_7_b = $res->fields['Full_time_cohert'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'race and ethnicity unknown') {
							$men_9_1 = $res->fields['LT2AY'];
							$men_9_2 = $res->fields['LT4AY'];
							$men_9_a = $res->fields['TOTAL'];
							$men_9_b = $res->fields['Full_time_cohert'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'two or more races') {
							$men_99_1 = $res->fields['LT2AY'];
							$men_99_2 = $res->fields['LT4AY'];
							$men_99_a = $res->fields['TOTAL'];
							$men_99_b = $res->fields['Full_time_cohert'];
						}
					} else if(strtolower($res->fields['GENDER']) == 'women') {
						if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'u.s. nonresident') {
							$women_1_1 = $res->fields['LT2AY'];
							$women_1_2 = $res->fields['LT4AY'];
							$women_1_a = $res->fields['TOTAL'];
							$women_1_b = $res->fields['Full_time_cohert'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'hispanic/latino') {
							$women_2_1 = $res->fields['LT2AY'];
							$women_2_2 = $res->fields['LT4AY'];
							$women_2_a = $res->fields['TOTAL'];
							$women_2_b = $res->fields['Full_time_cohert'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'american indian or alaska native') {
							$women_3_1 = $res->fields['LT2AY'];
							$women_3_2 = $res->fields['LT4AY'];
							$women_3_a = $res->fields['TOTAL'];
							$women_3_b = $res->fields['Full_time_cohert'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'asian') {
							$women_4_1 = $res->fields['LT2AY'];
							$women_4_2 = $res->fields['LT4AY'];
							$women_4_a = $res->fields['TOTAL'];
							$women_4_b = $res->fields['Full_time_cohert'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'black or african american') {
							$women_5_1 = $res->fields['LT2AY'];
							$women_5_2 = $res->fields['LT4AY'];
							$women_5_a = $res->fields['TOTAL'];
							$women_5_b = $res->fields['Full_time_cohert'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'native hawaiian or other pacific islander') {
							$women_6_1 = $res->fields['LT2AY'];
							$women_6_2 = $res->fields['LT4AY'];
							$women_6_a = $res->fields['TOTAL'];
							$women_6_b = $res->fields['Full_time_cohert'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'white') {
							$women_7_1 = $res->fields['LT2AY'];
							$women_7_2 = $res->fields['LT4AY'];
							$women_7_a = $res->fields['TOTAL'];
							$women_7_b = $res->fields['Full_time_cohert'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'race and ethnicity unknown') {
							$women_9_1 = $res->fields['LT2AY'];
							$women_9_2 = $res->fields['LT4AY'];
							$women_9_a = $res->fields['TOTAL'];
							$women_9_b = $res->fields['Full_time_cohert'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'two or more races') {
							$women_99_1 = $res->fields['LT2AY'];
							$women_99_2 = $res->fields['LT4AY'];
							$women_99_a = $res->fields['TOTAL'];
							$women_99_b = $res->fields['Full_time_cohert'];
						}
					}
					$res->MoveNext();
				}
				
				$men_tot_1 	= $men_1_1 + $men_2_1 + $men_3_1 + $men_4_1 + $men_5_1 + $men_6_1 + $men_7_1 + $men_9_1 + $men_99_1;
				$men_tot_2 	= $men_1_2 + $men_2_2 + $men_3_2 + $men_4_2 + $men_5_2 + $men_6_2 + $men_7_2 + $men_9_2 + $men_99_2;
				$men_tot_a 	= $men_1_a + $men_2_a + $men_3_a + $men_4_a + $men_5_a + $men_6_a + $men_7_a + $men_9_a + $men_99_a;
				$men_tot_b 	= $men_1_b + $men_2_b + $men_3_b + $men_4_b + $men_5_b + $men_6_b + $men_7_b + $men_9_b + $men_99_b;
				
				$women_tot_1 	= $women_1_1 + $women_2_1 + $women_3_1 + $women_4_1 + $women_5_1 + $women_6_1 + $women_7_1 + $women_9_1 + $women_99_1;
				$women_tot_2 	= $women_1_2 + $women_2_2 + $women_3_2 + $women_4_2 + $women_5_2 + $women_6_2 + $women_7_2 + $women_9_2 + $women_99_2;
				$women_tot_a 	= $women_1_a + $women_2_a + $women_3_a + $women_4_a + $women_5_a + $women_6_a + $women_7_a + $women_9_a + $women_99_a;
				$women_tot_b 	= $women_1_b + $women_2_b + $women_3_b + $women_4_b + $women_5_b + $women_6_b + $women_7_b + $women_9_b + $women_99_b;
				
				$men_women_tot_1 = $men_tot_1 + $women_tot_1;
				$men_women_tot_2 = $men_tot_2 + $women_tot_2;
				$men_women_tot_a = $men_tot_a + $women_tot_a;
				$men_women_tot_b = $men_tot_b + $women_tot_b;

				$txt = '
						Men
						<br />
						<table border="1" cellspacing="0" cellpadding="1" width="90%">
							<tr>
								<td style="border-bottom: 1px solid #CCC;" ></td>
								<td colspan="4" style="border-bottom: 1px solid #CCC;" align="center" >'.$sGetFinalYear.'</td>
							</tr>

							<tr>
								<td style="border-bottom: 1px solid #CCC;" ></td>
								<td style="border-bottom: 1px solid #CCC;" ></td>
								<td colspan="2" style="border-bottom: 1px solid #CCC;" align="center" >Cohort students who completed their program within '.'150%'.' of normal time to completion</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" ></td>
							</tr>

							<tr>
								<td width="40%" style="border-bottom: 1px solid #CCC;" ></td>
								<td width="15%" style="border-bottom: 1px solid #CCC;" align="center" >Cohort</td>
								<td width="15%" style="border-bottom: 1px solid #CCC;" align="center" >Completers of programs of less than 2 academic yrs (or equivalent)</td>
								<td width="15%" style="border-bottom: 1px solid #CCC;" align="center" >Completers of programs of at least 2 but less than 4 academic yrs (or equivalent	)</b></td>
								<td width="15%" style="border-bottom: 1px solid #CCC;" align="center" >Total Completers Within 150%</td>
							</tr>

							<tr>
								<td style="border-bottom: 1px solid #CCC;" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >(Column 10)</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >(Column 11)</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >(Column 12)</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >(Column 29)</td>
							</tr>
							
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >U.S. Nonresident</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_1_b == '' ? 0 : number_format_value_checker($men_1_b)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_1_1 == '' ? 0 : number_format_value_checker($men_1_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_1_2 == '' ? 0 : number_format_value_checker($men_1_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_1_a == '' ? 0 : number_format_value_checker($men_1_a)).'</td>
							</tr>
							
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Hispanic/Latino</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_2_b == '' ? 0 : number_format_value_checker($men_2_b)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_2_1 == '' ? 0 : number_format_value_checker($men_2_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_2_2 == '' ? 0 : number_format_value_checker($men_2_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_2_a == '' ? 0 : number_format_value_checker($men_2_a)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >American Indian or Alaska Native</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_3_b == '' ? 0 : number_format_value_checker($men_3_b)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_3_1 == '' ? 0 : number_format_value_checker($men_3_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_3_2 == '' ? 0 : number_format_value_checker($men_3_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_3_a == '' ? 0 : number_format_value_checker($men_3_a)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Asian</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_4_b == '' ? 0 : number_format_value_checker($men_4_b)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_4_1 == '' ? 0 : number_format_value_checker($men_4_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_4_2 == '' ? 0 : number_format_value_checker($men_4_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_4_a == '' ? 0 : number_format_value_checker($men_4_a)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Black or African American</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_5_b == '' ? 0 : number_format_value_checker($men_5_b)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_5_1 == '' ? 0 : number_format_value_checker($men_5_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_5_2 == '' ? 0 : number_format_value_checker($men_5_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_5_a == '' ? 0 : number_format_value_checker($men_5_a)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Native Hawaiian or Other Pacific Islander</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_6_b == '' ? 0 : number_format_value_checker($men_6_b)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_6_1 == '' ? 0 : number_format_value_checker($men_6_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_6_2 == '' ? 0 : number_format_value_checker($men_6_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_6_a == '' ? 0 : number_format_value_checker($men_6_a)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >White</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_7_b == '' ? 0 : number_format_value_checker($men_7_b)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_7_1 == '' ? 0 : number_format_value_checker($men_7_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_7_2 == '' ? 0 : number_format_value_checker($men_7_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_7_a == '' ? 0 : number_format_value_checker($men_7_a)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Race and Ethnicity Unknown</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_9_b == '' ? 0 : number_format_value_checker($men_9_b)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_9_1 == '' ? 0 : number_format_value_checker($men_9_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_9_2 == '' ? 0 : number_format_value_checker($men_9_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_9_a == '' ? 0 : number_format_value_checker($men_9_a)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Two or more races</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_99_b == '' ? 0 : number_format_value_checker($men_99_b)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_99_1 == '' ? 0 : number_format_value_checker($men_99_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_99_2 == '' ? 0 : number_format_value_checker($men_99_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_99_a == '' ? 0 : number_format_value_checker($men_99_a)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Total Men</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_tot_b == '' ? 0 : number_format_value_checker($men_tot_b)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_tot_1 == '' ? 0 : number_format_value_checker($men_tot_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_tot_2 == '' ? 0 : number_format_value_checker($men_tot_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_tot_a == '' ? 0 : number_format_value_checker($men_tot_a)).'</td>
							</tr>
						</table>
						<br />
						Women
						<br />
						<table border="1" cellspacing="0" cellpadding="1" width="90%">
							<tr>
								<td style="border-bottom: 1px solid #CCC;" ></td>
								<td colspan="4" style="border-bottom: 1px solid #CCC;" align="center" >'.$sGetFinalYear.'</td>
							</tr>

							<tr>
								<td style="border-bottom: 1px solid #CCC;" ></td>
								<td style="border-bottom: 1px solid #CCC;" ></td>
								<td colspan="2" style="border-bottom: 1px solid #CCC;" align="center" >Cohort students who completed their program within '.'150%'.' of normal time to completion</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" ></td>
							</tr>

							<tr>
								<td width="40%" style="border-bottom: 1px solid #CCC;" ></td>
								<td width="15%" style="border-bottom: 1px solid #CCC;" align="center" >Cohort</td>
								<td width="15%" style="border-bottom: 1px solid #CCC;" align="center" >Completers of programs of less than 2 academic yrs (or equivalent)</td>
								<td width="15%" style="border-bottom: 1px solid #CCC;" align="center" >Completers of programs of at least 2 but less than 4 academic yrs (or equivalent	)</td>
								<td width="15%" style="border-bottom: 1px solid #CCC;" align="center" >Total Completers Within 150%</td>
							</tr>

							<tr>
								<td style="border-bottom: 1px solid #CCC;" ></td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >(Column 10)</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >(Column 11)</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >(Column 12)</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >(Column 29)</td>
							</tr>
							
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >U.S. Nonresident</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_1_b == '' ? 0 : number_format_value_checker($women_1_b)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_1_1 == '' ? 0 : number_format_value_checker($women_1_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_1_2 == '' ? 0 : number_format_value_checker($women_1_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_1_a == '' ? 0 : number_format_value_checker($women_1_a)).'</td>
							</tr>
							
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Hispanic/Latino</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_2_b == '' ? 0 : number_format_value_checker($women_2_b)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_2_1 == '' ? 0 : number_format_value_checker($women_2_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_2_2 == '' ? 0 : number_format_value_checker($women_2_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_2_a == '' ? 0 : number_format_value_checker($women_2_a)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >American Indian or Alaska Native</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_3_b == '' ? 0 : number_format_value_checker($women_3_b)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_3_1 == '' ? 0 : number_format_value_checker($women_3_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_3_2 == '' ? 0 : number_format_value_checker($women_3_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_3_a == '' ? 0 : number_format_value_checker($women_3_a)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Asian</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_4_b == '' ? 0 : number_format_value_checker($women_4_b)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_4_1 == '' ? 0 : number_format_value_checker($women_4_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_4_2 == '' ? 0 : number_format_value_checker($women_4_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_4_a == '' ? 0 : number_format_value_checker($women_4_a)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Black or African American</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_5_b == '' ? 0 : number_format_value_checker($women_5_b)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_5_1 == '' ? 0 : number_format_value_checker($women_5_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_5_2 == '' ? 0 : number_format_value_checker($women_5_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_5_a == '' ? 0 : number_format_value_checker($women_5_a)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Native Hawaiian or Other Pacific Islander</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_6_b == '' ? 0 : number_format_value_checker($women_6_b)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_6_1 == '' ? 0 : number_format_value_checker($women_6_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_6_2 == '' ? 0 : number_format_value_checker($women_6_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_6_a == '' ? 0 : number_format_value_checker($women_6_a)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >White</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_7_b == '' ? 0 : number_format_value_checker($women_7_b)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_7_1 == '' ? 0 : number_format_value_checker($women_7_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_7_2 == '' ? 0 : number_format_value_checker($women_7_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_7_a == '' ? 0 : number_format_value_checker($women_7_a)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Race and Ethnicity Unknown</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_9_b == '' ? 0 : number_format_value_checker($women_9_b)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_9_1 == '' ? 0 : number_format_value_checker($women_9_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_9_2 == '' ? 0 : number_format_value_checker($women_9_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_9_a == '' ? 0 : number_format_value_checker($women_9_a)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Two or more races</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_99_b == '' ? 0 : number_format_value_checker($women_99_b)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_99_1 == '' ? 0 : number_format_value_checker($women_99_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_99_2 == '' ? 0 : number_format_value_checker($women_99_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_99_a == '' ? 0 : number_format_value_checker($women_99_a)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Total Women</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_tot_b == '' ? 0 : number_format_value_checker($women_tot_b)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_tot_1 == '' ? 0 : number_format_value_checker($women_tot_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_tot_2 == '' ? 0 : number_format_value_checker($women_tot_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_tot_a == '' ? 0 : number_format_value_checker($women_tot_a)).'</td>
							</tr>
						</table>
						<br />
						<table border="1" cellspacing="0" cellpadding="1" width="90%">
							<tr>
								<td width="40%" style="border-top: 1px solid #CCC;border-bottom: 1px solid #CCC;" >Total Men + Women:</td>
								<td width="15%" style="border-top: 1px solid #CCC;border-bottom: 1px solid #CCC;" align="right" >'.($men_women_tot_b == '' ? 0 : number_format_value_checker($men_women_tot_b)).'</td>
								<td width="15%" style="border-top: 1px solid #CCC;border-bottom: 1px solid #CCC;" align="right" >'.($men_women_tot_1 == '' ? 0 : number_format_value_checker($men_women_tot_1)).'</td>
								<td width="15%" style="border-top: 1px solid #CCC;border-bottom: 1px solid #CCC;" align="right" >'.($men_women_tot_2 == '' ? 0 : number_format_value_checker($men_women_tot_2)).'</td>
								<td width="15%" style="border-top: 1px solid #CCC;border-bottom: 1px solid #CCC;" align="right" >'.($men_women_tot_a == '' ? 0 : number_format_value_checker($men_women_tot_a)).'</td>
							</tr>
						</table>';
				$mpdf->WriteHTML($txt);
			} else if($_POST['GRADUATION_RATES'] == 4) {
				$men_1_1 	= 0;
				$men_2_1 	= 0;
				$men_3_1 	= 0;
				$men_4_1 	= 0;
				$men_5_1 	= 0;
				$men_6_1 	= 0;
				$men_7_1 	= 0;
				$men_9_1 	= 0;
				$men_99_1 	= 0;
				$men_tot_1 	= 0;
				
				$men_1_2 	= 0;
				$men_2_2 	= 0;
				$men_3_2 	= 0;
				$men_4_2 	= 0;
				$men_5_2 	= 0;
				$men_6_2 	= 0;
				$men_7_2 	= 0;
				$men_9_2 	= 0;
				$men_99_2 	= 0;
				$men_tot_2 	= 0;
				
				$women_1_1 		= 0;
				$women_2_1 		= 0;
				$women_3_1 		= 0;
				$women_4_1 		= 0;
				$women_5_1 		= 0;
				$women_6_1 		= 0;
				$women_7_1 		= 0;
				$women_9_1 		= 0;
				$women_99_1 	= 0;
				$women_tot_1 	= 0;
				
				$women_1_2 		= 0;
				$women_2_2 		= 0;
				$women_3_2		= 0;
				$women_4_2 		= 0;
				$women_5_2 		= 0;
				$women_6_2 		= 0;
				$women_7_2 		= 0;
				$women_9_2 		= 0;
				$women_99_2 	= 0;
				$women_tot_2 	= 0;

				// Find previous 3 Year
				$res = $db->Execute("SELECT BEGIN_DATE FROM M_AWARD_YEAR where PK_AWARD_YEAR='".$_POST['PK_AWARD_YEAR']."' ");
				$aGetYear = $res->fields['BEGIN_DATE'];
				$aYear    = strtotime($aGetYear.'-3 year');
				$sGetFinalYear  = date("Y", $aYear);
				// End Find previous 3 Year
				
				$res = $db->Execute("CALL COMP20005(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".$_POST['GRADUATION_RATES_LENGTH']."', ".$_POST['PK_AWARD_YEAR'].",'Winter Transfers/Exclusions')");
				while (!$res->EOF) {
					if(strtolower($res->fields['GENDER']) == 'men') {
						if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'u.s. nonresident') {
							$men_1_1 = $res->fields['TRANSFER_OUT'];
							$men_1_2 = $res->fields['EXCLUSION'];
							$men_1_a = $res->fields['STILL_ACTIVE'];
							$men_1_b = $res->fields['NO_LONGER_ACTIVE'];
							$men_1_c = $res->fields['TOTAL'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'hispanic/latino') {
							$men_2_1 = $res->fields['TRANSFER_OUT'];
							$men_2_2 = $res->fields['EXCLUSION'];
							$men_2_a = $res->fields['STILL_ACTIVE'];
							$men_2_b = $res->fields['NO_LONGER_ACTIVE'];
							$men_2_c = $res->fields['TOTAL'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'american indian or alaska native') {
							$men_3_1 = $res->fields['TRANSFER_OUT'];
							$men_3_2 = $res->fields['EXCLUSION'];
							$men_3_a = $res->fields['STILL_ACTIVE'];
							$men_3_b = $res->fields['NO_LONGER_ACTIVE'];
							$men_3_c = $res->fields['TOTAL'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'asian') {
							$men_4_1 = $res->fields['TRANSFER_OUT'];
							$men_4_2 = $res->fields['EXCLUSION'];
							$men_4_a = $res->fields['STILL_ACTIVE'];
							$men_4_b = $res->fields['NO_LONGER_ACTIVE'];
							$men_4_c = $res->fields['TOTAL'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'black or african american') {
							$men_5_1 = $res->fields['TRANSFER_OUT'];
							$men_5_2 = $res->fields['EXCLUSION'];
							$men_5_a = $res->fields['STILL_ACTIVE'];
							$men_5_b = $res->fields['NO_LONGER_ACTIVE'];
							$men_5_c = $res->fields['TOTAL'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'native hawaiian or other pacific islander') {
							$men_6_1 = $res->fields['TRANSFER_OUT'];
							$men_6_2 = $res->fields['EXCLUSION'];
							$men_6_a = $res->fields['STILL_ACTIVE'];
							$men_6_b = $res->fields['NO_LONGER_ACTIVE'];
							$men_6_c = $res->fields['TOTAL'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'white') {
							$men_7_1 = $res->fields['TRANSFER_OUT'];
							$men_7_2 = $res->fields['EXCLUSION'];
							$men_7_a = $res->fields['STILL_ACTIVE'];
							$men_7_b = $res->fields['NO_LONGER_ACTIVE'];
							$men_7_c = $res->fields['TOTAL'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'race and ethnicity unknown') {
							$men_9_1 = $res->fields['TRANSFER_OUT'];
							$men_9_2 = $res->fields['EXCLUSION'];
							$men_9_a = $res->fields['STILL_ACTIVE'];
							$men_9_b = $res->fields['NO_LONGER_ACTIVE'];
							$men_9_c = $res->fields['TOTAL'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'two or more races') {
							$men_99_1 = $res->fields['TRANSFER_OUT'];
							$men_99_2 = $res->fields['EXCLUSION'];
							$men_99_a = $res->fields['STILL_ACTIVE'];
							$men_99_b = $res->fields['NO_LONGER_ACTIVE'];
							$men_99_c = $res->fields['TOTAL'];
						}
					} else if(strtolower($res->fields['GENDER']) == 'women') {
						if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'u.s. nonresident') {
							$women_1_1 = $res->fields['TRANSFER_OUT'];
							$women_1_2 = $res->fields['EXCLUSION'];
							$women_1_a = $res->fields['STILL_ACTIVE'];
							$women_1_b = $res->fields['NO_LONGER_ACTIVE'];
							$women_1_c = $res->fields['TOTAL'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'hispanic/latino') {
							$women_2_1 = $res->fields['TRANSFER_OUT'];
							$women_2_2 = $res->fields['EXCLUSION'];
							$women_2_a = $res->fields['STILL_ACTIVE'];
							$women_2_b = $res->fields['NO_LONGER_ACTIVE'];
							$women_2_c = $res->fields['TOTAL'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'american indian or alaska native') {
							$women_3_1 = $res->fields['TRANSFER_OUT'];
							$women_3_2 = $res->fields['EXCLUSION'];
							$women_3_a = $res->fields['STILL_ACTIVE'];
							$women_3_b = $res->fields['NO_LONGER_ACTIVE'];
							$women_3_c = $res->fields['TOTAL'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'asian') {
							$women_4_1 = $res->fields['TRANSFER_OUT'];
							$women_4_2 = $res->fields['EXCLUSION'];
							$women_4_a = $res->fields['STILL_ACTIVE'];
							$women_4_b = $res->fields['NO_LONGER_ACTIVE'];
							$women_4_c = $res->fields['TOTAL'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'black or african american') {
							$women_5_1 = $res->fields['TRANSFER_OUT'];
							$women_5_2 = $res->fields['EXCLUSION'];
							$women_5_a = $res->fields['STILL_ACTIVE'];
							$women_5_b = $res->fields['NO_LONGER_ACTIVE'];
							$women_5_c = $res->fields['TOTAL'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'native hawaiian or other pacific islander') {
							$women_6_1 = $res->fields['TRANSFER_OUT'];
							$women_6_2 = $res->fields['EXCLUSION'];
							$women_6_a = $res->fields['STILL_ACTIVE'];
							$women_6_b = $res->fields['NO_LONGER_ACTIVE'];
							$women_6_c = $res->fields['TOTAL'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'white') {
							$women_7_1 = $res->fields['TRANSFER_OUT'];
							$women_7_2 = $res->fields['EXCLUSION'];
							$women_7_a = $res->fields['STILL_ACTIVE'];
							$women_7_b = $res->fields['NO_LONGER_ACTIVE'];
							$women_7_c = $res->fields['TOTAL'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'race and ethnicity unknown') {
							$women_9_1 = $res->fields['TRANSFER_OUT'];
							$women_9_2 = $res->fields['EXCLUSION'];
							$women_9_a = $res->fields['STILL_ACTIVE'];
							$women_9_b = $res->fields['NO_LONGER_ACTIVE'];
							$women_9_c = $res->fields['TOTAL'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'two or more races') {
							$women_99_1 = $res->fields['TRANSFER_OUT'];
							$women_99_2 = $res->fields['EXCLUSION'];
							$women_99_a = $res->fields['STILL_ACTIVE'];
							$women_99_b = $res->fields['NO_LONGER_ACTIVE'];
							$women_99_c = $res->fields['TOTAL'];
						}
					}
					$res->MoveNext();
				}
				
				$men_tot_1 	= $men_1_1 + $men_2_1 + $men_3_1 + $men_4_1 + $men_5_1 + $men_6_1 + $men_7_1 + $men_9_1 + $men_99_1;
				$men_tot_2 	= $men_1_2 + $men_2_2 + $men_3_2 + $men_4_2 + $men_5_2 + $men_6_2 + $men_7_2 + $men_9_2 + $men_99_2;
				$men_tot_a 	= $men_1_a + $men_2_a + $men_3_a + $men_4_a + $men_5_a + $men_6_a + $men_7_a + $men_9_a + $men_99_a;
				$men_tot_b 	= $men_1_b + $men_2_b + $men_3_b + $men_4_b + $men_5_b + $men_6_b + $men_7_b + $men_9_b + $men_99_b;
				$men_tot_c 	= $men_1_c + $men_2_c + $men_3_c + $men_4_c + $men_5_c + $men_6_c + $men_7_c + $men_9_c + $men_99_c;
				
				$women_tot_1 	= $women_1_1 + $women_2_1 + $women_3_1 + $women_4_1 + $women_5_1 + $women_6_1 + $women_7_1 + $women_9_1 + $women_99_1;
				$women_tot_2 	= $women_1_2 + $women_2_2 + $women_3_2 + $women_4_2 + $women_5_2 + $women_6_2 + $women_7_2 + $women_9_2 + $women_99_2;
				$women_tot_a 	= $women_1_a + $women_2_a + $women_3_a + $women_4_a + $women_5_a + $women_6_a + $women_7_a + $women_9_a + $women_99_a;
				$women_tot_b 	= $women_1_b + $women_2_b + $women_3_b + $women_4_b + $women_5_b + $women_6_b + $women_7_b + $women_9_b + $women_99_b;
				$women_tot_c 	= $women_1_c + $women_2_c + $women_3_c + $women_4_c + $women_5_c + $women_6_c + $women_7_c + $women_9_c + $women_99_c;
				
				$men_women_tot_1 = $men_tot_1 + $women_tot_1; // TRANSFER_OUT
				$men_women_tot_2 = $men_tot_2 + $women_tot_2; // EXCLUSION
				$men_women_tot_a = $men_tot_a + $women_tot_a; // STILL_ACTIVE
				$men_women_tot_b = $men_tot_b + $women_tot_b; // NO_LONGER_ACTIVE
				$men_women_tot_c = $men_tot_c + $women_tot_c; // Total 150%

				$txt = '
						Men
						<br><br>
						<table border="1" cellspacing="0" cellpadding="1" width="96%">
							<tr>
								<td width="30%" style="border-bottom: 1px solid #CCC;" ></td>
								<td width="11%" colspan="6" style="border-bottom: 1px solid #CCC;" align="center" >'.$sGetFinalYear.'</td>
							</tr>

							<tr>
								<td width="30%" style="border-bottom: 1px solid #CCC;" ></td>
								<td width="11%" style="border-bottom: 1px solid #CCC;" align="center" >Cohort</td>
								<td width="11%" style="border-bottom: 1px solid #CCC;" align="center" >Total completers within 150%</td>
								<td width="11%" style="border-bottom: 1px solid #CCC;" align="center" >Total transfer-out students</td>
								<td width="11%" style="border-bottom: 1px solid #CCC;" align="center" >Total Exclusions</td>
								<td width="11%" style="border-bottom: 1px solid #CCC;" align="center" >Still Enrolled</td>
								<td width="11%" style="border-bottom: 1px solid #CCC;" align="center" >No longer enrolled</td>
							</tr>

							<tr>
								<td width="30%" style="border-bottom: 1px solid #CCC;" ></td>
								<td width="11%" style="border-bottom: 1px solid #CCC;" align="center" >(Column 10)</td>
								<td width="11%" style="border-bottom: 1px solid #CCC;" align="center" >(Column 29)</td>
								<td width="11%" style="border-bottom: 1px solid #CCC;" align="center" >(Column 30)</td>
								<td width="11%" style="border-bottom: 1px solid #CCC;" align="center" >(Column 45)</td>
								<td width="11%" style="border-bottom: 1px solid #CCC;" align="center" >(Column 51)</td>
								<td width="11%" style="border-bottom: 1px solid #CCC;" align="center" >(Column 52)</td>
							</tr>
							
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >U.S. Nonresident</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >0</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_1_c == '' ? 0 : number_format_value_checker($men_1_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_1_1 == '' ? 0 : number_format_value_checker($men_1_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_1_2 == '' ? 0 : number_format_value_checker($men_1_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_1_a == '' ? 0 : number_format_value_checker($men_1_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_1_b == '' ? 0 : number_format_value_checker($men_1_b)).'</td>
							</tr>
							
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Hispanic/Latino</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >0</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_2_c == '' ? 0 : number_format_value_checker($men_2_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_2_1 == '' ? 0 : number_format_value_checker($men_2_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_2_2 == '' ? 0 : number_format_value_checker($men_2_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_2_a == '' ? 0 : number_format_value_checker($men_2_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_2_b == '' ? 0 : number_format_value_checker($men_2_b)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >American Indian or Alaska Native</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >0</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_3_c == '' ? 0 : number_format_value_checker($men_3_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_3_1 == '' ? 0 : number_format_value_checker($men_3_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_3_2 == '' ? 0 : number_format_value_checker($men_3_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_3_a == '' ? 0 : number_format_value_checker($men_3_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_3_b == '' ? 0 : number_format_value_checker($men_3_b)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Asian</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >0</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_4_c == '' ? 0 : number_format_value_checker($men_4_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_4_1 == '' ? 0 : number_format_value_checker($men_4_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_4_2 == '' ? 0 : number_format_value_checker($men_4_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_4_a == '' ? 0 : number_format_value_checker($men_4_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_4_b == '' ? 0 : number_format_value_checker($men_4_b)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Black or African American</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >0</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_5_c == '' ? 0 : number_format_value_checker($men_5_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_5_1 == '' ? 0 : number_format_value_checker($men_5_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_5_2 == '' ? 0 : number_format_value_checker($men_5_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_5_a == '' ? 0 : number_format_value_checker($men_5_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_5_b == '' ? 0 : number_format_value_checker($men_5_b)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Native Hawaiian or Other Pacific Islander</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >0</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_6_c == '' ? 0 : number_format_value_checker($men_6_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_6_1 == '' ? 0 : number_format_value_checker($men_6_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_6_2 == '' ? 0 : number_format_value_checker($men_6_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_6_a == '' ? 0 : number_format_value_checker($men_6_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_6_b == '' ? 0 : number_format_value_checker($men_6_b)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >White</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >0</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_7_c == '' ? 0 : number_format_value_checker($men_7_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_7_1 == '' ? 0 : number_format_value_checker($men_7_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_7_2 == '' ? 0 : number_format_value_checker($men_7_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_7_a == '' ? 0 : number_format_value_checker($men_7_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_7_b == '' ? 0 : number_format_value_checker($men_7_b)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Race and Ethnicity Unknown</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >0</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_9_c == '' ? 0 : number_format_value_checker($men_9_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_9_1 == '' ? 0 : number_format_value_checker($men_9_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_9_2 == '' ? 0 : number_format_value_checker($men_9_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_9_a == '' ? 0 : number_format_value_checker($men_9_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_9_b == '' ? 0 : number_format_value_checker($men_9_b)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Two or more races</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >0</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_99_c == '' ? 0 : number_format_value_checker($men_99_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_99_1 == '' ? 0 : number_format_value_checker($men_99_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_99_2 == '' ? 0 : number_format_value_checker($men_99_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_99_a == '' ? 0 : number_format_value_checker($men_99_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_99_b == '' ? 0 : number_format_value_checker($men_99_b)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Total Men</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >0</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_tot_c == '' ? 0 : number_format_value_checker($men_tot_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_tot_1 == '' ? 0 : number_format_value_checker($men_tot_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_tot_2 == '' ? 0 : number_format_value_checker($men_tot_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_tot_a == '' ? 0 : number_format_value_checker($men_tot_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($men_tot_b == '' ? 0 : number_format_value_checker($men_tot_b)).'</td>
							</tr>
						</table>
						<br />
						Women
						<br /><br />
						<table border="1" cellspacing="0" cellpadding="1" width="96%">
							<tr>
								<td width="30%" style="border-bottom: 1px solid #CCC;" ></td>
								<td width="11%" colspan="6" style="border-bottom: 1px solid #CCC;" align="center" >'.$sGetFinalYear.'</td>
							</tr>

							<tr>
								<td width="30%" style="border-bottom: 1px solid #CCC;" ></td>
								<td width="11%" style="border-bottom: 1px solid #CCC;" align="center" >Cohort</td>
								<td width="11%" style="border-bottom: 1px solid #CCC;" align="center" >Total completers within 150%</td>
								<td width="11%" style="border-bottom: 1px solid #CCC;" align="center" >Total transfer-out students</td>
								<td width="11%" style="border-bottom: 1px solid #CCC;" align="center" >Total Exclusions</td>
								<td width="11%" style="border-bottom: 1px solid #CCC;" align="center" >Still Enrolled</td>
								<td width="11%" style="border-bottom: 1px solid #CCC;" align="center" >No longer enrolled</td>
							</tr>

							<tr>
								<td width="30%" style="border-bottom: 1px solid #CCC;" ></td>
								<td width="11%" style="border-bottom: 1px solid #CCC;" align="center" >(Column 10)</td>
								<td width="11%" style="border-bottom: 1px solid #CCC;" align="center" >(Column 29)</td>
								<td width="11%" style="border-bottom: 1px solid #CCC;" align="center" >(Column 30)</td>
								<td width="11%" style="border-bottom: 1px solid #CCC;" align="center" >(Column 45)</td>
								<td width="11%" style="border-bottom: 1px solid #CCC;" align="center" >(Column 51)</td>
								<td width="11%" style="border-bottom: 1px solid #CCC;" align="center" >(Column 52)</td>
							</tr>
							
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >U.S. Nonresident</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >0</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_1_c == '' ? 0 : number_format_value_checker($women_1_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_1_1 == '' ? 0 : number_format_value_checker($women_1_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_1_2 == '' ? 0 : number_format_value_checker($women_1_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_1_a == '' ? 0 : number_format_value_checker($women_1_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_1_b == '' ? 0 : number_format_value_checker($women_1_b)).'</td>
							</tr>
							
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Hispanic/Latino</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >0</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_2_c == '' ? 0 : number_format_value_checker($women_2_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_2_1 == '' ? 0 : number_format_value_checker($women_2_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_2_2 == '' ? 0 : number_format_value_checker($women_2_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_2_a == '' ? 0 : number_format_value_checker($women_2_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_2_b == '' ? 0 : number_format_value_checker($women_2_b)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >American Indian or Alaska Native</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >0</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_3_c == '' ? 0 : number_format_value_checker($women_3_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_3_1 == '' ? 0 : number_format_value_checker($women_3_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_3_2 == '' ? 0 : number_format_value_checker($women_3_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_3_a == '' ? 0 : number_format_value_checker($women_3_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_3_b == '' ? 0 : number_format_value_checker($women_3_b)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Asian</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >0</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_4_c == '' ? 0 : number_format_value_checker($women_4_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_4_1 == '' ? 0 : number_format_value_checker($women_4_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_4_2 == '' ? 0 : number_format_value_checker($women_4_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_4_a == '' ? 0 : number_format_value_checker($women_4_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_4_b == '' ? 0 : number_format_value_checker($women_4_b)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Black or African American</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >0</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_5_c == '' ? 0 : number_format_value_checker($women_5_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_5_1 == '' ? 0 : number_format_value_checker($women_5_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_5_2 == '' ? 0 : number_format_value_checker($women_5_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_5_a == '' ? 0 : number_format_value_checker($women_5_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_5_b == '' ? 0 : number_format_value_checker($women_5_b)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Native Hawaiian or Other Pacific Islander</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >0</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_6_c == '' ? 0 : number_format_value_checker($women_6_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_6_1 == '' ? 0 : number_format_value_checker($women_6_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_6_2 == '' ? 0 : number_format_value_checker($women_6_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_6_a == '' ? 0 : number_format_value_checker($women_6_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_6_b == '' ? 0 : number_format_value_checker($women_6_b)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >White</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >0</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_7_c == '' ? 0 : number_format_value_checker($women_7_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_7_1 == '' ? 0 : number_format_value_checker($women_7_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_7_2 == '' ? 0 : number_format_value_checker($women_7_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_7_a == '' ? 0 : number_format_value_checker($women_7_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_7_b == '' ? 0 : number_format_value_checker($women_7_b)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Race and Ethnicity Unknown</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >0</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_9_c == '' ? 0 : number_format_value_checker($women_9_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_9_1 == '' ? 0 : number_format_value_checker($women_9_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_9_2 == '' ? 0 : number_format_value_checker($women_9_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_9_a == '' ? 0 : number_format_value_checker($women_9_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_9_b == '' ? 0 : number_format_value_checker($women_9_b)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Two or more races</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >0</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_99_c == '' ? 0 : number_format_value_checker($women_99_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_99_1 == '' ? 0 : number_format_value_checker($women_99_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_99_2 == '' ? 0 : number_format_value_checker($women_99_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_99_a == '' ? 0 : number_format_value_checker($women_99_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_99_b == '' ? 0 : number_format_value_checker($women_99_b)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >Total Women</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >0</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_tot_c == '' ? 0 : number_format_value_checker($women_tot_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_tot_1 == '' ? 0 : number_format_value_checker($women_tot_1)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_tot_2 == '' ? 0 : number_format_value_checker($women_tot_2)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_tot_a == '' ? 0 : number_format_value_checker($women_tot_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($women_tot_b == '' ? 0 : number_format_value_checker($women_tot_b)).'</td>
							</tr>
						</table>
						<br /><br />
						<table border="1" cellspacing="0" cellpadding="1" width="96%">
							<tr>
								<td width="30%" style="border-bottom: 1px solid #CCC;" >Total Men + Women:</td>
								<td width="11%" style="border-bottom: 1px solid #CCC;" align="right" >0</td>
								<td width="11%" style="border-bottom: 1px solid #CCC;" align="right" >'.($men_women_tot_c == '' ? 0 : number_format_value_checker($men_women_tot_c)).'</td>
								<td width="11%" style="border-bottom: 1px solid #CCC;" align="right" >'.($men_women_tot_a == '' ? 0 : number_format_value_checker($men_women_tot_1)).'</td>
								<td width="11%" style="border-bottom: 1px solid #CCC;" align="right" >'.($men_women_tot_2 == '' ? 0 : number_format_value_checker($men_women_tot_2)).'</td>
								<td width="11%" style="border-bottom: 1px solid #CCC;" align="right" >'.($men_women_tot_a == '' ? 0 : number_format_value_checker($men_women_tot_a)).'</td>
								<td width="11%" style="border-bottom: 1px solid #CCC;" align="right" >'.($men_women_tot_b == '' ? 0 : number_format_value_checker($men_women_tot_b)).'</td>
							</tr>
						</table>';
				$mpdf->WriteHTML($txt);
			} else if($_POST['GRADUATION_RATES'] == 5) {
				$men_1_1 	= 0;
				$men_2_1 	= 0;
				$men_3_1 	= 0;
				$men_4_1 	= 0;
				$men_5_1 	= 0;
				$men_6_1 	= 0;
				$men_7_1 	= 0;
				$men_9_1 	= 0;
				$men_99_1 	= 0;
				$men_tot_1 	= 0;
				
				$men_1_2 	= 0;
				$men_2_2 	= 0;
				$men_3_2 	= 0;
				$men_4_2 	= 0;
				$men_5_2 	= 0;
				$men_6_2 	= 0;
				$men_7_2 	= 0;
				$men_9_2 	= 0;
				$men_99_2 	= 0;
				$men_tot_2 	= 0;
				
				$women_1_1 		= 0;
				$women_2_1 		= 0;
				$women_3_1 		= 0;
				$women_4_1 		= 0;
				$women_5_1 		= 0;
				$women_6_1 		= 0;
				$women_7_1 		= 0;
				$women_9_1 		= 0;
				$women_99_1 	= 0;
				$women_tot_1 	= 0;
				
				$women_1_2 		= 0;
				$women_2_2 		= 0;
				$women_3_2		= 0;
				$women_4_2 		= 0;
				$women_5_2 		= 0;
				$women_6_2 		= 0;
				$women_7_2 		= 0;
				$women_9_2 		= 0;
				$women_99_2 	= 0;
				$women_tot_2 	= 0;

				// Find previous 3 Year
				$res = $db->Execute("SELECT BEGIN_DATE FROM M_AWARD_YEAR where PK_AWARD_YEAR='".$_POST['PK_AWARD_YEAR']."' ");
				$aGetYear = $res->fields['BEGIN_DATE'];
				$aYear    = strtotime($aGetYear.'-3 year');
				$sGetFinalYear  = date("Y", $aYear);
				// End Find previous 3 Year
				
				$res = $db->Execute("CALL COMP20005(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".$_POST['GRADUATION_RATES_LENGTH']."', ".$_POST['PK_AWARD_YEAR'].",'Winter Completers within 100%')");
				while (!$res->EOF) {
					if(strtolower($res->fields['GENDER']) == 'men') {
						if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'u.s. nonresident') {
							$men_1_1 = $res->fields['LT2AY'];
							$men_1_2 = $res->fields['LT4AY'];
							$men_1_a = $res->fields['TOTAL'];
							$men_1_b = $res->fields['EXCLUSION'];
							$men_1_c = $res->fields['Full_time_cohert'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'hispanic/latino') {
							$men_2_1 = $res->fields['LT2AY'];
							$men_2_2 = $res->fields['LT4AY'];
							$men_2_a = $res->fields['TOTAL'];
							$men_2_b = $res->fields['EXCLUSION'];
							$men_2_c = $res->fields['Full_time_cohert'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'american indian or alaska native') {
							$men_3_1 = $res->fields['LT2AY'];
							$men_3_2 = $res->fields['LT4AY'];
							$men_3_a = $res->fields['TOTAL'];
							$men_3_b = $res->fields['EXCLUSION'];
							$men_3_c = $res->fields['Full_time_cohert'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'asian') {
							$men_4_1 = $res->fields['LT2AY'];
							$men_4_2 = $res->fields['LT4AY'];
							$men_4_a = $res->fields['TOTAL'];
							$men_4_b = $res->fields['EXCLUSION'];
							$men_4_c = $res->fields['Full_time_cohert'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'black or african american') {
							$men_5_1 = $res->fields['LT2AY'];
							$men_5_2 = $res->fields['LT4AY'];
							$men_5_a = $res->fields['TOTAL'];
							$men_5_b = $res->fields['EXCLUSION'];
							$men_5_c = $res->fields['Full_time_cohert'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'native hawaiian or other pacific islander') {
							$men_6_1 = $res->fields['LT2AY'];
							$men_6_2 = $res->fields['LT4AY'];
							$men_6_a = $res->fields['TOTAL'];
							$men_6_b = $res->fields['EXCLUSION'];
							$men_6_c = $res->fields['Full_time_cohert'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'white') {
							$men_7_1 = $res->fields['LT2AY'];
							$men_7_2 = $res->fields['LT4AY'];
							$men_7_a = $res->fields['TOTAL'];
							$men_7_b = $res->fields['EXCLUSION'];
							$men_7_c = $res->fields['Full_time_cohert'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'race and ethnicity unknown') {
							$men_9_1 = $res->fields['LT2AY'];
							$men_9_2 = $res->fields['LT4AY'];
							$men_9_a = $res->fields['TOTAL'];
							$men_9_b = $res->fields['EXCLUSION'];
							$men_9_c = $res->fields['Full_time_cohert'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'two or more races') {
							$men_99_1 = $res->fields['LT2AY'];
							$men_99_2 = $res->fields['LT4AY'];
							$men_99_a = $res->fields['TOTAL'];
							$men_99_b = $res->fields['EXCLUSION'];
							$men_99_c = $res->fields['Full_time_cohert'];
						}
					} else if(strtolower($res->fields['GENDER']) == 'women') {
						if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'u.s. nonresident') {
							$women_1_1 = $res->fields['LT2AY'];
							$women_1_2 = $res->fields['LT4AY'];
							$women_1_a = $res->fields['TOTAL'];
							$women_1_b = $res->fields['EXCLUSION'];
							$women_1_c = $res->fields['Full_time_cohert'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'hispanic/latino') {
							$women_2_1 = $res->fields['LT2AY'];
							$women_2_2 = $res->fields['LT4AY'];
							$women_2_a = $res->fields['TOTAL'];
							$women_2_b = $res->fields['EXCLUSION'];
							$women_2_c = $res->fields['Full_time_cohert'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'american indian or alaska native') {
							$women_3_1 = $res->fields['LT2AY'];
							$women_3_2 = $res->fields['LT4AY'];
							$women_3_a = $res->fields['TOTAL'];
							$women_3_b = $res->fields['EXCLUSION'];
							$women_3_c = $res->fields['Full_time_cohert'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'asian') {
							$women_4_1 = $res->fields['LT2AY'];
							$women_4_2 = $res->fields['LT4AY'];
							$women_4_a = $res->fields['TOTAL'];
							$women_4_b = $res->fields['EXCLUSION'];
							$women_4_c = $res->fields['Full_time_cohert'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'black or african american') {
							$women_5_1 = $res->fields['LT2AY'];
							$women_5_2 = $res->fields['LT4AY'];
							$women_5_a = $res->fields['TOTAL'];
							$women_5_b = $res->fields['EXCLUSION'];
							$women_5_c = $res->fields['Full_time_cohert'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'native hawaiian or other pacific islander') {
							$women_6_1 = $res->fields['LT2AY'];
							$women_6_2 = $res->fields['LT4AY'];
							$women_6_a = $res->fields['TOTAL'];
							$women_6_b = $res->fields['EXCLUSION'];
							$women_6_c = $res->fields['Full_time_cohert'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'white') {
							$women_7_1 = $res->fields['LT2AY'];
							$women_7_2 = $res->fields['LT4AY'];
							$women_7_a = $res->fields['TOTAL'];
							$women_7_b = $res->fields['EXCLUSION'];
							$women_7_c = $res->fields['Full_time_cohert'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'race and ethnicity unknown') {
							$women_9_1 = $res->fields['LT2AY'];
							$women_9_2 = $res->fields['LT4AY'];
							$women_9_a = $res->fields['TOTAL'];
							$women_9_b = $res->fields['EXCLUSION'];
							$women_9_c = $res->fields['Full_time_cohert'];
						} else if(strtolower(trim($res->fields['IPEDS_ETHNICITY'])) == 'two or more races') {
							$women_99_1 = $res->fields['LT2AY'];
							$women_99_2 = $res->fields['LT4AY'];
							$women_99_a = $res->fields['TOTAL'];
							$women_99_b = $res->fields['EXCLUSION'];
							$women_99_c = $res->fields['Full_time_cohert'];
						}
					}
					$res->MoveNext();
				}
				
				$men_tot_1 	= $men_1_1 + $men_2_1 + $men_3_1 + $men_4_1 + $men_5_1 + $men_6_1 + $men_7_1 + $men_9_1 + $men_99_1;
				$men_tot_2 	= $men_1_2 + $men_2_2 + $men_3_2 + $men_4_2 + $men_5_2 + $men_6_2 + $men_7_2 + $men_9_2 + $men_99_2;
				$men_tot_a 	= $men_1_a + $men_2_a + $men_3_a + $men_4_a + $men_5_a + $men_6_a + $men_7_a + $men_9_a + $men_99_a;
				$men_tot_b 	= $men_1_b + $men_2_b + $men_3_b + $men_4_b + $men_5_b + $men_6_a + $men_7_b + $men_9_b + $men_99_b;
				$men_tot_c 	= $men_1_c + $men_2_c + $men_3_c + $men_4_c + $men_5_c + $men_6_c + $men_7_c + $men_9_c + $men_99_c;
				
				$women_tot_1 	= $women_1_1 + $women_2_1 + $women_3_1 + $women_4_1 + $women_5_1 + $women_6_1 + $women_7_1 + $women_9_1 + $women_99_1;
				$women_tot_2 	= $women_1_2 + $women_2_2 + $women_3_2 + $women_4_2 + $women_5_2 + $women_6_2 + $women_7_2 + $women_9_2 + $women_99_2;
				$women_tot_a 	= $women_1_a + $women_2_a + $women_3_a + $women_4_a + $women_5_a + $women_6_a + $women_7_a + $women_9_a + $women_99_a;
				$women_tot_b 	= $women_1_b + $women_2_b + $women_3_b + $women_4_b + $women_5_b + $women_6_b + $women_7_b + $women_9_b + $women_99_b;
				$women_tot_c	= $women_1_c + $women_2_c + $women_3_c + $women_4_c + $women_5_c + $women_6_c + $women_7_c + $women_9_c + $women_99_c;
				
				$men_women_tot_1 = $men_tot_1 + $women_tot_1;
				$men_women_tot_2 = $men_tot_2 + $women_tot_2;
				$men_women_tot_a = $men_tot_a + $women_tot_a;
				$men_women_tot_b = $men_tot_a + $women_tot_b;
				$men_women_tot_c = $men_tot_a + $women_tot_c;

				$txt = '<table border="1" cellspacing="0" cellpadding="1" width="90%">
							<tr>
								<td width="20%" style="border-bottom: 1px solid #CCC;" ></td>
								<td width="10%" colspan="5" style="border-bottom: 1px solid #CCC;" align="center" >'.$sGetFinalYear.'</td>
							</tr>
							<tr>
								<td width="10%" style="border-bottom: 1px solid #CCC;" ></td>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="center" ></td>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="center" ></td>
								<td width="20%" colspan="2" style="border-bottom: 1px solid #CCC;" align="center" >Cohort students who completed their program within '.'100%'.' of normal time to completion</td>
								<td width="20%" style="border-bottom: 1px solid #CCC;" align="center" ></td>
							</tr>
							<tr>
								<td width="10%" style="border-bottom: 1px solid #CCC;" ></td>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="center" >Revised cohort</td>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="center" >Exclusions</td>
								<td width="20%" style="border-bottom: 1px solid #CCC;" align="center" >Completers of programs of less than 2 academic yrs (or equivalent)</td>
								<td width="20%" style="border-bottom: 1px solid #CCC;" align="center" >Completers of programs of at least 2 but less than 4 academic yrs (or equivalent)</td>
								<td width="20%" style="border-bottom: 1px solid #CCC;" align="center" >Total completers within 100% (Column 55 + 56)</td>
							</tr>
							<tr>
								<td width="10%" style="border-bottom: 1px solid #CCC;" ></td>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="center" ></td>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="center" ></td>
								<td width="20%" style="border-bottom: 1px solid #CCC;" align="center" >(Column 55)</td>
								<td width="20%" style="border-bottom: 1px solid #CCC;" align="center" >(Column 56)</td>
								<td width="20%" style="border-bottom: 1px solid #CCC;" align="center" >(Column 57)</td>
							</tr>
							<tr>
								<td width="10%" style="border-bottom: 1px solid #CCC;" >Total Men + Women:</td>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="right" >'.($men_women_tot_c == '' ? 0 : number_format_value_checker($men_women_tot_c)).'</td>
								<td width="10%" style="border-bottom: 1px solid #CCC;" align="right" >'.($men_women_tot_b == '' ? 0 : number_format_value_checker($men_women_tot_b)).'</td>
								<td width="20%" style="border-bottom: 1px solid #CCC;" align="right" >'.($men_women_tot_1 == '' ? 0 : number_format_value_checker($men_women_tot_1)).'</td>
								<td width="20%" style="border-bottom: 1px solid #CCC;" align="right" >'.($men_women_tot_2 == '' ? 0 : number_format_value_checker($men_women_tot_2)).'</td>
								<td width="20%" style="border-bottom: 1px solid #CCC;" align="right" >'.($men_women_tot_a == '' ? 0 : number_format_value_checker($men_women_tot_a)).'</td>
							</tr>
							
						</table>';

				$mpdf->WriteHTML($txt);
			} else if($_POST['GRADUATION_RATES'] == 6) {
				$res = $db->Execute("CALL COMP20005(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".$_POST['GRADUATION_RATES_LENGTH']."', ".$_POST['PK_AWARD_YEAR'].",'Winter FA Recipients')");

				// Find previous 3 Year
				$res = $db->Execute("SELECT BEGIN_DATE FROM M_AWARD_YEAR where PK_AWARD_YEAR='".$_POST['PK_AWARD_YEAR']."' ");
				$aGetYear = $res->fields['BEGIN_DATE'];
				$aYear    = strtotime($aGetYear.'-3 year');
				$sGetFinalYear  = date("Y", $aYear);
				// End Find previous 3 Year
				
				$txt = '<table border="1" cellspacing="0" cellpadding="1" width="100%">
				`			<tr>
								<td width="25%" colspan="2" rowspan="2" style="border-bottom: 1px solid #CCC;" ></td>
								<td width="15%" align="center" colspan="3" style="border-bottom: 1px solid #CCC;" >'.$sGetFinalYear.'</td>
							</tr>
							<tr>

								<td width="15%" style="border-bottom: 1px solid #CCC;" align="center" >Number of students in cohort</td>
								<td width="15%" style="border-bottom: 1px solid #CCC;" align="center" >Total exclusions</td>
								<td width="15%" style="border-bottom: 1px solid #CCC;" align="center" >Number of students that completed within '.'150%'.' of normal time to completion</td>
							</tr>	
							<tr>
								<td width="5%" style="border-bottom: 1px solid #CCC;" ></td>
								<td width="50%" style="border-bottom: 1px solid #CCC;" ></td>
								<td width="15%" style="border-bottom: 1px solid #CCC;" align="center" >(Column 10)</td>
								<td width="15%" style="border-bottom: 1px solid #CCC;" align="center" >(Column 45)</td>
								<td width="15%" style="border-bottom: 1px solid #CCC;" align="center" >(Column 29)</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" colspan="2" >Full-time, first-time, degree/certificate-seeking cohort</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['Full_time_cohert'] == '' ? 0 : number_format_value_checker($res->fields['Full_time_cohert'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['Full_time_cohert_exclusion'] == '' ? 0 : number_format_value_checker($res->fields['Full_time_cohert_exclusion'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['Full_time_cohert_completion'] == '' ? 0 : number_format_value_checker($res->fields['Full_time_cohert_completion'])).'</td>
							</tr>			
							<tr>
								<td style="border-bottom: 1px solid #CCC;" ></td>
								<td style="border-bottom: 1px solid #CCC;" >Recipients of a Pell Grant (within entering year)</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['RECEIVED_PELL'] == '' ? 0 : number_format_value_checker($res->fields['RECEIVED_PELL'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['EXCLUSIONS_RECEIVED_PELL'] == '' ? 0 : number_format_value_checker($res->fields['EXCLUSIONS_RECEIVED_PELL'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['PELL_150'] == '' ? 0 : number_format_value_checker($res->fields['PELL_150'])).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" ></td>
								<td style="border-bottom: 1px solid #CCC;" >Recipients of a Direct Subsidized Loan (Within entering year) that did not receive a Pell Grant</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['RECEIVED_SUBLOAN'] == '' ? 0 : number_format_value_checker($res->fields['RECEIVED_SUBLOAN'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['EXCLUSIONS_RECEIVED_SUBLOAN'] == '' ? 0 : number_format_value_checker($res->fields['EXCLUSIONS_RECEIVED_SUBLOAN'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['SUBLOAN150'] == '' ? 0 : number_format_value_checker($res->fields['SUBLOAN150'])).'</td>
							</tr>
							<tr>
							   <td style="border-bottom: 1px solid #CCC;" ></td>
								<td style="border-bottom: 1px solid #CCC;" >Did not receive either a Pell Grant or Direct Subsidized Loan (within entering year)</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['RECEIVED_NEITHER'] == '' ? 0 : number_format_value_checker($res->fields['RECEIVED_NEITHER'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['EXCLUSIONS_RECEIVED_NEITHER'] == '' ? 0 : number_format_value_checker($res->fields['EXCLUSIONS_RECEIVED_NEITHER'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['NEITHER150'] == '' ? 0 : number_format_value_checker($res->fields['NEITHER150'])).'</td>
							</tr>
						</table>';
						
				$mpdf->WriteHTML($txt);
			}
			$mpdf->Output($REPORT_NAME.'.pdf', 'D');
		}
	} else if($_POST['FORMAT'] == '200_GR'){
		if($_POST['_200_GRADUATION_RATES'] == 1) {
			if($_POST['_200_GRADUATION_RATES'] == 1) {
				$REPORT_NAME = "Winter 200 Graduation Rates Datasheet";
			}
			
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
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

			$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setIncludeCharts(TRUE);
			//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
			$objPHPExcel = new PHPExcel();
			$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			
			if($_POST['_200_GRADUATION_RATES'] == 1) {
				$line 	= 1;	
				$index 	= -1;
	
				$res = $db->Execute("CALL COMP20006(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".$_POST['_200_GRADUATION_RATES_LENGTH']."', ".$_POST['PK_AWARD_YEAR'].",'Winter 200% Graduation Rates Datasheet')");
				$heading = array_keys($res->fields);
				while (!$res->EOF) 
				{
					$index = -1;
					foreach ($heading as $key) 
					{
						if($key!='ROW_TYPE')
						{
							//Get Header column name and set styling 
							if($line==1)
							{
								
								$index++;
								$cell_no = $cell[$index].$line;
								$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($key);
								$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
								$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(20);
								$objPHPExcel->getActiveSheet()->freezePane('A1');
							}
							else
							{
								// Get data column value and set data
								$index++;
								$cell_no = $cell[$index].$line;
								$cellValue=$res->fields[$key];
								$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($cellValue);
							
							}	
						}
						else
						{
							echo 'Skip header';
						}
					}							
					$line++;
					
					$res->MoveNext();
				}
			} 
			
			$objWriter->save($outputFileName);
			$objPHPExcel->disconnectWorksheets();
			header("location:".$outputFileName);
		} else if($_POST['_200_GRADUATION_RATES'] == 2) {
			require_once '../global/mpdf/vendor/autoload.php';
			
			$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
			$SCHOOL_NAME = $res->fields['SCHOOL_NAME'];
			$PDF_LOGO 	 = $res->fields['PDF_LOGO'];
			
			$logo = "";
			if($PDF_LOGO != '')
				$logo = '<img src="'.$PDF_LOGO.'" height="50px" />';
				
			$res_type = $db->Execute("select AWARD_YEAR from M_AWARD_YEAR WHERE PK_AWARD_YEAR = '$_POST[PK_AWARD_YEAR]' ");
			$AWARD_YEAR = 'Collection: '.$res_type->fields['AWARD_YEAR'];
				
			if($_POST['_200_GRADUATION_RATES'] == 2)
				$REPORT_NAME = "200% Graduation Rates: Completers within 200%";
				
			$header = '<table width="100%" >
							<tr>
								<td width="20%" valign="top" >'.$logo.'</td>
								<td width="35%" valign="top" style="font-size:20px" >'.$SCHOOL_NAME.'</td>
								<td width="45%" valign="top" >
									<table width="100%" >
										<tr>
											<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b><i>IPEDS Winter Collection</i></b></td>
										</tr>
										<tr>
											<td width="100%" align="right" style="font-size:13px;" ><i>'.$REPORT_NAME.'</i></td>
										</tr>
										<tr>
											<td width="100%" align="right" style="font-size:13px;" >'.$AWARD_YEAR.'</td>
										</tr>
										<tr>
											<td width="100%" align="right" style="font-size:13px;" >Campus(es): '.$campus_name.'</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>';
						
			
			$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$TIMEZONE,date_default_timezone_get());
						
			$footer = '<table width="100%" >
							<tr>
								<td width="33%" valign="top" style="font-size:10px;" ><i>'.$date.'</i></td>
								<td width="33%" valign="top" style="font-size:10px;" align="center" >COMP20006</td>
								<td width="33%" valign="top" style="font-size:10px;" align="right" ><i>Page {PAGENO} of {nb}</i></td>
							</tr>
						</table>';
			
			$mpdf = new \Mpdf\Mpdf([
				'margin_left' => 7,
				'margin_right' => 5,
				'margin_top' => 35,
				'margin_bottom' => 20,
				'margin_header' => 3,
				'margin_footer' => 10,
				'default_font' => 'helvetica',
				'default_font_size' => 10,
				'orientation' => 'P'
			]);
			$mpdf->autoPageBreak = true;
			
			$mpdf->SetHTMLHeader($header);
			$mpdf->SetHTMLFooter($footer);
			
			if($_POST['_200_GRADUATION_RATES'] == 2) {
				$res = $db->Execute("CALL COMP20006(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".$_POST['_200_GRADUATION_RATES_LENGTH']."', ".$_POST['PK_AWARD_YEAR'].",'Winter 200% Graduation Rates')");
				
				$txt = '<table border="1" cellspacing="0" cellpadding="1" width="90%">
							<tr>
								<td width="7%" style="border-bottom: 1px solid #CCC;" >&nbsp;</td>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;</td>
								<td width="10%" style="border-bottom: 1px solid #CCC;" >&nbsp;</td>
								<td style="border-bottom: 1px solid #CCC;" align="center" >Graduation Rates</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="center" >1</td>
								<td style="border-bottom: 1px solid #CCC;" >Revised cohort</td>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >0</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="center" >2</td>
								<td style="border-bottom: 1px solid #CCC;" >Exclusions within 150%</td>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >0</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="center" >3</td>
								<td style="border-bottom: 1px solid #CCC;" >Adjusted cohort 150%</td>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >0</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="center" >4</td>
								<td style="border-bottom: 1px solid #CCC;" >Number of students in the cohort who completed a program within 100% of normal time to completion</td>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >0</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="center" >5</td>
								<td style="border-bottom: 1px solid #CCC;" >Number of students in the cohort who completed a program within 150% of normal time to completion</td>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >0</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="center" >6</td>
								<td style="border-bottom: 1px solid #CCC;" >Additional exclusions (between 151% and 200% of normal time)</td>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['Excluded_200'] == '' ? 0 : number_format_value_checker($res->fields['Excluded_200'])).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="center" >7</td>
								<td style="border-bottom: 1px solid #CCC;" >Adjusted cohort 200% (line 3 - line 6)</td>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >0</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="center" >8</td>
								<td style="border-bottom: 1px solid #CCC;" >Number of students in the cohort who completed a program between 151% and 200% of normal time to completion</td>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['Completer_200'] == '' ? 0 : number_format_value_checker($res->fields['Completer_200'])).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="center" >9</td>
								<td style="border-bottom: 1px solid #CCC;" >Still enrolled as of 200% of normal time to completion</td>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['Active_200'] == '' ? 0 : number_format_value_checker($res->fields['Active_200'])).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" align="center" >10</td>
								<td style="border-bottom: 1px solid #CCC;" >Total completers within 200% of normal time (line 5 + line 8)</td>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >0</td>
							</tr>
							
						</table>';
						
				$mpdf->WriteHTML($txt);
			}
			$mpdf->Output($REPORT_NAME.'.pdf', 'D');
		}
	} else if($_POST['FORMAT'] == 'AD'){
		if($_POST['ADMISSIONS'] == 1) {
			if($_POST['ADMISSIONS'] == 1) {
				$REPORT_NAME = "Admissions Datasheet";
			}
			
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
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

			$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setIncludeCharts(TRUE);
			//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
			$objPHPExcel = new PHPExcel();
			$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			
			if($_POST['ADMISSIONS'] == 1) {
				$line 	= 1;	
				$index 	= -1;
				$res = $db->Execute("CALL COMP20007(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].",'Admissions Datasheet')");
				$heading = array_keys($res->fields);
				while (!$res->EOF) 
				{
					$index = -1;
					foreach ($heading as $key) 
					{
						if($key!='ROW_TYPE')
						{
						//Get Header column name and set styling 
						if($line==1)
						{
							
							$index++;
							$cell_no = $cell[$index].$line;
							$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($key);
							$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
							$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(20);
							$objPHPExcel->getActiveSheet()->freezePane('A1');
						}
						else
						{
							// Get data column value and set data
						$index++;
						$cell_no = $cell[$index].$line;
						$cellValue=$res->fields[$key];
						if($res->fields[$key]=='SSNEncrypted')
						{
							$SSN 		= $res->fields['SSNEncrypted'];
							$cellValue 	= my_decrypt('',$SSN);
						}
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($cellValue);
						
						}	
					}
					else
					{
						echo 'Skip header';
					}
					}							
					$line++;
					
					$res->MoveNext();
				}
			}
			
			$objWriter->save($outputFileName);
			$objPHPExcel->disconnectWorksheets();
			header("location:".$outputFileName);
		} else if($_POST['ADMISSIONS'] == 2 || $_POST['ADMISSIONS'] == 3) {
			require_once '../global/mpdf/vendor/autoload.php';
			
			$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
			$SCHOOL_NAME = $res->fields['SCHOOL_NAME'];
			$PDF_LOGO 	 = $res->fields['PDF_LOGO'];
			
			$logo = "";
			if($PDF_LOGO != '')
				$logo = '<img src="'.$PDF_LOGO.'" height="50px" />';
				
			$res_type = $db->Execute("select AWARD_YEAR from M_AWARD_YEAR WHERE PK_AWARD_YEAR = '$_POST[PK_AWARD_YEAR]' ");
			$AWARD_YEAR = 'Collection: '.$res_type->fields['AWARD_YEAR'];
				
			if($_POST['ADMISSIONS'] == 2)
				$REPORT_NAME = "Admission: AEE";
			else if($_POST['ADMISSIONS'] == 3)
				$REPORT_NAME = "Admission: Test Scores";
				
			$header = '<table width="100%" >
							<tr>
								<td width="20%" valign="top" >'.$logo.'</td>
								<td width="35%" valign="top" style="font-size:20px" >'.$SCHOOL_NAME.'</td>
								<td width="45%" valign="top" >
									<table width="100%" >
										<tr>
											<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b><i>IPEDS Winter Collection</i></b></td>
										</tr>
										<tr>
											<td width="100%" align="right" style="font-size:13px;" ><i>'.$REPORT_NAME.'</i></td>
										</tr>
										<tr>
											<td width="100%" align="right" style="font-size:13px;" >'.$AWARD_YEAR.'</td>
										</tr>
										<tr>
											<td width="100%" align="right" style="font-size:13px;" >Campus(es): '.$campus_name.'</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>';
						
			
			$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$TIMEZONE,date_default_timezone_get());
						
			$footer = '<table width="100%" >
							<tr>
								<td width="33%" valign="top" style="font-size:10px;" ><i>'.$date.'</i></td>
								<td width="33%" valign="top" style="font-size:10px;" align="center" >COMP20007</td>
								<td width="33%" valign="top" style="font-size:10px;" align="right" ><i>Page {PAGENO} of {nb}</i></td>
							</tr>
						</table>';
			
			$mpdf = new \Mpdf\Mpdf([
				'margin_left' => 7,
				'margin_right' => 5,
				'margin_top' => 35,
				'margin_bottom' => 20,
				'margin_header' => 3,
				'margin_footer' => 10,
				'default_font' => 'helvetica',
				'default_font_size' => 10,
				'orientation' => 'P'
			]);
			$mpdf->autoPageBreak = true;
			
			$mpdf->SetHTMLHeader($header);
			$mpdf->SetHTMLFooter($footer);
			
			if($_POST['ADMISSIONS'] == 2) {
				//echo "CALL COMP20007(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].",'AAE')";
				//exit;
				$res = $db->Execute("CALL COMP20007(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].",'AAE')");
				
				$txt = '<table border="1" cellspacing="0" cellpadding="1" width="100%">
							<tr>
								<td width="45%" >&nbsp;</td>
								<td width="15%" style="border-bottom: 1px solid #CCC;" align="center" ><b>Men</b></td>
								<td width="15%" style="border-bottom: 1px solid #CCC;" align="center" ><b>Women</b></td>
								<td width="15%" style="border-bottom: 1px solid #CCC;" align="center" ><b>Another Gender</b></td>

								<td width="15%" style="border-bottom: 1px solid #CCC;" align="center" ><b><u>Total</u></b></td>
							</tr>
							
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;Number of applicants</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['ApplicantsMen'] == '' ? 0 : number_format_value_checker($res->fields['ApplicantsMen'])).'</td>
								<td align="right" style="border-bottom: 1px solid #CCC;" >'.($res->fields['ApplicantsWomen'] == '' ? 0 : number_format_value_checker($res->fields['ApplicantsWomen'])).'</td>
								<td align="right" style="border-bottom: 1px solid #CCC;" >'.($res->fields['ApplicantsOther'] == '' ? 0 : number_format_value_checker($res->fields['ApplicantsOther'])).'</td>
								<td align="right" style="border-bottom: 1px solid #CCC;" >'.($res->fields['ApplicantsTotal'] == '' ? 0 : number_format_value_checker($res->fields['ApplicantsTotal'])).'</td>
							</tr>
							
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;Number of admissions</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['AdmissionsMen'] == '' ? 0 : number_format_value_checker($res->fields['AdmissionsMen'])).'</td>
								<td align="right" style="border-bottom: 1px solid #CCC;" >'.($res->fields['AdmissionsWomen'] == '' ? 0 : number_format_value_checker($res->fields['AdmissionsWomen'])).'</td>
								<td align="right" style="border-bottom: 1px solid #CCC;" >'.($res->fields['AdmissionsOther'] == '' ? 0 : number_format_value_checker($res->fields['AdmissionsOther'])).'</td>
								<td align="right" style="border-bottom: 1px solid #CCC;" >'.($res->fields['AdmissionsTotal'] == '' ? 0 : number_format_value_checker($res->fields['AdmissionsTotal'])).'</td>
							</tr>
							
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;Number (of admitted) that enrolled full-time</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['EnrolledFTMen'] == '' ? 0 : number_format_value_checker($res->fields['EnrolledFTMen'])).'</td>
								<td align="right" style="border-bottom: 1px solid #CCC;" >'.($res->fields['EnrolledFTWomen'] == '' ? 0 : number_format_value_checker($res->fields['EnrolledFTWomen'])).'</td>
								<td align="right" style="border-bottom: 1px solid #CCC;" >'.($res->fields['EnrolledFTOther'] == '' ? 0 : number_format_value_checker($res->fields['EnrolledFTOther'])).'</td>
								<td align="right" style="border-bottom: 1px solid #CCC;" >'.($res->fields['EnrolledFTTotal'] == '' ? 0 : number_format_value_checker($res->fields['EnrolledFTTotal'])).'</td>
							</tr>							
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;Number (of admitted) that enrolled part-time</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['EnrolledPTMen'] == '' ? 0 : number_format_value_checker($res->fields['EnrolledPTMen'])).'</td>
								<td align="right" style="border-bottom: 1px solid #CCC;" >'.($res->fields['EnrolledPTWomen'] == '' ? 0 : number_format_value_checker($res->fields['EnrolledPTWomen'])).'</td>
								<td align="right" style="border-bottom: 1px solid #CCC;" >'.($res->fields['EnrolledPTOther'] == '' ? 0 : number_format_value_checker($res->fields['EnrolledPTOther'])).'</td>
								<td align="right" style="border-bottom: 1px solid #CCC;" >'.($res->fields['EnrolledPTTotal'] == '' ? 0 : number_format_value_checker($res->fields['EnrolledPTTotal'])).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;Total enrolled full-time and part-time</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['EnrolledPTMen'] == '' ? 0 : number_format_value_checker($res->fields['EnrolledPTMen'])).'</td>
								<td align="right" style="border-bottom: 1px solid #CCC;" >'.($res->fields['EnrolledPTWomen'] == '' ? 0 : number_format_value_checker($res->fields['EnrolledPTWomen'])).'</td>
								<td align="right" style="border-bottom: 1px solid #CCC;" >'.($res->fields['EnrolledPTOther'] == '' ? 0 : number_format_value_checker($res->fields['EnrolledPTOther'])).'</td>
								<td align="right" style="border-bottom: 1px solid #CCC;" >'.($res->fields['EnrolledPTTotal'] == '' ? 0 : number_format_value_checker($res->fields['EnrolledPTTotal'])).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;Percent of admissions enrolled full-time and part-time</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['EnrolledPTMen'] == '' ? 0 : number_format_value_checker($res->fields['EnrolledPTMen'])).'</td>
								<td align="right" style="border-bottom: 1px solid #CCC;" >'.($res->fields['EnrolledPTWomen'] == '' ? 0 : number_format_value_checker($res->fields['EnrolledPTWomen'])).'</td>
								<td align="right" style="border-bottom: 1px solid #CCC;" >'.($res->fields['EnrolledPTOther'] == '' ? 0 : number_format_value_checker($res->fields['EnrolledPTOther'])).'</td>
								<td align="right" style="border-bottom: 1px solid #CCC;" >'.($res->fields['EnrolledPTTotal'] == '' ? 0 : number_format_value_checker($res->fields['EnrolledPTTotal'])).'</td>
							</tr>
						</table>';
						
				$mpdf->WriteHTML($txt);
			} else if($_POST['ADMISSIONS'] == 3) {
				$res = $db->Execute("CALL COMP20007(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].",'Test Scores')");
				
				$txt = '<table border="1" cellspacing="0" cellpadding="1" width="90%">
							<tr>
								<td style="border-bottom: 1px solid #CCC;" width="85%" >&nbsp;Number of enrolled students for whom an SAT score was used in admissions decision</td>
								<td style="border-bottom: 1px solid #CCC;" width="15%" align="right" >'.($res->fields['SubmittedSAT'] == '' ? 0 : number_format_value_checker($res->fields['SubmittedSAT'])).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;Percent of enrolled students for whom an SAT score was used in admissions decision</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['PercentSubmittedSAT'] == '' ? 0 : number_format_value_checker($res->fields['PercentSubmittedSAT'],2)).'%</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;Number of enrolled students for whom an ACT score was used in admissions decision</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['SubmittedACT'] == '' ? 0 : number_format_value_checker($res->fields['SubmittedACT'])).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;Percent of enrolled students for whom an ACT score was used in admissions decision</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['PercentSubmittedACT'] == '' ? 0 : number_format_value_checker($res->fields['PercentSubmittedACT'],2)).'%</td>
							</tr>
						</table>
						<br /><br />
						<table border="1" cellspacing="0" cellpadding="1" width="100%">
							<tr>
								<td style="border-bottom: 1px solid #CCC;" width="54%" ></td>
								<td style="border-bottom: 1px solid #CCC;" width="23%" align="center" ><b>25th Percentile</b></td>
								<td style="border-bottom: 1px solid #CCC;" width="23%" align="center" ><b>50th Percentile<br>(median)</b></td>
								<td style="border-bottom: 1px solid #CCC;" width="23%" align="center" ><b>75th Percentile</b></td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;SAT Evidence-Based Reading and Writing</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['SAT_RW_25th_Percentile'] == '' ? 0 : number_format_value_checker($res->fields['SAT_RW_25th_Percentile'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['SAT_RW_50th_Percentile'] == '' ? 0 : number_format_value_checker($res->fields['SAT_RW_50th_Percentile'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['SAT_RW_75th_Percentile'] == '' ? 0 : number_format_value_checker($res->fields['SAT_RW_75th_Percentile'])).'</td>
							</tr>
							
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;SAT Math</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['SAT_MATH_25th_Percentile'] == '' ? 0 : number_format_value_checker($res->fields['SAT_MATH_25th_Percentile'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['SAT_MATH_50th_Percentile'] == '' ? 0 : number_format_value_checker($res->fields['SAT_MATH_50th_Percentile'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['SAT_MATH_75th_Percentile'] == '' ? 0 : number_format_value_checker($res->fields['SAT_MATH_75th_Percentile'])).'</td>
							</tr>
						</table>
						<br /><br />
						<table border="1" cellspacing="0" cellpadding="1" width="100%">
							<tr>
								<td style="border-bottom: 1px solid #CCC;" width="54%" ></td>
								<td style="border-bottom: 1px solid #CCC;" width="23%" align="center" ><b>25th Percentile</b></td>
								<td style="border-bottom: 1px solid #CCC;" width="23%" align="center" ><b>50th Percentile<br>(median)</b></td>
								<td style="border-bottom: 1px solid #CCC;" width="23%" align="center" ><b>75th Percentile</b></td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;ACT Composite</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['ACT_COMPOSITE_25th_Percentile'] == '' ? 0 : number_format_value_checker($res->fields['ACT_COMPOSITE_25th_Percentile'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['ACT_COMPOSITE_50th_Percentile'] == '' ? 0 : number_format_value_checker($res->fields['ACT_COMPOSITE_50th_Percentile'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['ACT_COMPOSITE_75th_Percentile'] == '' ? 0 : number_format_value_checker($res->fields['ACT_COMPOSITE_75th_Percentile'])).'</td>
							</tr>
							<!--<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;ACT English</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['ACT_ENGLISH_25th_Percentile'] == '' ? 0 : number_format_value_checker($res->fields['ACT_ENGLISH_25th_Percentile'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['ACT_ENGLISH_50th_Percentile'] == '' ? 0 : number_format_value_checker($res->fields['ACT_ENGLISH_50th_Percentile'])).'</td>
								<td style="border-bottom: 1px solid #CCC;"  align="right" >'.($res->fields['ACT_ENGLISH_75th_Percentile'] == '' ? 0 : number_format_value_checker($res->fields['ACT_ENGLISH_75th_Percentile'])).'</td>
							</tr>-->
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;ACT Math</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['ACT_MATH_25th_Percentile'] == '' ? 0 : number_format_value_checker($res->fields['ACT_MATH_25th_Percentile'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['ACT_MATH_50th_Percentile'] == '' ? 0 : number_format_value_checker($res->fields['ACT_MATH_50th_Percentile'])).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($res->fields['ACT_MATH_75th_Percentile'] == '' ? 0 : number_format_value_checker($res->fields['ACT_MATH_75th_Percentile'])).'</td>
							</tr>
						</table>';
						
				$mpdf->WriteHTML($txt);
			}
			
			$mpdf->Output($REPORT_NAME.'.pdf', 'D');
		}
	} else if($_POST['FORMAT'] == 'OM'){
		if($_POST['OUTCOME_MEASURES'] == 1) {
			if($_POST['OUTCOME_MEASURES'] == 1) {
				$REPORT_NAME = "Outcome Measures Datasheet";
			}
			
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
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

			$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setIncludeCharts(TRUE);
			//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
			$objPHPExcel = new PHPExcel();
			$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			
			if($_POST['OUTCOME_MEASURES'] == 1) {
				$line 	= 1;	
				$index 	= -1;

				$heading[] = 'Student';
				$width[]   = 20;
				$heading[] = 'OM Status';
				$width[]   = 20;
				$heading[] = 'Student Status';
				$width[]   = 20;
				$heading[] = 'Program Code';
				$width[]   = 20;
				$heading[] = 'Credential Level';
				$width[]   = 20;
				$heading[] = 'Enrollment Begin Date';
				$width[]   = 20;
				$heading[] = 'Enrollment End Date';
				$width[]   = 20;
				$heading[] = 'Cohort Status';
				$width[]   = 20;
				$heading[] = 'Drop Reason';
				$width[]   = 20;
				$heading[] = 'Received Pell';
				$width[]   = 20;
				$i = 0;
				foreach($heading as $title) {
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
					$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
					$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
					$i++;
				}	

				$objPHPExcel->getActiveSheet()->freezePane('A1');
		
				$res = $db->Execute("CALL COMP20008(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].",'Outcome Measures Datasheet')");
				while (!$res->EOF) {

					$line++;
					$index = -1;
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Student']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['OMStatus']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['StudentStatus']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ProgramCode']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CREDENTIAL_LEVEL']);
	
					if($res->fields['EnrollmentBeginDate'] != '0000-00-00' && $res->fields['EnrollmentBeginDate'] != '' )
						$EnrollmentBeginDate = date("m/d/Y",strtotime($res->fields['EnrollmentBeginDate']));
					else
						$EnrollmentBeginDate = '';
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($EnrollmentBeginDate);
					
					if($res->fields['EnrollmentEndDate'] != '0000-00-00' && $res->fields['EnrollmentEndDate'] != '' )
						$EnrollmentEndDate = date("m/d/Y",strtotime($res->fields['EnrollmentEndDate']));
					else
						$EnrollmentEndDate = '';
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($EnrollmentEndDate);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CohortStatus']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['DROP_REASON']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['RECEIVED_PELL']);
					
					$res->MoveNext();
				}
			}
			
			$objWriter->save($outputFileName);
			$objPHPExcel->disconnectWorksheets();
			header("location:".$outputFileName);
		} else if($_POST['OUTCOME_MEASURES'] == 2 || $_POST['OUTCOME_MEASURES'] == 3 || $_POST['OUTCOME_MEASURES'] == 4 || $_POST['OUTCOME_MEASURES'] == 5) {
			require_once '../global/mpdf/vendor/autoload.php';
			
			$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
			$SCHOOL_NAME = $res->fields['SCHOOL_NAME'];
			$PDF_LOGO 	 = $res->fields['PDF_LOGO'];
			
			$logo = "";
			if($PDF_LOGO != '')
				$logo = '<img src="'.$PDF_LOGO.'" height="50px" />';
				
			$res_type = $db->Execute("select AWARD_YEAR from M_AWARD_YEAR WHERE PK_AWARD_YEAR = '$_POST[PK_AWARD_YEAR]' ");
			$AWARD_YEAR = 'Collection: '.$res_type->fields['AWARD_YEAR'];
				
			if($_POST['OUTCOME_MEASURES'] == 2)
				$REPORT_NAME = "Cohort";
			else if($_POST['OUTCOME_MEASURES'] == 3)
				$REPORT_NAME = "Winter 4 Year";
			else if($_POST['OUTCOME_MEASURES'] == 4)
				$REPORT_NAME = "Winter 6 Year";
			else if($_POST['OUTCOME_MEASURES'] == 5)
				$REPORT_NAME = "Outcome Measures 8 Year";
				
			$header = '<table width="100%" >
							<tr>
								<td width="20%" valign="top" >'.$logo.'</td>
								<td width="35%" valign="top" style="font-size:20px" >'.$SCHOOL_NAME.'</td>
								<td width="45%" valign="top" >
									<table width="100%" >
										<tr>
											<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b><i>IPEDS - Winter Collection</i></b></td>
										</tr>
										<tr>
											<td width="100%" align="right" style="font-size:13px;" ><i>Outcome Measures:'.$REPORT_NAME.'</i></td>
										</tr>
										<tr>
											<td width="100%" align="right" style="font-size:13px;" >'.$AWARD_YEAR.'</td>
										</tr>
										<tr>
											<td width="100%" align="right" style="font-size:13px;" >Campus(es): '.$campus_name.'</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>';
						
			
			$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$TIMEZONE,date_default_timezone_get());
						
			$footer = '<table width="100%" >
							<tr>
								<td width="33%" valign="top" style="font-size:10px;" ><i>'.$date.'</i></td>
								<td width="33%" valign="top" style="font-size:10px;" align="center" >COMP20008</td>
								<td width="33%" valign="top" style="font-size:10px;" align="right" ><i>Page {PAGENO} of {nb}</i></td>
							</tr>
						</table>';
			
			$mpdf = new \Mpdf\Mpdf([
				'margin_left' => 7,
				'margin_right' => 5,
				'margin_top' => 35,
				'margin_bottom' => 20,
				'margin_header' => 3,
				'margin_footer' => 10,
				'default_font' => 'helvetica',
				'default_font_size' => 9,
				'orientation' => 'P'
			]);
			$mpdf->autoPageBreak = true;
			
			$mpdf->SetHTMLHeader($header);
			$mpdf->SetHTMLFooter($footer);
			
			if($_POST['OUTCOME_MEASURES'] == 2) {
				//echo "CALL COMP20008(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].", 'Outcome Measures Cohort')";
				//exit;
				$res = $db->Execute("CALL COMP20008(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].", 'Outcome Measures Cohort')");
				while (!$res->EOF) {
					if(strtolower($res->fields['OMStatus']) == 'first time entering/full time' && strtolower($res->fields['Pell']) == 'nonpell grant recipients') {
						$_1_c = $res->fields['CohortTotal'];
						$_1_e = $res->fields['ExclusionsTotal'];
					} else if(strtolower($res->fields['OMStatus']) == 'first time entering/full time' && strtolower($res->fields['Pell']) == 'pell grant recipients') {
						$_2_c = $res->fields['CohortTotal'];
						$_2_e = $res->fields['ExclusionsTotal'];
					} else if(strtolower($res->fields['OMStatus']) == 'first time entering/part time' && strtolower($res->fields['Pell']) == 'nonpell grant recipients') {
						$_3_c = $res->fields['CohortTotal'];
						$_3_e = $res->fields['ExclusionsTotal'];
					} else if(strtolower($res->fields['OMStatus']) == 'first time entering/part time' && strtolower($res->fields['Pell']) == 'pell grant recipients') {
						$_4_c = $res->fields['CohortTotal'];
						$_4_e = $res->fields['ExclusionsTotal'];
					} else if(strtolower($res->fields['OMStatus']) == 'first time/full time' && strtolower($res->fields['Pell']) == 'nonpell grant recipients') {
						$_5_c = $res->fields['CohortTotal'];
						$_5_e = $res->fields['ExclusionsTotal'];
					} else if(strtolower($res->fields['OMStatus']) == 'first time/full time' && strtolower($res->fields['Pell']) == 'pell grant recipients') {
						$_6_c = $res->fields['CohortTotal'];
						$_6_e = $res->fields['ExclusionsTotal'];
					} else if(strtolower($res->fields['OMStatus']) == 'non first time entering/full time' && strtolower($res->fields['Pell']) == 'nonpell grant recipients') {
						$_7_c = $res->fields['CohortTotal'];
						$_7_e = $res->fields['ExclusionsTotal'];
					} else if(strtolower($res->fields['OMStatus']) == 'non first time entering/full time' && strtolower($res->fields['Pell']) == 'pell grant recipients') {
						$_8_c = $res->fields['CohortTotal'];
						$_8_e = $res->fields['ExclusionsTotal'];
					} else if(strtolower($res->fields['OMStatus']) == 'non first time entering/part time' && strtolower($res->fields['Pell']) == 'nonpell grant recipients') {
						$_9_c = $res->fields['CohortTotal'];
						$_9_e = $res->fields['ExclusionsTotal'];
					} else if(strtolower($res->fields['OMStatus']) == 'non first time entering/part time' && strtolower($res->fields['Pell']) == 'pell grant recipients') {
						$_10_c = $res->fields['CohortTotal'];
						$_10_e = $res->fields['ExclusionsTotal'];
					}
					$res->MoveNext();
				}
				
				$txt = '
						<table border="1" cellspacing="0" cellpadding="8" width="70%">
									<tr> 
										<td  width="31%" style=""><b>Degree/certificate seeking <br> undergraduate Students </b></td>
										<td   width="24%" style=""><b>Cohort</b></td>
										<td   width="20%" style=""><b>Exclusions</b></td>
									</tr>
					    </table>

						<br/>
						<br/>
						<table border="1" cellspacing="0" cellpadding="8" width="70%">
							<tr>
								<td width="25%" ><b>First Time entering</b></td>
								<td width="25%" ><b></b></td>
								<td width="25%" ><b></b></td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;<b>Full Time</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_c == '' ? 0 : number_format_value_checker($_2_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_e == '' ? 0 : number_format_value_checker($_2_e)).'</td>
							</tr>

							<tr>
								<td style="border-bottom: 1px solid #CCC;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Pell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_c == '' ? 0 : number_format_value_checker($_2_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_e == '' ? 0 : number_format_value_checker($_2_e)).'</td>
							</tr>

							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>NonPell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_c == '' ? 0 : number_format_value_checker($_2_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_e == '' ? 0 : number_format_value_checker($_2_e)).'</td>
							</tr>
							
							<tr>
								<td >&nbsp;&nbsp;<b>Part-time</b></td>
								<td align="right" >'.($_3_c == '' ? 0 : number_format_value_checker($_3_c)).'</td>
								<td align="right" >'.($_3_e == '' ? 0 : number_format_value_checker($_3_e)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Pell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_4_c == '' ? 0 : number_format_value_checker($_4_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_4_e == '' ? 0 : number_format_value_checker($_4_e)).'</td>
							</tr>
							<tr>
							<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>NonPell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_4_c == '' ? 0 : number_format_value_checker($_4_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_4_e == '' ? 0 : number_format_value_checker($_4_e)).'</td>
							</tr>

							<!--tr>
								<td >Non-First-time entering | Full-time</td>
								<td >Pell Grant recipients</td>
								<td align="right" >'.($_7_c == '' ? 0 : number_format_value_checker($_7_c)).'</td>
								<td align="right" >'.($_7_e == '' ? 0 : number_format_value_checker($_7_e)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" ></td>
								<td style="border-bottom: 1px solid #CCC;" >NonPell Grant recipients</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_8_c == '' ? 0 : number_format_value_checker($_8_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_8_e == '' ? 0 : number_format_value_checker($_8_e)).'</td>
							</tr>
							
							<tr>
								<td >Non-First-time entering | Part-time</td>
								<td >Pell Grant recipients</td>
								<td align="right" >'.($_9_c == '' ? 0 : number_format_value_checker($_9_c)).'</td>
								<td align="right" >'.($_9_e == '' ? 0 : number_format_value_checker($_9_e)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" ></td>
								<td style="border-bottom: 1px solid #CCC;" >NonPell Grant recipients</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_10_c == '' ? 0 : number_format_value_checker($_10_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_10_e == '' ? 0 : number_format_value_checker($_10_e)).'</td>
							</tr>-->
							
						</table>
						<br/>
						<br/>
						<table border="1" cellspacing="0" cellpadding="8" width="70%">
							<tr>
								<td width="25%" ><b>Non-First-time entering</b></td>
								<td width="25%" ></td>
								<td width="25%" ></td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;<b>Full Time</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_c == '' ? 0 : number_format_value_checker($_2_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_e == '' ? 0 : number_format_value_checker($_2_e)).'</td>
							</tr>

							<tr>
								<td style="border-bottom: 1px solid #CCC;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Pell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_c == '' ? 0 : number_format_value_checker($_2_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_e == '' ? 0 : number_format_value_checker($_2_e)).'</td>
							</tr>

							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>NonPell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_c == '' ? 0 : number_format_value_checker($_2_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_e == '' ? 0 : number_format_value_checker($_2_e)).'</td>
							</tr>
							
							<tr>
								<td >&nbsp;&nbsp;<b>Part-time</b></td>
								<td align="right" >'.($_3_c == '' ? 0 : number_format_value_checker($_3_c)).'</td>
								<td align="right" >'.($_3_e == '' ? 0 : number_format_value_checker($_3_e)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Pell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_4_c == '' ? 0 : number_format_value_checker($_4_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_4_e == '' ? 0 : number_format_value_checker($_4_e)).'</td>
							</tr>
							<tr>
							<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>NonPell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_4_c == '' ? 0 : number_format_value_checker($_4_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_4_e == '' ? 0 : number_format_value_checker($_4_e)).'</td>
							</tr>

						</table>
						<br/><br/>
						<table border="1" cellspacing="0" cellpadding="8" width="70%">
							<tr>
								<td width="25%" ><b>Total entering</b></td>
								<td width="25%" ></td>
								<td width="25%" ></td>
							</tr>
					

							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Pell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_c == '' ? 0 : number_format_value_checker($_2_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_e == '' ? 0 : number_format_value_checker($_2_e)).'</td>
							</tr>

							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>NonPell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_c == '' ? 0 : number_format_value_checker($_2_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_e == '' ? 0 : number_format_value_checker($_2_e)).'</td>
							</tr>
						</table>
						';
						// echo $txt;
						// exit;
				$mpdf->WriteHTML($txt);
			} else if($_POST['OUTCOME_MEASURES'] == 3) {
				$res = $db->Execute("CALL COMP20008(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].", 'Outcome Measures 4 Year')");
				while (!$res->EOF) {
					if(strtolower($res->fields['OMStatus']) == 'first time entering/full time' && strtolower($res->fields['Pell']) == 'nonpell grant recipients') {
						$_1_c = $res->fields['Certificates'];
						$_1_a = $res->fields['Associates'];
						$_1_b = $res->fields['Bachelors'];
					} else if(strtolower($res->fields['OMStatus']) == 'first time entering/full time' && strtolower($res->fields['Pell']) == 'pell grant recipients') {
						$_2_c = $res->fields['Certificates'];
						$_2_a = $res->fields['Associates'];
						$_2_b = $res->fields['Bachelors'];
					} else if(strtolower($res->fields['OMStatus']) == 'first time entering/part time' && strtolower($res->fields['Pell']) == 'nonpell grant recipients') {
						$_3_c = $res->fields['Certificates'];
						$_3_a = $res->fields['Associates'];
						$_3_b = $res->fields['Bachelors'];
					} else if(strtolower($res->fields['OMStatus']) == 'first time entering/part time' && strtolower($res->fields['Pell']) == 'pell grant recipients') {
						$_4_c = $res->fields['Certificates'];
						$_4_a = $res->fields['Associates'];
						$_4_b = $res->fields['Bachelors'];
					} else if(strtolower($res->fields['OMStatus']) == 'first time/full time' && strtolower($res->fields['Pell']) == 'nonpell grant recipients') {
						$_5_c = $res->fields['Certificates'];
						$_5_a = $res->fields['Associates'];
						$_5_b = $res->fields['Bachelors'];
					} else if(strtolower($res->fields['OMStatus']) == 'first time/full time' && strtolower($res->fields['Pell']) == 'pell grant recipients') {
						$_6_c = $res->fields['Certificates'];
						$_6_a = $res->fields['Associates'];
						$_6_b = $res->fields['Bachelors'];
					} else if(strtolower($res->fields['OMStatus']) == 'non first time entering/full time' && strtolower($res->fields['Pell']) == 'nonpell grant recipients') {
						$_7_c = $res->fields['Certificates'];
						$_7_a = $res->fields['Associates'];
						$_7_b = $res->fields['Bachelors'];
					} else if(strtolower($res->fields['OMStatus']) == 'non first time entering/full time' && strtolower($res->fields['Pell']) == 'pell grant recipients') {
						$_8_c = $res->fields['Certificates'];
						$_8_a = $res->fields['Associates'];
						$_8_b = $res->fields['Bachelors'];
					} else if(strtolower($res->fields['OMStatus']) == 'non first time entering/part time' && strtolower($res->fields['Pell']) == 'nonpell grant recipients') {
						$_9_c = $res->fields['Certificates'];
						$_9_a = $res->fields['Associates'];
						$_9_b = $res->fields['Bachelors'];
					} else if(strtolower($res->fields['OMStatus']) == 'non first time entering/part time' && strtolower($res->fields['Pell']) == 'pell grant recipients') {
						$_10_c = $res->fields['Certificates'];
						$_10_a = $res->fields['Associates'];
						$_10_b = $res->fields['Bachelors'];
					}
					$res->MoveNext();
				}
				
				$txt = '
						<table border="1" cellspacing="0" cellpadding="8" width="90%">
							<tr> 
								<td  width="32%" style=""><b>Undergraduate Students</b></td>
								<td  width="23%" style=""><b>Certificates</b></td>
								<td  width="20%" style=""><b>Associate\'s</b></td>
								<td  width="20%" style=""><b>Bachelor\'s</b></td>
							</tr>
					    </table>
					    <br/><br/>
					    <table border="1" cellspacing="0" cellpadding="8" width="90%">
							<tr>
								<td width="31%" ><b>First Time entering</b></td>
								<td width="24%" ><b></b></td>
								<td width="20%" ><b></b></td>
								<td width="20%" ><b></b></td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;<b>Full Time</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_c == '' ? 0 : number_format_value_checker($_2_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_a == '' ? 0 : number_format_value_checker($_2_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_b == '' ? 0 : number_format_value_checker($_2_b)).'</td>
							</tr>

							<tr>
								<td style="border-bottom: 1px solid #CCC;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Pell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_c == '' ? 0 : number_format_value_checker($_2_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_a == '' ? 0 : number_format_value_checker($_2_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_b == '' ? 0 : number_format_value_checker($_2_b)).'</td>
							</tr>

							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>NonPell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_1_c == '' ? 0 : number_format_value_checker($_1_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_1_a == '' ? 0 : number_format_value_checker($_1_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_1_b == '' ? 0 : number_format_value_checker($_1_b)).'</td>
							</tr>
							
							<tr>
								<td >&nbsp;&nbsp;<b>Part-time</b></td>
								<td align="right" >'.($_4_c == '' ? 0 : number_format_value_checker($_4_c)).'</td>
								<td align="right" >'.($_4_a == '' ? 0 : number_format_value_checker($_4_a)).'</td>
								<td align="right" >'.($_4_b == '' ? 0 : number_format_value_checker($_4_b)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Pell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_4_c == '' ? 0 : number_format_value_checker($_4_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_4_a == '' ? 0 : number_format_value_checker($_4_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_4_b == '' ? 0 : number_format_value_checker($_4_b)).'</td>
							</tr>
							<tr>
							<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>NonPell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_3_c == '' ? 0 : number_format_value_checker($_3_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_3_a == '' ? 0 : number_format_value_checker($_3_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_3_b == '' ? 0 : number_format_value_checker($_3_b)).'</td>
							</tr>

						</table>
						<br/><br/>
						<table border="1" cellspacing="0" cellpadding="8" width="90%">
							<tr>
								<td width="31%" ><b>Non-First-time entering</b></td>
								<td width="24%" ></td>
								<td width="20%" ></td>
								<td width="20%" ></td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;<b>Full Time</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_8_c == '' ? 0 : number_format_value_checker($_8_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_8_a == '' ? 0 : number_format_value_checker($_8_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_8_b == '' ? 0 : number_format_value_checker($_8_b)).'</td>
							</tr>

							<tr>
								<td style="border-bottom: 1px solid #CCC;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Pell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_8_c == '' ? 0 : number_format_value_checker($_8_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_8_a == '' ? 0 : number_format_value_checker($_8_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_8_b == '' ? 0 : number_format_value_checker($_8_b)).'</td>
							</tr>

							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>NonPell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_7_c == '' ? 0 : number_format_value_checker($_7_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_7_a == '' ? 0 : number_format_value_checker($_7_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_7_b == '' ? 0 : number_format_value_checker($_7_b)).'</td>
							</tr>
							
							<tr>
								<td >&nbsp;&nbsp;<b>Part-time</b></td>
								<td align="right" >'.($_10_c == '' ? 0 : number_format_value_checker($_10_c)).'</td>
								<td align="right" >'.($_10_a == '' ? 0 : number_format_value_checker($_10_a)).'</td>
								<td align="right" >'.($_10_b == '' ? 0 : number_format_value_checker($_10_b)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Pell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_10_c == '' ? 0 : number_format_value_checker($_10_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_10_a == '' ? 0 : number_format_value_checker($_10_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_10_b == '' ? 0 : number_format_value_checker($_10_b)).'</td>
							</tr>
							<tr>
							<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>NonPell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_9_c == '' ? 0 : number_format_value_checker($_9_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_9_a == '' ? 0 : number_format_value_checker($_9_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_9_b == '' ? 0 : number_format_value_checker($_9_b)).'</td>
							</tr>

						</table>
						<br><br>
						<table border="1" cellspacing="0" cellpadding="8" width="90%">
							<tr>
								<td width="31%" ><b>Total entering</b></td>
								<td width="24%" ></td>
								<td width="20%" ></td>
								<td width="20%" ></td>
							</tr>
					

							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Pell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_c == '' ? 0 : number_format_value_checker($_2_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_e == '' ? 0 : number_format_value_checker($_2_e)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_e == '' ? 0 : number_format_value_checker($_2_e)).'</td>
							</tr>

							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>NonPell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_c == '' ? 0 : number_format_value_checker($_2_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_e == '' ? 0 : number_format_value_checker($_2_e)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_e == '' ? 0 : number_format_value_checker($_2_e)).'</td>
							</tr>
						</table>';
						
				$mpdf->WriteHTML($txt);
			} else if($_POST['OUTCOME_MEASURES'] == 4) {
				$res = $db->Execute("CALL COMP20008(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].", 'Outcome Measures 6 Year')");
				while (!$res->EOF) {
					if(strtolower($res->fields['OMStatus']) == 'first time entering/full time' && strtolower($res->fields['Pell']) == 'nonpell grant recipients') {
						$_1_c = $res->fields['Certificates'];
						$_1_a = $res->fields['Associates'];
						$_1_b = $res->fields['Bachelors'];
					} else if(strtolower($res->fields['OMStatus']) == 'first time entering/full time' && strtolower($res->fields['Pell']) == 'pell grant recipients') {
						$_2_c = $res->fields['Certificates'];
						$_2_a = $res->fields['Associates'];
						$_2_b = $res->fields['Bachelors'];
					} else if(strtolower($res->fields['OMStatus']) == 'first time entering/part time' && strtolower($res->fields['Pell']) == 'nonpell grant recipients') {
						$_3_c = $res->fields['Certificates'];
						$_3_a = $res->fields['Associates'];
						$_3_b = $res->fields['Bachelors'];
					} else if(strtolower($res->fields['OMStatus']) == 'first time entering/part time' && strtolower($res->fields['Pell']) == 'pell grant recipients') {
						$_4_c = $res->fields['Certificates'];
						$_4_a = $res->fields['Associates'];
						$_4_b = $res->fields['Bachelors'];
					} else if(strtolower($res->fields['OMStatus']) == 'first time/full time' && strtolower($res->fields['Pell']) == 'nonpell grant recipients') {
						$_5_c = $res->fields['Certificates'];
						$_5_a = $res->fields['Associates'];
						$_5_b = $res->fields['Bachelors'];
					} else if(strtolower($res->fields['OMStatus']) == 'first time/full time' && strtolower($res->fields['Pell']) == 'pell grant recipients') {
						$_6_c = $res->fields['Certificates'];
						$_6_a = $res->fields['Associates'];
						$_6_b = $res->fields['Bachelors'];
					} else if(strtolower($res->fields['OMStatus']) == 'non first time entering/full time' && strtolower($res->fields['Pell']) == 'nonpell grant recipients') {
						$_7_c = $res->fields['Certificates'];
						$_7_a = $res->fields['Associates'];
						$_7_b = $res->fields['Bachelors'];
					} else if(strtolower($res->fields['OMStatus']) == 'non first time entering/full time' && strtolower($res->fields['Pell']) == 'pell grant recipients') {
						$_8_c = $res->fields['Certificates'];
						$_8_a = $res->fields['Associates'];
						$_8_b = $res->fields['Bachelors'];
					} else if(strtolower($res->fields['OMStatus']) == 'non first time entering/part time' && strtolower($res->fields['Pell']) == 'nonpell grant recipients') {
						$_9_c = $res->fields['Certificates'];
						$_9_a = $res->fields['Associates'];
						$_9_b = $res->fields['Bachelors'];
					} else if(strtolower($res->fields['OMStatus']) == 'non first time entering/part time' && strtolower($res->fields['Pell']) == 'pell grant recipients') {
						$_10_c = $res->fields['Certificates'];
						$_10_a = $res->fields['Associates'];
						$_10_b = $res->fields['Bachelors'];
					}
					$res->MoveNext();
				}
				
				$txt = '
						<table border="1" cellspacing="0" cellpadding="8" width="90%">
							<tr> 
								<td  width="32%" style=""><b>Undergraduate Students</b></td>
								<td  width="23%" style=""><b>Certificates</b></td>
								<td  width="20%" style=""><b>Associate\'s</b></td>
								<td  width="20%" style=""><b>Bachelor\'s</b></td>
							</tr>
					    </table>
					    <br/><br/>
					    <table border="1" cellspacing="0" cellpadding="8" width="90%">
							<tr>
								<td width="31%" ><b>First Time entering</b></td>
								<td width="24%" ><b></b></td>
								<td width="20%" ><b></b></td>
								<td width="20%" ><b></b></td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;<b>Full Time</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_c == '' ? 0 : number_format_value_checker($_2_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_a == '' ? 0 : number_format_value_checker($_2_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_b == '' ? 0 : number_format_value_checker($_2_b)).'</td>
							</tr>

							<tr>
								<td style="border-bottom: 1px solid #CCC;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Pell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_c == '' ? 0 : number_format_value_checker($_2_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_a == '' ? 0 : number_format_value_checker($_2_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_b == '' ? 0 : number_format_value_checker($_2_b)).'</td>
							</tr>

							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>NonPell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_1_c == '' ? 0 : number_format_value_checker($_1_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_1_a == '' ? 0 : number_format_value_checker($_1_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_1_b == '' ? 0 : number_format_value_checker($_1_b)).'</td>
							</tr>
							
							<tr>
								<td >&nbsp;&nbsp;<b>Part-time</b></td>
								<td align="right" >'.($_4_c == '' ? 0 : number_format_value_checker($_4_c)).'</td>
								<td align="right" >'.($_4_a == '' ? 0 : number_format_value_checker($_4_a)).'</td>
								<td align="right" >'.($_4_b == '' ? 0 : number_format_value_checker($_4_b)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Pell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_4_c == '' ? 0 : number_format_value_checker($_4_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_4_a == '' ? 0 : number_format_value_checker($_4_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_4_b == '' ? 0 : number_format_value_checker($_4_b)).'</td>
							</tr>
							<tr>
							<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>NonPell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_3_c == '' ? 0 : number_format_value_checker($_3_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_3_a == '' ? 0 : number_format_value_checker($_3_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_3_b == '' ? 0 : number_format_value_checker($_3_b)).'</td>
							</tr>

						</table>
						<br/><br/>
						<table border="1" cellspacing="0" cellpadding="8" width="90%">
							<tr>
								<td width="31%" ><b>Non-First-time entering</b></td>
								<td width="24%" ></td>
								<td width="20%" ></td>
								<td width="20%" ></td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;<b>Full Time</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_8_c == '' ? 0 : number_format_value_checker($_8_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_8_a == '' ? 0 : number_format_value_checker($_8_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_8_b == '' ? 0 : number_format_value_checker($_8_b)).'</td>
							</tr>

							<tr>
								<td style="border-bottom: 1px solid #CCC;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Pell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_8_c == '' ? 0 : number_format_value_checker($_8_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_8_a == '' ? 0 : number_format_value_checker($_8_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_8_b == '' ? 0 : number_format_value_checker($_8_b)).'</td>
							</tr>

							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>NonPell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_7_c == '' ? 0 : number_format_value_checker($_7_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_7_a == '' ? 0 : number_format_value_checker($_7_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_7_b == '' ? 0 : number_format_value_checker($_7_b)).'</td>
							</tr>
							
							<tr>
								<td >&nbsp;&nbsp;<b>Part-time</b></td>
								<td align="right" >'.($_10_c == '' ? 0 : number_format_value_checker($_10_c)).'</td>
								<td align="right" >'.($_10_a == '' ? 0 : number_format_value_checker($_10_a)).'</td>
								<td align="right" >'.($_10_b == '' ? 0 : number_format_value_checker($_10_b)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Pell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_10_c == '' ? 0 : number_format_value_checker($_10_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_10_a == '' ? 0 : number_format_value_checker($_10_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_10_b == '' ? 0 : number_format_value_checker($_10_b)).'</td>
							</tr>
							<tr>
							<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>NonPell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_9_c == '' ? 0 : number_format_value_checker($_9_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_9_a == '' ? 0 : number_format_value_checker($_9_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_9_b == '' ? 0 : number_format_value_checker($_9_b)).'</td>
							</tr>

						</table>
						<br><br>
						<table border="1" cellspacing="0" cellpadding="8" width="90%">
							<tr>
								<td width="31%" ><b>Total entering</b></td>
								<td width="24%" ></td>
								<td width="20%" ></td>
								<td width="20%" ></td>
							</tr>
					

							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Pell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_c == '' ? 0 : number_format_value_checker($_2_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_e == '' ? 0 : number_format_value_checker($_2_e)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_e == '' ? 0 : number_format_value_checker($_2_e)).'</td>
							</tr>

							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>NonPell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_c == '' ? 0 : number_format_value_checker($_2_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_e == '' ? 0 : number_format_value_checker($_2_e)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_e == '' ? 0 : number_format_value_checker($_2_e)).'</td>
							</tr>
						</table>';
						
				$mpdf->WriteHTML($txt);
			} else if($_POST['OUTCOME_MEASURES'] == 5) {
				$res = $db->Execute("CALL COMP20008(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].", 'Outcome Measures 8 Year')");
				while (!$res->EOF) {
					if(strtolower($res->fields['OMStatus']) == 'first time entering/full time' && strtolower($res->fields['Pell']) == 'nonpell grant recipients') {
						$_1_c 	= $res->fields['Certificates'];
						$_1_a 	= $res->fields['Associates'];
						$_1_b 	= $res->fields['Bachelors'];
						$_1_at 	= $res->fields['Actives'];
						$_1_t 	= $res->fields['Transfers'];
					} else if(strtolower($res->fields['OMStatus']) == 'first time entering/full time' && strtolower($res->fields['Pell']) == 'pell grant recipients') {
						$_2_c 	= $res->fields['Certificates'];
						$_2_a 	= $res->fields['Associates'];
						$_2_b 	= $res->fields['Bachelors'];
						$_2_at 	= $res->fields['Actives'];
						$_2_t 	= $res->fields['Transfers'];
					} else if(strtolower($res->fields['OMStatus']) == 'first time entering/part time' && strtolower($res->fields['Pell']) == 'nonpell grant recipients') {
						$_3_c 	= $res->fields['Certificates'];
						$_3_a 	= $res->fields['Associates'];
						$_3_b 	= $res->fields['Bachelors'];
						$_3_at 	= $res->fields['Actives'];
						$_3_t 	= $res->fields['Transfers'];
					} else if(strtolower($res->fields['OMStatus']) == 'first time entering/part time' && strtolower($res->fields['Pell']) == 'pell grant recipients') {
						$_4_c 	= $res->fields['Certificates'];
						$_4_a 	= $res->fields['Associates'];
						$_4_b 	= $res->fields['Bachelors'];
						$_4_at 	= $res->fields['Actives'];
						$_4_t 	= $res->fields['Transfers'];
					} else if(strtolower($res->fields['OMStatus']) == 'first time/full time' && strtolower($res->fields['Pell']) == 'nonpell grant recipients') {
						$_5_c 	= $res->fields['Certificates'];
						$_5_a 	= $res->fields['Associates'];
						$_5_b 	= $res->fields['Bachelors'];
						$_5_at 	= $res->fields['Actives'];
						$_5_t 	= $res->fields['Transfers'];
					} else if(strtolower($res->fields['OMStatus']) == 'first time/full time' && strtolower($res->fields['Pell']) == 'pell grant recipients') {
						$_6_c 	= $res->fields['Certificates'];
						$_6_a 	= $res->fields['Associates'];
						$_6_b 	= $res->fields['Bachelors'];
						$_6_at 	= $res->fields['Actives'];
						$_6_t 	= $res->fields['Transfers'];
					} else if(strtolower($res->fields['OMStatus']) == 'non first time entering/full time' && strtolower($res->fields['Pell']) == 'nonpell grant recipients') {
						$_7_c 	= $res->fields['Certificates'];
						$_7_a 	= $res->fields['Associates'];
						$_7_b 	= $res->fields['Bachelors'];
						$_7_at 	= $res->fields['Actives'];
						$_7_t 	= $res->fields['Transfers'];
					} else if(strtolower($res->fields['OMStatus']) == 'non first time entering/full time' && strtolower($res->fields['Pell']) == 'pell grant recipients') {
						$_8_c	 = $res->fields['Certificates'];
						$_8_a 	= $res->fields['Associates'];
						$_8_b 	= $res->fields['Bachelors'];
						$_8_at 	= $res->fields['Actives'];
						$_8_t 	= $res->fields['Transfers'];
					} else if(strtolower($res->fields['OMStatus']) == 'non first time entering/part time' && strtolower($res->fields['Pell']) == 'nonpell grant recipients') {
						$_9_c 	= $res->fields['Certificates'];
						$_9_a 	= $res->fields['Associates'];
						$_9_b 	= $res->fields['Bachelors'];
						$_9_at 	= $res->fields['Actives'];
						$_9_t 	= $res->fields['Transfers'];
					} else if(strtolower($res->fields['OMStatus']) == 'non first time entering/part time' && strtolower($res->fields['Pell']) == 'pell grant recipients') {
						$_10_c 	= $res->fields['Certificates'];
						$_10_a 	= $res->fields['Associates'];
						$_10_b 	= $res->fields['Bachelors'];
						$_10_at = $res->fields['Actives'];
						$_10_t 	= $res->fields['Transfers'];
					}
					$res->MoveNext();
				}
				
				$txt = '
						<table border="1" cellspacing="0" cellpadding="8" width="90%">
							<tr> 
								<td  width="32%" style=""><b>Undergraduate Students</b></td>
								<td  width="23%" style=""><b>Certificates</b></td>
								<td  width="20%" style=""><b>Associate\'s</b></td>
								<td  width="20%" style=""><b>Bachelor\'s</b></td>
							</tr>
					    </table>
					    <br/><br/>
					    <table border="1" cellspacing="0" cellpadding="8" width="90%">
							<tr>
								<td width="31%" ><b>First Time entering</b></td>
								<td width="24%" ><b></b></td>
								<td width="20%" ><b></b></td>
								<td width="20%" ><b></b></td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;<b>Full Time</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_c == '' ? 0 : number_format_value_checker($_2_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_a == '' ? 0 : number_format_value_checker($_2_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_b == '' ? 0 : number_format_value_checker($_2_b)).'</td>
							</tr>

							<tr>
								<td style="border-bottom: 1px solid #CCC;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Pell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_c == '' ? 0 : number_format_value_checker($_2_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_a == '' ? 0 : number_format_value_checker($_2_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_b == '' ? 0 : number_format_value_checker($_2_b)).'</td>
							</tr>

							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>NonPell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_1_c == '' ? 0 : number_format_value_checker($_1_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_1_a == '' ? 0 : number_format_value_checker($_1_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_1_b == '' ? 0 : number_format_value_checker($_1_b)).'</td>
							</tr>
							
							<tr>
								<td >&nbsp;&nbsp;<b>Part-time</b></td>
								<td align="right" >'.($_4_c == '' ? 0 : number_format_value_checker($_4_c)).'</td>
								<td align="right" >'.($_4_a == '' ? 0 : number_format_value_checker($_4_a)).'</td>
								<td align="right" >'.($_4_b == '' ? 0 : number_format_value_checker($_4_b)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Pell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_4_c == '' ? 0 : number_format_value_checker($_4_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_4_a == '' ? 0 : number_format_value_checker($_4_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_4_b == '' ? 0 : number_format_value_checker($_4_b)).'</td>
							</tr>
							<tr>
							<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>NonPell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_3_c == '' ? 0 : number_format_value_checker($_3_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_3_a == '' ? 0 : number_format_value_checker($_3_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_3_b == '' ? 0 : number_format_value_checker($_3_b)).'</td>
							</tr>

						</table>
						<br/><br/>
						<table border="1" cellspacing="0" cellpadding="8" width="90%">
							<tr>
								<td width="31%" ><b>Non-First-time entering</b></td>
								<td width="24%" ></td>
								<td width="20%" ></td>
								<td width="20%" ></td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;<b>Full Time</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_8_c == '' ? 0 : number_format_value_checker($_8_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_8_a == '' ? 0 : number_format_value_checker($_8_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_8_b == '' ? 0 : number_format_value_checker($_8_b)).'</td>
							</tr>

							<tr>
								<td style="border-bottom: 1px solid #CCC;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Pell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_8_c == '' ? 0 : number_format_value_checker($_8_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_8_a == '' ? 0 : number_format_value_checker($_8_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_8_b == '' ? 0 : number_format_value_checker($_8_b)).'</td>
							</tr>

							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>NonPell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_7_c == '' ? 0 : number_format_value_checker($_7_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_7_a == '' ? 0 : number_format_value_checker($_7_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_7_b == '' ? 0 : number_format_value_checker($_7_b)).'</td>
							</tr>
							
							<tr>
								<td >&nbsp;&nbsp;<b>Part-time</b></td>
								<td align="right" >'.($_10_c == '' ? 0 : number_format_value_checker($_10_c)).'</td>
								<td align="right" >'.($_10_a == '' ? 0 : number_format_value_checker($_10_a)).'</td>
								<td align="right" >'.($_10_b == '' ? 0 : number_format_value_checker($_10_b)).'</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Pell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_10_c == '' ? 0 : number_format_value_checker($_10_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_10_a == '' ? 0 : number_format_value_checker($_10_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_10_b == '' ? 0 : number_format_value_checker($_10_b)).'</td>
							</tr>
							<tr>
							<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>NonPell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_9_c == '' ? 0 : number_format_value_checker($_9_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_9_a == '' ? 0 : number_format_value_checker($_9_a)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_9_b == '' ? 0 : number_format_value_checker($_9_b)).'</td>
							</tr>

						</table>
						<br><br>
						<table border="1" cellspacing="0" cellpadding="8" width="90%">
							<tr>
								<td width="31%" ><b>Total entering</b></td>
								<td width="24%" ></td>
								<td width="20%" ></td>
								<td width="20%" ></td>
							</tr>
					

							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Pell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_c == '' ? 0 : number_format_value_checker($_2_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_e == '' ? 0 : number_format_value_checker($_2_e)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_e == '' ? 0 : number_format_value_checker($_2_e)).'</td>
							</tr>

							<tr>
								<td style="border-bottom: 1px solid #CCC;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>NonPell Grant recipients</b></td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_c == '' ? 0 : number_format_value_checker($_2_c)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_e == '' ? 0 : number_format_value_checker($_2_e)).'</td>
								<td style="border-bottom: 1px solid #CCC;" align="right" >'.($_2_e == '' ? 0 : number_format_value_checker($_2_e)).'</td>
							</tr>
						</table>';
						
				$mpdf->WriteHTML($txt);
			}
			
			$mpdf->Output($REPORT_NAME.'.pdf', 'D');
		}
	}
	
}
$res = $db->Execute("select * from S_IPEDS_WINTER_COLLECTION WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$EXCLUDED_PROGRAM_ARR 		 = explode(",",$res->fields['EXCLUDED_PROGRAM']);

$REQUIRED_FIELDS = "Registrar > Student > Info Tab > Last Name
Registrar > Student > Info Tab > First Name
Registrar > Student > Enrollment Tab > First Term Date
Registrar > Student > Enrollment Tab > Program
Registrar > Student > Enrollment Tab > Status
Registrar > Student > Enrollment Tab > Full/Part Time
Registrar > Student > Enrollment Tab > IPEDS Enrollment Status
Registrar > Student > Enrollment Tab > Enrollment End Date (Where Applicable)
Registrar > Student > Enrollment Tab > Campus

Finance > Student > Finance Plan > Financial Aid > Award Year
Finance > Student > Finance Plan > Financial Aid > ISIR Trans No.
Finance > Student > Finance Plan > Financial Aid > COA Category
Finance > Student > Finance Plan > Financial Aid > FISAP Total Income

Accounting > Student > Ledger

Setup > Registrar > Program > Info Tab > Program Code
Setup > Registrar > Program > Info Tab > Program Description
Setup > Registrar > Program > Info Tab > Credential Level
Setup > Student > Student Status > End Date
";
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
	<title><?=MNU_IPEDS_WINTER_COLLECTION?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		#advice-required-entry-PK_CAMPUS {position: absolute;top: 30px;width: 142px}
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
                        <h4 class="text-themecolor"><?=MNU_IPEDS_WINTER_COLLECTION?> <span style="color:red">- Under Developement</span></h4> 
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" >
								<div class="p-20">
									<div class="d-flex">
										<div class="col-7 col-sm-7 ">
											
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="PK_AWARD_YEAR" name="PK_AWARD_YEAR" class="form-control required-entry" >
														<option></option>
														<? $res_type = $db->Execute("select AWARD_YEAR,PK_AWARD_YEAR from M_AWARD_YEAR WHERE ACTIVE = 1 AND PK_AWARD_YEAR IN (4,5,6) order by AWARD_YEAR ASC");
														while (!$res_type->EOF) { ?>
															<option value="<?=$res_type->fields['PK_AWARD_YEAR']?>" ><?=$res_type->fields['AWARD_YEAR']?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
													<span class="bar"></span> 
													<label for="PK_AWARD_YEAR" ><?=AWARD_YEAR?></label>
												</div>
											</div>
											
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control required-entry" >
														<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
														while (!$res_type->EOF) { 
															$selected = "";
															if(!empty($_SESSION['SRC_PK_CAMPUS'])){
																foreach($_SESSION['SRC_PK_CAMPUS'] as $PK_CAMPUS1){
																	if($res_type->fields['PK_CAMPUS'] == $PK_CAMPUS1)
																		$selected = "selected";
																}
															} ?>
															<option value="<?=$res_type->fields['PK_CAMPUS']?>" <?=$selected?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="d-flex">
												<div class="col-8 col-sm-8 form-group">
													<select id="STUDENT_FINANCIAL_AID" name="STUDENT_FINANCIAL_AID" class="form-control" >
														<option></option>
														<option value="1" >Section 1 Part A,B,C Datasheet</option>
														<option value="2" >Section 1 Part A</option>
														<option value="3" >Section 1 Part B</option>
														<option value="4" >Section 1 Part C</option>
														<option value="5" >Section 1 Part D Datasheet</option>
														<option value="6" >Section 1 Part D</option>
														<option value="7" >Section 1 Part E Datasheet</option>
														<option value="8" >Section 1 Part E</option>
														<!-- <option value="9" >Section 1 Part Eb</option> -->
														<option value="10" >Section 2 Military Datasheet</option>
														<option value="11" >Section 2 Military</option>
													</select>
													<span class="bar"></span> 
													<label for="STUDENT_FINANCIAL_AID" ><?=STUDENT_FINANCIAL_AID?></label>
												</div>
												<div class="col-2 col-sm-2 form-group" style="flex: 0 0 18.667%;max-width: 18.667%;" >&nbsp;</div>
												<div class="col-2 col-sm-2 form-group" style="flex: 0 0 15.667%;max-width: 15.667%;" >
													<button type="button" class="btn waves-effect waves-light btn-dark" onclick="generate_report('FA')" ><?=GENERATE?></button>
												</div>
											</div>
											
											<div class="d-flex">
												<div class="col-8 col-sm-8 form-group">
													<select id="GRADUATION_RATES" name="GRADUATION_RATES" class="form-control" >
														<option></option>
														<option value="1" >Graduation Rates Datasheet</option>
														<option value="2" >Winter Cohort</option>
														<option value="3" >Winter Completers Within 150%</option>
														<option value="4" >Winter Transfers/Exclusions</option>
														<option value="5" >Winter Completers Within 100%</option>
														<option value="6" >Winter FA Recipients</option>
													</select>
													<span class="bar"></span> 
													<label for="GRADUATION_RATES" ><?=GRADUATION_RATES?></label>
												</div>
												<div class="col-2 col-sm-2 form-group" style="flex: 0 0 18.667%;max-width: 18.667%;" >
													<select id="GRADUATION_RATES_LENGTH" name="GRADUATION_RATES_LENGTH" class="form-control" >
														<option></option>
														<option value="Hours">Hours</option>
														<option value="Weeks" >Weeks</option>
													</select>
													<span class="bar"></span> 
													<label for="GRADUATION_RATES_LENGTH" ><?=PROGRAM_LENGTH?></label>
												</div>
												<div class="col-2 col-sm-2 form-group" style="flex: 0 0 15.667%;max-width: 15.667%;" >
													<button type="button" class="btn waves-effect waves-light btn-dark" onclick="generate_report('GR')" ><?=GENERATE?></button>
												</div>
											</div>
											
											<div class="d-flex">
												<div class="col-8 col-sm-8 form-group">
													<select id="_200_GRADUATION_RATES" name="_200_GRADUATION_RATES" class="form-control" >
														<option></option>
														<option value="1" >Winter 200% Graduation Rates Datasheet</option>
														<option value="2" >Winter 200% Graduation Rates</option>
													</select>
													<span class="bar"></span> 
													<label for="_200_GRADUATION_RATES" ><?=_200_GRADUATION_RATES?></label>
												</div>
												<div class="col-2 col-sm-2 form-group" style="flex: 0 0 18.667%;max-width: 18.667%;" >
													<select id="_200_GRADUATION_RATES_LENGTH" name="_200_GRADUATION_RATES_LENGTH" class="form-control" >
														<option></option>
														<option value="Hours">Hours</option>
														<option value="Weeks" >Weeks</option>
													</select>
													<span class="bar"></span> 
													<label for="_200_GRADUATION_RATES_LENGTH" ><?=PROGRAM_LENGTH?></label>
												</div>
												<div class="col-2 col-sm-2 form-group" style="flex: 0 0 15.667%;max-width: 15.667%;" >
													<button type="button" class="btn waves-effect waves-light btn-dark" onclick="generate_report('200_GR')" ><?=GENERATE?></button>
												</div>
											</div>
											
											<div class="d-flex">
												<div class="col-8 col-sm-8 form-group">
													<select id="ADMISSIONS" name="ADMISSIONS" class="form-control" >
														<option></option>
														<option value="1" >Admissions Datasheet</option>
														<option value="2" >AAE</option>
														<option value="3" >Test Scores</option>
													</select>
													<span class="bar"></span> 
													<label for="ADMISSIONS" ><?=ADMISSIONS?></label>
												</div>
												<div class="col-2 col-sm-2 form-group" style="flex: 0 0 18.667%;max-width: 18.667%;" >&nbsp;</div>
												<div class="col-2 col-sm-2 form-group" style="flex: 0 0 15.667%;max-width: 15.667%;" >
													<button type="button" class="btn waves-effect waves-light btn-dark" onclick="generate_report('AD')" ><?=GENERATE?></button>
												</div>
											</div>
											
											<div class="d-flex">
												<div class="col-8 col-sm-8 form-group">
													<select id="OUTCOME_MEASURES" name="OUTCOME_MEASURES" class="form-control" >
														<option></option>
														<option value="1" >Outcome Measures Datasheet</option>
														<option value="2" >Outcome Measures Cohort</option>
														<option value="3" >Outcome Measures 4 Year</option>
														<option value="4" >Outcome Measures 6 Year</option>
														<option value="5" >Outcome Measures 8 Year</option>
													</select>
													<span class="bar"></span> 
													<label for="OUTCOME_MEASURES" ><?=OUTCOME_MEASURES?></label>
												</div>
												<div class="col-2 col-sm-2 form-group" style="flex: 0 0 18.667%;max-width: 18.667%;">
													<!--<select id="OUTCOME_MEASURES_LENGTH" name="OUTCOME_MEASURES_LENGTH" class="form-control" >
														<option></option>
														<option value="Hours">Hours</option>
														<option value="Weeks" >Weeks</option>
													</select>
													<span class="bar"></span> 
													<label for="OUTCOME_MEASURES_LENGTH" ><?=PROGRAM_LENGTH?></label>-->
												</div>
												<div class="col-2 col-sm-2 form-group" style="flex: 0 0 15.667%;max-width: 15.667%;" >
													<button type="button" class="btn waves-effect waves-light btn-dark" onclick="generate_report('OM')" ><?=GENERATE?></button>
												</div>
											</div>
										</div>
										<div class="col-5 col-sm-5 ">
											<div class="d-flex">
												<div class="col-3 col-sm-3 ">
													<span class="bar"></span> 
													<label ><?=REQUIRED_FIELDS?></label>
												</div>
											
												<div class="col-3 col-sm-3 focused">
													<button type="button" onclick="show_required_fields()"  class="btn waves-effect waves-light btn-dark"><?=REQUIREMENTS?></button>
												</div>
											</div>
											
											<div class="d-flex">
												<div class="col-12 col-sm-12 " style="display:none" id="REQUIRED_FIELDS_DIV" >
													<br />
													<?=nl2br($REQUIRED_FIELDS) ?>
												</div>
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="col-3 col-sm-3">
										</div>
										<div class="col-9 col-sm-9">
											<input type="hidden" name="FORMAT" id="FORMAT" >
											<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
											<button type="button" onclick="window.location.href='index'"  class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
										</div>
									</div>
								</div>
							</form>
                        </div>
					</div>
				</div>
				
            </div>
        </div>
        <? require_once("footer.php"); ?>
    </div>
   
	<? require_once("js.php"); ?>

	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	
	<script type="text/javascript">
		function generate_report(type){
			document.getElementById('STUDENT_FINANCIAL_AID').className 	= 'form-control';
			document.getElementById('GRADUATION_RATES').className 		= 'form-control';
			document.getElementById('_200_GRADUATION_RATES').className 	= 'form-control';
			document.getElementById('ADMISSIONS').className 			= 'form-control';
			document.getElementById('OUTCOME_MEASURES').className 		= 'form-control';
			
			document.getElementById('GRADUATION_RATES_LENGTH').className 		= 'form-control';
			document.getElementById('_200_GRADUATION_RATES_LENGTH').className 	= 'form-control';
			//document.getElementById('OUTCOME_MEASURES_LENGTH').className 		= 'form-control';
			
			if(type == 'FA')
				document.getElementById('STUDENT_FINANCIAL_AID').className 	= 'form-control required-entry';
			else if(type == 'GR'){
				document.getElementById('GRADUATION_RATES').className 			= 'form-control required-entry';
				document.getElementById('GRADUATION_RATES_LENGTH').className 	= 'form-control required-entry';
			} else if(type == '200_GR'){
				document.getElementById('_200_GRADUATION_RATES').className 			= 'form-control required-entry';
				document.getElementById('_200_GRADUATION_RATES_LENGTH').className 	= 'form-control required-entry';
			} else if(type == 'AD'){
				document.getElementById('ADMISSIONS').className = 'form-control required-entry';
			} else if(type == 'OM'){
				document.getElementById('OUTCOME_MEASURES').className 			= 'form-control required-entry';
				//document.getElementById('OUTCOME_MEASURES_LENGTH').className 	= 'form-control required-entry';
			}
				
			jQuery(document).ready(function($) {
				var valid = new Validation('form1', {onSubmit:false});
				var result = valid.validate();
				if(result == true){ 
					document.getElementById('FORMAT').value = type
					document.form1.submit();
				}
			});
		}
		function show_required_fields(){
			if(document.getElementById('REQUIRED_FIELDS_DIV').style.display == 'none')
				document.getElementById('REQUIRED_FIELDS_DIV').style.display = 'block';
			else
				document.getElementById('REQUIRED_FIELDS_DIV').style.display = 'none';
		}
		
		function show_setup(){
			var w = 1300;
			var h = 550;
			// var id = common_id;
			var left = (screen.width/2)-(w/2);
			var top = (screen.height/2)-(h/2);
			var parameter = 'toolbar=0,menubar=0,location=0,status=1,scrollbars=1,resizable=1,width='+w+', height='+h+', top='+top+', left='+left;
			window.open('program_award_level_setup','',parameter);
			return false;
		}
	</script>
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#EXCLUDED_DROP_REASON').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=EXCLUDED_DROP_REASON?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=EXCLUDED_DROP_REASON?> selected'
		});
		
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '<?=CAMPUS?>',
			numberDisplayed: 1,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		/////////////////
		
	});
	</script>
</body>

</html>
