<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2 && $_SESSION['PK_ROLES'] != 3) ){ 
	header("location:../index.php");
	exit;
}

if($_GET['id'] != '' && $_SESSION['PK_ROLES'] == 3){
	$res = $db->Execute("SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER FROM S_STUDENT_MASTER,S_STUDENT_CAMPUS WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = '$_GET[id]' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_CAMPUS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND S_STUDENT_CAMPUS.PK_CAMPUS = '$_SESSION[PK_CAMPUS]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_employee.php?t=".$_GET['t']);
		exit;
	}
}

if($_GET['act'] == 'document_del'){
	$db->Execute("DELETE FROM S_STUDENT_DOCUMENTS WHERE PK_STUDENT_DOCUMENTS = '$_GET[iid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	header("location:student.php?id=".$_GET['id'].'&tab=documentsTab&t='.$_GET['t']);
} else if($_GET['act'] == 'task_del'){
	$db->Execute("DELETE FROM S_STUDENT_TASK WHERE PK_STUDENT_TASK = '$_GET[iid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	header("location:student.php?id=".$_GET['id'].'&tab=taskTab&t='.$_GET['t']);
} else if($_GET['act'] == 'img_del'){
	$res = $db->Execute("SELECT IMAGE FROM S_STUDENT_MASTER WHERE PK_STUDENT_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	unlink($res->fields['IMAGE']);
	$db->Execute("UPDATE S_STUDENT_MASTER SET IMAGE = '' WHERE PK_STUDENT_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	header("location:student.php?id=".$_GET['id'].'&tab=infoTab&t='.$_GET['t']);
} else if($_GET['act'] == 'contact_del'){
	$db->Execute("DELETE FROM S_STUDENT_CONTACT WHERE PK_STUDENT_CONTACT = '$_GET[iid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	header("location:student.php?id=".$_GET['id'].'&tab=contactTab&t='.$_GET['t']);
} else if($_GET['act'] == 'notes_del'){
	$db->Execute("DELETE FROM S_STUDENT_NOTES WHERE PK_STUDENT_NOTES = '$_GET[iid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	header("location:student.php?id=".$_GET['id'].'&tab=noteTab&t='.$_GET['t']);
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	if($_POST['FORM_NAME'] == 'form1'){
		$STUDENT_MASTER['FIRST_NAME']  			= $_POST['FIRST_NAME'];
		$STUDENT_MASTER['LAST_NAME']  			= $_POST['LAST_NAME'];
		$STUDENT_MASTER['MIDDLE_NAME']  		= $_POST['MIDDLE_NAME'];
		$STUDENT_MASTER['OTHER_NAME']  			= $_POST['OTHER_NAME'];
		$STUDENT_MASTER['DATE_OF_BIRTH']  		= $_POST['DATE_OF_BIRTH'];
		$STUDENT_MASTER['GENDER']  				= $_POST['GENDER'];
		$STUDENT_MASTER['DRIVERS_LICENSE']  	= $_POST['DRIVERS_LICENSE'];
		$STUDENT_MASTER['PK_MARITAL_STATUS']  	= $_POST['PK_MARITAL_STATUS'];
		$STUDENT_MASTER['PK_COUNTRY_CITIZEN']  	= $_POST['PK_COUNTRY_CITIZEN'];
		$STUDENT_MASTER['PK_CITIZENSHIP']  		= $_POST['PK_CITIZENSHIP'];
		$STUDENT_MASTER['IPEDS_ETHNICITY']  	= $_POST['IPEDS_ETHNICITY'];
		$STUDENT_MASTER['PLACE_OF_BIRTH']  		= $_POST['PLACE_OF_BIRTH'];
		$STUDENT_MASTER['PK_STUDENT_STATUS'] 	= $_POST['PK_STUDENT_STATUS'];
	
		if($STUDENT_MASTER['DATE_OF_BIRTH'] != '')
			$STUDENT_MASTER['DATE_OF_BIRTH'] = date("Y-m-d",strtotime($STUDENT_MASTER['DATE_OF_BIRTH']));
		else
			$STUDENT_MASTER['DATE_OF_BIRTH'] = '';

		$STUDENT_OTHER['PK_REPRESENTATIVE'] 		= $_POST['PK_REPRESENTATIVE'];
		$STUDENT_OTHER['PK_SECOND_REPRESENTATIVE'] 	= $_POST['PK_SECOND_REPRESENTATIVE'];
		$STUDENT_OTHER['PK_LEAD_SOURCE'] 			= $_POST['PK_LEAD_SOURCE'];
		$STUDENT_OTHER['PK_CONTACT_SOURCE'] 		= $_POST['PK_CONTACT_SOURCE'];
		$STUDENT_OTHER['STATUS_DATE'] 				= $_POST['STATUS_DATE'];
		$STUDENT_OTHER['PK_CAMPUS_PROGRAM'] 		= $_POST['PK_CAMPUS_PROGRAM'];
		$STUDENT_OTHER['FIRST_TERM_DATE'] 			= $_POST['FIRST_TERM_DATE'];
		$STUDENT_OTHER['PK_FUNDING'] 				= $_POST['PK_FUNDING'];
		/*$STUDENT_OTHER['ENTRY_DATE'] 				= $_POST['ENTRY_DATE'];
		$STUDENT_OTHER['ENTRY_TIME'] 				= $_POST['ENTRY_TIME'];*/
		$STUDENT_OTHER['LEAD_ID'] 					= $_POST['LEAD_ID'];
		$STUDENT_OTHER['ADM_USER_ID'] 				= $_POST['ADM_USER_ID'];
		$STUDENT_OTHER['NOTES'] 					= $_POST['NOTES'];
		
		if($STUDENT_OTHER['STATUS_DATE'] != '')
			$STUDENT_OTHER['STATUS_DATE'] = date("Y-m-d",strtotime($STUDENT_OTHER['STATUS_DATE']));
		else
			$STUDENT_OTHER['STATUS_DATE'] = '';
			
		if($STUDENT_OTHER['FIRST_TERM_DATE'] != '')
			$STUDENT_OTHER['FIRST_TERM_DATE'] = date("Y-m-d",strtotime($STUDENT_OTHER['FIRST_TERM_DATE']));
		else
			$STUDENT_OTHER['FIRST_TERM_DATE'] = '';
			
		/*if($STUDENT_OTHER['ENTRY_DATE'] != '')
			$STUDENT_OTHER['ENTRY_DATE'] = date("Y-m-d",strtotime($STUDENT_OTHER['ENTRY_DATE']));
		else
			$STUDENT_OTHER['ENTRY_DATE'] = '';
			
		if($STUDENT_OTHER['ENTRY_TIME'] != '')
			$STUDENT_OTHER['ENTRY_TIME'] = date("H:i:s",strtotime($STUDENT_OTHER['ENTRY_TIME']));
		else
			$STUDENT_OTHER['ENTRY_TIME'] = '';*/
		
		$STUDENT_ACADEMICS['HS_CLASS_RANK'] 		 = $_POST['HS_CLASS_RANK'];
		$STUDENT_ACADEMICS['HS_CGPA'] 				 = $_POST['HS_CGPA'];
		$STUDENT_ACADEMICS['POST_SEC_CUM_CGPA'] 	 = $_POST['POST_SEC_CUM_CGPA'];
		$STUDENT_ACADEMICS['PK_PREVIOUS_COLLEGE'] 	 = $_POST['PK_PREVIOUS_COLLEGE'];
		$STUDENT_ACADEMICS['PK_HIGHEST_LEVEL_OF_ED'] = $_POST['PK_HIGHEST_LEVEL_OF_ED'];
		$STUDENT_ACADEMICS['EXPECTED_GRAD_DATE'] 	 = $_POST['EXPECTED_GRAD_DATE'];
		$STUDENT_ACADEMICS['PK_SESSION'] 			 = $_POST['PK_SESSION'];
		$STUDENT_ACADEMICS['FULL_PART_TIME'] 		 = $_POST['FULL_PART_TIME'];
		$STUDENT_ACADEMICS['PK_STUDENT_GROUP'] 		 = $_POST['PK_STUDENT_GROUP'];
		$STUDENT_ACADEMICS['PK_FERPA_BLOCK'] 		 = $_POST['PK_FERPA_BLOCK'];
		$STUDENT_ACADEMICS['STUDENT_ID'] 			 = $_POST['STUDENT_ID'];
		$STUDENT_ACADEMICS['CONTRACT_SIGNED_DATE'] 	 = $_POST['CONTRACT_SIGNED_DATE'];
		$STUDENT_ACADEMICS['CONTRACT_END_DATE'] 	 = $_POST['CONTRACT_END_DATE'];
		
		if($STUDENT_ACADEMICS['EXPECTED_GRAD_DATE'] != '')
			$STUDENT_ACADEMICS['EXPECTED_GRAD_DATE'] = date("Y-m-d",strtotime($STUDENT_ACADEMICS['EXPECTED_GRAD_DATE']));
		else
			$STUDENT_ACADEMICS['EXPECTED_GRAD_DATE'] = '';
			
		if($STUDENT_ACADEMICS['CONTRACT_SIGNED_DATE'] != '')
			$STUDENT_ACADEMICS['CONTRACT_SIGNED_DATE'] = date("Y-m-d",strtotime($STUDENT_ACADEMICS['CONTRACT_SIGNED_DATE']));
		else
			$STUDENT_ACADEMICS['CONTRACT_SIGNED_DATE'] = '';	
			
		if($STUDENT_ACADEMICS['CONTRACT_END_DATE'] != '')
			$STUDENT_ACADEMICS['CONTRACT_END_DATE'] = date("Y-m-d",strtotime($STUDENT_ACADEMICS['CONTRACT_END_DATE']));
		else
			$STUDENT_ACADEMICS['CONTRACT_END_DATE'] = '';
		
		if($_GET['id'] == ''){
			$res = $db->Execute("SELECT PK_STUDENT_STATUS FROM M_STUDENT_STATUS WHERE PK_STUDENT_STATUS_MASTER = '7' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
			$STUDENT_MASTER['PK_STUDENT_STATUS'] = $res->fields['PK_STUDENT_STATUS'];
			
			$STUDENT_MASTER['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
			$STUDENT_MASTER['CREATED_BY']  = $_SESSION['PK_USER'];
			$STUDENT_MASTER['CREATED_ON']  = date("Y-m-d H:i");
			db_perform('S_STUDENT_MASTER', $STUDENT_MASTER, 'insert');
			$PK_STUDENT_MASTER = $db->insert_ID();
			
			$STUDENT_OTHER['ENTRY_DATE'] 		= date("Y-m-d");
			$STUDENT_OTHER['ENTRY_TIME'] 		= date("H:i:s",strtotime(date("Y-m-d H:i:s")));
			$STUDENT_OTHER['LEAD_ID'] 			= $PK_STUDENT_MASTER;
			
			$STUDENT_OTHER['PK_STUDENT_MASTER'] = $PK_STUDENT_MASTER;
			$STUDENT_OTHER['PK_ACCOUNT']  		= $_SESSION['PK_ACCOUNT'];
			$STUDENT_OTHER['CREATED_BY']  		= $_SESSION['PK_USER'];
			$STUDENT_OTHER['CREATED_ON']  		= date("Y-m-d H:i");
			db_perform('S_STUDENT_OTHER', $STUDENT_OTHER, 'insert');
			
			$STUDENT_ACADEMICS['PK_STUDENT_MASTER'] = $PK_STUDENT_MASTER;
			$STUDENT_ACADEMICS['PK_ACCOUNT']  		= $_SESSION['PK_ACCOUNT'];
			$STUDENT_ACADEMICS['CREATED_BY']  		= $_SESSION['PK_USER'];
			$STUDENT_ACADEMICS['CREATED_ON']  		= date("Y-m-d H:i");
			db_perform('S_STUDENT_ACADEMICS', $STUDENT_ACADEMICS, 'insert');
			
		} else {
			$PK_STUDENT_MASTER 				= $_GET['id'];
			$STUDENT_MASTER['EDITED_BY']   	= $_SESSION['PK_USER'];
			$STUDENT_MASTER['EDITED_ON']   	= date("Y-m-d H:i");
			db_perform('S_STUDENT_MASTER', $STUDENT_MASTER, 'update'," PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			
			$STUDENT_OTHER['EDITED_BY']   = $_SESSION['PK_USER'];
			$STUDENT_OTHER['EDITED_ON']   = date("Y-m-d H:i");
			db_perform('S_STUDENT_OTHER', $STUDENT_OTHER, 'update'," PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
			
			$STUDENT_ACADEMICS['EDITED_BY']   = $_SESSION['PK_USER'];
			$STUDENT_ACADEMICS['EDITED_ON']   = date("Y-m-d H:i");
			db_perform('S_STUDENT_ACADEMICS', $STUDENT_ACADEMICS, 'update'," PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
		}
		
		$file_dir_1 = '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/student/';
		if($_FILES['IMAGE']['name'] != ''){
			require_once("../global/image_fun.php");
			$extn 			= explode(".",$_FILES['IMAGE']['name']);
			$iindex			= count($extn) - 1;
			$rand_string 	= time()."-".rand(10000,99999);
			$file11			= 'stu_profile_'.$_SESSION['PK_USER'].$rand_string.".".$extn[$iindex];	
			$extension   	= strtolower($extn[$iindex]);
			
			if($extension == "gif" || $extension == "jpeg" || $extension == "pjpeg" || $extension == "png" || $extension == "jpg"){ 
				$newfile1    = $file_dir_1.$file11;
				$image_path  = $newfile1;
						
				move_uploaded_file($_FILES['IMAGE']['tmp_name'], $image_path);
				$size = getimagesize($image_path);
				$new_w = 500;
				$new_h = 500;
				
				if($size['0'] > $new_w || $size['1'] >  $new_h) {
					$image_path = thumb_gallery($file11,$file11,$new_w,$new_h,$file_dir_1,1);
				}
				$STUDENT_MASTER1['IMAGE'] = $image_path;
				db_perform('S_STUDENT_MASTER', $STUDENT_MASTER1, 'update'," PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			}
		}
		
		if($_POST['SSN'] != '') {
			$STUDENT_MASTER2['SSN'] = my_encrypt($_SESSION['PK_ACCOUNT'].$PK_STUDENT_MASTER,$_POST['SSN']);
			db_perform('S_STUDENT_MASTER', $STUDENT_MASTER2, 'update'," PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		}
		
		foreach($_POST['PK_CAMPUS'] as $PK_CAMPUS) {
			$res = $db->Execute("SELECT PK_STUDENT_CAMPUS FROM S_STUDENT_CAMPUS WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS = '$PK_CAMPUS' "); 
			if($res->RecordCount() == 0) {
				$STUDENT_CAMPUS['PK_CAMPUS']   			= $PK_CAMPUS;
				$STUDENT_CAMPUS['PK_STUDENT_MASTER'] 	= $PK_STUDENT_MASTER;
				$STUDENT_CAMPUS['PK_ACCOUNT'] 			= $_SESSION['PK_ACCOUNT'];
				$STUDENT_CAMPUS['CREATED_BY']  			= $_SESSION['PK_USER'];
				$STUDENT_CAMPUS['CREATED_ON']  			= date("Y-m-d H:i");
				db_perform('S_STUDENT_CAMPUS', $STUDENT_CAMPUS, 'insert');
				$PK_STUDENT_CAMPUS_ARR[] = $db->insert_ID();
			} else {
				$PK_STUDENT_CAMPUS_ARR[] = $res->fields['PK_STUDENT_CAMPUS'];
			}
		}
		
		$cond = "";
		if(!empty($PK_STUDENT_CAMPUS_ARR))
			$cond = " AND PK_STUDENT_CAMPUS NOT IN (".implode(",",$PK_STUDENT_CAMPUS_ARR).") ";
		
		if($_SESSION['PK_ROLES'] == 2)
			$db->Execute("DELETE FROM S_STUDENT_CAMPUS WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond "); 
		
		foreach($_POST['RACE'] as $PK_RACE){
			$res = $db->Execute("SELECT PK_STUDENT_RACE FROM S_STUDENT_RACE WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_RACE = '$PK_RACE' "); 
			if($res->RecordCount() == 0) {
				$STUDENT_RACE['PK_RACE']   			= $PK_RACE;
				$STUDENT_RACE['PK_STUDENT_MASTER'] 	= $PK_STUDENT_MASTER;
				$STUDENT_RACE['PK_ACCOUNT'] 			= $_SESSION['PK_ACCOUNT'];
				$STUDENT_RACE['CREATED_BY']  			= $_SESSION['PK_USER'];
				$STUDENT_RACE['CREATED_ON']  			= date("Y-m-d H:i");
				db_perform('S_STUDENT_RACE', $STUDENT_RACE, 'insert');
				$PK_STUDENT_RACE_ARR[] = $db->insert_ID();
			} else {
				$PK_STUDENT_RACE_ARR[] = $res->fields['PK_STUDENT_RACE'];
			}
		}
		
		$cond = "";
		if(!empty($PK_STUDENT_RACE_ARR))
			$cond = " AND PK_STUDENT_RACE NOT IN (".implode(",",$PK_STUDENT_RACE_ARR).") ";
		
		$db->Execute("DELETE FROM S_STUDENT_RACE WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond "); 
		
		$i = 0;
		foreach($_POST['PK_QUESTIONNAIRE'] as $PK_QUESTIONNAIRE){
			$res = $db->Execute("SELECT PK_STUDENT_QUESTIONNAIRE FROM S_STUDENT_QUESTIONNAIRE WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_QUESTIONNAIRE = '$PK_QUESTIONNAIRE' "); 
			if($res->RecordCount() == 0) {
				$STUDENT_QUESTIONNAIRE['PK_QUESTIONNAIRE']  = $PK_QUESTIONNAIRE;
				$STUDENT_QUESTIONNAIRE['ANSWER']   			= $_POST['ANSWER'][$i];
				$STUDENT_QUESTIONNAIRE['PK_STUDENT_MASTER'] = $PK_STUDENT_MASTER;
				$STUDENT_QUESTIONNAIRE['PK_ACCOUNT'] 		= $_SESSION['PK_ACCOUNT'];
				$STUDENT_QUESTIONNAIRE['CREATED_BY']  		= $_SESSION['PK_USER'];
				$STUDENT_QUESTIONNAIRE['CREATED_ON']  		= date("Y-m-d H:i");
				db_perform('S_STUDENT_QUESTIONNAIRE', $STUDENT_QUESTIONNAIRE, 'insert');
				$PK_STUDENT_QUESTIONNAIRE_ARR[] = $db->insert_ID();
			} else {
				$PK_STUDENT_QUESTIONNAIRE_ARR[] = $res->fields['PK_STUDENT_QUESTIONNAIRE'];
			}
			
			$i++;
		}
		
		$cond = "";
		if(!empty($PK_STUDENT_QUESTIONNAIRE_ARR))
			$cond = " AND PK_STUDENT_QUESTIONNAIRE NOT IN (".implode(",",$PK_STUDENT_QUESTIONNAIRE_ARR).") ";
		
		$db->Execute("DELETE FROM S_STUDENT_QUESTIONNAIRE WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond "); 

		if($_POST['SAVE_CONTINUE'] == 0)
			header("location:manage_student.php?t=".$_GET['t']);
		else
			header("location:student.php?t=".$_GET['t']."&id=".$PK_STUDENT_MASTER."&tab=".str_replace("#","",$_POST['current_tab']));
		exit;
	} else if($_POST['FORM_NAME'] == 'ssn') {
		if($_POST['SSN_1'] != '') {
			$STUDENT_MASTER2['SSN'] = my_encrypt($_SESSION['PK_ACCOUNT'].$PK_STUDENT_MASTER,$_POST['SSN_1']);
			db_perform('S_STUDENT_MASTER', $STUDENT_MASTER2, 'update'," PK_STUDENT_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		}
		
		header("location:student.php?t=".$_GET['t']."&id=".$_GET['id'].'&tab='.str_replace("#","",$_POST['current_tab']));
	} else if($_POST['FORM_NAME'] == 'document') {
		//echo "<pre>";print_r($_POST);exit;
		
		foreach($_POST['PK_DOCUMENT_TYPE'] as $PK_DOCUMENT_TYPE) {
			$res_type = $db->Execute("select DOCUMENT_TYPE from M_DOCUMENT_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DOCUMENT_TYPE = '$PK_DOCUMENT_TYPE' ");
			
			$STUDENT_DOCUMENTS['PK_DOCUMENT_TYPE']  = $PK_DOCUMENT_TYPE;
			$STUDENT_DOCUMENTS['DOCUMENT_TYPE']  	= $res_type->fields['DOCUMENT_TYPE'];
			$STUDENT_DOCUMENTS['REQUESTED_DATE']  	= date("Y-m-d");
			$STUDENT_DOCUMENTS['PK_ACCOUNT']  		= $_SESSION['PK_ACCOUNT'];
			$STUDENT_DOCUMENTS['PK_STUDENT_MASTER'] = $_GET['id'];
			$STUDENT_DOCUMENTS['CREATED_BY']  		= $_SESSION['PK_USER'];
			$STUDENT_DOCUMENTS['CREATED_ON']  		= date("Y-m-d H:i");
			db_perform('S_STUDENT_DOCUMENTS', $STUDENT_DOCUMENTS, 'insert');
		}
		header("location:student.php?t=".$_GET['t']."&id=".$_GET['id'].'&tab=documentsTab');
	} 
}

if($_GET['id'] == ''){
	$FIRST_NAME 			= '';
	$LAST_NAME 				= '';
	$MIDDLE_NAME	 		= '';
	$OTHER_NAME	 			= '';
	$SSN	 				= '';
	$DATE_OF_BIRTH	 		= '';
	$GENDER	 				= '';
	$DRIVERS_LICENSE	 	= '';
	$PK_MARITAL_STATUS	 	= '';
	$PK_COUNTRY_CITIZEN	 	= '';
	$PK_CITIZENSHIP	 		= '';
	$IPEDS_ETHNICITY	 	= '';
	$PLACE_OF_BIRTH	 		= '';
	$IMAGE					= '';
		
	$PK_REPRESENTATIVE			= '';
	$PK_SECOND_REPRESENTATIVE	= '';
	$PK_LEAD_SOURCE				= '';
	$PK_CONTACT_SOURCE			= '';
	$PK_STUDENT_STATUS			= '';
	$STATUS_DATE				= '';
	$PK_CAMPUS_PROGRAM			= '';
	$FIRST_TERM_DATE			= '';
	$PK_FUNDING					= '';
	$ENTRY_DATE					= '';
	$ENTRY_TIME					= '';
	$LEAD_ID					= '';
	$ADM_USER_ID				= '';
	$NOTES						= '';
	
	if($_SESSION['PK_DEPARTMENT_MASTER'] == 2)
		$PK_REPRESENTATIVE = $_SESSION['PK_EMPLOYEE_MASTER'];

} else {
	$res = $db->Execute("SELECT S_STUDENT_MASTER.*, STUDENT_STATUS FROM S_STUDENT_MASTER LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_MASTER.PK_STUDENT_STATUS WHERE PK_STUDENT_MASTER = '$_GET[id]' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_student.php?t=".$_GET['t']);
		exit;
	}
	
	$FIRST_NAME 			= $res->fields['FIRST_NAME'];
	$LAST_NAME 				= $res->fields['LAST_NAME'];
	$MIDDLE_NAME	 		= $res->fields['MIDDLE_NAME'];
	$OTHER_NAME	 			= $res->fields['OTHER_NAME'];
	$SSN	 				= $res->fields['SSN'];
	$DATE_OF_BIRTH	 		= $res->fields['DATE_OF_BIRTH'];
	$GENDER	 				= $res->fields['GENDER'];
	$DRIVERS_LICENSE	 	= $res->fields['DRIVERS_LICENSE'];
	$PK_MARITAL_STATUS	 	= $res->fields['PK_MARITAL_STATUS'];
	$PK_COUNTRY_CITIZEN	 	= $res->fields['PK_COUNTRY_CITIZEN'];
	$PK_CITIZENSHIP	 		= $res->fields['PK_CITIZENSHIP'];
	$IPEDS_ETHNICITY	 	= $res->fields['IPEDS_ETHNICITY'];
	$PLACE_OF_BIRTH	 		= $res->fields['PLACE_OF_BIRTH'];
	$PK_STUDENT_STATUS		= $res->fields['PK_STUDENT_STATUS'];
	$IMAGE					= $res->fields['IMAGE'];
	$STUDENT_STATUS			= $res->fields['STUDENT_STATUS'];
	
	if($DATE_OF_BIRTH != '0000-00-00')
		$DATE_OF_BIRTH = date("m/d/Y",strtotime($DATE_OF_BIRTH));
	else
		$DATE_OF_BIRTH = '';
	
	if($SSN != '') {
		$SSN = 'xxx-xx-xxxx';
		//$SSN = my_decrypt($_SESSION['PK_ACCOUNT'].$_GET['id'],$SSN);
	}

	$res = $db->Execute("SELECT S_STUDENT_OTHER.*,CODE FROM S_STUDENT_OTHER LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_OTHER.PK_CAMPUS_PROGRAM WHERE PK_STUDENT_MASTER = '$_GET[id]' AND S_STUDENT_OTHER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	$PK_REPRESENTATIVE			= $res->fields['PK_REPRESENTATIVE'];
	$PK_SECOND_REPRESENTATIVE	= $res->fields['PK_SECOND_REPRESENTATIVE'];
	$PK_LEAD_SOURCE				= $res->fields['PK_LEAD_SOURCE'];
	$PK_CONTACT_SOURCE			= $res->fields['PK_CONTACT_SOURCE'];
	$STATUS_DATE				= $res->fields['STATUS_DATE'];
	$PK_CAMPUS_PROGRAM			= $res->fields['PK_CAMPUS_PROGRAM'];
	$FIRST_TERM_DATE			= $res->fields['FIRST_TERM_DATE'];
	$PK_FUNDING					= $res->fields['PK_FUNDING'];
	$ENTRY_DATE					= $res->fields['ENTRY_DATE'];
	$ENTRY_TIME					= $res->fields['ENTRY_TIME'];
	$LEAD_ID					= $res->fields['LEAD_ID'];
	$ADM_USER_ID				= $res->fields['ADM_USER_ID'];
	$NOTES						= $res->fields['NOTES'];
	$CAMPUS_PROGRAM				= $res->fields['CODE'];
	
	if($STATUS_DATE != '0000-00-00')
		$STATUS_DATE = date("m/d/Y",strtotime($STATUS_DATE));
	else
		$STATUS_DATE = '';
		
	if($FIRST_TERM_DATE != '0000-00-00')
		$FIRST_TERM_DATE = date("m/d/Y",strtotime($FIRST_TERM_DATE));
	else
		$FIRST_TERM_DATE = '';
		
	if($ENTRY_DATE != '0000-00-00')
		$ENTRY_DATE = date("m/d/Y",strtotime($ENTRY_DATE));
	else
		$ENTRY_DATE = '';
			
	if($ENTRY_TIME == '00:00:00')
		$ENTRY_TIME = '';
	else
		$ENTRY_TIME = date("h:i A",strtotime($ENTRY_TIME));

	$res = $db->Execute("SELECT * FROM S_STUDENT_ACADEMICS WHERE PK_STUDENT_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	$HS_CLASS_RANK			= $res->fields['HS_CLASS_RANK'];
	$HS_CGPA				= $res->fields['HS_CGPA'];
	$POST_SEC_CUM_CGPA		= $res->fields['POST_SEC_CUM_CGPA'];
	$PK_PREVIOUS_COLLEGE	= $res->fields['PK_PREVIOUS_COLLEGE'];
	$PK_HIGHEST_LEVEL_OF_ED	= $res->fields['PK_HIGHEST_LEVEL_OF_ED'];
	$EXPECTED_GRAD_DATE		= $res->fields['EXPECTED_GRAD_DATE'];
	$PK_SESSION				= $res->fields['PK_SESSION'];
	$FULL_PART_TIME			= $res->fields['FULL_PART_TIME'];
	$PK_STUDENT_GROUP		= $res->fields['PK_STUDENT_GROUP'];
	$PK_FERPA_BLOCK			= $res->fields['PK_FERPA_BLOCK'];
	$STUDENT_ID				= $res->fields['STUDENT_ID'];
	$CONTRACT_SIGNED_DATE	= $res->fields['CONTRACT_SIGNED_DATE'];
	$CONTRACT_END_DATE		= $res->fields['CONTRACT_END_DATE'];
	
	if($EXPECTED_GRAD_DATE != '0000-00-00')
		$EXPECTED_GRAD_DATE = date("m/d/Y",strtotime($EXPECTED_GRAD_DATE));
	else
		$EXPECTED_GRAD_DATE = '';
		
	if($CONTRACT_SIGNED_DATE != '0000-00-00')
		$CONTRACT_SIGNED_DATE = date("m/d/Y",strtotime($CONTRACT_SIGNED_DATE));
	else
		$CONTRACT_SIGNED_DATE = '';
		
	if($CONTRACT_END_DATE != '0000-00-00')
		$CONTRACT_END_DATE = date("m/d/Y",strtotime($CONTRACT_END_DATE));
	else
		$CONTRACT_END_DATE = '';
}

if($_GET['tab'] == '' || $_GET['tab'] == 'infoTab' )
	$home_tab = 'active';
else if($_GET['tab'] == 'otherTab')
	$other_tab = 'active';
else if($_GET['tab'] == 'academicTab')
	$academic_tab = 'active';
else if($_GET['tab'] == 'contactTab')
	$contact_tab = 'active';
else if($_GET['tab'] == 'taskTab')
	$task_tab = 'active';
else if($_GET['tab'] == 'questionnaireTab')
	$questionnaire_tab = 'active';
else if($_GET['tab'] == 'noteTab')
	$note_tab = 'active';
else if($_GET['tab'] == 'documentsTab')
	$documents_tab = 'active';
else if($_GET['tab'] == 'requirementsTab')
	$requirements_tab = 'active';
else
	$home_tab = 'active';
	
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
	<link href="../backend_assets/node_modules/Magnific-Popup-master/dist/magnific-popup.css" rel="stylesheet">
	<link href="../backend_assets/dist/css/pages/user-card.css" rel="stylesheet">
	<link href="../backend_assets/dist/css/pages/stylish-tooltip.css" rel="stylesheet">
	<title><?=STUDENT_PAGE_TITLE?> | <?=$title?></title>
	<style>
		#advice-validate-one-required-by-name-PK_DOCUMENT_TYPE{position: absolute;top: 24px;}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-2 align-self-center">
                        <h4 class="text-themecolor"><?=STUDENT_PAGE_TITLE?></h4>
                    </div>
					<div class="col-md-1 align-self-center" >
						<? if($IMAGE != '') { ?>
							<div class="row el-element-overlay">
								<div class="card" style="margin-bottom: 0;" >
									<div class="el-card-item" style="padding-bottom:0" >
										<div class="el-card-avatar el-overlay-1" style="margin-bottom: 0;" > 
											<img src="<?=$IMAGE?>" alt="user" />
											<div class="el-overlay">
												<ul class="el-info">
													<li><a class="btn default btn-outline image-popup-vertical-fit" href="<?=$IMAGE?>"><i class="icon-magnifier"></i></a></li>
												</ul>
											</div>
										</div>
									</div>
								</div>
							</div>
							<!--<img src="<?=$IMAGE?>" style="height: 80px;" />-->
						<? } ?>
					</div>
					<div class="col-md-4 align-self-center">
						<?=$FIRST_NAME.' '.$MIDDLE_NAME.' '.$LAST_NAME?><br />
						<? if($STATUS_DATE != '') echo $STATUS_DATE.'<br />'; ?>
						<? if($STUDENT_STATUS != '') echo $STUDENT_STATUS.'<br />'; ?>
						<? if($CAMPUS_PROGRAM != '') echo $CAMPUS_PROGRAM.'<br />'; ?>
					</div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<ul class="nav nav-tabs customtab" role="tablist">
								<li class="nav-item"> <a class="nav-link <?=$home_tab?>" data-toggle="tab" href="#infoTab" role="tab"><span class="hidden-sm-up"><i class="ti-home"></i></span> <span class="hidden-xs-down"><?=TAB_INFO?></span></a> </li>
								<? if($_GET['id'] != ''){ ?>
								
								<li class="nav-item"> <a class="nav-link <?=$contact_tab?>" data-toggle="tab" href="#contactTab" role="tab"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down"><?=TAB_CONTACT?></span></a> </li>
								
                                <li class="nav-item"> <a class="nav-link <?=$other_tab?>" data-toggle="tab" href="#otherTab" role="tab"><span class="hidden-sm-up"><i class="ti-home"></i></span> <span class="hidden-xs-down"><?=TAB_OTHER?></span></a> </li>
								
                                <li class="nav-item"> <a class="nav-link <?=$academic_tab?>" data-toggle="tab" href="#academicTab" role="tab"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down"><?=TAB_ACADEMICS?></span></a> </li>
								
								<li class="nav-item"> <a class="nav-link <?=$task_tab?>" data-toggle="tab" href="#taskTab" role="tab"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down"><?=TAB_TASK?></span></a> </li>
								
								<li class="nav-item"> <a class="nav-link <?=$note_tab?>" data-toggle="tab" href="#noteTab" role="tab"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down"><?=TAB_NOTES?></span></a> </li>
								
								<li class="nav-item"> <a class="nav-link <?=$documents_tab?>" data-toggle="tab" href="#documentsTab" role="tab"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down"><?=TAB_DOCUMENT?></span></a> </li>
								
								<li class="nav-item"> <a class="nav-link <?=$questionnaire_tab?>" data-toggle="tab" href="#questionnaireTab" role="tab"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down"><?=TAB_QUESTIONNAIRE?></span></a> </li>
								
								<li class="nav-item"> <a class="nav-link <?=$requirements_tab?>" data-toggle="tab" href="#requirementsTab" role="tab"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down"><?=TAB_REQUIREMENTS?></span></a> </li>
								
								<? } ?>
                            </ul>
                            <!-- Tab panes -->
                           <form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
                            <div class="tab-content">
								<div class="tab-pane <?=$home_tab?>" id="infoTab" role="tabpanel">
									<div class="p-20">
										<div class="row">
											<div class="col-sm-8 ">
												<div class="d-flex">
													<div class="col-12 col-sm-4 form-group">
														<input id="FIRST_NAME" name="FIRST_NAME" type="text" class="form-control required-entry" value="<?=$FIRST_NAME?>">
														<span class="bar"></span> 
														<label for="FIRST_NAME"><?=FIRST_NAME?></label>
													</div>
													<div class="col-12 col-sm-4 form-group">
														<input id="LAST_NAME" name="LAST_NAME" type="text" class="form-control required-entry" value="<?=$LAST_NAME?>">
														<span class="bar"></span> 
														<label for="LAST_NAME"><?=LAST_NAME?></label>
													</div>
													<div class="col-12 col-sm-4 form-group">
														<input id="MIDDLE_NAME" name="MIDDLE_NAME" type="text" class="form-control" value="<?=$MIDDLE_NAME?>">
														<span class="bar"></span> 
														<label for="MIDDLE_NAME"><?=MIDDLE_NAME?></label>
													</div>
												</div>
												<div class="d-flex">
													<div class="col-12 col-sm-4 form-group">
														<input id="OTHER_NAME" name="OTHER_NAME" type="text" class="form-control" value="<?=$OTHER_NAME?>">
														<span class="bar"></span> 
														<label for="OTHER_NAME"><?=OTHER_NAME?></label>
													</div>
													<div class="col-12 col-sm-4 form-group">
														<input id="SSN" <? if($SSN == ''){ ?> name="SSN" <? } else echo "disabled"; ?> type="text" class="form-control <? if($SSN == ''){ ?> validate-ssn <? } ?> " value="<?=$SSN?>">
														<span class="bar"></span> 
														<label for="SSN">
															<?=SSN?>
															<? if($SSN != ''){ ?>
																&nbsp;&nbsp;&nbsp;&nbsp;
																<a href="javascript:void(0)" onclick="change_ssn()" ><?=CHANGE?></a>
															<? } ?>
														</label>
													</div>
													<!--
													<div class="col-12 col-sm-1 form-group">
														<span class="mytooltip tooltip-effect-1">
															<span class="tooltip-item tool_tip_custom">
																<i class="mdi mdi-help-circle help_size"></i>
															</span>
															<span class="tooltip-content clearfix">
																<span class="tooltip-text">
																	Also known as Euclid of andria, was a Greek mathematician, often referred. 1111
																</span>
															</span>
														</span>
													</div>
													-->
													<? if($_GET['n'] == 1){ ?>
													<div class="col-12 col-sm-4 form-group">
														<select id="PK_LEAD_SOURCE" name="PK_LEAD_SOURCE" class="form-control">
															<option></option>
															<? $res_type = $db->Execute("select PK_LEAD_SOURCE,LEAD_SOURCE from M_LEAD_SOURCE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by LEAD_SOURCE ASC");
															while (!$res_type->EOF) { ?>
																<option value="<?=$res_type->fields['PK_LEAD_SOURCE']?>" ><?=$res_type->fields['LEAD_SOURCE']?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														<label for="PK_LEAD_SOURCE"><?=SOURCE_CODE?></label>
													</div>
													
													<div class="col-12 col-sm-4 form-group">
														<select id="PK_REPRESENTATIVE" name="PK_REPRESENTATIVE" class="form-control" >
															<option></option>
															<? $res_type = $db->Execute("select PK_EMPLOYEE_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME from S_EMPLOYEE_MASTER,M_DEPARTMENT WHERE S_EMPLOYEE_MASTER.ACTIVE = 1 AND S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DEPARTMENT_MASTER = 2 AND S_EMPLOYEE_MASTER.PK_DEPARTMENT = M_DEPARTMENT.PK_DEPARTMENT order by CONCAT(FIRST_NAME,' ',LAST_NAME) ASC");
															while (!$res_type->EOF) { ?>
																<option value="<?=$res_type->fields['PK_EMPLOYEE_MASTER']?>" <? if($PK_REPRESENTATIVE == $res_type->fields['PK_EMPLOYEE_MASTER']) echo "selected"; ?> ><?=$res_type->fields['NAME']?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														<label for="PK_REPRESENTATIVE"><?=REPRESENTATIVE?></label>
													</div>
													<? } ?>
												</div>
											</div>
											<div class="col-sm-4">
												<? if($IMAGE == '') { ?>
													<!-- <label><? //IMAGE?></label> -->
													<div class="input-group">
														<!--<div class="input-group-prepend" style="margin-top: 5px;" >
															<span class="input-group-text"><? //IMAGE?></span>
														</div> -->
														<div class="custom-file student-profile-image">
															<input type="file" name="IMAGE" class="custom-file-input" id="inputGroupFile01">
															<label class="custom-file-label" for="inputGroupFile01"><img src="../backend_assets/images/user.png">
																<i class="fa fa-edit"></i>
															</label>
														</div>
													</div>
												<? } else { ?>
													<table>
														<tr>
															<td><img src="<?=$IMAGE?>" style="height:80px;" /></td>
															<td>
																<a href="javascript:void(0);" onclick="delete_row('','img')" title="Delete" class="btn btn-primary btn-circle"><i class="far fa-trash-alt"></i> </a>
															</td>
														</tr>
													</table>
												<? } ?>
											</div>
										</div>
										
										<div class="row">
											<div class="col-sm-12 ">
												<div class="d-flex theme-h-border">
												</div>
											</div>
										</div>
										
										<div class="row">
											<div class="col-sm-4 pt-25">
												<div class="d-flex">
													<div class="col-12 col-sm-12 form-group" id="IPEDS_ETHNICITY_LABEL" >
														<? $res = $db->Execute("SELECT CONCAT(ADDRESS,' ',ADDRESS_1) AS ADDRESS, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 
														if($res->RecordCount() > 0) { ?>
															<h4 class="card-title"><?=CURRENT_ADDRESS?></h4>
															
															<? echo $res->fields['ADDRESS'].'<br />'.$res->fields['CITY'].', '.$res->fields['STATE_CODE'].' '.$res->fields['ZIP'].'<br /><br />'; 
															
															if($res->fields['CELL_PHONE'] != '' || $res->fields['HOME_PHONE'] != '' || $res->fields['WORK_PHONE'] != '' || $res->fields['OTHER_PHONE'] != '') { ?>
																<h4 class="card-title"><?=PHONES?>:</h4>
																<table style="width:100%">
																	<? if($res->fields['CELL_PHONE'] != '') { ?>
																		<tr>
																			<td style="width:25%"><?=CELL_PHONE_SHORT?>:</td>
																			<td><?=$res->fields['CELL_PHONE']?></td>
																		</tr>
																	<? } ?>
																	<? if($res->fields['HOME_PHONE'] != '') { ?>
																		<tr>
																			<td><?=HOME_PHONE_SHORT?>:</td>
																			<td><?=$res->fields['HOME_PHONE']?></td>
																		</tr>
																	<? } ?>
																	<? if($res->fields['WORK_PHONE'] != '') { ?>
																		<tr>
																			<td><?=WORK_PHONE_SHORT?>:</td>
																			<td><?=$res->fields['WORK_PHONE']?></td>
																		</tr>
																	<? } ?>
																	<? if($res->fields['OTHER_PHONE'] != '') { ?>
																		<tr>
																			<td><?=OTHER_PHONE_SHORT?>:</td>
																			<td><?=$res->fields['OTHER_PHONE']?></td>
																		</tr>
																	<? } ?>
																</table>
															<? } 
														
															if($res->fields['EMAIL'] != '')
																echo '<br />'.EMAIL.': '.$res->fields['EMAIL'].'<br />'; 
														} ?>
													</div>
												</div>
											</div>
											<div class="col-sm-4 pt-25 theme-v-border">
												<div class="d-flex ">
													<div class="col-12 col-sm-6 form-group">
														<input class="form-control date" type="text" value="<?=$DATE_OF_BIRTH?>" name="DATE_OF_BIRTH" id="DATE_OF_BIRTH">
														<span class="bar"></span> 
														<label for="DATE_OF_BIRTH"><?=DATE_OF_BIRTH?></label>
													</div>
													<div class="col-12 col-sm-6 form-group">
														<select id="GENDER" name="GENDER" class="form-control">
															<option value=""></option>
															<option value="1" <? if($GENDER == 1) echo "selected"; ?> >Male</option>
															<option value="2" <? if($GENDER == 2) echo "selected"; ?> >Female</option>
														</select>
														<span class="bar"></span> 
														 <label for="GENDER"><?=GENDER?></label>
													</div>
												</div>
												
												<div class="d-flex ">
													<div class="col-12 col-sm-6 form-group">
														<input id="DRIVERS_LICENSE" name="DRIVERS_LICENSE" type="text" class="form-control" value="<?=$DRIVERS_LICENSE?>">
														<span class="bar"></span> 
														<label for="DRIVERS_LICENSE"><?=DRIVERS_LICENSE?></label>
													</div>
													
													<div class="col-12 col-sm-6 form-group">
														<select id="PK_MARITAL_STATUS" name="PK_MARITAL_STATUS" class="form-control">
															<option selected></option>
															<? $res_type = $db->Execute("select * from Z_MARITAL_STATUS order by MARITAL_STATUS ASC");
															while (!$res_type->EOF) { ?>
																<option value="<?=$res_type->fields['PK_MARITAL_STATUS']?>" <? if($PK_MARITAL_STATUS == $res_type->fields['PK_MARITAL_STATUS']) echo "selected"; ?> ><?=$res_type->fields['MARITAL_STATUS']?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="PK_MARITAL_STATUS"><?=MARITAL_STATUS?></label>
													</div>
												</div>
												
												<div class="d-flex ">
													<div class="col-12 col-sm-6 form-group">
														<select id="PK_COUNTRY_CITIZEN" name="PK_COUNTRY_CITIZEN" class="form-control" >
															<option value="" ></option>
															<? $res_type = $db->Execute("select PK_COUNTRY, NAME from Z_COUNTRY WHERE ACTIVE = '1' ORDER BY NAME ASC ");
															while (!$res_type->EOF) { ?>
																<option value="<?=$res_type->fields['PK_COUNTRY'] ?>" <? if($PK_COUNTRY_CITIZEN == $res_type->fields['PK_COUNTRY']) echo "selected" ?> ><?=$res_type->fields['NAME']?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														<label for="COUNTRY_CITIZEN"><?=COUNTRY_CITIZEN?></label>
													</div>
													<div class="col-12 col-sm-6 form-group">
														<select id="PK_CITIZENSHIP" name="PK_CITIZENSHIP" class="form-control">
															<option selected></option>
															<? $res_type = $db->Execute("select * from Z_CITIZENSHIP order by CITIZENSHIP ASC");
															while (!$res_type->EOF) { ?>
																<option value="<?=$res_type->fields['PK_CITIZENSHIP']?>" <? if($PK_CITIZENSHIP == $res_type->fields['PK_CITIZENSHIP']) echo "selected"; ?> ><?=$res_type->fields['CITIZENSHIP']?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														<label for="US_CITIZEN"><?=US_CITIZEN?></label>
													</div>
												</div>
												
												<div class="d-flex ">
													<div class="col-12 col-sm-6 form-group">
														<input id="PLACE_OF_BIRTH" name="PLACE_OF_BIRTH" type="text" class="form-control" value="<?=$PLACE_OF_BIRTH?>">
														<span class="bar"></span> 
														<label for="PLACE_OF_BIRTH"><?=PLACE_OF_BIRTH?></label>
													</div>
												</div>
											</div>
											<div class="col-sm-4 pt-25 theme-v-border">
												<div class="d-flex">
													<div class="col-12 col-sm-12 form-group" id="IPEDS_ETHNICITY_LABEL" >
														<input id="IPEDS_ETHNICITY" name="IPEDS_ETHNICITY" type="text" class="form-control" value="<?=$IPEDS_ETHNICITY?>" readonly>
														<span class="bar"></span> 
														<label for="IPEDS_ETHNICITY"><?=IPEDS_ETHNICITY?></label>
													</div>
												</div>
												
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label for="RACE"><?=RACE?></label>
												</div>
												<div class="form-group row d-flex" >
													<? $res_type = $db->Execute("select * from Z_RACE WHERE ACTIVE = 1 ");
													while (!$res_type->EOF) { ?>
													<div class="col-12 col-sm-12" >
														<div class="custom-control custom-checkbox mr-sm-2">
															<? $checked = '';
															$PK_RACE = $res_type->fields['PK_RACE'];
															$res = $db->Execute("select PK_STUDENT_RACE FROM S_STUDENT_RACE WHERE PK_RACE = '$PK_RACE' AND PK_STUDENT_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
															if($res->RecordCount() > 0)
																$checked = 'checked'; ?>
													
															<input type="checkbox" class="custom-control-input" id="RACE_<?=$PK_RACE?>" name="RACE[]" value="<?=$PK_RACE?>" onclick="generate_ethnicity()" <?=$checked ?> >
															<label class="custom-control-label" id="LBL_RACE_<?=$PK_RACE?>" for="RACE_<?=$res_type->fields['PK_RACE']?>" style="line-height: 15px;" ><?=$res_type->fields['RACE']?></label>
														</div>
													</div>
													<?	$res_type->MoveNext();
													} ?>
												</div>
												
												<div class="d-flex">
													<div class="col-12 col-sm-12 ">
														<button onclick="validate_form(1)" type="button" class="btn waves-effect waves-light btn-info"><?=SAVE_CONTINUE?></button>
														<button onclick="validate_form(0)" type="button" class="btn waves-effect waves-light btn-info"><?=SAVE_EXIT?></button>
														<button type="button" onclick="window.location.href='manage_student.php?t=<?=$_GET['t']?>'" class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
													</div>
												</div>
											</div>
										</div>
									</div>
									
								</div>
                                <? if($_GET['id'] != ''){ ?>
								<div class="tab-pane <?=$contact_tab?>" id="contactTab" role="tabpanel">
                                	<div class="">
										<div class="row">
											<div class="col-md-10 align-self-center">
											</div>  
											<div class="col-md-2 align-self-center text-right">
												<div class="d-flex justify-content-end align-items-center">
													<a href="student_contact.php?sid=<?=$_GET['id']?>&t=<?=$_GET['t']?>" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> <?=CREATE_NEW?></a>&nbsp;&nbsp;
												</div>
											</div>
										</div>
										
										<div class="table-responsive p-20">
											<table class="table table-hover">
												<thead>
													<tr>
														<th><?=CONTACT_TYPE?></th>
														<th><?=ADDRESS?></th>
														<th><?=CELL_PHONE?></th>
														<th><?=OPTIONS?></th>
													</tr>
												</thead>
												<tbody>
													<? $res_type = $db->Execute("select PK_STUDENT_CONTACT,HOME_PHONE, STUDENT_CONTACT_TYPE, STUDENT_RELATIONSHIP, CONCAT(ADDRESS,' ',ADDRESS_1,'<br />',CITY,', ',STATE_NAME,' - ',ZIP) AS ADDRESS, WORK_PHONE, CELL_PHONE, EMAIL, IF(S_STUDENT_CONTACT.ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE FROM S_STUDENT_CONTACT LEFT JOIN M_STUDENT_CONTACT_TYPE_MASTER ON M_STUDENT_CONTACT_TYPE_MASTER.PK_STUDENT_CONTACT_TYPE_MASTER = S_STUDENT_CONTACT.PK_STUDENT_CONTACT_TYPE_MASTER LEFT JOIN M_STUDENT_RELATIONSHIP_MASTER ON M_STUDENT_RELATIONSHIP_MASTER.PK_STUDENT_RELATIONSHIP_MASTER = S_STUDENT_CONTACT.PK_STUDENT_RELATIONSHIP_MASTER LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES WHERE PK_STUDENT_MASTER = '$_GET[id]' AND S_STUDENT_CONTACT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY PK_STUDENT_CONTACT DESC");
													
													while (!$res_type->EOF) { ?>
														<tr>
															<td><?=$res_type->fields['STUDENT_CONTACT_TYPE']?></td>
															<td><?=$res_type->fields['ADDRESS']?></td>
															<td><?=$res_type->fields['CELL_PHONE']?></td>
															<td>
																<?=$res_type->fields['ACTIVE']?>
																<a href="student_contact.php?sid=<?=$_GET['id']?>&id=<?=$res_type->fields['PK_STUDENT_CONTACT']?>&t=<?=$_GET['t']?>" title="<?=EDIT?>" class="btn btn-secondary btn-circle"><i class="far fa-edit"></i> </a>
																<a href="javascript:void(0);" onclick="delete_row('<?=$res_type->fields['PK_STUDENT_CONTACT']?>','contact')" title="<?=DELETE?>" class="btn btn-primary btn-circle"><i class="far fa-trash-alt"></i> </a>
															</td>
														</tr>
													<?	$res_type->MoveNext();
													} ?>
												</tbody>
											</table>
										</div>
									
									</div>
                                </div>
								
								<div class="tab-pane <?=$other_tab?>" id="otherTab" role="tabpanel">
                                    <div class="p-20">
										<div class="row">
											<div class="col-sm-6 pt-25">
												<div class="d-flex">
													<div class="col-12 col-sm-6 form-group">
														<select id="PK_REPRESENTATIVE" name="PK_REPRESENTATIVE" class="form-control" >
															<option></option>
															<? $res_type = $db->Execute("select PK_EMPLOYEE_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME from S_EMPLOYEE_MASTER,M_DEPARTMENT WHERE S_EMPLOYEE_MASTER.ACTIVE = 1 AND S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DEPARTMENT_MASTER = 2 AND S_EMPLOYEE_MASTER.PK_DEPARTMENT = M_DEPARTMENT.PK_DEPARTMENT order by CONCAT(FIRST_NAME,' ',LAST_NAME) ASC");
															while (!$res_type->EOF) { ?>
																<option value="<?=$res_type->fields['PK_EMPLOYEE_MASTER']?>" <? if($PK_REPRESENTATIVE == $res_type->fields['PK_EMPLOYEE_MASTER']) echo "selected"; ?> ><?=$res_type->fields['NAME']?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														<label for="PK_REPRESENTATIVE"><?=REPRESENTATIVE?></label>
													</div>
													<div class="col-12 col-sm-6 form-group">
														<select id="PK_SECOND_REPRESENTATIVE" name="PK_SECOND_REPRESENTATIVE" class="form-control">
															<option></option>
														</select>
														<span class="bar"></span> 
														<label for="PK_SECOND_REPRESENTATIVE"><?=SECOND_REP?></label>
													</div>
												</div>
												<div class="d-flex">
													<div class="col-12 col-sm-6 form-group">
														<select id="PK_LEAD_SOURCE" name="PK_LEAD_SOURCE" class="form-control">
															<option></option>
															<? $res_type = $db->Execute("select PK_LEAD_SOURCE,LEAD_SOURCE from M_LEAD_SOURCE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by LEAD_SOURCE ASC");
															while (!$res_type->EOF) { ?>
																<option value="<?=$res_type->fields['PK_LEAD_SOURCE']?>" <? if($PK_LEAD_SOURCE == $res_type->fields['PK_LEAD_SOURCE']) echo "selected"; ?> ><?=$res_type->fields['LEAD_SOURCE']?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														<label for="PK_LEAD_SOURCE"><?=SOURCE_CODE?></label>
													</div>
													<div class="col-12 col-sm-6 form-group">
														<select id="PK_CONTACT_SOURCE" name="PK_CONTACT_SOURCE" class="form-control">
															<option></option>
														</select>
														<span class="bar"></span> 
														<label for="PK_CONTACT_SOURCE"><?=CONTACT_SOURCE?></label>
													</div>
												</div>
												
												<div class="d-flex">
													<div class="col-12 col-sm-6 form-group">
														<select id="PK_CUSTOM1" name="PK_CUSTOM1" class="form-control">
															<option></option>
														</select>
														<span class="bar"></span> 
														<label for="PK_CUSTOM1"><?=CUSTOM1?></label>
													</div>
												</div>
												
												<hr style="margin-top: 0;border: 2px solid #e0e0e0;" />
												
												<div class="d-flex">
													<div class="col-12 col-sm-6 form-group">
														<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS" class="form-control">
															<option></option>
															<? $sts_cond = "";
															if($_SESSION['PK_ROLES'] != 2)
																$sts_cond = " AND PK_DEPARTMENT = '$_SESSION[PK_DEPARTMENT]' ";
															$res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $sts_cond order by STUDENT_STATUS ASC");
															while (!$res_type->EOF) { ?>
																<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" <? if($PK_STUDENT_STATUS == $res_type->fields['PK_STUDENT_STATUS']) echo "selected"; ?> ><?=$res_type->fields['STUDENT_STATUS']?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														<label for="PK_STUDENT_STATUS"><?=STATUS_CODE?></label>
													</div>
												</div>
												<div class="d-flex">
													<div class="col-12 col-sm-6 form-group">
														<input class="form-control date" type="text" id="STATUS_DATE" name="STATUS_DATE" value="<?=$STATUS_DATE?>" >
														<span class="bar"></span> 
														<label for="STATUS_DATE"><?=STATUS_DATE?></label>
													</div>
													<div class="col-12 col-sm-6 form-group">
														<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM" class="form-control">
															<option></option>
															<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE from M_CAMPUS_PROGRAM WHERE PK_CAMPUS = '$_SESSION[PK_CAMPUS]' order by CODE ASC");
															while (!$res_type->EOF) { ?>
																<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" <? if($PK_CAMPUS_PROGRAM == $res_type->fields['PK_CAMPUS_PROGRAM']) echo "selected"; ?> ><?=$res_type->fields['CODE']?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														<label for="PK_CAMPUS_PROGRAM"><?=PROGRAM?></label>
													</div>
												</div>
												<div class="d-flex">
													<div class="col-12 col-sm-6 form-group">
														<input class="form-control date" type="text" id="FIRST_TERM_DATE" name="FIRST_TERM_DATE" value="<?=$FIRST_TERM_DATE?>" >
														<span class="bar"></span> 
														<label for="FIRST_TERM_DATE"><?=FIRST_TERM_DATE?></label>
													</div>
													<div class="col-12 col-sm-6 form-group">
														<select id="PK_FUNDING" name="PK_FUNDING" class="form-control">
															<option></option>
															<? $res_type = $db->Execute("select * from M_FUNDING WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by FUNDING ASC");
															while (!$res_type->EOF) { ?>
																<option value="<?=$res_type->fields['PK_FUNDING']?>" <? if($PK_FUNDING == $res_type->fields['PK_FUNDING']) echo "selected"; ?> ><?=$res_type->fields['FUNDING']?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														<label for="PK_FUNDING"><?=FUNDING?></label>
													</div>
												</div>
											</div>
											<div class="col-sm-6 pt-25 theme-v-border">
												
												<div class="d-flex">
													<div class="col-12 form-group">
														<textarea class="form-control" rows="2" id="NOTES" name="NOTES"><?=$NOTES?></textarea>
														<span class="bar"></span>
														<label for="NOTES"><?=NOTES?></label>
													</div>
												</div>
												
												<hr style="margin-top: 0;border: 2px solid #e0e0e0;" />
												
												<div class="d-flex">
													<div class="col-12 col-sm-6 form-group">
														<input class="form-control date" type="text" value="<?=$ENTRY_DATE?>" id="ENTRY_DATE" readonly >
														<span class="bar"></span> 
														<label for="ENTRY_DATE"><?=ENTRY_DATE?></label>
													</div>
													<div class="col-12 col-sm-6 form-group">
														<input id="ENTRY_TIME" readonly type="text" class="form-control timepicker" value="<?=$ENTRY_TIME?>">
														<span class="bar"></span> 
														 <label for="ENTRY_TIME"><?=ENTRY_TIME?></label>
													</div>
												</div>
												<div class="d-flex">
													<div class="col-12 col-sm-6 form-group">
														<input id="LEAD_ID" name="LEAD_ID" type="text" class="form-control" value="<?=$LEAD_ID?>">
														<span class="bar"></span> 
														 <label for="LEAD_ID"><?=LEAD_ID?></label>
													</div>
													<div class="col-12 col-sm-6 form-group">
														<input id="ADM_USER_ID" name="ADM_USER_ID" type="text" class="form-control" value="<?=$ADM_USER_ID?>">
														<span class="bar"></span> 
														 <label for="ADM_USER_ID"><?=ADM_USER_ID?></label>
													</div>
												</div>
												
												<div class="d-flex">
													<div class="col-12 col-sm-12 ">
														<button onclick="validate_form(1)" type="button" class="btn waves-effect waves-light btn-info"><?=SAVE_CONTINUE?></button>
														<button onclick="validate_form(0)" type="button" class="btn waves-effect waves-light btn-info"><?=SAVE_EXIT?></button>
														<button type="button" onclick="window.location.href='manage_student.php?t=<?=$_GET['t']?>'" class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
													</div>
												</div>
											</div>
										</div>
										
                                    </div>
                                </div>
                                <div class="tab-pane <?=$academic_tab?>" id="academicTab" role="tabpanel">
                                	<div class="p-20">
										<div class="row">
											<div class="col-sm-3 pt-25">
												<div class="d-flex">
													<div class="col-12 col-sm-12 form-group">
														<input id="HS_CLASS_RANK" name="HS_CLASS_RANK" type="text" class="form-control" value="<?=$HS_CLASS_RANK?>">
														<span class="bar"></span> 
														<label for="HS_CLASS_RANK"><?=HS_CLASS_RANK?></label>
													</div>
												</div>
												
												<div class="d-flex">
													<div class="col-12 col-sm-12 form-group">
														<input id="HS_CGPA" name="HS_CGPA" type="text" class="form-control" value="<?=$HS_CGPA?>">
														<span class="bar"></span> 
														<label for="HS_CGPA"><?=HS_CGPA?></label>
													</div>
												</div>
												
												<div class="d-flex">
													<div class="col-12 col-sm-12 form-group">
														<input id="POST_SEC_CUM_CGPA" name="POST_SEC_CUM_CGPA" type="text" class="form-control" value="<?=$POST_SEC_CUM_CGPA?>">
														<span class="bar"></span> 
														<label for="POST_SEC_CUM_CGPA"><?=POST_SEC_CUM_CGPA?></label>
													</div>
												</div>
												
											</div>
											<div class="col-sm-9 pt-25 theme-v-border">
												<div class="d-flex ">
													<div class="col-12 col-sm-4 form-group">
														<select id="PK_PREVIOUS_COLLEGE" name="PK_PREVIOUS_COLLEGE" class="form-control">
															<option selected></option>
														</select>
														<span class="bar"></span> 
														 <label for="PK_PREVIOUS_COLLEGE"><?=PREVIOUS_COLLEGE?></label>
													</div>
													<div class="col-12 col-sm-4 form-group">
														<select id="PK_HIGHEST_LEVEL_OF_ED" name="PK_HIGHEST_LEVEL_OF_ED" class="form-control">
															<option selected></option>
														</select>
														<span class="bar"></span> 
														<label for="PK_HIGHEST_LEVEL_OF_ED"><?=HIGHEST_LEVEL_OF_ED?></label>
													</div>
													<div class="col-12 col-sm-4 form-group">
														<input class="form-control date" type="text" value="<?=$EXPECTED_GRAD_DATE?>" name="EXPECTED_GRAD_DATE" id="EXPECTED_GRAD_DATE">
														<span class="bar"></span> 
														<label for="EXPECTED_GRAD_DATE"><?=EXPECTED_GRAD_DATE?></label>
													</div>
												</div>
												
												<hr style="margin-top: 0;border: 2px solid #e0e0e0;" />
												
												<div class="d-flex ">
													<div class="col-12 col-sm-4 form-group">
														<select id="PK_SESSION" name="PK_SESSION" class="form-control">
															<option selected></option>
														</select>
														<span class="bar"></span> 
														 <label for="PK_SESSION"><?=SESSION?></label>
													</div>
													<div class="col-12 col-sm-4 form-group">
														<select id="FULL_PART_TIME" name="FULL_PART_TIME" class="form-control">
															<option value=""></option>
															<option value="1" <? if($FULL_PART_TIME == 1) echo "selected"; ?> >Full Time</option>
															<option value="2" <? if($FULL_PART_TIME == 2) echo "selected"; ?> >Part Time</option>
														</select>
														<span class="bar"></span> 
														 <label for="FULL_PART_TIME"><?=FULL_PART_TIME?></label>
													</div>
													<div class="col-12 col-sm-4 form-group">
														<select id="PK_STUDENT_GROUP" name="PK_STUDENT_GROUP" class="form-control">
															<option selected></option>
														</select>
														<span class="bar"></span> 
														 <label for="PK_STUDENT_GROUP"><?=STUDENT_GROUP?></label>
													</div>	
												</div>
												
												<hr style="margin-top: 0;border: 2px solid #e0e0e0;" />
												
												<div class="d-flex ">
													<div class="col-12 col-sm-4 form-group">
														<select id="PK_FERPA_BLOCK" name="PK_FERPA_BLOCK" class="form-control">
															<option selected></option>
														</select>
														<span class="bar"></span> 
														 <label for="PK_FERPA_BLOCK"><?=FERPA_BLOCK?></label>
													</div>
													
													<div class="col-12 col-sm-4 form-group">
														<input id="STUDENT_ID" name="STUDENT_ID" type="text" class="form-control" value="<?=$STUDENT_ID?>">
														<span class="bar"></span> 
														 <label for="STUDENT_ID"><?=STUDENT_ID?></label>
													</div>
												</div>
												
												<div class="col-12 col-sm-6 focused">
													<span class="bar"></span> 
													<label for="CAMPUS"><?=CAMPUS?></label>
												</div>
												<div class="col-12 col-sm-4 form-group row">
													<? $res_type = $db->Execute("select OFFICIAL_CAMPUS_NAME,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by OFFICIAL_CAMPUS_NAME ASC");
													while (!$res_type->EOF) { ?>
														<div class="form-group col-12 col-sm-12">
															<div class="custom-control custom-checkbox mr-sm-2">
																<? $checked = '';
																$PK_CAMPUS = $res_type->fields['PK_CAMPUS'];
																$res = $db->Execute("select PK_STUDENT_CAMPUS FROM S_STUDENT_CAMPUS WHERE PK_CAMPUS = '$PK_CAMPUS' AND PK_STUDENT_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
																if($res->RecordCount() > 0)
																	$checked = 'checked';
																?>
																<input type="checkbox" class="custom-control-input" id="PK_CAMPUS_<?=$PK_CAMPUS?>" name="PK_CAMPUS[]" value="<?=$PK_CAMPUS?>" <?=$checked?> >
																<label class="custom-control-label" for="PK_CAMPUS_<?=$PK_CAMPUS?>" ><?=$res_type->fields['OFFICIAL_CAMPUS_NAME']?></label>
															</div>
														</div>
													<?	$res_type->MoveNext();
													} ?>
												</div>
											
												
												<hr style="margin-top: 0;border: 2px solid #e0e0e0;" />
												
												<div class="d-flex">
													<div class="col-12 col-sm-4 form-group">
														<input class="form-control date" type="text" value="<?=$CONTRACT_SIGNED_DATE?>" id="CONTRACT_SIGNED_DATE" name="CONTRACT_SIGNED_DATE">
														<span class="bar"></span> 
														<label for="CONTRACT_SIGNED_DATE"><?=CONTRACT_SIGNED_DATE?></label>
													</div>
													<div class="col-12 col-sm-4 form-group">
														<input class="form-control date" type="text" value="<?=$CONTRACT_END_DATE?>" id="CONTRACT_END_DATE" name="CONTRACT_END_DATE">
														<span class="bar"></span> 
														<label for="CONTRACT_END_DATE"><?=CONTRACT_END_DATE?></label>
													</div>
												</div>
												
												<div class="row form-group">
													<div class="col-12 col-sm-9 ">
														<button onclick="validate_form(1)" type="button" class="btn waves-effect waves-light btn-info"><?=SAVE_CONTINUE?></button>
														<button onclick="validate_form(0)" type="button" class="btn waves-effect waves-light btn-info"><?=SAVE_EXIT?></button>
														<button type="button" onclick="window.location.href='manage_student.php?t=<?=$_GET['t']?>'" class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
													</div>
												</div>
											</div>
										</div>
										
                                    </div>
                                </div>
								
								<div class="tab-pane <?=$task_tab?>" id="taskTab" role="tabpanel">
									<div class="row">
										<div class="col-md-7 align-self-center">
										</div>  
										<div class="col-md-3 align-self-center ">
											<input id="TASK_SEARCH" name="TASK_SEARCH" type="text" class="form-control" placeholder="Search" onkeypress="search_task(event)" >
										</div>
										<div class="col-md-2 align-self-center ">
											<div class="d-flex ">
												<a href="student_task.php?sid=<?=$_GET['id']?>&t=<?=$_GET['t']?>" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> <?=CREATE_NEW?></a>&nbsp;&nbsp;
											</div>
										</div>
									</div>
									
									<div class="table-responsive p-20" id="task_div" >
										<? $_REQUEST['sid'] = $_GET['id']; 
										include('ajax_student_task.php'); ?>
									</div>
								</div>
								
								<div class="tab-pane <?=$note_tab?>" id="noteTab" role="tabpanel">
									<div class="row">
										<div class="col-md-7 align-self-center">
										</div>  
										<div class="col-md-3 align-self-center ">
											<input id="NOTES_SEARCH" name="NOTES_SEARCH" type="text" class="form-control" placeholder="Search" onkeypress="search_notes(event)" >
										</div>
										<div class="col-md-2 align-self-center text-right">
											<div class="d-flex justify-content-end align-items-center">
												<a href="student_notes.php?sid=<?=$_GET['id']?>&t=<?=$_GET['t']?>" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> <?=CREATE_NEW?></a>&nbsp;&nbsp;
											</div>
										</div>
									</div>
									<div class="table-responsive p-20" id="notes_div" >
										<? $_REQUEST['sid'] = $_GET['id']; 
										include('ajax_student_notes.php'); ?>
									</div>
								</div>
								
								<div class="tab-pane <?=$documents_tab?>" id="documentsTab" role="tabpanel">
									<div class="row">
										<div class="col-md-12 align-self-center text-right">
											<div class="d-flex justify-content-end align-items-center">
												<a href="javascript:void(0)" onclick="request_documents()" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> <?=REQUEST_DOCUMENTS?></a>&nbsp;&nbsp;
											
												<a href="student_document.php?sid=<?=$_GET['id']?>&t=<?=$_GET['t']?>" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> <?=CREATE_NEW?></a>&nbsp;&nbsp;
											</div>
										</div>
									</div>
									<div class="table-responsive p-20">
										<table class="table table-hover">
											<thead>
												<tr>
													<th><?=REQUESTED?></th>
													<th><?=DOCUMENT?></th>
													<th><?=RECEIVED?></th>
													<th><?=OPTIONS?></th>
												</tr>
											</thead>
											<tbody>
												<? $res_type = $db->Execute("select PK_STUDENT_DOCUMENTS,DOCUMENT_TYPE, IF(REQUESTED_DATE = '0000-00-00', '',  DATE_FORMAT(REQUESTED_DATE,'%m/%d/%Y')) AS REQUESTED_DATE, IF(DOCUMENT_PATH != '','Yes', 'No') as RECEIVED,  NOTES,  IF(DATE_RECEIVED = '0000-00-00', '',  DATE_FORMAT(DATE_RECEIVED,'%m/%d/%Y')) AS DATE_RECEIVED, IF(FOLLOWUP_DATE = '0000-00-00', '',  DATE_FORMAT(FOLLOWUP_DATE,'%m/%d/%Y')) AS FOLLOWUP_DATE FROM S_STUDENT_DOCUMENTS WHERE PK_STUDENT_MASTER = '$_GET[id]' AND S_STUDENT_DOCUMENTS.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
												
												while (!$res_type->EOF) { ?>
													<tr>
														<td><?=$res_type->fields['REQUESTED_DATE']?></td>
														<td><?=$res_type->fields['DOCUMENT_TYPE']?></td>
														<td><?=$res_type->fields['RECEIVED']?></td>
														<td>
															<a href="student_document.php?sid=<?=$_GET['id']?>&id=<?=$res_type->fields['PK_STUDENT_DOCUMENTS']?>&t=<?=$_GET['t']?>" title="<?=EDIT?>" class="btn btn-secondary btn-circle"><i class="far fa-edit"></i> </a>
															<a href="javascript:void(0);" onclick="delete_row('<?=$res_type->fields['PK_STUDENT_DOCUMENTS']?>','document')" title="<?=DELETE?>" class="btn btn-primary btn-circle"><i class="far fa-trash-alt"></i> </a>
														</td>
													</tr>
												<?	$res_type->MoveNext();
												} ?>
											</tbody>
										</table>
									</div>
								</div>
								
								<div class="tab-pane <?=$questionnaire_tab?>" id="questionnaireTab" role="tabpanel">
									<? $i = 1;
									
									$res_type = $db->Execute("select PK_QUESTIONNAIRE,QUESTION from M_QUESTIONNAIRE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 ORDER BY DISPLAY_ORDER ");
									while (!$res_type->EOF) { ?>
										<div class="d-flex">
											<div class="col-12 form-group">
												<? $res = $db->Execute("SELECT ANSWER FROM S_STUDENT_QUESTIONNAIRE WHERE PK_STUDENT_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_QUESTIONNAIRE = '".$res_type->fields['PK_QUESTIONNAIRE']."' ");  ?>
												<textarea class="form-control" rows="1" id="ANSWER_<?=$res_type->fields['PK_QUESTIONNAIRE']?>" name="ANSWER[]"><?=$res->fields['ANSWER']?></textarea>
												<input type="hidden" name="PK_QUESTIONNAIRE[]" value="<?=$res_type->fields['PK_QUESTIONNAIRE']?>" />
												<span class="bar"></span>
												<label for="QUESTION_<?=$res_type->fields['PK_QUESTIONNAIRE']?>"><?=$res_type->fields['QUESTION']?></label>
											</div>
										</div>
									<?	$i++;
										$res_type->MoveNext();
									} ?>
									
									<div class="row form-group">
										<div class="col-12 col-sm-9 ">
											<button onclick="validate_form(1)" type="button" class="btn waves-effect waves-light btn-info"><?=SAVE_CONTINUE?></button>
											<button onclick="validate_form(0)" type="button" class="btn waves-effect waves-light btn-info"><?=SAVE_EXIT?></button>
											<button type="button" onclick="window.location.href='manage_student.php?t=<?=$_GET['t']?>'" class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
										</div>
									</div>
								</div>
								
								<div class="tab-pane <?=$requirements_tab?>" id="requirementsTab" role="tabpanel">
								</div>
								
								<? } ?>
								<input type="hidden" name="SAVE_CONTINUE" id="SAVE_CONTINUE" value="0" />
								<input type="hidden" id="current_tab" name="current_tab" value="0" >
								<input type="hidden" name="FORM_NAME" value="form1" >
                            </div>
                            </form>
                        </div>
					</div>
				</div>
				
            </div>
        </div>
        <? require_once("footer.php"); ?>
		
		<div class="modal" id="SSNModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<form class="floating-labels m-t-40" method="post" name="form2" id="form2" >
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-body">
							<input type="hidden" name="FORM_NAME" value="ssn" >
							<div class="d-flex row">
								<div class="col-12 col-sm-12 form-group">
									<input id="SSN_1" name="SSN_1" type="text" class="form-control validate-ssn required-entry" value="">
									<span class="bar"></span> 
									<label for="SSN">
										<?=SSN?>
									</label>
								</div>
								
								<div class="col-12 col-sm-12 form-group">
									<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
									<button type="button" class="btn waves-effect waves-light btn-dark" onclick="close_pop('SSNModal')" ><?=CANCEL?></button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
		
		<div class="modal" id="documentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<form class="floating-labels m-t-40" method="post" name="form3" id="form3" >
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title" id="exampleModalLabel1"><?=REQUEST_DOCUMENTS?></h4>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						</div>
						<div class="modal-body">
							<input type="hidden" name="FORM_NAME" value="document" >
							<div class="col-12 col-sm-12 form-group row"  >
								<? $res_type = $db->Execute("select DOCUMENT_TYPE,PK_DOCUMENT_TYPE from M_DOCUMENT_TYPE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DOCUMENT_TYPE NOT IN (SELECT PK_DOCUMENT_TYPE FROM S_STUDENT_DOCUMENTS WHERE PK_STUDENT_MASTER = '$_GET[id]' ) order by DOCUMENT_TYPE ASC");
								$i = 0;
								while (!$res_type->EOF) { 
									$i++; ?>
									<div class="form-group col-12 col-sm-12" style="margin-bottom: 5px;" >
										<div class="custom-control custom-checkbox mr-sm-12">
											<? $PK_DOCUMENT_TYPE = $res_type->fields['PK_DOCUMENT_TYPE']; 
											$cls = "";
											$id	 = "PK_DOCUMENT_TYPE_".$PK_DOCUMENT_TYPE;
											if($i == $res_type->RecordCount()) {
												$id	 = "PK_DOCUMENT_TYPE";
												$cls = "validate-one-required-by-name";
											} ?>
											<input type="checkbox" class="custom-control-input <?=$cls?> " id="<?=$id?>" name="PK_DOCUMENT_TYPE[]" value="<?=$PK_DOCUMENT_TYPE?>" >
											<label class="custom-control-label" for="<?=$id?>" ><?=$res_type->fields['DOCUMENT_TYPE']?></label>
										</div>
									</div>
								<?	$res_type->MoveNext();
								} ?>
							</div>
												
							<div class="d-flex row">
								<div class="col-12 col-sm-12 form-group">
									<button type="submit" class="btn waves-effect waves-light btn-info"><?=REQUEST?></button>
									<button type="button" class="btn waves-effect waves-light btn-dark" onclick="close_pop('documentModal')" ><?=CANCEL?></button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
		
		<div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?=DELETE_CONFIRMATION?></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group" id="delete_message" ></div>
						<input type="hidden" id="DELETE_ID" value="0" />
						<input type="hidden" id="DELETE_TYPE" value="0" />
					</div>
					<div class="modal-footer">
						<button type="button" onclick="conf_delete(1)" class="btn waves-effect waves-light btn-info"><?=YES?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete(0)" ><?=NO?></button>
					</div>
				</div>
			</div>
		</div>
		
    </div>
   
	<? require_once("js.php"); ?>
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	
	<script type="text/javascript">
	<? if($_GET['tab'] != '') { ?>
		var current_tab = '<?=$_GET['tab']?>';
	<? } else { ?>
		var current_tab = 'infoTab';
	<? } ?>
	jQuery(document).ready(function($) { 
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
		
		$('.timepicker').inputmask(
			"hh:mm t", {
				placeholder: "HH:MM AM/PM", 
				insertMode: false, 
				showMaskOnHover: false,
				hourFormat: 12
			}
		);
		
		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
			current_tab = $(e.target).attr("href") // activated tab
			//alert(current_tab)
		});
	});
	</script>
	
	<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.4.1.min.js"></script>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			<? if($_GET['id'] != ''){ ?>
				//get_country('<?=$PK_STATES?>','PK_COUNTRY')
			<? } ?>
		});
		
		var form2 = new Validation('form2');
		var form3 = new Validation('form3');
		
		function validate_form(val){
			document.getElementById('current_tab').value   = current_tab;
			document.getElementById("SAVE_CONTINUE").value = val;
			
			var valid = new Validation('form1', {onSubmit:false});
			var result = valid.validate();
			if(result == true)
				document.form1.submit();
		}
		
		function get_country(val,id){
			jQuery(document).ready(function($) { 
				var data  = 'state='+val+'&id='+id;
				var value = $.ajax({
					url: "../super_admin/ajax_get_country_from_state.php",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						document.getElementById(id).innerHTML = data;
						document.getElementById('PK_COUNTRY_LABEL').classList.add("focused");
						
					}		
				}).responseText;
			});
		}
		
		function generate_ethnicity(){
			var ethnicity = '';
			if(document.getElementById('RACE_1').checked == true)
				ethnicity = 'Hispanic/Latino';
			else {
				var RACE = document.getElementsByName('RACE[]')
				for(var i = 0 ; i < RACE.length ; i++){
					if(RACE[i].checked == true)
						if(ethnicity == '') {
							//alert(('LBL_RACE_'+RACE[i].value))
							ethnicity = document.getElementById('LBL_RACE_'+RACE[i].value).innerHTML;
						} else
							ethnicity = 'Two or more races';
				}
			}
			document.getElementById('IPEDS_ETHNICITY').value = ethnicity;
			document.getElementById('IPEDS_ETHNICITY_LABEL').classList.add("focused");
			
		}
		
		function change_ssn(){
			jQuery(document).ready(function($) {
				$("#SSNModal").modal()
				$("#SSN_1").val('')
			});
		}
		function request_documents(){
			jQuery(document).ready(function($) {
				$("#documentModal").modal()
			});
		}
		function close_pop(id){
			jQuery(document).ready(function($) {
				$("#"+id).modal("hide");
			});
		}
		
		function delete_row(id,type){
			jQuery(document).ready(function($) {
				if(type == 'document')
					document.getElementById('delete_message').innerHTML = '<?=DELETE_MESSAGE.DOCUMENT?>?';
				else if(type == 'task')
					document.getElementById('delete_message').innerHTML = '<?=DELETE_MESSAGE.TASK?>?';
				else if(type == 'img')
					document.getElementById('delete_message').innerHTML = '<?=DELETE_MESSAGE.IMAGE?>?';
				else if(type == 'contact')
					document.getElementById('delete_message').innerHTML = '<?=DELETE_MESSAGE.CONTACT?>?';
				else if(type == 'notes')
					document.getElementById('delete_message').innerHTML = '<?=DELETE_MESSAGE.NOTES?>?';	
					
					
				$("#deleteModal").modal()
				$("#DELETE_ID").val(id)
				$("#DELETE_TYPE").val(type)
			});
		}
		function conf_delete(val,id){
			jQuery(document).ready(function($) {
				if(val == 1) {
					if($("#DELETE_TYPE").val() == 'document')
						window.location.href = 'student.php?act=document_del&t=<?=$_GET['t']?>&id=<?=$_GET['id']?>&iid='+$("#DELETE_ID").val();
					else if($("#DELETE_TYPE").val() == 'task')
						window.location.href = 'student.php?act=task_del&t=<?=$_GET['t']?>&id=<?=$_GET['id']?>&iid='+$("#DELETE_ID").val();
					else if($("#DELETE_TYPE").val() == 'img')
						window.location.href = 'student.php?act=img_del&t=<?=$_GET['t']?>&id=<?=$_GET['id']?>&iid='+$("#DELETE_ID").val();
					else if($("#DELETE_TYPE").val() == 'contact')
						window.location.href = 'student.php?act=contact_del&t=<?=$_GET['t']?>&id=<?=$_GET['id']?>&iid='+$("#DELETE_ID").val();	
					else if($("#DELETE_TYPE").val() == 'notes')
						window.location.href = 'student.php?act=notes_del&t=<?=$_GET['t']?>&id=<?=$_GET['id']?>&iid='+$("#DELETE_ID").val();
						
				} else
					$("#deleteModal").modal("hide");
			});
		}
		
		function search_task(e){
			if (e.keyCode == 13) {
				jQuery(document).ready(function($) { 
					var data  = 'search='+$("#TASK_SEARCH").val()+'&sid=<?=$_GET['id']?>&t=<?=$_GET['t']?>';
					var value = $.ajax({
						url: "ajax_student_task.php",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							//alert(data)
							document.getElementById('task_div').innerHTML = data;
						}		
					}).responseText;
				});
			}
		}
		
		function search_notes(e){
			if (e.keyCode == 13) {
				jQuery(document).ready(function($) { 
					var data  = 'search='+$("#NOTES_SEARCH").val()+'&sid=<?=$_GET['id']?>&t=<?=$_GET['t']?>';
					var value = $.ajax({
						url: "ajax_student_notes.php",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							//alert(data)
							document.getElementById('notes_div').innerHTML = data;
						}		
					}).responseText;
				});
			}
		}
	</script>

	<script src="../backend_assets/node_modules/Magnific-Popup-master/dist/jquery.magnific-popup.js"></script>
    <script src="../backend_assets/node_modules/Magnific-Popup-master/dist/jquery.magnific-popup-init.js"></script>
</body>

</html>