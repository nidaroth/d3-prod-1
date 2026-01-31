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

        if ($_POST['REPORT_OPTION'] == 1) // Detail
        {
            $groupProgramCode = $_POST['GROUP_PROGRAM_CODE'];
            $reportType       = $_POST['REPORT_TYPE'];
            $reportOption     = 'Licensure Statistics Detail';
            $startDate        = date("Y-m-d", strtotime($_POST['START_DATE']));
            $endDate          = date("Y-m-d", strtotime($_POST['END_DATE']));

            $pkCampus      = "";
            $campus_cond  = "";
            $campus_name  = "";
            if (!empty($_POST['PK_CAMPUS'])) {
                $pkCampus      = implode(",", $_POST['PK_CAMPUS']);
                $campus_cond  = " AND PK_CAMPUS IN ($pkCampus) ";
            }

            $group_by = 0;
            if (!empty($groupProgramCode)) {
                $group_by = 1;
            }

            $res_campus = $db->Execute("select PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $campus_cond order by CAMPUS_CODE ASC");
            while (!$res_campus->EOF) {
                if ($campus_name != '')
                    $campus_name .= ', ';
                $campus_name .= $res_campus->fields['CAMPUS_CODE'];

                $res_campus->MoveNext();
            }

            $res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
            $SCHOOL_NAME = $res->fields['SCHOOL_NAME'];
            $PDF_LOGO      = $res->fields['PDF_LOGO'];

            $logo = "";
            if ($PDF_LOGO != '') {
                $logo = '<img src="' . $PDF_LOGO . '" height="50px" />';
            }

            $txt  = "";
            $txt .= '<br><br>';
            $txt .= '<table border="1" cellspacing="0" cellpadding="3" width="100%">
                        <thead>
                            <tr>
                                <th width="4%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="center" >
                                </th>
                                <th width="17%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
                                    <b><i>Student</i></b>
                                </th>
                                <th width="10%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
                                    <b><i>Student ID</i></b>
                                </th>
                                <th width="16%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
                                    <b><i>Program Name</i></b>
                                </th>
                                <th width="10%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
                                    <b><i>Credential</i></b>
                                </th>
                                <th width="9%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
                                    <b><i>CIP Code</i></b>
                                </th>
                                <th width="8%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
                                    <b><i>Grad Date</i></b>
                                </th>
                                <th width="13%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
                                    <b><i>Exam Name</i></b>
                                </th>
                                <th width="13%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
                                    <b><i>Exam Status</i></b>
                                </th>
                            </tr>
                        </thead>
                        <tbody>';

            //echo "CALL ABHES10001(".$_SESSION['PK_ACCOUNT'].", ".$pkCampus.", ".$startDate.", ".$endDate.",'".$reportOption."', '".$group_by."')";exit;
            $res = $db->Execute("CALL ABHES10001(" . $_SESSION['PK_ACCOUNT'] . ", '" . $pkCampus . "', '" . $startDate . "','" . $endDate . "', '" . $reportOption . "', '" . $group_by . "')");
            //print_r($res);exit;
            if (count($res->fields) == 0) {
                $report_error = "No data in the report for the selections made.";
            } else {
                $CAMPUS_CODE      = $campus_name;
                $DATE_RANGE      = date("m/d/Y", strtotime($res->fields['REPORTING_PERIOD_BEGIN_DATE'])) . ' - ' . date("m/d/Y", strtotime($res->fields['REPORTING_PERIOD_END_DATE']));

                $i = 1;
                while (!$res->EOF) {
                    $gradDate = '';
                    if ($res->fields['GRADE_DATE'] != '0000-00-00' && $res->fields['GRADE_DATE'] != '') {
                        $gradDate = date("m/d/Y", strtotime($res->fields['GRADE_DATE']));
                    }

                    $STUDENT           = $res->fields['STUDENT'];
                    $STUDENT_ID        = $res->fields['STUDENT_ID'];
                    $PROGRAM_NAME      = $res->fields['PROGRAM_DESCRIPTION'];
                    $CREDENTAIL        = $res->fields['CREDENTIAL_LEVEL'];
                    $CIP_CODE          = $res->fields['CIP_CODE'];
                    $GRAD_DATE         = $gradDate;
                    $EXAM_NAME         = $res->fields['EXAM_NAME'];
                    $EXAM_STATUS       = $res->fields['EXAM_STATUS'];

                    $txt .= '<tr>
                                <td align="center">' . $i . '</td>
                                <td >' . $STUDENT . '</td>
                                <td >' . $STUDENT_ID . '</td>
                                <td >' . $PROGRAM_NAME . '</td>
                                <td >' . $CREDENTAIL . '</td>
                                <td >' . $CIP_CODE . '</td>
                                <td >' . $GRAD_DATE . '</td>
                                <td >' . $EXAM_NAME . '</td>
                                <td >' . $EXAM_STATUS . '</td>
                            </tr>';
                    $i++;
                    $res->MoveNext();
                }

                $txt .= '</tbody></table>';

                $header = '<table width="100%" >
                                <tr>
                                    <td width="20%" valign="top" >' . $logo . '</td>
                                    <td width="40%" valign="top" style="font-size:20px" >' . $SCHOOL_NAME . '</td>
                                    <td width="40%" valign="top" >
                                        <table width="100%" >
                                            <tr>
                                                <td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>ABHES Licensure Statistics</b></td>
                                            </tr>
                                            <tr>
                                                <td align="right"><b>Campus(es) : </b> ' . $CAMPUS_CODE . '</td>
                                            </tr>
                                            <tr>
                                                <td align="right"><b>Date Range : </b> ' . $DATE_RANGE . '</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>';


                $header_cont = '<!DOCTYPE HTML>
                <html>
                <head>

                </head>
                <body>
                <div> ' . $header . ' </div>
                </body>
                </html>';

                $html_body_cont = '<!DOCTYPE HTML>
                <html>
                <head> <style>
                table{  margin-top: 2px; }
                table tr{  padding-top: 1px !important; }
                </style>
                </head>
                <body>' . $txt . '</body></html>';

                $date_footer = convert_to_user_date(date('Y-m-d H:i:s'), 'l, F d, Y h:i A', $TIMEZONE, date_default_timezone_get());

                $footer = '<table width="100%" >
                        <tr>
                            <td width="33%" valign="top" style="font-size:10px;" ><i>' . $date_footer . '</i></td>
                            <td width="33%" valign="top" style="font-size:10px;" align="center" ><i></i></td>
                            <td></td>							
                        </tr>
                    </table>';
                $footer_cont = '<!DOCTYPE HTML><html><head><style>
                    tbody td{ font-size:14px !important; }
                    </style></head><body>' . $footer . '</body></html>';

                $header_path = create_html_file('header_license_statistics_detail.html', $header_cont, "invoice");
                $content_path = create_html_file('content_license_statistics_detail.html', $txt, "invoice");
                $footer_path = create_html_file('footer_license_statistics_detail.html', $footer_cont, "invoice");

                $file_name = 'Licensure_Statistics_Detail_' . uniqid() . '.pdf';
                $exec = 'xvfb-run -a wkhtmltopdf -T 0 -R 0 -B 0 -L 0 --enable-local-file-access --orientation portrait --page-size A4 --page-width 300 --page-height 210 --margin-top 25mm --margin-left 7mm --margin-right 5mm  --margin-bottom 20mm --footer-font-size 8 --footer-right "Page [page] of [toPage]" --header-html ' . $header_path . ' --footer-html  ' . $footer_path . ' ' . $content_path . ' ../school/temp/invoice/' . $file_name . ' 2>&1';

                $pdfdata = array('filepath' => 'temp/invoice/' . $file_name, 'exec' => $exec, 'filename' => $file_name, 'filefullpath' => $http_path . 'school/temp/invoice/' . $file_name);

                exec($pdfdata['exec'], $output, $retval);
                echo 'school/temp/invoice/' . $file_name;
                header('Content-Type: Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . basename($pdfdata['filefullpath']) . '"');
                readfile($pdfdata['filepath']);

                unlink('../school/temp/invoice/header_license_statistics_detail.html');
                unlink('../school/temp/invoice/content_license_statistics_detail.html');
                unlink('../school/temp/invoice/footer_license_statistics_detail.html');
                exit;
            }
        } 
        else if ($_POST['REPORT_OPTION'] == 2) // Summary
        {
            $groupProgramCode = $_POST['GROUP_PROGRAM_CODE'];
            $reportType       = $_POST['REPORT_TYPE'];
            $reportOption     = 'Licensure Statistics Summary';
            $startDate        = date("Y-m-d", strtotime($_POST['START_DATE']));
            $endDate          = date("Y-m-d", strtotime($_POST['END_DATE']));

            $pkCampus      = "";
            $campus_cond  = "";
            $campus_name  = "";
            if (!empty($_POST['PK_CAMPUS'])) {
                $pkCampus      = implode(",", $_POST['PK_CAMPUS']);
                $campus_cond  = " AND PK_CAMPUS IN ($pkCampus) ";
            }

            $group_by = 0;
            if (!empty($groupProgramCode)) {
                $group_by = 1;
            }

            $res_campus = $db->Execute("select PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $campus_cond order by CAMPUS_CODE ASC");
            while (!$res_campus->EOF) {
                if ($campus_name != '')
                    $campus_name .= ', ';
                $campus_name .= $res_campus->fields['CAMPUS_CODE'];

                $res_campus->MoveNext();
            }

            $res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
            $SCHOOL_NAME = $res->fields['SCHOOL_NAME'];
            $PDF_LOGO      = $res->fields['PDF_LOGO'];

            $logo = "";
            if ($PDF_LOGO != '') {
                $logo = '<img src="' . $PDF_LOGO . '" height="50px" />';
            }

            $txt  = "";
            $txt .= '<br><br>';
            $txt .= '<table border="1" cellspacing="0" cellpadding="3" width="100%">
                        <thead>
                            <tr>
                                <th width="22%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
                                    <b><i>Program Name</i></b>
                                </th>
                                <th width="13%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
                                    <b><i>Credentail</i></b>
                                </th>
                                <th width="8%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
                                    <b><i>CIP</i></b>
                                </th>
                                <th width="14%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
                                    <b><i>Exam Name</i></b>
                                </th>
                                <th width="8%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
                                    <b><i>Took Exam</i></b>
                                </th>
                                <th width="8%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
                                    <b><i>Passed Exam</i></b>
                                </th>
                                <th width="8%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
                                    <b><i>Failed Exam</i></b>
                                </th>
                                <th width="8%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
                                    <b><i>Results Pending</i></b>
                                </th>
                                <th width="8%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
                                    <b><i>Exam Pass Rate</i></b>
                                </th>
                            </tr>
                        </thead>
                        <tbody>';

            //echo "CALL ABHES10001(".$_SESSION['PK_ACCOUNT'].", ".$pkCampus.", ".$startDate.", ".$endDate.",'".$reportOption."', '".$group_by."')";exit;
            $res = $db->Execute("CALL ABHES10001(" . $_SESSION['PK_ACCOUNT'] . ", '" . $pkCampus . "', '" . $startDate . "','" . $endDate . "', '" . $reportOption . "', '" . $group_by . "')");

            if (count($res->fields) == 0) {
                $report_error = "No data in the report for the selections made.";
            } else {
                $CAMPUS_CODE      = $campus_name;
                $DATE_RANGE      = date("m/d/Y", strtotime($res->fields['REPORTING_PERIOD_BEGIN_DATE'])) . ' - ' . date("m/d/Y", strtotime($res->fields['REPORTING_PERIOD_END_DATE']));

                $data = [];
                $terms = [];

                while (!$res->EOF) {
                    $data[$res->fields['PROGRAM']][] = $res->fields;
                    //$terms[$res->fields['PROGRAM']]=array('PROGRAM'=>$res->fields['PROGRAM'],'PROGRAM_DESCRIPTION'=>$res->fields['PROGRAM_DESCRIPTION'],'BEGIN_ENROL'=>$res->fields['BEGIN_ENROL'],'ENDING_ENROL'=>$res->fields['ENDING_ENROL'],'NEW_STARTS'=>$res->fields['NEW_STARTS'],'RE_ENTRY'=>$res->fields['RE_ENTRY'],'GRAD'=>$res->fields['GRAD']);
                    $res->MoveNext();
                }
                //echo "<pre>";print_r($data);exit;

                $actual_data_rows  = [];
                foreach ($data as $program => $program_data_rows) 
                {
                    $TOOK_EXAM_COUNT = 0;
                    $PASSED_EXAM_COUNT = 0;
                    $FAILED_EXAM_COUNT = 0;
                    $RESULTS_PENDING_COUNT = 0;
                    $program_Name = $program;
                    
                    foreach ($program_data_rows as $program_data_row) 
                    {
        
                        $credentail   = $program_data_row['CREDENTIAL_LEVEL'];
                        $cip_Code     = $program_data_row['CIP_CODE'];
                        $exam_name    = $program_data_row['EXAM_NAME'];
                        $program_Desc = $program_data_row['PROGRAM_DESCRIPTION'];

                        if ($program_data_row['RECORD_TYPE'] == 'TOOK_EXAM') {
                            $TOOK_EXAM_COUNT = $program_data_row['COUNT'];
                        }
                        if ($program_data_row['RECORD_TYPE'] == 'PASSED_EXAM') {
                            $PASSED_EXAM_COUNT = $program_data_row['COUNT'];
                        }
                        if ($program_data_row['RECORD_TYPE'] == 'FAILED_EXAM') {
                            $FAILED_EXAM_COUNT = $program_data_row['COUNT'];
                        }
                        if ($program_data_row['RECORD_TYPE'] == 'RESULTS_PENDING') {
                            $RESULTS_PENDING_COUNT = $program_data_row['COUNT'];
                        }
                    
                    }

                    $actual_data_rows[$program_Name]['PROGRAM_DESCRIPTION']  = $program_Desc;
                    $actual_data_rows[$program_Name]['CREDENTIAL_LEVEL']     = $credentail;
                    $actual_data_rows[$program_Name]['CIP_CODE']             = $cip_Code;
                    $actual_data_rows[$program_Name]['EXAM_NAME']            = $exam_name;
                    $actual_data_rows[$program_Name]['TOOK_EXAM']            = $TOOK_EXAM_COUNT;
                    $actual_data_rows[$program_Name]['PASSED_EXAM']          = $PASSED_EXAM_COUNT;
                    $actual_data_rows[$program_Name]['FAILED_EXAM']          = $FAILED_EXAM_COUNT;
                    $actual_data_rows[$program_Name]['RESULTS_PENDING']      = $RESULTS_PENDING_COUNT;

                }
                
                ksort($actual_data_rows);
                // echo "<pre>";
                // print_r($actual_data_rows);exit;

                $i = 1;
                foreach ($actual_data_rows as $k => $result) 
                {

                    $program_Name       = $result['PROGRAM_DESCRIPTION'];
                    $credentail         = $result['CREDENTIAL_LEVEL'];
                    $cip_Code           = $result['CIP_CODE'];
                    $exam_Name          = $result['EXAM_NAME'];
                    $took_Exam          = $result['TOOK_EXAM'];
                    $pass_Exam          = $result['PASSED_EXAM'];
                    $failed_Exam        = $result['FAILED_EXAM'];
                    $results_Pending    = $result['RESULTS_PENDING'];
                    $exam_Pass_Rate     = ($pass_Exam / $took_Exam) * 100;

                    $txt .= '<tr>
                                <td >' . $program_Name . '</td>
                                <td >' . $credentail . '</td>
                                <td >' . $cip_Code . '</td>
                                <td >' . $exam_Name . '</td>
                                <td align="right">' . $took_Exam . '</td>
                                <td align="right">' . $pass_Exam . '</td>
                                <td align="right">' . $failed_Exam . '</td>
                                <td align="right">' . $results_Pending . '</td>
                                <td align="right">' . number_format_value_checker($exam_Pass_Rate, 2) . '%</td>
                            </tr>';

                    $i++;
                    //$res->MoveNext();
                }

                $txt .= '</tbody></table>';

                $header = '<table width="100%" >
                                <tr>
                                    <td width="20%" valign="top" >' . $logo . '</td>
                                    <td width="40%" valign="top" style="font-size:20px" >' . $SCHOOL_NAME . '</td>
                                    <td width="40%" valign="top" >
                                        <table width="100%" >
                                            <tr>
                                                <td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>ABHES Licensure Statistics</b></td>
                                            </tr>
                                            <tr>
                                                <td align="right"><b>Campus(es) : </b> ' . $CAMPUS_CODE . '</td>
                                            </tr>
                                            <tr>
                                                <td align="right"><b>Date Range : </b> ' . $DATE_RANGE . '</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>';


                $header_cont = '<!DOCTYPE HTML>
                <html>
                <head>

                </head>
                <body>
                <div> ' . $header . ' </div>
                </body>
                </html>';

                $html_body_cont = '<!DOCTYPE HTML>
                <html>
                <head> <style>
                table{  margin-top: 2px; }
                table tr{  padding-top: 1px !important; }
                </style>
                </head>
                <body>' . $txt . '</body></html>';

                $date_footer = convert_to_user_date(date('Y-m-d H:i:s'), 'l, F d, Y h:i A', $TIMEZONE, date_default_timezone_get());

                $footer = '<table width="100%" >
                        <tr>
                            <td width="33%" valign="top" style="font-size:10px;" ><i>' . $date_footer . '</i></td>
                            <td width="33%" valign="top" style="font-size:10px;" align="center" ><i></i></td>
                            <td></td>							
                        </tr>
                    </table>';
                $footer_cont = '<!DOCTYPE HTML><html><head><style>
                    tbody td{ font-size:14px !important; }
                    </style></head><body>' . $footer . '</body></html>';

                $header_path = create_html_file('header_license_statistics_summary.html', $header_cont, "invoice");
                $content_path = create_html_file('content_license_statistics_summary.html', $txt, "invoice");
                $footer_path = create_html_file('footer_license_statistics_summary.html', $footer_cont, "invoice");

                $file_name = 'Licensure_Statistics_Summary_' . uniqid() . '.pdf';
                $exec = 'xvfb-run -a wkhtmltopdf -T 0 -R 0 -B 0 -L 0 --enable-local-file-access --orientation portrait --page-size A4 --page-width 300 --page-height 210 --margin-top 25mm --margin-left 7mm --margin-right 5mm  --margin-bottom 20mm --footer-font-size 8 --footer-right "Page [page] of [toPage]" --header-html ' . $header_path . ' --footer-html  ' . $footer_path . ' ' . $content_path . ' ../school/temp/invoice/' . $file_name . ' 2>&1';

                $pdfdata = array('filepath' => 'temp/invoice/' . $file_name, 'exec' => $exec, 'filename' => $file_name, 'filefullpath' => $http_path . 'school/temp/invoice/' . $file_name);

                exec($pdfdata['exec'], $output, $retval);
                echo 'school/temp/invoice/' . $file_name;
                header('Content-Type: Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . basename($pdfdata['filefullpath']) . '"');
                readfile($pdfdata['filepath']);

                unlink('../school/temp/invoice/header_license_statistics_summary.html');
                unlink('../school/temp/invoice/content_license_statistics_summary.html');
                unlink('../school/temp/invoice/footer_license_statistics_summary.html');
                exit;
            }
        }
    } //  End License Statistics
    else if ($_POST['REPORT_TYPE'] == 2) // Placement Statistics
    {
        if ($_POST['REPORT_OPTION'] == 1) // Detail
        {
            $groupProgramCode = $_POST['GROUP_PROGRAM_CODE'];
            $reportType       = $_POST['REPORT_TYPE'];
            $reportOption     = 'Placement Statistics Detail';
            $startDate        = date("Y-m-d", strtotime($_POST['START_DATE']));
            $endDate          = date("Y-m-d", strtotime($_POST['END_DATE']));

            $pkCampus      = "";
            $campus_cond  = "";
            $campus_name  = "";
            if (!empty($_POST['PK_CAMPUS'])) {
                $pkCampus      = implode(",", $_POST['PK_CAMPUS']);
                $campus_cond  = " AND PK_CAMPUS IN ($pkCampus) ";
            }

            $group_by = 0;
            if (!empty($groupProgramCode)) {
                $group_by = 1;
            }

            $res_campus = $db->Execute("select PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $campus_cond order by CAMPUS_CODE ASC");
            while (!$res_campus->EOF) {
                if ($campus_name != '')
                    $campus_name .= ', ';
                $campus_name .= $res_campus->fields['CAMPUS_CODE'];

                $res_campus->MoveNext();
            }

            $res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
            $SCHOOL_NAME = $res->fields['SCHOOL_NAME'];
            $PDF_LOGO      = $res->fields['PDF_LOGO'];

            $logo = "";
            if ($PDF_LOGO != '') {
                $logo = '<img src="' . $PDF_LOGO . '" height="50px" />';
            }

            $txt  = "";

            //echo "CALL ABHES10001(".$_SESSION['PK_ACCOUNT'].", ".$pkCampus.", ".$startDate.", ".$endDate.",'".$reportOption."', '".$group_by."')";exit;
            $res = $db->Execute("CALL ABHES10001(" . $_SESSION['PK_ACCOUNT'] . ", '" . $pkCampus . "', '" . $startDate . "','" . $endDate . "', '" . $reportOption . "', '" . $group_by . "')");

            if (count($res->fields) == 0) {
                $report_error = "No data in the report for the selections made.";
            } else {
                $CAMPUS_CODE      = $campus_name;
                $DATE_RANGE      = date("m/d/Y", strtotime($res->fields['REPORTING_PERIOD_BEGIN_DATE'])) . ' - ' . date("m/d/Y", strtotime($res->fields['REPORTING_PERIOD_END_DATE']));


                /*****************************************/
                $data = [];
                while (!$res->EOF) {
                    $data[] = $res->fields;
                    $res->MoveNext();
                }
                //echo "<pre>";print_r($data);exit;
                
                $new_array = array();
                foreach($data as $k=>$v){

                    if($v['EmployedInField']>0 && $v['EmployedInField'] == 1){
                        $new_array[$v['PROGRAM_DESCRIPTION']]['Place In Field'][]=$v;
                    }
                    if($v['EmployedOutOfField']>0 && $v['EmployedOutOfField'] == 1){
                        $new_array[$v['PROGRAM_DESCRIPTION']]['Place Out of Field'][]=$v;
                    }
                    if($v['EmployedInRelatedField']>0 && $v['EmployedInRelatedField'] == 1){
                        $new_array[$v['PROGRAM_DESCRIPTION']]['Place in Related Field'][]=$v;
                    }
                    if($v['Available']>0 && $v['Available'] == 1){
                        $new_array[$v['PROGRAM_DESCRIPTION']]['Available'][]=$v;
                    }
                    if($v['NotAvailable']>0 && $v['NotAvailable'] == 1){
                        $new_array[$v['PROGRAM_DESCRIPTION']]['Unavailable'][]=$v;
                    }
                }
                // echo "<pre>";print_r($new_array);exit;
             
                foreach($new_array as $program=>$val)
				{
                    $txt .= '<br><br>';
                    $txt .= '<b>Program : '.$program.'</b>';
                    $txt .= '<br>';
                    foreach($new_array[$program] as $enrolled=>$values)
                    {
                        $txt .= '<br>';
                        $txt .= '<b>'.$enrolled.'</b>';
                        $txt .= '<br><br>';
                        $txt .= '<table border="1" cellspacing="0" cellpadding="3" width="100%">
                                    <thead>
                                        <tr>
                                            <th width="4%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="center" >
                                            </th>
                                            <th width="17%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
                                                <b><i>Student</i></b>
                                            </th>
                                            <th width="10%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
                                                <b><i>Student ID</i></b>
                                            </th>
                                            <th width="10%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
                                                <b><i>Credential</i></b>
                                            </th>
                                            <th width="8%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
                                                <b><i>Grad Date</i></b>
                                            </th>
                                            <th width="3%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="center" >
                                                <b><i>A</i></b>
                                            </th>
                                            <th width="3%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="center" >
                                                <b><i>P</i></b>
                                            </th>
                                            <th width="3%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="center" >
                                                <b><i>OF</i></b>
                                            </th>
                                            <th width="3%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="center" >
                                                <b><i>RF</i></b>
                                            </th>
                                            <th width="3%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="center" >
                                                <b><i>U</i></b>
                                            </th>
                                            <th width="13%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
                                                <b><i>Employer <br>Employer Phone</i></b>
                                            </th>
                                            <th width="10%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
                                                <b><i>Job Title</i></b>
                                            </th>
                                            <th width="15%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
                                                <b><i>Employment Date <br>Verification Date</i></b>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>';
                        $cnt = 1;
                        foreach($values as $result)
                        {
                            $aGrad_Date = '';
                            $aVERIFICATION_DATE = '';
                            $aEMP_DATE = '';
                            if ($result['GRADE_DATE'] != '0000-00-00' && $result['GRADE_DATE'] != '') {
                                $aGrad_Date = date("m/d/Y", strtotime($result['GRADE_DATE']));
                            }
                            if ($result['VERIFICATION_DATE'] != '0000-00-00' && $result['VERIFICATION_DATE'] != '') {
                                $aVERIFICATION_DATE = date("m/d/Y", strtotime($result['VERIFICATION_DATE']));
                            }
                            if ($result['EMP_DATE'] != '0000-00-00' && $result['EMP_DATE'] != '') {
                                $aEMP_DATE = date("m/d/Y", strtotime($result['EMP_DATE']));
                            }

                            $PROGRAM_NAME       = $result['PROGRAM_DESCRIPTION'];

                            $STUDENT            = $result['STUDENT'];
                            $STUDENT_ID         = $result['STUDENT_ID'];
                            $CREDENTAIL         = $result['CREDENTIAL_LEVEL'];
                            $GRAD_DATE          = $aGrad_Date;

                            $EmployedInField          = $result['EmployedInField'];
                            $EmployedOutOfField       = $result['EmployedOutOfField'];
                            $EmployedInRelatedField   = $result['EmployedInRelatedField'];
                            $Available                = $result['Available'];
                            $NotAvailable             = $result['NotAvailable'];

                            $JOB_TITLE                = $result['JOB_TITLE'];
                            $VERIFICATION_DATE        = $aVERIFICATION_DATE;
                            $EMPOLYEMENT_DATE         = $aEMP_DATE;
                            $COMPANY_NAME             = $result['COMPANY_NAME'];
                            $PHONE                    = $result['PHONE'];

                            $txt .= '<tr>
                                        <td align="center">' . $cnt . '</td>
                                        <td >' . $STUDENT . '</td>
                                        <td >' . $STUDENT_ID . '</td>
                                        <td >' . $CREDENTAIL . '</td>
                                        <td >' . $GRAD_DATE . '</td>
                                        <td align="center">' . $Available . '</td>
                                        <td align="center">' . $EmployedInField . '</td>
                                        <td align="center">' . $EmployedOutOfField . '</td>
                                        <td align="center">' . $EmployedInRelatedField . '</td>
                                        <td align="center">' . $NotAvailable . '</td>
                                        <td >' . $COMPANY_NAME . '<br>' . $PHONE . '</td>
                                        <td >' . $JOB_TITLE . '</td>
                                        <td align="right">' . $EMPOLYEMENT_DATE . '<br>' . $VERIFICATION_DATE . '</td>
                                    </tr>';

                            $cnt++;
                        }
                        $txt .= '</tbody></table>';
                        
                    }
                }

                $header = '<table width="100%" >
                                <tr>
                                    <td width="20%" valign="top" >' . $logo . '</td>
                                    <td width="40%" valign="top" style="font-size:20px" >' . $SCHOOL_NAME . '</td>
                                    <td width="40%" valign="top" >
                                        <table width="100%" >
                                            <tr>
                                                <td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>ABHES Placement Statistics</b></td>
                                            </tr>
                                            <tr>
                                                <td align="right"><b>Campus(es) : </b> ' . $CAMPUS_CODE . '</td>
                                            </tr>
                                            <tr>
                                                <td align="right"><b>Date Range : </b> ' . $DATE_RANGE . '</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>';


                $header_cont = '<!DOCTYPE HTML>
                <html>
                <head>

                </head>
                <body>
                <div> ' . $header . ' </div>
                </body>
                </html>';

                $html_body_cont = '<!DOCTYPE HTML>
                <html>
                <head> <style>
                table{  margin-top: 2px; }
                table tr{  padding-top: 1px !important; }
                </style>
                </head>
                <body>' . $txt . '</body></html>';

                $date_footer = convert_to_user_date(date('Y-m-d H:i:s'), 'l, F d, Y h:i A', $TIMEZONE, date_default_timezone_get());

                $footer = '<table width="100%" >
                        <tr>
                            <td width="33%" valign="top" style="font-size:10px;" ><i>' . $date_footer . '</i></td>
                            <td width="33%" valign="top" style="font-size:10px;" align="center" ><i></i></td>
                            <td></td>							
                        </tr>
                    </table>';
                $footer_cont = '<!DOCTYPE HTML><html><head><style>
                    tbody td{ font-size:14px !important; }
                    </style></head><body>' . $footer . '</body></html>';

                $header_path = create_html_file('header_placement_statistics_detail.html', $header_cont, "invoice");
                $content_path = create_html_file('content_placement_statistics_detail.html', $txt, "invoice");
                $footer_path = create_html_file('footer_placement_statistics_detail.html', $footer_cont, "invoice");

                $file_name = 'Placement_Statistics_Detail_' . uniqid() . '.pdf';
                $exec = 'xvfb-run -a wkhtmltopdf -T 0 -R 0 -B 0 -L 0 --enable-local-file-access --orientation portrait --page-size A4 --page-width 300 --page-height 210 --margin-top 25mm --margin-left 7mm --margin-right 5mm  --margin-bottom 20mm --footer-font-size 8 --footer-right "Page [page] of [toPage]" --header-html ' . $header_path . ' --footer-html  ' . $footer_path . ' ' . $content_path . ' ../school/temp/invoice/' . $file_name . ' 2>&1';

                $pdfdata = array('filepath' => 'temp/invoice/' . $file_name, 'exec' => $exec, 'filename' => $file_name, 'filefullpath' => $http_path . 'school/temp/invoice/' . $file_name);

                exec($pdfdata['exec'], $output, $retval);
                echo 'school/temp/invoice/' . $file_name;
                header('Content-Type: Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . basename($pdfdata['filefullpath']) . '"');
                readfile($pdfdata['filepath']);

                unlink('../school/temp/invoice/header_placement_statistics_detail.html');
                unlink('../school/temp/invoice/content_placement_statistics_detail.html');
                unlink('../school/temp/invoice/footer_placement_statistics_detail.html');
                exit;
            }
        } 
        else if ($_POST['REPORT_OPTION'] == 2) // Summary
        {
            $groupProgramCode = $_POST['GROUP_PROGRAM_CODE'];
            $reportType       = $_POST['REPORT_TYPE'];
            $reportOption     = 'Placement Statistics Summary';
            $startDate        = date("Y-m-d", strtotime($_POST['START_DATE']));
            $endDate          = date("Y-m-d", strtotime($_POST['END_DATE']));

            $pkCampus      = "";
            $campus_cond  = "";
            $campus_name  = "";
            if (!empty($_POST['PK_CAMPUS'])) {
                $pkCampus      = implode(",", $_POST['PK_CAMPUS']);
                $campus_cond  = " AND PK_CAMPUS IN ($pkCampus) ";
            }

            $group_by = 0;
            if (!empty($groupProgramCode)) {
                $group_by = 1;
            }

            $res_campus = $db->Execute("select PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $campus_cond order by CAMPUS_CODE ASC");
            while (!$res_campus->EOF) {
                if ($campus_name != '')
                    $campus_name .= ', ';
                $campus_name .= $res_campus->fields['CAMPUS_CODE'];

                $res_campus->MoveNext();
            }

            $res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
            $SCHOOL_NAME = $res->fields['SCHOOL_NAME'];
            $PDF_LOGO      = $res->fields['PDF_LOGO'];

            $logo = "";
            if ($PDF_LOGO != '') {
                $logo = '<img src="' . $PDF_LOGO . '" height="50px" />';
            }

            $txt  = "";
            $txt .= '<br><br>';
            $txt .= '<table border="1" cellspacing="0" cellpadding="3" width="100%">
                        <thead>
                            <tr>
                                <th width="23%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
                                    <b><i>Program Name</i></b>
                                </th>
                                <th width="13%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
                                    <b><i>Credentail</i></b>
                                </th>
                                <th width="10%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
                                    <b><i>CIP</i></b>
                                </th>
                                <th width="9%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
                                    <b><i>Grads</i></b>
                                </th>
                                <th width="9%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
                                    <b><i>Place In Field</i></b>
                                </th>
                                <th width="9%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
                                    <b><i>Place In Related Field</i></b>
                                </th>
                                <th width="9%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
                                    <b><i>Place Out of Field</i></b>
                                </th>
                                <th width="9%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
                                    <b><i>Unavailable</i></b>
                                </th>
                                <th width="9%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
                                    <b><i>Placement Rate</i></b>
                                </th>
                            </tr>
                        </thead>
                        <tbody>';

            //echo "CALL ABHES10001(".$_SESSION['PK_ACCOUNT'].", ".$pkCampus.", ".$startDate.", ".$endDate.",'".$reportOption."', '".$group_by."')";exit;
            $res = $db->Execute("CALL ABHES10001(" . $_SESSION['PK_ACCOUNT'] . ", '" . $pkCampus . "', '" . $startDate . "','" . $endDate . "', '" . $reportOption . "', '" . $group_by . "')");
            //print_r($res);exit;
            if (count($res->fields) == 0) {
                $report_error = "No data in the report for the selections made.";
            } else {
                $CAMPUS_CODE      = $campus_name;
                $DATE_RANGE      = date("m/d/Y", strtotime($res->fields['REPORTING_PERIOD_BEGIN_DATE'])) . ' - ' . date("m/d/Y", strtotime($res->fields['REPORTING_PERIOD_END_DATE']));


                $Total_Grads                    = 0;
                $Total_EmployedInField          = 0;
                $Total_EmployedInRelatedField   = 0;
                $Total_EmployedOutOfField       = 0;
                $Total_NotAvailable             = 0;
                $Total_Placement_Rate           = 0.00;

                while (!$res->EOF) {

                    $program_Name              = $res->fields['PROGRAM_DESCRIPTION'];
                    $credentail                = $res->fields['CREDENTIAL_LEVEL'];
                    $cip_Code                  = $res->fields['CIP_CODE'];

                    $Grads                     = $res->fields['GRADS'];
                    $EmployedInField           = $res->fields['EmployedInField'];
                    $EmployedInRelatedField    = $res->fields['EmployedInRelatedField'];
                    $EmployedOutOfField        = $res->fields['EmployedOutOfField'];
                    $NotAvailable              = $res->fields['NotAvailable'];

                    $Placement_Rate            = $EmployedInField / ($Grads - $NotAvailable);

                    $txt .= '<tr>
                                <td >' . $program_Name . '</td>
                                <td >' . $credentail . '</td>
                                <td >' . $cip_Code . '</td>
                                <td align="right">' . $Grads . '</td>
                                <td align="right">' . $EmployedInField . '</td>
                                <td align="right">' . $EmployedInRelatedField . '</td>
                                <td align="right">' . $EmployedOutOfField . '</td>
                                <td align="right">' . $NotAvailable . '</td>

                                <td align="right">' . number_format_value_checker($Placement_Rate, 2) . '%</td>
                            </tr>';

                    $res->MoveNext();

                    $Total_Grads                    += $res->fields['GRADS'];
                    $Total_EmployedInField          += $res->fields['EmployedInField'];
                    $Total_EmployedInRelatedField   += $res->fields['EmployedInRelatedField'];
                    $Total_EmployedOutOfField       += $res->fields['EmployedOutOfField'];
                    $Total_NotAvailable             += $res->fields['NotAvailable'];
                    $Total_Placement_Rate           += $Total_EmployedInField / ($Total_Grads - $Total_NotAvailable);
                }

                $txt .= '<tr>
                            <td ></td>
                            <td ></td>
                            <td >Totals :</td>
                            <td align="right">' . $Total_Grads . '</td>
                            <td align="right">' . $Total_EmployedInField . '</td>
                            <td align="right">' . $Total_EmployedInRelatedField . '</td>
                            <td align="right">' . $Total_EmployedOutOfField . '</td>
                            <td align="right">' . $Total_NotAvailable . '</td>

                            <td align="right">' . number_format_value_checker($Total_Placement_Rate, 2) . '%</td>
                        </tr>';

                $txt .= '</tbody></table>';

                $header = '<table width="100%" >
                                <tr>
                                    <td width="20%" valign="top" >' . $logo . '</td>
                                    <td width="40%" valign="top" style="font-size:20px" >' . $SCHOOL_NAME . '</td>
                                    <td width="40%" valign="top" >
                                        <table width="100%" >
                                            <tr>
                                                <td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>ABHES Placement Statistics</b></td>
                                            </tr>
                                            <tr>
                                                <td align="right"><b>Campus(es) : </b> ' . $CAMPUS_CODE . '</td>
                                            </tr>
                                            <tr>
                                                <td align="right"><b>Date Range : </b> ' . $DATE_RANGE . '</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>';


                $header_cont = '<!DOCTYPE HTML>
                <html>
                <head>

                </head>
                <body>
                <div> ' . $header . ' </div>
                </body>
                </html>';

                $html_body_cont = '<!DOCTYPE HTML>
                <html>
                <head> <style>
                table{  margin-top: 2px; }
                table tr{  padding-top: 1px !important; }
                </style>
                </head>
                <body>' . $txt . '</body></html>';

                $date_footer = convert_to_user_date(date('Y-m-d H:i:s'), 'l, F d, Y h:i A', $TIMEZONE, date_default_timezone_get());

                $footer = '<table width="100%" >
                        <tr>
                            <td width="33%" valign="top" style="font-size:10px;" ><i>' . $date_footer . '</i></td>
                            <td width="33%" valign="top" style="font-size:10px;" align="center" ><i></i></td>
                            <td></td>							
                        </tr>
                    </table>';
                $footer_cont = '<!DOCTYPE HTML><html><head><style>
                    tbody td{ font-size:14px !important; }
                    </style></head><body>' . $footer . '</body></html>';

                $header_path = create_html_file('header_placement_statistics_summary.html', $header_cont, "invoice");
                $content_path = create_html_file('content_placement_statistics_summary.html', $txt, "invoice");
                $footer_path = create_html_file('footer_placement_statistics_summary.html', $footer_cont, "invoice");

                $file_name = 'Placement_Statistics_Summary_' . uniqid() . '.pdf';
                $exec = 'xvfb-run -a wkhtmltopdf -T 0 -R 0 -B 0 -L 0 --enable-local-file-access --orientation portrait --page-size A4 --page-width 300 --page-height 210 --margin-top 25mm --margin-left 7mm --margin-right 5mm  --margin-bottom 20mm --footer-font-size 8 --footer-right "Page [page] of [toPage]" --header-html ' . $header_path . ' --footer-html  ' . $footer_path . ' ' . $content_path . ' ../school/temp/invoice/' . $file_name . ' 2>&1';

                $pdfdata = array('filepath' => 'temp/invoice/' . $file_name, 'exec' => $exec, 'filename' => $file_name, 'filefullpath' => $http_path . 'school/temp/invoice/' . $file_name);

                exec($pdfdata['exec'], $output, $retval);
                echo 'school/temp/invoice/' . $file_name;
                header('Content-Type: Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . basename($pdfdata['filefullpath']) . '"');
                readfile($pdfdata['filepath']);

                unlink('../school/temp/invoice/header_placement_statistics_summary.html');
                unlink('../school/temp/invoice/content_placement_statistics_summary.html');
                unlink('../school/temp/invoice/footer_placement_statistics_summary.html');
                exit;
            }
        }
    } // End Placement Statistics
    else if ($_POST['REPORT_TYPE'] == 3) // Retention Statistics
    {
        if ($_POST['REPORT_OPTION'] == 1) // Detail
        {
            $groupProgramCode = $_POST['GROUP_PROGRAM_CODE'];
            $reportType       = $_POST['REPORT_TYPE'];
            $reportOption     = 'Retention Statistics Detail';
            $startDate        = date("Y-m-d", strtotime($_POST['START_DATE']));
            $endDate          = date("Y-m-d", strtotime($_POST['END_DATE']));

            $pkCampus      = "";
            $campus_cond  = "";
            $campus_name  = "";
            if (!empty($_POST['PK_CAMPUS'])) {
                $pkCampus      = implode(",", $_POST['PK_CAMPUS']);
                $campus_cond  = " AND PK_CAMPUS IN ($pkCampus) ";
            }

            $group_by = 0;
            if (!empty($groupProgramCode)) {
                $group_by = 1;
            }

            $res_campus = $db->Execute("select PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $campus_cond order by CAMPUS_CODE ASC");
            while (!$res_campus->EOF) {
                if ($campus_name != '')
                    $campus_name .= ', ';
                $campus_name .= $res_campus->fields['CAMPUS_CODE'];

                $res_campus->MoveNext();
            }

            $res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
            $SCHOOL_NAME = $res->fields['SCHOOL_NAME'];
            $PDF_LOGO      = $res->fields['PDF_LOGO'];

            $logo = "";
            if ($PDF_LOGO != '') {
                $logo = '<img src="' . $PDF_LOGO . '" height="50px" />';
            }

            $txt  = "";
            
            //echo "CALL ABHES10001(".$_SESSION['PK_ACCOUNT'].", ".$pkCampus.", ".$startDate.", ".$endDate.",'".$reportOption."', '".$group_by."')";exit;
            $res = $db->Execute("CALL ABHES10001(" . $_SESSION['PK_ACCOUNT'] . ", '" . $pkCampus . "', '" . $startDate . "','" . $endDate . "', '" . $reportOption . "', '" . $group_by . "')");
            //echo "<pre>";print_r($res->fields);exit;

            if (count($res->fields) == 0) {
                $report_error = "No data in the report for the selections made.";
            } else {
                $CAMPUS_CODE      = $campus_name;
                $DATE_RANGE      = date("d/m/Y", strtotime($res->fields['REPORTING_PERIOD_BEGIN_DATE'])) . ' - ' . date("d/m/Y", strtotime($res->fields['REPORTING_PERIOD_END_DATE']));

                $data = [];
                $terms = [];

                while (!$res->EOF) {
                    $data[] = $res->fields;
                    //$terms[$res->fields['PROGRAM']]=array('PROGRAM'=>$res->fields['PROGRAM'],'PROGRAM_DESCRIPTION'=>$res->fields['PROGRAM_DESCRIPTION'],'BEGIN_ENROL'=>$res->fields['BEGIN_ENROL'],'ENDING_ENROL'=>$res->fields['ENDING_ENROL'],'NEW_STARTS'=>$res->fields['NEW_STARTS'],'RE_ENTRY'=>$res->fields['RE_ENTRY'],'GRAD'=>$res->fields['GRAD']);
                    $res->MoveNext();
                }
                
                $new_array = array();
                foreach($data as $k=>$v){

                    if($v['BEGIN_ENROL']>0 && $v['BEGIN_ENROL'] == 1){
                        $new_array[$v['PROGRAM_DESCRIPTION']]['Beginning Enrollment'][]=$v;
                    }
                    if($v['NEW_STARTS']>0 && $v['NEW_STARTS'] == 1){
                        $new_array[$v['PROGRAM_DESCRIPTION']]['New Starts'][]=$v;
                    }
                    if($v['RE_ENTRY']>0 && $v['RE_ENTRY'] == 1){
                        $new_array[$v['PROGRAM_DESCRIPTION']]['Re-Entries'][]=$v;
                    }
                    if($v['ENDING_ENROL']>0 && $v['ENDING_ENROL'] == 1){
                        $new_array[$v['PROGRAM_DESCRIPTION']]['Ending Enrollment'][]=$v;
                    }
                    if($v['GRAD']>0 && $v['GRAD'] == 1){
                        $new_array[$v['PROGRAM_DESCRIPTION']]['Grads'][]=$v;
                    }
                }
             
                foreach($new_array as $program=>$val)
				{
                    $txt .= '<br><br>';
                    $txt .= '<b>Program : '.$program.'</b>';
                    $txt .= '<br>';
                    foreach($new_array[$program] as $enrolled=>$values)
                    {
                        $txt .= '<br>';
                        $txt .= '<b>'.$enrolled.'</b>';
                        $txt .= '<br><br>';
                        $txt .= '<table border="1" cellspacing="0" cellpadding="3" width="100%">
                                        <thead>
                                            <tr>
                                                <th width="3%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="center" >
                                                
                                                </th>
                                                <th width="23%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
                                                    <b><i>Student</i></b>
                                                </th>
                                                <th width="10%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="center" >
                                                    <b><i>Student ID</i></b>
                                                </th>
                                                <th width="10%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="center" >
                                                    <b><i>First Term</i></b>
                                                </th>
                                                <th width="9%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="center" >
                                                    <b><i>Status</i></b>
                                                </th>
                                                <th width="9%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="center" >
                                                    <b><i>Exp Grad Date</i></b>
                                                </th>
                                                <th width="9%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="center" >
                                                    <b><i>Grad Date</i></b>
                                                </th>
                                                <th width="9%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="center" >
                                                    <b><i>Drop Date</i></b>
                                                </th>
                                                <th width="9%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="center" >
                                                    <b><i>LDA</i></b>
                                                </th>
                                                <th width="9%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="center" >
                                                    <b><i>Determination Date</i></b>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>';
                        $cnt = 1;
                        foreach($values as $result)
                        {
                            $STUDENT          = $result['STUDENT'];
                            $STUDENT_ID       = $result['STUDENT_ID'];
                            $STUDENT_STATUS   = $result['STUDENT_STATUS'];

                            $FIRST_TERM = '';
                            if($result['FIRST_TERM'] != '' && $result['FIRST_TERM'] != '0000-00-00')
                            {
                                $FIRST_TERM = date("m/d/Y", strtotime($result['FIRST_TERM']));
                            }
                            $EXPECTED_GRAD_DATE = '';
                            if($result['EXPECTED_GRAD_DATE'] != '' && $result['EXPECTED_GRAD_DATE'] != '0000-00-00')
                            {
                                $EXPECTED_GRAD_DATE = date("m/d/Y", strtotime($result['EXPECTED_GRAD_DATE']));
                            }
                            $ORIGINAL_EXPECTED_GRAD_DATE = '';
                            if($result['ORIGINAL_EXPECTED_GRAD_DATE'] != '' && $result['ORIGINAL_EXPECTED_GRAD_DATE'] != '0000-00-00')
                            {
                                $ORIGINAL_EXPECTED_GRAD_DATE = date("m/d/Y", strtotime($result['ORIGINAL_EXPECTED_GRAD_DATE']));
                            }
                            $DROP_DATE = '';
                            if($result['DROP_DATE'] != '' && $result['DROP_DATE'] != '0000-00-00')
                            {
                                $DROP_DATE = date("m/d/Y", strtotime($result['DROP_DATE']));
                            }
                            $LDA_DATE = '';
                            if($result['LDA_DATE'] != '' && $result['LDA_DATE'] != '0000-00-00')
                            {
                                $LDA_DATE = date("m/d/Y", strtotime($result['LDA_DATE']));
                            }
                            $DETERMINATION_DATE = '';
                            if($result['DETERMINATION_DATE'] != '' && $result['DETERMINATION_DATE'] != '0000-00-00')
                            {
                                $DETERMINATION_DATE = date("m/d/Y", strtotime($result['DETERMINATION_DATE']));
                            }
    
                            $txt .= '<tr>
                                        <td align="center">' . $cnt .'</td>
                                        <td >' . $STUDENT . '</td>
                                        <td align="center">' . $STUDENT_ID . '</td>
                                        <td align="center">' . $FIRST_TERM . '</td>
                                        <td align="center">' . $STUDENT_STATUS . '</td>
                                        <td align="center">' . $EXPECTED_GRAD_DATE . '</td>
                                        <td align="center">' . $ORIGINAL_EXPECTED_GRAD_DATE . '</td>
                                        <td align="center">' . $DROP_DATE . '</td>
                                        <td align="center">' . $LDA_DATE . '</td>
                                        <td align="center">' . $DETERMINATION_DATE . '</td>
                                    </tr>';

                            

                            $cnt++;
                        }
                        $txt .= '</tbody></table>';
                        
                    }
                }

                $header = '<table width="100%" >
                                <tr>
                                    <td width="20%" valign="top" >' . $logo . '</td>
                                    <td width="40%" valign="top" style="font-size:20px" >' . $SCHOOL_NAME . '</td>
                                    <td width="40%" valign="top" >
                                        <table width="100%" >
                                            <tr>
                                                <td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>ABHES Retention Statistics</b></td>
                                            </tr>
                                            <tr>
                                                <td align="right"><b>Campus(es) : </b> ' . $CAMPUS_CODE . '</td>
                                            </tr>
                                            <tr>
                                                <td align="right"><b>Date Range : </b> ' . $DATE_RANGE . '</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>';


                $header_cont = '<!DOCTYPE HTML>
                <html>
                <head>

                </head>
                <body>
                <div> ' . $header . ' </div>
                </body>
                </html>';

                $html_body_cont = '<!DOCTYPE HTML>
                <html>
                <head> <style>
                table{  margin-top: 2px; }
                table tr{  padding-top: 1px !important; }
                </style>
                </head>
                <body>' . $txt . '</body></html>';

                $date_footer = convert_to_user_date(date('Y-m-d H:i:s'), 'l, F d, Y h:i A', $TIMEZONE, date_default_timezone_get());

                $footer = '<table width="100%" >
                        <tr>
                            <td width="33%" valign="top" style="font-size:10px;" ><i>' . $date_footer . '</i></td>
                            <td width="33%" valign="top" style="font-size:10px;" align="center" ><i></i></td>
                            <td></td>							
                        </tr>
                    </table>';
                $footer_cont = '<!DOCTYPE HTML><html><head><style>
                    tbody td{ font-size:14px !important; }
                    </style></head><body>' . $footer . '</body></html>';

                $header_path = create_html_file('header_retention_statistics_detail.html', $header_cont, "invoice");
                $content_path = create_html_file('content_retention_statistics_detail.html', $txt, "invoice");
                $footer_path = create_html_file('footer_retention_statistics_detail.html', $footer_cont, "invoice");

                $file_name = 'Retention_Statistics_Detail_' . uniqid() . '.pdf';
                $exec = 'xvfb-run -a wkhtmltopdf -T 0 -R 0 -B 0 -L 0 --enable-local-file-access --orientation portrait --page-size A4 --page-width 300 --page-height 210 --margin-top 25mm --margin-left 7mm --margin-right 5mm  --margin-bottom 20mm --footer-font-size 8 --footer-right "Page [page] of [toPage]" --header-html ' . $header_path . ' --footer-html  ' . $footer_path . ' ' . $content_path . ' ../school/temp/invoice/' . $file_name . ' 2>&1';

                $pdfdata = array('filepath' => 'temp/invoice/' . $file_name, 'exec' => $exec, 'filename' => $file_name, 'filefullpath' => $http_path . 'school/temp/invoice/' . $file_name);

                exec($pdfdata['exec'], $output, $retval);
                echo 'school/temp/invoice/' . $file_name;
                header('Content-Type: Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . basename($pdfdata['filefullpath']) . '"');
                readfile($pdfdata['filepath']);

                unlink('../school/temp/invoice/header_retention_statistics_detail.html');
                unlink('../school/temp/invoice/content_retention_statistics_detail.html');
                unlink('../school/temp/invoice/footer_retention_statistics_detail.html');
                exit;
            }
        }
        else if ($_POST['REPORT_OPTION'] == 2) // Summary
        {
            $groupProgramCode = $_POST['GROUP_PROGRAM_CODE'];
            $reportType       = $_POST['REPORT_TYPE'];
            $reportOption     = 'Retention Statistics Summary';
            $startDate        = date("Y-m-d", strtotime($_POST['START_DATE']));
            $endDate          = date("Y-m-d", strtotime($_POST['END_DATE']));

            $pkCampus      = "";
            $campus_cond  = "";
            $campus_name  = "";
            if (!empty($_POST['PK_CAMPUS'])) {
                $pkCampus      = implode(",", $_POST['PK_CAMPUS']);
                $campus_cond  = " AND PK_CAMPUS IN ($pkCampus) ";
            }

            $group_by = 0;
            if (!empty($groupProgramCode)) {
                $group_by = 1;
            }

            $res_campus = $db->Execute("select PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $campus_cond order by CAMPUS_CODE ASC");
            while (!$res_campus->EOF) {
                if ($campus_name != '')
                    $campus_name .= ', ';
                $campus_name .= $res_campus->fields['CAMPUS_CODE'];

                $res_campus->MoveNext();
            }

            $res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
            $SCHOOL_NAME = $res->fields['SCHOOL_NAME'];
            $PDF_LOGO      = $res->fields['PDF_LOGO'];

            $logo = "";
            if ($PDF_LOGO != '') {
                $logo = '<img src="' . $PDF_LOGO . '" height="50px" />';
            }

            $txt  = "";
            $txt .= '<br><br>';
            $txt .= '<table border="1" cellspacing="0" cellpadding="3" width="100%">
                        <thead>
                            <tr>
                                <th width="23%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
                                    <b><i>Program Name</i></b>
                                </th>
                                <th width="13%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
                                    <b><i>Credentail</i></b>
                                </th>
                                <th width="10%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
                                    <b><i>CIP</i></b>
                                </th>
                                <th width="9%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
                                    <b><i>Begning Enrollment</i></b>
                                </th>
                                <th width="9%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
                                    <b><i>New Starts</i></b>
                                </th>
                                <th width="9%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
                                    <b><i>Re-Entries</i></b>
                                </th>
                                <th width="9%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
                                    <b><i>Ending Enrollment</i></b>
                                </th>
                                <th width="9%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
                                    <b><i>Grads</i></b>
                                </th>
                                <th width="9%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
                                    <b><i>Retention Rate</i></b>
                                </th>
                            </tr>
                        </thead>
                        <tbody>';

            //echo "CALL ABHES10001(".$_SESSION['PK_ACCOUNT'].", ".$pkCampus.", ".$startDate.", ".$endDate.",'".$reportOption."', '".$group_by."')";exit;
            $res = $db->Execute("CALL ABHES10001(" . $_SESSION['PK_ACCOUNT'] . ", '" . $pkCampus . "', '" . $startDate . "','" . $endDate . "', '" . $reportOption . "', '" . $group_by . "')");

            if (count($res->fields) == 0) {
                $report_error = "No data in the report for the selections made.";
            } else {
                $CAMPUS_CODE      = $campus_name;
                $DATE_RANGE      = date("m/d/Y", strtotime($res->fields['REPORTING_PERIOD_BEGIN_DATE'])) . ' - ' . date("m/d/Y", strtotime($res->fields['REPORTING_PERIOD_END_DATE']));

                $data = [];
                $terms = [];

                while (!$res->EOF) {
                    $data[$res->fields['PROGRAM']][] = $res->fields;
                    // $terms[$res->fields['PROGRAM']]=array('PROGRAM'=>$res->fields['PROGRAM'],'PROGRAM_DESCRIPTION'=>$res->fields['PROGRAM_DESCRIPTION'],'CREDENTIAL_LEVEL'=>$res->fields['CREDENTIAL_LEVEL']);
                    $res->MoveNext();
                }
                // echo "<pre>";
                // print_r($data);exit;

                $actual_data_rows  = [];
                foreach ($data as $program => $program_data_rows) 
                {
                    $Beginning_Enrollment = 0;
                    $Ending_Enrollment = 0;
                    $New_Starts = 0;
                    $Re_Entries = 0;
                    $Grads = 0;
                    $program_Name = $program;

                    foreach ($program_data_rows as $program_data_row) 
                    {
                        $credentail = $program_data_row['CREDENTIAL_LEVEL'];
                        $cip_Code =  $program_data_row['CIP_CODE'];

                        if ($program_data_row['RECORD_TYPE'] == 'Beginning Enrollment') {
                            $Beginning_Enrollment = $program_data_row['COUNT'];
                        }
                        if ($program_data_row['RECORD_TYPE'] == 'Ending Enrollment') {
                            $Ending_Enrollment = $program_data_row['COUNT'];
                        }
                        if ($program_data_row['RECORD_TYPE'] == 'New Starts') {
                            $New_Starts = $program_data_row['COUNT'];
                        }
                        if ($program_data_row['RECORD_TYPE'] == 'Re-Entries') {
                            $Re_Entries = $program_data_row['COUNT'];
                        }
                        if ($program_data_row['RECORD_TYPE'] == 'Grads') {
                            $Grads = $program_data_row['COUNT'];
                        }
                    }
                  
                    $actual_data_rows[$program_Name]['PROGRAM_DESCRIPTION']  = $program_Name;
                    $actual_data_rows[$program_Name]['CREDENTIAL_LEVEL']     = $credentail;
                    $actual_data_rows[$program_Name]['CIP_CODE']             = $cip_Code;
                    $actual_data_rows[$program_Name]['Beginning_Enrollment'] = $Beginning_Enrollment;
                    $actual_data_rows[$program_Name]['Ending_Enrollment']    = $Ending_Enrollment;
                    $actual_data_rows[$program_Name]['New_Starts']           = $New_Starts;
                    $actual_data_rows[$program_Name]['Re_Entries']           = $Re_Entries;
                    $actual_data_rows[$program_Name]['Grads']                = $Grads;
                }
                ksort($actual_data_rows);
                // echo "<pre>";
                // print_r($actual_data_rows);exit;

                $Total_Beginning_Enrollment  = 0;
                $Total_Ending_Enrollment     = 0;
                $Total_New_Starts            = 0;
                $Total_Re_Entries            = 0;
                $Total_Grads                 = 0;

                foreach ($actual_data_rows as $k => $result) {
                    
                    $program_Name              = $result['PROGRAM_DESCRIPTION'];
                    $credentail                = $result['CREDENTIAL_LEVEL'];
                    $cip_Code                  = $result['CIP_CODE'];

                    $Beginning_Enrollment      = $result['Beginning_Enrollment'] ? $result['Beginning_Enrollment'] : 0;
                    $Ending_Enrollment         = $result['Ending_Enrollment'] ? $result['Ending_Enrollment'] : 0;
                    $New_Starts                = $result['New_Starts'] ? $result['New_Starts'] : 0;
                    $Re_Entries                = $result['Re_Entries'] ? $result['Re_Entries'] : 0;
                    $Grads                     = $result['Grads'] ? $result['Grads'] : 0;

                    $Retention_Rate            = $Ending_Enrollment+ $Grads / $Beginning_Enrollment + $New_Starts + $Re_Entries;

                    $txt .= '<tr>
                                <td >' . $program_Name . '</td>
                                <td >' . $credentail . '</td>
                                <td >' . $cip_Code . '</td>

                                <td align="right">' . $Beginning_Enrollment . '</td>
                                <td align="right">' . $New_Starts . '</td>
                                <td align="right">' . $Re_Entries . '</td>
                                <td align="right">' . $Ending_Enrollment . '</td>
                                <td align="right">' . $Grads . '</td>

                                <td align="right">' . number_format_value_checker($Retention_Rate, 2) . '%</td>
                            </tr>';

                    $Total_Beginning_Enrollment  += $result['Beginning_Enrollment'];
                    $Total_Ending_Enrollment     += $result['Ending_Enrollment'];
                    $Total_New_Starts            += $result['New_Starts'];
                    $Total_Re_Entries            += $result['Re_Entries'];
                    $Total_Grads                 += $result['Grads'];
                }

                $txt .= '<tr>
                            <td ></td>
                            <td ></td>
                            <td ><b>Totals :</b></td>
                            <td align="right"><b>' . $Total_Beginning_Enrollment . '</b></td>
                            <td align="right"><b>' . $Total_New_Starts . '</b></td>
                            <td align="right"><b>' . $Total_Re_Entries . '</b></td>
                            <td align="right"><b>' . $Total_Ending_Enrollment . '</b></td>
                            <td align="right"><b>' . $Total_Grads . '</b></td>
                            <td align="right"></td>
                        </tr>';

                $txt .= '</tbody></table>';

                $header = '<table width="100%" >
                                <tr>
                                    <td width="20%" valign="top" >' . $logo . '</td>
                                    <td width="40%" valign="top" style="font-size:20px" >' . $SCHOOL_NAME . '</td>
                                    <td width="40%" valign="top" >
                                        <table width="100%" >
                                            <tr>
                                                <td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>ABHES Retention Statistics</b></td>
                                            </tr>
                                            <tr>
                                                <td align="right"><b>Campus(es) : </b> ' . $CAMPUS_CODE . '</td>
                                            </tr>
                                            <tr>
                                                <td align="right"><b>Date Range : </b> ' . $DATE_RANGE . '</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>';


                $header_cont = '<!DOCTYPE HTML>
                <html>
                <head>

                </head>
                <body>
                <div> ' . $header . ' </div>
                </body>
                </html>';

                $html_body_cont = '<!DOCTYPE HTML>
                <html>
                <head> <style>
                table{  margin-top: 2px; }
                table tr{  padding-top: 1px !important; }
                </style>
                </head>
                <body>' . $txt . '</body></html>';

                $date_footer = convert_to_user_date(date('Y-m-d H:i:s'), 'l, F d, Y h:i A', $TIMEZONE, date_default_timezone_get());

                $footer = '<table width="100%" >
                        <tr>
                            <td width="33%" valign="top" style="font-size:10px;" ><i>' . $date_footer . '</i></td>
                            <td width="33%" valign="top" style="font-size:10px;" align="center" ><i></i></td>
                            <td></td>							
                        </tr>
                    </table>';
                $footer_cont = '<!DOCTYPE HTML><html><head><style>
                    tbody td{ font-size:14px !important; }
                    </style></head><body>' . $footer . '</body></html>';

                $header_path = create_html_file('header_retention_statistics_summary.html', $header_cont, "invoice");
                $content_path = create_html_file('content_retention_statistics_summary.html', $txt, "invoice");
                $footer_path = create_html_file('footer_retention_statistics_summary.html', $footer_cont, "invoice");

                $file_name = 'Retention_Statistics_Summary_' . uniqid() . '.pdf';
                $exec = 'xvfb-run -a wkhtmltopdf -T 0 -R 0 -B 0 -L 0 --enable-local-file-access --orientation portrait --page-size A4 --page-width 300 --page-height 210 --margin-top 25mm --margin-left 7mm --margin-right 5mm  --margin-bottom 20mm --footer-font-size 8 --footer-right "Page [page] of [toPage]" --header-html ' . $header_path . ' --footer-html  ' . $footer_path . ' ' . $content_path . ' ../school/temp/invoice/' . $file_name . ' 2>&1';

                $pdfdata = array('filepath' => 'temp/invoice/' . $file_name, 'exec' => $exec, 'filename' => $file_name, 'filefullpath' => $http_path . 'school/temp/invoice/' . $file_name);

                exec($pdfdata['exec'], $output, $retval);
                echo 'school/temp/invoice/' . $file_name;
                header('Content-Type: Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . basename($pdfdata['filefullpath']) . '"');
                readfile($pdfdata['filepath']);

                unlink('../school/temp/invoice/header_retention_statistics_summary.html');
                unlink('../school/temp/invoice/content_retention_statistics_summary.html');
                unlink('../school/temp/invoice/footer_retention_statistics_summary.html');
                exit;
            }
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

        $group_by = 0;
        if (!empty($groupProgramCode)) {
            $group_by = 1;
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

        //echo "CALL ABHES10001(".$_SESSION['PK_ACCOUNT'].", ".$pkCampus.", ".$startDate.", ".$endDate.",'".$reportOption."', '".$group_by."')";exit;
        $res = $db->Execute("CALL ABHES10001(" . $_SESSION['PK_ACCOUNT'] . ", '" . $pkCampus . "', '" . $startDate . "','" . $endDate . "', '" . $reportOption . "', '" . $group_by . "')");

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
                                            <select id="REPORT_TYPE" name="REPORT_TYPE" class="form-control" onchange="ReportFilters(this.value);">
                                                <option value="1"><?= MNU_LICENSURE_STATISTICS ?></option>
                                                <option value="2"><?= MNU_PLACEMENT_STATISTICS ?></option>
                                                <option value="3"><?= MNU_RETENTION_STATISTICS ?></option>
                                                <option value="4"><?= MNU_PROGRAM_TEMPLATE ?></option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <div id="report_option_div">
                                                <b>Report Option</b>
                                                <select id="REPORT_OPTION" name="REPORT_OPTION" class="form-control">
                                                    <option value="1">Detail</option>
                                                    <option value="2">Summary</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
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
                                        <div class="col-md-2 align-self-center" id="GROUP_PROGRAM_CODE_DIV">
                                            <div class="col-12 col-sm-12 custom-control custom-checkbox form-group">
                                                <input type="checkbox" class="custom-control-input" id="GROUP_PROGRAM_CODE" name="GROUP_PROGRAM_CODE" value="1">
                                                <label class="custom-control-label" for="GROUP_PROGRAM_CODE"><?= GROUP_PROGRAM_CODE ?></label>
                                            </div>
                                        </div>
                                        <div class="col-md-3" style="padding: 0;">
                                            <br />
                                            <button type="button" onclick="submit_form()" id="btn_1" class="btn waves-effect waves-light btn-info"><?= PDF ?></button>
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

            ReportFilters(1);

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

        function ReportFilters(report_value) {

            if (report_value == 1 || report_value == 2 || report_value == 3) {
                document.getElementById('btn_2').style.display = 'none';
                document.getElementById('btn_1').style.display = 'inline';
                document.getElementById('report_option_div').style.display = 'inline';
            } else {
                document.getElementById('btn_2').style.display = 'inline';
                document.getElementById('btn_1').style.display = 'none';
                document.getElementById('report_option_div').style.display = 'none';
            }
        }

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