<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2 && $_SESSION['PK_ROLES'] != 3)){ 
	header("location:../index");
	exit;
}

$DETAIL_TYPE 			= $_REQUEST['detail_type'];
$DETAIL_ID				= $_REQUEST['detail_id'];
$SID					= $_REQUEST['sid'];
$EID					= $_REQUEST['eid'];
$DISBURSEMENT_DETAIL	= $_REQUEST['DISBURSEMENT_DETAIL'];

if($DETAIL_TYPE == 1){ ?>
	<select id="DISBURSEMENT_DETAIL_<?=$DETAIL_ID?>" name="DISBURSEMENT_DETAIL_<?=$DETAIL_ID?>" class="form-control" style="width:200px;" <?=$_REQUEST['disable_disp']?> >
		<option value="" ></option>
		<? $res_type = $db->Execute("select PK_TERM_MASTER,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_TERM_MASTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by BEGIN_DATE DESC");
		while (!$res_type->EOF) { ?>
			<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" <? if($res_type->fields['PK_TERM_MASTER'] == $DISBURSEMENT_DETAIL) echo "selected"; ?> ><?=$res_type->fields['BEGIN_DATE_1']?></option>
		<?	$res_type->MoveNext();
		} ?>
	</select>
<? } else if($DETAIL_TYPE == 2){ ?>
	<select id="DISBURSEMENT_DETAIL_<?=$DETAIL_ID?>" name="DISBURSEMENT_DETAIL_<?=$DETAIL_ID?>" class="form-control" style="width:200px;" <?=$_REQUEST['disable_disp']?> >
		<option value="" ></option>
		<? $res_type = $db->Execute("select PK_STUDENT_COURSE,COURSE_CODE,DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y') AS BEGIN_DATE_1 from S_STUDENT_COURSE, S_COURSE, S_COURSE_OFFERING, S_TERM_MASTER WHERE S_STUDENT_COURSE.ACTIVE = 1 AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = '$EID' AND S_STUDENT_COURSE.PK_STUDENT_MASTER = '$SID' AND S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE AND S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER");
		while (!$res_type->EOF) { ?>
			<option value="<?=$res_type->fields['PK_STUDENT_COURSE']?>" <? if($res_type->fields['PK_STUDENT_COURSE'] == $DISBURSEMENT_DETAIL) echo "selected"; ?> ><?=$res_type->fields['COURSE_CODE'].' - '.$res_type->fields['BEGIN_DATE_1']?></option>
		<?	$res_type->MoveNext();
		} ?>
	</select>
<? } else if($DETAIL_TYPE == 3){ ?>
	<select id="DISBURSEMENT_DETAIL_<?=$DETAIL_ID?>" name="DISBURSEMENT_DETAIL_<?=$DETAIL_ID?>" class="form-control" style="width:200px;" <?=$_REQUEST['disable_disp']?> >
		<option value="" ></option>
		<? $res_type = $db->Execute("select PK_AR_FEE_TYPE,AR_FEE_TYPE from M_AR_FEE_TYPE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		while (!$res_type->EOF) { ?>
			<option value="<?=$res_type->fields['PK_AR_FEE_TYPE']?>" <? if($res_type->fields['PK_AR_FEE_TYPE'] == $DISBURSEMENT_DETAIL) echo "selected"; ?> ><?=$res_type->fields['AR_FEE_TYPE'] ?></option>
		<?	$res_type->MoveNext();
		} ?>
	</select>
<? } else if($DETAIL_TYPE == 4){ ?>
	<select id="DISBURSEMENT_DETAIL_<?=$DETAIL_ID?>" name="DISBURSEMENT_DETAIL_<?=$DETAIL_ID?>" class="form-control" style="width:200px;" <?=$_REQUEST['disable_disp']?> >
		<option value="" ></option>
		<? $res_type = $db->Execute("select PK_AR_PAYMENT_TYPE,AR_PAYMENT_TYPE from M_AR_PAYMENT_TYPE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		while (!$res_type->EOF) { ?>
			<option value="<?=$res_type->fields['PK_AR_PAYMENT_TYPE']?>" <? if($res_type->fields['PK_AR_PAYMENT_TYPE'] == $DISBURSEMENT_DETAIL) echo "selected"; ?> ><?=$res_type->fields['AR_PAYMENT_TYPE'] ?></option>
		<?	$res_type->MoveNext();
		} ?>
	</select>
<? } else if($DETAIL_TYPE == 5){ ?>
	<select id="DISBURSEMENT_DETAIL_<?=$DETAIL_ID?>" name="DISBURSEMENT_DETAIL_<?=$DETAIL_ID?>" class="form-control" style="width:200px;" <?=$_REQUEST['disable_disp']?> >
		<option value="" ></option>
		<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
		while (!$res_type->EOF) { ?>
			<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" <? if($res_type->fields['PK_CAMPUS_PROGRAM'] == $DISBURSEMENT_DETAIL) echo "selected"; ?> ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
		<?	$res_type->MoveNext();
		} ?>
	</select>
<? } else if($DETAIL_TYPE == 6){ ?>
	<select id="DISBURSEMENT_DETAIL_<?=$DETAIL_ID?>" name="DISBURSEMENT_DETAIL_<?=$DETAIL_ID?>" class="form-control" style="width:200px;" <?=$_REQUEST['disable_disp']?> >
		<option value="" ></option>
	</select>
<? } ?>