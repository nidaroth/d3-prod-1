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
        $file_name 		= 'Student_Enrollment_Review_'.time().'.xlsx';
        $outputFileName = $dir.$file_name ;
        $outputFileName = str_replace(pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName ); 
    
        $objReader      = PHPExcel_IOFactory::createReader($inputFileType);
        $objReader->setIncludeCharts(TRUE);
        //$objPHPExcel   = $objReader->load('../../global/excel/Template/Licensure_Certification_Exam_Pass_Rates.xlsx');
        $objPHPExcel = new PHPExcel();
        $objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        
        $line 	= 1;	
        $index 	= -1;
     
        $stud_cond ='';
        if(!empty($PK_STUDENT_MASTER)){
        $stud_cond = " AND S_STUDENT_MASTER.PK_STUDENT_MASTER IN ($PK_STUDENT_MASTER) ";
        }

        // $res = $db->Execute("SELECT sq.STUDENT_NAME AS 'STUDENT NAME', sq.STUDENT_ID AS 'STUDENT ID'
        // ,MAX(sq.ENROLLMENT_ORDER) AS 'ENROLLMENT COUNT'
        // ,MAX(CASE WHEN ENROLLMENT_ORDER = 1 THEN sq.ENROLLMENT ELSE '' END) AS E01
        // ,MAX(CASE WHEN ENROLLMENT_ORDER = 2 THEN sq.ENROLLMENT ELSE '' END) AS E02
        // ,MAX(CASE WHEN ENROLLMENT_ORDER = 3 THEN sq.ENROLLMENT ELSE '' END) AS E03
        // ,MAX(CASE WHEN ENROLLMENT_ORDER = 4 THEN sq.ENROLLMENT ELSE '' END) AS E04
        // ,MAX(CASE WHEN ENROLLMENT_ORDER = 5 THEN sq.ENROLLMENT ELSE '' END) AS E05
        // ,MAX(CASE WHEN ENROLLMENT_ORDER = 6 THEN sq.ENROLLMENT ELSE '' END) AS E06
        // ,MAX(CASE WHEN ENROLLMENT_ORDER = 7 THEN sq.ENROLLMENT ELSE '' END) AS E07
        // ,MAX(CASE WHEN ENROLLMENT_ORDER = 8 THEN sq.ENROLLMENT ELSE '' END) AS E08
        // ,MAX(CASE WHEN ENROLLMENT_ORDER = 9 THEN sq.ENROLLMENT ELSE '' END) AS E09
        // ,MAX(CASE WHEN ENROLLMENT_ORDER = 10 THEN sq.ENROLLMENT ELSE '' END) AS E10
        // FROM (
        //     SELECT CONCAT(S.LAST_NAME,', ',S.FIRST_NAME) AS STUDENT_NAME
        //     ,SA.STUDENT_ID
        //     ,T.BEGIN_DATE
        //     ,SE.IS_ACTIVE_ENROLLMENT
        //     ,CONCAT(CASE WHEN COALESCE(SE.IS_ACTIVE_ENROLLMENT,0) = 1 THEN '<BOLD>' ELSE '' END
		// ,COALESCE(P.CODE,'NO PROGRAM')
		// ,'<NEW LINE>'
        // ,COALESCE(DATE_FORMAT(T.BEGIN_DATE, '%m/%d/%Y'),'NOT FIRST TERM')
        // ,'<NEW LINE>'              
		// ,COALESCE(SS.STUDENT_STATUS,'NO STATUS')
        // ,'<NEW LINE>'       
		// ,COALESCE(DATE_FORMAT(CASE WHEN ED.CODE = 'Grad Date' THEN COALESCE(SE.GRADE_DATE,'')
		// 			  WHEN ED.CODE = 'Drop Date' THEN COALESCE(SE.DROP_DATE,'')
		// 			  WHEN ED.CODE = 'LDA' THEN COALESCE(SE.LDA,'')
		// 			  WHEN ED.CODE = 'Determination Date' THEN COALESCE(SE.DETERMINATION_DATE,'')
		// 			  ELSE '' END, '%m/%d/%Y'),'')
        // ) AS ENROLLMENT,ED.CODE AS END_DATE_TYPE
        //     ,(SELECT COUNT(*) + 1
        //       FROM S_STUDENT_ENROLLMENT AS sqSE
        //       INNER JOIN S_TERM_MASTER AS sqT ON sqSE.PK_TERM_MASTER = sqT.PK_TERM_MASTER  
        //       WHERE sqSE.PK_STUDENT_MASTER = S.PK_STUDENT_MASTER
        //       AND CONCAT(DATE_FORMAT(T.BEGIN_DATE, '%Y%m%d'),SE.PK_STUDENT_ENROLLMENT) > CONCAT(DATE_FORMAT(sqT.BEGIN_DATE, '%Y%m%d'),sqSE.PK_STUDENT_ENROLLMENT)
        //       ) AS ENROLLMENT_ORDER
        //     FROM S_STUDENT_ENROLLMENT AS SE
        //     INNER JOIN S_STUDENT_MASTER AS S ON SE.PK_STUDENT_MASTER = S.PK_STUDENT_MASTER
        //     INNER JOIN S_STUDENT_ACADEMICS AS SA ON S.PK_STUDENT_MASTER = SA.PK_STUDENT_MASTER
        //     INNER JOIN S_STUDENT_CAMPUS AS SEC ON SE.PK_STUDENT_ENROLLMENT = SEC.PK_STUDENT_ENROLLMENT
        //     INNER JOIN M_CAMPUS_PROGRAM AS P ON SE.PK_CAMPUS_PROGRAM = P.PK_CAMPUS_PROGRAM
        //     INNER JOIN S_TERM_MASTER AS T ON SE.PK_TERM_MASTER = T.PK_TERM_MASTER
        //     INNER JOIN M_STUDENT_STATUS AS SS ON SE.PK_STUDENT_STATUS = SS.PK_STUDENT_STATUS
        //     INNER JOIN M_END_DATE AS ED ON SS.PK_END_DATE = ED.PK_END_DATE
        //     WHERE SE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $stud_cond 
        //     ) AS sq
        // GROUP BY sq.STUDENT_NAME, sq.STUDENT_ID    
        // ORDER BY sq.STUDENT_NAME, sq.STUDENT_ID");
        
        $sql_query = "SELECT    
       CONCAT(
           S_STUDENT_MASTER.LAST_NAME,
           ', ',
           S_STUDENT_MASTER.FIRST_NAME,
           ' ',
           SUBSTRING(
               S_STUDENT_MASTER.MIDDLE_NAME,
               1,
               1
           )
       ) AS STUDENT,
       STUDENT_ID AS 'STUDENT ID',
       CAMPUS_CODE AS 'CAMPUS',
       M_CAMPUS_PROGRAM.CODE AS 'PROGRAM',
       IF(
           S_TERM_MASTER.BEGIN_DATE = '0000-00-00',
           '',
           DATE_FORMAT(
               S_TERM_MASTER.BEGIN_DATE,
               '%m/%d/%Y'
           )
       ) AS 'FIRST TERM DATE',
        STUDENT_STATUS AS 'STATUS',
        IF(S_STUDENT_ENROLLMENT.ORIGINAL_EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.ORIGINAL_EXPECTED_GRAD_DATE,'%Y-%m-%d' )) AS 'ORIGINAL EXPECTED GRAD DATE',   
       IF(
           EXPECTED_GRAD_DATE = '0000-00-00',
           '',
           DATE_FORMAT(EXPECTED_GRAD_DATE, '%m/%d/%Y')
       ) AS 'EXPECTED GRAD DATE',
        IF(
           MIDPOINT_DATE = '0000-00-00',
           '',
           DATE_FORMAT(MIDPOINT_DATE, '%m/%d/%Y')
       ) AS 'MID POINT DATE',  
       
       IF(
           GRADE_DATE = '0000-00-00',
           '',
           DATE_FORMAT(GRADE_DATE, '%m/%d/%Y')
       ) AS 'GRADE DATE',
       IF(
           LDA = '0000-00-00',
           '',
           DATE_FORMAT(LDA, '%m/%d/%Y')
       ) AS LDA,
       IF(
           DETERMINATION_DATE = '0000-00-00',
           '',
           DATE_FORMAT(DETERMINATION_DATE, '%m/%d/%Y')
       ) AS 'DETERMINATION DATE',
       IF(
           DROP_DATE = '0000-00-00',
           '',
           DATE_FORMAT(DROP_DATE, '%m/%d/%Y')
       ) AS 'DROP DATE',
       CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS 'ADMISSION REP'
    FROM
        S_STUDENT_MASTER 
        INNER JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER
        INNER JOIN  S_STUDENT_ACADEMICS ON S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER 
        INNER JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER 
        
    LEFT JOIN M_STUDENT_GROUP ON M_STUDENT_GROUP.PK_STUDENT_GROUP = S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP
    INNER JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER
    INNER JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM
    INNER JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS
    INNER JOIN M_END_DATE AS ED ON M_STUDENT_STATUS.PK_END_DATE = ED.PK_END_DATE
    INNER JOIN S_CAMPUS ON S_STUDENT_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS
    LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE 

    WHERE
          S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
          AND S_STUDENT_MASTER.ARCHIVED = 0 
          $stud_cond 
    GROUP BY
        S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT
    ORDER BY
        CONCAT(
            S_STUDENT_MASTER.LAST_NAME,
            ', ',
            S_STUDENT_MASTER.FIRST_NAME
        ) ASC";
        

        $res = $db->Execute($sql_query);

        if(count($res->fields) == 0){
            $report_error = "No data in the report for the selections made.";
            header('Location:student_enrollment_review?m=1');
        }else{
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
        while (!$res->EOF){
            $index = -1;
            $line++;
            foreach ($heading as $key) 
            {
            $index++;
            $cell_no = $cell[$index].$line;
            $cellValue=$res->fields[$key];
            $cellValue =  str_replace("<NEW LINE>","\r",$cellValue);
            $cellValue =  str_replace("<BOLD>","",$cellValue);
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
