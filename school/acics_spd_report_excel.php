<?php
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
        $file_name 		= 'Student Population Data_'.time().'.xlsx';
        $outputFileName = $dir.$file_name ;
        $outputFileName = str_replace(pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName ); 
    
        $objReader      = PHPExcel_IOFactory::createReader($inputFileType);
        $objReader->setIncludeCharts(TRUE);
        //$objPHPExcel   = $objReader->load('../../global/excel/Template/Licensure_Certification_Exam_Pass_Rates.xlsx');
        $objPHPExcel = new PHPExcel();
        $objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        
        $line 	= 1;	
        $index 	= -1;
        //SP
        // echo "CALL COMP60001(".$_SESSION['PK_ACCOUNT'].", '".$PK_CAMPUS."','".$ST."','".$ET."', 'SPD')";
        // exit;   
        $res = $db->Execute("CALL COMP60001(".$_SESSION['PK_ACCOUNT'].", '".$PK_CAMPUS."','".$ST."','".$ET."', 'SPD')");	
        if(count($res->fields) == 0){
            $report_error = "No data in the report for the selections made.";
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
            $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($cellValue);
            }
            $res->MoveNext();
        } 
    $objPHPExcel->getActiveSheet()->freezePane('A1');
    $objWriter->save($outputFileName);
    $objPHPExcel->disconnectWorksheets();
    header("location:".$outputFileName);
    }

?>