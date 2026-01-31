<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_ACCREDITATION') == 0 ){
	header("location:../index");
	exit;
}

$res = $db->Execute("SELECT COE FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
if($res->fields['COE'] == 0 || $res->fields['COE'] == '') {
	header("location:../index");
	exit;
}

if(!empty($_POST)){

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
	
	if($_POST['GROUP_BY_PROGRAM_CODE'] == 1)
		$label = "Program Group";
	else
		$label = "Program";
		
	if($_POST['GROUP_BY_PROGRAM_CODE'] == 1)
		$GROUP_BY_PROGRAM_CODE = "TRUE";
	else
		$GROUP_BY_PROGRAM_CODE = "FALSE";
	
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
				global $db, $campus_name;
 
				$this->SetFont('helvetica', 'B', 10);
				$this->SetY(15);
				$this->SetX(6);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, 'Campus: ', 0, false, 'L', 0, '', 0, false, 'M', 'L');
				
				$this->SetY(20);
				$this->SetX(6);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, 'Reporting Period: ', 0, false, 'L', 0, '', 0, false, 'M', 'L');
				
				$this->SetFont('helvetica', '', 10);
				$this->SetY(15);
				$this->SetX(40);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, $campus_name, 0, false, 'L', 0, '', 0, false, 'M', 'L');
				
				$this->SetY(20);
				$this->SetX(40);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, $_POST['START_DATE'].' ---- '.$_POST['END_DATE'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
				
				$this->SetFont('helvetica', 'B', 13);
				$this->SetY(10);
				$this->SetX(181);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, 'Completion, Placement, and Licensure Form', 0, false, 'L', 0, '', 0, false, 'M', 'L');
				
				$this->SetY(16);
				$this->SetX(200);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, 'for Postsecondary Programs', 0, false, 'L', 0, '', 0, false, 'M', 'L');
				
				$this->SetY(22);
				$this->SetX(190);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, '-- Public and Non-Public Institutions --', 0, false, 'L', 0, '', 0, false, 'M', 'L');
			}
			public function Footer() {
				global $db;
				
				/*$this->SetY(-15);
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
					
				$this->Cell(30, 10, $date, 0, false, 'C', 0, '', 0, false, 'T', 'M'); */
				
				$this->SetY(-15);
				$this->SetX(130);
				$this->SetFont('helvetica', 'I', 7);
				$this->Cell(30, 10, 'COMP10001', 0, false, 'C', 0, '', 0, false, 'T', 'M');
			}
			
			 public function Test( $ae ) {
				if( !isset($this->xywalter) ) {
					$this->xywalter = array();
				}
				$this->xywalter[] = array($this->GetX(), $this->GetY());
			}
		}

		$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,68,255), array(0,68,128));
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(7, 31, 7);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, 18);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->setLanguageArray($l);
		$pdf->setFontSubsetting(true);
		$pdf->SetFont('helvetica', '', 8, '', true);

		$params = $pdf->serializeTCPDFtagParameters(array(90));
		// other configs
		$pdf->setOpenCell(0);
		$pdf->SetCellPadding(0);
		$pdf->setCellHeightRatio(1.25);

		$pdf->AddPage();

		// create some HTML content
		$html = '<table width="100%" border="1" cellspacing="0" cellpadding="2">
					<thead>
						<tr >
							<th align="center" width="93px" >&nbsp;</th>
							<th align="center" width="140px" ><b>Enrollment</b></th>
							<th align="center" width="105px" bgcolor="#ffff9a" ><b>Completion</b></th>
							<th align="center" width="140px" bgcolor="#9accff" ><b>Placement</b></th>
							<th align="center" width="105px" bgcolor="#ccffcc" ><b>Licensure</b></th>
						</tr>
						<tr >
							<th align="center" height="210" width="93px" valign="bottom" ><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />'.$label.'</th>
							<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
							<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
							<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
							<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
							<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
							<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
							<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
							<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
							<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
							<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
							<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
							<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
							<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
							<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
							<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
							<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
							<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
							<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
							<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
							<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
							<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
							<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
							<th bgcolor="#ffff9a" align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
							<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
							<th bgcolor="#9accff" align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
							<th bgcolor="#ccffcc" align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
						</tr>
					</thead>
					<tbody> ';
					
					$TOT_CPL5  = 0;
					$TOT_CPL6  = 0;
					$TOT_CPL7  = 0;
					$TOT_CPL8  = 0;
					$TOT_CPL9  = 0;
					$TOT_CPL10 = 0;
					$TOT_CPL11 = 0;
					$TOT_CPL12 = 0;
					$TOT_CPL13 = 0;
					$TOT_CPL14 = 0;
					$TOT_CPL15 = 0;
					$TOT_CPL16 = 0;
					$TOT_CPL17 = 0;
					$TOT_CPL18 = 0;
					$TOT_CPL19 = 0;
					$TOT_CPL20 = 0;
					$TOT_CPL21 = 0;
					$TOT_CPL22 = 0;
					$TOT_CPL23 = 0;
						
					$index = 0;
					$res = $db->Execute("CALL `COMP10001`(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".$ST."','".$ET."',".$GROUP_BY_PROGRAM_CODE.", FALSE)");
					while (!$res->EOF) {
						$CPL23 = ($res->fields['CPL16'] + $res->fields['CPL19'] + $res->fields['CPL20']);
						
						$TOT_CPL5  += $res->fields['CPL5'];
						$TOT_CPL6  += $res->fields['CPL6'];
						$TOT_CPL7  += $res->fields['CPL7'];
						$TOT_CPL8  += $res->fields['CPL8'];
						$TOT_CPL9  += $res->fields['CPL9'];
						$TOT_CPL10 += $res->fields['CPL10'];
						$TOT_CPL11 += $res->fields['CPL11'];
						$TOT_CPL12 += $res->fields['CPL12'];
						$TOT_CPL13 += $res->fields['CPL13'];
						$TOT_CPL14 += $res->fields['CPL14'];
						$TOT_CPL15 += $res->fields['CPL15'];
						$TOT_CPL16 += $res->fields['CPL16'];
						$TOT_CPL17 += $res->fields['CPL17'];
						$TOT_CPL18 += $res->fields['CPL18'];
						$TOT_CPL19 += $res->fields['CPL19'];
						$TOT_CPL20 += $res->fields['CPL20'];
						$TOT_CPL21 += $res->fields['CPL21'];
						$TOT_CPL22 += $res->fields['CPL22'];
						$TOT_CPL23 += $CPL23;
						
						if($res->fields['CPL7'] - $res->fields['CPL8'] > 0)
							$per_26 = (($res->fields['CPL10'])/($res->fields['CPL7']-$res->fields['CPL8']))*100;
						else
							$per_26 = 0;
							
						if($res->fields['CPL7'] - $res->fields['CPL8'] > 0)
							$per_27 = (($res->fields['CPL11'])/($res->fields['CPL7']-$res->fields['CPL8']))*100;
						else
							$per_27 = 0;
							
						if($res->fields['CPL10'] - $CPL23 > 0)
							$per_28 = (($res->fields['CPL13'])/($res->fields['CPL10']-$CPL23))*100;
						else
							$per_28 = 0;
							
						if($res->fields['CPL11'] - $CPL23 > 0)
							$per_29 = (($res->fields['CPL14'])/($res->fields['CPL11']-$CPL23))*100;
						else
							$per_29 = 0;
							
						if($res->fields['CPL17'] > 0)
							$per_30 = (($res->fields['CPL18'])/($res->fields['CPL17']))*100;
						else
							$per_30 = 0;
							
						$style_7  	 = "";
						$style_9_10  = "";
						$style_11 	 = "";
						$style_23 	 = "";
						$style_27 	 = "";
						$style_29 	 = "";
						$style_30 	 = "";
						
						if(($res->fields['CPL8'] + $res->fields['CPL11'] + $res->fields['CPL22']) != $res->fields['CPL7']){
							$style_7  = 'style="color:red"';
						}
						
						if(($res->fields['CPL9'] + $res->fields['CPL10']) != $res->fields['CPL11']){
							$style_9_10  = 'style="color:red"';
						}
						
						if(($res->fields['CPL14'] + $res->fields['CPL15'] + $res->fields['CPL16'] + $res->fields['CPL19'] + $res->fields['CPL20'] + $res->fields['CPL21']) != $res->fields['CPL11']){
							$style_11  = 'style="color:red"';
						}
						
						if($CPL23 > $res->fields['CPL10']){
							$style_23  = 'style="color:red"';
						}
						
						if($per_27 < 60){
							$style_27  = 'style="color:red"';
						}
						
						if($per_29 < 70){
							$style_29  = 'style="color:red"';
						}
						
						if($per_30 < 70){
							$style_30  = 'style="color:red"';
						}
						
						$html .= '<tr >
								<th width="93px" >'.$res->fields['Program'].'</th>
								<th align="right" width="35px" >'.$res->fields['CPL5'].'</th>
								<th align="right" width="35px" >'.$res->fields['CPL6'].'</th>
								<th align="right" width="35px" '.$style_7.' >'.$res->fields['CPL7'].'</th>
								<th align="right" width="35px" >'.$res->fields['CPL8'].'</th>
								<th align="right" width="35px" '.$style_9_10.' >'.$res->fields['CPL9'].'</th>
								<th align="right" width="35px" '.$style_9_10.' >'.$res->fields['CPL10'].'</th>
								<th align="right" width="35px" '.$style_11.' >'.$res->fields['CPL11'].'</th>
								<th align="right" width="35px" >'.$res->fields['CPL12'].'</th>
								<th align="right" width="35px" >'.$res->fields['CPL13'].'</th>
								<th align="right" width="35px" >'.$res->fields['CPL14'].'</th>
								<th align="right" width="35px" >'.$res->fields['CPL15'].'</th>
								<th align="right" width="35px" >'.$res->fields['CPL16'].'</th>
								<th align="right" width="35px" >'.$res->fields['CPL17'].'</th>
								<th align="right" width="35px" >'.$res->fields['CPL18'].'</th>
								<th align="right" width="35px" >'.$res->fields['CPL19'].'</th>
								<th align="right" width="35px" >'.$res->fields['CPL20'].'</th>
								<th align="right" width="35px" >'.$res->fields['CPL21'].'</th>
								<th align="right" width="35px" >'.$res->fields['CPL22'].'</th>
								<th align="right" width="35px" '.$style_23.' >'.($res->fields['CPL16'] + $res->fields['CPL19'] + $res->fields['CPL20']).'</th>
								<th align="right" width="35px" >'.($res->fields['CPL10'] - $CPL23).'</th>
								<th align="right" width="35px" >'.($res->fields['CPL11'] - $CPL23).'</th>
								<th align="right" width="35px" >'.round($per_26).'</th>
								<th align="right" width="35px" '.$style_27.' >'.round($per_27).'</th>
								<th align="right" width="35px" >'.round($per_28).'</th>
								<th align="right" width="35px" '.$style_29.' >'.round($per_29).'</th>
								<th align="right" width="35px" '.$style_30.' >'.round($per_30).'</th>
							</tr>';
							
							if($res->fields['E1'] != '') {
								$ERROR_1_ARR[$index] = $res->fields['E1'];
							}
							
							if($res->fields['E2'] != '') {
								$ERROR_2_ARR[$index] = $res->fields['E2'];
							}
							
							if($res->fields['E3'] != '') {
								$ERROR_3_ARR[$index] = $res->fields['E3'];
							}
							
							if($res->fields['E4'] != '') {
								$ERROR_4_ARR[$index] = $res->fields['E4'];
							}
							
							if($res->fields['E5'] != '') {
								$ERROR_5_ARR[$index] = $res->fields['E5'];
							}
							
						$index++;
						$res->MoveNext();
					}
					
					if($TOT_CPL7 - $TOT_CPL8 > 0)
						$per_26 = (($TOT_CPL10)/($TOT_CPL7 - $TOT_CPL8))*100;
					else
						$per_26 = 0;
						
					if($TOT_CPL7 - $TOT_CPL8 > 0)
						$per_27 = (($TOT_CPL11)/($TOT_CPL7-$TOT_CPL8))*100;
					else
						$per_27 = 0;
						
					if($TOT_CPL10 - $TOT_CPL23 > 0)
						$per_28 = (($TOT_CPL13)/($TOT_CPL10-$TOT_CPL23))*100;
					else
						$per_28 = 0;
						
					if($TOT_CPL11 - $TOT_CPL23 > 0)
						$per_29 = (($TOT_CPL14)/($TOT_CPL11-$TOT_CPL23))*100;
					else
						$per_29 = 0;
						
					if($TOT_CPL17 > 0)
						$per_30 = (($TOT_CPL18)/($TOT_CPL17))*100;
					else
						$per_30 = 0;
						
					$style_7  	 = "";
					$style_9_10  = "";
					$style_11 	 = "";
					$style_23 	 = "";
					$style_27 	 = "";
					$style_29 	 = "";
					$style_30 	 = "";
					
					if(($TOT_CPL8 + $TOT_CPL11 + $TOT_CPL22) != $TOT_CPL7){
						$style_7  = 'style="color:red"';
					}
					
					if(($TOT_CPL9 + $TOT_CPL10) != $TOT_CPL11){
						$style_9_10  = 'style="color:red"';
					}
					
					if(($TOT_CPL14 + $TOT_CPL15 + $TOT_CPL16 + $TOT_CPL19 + $TOT_CPL20 + $TOT_CPL21) != $TOT_CPL11){
						$style_11  = 'style="color:red"';
					}
					
					if($TOT_CPL23 > $TOT_CPL10){
						$style_23  = 'style="color:red"';
					}
					
					if($per_27 < 60){
						$style_27  = 'style="color:red"';
					}
					
					if($per_29 < 70){
						$style_29  = 'style="color:red"';
					}
					
					if($per_30 < 70){
						$style_30  = 'style="color:red"';
					}
						
					$html .= '<tr >
								<th width="93px" ><b>Totals</b></th>
								<th align="right" width="35px" ><b>'.$TOT_CPL5.'</b></th>
								<th align="right" width="35px" ><b>'.$TOT_CPL6.'</b></th>
								<th align="right" width="35px" '.$style_7.' ><b>'.$TOT_CPL7.'</b></th>
								<th align="right" width="35px" ><b>'.$TOT_CPL8.'</b></th>
								<th align="right" width="35px" '.$style_9_10.' ><b>'.$TOT_CPL9.'</b></th>
								<th align="right" width="35px" '.$style_9_10.' ><b>'.$TOT_CPL10.'</b></th>
								<th align="right" width="35px" '.$style_11.' ><b>'.$TOT_CPL11.'</b></th>
								<th align="right" width="35px" ><b>'.$TOT_CPL12.'</b></th>
								<th align="right" width="35px" ><b>'.$TOT_CPL13.'</b></th>
								<th align="right" width="35px" ><b>'.$TOT_CPL14.'</b></th>
								<th align="right" width="35px" ><b>'.$TOT_CPL15.'</b></th>
								<th align="right" width="35px" ><b>'.$TOT_CPL16.'</b></th>
								<th align="right" width="35px" ><b>'.$TOT_CPL17.'</b></th>
								<th align="right" width="35px" ><b>'.$TOT_CPL18.'</b></th>
								<th align="right" width="35px" ><b>'.$TOT_CPL19.'</b></th>
								<th align="right" width="35px" ><b>'.$TOT_CPL20.'</b></th>
								<th align="right" width="35px" ><b>'.$TOT_CPL21.'</b></th>
								<th align="right" width="35px" ><b>'.$TOT_CPL22.'</b></th>
								<th align="right" width="35px" '.$style_23.' ><b>'.($TOT_CPL23).'</b></th>
								<th align="right" width="35px" ><b>'.($TOT_CPL10 - $TOT_CPL23).'</b></th>
								<th align="right" width="35px" ><b>'.($TOT_CPL11 - $TOT_CPL23).'</b></th>
								<th align="right" width="35px" ><b>'.round($per_26).'</b></th>
								<th align="right" width="35px" '.$style_27.' ><b>'.round($per_27).'</b></th>
								<th align="right" width="35px" ><b>'.round($per_28).'</b></th>
								<th align="right" width="35px" '.$style_29.' ><b>'.round($per_29).'</b></th>
								<th align="right" width="35px" '.$style_30.' ><b>'.round($per_30).'</b></th>
							</tr>';
					
					$html .= '</tbody>
				</table>
				<br /><br />';
				
				$html .= '<table width="100%" border="0" cellspacing="0" cellpadding="2">';
				for($i = 0 ; $i < $index ; $i++){
					if($ERROR_1_ARR[$i] != '') {
						$html .= '<tr >
									<td width="100%" >'.htmlspecialchars($ERROR_1_ARR[$i]).'</td>
								</tr>';
					}
					
					if($ERROR_2_ARR[$i] != '') {
						$html .= '<tr >
									<td width="100%" >'.htmlspecialchars($ERROR_2_ARR[$i]).'</td>
								</tr>';
					}
					
					if($ERROR_3_ARR[$i] != '') {
						$html .= '<tr >
									<td width="100%" >'.htmlspecialchars($ERROR_3_ARR[$i]).'</td>
								</tr>';
					}
					
					if($ERROR_4_ARR[$i] != '') {
						$html .= '<tr >
									<td width="100%" >'.htmlspecialchars($ERROR_4_ARR[$i]).'</td>
								</tr>';
					}
					
					if($ERROR_5_ARR[$i] != '') {
						$html .= '<tr >
									<td width="100%" >'.htmlspecialchars($ERROR_5_ARR[$i]).'</td>
								</tr>';
					}
				}
				$html .= '</table>';
					
				/*if($TOT_CPL10 != ($TOT_CPL13 + $TOT_CPL15 + $TOT_CPL16 + $TOT_CPL19 + $TOT_CPL20 + $TOT_CPL21) ) {
					$html .= '<table width="100%" border="0" cellspacing="0" cellpadding="2">
								<tr >
									<td width="100%" >
										10 = 13 + 15 + 16 + 19 + 20 + 21
									</td>
								</tr>
							</table><br /><br />';
				}
				
				if($TOT_CPL11 != ($TOT_CPL14 + $TOT_CPL15 + $TOT_CPL16 + $TOT_CPL19 + $TOT_CPL20 + $TOT_CPL21) ) {
					$html .= '<table width="100%" border="0" cellspacing="0" cellpadding="2">
								<tr >
									<td width="100%" >
										11 = 14 + 15 + 16 + 19 + 20 + 21
									</td>
								</tr>
							</table><br /><br />';
				}
				
				if($TOT_CPL18 > $TOT_CPL17) {
					$html .= '<table width="100%" border="0" cellspacing="0" cellpadding="2">
								<tr >
									<td width="100%" >
										18 <= 17
									</td>
								</tr>
							</table><br /><br />';
				}
				
				if($TOT_CPL23 > $TOT_CPL10) {
					$html .= '<table width="100%" border="0" cellspacing="0" cellpadding="2">
								<tr >
									<td width="100%" >
										23 <= 10
									</td>
								</tr>
							</table><br /><br />';
				}
				
				if($TOT_CPL7 != ($TOT_CPL8 + $TOT_CPL11 + $TOT_CPL22)) {
					$html .= '<table width="100%" border="0" cellspacing="0" cellpadding="2">
								<tr >
									<td width="100" >
										7 = 8 + 11 + 22
									</td>
								</tr>
							</table><br /><br />';
				}*/
						
				

		// output the HTML content
		$pdf->writeHTML($html, true, false, true, false, '');

		// array with names of columns
		$arr_nomes = array(
			array("5. Beginning Enrollment", 8, 17),
			array("6. New Enrollees", 8, 21),
			array("7. Cumulative Enrollment", 8, 16),
			array("8. Students Still Enrolled", 8, 16),
			array("9. Non Graduate Completers", 8, 13),
			array("10. Graduate Completers", 8, 15),
			array("11. Total Completers", 8, 18),
			array("12. Non Graduate Completers Employed in Position", 8, 1),
			array("13. Graduate Completers Employed in Positions", 8, 3),
			array("14. Total Completers Employed in Positions", 8, 5),
			array("15. Graduate Completers Employed in Positions", 8, 2),
			array("16. Graduate Completers Waiting to Take", 8, 6),
			array("17. Graduate Completers Who Took Licensure", 8, 3),
			array("18. Graduate Completers Who Passed Licensure", 8, 2),
			array("19. Graduate Completers Unavailable for", 8, 6),
			array("20. Graduate Completers Who Refused", 8, 8),
			array("21. Graduate Completers Seeking", 8, 11),
			array("22. Withdrawals", 8, 20),
			array("23. Sum of 16, 19 and 20", 8, 15),
			array("24. Difference of Row 10 minus Row 23", 8, 8),
			array("25. Difference of Row 11 minus Row 23", 8, 8),
			array("26. Graduation Rate (%)", 8, 16),
			array("27. Total Completion Rate (%)", 8, 14),
			array("28. Graduate Placement Rate (%)", 8, 13),
			array("29. Total Placement Rate (%)", 8, 14),
			array("30. Licensure Exam Pass Rate (%)", 8, 11),
		);

		// num of pages
		$ttPages = $pdf->getNumPages();
		for($i=1; $i<=$ttPages; $i++) {
			// set page
			$pdf->setPage($i);
			// all columns of current page
			foreach( $arr_nomes as $num => $arrCols ) {
				$x = $pdf->xywalter[$num][0] + $arrCols[1]; // new X
				$y = $pdf->xywalter[$num][1] + $arrCols[2]; // new Y
				$n = $arrCols[0]; // column name
				// transforme Rotate
				$pdf->StartTransform();
				// Rotate 90 degrees counter-clockwise
				$pdf->Rotate(270, $x, $y);
				$pdf->Text($x, $y, $n);
				
				if($num == 7) {
					$pdf->Text($x+12, $y+4, 'Related to Field of Instruction');
				} else if($num == 8) {
					$pdf->Text($x+11, $y+4, 'Related to Field of Instruction');
				} else if($num == 9) {
					$pdf->Text($x+10, $y+4, 'Related to Field of Instruction');
				} else if($num == 10) {
					$pdf->Text($x+10, $y+4, 'Unrelated to Field of Instruction');
				} else if($num == 11) {
					$pdf->Text($x+16, $y+4, 'Licensure Exam');
				} else if($num == 12) {
					$pdf->Text($x+24, $y+4, 'Exam');
				} else if($num == 13) {
					$pdf->Text($x+24, $y+4, 'Exam');
				} else if($num == 14) {
					$pdf->Text($x+18, $y+4, 'Employment');
				} else if($num == 15) {
					$pdf->Text($x+16, $y+4, 'Employment');
				} else if($num == 16) {
					$pdf->Text($x+4, $y+4, 'Employment/Status Unknown');
				}
				
				
				
				// Stop Transformation
				$pdf->StopTransform();
			}
		}

		// reset pointer to the last page
		$pdf->lastPage();

		$file_name = 'COE CPL Report'.'.pdf';
		/*if($browser == 'Safari')
			$pdf->Output('temp/'.$file_name, 'FD');
		else	
			$pdf->Output($file_name, 'I');*/
			
		$pdf->Output('temp/'.$file_name, 'FD');
		return $file_name;	
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
		$file_name 		= 'COE CPL Report.xlsx';
		$outputFileName = $dir.$file_name; 
		$outputFileName = str_replace(
		pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName);  

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
		$heading[] = 'Student';
		$width[]   = 20;
		$heading[] = 'CPLExclude';
		$width[]   = 20;
		$heading[] = 'ProgramCode';
		$width[]   = 20;
		$heading[] = 'ProgramGroup';
		$width[]   = 20;
		$heading[] = 'StudentStatus';
		$width[]   = 20;
		$heading[] = 'EndDateType';
		$width[]   = 20;
		$heading[] = 'StartDate';
		$width[]   = 20;
		$heading[] = 'LDA';
		$width[]   = 20;
		$heading[] = 'Determination';
		$width[]   = 20;
		$heading[] = 'Drop';
		$width[]   = 20;
		$heading[] = 'Graduation';
		$width[]   = 20;
		$heading[] = 'PlacementStatus';
		$width[]   = 20;
		$heading[] = 'PlacementStatusCategory';
		$width[]   = 20;
		$heading[] = 'CPL05';
		$width[]   = 20;
		$heading[] = 'CPL06';
		$width[]   = 20;
		$heading[] = 'CPL07';
		$width[]   = 20;
		$heading[] = 'CPL08';
		$width[]   = 20;
		$heading[] = 'CPL09';
		$width[]   = 20;
		$heading[] = 'CPL10';
		$width[]   = 20;
		$heading[] = 'CPL11';
		$width[]   = 20;
		$heading[] = 'CPL12';
		$width[]   = 20;
		$heading[] = 'CPL13';
		$width[]   = 20;
		$heading[] = 'CPL14';
		$width[]   = 20;
		$heading[] = 'CPL15';
		$width[]   = 20;
		$heading[] = 'CPL16';
		$width[]   = 20;
		$heading[] = 'CPL17';
		$width[]   = 20;
		$heading[] = 'CPL18';
		$width[]   = 20;
		$heading[] = 'CPL19';
		$width[]   = 20;
		$heading[] = 'CPL20';
		$width[]   = 20;
		$heading[] = 'CPL21';
		$width[]   = 20;
		$heading[] = 'CPL22';
		$width[]   = 20;
		$heading[] = 'CPL23';
		$width[]   = 20;
		$heading[] = 'CPL24';
		$width[]   = 20;
		$heading[] = 'CPL25';
		$width[]   = 20;
		$heading[] = 'CPL26';
		$width[]   = 20;
		$heading[] = 'CPL27';
		$width[]   = 20;
		$heading[] = 'CPL28';
		$width[]   = 20;
		$heading[] = 'CPL29';
		$width[]   = 20;
		$heading[] = 'CPL30';
		$width[]   = 20;
		$heading[] = 'Created';
		$width[]   = 20;
		$heading[] = 'Error1';
		$width[]   = 20;
		$heading[] = 'Error2';
		$width[]   = 20;
		$heading[] = 'Error3';
		$width[]   = 20;
		$heading[] = 'Error4';
		$width[]   = 20;
		$heading[] = 'Error5';
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
		$res = $db->Execute("CALL `COMP10001`(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".$ST."','".$ET."', ".$GROUP_BY_PROGRAM_CODE.", TRUE)");
		while (!$res->EOF) {
			$line++;
			$index = -1;
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Student']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CPLExclude']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ProgramCode']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ProgramGroup']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['StudentStatus']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EndDateType']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			if($res->fields['StartDate'] != '0000-00-00') {
				$dateValue = floor(PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res->fields['StartDate'])));
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
			}
			
			$index++;
			$cell_no = $cell[$index].$line;
			if($res->fields['LDA'] != '0000-00-00') {
				$dateValue = floor(PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res->fields['LDA'])));
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
			}
			
			$index++;
			$cell_no = $cell[$index].$line;
			if($res->fields['DeterminationDate'] != '0000-00-00') {
				$dateValue = floor(PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res->fields['DeterminationDate'])));
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
			}
			
			$index++;
			$cell_no = $cell[$index].$line;
			if($res->fields['DropDate'] != '0000-00-00') {
				$dateValue = floor(PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res->fields['DropDate'])));
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
			}
			
			$index++;
			$cell_no = $cell[$index].$line;
			if($res->fields['GradDate'] != '0000-00-00') {
				$dateValue = floor(PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res->fields['GradDate'])));
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
			}
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PlacementStatus']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PlacementStatusCategory']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CPL05']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CPL06']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CPL07']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CPL08']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CPL09']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CPL10']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CPL11']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CPL12']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CPL13']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CPL14']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CPL15']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CPL16']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CPL17']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CPL18']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CPL19']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CPL20']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CPL21']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CPL22']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CPL23']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CPL24']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CPL25']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CPL26']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CPL27']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CPL28']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CPL29']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CPL30']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(date("Y-m-d h:i a"));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Error1']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Error2']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Error3']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Error4']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Error5']);

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
	<title><?=MNU_CPL_REPORT ?> | <?=$title?></title>
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
							<?=MNU_CPL_REPORT ?>
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
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" onchange="doSearch()">
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										<div class="col-md-2 ">
											<?=START_DATE?>
											<input type="text" class="form-control date" id="START_DATE" name="START_DATE" value="" >
										</div>
										<div class="col-md-2">
											<?=END_DATE?>
											<input type="text" class="form-control date" id="END_DATE" name="END_DATE" value="" >
										</div>
										<div class="col-md-2">
											<br />
											<div class="col-12 col-sm-12 custom-control custom-checkbox form-group" >
												<input type="checkbox" class="custom-control-input" id="GROUP_BY_PROGRAM_CODE" name="GROUP_BY_PROGRAM_CODE" value="1"  >
												<label class="custom-control-label" for="GROUP_BY_PROGRAM_CODE"><?=GROUP_BY_PROGRAM_CODE?></label>
											</div>
										</div>
										<div class="col-md-2" style="padding: 0;" >
											<br />
											<button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info"><?=RUN?></button>
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
	
	function submit_form(val){
		document.getElementById('FORMAT').value = val
		document.form1.submit();
	}
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
</body>

</html>
