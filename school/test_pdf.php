<?php require_once('../global/config.php');
$browser = '';
if(stripos($_SERVER['HTTP_USER_AGENT'],"chrome") != false)
	$browser =  "chrome";
else if(stripos($_SERVER['HTTP_USER_AGENT'],"Safari") != false)
	$browser = "Safari";
else
	$browser = "firefox";
require_once('../global/tcpdf/config/lang/eng.php');
require_once('../global/tcpdf/tcpdf.php');

	
class MYPDF extends TCPDF {
    public function Header() {
		global $db;
		
		$res = $db->Execute("SELECT LOGO,SCHOOL_NAME FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		
		if($res->fields['LOGO'] != '') {
			$ext = explode(".",$res->fields['LOGO']);
			$this->Image($res->fields['LOGO'], 8, 3, 0, 20, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
		}
		
		$this->SetFont('helvetica', '', 15);
		$this->SetY(8);
		$this->SetX(45);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
		$this->SetFont('helvetica', 'I', 20);
		$this->SetY(8);
		$this->SetX(165);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(55, 8, "Lead Tasks", 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
		
		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(100, 13, 202, 13, $style);
		
		$str = "";
		if($_GET['st'] != '' && $_GET['et'] != '')
			$str = " between ".$_GET['st'].' and '.$_GET['et'];
		else if($_GET['st'] != '')
			$str = " from ".$_GET['st'];
		else if($_GET['et'] != '')
			$str = " to ".$_GET['et'];
			
		$this->SetFont('helvetica', 'I', 10);
		$this->SetY(16);
		$this->SetX(100);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(102, 5, "Task Date".$str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
		
		$str = "";
		if($_GET['tc'] == 0)
			$str = "All Tasks";
		else if($_GET['tc'] == 1)
			$str = "Completed Tasks";
		else if($_GET['tc'] == 2)
			$str = "Uncompleted Tasks";
			
		$this->SetFont('helvetica', 'I', 9);
		$this->SetY(21);
		$this->SetX(100);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
		
		$this->SetFont('helvetica', 'BI', 8);
		$this->SetY(27);
		$this->SetX(8);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(19.2, 8, 'Lead', 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
		$this->SetY(27);
		$this->SetX(27.3);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(19.2, 8, 'Program', 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
		$this->SetY(27);
		$this->SetX(46.7);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(19.2, 8, 'Task Date', 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
		$this->SetY(27);
		$this->SetX(65.9);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(19.2, 8, 'Task Time', 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
		$this->SetY(27);
		$this->SetX(85.1);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(29.8, 8, 'Task', 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
		$this->SetY(27);
		$this->SetX(114.5);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(19.2, 8, 'Task Status', 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
		$this->SetY(27);
		$this->SetX(133.7);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(19.8, 8, 'Lead Phone', 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
		$this->SetY(27);
		$this->SetX(153.1);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(19.5, 8, 'Email', 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
		$this->SetY(27);
		$this->SetX(172.6);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(29.8, 8, 'Notes', 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(8, 29, 202, 29, $style);
    }
    public function Footer() {
		$this->SetY(-15);
		$this->SetX(180);
		$this->SetFont('helvetica', 'I', 7);
		$this->Cell(30, 10, 'Page '.$this->getAliasNumPage().' of '.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		
		$this->SetY(-15);
		$this->SetX(10);
		$this->SetFont('helvetica', 'I', 7);
		$this->Cell(30, 10, date('l, F d, Y'), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
	
	 public function Test( $ae ) {
        if( !isset($this->xywalter) ) {
            $this->xywalter = array();
        }
        $this->xywalter[] = array($this->GetX(), $this->GetY());
    }
}

$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,68,255), array(0,68,128));
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(7, 31, 7);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, 30);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setLanguageArray($l);
$pdf->setFontSubsetting(true);
$pdf->SetFont('helvetica', '', 7, '', true);

$params = $pdf->serializeTCPDFtagParameters(array(90));
// other configs
$pdf->setOpenCell(0);
$pdf->SetCellPadding(0);
$pdf->setCellHeightRatio(1.25);

$pdf->AddPage();

// create some HTML content
$html = '<table width="100%" border="1" cellspacing="0" cellpadding="2">
			<thead>
				<tr >
					<th align="center" height="210" width="93px" valign="bottom" ><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />Program Group</th>
					<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
					<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
					<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
					<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
					<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
					<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
					<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
					<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
					<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
					<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
					<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
					<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
					<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
					<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
					<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
					<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
					<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
					<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
					<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
					<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
					<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
					<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
					<th bgcolor="#ffff9a" align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
					<th align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
					<th bgcolor="#9accff" align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
					<th bgcolor="#ccffcc" align="center" width="35px" ><tcpdf method="Test" params="'.$params.'" /></th>
				</tr>
			</thead>
		</table>';

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// array with names of columns
$arr_nomes = array(
    array("5. Beginning Enrollment", 8, 17),
    array("6. New Enrollees", 8, 21),
    array("7. Cumulative Enrollment", 8, 16),
    array("8. Students Still Enrolled", 8, 16),
    array("9. Non Graduate Completers", 8, 13),
    array("10. Graduate Completers", 8, 15),
    array("11. Total Completers", 8, 18),
    array("12. Non Graduate Completers Employed in Position", 8, 1),
    array("13. Graduate Completers Employed in Positions", 8, 3),
    array("14. Total Completers Employed in Positions", 8, 5),
    array("15. Graduate Completers Employed in Positions", 8, 2),
	array("16. Graduate Completers Waiting to Take", 8, 6),
	array("17. Graduate Completers Who Took Licensure", 8, 3),
	array("18. Graduate Completers Who Passed Licensure", 8, 2),
	array("19. Graduate Completers Unavailable for", 8, 6),
	array("20. Graduate Completers Who Refused", 8, 8),
	array("21. Graduate Completers Seeking", 8, 11),
	
	array("22. Withdrawals", 8, 20),
	array("23. Sum of 16, 19 and 20", 8, 15),
	array("24. Difference of Row 10 minus Row 23", 8, 8),
	array("25. Difference of Row 11 minus Row 23", 8, 8),
	array("26. Graduation Rate (%)", 8, 16),
	array("27. Total Completion Rate (%)", 8, 14),
	array("28. Graduate Placement Rate (%)", 8, 13),
	array("29. Total Placement Rate (%)", 8, 14),
	array("30. Licensure Exam Pass Rate (%)", 8, 11),
);

// num of pages
$ttPages = $pdf->getNumPages();
for($i=1; $i<=$ttPages; $i++) {
    // set page
    $pdf->setPage($i);
    // all columns of current page
    foreach( $arr_nomes as $num => $arrCols ) {
        $x = $pdf->xywalter[$num][0] + $arrCols[1]; // new X
        $y = $pdf->xywalter[$num][1] + $arrCols[2]; // new Y
        $n = $arrCols[0]; // column name
        // transforme Rotate
        $pdf->StartTransform();
        // Rotate 90 degrees counter-clockwise
        $pdf->Rotate(270, $x, $y);
        $pdf->Text($x, $y, $n);
		
		if($num == 7) {
			$pdf->Text($x+12, $y+4, 'Related to Field of Instruction');
		} else if($num == 8) {
			$pdf->Text($x+11, $y+4, 'Related to Field of Instruction');
		} else if($num == 9) {
			$pdf->Text($x+10, $y+4, 'Related to Field of Instruction');
		} else if($num == 10) {
			$pdf->Text($x+10, $y+4, 'Unrelated to Field of Instruction');
		} else if($num == 11) {
			$pdf->Text($x+16, $y+4, 'Licensure Exam');
		} else if($num == 12) {
			$pdf->Text($x+24, $y+4, 'Exam');
		} else if($num == 13) {
			$pdf->Text($x+24, $y+4, 'Exam');
		} else if($num == 14) {
			$pdf->Text($x+18, $y+4, 'Employment');
		} else if($num == 15) {
			$pdf->Text($x+16, $y+4, 'Employment');
		} else if($num == 16) {
			$pdf->Text($x+4, $y+4, 'Employment/Status Unknown');
		}
		
		
		
        // Stop Transformation
        $pdf->StopTransform();
    }
}

// reset pointer to the last page
$pdf->lastPage();

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('example_006.pdf', 'I');