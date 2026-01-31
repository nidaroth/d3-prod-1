<? require_once("../global/config.php");
require_once("check_access.php");
require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
include_once('../global/excel/Classes/PHPExcel/IOFactory.php');
include_once('../global/cutome_excel_generator.php');
include_once('../global/cutome_sql_generator.php');
error_reporting(0);
ini_set('max_execution_time', '0'); // for infinite time of execution 
ini_set("memory_limit", "-1");

if (check_access('MANAGEMENT_ACCOUNTING') == 0) {
	header("location:../index");
	exit;
}

// print_r($_POST);


if (isset($_POST['export_id'])) {

	//INSERT M_STORED_PROCEDURE_LOG
	$PK_ACCOUNT = $_SESSION['PK_ACCOUNT'];
	$PK_CAMPUS = $_SESSION['PK_CAMPUS'];
	$PK_USER = $_SESSION['PK_USER'];
	$M_STORED_PROCEDURE_LOG['PK_ACCOUNT'] = $PK_ACCOUNT;
	$M_STORED_PROCEDURE_LOG['PK_CAMPUS'] = $PK_CAMPUS;
	$M_STORED_PROCEDURE_LOG['PK_USER'] = $PK_USER;
	$M_STORED_PROCEDURE_LOG['SP'] = 'DSIS70001';
	$M_STORED_PROCEDURE_LOG['SP_CALL'] = "DSIS70001($PK_ACCOUNT,'38',53)";
	db_perform('M_STORED_PROCEDURE_LOG', $M_STORED_PROCEDURE_LOG, 'insert');
	$PK_M_STORED_PROCEDURE_LOG = $db->insert_ID();


	foreach (explode(',', $_POST['export_id']) as $key => $value) {
		#3 ADD EXPORT FOR GENERATION IN STATUS  
		$M_DATA_EXPORT_LOG['PK_M_STORED_PROCEDURE_LOG'] = $PK_M_STORED_PROCEDURE_LOG;
		$M_DATA_EXPORT_LOG['EXPORT_TYPE'] = $_POST['EXPORT_TYPE'];
		$M_DATA_EXPORT_LOG['PK_ACCOUNT'] = $PK_ACCOUNT;
		$M_DATA_EXPORT_LOG['PK_USER'] = $_SESSION['PK_EMPLOYEE_MASTER'];
		$M_DATA_EXPORT_LOG['EXPORT_TYPE'] = $_POST['EXPORT_TYPE'];
		$M_DATA_EXPORT_LOG['PK_DATA_EXPORT'] = $value;
		db_perform('M_DATA_EXPORT_LOG', $M_DATA_EXPORT_LOG, 'insert');
	}




	//calling data exporter 



	//TODO : Remove read / write / exec access to public 



	if ($_SERVER['HTTP_HOST'] == 'localhost') {
		// $db_name     = 'DSIS';
		// $db_pass     = 'root';
		// $mysqli = new mysqli("localhost", "root", "$db_pass", "$db_name");
		$db_name     = 'DSIS';
		$db_pass     = 'Password_321*';
		$mysqli = new mysqli('192.168.50.143','dsis', "$db_pass", "$db_name");
		
		$file_path_prefix = 'diamondsis/school/';
	} else {
		$db_name     = 'DSIS';
		$db_pass     = 'DSISMySQLPa$$1!';
		$mysqli = new mysqli($db_host, "root", "$db_pass", "$db_name");
		$file_path_prefix = 'school/';
	}
	//List all initiated exports

	#LOPER ALGO 

	//1 Get a 
$query = "SELECT
    
	M_DATA_EXPORT_LOG.*,
	M_DATA_EXPORT.EXPORT_NAME,
	M_STORED_PROCEDURE_LOG.PK_ACCOUNT AS PK_ACCOUNT,
	M_STORED_PROCEDURE_LOG.PK_USER AS PK_USER
  FROM
	M_DATA_EXPORT_LOG
  LEFT JOIN
	M_STORED_PROCEDURE_LOG ON M_STORED_PROCEDURE_LOG.PK_STORED_PROCEDURE_LOG = M_DATA_EXPORT_LOG.PK_M_STORED_PROCEDURE_LOG
  LEFT JOIN 
  M_DATA_EXPORT ON M_DATA_EXPORT.PK_DATA_EXPORT = M_DATA_EXPORT_LOG.PK_DATA_EXPORT
  WHERE
	EXPORT_STATUS = '0' AND PK_M_STORED_PROCEDURE_LOG = '$PK_M_STORED_PROCEDURE_LOG'";
	$rs = mysql_query($query) or die(mysql_error());
	$files = [];

	while ($row = mysql_fetch_assoc($rs)) {

		//sleep(1);

		$PK_ACCOUNT = $row['PK_ACCOUNT'];
		$PK_USER = $row['PK_USER'];
		$PK_DATA_EXPORT = $row['PK_DATA_EXPORT'];
		$PK_DATA_EXPORT_LOG = $row['PK_DATA_EXPORT_LOG'];
		// echo "calling ---> CALL DSIS70001($PK_ACCOUNT , '$PK_DATA_EXPORT' , $PK_USER)";
		$mysqli->multi_query("CALL DSIS70001($PK_ACCOUNT , '$PK_DATA_EXPORT' , $PK_USER)");

		//print_r($mysqli->next_result());

		if ($mysqli->errno) {
			print_r($mysqli->error);
			exit;
		}
		do {
			// Store first result set
			$header = [];
			$data = [];
			//echo "==".mysql_num_rows($mysqli->store_result());
			if ($result = $mysqli->store_result()) {
 
				while ($job_row = $result->fetch_assoc()) {
					$data[] = $job_row;
					$header[] = array_keys($job_row);
				}
				$result->free_result();


				if ($_POST['EXPORT_TYPE'] == 'csv') {
					// $name = preg_replace( '/[^a-zA-Z0-9]+/', '-', strtolower( $row['EXPORT_NAME'] ) ) . '.xlsx'; 
					// $final_path = CustomExcelGenerator::make('Excel2007', 'temp/', $name, $data, $header);
									
					$name = 'temp/'.preg_replace( '/[^a-zA-Z0-9]+/', '-', strtolower( $row['EXPORT_NAME'] ) ) . '.csv'; 
					$final_path = downloadCsv($name, $data);				
					
				} else {
					$name = preg_replace('/[^a-zA-Z0-9]+/', '-', strtolower($row['EXPORT_NAME'])) . '.sql';
					$final_path = CustomSqlGenerator::make('Excel2007', 'temp/', $name, $data, $header);
				}

				$files[] = $final_path;
				
				 //echo $final_path;
			}
			// if there are more result-sets, the print a divider
			if ($mysqli->more_results()) {
				// printf("\n -------------\n");
			}
			//Prepare next result set
		} while ($mysqli->next_result());


		//add zip

		$mysqli->query("UPDATE M_DATA_EXPORT_LOG SET EXPORT_STATUS = 1 , EXPORT_LINK = '$final_path' WHERE PK_DATA_EXPORT_LOG = $PK_DATA_EXPORT_LOG");

		// header("location:" . $outputFileName);


	}

	$outputFileName = ZipFilesFromArray($files, 'temp/', 'DataExport');
	$response['path'] = $file_path_prefix . $outputFileName;
	$response['name'] = 'DataExport.zip';
	$response['files'] = $files;
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($response);
}

