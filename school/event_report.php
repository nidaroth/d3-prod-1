<?
ini_set("pcre.backtrack_limit", "5000000");
require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/event_report.php");
require_once("../language/menu.php");
require_once("check_access.php");
require_once("get_department_from_t.php");

if(check_access('REPORT_CUSTOM_REPORT') == 0 ){
header("location:../index");
exit;
}

function formatPhoneNumber($phone) {
	//$phonedata = explode(':',$phone);
	$phoneval = $phone;
	if(!empty($phoneval) && $phoneval!=''){
		$HOME_PHONE 	= preg_replace( '/[^0-9]/', '',$phoneval);
		
		if(!empty($HOME_PHONE))
		$phoneNumber = '('.$HOME_PHONE[0].$HOME_PHONE[1].$HOME_PHONE[2].') '.$HOME_PHONE[3].$HOME_PHONE[4].$HOME_PHONE[5].'-'.$HOME_PHONE[6].$HOME_PHONE[7].$HOME_PHONE[8].$HOME_PHONE[9];
	
	}

	return $phoneNumber;
}

if(!empty($_POST)){
//echo "<pre>";print_r($_POST);exit;

$cond = "";
if($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '') {
	$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
	$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
} else if($_POST['START_DATE'] != ''){
	$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
} else if($_POST['END_DATE'] != ''){
	$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
}

$campus_name = "";
$campus_cond = "";
$campus_id	 = "";
if(!empty($_POST['PK_CAMPUS'])){
	$PK_CAMPUS 	 = implode(",",$_POST['PK_CAMPUS']);
	$campus_cond = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
}
$COMPLETED='Complete/Incomplete Events';
if($_POST['COMPLETED']=='1'){
	$COMPLETED='Complete Events';
}else if($_POST['COMPLETED']=='2'){
	$COMPLETED='Incomplete Events';
}


$terms = "";
$SELECTED_EVENT_STATUS='';
if(!empty($_POST['PK_NOTE_STATUS'])){
	$PK_NOTE_STATUS 	 = implode(",",$_POST['PK_NOTE_STATUS']);
    $wh_cond = " AND PK_NOTE_STATUS IN ($PK_NOTE_STATUS) ";

	$res_type = $db->Execute("select PK_NOTE_STATUS,NOTE_STATUS from M_NOTE_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $wh_cond order by TRIM(NOTE_STATUS) ASC");
        while (!$res_type->EOF) {
            if($terms != '')
                $terms .= ', ';
            $terms .= $res_type->fields['NOTE_STATUS'];	
            $res_type->MoveNext();
        }        
        if(count(explode(',',$terms)) > 5){
        $SELECTED_EVENT_STATUS = "Multiple Status Selected";
		}
}

$cond = "";
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

	$cond .= " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($campus_id) ";

	$timezone = $_SESSION['PK_TIMEZONE'];
	if($timezone == '' || $timezone == 0) {
		$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$timezone = $res->fields['PK_TIMEZONE'];
		if($timezone == '' || $timezone == 0)
			$timezone = 4;
	}

	$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
	$TIMEZONE = $res->fields['TIMEZONE'];


	$PK_STUDENT_MASTER_ARR = array();
	foreach($_POST['PK_STUDENT_ENROLLMENT'] as $PK_STUDENT_ENROLLMENT){
		$PK_STUDENT_MASTER_ARR[$_POST['PK_STUDENT_MASTER_'.$PK_STUDENT_ENROLLMENT]] = $_POST['PK_STUDENT_MASTER_'.$PK_STUDENT_ENROLLMENT];
	}
	$PK_STUDENT_MASTER = implode(",", $PK_STUDENT_MASTER_ARR);
	
	$query1 = $_SESSION['EVENT_QUERY']." AND S_STUDENT_NOTES.PK_STUDENT_MASTER IN ($PK_STUDENT_MASTER) GROUP BY NOTE_TYPE,S_STUDENT_NOTES.PK_STUDENT_ENROLLMENT ORDER BY `M_NOTE_TYPE`.`NOTE_TYPE` DESC, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ',S_STUDENT_MASTER.FIRST_NAME,' ', S_STUDENT_MASTER.MIDDLE_NAME) ASC, STUDENT_ID ASC, NOTE_DATE ASC, NOTE_TIME ASC";
	
	//$query = $_SESSION['EVENT_QUERY']." AND S_STUDENT_NOTES.PK_STUDENT_MASTER IN ($PK_STUDENT_MASTER) GROUP BY PK_STUDENT_NOTES ".$_SESSION['EVENT_QUERY_ORDER'];
	//echo $query;exit;
	
	if($_POST['FORMAT'] == 1){
	
	require_once '../global/mpdf/vendor/autoload.php';	
	$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	$SCHOOL_NAME = $res->fields['SCHOOL_NAME'];
	$PDF_LOGO 	 = $res->fields['PDF_LOGO'];
	
	$logo = "";
	if($PDF_LOGO != '')
		$logo = '<img src="'.$PDF_LOGO.'" height="50px" />';
		

	//$report_name = "Events Status: ";

	
	$header = '<table width="100%" >
					<tr>
						<td width="20%" valign="top" >'.$logo.'</td>
						<td width="40%" valign="top" style="font-size:20px" ><b>'.$SCHOOL_NAME.'</b></td>
						<td width="40%" valign="top" >
							<table width="100%" >
								<tr>
									<td width="100%" align="right" style="font-size:28px;border-bottom:1px solid #000;font-style: italic;font-family:helvetica;font-weight:normal;" ><b>Student Events Report</b></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td colspan="3" width="100%" align="right" style="font-size:13px;" >Events Between: '.$_POST['START_DATE']." - ".$_POST['END_DATE'].'</td>
					</tr>
					<tr>
						<td colspan="3" width="100%" align="right" style="font-size:13px;" >'.$COMPLETED.'</td>
					</tr>';

					if(!empty($SELECTED_EVENT_STATUS)){
					$header .= '<tr>
						<td colspan="3" width="100%" align="right" style="font-size:13px;" >Events Status: '.$SELECTED_EVENT_STATUS.'</td>
					</tr>';
					}

					$header .= '<tr>
						<td colspan="3" width="100%" align="right" style="font-size:13px;" >All Languages</td>
					</tr>
				</table>';
	
	$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$TIMEZONE,date_default_timezone_get());
				
	$footer = '<table width="100%" >
					<tr>
						<td width="33%" valign="top" style="font-size:10px;" ><i>'.$date.'</i></td>
						<td width="33%" valign="top" style="font-size:10px;" align="center" ></td>
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
			'default_font_size' => 9,
			'format' => [210, 296],
			'orientation' => 'L'
		]);
		$mpdf->autoPageBreak = true;
		
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLFooter($footer);
		
		$file_name = "Student_Event_Report_".time().".pdf";

		
		$txt = '<table border="0" cellspacing="0" cellpadding="2" width="100%">
					<thead>
						<tr>
							<td width="35%" style="border-bottom:1px solid #000;font-size:15px">
								
								<table border="0" cellspacing="0" cellpadding="5" width="40%">
								<tr>
									<td style="font-size:15px"><b><i>In/Out</i></b></td>
									<td style="font-size:15px"><b><i>Student</i></b></td>
								</tr>
							</table>
							</td>
							<td width="12%" style="border-bottom:1px solid #000;font-size:15px">
								<b><i>Student ID</i></b>
							</td>
							<td width="12%" style="border-bottom:1px solid #000;font-size:15px">
								<b><i>Home Phone</i></b>
							</td>
							<td width="12%" style="border-bottom:1px solid #000;font-size:15px">
								<b><i>Work Phone</i></b>
							</td>
							<td width="12%" style="border-bottom:1px solid #000;font-size:15px">
								<b><i>Other</i></b>
							</td>
							<td width="19%" style="border-bottom:1px solid #000;font-size:15px"></td>
							
						</tr>
					</thead>';

		//echo $query; exit;

		//OTHER EVENT COUNT AND TOTAL		
		$count_arr = array();
		$res_event_cnt = $db->Execute($query1);	
		while (!$res_event_cnt->EOF) { 
			if(!empty($res_event_cnt->fields['NOTE_TYPE']) && !empty($res_event_cnt->fields['EVENT_OTHER'])){
			$count_arr[$res_event_cnt->fields['NOTE_TYPE']]['OTHER_EVENTS'][$res_event_cnt->fields['EVENT_OTHER']]++;
			$count_arr[$res_event_cnt->fields['NOTE_TYPE']]['Total']++;
			}
			$res_event_cnt->MoveNext();
		}
	
		// echo "<pre/>";
		// print_r($count_arr); exit;

		$res = $db->Execute($query1);	
		$event_count = 0;	
		$count_arr1 = array();	
		$NOTE_TYPE_ARR = array();
		$mpdf->AddPage();

		while (!$res->EOF) { 
		
			if(!in_array($res->fields['NOTE_TYPE'],$NOTE_TYPE_ARR)){
				$NOTE_TYPE_ARR[] = $res->fields['NOTE_TYPE'];
				$count_rows = 1;
				$txt .= '<tr><td colspan="6"><span style="font-size:18px">'.$res->fields['NOTE_TYPE'].'</span></td></tr>';
			}

			$txt .= '<tr>
						<td >
							<table border="0" cellspacing="1" cellpadding="2" width="100%">
								<tr>
									<td style="border:1px solid #000;width:70px;height:25px;">&nbsp;</td>
									<td>'.$res->fields['NAME'].'</td>
								</tr>
							</table>
						</td>						
						<td >'.$res->fields['STUDENT_ID'].'</td>
						<td >'.formatPhoneNumber($res->fields['HOME_PHONE']).'</td>
						<td >'.formatPhoneNumber($res->fields['WORK_PHONE']).'</td>
						<td >'.$res->fields['EVENT_OTHER'].'</td>
						<td >
							<table cellspacing="3" cellpadding="2">
								<tr>
									<td style="border-bottom:1px solid #000;width:100px;height:20px;left: 6%;position: relative;"></td>
									<td style="border-bottom:1px solid #000;width:100px;height:20px;left: 12%;position: relative;"></td>
								</tr>
							</table>
						</td>		
					</tr>';

					if($count_arr[$res->fields['NOTE_TYPE']]['Total']==$count_rows){
						if(count($count_arr[$res->fields['NOTE_TYPE']]['OTHER_EVENTS'])>0){
							ksort($count_arr[$res->fields['NOTE_TYPE']]['OTHER_EVENTS']);
							foreach ($count_arr[$res->fields['NOTE_TYPE']]['OTHER_EVENTS'] as $key => $value) {
								$txt .= '<tr>
											<td colspan="4"></td>
											<td style="font-size:15px">'.$key.'</td>
											<td style="text-align:center">'.$value.'</td>
										</tr>';
						
							}
							$txt .= '<tr>
											<td colspan="4"></td>
											<td style="font-size:15px"><b>Total</b></td>
											<td style="text-align:center">'.$count_arr[$res->fields['NOTE_TYPE']]['Total'].'</td>
										</tr>';
						}		
					}
					$count_rows++;				
			$res->MoveNext();
			$event_count ++;
			
		}
		
		$txt .= '</table>';

		if(!empty($PK_STUDENT_MASTER_ARR)){
		$txt .= '<br/>
				<table border="0" cellspacing="0" cellpadding="3" width="30%">
				<tr>
					<td style="font-size:15px;border-top:1px solid #000"><b>Student Count</b></td>
					<td style="font-size:15px;border-top:1px solid #000">'.count($PK_STUDENT_MASTER_ARR).'</td>
				</tr>
				<tr>
					<td style="font-size:15px"><b>Event Count</b></td>
					<td style="font-size:15px">'.$event_count.'</td>
				</tr>';
		$txt .= '</table>';
		}

		//  print_r($count_arr);
		//  echo $txt;exit;
		$mpdf->WriteHTML($txt);
		$mpdf->Output($file_name, 'D');

	} else {

		// 		$file_name = "Student_Event_Report.xlsx";
									
		// 		include '../global/excel/Classes/PHPExcel/IOFactory.php';
		// 		$cell1  = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");		
		// 		define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

		// 		$total_fields = 120;
		// 		for($i = 0 ; $i <= $total_fields ; $i++){
		// 			if($i <= 25)
		// 				$cell[] = $cell1[$i];
		// 			else {
		// 				$j = floor($i / 26) - 1;
		// 				$k = ($i % 26);
		// 				//echo $j."--".$k."<br />";
		// 				$cell[] = $cell1[$j].$cell1[$k];
		// 			}	
		// 		}

		// 		$dir 			= 'temp/';
		// 		$inputFileType  = 'Excel2007';
		// 		$outputFileName = $dir.$file_name; 
		// $outputFileName = str_replace(
		// 	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
		// 	$outputFileName );  

		// 		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		// 		$objReader->setIncludeCharts(TRUE);
		// 		//..$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		// 		$objPHPExcel = new PHPExcel();
		// 		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		// 		$objWriter->setPreCalculateFormulas(false);

		// 		$cell_no = 'A1';
		// 		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($campus_name);
		// 		$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);


		// $line = 1;
		// $index 	= -1;
		// $heading[] = 'Student';
		// $width[]   = 15;
		// $heading[] = 'Campus';
		// $width[]   = 15;
		// $heading[] = 'Student ID';
		// $width[]   = 15;
		// $heading[] = 'Enrollment';
		// $width[]   = 15;
		// $heading[] = 'Event Date';
		// $width[]   = 15;
		// $heading[] = 'Event Time';
		// $width[]   = 15;
		// $heading[] = 'Event Type';
		// $width[]   = 15;
		// $heading[] = 'Event Status';
		// $width[]   = 15;
		// $heading[] = 'Event Other';
		// $width[]   = 15;
		// $heading[] = 'Company';
		// $width[]   = 15;
		// $heading[] = 'Follow Up Date';
		// $width[]   = 15;
		// $heading[] = 'Follow Up Time';
		// $width[]   = 15;
		// $heading[] = 'Employee';
		// $width[]   = 15;
		// $heading[] = 'Created By';
		// $width[]   = 15;
		// $heading[] = 'Home Phone';
		// $width[]   = 15;
		// $heading[] = 'Mobile Phone';
		// $width[]   = 15;
		// $heading[] = 'Email';
		// $width[]   = 15;
		// $heading[] = 'Completed';
		// $width[]   = 15;
		// $heading[] = 'Comments';
		// $width[]   = 15;

		// $i = 0;
		// foreach($heading as $title) {
		// 	$index++;
		// 	$cell_no = $cell[$index].$line;
		// 	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
		// 	$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
		// 	$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
			
		// 	$i++;
		// }

		// $res = $db->Execute($query);
		// while (!$res->EOF) { 
		// 	$NOTE_TIME = '';
		// 	if($res->fields['NOTE_TIME'] != '00-00-00' && $res->fields['NOTE_DATE_1'] != '') 
		// 		$NOTE_TIME = date("h:i A", strtotime($res->fields['NOTE_TIME']));
				
		// 	$FOLLOWUP_TIME = '';
		// 	if($res->fields['FOLLOWUP_TIME'] != '00-00-00' && $res->fields['FOLLOWUP_DATE_1'] != '') 
		// 		$FOLLOWUP_TIME = date("h:i A", strtotime($res->fields['FOLLOWUP_TIME']));

		// 	$line++;
		// 	$index = -1;
			
		// 	$index++;
		// 	$cell_no = $cell[$index].$line;
		// 	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NAME']);
			
		// 	$index++;
		// 	$cell_no = $cell[$index].$line;
		// 	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CAMPUS_CODE']);

		// 	$index++;
		// 	$cell_no = $cell[$index].$line;
		// 	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ID']);
			
		// 	$index++;
		// 	$cell_no = $cell[$index].$line;
		// 	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['BEGIN_DATE_1'].' - '.$res->fields['CODE'].' - '.$res->fields['STUDENT_STATUS'].' - '.$res->fields['CAMPUS_CODE']);
			
		// 	$index++;
		// 	$cell_no = $cell[$index].$line;
		// 	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NOTE_DATE_1']);
			
		// 	$index++;
		// 	$cell_no = $cell[$index].$line;
		// 	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($NOTE_TIME);
			
		// 	$index++;
		// 	$cell_no = $cell[$index].$line;
		// 	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NOTE_TYPE']);
			
		// 	$index++;
		// 	$cell_no = $cell[$index].$line;
		// 	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NOTE_STATUS']);

		// 	$index++;
		// 	$cell_no = $cell[$index].$line;
		// 	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EVENT_OTHER']);
			
		// 	$index++;
		// 	$cell_no = $cell[$index].$line;
		// 	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['COMPANY_NAME']);
	
		// 	$index++;
		// 	$cell_no = $cell[$index].$line;
		// 	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FOLLOWUP_DATE_1']);
			
		// 	$index++;
		// 	$cell_no = $cell[$index].$line;
		// 	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($FOLLOWUP_TIME);
			
		// 	$index++;
		// 	$cell_no = $cell[$index].$line;
		// 	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EMP_NAME']);
			
		// 	$index++;
		// 	$cell_no = $cell[$index].$line;
		// 	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CREATED_BY']);
			
		// 	$index++;
		// 	$cell_no = $cell[$index].$line;
		// 	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['HOME_PHONE']);
			
		// 	$index++;
		// 	$cell_no = $cell[$index].$line;
		// 	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CELL_PHONE']);
			
		// 	$index++;
		// 	$cell_no = $cell[$index].$line;
		// 	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EMAIL']);
			
		// 	$index++;
		// 	$cell_no = $cell[$index].$line;
		// 	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['COMPLETED']);
			
		// 	$index++;
		// 	$cell_no = $cell[$index].$line;
		// 	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NOTES']);
			
		// 	$res->MoveNext();
		// }
		
		// $objWriter->save($outputFileName);
		// $objPHPExcel->disconnectWorksheets();
		// header("location:".$outputFileName);
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
<title><?=MNU_EVENT_REPORT?> | <?=$title?></title>
<style>
	li > a > label{position: unset !important;}
	#advice-required-entry-PK_CAMPUS {position: absolute;top: 55px;width: 142px}		
	.dropdown-menu>li>a { white-space: nowrap; }
	.option_red > a > label{color:red !important}
	.lds-ring {
position: absolute;
left: 0;
top: 0;
right: 0;
bottom: 0;
margin: auto;
width: 64px;
height: 64px;
}

.lds-ring div {
box-sizing: border-box;
display: block;
position: absolute;
width: 51px;
height: 51px;
margin: 6px;
border: 6px solid #0066ac;
border-radius: 50%;
animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
border-color: #007bff transparent transparent transparent;
}

.lds-ring div:nth-child(1) {
animation-delay: -0.45s;
}

.lds-ring div:nth-child(2) {
animation-delay: -0.3s;
}

.lds-ring div:nth-child(3) {
animation-delay: -0.15s;
}

@keyframes lds-ring {
0% {
	transform: rotate(0deg);
}

100% {
	transform: rotate(360deg);
}
}

#loaders {
position: fixed;
width: 100%;
z-index: 9999;
bottom: 0;
background-color: #2c3e50;
display: block;
left: 0;
top: 0;
right: 0;
bottom: 0;
opacity: 0.6;
display: none;
}	
</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
<? require_once("pre_load.php"); ?>
<div id="loaders" style="display: none;">
	<div class="lds-ring">
		<div></div>
		<div></div>
		<div></div>
		<div></div>
	</div>
