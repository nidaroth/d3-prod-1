<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php"); //ticket #1064
require_once("../language/student_probation.php");
require_once("get_department_from_t.php");
require_once("check_access.php");
$REGISTRAR_ACCESS 	= check_access('REGISTRAR_ACCESS');

if($REGISTRAR_ACCESS != 2 && $REGISTRAR_ACCESS != 3){ 
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$STUDENT_PROBATION = $_POST;
	
	if($STUDENT_PROBATION['BEGIN_DATE'] != '')
		$STUDENT_PROBATION['BEGIN_DATE'] = date("Y-m-d",strtotime($STUDENT_PROBATION['BEGIN_DATE']));
		
	if($STUDENT_PROBATION['END_DATE'] != '')
		$STUDENT_PROBATION['END_DATE'] = date("Y-m-d",strtotime($STUDENT_PROBATION['END_DATE']));
	
	$PK_DEPARTMENT = get_department_from_t($_GET['t']);	
		
	if($_GET['id'] == ''){
		$STUDENT_PROBATION['PK_ACCOUNT']   			= $_SESSION['PK_ACCOUNT'];
		$STUDENT_PROBATION['PK_STUDENT_MASTER'] 	= $_GET['sid'];
		$STUDENT_PROBATION['PK_STUDENT_ENROLLMENT'] = $_GET['eid'];
		$STUDENT_PROBATION['PK_DEPARTMENT'] 		= $PK_DEPARTMENT;
		$STUDENT_PROBATION['CREATED_BY']  			= $_SESSION['PK_USER'];
		$STUDENT_PROBATION['CREATED_ON']  			= date("Y-m-d H:i");
		db_perform('S_STUDENT_PROBATION', $STUDENT_PROBATION, 'insert');
		
		$PK_STUDENT_PROBATION = $db->insert_ID();
	} else {
		$PK_STUDENT_PROBATION = $_GET['id'];
		$cond = "";

		$STUDENT_PROBATION['EDITED_BY']  = $_SESSION['PK_USER'];
		$STUDENT_PROBATION['EDITED_ON']  = date("Y-m-d H:i");
		db_perform('S_STUDENT_PROBATION', $STUDENT_PROBATION, 'update', " PK_STUDENT_PROBATION = '$PK_STUDENT_PROBATION' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond " );
		
		//echo "<pre> K_STUDENT_PROBATION = '$PK_STUDENT_PROBATION' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$_GET[eid]' $cond  ";print_r($STUDENT_PROBATION);exit;
	}
	//echo "<pre>";print_r($STUDENT_DOCUMENTS);exit;
	
	//echo "<pre>";print_r($_FILES);exit;
	
	header("location:student?id=".$_GET['sid'].'&tab=probationTab&eid='.$_GET['eid'].'&t='.$_GET['t']);
}
if($_GET['id'] == ''){
	$BEGIN_DATE 			= '';
	$END_DATE				= '';
	$PK_PROBATION_TYPE		= '';
	$PK_PROBATION_LEVEL		= '';
	$PK_PROBATION_STATUS	= '';
	$REASON					= '';
	$NOTES 					= '';
} else {
	$cond = "";

	$res = $db->Execute("SELECT * FROM S_STUDENT_PROBATION WHERE PK_STUDENT_PROBATION = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond "); 
	if($res->RecordCount() == 0){
		header("location:student?id=".$_GET['sid'].'&tab=probationTab&eid='.$_GET['eid'].'&t='.$_GET['t']);
		exit;
	}
	
	$BEGIN_DATE 			= $res->fields['BEGIN_DATE'];
	$END_DATE				= $res->fields['END_DATE'];
	$PK_PROBATION_TYPE		= $res->fields['PK_PROBATION_TYPE'];
	$PK_PROBATION_LEVEL		= $res->fields['PK_PROBATION_LEVEL'];
	$PK_PROBATION_STATUS	= $res->fields['PK_PROBATION_STATUS'];
	$REASON 				= $res->fields['REASON'];
	$NOTES  				= $res->fields['NOTES'];
	
	if($BEGIN_DATE != '0000-00-00')
		$BEGIN_DATE = date("m/d/Y",strtotime($BEGIN_DATE));
	else
		$BEGIN_DATE = '';
		
	if($END_DATE != '0000-00-00')
		$END_DATE = date("m/d/Y",strtotime($END_DATE));
	else
		$END_DATE = '';
}

