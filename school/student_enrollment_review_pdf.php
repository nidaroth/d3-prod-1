<?php
require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");
if(check_access('REPORT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

/////////////////////////////////////////////////////////////////
$stud_cond ='';
if(!empty($PK_STUDENT_MASTER)){
$stud_cond = " AND SE.PK_STUDENT_MASTER IN ($PK_STUDENT_MASTER) ";
}

$txt = '';  
$txt .= '<div style="page-break-before: always;">';
$txt .= '<table  cellspacing="0" cellpadding="0" width="100%" style="border:0px solid #808080;">
<thead>
  <tr>
    <th style="border:1px solid #808080;background-color:#d3d3d3;font-style: italic;font-family:helvetica;font-size:18px;vertical-align:center;width:20%;">Student</th>
    <th colspan="8" style="border:1px solid #808080;background-color:#d3d3d3;font-style: italic;font-family:helvetica;font-size:18px;vertical-align:center;width:80%;">Enrollments by First Term Date  <span style="float:right; margin-right:2%;margin-top:5px; font-size:10px; font-weight:normal;">(Current Enrollment in Bold)</span></th>
 </tr>
</thead>';

$res = $db->Execute("SELECT sq.STUDENT_NAME, sq.STUDENT_ID,sq.PK_REPRESENTATIVE
,MAX(sq.ENROLLMENT_ORDER) AS ENROLLMENT_COUNT
,MAX(CASE WHEN ENROLLMENT_ORDER = 1 THEN sq.ENROLLMENT ELSE '' END) AS E01
,MAX(CASE WHEN ENROLLMENT_ORDER = 2 THEN sq.ENROLLMENT ELSE '' END) AS E02
,MAX(CASE WHEN ENROLLMENT_ORDER = 3 THEN sq.ENROLLMENT ELSE '' END) AS E03
,MAX(CASE WHEN ENROLLMENT_ORDER = 4 THEN sq.ENROLLMENT ELSE '' END) AS E04
,MAX(CASE WHEN ENROLLMENT_ORDER = 5 THEN sq.ENROLLMENT ELSE '' END) AS E05
,MAX(CASE WHEN ENROLLMENT_ORDER = 6 THEN sq.ENROLLMENT ELSE '' END) AS E06
,MAX(CASE WHEN ENROLLMENT_ORDER = 7 THEN sq.ENROLLMENT ELSE '' END) AS E07
,MAX(CASE WHEN ENROLLMENT_ORDER = 8 THEN sq.ENROLLMENT ELSE '' END) AS E08
,MAX(CASE WHEN ENROLLMENT_ORDER = 9 THEN sq.ENROLLMENT ELSE '' END) AS E09
,MAX(CASE WHEN ENROLLMENT_ORDER = 10 THEN sq.ENROLLMENT ELSE '' END) AS E10
FROM (
    SELECT CONCAT(S.LAST_NAME,', ',S.FIRST_NAME) AS STUDENT_NAME
    ,SA.STUDENT_ID
    ,SE.PK_REPRESENTATIVE 
    ,T.BEGIN_DATE
    ,SE.IS_ACTIVE_ENROLLMENT
    ,CONCAT(CASE WHEN COALESCE(SE.IS_ACTIVE_ENROLLMENT,0) = 1 THEN '<b>' ELSE '' END
		,COALESCE(P.CODE,'NO PROGRAM')
		,'<br/>'
        ,COALESCE(DATE_FORMAT(T.BEGIN_DATE, '%m/%d/%Y'),'NOT FIRST TERM')
        ,'<br/>'              
		,COALESCE(SS.STUDENT_STATUS,'NO STATUS')
        ,'<br/>'       
		,COALESCE(DATE_FORMAT(CASE WHEN ED.CODE = 'Grad Date' THEN COALESCE(SE.GRADE_DATE,'')
					  WHEN ED.CODE = 'Drop Date' THEN COALESCE(SE.DROP_DATE,'')
					  WHEN ED.CODE = 'LDA' THEN COALESCE(SE.LDA,'')
					  WHEN ED.CODE = 'Determination Date' THEN COALESCE(SE.DETERMINATION_DATE,'')
					  ELSE '' END, '%m/%d/%Y'),'')
        ) AS ENROLLMENT
    ,ED.CODE AS END_DATE_TYPE
    ,(SELECT COUNT(*) + 1
      FROM S_STUDENT_ENROLLMENT AS sqSE
      INNER JOIN S_TERM_MASTER AS sqT ON sqSE.PK_TERM_MASTER = sqT.PK_TERM_MASTER  
      WHERE sqSE.PK_STUDENT_MASTER = S.PK_STUDENT_MASTER
      AND CONCAT(DATE_FORMAT(T.BEGIN_DATE, '%Y%m%d'),SE.PK_STUDENT_ENROLLMENT) > CONCAT(DATE_FORMAT(sqT.BEGIN_DATE, '%Y%m%d'),sqSE.PK_STUDENT_ENROLLMENT)
      ) AS ENROLLMENT_ORDER
    FROM S_STUDENT_ENROLLMENT AS SE
    INNER JOIN S_STUDENT_MASTER AS S ON SE.PK_STUDENT_MASTER = S.PK_STUDENT_MASTER
    INNER JOIN S_STUDENT_ACADEMICS AS SA ON S.PK_STUDENT_MASTER = SA.PK_STUDENT_MASTER
    INNER JOIN S_STUDENT_CAMPUS AS SEC ON SE.PK_STUDENT_ENROLLMENT = SEC.PK_STUDENT_ENROLLMENT
    INNER JOIN M_CAMPUS_PROGRAM AS P ON SE.PK_CAMPUS_PROGRAM = P.PK_CAMPUS_PROGRAM
    INNER JOIN S_TERM_MASTER AS T ON SE.PK_TERM_MASTER = T.PK_TERM_MASTER
    INNER JOIN M_STUDENT_STATUS AS SS ON SE.PK_STUDENT_STATUS = SS.PK_STUDENT_STATUS
    INNER JOIN M_END_DATE AS ED ON SS.PK_END_DATE = ED.PK_END_DATE
    WHERE SE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $stud_cond 
    ) AS sq
GROUP BY sq.STUDENT_NAME, sq.STUDENT_ID    
ORDER BY sq.STUDENT_NAME, sq.STUDENT_ID");


while (!$res->EOF) {

    // $STR_E01 =  str_replace("<NEW LINE>","<br/>",$res->fields['E01']);
    // $E01 =  str_replace("<BOLD>","<b>",$STR_E01);

    $resemp = $db->Execute("SELECT S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER,CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, S_EMPLOYEE_MASTER.ACTIVE from S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '".$res->fields['PK_REPRESENTATIVE']."' AND ACTIVE = '1'");


// exit;

$txt .='<tbody>';
$txt .= '<tr>
    <td width="20%" style="border:1px solid #808080;font-style: italic;font-family:helvetica;font-size:14px; vertical-align:top">
    '.$res->fields['STUDENT_NAME'].'<br/>
    Student ID: '.$res->fields['STUDENT_ID'].'<br/>
    AdRep: '.$resemp->fields['NAME'].'<br/>
    </td>
    <td width="9%" style="border:1px solid #808080;font-style: italic;font-family:helvetica;font-size:14px;vertical-align:top">
    Program:<br/>Start Date:<br/>Status:<br/>LDA:
    </td>
    <td width="9%" style="border:1px solid #808080;font-style: italic;font-family:helvetica;font-size:14px;vertical-align:top padding-right:5px;word-break: break-word;">'.$res->fields['E01'].'</td>
    <td width="9%" style="border:1px solid #808080;font-style: italic;font-family:helvetica;font-size:14px;vertical-align:top;word-break: break-word;">'.$res->fields['E02'].'</td>
    <td width="9%" style="border:1px solid #808080;font-style: italic;font-family:helvetica;font-size:14px;vertical-align:top;word-break: break-word;">'.$res->fields['E03'].'</td>
    <td width="9%" style="border:1px solid #808080;font-style: italic;font-family:helvetica;font-size:14px;vertical-align:top;word-break: break-word;">'.$res->fields['E04'].'</td>
    <td width="9%" style="border:1px solid #808080;font-style: italic;font-family:helvetica;font-size:14px;vertical-align:top;word-break: break-word;">'.$res->fields['E05'].'</td>
    <td width="9%" style="border:1px solid #808080;font-style: italic;font-family:helvetica;font-size:14px;vertical-align:top;word-break: break-word;">'.$res->fields['E06'].'</td>
    <td width="9%" style="border:1px solid #808080;font-style: italic;font-family:helvetica;font-size:14px;vertical-align:top;word-break: break-word;">'.$res->fields['E07'].'</td>    
  </tr>'; 
  $txt .='</tbody>';

      
        $res->MoveNext();
     }
   
 $txt .= '</table>';
 $txt .= '</div>';
 
 //$text;  exit;
  
$file_name = 'Student_Enrollment_Review_'.uniqid().'.pdf'; 

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
                        <td width="40%" valign="top" style="font-size:22px;" >'.$SCHOOL_NAME.'</td>
                        <td width="40%" valign="top" >
                            <table width="100%" >
                                <tr>
                                    <td width="100%" align="right" style="font-size:26px;border-bottom:1px solid #000;font-style: italic;font-family:helvetica;" ><b>Enrollment Review</b></td>
                                </tr>										
                            </table>
                        </td>
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

$header_path= create_html_file('header.html',$header_cont,'student_enrollment_review');
$content_path=create_html_file('content.html',$html_body_cont,'student_enrollment_review');
$footer_path= create_html_file('footer.html',$footer_cont,'student_enrollment_review');

sleep(2);
$margin_top="30mm";
// if(strlen($header)>1530){
// $margin_top="60mm";
// }
exec('xvfb-run -a wkhtmltopdf -T 0 -R 0 -B 0 -L 0 --enable-local-file-access --orientation landscape --page-size A4 --page-width 230mm --page-height 297mm --margin-top '.$margin_top.'  --footer-spacing 8  --margin-left 5mm --margin-right 5mm  --margin-bottom 20mm --footer-font-size 8 --footer-right "Page [page] of [toPage]" --header-html '.$header_path.' --footer-html  '.$footer_path.' '.$content_path.' ../school/temp/student_enrollment_review/'.$file_name.' 2>&1');

//exec('xvfb-run -a wkhtmltopdf -T 0 -R 0 -B 0 -L 0 --enable-local-file-access --orientation portrait --page-size A4 --page-width 210mm --page-height 297mm --margin-top '.$margin_top.'  --footer-spacing 8  --margin-left 5mm --margin-right 5mm  --margin-bottom 20mm --footer-font-size 8 --footer-right "Page [page] of [toPage]" --header-html '.$header_path.' --footer-html  '.$footer_path.' '.$content_path.' ../school/temp/student_enrollment_review/'.$file_name.' 2>&1');
        
//echo 'temp/student_enrollment_review/'.$file_name;
header('Content-Type: Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . basename($http_path.'school/temp/student_enrollment_review/'.$file_name) . '"');
//header('Content-Length: ' . $pdfdata['filefullpath']);
readfile('temp/student_enrollment_review/'.$file_name);
unlink('../school/temp/student_enrollment_review/'.$file_name); // unlink file after download
exit;
?>
