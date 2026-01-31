<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/title_iv.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_ACCOUNTING') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){

	$timezone = $_SESSION['PK_TIMEZONE'];
	if($timezone == '' || $timezone == 0) {
		$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$timezone = $res->fields['PK_TIMEZONE'];
		if($timezone == '' || $timezone == 0)
			$timezone = 4;
	}
	
	$res_tz = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");

	$upost_batch_ids= $_POST['PK_BATCH_UNPOSTED_HISTORY'];
	$payment_cond=array();
	$misc_cond=array();
	$tuition_cond=array();
	$batch_types = array();
	foreach ($upost_batch_ids as $key => $value) {
		# code...
		$type=$_POST['PK_BATCH_TYPE_'.$value];
		if($type=="Payment"){

			$payment_cond[]=$value;
			$batch_types[]=2;
		}

		if($type=="Miscellaneous"){
			$misc_cond[]=$value;
			$batch_types[]=1;
		}

		if($type=="Tuition"){
			$tuition_cond[]=$value;
			$batch_types[]=3;
		}
	}



	// header campus

	




	$batch_type=array_unique($batch_types);
	//echo $cond;exit;

	$where_payment="";
	if(!empty($payment_cond)){
		$where_payment = " AND PK_PAYMENT_BATCH_UNPOSTED_HISTORY IN(".implode(',',$payment_cond).")";
	}


	$where_misc="";
	if(!empty($misc_cond)){
		$where_misc = " AND PK_MISC_BATCH_UNPOSTED_HISTORY IN(".implode(',',$misc_cond).")";
	}

	$where_tuition="";
	if(!empty($tuition_cond)){
		$where_tuition = " AND PK_TUITION_BATCH_UNPOSTED_HISTORY IN(".implode(',',$tuition_cond).")";
	}

	// PAYMENT BATCH
	$query_payment = "select CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, BATCH_NO, UNPOSTED_ON ,S_PAYMENT_BATCH_MASTER.BATCH_PK_CAMPUS
	FROM S_PAYMENT_BATCH_UNPOSTED_HISTORY 
	LEFT JOIN Z_USER ON Z_USER.PK_USER = S_PAYMENT_BATCH_UNPOSTED_HISTORY.UNPOSTED_BY 
	LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID 
	, S_PAYMENT_BATCH_MASTER 
	WHERE 
	S_PAYMENT_BATCH_MASTER.ACTIVE = 1  AND 
	S_PAYMENT_BATCH_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	S_PAYMENT_BATCH_MASTER.PK_PAYMENT_BATCH_MASTER = S_PAYMENT_BATCH_UNPOSTED_HISTORY.PK_PAYMENT_BATCH_MASTER $where_payment ";
	//exit;
	
	// MISC BATCH
	$query_misc = "select CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, BATCH_NO, UNPOSTED_ON , S_MISC_BATCH_MASTER.MISC_BATCH_PK_CAMPUS
	FROM S_MISC_BATCH_UNPOSTED_HISTORY 
	LEFT JOIN Z_USER ON Z_USER.PK_USER = S_MISC_BATCH_UNPOSTED_HISTORY.UNPOSTED_BY 
	LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID
	, S_MISC_BATCH_MASTER 
	WHERE 
	S_MISC_BATCH_MASTER.ACTIVE = 1  AND 
	S_MISC_BATCH_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	S_MISC_BATCH_MASTER.PK_MISC_BATCH_MASTER = S_MISC_BATCH_UNPOSTED_HISTORY.PK_MISC_BATCH_MASTER $where_misc ";
	//exit;

	$advanced_query_misc = "SELECT
	PK_BATCH_HISTORY,
	S_BATCH_HISTORY_ADVANCE_LOGINING.PK_MISC_BATCH_MASTER,
	ID,
	CHANGED_ON,
	S_MISC_BATCH_MASTER.BATCH_NO,
	GROUP_CONCAT(DISTINCT CONCAT(FIELD_NAME, '---', OLD_VALUE, '<-->', NEW_VALUE) ORDER BY FIELD_NAME SEPARATOR '|||') AS changes
  FROM
	S_BATCH_HISTORY_ADVANCE_LOGINING 
	LEFT JOIN S_MISC_BATCH_MASTER ON S_MISC_BATCH_MASTER.PK_MISC_BATCH_MASTER=S_BATCH_HISTORY_ADVANCE_LOGINING.PK_MISC_BATCH_MASTER
	WHERE  
	S_BATCH_HISTORY_ADVANCE_LOGINING.PK_MISC_BATCH_MASTER=161313 AND
	S_MISC_BATCH_MASTER.ACTIVE = 1  AND S_MISC_BATCH_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  
	GROUP BY CHANGED_ON,ID;";

	// TUITION BATCH
	$query_tuition = "select CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, BATCH_NO, UNPOSTED_ON ,S_TUITION_BATCH_MASTER.TUITION_BATCH_PK_CAMPUS 
	FROM  S_TUITION_BATCH_UNPOSTED_HISTORY 
	LEFT JOIN Z_USER ON Z_USER.PK_USER = S_TUITION_BATCH_UNPOSTED_HISTORY.UNPOSTED_BY 
	LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID
	, S_TUITION_BATCH_MASTER
	WHERE 
	S_TUITION_BATCH_MASTER.ACTIVE = 1  AND 
	S_TUITION_BATCH_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	S_TUITION_BATCH_MASTER.PK_TUITION_BATCH_MASTER = S_TUITION_BATCH_UNPOSTED_HISTORY.PK_TUITION_BATCH_MASTER $where_tuition ";

	
	if($_POST['FORMAT'] == 1){
		/////////////////////////////////////////////////////////////////
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
				global $db;
				
				$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
				
				if($res->fields['PDF_LOGO'] != '') {
					$ext = explode(".",$res->fields['PDF_LOGO']);
					$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
				}
				
				$this->SetFont('helvetica', '', 15);
				$this->SetY(8);
				$this->SetX(55);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
				
				$this->SetFont('helvetica', 'I', 20);
				$this->SetY(8);
				$this->SetX(146);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, "Unposted Batches", 0, false, 'L', 0, '', 0, false, 'M', 'L');
				
				
				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(120, 13, 202, 13, $style);
				
				if($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '')
					$str = "Between: ".$_POST['START_DATE'].' - '.$_POST['END_DATE'];
				else if($_POST['START_DATE'] != '')
					$str = "From ".$_POST['START_DATE'];
				else if($_POST['END_DATE'] != '')
					$str = "As of Date: ".$_POST['END_DATE'];

				$batches= $_POST['BATCH_TYPES'];
	
				$arrayName = array();
				if(in_array('1',$batches)){
					$arrayName[]="Miscellaneous";
					
				}
				if(in_array('2',$batches)){
					$arrayName[]="Payment";
				}

				if(in_array('3',$batches)){
					$arrayName[]="Tuition";
				}

				$batch_name = implode(', ',$arrayName);	
				$batch_name = "Batch Type(s): ".$batch_name;

				$header_campus="";
				if(!empty($_REQUEST['PK_CAMPUS'])){
					$rs_camp = mysql_query("SELECT GROUP_CONCAT(CAMPUS_CODE) AS CODE  from S_CAMPUS WHERE PK_CAMPUS IN (".implode(',',$_REQUEST['PK_CAMPUS']).") ORDER BY CAMPUS_CODE ASC ");	
					while($row_camp = mysql_fetch_array($rs_camp)){
							$header_campus = $row_camp['CODE'];
					}
					$campus= "Campus: ".$header_campus;
			}
				

				$this->SetFont('helvetica', 'I', 10);
				$this->SetY(16);
				$this->SetX(100);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(102, 5, $campus, 0, false, 'R', 0, '', 0, false, 'M', 'L');

				$this->SetFont('helvetica', 'I', 10);
				$this->SetY(20);
				$this->SetX(100);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(102, 5, $batch_name, 0, false, 'R', 0, '', 0, false, 'M', 'L');

				$this->SetFont('helvetica', 'I', 10);
				$this->SetY(24);
				$this->SetX(100);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');

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
		$pdf->SetMargins(7, 35, 7);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, 30);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->setLanguageArray($l);
		$pdf->setFontSubsetting(true);
		$pdf->SetFont('helvetica', '', 7, '', true);
		$pdf->AddPage();

		$total 	= 0;

		$txt 	= '';

		if(in_array('1',$batch_type))
		{

			
				
			$txt .= '
			<b style="font-size:40px" >Miscellaneous Batch</b>
			<br><br>
			<table border="1" cellspacing="0" cellpadding="3" width="100%">
						<thead> 
						<tr>
						<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Campus</b></td>
						<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Batch Number</b></td>
						<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Unposted On</b></td>
						<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Unposted By</b></td>
					</tr>
						</thead>';
			// $txt .= '<tr>
			// 			<td width="60%" ><b style="font-size:40px" >Miscellaneous Batch</b></td>
			// 		</tr>';
				$res_misc = $db->Execute($query_misc);
				$sno = 0;
				if($res_misc->RecordCount() == 0) {
					$txt 	.= '<tr>
								<td width="60%" >No Data</td>
							</tr>';
				} else {
					while (!$res_misc->EOF) {

						$CAMPUS = '';
						if($res_misc->fields['MISC_BATCH_PK_CAMPUS'] != '') {
							$misc_campus = $res_misc->fields['MISC_BATCH_PK_CAMPUS'];

							$rs_camp = mysql_query("SELECT CAMPUS_CODE from S_CAMPUS WHERE PK_CAMPUS IN ($misc_campus) ORDER BY CAMPUS_CODE ASC ");	
							while($row_camp = mysql_fetch_array($rs_camp)){
								if($CAMPUS != '')
									$CAMPUS .= ', ';
								$CAMPUS .= $row_camp['CAMPUS_CODE'];
							}						
						}

						$UNPOSTED_ON = convert_to_user_date($res_misc->fields['UNPOSTED_ON'],'m/d/Y h:i A',$res_tz->fields['TIMEZONE'],date_default_timezone_get());
						$txt 	.= '<tr>
										<td width="20%" >'.$CAMPUS.'</td>
										<td width="20%" >'.$res_misc->fields['BATCH_NO'].'</td>
										<td width="20%" >'.$UNPOSTED_ON.'</td>
										<td width="20%" >'.$res_misc->fields['NAME'].'</td>
									</tr>';
						
						$res_misc->MoveNext();
					}
				}
				$txt 	.= '</table>';
		}

	// payment batch
	if(in_array('2',$batch_type))
	{
	
		$txt .= '
		<br>
		<br>
		<b style="font-size:40px" >Payment Batch</b>
		<br><br>
		<table border="1" cellspacing="0" cellpadding="3" width="100%">
					<thead>
					<tr>
						<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Campus</b></td>
						<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Batch Number</b></td>
						<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Unposted On</b></td>
						<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Unposted By</b></td>
					</tr>
					</thead>';
			

			$res_payment = $db->Execute($query_payment);
			$sno = 0;
			if($res_payment->RecordCount() == 0) {
				$txt 	.= '<tr>
							<td width="60%" >No Data</td>
						</tr>';
			} else {
				while (!$res_payment->EOF) {
					$CAMPUS = '';
					
						if($res_payment->fields['BATCH_PK_CAMPUS'] != '') {
							$batch_campus = $res_payment->fields['BATCH_PK_CAMPUS'];
							$rs_camp = mysql_query("SELECT CAMPUS_CODE from S_CAMPUS WHERE PK_CAMPUS IN ($batch_campus) ORDER BY CAMPUS_CODE ASC ");	
							while($row_camp = mysql_fetch_array($rs_camp)){
								if($CAMPUS != '')
									$CAMPUS .= ', ';
								$CAMPUS .= $row_camp['CAMPUS_CODE'];
							}						
					}
					$UNPOSTED_ON = convert_to_user_date($res_payment->fields['UNPOSTED_ON'],'m/d/Y h:i A',$res_tz->fields['TIMEZONE'],date_default_timezone_get());
					$txt 	.= '<tr>
									<td width="20%" >'.$CAMPUS.'</td>
									<td width="20%" >'.$res_payment->fields['BATCH_NO'].'</td>
									<td width="20%" >'.$UNPOSTED_ON.'</td>
									<td width="20%" >'.$res_payment->fields['NAME'].'</td>
								</tr>';
					
					$res_payment->MoveNext();
				}
			}

			$txt 	.= '</table>';

	}
		//Tuition Batch
		if(in_array('3',$batch_type))
		{
			$txt .= '
			<br>
			<br>
			<b style="font-size:40px" >Tuition Batch</b>
			<br><br>
			<table border="1" cellspacing="0" cellpadding="3" width="100%">
						<thead>
							<tr>
								<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Campus</b></td>
								<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Batch Number</b></td>
								<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Unposted On</b></td>
								<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Unposted By</b></td>
							</tr>
						</thead>';

				$res_tuition = $db->Execute($query_tuition);
				$sno = 0;
				if($res_tuition->RecordCount() == 0) {
					$txt 	.= '<tr>
								<td width="60%" >No Data</td>
							</tr>';
				} else {
					while (!$res_tuition->EOF) {
						$CAMPUS = '';
						if($res_tuition->fields['TUITION_BATCH_PK_CAMPUS'] != '') {
							$tuition_campus = $res_tuition->fields['TUITION_BATCH_PK_CAMPUS'];
							$rs_camp = mysql_query("SELECT CAMPUS_CODE from S_CAMPUS WHERE PK_CAMPUS IN ($tuition_campus) ORDER BY CAMPUS_CODE ASC ");	
							while($row_camp = mysql_fetch_array($rs_camp)){
								if($CAMPUS != '')
									$CAMPUS .= ', ';
								$CAMPUS .= $row_camp['CAMPUS_CODE'];
							}						
					}
						$UNPOSTED_ON = convert_to_user_date($res_tuition->fields['UNPOSTED_ON'],'m/d/Y h:i A',$res_tz->fields['TIMEZONE'],date_default_timezone_get());
						$txt 	.= '<tr>
										<td width="20%" >'.$CAMPUS.'</td>
										<td width="20%" >'.$res_tuition->fields['BATCH_NO'].'</td>
										<td width="20%" >'.$UNPOSTED_ON.'</td>
										<td width="20%" >'.$res_tuition->fields['NAME'].'</td>
									</tr>';						
						$res_tuition->MoveNext();
					}
				}

				$txt 	.= '</table>';
		
		}


		

		$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

		$file_name = 'Unposted Batches'.date("Y-m-d-H-i-s").'.pdf';
		/*if($browser == 'Safari')
			$pdf->Output('temp/'.$file_name, 'FD');
		else	
			$pdf->Output($file_name, 'I');*/
			
		$pdf->Output('temp/'.$file_name, 'FD');
		return $file_name;	
		/////////////////////////////////////////////////////////////////
	} else if($_POST['FORMAT'] == 2){

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
		$file_name 		= 'Unposted Batches.xlsx';
		$outputFileName = $dir.$file_name; 
		$outputFileName = str_replace(
		pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
		$outputFileName );  

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

		$line 	= 1;	
		$index 	= -1;
		$heading[] = 'Campus';
		$width[]   = 20;
		$heading[] = 'Batch Type';
		$width[]   = 20;
		$heading[] = 'Batch';
		$width[]   = 20;
		$heading[] = 'Unposted On';
		$width[]   = 20;
		$heading[] = 'Unposted By';
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
		
		if(in_array('2',$batch_type))
		{
				$res_payment = $db->Execute($query_payment);
				while (!$res_payment->EOF) {
				
					$line++;
					$index = -1;
					
					$UNPOSTED_ON = convert_to_user_date($res_payment->fields['UNPOSTED_ON'],'m/d/Y  h:i A',$res_tz->fields['TIMEZONE'],date_default_timezone_get());

					$CAMPUS = '';
					
						if($res_payment->fields['BATCH_PK_CAMPUS'] != '') {
							$batch_campus = $res_payment->fields['BATCH_PK_CAMPUS'];
							$rs_camp = mysql_query("SELECT CAMPUS_CODE from S_CAMPUS WHERE PK_CAMPUS IN ($batch_campus) ORDER BY CAMPUS_CODE ASC ");	
							while($row_camp = mysql_fetch_array($rs_camp)){
								if($CAMPUS != '')
									$CAMPUS .= ', ';
								$CAMPUS .= $row_camp['CAMPUS_CODE'];
							}						
					}
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($CAMPUS);

					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('Payment Batch');

					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_payment->fields['BATCH_NO']);
				
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($UNPOSTED_ON);
					
				
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_payment->fields['NAME']);
					
					$res_payment->MoveNext();
				}
			}
		

		if(in_array('1',$batch_type))
		{

	
			$res_misc = $db->Execute($query_misc);
			while (!$res_misc->EOF) {

			
				$line++;
				$index = -1;
				
				$UNPOSTED_ON = convert_to_user_date($res_misc->fields['UNPOSTED_ON'],'m/d/Y h:i A',$res_tz->fields['TIMEZONE'],date_default_timezone_get());
				
				$CAMPUS = '';
				if($res_misc->fields['MISC_BATCH_PK_CAMPUS'] != '') {
					$misc_campus = $res_misc->fields['MISC_BATCH_PK_CAMPUS'];

					$rs_camp = mysql_query("SELECT CAMPUS_CODE from S_CAMPUS WHERE PK_CAMPUS IN ($misc_campus) ORDER BY CAMPUS_CODE ASC ");	
					while($row_camp = mysql_fetch_array($rs_camp)){
						if($CAMPUS != '')
							$CAMPUS .= ', ';
						$CAMPUS .= $row_camp['CAMPUS_CODE'];
					}						
				}
				


				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($CAMPUS);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('Miscellaneous Batch');

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_misc->fields['BATCH_NO']);
			
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($UNPOSTED_ON);
				
			
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_misc->fields['NAME']);
				
				$res_misc->MoveNext();
			}
		}

		if(in_array('3',$batch_type))
		{
		
			$res_tuition = $db->Execute($query_tuition);
			while (!$res_tuition->EOF) {
			
				$line++;
				$index = -1;
				
				$UNPOSTED_ON = convert_to_user_date($res_tuition->fields['UNPOSTED_ON'],'m/d/Y h:i A',$res_tz->fields['TIMEZONE'],date_default_timezone_get());

				
				$CAMPUS = '';
				if($res_tuition->fields['TUITION_BATCH_PK_CAMPUS'] != '') {
					$tuition_campus = $res_tuition->fields['TUITION_BATCH_PK_CAMPUS'];
					$rs_camp = mysql_query("SELECT CAMPUS_CODE from S_CAMPUS WHERE PK_CAMPUS IN ($tuition_campus) ORDER BY CAMPUS_CODE ASC ");	
					while($row_camp = mysql_fetch_array($rs_camp)){
						if($CAMPUS != '')
							$CAMPUS .= ', ';
						$CAMPUS .= $row_camp['CAMPUS_CODE'];
					}						
				}

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($CAMPUS);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('Tuition Batch');

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_tuition->fields['BATCH_NO']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($UNPOSTED_ON);
				
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_tuition->fields['NAME']);
				
				$res_tuition->MoveNext();
			}
		}
		
		$objWriter->save($outputFileName);
		$objPHPExcel->disconnectWorksheets();
		header("location:".$outputFileName);
	}else if($_POST['FORMAT'] == 3){
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
		$file_name 		= 'Advance_logging.xlsx';
		$outputFileName = $dir.$file_name; 
		$outputFileName = str_replace(
		pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
		$outputFileName );  

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

		$line 	= 1;	
		$index 	= -1;
		
		$heading[] = "BATCH_NO";
		$width[]   = 20;

		$heading[] = "BATCH_STATUS";
		$width[]   = 20;
	
		$heading[] = "BATCH_CAMPUS";
		$width[]   = 20;

		$heading[] = "BATCH_DATE";
		$width[]   = 20;

		$heading[] = "POSTED_DATE";
		$width[]   = 20;

		$heading[] = "BATCH_DESCRIPTION";
		$width[]   = 20;

		$heading[] = "DEBIT_TOTAL";
		$width[]   = 20;

		$heading[] = "CREDIT_TOTAL";
		$width[]   = 20;
		
		$heading[] = "BATCH_COMMENTS";
		$width[]   = 20;

		$heading[] = "PK_STUDENT_MASTER";
		$width[]   = 20;

		$heading[] = "LEDGER_CODE";
		$width[]   = 20;

		$heading[] = "TRANS_DATE";
		$width[]   = 20;
		
		$heading[] = "DEBIT_AMOINT";
		$width[]   = 20;

		$heading[] = "CREDIT_AMOUNT";
		$width[]   = 20;

		$heading[] = "FEE_PAYMENT_TYPE";
		$width[]   = 20;

		$heading[] = "AY";
		$width[]   = 20;
		
		$heading[] = "AP";
		$width[]   = 20;

		$heading[] = "RECEIPT_NO";
		$width[]   = 20;

		$heading[] = "PK_STUDENT_ENROLLMENT";
		$width[]   = 20;

		$heading[] = "PK_TERM_BLOCK";
		$width[]   = 20;

		$heading[] = "PRIOR_YEAR";
		$width[]   = 20;

		$i = 0;

		foreach($heading as $title) {
			$index++;
			$cell_no = $cell[$index].$line;
			$title = str_replace('_',' ',$title);
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);			
		}

		$objPHPExcel->getActiveSheet()->freezePane('A1');

		if(in_array('1',$batch_type))
		{
			$res_misc = $db->Execute($advanced_query_misc);

			while (!$res_misc->EOF) 
			{
					$batch_array=explode("|||",$res_misc->fields['changes']);
					$change_array=array();

					foreach($batch_array as $val){
						$new=explode('---', $val);
						$change_array[$new[0]] = $new[1];
					}

					$line++;
					$index = -1;

					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_misc->fields['BATCH_NO']);
								
	

					if(array_key_exists('BATCH_STATUS',$change_array)) {

						$batch_status = explode('<-->',$change_array['BATCH_STATUS']);
						$str_batch_status="";
						$old_val=($batch_status[0]?$batch_status[0]:"");
						$new_val=($batch_status[1]?$batch_status[1]:"");
						$str_batch_status="Old Value: ".$old_val." - New Value:".$new_val;
						$index++;
						$cell_no = $cell[$index].$line;
			
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($str_batch_status);				
					}else{
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('');
					}

					if(array_key_exists('BATCH_CAMPUS',$change_array)) {

						$batch_status = explode('<-->',$change_array['BATCH_CAMPUS']);
						$str_batch_status="";
						$old_val=($batch_status[0]?$batch_status[0]:"");
						$new_val=($batch_status[1]?$batch_status[1]:"");
						$str_batch_status="Old Value: ".$old_val."- New Value:".$new_val;
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($str_batch_status);				
					}else{
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('');
					}

					if(array_key_exists('BATCH_DATE',$change_array)) {

						$batch_status = explode('<-->',$change_array['BATCH_DATE']);
						$str_batch_status="";
						$old_val=($batch_status[0]?$batch_status[0]:"");
						$new_val=($batch_status[1]?$batch_status[1]:"");
						$str_batch_status="Old Value: ".$old_val."- New Value:".$new_val;
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($str_batch_status);				
					}else{
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('');
					}
				
					if(array_key_exists('POSTED_DATE',$change_array)) {

						$batch_status = explode('<-->',$change_array['POSTED_DATE']);
						$str_batch_status="";
						$old_val=($batch_status[0]?$batch_status[0]:"");
						$new_val=($batch_status[1]?$batch_status[1]:"");
						$str_batch_status="Old Value: ".$old_val."- New Value:".$new_val;
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($str_batch_status);				
					}else{
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('');
					}

					if(array_key_exists('BATCH_DESCRIPTION',$change_array)) {

						$batch_status = explode('<-->',$change_array['BATCH_DESCRIPTION']);
						$str_batch_status="";
						$old_val=($batch_status[0]?$batch_status[0]:"");
						$new_val=($batch_status[1]?$batch_status[1]:"");
						$str_batch_status="Old Value: ".$old_val."- New Value:".$new_val;
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($str_batch_status);				
					}else{
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('');
					}

					if(array_key_exists('DEBIT_TOTAL',$change_array)) {

						$batch_status = explode('<-->',$change_array['DEBIT_TOTAL']);
						$str_batch_status="";
						$old_val=($batch_status[0]?$batch_status[0]:"");
						$new_val=($batch_status[1]?$batch_status[1]:"");
						$str_batch_status="Old Value: ".$old_val."- New Value:".$new_val;
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($str_batch_status);				
					}else{
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('');
					}

					if(array_key_exists('CREDIT_TOTAL',$change_array)) {

						$batch_status = explode('<-->',$change_array['CREDIT_TOTAL']);
						$str_batch_status="";
						$old_val=($batch_status[0]?$batch_status[0]:"");
						$new_val=($batch_status[1]?$batch_status[1]:"");
						$str_batch_status="Old Value: ".$old_val."- New Value:".$new_val;
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($str_batch_status);				
					}else{
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('');
					}

					if(array_key_exists('BATCH_COMMENTS',$change_array)) {

						$batch_status = explode('<-->',$change_array['BATCH_COMMENTS']);
						$str_batch_status="";
						$old_val=($batch_status[0]?$batch_status[0]:"");
						$new_val=($batch_status[1]?$batch_status[1]:"");
						$str_batch_status="Old Value: ".$old_val."- New Value:".$new_val;
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($str_batch_status);				
					}else{
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('');
					}

					if(array_key_exists('PK_STUDENT_MASTER',$change_array)) {

						$batch_status = explode('<-->',$change_array['PK_STUDENT_MASTER']);
						$str_batch_status="";
						$old_val=($batch_status[0]?$batch_status[0]:"");
						$new_val=($batch_status[1]?$batch_status[1]:"");
						$str_batch_status="Old Value: ".$old_val."- New Value:".$new_val;
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($str_batch_status);				
					}else{
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('');
					}
				
					if(array_key_exists('LEDGER_CODE',$change_array)) {

						$batch_status = explode('<-->',$change_array['LEDGER_CODE']);
						$str_batch_status="";
						$old_val=($batch_status[0]?$batch_status[0]:"");
						$new_val=($batch_status[1]?$batch_status[1]:"");
						$str_batch_status="Old Value: ".$old_val."- New Value:".$new_val;
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($str_batch_status);				
					}else{
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('');
					}

					if(array_key_exists('TRANS_DATE',$change_array)) {

						$batch_status = explode('<-->',$change_array['TRANS_DATE']);
						$str_batch_status="";
						$old_val=($batch_status[0]?$batch_status[0]:"");
						$new_val=($batch_status[1]?$batch_status[1]:"");
						$str_batch_status="Old Value: ".$old_val."- New Value:".$new_val;
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($str_batch_status);				
					}else{
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('');
					}

					if(array_key_exists('DEBIT_AMOINT',$change_array)) {

						$batch_status = explode('<-->',$change_array['DEBIT_AMOINT']);
						$str_batch_status="";
						$old_val=($batch_status[0]?$batch_status[0]:"");
						$new_val=($batch_status[1]?$batch_status[1]:"");
						$str_batch_status="Old Value: ".$old_val."- New Value:".$new_val;
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($str_batch_status);				
					}else{
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('');
					}

					if(array_key_exists('FEE_PAYMENT_TYPE',$change_array)) {

						$batch_status = explode('<-->',$change_array['FEE_PAYMENT_TYPE']);
						$str_batch_status="";
						$old_val=($batch_status[0]?$batch_status[0]:"");
						$new_val=($batch_status[1]?$batch_status[1]:"");
						$str_batch_status="Old Value: ".$old_val."- New Value:".$new_val;
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($str_batch_status);				
					}else{
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('');
					}

					if(array_key_exists('AY',$change_array)) {

						$batch_status = explode('<-->',$change_array['AY']);
						$str_batch_status="";
						$old_val=($batch_status[0]?$batch_status[0]:"");
						$new_val=($batch_status[1]?$batch_status[1]:"");
						$str_batch_status="Old Value: ".$old_val."- New Value:".$new_val;
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($str_batch_status);				
					}else{
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('');
					}

					if(array_key_exists('AP',$change_array)) {

						$batch_status = explode('<-->',$change_array['AP']);
						$str_batch_status="";
						$old_val=($batch_status[0]?$batch_status[0]:"");
						$new_val=($batch_status[1]?$batch_status[1]:"");
						$str_batch_status="Old Value: ".$old_val."- New Value:".$new_val;
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($str_batch_status);				
					}else{
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('');
					}

					if(array_key_exists('RECEIPT_NO',$change_array)) {

						$batch_status = explode('<-->',$change_array['RECEIPT_NO']);
						$str_batch_status="";
						$old_val=($batch_status[0]?$batch_status[0]:"");
						$new_val=($batch_status[1]?$batch_status[1]:"");
						$str_batch_status="Old Value: ".$old_val."- New Value:".$new_val;
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($str_batch_status);				
					}else{
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('');
					}

					if(array_key_exists('PK_STUDENT_ENROLLMENT',$change_array)) {

						$batch_status = explode('<-->',$change_array['PK_STUDENT_ENROLLMENT']);
						$str_batch_status="";
						$old_val=($batch_status[0]?$batch_status[0]:"");
						$new_val=($batch_status[1]?$batch_status[1]:"");
						$str_batch_status="Old Value: ".$old_val."- New Value:".$new_val;
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($str_batch_status);				
					}else{
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('');
					}

					if(array_key_exists('PK_TERM_BLOCK',$change_array)) {

						$batch_status = explode('<-->',$change_array['PK_TERM_BLOCK']);
						$str_batch_status="";
						$old_val=($batch_status[0]?$batch_status[0]:"");
						$new_val=($batch_status[1]?$batch_status[1]:"");
						$str_batch_status="Old Value: ".$old_val."- New Value:".$new_val;
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($str_batch_status);				
					}else{
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('');
					}

					if(array_key_exists('PRIOR_YEAR',$change_array)) {

						$batch_status = explode('<-->',$change_array['PRIOR_YEAR']);
						$str_batch_status="";
						$old_val=($batch_status[0]?$batch_status[0]:"");
						$new_val=($batch_status[1]?$batch_status[1]:"");
						$str_batch_status="Old Value: ".$old_val."- New Value:".$new_val;
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($str_batch_status);				
					}else{
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('');
					}
					
					$res_misc->MoveNext();

			}
		}
		
		if(in_array('2',$batch_type))
		{
				$res_payment = $db->Execute($query_payment);
				while (!$res_payment->EOF) {
				}
		}

		if(in_array('3',$batch_type))
		{
				$res_payment = $db->Execute($query_payment);
				while (!$res_payment->EOF) {
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
	<title><?=MNU_UNPOSTED_BATCHES ?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		#advice-required-entry-BATCH_TYPES {
			position: absolute;
			top: 57px;
			width: 140px;
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
							<?=MNU_UNPOSTED_BATCHES ?>
						</h4>
                    </div>
                </div>
				
				<form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col-md-2">
										&nbsp;&nbsp;&nbsp;
											<select id="BATCH_TYPES" name="BATCH_TYPES[]" multiple class="form-control required-entry" >
												<option value="1">Miscellanious</option>
												<option value="2">Payment</option>
												<option value="3">Tuition</option>
											</select>
											<div class="clearfix"></div>
										</div>

										<?php
										
										$res_type1 = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");

										?>
										<div class="col-md-2">
											&nbsp;&nbsp;&nbsp;
										<select id="PK_CAMPUS" name="PK_CAMPUS[]" class="form-control" multiple>
															<?
															while (!$res_type1->EOF) {
																if ($res_type1->RecordCount() == 1)
																	$selected = 'selected'; ?>
																<option value="<?= $res_type1->fields['PK_CAMPUS'] ?>" <?= $selected ?>><?= $res_type1->fields['CAMPUS_CODE'] ?></option>
															<? $res_type1->MoveNext();
															} ?>
														</select>
										</div>
										<div class="col-md-2">
										Unposted <?=START_DATE?>
											<input type="text" class="form-control date" id="START_DATE" name="START_DATE" value="" >
										</div>
										<div class="col-md-2">
										Unposted <?=END_DATE ?>
											<input type="text" class="form-control date" id="END_DATE" name="END_DATE" value="" >
										</div>

										<div class="col-md-4" >
											<br />
											<button type="button" onclick="search()" class="btn waves-effect waves-light btn-info"><?=SEARCH?></button>
											<button type="button" onclick="submit_form(1)" style="display:none;" id="btn1"  class="btn  waves-effect waves-light btn-info"><?=PDF?></button>
											<button type="button" onclick="submit_form(2)" style="display:none;" id="btn2" class="btn  waves-effect waves-light btn-info"><?=EXCEL?></button>

											<!-- <button type="button" onclick="submit_form(3)" style="display:none;" id="btn3" class="btn  waves-effect waves-light btn-info"><?php echo "Adavance Logging EXCEL"; ?></button> -->
											<input type="hidden" name="FORMAT" id="FORMAT" >
										</div>
									</div>
									<div class="row">
										<div class="col-md-2 mt-4" id="SEARCH_TXT_DIV" style="display:none;">
										<input type="text" class="form-control" id="SEARCH_TXT" name="SEARCH_TXT" placeholder="&#xF002; <?= SEARCH ?>" style="font-family: FontAwesome;" >
										</div>
									</div>
									<br /><br />
									<div id="batch_data" >
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
	<script type="text/javascript">

function clear_search(){
	document.getElementById('student_div').innerHTML = '';
	show_btn()
}

jQuery.expr[':'].icontains = function(a, i, m) {
  return jQuery(a).text().toUpperCase()
      .indexOf(m[3].toUpperCase()) >= 0;
};

jQuery(document).ready(function($) {

// Search all columns
$('#SEARCH_TXT').keyup(function(){
  // Search Text

		var search = $(this).val();
		// Hide all table tbody rows
		$('table tbody tr').hide();
		// Count total search result
		var len = $('table tbody tr:not(.notfound) td:icontains("'+search+'")').length;
		if(search!=""){
			document.getElementById('TOTAL_COUNT').innerHTML = len
		}else{
			document.getElementById('TOTAL_COUNT').innerHTML = $('table tbody tr').length-1;
		}

		if(len > 0){
			// Searching text in columns and show match row
			$('table tbody tr:not(.notfound) td:icontains("'+search+'")').each(function(){
			$(this).closest('tr').show();
			});
			get_count();

		document.getElementById('SEARCH_SELECT_ALL').checked = false
		var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_BATCH_UNPOSTED_HISTORY[]')
			for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
				if(PK_STUDENT_ENROLLMENT[i].parentNode.parentNode.style.display=="none"){
				PK_STUDENT_ENROLLMENT[i].checked = false
				}

			}
		}else{
			$('.notfound').show();
		}

	});
})

function fun_select_all(){
	var str = '';
	if(document.getElementById('SEARCH_SELECT_ALL').checked == true)
		str = true;
	else
		str = false;
		
	var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_BATCH_UNPOSTED_HISTORY[]')
	for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
	if(PK_STUDENT_ENROLLMENT[i].parentNode.parentNode.style.display!="none"){
		PK_STUDENT_ENROLLMENT[i].checked = str
	}
		
	}
	get_count()
}

