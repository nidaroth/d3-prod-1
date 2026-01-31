<?php

echo "only enabled by developers"; exit;
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "DSIS";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Tables to query
$tables = array(
    'S_SELECT_STUDENT_FILTER',
    'S_SELECT_STUDENT_FILTER_INFO_TAB',
    'S_SELECT_STUDENT_FILTER_ENROLLMENT_TAB',
    'S_SELECT_STUDENT_FILTER_ACTIVITIES_TAB',
    'S_SELECT_STUDENT_FILTER_REQUIREMENT_TAB',
    'S_SELECT_STUDENT_FILTER_OTHER_EDU_TAB',
    'S_SELECT_STUDENT_FILTER_DISBURSEMENT_TAB',
    'S_SELECT_STUDENT_FILTER_ESTIMATED_FEES_TAB',
    'S_SELECT_STUDENT_FILTER_COMPANY_TAB',
    'S_SELECT_STUDENT_FILTER_LEDGER_TAB',
    'S_SELECT_STUDENT_FILTER_STUDENT_JOB_TAB'
);

// Iterate through tables
foreach ($tables as $table) {
    $sql = "SHOW COLUMNS FROM " . $table;
    $result = $conn->query($sql);

    // Check if query returned results
    if ($result->num_rows > 0) {
        $stmt = "ALTER TABLE " . $table . " <br/>";
        while ($row = $result->fetch_assoc()) {
            $columnName = $row["Field"];
            $columnType = $row["Type"];
            $columnKey = $row["Key"];
            
            // Check if column is not of type DATE and not a primary key
            if (strpos($columnType, 'date') === false && $columnKey !== 'PRI' && $columnName != 'PK_ACCOUNT' && $columnName != 'PK_USER' && $columnName != 'PK_SELECT_STUDENT_FILTER') {
                $stmt .= "MODIFY COLUMN " . $columnName . " TEXT, <br/>";
            }
        }
        $stmt[-7] = ';';
        echo $stmt."<br/>";
    } else {
        echo "0 results for table " . $table . "<br/>";
    }
}

$conn->close();
?>
