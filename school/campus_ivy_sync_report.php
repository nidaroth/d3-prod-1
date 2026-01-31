<?php
require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("check_access.php");

// Solo para PK_ACCOUNT 63 (FEI)
if($_SESSION['PK_ACCOUNT'] != 63 || check_access('REPORT_FINANCE') == 0){
    header("location:../index");
    exit;
}

// Procesar filtros si se envía el formulario
$where_conditions = array("LOG.PK_ACCOUNT = 63", "LOG.STATUS != 'skipped'");
$date_filter = "";

if(!empty($_POST)){
    
    // Filtro de fechas
    if(!empty($_POST['START_DATE']) && !empty($_POST['END_DATE'])){
        $start_date = date('Y-m-d', strtotime($_POST['START_DATE']));
        $end_date = date('Y-m-d', strtotime($_POST['END_DATE']));
        $where_conditions[] = "DATE(LOG.SYNC_DATE) BETWEEN '$start_date' AND '$end_date'";
        $date_filter = " from " . $_POST['START_DATE'] . " to " . $_POST['END_DATE'];
    } else if(!empty($_POST['START_DATE'])){
        $start_date = date('Y-m-d', strtotime($_POST['START_DATE']));
        $where_conditions[] = "DATE(LOG.SYNC_DATE) >= '$start_date'";
        $date_filter = " from " . $_POST['START_DATE'];
    } else if(!empty($_POST['END_DATE'])){
        $end_date = date('Y-m-d', strtotime($_POST['END_DATE']));
        $where_conditions[] = "DATE(LOG.SYNC_DATE) <= '$end_date'";
        $date_filter = " until " . $_POST['END_DATE'];
    }
    
    // Filtro de tipo de transacción
    if(!empty($_POST['TRANSACTION_TYPE'])){
        $where_conditions[] = "LOG.TRANSACTION_TYPE = '".$_POST['TRANSACTION_TYPE']."'";
    }
    
    // Filtro de estudiante
    if(!empty($_POST['PK_STUDENT_MASTER'])){
        $where_conditions[] = "LOG.PK_STUDENT_MASTER = '".$_POST['PK_STUDENT_MASTER']."'";
    }
    
    // Filtro de status
    if(!empty($_POST['STATUS'])){
        $where_conditions[] = "LOG.STATUS = '".$_POST['STATUS']."'";
    }
    
    // Filtro de refunds
    if(isset($_POST['IS_REFUND']) && $_POST['IS_REFUND'] != ''){
        $where_conditions[] = "LOG.IS_REFUND = '".$_POST['IS_REFUND']."'";
    }
    
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
    
    // GENERAR PDF
    if($_POST['FORMAT'] == 1){
        require_once '../global/mpdf/vendor/autoload.php';
        
        // Obtener datos
        $query = "SELECT 
            LOG.PK_IVY_SYNC_LOG,
            LOG.SYNC_DATE,
            LOG.TRANSACTION_TYPE,
            LOG.TRANSACTION_ID,
            CONCAT(SM.FIRST_NAME, ' ', SM.LAST_NAME) AS STUDENT_NAME,
            SM.PK_STUDENT_MASTER,
            SSA.STUDENT_ID,
            LOG.PK_STUDENT_ENROLLMENT,
            LOG.AMOUNT,
            LOG.STATUS,
            LOG.ACTION_TAKEN,
            LOG.BATCH_NO,
            LOG.IS_REFUND,
            LOG.REFUND_TYPE,
            LOG.ERROR_MESSAGE,
            LOG.RELATED_PK_STUDENT_LEDGER,
            ALC.CODE AS LEDGER_CODE,
            ALC.LEDGER_DESCRIPTION,
            CASE 
                WHEN LOG.TRANSACTION_TYPE IN ('award', 'disbursement', 'refund') THEN 'Awards'
                WHEN LOG.TRANSACTION_TYPE IN ('disbursement_update', 'refund_update', 'batch_detail') THEN 'Batch Detail'
                ELSE 'Other'
            END AS ENDPOINT
        FROM S_IVY_SYNC_LOG LOG
        LEFT JOIN S_STUDENT_MASTER SM ON LOG.PK_STUDENT_MASTER = SM.PK_STUDENT_MASTER
        LEFT JOIN S_STUDENT_ACADEMICS SSA ON SM.PK_STUDENT_MASTER = SSA.PK_STUDENT_MASTER
        LEFT JOIN S_STUDENT_LEDGER SL ON LOG.RELATED_PK_STUDENT_LEDGER = SL.PK_STUDENT_LEDGER
        LEFT JOIN M_AR_LEDGER_CODE ALC ON SL.PK_AR_LEDGER_CODE = ALC.PK_AR_LEDGER_CODE
        $where_clause
        ORDER BY LOG.SYNC_DATE DESC
        LIMIT 1000";
        
        $res_data = $db->Execute($query);
        
        $all_data = array();
        $total_amount = 0;
        $total_refunds = 0;
        
        while(!$res_data->EOF){
            $row = $res_data->fields;
            
            if($row['IS_REFUND'] == 1){
                $total_refunds += abs($row['AMOUNT']);
            } else {
                $total_amount += $row['AMOUNT'];
            }
            
            $all_data[] = $row;
            $res_data->MoveNext();
        }
        
        // Configurar PDF
        $res = $db->Execute("SELECT PDF_LOGO, SCHOOL_NAME FROM Z_ACCOUNT WHERE PK_ACCOUNT = 63");
        $SCHOOL_NAME = $res->fields['SCHOOL_NAME'];
        $PDF_LOGO = $res->fields['PDF_LOGO'];
        
        $logo = "";
        if($PDF_LOGO != '')
            $logo = '<img src="'.$PDF_LOGO.'" height="50px" />';
        
        $header = '<table width="100%">
            <tr>
                <td width="20%" valign="top">'.$logo.'</td>
                <td width="50%" valign="top" style="font-size:20px">'.$SCHOOL_NAME.'</td>
                <td width="30%" valign="top">
                    <table width="100%">
                        <tr>
                            <td width="100%" align="right" style="font-size:18px;border-bottom:1px solid #000;"><b>Campus Ivy Sync Report</b></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="3" width="100%" align="right" style="font-size:13px;">'.$date_filter.'</td>
            </tr>
            <tr>
                <td colspan="3" width="100%" align="right" style="font-size:13px;">Total Records: '.count($all_data).'</td>
            </tr>
        </table>';
        
        $date = date('l, F d, Y h:i A');
        
        $footer = '<table width="100%">
            <tr>
                <td width="33%" valign="top" style="font-size:10px;"><i>'.$date.'</i></td>
                <td width="33%" valign="top" style="font-size:10px;" align="center"></td>
                <td width="33%" valign="top" style="font-size:10px;" align="right"><i>Page {PAGENO} of {nb}</i></td>
            </tr>
        </table>';
        
        $mpdf = new \Mpdf\Mpdf([
            'margin_left' => 7,
            'margin_right' => 5,
            'margin_top' => 38,
            'margin_bottom' => 15,
            'margin_header' => 3,
            'margin_footer' => 10,
            'default_font_size' => 6,
            'format' => [210, 296],
            'orientation' => 'L'
        ]);
        
        $mpdf->autoPageBreak = true;
        $mpdf->SetHTMLHeader($header);
        $mpdf->SetHTMLFooter($footer);
        
        $txt = '<table border="0" cellspacing="0" cellpadding="2" width="100%">
            <thead>
                <tr>
                    <td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;font-size:6px">Date/Time</td>
                    <td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;font-size:6px">Endpoint</td>
                    <td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;font-size:6px">Type</td>
                    <td width="12%" style="border-top:1px solid #000;border-bottom:1px solid #000;font-size:6px">Student</td>
                    <td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;font-size:6px">Student ID</td>
                    <td width="6%" style="border-top:1px solid #000;border-bottom:1px solid #000;font-size:6px">Trans ID</td>
                    <td width="10%" style="border-top:1px solid #000;border-bottom:1px solid #000;font-size:6px">Ledger Code</td>
                    <td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;font-size:6px">Amount</td>
                    <td width="6%" style="border-top:1px solid #000;border-bottom:1px solid #000;font-size:6px">Status</td>
                    <td width="10%" style="border-top:1px solid #000;border-bottom:1px solid #000;font-size:6px">Batch No</td>
                    <td width="18%" style="border-top:1px solid #000;border-bottom:1px solid #000;font-size:6px">Error</td>
                </tr>
            </thead>';
        
        foreach($all_data as $row){
            $amount_display = '$' . number_format(abs($row['AMOUNT']), 2);
            if($row['IS_REFUND'] == 1){
                $amount_display = '<span style="color:red;">($' . number_format(abs($row['AMOUNT']), 2) . ')</span>';
            }
            
            $status_color = ($row['STATUS'] == 'success') ? 'green' : (($row['STATUS'] == 'error') ? 'red' : 'orange');
            
            $ledger_display = $row['LEDGER_CODE'] ? $row['LEDGER_CODE'] : '-';
            
            $txt .= '<tr>
                <td style="font-size:6px">'.date('m/d/Y H:i', strtotime($row['SYNC_DATE'])).'</td>
                <td style="font-size:6px"><b>'.$row['ENDPOINT'].'</b></td>
                <td style="font-size:6px">'.$row['TRANSACTION_TYPE'].'</td>
                <td style="font-size:6px">'.$row['STUDENT_NAME'].'</td>
                <td style="font-size:6px">'.$row['STUDENT_ID'].'</td>
                <td style="font-size:6px">'.$row['TRANSACTION_ID'].'</td>
                <td style="font-size:6px">'.$ledger_display.'</td>
                <td style="font-size:6px">'.$amount_display.'</td>
                <td style="font-size:6px;color:'.$status_color.'"><b>'.$row['STATUS'].'</b></td>
                <td style="font-size:6px">'.$row['BATCH_NO'].'</td>
                <td style="font-size:6px">'.substr($row['ERROR_MESSAGE'], 0, 100).'</td>
            </tr>';
        }
        
        $txt .= '<tr>
            <td colspan="7" style="border-top:1px solid #000;font-size:7px;font-weight:bold;">TOTALS</td>
            <td style="border-top:1px solid #000;font-size:7px;font-weight:bold;">$'.number_format($total_amount - $total_refunds, 2).'</td>
            <td colspan="3" style="border-top:1px solid #000;font-size:7px;"></td>
        </tr>';
        
        $txt .= '</table>';
        
        $mpdf->WriteHTML($txt);
        $mpdf->Output('Campus_Ivy_Sync_Report.pdf', 'D');
        exit;
    }
    
    // GENERAR EXCEL
    else if($_POST['FORMAT'] == 2){
        include '../global/excel/Classes/PHPExcel/IOFactory.php';
        
        $cell1 = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
        define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
        
        $total_fields = 120;
        for($i = 0 ; $i <= $total_fields ; $i++){
            if($i <= 25)
                $cell[] = $cell1[$i];
            else {
                $j = floor($i / 26) - 1;
                $k = ($i % 26);
                $cell[] = $cell1[$j].$cell1[$k];
            }
        }
        
        $dir = 'temp/';
        $inputFileType = 'Excel2007';
        $file_name = 'Campus_Ivy_Sync_Report.xlsx';
        $outputFileName = $dir.$file_name;
        $outputFileName = str_replace(
            pathinfo($outputFileName,PATHINFO_FILENAME),
            pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
            $outputFileName
        );
        
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objReader->setIncludeCharts(TRUE);
        $objPHPExcel = new PHPExcel();
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        
        $line = 1;
        $index = -1;
        
        $headings = array('SYNC_DATE','ENDPOINT','TRANSACTION_TYPE','STUDENT_NAME','STUDENT_ID','PK_STUDENT_MASTER','PK_STUDENT_ENROLLMENT','TRANSACTION_ID','LEDGER_CODE','LEDGER_DESCRIPTION','AMOUNT','STATUS','ACTION_TAKEN','BATCH_NO','IS_REFUND','REFUND_TYPE','ERROR_MESSAGE');
        $widths = array(20,15,20,30,15,15,15,15,15,25,15,15,20,20,10,15,50);
        
        $i = 0;
        foreach($headings as $header) {
            $index++;
            $cell_no = $cell[$index].$line;
            $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($header);
            $objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($widths[$i]);
            $i++;
        }
        
        // Obtener datos
        $query = "SELECT 
            LOG.SYNC_DATE,
            CASE 
                WHEN LOG.TRANSACTION_TYPE IN ('award', 'disbursement', 'refund') THEN 'Awards'
                WHEN LOG.TRANSACTION_TYPE IN ('disbursement_update', 'refund_update', 'batch_detail') THEN 'Batch Detail'
                ELSE 'Other'
            END AS ENDPOINT,
            LOG.TRANSACTION_TYPE,
            CONCAT(SM.FIRST_NAME, ' ', SM.LAST_NAME) AS STUDENT_NAME,
            SSA.STUDENT_ID,
            LOG.PK_STUDENT_MASTER,
            LOG.PK_STUDENT_ENROLLMENT,
            LOG.TRANSACTION_ID,
            ALC.CODE AS LEDGER_CODE,
            ALC.LEDGER_DESCRIPTION,
            LOG.AMOUNT,
            LOG.STATUS,
            LOG.ACTION_TAKEN,
            LOG.BATCH_NO,
            LOG.IS_REFUND,
            LOG.REFUND_TYPE,
            LOG.ERROR_MESSAGE
        FROM S_IVY_SYNC_LOG LOG
        LEFT JOIN S_STUDENT_MASTER SM ON LOG.PK_STUDENT_MASTER = SM.PK_STUDENT_MASTER
        LEFT JOIN S_STUDENT_ACADEMICS SSA ON SM.PK_STUDENT_MASTER = SSA.PK_STUDENT_MASTER
        LEFT JOIN S_STUDENT_LEDGER SL ON LOG.RELATED_PK_STUDENT_LEDGER = SL.PK_STUDENT_LEDGER
        LEFT JOIN M_AR_LEDGER_CODE ALC ON SL.PK_AR_LEDGER_CODE = ALC.PK_AR_LEDGER_CODE
        $where_clause
        ORDER BY LOG.SYNC_DATE DESC
        LIMIT 1000";
        
        $res_data = $db->Execute($query);
        
        while(!$res_data->EOF){
            $line++;
            $index = -1;
            
            foreach($headings as $field) {
                $index++;
                $value = $res_data->fields[$field];
                
                // Formatear valores especiales
                if($field == 'AMOUNT'){
                    $value = number_format($value, 2);
                } else if($field == 'IS_REFUND'){
                    $value = ($value == 1) ? 'YES' : 'NO';
                }
                
                $objPHPExcel->getActiveSheet()->getCell($cell[$index].$line)->setValue($value);
            }
            
            $res_data->MoveNext();
        }
        
        $objWriter->save($outputFileName);
        $objPHPExcel->disconnectWorksheets();
        header("location:".$outputFileName);
        exit;
    }
}

