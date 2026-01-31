<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/course_offering.php");
require_once("../language/student.php");
require_once("check_access.php");

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$count = 0;
	if($_GET['type'] == "ls") {
		$USER_FILTER['PK_LEAD_SOURCE']  = implode(",",$_POST['PK_LEAD_SOURCE']);
		$_SESSION['SRC_PK_LEAD_SOURCE'] = $USER_FILTER['PK_LEAD_SOURCE'];
		$count = count($_POST['PK_LEAD_SOURCE']);
	} else if($_GET['type'] == "camp") {
		$USER_FILTER['PK_CAMPUS']  = implode(",",$_POST['PK_CAMPUS']);
		$_SESSION['SRC_PK_CAMPUS'] = $USER_FILTER['PK_CAMPUS'];
		$count = count($_POST['PK_CAMPUS']);
	}  else if($_GET['type'] == "stu_sts") {
		$USER_FILTER['SEARCH_PAST_STUDENT']  = $_GET['do_not_show_admission'];
		$USER_FILTER['SHOW_LEAD']  			 = $_GET['SHOW_LEAD'];
		
		if(!empty($_POST['PK_STUDENT_STATUS']))
			$USER_FILTER['PK_STUDENT_STATUS'] = implode(",",$_POST['PK_STUDENT_STATUS']);
		else
			$USER_FILTER['PK_STUDENT_STATUS'] = "";

		$_SESSION['SRC_PK_STUDENT_STATUS'] 	 = $USER_FILTER['PK_STUDENT_STATUS'];
		$count = count($_POST['PK_STUDENT_STATUS']);
		
		if($_GET['do_not_show_admission'] == 1)
			$_SESSION['SRC_SEARCH_PAST_STUDENT'] = 'true';
		else
			$_SESSION['SRC_SEARCH_PAST_STUDENT'] = "";
			
		if($_GET['SHOW_LEAD'] == 1)
			$_SESSION['SRC_SHOW_LEAD'] = 'true';
		else
			$_SESSION['SRC_SHOW_LEAD'] = "";
			
	} else if($_GET['type'] == "prog") {
		$USER_FILTER['PK_CAMPUS_PROGRAM']  = implode(",",$_POST['PK_CAMPUS_PROGRAM']);
		$_SESSION['SRC_PK_CAMPUS_PROGRAM'] = $USER_FILTER['PK_CAMPUS_PROGRAM'];
		$count = count($_POST['PK_CAMPUS_PROGRAM']);
	} else if($_GET['type'] == "term") {
		$USER_FILTER['PK_TERM_MASTER']  = implode(",",$_POST['PK_TERM_MASTER']);
		$_SESSION['SRC_PK_TERM_MASTER'] = $USER_FILTER['PK_TERM_MASTER'];
		$count = count($_POST['PK_TERM_MASTER']);
	} else if($_GET['type'] == "ps") {
		$USER_FILTER['PK_PLACEMENT_STATUS']  = implode(",",$_POST['PK_PLACEMENT_STATUS']);
		$_SESSION['SRC_PK_PLACEMENT_STATUS'] = $USER_FILTER['PK_PLACEMENT_STATUS'];
		$count = count($_POST['PK_PLACEMENT_STATUS']);
	} else if($_GET['type'] == "fund") {
		$USER_FILTER['PK_FUNDING']  = implode(",",$_POST['PK_FUNDING']);
		$_SESSION['SRC_PK_FUNDING'] = $USER_FILTER['PK_FUNDING'];
		$count = count($_POST['PK_FUNDING']);
	} else if($_GET['type'] == "rep") {
		$USER_FILTER['PK_REPRESENTATIVE']  = implode(",",$_POST['PK_REPRESENTATIVE']);
		$_SESSION['SRC_PK_REPRESENTATIVE'] = $USER_FILTER['PK_REPRESENTATIVE'];
		$count = count($_POST['PK_REPRESENTATIVE']);
	} else if($_GET['type'] == "employed") {
		$USER_FILTER['EMPLOYED']  = implode(",",$_POST['EMPLOYED']);
		$_SESSION['SRC_EMPLOYED'] = $USER_FILTER['EMPLOYED'];
		$count = count($_POST['EMPLOYED']);
	}
	/* Ticket # 1650 */
	else if($_GET['type'] == "session") {
		$USER_FILTER['PK_SESSION']  = implode(",",$_POST['PK_SESSION']);
		$_SESSION['SRC_PK_SESSION'] = $USER_FILTER['PK_SESSION'];
		$count = count($_POST['PK_SESSION']);
	} else if($_GET['type'] == "student_group") {
		$USER_FILTER['PK_STUDENT_GROUP']  = implode(",",$_POST['PK_STUDENT_GROUP']);
		$_SESSION['SRC_PK_STUDENT_GROUP'] = $USER_FILTER['PK_STUDENT_GROUP'];
		$count = count($_POST['PK_STUDENT_GROUP']);
	}
	/* Ticket # 1650 */
	
	$res_s = $db->Execute("select PK_USER_FILTER from Z_USER_FILTER WHERE PK_USER = '$_SESSION[PK_USER]' AND PAGE_T = '$_GET[t]' ");
	if($res_s->RecordCount() == 0){
		$USER_FILTER['PK_ACCOUNT'] 	= $_SESSION['PK_ACCOUNT'];
		$USER_FILTER['PK_USER'] 	= $_SESSION['PK_USER'];
		$USER_FILTER['PAGE_T'] 		= $_GET['t'];
		$USER_FILTER['CREATED_ON']	= date("Y-m-d H:i:s");
		$USER_FILTER['CREATED_BY'] 	= $_SESSION['PK_USER'];
		db_perform('Z_USER_FILTER', $USER_FILTER, 'insert');
	} else {
		$USER_FILTER['EDITED_ON']	= date("Y-m-d H:i:s");
		$USER_FILTER['EDITED_BY'] 	= $_SESSION['PK_USER'];
		db_perform('Z_USER_FILTER', $USER_FILTER, 'update'," PK_USER_FILTER = '".$res_s->fields['PK_USER_FILTER']."' ");
	}
	//echo "<pre>";print_r($_POST);exit;
	if($_GET['close'] == 1) {
		echo $count;
		exit;
	} else { ?>
	<script type="text/javascript">window.opener.doSearch_1(this,'<?=$count?>','<?=$_GET['type']?>');</script>
<? }
} 


