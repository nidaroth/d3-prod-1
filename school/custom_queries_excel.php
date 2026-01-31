<?php require_once('../global/config.php');
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/student.php");
require_once("../language/custom_report.php");
require_once("../language/student_contact.php");
require_once("check_access.php");

$res_add_on = $db->Execute("SELECT CUSTOM_QUERIES FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res_add_on->fields['CUSTOM_QUERIES'] == 0 || check_access('MANAGEMENT_CUSTOM_QUERY') == 0){
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
$res = $db->Execute("SELECT PK_CUSTOM_QUERY_ACCOUNT,CUSTOM_NAME,EXTERNAL_DESCRIPTION, M_CUSTOM_QUERY.PK_CUSTOM_QUERY,CUSTOM_QUERY FROM M_CUSTOM_QUERY_ACCOUNT, M_CUSTOM_QUERY WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND M_CUSTOM_QUERY.PK_CUSTOM_QUERY = M_CUSTOM_QUERY_ACCOUNT.PK_CUSTOM_QUERY  AND PK_CUSTOM_QUERY_ACCOUNT = '$_GET[id]' ");

$dir 			= 'temp/';
$inputFileType  = 'Excel2007';
$file_name 		= $res->fields['CUSTOM_NAME'].'.xlsx';
$outputFileName = $dir.$file_name; 
$outputFileName = str_replace(
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
$objReader->setIncludeCharts(TRUE);
//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
$objPHPExcel = new PHPExcel();
$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$line 	= 0;	
$index 	= -1;

$i = 1;
if($res->fields['PK_CUSTOM_QUERY'] == 80){
	try {
	    // Mostrar el query
	    // echo "<pre>" . $res->fields['CUSTOM_QUERY'] . "</pre>";

	    // Ejecutar query
	    $res = $db->Execute($res->fields['CUSTOM_QUERY']); 

	    if ($res === false) {
	        // Captura error de ADOdb si el query falla
	        throw new Exception("Error query: " . $db->ErrorMsg());
	    }

	    // Mostrar resultado del query (para debug)
	    // echo "<pre>";
	    // print_r($res);
	    // echo "</pre>";
	    
	} catch (Exception $e) {
	    echo "<div style='color:red; font-weight:bold;'>Error:</div>";
	    echo "<pre>" . $e->getMessage() . "</pre>";
	    // Aquí puedes loguear también: error_log($e->getMessage());
	}
	
}else{
	$res = $db->Execute("CALL DSIS_CUSTOM_QUERY(".$_SESSION['PK_ACCOUNT'].", ".$res->fields['PK_CUSTOM_QUERY'].")"); 	
}
while (!$res->EOF) { 
	$index 	= -1;
	if($i == 1){
		$line++;	
		foreach($res->fields as $key => $val){
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($key);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(20);
		}
	}
	
	$index 	= -1;
	$line++;
	foreach($res->fields as $key => $val){	
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($val);
	}
	
	$i++;
	$res->MoveNext();
}
$objWriter->save($outputFileName);
$objPHPExcel->disconnectWorksheets();
header("location:".$outputFileName);