// Obtener estadísticas generales (excluyendo skipped)
$stats_query = "SELECT 
    COUNT(*) as total_records,
    SUM(CASE WHEN STATUS = 'success' THEN 1 ELSE 0 END) as successful,
    SUM(CASE WHEN STATUS = 'error' THEN 1 ELSE 0 END) as errors,
    SUM(CASE WHEN STATUS = 'warning' THEN 1 ELSE 0 END) as warnings,
    SUM(CASE WHEN IS_REFUND = 1 THEN 1 ELSE 0 END) as total_refunds,
    SUM(CASE WHEN IS_REFUND = 0 AND STATUS = 'success' THEN AMOUNT ELSE 0 END) as total_amount,
    SUM(CASE WHEN IS_REFUND = 1 AND STATUS = 'success' THEN ABS(AMOUNT) ELSE 0 END) as total_refund_amount,
    MAX(SYNC_DATE) as last_sync
FROM S_IVY_SYNC_LOG
WHERE PK_ACCOUNT = 63
AND STATUS != 'skipped'";

$res_stats = $db->Execute($stats_query);
$stats = $res_stats->fields;

// Estadísticas por tipo de transacción (excluyendo skipped)
$trans_type_query = "SELECT 
    CASE 
        WHEN TRANSACTION_TYPE IN ('award', 'disbursement', 'refund') THEN 'Awards Endpoint'
        WHEN TRANSACTION_TYPE IN ('disbursement_update', 'refund_update', 'batch_detail') THEN 'Batch Detail Endpoint'
        ELSE 'Other'
    END AS ENDPOINT,
    TRANSACTION_TYPE,
    COUNT(*) as count,
    SUM(CASE WHEN STATUS = 'success' THEN 1 ELSE 0 END) as successful,
    SUM(CASE WHEN STATUS = 'error' THEN 1 ELSE 0 END) as errors,
    SUM(CASE WHEN IS_REFUND = 0 AND STATUS = 'success' THEN AMOUNT ELSE 0 END) as total_amount
