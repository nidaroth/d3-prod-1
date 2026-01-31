<?
ini_set('session.cache_limiter','public');
session_cache_limiter(false); 
require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("check_access.php");

// Edited By 
$ress = $db->Execute("SELECT EDITED_ON, EDITED_BY FROM ABHES_REPORT_SETUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if ($ress->RecordCount() == 0) {
    header("location:abhes_report_setup");
    exit;
}
$EDITED_ON_1        = '';
if ($ress->fields['EDITED_ON'] == '0000-00-00 00:00:00') {
    $EDITED_ON_1    = '';
} else {
    $EDITED_ON_1    = date("m/d/Y", strtotime($ress->fields['EDITED_ON']));
}
$EDITED_ON            = $ress->fields['EDITED_BY'];

$res_usr_name = $db->Execute("SELECT FIRST_NAME,LAST_NAME FROM S_EMPLOYEE_MASTER,Z_USER WHERE Z_USER.PK_USER = '$EDITED_ON' AND Z_USER.ID = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER");
$Edited_Name_1 = "";
if ($res_usr_name->RecordCount() == 1) {
    $Edited_Name_1 = $res_usr_name->fields['LAST_NAME'] . ', ' . $res_usr_name->fields['FIRST_NAME'];
}
// End - Edited By 

// Campus 
$res_type_campus = $db->Execute("select OFFICIAL_CAMPUS_NAME,PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by OFFICIAL_CAMPUS_NAME ASC");
// End Campus

if (check_access('MANAGEMENT_ACCREDITATION') == 0) {
    header("location:../index");
    exit;
}

$res = $db->Execute("SELECT ABHES FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if ($res->fields['ABHES'] == 0 || $res->fields['ABHES'] == '') {
    header("location:../index");
    exit;
}

$report_error = "";

