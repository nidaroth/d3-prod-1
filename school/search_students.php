<?php
require_once("../global/config.php");
require_once("../language/common.php");
require_once("check_access.php");

// Headers para JSON
header('Content-Type: application/json; charset=utf-8');

if(check_access('MANAGEMENT_FINANCE') == 0 ){
    echo json_encode(array('total' => 0, 'rows' => array(), 'error' => 'Access denied'));
    exit;
}

// VALIDACIÓN ESPECÍFICA: Solo PK_ACCOUNT = 72
if($_SESSION['PK_ACCOUNT'] != 72) {
    echo json_encode(array('total' => 0, 'rows' => array(), 'error' => 'Account not authorized'));
    exit;
}

try {
    $SEARCH = isset($_REQUEST['SEARCH']) ? trim($_REQUEST['SEARCH']) : '';
    
    error_log("Search term received: " . $SEARCH);
    
    $result = array();
    
    // Validar longitud mínima de búsqueda
    if(strlen($SEARCH) < 2) {
        echo json_encode(array('total' => 0, 'rows' => array(), 'message' => 'Search term too short'));
        exit;
    }
    
    // Escapar el término de búsqueda
    $SEARCH = mysql_real_escape_string($SEARCH);
    
    // ESPECÍFICO: Solo buscar en PK_ACCOUNT = 72
    $where = " S_STUDENT_MASTER.PK_ACCOUNT = 72 AND S_STUDENT_MASTER.ACTIVE = 1 AND S_STUDENT_MASTER.ARCHIVED = 0 ";
    $where .= " AND (S_STUDENT_MASTER.FIRST_NAME LIKE '%$SEARCH%' OR S_STUDENT_MASTER.LAST_NAME LIKE '%$SEARCH%') ";
    
    // Contar total de registros
    $count_query = "SELECT COUNT(*) as total FROM S_STUDENT_MASTER WHERE " . $where;
    $rs_count = mysql_query($count_query);
    
    if (!$rs_count) {
        throw new Exception(mysql_error());
    }
    
    $count_row = mysql_fetch_array($rs_count);
    $result["total"] = intval($count_row['total']);
    
    // LÍMITE: 30 resultados
    $query = "SELECT 
                    S_STUDENT_MASTER.PK_STUDENT_MASTER, 
                    S_STUDENT_MASTER.FIRST_NAME, 
                    S_STUDENT_MASTER.LAST_NAME, 
                    S_STUDENT_MASTER.MIDDLE_NAME,
                    S_STUDENT_MASTER.SSN,
                    S_STUDENT_MASTER.DATE_OF_BIRTH,
                    S_STUDENT_ACADEMICS.STUDENT_ID
                FROM 
                    S_STUDENT_MASTER 
                    LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER
                WHERE 
                    " . $where . " 
                ORDER BY 
                    S_STUDENT_MASTER.FIRST_NAME ASC, S_STUDENT_MASTER.LAST_NAME ASC
                LIMIT 30";
    
    error_log("Executing query: " . $query);
    
    $rs = mysql_query($query);
    
    if (!$rs) {
        throw new Exception(mysql_error());
    }
    
    $items = array();
    while($row = mysql_fetch_array($rs)){
        
        // Desencriptar SSN para mostrar solo los últimos 4 dígitos
        $ssn_display = '';
        if($row['SSN'] != '') {
            try {
                // ESPECÍFICO: Usar PK_ACCOUNT = 72 para desencriptación
                $ssn_decrypted = my_decrypt('72' . $row['PK_STUDENT_MASTER'], $row['SSN']);
                if(strlen($ssn_decrypted) >= 4) {
                    $ssn_display = 'xxx-xx-' . substr($ssn_decrypted, -4);
                } else {
                    $ssn_display = 'xxx-xx-xxxx';
                }
            } catch(Exception $e) {
                $ssn_display = 'xxx-xx-xxxx';
                error_log("SSN decryption error: " . $e->getMessage());
            }
        }
        
        // Formatear fecha de nacimiento
        $dob_display = '';
        if($row['DATE_OF_BIRTH'] != '0000-00-00' && $row['DATE_OF_BIRTH'] != '' && $row['DATE_OF_BIRTH'] != null) {
            $dob_display = date('m/d/Y', strtotime($row['DATE_OF_BIRTH']));
        }
        
        // Construir nombre completo
        $full_name_parts = array();
        if(!empty($row['FIRST_NAME'])) $full_name_parts[] = $row['FIRST_NAME'];
        if(!empty($row['MIDDLE_NAME'])) $full_name_parts[] = $row['MIDDLE_NAME'];
        if(!empty($row['LAST_NAME'])) $full_name_parts[] = $row['LAST_NAME'];
        $full_name = implode(' ', $full_name_parts);
        
        $student_data = array(
            'PK_STUDENT_MASTER' => intval($row['PK_STUDENT_MASTER']),
            'FIRST_NAME' => $row['FIRST_NAME'] ?: '',
            'LAST_NAME' => $row['LAST_NAME'] ?: '',
            'MIDDLE_NAME' => $row['MIDDLE_NAME'] ?: '',
            'FULL_NAME' => $full_name,
            'SSN_DISPLAY' => $ssn_display,
            'DOB_DISPLAY' => $dob_display,
            'STUDENT_ID' => $row['STUDENT_ID'] ?: ''
        );
        
        $items[] = $student_data;
    }
    
    $result["rows"] = $items;
    
    error_log("Search completed. Found " . count($items) . " students");
    
    echo json_encode($result);
    
} catch(Exception $e) {
    error_log("Error in search_students.php: " . $e->getMessage());
    echo json_encode(array(
        'total' => 0,
        'rows' => array(),
        'error' => 'Database error: ' . $e->getMessage()
    ));
}
?>