</div>
<div id="main-wrapper">
	<? require_once("menu.php"); ?>
	<div class="page-wrapper">
		<div class="container-fluid">
				<div class="row page-titles">
				<div class="col-md-12 align-self-center">
					<h4 class="text-themecolor">
					<? echo MNU_EVENT_REPORT ?> </h4>
				</div>
			</div>
			<form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<div class="row" style="margin-bottom:1px" >
																			
									<div class="col-md-2" id="PK_CAMPUS_DIV"  >
										<?=CAMPUS?>
										<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control required-entry" onchange="search(0)" >
											<? 
											$res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
											while (!$res_type->EOF) { ?>
												<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option> <!-- Ticket # 1921 -->
											<?	$res_type->MoveNext();
											} ?>
										</select>
									</div>
									
									<div class="col-md-2 " id="EVENT_DATE_TYPE_DIV"  >
										<?=DATE_TYPE?>
										<select id="EVENT_DATE_TYPE" name="EVENT_DATE_TYPE" class="form-control" >
											<option value="ED" >Event Date</option>
											<option value="FD" >Follow Up Date</option>
										</select>
									</div>
									
									
									
									
									
									<div class="col-md-2" id="START_DATE_DIV"  >
										<?=START_DATE?>
										<input type="text" class="form-control date required-entry" id="START_DATE" name="START_DATE" value="" onchange="search(0)" >
									</div>
									<div class="col-md-2" id="END_DATE_DIV"  >
										<?=END_DATE?>
										<input type="text" class="form-control date required-entry" id="END_DATE" name="END_DATE" value="" onchange="search(0)" >
									</div>
									
									
									
									<div class="col-md-2 ">
										<br />
										<button type="button" onclick="submit_form(1)" id="btn_1" class="btn waves-effect waves-light btn-info" style="display:none"><?=PDF?></button>
										<!-- <button type="button" onclick="submit_form(2)" id="btn_2" class="btn waves-effect waves-light btn-info" style="display:none"><?=EXCEL?></button> -->
									</div>
								</div>
								<hr style="border-top: 1px solid #ccc;" />
								
								<div class="row" style="margin-bottom:1px" >
								
																			
									<div class="col-md-2 " id="PK_NOTE_TYPE_DIV"  >
										<div id="PK_NOTE_TYPE_LABEL" ><?=EVENT_TYPE?></div>
										<div id="PK_NOTE_TYPE_DIV_1" >
											<select id="PK_NOTE_TYPE" name="PK_NOTE_TYPE[]" class="form-control" multiple  >
											</select>
										</div>
									</div>
									
									<div class="col-md-2 " id="PK_NOTE_STATUS_DIV"  >
										<div id="PK_NOTE_STATUS_LABEL" ><?=EVENT_STATUS?></div>
										<div id="PK_NOTE_STATUS_DIV_1" >
											<select id="PK_NOTE_STATUS" name="PK_NOTE_STATUS[]" multiple class="form-control"  >
											</select>
										</div>
									</div>
									
									<div class="col-md-2 " id="PK_EVENT_OTHER_DIV"  >
										<div id="PK_EVENT_OTHER_LABEL" ><?=EVENT_OTHER?></div>
										<div id="PK_EVENT_OTHER_DIV_1" >
											<select id="PK_EVENT_OTHER" name="PK_EVENT_OTHER[]" multiple class="form-control"  >
											</select>
										</div>
									</div>
									
									<div class="col-md-2 " id="TASK_COMPLETED_DIV"  >
										<div id="COMPLETED_LABEL" ><?=EVENT_COMPLETED?></div>
										<select id="COMPLETED" name="COMPLETED" class="form-control"  >
											<option value="0" >Both</option>
											<option value="1" >Yes</option>
											<option value="2" >No</option>
										</select>
									</div>

								</div>

								<div class="row" style="margin-bottom:1px" >
									<div class="col-md-2 " id="PK_COMPANY_DIV"  >
										<div id="COMPANY_LABEL" ><?=COMPANY?></div>
										<select id="PK_COMPANY" name="PK_COMPANY[]" multiple class="form-control">
											<? $res_type = $db->Execute("SELECT PK_COMPANY,COMPANY_NAME,ACTIVE FROM S_COMPANY WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY ACTIVE DESC, COMPANY_NAME ASC ");
											while (!$res_type->EOF) 
											{ 
												$selected 		= "";
												$PK_COMPANY 	= $res_type->fields['PK_COMPANY']; 
												if($PK_COMPANY_ARR == $PK_COMPANY) {
													$selected = 'selected';
												}

												$option_labels = $res_type->fields['COMPANY_NAME'];
												if($res_type->fields['ACTIVE'] == 0)
												{
													$option_labels .= " (Inactive)";
												}
												?>
												<option value="<?=$res_type->fields['PK_COMPANY']?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) {echo "class='option_red'"; } ?>  ><?=$option_labels?></option>
											<?	$res_type->MoveNext();
											} ?>
										</select>
									</div>
									<div class="col-md-2" id="PK_DEPARTMENT_DIV"  >
										<?=DEPARTMENT?>
										<select id="PK_DEPARTMENT" name="PK_DEPARTMENT[]" multiple class="form-control" onchange="fetch_values();search(0)" >
											<option value="5">Accounting</option>
											<option value="1">Admissions</option>
											<option value="3">Finance</option>
											<option value="6">Placement</option>
											<option value="2">Registrar</option>
										</select>
									</div>
									
									<div class="col-md-2" id="PK_EMPLOYEE_MASTER_DIV"  >
										<?=EMPLOYEE?>
										<div id="PK_EMPLOYEE_MASTER_DIV_1" >
											<select id="PK_EMPLOYEE_MASTER" name="PK_EMPLOYEE_MASTER[]" multiple class="form-control required-entry" >
											</select>
										</div>
									</div>
									
									<div class="col-md-2" id="CREATED_BY_DIV"  >
										<?=CREATED_BY?>
										<div id="CREATED_BY_DIV_1" >
											<select id="CREATED_BY" name="CREATED_BY[]" multiple class="form-control" >
											</select>
										</div>
									</div>
								</div>

								
								<div class="row" style="margin-bottom:1px" >	
									<div class="col-md-2 form-group " id="PK_TERM_MASTER_DIV"  >
										<div id="TERM_MASTER_LABEL" ><?=FIRST_TERM?></div>
										<select id="PK_TERM_MASTER" name="PK_TERM_MASTER[]" multiple class="form-control" >
											<? $res_type = $db->Execute("SELECT PK_TERM_MASTER, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION, ACTIVE from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, BEGIN_DATE DESC");
											while (!$res_type->EOF) { 
												$str = $res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['END_DATE_1'];
												if($res_type->fields['ACTIVE'] == 0)
													$str .= ' (Inactive)'; ?>
												<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$str ?></option>
											<?	$res_type->MoveNext();
											} ?>
										</select>
									</div>
									<div class="col-md-2 form-group " id="PK_CAMPUS_PROGRAM_DIV"  >
										<div id="CAMPUS_PROGRAM_LABEL" ><?=PROGRAM?></div>
										<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control" >
											<? $res_type = $db->Execute("SELECT PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
											while (!$res_type->EOF) { ?>
												<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
											<?	$res_type->MoveNext();
											} ?>
										</select>
									</div>
									<div class="col-md-2" id="PK_STUDENT_STATUS_DIV"  >
										<?=STUDENT_STATUS?><br />
										<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control" >
											<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND (ADMISSIONS = 0) order by ACTIVE DESC, STUDENT_STATUS ASC");
											while (!$res_type->EOF) { 
												$option_label = $res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION'];
												if($res_type->fields['ACTIVE'] == 0)
													$option_label .= " (Inactive)";  ?>
												<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
											<?	$res_type->MoveNext();
											} ?>
										</select>
									</div>
									
									<div class="col-md-1 ">
										<br />
										<button type="button" onclick="search(1)" id="btn_search" class="btn waves-effect waves-light btn-info"  ><?=SEARCH?></button>
										<input type="hidden" name="FORMAT" id="FORMAT" >
									</div>
								</div>
								
								
								
								<br />
								<div id="PHONE_DIV" >
									
								</div>
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

<script src="../backend_assets/dist/js/validation_prototype.js"></script>
<script src="../backend_assets/dist/js/validation.js"></script>
<script type="text/javascript">
		
	function fetch_values(){
		jQuery(document).ready(function($) { 
				var t = $("#PK_DEPARTMENT").val()
				get_note_type(t,1);
				get_note_status(t,1);
				get_event_other(t,0);
				get_employee(t,1);
				get_created_by(t,0);				
		});	
	}
	
	function get_created_by(val,events){
		jQuery(document).ready(function($) {
			var data  = 't='+val+'&event='+events+'&show_inactive=1';
			var value = $.ajax({
				url: "ajax_get_employee_from_department",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					data = data.replace("PK_EMPLOYEE_MASTER","CREATED_BY")
					document.getElementById('CREATED_BY_DIV_1').innerHTML = data
					
					document.getElementById('CREATED_BY').setAttribute('multiple', true);
					document.getElementById('CREATED_BY').name = "CREATED_BY[]"
					document.getElementById('CREATED_BY').setAttribute('onchange', "search(0)");
					$("#CREATED_BY option[value='']").remove();
					
					$("#CREATED_BY").children().first().remove();
	
					$('#CREATED_BY').multiselect({
						includeSelectAllOption: true,
						allSelectedText: 'All <?=CREATED_BY?>',
						nonSelectedText: '',
						numberDisplayed: 2,
						nSelectedText: '<?=CREATED_BY?> selected', 
						enableCaseInsensitiveFiltering: true, 
					});
				}		
			}).responseText;
		});
	}
					
	function get_note_type(val,events){
		jQuery(document).ready(function($) {
			var data  = 't='+val+'&event='+events+'&show_inactive=1';
			var value = $.ajax({
				url: "ajax_get_note_type_from_department",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					document.getElementById('PK_NOTE_TYPE_DIV_1').innerHTML = data
					
					document.getElementById('PK_NOTE_TYPE').setAttribute('multiple', true);
					document.getElementById('PK_NOTE_TYPE').name = "PK_NOTE_TYPE[]"
					$("#PK_NOTE_TYPE option[value='']").remove();
					
					$("#PK_NOTE_TYPE").children().first().remove();
	
					$('#PK_NOTE_TYPE').multiselect({
						includeSelectAllOption: true,
						allSelectedText: 'All <?=EVENT_TYPE?>',
						nonSelectedText: '',
						numberDisplayed: 2,
						nSelectedText: '<?=EVENT_TYPE?> selected'
					});
				}		
			}).responseText;
		});
	}
	function get_event_other(val,task){
		jQuery(document).ready(function($) {
			var data  = 't='+val+'&task='+task+'&show_inactive=1';
			var value = $.ajax({
				url: "ajax_get_event_other_from_department",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					document.getElementById('PK_EVENT_OTHER_DIV_1').innerHTML = data
					
					document.getElementById('PK_EVENT_OTHER').setAttribute('multiple', true);
					document.getElementById('PK_EVENT_OTHER').name = "PK_EVENT_OTHER[]"
					document.getElementById('PK_EVENT_OTHER').setAttribute('onchange', "search(0)");
					$("#PK_EVENT_OTHER option[value='']").remove();
					
					$("#PK_EVENT_OTHER").children().first().remove();
	
					$('#PK_EVENT_OTHER').multiselect({
						includeSelectAllOption: true,
						allSelectedText: 'All <?=EVENT_OTHER?>',
						nonSelectedText: '',
						numberDisplayed: 2,
						nSelectedText: '<?=EVENT_OTHER?> selected'
					});	
					
				}		
			}).responseText;
		});
	}
	function get_note_status(val,events){
		jQuery(document).ready(function($) {
			var data  = 't='+val+'&event='+events+'&show_inactive=1';
			var value = $.ajax({
				url: "ajax_get_note_status_from_department",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					document.getElementById('PK_NOTE_STATUS_DIV_1').innerHTML = data
					
					document.getElementById('PK_NOTE_STATUS').setAttribute('multiple', true);
					document.getElementById('PK_NOTE_STATUS').name = "PK_NOTE_STATUS[]"
					$("#PK_NOTE_STATUS option[value='']").remove();
					
					$("#PK_NOTE_STATUS").children().first().remove();
	
					$('#PK_NOTE_STATUS').multiselect({
						includeSelectAllOption: true,
						allSelectedText: 'All <?=EVENT_STATUS?>',
						nonSelectedText: '',
						numberDisplayed: 2,
						nSelectedText: '<?=EVENT_STATUS?> selected'
					});
				}		
			}).responseText;
		});
	}
	function get_employee(val,events){
		jQuery(document).ready(function($) {
			var data  = 't='+val+'&event='+events+'&show_inactive=1';
			var value = $.ajax({
				url: "ajax_get_employee_from_department",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					document.getElementById('PK_EMPLOYEE_MASTER_DIV_1').innerHTML = data
					
					document.getElementById('PK_EMPLOYEE_MASTER').setAttribute('multiple', true);
					document.getElementById('PK_EMPLOYEE_MASTER').name = "PK_EMPLOYEE_MASTER[]"
					document.getElementById('PK_EMPLOYEE_MASTER').setAttribute('onchange', "search(0)");
					$("#PK_EMPLOYEE_MASTER option[value='']").remove();
					
					$("#PK_EMPLOYEE_MASTER").children().first().remove();
	
					$('#PK_EMPLOYEE_MASTER').multiselect({
						includeSelectAllOption: true,
						allSelectedText: 'All <?=EMPLOYEE?>',
						nonSelectedText: '',
						numberDisplayed: 2,
						nSelectedText: '<?=EMPLOYEE?> selected', 
						enableCaseInsensitiveFiltering: true, 
					});
			
				}		
			}).responseText;
		});
	}
	

	function fun_select_all(){
		var str = '';
		if(document.getElementById('SEARCH_SELECT_ALL').checked == true)
			str = true;
		else
			str = false;
			
		var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
		for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
			PK_STUDENT_ENROLLMENT[i].checked = str
		}
		get_count()
	}
	
	function show_btn(){
		
		var flag = 0;
		var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
		for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
			if(PK_STUDENT_ENROLLMENT[i].checked == true) {
				flag++;
				break;
			}
		}
		
		if(flag == 1) {
			document.getElementById('btn_1').style.display = 'inline';
			document.getElementById('btn_2').style.display = 'inline';
		} else {
			document.getElementById('btn_1').style.display = 'none';
			document.getElementById('btn_2').style.display = 'none';
		}
	}
	
	function get_count(){
		var tot = 0
		var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
		for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
			if(PK_STUDENT_ENROLLMENT[i].checked == true)
				tot++;
		}
		document.getElementById('SELECTED_COUNT').innerHTML = tot
		show_btn()
	}
	
	function search(type){
	
		if(type == 0) {
			document.getElementById('PHONE_DIV').innerHTML = ''
			
		} else {
			var valid = new Validation('form1', {onSubmit:false});
			var result = valid.validate();
			if(result == true){ 
				document.getElementById('loaders').style.display = 'block';

				jQuery(document).ready(function($) {
					
						var data  = 'NOTE_DATE_TYPE='+$('#EVENT_DATE_TYPE').val()+'&START_DATE='+$('#START_DATE').val()+'&END_DATE='+$('#END_DATE').val()+'&PK_NOTE_TYPE='+$('#PK_NOTE_TYPE').val()+'&PK_NOTE_STATUS='+$('#PK_NOTE_STATUS').val()+'&PK_EVENT_OTHER='+$('#PK_EVENT_OTHER').val()+'&COMPLETED='+$('#COMPLETED').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&PK_COMPANY='+$('#PK_COMPANY').val()+'&PK_CAMPUS='+$('#PK_CAMPUS').val()+'&PK_DEPARTMENT='+$('#PK_DEPARTMENT').val()+'&PK_EMPLOYEE_MASTER='+$('#PK_EMPLOYEE_MASTER').val()+'&CREATED_BY='+$('#CREATED_BY').val()+'&show_check=1&show_count=1&type=event'; 
												
				
					var value = $.ajax({
						url: "ajax_event_report?",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							document.getElementById('loaders').style.display = 'none';
							document.getElementById('PHONE_DIV').innerHTML = data
							show_btn();
						}		
					}).responseText;
				});
			}
		}
	}

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
	$('#PK_CAMPUS').val('').trigger("change");
	
	$('#PK_EMPLOYEE_MASTER').multiselect({
		includeSelectAllOption: true,
		allSelectedText: 'All <?=EMPLOYEE?>',
		nonSelectedText: '',
		numberDisplayed: 2,
		nSelectedText: '<?=EMPLOYEE?> selected', 
		enableCaseInsensitiveFiltering: true,
	});
	$('#PK_EMPLOYEE_MASTER').val('').trigger("change");
	
	$('#PK_DEPARTMENT').multiselect({
		includeSelectAllOption: true,
		allSelectedText: 'All <?=DEPARTMENT?>',
		nonSelectedText: '',
		numberDisplayed: 2,
		nSelectedText: '<?=DEPARTMENT?> selected'
	});
	$('#PK_DEPARTMENT').val('').trigger("change");
	
	$('#PK_NOTE_TYPE').multiselect({
		includeSelectAllOption: true,
		allSelectedText: 'All <?=EVENT_TYPE?>',
		nonSelectedText: '',
		numberDisplayed: 2,
		nSelectedText: '<?=EVENT_TYPE?> selected'
	});
	$('#PK_NOTE_TYPE').val('').trigger("change");
	
	$('#PK_NOTE_STATUS').multiselect({
		includeSelectAllOption: true,
		allSelectedText: 'All <?=EVENT_STATUS?>',
		nonSelectedText: '',
		numberDisplayed: 2,
		nSelectedText: '<?=EVENT_STATUS?> selected'
	});
	$('#PK_NOTE_STATUS').val('').trigger("change");
	
	$('#PK_EVENT_OTHER').multiselect({
		includeSelectAllOption: true,
		allSelectedText: 'All <?=EVENT_OTHER?>',
		nonSelectedText: '',
		numberDisplayed: 2,
		nSelectedText: '<?=EVENT_OTHER?> selected'
	});
	$('#PK_EVENT_OTHER').val('').trigger("change");
	
	$('#CREATED_BY').multiselect({
		includeSelectAllOption: true,
		allSelectedText: 'All <?=CREATED_BY?>',
		nonSelectedText: '',
		numberDisplayed: 2,
		nSelectedText: '<?=CREATED_BY?> selected', 
		enableCaseInsensitiveFiltering: true, 
	});
	$('#CREATED_BY').val('').trigger("change");
	
	$('#PK_STUDENT_STATUS').multiselect({
		includeSelectAllOption: true,
		allSelectedText: 'All <?=STUDENT_STATUS?>',
		nonSelectedText: '',
		numberDisplayed: 2,
		nSelectedText: '<?=STUDENT_STATUS?> selected'
	});

	$('#PK_COMPANY').multiselect({
		includeSelectAllOption: true,
		allSelectedText: 'All <?=COMPANY?>',
		nonSelectedText: '',
		numberDisplayed: 2,
		nSelectedText: '<?=COMPANY?> selected'
	});

	$('#PK_TERM_MASTER').multiselect({
		includeSelectAllOption: true,
		allSelectedText: 'All <?=FIRST_TERM?>',
		nonSelectedText: '',
		numberDisplayed: 2,
		nSelectedText: '<?=FIRST_TERM?> selected'
	});

	$('#PK_CAMPUS_PROGRAM').multiselect({
		includeSelectAllOption: true,
		allSelectedText: 'All <?=PROGRAM?>',
		nonSelectedText: '',
		numberDisplayed: 2,
		nSelectedText: '<?=PROGRAM?> selected'
	});
	
});
</script>

</body>

</html>