FROM S_IVY_SYNC_LOG
WHERE PK_ACCOUNT = 63
AND STATUS != 'skipped'
GROUP BY ENDPOINT, TRANSACTION_TYPE
ORDER BY ENDPOINT, count DESC";

$res_trans_types = $db->Execute($trans_type_query);

// Últimas sincronizaciones para DataTable (excluyendo skipped, con ledger code)
$recent_query = "SELECT 
    LOG.PK_IVY_SYNC_LOG,
    LOG.SYNC_DATE,
    CASE 
        WHEN LOG.TRANSACTION_TYPE IN ('award', 'disbursement', 'refund') THEN 'Awards'
        WHEN LOG.TRANSACTION_TYPE IN ('disbursement_update', 'refund_update', 'batch_detail') THEN 'Batch Detail'
        ELSE 'Other'
    END AS ENDPOINT,
    LOG.TRANSACTION_TYPE,
    CONCAT(SM.FIRST_NAME, ' ', SM.LAST_NAME) AS STUDENT_NAME,
    SM.PK_STUDENT_MASTER,
    SSA.STUDENT_ID,
    LOG.PK_STUDENT_ENROLLMENT,
    LOG.TRANSACTION_ID,
    LOG.AMOUNT,
    LOG.STATUS,
    LOG.ACTION_TAKEN,
    LOG.IS_REFUND,
    LOG.REFUND_TYPE,
    LOG.BATCH_NO,
    LOG.RELATED_PK_STUDENT_LEDGER,
    LOG.ERROR_MESSAGE,
    ALC.CODE AS LEDGER_CODE,
    ALC.LEDGER_DESCRIPTION
