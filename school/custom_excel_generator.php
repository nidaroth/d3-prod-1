<?php


class CustomExcelGenerator
{

    static public function make($inputFileType, $dir, $file_title, $data, $header = false, $download_and_exit = false)
    {
        include_once('../global/excel/Classes/PHPExcel/IOFactory.php');
        $outputFileName = $dir . $file_title;
        $outputFileName = str_replace(
            pathinfo($outputFileName, PATHINFO_FILENAME),
            pathinfo($outputFileName, PATHINFO_FILENAME) . "_" . $_SESSION['PK_USER'] . "_" . floor(microtime(true) * 1000),
            $outputFileName
        );

        $objReader   = PHPExcel_IOFactory::createReader($inputFileType);
        $objReader->setIncludeCharts(TRUE);
        $objPHPExcel = new PHPExcel();
        $objWriter   = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

        if ($header) {

            $objPHPExcel->getActiveSheet()->fromArray($header);
            $objPHPExcel->getActiveSheet()->fromArray($data, "--", "A2");
        } else {

            $objPHPExcel->getActiveSheet()->fromArray($data);
        }

        $objPHPExcel->getActiveSheet()->freezePane('A1');
        // return $outputFileName;
        try {
            $final_path = $objWriter->save($outputFileName);
            return $final_path;
        } catch (\Throwable $th) {
            return "errorsome";
        }

        $objPHPExcel->disconnectWorksheets();

        return $final_path;
    }


    static public function makecustom($inputFileType, $dir, $file_title, $data, $header = false, $style = false, $download_and_exit = false)
    {
        include_once('../global/excel/Classes/PHPExcel/IOFactory.php');

        $outputFileName = $dir . $file_title;
        $outputFileName = str_replace(
            pathinfo($outputFileName, PATHINFO_FILENAME),
            pathinfo($outputFileName, PATHINFO_FILENAME) . "_" . $_SESSION['PK_USER'] . "_" . floor(microtime(true) * 1000),
            $outputFileName
        );

        $objReader   = PHPExcel_IOFactory::createReader($inputFileType);
        $objReader->setIncludeCharts(TRUE);
        $objPHPExcel = new PHPExcel();
        $objWriter   = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

        if ($header) {
            $objPHPExcel->getActiveSheet()->fromArray($header);
            $header_column = $objPHPExcel->getActiveSheet()->getHighestDataColumn();

            $header_row = $objPHPExcel->getActiveSheet()->getHighestDataRow();
            if($style['header_style'] != 'no_background' || !isset($style['header_style'])){
                // Removed shaded headers as not required or asked by client / andrea any longer
                // $objPHPExcel->getActiveSheet()->getStyle("A$header_row:$header_column$header_row")->applyFromArray(
                //     array(
                //         'fill' => array(
                //             'type' => PHPExcel_Style_Fill::FILL_SOLID,
                //             'color' => array('rgb' => '9b8fa0')
                //         )
                //     )
                // );
            }
            
        }
        //_c stands for cell;
        $row_control_cnt = 0;
        foreach ($data as $index) {

            if (is_array($index[array_keys($index)[0]])) {
                // foreach ($index as $key_c => $value_c) {
                // echo "<br>--------- Styled Array ----------<br>";
                $column =  $objPHPExcel->getActiveSheet()->getHighestDataColumn();
                $row =  $objPHPExcel->getActiveSheet()->getHighestDataRow();
                if ($row_control_cnt == 0) {
                    $row = 0;
                }

                $objPHPExcel->getActiveSheet()->fromArray($index[array_keys($index)[0]], '--', 'A' . ($row + 1));
                // print_r([$column, $row + 1]);
                // $lcolumn = $objPHPExcel->getActiveSheet()->getHighestDataColumn();
                $lcolumn = PHPExcel_Cell::stringFromColumnIndex(count($index[array_keys($index)[0]]) - 1);
                // echo $lcolumn."||".count($index[array_keys($index)[0]])."<<";exit;
                $lrow = $objPHPExcel->getActiveSheet()->getHighestDataRow();

                if (array_keys($index)[0] == '*header*') {
                    $objPHPExcel->getActiveSheet()->getStyle("A$lrow:$lcolumn$lrow")->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => '9b8fa0')
                            )
                        )
                    );
                } else if (array_keys($index)[0] == '*bold*') {
                    $objPHPExcel->getActiveSheet()->getStyle("A$lrow:$lcolumn$lrow")->applyFromArray(
                        array(
                            'font'  => array(
                                'bold'  => true
                            )
                        )
                    );
                }


                // }
            } else {
                // echo "<br>------Normal array-------<br>" . $index[0] . "<< <br>";
                $column =  $objPHPExcel->getActiveSheet()->getHighestDataColumn();
                $row =  $objPHPExcel->getActiveSheet()->getHighestDataRow();
                $objPHPExcel->getActiveSheet()->fromArray($index, '--', 'A' . ($row + 1));
                // print_r([$column, $row + 1]);
            }


            //    if($key_c == '*header*'){

            //     $objPHPExcel->getActiveSheet()->fromArray($value_c);
            //     $column =  $objPHPExcel->getActiveSheet()->getHighestDataColumn();
            //     $row =  $objPHPExcel->getActiveSheet()->getHighestDataRow();
            //     print_r([$column, $row]);
            //    }else{
            //     $objPHPExcel->getActiveSheet()->fromArray($data);
            //    }
            $row_control_cnt = $row_control_cnt + 1;
        }


        // $objPHPExcel->getActiveSheet()->fromArray($data);

        // foreach (range('A', 'I') as $letra) {
        //     $objPHPExcel->getActiveSheet()->getColumnDimension($letra)->setAutoSize(true);
        // }


        ############ ALGO FOR COLUMN WIDTH ADJUSTMENT  #########

        // Set the user-provided OR LAST POPULATED HEADER COLUMNS to search for
        $userProvidedString = $objPHPExcel->getActiveSheet()->getHighestDataColumn();

        // Set headers from A to Z and AA to ZZ for demonstration
        $headers = range('A', 'Z');
        foreach (range('A', 'Z') as $firstLetter) {
            foreach (range('A', 'Z') as $secondLetter) {
                $headers[] = $firstLetter . $secondLetter;
                if ($firstLetter . $secondLetter === $userProvidedString) { 
                    break 2; // Use break 2 to break out of both nested loops
                }  
            }
        }
        foreach ($headers as $columnHeader) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($columnHeader)->setAutoSize(true);
        }


        $objPHPExcel->getActiveSheet()->getColumnDimension($firstLetter.$secondLetter)->setAutoSize(true);


        ############# END OF THIS ALGO ########
        
        $objPHPExcel->getActiveSheet()->freezePane('A1');
        $final_path = $objWriter->save($outputFileName);
        $objPHPExcel->disconnectWorksheets();

        return $final_path;
    }
}
