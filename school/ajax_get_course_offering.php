<? require_once("../global/config.php"); 
require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
}

$PK_COURSE		= $_REQUEST['val'];
$id				= $_REQUEST['id'];
$PK_TERM_MASTER	= $_REQUEST['PK_TERM_MASTER'];

$multiple	= $_REQUEST['multiple'];
$show_more_detail	= $_REQUEST['show_more_detail'];
$PK_COURSE_OFFERING = $_REQUEST['PK_COURSE_OFFERING'];

if($multiple == 1)
	$name = "PK_COURSE_OFFERING[]";
else
	$name = "PK_COURSE_OFFERING";
	
if($id != '')
	$id = "PK_COURSE_OFFERING_".$id;
else
	$id = "PK_COURSE_OFFERING";
	
if($_REQUEST['page'] == "tuition")
	$id = "PK_COURSE_OFFERING";

$term_cond = "";
if($PK_TERM_MASTER != '')
	$term_cond = " AND S_COURSE_OFFERING.PK_TERM_MASTER IN ($PK_TERM_MASTER) "; //Ticket # 1635
	
/* Ticket # 1898  */
$camp_cond_cond = "";
if($campus != '')
	$camp_cond_cond = " AND S_COURSE_OFFERING.PK_CAMPUS IN ($campus) ";
	
$UNION = "";
if($_REQUEST['page'] == "tuition" && !empty($PK_COURSE_OFFERING)) {
	$PK_COURSE_OFFERING1 = implode(",", $PK_COURSE_OFFERING);
	$UNION = " UNION select PK_COURSE_OFFERING, COURSE_CODE, S_TERM_MASTER.BEGIN_DATE, IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') AS TERM_BEGIN_DATE, SESSION_NO, SESSION, TRANSCRIPT_CODE, COURSE_DESCRIPTION from S_COURSE, S_COURSE_OFFERING LEFT JOIN M_SESSION on M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER WHERE S_COURSE_OFFERING.PK_COURSE_OFFERING IN ($PK_COURSE_OFFERING1) AND S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE ";
	$name = "PK_COURSE_OFFERING[]";
}
/* Ticket # 1898  */

?>
<?php
if($_REQUEST['page']=='student_invoice'){ 
?>
<select id="PK_COURSE_OFFERING" name="PK_COURSE_OFFERING[]" multiple class="form-control course_offering" onchange="clear_search()" >
<?php 
}
elseif($_REQUEST['page']=='add_course_stud'){  // DIAM - 79
	?>
	<select id="PK_COURSE_OFFERING" name="PK_COURSE_OFFERING[]" multiple class="form-control" >
	<?php 
}
else{ ?>
<select id="<?=$id?>" name="<?=$name?>" <?php if($_REQUEST['page'] == "tuition") echo "multiple"; ?> class="form-control course_offering" onchange="get_course_offering_session(this.value,'<?=$_REQUEST['id']?>')" >
<?php } if($_REQUEST['page'] != "tuition" || $_REQUEST['page'] != "add_course_stud") { ?>
		<!-- <option value="0">ALL</option> -->
		<option value=""></option>
	<? } ?>
	
	<? $res_type = $db->Execute("SELECT * FROM (
		select PK_COURSE_OFFERING, COURSE_CODE, S_TERM_MASTER.BEGIN_DATE, IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') AS TERM_BEGIN_DATE, SESSION_NO, SESSION, TRANSCRIPT_CODE, COURSE_DESCRIPTION from S_COURSE, S_COURSE_OFFERING LEFT JOIN M_SESSION on M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER WHERE S_COURSE_OFFERING.ACTIVE = 1 AND S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.PK_COURSE IN ($PK_COURSE) AND S_COURSE_OFFERING.ACTIVE = 1 AND S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE  $term_cond $camp_cond_cond 
		$UNION 
		) AS TEMP 
		ORDER BY BEGIN_DATE DESC, COURSE_CODE ASC, SESSION ASC, SESSION_NO ASC "); //Ticket # 1898
	while (!$res_type->EOF) { 
		$flag = 0;
		if($_REQUEST['page'] == "tuition"){ 
			$PK_COURSE_OFFERING_ARR = $_REQUEST['PK_COURSE_OFFERING'];
			if(in_array($res_type->fields['PK_COURSE_OFFERING'], $PK_COURSE_OFFERING_ARR))
				$flag = 1;
		} ?>
		<option value="<?=$res_type->fields['PK_COURSE_OFFERING'] ?>" <? if($res_type->fields['PK_COURSE_OFFERING'] == $PK_COURSE_OFFERING || $flag == 1) echo "selected"; ?> >
			<? echo $res_type->fields['COURSE_CODE']." (".substr($res_type->fields['SESSION'],0,1)."-".$res_type->fields['SESSION_NO'].") ".$res_type->fields['TRANSCRIPT_CODE'].' - '.$res_type->fields['COURSE_DESCRIPTION']." - ".$res_type->fields['TERM_BEGIN_DATE'];
			if($_REQUEST['show_more_detail'] == 1){
				$res_count = $db->Execute("select PK_STUDENT_COURSE from S_STUDENT_COURSE WHERE PK_COURSE_OFFERING = '".$res_type->fields['PK_COURSE_OFFERING']."' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
				
				//echo " (".$res_type->fields['SESSION']." - ".$res_type->fields['SESSION_NO'].") - ".$res_count->RecordCount();
				echo " - ".$res_count->RecordCount();
			} ?>
		</option>
	<?	$res_type->MoveNext();
	} ?>
</select>