FROM S_IVY_SYNC_LOG LOG
LEFT JOIN S_STUDENT_MASTER SM ON LOG.PK_STUDENT_MASTER = SM.PK_STUDENT_MASTER
LEFT JOIN S_STUDENT_ACADEMICS SSA ON SM.PK_STUDENT_MASTER = SSA.PK_STUDENT_MASTER
LEFT JOIN S_STUDENT_LEDGER SL ON LOG.RELATED_PK_STUDENT_LEDGER = SL.PK_STUDENT_LEDGER
LEFT JOIN M_AR_LEDGER_CODE ALC ON SL.PK_AR_LEDGER_CODE = ALC.PK_AR_LEDGER_CODE
WHERE LOG.PK_ACCOUNT = 63
AND LOG.STATUS != 'skipped'
ORDER BY LOG.SYNC_DATE DESC
LIMIT 500";

$res_recent = $db->Execute($recent_query);

// Convertir a JSON para DataTables
$recent_data = array();
while(!$res_recent->EOF){
    $row = $res_recent->fields;
    
    $recent_data[] = array(
        'sync_date' => date('m/d/Y H:i', strtotime($row['SYNC_DATE'])),
        'endpoint' => $row['ENDPOINT'],
        'transaction_type' => $row['TRANSACTION_TYPE'],
        'student_name' => $row['STUDENT_NAME'],
        'student_id' => $row['STUDENT_ID'],
        'pk_student_master' => $row['PK_STUDENT_MASTER'],
        'pk_student_enrollment' => $row['PK_STUDENT_ENROLLMENT'],
        'transaction_id' => $row['TRANSACTION_ID'],
        'ledger_code' => $row['LEDGER_CODE'] ? $row['LEDGER_CODE'] : '-',
        'ledger_description' => $row['LEDGER_DESCRIPTION'] ? $row['LEDGER_DESCRIPTION'] : '-',
        'amount' => $row['AMOUNT'],
        'status' => $row['STATUS'],
        'action_taken' => $row['ACTION_TAKEN'],
        'is_refund' => $row['IS_REFUND'],
        'refund_type' => $row['REFUND_TYPE'],
        'batch_no' => $row['BATCH_NO'],
        'error_message' => $row['ERROR_MESSAGE']
    );
    
    $res_recent->MoveNext();
}

