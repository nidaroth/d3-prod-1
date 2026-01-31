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
	if($_GET['p'] == 'r') {
		$_POST['PK_TERM_MASTER']	= $_GET['tm'];
		$_POST['FORMAT']			= $_GET['FORMAT'];
		$_POST['campus']			= $_GET['campus'];
	}

	$PK_COURSE_OFFERING_ARR = explode(",",$_GET['co_id']);

	$query = "SELECT CONCAT(LAST_NAME,', ',FIRST_NAME, ' ', SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) as STUD_NAME, STUDENT_ID, IMAGE, HOME_PHONE, CELL_PHONE, WORK_PHONE, EMAIL, COURSE_OFFERING_STUDENT_STATUS 
	FROM
	S_STUDENT_MASTER 
	LEFT JOIN S_STUDENT_CONTACT ON S_STUDENT_CONTACT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' 
	, S_STUDENT_ACADEMICS, S_STUDENT_COURSE  
	LEFT JOIN M_COURSE_OFFERING_STUDENT_STATUS ON M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS
	WHERE 
	S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	S_STUDENT_COURSE.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER ";
	
	$group_by = " GROUP BY S_STUDENT_MASTER.PK_STUDENT_MASTER ";
	$order_by = " ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME) ASC ";
	
	$cs_query = "select DATE_FORMAT(DEF_START_TIME,'%h:%i %p') AS START_TIME, DATE_FORMAT(DEF_END_TIME,'%h:%i %p') AS END_TIME, HOURS, CONCAT(ROOM_NO,' - ',ROOM_DESCRIPTION) AS ROOM_NO,FA_UNITS,  UNITS, CONCAT(S_EMPLOYEE_MASTER_INST.FIRST_NAME,' ',S_EMPLOYEE_MASTER_INST.MIDDLE_NAME,' ',S_EMPLOYEE_MASTER_INST.LAST_NAME) AS INSTRUCTOR_NAME,ATTENDANCE_TYPE, BEGIN_DATE, IF(BEGIN_DATE = '0000-00-00','', DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 , IF(S_TERM_MASTER.END_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.END_DATE, '%m/%d/%Y' )) AS  END_DATE_1,SESSION,SESSION_NO, COURSE_OFFERING_STATUS, TRANSCRIPT_CODE, COURSE_DESCRIPTION, IF(S_COURSE_OFFERING_SCHEDULE.START_DATE = '0000-00-00','',DATE_FORMAT(S_COURSE_OFFERING_SCHEDULE.START_DATE, '%m/%d/%Y' )) AS START_DATE, IF(S_COURSE_OFFERING_SCHEDULE.END_DATE = '0000-00-00','',DATE_FORMAT(S_COURSE_OFFERING_SCHEDULE.END_DATE, '%m/%d/%Y' )) AS END_DATE, DEF_HOURS, CAMPUS_CODE  from 
	S_COURSE_OFFERING 
	LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_COURSE_OFFERING.PK_CAMPUS
	LEFT JOIN M_COURSE_OFFERING_STATUS ON M_COURSE_OFFERING_STATUS.PK_COURSE_OFFERING_STATUS = S_COURSE_OFFERING.PK_COURSE_OFFERING_STATUS 
	LEFT JOIN M_ATTENDANCE_TYPE ON M_ATTENDANCE_TYPE.PK_ATTENDANCE_TYPE = S_COURSE_OFFERING.PK_ATTENDANCE_TYPE 
	LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER 
	LEFT JOIN S_EMPLOYEE_MASTER AS S_EMPLOYEE_MASTER_INST ON S_EMPLOYEE_MASTER_INST.PK_EMPLOYEE_MASTER = INSTRUCTOR 
	LEFT JOIN M_CAMPUS_ROOM ON M_CAMPUS_ROOM.PK_CAMPUS_ROOM = S_COURSE_OFFERING.PK_CAMPUS_ROOM
	LEFT JOIN S_COURSE_OFFERING_SCHEDULE ON S_COURSE_OFFERING_SCHEDULE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING 
	LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
	WHERE 
	S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";
	
	$cs_sch_query = "SELECT DATE_FORMAT(MIN(SCHEDULE_DATE), '%m/%d/%Y' ) as START_DATE, DATE_FORMAT(MAX(SCHEDULE_DATE), '%m/%d/%Y' ) as END_DATE, COUNT(PK_COURSE_OFFERING_SCHEDULE_DETAIL) as MEETING_COUNT FROM S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE  ACTIVE = 1 ";
	
	if($_POST['FORMAT'] == 1 || $_POST['FORMAT'] == 3){
		/////////////////////////////////////////////////////////////////
		
		require_once '../global/mpdf/vendor/autoload.php';
		
		function co_with_picture_pdf($PK_COURSE_OFFERING_1, $one_stud_per_pdf){
			global $db, $query, $group_by, $order_by, $cs_query, $cs_sch_query;
			
			$PK_COURSE_OFFERING_ARR = explode(",",$PK_COURSE_OFFERING_1);
			
			$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
			$SCHOOL_NAME = $res->fields['SCHOOL_NAME'];
			$PDF_LOGO 	 = $res->fields['PDF_LOGO'];
		
			$logo = "";
			if($PDF_LOGO != '')
				$logo = '<img src="'.$PDF_LOGO.'" height="50px" />';
				
			$mpdf = new \Mpdf\Mpdf([
				'margin_left' => 7,
				'margin_right' => 5,
				'margin_top' => 50,
				'margin_bottom' => 15,
				'margin_header' => 3,
				'margin_footer' => 10,
				'default_font_size' => 8,
				'format' => [210, 296],
			]);
			$mpdf->autoPageBreak = true;
			
			$timezone = $_SESSION['PK_TIMEZONE'];
			if($timezone == '' || $timezone == 0) {
				$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
				$timezone = $res->fields['PK_TIMEZONE'];
				if($timezone == '' || $timezone == 0)
					$timezone = 4;
			}
			$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
			
			$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$res->fields['TIMEZONE'],date_default_timezone_get());
						
			$footer = '<table width="100%" >
							<tr>
								<td width="33%" valign="top" style="font-size:10px;" ><i>'.$date.'</i></td>
								<td width="33%" valign="top" style="font-size:10px;" align="center" ></td>
								<td width="33%" valign="top" style="font-size:10px;" align="right" ><i>Page {PAGENO} of [pagetotal]</i></td>
							</tr>
						</table>';
						
			$mpdf->SetHTMLFooter($footer);
			
			$txt   = "";
			foreach($PK_COURSE_OFFERING_ARR as $PK_COURSE_OFFERING){
			
				$res_cs = $db->Execute($cs_query." AND S_COURSE_OFFERING.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ");
				$res_cs_sch = $db->Execute($cs_sch_query." AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ");
				
				$header = '<table width="100%" >
						<tr>
							<td width="20%" valign="top" >'.$logo.'</td>
							<td width="38%" valign="top" style="font-size:18px" >'.$SCHOOL_NAME.'</td>
							<td width="42%" valign="top" >
								<table width="100%" >
									<tr>
										<td width="100%" align="right" style="font-size:15px;border-bottom:1px solid #000;" ><b>Course Offering Roster With Pictures</b></td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:12px;" ><b>Campus: '.$res_cs->fields['CAMPUS_CODE'].'</b></td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
					<br />';
					
				$header .= '<table border="1" cellspacing="0" cellpadding="3" width="100%">
								<tr>
									<td align="center" width="25%" ><b>Course: '.$res_cs->fields['TRANSCRIPT_CODE'].'</b><br />'.$res_cs->fields['COURSE_DESCRIPTION'].'</td>
									<td align="center" width="20%" ><b>Term Date</b><br />'.$res_cs->fields['BEGIN_DATE_1'].' - '.$res_cs->fields['END_DATE_1'].'</td>
									<td align="center" width="25%" ><b>Instructor</b><br />'.$res_cs->fields['INSTRUCTOR_NAME'].'</td>
									<td align="center" width="20%"><b>Room</b><br />'.$res_cs->fields['ROOM_NO'].'</td>
									<td align="center" width="10%"><b>Attendance</b><br />'.$res_cs->fields['ATTENDANCE_TYPE'].'</td>
								</tr>
								<tr>
									<td colspan="5" >
										<b>Course: </b>'.$res_cs_sch->fields['START_DATE'].' to '.$res_cs_sch->fields['END_DATE'].' - '.$res_cs_sch->fields['MEETING_COUNT'].' Meetings
										<br /><b>Class: </b>'.$res_cs->fields['START_TIME'].' to '.$res_cs->fields['END_TIME'].' - '.$res_cs->fields['DEF_HOURS'].' hours
									</td>
								</tr>
							</table>';
				
				$mpdf->SetHTMLHeader($header);
				
				$mpdf->AddPage('','',1);
				$mpdf->AliasNbPageGroups('[pagetotal]');
				
				$txt = '<table border="0" cellspacing="0" cellpadding="10" width="100%">'; 
				
				$cond1 = " AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ";
				$i = 1;
				$res_stud = $db->Execute($query." ".$cond1." ".$group_by." ".$order_by);
				while (!$res_stud->EOF) {
					if($i == 1)
						$txt .= '<tr>';
						
					$IMAGE = $res_stud->fields['IMAGE'];
					if($IMAGE == '')
						$IMAGE = "../backend_assets/images/user.png";
					
					$txt .= '<td width="25%" align="center" >
								<img src="'.$IMAGE.'" width="130px" ><br />
								'.$res_stud->fields['STUD_NAME'].'<br />
								'.$res_stud->fields['STUDENT_ID'].'
							</td>';
							
					$i++;
					
					if($i == 5) {
						$txt .= '</tr>';
						$i = 1;
					}
						
					$res_stud->MoveNext();
				}
				
				if($i <= 5) {
					for(; $i <= 4 ; $i++)
						$txt .= '<td></td>';
						
					$txt .= '</tr>';
				}
					
				$txt .= '</table>';
				
				$mpdf->WriteHTML($txt);
			}
			
			if($one_stud_per_pdf == 0) {
				$file_name = 'Course Offering Roster With Images.pdf';
				$mpdf->Output($file_name, 'D');
			} else {
				// $file_dir_1 = '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/';
				$file_dir_1 = '../backend_assets/tmp_upload/';
				$file_name  = "Course Offering Roster with Pictures - ".$res_cs->fields['BEGIN_DATE'].' - '.$res_cs->fields['TRANSCRIPT_CODE'].' - '.substr($res_cs->fields['SESSION'],0,1)."-".$res_cs->fields['SESSION_NO'].'.pdf';
				$mpdf->Output($file_dir_1.$file_name, 'F');
			}
			
			return $file_name;	
		}
		
		if($_GET['FORMAT'] == 3) {
			function unlinkRecursive($dir, $deleteRootToo){
				if(!$dh = @opendir($dir)){
					return;
				}
				while (false !== ($obj = readdir($dh))){
					if($obj == '.' || $obj == '..'){
						continue;
					}
					if (!@unlink($dir . '/' . $obj)){
						unlinkRecursive($dir.'/'.$obj, true);
					}
				}
				closedir($dh);
				if ($deleteRootToo){
					@rmdir($dir);
				}
				return;
			}
			
			class FlxZipArchive extends ZipArchive {
				public function addDir($location, $name) {
					$this->addEmptyDir($name);
					$this->addDirDo($location, $name);
				} 
				private function addDirDo($location, $name) {
					$name .= '/';
					$location .= '/';
					$dir = opendir ($location);
					while ($file = readdir($dir)){
						if ($file == '.' || $file == '..') 
							continue;
						$do = (filetype( $location . $file) == 'dir') ? 'addDir' : 'addFile';
						$this->$do($location . $file, $name . $file);
					}
				}
			}
			
			// $folder = '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/CO';
			$folder = '../backend_assets/tmp_upload/CO';
			$zip_file_name  = $folder.'.zip';
			if($folder != '') {
				unlinkRecursive("$folder/",0);
				unlink($zip_file_name);
				@rmdir($folder);
			}
			mkdir($folder);
	
			$za = new FlxZipArchive;
			$res = $za->open($zip_file_name, ZipArchive::CREATE);
			if($res === TRUE) {
				$PK_COURSE_OFFERING_ARR = explode(",",$_GET['co_id']);
				foreach($PK_COURSE_OFFERING_ARR as $PK_COURSE_OFFERING) {
					$file_name_1 = co_with_picture_pdf($PK_COURSE_OFFERING, 1);
					
					// $za->addFile('../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/'.$file_name_1, $file_name_1);
					$za->addFile('../backend_assets/tmp_upload/'.$file_name_1, $file_name_1);
					
					// $file_name_arr[] = '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/'.$file_name_1;
					$file_name_arr[] = '../backend_assets/tmp_upload/'.$file_name_1;
				}
				
				$za->close();
				
				unlinkRecursive("$folder/",0);
				@rmdir($folder);
				
				foreach($file_name_arr as $file_name_2)
					unlink($file_name_2);
				
				header("location:".$zip_file_name);
			}
		} else 
			co_with_picture_pdf($_GET['co_id'], 0);
	}
} ?>