// Reports
if (!empty($_POST)) {

    if ($_POST['REPORT_TYPE'] == 1) // Licensure Statistics
    {

        $reportType       = $_POST['REPORT_TYPE'];
        $reportOption     = 'Licensure Statistics Detail';
        $startDate        = date("Y-m-d", strtotime($_POST['START_DATE']));
        $endDate          = date("Y-m-d", strtotime($_POST['END_DATE']));

        $pkCampus      = "";
        $campus_cond  = "";
        if (!empty($_POST['PK_CAMPUS'])) {
            $pkCampus      = implode(",", $_POST['PK_CAMPUS']);
            $campus_cond  = " AND PK_CAMPUS IN ($pkCampus) ";
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
        
        $dir 			= 'temp/';
        $inputFileType  = 'Excel2007';
        $file_name 		= 'Licensure_Statistics_Detail_'.time().'.xlsx';
        $outputFileName = $dir.$file_name;
        $outputFileName = str_replace(pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName ); 
    
        $objReader      = PHPExcel_IOFactory::createReader($inputFileType);
        $objReader->setIncludeCharts(TRUE);
        //$objPHPExcel   = $objReader->load('../../global/excel/Template/Licensure_Certification_Exam_Pass_Rates.xlsx');
        $objPHPExcel = new PHPExcel();
        $objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        
        $line 	= 1;	
        $index 	= -1;

        // echo "CALL ABHES10001(".$_SESSION['PK_ACCOUNT'].", '" . $pkCampus . "', '" . $startDate . "', '" . $endDate . "','".$reportOption."')";exit;
        $res = $db->Execute("CALL ABHES10001(" . $_SESSION['PK_ACCOUNT'] . ", '" . $pkCampus . "', '" . $startDate . "','" . $endDate . "', '" . $reportOption . "')");

        if(count($res->fields) == 0)
        {
            $report_error = "No data in the report for the selections made.";
        }
        else
        {
            $heading = array_keys($res->fields);
            foreach ($heading as $key) 
            {
                $index++;
                $cell_no = $cell[$index].$line;
                $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($key);
                $objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(20);
                $objPHPExcel->getActiveSheet()->freezePane('A1');
            }
            while (!$res->EOF)
            {
                $index = -1;
                $line++;
                foreach ($heading as $key) 
                {
                    $index++;
                    $cell_no = $cell[$index].$line;
                    $cellValue=$res->fields[$key];
                    $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($cellValue);
                }
                $res->MoveNext();
            } 
            $objPHPExcel->getActiveSheet()->freezePane('A1');
            $objWriter->save($outputFileName);
            $objPHPExcel->disconnectWorksheets();
            header("location:".$outputFileName);
        }
            
        
    } //  End License Statistics
    else if ($_POST['REPORT_TYPE'] == 2) // Placement Statistics
    {
        $reportType       = $_POST['REPORT_TYPE'];
        $reportOption     = 'Placement Statistics Detail';
        $startDate        = date("Y-m-d", strtotime($_POST['START_DATE']));
        $endDate          = date("Y-m-d", strtotime($_POST['END_DATE']));

        $pkCampus      = "";
        $campus_cond  = "";
        if (!empty($_POST['PK_CAMPUS'])) {
            $pkCampus      = implode(",", $_POST['PK_CAMPUS']);
            $campus_cond  = " AND PK_CAMPUS IN ($pkCampus) ";
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
        
        $dir 			= 'temp/';
        $inputFileType  = 'Excel2007';
        $file_name 		= 'Placement_Statistics_Detail_'.time().'.xlsx';
        $outputFileName = $dir.$file_name;
        $outputFileName = str_replace(pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName ); 
    
        $objReader      = PHPExcel_IOFactory::createReader($inputFileType);
        $objReader->setIncludeCharts(TRUE);
        //$objPHPExcel   = $objReader->load('../../global/excel/Template/Licensure_Certification_Exam_Pass_Rates.xlsx');
        $objPHPExcel = new PHPExcel();
        $objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        
        $line 	= 1;	
        $index 	= -1;

        // echo "CALL ABHES10001(".$_SESSION['PK_ACCOUNT'].", '" . $pkCampus . "', '" . $startDate . "', '" . $endDate . "','".$reportOption."')";exit;
        $res = $db->Execute("CALL ABHES10001(" . $_SESSION['PK_ACCOUNT'] . ", '" . $pkCampus . "', '" . $startDate . "','" . $endDate . "', '" . $reportOption . "')");

        if(count($res->fields) == 0)
        {
            $report_error = "No data in the report for the selections made.";
        }
        else
        {
            $heading = array_keys($res->fields);
            foreach ($heading as $key) 
            {
                $index++;
                $cell_no = $cell[$index].$line;
                $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($key);
                $objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(20);
                $objPHPExcel->getActiveSheet()->freezePane('A1');
            }
            while (!$res->EOF)
            {
                $index = -1;
                $line++;
                foreach ($heading as $key) 
                {
                    $index++;
                    $cell_no = $cell[$index].$line;
                    $cellValue=$res->fields[$key];
                    $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($cellValue);
                }
                $res->MoveNext();
            } 
            $objPHPExcel->getActiveSheet()->freezePane('A1');
            $objWriter->save($outputFileName);
            $objPHPExcel->disconnectWorksheets();
            header("location:".$outputFileName);
        }

    } // End Placement Statistics
    else if ($_POST['REPORT_TYPE'] == 3) // Retention Statistics
    {

        $reportType       = $_POST['REPORT_TYPE'];
        $reportOption     = 'Retention Statistics Detail';
        $startDate        = date("Y-m-d", strtotime($_POST['START_DATE']));
        $endDate          = date("Y-m-d", strtotime($_POST['END_DATE']));

        $pkCampus      = "";
        $campus_cond  = "";
        if (!empty($_POST['PK_CAMPUS'])) {
            $pkCampus      = implode(",", $_POST['PK_CAMPUS']);
            $campus_cond  = " AND PK_CAMPUS IN ($pkCampus) ";
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
        
        $dir 			= 'temp/';
        $inputFileType  = 'Excel2007';
        $file_name 		= 'Retention_Statistics_Detail_'.time().'.xlsx';
        $outputFileName = $dir.$file_name;
        $outputFileName = str_replace(pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName ); 
    
        $objReader      = PHPExcel_IOFactory::createReader($inputFileType);
        $objReader->setIncludeCharts(TRUE);
        //$objPHPExcel   = $objReader->load('../../global/excel/Template/Licensure_Certification_Exam_Pass_Rates.xlsx');
        $objPHPExcel = new PHPExcel();
        $objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        
        $line 	= 1;	
        $index 	= -1;

        // echo "CALL ABHES10001(".$_SESSION['PK_ACCOUNT'].", '" . $pkCampus . "', '" . $startDate . "', '" . $endDate . "','".$reportOption."')";exit;
        $res = $db->Execute("CALL ABHES10001(" . $_SESSION['PK_ACCOUNT'] . ", '" . $pkCampus . "', '" . $startDate . "','" . $endDate . "', '" . $reportOption . "')");

        if(count($res->fields) == 0)
        {
            $report_error = "No data in the report for the selections made.";
        }
        else
        {
            $heading = array_keys($res->fields);
            foreach ($heading as $key) 
            {
                $index++;
                $cell_no = $cell[$index].$line;
                $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($key);
                $objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(20);
                $objPHPExcel->getActiveSheet()->freezePane('A1');
            }
            while (!$res->EOF)
            {
                $index = -1;
                $line++;
                foreach ($heading as $key) 
                {
                    $index++;
                    $cell_no = $cell[$index].$line;
                    $cellValue=$res->fields[$key];
                    $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($cellValue);
                }
                $res->MoveNext();
            } 
            $objPHPExcel->getActiveSheet()->freezePane('A1');
            $objWriter->save($outputFileName);
            $objPHPExcel->disconnectWorksheets();
            header("location:".$outputFileName);
        }

    } // End Retention Statistics
    else if ($_POST['REPORT_TYPE'] == 4) // Program Template Excel
    {
        $groupProgramCode = $_POST['GROUP_PROGRAM_CODE'];
        $reportType       = $_POST['REPORT_TYPE'];
        $reportOption     = 'Program Template Excel';
        $startDate        = date("Y-m-d", strtotime($_POST['START_DATE']));
        $endDate          = date("Y-m-d", strtotime($_POST['END_DATE']));

        $pkCampus      = "";
        $campus_cond  = "";
        if (!empty($_POST['PK_CAMPUS'])) {
            $pkCampus      = implode(",", $_POST['PK_CAMPUS']);
            $campus_cond  = " AND PK_CAMPUS IN ($pkCampus) ";
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
        
        $dir 			= 'temp/';
        $inputFileType  = 'Excel2007';
        $file_name 		= 'Program_Template_Report_'.time().'.xlsx';
        $outputFileName = $dir.$file_name;
        $outputFileName = str_replace(pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName ); 
    
        $objReader      = PHPExcel_IOFactory::createReader($inputFileType);
        $objReader->setIncludeCharts(TRUE);
        //$objPHPExcel   = $objReader->load('../../global/excel/Template/Licensure_Certification_Exam_Pass_Rates.xlsx');
        $objPHPExcel = new PHPExcel();
        $objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        
        $line 	= 1;	
        $index 	= -1;

        // echo "CALL ABHES10001(".$_SESSION['PK_ACCOUNT'].", '" . $pkCampus . "', '" . $startDate . "', '" . $endDate . "','".$reportOption."')";exit;
        $res = $db->Execute("CALL ABHES10001(" . $_SESSION['PK_ACCOUNT'] . ", '" . $pkCampus . "', '" . $startDate . "','" . $endDate . "', '" . $reportOption . "')");

        if(count($res->fields) == 0)
        {
            $report_error = "No data in the report for the selections made.";
        }
        else
        {
            $heading = array_keys($res->fields);
            foreach ($heading as $key) 
            {
                $index++;
                $cell_no = $cell[$index].$line;
                $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($key);
                $objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(20);
                $objPHPExcel->getActiveSheet()->freezePane('A1');
            }
            while (!$res->EOF)
            {
                $index = -1;
                $line++;
                foreach ($heading as $key) 
                {
                    $index++;
                    $cell_no = $cell[$index].$line;
                    $cellValue=$res->fields[$key];
                    $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($cellValue);
                }
                $res->MoveNext();
            } 
            $objPHPExcel->getActiveSheet()->freezePane('A1');
            $objWriter->save($outputFileName);
            $objPHPExcel->disconnectWorksheets();
            header("location:".$outputFileName);
        }
        
    } // End Program Template
}

// End Reports



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
    <title><?= MNU_ABHES ?> | <?= $title ?></title>
    <style>
        li>a>label {
            position: unset !important;
        }

        #advice-required-entry-PK_CAMPUS {
            position: absolute;
            top: 57px;
            width: 150px
        }

        .title-adjustment {
            padding-bottom: 12px;
            padding-top: 15px;
        }

        .adjust-sub-menu {
            padding-left: 10px;
            padding-top: 2px;
        }

        .button-adjustment {
            text-align: right;
        }

        .edited-by {
            font-weight: 500;
            padding-top: 7px;
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
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">
                            <?= MNU_ABHES ?>
                        </h4>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <b>Report Type</b>
                                            <select id="REPORT_TYPE" name="REPORT_TYPE" class="form-control" >
                                                <option value="1"><?= MNU_LICENSURE_STATISTICS ?></option>
                                                <option value="2"><?= MNU_PLACEMENT_STATISTICS ?></option>
                                                <option value="3"><?= MNU_RETENTION_STATISTICS ?></option>
                                                <option value="4"><?= MNU_PROGRAM_TEMPLATE ?></option>
                                            </select>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="button-adjustment">
                                                <button type="button" onclick="window.location.href='abhes_report_setup'" class="btn waves-effect waves-light btn-info">Report Setup</button>
                                                <div class="edited-by">Edited :
                                                    <?
                                                    if ($EDITED_ON_1 != '') {
                                                        echo $Edited_Name_1 . ' ' . $EDITED_ON_1;
                                                    } else {
                                                        echo 'N/A';
                                                    } ?>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <br /><br /><br />
                                    <div class="row">
                                        <div class="col-md-3 ">
                                            <?= CAMPUS ?>
                                            <select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control required-entry">
                                                <?
                                                while (!$res_type_campus->EOF) { ?>
                                                    <option value="<?= $res_type_campus->fields['PK_CAMPUS'] ?>"><?= $res_type_campus->fields['CAMPUS_CODE'] ?></option>
                                                <? $res_type_campus->MoveNext();
                                                } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2 ">
                                            <?= START_DATE ?>
                                            <input type="text" class="form-control date required-entry" id="START_DATE" name="START_DATE" value="">
                                        </div>
                                        <div class="col-md-2">
                                            <?= END_DATE ?>
                                            <input type="text" class="form-control date required-entry" id="END_DATE" name="END_DATE" value="">
                                        </div>
                                        <div class="col-md-3" style="padding: 0;">
                                            <br />
                                            <button type="button" onclick="submit_form()" id="btn_2" class="btn waves-effect waves-light btn-info"><?= EXCEL ?></button>
                                            <input type="hidden" name="FORMAT" id="FORMAT">
                                        </div>
                                    </div>
                                </form>

                                <br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <? require_once("footer.php"); ?>

        <?php if ($report_error != "") { ?>
            <div class="modal" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="exampleModalLabel1">Warning</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group" style="color: red;font-size: 15px;">
                                <b><?php echo $report_error; ?></b>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" data-dismiss="modal" class="btn waves-effect waves-light btn-info">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>

    <? require_once("js.php"); ?>
    <script src="../backend_assets/dist/js/validation_prototype.js"></script>
    <script src="../backend_assets/dist/js/validation.js"></script>

    <script src="../backend_assets/dist/js/jquery-migrate-1.0.0.js"></script>
    <script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />

    <script type="text/javascript">
        jQuery(document).ready(function($) {

            jQuery('.date').datepicker({
                todayHighlight: true,
                orientation: "bottom auto"
            });
        });

        var error = '<?php echo  $report_error; ?>';
        jQuery(document).ready(function($) {
            if (error != "") {
                jQuery('#errorModal').modal();
            }
        });

        function submit_form() {
            jQuery(document).ready(function($) {
                var valid = new Validation('form1', {
                    onSubmit: false
                });
                var result = valid.validate();
                if (result == true) {
                    //document.getElementById('FORMAT').value = val
                    document.form1.submit();
                }
            });
        }

        function submit_form_2(form_no, report_type) {
            jQuery(document).ready(function($) {
                var valid = new Validation('form' + form_no, {
                    onSubmit: false
                });
                var result = valid.validate();
                if (result == true) {

                    if (report_type == '1' || report_type == '2') {

                        var campus = $("#PK_CAMPUS").val();
                        var start_date = $('#START_DATE').val();
                        var end_date = $('#END_DATE').val();
                        var report_option = $('#REPORT_OPTION').val();
                        var data = 'PK_CAMPUS=' + campus + '&START_DATE=' + start_date + '&END_DATE=' + end_date + '&REPORT_OPTION=' + report_option + '&FORMAT=' + report_type;
                    } else if (report_type == '3' || report_type == '4') {

                        var campus_2 = $("#PK_CAMPUS_2").val();
                        var start_date_2 = $('#START_DATE_2').val();
                        var end_date_2 = $('#END_DATE_2').val();
                        var report_option_2 = $('#REPORT_OPTION_2').val();
                        var data = 'PK_CAMPUS_2=' + campus_2 + '&START_DATE_2=' + start_date_2 + '&END_DATE_2=' + end_date_2 + '&REPORT_OPTION_2=' + report_option_2 + '&FORMAT=' + report_type;
                    } else if (report_type == '5' || report_type == '6') {

                        var campus_3 = $("#PK_CAMPUS_3").val();
                        var start_date_3 = $('#START_DATE_3').val();
                        var end_date_3 = $('#END_DATE_3').val();
                        var report_option_3 = $('#REPORT_OPTION_3').val();
                        var data = 'PK_CAMPUS_3=' + campus_3 + '&START_DATE_3=' + start_date_3 + '&END_DATE_3=' + end_date_3 + '&REPORT_OPTION_3=' + report_option_3 + '&FORMAT=' + report_type;
                    }

                    var value = $.ajax({
                        url: "ajax_accsc_reports",
                        type: "POST",
                        data: data,
                        async: false,
                        cache: false,
                        dataType: "json",
                        success: function(data) {
                            const text = window.location.href;
                            const word = '/school';
                            const textArray = text.split(word); // ['This is ', ' text...']
                            const result = textArray.shift();
                            if (report_type == '1' || report_type == '3' || report_type == '5') {
                                var source_file = "ACCSC Employment Verification Source Report_" + Date.now() + ".pdf";
                            } else {
                                var source_file = "ACCSC Employment Verification Source Report_" + Date.now() + ".xlsx";
                            }
                            downloadDataUrlFromJavascript(source_file, result + '/school/' + data.path)

                        }
                    }).responseText;

                    // document.getElementById('FORMAT').value = val
                    // document.form1.submit();
                }
            });
        }

        function downloadDataUrlFromJavascript(filename, dataUrl) {

            // Construct the 'a' element
            var link = document.createElement("a");
            link.download = filename;
            link.target = "_blank";

            // Construct the URI
            link.href = dataUrl;
            document.body.appendChild(link);
            link.click();

            // Cleanup the DOM
            document.body.removeChild(link);
            delete link;
        }
    </script>

    <script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
    <link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css" />
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#PK_CAMPUS').multiselect({
                includeSelectAllOption: true,
                allSelectedText: 'All <?= CAMPUS ?>',
                nonSelectedText: '',
                numberDisplayed: 1,
                nSelectedText: '<?= CAMPUS ?> selected'
            });
        });
    </script>

    <?php $report_error = ""; ?>
</body>

</html>