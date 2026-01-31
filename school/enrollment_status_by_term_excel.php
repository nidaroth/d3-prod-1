<?php
require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");
if(check_access('REPORT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
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
        $file_name 		= 'Enrollment_Status_By_Term_'.time().'.xlsx';
        $outputFileName = $dir.$file_name ;
        $outputFileName = str_replace(pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName ); 
    
        $objReader      = PHPExcel_IOFactory::createReader($inputFileType);
        $objReader->setIncludeCharts(TRUE);
        //$objPHPExcel   = $objReader->load('../../global/excel/Template/Licensure_Certification_Exam_Pass_Rates.xlsx');
        $objPHPExcel = new PHPExcel();
        $objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        
        $line 	= 1;	
        $index 	= -1;
     
        $wh_cond ='';

        if(!empty($PK_STUDENT_MASTER)){
        $wh_cond .= " AND SE.PK_STUDENT_MASTER IN ($PK_STUDENT_MASTER) ";
        }

        if(!empty($PK_TERM_MASTER1)){
        $wh_cond .= " AND CO.PK_TERM_MASTER IN ($PK_TERM_MASTER1) ";
        }

        if(!empty($PK_CAMPUS1)){
        $wh_cond .= " AND CO.PK_CAMPUS IN ($PK_CAMPUS1) ";
        }

        $inner_join_cond ='';
        if($EXCLUDE_NON_PROGRAM_COURSES==1){
           $inner_join_cond ='INNER JOIN M_CAMPUS_PROGRAM_COURSE AS PC ON P.PK_CAMPUS_PROGRAM = PC.PK_CAMPUS_PROGRAM AND CO.PK_COURSE = PC.PK_COURSE';
        }
        //echo $wh_cond; die;
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

        if(count($res->fields) == 0){
            $report_error = "No data in the report for the selections made.";
            header('Location:enrollment_status_by_term?m=1');
        }else{
        $heading = array_keys($res->fields);
        foreach ($heading as $key) 
        {
            $key = str_replace('_',' ',$key);
            $index++;
            $cell_no = $cell[$index].$line;
            $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($key);
            $objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(20);
            $objPHPExcel->getActiveSheet()->freezePane('A1');
        }
        while (!$res->EOF){
            $index = -1;
            $line++;
            foreach ($heading as $key) 
            {
            $index++;
            $cell_no = $cell[$index].$line;
            $cellValue=$res->fields[$key];
            // $cellValue =  str_replace("<NEW LINE>","\r",$cellValue);
            // $cellValue =  str_replace("<BOLD>","",$cellValue);
            $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($cellValue);
            $objPHPExcel->getActiveSheet()->getStyle($cell_no)->getAlignment()->setWrapText(true);

            }
            $res->MoveNext();
        } 
    $objPHPExcel->getActiveSheet()->freezePane('A1');
    $objWriter->save($outputFileName);
    $objPHPExcel->disconnectWorksheets();
    header("location:".$outputFileName);
    }
    
?>