function show_btn(){
	
	var flag = 0;
	var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_BATCH_UNPOSTED_HISTORY[]')
	for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
		if(PK_STUDENT_ENROLLMENT[i].checked == true) {
			flag++;
			break;
		}
	}
    document.getElementById('SEARCH_TXT_DIV').style.display = 'inline';

	if(flag == 1) {
		document.getElementById('btn1').style.display = 'inline';
		document.getElementById('btn2').style.display = 'inline';
		//document.getElementById('btn3').style.display = 'inline';

		

	} else {
		document.getElementById('btn1').style.display = 'none';
		document.getElementById('btn2').style.display = 'none';
		//document.getElementById('btn3').style.display = 'none';

	}
}

function get_count(){
	var PK_STUDENT_MASTER_sel = '';
	var tot = 0
	var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_BATCH_UNPOSTED_HISTORY[]')
	for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
		if(PK_STUDENT_ENROLLMENT[i].checked == true) {
			if(PK_STUDENT_MASTER_sel != '')
				PK_STUDENT_MASTER_sel += ',';
				
			//PK_STUDENT_MASTER_sel += document.getElementById('S_PK_STUDENT_MASTER_'+PK_STUDENT_ENROLLMENT[i].value).value
			tot++;
		}
	}
	//document.getElementById('SELECTED_PK_STUDENT_MASTER').value = PK_STUDENT_MASTER_sel
	//alert(PK_STUDENT_MASTER_sel)
	
	document.getElementById('SELECTED_COUNT').innerHTML = tot
	show_btn()
}
</script>
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
	$('#BATCH_TYPES').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=BATCH_TYPES?>',
			nonSelectedText: '<?=BATCH_TYPES?>',
			numberDisplayed: 2,
			nSelectedText: '<?=BATCH_TYPES?> selected'
		});


		$('#PK_CAMPUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= "Campus" ?>',
				nonSelectedText: '<?= "Campus" ?>',
				numberDisplayed: 2,
				nSelectedText: '<?= "Campus" ?> selected'
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
	
function search(){
	jQuery(document).ready(function($) {

		var valid = new Validation('form1', {onSubmit:false});
		var result = valid.validate();
		if(result==true){
		var data  = 'BATCH_TYPES='+$('#BATCH_TYPES').val()+'&END_DATE='+$('#END_DATE').val()+'&START_DATE='+$('#START_DATE').val()+'&PK_CAMPUS='+$('#PK_CAMPUS').val();
		var value = $.ajax({
			url: "ajax_search_unposted_batches",	
			type: "POST",		 
			data: data,		
			async: false,
			cache: false,
			success: function (data) {	
				document.getElementById('batch_data').innerHTML = data
				show_btn()
			}		
		}).responseText;

		}
	});
}

	</script>
	
</body>

</html>