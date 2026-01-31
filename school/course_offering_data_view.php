<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/course_offering.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
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
	<title>
		<?=COURSE_OFFERING_PAGE_TITLE.' '.DATA_VIEW; ?> | <?=$title?>
	</title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
	<link href="../backend_assets/dist/css/pages/other-pages.css" rel="stylesheet">
	<style>
	.custom_table{};
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
				<div class="row page-titles" style="padding-bottom: 10px;" >
                    <div class="col-md-10 align-self-center">
                        <h4 class="text-themecolor">
							<?=COURSE_OFFERING_PAGE_TITLE.' '.DATA_VIEW; ?>
						</h4>
                    </div>
					<div class="col-md-2 align-self-center">
						<button onclick="javascript:window.location.href='course_offering_data_view_excel'" type="button" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button>
					 </div>
				</div>
				
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="row">
									<div class="col-md-12" style="max-height:600px;overflow-x: auto;overflow-y: auto;">
										<table data-toggle="table" data-height="500" data-mobile-responsive="true" class="table-striped" id="table_1" >
											<thead>
												<tr>
													<th>#</th>
													<th><?=CAMPUS_CODE?></th>
													<th><?=TERM?></th>
													<th><?=COURSE_CODE_1?></th>
													<th><?=TRANSCRIPT_CODE?></th>
													<th><?=SESSION?></th>
													<th><?=SESSION_NO?></th>
													<th><?=INSTRUCTOR?></th>
													<th><?=ROOM_NO_1?></th>
													<th><?=COURSE_OFFERING_STATUS?></th>
													<th><?=STUDENTS?></th>
													<th><?=CLASS_SIZE?></th>
													<th><?=ROOM_SIZE?></th>
													<th><?=LMS_ACTIVE?></th>
													<th><?=LMS_CODE?></th>
													<th><?=EXTERNAL_ID?></th>
													<th><?=ASSISTANT?></th>
													<th><?=ACTIVE?></th>
												</tr>
											</thead>
											<tbody>
												<? /*$cond = $_SESSION['REPORT_QUERY'];	
												if($cond == '')
													$cond = " S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";*/
												$query = "SELECT S_COURSE_OFFERING.PK_COURSE_OFFERING,COURSE_CODE, LMS_CODE, CO_EXTERNAL_ID, IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%Y-%m-%d'),'') AS TERM_BEGIN_DATE, CAMPUS_CODE ,CONCAT(EMP_INSTRUCTOR.LAST_NAME,', ',EMP_INSTRUCTOR.FIRST_NAME) AS INSTRUCTOR_NAME,SESSION,SESSION_NO, ROOM_NO, S_COURSE_OFFERING.ROOM_SIZE, S_COURSE_OFFERING.CLASS_SIZE, IF(S_COURSE_OFFERING.ACTIVE = 1,'Yes','No') AS ACTIVE, IF(S_COURSE_OFFERING.LMS_ACTIVE = 1, 'Yes', 'No') as LMS_ACTIVE, COURSE_OFFERING_STATUS, S_COURSE_OFFERING.INSTRUCTOR, S_COURSE_OFFERING.PK_CAMPUS, TRANSCRIPT_CODE FROM S_COURSE_OFFERING LEFT JOIN M_COURSE_OFFERING_STATUS ON M_COURSE_OFFERING_STATUS.PK_COURSE_OFFERING_STATUS = S_COURSE_OFFERING.PK_COURSE_OFFERING_STATUS LEFT JOIN M_CAMPUS_ROOM ON M_CAMPUS_ROOM.PK_CAMPUS_ROOM = S_COURSE_OFFERING.PK_CAMPUS_ROOM  LEFT JOIN S_COURSE ON S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_COURSE_OFFERING.PK_CAMPUS LEFT JOIN S_EMPLOYEE_MASTER AS EMP_INSTRUCTOR ON EMP_INSTRUCTOR.PK_EMPLOYEE_MASTER = S_COURSE_OFFERING.INSTRUCTOR WHERE $_SESSION[REPORT_QUERY] ORDER BY S_TERM_MASTER.BEGIN_DATE DESC, COURSE_CODE ASC, SESSION ASC, SESSION_NO ASC ";
												//echo $query;exit;
												$_SESSION['REPORT_QUERY_1'] = $query;
												$res_type = $db->Execute($query);
											
												$no = $res_type->RecordCount();
												$nr = $no;
												
												$pn 			= $_GET['pn'];
												$itemsPerPage 	= $_GET['ipp'];
												
												if($pn == '')
													$pn = '1';
													
												if($itemsPerPage == '')
													$itemsPerPage = 25;
													
												if($nr > 0){
													$lastPage = ceil($nr / $itemsPerPage);
													if ($pn < 1) { // If it is less than 1
														$pn = 1; // force if to be 1
													} else if ($pn > $lastPage) { // if it is greater than $lastpage
														$pn = $lastPage; // force it to be $lastpage's value
													}

													$sub1 = $pn - 1;
													$add1 = $pn + 1;
													
													$disabled1 = "";
													$disabled2 = "";
													
													$onclick1  = 'onclick="ajax_notification('.$sub1.')"';
													$onclick2  = 'onclick="ajax_notification('.$add1.')"';
													
													if($pn == 1) {
														$disabled1 = "l-btn-disabled";
														$onclick1  = "";
													}
													
													if($pn == $lastPage) {
														$disabled2 = "l-btn-disabled";
														$onclick2  = "";
													}
													
													$paginationDisplay = ""; // Initialize the pagination output variable
													
													$paginationDisplay .= '<li><a href="javascript:void(0)" '.$onclick1.' class="l-btn l-btn-plain '.$disabled1.' " style="height: 13px !important; border: none !important;" ><span class="l-btn-left"><span class="l-btn-text"><span class="l-btn-empty ti-angle-left">&nbsp;</span></span></span></a>';
													
													$paginationDisplay .= '<li><a href="javascript:void(0)" '.$onclick2.' class="l-btn l-btn-plain '.$disabled2.'" style="height: 13px !important; border: none !important;" ><span class="l-btn-left"><span class="l-btn-text"><span class="l-btn-empty ti-angle-right">&nbsp;</span></span></span></a>';
													
													$limit = 'LIMIT ' .($pn - 1) * $itemsPerPage .',' .$itemsPerPage;
												}
												$res_type = $db->Execute($query." ".$limit);
												
												$pn2 = $pn - 1;
												$i  = ($itemsPerPage * $pn2);
												$kl = $i + 1;
												while (!$res_type->EOF) { 
													$i++; 
													$PK_COURSE_OFFERING = $res_type->fields['PK_COURSE_OFFERING']; 
													$res_stu = $db->Execute("select COUNT(PK_STUDENT_COURSE) as NO from S_STUDENT_COURSE WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING'  "); ?>
													<tr>
														<td><?=$i?></td>
														<td><?=$res_type->fields['CAMPUS_CODE']?></td>
														<td><?=$res_type->fields['TERM_BEGIN_DATE']?></td>
														<td><?=$res_type->fields['COURSE_CODE']?></td>
														<td><?=$res_type->fields['TRANSCRIPT_CODE']?></td>
														<td><?=$res_type->fields['SESSION']?></td>
														
														<td><?=$res_type->fields['SESSION_NO']?></td>
														<td><?=$res_type->fields['INSTRUCTOR_NAME']?></td>
														<td><?=$res_type->fields['ROOM_NO']?></td>
														<td><?=$res_type->fields['COURSE_OFFERING_STATUS']?></td>
														<td><?=$res_stu->fields['NO'] ?></td>
														<td><?=$res_type->fields['CLASS_SIZE']?></td>
														<td><?=$res_type->fields['ROOM_SIZE']?></td>
														
														<td><?=$res_type->fields['LMS_ACTIVE']?></td>
														<td><?=$res_type->fields['LMS_CODE']?></td>
														<td><?=$res_type->fields['CO_EXTERNAL_ID']?></td>
														<td>
															<? $ASSISTANT = '';
															$res_ass = $db->Execute("select CONCAT(LAST_NAME,', ',FIRST_NAME) AS INSTRUCTOR_NAME FROM S_COURSE_OFFERING_ASSISTANT, S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = ASSISTANT AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ");
															while (!$res_ass->EOF) {
																if($ASSISTANT != '')
																	$ASSISTANT .= '<br />';
																$ASSISTANT .= $res_ass->fields['INSTRUCTOR_NAME'];
																
																$res_ass->MoveNext();
															} 
															echo $ASSISTANT; ?>
														</td>
														<td><?=$res_type->fields['ACTIVE']?></td>
													</tr>
												<?	$res_type->MoveNext();
												} ?>
											</tbody>
										</table>
									</div>
								</div>
								
								<? if($paginationDisplay != '') {  ?>
								<br />
								<div id="paginator1" class="datepaginator">
									<ul class="pagination">
										<li>
											<select class="pagination-page-list" onchange="ajax_notification(1)" id="ipp" >
												<option <? if($itemsPerPage == 10) echo "selected"; ?> value="10" >10</option>
												<option <? if($itemsPerPage == 25) echo "selected"; ?> value="25" >25</option>
												<option <? if($itemsPerPage == 100) echo "selected"; ?> value="100" >100</option>
												<option <? if($itemsPerPage == 500) echo "selected"; ?> value="500" >500</option>
											</select>
										</li>
										
										<?=$paginationDisplay?>
										
										<? if($nr > 0){ ?>
											<li><div class="pagination-info1 float-right col-xs-5 " ><strong><?=$kl.' - '.$i ?> of <?=$no ?></strong></div></li>
										<? } ?>
									</ul>
								</div>
								<? } ?>
                            </div>
                        </div>
					</div>
				</div>
				
            </div>
        </div>

        <? require_once("footer.php"); ?>
    </div>
	<? require_once("js.php"); ?>
	<link href="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.css" rel="stylesheet" type="text/css" />
	<script src="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.js"></script>
	<script type="text/javascript" >

	function ajax_notification(pn){
		var ipp = document.getElementById('ipp').value
		window.location.href = "student_data_view?t=<?=$_GET['t']?>&me=<?=$_GET['me']?>&arch=<?=$_GET['arch']?>&pn="+pn+'&ipp='+ipp;
	}
	</script>
	
	<script src="../backend_assets/node_modules/date-paginator/moment.min.js"></script>
	<script src="../backend_assets/node_modules/date-paginator/bootstrap-datepaginator.min.js"></script>
    <script type="text/javascript">
    var datepaginator = function() {
        return {
            init: function() {
                $("#paginator1").datepaginator(),
				$("#paginator2").datepaginator({
					size: "large"
				}),
				$("#paginator3").datepaginator({
					size: "small"
				})
            }
        }
    }();
    jQuery(document).ready(function() {
        datepaginator.init()
    });
</body>

</html>