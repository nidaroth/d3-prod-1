<? require_once("../global/config.php");
require_once("../school/function_calc_student_grade.php");
require_once("../language/common.php");
require_once("../language/dashboard.php");
require_once("../language/instructor_grade_book_entry.php");

//echo "<pre>";print_r($_SESSION);exit;
if ($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || $_SESSION['PK_ROLES'] != 3) {
	header("location:../index");
	exit;
}

if (!empty($_POST)) {
	//echo "<pre>";print_r($_POST);exit;
	$CREATED_ON_HISTROY = date("Y-m-d H:i:s"); // DIAM-785
	$i = 0;
	foreach ($_POST['PK_STUDENT_GRADE'] as $PK_STUDENT_GRADE) {
		$STUDENT_GRADE = array();
		$STUDENT_GRADE['POINTS'] = $_POST['GRADE_INPUT_POINTS'][$i];
		db_perform('S_STUDENT_GRADE', $STUDENT_GRADE, 'update', " PK_STUDENT_GRADE = '$PK_STUDENT_GRADE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");


		//To update S_STUDENT_GRADE history start	27 june		
		$res_SSG = $db->Execute("SELECT PK_STUDENT_ENROLLMENT,PK_STUDENT_MASTER,PK_COURSE_OFFERING_GRADE FROM `S_STUDENT_GRADE` WHERE `PK_STUDENT_GRADE` = '$PK_STUDENT_GRADE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");

		$STUDENT_GRADE['PK_STUDENT_GRADE'] = $PK_STUDENT_GRADE;
		$STUDENT_GRADE['PK_COURSE_OFFERING_GRADE']  = $res_SSG->fields['PK_COURSE_OFFERING_GRADE'];
		$STUDENT_GRADE['PK_COURSE_OFFERING']		= $_POST['PK_COURSE_OFFERING']; //$PK_COURSE_OFFERING;
		$STUDENT_GRADE['PK_STUDENT_ENROLLMENT'] 	= $res_SSG->fields['PK_STUDENT_ENROLLMENT'];
		$STUDENT_GRADE['PK_STUDENT_MASTER'] 	 	= $res_SSG->fields['PK_STUDENT_MASTER'];
		$STUDENT_GRADE['PK_ACCOUNT'] = $_SESSION['PK_ACCOUNT'];
		$STUDENT_GRADE['CREATED_ON']  			= $CREATED_ON_HISTROY;
		$STUDENT_GRADE['EDITED_BY'] = $_SESSION['PK_USER'];
		db_perform('S_STUDENT_GRADE_HISTROY', $STUDENT_GRADE, 'insert');
		//To update S_STUDENT_GRADE history end 27 june

		$i++;
	}

	$PK_COURSE_OFFERING = $_POST['PK_COURSE_OFFERING'];

	$COND = "";
	if ($_POST['VIEW'] == 2)
		$COND = " AND PK_STUDENT_ENROLLMENT = '$_POST[PK_STUDENT_ENROLLMENT]' ";

	$res_stu = $db->Execute("select PK_STUDENT_COURSE, PK_STUDENT_MASTER FROM S_STUDENT_COURSE WHERE PK_STUDENT_MASTER = S_STUDENT_COURSE.PK_STUDENT_MASTER AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' $COND AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	while (!$res_stu->EOF) {
		$PK_STUDENT_COURSE = $res_stu->fields['PK_STUDENT_COURSE'];
		$PK_STUDENT_MASTER = $res_stu->fields['PK_STUDENT_MASTER'];

		$PK_STUDENT_GRADE 	= '';
		$POINTS 			= '';
		$res_grade = $db->Execute("SELECT PK_COURSE_OFFERING_GRADE FROM S_COURSE_OFFERING_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ORDER BY PK_COURSE_OFFERING_GRADE ASC ");
		while (!$res_grade->EOF) {
			$PK_COURSE_OFFERING_GRADE = $res_grade->fields['PK_COURSE_OFFERING_GRADE'];
			$res_stu_grade = $db->Execute("SELECT PK_STUDENT_GRADE,POINTS FROM S_STUDENT_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_GRADE = '$PK_COURSE_OFFERING_GRADE' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ");
			if ($res_stu_grade->fields['POINTS'] != '') {
				if ($PK_STUDENT_GRADE != '')
					$PK_STUDENT_GRADE .= ',';

				$PK_STUDENT_GRADE .= $res_stu_grade->fields['PK_STUDENT_GRADE'];

				if ($POINTS != '')
					$POINTS .= ',';

				$POINTS .= $res_stu_grade->fields['POINTS'];
			}

			$res_grade->MoveNext();
		}

		calc_stu_grade($POINTS, $PK_STUDENT_GRADE, $PK_STUDENT_COURSE, $PK_STUDENT_MASTER, 1);

		$res_stu->MoveNext();
	}

	header("location:grade_book_entry?tm=" . $_POST['PK_TERM_MASTER'] . "&co=" . $_POST['PK_COURSE_OFFERING'] . "&view=" . $_POST['VIEW'] . "&cog=" . $_POST['PK_COURSE_OFFERING_GRADE'] . "&eid=" . $_POST['PK_STUDENT_ENROLLMENT']);
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
	<style>
		.table th,
		.table td {
			padding: 7px;
		}

		.table th,
		.table td {
			border: 1px solid #c4c4c4;
		}
	</style>
	<title><?= MNU_GRADE_BOOK_ENTRY ?> | <?= $title ?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
	<? require_once("pre_load.php"); ?>
	<div id="main-wrapper">
		<? require_once("menu.php"); ?>
		<div class="page-wrapper">
			<div class="container-fluid">
				<div class="row page-titles">
					<div class="col-md-5 align-self-center">
						<h4 class="text-themecolor"><?= MNU_GRADE_BOOK_ENTRY ?></h4>
					</div>
				</div>
				<div class="card-group">
					<div class="card">
						<div class="card-body">
							<div class="row">
								<form class="floating-labels w-100 m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off">
									<div class="row">
										<div class="col-sm-3 pt-25">
											<div class="row">
												<div class="col-12 form-group">
													<select id="PK_TERM_MASTER" name="PK_TERM_MASTER" class="form-control required-entry" onchange="get_course_offering(this.value)">
														<option value=""></option>
														<? $res_type = $db->Execute("select S_TERM_MASTER.PK_TERM_MASTER,IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00', DATE_FORMAT( S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') AS TERM_BEGIN_DATE, IF(S_TERM_MASTER.END_DATE != '0000-00-00', DATE_FORMAT(S_TERM_MASTER.END_DATE,'%m/%d/%Y'),'') AS TERM_END_DATE, TERM_DESCRIPTION from S_COURSE_OFFERING  LEFT JOIN S_COURSE_OFFERING_ASSISTANT ON S_COURSE_OFFERING_ASSISTANT.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING, S_TERM_MASTER WHERE S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND (INSTRUCTOR = '$_SESSION[PK_EMPLOYEE_MASTER]' OR S_COURSE_OFFERING_ASSISTANT.ASSISTANT = '$_SESSION[PK_EMPLOYEE_MASTER]') AND  S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER GROUP BY S_TERM_MASTER.PK_TERM_MASTER ORDER BY BEGIN_DATE DESC");
														while (!$res_type->EOF) { ?>
															<option value="<?= $res_type->fields['PK_TERM_MASTER'] ?>" <? if ($_GET['tm'] == $res_type->fields['PK_TERM_MASTER']) echo "selected"; ?>><?= $res_type->fields['TERM_BEGIN_DATE'] . ' - ' . $res_type->fields['TERM_END_DATE'] . ' - ' . $res_type->fields['TERM_DESCRIPTION'] ?></option>
														<? $res_type->MoveNext();
														} ?>
													</select>
													<span class="bar"></span>
													<label for="PK_TERM_MASTER"><?= SELECT_TERM ?></label>
												</div>
												<div class="col-12 form-group" id="PK_COURSE_OFFERING_LABEL">
													<div id="PK_COURSE_OFFERING_DIV">
														<? $_REQUEST['val'] = $_GET['tm'];
														$_REQUEST['def'] 	= $_GET['co'];
														include("ajax_get_course_offering.php"); ?>
													</div>
													<span class="bar"></span>
													<label for="PK_COURSE_OFFERING"><?= SELECT_COURSE_OFFERING ?></label>
												</div>
												<div class="col-12 form-group text-right">
													<button type="button" onclick="get_grade_book_form(1)" class="btn waves-effect waves-light btn-info"><?= SHOW ?></button>
												</div>
											</div>
											<div id="course_details">
											</div>
										</div>
										<div class="col-sm-6 pt-25 theme-v-border">
											<!-- DIAM-1527-->
											<? if (isset($_POST) && !empty($_GET['tm'])) { ?>
												<div class="alert alert-success alert-dismissible fade show" role="alert">
													<strong>Successfully Saved.</strong>
													<button type="button" class="close" data-dismiss="alert" aria-label="Close">
														<span aria-hidden="true">&times;</span>
													</button>
												</div>
												<!-- DIAM-1527-->
											<? } ?>
											<div id="FORM_DIV">
											</div>
										</div>
									</div>
									<input type="hidden" name="COMPLETE" id="COMPLETE" value="1" />
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

	<script type="text/javascript">
		jQuery(document).ready(function($) {
			<? if ($_GET['tm'] != '') { ?>
				get_grade_book_form('<?= $_GET['view'] ?>')
			<? } ?>

			<? if ($_GET['cog'] != '') { ?>
				get_student_for_grade_book_input_by_assignment()
			<? } ?>

			<? if ($_GET['eid'] != '') { ?>
				get_assignment_for_grade_book_input_by_student()
			<? } ?>
		});

		function get_course_offering(val) {
			jQuery(document).ready(function($) {
				var data = 'val=' + val;
				var value = $.ajax({
					url: "ajax_get_course_offering",
					type: "POST",
					data: data,
					async: false,
					cache: false,
					success: function(data) {
						//alert(data)
						document.getElementById('PK_COURSE_OFFERING_DIV').innerHTML = data;
						document.getElementById('PK_COURSE_OFFERING_LABEL').classList.add("focused");

					}
				}).responseText;
			});
		}

		function get_grade_book_form(type) {
			jQuery(document).ready(function($) {
				if (document.getElementById('PK_COURSE_OFFERING').value != '') {
					var data = 'val=' + document.getElementById('PK_COURSE_OFFERING').value + '&type=' + type + '&cog=<?= $_GET['cog'] ?>&eid=<?= $_GET['eid'] ?>';
					//alert(data)
					var value = $.ajax({
						url: "ajax_get_grade_book_input_form",
						type: "POST",
						data: data,
						async: false,
						cache: false,
						success: function(data) {
							//alert(data)
							document.getElementById('FORM_DIV').innerHTML = data;

							$('.floating-labels .form-control').on('focus blur', function(e) {
								$(this).parents('.form-group').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
							}).trigger('blur');


						}
					}).responseText;
				}
			});
		}

		function get_student_for_grade_book_input_by_assignment() {
			jQuery(document).ready(function($) {
				if (document.getElementById('PK_COURSE_OFFERING').value != '' && document.getElementById('PK_COURSE_OFFERING_GRADE').value != '') { //DIAM-785
					var data = 'co=' + document.getElementById('PK_COURSE_OFFERING').value + '&cog=' + document.getElementById('PK_COURSE_OFFERING_GRADE').value;
					//alert(data)
					var value = $.ajax({
						url: "ajax_get_student_for_grade_book_input_by_assignment",
						type: "POST",
						data: data,
						async: false,
						cache: false,
						success: function(data) {
							//alert(data)
							document.getElementById('STUDENT_DIV').innerHTML = data;
						}
					}).responseText;
				}
			});
		}

		function get_assignment_for_grade_book_input_by_student() {
			jQuery(document).ready(function($) {
				if (document.getElementById('PK_COURSE_OFFERING').value != '' && document.getElementById('PK_STUDENT_ENROLLMENT').value != '') {
					var data = 'co=' + document.getElementById('PK_COURSE_OFFERING').value + '&eid=' + document.getElementById('PK_STUDENT_ENROLLMENT').value;
					//alert(data)
					var value = $.ajax({
						url: "ajax_get_assignment_for_grade_book_input_by_student",
						type: "POST",
						data: data,
						async: false,
						cache: false,
						success: function(data) {
							//alert(data)
							document.getElementById('STUDENT_DIV').innerHTML = data;

							var PK_STUDENT_GRADE = document.getElementById('TEMP_PK_STUDENT_GRADE').value
							var PK_STUDENT_COURSE = document.getElementById('TEMP_PK_STUDENT_COURSE').value
							var PK_STUDENT_MASTER = document.getElementById('TEMP_PK_STUDENT_MASTER').value

							calc_grade(PK_STUDENT_GRADE, PK_STUDENT_COURSE, PK_STUDENT_MASTER, 0)
						}
					}).responseText;
				}
			});
		}

		function get_schedule(val) {
			document.getElementById('FORM_DIV').innerHTML = ''
		}

		/* Ticket #1505 */
		function recalculate() {
			if (document.getElementById('BY_STUDENT').checked == true) {
				var PK_STUDENT_GRADE = document.getElementById('TEMP_PK_STUDENT_GRADE').value
				var PK_STUDENT_COURSE = document.getElementById('TEMP_PK_STUDENT_COURSE').value
				var PK_STUDENT_MASTER = document.getElementById('TEMP_PK_STUDENT_MASTER').value

				calc_grade(PK_STUDENT_GRADE, PK_STUDENT_COURSE, PK_STUDENT_MASTER, 0)
			} else {
				var PK_STUDENT_GRADE = document.getElementsByName('PK_STUDENT_GRADE[]');
				for (var i = 0; i < PK_STUDENT_GRADE.length; i++) {
					var id = PK_STUDENT_GRADE[i].value
					var point = document.getElementById('TEMP_ASS_POINT_' + id).value
					calc_per(id, point)
				}
			}
		}
		/* Ticket #1505 */

		function calc_per(id, assignment_point) {
			var input_point = document.getElementById('GRADE_INPUT_POINTS_' + id).value;
			if (input_point == '') input_point = '0'; //DIAM-785

			if (document.getElementById('point_div_' + id))
				document.getElementById('point_div_' + id).innerHTML = input_point + ' / ' + assignment_point

			if (document.getElementById('per_div_' + id))
				document.getElementById('per_div_' + id).innerHTML = (parseFloat(input_point) / parseFloat(assignment_point) * 100) + ' %'
		}

		function calc_grade(sg, sc, sm, post_grade) {
			jQuery(document).ready(function($) {
				$("#delete_grade_input_table").remove();

				var stu_points = document.getElementsByClassName('stu_points_' + sm);
				var stu_grade = document.getElementsByClassName('stu_grade_' + sm);

				var points = '';
				var pk_grade = '';
				for (var i = 0; i < stu_points.length; i++) {
					if (stu_points[i].value != '') {
						if (points != '') {
							points += ',';
							pk_grade += ',';
						}

						points += stu_points[i].value;
						pk_grade += stu_grade[i].value;

					}
				}

				var data = 'points=' + points + '&pk_grade=' + pk_grade + '&sc=' + sc + '&sm=' + sm;
				var value = $.ajax({
					url: "../school/ajax_calc_student_grade",
					type: "POST",
					data: data,
					async: false,
					cache: false,
					success: function(data) {
						//alert(data)
						data = data.split('|||');
						document.getElementById('CURRENT_TOTAL_' + sc).innerHTML = '<?= TOTAL_POINTS_FOR_CLASS ?>: ' + data[0];
					}
				}).responseText;
			});
		}
		//DIAM-785	
		function loader(id) {
			document.getElementById(id).innerHTML = '<tr><td><div style="position: inherit;margin-top: 0;height: 46px;"><div class="datagrid-mask" style="display:block;top:74%;"></div><div class="datagrid-mask-msg" style="display:block;left:44%;top:80%"> Please wait...</div></div></td></tr>';

		}

		function confirm_restore_grade_book_entry() {
			jQuery(document).ready(function($) {
				$("#restore_Modal_grade_book_entry").modal();
			});
		}

		function RestoreGradeBook(val, TABVAL) {

			if (document.getElementById("RESTORE_GRADE_BOOK_ENTRY").value == '') {
				document.getElementById("RESTORE_GRADE_BOOK_ENTRY_ERR").style.display = 'block';
			} else {
				loader('STUDENT_DIV');

				var VIEW_TYPE = document.querySelector('input[name="VIEW"]:checked').value;
				var PK_STUDENT_ENROLLMENT = "";
				var PK_COURSE_OFFERING_GRADE = "";
				if (typeof $('#PK_STUDENT_ENROLLMENT').val() !== 'undefined') {
					PK_STUDENT_ENROLLMENT = $('#PK_STUDENT_ENROLLMENT').val();
				}

				if (typeof $('#PK_COURSE_OFFERING_GRADE').val() !== 'undefined') {
					PK_COURSE_OFFERING_GRADE = $('#PK_COURSE_OFFERING_GRADE').val()
				}

				var last_date = document.getElementById("RESTORE_GRADE_BOOK_ENTRY").value;
				jQuery(document).ready(function($) {

					var data = 'id=' + val + '&last_date=' + last_date + '&view=' + VIEW_TYPE + '&co=' + $('#PK_COURSE_OFFERING').val() + '&eid=' + PK_STUDENT_ENROLLMENT + '&cog=' + PK_COURSE_OFFERING_GRADE;
					var value = $.ajax({
						url: "ajax_get_grade_book_entry_history",
						type: "POST",
						data: data,
						async: false,
						cache: false,
						success: function(data) {
							document.getElementById('STUDENT_DIV').innerHTML = data; //gradeBookEntryData
							jQuery('#restore_Modal_grade_book_entry').modal('hide');

						}
					}).responseText;

				});
			}

		}
		//DIAM-785
	</script>
</body>

</html>