function ZipFilesFromArray(array $files, $dir, $filetitle)
{
	$dir = "temp/";
	$outputFileName = $dir . $filetitle . '.zip';
	$outputFileName = str_replace(
		pathinfo($outputFileName, PATHINFO_FILENAME),
		pathinfo($outputFileName, PATHINFO_FILENAME) . "_" . $_SESSION['PK_USER'] . "_" . floor(microtime(true) * 1000),
		$outputFileName
	);
	$zip = new ZipArchive();
	$zip->open($outputFileName,  ZipArchive::CREATE);
	foreach ($files as $file) {
		$zip->addFile("{$file}", str_replace($dir, '', $file));
	}
	if ($zip->close()) {
		return $outputFileName;
	} else {
		//throw error ?
		return false;
	}
}
//* * * * * home/path/to/command/the_command.sh


function downloadCsv($name, $data){
	$file_path= str_replace(
		pathinfo($name, PATHINFO_FILENAME),
		pathinfo($name, PATHINFO_FILENAME) . "_" . $_SESSION['PK_USER'] . "_" . floor(microtime(true) * 1000),
		$name
	);					
			
	$file = fopen($file_path, 'w');			
	fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));		
	// send the column headers
	fputcsv($file, array_keys(reset($data)));
	foreach ($data as $row) {
		fputcsv($file, $row);
	 }
	 fclose($file);	
	return $file_path;
}