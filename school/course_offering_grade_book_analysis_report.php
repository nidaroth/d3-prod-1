<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
require_once("check_access.php");

// error_reporting(E_ALL);
// ini_set('display_errors', 1);
if (check_access('REPORT_REGISTRAR') == 0 && check_access('REGISTRAR_ACCESS') == 0 && $_SESSION['PK_ROLES'] != 3) { //Ticket # 1472
    header("location:../index");
    exit;
}
// Array ( [co_id] => 72910,76035,76147 [format] => 1 [campus] => 18,17 ) 
// print_r($_GET);exit;
$campus_name    = "";
$campus_cond    = "";
$campus_cond1   = "";
$campus_id      = "";
if ($_GET['campus'] != '') {
    $PK_CAMPUS   = $_GET['campus'];
    $campus_cond  = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
    $campus_cond1 = " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
}
$GET_co_id = explode(',', $_GET['co_id']);

if ($_GET['format'] == 1) 
{
    $browser = '';
    if (stripos($_SERVER['HTTP_USER_AGENT'], "chrome") != false)
        $browser =  "chrome";
    else if (stripos($_SERVER['HTTP_USER_AGENT'], "Safari") != false)
        $browser = "Safari";
    else
        $browser = "firefox";
    require_once('../global/tcpdf/config/lang/eng.php');
    require_once('../global/tcpdf/tcpdf.php');


    class MYPDF extends TCPDF
    {
        public $co_id_class_iterator_index = 0;
        public function Header()
        {
            global $db, $campus_cond, $GET_co_id;

            $GET_co_id_2 = $GET_co_id[$this->co_id_class_iterator_index];
            // echo $GET_co_id_2;exit;
            $this->co_id_class_iterator_index = $this->co_id_class_iterator_index + 1;

            $res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

            if ($res->fields['PDF_LOGO'] != '') {
                $ext = explode(".", $res->fields['PDF_LOGO']);
                $this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
            }

            $this->SetFont('helvetica', '', 15);
            $this->SetY(5);
            $this->SetX(55);
            $this->SetTextColor(000, 000, 000);
            //$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
            $this->MultiCell(55, 5, $res->fields['SCHOOL_NAME'], 0, 'L', 0, 0, '', '', true);

            $this->SetFont('helvetica', 'I', 14);
            $this->SetY(8);
            $this->SetX(120);
            $this->SetTextColor(000, 000, 000);
            $this->Cell(55, 8, "Course Offering Grade Book Analysis", 0, false, 'L', 0, '', 0, false, 'M', 'L');


            $this->SetFillColor(0, 0, 0);
            $style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
            $this->Line(120, 13, 202, 13, $style);

            $cond = "";
            $res_campus = $db->Execute("select CAMPUS_CODE from S_CAMPUS, S_COURSE_OFFERING WHERE S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND  S_COURSE_OFFERING.PK_CAMPUS = S_CAMPUS.PK_CAMPUS AND S_COURSE_OFFERING.PK_COURSE_OFFERING IN ($GET_co_id_2) order by CAMPUS_CODE ASC");
            while (!$res_campus->EOF) {
                if ($campus_name != '')
                    $campus_name .= ', ';
                $campus_name .= $res_campus->fields['CAMPUS_CODE'];

                $res_campus->MoveNext();
            }

            $this->SetFont('helvetica', 'I', 11);
            $this->SetY(14);
            $this->SetX(98);
            $this->SetTextColor(000, 000, 000);
            //$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
            $this->MultiCell(102, 5, "Campus(es): " . $campus_name, 0, 'R', 0, 0, '', '', true);

            $res_cs = $db->Execute("select CONCAT(S_EMPLOYEE_MASTER_INST.FIRST_NAME,', ',S_EMPLOYEE_MASTER_INST.LAST_NAME) AS INSTRUCTOR_NAME, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION, SESSION, SESSION_NO, COURSE_CODE, COURSE_DESCRIPTION from S_COURSE_OFFERING LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER LEFT JOIN S_EMPLOYEE_MASTER AS S_EMPLOYEE_MASTER_INST ON S_EMPLOYEE_MASTER_INST.PK_EMPLOYEE_MASTER = INSTRUCTOR, S_COURSE WHERE S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.PK_COURSE_OFFERING IN ($GET_co_id_2) AND S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE");

            $this->SetY(23);
            $this->SetX(147);
            $this->SetTextColor(000, 000, 000);
            $this->Cell(55, 8, "Term: " . $res_cs->fields['BEGIN_DATE_1'] . ' - ' . $res_cs->fields['END_DATE_1'], 0, false, 'R', 0, '', 0, false, 'M', 'L');

            $this->SetY(28);
            $this->SetX(147);
            $this->SetTextColor(000, 000, 000);
            $this->Cell(55, 8, $res_cs->fields['TERM_DESCRIPTION'], 0, false, 'R', 0, '', 0, false, 'M', 'L');

            $this->SetY(33);
            $this->SetX(147);
            $this->SetTextColor(000, 000, 000);
            $this->Cell(55, 8, "Course Offering: " . $res_cs->fields['COURSE_CODE'] . ' (' . substr($res_cs->fields['SESSION'], 0, 1) . ' - ' . $res_cs->fields['SESSION_NO'] . ') ' . $res_cs->fields['COURSE_DESCRIPTION'], 0, false, 'R', 0, '', 0, false, 'M', 'L');

            $this->SetY(38);
            $this->SetX(147);
            $this->SetTextColor(000, 000, 000);
            $this->Cell(55, 8, "Instructor: " . $res_cs->fields['INSTRUCTOR_NAME'], 0, false, 'R', 0, '', 0, false, 'M', 'L');
        }
        public function Footer()
        {
            global $db;
            $this->SetY(-15);
            $this->SetX(180);
            $this->SetFont('helvetica', 'I', 7);
            $this->Cell(30, 10, 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

            $this->SetY(-15);
            $this->SetX(10);
            $this->SetFont('helvetica', 'I', 7);

            $timezone = $_SESSION['PK_TIMEZONE'];
            if ($timezone == '' || $timezone == 0) {
                $res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
                $timezone = $res->fields['PK_TIMEZONE'];
                if ($timezone == '' || $timezone == 0)
                    $timezone = 4;
            }

            $res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
            $date = convert_to_user_date(date('Y-m-d H:i:s'), 'l, F d, Y h:i A', $res->fields['TIMEZONE'], date_default_timezone_get());

            $this->Cell(30, 10, $date, 0, false, 'C', 0, '', 0, false, 'T', 'M');
        }
    }

    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . ' 001', PDF_HEADER_STRING, array(0, 64, 255), array(0, 64, 128));
    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(7, 43, 7);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    //$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->SetAutoPageBreak(TRUE, 30);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    $pdf->setLanguageArray($l);
    $pdf->setFontSubsetting(true);
    $pdf->SetFont('helvetica', '', 8, '', true);
    // $pdf->AddPage();
}

