<?php

// Database connection details
$host = 'diamondsis-d3-prod-instance-1.cj8aalmd5rsa.us-east-1.rds.amazonaws.com';
$user = 'root';
$password = 'DSISMySQLPa$$1!';
$database = 'DSIS';

// Connect to the database
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Tables to export
$tables = ['S_COMPANY', 'S_COMPANY_CAMPUS','S_COMPANY_CONTACT'];
$account_id = 84;

foreach ($tables as $table) {
    // Execute query with PK_ACCOUNT filter
    $query = "SELECT * FROM $table WHERE PK_ACCOUNT = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Prepare SQL file content
    $sql_file_content = "";
    while ($row = $result->fetch_assoc()) {
        $values = array_map(function($value) use ($conn) {
            return is_null($value) ? "NULL" : "'" . $conn->real_escape_string($value) . "'";
        }, $row);
        
        $sql_file_content .= "INSERT INTO $table (" . implode(", ", array_keys($row)) . ") VALUES (" . implode(", ", $values) . ");\n";
    }
    
    // Write to SQL file
    $filename = '/var/www/mysql_backup/mysql_tables/'.$table . "_account_" . $account_id . ".sql";
    file_put_contents($filename, $sql_file_content);
    
    // Free result and close statement
    $result->free();
    $stmt->close();
}

// Close connection
$conn->close();

echo "SQL files generated successfully.";

?>
