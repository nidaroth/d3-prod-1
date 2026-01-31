<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/sap_scale.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(check_access('SETUP_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
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

$res_type = $db->Execute("SELECT * FROM S_SAP_SCALE_SETUP WHERE PK_SAP_SCALE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 

if($res_type->RecordCount() == 0){
	header("location:manage_sap_scale");
	exit;
}

$SAP_SCALE_NAME = $res_type->fields['SAP_SCALE_NAME'];
$PK_PROGRAM_PACE = $res_type->fields['PK_PROGRAM_PACE'];

$res_type_pace = $db->Execute("SELECT PROGRAM_PACE_NAME FROM S_PROGRAM_PACE WHERE ACTIVE = '1' AND  PK_PROGRAM_PACE = '$PK_PROGRAM_PACE' "); 
$PROGRAM_PACE_NAME = $res_type_pace->fields['PROGRAM_PACE_NAME'] ? $res_type_pace->fields['PROGRAM_PACE_NAME'] : 'N/A';


$SAP_SCALE_DESCRIPTION 	= $res_type->fields['SAP_SCALE_DESCRIPTION'];
$PK_CAMPUS 				= $res_type->fields['PK_CAMPUS'];
$IS_DEFAULT_YN 			= $res_type->fields['IS_DEFAULT'];

if ($IS_DEFAULT_YN == '1') {
	$IS_DEFAULT = 'Yes';
}
else{
	$IS_DEFAULT = 'No';
}
$ACTIVE_YN  	= $res_type->fields['ACTIVE'];
if ($ACTIVE_YN == '1') {
	$ACTIVE = 'Yes';
}
else{
	$ACTIVE = 'No';
}

$HOURS_COMPLETED_SCHEDULED_YN 	= $res_type->fields['HOURS_COMPLETED_SCHEDULED'];
if ($HOURS_COMPLETED_SCHEDULED_YN == '1') {
	$HOURS_COMPLETED_SCHEDULED = 'Yes';
}
else{
	$HOURS_COMPLETED_SCHEDULED = 'No';
}

$HOURS_COMPLETED_PROGRAM_YN 	= $res_type->fields['HOURS_COMPLETED_PROGRAM'];
if ($HOURS_COMPLETED_PROGRAM_YN == '1') {
	$HOURS_COMPLETED_PROGRAM = 'Yes';
}
else{
	$HOURS_COMPLETED_PROGRAM = 'No';
}

$HOURS_SCHEDULED_PROGRAM_YN 	= $res_type->fields['HOURS_SCHEDULED_PROGRAM'];
if ($HOURS_SCHEDULED_PROGRAM_YN == '1') {
	$HOURS_SCHEDULED_PROGRAM = 'Yes';
}
else{
	$HOURS_SCHEDULED_PROGRAM = 'No';
}

$UNITS_COMPLETED_ATTEMPTED_YN 	= $res_type->fields['UNITS_COMPLETED_ATTEMPTED'];
if ($UNITS_COMPLETED_ATTEMPTED_YN == '1') {
	$UNITS_COMPLETED_ATTEMPTED = 'Yes';
}
else{
	$UNITS_COMPLETED_ATTEMPTED = 'No';
}

$UNITS_COMPLETED_PROGRAM_YN 	= $res_type->fields['UNITS_COMPLETED_PROGRAM'];
if ($UNITS_COMPLETED_PROGRAM_YN == '1') {
	$UNITS_COMPLETED_PROGRAM = 'Yes';
}
else{
	$UNITS_COMPLETED_PROGRAM = 'No';
}

$UNITS_ATTEMPTED_PROGRAM_YN 	= $res_type->fields['UNITS_ATTEMPTED_PROGRAM'];
if ($UNITS_ATTEMPTED_PROGRAM_YN == '1') {
	$UNITS_ATTEMPTED_PROGRAM = 'Yes';
}
else{
	$UNITS_ATTEMPTED_PROGRAM = 'No';
}

$FA_UNITS_COMPLETED_ATTEMPTED_YN 	= $res_type->fields['FA_UNITS_COMPLETED_ATTEMPTED'];
if ($FA_UNITS_COMPLETED_ATTEMPTED_YN == '1') {
	$FA_UNITS_COMPLETED_ATTEMPTED = 'Yes';
}
else{
	$FA_UNITS_COMPLETED_ATTEMPTED = 'No';
}

$FA_UNITS_COMPLETED_PROGRAM_YN 	= $res_type->fields['FA_UNITS_COMPLETED_PROGRAM'];
if ($FA_UNITS_COMPLETED_PROGRAM_YN == '1') {
	$FA_UNITS_COMPLETED_PROGRAM = 'Yes';
}
else{
	$FA_UNITS_COMPLETED_PROGRAM = 'No';
}

$FA_UNITS_ATTEMPTED_PROGRAM_YN 	= $res_type->fields['FA_UNITS_ATTEMPTED_PROGRAM'];
if ($FA_UNITS_ATTEMPTED_PROGRAM_YN == '1') {
	$FA_UNITS_ATTEMPTED_PROGRAM = 'Yes';
}
else{
	$FA_UNITS_ATTEMPTED_PROGRAM = 'No';
}

$CUMULATIVE_GPA_YN 	= $res_type->fields['CUMULATIVE_GPA'];
if ($CUMULATIVE_GPA_YN == '1') {
	$CUM_GPA = 'Yes';
}
else{
	$CUM_GPA = 'No';
}

$PERIOD_HOURS_COMPLETED_SCHEDULED_YN = $res_type->fields['PERIOD_HOURS_COMPLETED_SCHEDULED'];
if ($PERIOD_HOURS_COMPLETED_SCHEDULED_YN == '1') {
	$PERIOD_HOURS_COMPLETED_SCHEDULED = 'Yes';
}
else{
	$PERIOD_HOURS_COMPLETED_SCHEDULED = 'No';
}

$PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED_YN = $res_type->fields['PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED'];
if ($PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED_YN == '1') {
	$PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED = 'Yes';
}
else{
	$PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED = 'No';
}

$PERIOD_FA_UNITS_COMPLETED_ATTEMPTED_YN = $res_type->fields['PERIOD_FA_UNITS_COMPLETED_ATTEMPTED'];
if ($PERIOD_FA_UNITS_COMPLETED_ATTEMPTED_YN == '1') {
	$PERIOD_FA_UNITS_COMPLETED_ATTEMPTED = 'Yes';
}
else{
	$PERIOD_FA_UNITS_COMPLETED_ATTEMPTED = 'No';
}

$PERIOD_GPA_YN 	= $res_type->fields['PERIOD_GPA'];
if ($PERIOD_GPA_YN == '1') {
	$PERIOD_GPA = 'Yes';
}
else{
	$PERIOD_GPA = 'No';
}

$INCLUDE_FIRST_PERIOD_YN 	= $res_type->fields['INCLUDE_FIRST_PERIOD'];
if ($INCLUDE_FIRST_PERIOD_YN == '1') {
	$INCLUDE_FIRST_PERIOD = 'Yes';
}
else{
	$INCLUDE_FIRST_PERIOD = 'No';
}

$dir 			= 'temp/';
$inputFileType  = 'Excel2007';
$file_name 		= $SAP_SCALE_NAME.'_SAP_Scale_Setup.xlsx';
$outputFileName = $dir.$file_name; 
$outputFileName = str_replace(pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName );  

$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
$objReader->setIncludeCharts(TRUE);
//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
$objPHPExcel = new PHPExcel();
$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$line 	= 1;	
$index 	= -1;

$heading[] = 'SAP Scale Name';
$width[]   = 20;
$heading[] = 'SAP Scale Description';
$width[]   = 20;
$heading[] = 'Campus';
$width[]   = 20;
$heading[] = 'Is Default';
$width[]   = 20;
$heading[] = 'Program Pace';
$width[]   = 20;
$heading[] = 'Active (Y/N)';
$width[]   = 20;
$heading[] = 'Period';
$width[]   = 20;
$heading[] = 'Program Percentage';
$width[]   = 20;
$heading[] = 'SAP Warning Status If Failed';
$width[]   = 20;
if($HOURS_COMPLETED_SCHEDULED != 'No')
{
	$heading[] = 'Hours Completed/Hours Scheduled';
	$width[]   = 20;
	$heading[] = 'Hours Completed/Hours Scheduled Percentage';
	$width[]   = 20;
}
if($HOURS_COMPLETED_PROGRAM != 'No')
{
	$heading[] = 'Hours Completed/Program Hours';
	$width[]   = 20;
	$heading[] = 'Hours Completed/Program Hours Percentage';
	$width[]   = 20;
}
if($HOURS_SCHEDULED_PROGRAM != 'No')
{
	$heading[] = 'Hours Scheduled/Program Hours';
	$width[]   = 20;
	$heading[] = 'Hours Scheduled/Program Hours Percentage';
	$width[]   = 20;
}
if($FA_UNITS_COMPLETED_ATTEMPTED != 'No')
{
	$heading[] = 'FA Units Completed/FA Units Attempted';
	$width[]   = 20;
	$heading[] = 'FA Units Completed/FA Units Attempted Percentage';
	$width[]   = 20;
}
if($FA_UNITS_COMPLETED_PROGRAM != 'No')
{
	$heading[] = 'FA Units Completed/Program FA Units';
	$width[]   = 20;
	$heading[] = 'FA Units Completed/Program FA Units Percentage';
	$width[]   = 20;
}
if($FA_UNITS_ATTEMPTED_PROGRAM != 'No')
{
	$heading[] = 'FA Units Attempted/Program FA Units';
	$width[]   = 20;
	$heading[] = 'FA Units Attempted/Program FA Units Percentage';
	$width[]   = 20;
}
if($UNITS_COMPLETED_ATTEMPTED != 'No')
{
	$heading[] = 'Units Completed/Hours Attempted';
	$width[]   = 20;
	$heading[] = 'Units Completed/Hours Attempted Percentage';
	$width[]   = 20;
}
if($UNITS_COMPLETED_PROGRAM != 'No')
{
	$heading[] = 'Units Completed/Program Units';
	$width[]   = 20;
	$heading[] = 'Units Completed/Program Units Percentage';
	$width[]   = 20;
}
if($UNITS_ATTEMPTED_PROGRAM != 'No')
{
	$heading[] = 'Units Attempted/Program Units';
	$width[]   = 20;
	$heading[] = 'Units Attempted/Program Units Percentage';
	$width[]   = 20;
}
if($CUM_GPA != 'No')
{
	$heading[] = 'Cumulative GPA';
	$width[]   = 20;
	$heading[] = 'Cumulative GPA Percentage';
	$width[]   = 20;
}
if($PERIOD_HOURS_COMPLETED_SCHEDULED != 'No')
{
	$heading[] = 'Include Transfer Hours';
	$width[]   = 20;
	}
if($PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED != 'No')
{
	$heading[] = 'Include Transfer Credits/Units - Standard';
	$width[]   = 20;
}
if($PERIOD_FA_UNITS_COMPLETED_ATTEMPTED != 'No')
{
	$heading[] = 'Include Transfer Credits/Units - FA';
	$width[]   = 20;
}
if($PERIOD_GPA != 'No')
{
	$heading[] = 'Include Transfer GPA';
	$width[]   = 20;
}

$resss = $db->Execute("select CAMPUS_CODE from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS IN ($PK_CAMPUS) AND ACTIVE = 1 order by CAMPUS_CODE ASC");
$PK_CAMPUS_ARRAY = array();
while (!$resss->EOF) 
{
	$PK_CAMPUS_ARRAY[] 	= $resss->fields['CAMPUS_CODE'];
	$resss->MoveNext();
}
$PK_CAMPUS_ARR = implode(', ', $PK_CAMPUS_ARRAY);

$res_det = $db->Execute("select PK_SAP_SCALE_DETAIL from S_SAP_SCALE_SETUP_DETAIL WHERE PK_SAP_SCALE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");	

$i = 0;
foreach($heading as $title) {
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
	$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
	$i++;
}	

$objPHPExcel->getActiveSheet()->freezePane('A2');

while (!$res_det->EOF) {

		$PK_SAP_SCALE_DETAIL = $res_det->fields['PK_SAP_SCALE_DETAIL'];

		$res_det1 = $db->Execute("select * from S_SAP_SCALE_SETUP_DETAIL WHERE PK_SAP_SCALE_DETAIL = '$PK_SAP_SCALE_DETAIL' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

		$PERIOD  								= $res_det1->fields['PERIOD'];
		$PROGRAM_PERCENTAGE  					= $res_det1->fields['PROGRAM_PACE_PERCENTAGE'];
		$PK_SAP_WARNING_ID  					= $res_det1->fields['PK_SAP_WARNING'];
		$CUMULATIVE_HOURS_COMPLETED_SCHEDULED  	= $res_det1->fields['CUMULATIVE_HOURS_COMPLETED_SCHEDULED'];
		$CUMULATIVE_HOURS_COMPLETED_PROGRAM  	= $res_det1->fields['CUMULATIVE_HOURS_COMPLETED_PROGRAM'];
		$CUMULATIVE_HOURS_SCHEDULED_PROGRAM  	= $res_det1->fields['CUMULATIVE_HOURS_SCHEDULED_PROGRAM'];

		$CUMULATIVE_UNITS_COMPLETED_ATTEMPTED  	= $res_det1->fields['CUMULATIVE_UNITS_COMPLETED_ATTEMPTED'];
		$CUMULATIVE_UNITS_COMPLETED_PROGRAM  	= $res_det1->fields['CUMULATIVE_UNITS_COMPLETED_PROGRAM'];
		$CUMULATIVE_UNITS_ATTEMPTED_PROGRAM  	= $res_det1->fields['CUMULATIVE_UNITS_ATTEMPTED_PROGRAM'];

		$CUMULATIVE_FA_UNITS_COMPLETED_ATTEMPTED = $res_det1->fields['CUMULATIVE_FA_UNITS_COMPLETED_ATTEMPTED'];
		$CUMULATIVE_FA_UNITS_COMPLETED_PROGRAM 	 = $res_det1->fields['CUMULATIVE_FA_UNITS_COMPLETED_PROGRAM'];
		$CUMULATIVE_FA_UNITS_ATTEMPTED_PROGRAM   = $res_det1->fields['CUMULATIVE_FA_UNITS_ATTEMPTED_PROGRAM'];
		
		$CUMULATIVE_GPA  						 = $res_det1->fields['CUMULATIVE_GPA'];

		$CUMULATIVE_PERIOD_HOURS_COMPLETED_SCHEDULED_YN  = $res_det1->fields['PERIOD_HOURS_COMPLETED_SCHEDULED'];
		if ($CUMULATIVE_PERIOD_HOURS_COMPLETED_SCHEDULED_YN == '1') {
			$CUMULATIVE_PERIOD_HOURS_COMPLETED_SCHEDULED = 'Yes';
		}
		else{
			$CUMULATIVE_PERIOD_HOURS_COMPLETED_SCHEDULED = 'No';
		}

		$CUMULATIVE_PERIOD_UNITS_COMPLETED_ATTEMPTED_YN  = $res_det1->fields['PERIOD_UNITS_COMPLETED_ATTEMPTED'];
		if ($CUMULATIVE_PERIOD_UNITS_COMPLETED_ATTEMPTED_YN == '1') {
			$CUMULATIVE_PERIOD_UNITS_COMPLETED_ATTEMPTED = 'Yes';
		}
		else{
			$CUMULATIVE_PERIOD_UNITS_COMPLETED_ATTEMPTED = 'No';
		}

		$CUMULATIVE_PERIOD_FA_UNITS_COMPLETED_ATTEMPTED_YN = $res_det1->fields['PERIOD_FA_UNITS_COMPLETED_ATTEMPTED'];
		if ($CUMULATIVE_PERIOD_FA_UNITS_COMPLETED_ATTEMPTED_YN == '1') {
			$CUMULATIVE_PERIOD_FA_UNITS_COMPLETED_ATTEMPTED = 'Yes';
		}
		else{
			$CUMULATIVE_PERIOD_FA_UNITS_COMPLETED_ATTEMPTED = 'No';
		}

		$CUMULATIVE_PERIOD_GPA_YN  						= $res_det1->fields['PERIOD_GPA'];
		if ($CUMULATIVE_PERIOD_GPA_YN == '1') {
			$CUMULATIVE_PERIOD_GPA = 'Yes';
		}
		else{
			$CUMULATIVE_PERIOD_GPA = 'No';
		}

		$res_sap = $db->Execute("select SAP_WARNING from S_SAP_WARNING WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND PK_SAP_WARNING = '$PK_SAP_WARNING_ID' ");
		$PK_SAP_WARNING  					 = $res_sap->fields['SAP_WARNING'];

		$line++;
		$index = -1;

		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($SAP_SCALE_NAME);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($SAP_SCALE_DESCRIPTION);

		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($PK_CAMPUS_ARR);

		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($IS_DEFAULT);

		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($PROGRAM_PACE_NAME);

		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($ACTIVE);

		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($PERIOD);

		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($PROGRAM_PERCENTAGE);

		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($PK_SAP_WARNING);

		if($HOURS_COMPLETED_SCHEDULED != 'No')
		{
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($HOURS_COMPLETED_SCHEDULED);

			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($CUMULATIVE_HOURS_COMPLETED_SCHEDULED);
		}
		if($HOURS_COMPLETED_PROGRAM != 'No')
		{
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($HOURS_COMPLETED_PROGRAM);

			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($CUMULATIVE_HOURS_COMPLETED_PROGRAM);
		}
		if($HOURS_SCHEDULED_PROGRAM != 'No')
		{
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($HOURS_SCHEDULED_PROGRAM);

			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($CUMULATIVE_HOURS_SCHEDULED_PROGRAM);
		}
		if($FA_UNITS_COMPLETED_ATTEMPTED != 'No')
		{
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($FA_UNITS_COMPLETED_ATTEMPTED);

			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($CUMULATIVE_FA_UNITS_COMPLETED_ATTEMPTED);
		}
		if($FA_UNITS_COMPLETED_PROGRAM != 'No')
		{
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($FA_UNITS_COMPLETED_PROGRAM);

			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($CUMULATIVE_FA_UNITS_COMPLETED_PROGRAM);
		}
		if($FA_UNITS_ATTEMPTED_PROGRAM != 'No')
		{
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($FA_UNITS_ATTEMPTED_PROGRAM);

			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($CUMULATIVE_FA_UNITS_ATTEMPTED_PROGRAM);
		}
		if($UNITS_COMPLETED_ATTEMPTED != 'No')
		{
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($UNITS_COMPLETED_ATTEMPTED);

			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($CUMULATIVE_UNITS_COMPLETED_ATTEMPTED);
		}
		if($UNITS_COMPLETED_PROGRAM != 'No')
		{
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($UNITS_COMPLETED_PROGRAM);

			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($CUMULATIVE_UNITS_COMPLETED_PROGRAM);
		}
		if($UNITS_ATTEMPTED_PROGRAM != 'No')
		{
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($UNITS_ATTEMPTED_PROGRAM);

			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($CUMULATIVE_UNITS_ATTEMPTED_PROGRAM);
		}
		if($CUM_GPA != 'No')
		{
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($CUM_GPA);

			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($CUMULATIVE_GPA);
		}
		if($CUMULATIVE_PERIOD_HOURS_COMPLETED_SCHEDULED != 'No')
		{
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($CUMULATIVE_PERIOD_HOURS_COMPLETED_SCHEDULED);
		}
		if($CUMULATIVE_PERIOD_UNITS_COMPLETED_ATTEMPTED != 'No')
		{
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($CUMULATIVE_PERIOD_UNITS_COMPLETED_ATTEMPTED);
		}
		if($CUMULATIVE_PERIOD_FA_UNITS_COMPLETED_ATTEMPTED != 'No')
		{
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($CUMULATIVE_PERIOD_FA_UNITS_COMPLETED_ATTEMPTED);
		}
		if($CUMULATIVE_PERIOD_GPA != 'No')
		{
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($CUMULATIVE_PERIOD_GPA);
		}
	
	$res_det->MoveNext();
}

$objWriter->save($outputFileName);
$objPHPExcel->disconnectWorksheets();
header("location:".$outputFileName);