if($_GET['type'] == 'ls') 
	$title1 = LEAD_SOURCE; 
else if($_GET['type'] == 'camp') 
	$title1 = CAMPUS; 
else if($_GET['type'] == 'stu_sts') 
	$title1 = STUDENT_STATUS; 
else if($_GET['type'] == 'prog') 
	$title1 = PROGRAM; 
else if($_GET['type'] == 'term') 
	$title1 = FIRST_TERM; 
else if($_GET['type'] == 'ps') 
	$title1 = PLACEMENT_STATUS;
else if($_GET['type'] == 'fund') 
	$title1 = FUNDING; 
else if($_GET['type'] == 'rep') 
	$title1 = ADMISSION_REP; 
else if($_GET['type'] == 'employed') 
	$title1 = EMPLOYED; 
/* Ticket # 1650 */	
else if($_GET['type'] == 'session') 
	$title1 = SESSION;
else if($_GET['type'] == 'student_group') 
	$title1 = STUDENT_GROUP;
/* Ticket # 1650 */
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
	<title><?=$title1?> | <?=$title?></title>
	
	<style>
		.tableFixHead          { overflow-y: auto; height: 600px; }
		.tableFixHead thead th { position: sticky; top: 0; }
		.tableFixHead thead th { background:#E8E8E8; }
		.floating-labels label {
			position: inherit;
			max-width: 100%;
		}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? //require_once("menu.php"); ?>
        <div class="page-wrapper">
			<form class="floating-labels" method="post" name="form1" id="form1" >
				<div class="container-fluid">
					<div class="row page-titles">
						<? if($_GET['type'] == 'term') { ?>
						<div class="col-md-2 align-self-center">
							<h4 class="text-themecolor">
								<?=$title1 ?>
							</h4>
						</div>
						<div class="col-md-2 align-self-center" style="flex: 0 0 12.66667%;max-width: 12.66667%;" >
							<?=BEGIN_DATE?>
							<input type="text" class="form-control date" id="START_DATE" placeholder=" <?=START_DATE?>" onchange="search()" >
						</div>
						<div class="col-md-2 align-self-center" style="flex: 0 0 12.66667%;max-width: 12.66667%;" >
							<br />
							<input type="text" class="form-control date" id="END_DATE" placeholder=" <?=END_DATE?>" onchange="search()">
						</div>
						<div class="col-md-2 align-self-center" style="flex: 0 0 12.66667%;max-width: 12.66667%;" >
							<?=END_DATE?>
							<input type="text" class="form-control date" id="START_DATE_1" placeholder=" <?=START_DATE?>" onchange="search()" >
						</div>	
						<div class="col-md-2 align-self-center" style="flex: 0 0 12.66667%;max-width: 12.66667%;" >
							<br />
							<input type="text" class="form-control date" id="END_DATE_1" placeholder=" <?=END_DATE?>" onchange="search()">
						</div>
						<div class="col-md-2 align-self-center" >
							<input type="checkbox" id="ACTIVE_TERMS_ONLY" value="1" onclick="search()" > <?=ACTIVE_TERMS_ONLY?>
							<input type="text" class="form-control" id="SEARCH" placeholder=" <?=SEARCH?>" onkeypress="do_search(event)" >
						</div>
						<div class="col-md-1 align-self-center" >		
							<br />
							<button type="submit" name="btn" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
						</div>
						<? } else if($_GET['type'] == 'prog' || $_GET['type'] == 'student_group') { // <!-- DIAM-1965 -->?>
							<div class="col-md-7 align-self-center">
							<h4 class="text-themecolor">
								<?=$title1 ?>
							</h4>
						</div>	
							<? include("select_filter_option.php"); // <!-- DIAM-1965 --> ?>
						<? } else { ?>
						<div class="col-md-12 align-self-center">
							<h4 class="text-themecolor">
								<?=$title1 ?>
							</h4>
						</div>						
						<? } ?>
					</div>
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<? if($_GET['type'] != 'term') { ?>
									<div class="row">
										<div class="col-md-3">
											<div class="col-md-12 form-group custom-control custom-checkbox form-group">
												<input type="checkbox" class="custom-control-input" id="SELECT_ALL" value="1" onclick="select_all()" >
												<label class="custom-control-label" for="SELECT_ALL" ><?=SELECT_ALL ?></label>
											</div>
										</div>
									</div>
									<? } ?>
									
									<? if($_GET['type'] == 'ls'){ ?>
										<div class="row">
											<? $res_s = $db->Execute("select PK_LEAD_SOURCE from Z_USER_FILTER WHERE PK_USER = '$_SESSION[PK_USER]' AND PAGE_T = '$_GET[t]' ");
											$PK_LEAD_SOURCE_ARR = explode(",",$res_s->fields['PK_LEAD_SOURCE']);
											
											$res_type = $db->Execute("select PK_LEAD_SOURCE,LEAD_SOURCE,DESCRIPTION from M_LEAD_SOURCE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by LEAD_SOURCE ASC");
											while (!$res_type->EOF) { 
												$checked = "";
												if(!empty($PK_LEAD_SOURCE_ARR)){
													foreach($PK_LEAD_SOURCE_ARR as $PK_LEAD_SOURCE){
														if($res_type->fields['PK_LEAD_SOURCE'] == $PK_LEAD_SOURCE)
															$checked = "checked";
													}
												} ?>
												<div class="col-md-3">
													<div class="col-md-12 form-group custom-control custom-checkbox form-group">
														<input type="checkbox" class="custom-control-input" id="PK_LEAD_SOURCE_<?=$res_type->fields['PK_LEAD_SOURCE']?>" name="PK_LEAD_SOURCE[]" value="<?=$res_type->fields['PK_LEAD_SOURCE']?>" <?=$checked?> >
														<label class="custom-control-label" for="PK_LEAD_SOURCE_<?=$res_type->fields['PK_LEAD_SOURCE']?>" ><?=$res_type->fields['LEAD_SOURCE'].' - '.$res_type->fields['DESCRIPTION']?></label>
													</div>
												</div>
											<?	$res_type->MoveNext();
											} ?>
										</div>
									<? } else if($_GET['type'] == 'camp'){ ?>
										<div class="row">
											<? /* Ticket # 2028  */
											$PK_CAMPUS_ARR = array();
											$cond = '';
											if($_SESSION['PK_ROLES'] == 3)
												$cond = " AND PK_CAMPUS IN ($_SESSION[PK_CAMPUS]) ";
											$res_s = $db->Execute("select PK_CAMPUS from Z_USER_FILTER WHERE PK_USER = '$_SESSION[PK_USER]' AND PAGE_T = '$_GET[t]' ");
											if(trim($res_s->fields['PK_CAMPUS']) != "")
												$PK_CAMPUS_ARR = explode(",",$res_s->fields['PK_CAMPUS']);
											
											$res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond order by CAMPUS_CODE ASC");
											if(empty($PK_CAMPUS_ARR) && $res_type->RecordCount() == 1){
												$PK_CAMPUS_ARR[] = $res_type->fields['PK_CAMPUS']; 
											}
											
											while (!$res_type->EOF) { 
												$checked = "";
												if(!empty($PK_CAMPUS_ARR)){
													foreach($PK_CAMPUS_ARR as $PK_CAMPUS){
														if($res_type->fields['PK_CAMPUS'] == $PK_CAMPUS)
															$checked = "checked";
													}
												} ?>
												<div class="col-md-3">
													<div class="col-md-12 form-group custom-control custom-checkbox form-group">
														<input type="checkbox" class="custom-control-input" id="PK_CAMPUS_<?=$res_type->fields['PK_CAMPUS']?>" name="PK_CAMPUS[]" value="<?=$res_type->fields['PK_CAMPUS']?>" <?=$checked?> >
														<label class="custom-control-label" for="PK_CAMPUS_<?=$res_type->fields['PK_CAMPUS']?>" ><?=$res_type->fields['CAMPUS_CODE'] ?></label>
													</div>
												</div>
											<?	$res_type->MoveNext();
											} /* Ticket # 2028  */ ?>
										</div>
									<? } else if($_GET['type'] == 'stu_sts'){ ?>
										<div class="row">
											<? if($_GET['do_not_show_admission'] == 1) {
												$cond = " AND (ADMISSIONS = 0) ";
											} else if($_GET['SHOW_LEAD'] == 1) {
												$cond = " AND (ADMISSIONS = 1) ";
											} else {
												if($_GET['t'] == 1)
													$cond = " AND (ADMISSIONS = 1) ";
												else if($_GET['t'] == 2 || $_GET['t'] == 3 || $_GET['t'] == 4 || $_GET['t'] == 5 || $_GET['t'] == 6)
													$cond = " AND (ADMISSIONS = 0) ";
											}
												
											$res_s = $db->Execute("select PK_STUDENT_STATUS from Z_USER_FILTER WHERE PK_USER = '$_SESSION[PK_USER]' AND PAGE_T = '$_GET[t]' ");
											$PK_STUDENT_STATUS_ARR = explode(",",$res_s->fields['PK_STUDENT_STATUS']);
											
											$res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond11 $cond order by STUDENT_STATUS ASC");
											while (!$res_type->EOF) { 
												$checked = "";
												if(!empty($PK_STUDENT_STATUS_ARR)){
													foreach($PK_STUDENT_STATUS_ARR as $PK_STUDENT_STATUS){
														if($res_type->fields['PK_STUDENT_STATUS'] == $PK_STUDENT_STATUS)
															$checked = "checked";
													}
												} ?>
												<div class="col-md-3">
													<div class="col-md-12 form-group custom-control custom-checkbox form-group">
														<input type="checkbox" class="custom-control-input" id="PK_STUDENT_STATUS_<?=$res_type->fields['PK_STUDENT_STATUS']?>" name="PK_STUDENT_STATUS[]" value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" <?=$checked?> >
														<label class="custom-control-label" for="PK_STUDENT_STATUS_<?=$res_type->fields['PK_STUDENT_STATUS']?>" ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></label>
													</div>
												</div>
											<?	$res_type->MoveNext();
											} ?>
										</div>
									<? } else if($_GET['type'] == 'prog'){ ?>
										<div class="d-flex flex-column row" id="prog_div"> <!-- DIAM-1965 -->
										<? include("ajax_search_program_for_select_filter.php"); //<!-- DIAM-1965 --> ?>
										</div>
									<? } else if($_GET['type'] == 'term'){ ?>
										<div class="row">
											<div class="col-md-12" id="term_div">
												<? include("ajax_search_term_for_select_filter.php"); ?>
											</div>
										</div>
									<? } else if($_GET['type'] == 'ps'){ ?>
										<div class="row">
											<? $res_s = $db->Execute("select PK_PLACEMENT_STATUS from Z_USER_FILTER WHERE PK_USER = '$_SESSION[PK_USER]' AND PAGE_T = '$_GET[t]' ");
											$PK_PLACEMENT_STATUS_ARR = explode(",",$res_s->fields['PK_PLACEMENT_STATUS']);
											
											$res_type = $db->Execute("select PK_PLACEMENT_STATUS,PLACEMENT_STATUS from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by PLACEMENT_STATUS ASC");
											while (!$res_type->EOF) { 
												$checked = "";
												if(!empty($PK_PLACEMENT_STATUS_ARR)){
													foreach($PK_PLACEMENT_STATUS_ARR as $PK_PLACEMENT_STATUS){
														if($res_type->fields['PK_PLACEMENT_STATUS'] == $PK_PLACEMENT_STATUS)
															$checked = "checked";
													}
												} ?>
												<div class="col-md-3">
													<div class="col-md-12 form-group custom-control custom-checkbox form-group">
														<input type="checkbox" class="custom-control-input" id="PK_PLACEMENT_STATUS_<?=$res_type->fields['PK_PLACEMENT_STATUS']?>" name="PK_PLACEMENT_STATUS[]" value="<?=$res_type->fields['PK_PLACEMENT_STATUS']?>" <?=$checked?> >
														<label class="custom-control-label" for="PK_PLACEMENT_STATUS_<?=$res_type->fields['PK_PLACEMENT_STATUS']?>" ><?=$res_type->fields['PLACEMENT_STATUS'] ?></label>
													</div>
												</div>
											<?	$res_type->MoveNext();
											} ?>
										</div>
									<? } else if($_GET['type'] == 'fund'){ ?>
										<div class="row">
											<? $res_s = $db->Execute("select PK_FUNDING from Z_USER_FILTER WHERE PK_USER = '$_SESSION[PK_USER]' AND PAGE_T = '$_GET[t]' ");
											$PK_FUNDING_ARR = explode(",",$res_s->fields['PK_FUNDING']);
											
											$res_type = $db->Execute("select PK_FUNDING, FUNDING, DESCRIPTION from M_FUNDING WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond11 order by FUNDING ASC");
											while (!$res_type->EOF) { 
												$checked = "";
												if(!empty($PK_FUNDING_ARR)){
													foreach($PK_FUNDING_ARR as $PK_FUNDING){
														if($res_type->fields['PK_FUNDING'] == $PK_FUNDING)
															$checked = "checked";
													}
												} ?>
												<div class="col-md-3">
													<div class="col-md-12 form-group custom-control custom-checkbox form-group">
														<input type="checkbox" class="custom-control-input" id="PK_FUNDING_<?=$res_type->fields['PK_FUNDING']?>" name="PK_FUNDING[]" value="<?=$res_type->fields['PK_FUNDING']?>" <?=$checked?> >
														<label class="custom-control-label" for="PK_FUNDING_<?=$res_type->fields['PK_FUNDING']?>" ><?=$res_type->fields['FUNDING'].' - '.$res_type->fields['DESCRIPTION']?></label>
													</div>
												</div>
											<?	$res_type->MoveNext();
											} ?>
										</div>	
									<? } else if($_GET['type'] == 'rep'){ ?>
										<div class="row">
											<? $res_s = $db->Execute("select PK_REPRESENTATIVE from Z_USER_FILTER WHERE PK_USER = '$_SESSION[PK_USER]' AND PAGE_T = '$_GET[t]' ");
											$PK_REPRESENTATIVE_ARR = explode(",",$res_s->fields['PK_REPRESENTATIVE']);
											
											$res_type = $db->Execute("select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME from S_EMPLOYEE_MASTER, M_DEPARTMENT , S_EMPLOYEE_DEPARTMENT  WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DEPARTMENT_MASTER = 2 AND S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT = M_DEPARTMENT.PK_DEPARTMENT AND S_EMPLOYEE_DEPARTMENT.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER order by CONCAT(FIRST_NAME,' ',LAST_NAME) ASC ");
											while (!$res_type->EOF) { 
												$checked = "";
												if(!empty($PK_REPRESENTATIVE_ARR)){
													foreach($PK_REPRESENTATIVE_ARR as $PK_REPRESENTATIVE){
														if($res_type->fields['PK_EMPLOYEE_MASTER'] == $PK_REPRESENTATIVE)
															$checked = "checked";
													}
												} ?>
												<div class="col-md-3">
													<div class="col-md-12 form-group custom-control custom-checkbox form-group">
														<input type="checkbox" class="custom-control-input" id="PK_REPRESENTATIVE_<?=$res_type->fields['PK_EMPLOYEE_MASTER']?>" name="PK_REPRESENTATIVE[]" value="<?=$res_type->fields['PK_EMPLOYEE_MASTER']?>" <?=$checked?> >
														<label class="custom-control-label" for="PK_REPRESENTATIVE_<?=$res_type->fields['PK_EMPLOYEE_MASTER']?>" ><?=$res_type->fields['FUNDING'].' - '.$res_type->fields['NAME']?></label>
													</div>
												</div>
											<?	$res_type->MoveNext();
											} ?>
										</div>	
									<? } else if($_GET['type'] == 'employed'){ ?>
										<div class="row">
											<? $res_s = $db->Execute("select EMPLOYED from Z_USER_FILTER WHERE PK_USER = '$_SESSION[PK_USER]' AND PAGE_T = '$_GET[t]' ");
											$EMPLOYED_ARR = explode(",",$res_s->fields['EMPLOYED']);
											
											$EMPLOYED_VAL[0] = 1;
											$EMPLOYED_TXT[0] = 'Yes';
											
											$EMPLOYED_VAL[1] = 2;
											$EMPLOYED_TXT[1] = 'No';
											
											$EMPLOYED_VAL[2] = 3;
											$EMPLOYED_TXT[2] = 'Not Set';
											
											foreach($EMPLOYED_VAL as $ind => $val){
												$checked = "";
												if(!empty($EMPLOYED_ARR)){
													foreach($EMPLOYED_ARR as $EMPLOYED){
														if($val == $EMPLOYED)
															$checked = "checked";
													}
												} ?>
												<div class="col-md-3">
													<div class="col-md-12 form-group custom-control custom-checkbox form-group">
														<input type="checkbox" class="custom-control-input" id="EMPLOYED_<?=$val?>" name="EMPLOYED[]" value="<?=$val?>" <?=$checked?> >
														<label class="custom-control-label" for="EMPLOYED_<?=$val?>" ><?=$EMPLOYED_TXT[$ind]?></label>
													</div>
												</div>
											<? } ?>
										</div>
									<? /* Ticket # 1650 */
									} else if($_GET['type'] == 'session'){ ?>
										<div class="row">
											<? $res_s = $db->Execute("select PK_SESSION from Z_USER_FILTER WHERE PK_USER = '$_SESSION[PK_USER]' AND PAGE_T = '$_GET[t]' ");
											$PK_SESSION_ARR = explode(",",$res_s->fields['PK_SESSION']);
											
											$res_type = $db->Execute("select PK_SESSION, SESSION from M_SESSION WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by SESSION ASC");
											while (!$res_type->EOF) { 
												$checked = "";
												if(!empty($PK_SESSION_ARR)){
													foreach($PK_SESSION_ARR as $PK_SESSION){
														if($res_type->fields['PK_SESSION'] == $PK_SESSION)
															$checked = "checked";
													}
												} ?>
												<div class="col-md-3">
													<div class="col-md-12 form-group custom-control custom-checkbox form-group">
														<input type="checkbox" class="custom-control-input" id="PK_SESSION_<?=$res_type->fields['PK_SESSION']?>" name="PK_SESSION[]" value="<?=$res_type->fields['PK_SESSION']?>" <?=$checked?> >
														<label class="custom-control-label" for="PK_SESSION_<?=$res_type->fields['PK_SESSION']?>" ><?=$res_type->fields['SESSION'] ?></label>
													</div>
												</div>
											<?	$res_type->MoveNext();
											} ?>
										</div>	
									<? } else if($_GET['type'] == 'student_group'){ ?>
										<div class="d-flex flex-column row" id="student_group_div"> <!-- DIAM-1965 -->
											<? include("ajax_search_student_group_for_select_filter.php"); //<!-- DIAM-1965 --> ?>
										</div>	
									<? /* Ticket # 1650 */ ?>
									
									<? } ?>
									
									<? if($_GET['type'] != 'term' && $_GET['type'] != 'prog' && $_GET['type'] != 'student_group' ) { //DIAM-1965 ?>
									<div class="row">
										 <div class="col-md-12 submit-button-sec">
											<center><button type="submit" name="btn" class="btn waves-effect waves-light btn-info"><?=SAVE?></button></center>
										</div>
									</div>
									<? } ?>
										<!-- //DIAM-1965-->
										<input type="hidden" name="FILTERTYPE" id="FILTERTYPE" value="<?=$_GET['type']?>">
										<!-- //DIAM-1965-->
								</div>
							</div>
						</div>
					</div>
					
				</div>
			</form>
        </div>
        <? require_once("footer.php"); ?>
    </div>
	
	<script src="../backend_assets/node_modules/jquery/jquery-3.2.1.min.js"></script>
	<!-- Bootstrap popper Core JavaScript -->
	<script src="../backend_assets/node_modules/popper/popper.min.js"></script>
	<script src="../backend_assets/node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
	<!-- slimscrollbar scrollbar JavaScript -->
	<script src="../backend_assets/dist/js/perfect-scrollbar.jquery.min.js"></script>
	<!--Wave Effects -->
	<script src="../backend_assets/dist/js/waves.js"></script>
	<!--Menu sidebar -->
	<script src="../backend_assets/dist/js/sidebarmenu.js"></script>
	<!--Custom JavaScript -->
	<script src="../backend_assets/dist/js/custom.min.js"></script>
	<script src="../backend_assets/node_modules/toast-master/js/jquery.toast.js"></script>

	<script src="../backend_assets/node_modules/inputmask/dist/min/jquery.inputmask.bundle.min.js"></script>
	<script src="../backend_assets/dist/js/pages/mask.init.js"></script>

	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		function select_all(){
			var name = "";
			<? if($_GET['type'] == 'ls'){ ?>
				name = 'PK_LEAD_SOURCE[]';
			<? } else if($_GET['type'] == 'camp'){ ?>
				name = 'PK_CAMPUS[]';
			<? } else if($_GET['type'] == 'stu_sts'){ ?>
				name = 'PK_STUDENT_STATUS[]';
			<? } else if($_GET['type'] == 'prog'){ ?>
				name = 'PK_CAMPUS_PROGRAM[]';
			<? } else if($_GET['type'] == 'term'){ ?>
				name = 'PK_TERM_MASTER[]';
			<? } else if($_GET['type'] == 'ps'){ ?>
				name = 'PK_PLACEMENT_STATUS[]';
			<? } else if($_GET['type'] == 'fund'){ ?>
				name = 'PK_FUNDING[]';
			<? } else if($_GET['type'] == 'rep'){ ?>
				name = 'PK_REPRESENTATIVE[]';
			<? } else if($_GET['type'] == 'employed'){ ?>
				name = 'EMPLOYED[]';
			<? } else if($_GET['type'] == 'session'){ ?>
				name = 'PK_SESSION[]';
			<? } else if($_GET['type'] == 'student_group'){ ?>
				name = 'PK_STUDENT_GROUP[]';
			<? } ?>
			
			
			var str = '';
			if(document.getElementById('SELECT_ALL').checked == true)
				str = true;
			else
				str = false;
				
			var PK_Field = document.getElementsByName(name)
			for(var i = 0 ; i < PK_Field.length ; i++){
				PK_Field[i].checked = str
			}
		}
		
		function fun_select_all(){
			var str = '';
			if(document.getElementById('SEARCH_SELECT_ALL').checked == true)
				str = true;
			else
				str = false;
				
			var PK_TERM_MASTER = document.getElementsByName('PK_TERM_MASTER[]')
			for(var i = 0 ; i < PK_TERM_MASTER.length ; i++){
				PK_TERM_MASTER[i].checked = str
			}
		}
	</script>
	
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	
	<script type="text/javascript">
	jQuery(document).ready(function($) { 
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
	});
	function do_search(e){
		if (e.keyCode == 13) {
			if($("#FILTERTYPE").val()=='prog' || $("#FILTERTYPE").val()=='student_group'){ //<!-- //DIAM-1965-->
				 doFilterSearch();
			 }else{ //<!-- //DIAM-1965-->
			search();
			} //<!-- //DIAM-1965-->
		}
	}
	
	function search(){
		jQuery(document).ready(function($) { 
			var ACTIVE_TERMS_ONLY = 0
			if(document.getElementById('ACTIVE_TERMS_ONLY').checked == true)
				ACTIVE_TERMS_ONLY = 1;
				
			var data  = 'START_DATE='+$("#START_DATE").val()+'&END_DATE='+$("#END_DATE").val()+'&SEARCH='+$("#SEARCH").val()+'&ACTIVE_TERMS_ONLY='+ACTIVE_TERMS_ONLY+'&START_DATE_1='+$("#START_DATE_1").val()+'&END_DATE_1='+$("#END_DATE_1").val();
			var value = $.ajax({
				url: "ajax_search_term_for_select_filter",
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					//alert(data)
					document.getElementById('term_div').innerHTML = data;
				}		
			}).responseText;
		});
	}
	
	jQuery(document).ready(function($) { 
		$(window).keydown(function(event){
			if(event.keyCode == 13) {
				if($("#FILTERTYPE").val()=='prog' || $("#FILTERTYPE").val()=='student_group'){ //<!-- //DIAM-1965-->
					doFilterSearch();
				 }else{ //<!-- //DIAM-1965-->
				search();
				} //<!-- //DIAM-1965-->
				event.preventDefault();
				return false;
			}
		});
	});	
	
	//<!-- //DIAM-1965-->
	function doFilterSearch(){
		jQuery(document).ready(function($) { 	
			if($("#FILTERTYPE").val()=='prog'){
			 	var URL = "ajax_search_program_for_select_filter";
				var DIVID ="prog_div";
			}else if($("#FILTERTYPE").val()=='student_group'){
				var URL = "ajax_search_student_group_for_select_filter";
				var DIVID ="student_group_div";
			}					
			var data  = 'SEARCH='+$("#SEARCH").val()+'&FILTER_ACTIVE_SATATUS='+$("#FILTER_ACTIVE_SATATUS").val();
			var value = $.ajax({
				url: URL,
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					document.getElementById(DIVID).innerHTML = data;
				}		
			}).responseText;
		});
	}
	//<!-- //DIAM-1965-->
	</script>
</body>

</html>
