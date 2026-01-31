<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/instructor_grade_book_setup.php");
require_once("../language/course_offering.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
}

$PK_COURSE_OFFERING = $_REQUEST['val']; 
$PK_TERM_MASTER 	= $_REQUEST['tid']; 
$result1 = $db->Execute("SELECT PK_COURSE FROM S_COURSE_OFFERING WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ");
$PK_COURSE 	= $result1->fields['PK_COURSE']; 

?>
<div class="row">
	<div class="col-md-6 align-self-center">
	</div>  
	<div class="col-md-6 align-self-center text-right">
		<div class="d-flex justify-content-end align-items-center m-b-15"> <!--DIAM-785 -->
			<!--27 June -->
			<?
			$result1 = $db->Execute("SELECT PK_COURSE_OFFERING_GRADE FROM S_COURSE_OFFERING_GRADE_HISTROY WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ORDER BY GRADE_ORDER ASC "); //Ticket #1290
			$reccnt = $result1->RecordCount();
			if ($reccnt > 0) {
			?>														
			<a href="javascript:void(0)" onclick="confirm_restore_grade_book_setup()" class="btn waves-effect waves-light btn-info m-l-15"><?= RESTORE_GRADE_BOOK_SETUP ?></a>&nbsp;&nbsp;
			<?php } ?>
			<!--27 June-->
			<a href="javascript:void(0)" onclick="import_grade(<?=$PK_COURSE?>)" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> <?=IMPORT_GRADE?></a>&nbsp;&nbsp;
			<a href="javascript:void(0)" onclick="add_grade()" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> <?=ADD?></a>&nbsp;&nbsp;
		</div>
	</div>
</div>
<div class="table-responsive">
	<table  class="table table-bordered" id="grade_table" >
		<thead>
			<tr>
				<th ><?=COLUMN?></th><!-- Ticket # 1437 -->
				<th ><?=CODE?></th>
				<th ><?=DESCRIPTION?></th>
				<th ><?=TYPE?></th>
				<th style="width:12%;"><?=DATE?></th>
				<!--<th ><?=PERIOD?></th>-->
				<th ><?=POINTS?></th>
				<th ><?=WEIGHT?></th>
				<th ><?=WEIGHTED_POINTS?></th>
				<th ><?=DELETE?></th>
			</tr>
		</thead>
		<tbody id="grad_list"><!--27 June -->
			<? $grade_cunt = 1; 
			$result1 = $db->Execute("SELECT PK_COURSE_OFFERING_GRADE FROM S_COURSE_OFFERING_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ");
			$reccnt = $result1->RecordCount();
			while (!$result1->EOF) {
				$_REQUEST['PK_COURSE_OFFERING_GRADE'] 	= $result1->fields['PK_COURSE_OFFERING_GRADE'];
				$_REQUEST['grade_cunt']  				= $grade_cunt;
				
				include('../school/ajax_course_offering_grade.php');
				
				$grade_cunt++;	
				$result1->MoveNext();
			} 
			$result1 = $db->Execute("SELECT SUM(WEIGHTED_POINTS) AS WEIGHTED_POINTS FROM S_COURSE_OFFERING_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ");?>
		</tbody>
		<tfoot>
			<tr>
				<td></td><!-- Ticket # 1437 -->
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<!--<td></td>-->
				<td></td>
				<td><?=TOTAL?></td>
				<td><div id="WEIGHTED_POINTS_TOTAL" style="text-align:right;" ><?=$result1->fields['WEIGHTED_POINTS']?></div></td>
				<td></td>
			</tr>
		</tfoot>
	</table>
</div>
<div class="col-12 form-group text-right">
	<button type="submit" class="btn waves-effect waves-light btn-info" id="SAVE_BTN" style="float:right;margin-right:5px;" ><?=SAVE?></button>
</div>
<!--27 June -->
<?php
	function getUser($userId){
	global $db;
	$res_usr_name = $db->Execute("SELECT FIRST_NAME,LAST_NAME FROM S_EMPLOYEE_MASTER,Z_USER WHERE Z_USER.PK_USER = '$userId' AND Z_USER.ID = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER");
	return " ".$res_usr_name->fields['LAST_NAME'].', '.$res_usr_name->fields['FIRST_NAME'];
	}
?>										
<div class="modal" id="restore_Modal_grade_book_setup" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="exampleModalLabel1"><?= RESTORE_GRADE_BOOK_SETUP_LABEL ?></h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<!-- <div class="form-group" id="restore_Modal_grade_message"></div> -->
			<div style="" class="col-sm-4">
				<select id="RESTORE_GRADE_BOOK_SETUP" name="RESTORE_GRADE_BOOK_SETUP" class="form-control" style="width:180px">
					<option value="">Select Latest Date</option>
					<?
					$res_cos = $db->Execute("SELECT PK_COURSE_OFFERING_GRADE_HISTROY_ID,CREATED_ON,CREATED_BY,EDITED_BY FROM S_COURSE_OFFERING_GRADE_HISTROY WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  GROUP BY CREATED_ON ORDER BY CREATED_ON DESC LIMIT 5");
					while (!$res_cos->EOF) {   
																
							if($res_cos->fields['CREATED_BY']!=0){		
								$usr =  getUser($res_cos->fields['CREATED_BY']);									
							}else if($res_cos->fields['EDITED_BY']!=0){									
								$usr =  getUser($res_cos->fields['EDITED_BY']);
								
							}else{
								$usr = "No User";
							}
							?>										
						<option value="<?= $res_cos->fields['CREATED_ON'] ?>"><?= $res_cos->fields['CREATED_ON'] ?>  <?=$usr?></option>
					<? $res_cos->MoveNext();
					}  
					?>
				</select>
				<div class="validation-advice" id="RESTORE_GRADE_BOOK_SETUP_ERR" style="display:none;">This is a required field.</div>
			</div>
			</div>
			<div class="modal-footer">
				<button type="button" onclick="RestoreGradeBook(<?= $PK_COURSE_OFFERING ?>,'GB_SETUP')" class="btn waves-effect waves-light btn-info"><?= YES ?></button>
				<button type="button" class="btn waves-effect waves-light btn-dark" onclick="jQuery('#restore_Modal_grade_book_setup').modal('hide');"><?= NO ?></button>
			</div>
		</div>
	</div>
</div>
<!--27 June -->
