<?
include_once('global/config.php');


// echo "Disabled for usage. enabled by devs only";exit;
 

// Define the path to your CSV file
$csvFile = 'country-codes.csv';

// Check if the file exists
if (!file_exists($csvFile)) {
    die("CSV file not found!");
}

// Open the CSV file for reading
$fileHandle = fopen($csvFile, 'r');

// Initialize an empty array to store CSV data
$csvData = [];

// Read each line from the CSV file
while (($data = fgetcsv($fileHandle, 1000, ',')) !== false) {
    // Add the CSV data to the array
    $csvData[] = $data;
}

// Close the file handle
fclose($fileHandle);

// Print the CSV data for demonstration (you can manipulate it as needed)
echo "<pre>";
// print_r($csvData); 

foreach ($csvData as $CountryData) {
    check_if_iso3_exists_in_db($CountryData[2] , $CountryData[1]);
}


function check_if_iso3_exists_in_db($ISO3,$LandCode){
    global $db;
    // $sql_update = "UPDATE Z_COUNTRY SET ISO_DIAL = '$LandCode' WHERE ISO3 = '$ISO3'";
    // $Z_COUNTRY = $db->Execute($sql_update);

    $sql = "SELECT * FROM  Z_COUNTRY WHERE ISO3 = '$ISO3'";
    $Z_COUNTRY = $db->Execute($sql);
    if($Z_COUNTRY->RecordCount() > 0){
        // echo " Found $ISO3 <br>";
        // echo "'$ISO3', <br>";
        $sql_update = "UPDATE Z_COUNTRY SET ISO_DIAL = '$LandCode' WHERE ISO3 = '$ISO3'";
        $db->Execute($sql_update);
        return $Z_COUNTRY->fields['PK_COUNTRY'];  
    }else{
        // echo "Missing $ISO3 <br>";
        return FALSE;
    }
}






while (!$Z_COUNTRY->EOF) {


    $Z_COUNTRY->MoveNext();
}

?>