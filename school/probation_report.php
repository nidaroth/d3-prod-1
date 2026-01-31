<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/projected_funds.php");
require_once("check_access.php");

if(check_access('REPORT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	$cond = "";
	
	if($_POST['REPORT_TYPE'] == 2){
		$cond .= " AND S_STUDENT_PROBATION.END_DATE != '0000-00-00' ";
	} else if($_POST['REPORT_TYPE'] == 3){
		$cond .= " AND S_STUDENT_PROBATION.END_DATE = '0000-00-00' ";
	}
	
	if($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '') {
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
		$cond .= " AND (S_STUDENT_PROBATION.BEGIN_DATE BETWEEN '$ST' AND '$ET' OR S_STUDENT_PROBATION.END_DATE BETWEEN '$ST' AND '$ET')";
	} else if($_POST['START_DATE'] != ''){
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
		$cond .= " AND (S_STUDENT_PROBATION.BEGIN_DATE >= '$ST' OR S_STUDENT_PROBATION.END_DATE >= '$ST') ";
	} else if($_POST['END_DATE'] != ''){
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
		$cond .= " AND (S_STUDENT_PROBATION.BEGIN_DATE <= '$ET' OR S_STUDENT_PROBATION.END_DATE <= '$ET') ";
	}
	
	$campus_cond = "";
	if(!empty($_POST['PK_CAMPUS'])){
		$cond .= " AND S_STUDENT_CAMPUS.PK_CAMPUS IN (".implode(",",$_POST['PK_CAMPUS']).") ";
		$campus_cond = " AND PK_CAMPUS IN (".implode(",",$_POST['PK_CAMPUS']).") ";
	}
	
	if(!empty($_POST['PK_PROBATION_TYPE'])){
		$cond .= " AND S_STUDENT_PROBATION.PK_PROBATION_TYPE IN (".implode(",",$_POST['PK_PROBATION_TYPE']).") ";
	}
	
	if(!empty($_POST['PK_PROBATION_LEVEL'])){
		$cond .= " AND S_STUDENT_PROBATION.PK_PROBATION_LEVEL IN (".implode(",",$_POST['PK_PROBATION_LEVEL']).") ";
	}
	
	if(!empty($_POST['PK_PROBATION_STATUS'])){
		$cond .= " AND S_STUDENT_PROBATION.PK_PROBATION_STATUS IN (".implode(",",$_POST['PK_PROBATION_STATUS']).") ";
	}
	
	$query = "select PK_STUDENT_PROBATION, CONCAT(LAST_NAME,', ',FIRST_NAME) as NAME,CODE,IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE, '%Y-%m-%d' )) AS BEGIN_DATE_1, S_STUDENT_PROBATION.NOTES, REASON,IF(S_STUDENT_PROBATION.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_PROBATION.BEGIN_DATE, '%Y-%m-%d' )) AS PROBATION_BEGIN_DATE ,IF(S_STUDENT_PROBATION.END_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_PROBATION.END_DATE, '%Y-%m-%d' )) AS PROBATION_END_DATE, PROBATION_TYPE, PROBATION_LEVEL, PROBATION_STATUS, CAMPUS_CODE, STUDENT_ID, STUDENT_STATUS
	FROM 
	S_STUDENT_MASTER, S_STUDENT_ACADEMICS, S_STUDENT_CAMPUS, S_CAMPUS, S_STUDENT_PROBATION 
	LEFT JOIN M_PROBATION_TYPE ON M_PROBATION_TYPE.PK_PROBATION_TYPE = S_STUDENT_PROBATION.PK_PROBATION_TYPE 
	LEFT JOIN M_PROBATION_LEVEL ON M_PROBATION_LEVEL.PK_PROBATION_LEVEL = S_STUDENT_PROBATION.PK_PROBATION_LEVEL 
	LEFT JOIN M_PROBATION_STATUS ON M_PROBATION_STATUS.PK_PROBATION_STATUS = S_STUDENT_PROBATION.PK_PROBATION_STATUS 
	LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_PROBATION.PK_STUDENT_ENROLLMENT 
	LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	WHERE 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER AND 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_PROBATION.PK_STUDENT_MASTER AND 
	S_STUDENT_CAMPUS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
	S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS AND 
	S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond GROUP BY PK_STUDENT_PROBATION ORDER BY CONCAT(LAST_NAME,' ',FIRST_NAME) ASC, S_STUDENT_PROBATION.BEGIN_DATE ASC";
	

	//echo $cond;exit;
		
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
				global $db, $campus_cond;
				
				$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
				
				if($res->fields['PDF_LOGO'] != '') {
					$ext = explode(".",$res->fields['PDF_LOGO']);
					$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
				}
				
				$this->SetFont('helvetica', '', 15);
				$this->SetY(5);
				$this->SetX(55);
				$this->SetTextColor(000, 000, 000);
				//$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
				$this->MultiCell(55, 5, $res->fields['SCHOOL_NAME'], 0, 'L', 0, 0, '', '', true);
				
				$this->SetFont('helvetica', 'I', 20);
				$this->SetY(8);
				$this->SetX(236);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, "Probation Report", 0, false, 'L', 0, '', 0, false, 'M', 'L');
				
				
				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(220, 13, 290, 13, $style);
				
				$str = "";
				if($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '')
					$str = " Between: ".$_POST['START_DATE'].' - '.$_POST['END_DATE'];
				else if($_POST['START_DATE'] != '')
					$str = " From ".$_POST['START_DATE'];
				else if($_POST['END_DATE'] != '')
					$str = " To ".$_POST['END_DATE'];
					
				$this->SetFont('helvetica', 'I', 10);
				$this->SetY(16);
				$this->SetX(187);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');

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
				
				$this->SetY(20);
				$this->SetX(187);
				$this->SetTextColor(000, 000, 000);
				//$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				$this->MultiCell(102, 5, "Campus(es): ".$campus_name, 0, 'R', 0, 0, '', '', true);
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

		$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(7, 31, 7);
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

		$sub_total = 0;
		
		$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<tr>
						<td width="15%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Student</td>
						<td width="10%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Student ID</td>
						<td width="13%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Campus</td>
						<td width="18%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Enrollment</td>
						<td width="9%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Begin Date</td>
						<td width="9%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >End Date</td>
						<td width="9%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Probation Type</td>
						<td width="9%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Probation Level</td>
						<td width="9%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Probation Status</td>
					</tr>';
		$res = $db->Execute($query);
		while (!$res->EOF) { 
	
			$txt 	.= '<tr>
						<td width="15%" >'.$res->fields['NAME'].'</td>
						<td width="10%"  >'.$res->fields['STUDENT_ID'].'</td>
						<td width="13%"  >'.$res->fields['CAMPUS_CODE'].'</td>
						<td width="18%"  >'.$res->fields['BEGIN_DATE_1'].' - '.$res->fields['CODE'].' - '.$res->fields['STUDENT_STATUS'].'</td>
						<td width="9%" >'.$res->fields['PROBATION_BEGIN_DATE'].'</td>
						<td width="9%" >'.$res->fields['PROBATION_END_DATE'].'</td>
						<td width="9%" >'.$res->fields['PROBATION_TYPE'].'</td>
						<td width="9%" >'.$res->fields['PROBATION_LEVEL'].'</td>
						<td width="9%" >'.$res->fields['PROBATION_STATUS'].'</td>
					</tr>';
					
			$res->MoveNext();
		}
		$txt 	.= '</table>';
		
			//echo $txt;exit;
		$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

		$file_name = 'Probation Report.pdf';
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
		$file_name 		= 'Probation Report.xlsx';
		$outputFileName = $dir.$file_name; 
$outputFileName = str_replace(
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	}
	
	$line = 1;
	$index 	= -1;
	$heading[] = 'Student';
	$width[]   = 30;
	$heading[] = 'Student ID';
	$width[]   = 30;
	$heading[] = 'Campus';
	$width[]   = 30;
	$heading[] = 'Program';
	$width[]   = 30;
	$heading[] = 'Status';
	$width[]   = 30;
	$heading[] = 'First Term';
	$width[]   = 15;
	$heading[] = 'Begin Date';
	$width[]   = 15;
	$heading[] = 'End Date';
	$width[]   = 20;
	$heading[] = 'Probation Type';
	$width[]   = 20;
	$heading[] = 'Probation Level';
	$width[]   = 20;
	$heading[] = 'Probation Status';
	$width[]   = 20;
	$heading[] = 'Reason';
	$width[]   = 20;
	$heading[] = 'Notes';
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

	$res_fa = $db->Execute($query);
	while (!$res_fa->EOF) { 

		$line++;
		$index = -1;
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['NAME']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['STUDENT_ID']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['CAMPUS_CODE']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['CODE']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['STUDENT_STATUS']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['BEGIN_DATE_1']);

		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['PROBATION_BEGIN_DATE']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['PROBATION_END_DATE']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['PROBATION_TYPE']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['PROBATION_LEVEL']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['PROBATION_STATUS']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['REASON']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['NOTES']);

		$res_fa->MoveNext();
	}
	
	$objWriter->save($outputFileName);
	$objPHPExcel->disconnectWorksheets();
	header("location:".$outputFileName);
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
	<title><?=MNU_PROBATION_REPORT?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		#advice-required-entry-PK_CAMPUS{position: absolute;top: 38px;}
		.dropdown-menu>li>a { white-space: nowrap; }
		.option_red > a > label{color:red !important}
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
							<?=MNU_PROBATION_REPORT?>
						</h4>
                    </div>
                </div>
				
				<form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col-md-12">
											<h4 class="text-themecolor"><?=START_DATE_BETWEEN?></h4>
										</div>
									</div>
									<div class="row form-group">
										<div class="col-md-2" id="PK_CAMPUS_DIV"  >
											<?=CAMPUS ?>
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control required-entry" >
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2">
											<?=BEGIN_DATE?>
											<input type="text" class="form-control date required-entry" id="START_DATE" name="START_DATE" value="" >
										</div>
										<div class="col-md-2">
											<?=END_DATE?>
											<input type="text" class="form-control date " id="END_DATE" name="END_DATE" value="" >
										</div>
										<div class="col-md-2">
											<?=REPORT_TYPE?>
											<select name="REPORT_TYPE" id="REPORT_TYPE" class="form-control" >
												<option value="1" >All Probations</option>
												<option value="2" >With an End Date</option>
												<option value="3" >Without an End Date</option>
											</select>
										</div>
										
										<div class="col-md-2" style="padding: 0;" >
											<br />
											<button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
											<button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button>
											<input type="hidden" name="FORMAT" id="FORMAT" >
										</div>
									</div>
									
									<div class="row form-group">
										<div class="col-md-2" >
											<?=PROBATION_TYPE ?>
											<select id="PK_PROBATION_TYPE" name="PK_PROBATION_TYPE[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select PK_PROBATION_TYPE, PROBATION_TYPE, ACTIVE from M_PROBATION_TYPE WHERE 1=1 order by ACTIVE DESC, PROBATION_TYPE ASC");
												while (!$res_type->EOF) { 
													$option_label = $res_type->fields['PROBATION_TYPE'];
													if($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)"; ?>
													<option value="<?=$res_type->fields['PK_PROBATION_TYPE']?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2" >
											<?=PROBATION_LEVEL ?>
											<select id="PK_PROBATION_LEVEL" name="PK_PROBATION_LEVEL[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select PK_PROBATION_LEVEL, PROBATION_LEVEL, ACTIVE from M_PROBATION_LEVEL WHERE 1=1 order by ACTIVE DESC, PROBATION_LEVEL ASC");
												while (!$res_type->EOF) { 
													$option_label = $res_type->fields['PROBATION_LEVEL'];
													if($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)"; ?>
													<option value="<?=$res_type->fields['PK_PROBATION_LEVEL']?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2" >
											<?=PROBATION_STATUS ?>
											<select id="PK_PROBATION_STATUS" name="PK_PROBATION_STATUS[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select PK_PROBATION_STATUS, PROBATION_STATUS, ACTIVE from M_PROBATION_STATUS WHERE 1=1 order by ACTIVE DESC, PROBATION_STATUS ASC");
												while (!$res_type->EOF) { 
													$option_label = $res_type->fields['PROBATION_STATUS'];
													if($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)"; ?>
													<option value="<?=$res_type->fields['PK_PROBATION_STATUS']?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
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
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		
		$('#PK_PROBATION_TYPE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PROBATION_TYPE?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=PROBATION_TYPE?> selected'
		});
		
		$('#PK_PROBATION_LEVEL').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PROBATION_LEVEL?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=PROBATION_LEVEL?> selected'
		});
		
		$('#PK_PROBATION_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PROBATION_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=PROBATION_STATUS?> selected'
		});
	});
	</script>

</body>

</html>