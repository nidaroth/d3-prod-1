<?php
require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");
if(check_access('REPORT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

/////////////////////////////////////////////////////////////////
$wh_cond ='';
if(!empty($PK_STUDENT_MASTER)){
$wh_cond .= " AND SE.PK_STUDENT_MASTER IN ($PK_STUDENT_MASTER) ";
}

$terms = "";
if(!empty($PK_TERM_MASTER1)){
    $wh_cond .= " AND CO.PK_TERM_MASTER IN ($PK_TERM_MASTER1) ";

    $res_term = $db->Execute("select IF(BEGIN_DATE != '0000-00-00',DATE_FORMAT(BEGIN_DATE,'%m/%d/%Y'),'') AS TERM_BEGIN_DATE from S_TERM_MASTER WHERE PK_TERM_MASTER IN ($PK_TERM_MASTER1) ORDER BY BEGIN_DATE ASC ");
        while (!$res_term->EOF) {
            if($terms != '')
                $terms .= ', ';
            $terms .= $res_term->fields['TERM_BEGIN_DATE'];	
            $res_term->MoveNext();
        }        
        if(count(explode(',',$terms)) > 8){
        $terms = "Multiple Terms Selected";
        }
}


$campus_name='';
if(!empty($PK_CAMPUS1)){

    $wh_cond .= " AND CO.PK_CAMPUS IN ($PK_CAMPUS1) ";
    $res_campus = $db->Execute("select PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS IN ($PK_CAMPUS1) order by CAMPUS_CODE ASC");
        while (!$res_campus->EOF) {
            if($campus_name != '')
                $campus_name .= ', ';
            $campus_name .= $res_campus->fields['CAMPUS_CODE'];			
            $res_campus->MoveNext();
        }    
}

$inner_join_cond ='';
if($EXCLUDE_NON_PROGRAM_COURSES==1){
   $inner_join_cond ='INNER JOIN M_CAMPUS_PROGRAM_COURSE AS PC ON P.PK_CAMPUS_PROGRAM = PC.PK_CAMPUS_PROGRAM AND CO.PK_COURSE = PC.PK_COURSE';
}

$txt = '';  
$txt .= '<div style="page-break-before: always;">';
$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
<thead>
    <tr>
        <td width="15%" style="border-bottom:1px solid #000;">
            <b><i>Student</i></b>
        </td>
        <td width="10%" style="border-bottom:1px solid #000;">
             <b><i>Student ID</i></b>
        </td>
        <td width="10%" style="border-bottom:1px solid #000;">
            <b><i>Program</i></b>
        </td>
        <td width="7%" style="border-bottom:1px solid #000;">
            <b><i>Status</i></b>
        </td>
        <td width="14%" style="border-bottom:1px solid #000;">
            <b><i>Course Terms</i></b>
        </td>
        <td width="7%" style="border-bottom:1px solid #000;">
            <b><i>FA Units Attempted</i></b>
        </td>
        <td width="7%" style="border-bottom:1px solid #000;">
            <b><i>Units Attempted</i></b>
        </td>
        <td width="7%" style="border-bottom:1px solid #000;">
            <b><i>Hours Attempted</i></b>
        </td>
        <td width="10%" style="border-bottom:1px solid #000;">
            <b><i>Enrollment Status</i></b>
        </td>
        <td width="13%" style="border-bottom:1px solid #000;">
            <b><i>Estimated Enrollment Status</i></b>
        </td>
    </tr>
</thead>';

$sql_query ="SELECT CONCAT(S.LAST_NAME, ', ', S.FIRST_NAME) AS STUDENT
,COALESCE(SA.STUDENT_ID,'NO STUDENT ID') AS STUDENT_ID,SC.CAMPUS_CODE
,P.CODE AS PROGRAM
,DATE_FORMAT(T.BEGIN_DATE,'%m/%d/%Y') AS ENROLLMENT_BEGIN_DATE
,SS.STUDENT_STATUS
,GROUP_CONCAT(DISTINCT DATE_FORMAT(CO_TERM.BEGIN_DATE,'%m/%d/%Y') ORDER BY CO_TERM.BEGIN_DATE) AS COURSE_TERMS
,COUNT(*) AS COURSES_ATTEMPTED
,GROUP_CONCAT(C.COURSE_CODE ORDER BY C.COURSE_CODE) AS COURSES
,SUM((CASE WHEN G.UNITS_ATTEMPTED = 1 THEN C.FA_UNITS  ELSE  0 END)) AS FA_UNITS_ATTEMPTED
,SUM((CASE WHEN G.UNITS_ATTEMPTED = 1 THEN C.UNITS  ELSE  0 END)) AS UNITS_ATTEMPTED
,SUM((CASE WHEN G.UNITS_ATTEMPTED = 1 THEN C.HOURS  ELSE  0 END)) AS HOURS_ATTEMPTED
,CASE WHEN ESSM.FA_UNITS_HOUR_UNITS = 1 THEN 'FA_UNITS'
                 WHEN ESSM.FA_UNITS_HOUR_UNITS = 2 THEN 'HOURS'
                 WHEN ESSM.FA_UNITS_HOUR_UNITS = 3 THEN 'UNITS'
                 ELSE '' END AS ENROLLMENT_SCALE_TYPE
,ES.DESCRIPTION AS ENROLLMENT_STATUS
,COALESCE((SELECT DISTINCT SES.DESCRIPTION
                                             FROM M_ENROLLMENT_STATUS_SCALE AS ESS
                                             INNER JOIN M_SCHOOL_ENROLLMENT_STATUS AS SES ON ESS.PK_SCHOOL_ENROLLMENT_STATUS = SES.PK_SCHOOL_ENROLLMENT_STATUS
                                             WHERE ESS.PK_ENROLLMENT_STATUS_SCALE_MASTER = ESSM.PK_ENROLLMENT_STATUS_SCALE_MASTER
                                             AND SUM((CASE WHEN G.UNITS_ATTEMPTED = 1 AND ESSM.FA_UNITS_HOUR_UNITS = 1 THEN C.FA_UNITS
                                                                                            WHEN G.UNITS_ATTEMPTED = 1 AND ESSM.FA_UNITS_HOUR_UNITS = 2 THEN C.HOURS
                          WHEN G.UNITS_ATTEMPTED = 1 AND ESSM.FA_UNITS_HOUR_UNITS = 3 THEN C.UNITS
                                                                                            ELSE  0 END)) >= ESS.MIN_UNITS_PER_TERM
                                             ORDER BY ESS.MIN_UNITS_PER_TERM DESC
                                             LIMIT 1),'') AS ESTIMATED_ENROLLMENT_STATUS

FROM S_STUDENT_ENROLLMENT AS SE
INNER JOIN S_STUDENT_MASTER AS S ON SE.PK_STUDENT_MASTER = S.PK_STUDENT_MASTER
INNER JOIN S_STUDENT_ACADEMICS AS SA ON S.PK_STUDENT_MASTER = SA.PK_STUDENT_MASTER
INNER JOIN M_CAMPUS_PROGRAM AS P ON SE.PK_CAMPUS_PROGRAM = P.PK_CAMPUS_PROGRAM
LEFT JOIN M_ENROLLMENT_STATUS_SCALE_MASTER AS ESSM On P.PK_ENROLLMENT_STATUS_SCALE_MASTER = ESSM.PK_ENROLLMENT_STATUS_SCALE_MASTER
INNER JOIN M_STUDENT_STATUS AS SS ON SE.PK_STUDENT_STATUS = SS.PK_STUDENT_STATUS
INNER JOIN S_TERM_MASTER AS T ON SE.PK_TERM_MASTER = T.PK_TERM_MASTER
INNER JOIN S_STUDENT_COURSE AS COS ON SE.PK_STUDENT_ENROLLMENT = COS.PK_STUDENT_ENROLLMENT
INNER JOIN S_COURSE_OFFERING AS CO ON COS.PK_COURSE_OFFERING = CO.PK_COURSE_OFFERING
$inner_join_cond 
INNER JOIN S_TERM_MASTER AS CO_TERM ON CO.PK_TERM_MASTER = CO_TERM.PK_TERM_MASTER
INNER JOIN M_COURSE_OFFERING_STUDENT_STATUS AS COSS ON COS.PK_COURSE_OFFERING_STUDENT_STATUS = COSS.PK_COURSE_OFFERING_STUDENT_STATUS
INNER JOIN S_COURSE AS C ON CO.PK_COURSE = C.PK_COURSE
INNER JOIN S_GRADE AS G ON COS.FINAL_GRADE = G.PK_GRADE
LEFT JOIN M_ENROLLMENT_STATUS AS ES ON SE.PK_ENROLLMENT_STATUS = ES.PK_ENROLLMENT_STATUS
INNER JOIN S_CAMPUS AS SC ON SC.PK_CAMPUS = CO.PK_CAMPUS

WHERE SE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $wh_cond
AND SS.ADMISSIONS = 0
AND COSS.SHOW_ON_TRANSCRIPT = 1
GROUP BY SE.PK_STUDENT_ENROLLMENT

ORDER BY CONCAT(S.LAST_NAME, ', ', S.FIRST_NAME)
,SA.STUDENT_ID
,T.BEGIN_DATE
,P.CODE
,SS.STUDENT_STATUS";	


$res = $db->Execute($sql_query);

while (!$res->EOF) {

    $txt .='<tbody>';
    $txt .= '<tr>
                <td >'.$res->fields['STUDENT'].'</td>
                <td >'.$res->fields['STUDENT_ID'].'</td>
                <td >'.$res->fields['PROGRAM'].'</td>
                <td >'.$res->fields['STUDENT_STATUS'].'</td>
                <td >'.wordwrap($res->fields['COURSE_TERMS'], 22, "\n", true).'</td>
                <td >'.$res->fields['FA_UNITS_ATTEMPTED'].'</td>
                <td >'.$res->fields['UNITS_ATTEMPTED'].'</td>
                <td >'.$res->fields['HOURS_ATTEMPTED'].'</td>
                <td >'.$res->fields['ENROLLMENT_STATUS'].'</td>
                <td >'.$res->fields['ESTIMATED_ENROLLMENT_STATUS'].'</td>
            </tr>';
    $txt .='</tbody>';

       
        $res->MoveNext();
     }
   
    $txt .= '</table>';
    $txt .= '</div>';
 
 //  echo $txt; exit;
  
$file_name = 'Enrollment_Status_By_Term_'.uniqid().'.pdf'; 

$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$logo='';
if($res->fields['PDF_LOGO'] != '')
    $PDF_LOGO =$res->fields['PDF_LOGO'];
    
    if($PDF_LOGO != ''){
        $PDF_LOGO=str_replace('../',$http_path,$PDF_LOGO);
        $logo = '<img src="'.$PDF_LOGO.'" height="50px" />';
    }

$SCHOOL_NAME ='';
if($res->fields['SCHOOL_NAME'] != '')
    $SCHOOL_NAME =$res->fields['SCHOOL_NAME'];

    $header = '<table width="100%" >
    <tr>
        <td width="20%" valign="top" >'.$logo.'</td>
        <td width="30%" valign="top" style="font-size:20px" >'.$SCHOOL_NAME.'</td>
        <td width="50%" valign="top" >
            <table width="100%" >
                <tr>
                    <td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>Enrollment Status By Term</b></td>
                </tr>
                <tr>
                    <td width="100%" align="right" style="font-size:13px;" >Campus(es): '.$campus_name.'</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="3" width="100%" align="right" style="font-size:13px;" >Term(s): '.$terms.'</td>
    </tr>   
</table>';


$timezone = $_SESSION['PK_TIMEZONE'];
if($timezone == '' || $timezone == 0) {
    $res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
    $timezone = $res->fields['PK_TIMEZONE'];
    if($timezone == '' || $timezone == 0)
        $timezone = 4;
}

$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$res->fields['TIMEZONE'],date_default_timezone_get());
                        

$footer = '<table width="100%">
<tr>
    <td width="33%" valign="top" style="font-size:10px;" ><i>'.$date.'</i></td>
    <td width="33%" valign="top" style="font-size:10px;" align="center" ></td>
    <td></td>							
</tr>
</table>';				


$header_cont= '<!DOCTYPE HTML>
<html>
	<head>
		<style>
			div{ padding-bottom:20px !important; }	
		</style>
	</head>
	<body>
		<div> '.$header.' </div>
	</body>
</html>';
$html_body_cont = '<!DOCTYPE HTML>
<html>
	<head> 
		<style>
			body{ font-size:12px; font-family:helvetica; }	
			table{  margin-top: 16px; }
			table tr{  padding-top: 15px !important; }
		</style>
	</head>
<body>'.$txt.'</body>
</html>';
$footer_cont= '<!DOCTYPE HTML>
<html>
	<head>
		<style>
			tbody td{ font-size:15px !important; }
		</style>
	</head>
	<body>'.$footer.'</body>
</html>';

$header_path= create_html_file('header.html',$header_cont,'enrollment_status_by_term');
$content_path=create_html_file('content.html',$html_body_cont,'enrollment_status_by_term');
$footer_path= create_html_file('footer.html',$footer_cont,'enrollment_status_by_term');

sleep(2);
$margin_top="30mm";
// if(strlen($header)>1530){
// $margin_top="60mm";
// }
exec('xvfb-run -a wkhtmltopdf -T 0 -R 0 -B 0 -L 0 --enable-local-file-access --orientation portrait --page-size A4 --page-width 230mm --page-height 297mm --margin-top '.$margin_top.'  --footer-spacing 8  --margin-left 5mm --margin-right 5mm  --margin-bottom 20mm --footer-font-size 8 --footer-right "Page [page] of [toPage]" --header-html '.$header_path.' --footer-html  '.$footer_path.' '.$content_path.' ../school/temp/enrollment_status_by_term/'.$file_name.' 2>&1');


header('Content-Type: Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . basename($http_path.'school/temp/enrollment_status_by_term/'.$file_name) . '"');
//header('Content-Length: ' . $pdfdata['filefullpath']);
readfile('temp/enrollment_status_by_term/'.$file_name);
unlink('../school/temp/enrollment_status_by_term/'.$file_name); // unlink file after download
exit;
?>
