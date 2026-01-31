<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
require_once("../language/tuition_batch.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_ACCOUNTING') == 0 ){
	header("location:../index");
	exit;
}

$PK_TUITION_BATCH_MASTER = $_REQUEST['PK_TUITION_BATCH_MASTER'];
$student_count			 = $_REQUEST['student_count'];
$PK_CAMPUS			 	 = $_REQUEST['campus_id'];

if($PK_TUITION_BATCH_MASTER == ''){
	$PK_TUITION_BATCH_DETAIL	= array();
	$PK_STUDENT_MASTER 			= array();
	$PK_STUDENT_ENROLLMENT 		= array();
	$STUDENT_NAME 				= array();
	$PK_AR_LEDGER_CODE			= array();
	$TRANSACTION_DATE 			= array();
	$AMOUNT						= array();
	$PK_STUDENT_FEE_BUDGET_ARR	= array();
	$PK_CAMPUS_PROGRAM_ARR_1  = array(); //DIAM-786
	
	$TYPE 				= $_REQUEST['TYPE'];
	$PK_TERM_MASTER 	= $_REQUEST['PK_TERM_MASTER'];
	$AY 				= $_REQUEST['AY'];
	$AP 				= $_REQUEST['AP'];
	$PK_FEE_TYPE 		= $_REQUEST['PK_FEE_TYPE'];
	$OPTION_1 			= $_REQUEST['OPTION_1'];
	$PK_COURSE_OFFERING	= $_REQUEST['PK_COURSE_OFFERING'];
	$START_DATE			= $_REQUEST['START_DATE'];
	$END_DATE			= $_REQUEST['END_DATE'];

	// DIAM-1446
	$COURSE_TERM_START_DATE	= $_REQUEST['COURSE_TERM_START_DATE']; 
	// End DIAM-1446
	
	/*if(!empty($_REQUEST['prog_id']))
		$PK_CAMPUS_PROGRAM = implode(",",$_REQUEST['prog_id']);
	else
		$PK_CAMPUS_PROGRAM = '';*/
		
	$PK_CAMPUS_PROGRAM = $_REQUEST['prog_id'];
	
	if($PK_CAMPUS_PROGRAM == -1)
		$PK_CAMPUS_PROGRAM = '';
		
	if($AY == -1)
		$AY = '';
		
	if($AP == -1)
		$AP = '';
	
	$campus_cond = "";
	if($PK_CAMPUS != '')
		$campus_cond = " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
		
	if($TYPE == 1) {
		$cond = "";
		if($AY != '')
			$cond .= " AND AY = '$AY' ";
		if($AP != '')
			$cond .= " AND AP = '$AP' ";
		if($PK_FEE_TYPE != '')
			$cond .= " AND PK_FEE_TYPE = '$PK_FEE_TYPE' ";
			
		$cond1 = "";	
		$table = "";
		if($OPTION_1 == 1){
			$table = " , S_STUDENT_COURSE, S_COURSE_OFFERING "; // , M_CAMPUS_PROGRAM_COURSE
			$cond1 = " AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
			AND S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
			-- AND M_CAMPUS_PROGRAM_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
			-- AND M_CAMPUS_PROGRAM_COURSE.PK_CAMPUS_PROGRAM IN ($PK_CAMPUS_PROGRAM) 
			AND S_STUDENT_COURSE.PK_TERM_MASTER = '$PK_TERM_MASTER' "; // DIAM-1357, remove condition as per Andrea Feedback
		}
		
		/* Ticket # 1424   */
		$res_type = $db->Execute("select M_CAMPUS_PROGRAM_FEE.PK_AR_LEDGER_CODE, AMOUNT, PK_CAMPUS_PROGRAM, DESCRIPTION, DAYS_FROM_START FROM M_CAMPUS_PROGRAM_FEE, M_AR_LEDGER_CODE WHERE M_CAMPUS_PROGRAM_FEE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS_PROGRAM IN ($PK_CAMPUS_PROGRAM) AND M_CAMPUS_PROGRAM_FEE.PK_AR_LEDGER_CODE = M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE AND TYPE = 2 $cond ORDER BY M_AR_LEDGER_CODE.CODE ASC "); //Ticket # 1620 
		while (!$res_type->EOF) {
			$DAYS_FROM_START_ARR[] 	   = $res_type->fields['DAYS_FROM_START'];
			$PK_CAMPUS_PROGRAM_ARR_1[] = $res_type->fields['PK_CAMPUS_PROGRAM'];
			$PK_AR_LEDGER_CODE_ARR_1[] = $res_type->fields['PK_AR_LEDGER_CODE'];
			$DESCRIPTION_1[]		   = $res_type->fields['DESCRIPTION'];
			$AMOUNT_ARR_1[] 		   = $res_type->fields['AMOUNT'];
			
			$res_type->MoveNext();
		}
		/* Ticket # 1424   */
		
		$res_stu = $db->Execute("select S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT,S_STUDENT_MASTER.PK_STUDENT_MASTER,CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, ENROLLMENT_PK_TERM_BLOCK, STUDENT_ID, BEGIN_DATE FROM S_STUDENT_MASTER, S_STUDENT_ACADEMICS, S_TERM_MASTER, S_STUDENT_ENROLLMENT LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT , M_STUDENT_STATUS $table WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ARCHIVED = 0 AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER  AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER  AND M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS AND POST_TUITION = 1 AND S_STUDENT_ENROLLMENT.PK_TERM_MASTER = '$PK_TERM_MASTER' AND S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM IN ($PK_CAMPUS_PROGRAM) $cond1 $campus_cond GROUP BY S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME)"); //AND IS_ACTIVE_ENROLLMENT = 1
	} else if($TYPE == 2) {
		/* Ticket # 1424   */
		
		//DIAM-786
		$res_type = $db->Execute("select S_COURSE_FEE.PK_AR_LEDGER_CODE, FEE_AMT, S_COURSE_FEE.DESCRIPTION, PK_COURSE_OFFERING FROM S_COURSE_OFFERING, S_COURSE_FEE, M_AR_LEDGER_CODE WHERE S_COURSE_FEE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_FEE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE AND S_COURSE_OFFERING.PK_COURSE_OFFERING IN ($_REQUEST[PK_COURSE_OFFERING]) AND S_COURSE_FEE.PK_AR_LEDGER_CODE = M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE AND TYPE = 2 ORDER BY M_AR_LEDGER_CODE.CODE ASC");
		//$res_type = $db->Execute("select S_COURSE_FEE.PK_AR_LEDGER_CODE, PK_CAMPUS_PROGRAM, FEE_AMT, S_COURSE_FEE.DESCRIPTION, PK_COURSE_OFFERING FROM M_CAMPUS_PROGRAM_FEE, S_COURSE_OFFERING, S_COURSE_FEE, M_AR_LEDGER_CODE WHERE S_COURSE_FEE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_FEE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE AND M_CAMPUS_PROGRAM_FEE.PK_CAMPUS_PROGRAM IN ($PK_CAMPUS_PROGRAM) AND S_COURSE_OFFERING.PK_COURSE_OFFERING IN ($_REQUEST[PK_COURSE_OFFERING]) AND S_COURSE_FEE.PK_AR_LEDGER_CODE = M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE AND TYPE = 2 ORDER BY M_AR_LEDGER_CODE.CODE ASC  "); //Ticket # 1620
		while (!$res_type->EOF) {
			$PK_COURSE_OFFERING_ARR_1[] = $res_type->fields['PK_COURSE_OFFERING'];
			//$PK_CAMPUS_PROGRAM_ARR_1[]  = $res_type->fields['PK_CAMPUS_PROGRAM'];
			$PK_AR_LEDGER_CODE_ARR_1[] 	= $res_type->fields['PK_AR_LEDGER_CODE'];
			$DESCRIPTION_1[]		   	= $res_type->fields['DESCRIPTION'];
			$AMOUNT_ARR_1[] 		   	= $res_type->fields['FEE_AMT'];
			
			$res_type->MoveNext();
		}

		if(!empty($PK_CAMPUS_PROGRAM))
			$PK_CAMPUS_PROGRAM_ARR_1 = explode(',',$PK_CAMPUS_PROGRAM);

		/* Ticket # 1424   */
		//AND IS_ACTIVE_ENROLLMENT = 1
		//DIAM-786

		if(!empty($PK_CAMPUS_PROGRAM)){
			$campus_cond .= " AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM IN ($PK_CAMPUS_PROGRAM)";
		}

		$res_stu = $db->Execute("select S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT,S_STUDENT_MASTER.PK_STUDENT_MASTER, STUDENT_ID, CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, ENROLLMENT_PK_TERM_BLOCK FROM S_STUDENT_MASTER, S_STUDENT_ACADEMICS, S_STUDENT_ENROLLMENT LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, M_STUDENT_STATUS, S_STUDENT_COURSE, M_COURSE_OFFERING_STUDENT_STATUS WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER  AND ARCHIVED = 0 AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER  AND M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS AND M_STUDENT_STATUS.POST_TUITION = 1 AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND S_STUDENT_COURSE.PK_COURSE_OFFERING IN ($_REQUEST[PK_COURSE_OFFERING]) AND S_STUDENT_COURSE.PK_TERM_MASTER = '$PK_TERM_MASTER'  AND M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS AND M_COURSE_OFFERING_STUDENT_STATUS.POST_TUITION = 1 $campus_cond GROUP BY S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME) ");
		
						
	} else if($TYPE == 7 || $TYPE == 9) {
		$cond = "";
		if($PK_CAMPUS_PROGRAM != '')
			$cond .= " AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM IN ($PK_CAMPUS_PROGRAM) ";
			
		if($PK_TERM_MASTER != '' && $OPTION_1 == 1)
			$cond .= " AND S_STUDENT_ENROLLMENT.PK_TERM_MASTER = '$PK_TERM_MASTER' ";
			
		if($AY != '')
			$cond .= " AND ACADEMIC_YEAR = '$AY' ";
		if($AP != '')
			$cond .= " AND ACADEMIC_PERIOD = '$AP' ";
			
		if($TYPE == 9) {
			$cond .= " AND PK_ESTIMATE_FEE_STATUS = 2 ";
			
			if($START_DATE != '')
				$START_DATE = date("Y-m-d",strtotime($START_DATE));
				
			if($END_DATE != '')
				$END_DATE = date("Y-m-d",strtotime($END_DATE));
			
			if($START_DATE != '' && $END_DATE != '')
				$cond .= " AND FEE_BUDGET_DATE BETWEEN '$START_DATE' AND '$END_DATE' ";
			else if($START_DATE != '')
				$cond .= " AND FEE_BUDGET_DATE >= '$START_DATE' ";
			else if($END_DATE != '')
				$cond .= " AND FEE_BUDGET_DATE <= '$END_DATE' ";
				
			if($PK_FEE_TYPE != '')
				$cond .= " AND PK_FEE_TYPE = '$PK_FEE_TYPE' ";
		}

		$res_stud = $res_stu = $db->Execute("select S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT,ENROLLMENT_PK_TERM_BLOCK, S_STUDENT_MASTER.PK_STUDENT_MASTER,CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, STUDENT_ID , S_STUDENT_FEE_BUDGET.PK_AR_LEDGER_CODE, FEE_AMOUNT, S_STUDENT_FEE_BUDGET.PK_STUDENT_FEE_BUDGET, IF(FEE_BUDGET_DATE != '0000-00-00', DATE_FORMAT(FEE_BUDGET_DATE, '%m/%d/%Y'),'') as FEE_BUDGET_DATE_1 FROM S_STUDENT_MASTER, S_STUDENT_ACADEMICS, S_STUDENT_ENROLLMENT LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, M_STUDENT_STATUS,S_STUDENT_FEE_BUDGET LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_FEE_BUDGET.PK_AR_LEDGER_CODE WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER  AND ARCHIVED = 0 AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS AND M_STUDENT_STATUS.POST_TUITION = 1 AND S_STUDENT_FEE_BUDGET.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT  $cond $campus_cond GROUP BY S_STUDENT_FEE_BUDGET.PK_STUDENT_FEE_BUDGET ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME) ASC, FEE_BUDGET_DATE ASC, M_AR_LEDGER_CODE.CODE ASC "); //AND IS_ACTIVE_ENROLLMENT = 1
		
	}

	$batch_cond = "";
	if($_REQUEST['pk_id'] != '')
		$batch_cond = " AND S_TUITION_BATCH_MASTER.PK_TUITION_BATCH_MASTER != '$_REQUEST[pk_id]' ";
	
	while (!$res_stu->EOF) {
		$PK_STUDENT_ENROLLMENT_1 = $res_stu->fields['PK_STUDENT_ENROLLMENT'];
		$PK_CAMPUS_PROGRAM=$res_stu->fields['PK_CAMPUS_PROGRAM'];
		
		if($TYPE == 1 || $TYPE == 2) { 

			//print_r($PK_AR_LEDGER_CODE_ARR_1);die;
			foreach($PK_AR_LEDGER_CODE_ARR_1 as $KEY => $PK_AR_LEDGER_CODE) {
				if($TYPE == 1) {
					$TUITION_BATCH_DETAIL_PK_CAMPUS_PROGRAM = $PK_CAMPUS_PROGRAM_ARR_1[$KEY];
					$res_batch_check = $db->Execute("select PK_STUDENT_ENROLLMENT FROM S_TUITION_BATCH_MASTER, S_TUITION_BATCH_DETAIL WHERE S_TUITION_BATCH_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = '1' AND PK_TERM_MASTER = '$PK_TERM_MASTER' AND TUITION_BATCH_DETAIL_PK_CAMPUS_PROGRAM = '$TUITION_BATCH_DETAIL_PK_CAMPUS_PROGRAM' $cond AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT_1' AND S_TUITION_BATCH_MASTER.PK_TUITION_BATCH_MASTER = S_TUITION_BATCH_DETAIL.PK_TUITION_BATCH_MASTER AND AY = '$AY' AND AP = '$AP' AND PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' $batch_cond "); 
					
				} else if($TYPE == 2) {
					$PK_COURSE_OFFERING_11 = $PK_COURSE_OFFERING_ARR_1[$KEY];
					$TUITION_BATCH_DETAIL_PK_CAMPUS_PROGRAM = $PK_CAMPUS_PROGRAM;//$PK_CAMPUS_PROGRAM_ARR_1[$KEY];
					//DIAM-786
					$Tbatch_con = '';
					if(!empty($TUITION_BATCH_DETAIL_PK_CAMPUS_PROGRAM)){
						$Tbatch_con = " AND TUITION_BATCH_DETAIL_PK_CAMPUS_PROGRAM = '$TUITION_BATCH_DETAIL_PK_CAMPUS_PROGRAM' ";
					}

					$res_batch_check = $db->Execute("select PK_STUDENT_ENROLLMENT FROM S_TUITION_BATCH_MASTER, S_TUITION_BATCH_DETAIL WHERE S_TUITION_BATCH_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = '2' AND PK_TERM_MASTER = '$PK_TERM_MASTER' $Tbatch_con AND TUITION_BATCH_DETAIL_PK_COURSE_OFFERING = '$PK_COURSE_OFFERING_11' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT_1' AND S_TUITION_BATCH_MASTER.PK_TUITION_BATCH_MASTER = S_TUITION_BATCH_DETAIL.PK_TUITION_BATCH_MASTER AND PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' $batch_cond ");
					
				}
				if($res_batch_check->RecordCount() == 0 ) {
					$flag = 0;
					if($TYPE == 1){
						$PK_CAMPUS_PROGRAM_11 = $PK_CAMPUS_PROGRAM_ARR_1[$KEY];
						$res_p = $db->Execute("select PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT_1' AND PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM_11'");
						
						if($res_p->RecordCount() == 0)
							$flag = 0;
						else
							$flag = 1;
					} else {
						
						//DIAM-786
						if($TYPE == 2){
							
							$PK_COURSE_OFFERING_11 = $PK_COURSE_OFFERING_ARR_1[$KEY];
							$PK_CAMPUS_PROGRAM_11  = $PK_CAMPUS_PROGRAM; //$PK_CAMPUS_PROGRAM_ARR_1[$KEY];
							$Pk_camp_con ="";
							if(!empty($PK_CAMPUS_PROGRAM_11)){
								$Pk_camp_con =" AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM_11' ";
							}
							
							$res_p = $db->Execute("select S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT, S_STUDENT_COURSE, S_COURSE_OFFERING WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT_1' $Pk_camp_con AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT AND S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND S_COURSE_OFFERING.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING_11' ");
						
							if($res_p->RecordCount() == 0)
								$flag = 0;
							else
								$flag = 1;
							//DIAM-786
						}else{

							$PK_COURSE_OFFERING_11 = $PK_COURSE_OFFERING_ARR_1[$KEY];
							$PK_CAMPUS_PROGRAM_11  = $PK_CAMPUS_PROGRAM_ARR_1[$KEY];
							$res_p = $db->Execute("select S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT, S_STUDENT_COURSE, S_COURSE_OFFERING WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT_1' AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM_11' AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT AND S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND S_COURSE_OFFERING.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING_11' ");
							
							if($res_p->RecordCount() == 0)
								$flag = 0;
							else
								$flag = 1;
						}
					}
					
					if($flag == 1) {
						$PK_STUDENT_MASTER[] 		= $res_stu->fields['PK_STUDENT_MASTER'];
						$PK_STUDENT_ENROLLMENT[] 	= $PK_STUDENT_ENROLLMENT_1;
						$STUDENT_NAME[] 			= $res_stu->fields['NAME'];
						$STUDENT_ID[] 				= $res_stu->fields['STUDENT_ID'];
						$PK_TUITION_BATCH_DETAIL[]	= '';
						
						//$TRANSACTION_DATE_ARR[] 	= $res_stu->fields['FEE_BUDGET_DATE']; //Ticket # 1312
						$PK_AR_LEDGER_CODE_ARR[]	= $PK_AR_LEDGER_CODE;
						$AMOUNT[]		 			= $AMOUNT_ARR_1[$KEY]; 
						$PK_TERM_BLOCK[]			= $res_stu->fields['ENROLLMENT_PK_TERM_BLOCK'];
						
						$TUITION_BATCH_DETAIL_AY[]		 = $_REQUEST['AY'];
						$TUITION_BATCH_DETAIL_AP[]		 = $_REQUEST['AP'];
						$TUITION_BATCH_PRIOR_YEAR[]		 = 2;
						
						$PK_COURSE_OFFERING_22_ARR[]	= $PK_COURSE_OFFERING_11; 
						$PK_CAMPUS_PROGRAM_22_ARR[]		= $PK_CAMPUS_PROGRAM_11;
						
						if($TYPE == 2) {
							
							$res_p = $db->Execute("select PK_COURSE_OFFERING, COURSE_CODE, IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') AS TERM_BEGIN_DATE, SESSION, SESSION_NO from S_COURSE, S_COURSE_OFFERING LEFT JOIN M_SESSION on M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER WHERE S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING_11'  ");
							$BATCH_DETAIL_DESCRIPTION_LBL[] = $res_p->fields['COURSE_CODE'].' ('.substr($res_p->fields['SESSION'],0,1).'-'.$res_p->fields['SESSION_NO'].') - '.$res_p->fields['TERM_BEGIN_DATE'];

							// DIAM-1446
							if($COURSE_TERM_START_DATE == '2')
							{
								$TRANSACTION_DATE_ARR[] = $res_p->fields['TERM_BEGIN_DATE']; 
							}
							else{
								$TRANSACTION_DATE_ARR[] = $_REQUEST['TRANS_DATE']; 
							}
							// End DIAM-1446

						} else {
							$DAYS_FROM_START 		= $DAYS_FROM_START_ARR[$KEY];
							//$TRANSACTION_DATE_ARR[] = date("m/d/Y",strtotime($res_stu->fields['BEGIN_DATE']." +".$DAYS_FROM_START." days"));
							$TRANSACTION_DATE_ARR[] = $_REQUEST['TRANS_DATE'];
						}
						
						/* Ticket # 1424   */
						//$res_ledger = $db->Execute("SELECT LEDGER_DESCRIPTION FROM M_AR_LEDGER_CODE WHERE PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
						//$BATCH_DETAIL_DESCRIPTION[]	= $res_ledger->fields['LEDGER_DESCRIPTION'];
						$BATCH_DETAIL_DESCRIPTION[]	= $DESCRIPTION_1[$KEY]; 
						/* Ticket # 1424   */
						
					}
				}
			}
		} else if($TYPE == 7 || $TYPE == 9) {
			$PK_AR_LEDGER_CODE 		= $res_stu->fields['PK_AR_LEDGER_CODE'];
			$PK_STUDENT_FEE_BUDGET 	= $res_stu->fields['PK_STUDENT_FEE_BUDGET'];
			
			//$res_batch_check = $db->Execute("select PK_STUDENT_ENROLLMENT FROM S_TUITION_BATCH_MASTER, S_TUITION_BATCH_DETAIL WHERE S_TUITION_BATCH_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = '7' AND PK_TERM_MASTER = '$PK_TERM_MASTER' AND PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT_1' AND S_TUITION_BATCH_MASTER.PK_TUITION_BATCH_MASTER = S_TUITION_BATCH_DETAIL.PK_TUITION_BATCH_MASTER AND AY = '$AY' AND AP = '$AP' $batch_cond ");
			
			//if($res_batch_check->RecordCount() == 0) {
				$PK_STUDENT_FEE_BUDGET_ARR[] 	= $PK_STUDENT_FEE_BUDGET;
				$PK_STUDENT_MASTER[] 			= $res_stu->fields['PK_STUDENT_MASTER'];
				$PK_STUDENT_ENROLLMENT[] 		= $PK_STUDENT_ENROLLMENT_1;
				$STUDENT_NAME[] 				= $res_stu->fields['NAME'];
				$STUDENT_ID[] 					= $res_stu->fields['STUDENT_ID'];
				$PK_TUITION_BATCH_DETAIL[]		= '';
				$TRANSACTION_DATE_ARR[] 		= $_REQUEST['TRANS_DATE'];
				//$TRANSACTION_DATE_ARR[] 		= $res_stu->fields['FEE_BUDGET_DATE_1'];
				$PK_AR_LEDGER_CODE_ARR[]		= $PK_AR_LEDGER_CODE;
				$AMOUNT[]		 				= $res_stu->fields['FEE_AMOUNT'];
				$PK_TERM_BLOCK[]				= $res_stu->fields['ENROLLMENT_PK_TERM_BLOCK'];
				
				$TUITION_BATCH_DETAIL_AY[]		 = $_REQUEST['AY'];
				$TUITION_BATCH_DETAIL_AP[]		 = $_REQUEST['AP'];
				$TUITION_BATCH_PRIOR_YEAR[]		 = 2;
				
				$res_ledger = $db->Execute("SELECT LEDGER_DESCRIPTION FROM M_AR_LEDGER_CODE WHERE PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' AND PK_ACCOUNT='$_SESSION[PK_ACCOUNT]' ");
				$BATCH_DETAIL_DESCRIPTION[]	= $res_ledger->fields['LEDGER_DESCRIPTION'];
			//}
		}

		$res_stu->MoveNext();
	}
} else {
	if($_REQUEST['TYPE'] == 2)
		$order_by = " CONCAT(LAST_NAME,', ',FIRST_NAME) ASC, COURSE_BATCH_DESC ASC, CODE ASC ";
	else
		$order_by = " CONCAT(LAST_NAME,', ',FIRST_NAME) ASC, CODE ASC ";
		
	$res = $db->Execute("select S_TUITION_BATCH_DETAIL.*, CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, STUDENT_ID, BATCH_DETAIL_DESCRIPTION, PK_STUDENT_FEE_BUDGET, CONCAT(COURSE_CODE, ' (', SUBSTRING(SESSION, 1, 1), '-', SESSION_NO, ') - ', IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') ) AS COURSE_BATCH_DESC  
	from 
	S_TUITION_BATCH_DETAIL 
	LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = TUITION_BATCH_DETAIL_PK_COURSE_OFFERING 
	LEFT JOIN M_SESSION on M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER 
	LEFT JOIN S_COURSE on S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
	LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_TUITION_BATCH_DETAIL.PK_AR_LEDGER_CODE 
	LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = S_TUITION_BATCH_DETAIL.PK_STUDENT_MASTER 
	LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER  
	WHERE PK_TUITION_BATCH_MASTER = '$PK_TUITION_BATCH_MASTER' AND S_TUITION_BATCH_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY  $order_by "); 
	
	while (!$res->EOF) { 
		$TRANSACTION_DATE 		= $res->fields['TRANSACTION_DATE'];
		if($TRANSACTION_DATE != '0000-00-00')
			$TRANSACTION_DATE = date("m/d/Y",strtotime($TRANSACTION_DATE));
		else
			$TRANSACTION_DATE = '';
			
		$PK_STUDENT_MASTER[] 			= $res->fields['PK_STUDENT_MASTER'];
		$PK_STUDENT_ENROLLMENT[] 		= $res->fields['PK_STUDENT_ENROLLMENT'];
		$STUDENT_NAME[] 				= $res->fields['NAME'];
		$STUDENT_ID[] 					= $res->fields['STUDENT_ID'];
		$PK_TUITION_BATCH_DETAIL[]		= $res->fields['PK_TUITION_BATCH_DETAIL'];
		$PK_AR_LEDGER_CODE_ARR[] 		= $res->fields['PK_AR_LEDGER_CODE'];
		$BATCH_DETAIL_DESCRIPTION[]		= $res->fields['BATCH_DETAIL_DESCRIPTION'];
		$PK_STUDENT_FEE_BUDGET_ARR[]	= $res->fields['PK_STUDENT_FEE_BUDGET'];
		$TRANSACTION_DATE_ARR[] 		= $TRANSACTION_DATE;
		$AMOUNT[]		 				= $res->fields['AMOUNT'];
		$PK_TERM_BLOCK[]		 		= $res->fields['PK_TERM_BLOCK'];
		
		$TUITION_BATCH_DETAIL_AY[]		 = $res->fields['TUITION_BATCH_DETAIL_AY'];
		$TUITION_BATCH_DETAIL_AP[]		 = $res->fields['TUITION_BATCH_DETAIL_AP'];
		$TUITION_BATCH_PRIOR_YEAR[]		 = $res->fields['TUITION_BATCH_DETAIL_PRIOR_YEAR'];
		$PK_COURSE_OFFERING_22_ARR[]	 = $res->fields['TUITION_BATCH_DETAIL_PK_COURSE_OFFERING'];
		$PK_CAMPUS_PROGRAM_22_ARR[]		 = $res->fields['TUITION_BATCH_DETAIL_PK_CAMPUS_PROGRAM'];
		
		if($_REQUEST['TYPE'] == 2)
			$BATCH_DETAIL_DESCRIPTION_LBL[] = $res->fields['COURSE_BATCH_DESC'];
		
		$res->MoveNext();
	}	
}

?>
<table class="table-striped table table-hover table-bordered" id="student_table2" >
	<thead style="position: sticky;top: 0;z-index: 9;">
		<!-- Ticket # 1340 -->
		<tr>
			<th class="sticky_header" scope="col" >
			<!-- Ticket # 714 -->
			
			<? if($_REQUEST['PK_BATCH_STATUS'] != 2){ ?>							
			<input type="checkbox" name="DELETE_SELECT_ALL" id="DELETE_SELECT_ALL" value="1" onclick="fun_select_all('All')" /> 
			<? }else{ ?>
				<?//=OPTIONS?>	
			<? } ?>
			
			<!-- Ticket # 714 -->
			</th>
			<th class="sticky_header" scope="col"  ><?=STUDENT?></th>
			<th class="sticky_header" scope="col"  ><div style="width:150px"><?=STUDENT_ID?></div></th>
			<th class="sticky_header" scope="col"  ><?=LEDGER_CODE?></th>
			<th class="sticky_header" scope="col"  ><?=LEDGER_CODE." ".DESCRIPTION?></th>
			<th class="sticky_header" scope="col"  ><?=TRANS_DATE?></th>
			<th class="sticky_header" scope="col"  ><?=DEBIT?></th>
			<th class="sticky_header" scope="col"  ><?=BATCH_DETAIL?></th>
			<th class="sticky_header" scope="col"  ><?=AY_1?></th>
			<th class="sticky_header" scope="col"  ><?=AP_1?></th>
			<th class="sticky_header" scope="col"  ><?=ENROLLMENT?></th>
			<th class="sticky_header" scope="col"  ><?=TERM_BLOCK?></th>
			<th class="sticky_header" scope="col"  ><?=PRIOR_YEAR?></th>
		</tr>
		<!-- Ticket # 1340 -->
	</thead>
	<tbody>
	
	
<?

$i = 0;
foreach($PK_TUITION_BATCH_DETAIL as $PK_TUITION_BATCH_DETAIL1) { 
	$student_count++; ?>
	<input type="hidden" class="pk_stud_enrol" name="PK_STUDENT_ENROLLMENT[]" id="PK_STUDENT_ENROLLMENT" value="<?=$PK_STUDENT_ENROLLMENT[$i]?>" />
	<tr id="tuition_batch_detail_div_<?=$student_count?>" >
		<td>
			<div style="width:50px">
				<? if($_REQUEST['PK_BATCH_STATUS'] != 2){ ?>
				<!-- Ticket # 714 -->
				<input type="checkbox" class="delete_if_not_selected" name="PK_STUDENT_DELETE[]" id="PK_STUDENT_DELETE" value="<?=$student_count?>" onclick="fun_select_all('Single')"/>	
				<!-- Ticket # 714 -->

				<!-- <a href="javascript:void(0);" onclick="delete_row('<?=$student_count?>')" title="<?=DELETE?>" id="delete_btn" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a> -->
				<? } ?>
			</div>
		</td>
		
		<td >
			<input type="hidden" name="BATCH_PK_TUITION_BATCH_DETAIL[]" id="PK_TUITION_BATCH_DETAIL" value="<?=$PK_TUITION_BATCH_DETAIL1?>" />
			<input type="hidden" name="BATCH_PK_STUDENT_MASTER[]" id="PK_STUDENT_MASTER" value="<?=$PK_STUDENT_MASTER[$i]?>" />
			<input type="hidden" name="BATCH_PK_STUDENT_FEE_BUDGET[]" id="PK_STUDENT_FEE_BUDGET" value="<?=$PK_STUDENT_FEE_BUDGET_ARR[$i]?>" />
			<input type="hidden" name="student_count[]"  value="<?=$student_count?>" />
			<div style="width:150px"><?=$STUDENT_NAME[$i]?></div>
		</td>
		
		<td >
			<div style="width:130px"><?=$STUDENT_ID[$i]?></div>
		</td>
		
		<td>
			<div style="">
				<?php
				$PK_AR_LEDGER_CODE_2 = $PK_AR_LEDGER_CODE_ARR[$i];
				$res_type = $db->Execute("select CODE from M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE_2' ");
				 echo $res_type->fields['CODE'];
					?>
			</div>
			<input type="hidden" id="BATCH_PK_AR_LEDGER_CODE_<?=$student_count?>" name="BATCH_PK_AR_LEDGER_CODE[]" value="<?=$PK_AR_LEDGER_CODE_2?>" />
		</td>
		<!-- Ticket # 1340 -->
		<td>
			<input type="text" class="form-control" placeholder="" name="BATCH_DETAIL_DESCRIPTION[]" id="BATCH_DETAIL_DESCRIPTION_<?=$student_count?>" value="<?=$BATCH_DETAIL_DESCRIPTION[$i]?>" style="width:200px" />
		</td>
		<td >
			<input type="text" class="form-control date required-entry" placeholder="" name="BATCH_TRANSACTION_DATE[]" id="BATCH_TRANSACTION_DATE_<?=$student_count?>" value="<?=$TRANSACTION_DATE_ARR[$i]?>" style="width:100px" />
		</td>
		<td align="right" >
			<input type="text" class="form-control" placeholder="" name="BATCH_AMOUNT[]" id="BATCH_AMOUNT_<?=$student_count?>" value="<?=$AMOUNT[$i]?>" batch-amt="<?=$AMOUNT[$i]?>" style="text-align:right;width:100px;" onchange="calc_total_2(1);paid_amount_value_change(<?=$student_count?>);check_number_validation(this);" />
		</td>
		<td >
			<div style="width:200px">
				<? if($TYPE == 2) {
					echo $BATCH_DETAIL_DESCRIPTION_LBL[$i]; ?>
					
					<input type="hidden" name="TUITION_BATCH_DETAIL_PK_COURSE_OFFERING[]" id="TUITION_BATCH_DETAIL_PK_COURSE_OFFERING" value="<?=$PK_COURSE_OFFERING_22_ARR[$i]?>" />

					<input type="hidden" name="TUITION_BATCH_DETAIL_PK_CAMPUS_PROGRAM[]" id="TUITION_BATCH_DETAIL_PK_CAMPUS_PROGRAM" value="<?=$PK_CAMPUS_PROGRAM_22_ARR[$i]?>" />

				<? } else if($TYPE == 1 || $TYPE == 9) { ?>
					<input type="hidden" name="TUITION_BATCH_DETAIL_PK_CAMPUS_PROGRAM[]" id="TUITION_BATCH_DETAIL_PK_CAMPUS_PROGRAM" value="<?=$PK_CAMPUS_PROGRAM_22_ARR[$i]?>" />
					
					<? $res_type = $db->Execute("SELECT CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1 FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT[$i]' AND PK_STUDENT_ENROLLMENT > 0 AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
					echo $res_type->fields['CODE'].' - '.$res_type->fields['BEGIN_DATE_1'];
				} ?>
			</div>
		</td>
		
		<td >
			<input type="text" class="form-control" placeholder="" name="TUITION_BATCH_DETAIL_AY[]" id="TUITION_BATCH_DETAIL_AY_<?=$student_count?>" value="<?=$TUITION_BATCH_DETAIL_AY[$i]?>" style="width:50px" />
		</td>
		<td >
			<input type="text" class="form-control" placeholder="" name="TUITION_BATCH_DETAIL_AP[]" id="TUITION_BATCH_DETAIL_AP_<?=$student_count?>" value="<?=$TUITION_BATCH_DETAIL_AP[$i]?>" style="width:50px" />
		</td>
		
		<td >
			<div style="width:200px">
				<? $_REQUEST['stud_id'] 	= $PK_STUDENT_MASTER[$i];
				$_REQUEST['count1'] 		= $student_count;
				$_REQUEST['en_def_val'] 	= $PK_STUDENT_ENROLLMENT[$i];
				include("ajax_get_misc_batch_student_enrollment.php"); ?>
			</div>
		</td>
		<!-- Ticket # 1340 -->
		<td>
			<select id="BATCH_PK_TERM_BLOCK_<?=$student_count?>" name="BATCH_PK_TERM_BLOCK[]" class="form-control" style="width:200px" >
				<option></option>
				<? $res_type = $db->Execute("select PK_TERM_BLOCK,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, DESCRIPTION from S_TERM_BLOCK WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by ACTIVE DESC, BEGIN_DATE DESC");
				while (!$res_type->EOF) { ?>
					<option value="<?=$res_type->fields['PK_TERM_BLOCK']?>" <? if($PK_TERM_BLOCK[$i] == $res_type->fields['PK_TERM_BLOCK']) echo "selected"; ?> ><?=$res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['DESCRIPTION']?></option>
				<?	$res_type->MoveNext();
				} ?>
			</select>
		</td>
		
		<td>
			<? $BATCH_PRIOR_YEAR = $TUITION_BATCH_PRIOR_YEAR[$i]; ?>
			<select id="BATCH_PRIOR_YEAR_<?=$student_count?>" name="BATCH_PRIOR_YEAR[]" class="form-control required-entry" style="width:50px" >
				<option ></option>
				<option value="1" <? if($BATCH_PRIOR_YEAR == 1) echo "selected"; ?> >Yes</option>
				<option value="2" <? if($BATCH_PRIOR_YEAR == 2) echo "selected"; ?> >No</option>
			</select>
		</td>
		
		
	</tr>
<? $i++;
} ?>

</tbody>
</table>
