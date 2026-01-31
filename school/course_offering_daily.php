<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
require_once("check_access.php");

if(check_access('REPORT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

/* Ticket #1225 */
if(!empty($_POST) || $_GET['p'] == 'r'){

	$CURRENT_DATE = date("Y-m-d");

	$PK_TERM_MASTER_COND = '';
	$PK_COURSE_OFFERING_COND = '';
	if($_GET['p'] == 'r') {
		//$_POST['PK_TERM_MASTER']	= $_GET['tm'];
		$_POST['FORMAT']			= $_GET['FORMAT'];
        //$PK_COURSE_OFFERING         = $_GET['co_id'];
		$START_DATE         		= date("Y-m-d",strtotime($_GET['start_date']));
		$END_DATE         			= date("Y-m-d",strtotime($_GET['end_date']));

		// DIAM-1273
		if(!empty($_GET['tm'])){
			$PK_TERM_MASTER         = $_GET['tm'];
			$PK_TERM_MASTER_COND    = " AND S_COURSE_OFFERING.PK_TERM_MASTER IN ($PK_TERM_MASTER) ";
		}

		if(!empty($_GET['co_id'])){
			$PK_COURSE_OFFERING       = $_GET['co_id'];
			$PK_COURSE_OFFERING_COND  = " AND S_COURSE_OFFERING.PK_COURSE_OFFERING IN ($PK_COURSE_OFFERING) ";
		}
		// End DIAM-1273
	}

    $campus_cond = '';
    if(!empty($_GET['campus'])){
		$PK_CAMPUS 	  = $_GET['campus'];
        $campus_cond  = " AND S_COURSE_OFFERING.PK_CAMPUS IN ($PK_CAMPUS) ";
	}

	// DIAM-1273
	$group_by = "";
	if($PK_TERM_MASTER_COND != '' || $PK_COURSE_OFFERING_COND != '')
	{
		$group_by = " GROUP BY SCHEDULE_DATE ";
	}
	// End DIAM-1273
	
	$order_by = " ORDER BY SCHEDULE_DATE, COURSE_CODE, CLASS_START_TIME, CLASS_END_TIME ASC ";
	
	$query_daily = "SELECT S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE AS SCHEDULE_DATE,
                            S_COURSE.COURSE_CODE AS COURSE_CODE, 
                            S_COURSE.COURSE_DESCRIPTION AS COURSE_DESCRIPTION, 
                            CONCAT(S_EMPLOYEE_MASTER_INST.FIRST_NAME, ', ', S_EMPLOYEE_MASTER_INST.MIDDLE_NAME, ' ',S_EMPLOYEE_MASTER_INST.LAST_NAME) AS INSTRUCTOR_NAME,
                            M_CAMPUS_ROOM.ROOM_NO AS ROOM_NO, 
                            S_COURSE_OFFERING_SCHEDULE_DETAIL.START_TIME AS CLASS_START_TIME,
                            S_COURSE_OFFERING_SCHEDULE_DETAIL.END_TIME AS CLASS_END_TIME,
                            IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00', '', DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE, '%Y-%m-%d')) AS TERM_BEGIN_DATE
                        FROM 
                            S_COURSE_OFFERING 
                            LEFT JOIN S_EMPLOYEE_MASTER AS S_EMPLOYEE_MASTER_INST ON S_EMPLOYEE_MASTER_INST.PK_EMPLOYEE_MASTER = S_COURSE_OFFERING.INSTRUCTOR
                            LEFT JOIN M_CAMPUS_ROOM ON M_CAMPUS_ROOM.PK_CAMPUS_ROOM = S_COURSE_OFFERING.PK_CAMPUS_ROOM 
                            LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER  
                            LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE   
                            LEFT JOIN S_COURSE_OFFERING_SCHEDULE_DETAIL ON S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING
                        WHERE 
                            S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
                            $PK_TERM_MASTER_COND
                            $PK_COURSE_OFFERING_COND
                            AND S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE BETWEEN '$START_DATE' AND '$END_DATE'
                            $campus_cond 
                            $group_by $order_by";
	//echo $query_daily;exit;
    
	if($_POST['FORMAT'] == 1){
		
		require_once '../global/mpdf/vendor/autoload.php';
		
		$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		$SCHOOL_NAME = $res->fields['SCHOOL_NAME'];
		$PDF_LOGO 	 = $res->fields['PDF_LOGO'];
		
		$logo = "";
		if($PDF_LOGO != '')
			$logo = '<img src="'.$PDF_LOGO.'" height="50px" />';
			
		$header = '<table width="100%" >
					<tr>
						<td width="20%" valign="top" >'.$logo.'</td>
						<td width="45%" valign="top" style="font-size:20px" >'.$SCHOOL_NAME.'</td>
						<td width="35%" valign="top" >
							<table width="100%" >
								<tr>
									<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>Daily Course Offerings</b></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>';
				
	
		$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$CURRENT_DATE,date_default_timezone_get());
				
		$footer = '<table width="100%" >
						<tr>
							<td width="33%" valign="top" style="font-size:10px;" ><i>'.$date.'</i></td>
							<td width="33%" valign="top" style="font-size:10px;" align="center" ></td>
							<td width="33%" valign="top" style="font-size:10px;" align="right" ><i>Page {PAGENO} of {nb}</i></td>
						</tr>
					</table>';
		
		$mpdf = new \Mpdf\Mpdf([
			'margin_left' => 7,
			'margin_right' => 5,
			'margin_top' => 25,
			'margin_bottom' => 15,
			'margin_header' => 3,
			'margin_footer' => 10,
			'default_font_size' => 8,
			'format' => [210, 296],
		]);
		$mpdf->autoPageBreak = true;
		
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLFooter($footer);
		
        $mpdf->AddPage();

        $res_daily = $db->Execute($query_daily);
        
        $data  = [];
        $terms = [];
        while (!$res_daily->EOF) 
        {
            $data[$res_daily->fields['SCHEDULE_DATE']][]  = $res_daily->fields;
            $terms[$res_daily->fields['SCHEDULE_DATE']]   = array('SCHEDULE_DATE'=>$res_daily->fields['SCHEDULE_DATE']);
            $res_daily->MoveNext();
        }

        $txt  = "";
		$txt .='<table border="0" cellspacing="0" cellpadding="3" width="100%">
                    <thead>
                        <tr>
                            <td width="25%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Course</b></td>
                            <td width="16%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Course Description</b></td>
                            <td width="15%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Instructor</b></td>
                            <td width="12%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Room</b></td>
                            <td width="22%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Class Times</b></td>
                            <td width="10%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Term</b></td>
                        </tr>
                    </thead>';   
        foreach($terms as $key=>$val)
        {
            $SCHEDULE_DATE = date("m/d/Y l",strtotime($val['SCHEDULE_DATE']));
            $txt .='<tr>
						<td width="25%"><b>'.$SCHEDULE_DATE.' </b></td>
						<td colspan="5"></td>
					</tr>';

            foreach ($data[$val['SCHEDULE_DATE']] as $k => $results)
            {        
                $START_TIME = $results['CLASS_START_TIME'];
                $END_TIME   = $results['CLASS_END_TIME'];
                $CLASS_TIME	= date("h:i A",strtotime($START_TIME)).' to '.date("h:i A",strtotime($END_TIME));

                $txt 	.= '<tr>
								<td >'.$results['COURSE_CODE'].'</td>
								<td >'.$results['COURSE_DESCRIPTION'].'</td>
								<td >'.$results['INSTRUCTOR_NAME'].'</td>
								<td >'.$results['ROOM_NO'].'</td>
								<td >'.$CLASS_TIME.'</td>
								<td >'.date("m/d/Y",strtotime($results['TERM_BEGIN_DATE'])).'</td>
							</tr>';

            }

        }
		$txt 	.= '</table>';
        //echo $txt;exit;

        $header = '<table width="100%" >
                        <tr>
                            <td width="20%" valign="top" >'.$logo.'</td>
                            <td width="45%" valign="top" style="font-size:20px" >'.$SCHOOL_NAME.'</td>
                            <td width="35%" valign="top" >
                                <table width="100%" >
                                    <tr>
                                        <td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>Daily Course Offerings</b></td>
                                    </tr>
									<tr>
										<td width="100%" align="right" >Between: '.$_GET['start_date'].' - '.$_GET['end_date'].'</td>
									</tr>
                                    <tr>
                                        <td><br><br></td>
                                    <tr>
                                </table>
                            </td>
                        </tr>
                    </table>';

			$header_cont= '<!DOCTYPE HTML>
			<html>
			<head>

			</head>
			<body>
			<div> '.$header.' </div>
			</body>
			</html>';

			$html_body_cont = '<!DOCTYPE HTML>
			<html>
			<head> <style>
			table{  margin-top: 2px; }
			table tr{  padding-top: 1px !important; }
			</style>
			</head>
			<body>'.$txt.'</body></html>';

			//$date_footer = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$CURRENT_DATE ,date_default_timezone_get());
            $date_footer = date("l, F d, Y",strtotime($CURRENT_DATE));

			$footer = '<table width="100%" >
					<tr>
						<td width="33%" valign="top" style="font-size:10px;" ><i>'.$date_footer.'</i></td>
						<td width="33%" valign="top" style="font-size:10px;" align="center" ><i></i></td>
						<td></td>							
					</tr>
				</table>';
			$footer_cont= '<!DOCTYPE HTML><html><head><style>
				tbody td{ font-size:14px !important; }
				</style></head><body>'.$footer.'</body></html>';

			$header_path = create_html_file('header_daily_course_offerings.html', $header_cont, "invoice");
			$content_path = create_html_file('content_daily_course_offerings.html', $txt, "invoice");
			$footer_path= create_html_file('footer_daily_course_offerings.html',$footer_cont);

			$file_name = 'Daily_Course_Offering_'.uniqid().'.pdf';
			$exec = 'xvfb-run -a wkhtmltopdf -T 0 -R 0 -B 0 -L 0 --enable-local-file-access --orientation portrait --page-size A4 --page-width 210 --page-height 297 --margin-top 25mm --margin-left 7mm --margin-right 5mm  --margin-bottom 20mm --footer-font-size 8 --footer-right "Page [page] of [toPage]" --header-html ' . $header_path . ' --footer-html  ' . $footer_path . ' ' . $content_path . ' ../school/temp/invoice/' . $file_name . ' 2>&1';

			$pdfdata = array('filepath' => 'temp/invoice/' . $file_name, 'exec' => $exec, 'filename' => $file_name, 'filefullpath' => $http_path . 'school/temp/invoice/' . $file_name);

			exec($pdfdata['exec'], $output, $retval);
			echo 'school/temp/invoice/' . $file_name;
			header('Content-Type: Content-Type: application/pdf');
			header('Content-Disposition: attachment; filename="' . basename($pdfdata['filefullpath']) . '"');
			readfile($pdfdata['filepath']);

            unlink('../school/temp/invoice/header_daily_course_offerings.html');
            unlink('../school/temp/invoice/content_daily_course_offerings.html');
            unlink('../school/temp/invoice/footer_daily_course_offerings.html');
			exit;
        
        // $mpdf->WriteHTML($txt);
		
		
		// $file_name = 'Daily_Course_Offering_'.uniqid().'.pdf';
		// $mpdf->Output($file_name, 'D');
		// return $file_name;	
		
	} 
    else if($_POST['FORMAT'] == 2){
		// Excel
	}
} ?>