$res = $db->Execute("SELECT IMAGE,FIRST_NAME,LAST_NAME,MIDDLE_NAME,OTHER_NAME FROM S_STUDENT_MASTER WHERE PK_STUDENT_MASTER = '$_GET[sid]' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
$IMAGE					= $res->fields['IMAGE'];
$FIRST_NAME 			= $res->fields['FIRST_NAME'];
$LAST_NAME 				= $res->fields['LAST_NAME'];
$MIDDLE_NAME	 		= $res->fields['MIDDLE_NAME'];
$OTHER_NAME	 			= $res->fields['OTHER_NAME'];

/* ticket #1116 */
$res = $db->Execute("SELECT STUDENT_ID, STATUS_DATE,STUDENT_STATUS,CODE, IF(EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(EXPECTED_GRAD_DATE, '%m/%d/%Y' )) AS EXPECTED_GRAD_DATE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IF(DETERMINATION_DATE = '0000-00-00','',DATE_FORMAT(DETERMINATION_DATE, '%m/%d/%Y' )) AS DETERMINATION_DATE, IF(DROP_DATE = '0000-00-00','',DATE_FORMAT(DROP_DATE, '%m/%d/%Y' )) AS DROP_DATE , IF(GRADE_DATE = '0000-00-00','',DATE_FORMAT(GRADE_DATE, '%m/%d/%Y' )) AS GRADE_DATE, IF(LDA = '0000-00-00','',DATE_FORMAT(LDA, '%m/%d/%Y' )) AS LDA, IF(ORIGINAL_EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(ORIGINAL_EXPECTED_GRAD_DATE, '%m/%d/%Y' )) AS ORIGINAL_EXPECTED_GRAD_DATE FROM S_STUDENT_ENROLLMENT LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$_GET[sid]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$_GET[eid]' ");  //Ticket # 1537
$STATUS_DATE 	 	= $res->fields['STATUS_DATE'];
$STUDENT_STATUS	 	= $res->fields['STUDENT_STATUS'];
$CAMPUS_PROGRAM  	= $res->fields['CODE'];
$FIRST_TERM_DATE 	= $res->fields['BEGIN_DATE_1'];
$EXPECTED_GRAD_DATE = $res->fields['EXPECTED_GRAD_DATE'];
$STUDENT_ID 		= $res->fields['STUDENT_ID'];//Ticket # 1537

$ORIGINAL_EXPECTED_GRAD_DATE 	= $res->fields['ORIGINAL_EXPECTED_GRAD_DATE'];
$DETERMINATION_DATE 			= $res->fields['DETERMINATION_DATE'];
$DROP_DATE 						= $res->fields['DROP_DATE'];
$GRADE_DATE 					= $res->fields['GRADE_DATE'];
$LDA 							= $res->fields['LDA'];
/* ticket #1116 */

/* Ticket # 1534 */
$res = $db->Execute("SELECT CAMPUS_CODE FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE PK_STUDENT_ENROLLMENT = '$_GET[eid]' AND S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS AND PK_STUDENT_ENROLLMENT > 0 ");
$HEADER_CAMPUS_CODE = $res->fields['CAMPUS_CODE'];
/* Ticket # 1534 */

if($STATUS_DATE != '0000-00-00')
	$STATUS_DATE = date("m/d/Y",strtotime($STATUS_DATE));
else
	$STATUS_DATE = '';
	
$has_warning_notes 	= 0;
$warning_notes 		= '';

$res_note = $db->Execute("select NOTES,DEPARTMENT FROM S_STUDENT_PROBATION LEFT JOIN M_DEPARTMENT ON M_DEPARTMENT.PK_DEPARTMENT = S_STUDENT_PROBATION.PK_DEPARTMENT, M_NOTE_TYPE WHERE PK_NOTE_TYPE_MASTER = 1 AND S_STUDENT_PROBATION.PK_NOTE_TYPE = M_NOTE_TYPE.PK_NOTE_TYPE AND S_STUDENT_PROBATION.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_PROBATION.PK_STUDENT_MASTER = '$_GET[sid]' AND PK_STUDENT_ENROLLMENT = '$_GET[eid]' AND SATISFIED = 0 ");
	
if($res_note->RecordCount() > 0) {
	$has_warning_notes = 1;
	$warning_notes = '';
	while (!$res_note->EOF){
		if($warning_notes != '')
			$warning_notes .= ', ';
			
		$warning_notes .= 'See '.$res_note->fields['DEPARTMENT'];
		$res_note->MoveNext();
	}
	$warning_notes = 'Warning - '.$warning_notes;
}
$res_probation = $db->Execute("select PK_STUDENT_PROBATION FROM S_STUDENT_PROBATION WHERE PK_PROBATION_STATUS = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_GET[sid]' AND PK_STUDENT_ENROLLMENT = '$_GET[eid]' ");
if($res_probation->RecordCount() > 0) {
	$has_warning_notes = 1;
	if($warning_notes != '')
		$warning_notes .= '<br />';
		
	$warning_notes .= 'On Probation';
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
	<link href="../backend_assets/node_modules/Magnific-Popup-master/dist/magnific-popup.css" rel="stylesheet">
	<link href="../backend_assets/dist/css/pages/user-card.css" rel="stylesheet">
	<title><?=PROBATION_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles"  <? if($has_warning_notes == 1){ ?> style="background-color: #d12323 !important;color: #fff;" <? } ?> >
					<!-- ticket 1116 -->
                    
					<div class="col-md-8 align-self-center" style="flex: 0 0 65.0%;max-width: 65.0%;" > <!-- ticket #1534 -->
						<table width="100%" >
							<tr>
								<td width="15%" >
									<h4 class="text-themecolor" <? if($has_warning_notes == 1){ ?> style="color: #fff;" <? } ?> ><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=PROBATION_PAGE_TITLE?> </h4>
									<br />
								</td>
								<td ><b ><?=$LAST_NAME.', '.$FIRST_NAME.' '.$MIDDLE_NAME?></b><br /><br /></td><!-- Ticket # 1715 -->
								<td colspan="3" valign="top" ><?=$warning_notes?></td>
							</tr>
							<!-- Ticket # 1537 -->
							<tr>
								<td rowspan="5" >
									<? if($IMAGE != '') { ?>
										<div class="row el-element-overlay" style="width: 85%;" >
											<div class="card" style="margin-bottom: 0;margin-left: 10px;" >
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
								</td>
								<td width="19%" ><b ><?=STUDENT_ID.':' ?></b></td>
								<td width="29%" ><?=$STUDENT_ID; ?></td> 
								<td width="18%" ></b></td>
								<td width="11%" ></td>
							</tr>
							<!-- Ticket # 1715 -->
							<tr>
								<td ><b  ><?=ENROLLMENT.':' ?></b></td>
								<td ><?=$FIRST_TERM_DATE.' - '.$CAMPUS_PROGRAM.' - '.$STUDENT_STATUS.' - '.$HEADER_CAMPUS_CODE; ?></td>
								<td >&nbsp;&nbsp;<b ><?=DETERMINATION_DATE.':' ?></b></td>
								<td ><?=$DETERMINATION_DATE ?></td>
							</tr>
							<tr>
								<td ><b  ><?=STATUS_DATE.':' ?></b></td>
								<td ><?=$STATUS_DATE ?></td>
								<td >&nbsp;&nbsp;<b ><?=DROP_DATE.':' ?></b></td>
								<td ><?=$DROP_DATE ?></td>
							</tr>
							<!-- Ticket # 1715 -->
							<tr>
								<td ><b  ><?=EXPECTED_GRAD_DATE.':' ?></b></td>
								<td ><?=$EXPECTED_GRAD_DATE ?></td>
								<td >&nbsp;&nbsp;<b ><?=GRADE_DATE.':' ?></b></td>
								<td ><?=$GRADE_DATE ?></td>
							</tr>
							<tr>
								<td ><b  ><?=ORIGINAL_EXPECTED_GRAD_DATE_1.':' ?></b></td>
								<td ><?=$ORIGINAL_EXPECTED_GRAD_DATE ?></td>
								<td >&nbsp;&nbsp;<b ><?=LDA.':' ?></b></td>
								<td ><?=$LDA ?></td>
							</tr>
						</table>
					</div>
					<!-- ticket 1116 -->
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
									<div class="row">
                                        <div class="col-md-12">
											
											<div class="row">
												<div class="col-md-3">
													<div class="form-group m-b-40">
														<input type="text" class="form-control date1 required-entry" id="BEGIN_DATE" name="BEGIN_DATE" value="<?=$BEGIN_DATE?>" >
														<span class="bar"></span>
														<label for="BEGIN_DATE"><?=BEGIN_DATE?></label>
													</div>
												</div>
												
												<div class="col-md-3">
													<div class="form-group m-b-40">
														<input type="text" class="form-control date2" id="END_DATE" name="END_DATE" value="<?=$END_DATE?>" >
														<span class="bar"></span>
														<label for="END_DATE"><?=END_DATE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-3">
													<div class="form-group m-b-40">
														<select name="PK_PROBATION_TYPE" id="PK_PROBATION_TYPE" class="form-control required-entry" >
															<option value=""></option>
															<? $res_dd = $db->Execute("select * from M_PROBATION_TYPE WHERE ACTIVE = '1' ORDER BY PROBATION_TYPE ASC ");
															while (!$res_dd->EOF) { ?>
																<option value="<?=$res_dd->fields['PK_PROBATION_TYPE']?>" <? if($res_dd->fields['PK_PROBATION_TYPE'] == $PK_PROBATION_TYPE) echo 'selected = "selected"';?> ><?=$res_dd->fields['PROBATION_TYPE']?></option>
															<?	$res_dd->MoveNext();
															}	?>
														</select>
														<span class="bar"></span>
														<label for="PK_PROBATION_TYPE"><?=PROBATION_TYPE?></label>
													</div>
												</div>
												
												<div class="col-md-3">
													<div class="form-group m-b-40">
														<select name="PK_PROBATION_LEVEL" id="PK_PROBATION_LEVEL" class="form-control required-entry" >
															<option value=""></option>
															<? $res_dd = $db->Execute("select * from M_PROBATION_LEVEL WHERE ACTIVE='1' ORDER BY SORT_ORDER ASC ");
															while (!$res_dd->EOF) { ?>
																<option value="<?=$res_dd->fields['PK_PROBATION_LEVEL']?>" <? if($res_dd->fields['PK_PROBATION_LEVEL'] == $PK_PROBATION_LEVEL) echo 'selected = "selected"';?> ><?=$res_dd->fields['PROBATION_LEVEL']?></option>
															<?	$res_dd->MoveNext();
															}	?>
														</select>
														<span class="bar"></span>
														<label for="PK_PROBATION_LEVEL"><?=PROBATION_LEVEL?></label>
													</div>
												</div>
												
												<div class="col-md-3">
													<div class="form-group m-b-40">
														<select name="PK_PROBATION_STATUS" id="PK_PROBATION_STATUS" class="form-control required-entry" >
															<option value=""></option>
															<? $res_dd = $db->Execute("select * from M_PROBATION_STATUS WHERE ACTIVE='1' ORDER BY PROBATION_STATUS ASC ");
															while (!$res_dd->EOF) { ?>
																<option value="<?=$res_dd->fields['PK_PROBATION_STATUS']?>" <? if($res_dd->fields['PK_PROBATION_STATUS'] == $PK_PROBATION_STATUS) echo 'selected = "selected"';?> ><?=$res_dd->fields['PROBATION_STATUS']?></option>
															<?	$res_dd->MoveNext();
															}	?>
														</select>
														<span class="bar"></span>
														<label for="PK_PROBATION_STATUS"><?=PROBATION_STATUS?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="text" class="form-control" id="REASON" name="REASON" value="<?=$REASON?>" >
														<span class="bar"></span>
														<label for="REASON"><?=REASON?></label>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<textarea class="form-control" rows="2" id="NOTES" name="NOTES"><?=$NOTES?></textarea>
														<span class="bar"></span>
														<label for="NOTES"><?=NOTES?></label>
													</div>
												</div>
											</div>
										</div>
									 </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='student?id=<?=$_GET['sid']?>&tab=probationTab&eid=<?=$_GET['eid']?>&t=<?=$_GET['t']?>'" ><?=CANCEL?></button>
												
											</div>
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
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript">
	jQuery(document).ready(function($) { 
		jQuery('.date1').datepicker({
			todayHighlight: true,
			orientation: "bottom auto",
			autoclose: true,
		});
		
		$('.date1').datepicker().on('hide', function(e) {
			if(document.getElementById('BEGIN_DATE').value != '') {
				var minDate = $("#BEGIN_DATE").val();
				$('#END_DATE').datepicker('setStartDate', minDate);
				
				document.getElementById('END_DATE').focus();
				$("#BEGIN_DATE").parent().addClass("focused")
			} else
				$("#BEGIN_DATE").parent().removeClass("focused")
		});
		
		jQuery('.date2').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
		
		<? if($BEGIN_DATE != ''){ ?>
			var minDate = $("#BEGIN_DATE").val();
			$('#END_DATE').datepicker('setStartDate', minDate);
		<? } ?>
	});
	</script>
	
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');
	</script>

	<script src="../backend_assets/node_modules/Magnific-Popup-master/dist/jquery.magnific-popup.js"></script>
    <script src="../backend_assets/node_modules/Magnific-Popup-master/dist/jquery.magnific-popup-init.js"></script>
</body>

</html>