$recent_json = json_encode($recent_data);

// Lista de estudiantes para filtro (excluyendo skipped)
$students_query = "SELECT DISTINCT 
    SM.PK_STUDENT_MASTER,
    CONCAT(SM.FIRST_NAME, ' ', SM.LAST_NAME) AS STUDENT_NAME
FROM S_IVY_SYNC_LOG LOG
INNER JOIN S_STUDENT_MASTER SM ON LOG.PK_STUDENT_MASTER = SM.PK_STUDENT_MASTER
WHERE LOG.PK_ACCOUNT = 63
AND LOG.STATUS != 'skipped'
ORDER BY SM.LAST_NAME, SM.FIRST_NAME
LIMIT 500";

$res_students = $db->Execute($students_query);
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
    
    <!-- DataTables CSS -->
    <link href="../backend_assets/node_modules/datatables.net-bs4/css/dataTables.bootstrap4.css" rel="stylesheet">
    <link href="../backend_assets/node_modules/datatables.net-bs4/css/responsive.dataTables.min.css" rel="stylesheet">
    
    <title>Campus Ivy Sync Report | <?=$title?></title>
    <style>
        .stat-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background: #fff;
        }
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #2962FF;
        }
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        .stat-success { color: #4CAF50 !important; }
        .stat-error { color: #F44336 !important; }
        .stat-warning { color: #FF9800 !important; }
        .stat-refund { color: #9C27B0 !important; }
        .badge-success { background-color: #4CAF50; padding: 5px 10px; }
        .badge-error { background-color: #F44336; padding: 5px 10px; }
        .badge-warning { background-color: #FF9800; padding: 5px 10px; }
        .badge-refund { background-color: #9C27B0; padding: 5px 10px; color: white; }
        .badge-awards { background-color: #2196F3; padding: 5px 10px; color: white; }
        .badge-batch { background-color: #607D8B; padding: 5px 10px; color: white; }
        .amount-negative { color: #F44336; font-weight: bold; }
        .amount-positive { color: #4CAF50; font-weight: bold; }
        table.dataTable tbody tr {
            cursor: pointer;
        }
        table.dataTable tbody tr:hover {
            background-color: #f5f5f5;
        }
        .dataTables_wrapper .dataTables_filter input {
            margin-left: 0.5em;
            border: 1px solid #ddd;
            padding: 5px;
        }
        .tooltip-error {
            cursor: help;
            border-bottom: 1px dotted #999;
        }
    </style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-8 align-self-center">
                        <h4 class="text-themecolor">
                            <i class="fa fa-cloud"></i> Campus Ivy Synchronization Report
                        </h4>
                        <small class="text-muted">Real-time monitoring of Campus Ivy integrations</small>
                    </div>
                    <div class="col-md-4 align-self-center text-right">
                        <small class="text-muted"><i class="fa fa-clock-o"></i> Last sync: <?=date('m/d/Y H:i', strtotime($stats['last_sync']))?></small>
                    </div>
                </div>

                <!-- ESTADÍSTICAS GENERALES -->
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card">
                            <div class="stat-number"><?=number_format($stats['total_records'])?></div>
                            <div class="stat-label"><i class="fa fa-database"></i> Total Records</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card">
                            <div class="stat-number stat-success"><?=number_format($stats['successful'])?></div>
                            <div class="stat-label"><i class="fa fa-check-circle"></i> Successful</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card">
                            <div class="stat-number stat-error"><?=number_format($stats['errors'])?></div>
                            <div class="stat-label"><i class="fa fa-exclamation-triangle"></i> Errors</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card">
                            <div class="stat-number stat-refund"><?=number_format($stats['total_refunds'])?></div>
                            <div class="stat-label"><i class="fa fa-undo"></i> Refunds</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="stat-card">
                            <div class="stat-number stat-success">$<?=number_format($stats['total_amount'], 2)?></div>
                            <div class="stat-label"><i class="fa fa-arrow-down"></i> Total Disbursed</div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="stat-card">
                            <div class="stat-number stat-refund">$<?=number_format($stats['total_refund_amount'], 2)?></div>
                            <div class="stat-label"><i class="fa fa-arrow-up"></i> Total Refunded</div>
                        </div>
                    </div>
                </div>

                <!-- ESTADÍSTICAS POR TIPO Y ENDPOINT -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fa fa-pie-chart"></i> Statistics by Endpoint & Transaction Type</h5>
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Endpoint</th>
                                            <th>Transaction Type</th>
                                            <th>Total</th>
                                            <th>Successful</th>
                                            <th>Errors</th>
                                            <th>Amount</th>
                                            <th>Success Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while(!$res_trans_types->EOF) {
                                            $success_rate = ($res_trans_types->fields['count'] > 0) ?
                                                           ($res_trans_types->fields['successful'] / $res_trans_types->fields['count'] * 100) : 0;
                                            $rate_class = ($success_rate >= 95) ? 'text-success' : (($success_rate >= 80) ? 'text-warning' : 'text-danger');
                                        ?>
                                        <tr>
                                            <td>
                                                <?php if($res_trans_types->fields['ENDPOINT'] == 'Awards Endpoint') { ?>
                                                    <span class="badge badge-awards">Awards</span>
                                                <?php } else { ?>
                                                    <span class="badge badge-batch">Batch Detail</span>
                                                <?php } ?>
                                            </td>
                                            <td><?=$res_trans_types->fields['TRANSACTION_TYPE']?></td>
                                            <td><strong><?=number_format($res_trans_types->fields['count'])?></strong></td>
                                            <td class="text-success"><?=number_format($res_trans_types->fields['successful'])?></td>
                                            <td class="text-danger"><?=number_format($res_trans_types->fields['errors'])?></td>
                                            <td class="amount-positive">$<?=number_format($res_trans_types->fields['total_amount'], 2)?></td>
                                            <td class="<?=$rate_class?>"><strong><?=number_format($success_rate, 1)?>%</strong></td>
                                        </tr>
                                        <?php $res_trans_types->MoveNext(); } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABLA DE SINCRONIZACIONES RECIENTES CON DATATABLES -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fa fa-list"></i> Recent Synchronizations
                                    <span class="badge badge-info"><?=count($recent_data)?> records</span>
                                </h5>
                                <p class="text-muted">
                                    <i class="fa fa-info-circle"></i> Click on any row for more details. Use search and filters to find specific transactions.
                                </p>
                                
                                <table id="syncTable" class="table table-striped table-bordered table-hover display responsive nowrap" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Date/Time</th>
                                            <th>Endpoint</th>
                                            <th>Type</th>
                                            <th>Student</th>
                                            <th>Student ID</th>
                                            <th>Trans ID</th>
                                            <th>Ledger Code</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Batch</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Populated by DataTables -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FILTROS Y GENERACIÓN DE REPORTES -->
                <form class="floating-labels" method="post" name="form1" id="form1" autocomplete="off">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fa fa-filter"></i> Generate Detailed Report (PDF/Excel)</h5>
                                    
                                    <div class="row form-group">
                                        <div class="col-md-3">
                                            <label>Start Date</label>
                                            <input type="text" class="form-control date" id="START_DATE" name="START_DATE" value="">
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <label>End Date</label>
                                            <input type="text" class="form-control date" id="END_DATE" name="END_DATE" value="">
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <label>Transaction Type</label>
                                            <select id="TRANSACTION_TYPE" name="TRANSACTION_TYPE" class="form-control">
                                                <option value="">All Types</option>
                                                <option value="disbursement">Disbursement</option>
                                                <option value="refund">Refund</option>
                                                <option value="disbursement_update">Disbursement Update</option>
                                                <option value="refund_update">Refund Update</option>
                                                <option value="award">Award</option>
                                                <option value="batch_detail">Batch Detail</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <label>Status</label>
                                            <select id="STATUS" name="STATUS" class="form-control">
                                                <option value="">All Status</option>
                                                <option value="success">Success</option>
                                                <option value="error">Error</option>
                                                <option value="warning">Warning</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row form-group">
                                        <div class="col-md-4">
                                            <label>Student</label>
                                            <select id="PK_STUDENT_MASTER" name="PK_STUDENT_MASTER" class="form-control select2">
                                                <option value="">All Students</option>
                                                <?php while(!$res_students->EOF) { ?>
                                                <option value="<?=$res_students->fields['PK_STUDENT_MASTER']?>">
                                                    <?=$res_students->fields['STUDENT_NAME']?>
                                                </option>
                                                <?php $res_students->MoveNext(); } ?>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <label>Refunds Only</label>
                                            <select id="IS_REFUND" name="IS_REFUND" class="form-control">
                                                <option value="">All Transactions</option>
                                                <option value="1">Refunds Only</option>
                                                <option value="0">No Refunds</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-5" style="padding-top: 30px;">
                                            <button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-danger">
                                                <i class="fa fa-file-pdf-o"></i> Generate PDF
                                            </button>
                                            <button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-success">
                                                <i class="fa fa-file-excel-o"></i> Generate Excel
                                            </button>
                                            <input type="hidden" name="FORMAT" id="FORMAT">
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-info">
                                        <i class="fa fa-info-circle"></i> <strong>Note:</strong> Exported reports are limited to 1,000 records. Use filters to narrow results.
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
    
    <!-- DataTables -->
    <script src="../backend_assets/node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../backend_assets/node_modules/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="../backend_assets/node_modules/datatables.net-bs4/js/dataTables.responsive.min.js"></script>
    
    <script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="../backend_assets/node_modules/select2/dist/css/select2.min.css" rel="stylesheet" type="text/css" />
    <script src="../backend_assets/node_modules/select2/dist/js/select2.full.min.js"></script>

    <script type="text/javascript">
    var recentData = <?=$recent_json?>;
    
    jQuery(document).ready(function($) {
        // Inicializar DatePicker
        jQuery('.date').datepicker({
            todayHighlight: true,
            orientation: "bottom auto",
            format: 'mm/dd/yyyy'
        });
        
        // Inicializar Select2
        $('.select2').select2({
            placeholder: "Select a student",
            allowClear: true
        });
        
        // Inicializar DataTable
        var table = $('#syncTable').DataTable({
            data: recentData,
            columns: [
                { data: 'sync_date' },
                {
                    data: 'endpoint',
                    render: function(data, type, row) {
                        if(data == 'Awards') {
                            return '<span class="badge badge-awards">Awards</span>';
                        } else if(data == 'Batch Detail') {
                            return '<span class="badge badge-batch">Batch Detail</span>';
                        }
                        return data;
                    }
                },
                { data: 'transaction_type' },
                { data: 'student_name' },
                { data: 'student_id' },
                { data: 'transaction_id' },
                {
                    data: 'ledger_code',
                    render: function(data, type, row) {
                        if(data && data != '-') {
                            return '<span title="' + row.ledger_description + '">' + data + '</span>';
                        }
                        return '-';
                    }
                },
                {
                    data: 'amount',
                    render: function(data, type, row) {
                        if(row.is_refund == 1) {
                            return '<span class="amount-negative">($' + Math.abs(data).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') + ')</span>';
                        }
                        return '<span class="amount-positive">$' + parseFloat(data).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') + '</span>';
                    }
                },
                {
                    data: 'status',
                    render: function(data, type, row) {
                        var badgeClass = 'badge-secondary';
                        if(data == 'success') badgeClass = 'badge-success';
                        else if(data == 'error') badgeClass = 'badge-error';
                        else if(data == 'warning') badgeClass = 'badge-warning';
                        
                        var refundBadge = '';
                        if(row.is_refund == 1) {
                            refundBadge = '<span class="badge badge-refund">REFUND</span> ';
                        }
                        
                        return refundBadge + '<span class="badge ' + badgeClass + '">' + data.toUpperCase() + '</span>';
                    }
                },
                { data: 'batch_no' }
            ],
            order: [[0, 'desc']],
            pageLength: 25,
            responsive: true,
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'print'
            ],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });
        
        // Hacer clickeable las filas para mostrar detalles
        $('#syncTable tbody').on('click', 'tr', function () {
            var data = table.row(this).data();
            if(data) {
                showDetails(data);
            }
        });
    });
    
    function showDetails(data) {
        var html = '<div class="modal fade" id="detailModal" tabindex="-1" role="dialog">';
        html += '<div class="modal-dialog modal-lg" role="document">';
        html += '<div class="modal-content">';
        html += '<div class="modal-header">';
        html += '<h5 class="modal-title">Transaction Details - ' + data.transaction_id + '</h5>';
        html += '<button type="button" class="close" data-dismiss="modal" aria-label="Close">';
        html += '<span aria-hidden="true">&times;</span>';
        html += '</button>';
        html += '</div>';
        html += '<div class="modal-body">';
        
        html += '<table class="table table-bordered">';
        html += '<tr><th width="30%">Sync Date</th><td>' + data.sync_date + '</td></tr>';
        html += '<tr><th>Endpoint</th><td><span class="badge ' + (data.endpoint == 'Awards' ? 'badge-awards' : 'badge-batch') + '">' + data.endpoint + '</span></td></tr>';
        html += '<tr><th>Transaction Type</th><td>' + data.transaction_type + '</td></tr>';
        html += '<tr><th>Student</th><td>' + data.student_name + ' (ID: ' + data.student_id + ')</td></tr>';
        html += '<tr><th>Student Master PK</th><td>' + data.pk_student_master + '</td></tr>';
        html += '<tr><th>Enrollment PK</th><td>' + data.pk_student_enrollment + '</td></tr>';
        html += '<tr><th>Transaction ID</th><td>' + data.transaction_id + '</td></tr>';
        html += '<tr><th>Ledger Code</th><td>' + data.ledger_code + (data.ledger_description && data.ledger_description != '-' ? ' - ' + data.ledger_description : '') + '</td></tr>';
        
        var amountDisplay = '$' + Math.abs(data.amount).toFixed(2);
        if(data.is_refund == 1) {
            amountDisplay = '<span class="amount-negative">($' + Math.abs(data.amount).toFixed(2) + ') - REFUND</span>';
            if(data.refund_type) {
                amountDisplay += '<br><small>Type: ' + data.refund_type + '</small>';
            }
        }
        html += '<tr><th>Amount</th><td>' + amountDisplay + '</td></tr>';
        
        var statusBadge = 'badge-secondary';
        if(data.status == 'success') statusBadge = 'badge-success';
        else if(data.status == 'error') statusBadge = 'badge-error';
        html += '<tr><th>Status</th><td><span class="badge ' + statusBadge + '">' + data.status.toUpperCase() + '</span></td></tr>';
        
        html += '<tr><th>Action Taken</th><td>' + data.action_taken + '</td></tr>';
        html += '<tr><th>Batch Number</th><td>' + data.batch_no + '</td></tr>';
        
        if(data.error_message) {
            html += '<tr><th>Error Message</th><td><span class="text-danger">' + data.error_message + '</span></td></tr>';
        }
        
        html += '</table>';
        html += '</div>';
        html += '<div class="modal-footer">';
        html += '<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        
        // Remover modal anterior si existe
        $('#detailModal').remove();
        
        // Agregar y mostrar nuevo modal
        $('body').append(html);
        $('#detailModal').modal('show');
    }
    
    function submit_form(val){
        document.getElementById('FORMAT').value = val;
        document.form1.submit();
    }
    </script>
</body>
</html>
