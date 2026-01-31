<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
require_once("../language/student.php");
require_once("check_access.php");
$tmp_file_arr = [];
// ini_set('display_errors', 1);
// ini_set('display_startup_errosrs', 1);
// error_reporting(E_ALL);
// error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
/* ticket #1072 */
if (check_access('MANAGEMENT_REGISTRAR') == 0 && $_GET['s'] == '') {
	header("location:../index");
	exit;
}

/* ticket #1072 */
if ($_GET['s'] == 1) {
	$_POST['DISPLAY_BARCODE'] = 1;

	$res = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND IS_ACTIVE_ENROLLMENT = 1");

	$_POST['PK_STUDENT_ENROLLMENT'][] = $res->fields['PK_STUDENT_ENROLLMENT'];
}
/* ticket #1072 */

if (!empty($_POST)) {
	//echo "<pre>";print_r($_POST);exit;

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
		public function Header()
		{
		}
		public function Footer()
		{
		}
	}

	//ticket #1184
	$res = $db->Execute("SELECT STUDENT_ID_BARCODE_TYPE, STUDENT_ID_PROGRAM_DISPLAY_TYPE, PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

	$address  = $res->fields['SCHOOL_NAME'] . '<br />';
	$address .= $res->fields['ADDRESS'] . '<br />';
	if ($res->fields['ADDRESS_1'] != '')
		$address .= $res->fields['ADDRESS_1'] . '<br />';

	$address .= $res->fields['CITY'] . ', ' . $res->fields['STATE_CODE'] . ' ' . $res->fields['ZIP'];

	if ($res->fields['ADDRESS_1'] == '')
		$address .= '<br />';

	//ticket #1184
	$ID_TYPE 						 = $res->fields['STUDENT_ID_BARCODE_TYPE'];
	$STUDENT_ID_PROGRAM_DISPLAY_TYPE = $res->fields['STUDENT_ID_PROGRAM_DISPLAY_TYPE'];

	$txt    = '';
	$height  = "75px";
	$height1 = "75px";
	if ($_POST['DISPLAY_BARCODE'] == 1 && $ID_TYPE > 0) {
		$height  = "65px";
		$height1 = "61px";
	}

	$logo = "";
	if ($res->fields['PDF_LOGO'] != '')
		$logo = '<img src="' . $res->fields['PDF_LOGO'] . '" style="max-width:114px;height:' . $height1 . ';" >';

	if ($_GET['s'] == 1) {
		$width  = 98;

		if ($_POST['DISPLAY_BARCODE'] == 1 && $ID_TYPE > 0)
			$height = 78;
		else
			$height = 68;

		$pageLayout = array($height, $width);
		$pdf = new MYPDF('L', 'mm', $pageLayout, true, 'UTF-8', false);
		//$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetMargins(4, 8, 1);
		$pdf->SetHeaderMargin(3);
		$pdf->SetAutoPageBreak(FALSE, 20);
	} else {
		$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		$pdf->SetMargins(7, 20, 7);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetAutoPageBreak(TRUE, 20);
	}
	$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . ' 001', PDF_HEADER_STRING, array(0, 64, 255), array(0, 64, 128));
	$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	$pdf->setLanguageArray($l);
	$pdf->setFontSubsetting(true);
	$pdf->SetFont('helvetica', '', 8, '', true);

	$str = '<table border="0" style="border:1px solid #000" cellspacing="0" cellpadding="0" width="286px" >
				<tr>
					<td width="60%" >
						<table border="0" cellspacing="0" cellpadding="3" width="100%" >
							<tr>
								<td width="100%" >
									[Name]<br />
									Program: [Program]<br />
									Expiration Date: [Exp Date]
								</td>
							</tr>
							
							<tr>
								<td width="100%" >[address]</td>
							</tr>
							
							<tr>
								<td width="100%" >
									ID: [Id]
								</td>
							</tr>
							
						</table>
					</td>
					<td width="40%" >
						<table border="0" cellspacing="0" cellpadding="1" width="100%" >
							<tr>
								<td style="height:' . $height . ';max-height:78px;width:114px;" align="center" >
									<center>[Photo]</center>'
		// .$height.'||'.$height1
		. '
								</td>
							</tr>
							<tr>
								<td width="100%" style="height:' . $height1 . ';width:114px;border : solid 1px blue" align="center" ><center>[logo]</center></td>
							</tr>
						</table>
					</td>
				</tr>';
	if ($_POST['DISPLAY_BARCODE'] == 1 && $ID_TYPE > 0) {
		$str .= '<tr>
							<td width="100%" style="height:20px" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[barcode]</td>
						</tr>';
	}

	$str .= '</table>';

	$i = 0;
	for ($j = 0; $j < count($_POST['PK_STUDENT_ENROLLMENT']); $j++) {
		$i += 2;

		$PK_STUDENT_ENROLLMENT  = $_POST['PK_STUDENT_ENROLLMENT'][$j];
		$k = $j + 1;
		$PK_STUDENT_ENROLLMENT1 = $_POST['PK_STUDENT_ENROLLMENT'][$k];

		//Ticket # 1184  
		$query = "select CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) AS STU_NAME, IMAGE, IF(EXPECTED_GRAD_DATE = '0000-00-00','', DATE_FORMAT(EXPECTED_GRAD_DATE, '%m/%d/%Y' )) AS EXPECTED_GRAD_DATE, M_CAMPUS_PROGRAM.CODE, M_CAMPUS_PROGRAM.DESCRIPTION as PROG_DESCRIPTION, STUDENT_ID, BADGE_ID 
		FROM 
		S_STUDENT_MASTER, S_STUDENT_ACADEMICS, S_STUDENT_ENROLLMENT  
		LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
		WHERE 
		S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
		S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER AND 
		S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";

		$res_stud = $db->Execute($query . " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ");
		$res_stud->fields['IMAGE'] = str_replace(' ','%20',$res_stud->fields['IMAGE']);
		/****************************************************/
		$res_header = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ,SCHOOL_HEADER_OPTION, LOGO_OPTION FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM_REPORT_HEADER ON S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM = M_CAMPUS_PROGRAM_REPORT_HEADER.PK_CAMPUS_PROGRAM  WHERE S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ");
		if ($res_header->fields['SCHOOL_HEADER_OPTION'] == 2) {
			//campus
			$res_pdf_header = $db->Execute("SELECT CAMPUS_CODE as NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, FAX, CAMPUS_WEBSITE as WEBSITE FROM S_STUDENT_CAMPUS, S_CAMPUS LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_CAMPUS.PK_STATES WHERE S_STUDENT_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_STUDENT_ENROLLMENT > 0");
		} else {
			//school
			$res_pdf_header = $db->Execute("SELECT SCHOOL_NAME as NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, FAX, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES  WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		}

		if ($res_header->fields['LOGO_OPTION'] == 2) {
			//campus
			$res_pdf_header_logo = $db->Execute("SELECT CAMPUS_PDF_LOGO as LOGO FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE S_STUDENT_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_STUDENT_ENROLLMENT > 0");
		} else {
			//school
			$res_pdf_header_logo = $db->Execute("SELECT PDF_LOGO as LOGO FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		}

		$address  = $res_pdf_header->fields['NAME'] . '<br />';
		$address .= $res_pdf_header->fields['ADDRESS'] . '<br />';
		if ($res_pdf_header->fields['ADDRESS_1'] != '')
			$address .= $res_pdf_header->fields['ADDRESS_1'] . '<br />';

		$address .= $res_pdf_header->fields['CITY'] . ', ' . $res_pdf_header->fields['STATE_CODE'] . ' ' . $res_pdf_header->fields['ZIP'];

		if ($res_pdf_header->fields['ADDRESS_1'] == '')
			$address .= '<br />';

		$logo = "";
		if ($res_pdf_header_logo->fields['LOGO'] != '')
			$logo = '<img src="' . $res_pdf_header_logo->fields['LOGO'] . '" style="max-width:114px;height:' . $height1 . ';" >';
		/****************************************************/

		//ticket #1072
		if ($ID_TYPE == 1)
			$ID = $res_stud->fields['BADGE_ID'];
		else
			$ID = $res_stud->fields['STUDENT_ID'];

		//ticket #1184
		if ($STUDENT_ID_PROGRAM_DISPLAY_TYPE == 1)
			$PROGRAM = $res_stud->fields['CODE'];
		else
			$PROGRAM = $res_stud->fields['PROG_DESCRIPTION'];

		$image = '';

		if ($res_stud->fields['IMAGE'] != '') {

			$file_headers = @get_headers($res_stud->fields['IMAGE']);
			if ($file_headers && strpos($file_headers[0], '200 OK')) {
				$temp_file_name = $res_stud->fields['STU_NAME'].'temp_student_converted_photo' . $_SESSION['pk_ACCOUNT'] . '_' . time();
				$res_stud->fields['IMAGE'] = 	imageCreateFromAny($res_stud->fields['IMAGE'], $temp_file_name);
				// unlink($temp_file_name);
				$image = '<img src="' . $res_stud->fields['IMAGE'] . '" style="max-width:114px;max-height:78px;height:' . $height . ';" >';
			}
		}

		$str1 = $str;
		$str1 = str_replace("[Name]", trim($res_stud->fields['STU_NAME']), $str1);
		$str1 = str_replace("[Program]", $PROGRAM, $str1);
		$str1 = str_replace("[Exp Date]", $res_stud->fields['EXPECTED_GRAD_DATE'], $str1);
		$str1 = str_replace("[Id]", $ID, $str1);
		$str1 = str_replace("[Photo]", $image, $str1);
		$str1 = str_replace("[address]", $address, $str1);
		$str1 = str_replace("[logo]", $logo, $str1);

		if ($ID != '') {
			$params = $pdf->serializeTCPDFtagParameters(array($ID, 'C39E', '', '', 40, 5, 0.2, array('position' => 'S', 'border' => false, 'padding' => 1, 'fgcolor' => array(0, 0, 0), 'bgcolor' => array(255, 255, 255), 'text' => false, 'font' => 'helvetica', 'fontsize' => 8, 'stretchtext' => 1), 'C'));
			$str1 = str_replace("[barcode]", '<tcpdf method="write1DBarcode" params="' . $params . '" />', $str1);
		} else
			$str1 = str_replace("[barcode]", '', $str1);

		$res_stud = $db->Execute($query . " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT1' ");
		$res_stud->fields['IMAGE'] = str_replace(' ','%20',$res_stud->fields['IMAGE']);
		if ($_POST['ID_TYPE'] == 1)
			$ID = $res_stud->fields['BADGE_ID'];
		else
			$ID = $res_stud->fields['STUDENT_ID'];

		if ($PK_STUDENT_ENROLLMENT1 == '')
			$str2 = '';
		else {
			$image = '';

			if ($res_stud->fields['IMAGE'] != '') {

				$file_headers = @get_headers($res_stud->fields['IMAGE']);
				if ($file_headers && strpos($file_headers[0], '200 OK')) {
					$temp_file_name = $res_stud->fields['STU_NAME'].'temp_student_converted_photo' . $_SESSION['pk_ACCOUNT'] . '_' . time();
					$res_stud->fields['IMAGE'] = 	imageCreateFromAny($res_stud->fields['IMAGE'], $temp_file_name);
					// unlink($temp_file_name);
					$image = '<img src="' . $res_stud->fields['IMAGE'] . '" style="max-width:114px;max-height:78px;height:' . $height . ';" >';
				}
			}
			/****************************************************/
			$res_header = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ,SCHOOL_HEADER_OPTION, LOGO_OPTION FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM_REPORT_HEADER ON S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM = M_CAMPUS_PROGRAM_REPORT_HEADER.PK_CAMPUS_PROGRAM  WHERE S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT1' ");
			if ($res_header->fields['SCHOOL_HEADER_OPTION'] == 2) {
				//campus
				$res_pdf_header = $db->Execute("SELECT CAMPUS_CODE as NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, FAX, CAMPUS_WEBSITE as WEBSITE FROM S_STUDENT_CAMPUS, S_CAMPUS LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_CAMPUS.PK_STATES WHERE S_STUDENT_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT1' AND PK_STUDENT_ENROLLMENT > 0");
			} else {
				//school
				$res_pdf_header = $db->Execute("SELECT SCHOOL_NAME as NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, FAX, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES  WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			}

			if ($res_header->fields['LOGO_OPTION'] == 2) {
				//campus
				$res_pdf_header_logo = $db->Execute("SELECT CAMPUS_PDF_LOGO as LOGO FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE S_STUDENT_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT1' AND PK_STUDENT_ENROLLMENT > 0");
			} else {
				//school
				$res_pdf_header_logo = $db->Execute("SELECT PDF_LOGO as LOGO FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			}

			$address  = $res_pdf_header->fields['NAME'] . '<br />';
			$address .= $res_pdf_header->fields['ADDRESS'] . '<br />';
			if ($res_pdf_header->fields['ADDRESS_1'] != '')
				$address .= $res_pdf_header->fields['ADDRESS_1'] . '<br />';

			$address .= $res_pdf_header->fields['CITY'] . ', ' . $res_pdf_header->fields['STATE_CODE'] . ' ' . $res_pdf_header->fields['ZIP'];

			if ($res_pdf_header->fields['ADDRESS_1'] == '')
				$address .= '<br />';

			$logo = "";
			if ($res_pdf_header_logo->fields['LOGO'] != '')
				$logo = '<img src="' . $res_pdf_header_logo->fields['LOGO'] . '" style="max-width:114px;height:' . $height1 . ';" >';
			/****************************************************/

			$str2 = $str;
			$str2 = str_replace("[Name]", trim($res_stud->fields['STU_NAME']), $str2);
			$str2 = str_replace("[Program]", $res_stud->fields['CODE'], $str2);
			$str2 = str_replace("[Exp Date]", $res_stud->fields['EXPECTED_GRAD_DATE'], $str2);
			$str2 = str_replace("[Id]", $ID, $str2);
			$str2 = str_replace("[Photo]", $image, $str2);
			$str2 = str_replace("[address]", $address, $str2);
			$str2 = str_replace("[logo]", $logo, $str2);

			if ($ID != '') {
				$params = $pdf->serializeTCPDFtagParameters(array($ID, 'C39E', '', '', 40, 5, 0.2, array('position' => 'S', 'border' => false, 'padding' => 1, 'fgcolor' => array(0, 0, 0), 'bgcolor' => array(255, 255, 255), 'text' => false, 'font' => 'helvetica', 'fontsize' => 8, 'stretchtext' => 1), 'C'));
				$str2 = str_replace("[barcode]", '<tcpdf method="write1DBarcode" params="' . $params . '" />', $str2);
			} else
				$str2 = str_replace("[barcode]", '', $str2);
		}

		$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<tr>
						<td width="3%" ></td>
						<td width="49%" >' . $str1 . '</td>
						<td width="2%" ></td>
						<td width="49%" >' . $str2 . '</td>
					</tr>
				</table>';

		if ($_GET['s'] != 1) {
			$txt .= '<br /><br />';
		}
		$j++;
		if ($i == 8) {
			$i = 0;
			$pdf->AddPage();
			// echo $txt;
			$pdf->writeHTML($txt, $ln = true, $fill = false, $reseth = true, $cell = true, $align = '');
			$txt = '';
		}
	}

	if ($txt != '') {
		$pdf->AddPage();
		// echo $txt;
		$pdf->writeHTML($txt, $ln = true, $fill = false, $reseth = true, $cell = true, $align = '');
	}

	$file_name = 'Student ID.pdf';
	foreach($tmp_file_arr as $file_to_delete){
		unlink($file_to_delete);
	}
	$pdf->Output('temp/' . $file_name, 'FD'); 
	return $file_name;
}
function imageCreateFromAny($filepath, $temp_file_name)
{

global $tmp_file_arr;

	$ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
	$type = exif_imagetype($filepath); // [] if you don't have exif you could use getImageSize()

	$allowedTypes = array(

		//Not usable 1,  // [] gif

		// 2,  // [] jpg

		// 3,  // [] png

		6   // [] bmp

	);

	if (!in_array($type, $allowedTypes)) {

		return $filepath;
	}

	switch ($type) {
			#AV - only convert if image type is bmp or else return null
			// case 1 :

			//     $im = imageCreateFromGif($filepath);

			// break;

			// case 2 :

			//     $im = imageCreateFromJpeg($filepath);

			// break;

			// case 3 :

			//     $im = imageCreateFromPng($filepath);

			// break;

		case 6:
			file_put_contents("temp/$temp_file_name.$ext", file_get_contents($filepath));
			$im = imagecreatefrombmpnew("temp/$temp_file_name.$ext");
			if (!imagepng($im, "temp/$temp_file_name.png")) {
				// echo "Checking for file " . "temp/$temp_file_name.$ext";
				unlink("temp/$temp_file_name.$ext");
				return false;
			}
			imagedestroy($im);
			unlink("temp/$temp_file_name.$ext");
			$tmp_file_arr[] = "temp/$temp_file_name.png";
			return "temp/$temp_file_name.png";




			break;
	}
 



}
function imagecreatefrombmpnew($p_sFile)
{
	$file    =    fopen($p_sFile, "rb");
	$read    =    fread($file, 10);
	while (!feof($file) && ($read <> ""))
		$read    .=    fread($file, 1024);
	$temp    =    unpack("H*", $read);
	$hex    =    $temp[1];
	$header    =    substr($hex, 0, 108);
	if (substr($header, 0, 4) == "424d") {
		$header_parts    =    str_split($header, 2);
		$width            =    hexdec($header_parts[19] . $header_parts[18]);
		$height            =    hexdec($header_parts[23] . $header_parts[22]);
		unset($header_parts);
	}
	$x                =    0;
	$y                =    1;
	$image            =    imagecreatetruecolor($width, $height);
	$body            =    substr($hex, 108);
	$body_size        =    (strlen($body) / 2);
	$header_size    =    ($width * $height);
	$usePadding        =    ($body_size > ($header_size * 3) + 4);
	for ($i = 0; $i < $body_size; $i += 3) {
		if ($x >= $width) {
			if ($usePadding)
				$i    +=    $width % 4;
			$x    =    0;
			$y++;
			if ($y > $height)
				break;
		}
		$i_pos    =    $i * 2;
		$r        =    hexdec($body[$i_pos + 4] . $body[$i_pos + 5]);
		$g        =    hexdec($body[$i_pos + 2] . $body[$i_pos + 3]);
		$b        =    hexdec($body[$i_pos] . $body[$i_pos + 1]);
		$color    =    imagecolorallocate($image, $r, $g, $b);
		imagesetpixel($image, $x, $height - $y, $color);
		$x++;
	}
	unset($body);
	return $image;
}
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
	<title><?= MNU_STUDENT_ID ?> | <?= $title ?></title>
	<style>
		li>a>label {
			position: unset !important;
		}

		/* Ticket # 1149 - term */
		.dropdown-menu>li>a {
			white-space: nowrap;
		}

		.option_red>a>label {
			color: red !important
		}

		/* Ticket # 1149 - term */
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
	<? require_once("pre_load.php"); ?>
	<div id="main-wrapper">
		<? require_once("menu.php"); ?>
		<div class="page-wrapper">
			<div class="container-fluid">
				<div class="row page-titles">
					<div class="col-md-12 align-self-center">
						<h4 class="text-themecolor"><?= MNU_STUDENT_ID ?> </h4>
					</div>
				</div>
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<form class="floating-labels m-t-40" method="post" name="form1" id="form1">
									<div class="row" style="padding-bottom:10px;">
										<div class="col-md-2 ">
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control">
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?= $res_type->fields['PK_CAMPUS'] ?>" <? if ($res_type->RecordCount() == 1) echo "selected"; ?>><?= $res_type->fields['CAMPUS_CODE'] ?></option>
												<? $res_type->MoveNext();
												} ?>
											</select>
										</div>

										<div class="col-md-2 ">
											<select id="PK_TERM_MASTER" name="PK_TERM_MASTER[]" multiple class="form-control">
												<? /* Ticket #1149 - term */
												$res_type = $db->Execute("select PK_TERM_MASTER, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION, ACTIVE from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, BEGIN_DATE DESC");
												while (!$res_type->EOF) {
													$str = $res_type->fields['BEGIN_DATE_1'] . ' - ' . $res_type->fields['END_DATE_1'] . ' - ' . $res_type->fields['TERM_DESCRIPTION'];
													if ($res_type->fields['ACTIVE'] == 0)
														$str .= ' (Inactive)'; ?>
													<option value="<?= $res_type->fields['PK_TERM_MASTER'] ?>" <? if ($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?= $str ?></option>
												<? $res_type->MoveNext();
												} /* Ticket #1149 - term */ ?>
											</select>
										</div>

										<div class="col-md-2 ">
											<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control">
												<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?= $res_type->fields['PK_CAMPUS_PROGRAM'] ?>"><?= $res_type->fields['CODE'] . ' - ' . $res_type->fields['DESCRIPTION'] ?></option>
												<? $res_type->MoveNext();
												} ?>
											</select>
										</div>

										<div class="col-md-2 ">
											<select id="PK_SESSION" name="PK_SESSION[]" class="form-control" multiple>
												<? $res_type = $db->Execute("select PK_SESSION,SESSION from M_SESSION WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by DISPLAY_ORDER ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?= $res_type->fields['PK_SESSION'] ?>"><?= $res_type->fields['SESSION'] ?></option>
												<? $res_type->MoveNext();
												} ?>
											</select>
										</div>

										<div class="col-md-2" id="PK_STUDENT_STATUS_DIV">
											<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control">
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS, DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by STUDENT_STATUS ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?= $res_type->fields['PK_STUDENT_STATUS'] ?>"><?= $res_type->fields['STUDENT_STATUS'] . ' - ' . $res_type->fields['DESCRIPTION'] ?></option>
												<? $res_type->MoveNext();
												} ?>
											</select>
										</div>

										<div class="col-md-2 ">
											<select id="PK_STUDENT_GROUP" name="PK_STUDENT_GROUP[]" multiple class="form-control">
												<? $res_type = $db->Execute("select PK_STUDENT_GROUP,STUDENT_GROUP from M_STUDENT_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by STUDENT_GROUP ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?= $res_type->fields['PK_STUDENT_GROUP'] ?>"><?= $res_type->fields['STUDENT_GROUP'] ?></option>
												<? $res_type->MoveNext();
												} ?>
											</select>
										</div>


									</div>
									<div class="row">

										<div class="col-md-2 align-self-center " style="padding-left: 12px;">
											<?= DISPLAY_BARCODE ?>
											<div class="row form-group">
												<div class="custom-control custom-radio col-md-3" style="padding-left: 2.5rem;">
													<input type="radio" id="DISPLAY_BARCODE_1" name="DISPLAY_BARCODE" value="1" <? if ($DISPLAY_BARCODE == 1) echo "checked"; ?> class="custom-control-input" checked>
													<label class="custom-control-label" for="DISPLAY_BARCODE_1"><?= YES ?></label>
												</div>
												<div class="custom-control custom-radio col-md-3" style="padding-left: 2.5rem;">
													<input type="radio" id="DISPLAY_BARCODE_2" name="DISPLAY_BARCODE" value="2" <? if ($DISPLAY_BARCODE == 2) echo "checked"; ?> class="custom-control-input">
													<label class="custom-control-label" for="DISPLAY_BARCODE_2"><?= NO ?></label>
												</div>
											</div>
										</div>

										<div class="col-md-8 ">
										</div>

										<div class="col-md-2 ">
											<button type="button" class="btn waves-effect waves-light btn-dark" onclick="search()"><?= SEARCH ?></button>
										</div>
									</div>
									<br />
									<div id="student_div" style="max-height:300px;overflow: auto;"></div>
									<div class="row">
										<div class="col-md-6 " style="text-align: center;"></div>
										<div class="col-md-6 " style="text-align: center;">
											<button type="submit" class="btn waves-effect waves-light btn-dark" style="display:none" id="btn">
												<?= CREATE ?>
											</button>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>

			</div>
		</div>
		<? require_once("footer.php"); ?>
	</div>

	<? require_once("js.php"); ?>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');

		function search() {
			jQuery(document).ready(function($) {

				var PK_STUDENT_GROUP = '';
				var PK_TERM_MASTER = '';
				var PK_CAMPUS_PROGRAM = '';
				var PK_STUDENT_STATUS = '';
				var PK_COURSE = '';
				var PK_COURSE_OFFERING = '';
				var PK_SESSION = '';
				var PK_CAMPUS = '';

				if (document.getElementById('PK_STUDENT_GROUP'))
					PK_STUDENT_GROUP = $('#PK_STUDENT_GROUP').val();

				if (document.getElementById('PK_TERM_MASTER'))
					PK_TERM_MASTER = $('#PK_TERM_MASTER').val();

				if (document.getElementById('PK_CAMPUS_PROGRAM'))
					PK_CAMPUS_PROGRAM = $('#PK_CAMPUS_PROGRAM').val();

				if (document.getElementById('PK_STUDENT_STATUS'))
					PK_STUDENT_STATUS = $('#PK_STUDENT_STATUS').val();

				if (document.getElementById('PK_COURSE'))
					PK_COURSE = $('#PK_COURSE').val();

				if (document.getElementById('PK_COURSE_OFFERING'))
					PK_COURSE_OFFERING = $('#PK_COURSE_OFFERING').val();

				if (document.getElementById('PK_SESSION'))
					PK_SESSION = $('#PK_SESSION').val();

				if (document.getElementById('PK_CAMPUS'))
					PK_CAMPUS = $('#PK_CAMPUS').val();

				var data = 'PK_STUDENT_GROUP=' + PK_STUDENT_GROUP + '&PK_TERM_MASTER=' + PK_TERM_MASTER + '&PK_CAMPUS_PROGRAM=' + PK_CAMPUS_PROGRAM + '&PK_STUDENT_STATUS=' + PK_STUDENT_STATUS + '&PK_STUDENT_COURSE=<?= $_GET['id'] ?>' + '&PK_COURSE=' + PK_COURSE + '&PK_COURSE_OFFERING=' + PK_COURSE_OFFERING + '&PK_SESSION=' + PK_SESSION + '&PK_CAMPUS=' + PK_CAMPUS + '&active_enroll=1&type=student_id'; //Ticket # 1402
				var value = $.ajax({
					url: "ajax_search_student",
					type: "POST",
					data: data,
					async: false,
					cache: false,
					success: function(data) {
						document.getElementById('student_div').innerHTML = data
					}
				}).responseText;
			});
		}

		function show_btn() {

			var flag = 0;
			var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
			for (var i = 0; i < PK_STUDENT_ENROLLMENT.length; i++) {
				if (PK_STUDENT_ENROLLMENT[i].checked == true) {
					flag++;
					break;
				}
			}

			if (flag == 1)
				document.getElementById('btn').style.display = 'block';
			else
				document.getElementById('btn').style.display = 'none';

		}

		function fun_select_all() {
			var str = '';
			if (document.getElementById('SEARCH_SELECT_ALL').checked == true)
				str = true;
			else
				str = false;

			var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
			for (var i = 0; i < PK_STUDENT_ENROLLMENT.length; i++) {
				PK_STUDENT_ENROLLMENT[i].checked = str
			}
			show_btn()
		}
	</script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css" />
	<script type="text/javascript">
		jQuery(document).ready(function($) {

			$('#PK_STUDENT_GROUP').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= GROUP_CODE ?>',
				nonSelectedText: '<?= GROUP_CODE ?>',
				numberDisplayed: 2,
				nSelectedText: '<?= GROUP_CODE ?> selected'
			});
			$('#PK_TERM_MASTER').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= FIRST_TERM ?>',
				nonSelectedText: '<?= FIRST_TERM ?>',
				numberDisplayed: 2,
				nSelectedText: '<?= FIRST_TERM ?> selected'
			});
			$('#PK_CAMPUS_PROGRAM').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= PROGRAM ?>',
				nonSelectedText: '<?= PROGRAM ?>',
				numberDisplayed: 2,
				nSelectedText: '<?= PROGRAM ?> selected'
			});
			$('#PK_STUDENT_STATUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= STATUS ?>',
				nonSelectedText: '<?= STATUS ?>',
				numberDisplayed: 2,
				nSelectedText: '<?= STATUS ?> selected'
			});

			$('#PK_SESSION').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= SESSION ?>',
				nonSelectedText: '<?= SESSION ?>',
				numberDisplayed: 2,
				nSelectedText: '<?= SESSION ?> selected'
			});

			$('#PK_CAMPUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= CAMPUS ?>',
				nonSelectedText: '<?= CAMPUS ?>',
				numberDisplayed: 2,
				nSelectedText: '<?= CAMPUS ?> selected'
			});
		});
	</script>
</body>

</html>