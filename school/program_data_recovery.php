<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<?php
require_once('custom_excel_generator.php');
require_once("../global/config.php");

ENABLE_DEBUGGING('E');
ini_set('memory_limit', '-1');
echo $field_changed_sql = "SELECT
PK_STUDENT_TRACK_CHANGES,
S_STUDENT_TRACK_CHANGES.PK_ACCOUNT AS PK_ACCOUNT,
S_STUDENT_TRACK_CHANGES.PK_STUDENT_MASTER,
S_STUDENT_TRACK_CHANGES.PK_STUDENT_ENROLLMENT, 
CONCAT(LAST_NAME, ', ', FIRST_NAME) AS `Student Name`, 
S_CAMPUS.CAMPUS_CODE As `Campus Code`,
FIELD_NAME as `Changed Field`,
OLD_VALUE , 
NEW_VALUE ,
'Empty Program Updated' AS `NOTE`,
S_STUDENT_TRACK_CHANGES.CHANGED_ON,
S_STUDENT_TRACK_CHANGES.CHANGED_BY
FROM
`S_STUDENT_TRACK_CHANGES`
LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_TRACK_CHANGES.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER
LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_TRACK_CHANGES.PK_STUDENT_ENROLLMENT
LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT
LEFT JOIN S_CAMPUS ON S_STUDENT_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS
LEFT JOIN Z_ACCOUNT ON S_STUDENT_TRACK_CHANGES.PK_ACCOUNT = Z_ACCOUNT.PK_ACCOUNT
LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_TRACK_CHANGES.PK_STUDENT_MASTER 
WHERE
`FIELD_NAME` LIKE 'Program' 
AND OLD_VALUE != '' 
AND NEW_VALUE = '' 
AND S_STUDENT_TRACK_CHANGES.PK_ACCOUNT = 81

ORDER BY
S_STUDENT_TRACK_CHANGES.PK_ACCOUNT;";
// echo $field_changed_sql;
$field_changed_data = $db->Execute($field_changed_sql);

#HEADER OF TABLE
$HEADER = array_keys($field_changed_data->fields);
$EXCEL_DATA = [];
$EXCEL_DATA[] = ['*bold*' => $HEADER];
$TABLE = " <div class='row'><div class='col-sm-10'><table class='table table-bordered' ><thead><tr>";
foreach ($HEADER as $key => $value) {
    $TABLE .= "<th>$value</th>";
}
$TABLE .= "<thead><tr><tbody>";

while (!$field_changed_data->EOF) {
    $EXCEL_DATA_ROW = [];
    $NEW_VALUE_TR = '';
    $TABLE .= "<tr>";
    foreach ($field_changed_data->fields as $key1 => $value1) {

        if ($key1 == 'OLD_VALUE') {
            $cleansed = str_replace(array("\r\n", "\n", "\r"), '', $value1);
            // echo "<hr><br>";
            // echo "<pre>$cleansed</pre>";
            // echo "<hr><br>";
            $PK_ACCOUNT = $field_changed_data->fields['PK_ACCOUNT'];
            $RECOVERY_SQL = "SELECT * ,  CONCAT(CODE , ' - ', DESCRIPTION) AS PROGRAM_STR FROM M_CAMPUS_PROGRAM WHERE CONCAT(CODE , ' - ', DESCRIPTION) = '$value1' AND PK_ACCOUNT = $PK_ACCOUNT";
            $RECOVERY = $db->Execute($RECOVERY_SQL);
            if ($RECOVERY->RecordCount() == 0) {
                $RECOVERY_SQL = "SELECT * ,  CONCAT(CODE , ' - ', DESCRIPTION) AS PROGRAM_STR FROM M_CAMPUS_PROGRAM WHERE CONCAT(CODE , ' - ', DESCRIPTION) = '$cleansed' AND PK_ACCOUNT = $PK_ACCOUNT";
                $RECOVERY = $db->Execute($RECOVERY_SQL);
            }

            if ($RECOVERY->RecordCount() > 1) {
                $NEW_VALUE_TR .= "<b style='color : red'>MORE THAN 1 RECORD FOUND</b>" . $RECOVERY_SQL;
            }
            if ($RECOVERY->RecordCount() == 0) {
                $NEW_VALUE_TR .= "<b style='color : red'>Progrm do not exist</b>" . $RECOVERY_SQL;
            }
            $RECOVERY = $db->Execute($RECOVERY_SQL);
            $field_changed_data->fields['NEW_VALUE']
                = $RECOVERY->fields['PK_CAMPUS_PROGRAM'];
            $field_changed_data->fields['NOTE'] =  $RECOVERY->fields['PROGRAM_STR'];
            $NEW_VALUE_TR .=  $field_changed_data->fields['NEW_VALUE']
                . ' <br>' . $RECOVERY->fields['PROGRAM_STR']
                . '<br>' . $RECOVERY_SQL;
        }
        if ($key1 == 'NEW_VALUE') {
            $value1 = "<pre>" . $NEW_VALUE_TR . "</pre>";
        }
        if ($key1 == 'CHANGED_ON') {

            if ($RECOVERY->fields['PROGRAM_STR'] != '') {


                $UPDATE_QUERY = '';
                $UPDATE_PK = $RECOVERY->fields['PK_CAMPUS_PROGRAM'] ?? '';
                $UPDATE_PK_STUDENT_ENROLLMENT = $field_changed_data->fields['PK_STUDENT_ENROLLMENT'];
                $UPDATE_QUERY = "UPDATE S_STUDENT_ENROLLMENT SET PK_CAMPUS_PROGRAM = '$UPDATE_PK' WHERE PK_STUDENT_ENROLLMENT = $UPDATE_PK_STUDENT_ENROLLMENT AND $UPDATE_PK_STUDENT_ENROLLMENT != '' AND $UPDATE_PK != ''";

                $field_changed_data->fields['CHANGED_ON'] = $UPDATE_QUERY;
            }
        }

        $TABLE .= "<td>$value1</td>";
    }


    $TABLE .= "</tr>";

    foreach ($field_changed_data->fields as $key2 => $value2) {
        $EXCEL_DATA_ROW[] = $value2;
    }
    $EXCEL_DATA[] = $EXCEL_DATA_ROW;
    $field_changed_data->MoveNext();
}
$TABLE .= "</tbody></table></div></div>";

echo $TABLE;




$file_name = 'RECOVERY.xlsx';
$outputFileName = $file_name;
$outputFileName = str_replace(
    pathinfo($outputFileName, PATHINFO_FILENAME),
    pathinfo($outputFileName, PATHINFO_FILENAME) . "_" . $_SESSION['PK_USER'] . "_" . floor(microtime(true) * 1000),
    $outputFileName
);
$output = CustomExcelGenerator::makecustom('Excel2007', 'temp/', $outputFileName, $EXCEL_DATA, $header);
// dd("File Generated ", $output);
// header('Content-Type: application/json; charset=utf-8');
$response["file_name"] = $outputFileName;
$response["path"] =  $output;
echo json_encode($response);
