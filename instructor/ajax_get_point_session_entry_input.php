<? require_once("../global/config.php"); 
require_once("../language/instructor_points_session_entry.php");
require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
}

$PK_COURSE_OFFERING 	= $_REQUEST['co'];
$PK_STUDENT_ENROLLMENT 	= $_REQUEST['PK_STUDENT_ENROLLMENT'];
$pgbc 					= $_REQUEST['pgbc'];  
$type 					= $_REQUEST['type'];  
if($type == 1 || $type == 3)
	$cond11 = " AND S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_CODE = '$pgbc' ";
else if($type == 2)
	$cond11 = " AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ";

if($pgbc > 0 || $PK_STUDENT_ENROLLMENT > 0){ ?>
<div class="table-responsive"  >
	<table  class="table table-hover" id="program_grade_book_table" >
		<thead style="position: sticky;top: 0" >
			<tr>
				<? if($type == 1 || $type == 3){ ?>
				<th class="sticky_header" scope="col" ><?=STUDENT?></th>
				<? } ?>
				<th class="sticky_header" scope="col" ><?=GRADE_BOOK_CODE?></th>
				<th class="sticky_header" scope="col" ><?=GRADE_BOOK_DESCRIPTION?></th>
				<th class="sticky_header" scope="col" ><?=GRADE_BOOK_TYPE?></th>
				<? //if($type == 2){ ?>
				<th class="sticky_header" scope="col" ><?=COMPLETED_DATE?></th>
				<? //} ?>
				<th class="sticky_header" scope="col" ><div style="width:80px;"><?=SESSION_REQUIRED?></div></th>
				<th class="sticky_header" scope="col" ><div style="width:80px;"><?=SESSION_COMPLETED?></div></th>
				<th class="sticky_header" scope="col" ><div style="width:80px;"><?=HOURS_REQUIRED?></div></th>
				<th class="sticky_header" scope="col" ><div style="width:80px;"><?=HOURS_COMPLETED?></div></th>
				<th class="sticky_header" scope="col" ><div style="width:80px;"><?=POINTS_REQUIRED?></div></th>
				<th class="sticky_header" scope="col" ><div style="width:80px;"><?=POINTS_EARNED?></div></th>
			</tr>
		</thead>
		<tbody>
			<? $res_grade = $db->Execute("select PK_STUDENT_PROGRAM_GRADE_BOOK_INPUT, CODE, GRADE_BOOK_TYPE, COMPLETED_DATE, S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.DESCRIPTION, SESSION_REQUIRED, SESSION_COMPLETED, HOUR_REQUIRED, HOUR_COMPLETED, POINTS_REQUIRED, POINTS_COMPLETED, PK_STUDENT_ENROLLMENT, CONCAT(LAST_NAME,', ',FIRST_NAME) as NAME from S_STUDENT_MASTER, S_STUDENT_PROGRAM_GRADE_BOOK_INPUT LEFT JOIN M_GRADE_BOOK_CODE ON M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_CODE LEFT JOIN M_GRADE_BOOK_TYPE ON M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE WHERE S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_STUDENT_MASTER $cond11 ORDER BY CODE ASC, COMPLETED_DATE ASC ");
			
			$TOT_SESSION_REQUIRED 	= 0;
			$TOT_SESSION_COMPLETED 	= 0;
			$TOT_HOUR_REQUIRED 		= 0;
			$TOT_HOUR_COMPLETED 	= 0;
			$TOT_POINTS_REQUIRED 	= 0;
			$TOT_POINTS_COMPLETED 	= 0;
			$prog_grade_book_count = 0;
			while (!$res_grade->EOF) { 
				$COMPLETED_DATE 				= $res_grade->fields['COMPLETED_DATE']; 
				$SESSION_COMPLETED 				= $res_grade->fields['SESSION_COMPLETED']; 
				$HOUR_COMPLETED 				= $res_grade->fields['HOUR_COMPLETED']; 
				$POINTS_COMPLETED 				= $res_grade->fields['POINTS_COMPLETED']; 
				$PROGRAM_GRADE_PK_ENROLLMENT 	= $res_grade->fields['PK_STUDENT_ENROLLMENT'];  
				if($COMPLETED_DATE != '0000-00-00' )
					$COMPLETED_DATE = date("m/d/Y",strtotime($COMPLETED_DATE));
				else
					$COMPLETED_DATE = ''; ?>
				<tr id="prog_grade_book_<?=$prog_grade_book_count?>" >
					<? if($type == 1 || $type == 3){ ?>
						<td><?=$res_grade->fields['NAME']?></td>
					<? } ?>
					<td>
						<input type="hidden" name="PROGRAM_GRADE_HID[]" value="<?=$prog_grade_book_count?>" />
						<input type="hidden" name="PK_STUDENT_PROGRAM_GRADE_BOOK_INPUT[]" value="<?=$res_grade->fields['PK_STUDENT_PROGRAM_GRADE_BOOK_INPUT']?>" />
						<input type="hidden" name="GRADE_PK_STUDENT_ENROLLMENT[]" value="<?=$PROGRAM_GRADE_PK_ENROLLMENT?>" /> <!-- Ticket # 1139 -->
						<?=$res_grade->fields['CODE']?>
					</td>
					<td><?=$res_grade->fields['DESCRIPTION']?></td>
					<td><?=$res_grade->fields['GRADE_BOOK_TYPE']?></td>
					<td>
						<? if($type == 2){ ?>
							<input type="text" class="form-control date" placeholder="" name="PROGRAM_GRADE_COMPLETED_DATE[]" id="PROGRAM_GRADE_COMPLETED_DATE_<?=$prog_grade_book_count?>" value="<?=$COMPLETED_DATE?>" style="width:100px;" />
						<? } else { ?>
							<?=$COMPLETED_DATE?>
						
						<? } ?>
					</td>
					<td>
						<input type="text" class="form-control" placeholder="" name="PROGRAM_GRADE_SESSION_REQUIRED[]" id="PROGRAM_GRADE_SESSION_REQUIRED_<?=$prog_grade_book_count?>" value="<?=$res_grade->fields['SESSION_REQUIRED']?>" style="text-align:right;width:80px;" onchange="calc_total()" />
					</td>
					
					<td>
						<input type="text" class="form-control" placeholder="" name="PROGRAM_GRADE_SESSION_COMPLETED[]" id="PROGRAM_GRADE_SESSION_COMPLETED_<?=$prog_grade_book_count?>" value="<?=$SESSION_COMPLETED?>" style="text-align:right;width:80px;" onchange="calc_total()"  />
					</td>
					<td>
						<input type="text" class="form-control" placeholder="" name="PROGRAM_GRADE_HOUR_REQUIRED[]" id="PROGRAM_GRADE_HOUR_REQUIRED_<?=$prog_grade_book_count?>" value="<?=$res_grade->fields['HOUR_REQUIRED']?>" style="text-align:right;width:80px;" readonly onchange="calc_total()"  />
					</td>
					<td>
						<input type="text" class="form-control" placeholder="" name="PROGRAM_GRADE_HOUR_COMPLETED[]" id="PROGRAM_GRADE_HOUR_COMPLETED_<?=$prog_grade_book_count?>" value="<?=$HOUR_COMPLETED?>" style="text-align:right;width:80px;" onchange="calc_total()"  />
					</td>
					<td>
						<input type="text" class="form-control" placeholder="" name="PROGRAM_GRADE_POINTS_REQUIRED[]" id="PROGRAM_GRADE_POINTS_REQUIRED_<?=$prog_grade_book_count?>" value="<?=$res_grade->fields['POINTS_REQUIRED']?>" style="text-align:right;width:80px;" readonly onchange="calc_total()"  />
					</td>
					<td>
						<input type="text" class="form-control" placeholder="" name="PROGRAM_GRADE_POINTS_COMPLETED[]" id="PROGRAM_GRADE_POINTS_COMPLETED_<?=$prog_grade_book_count?>" value="<?=$POINTS_COMPLETED?>" style="text-align:right;width:80px;" onchange="calc_total()"  />
					</td>
				</tr>
			<?	$TOT_SESSION_REQUIRED 	+= $res_grade->fields['SESSION_REQUIRED'];
				$TOT_SESSION_COMPLETED 	+= $SESSION_COMPLETED;
				$TOT_HOUR_REQUIRED 		+= $res_grade->fields['HOUR_REQUIRED'];
				$TOT_HOUR_COMPLETED 	+= $HOUR_COMPLETED;
				$TOT_POINTS_REQUIRED 	+= $res_grade->fields['POINTS_REQUIRED'];
				$TOT_POINTS_COMPLETED 	+= $POINTS_COMPLETED;
				$prog_grade_book_count++;
				$res_grade->MoveNext();
			}  ?>
		</tbody>
		<!-- Ticket # 1139 -->
		<tfoot>
			<tr>
				<td></td>
				<td></td>
				<td></td>
				<? if($type == 1 || $type == 3){ ?>
				<td></td>
				<? } ?>
				<td><div style=";font-weight:bold;"><?=TOTAL?></div></td>
				<td><div id="SESSION_REQUIRED_DIV" style="text-align:right;font-weight:bold;width:80px;" ><?=number_format_value_checker($TOT_SESSION_REQUIRED,2)?></div></td>
				<td><div id="SESSION_COMPLETED_DIV" style="text-align:right;font-weight:bold;width:80px;" ><?=number_format_value_checker($TOT_SESSION_COMPLETED,2)?></div></td>
				<td><div id="HOUR_REQUIRED_DIV" style="text-align:right;font-weight:bold;width:80px;" ><?=number_format_value_checker($TOT_HOUR_REQUIRED,2)?></div></td>
				<td><div id="HOUR_COMPLETED_DIV" style="text-align:right;font-weight:bold;width:80px;" ><?=number_format_value_checker($TOT_HOUR_COMPLETED,2)?></div></td>
				<td><div id="POINTS_REQUIRED_DIV" style="text-align:right;font-weight:bold;width:80px;" ><?=number_format_value_checker($TOT_POINTS_REQUIRED,2)?></div></td>
				<td><div id="POINTS_COMPLETED_DIV" style="text-align:right;font-weight:bold;width:80px;" ><?=number_format_value_checker($TOT_POINTS_COMPLETED,2)?></div></td>
			</tr>
		</tfoot>
		<!-- Ticket # 1139 -->
	</table>
</div>
<div class="col-12 form-group text-right">
	<button type="submit" class="btn waves-effect waves-light btn-info" id="SAVE_BTN" style="float:right;margin-right:5px;" ><?=SAVE?></button>
	<button type="button" onclick="add_data('<?=$type?>')" class="btn waves-effect waves-light btn-info" id="SAVE_BTN" style="float:right;margin-right:5px;" ><?=ADD?></button>
</div>
<? } ?>