if ($_GET['format'] == 2) 
{
    include '../global/excel/Classes/PHPExcel/IOFactory.php';

    $dir             = 'temp/';
    $inputFileType  = 'Excel2007';
    $file_name      = 'Course Offering Grade Book Analysis.xlsx';
    $outputFileName = $dir . $file_name;
    $outputFileName = str_replace(
        pathinfo($outputFileName, PATHINFO_FILENAME),
        pathinfo($outputFileName, PATHINFO_FILENAME) . "_" . $_SESSION['PK_USER'] . "_" . time(),
        $outputFileName
    );

    $objReader      = PHPExcel_IOFactory::createReader($inputFileType);
    $objReader->setIncludeCharts(TRUE);
    //$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
    $objPHPExcel = new PHPExcel();
    $objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

    $cell1  = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
    define('EOL', (PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

    $total_fields = 120;
    for ($i = 0; $i <= $total_fields; $i++) {
        if ($i <= 25)
            $cell[] = $cell1[$i];
        else {
            $j = floor($i / 26) - 1;
            $k = ($i % 26);
            //echo $j."--".$k."<br />";
            $cell[] = $cell1[$j] . $cell1[$k];
        }
    }


    $line = 1;
    $index  = -1;
    $heading[] = 'Campus';
    $width[]   = 20;
    $heading[] = 'Term';
    $width[]   = 20;
    $heading[] = 'Course Code';
    $width[]   = 20;
    $heading[] = 'Course Description';
    $width[]   = 20;
    $heading[] = 'Session';
    $width[]   = 20;
    $heading[] = 'Session Number';
    $width[]   = 20;
    $heading[] = 'Instructor';
    $width[]   = 20;
    $heading[] = 'Secondary Instructor';
    $width[]   = 20;
    $heading[] = 'Course Offering Status';
    $width[]   = 20;
    $heading[] = 'Room';
    $width[]   = 20;
    $heading[] = 'Last Name';
    $width[]   = 20;
    $heading[] = 'First Name';
    $width[]   = 20;
    $heading[] = 'Student ID';
    $width[]   = 20;
    $heading[] = 'Email';
    $width[]   = 20;
    $heading[] = 'Home Phone';
    $width[]   = 20;
    $heading[] = 'Mobile Phone';
    $width[]   = 20;
    $heading[] = 'Program';
    $width[]   = 20;
    $heading[] = 'Status';
    $width[]   = 20;
    $heading[] = 'Course Offering Student Status';
    $width[]   = 20;
    $heading[] = 'Final Grade';
    $width[]   = 20;
    $heading[] = 'Final Total';
    $width[]   = 20;
    $heading[] = 'Current Total';
    $width[]   = 20;

    $PK_COURSE_OFFERING_GRADE_ARR = array();
    $result1 = $db->Execute("SELECT S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING_GRADE,S_COURSE_OFFERING_GRADE.CODE,S_COURSE_OFFERING_GRADE.POINTS,S_COURSE_OFFERING_GRADE.WEIGHTED_POINTS,S_COURSE.COURSE_CODE,M_SESSION.SESSION, S_COURSE_OFFERING.SESSION_NO
        FROM S_COURSE_OFFERING_GRADE
        LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING
        LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
        LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION
        WHERE S_COURSE_OFFERING_GRADE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING IN ($_GET[co_id]) 
        ORDER BY S_COURSE.COURSE_CODE,M_SESSION.SESSION, S_COURSE_OFFERING.SESSION_NO, S_COURSE_OFFERING_GRADE.GRADE_ORDER, S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING_GRADE ASC ");
    while (!$result1->EOF) {
        $heading[] = $result1->fields['COURSE_CODE'] ." (".substr($result1->fields['SESSION'],0,1) ."-". $result1->fields['SESSION_NO'] .")". "\n" . $result1->fields['CODE'] . "\nPTS: " . $result1->fields['POINTS'] . "\nWTD: " . $result1->fields['WEIGHTED_POINTS'];
        $width[]   = 20;

        $PK_COURSE_OFFERING_GRADE_ARR[] = $result1->fields['PK_COURSE_OFFERING_GRADE'];

        $result1->MoveNext();
    }

    $i = 0;
    foreach ($heading as $title) {
        $index++;
        $cell_no = $cell[$index] . $line;
        $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
        $objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);

        $objPHPExcel->getActiveSheet()->getStyle($cell_no)->getAlignment()->setWrapText(true);

        $i++;
    }
    $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(60);
}

// print_r($GET_co_id);exit;
foreach ($GET_co_id as $co_id_iterator) {
    # code...
    // echo $co_id_iterator; 
    $_GET['co_id'] = $co_id_iterator;


    $stud_query = "select S_STUDENT_COURSE.PK_STUDENT_COURSE, S_STUDENT_MASTER.PK_STUDENT_MASTER, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, S_STUDENT_MASTER.LAST_NAME, S_STUDENT_MASTER.FIRST_NAME, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ',S_STUDENT_MASTER.FIRST_NAME, ' ', SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) AS STUD_NAME, STUDENT_ID, STUDENT_STATUS, COURSE_CODE, COURSE_DESCRIPTION, SESSION, SESSION_NO, CONCAT(S_EMPLOYEE_MASTER_INST.FIRST_NAME,', ',S_EMPLOYEE_MASTER_INST.LAST_NAME) AS INSTRUCTOR_NAME, COURSE_OFFERING_STATUS, CONCAT(ROOM_NO,' - ',ROOM_DESCRIPTION) AS ROOM_NO, S_STUDENT_CONTACT.HOME_PHONE, S_STUDENT_CONTACT.CELL_PHONE, S_STUDENT_CONTACT.EMAIL, M_CAMPUS_PROGRAM.CODE, COURSE_OFFERING_STUDENT_STATUS, CURRENT_TOTAL_OBTAINED, CURRENT_MAX_TOTAL, FINAL_TOTAL_OBTAINED, FINAL_MAX_TOTAL, GRADE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d' )) AS  BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%Y-%m-%d' )) AS  END_DATE_1, TERM_DESCRIPTION, CAMPUS_CODE    
    from 
    S_STUDENT_MASTER 
    LEFT JOIN S_STUDENT_CONTACT ON S_STUDENT_CONTACT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND PK_STUDENT_CONTACT_TYPE_MASTER = '1'  
    LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
    , S_STUDENT_ENROLLMENT 
    LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
    LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
    LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
    LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
    , S_STUDENT_COURSE
    LEFT JOIN M_COURSE_OFFERING_STUDENT_STATUS ON M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS 
    LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = FINAL_GRADE
    , S_COURSE_OFFERING 
    LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER 
    LEFT JOIN M_CAMPUS_ROOM ON M_CAMPUS_ROOM.PK_CAMPUS_ROOM = S_COURSE_OFFERING.PK_CAMPUS_ROOM 
    LEFT JOIN M_COURSE_OFFERING_STATUS ON M_COURSE_OFFERING_STATUS.PK_COURSE_OFFERING_STATUS = S_COURSE_OFFERING.PK_COURSE_OFFERING_STATUS 
    LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
    LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
    LEFT JOIN S_EMPLOYEE_MASTER AS S_EMPLOYEE_MASTER_INST ON S_EMPLOYEE_MASTER_INST.PK_EMPLOYEE_MASTER = INSTRUCTOR 
    WHERE 
    S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
    S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
    S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT AND 
    S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND S_STUDENT_COURSE.PK_COURSE_OFFERING IN ($_GET[co_id]) GROUP BY S_STUDENT_COURSE.PK_STUDENT_COURSE ";

    if ($_GET['format'] == 1) {
        /////////////////////////////////////////////////////////////////


        $txt    = '';

        $res_cs = $db->Execute("select COURSE_CODE,COURSE_DESCRIPTION from S_COURSE_OFFERING, S_COURSE WHERE S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.PK_COURSE_OFFERING IN ($_GET[co_id]) AND S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE");

        $txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
                    <thead>
                        <tr>
                            <td width="100%" ><b style="font-size:40px" >' . $res_cs->fields['COURSE_DESCRIPTION'] . '</b></td>
                        </tr>
                        <tr>
                            <td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br /><b>Student</b></td>
                            <td width="15%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br /><b>Student ID</b></td>
                            <td width="15%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br /><b>Program</b></td>
                            <td width="15%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br /><b>Status</b></td>
                            <td width="15%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Course Offering<br />Student Status</b></td>
                            
                            <td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Current Total</b></td>
                            <td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Final Total</b></td>
                            <td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Final Grade</b></td>
                        </tr>
                    </thead>';
        $res_en = $db->Execute($stud_query . " ORDER BY CONCAT(S_STUDENT_MASTER.LAST_NAME,', ',S_STUDENT_MASTER.FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC ");
        while (!$res_en->EOF) {
            /** DIAM - 601 ***/
            $FINAL_PERCENTAGE  = number_format_value_checker(($res_en->fields['FINAL_TOTAL_OBTAINED'] / $res_en->fields['FINAL_MAX_TOTAL'] * 100), 2);
            if(has_ccmc_access($_SESSION['PK_ACCOUNT'],1)){
            $FINAL_PERCENTAGE  = number_format_value_checker(($res_en->fields['FINAL_TOTAL_OBTAINED'] / round($res_en->fields['FINAL_MAX_TOTAL']) * 100), 2);
            }
            $CURRENT_PERCENTAGE = number_format_value_checker(($res_en->fields['CURRENT_TOTAL_OBTAINED'] / $res_en->fields['CURRENT_MAX_TOTAL'] * 100), 2);
            /** End DIAM - 601 ***/
            $txt    .= '<tr>
                            <td width="20%" >' . $res_en->fields['STUD_NAME'] . '</td>
                            <td width="15%" >' . $res_en->fields['STUDENT_ID'] . '</td>
                            <td width="15%" >' . $res_en->fields['CODE'] . '</td>
                            <td width="15%" >' . $res_en->fields['STUDENT_STATUS'] . '</td>
                            <td width="15%" >' . $res_en->fields['COURSE_OFFERING_STATUS'] . '</td>
                            <td width="8%" >' . $CURRENT_PERCENTAGE . ' %</td>
                            <td width="7%" >' . $FINAL_PERCENTAGE . ' %</td>
                            <td width="7%" align="center" >' . $res_en->fields['GRADE'] . '</td>
                        </tr>';
            $res_en->MoveNext();
        }
        $txt    .= '</table>';

        //echo $txt;exit;
        $pdf->AddPage();
        $pdf->writeHTML($txt, $ln = true, $fill = false, $reseth = true, $cell = true, $align = '');

        // $file_name = 'Course Offering Grade Book Analysis.pdf';
        // $pdf->Output('temp/'.$file_name, 'FD');
        // return $file_name;
        /*if($browser == 'Safari')
            $pdf->Output('temp/'.$file_name, 'FD');
        else    
            $pdf->Output($file_name, 'I');*/


        /////////////////////////////////////////////////////////////////
    } else if ($_GET['format'] == 2) {
        
    

        $res_en = $db->Execute($stud_query . " ORDER BY CAMPUS_CODE ASC, COURSE_CODE ASC, SESSION ASC, SESSION_NO ASC, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ',S_STUDENT_MASTER.FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC ");
        while (!$res_en->EOF) {
            $PK_STUDENT_MASTER = $res_en->fields['PK_STUDENT_MASTER'];

            $line++;
            $index = -1;

            $index++;
            $cell_no = $cell[$index] . $line;
            $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en->fields['CAMPUS_CODE']);

            $index++;
            $cell_no = $cell[$index] . $line;
            $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en->fields['BEGIN_DATE_1'] . ' - ' . $res_en->fields['END_DATE_1'] . ' ' . $res_en->fields['TERM_DESCRIPTION']);

            $index++;
            $cell_no = $cell[$index] . $line;
            $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en->fields['COURSE_CODE']);

            $index++;
            $cell_no = $cell[$index] . $line;
            $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en->fields['COURSE_DESCRIPTION']);

            $index++;
            $cell_no = $cell[$index] . $line;
            $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en->fields['SESSION']);

            $index++;
            $cell_no = $cell[$index] . $line;
            $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en->fields['SESSION_NO']);

            $index++;
            $cell_no = $cell[$index] . $line;
            $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en->fields['INSTRUCTOR_NAME']);

            $ASSISTANT_NAME = '';
            $res_ass = $db->Execute("SELECT CONCAT(FIRST_NAME,' ',MIDDLE_NAME,' ',LAST_NAME) AS ASSISTANT_NAME FROM S_COURSE_OFFERING_ASSISTANT, S_EMPLOYEE_MASTER WHERE S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_COURSE_OFFERING_ASSISTANT.ASSISTANT AND PK_COURSE_OFFERING IN ($_GET[co_id]) AND S_COURSE_OFFERING_ASSISTANT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
            while (!$res_ass->EOF) {
                if ($ASSISTANT_NAME != '')
                    $ASSISTANT_NAME .= ', ';
                $ASSISTANT_NAME .= $res_ass->fields['ASSISTANT_NAME'];

                $res_ass->MoveNext();
            }

            $index++;
            $cell_no = $cell[$index] . $line;
            $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($ASSISTANT_NAME);

            $index++;
            $cell_no = $cell[$index] . $line;
            $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en->fields['COURSE_OFFERING_STATUS']);

            $index++;
            $cell_no = $cell[$index] . $line;
            $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en->fields['ROOM_NO']);

            $index++;
            $cell_no = $cell[$index] . $line;
            $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en->fields['LAST_NAME']);

            $index++;
            $cell_no = $cell[$index] . $line;
            $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en->fields['FIRST_NAME']);

            $index++;
            $cell_no = $cell[$index] . $line;
            $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en->fields['STUDENT_ID']);

            $index++;
            $cell_no = $cell[$index] . $line;
            $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en->fields['EMAIL']);

            $index++;
            $cell_no = $cell[$index] . $line;
            $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en->fields['HOME_PHONE']);

            $index++;
            $cell_no = $cell[$index] . $line;
            $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en->fields['CELL_PHONE']);

            $index++;
            $cell_no = $cell[$index] . $line;
            $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en->fields['CODE']);

            $index++;
            $cell_no = $cell[$index] . $line;
            $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en->fields['STUDENT_STATUS']);

            $index++;
            $cell_no = $cell[$index] . $line;
            $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en->fields['COURSE_OFFERING_STUDENT_STATUS']);

            $index++;
            $cell_no = $cell[$index] . $line;
            $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en->fields['GRADE']);

            /** DIAM - 601 ***/
            $FINAL_PERCENTAGE  = number_format_value_checker(($res_en->fields['FINAL_TOTAL_OBTAINED'] / $res_en->fields['FINAL_MAX_TOTAL'] * 100), 2) . ' %';
            $CURRENT_PERCENTAGE = number_format_value_checker(($res_en->fields['CURRENT_TOTAL_OBTAINED'] / $res_en->fields['CURRENT_MAX_TOTAL'] * 100), 2) . ' %';
            /** End DIAM - 601 ***/

            $index++;
            $cell_no = $cell[$index] . $line;
            $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($FINAL_PERCENTAGE);

            $index++;
            $cell_no = $cell[$index] . $line;
            $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($CURRENT_PERCENTAGE);

            // DIAM-2389
            foreach ($PK_COURSE_OFFERING_GRADE_ARR as $PK_COURSE_OFFERING_GRADE) {
                $res_stu_grade = $db->Execute("SELECT POINTS as POINTS FROM S_STUDENT_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_GRADE = '$PK_COURSE_OFFERING_GRADE' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ");
                $Fail_Point = '';
                if ($res_stu_grade->fields['POINTS'] != "") 
                {
                    $Fail_Point = $res_stu_grade->fields['POINTS'];
                }

                $index++;
                $cell_no = $cell[$index] . $line;
                $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($Fail_Point);
            }
            // End DIAM-2389

            $res_en->MoveNext();
        }

        
        
    }
}

if ($_GET['format'] == 1) {
    $file_name = 'Course Offering Grade Book Analysis.pdf';
    $pdf->Output('temp/' . $file_name, 'FD');
    return  $file_name;
}

if ($_GET['format'] == 2) {

    $objWriter->save($outputFileName);
    $objPHPExcel->disconnectWorksheets();
    header("location:" . $outputFileName);
}
