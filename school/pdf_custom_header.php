<?php
require_once("check_access.php");
function pdf_custom_header($PK_STUDENT_MASTER, $PK_STUDENT_ENROLLMENT, $type){
	global $db;
	
	$res = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ,SCHOOL_HEADER_OPTION, LOGO_OPTION FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM_REPORT_HEADER ON S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM = M_CAMPUS_PROGRAM_REPORT_HEADER.PK_CAMPUS_PROGRAM  WHERE S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND IS_ACTIVE_ENROLLMENT = 1");
	$PK_STUDENT_ENROLLMENT = $res->fields['PK_STUDENT_ENROLLMENT'];
	if($res->fields['SCHOOL_HEADER_OPTION'] == 2){
		//campus
		$res_pdf_header = $db->Execute("SELECT OFFICIAL_CAMPUS_NAME as NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, FAX, CAMPUS_WEBSITE as WEBSITE FROM S_STUDENT_CAMPUS, S_CAMPUS LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_CAMPUS.PK_STATES WHERE S_STUDENT_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_STUDENT_ENROLLMENT > 0");
		
	} else {
		//school
		$res_pdf_header = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME as NAME, IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',ADDRESS) as ADDRESS,
		IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',ADDRESS_1) as ADDRESS_1,
		IF(
		HIDE_ACCOUNT_ADDRESS_ON_REPORTS = '1',
		'',
		IF(CITY!='',CONCAT(CITY, ','),'')
			) AS CITY,
		IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',STATE_CODE) as STATE_CODE,
		IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',ZIP) as ZIP,
		IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',PHONE) as PHONE, 
		IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',WEBSITE) as WEBSITE,HIDE_ACCOUNT_ADDRESS_ON_REPORTS FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'"); //DIAM-1421
	}
	
	if($res->fields['LOGO_OPTION'] == 2){
		//campus
		$res_pdf_header_logo = $db->Execute("SELECT CAMPUS_PDF_LOGO as LOGO FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE S_STUDENT_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_STUDENT_ENROLLMENT > 0");
		
	} else {
		//school
		$res_pdf_header_logo = $db->Execute("SELECT PDF_LOGO as LOGO FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	}
	
	if($type == 1) {
		$logo_width	  = 35;
		$logo_height  = "100px";
		$school_font  = "50px";
		$address_font = "35px";
	} else if($type == 2) {
		$logo_width	  = 45;
		$logo_height = "100px";
		$school_font = "45px";
		$address_font = "30px";
	} 
	
	$address_width = 100 - $logo_width;
	
	$LOGO = '';
	if($res_pdf_header_logo->fields['LOGO'] != '')
		$LOGO = '<img src="'.$res_pdf_header_logo->fields['LOGO'].'" style="height:'.$logo_height.'" />';
	
	// DIAM-1151, 2239
	$NAME 		= (utf8_encode($res_pdf_header->fields['NAME']));
	$ADDRESS 	= (utf8_encode($res_pdf_header->fields['ADDRESS']));
	$ADDRESS_1 	= (utf8_encode($res_pdf_header->fields['ADDRESS_1']));
	$CITY 		= (utf8_encode($res_pdf_header->fields['CITY']));
	if(av_check_access('has_transcript_report'))
	{
		$NAME 		= (utf8_decode($NAME));
		$ADDRESS 	= (utf8_decode($ADDRESS));
		$ADDRESS_1 	= (utf8_decode($ADDRESS_1));
		$CITY 		= (utf8_decode($CITY));
	}
	// End DIAM-1151, 2239

	$CONTENT = '<table border="0" cellspacing="0" cellpadding="3" width="100%" >
					<tr>
						<td colspan="2" style="font-size:'.$school_font.'" ><b>'.$NAME.'</b></td>
					</tr>
					<tr>
						<td style="width:'.$logo_width.'%" >'.$LOGO.'</td>
						<td style="width:'.$address_width.'%" >
							<span style="line-height:5px;'.$address_font.'" >'.$ADDRESS.' '.$ADDRESS_1.'<br />'.$CITY.' '.$res_pdf_header->fields['STATE_CODE'].' '.$res_pdf_header->fields['ZIP'].'<br /><br />'.$res_pdf_header->fields['PHONE'].'<br /><br />'.$res_pdf_header->fields['WEBSITE'].'</span>
						</td>
					</tr>
				</table>'; //DIAM-1421
				
	return $CONTENT;
}
?>
