<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/grade.php");
require_once("check_access.php");

if(check_access('SETUP_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$i = 0;
	foreach($_POST['COUNT'] as $COUNT){
		$S_GRADE = array();
		$S_GRADE['GRADE'] 					= $_POST['GRADE'][$i];
		$S_GRADE['NUMBER_GRADE'] 			= $_POST['NUMBER_GRADE'][$i];
		$S_GRADE['ACTIVE'] 					= $_POST['ACTIVE_'.$COUNT];
		$S_GRADE['IS_DEFAULT'] 				= $_POST['IS_DEFAULT_'.$COUNT];
		$S_GRADE['CALCULATE_GPA'] 			= $_POST['CALCULATE_GPA_'.$COUNT];
		$S_GRADE['UNITS_ATTEMPTED'] 		= $_POST['UNITS_ATTEMPTED_'.$COUNT];
		$S_GRADE['UNITS_COMPLETED'] 		= $_POST['UNITS_COMPLETED_'.$COUNT];
		$S_GRADE['UNITS_IN_PROGRESS'] 		= $_POST['UNITS_IN_PROGRESS_'.$COUNT];
		$S_GRADE['WEIGHTED_GRADE_CALC'] 	= $_POST['WEIGHTED_GRADE_CALC_'.$COUNT];
		$S_GRADE['RETAKE_UPDATE'] 			= $_POST['RETAKE_UPDATE_'.$COUNT];
		$S_GRADE['DISPLAY_ORDER'] 			= $_POST['DISPLAY_ORDER'][$i];
		
		if($S_GRADE['RETAKE_UPDATE'] == 1)
			$S_GRADE['RETAKE_GRADE'] = $_POST['RETAKE_GRADE_'.$COUNT];
		else
			$S_GRADE['RETAKE_GRADE'] = 0;

		if($_POST['PK_GRADE'][$i] == ''){
			$S_GRADE['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
			$S_GRADE['CREATED_BY']  = $_SESSION['PK_USER'];
			$S_GRADE['CREATED_ON']  = date("Y-m-d H:i");
			db_perform('S_GRADE', $S_GRADE, 'insert');
			$PK_GRADE = $db->insert_ID;
			
			$PK_GRADE_ARR[] = $PK_GRADE;
			
		} else {
			$PK_GRADE = $_POST['PK_GRADE'][$i];
			$S_GRADE['EDITED_BY'] = $_SESSION['PK_USER'];
			$S_GRADE['EDITED_ON'] = date("Y-m-d H:i");
			db_perform('S_GRADE', $S_GRADE, 'update'," PK_GRADE = '$PK_GRADE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
			
			$PK_GRADE_ARR[] = $PK_GRADE;
		}
		
		$i++;
	}

	$cond = '';
	if(!empty($PK_GRADE_ARR))
		$cond = " AND PK_GRADE NOT IN (".implode(",",$PK_GRADE_ARR).")";
		//$db->Execute("DELETE from S_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond"); 
	
	if(!empty($_GET['page'])){
		header("location:grade?page=".$_GET['page']);

	}else{
		header("location:grade");
	}
}


function custom_pagination($page, $totalpage, $link, $show)  //$link = '&page=%s' 
{ 
    //show page 
if($totalpage == 0) 
{ 
return 'Page 0 of 0'; 
} else { 
    $nav_page = '<div class="navpage" id="paginationdiv"><span class="currentpages">Page '.$page.' of '.$totalpage.' : </span>'; 
    $limit_nav = 3; 
    $start = ($page - $limit_nav <= 0) ? 1 : $page - $limit_nav; 
    $end = $page + $limit_nav > $totalpage ? $totalpage : $page + $limit_nav; 
    if($page + $limit_nav >= $totalpage && $totalpage > $limit_nav * 2){ 
        $start = $totalpage - $limit_nav * 2; 
    } 

	if($page != 1){
	$nav_page .= '<span class="item"><a href="'.sprintf($link, 1).'" title="First"> << </a></span>'; 
	}

    if($start != 1){ //show first page 
        $nav_page .= '<span class="item"><a href="'.sprintf($link, 1).'"> 1 </a></span>'; 
    } 
    if($start > 2){ //add ... 
        $nav_page .= '<span class="current">...</span>'; 
    } 
    if($page > 5){ //add prev 
        $nav_page .= '<span class="item"><a href="'.sprintf($link, $page-5).'">&laquo;</a></span>'; 
    } 
    for($i = $start; $i <= $end; $i++){ 
        if($page == $i) 
            $nav_page .= '<span class="current">'.$i.'</span>'; 
        else 
            $nav_page .= '<span class="item"><a href="'.sprintf($link, $i).'"> '.$i.' </a></span>'; 
    } 
    if($page + 3 < $totalpage){ //add next 
        $nav_page .= '<span class="item"><a href="'.sprintf($link, $page+4).'">&raquo;</a></span>'; 
    } 
    if($end + 1 < $totalpage){ //add ... 
        $nav_page .= '<span class="currentadd">...</span>'; 
    }     
    if($end != $totalpage) //show last page 
        $nav_page .= '<span class="item"><a href="'.sprintf($link, $totalpage).'"> '.$totalpage.' </a></span>'; 
    
	if($page < $totalpage)
	$nav_page .= '<span class="item"><a href="'.sprintf($link, $totalpage).'" title="Last"> >> </a></span>'; 

	$nav_page .= '</div>'; 
    return $nav_page; 
} 
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
	<title><?=GRADES_PAGE_TITLE?> | <?=$title?></title>
	<style>
		.no-records-found{display:none;}
		.fixed-table-container tbody td .th-inner, .fixed-table-container thead th .th-inner { padding: 5px !important;}
		/* DIAM-1855 */
		#paginationdiv.navpage .current{
			background-color: #022561;
			text-align: center;		
			color: #fff;
			width: 4%;
			display: inline-block;
			padding: 3px;
			border: 1px solid #d4d4d4;
			text-align: center;
 		   vertical-align: middle;
		

				
		}

		#paginationdiv.navpage .item{
			width: 4%;
			/* height: 25px; */
			color: #000;
			display: inline-block;
			padding: 3px;
			border: 1px solid #d4d4d4;
			text-align: center;
 		   vertical-align: middle;
		}

		#paginationdiv.navpage{
			margin-top:25px;
			font-size: 18px;
		}
		/* DIAM-1855 */
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"> <?=GRADES_PAGE_TITLE?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
									<table data-toggle="table" data-mobile-responsive="true" class="table-striped" id="grade_table" >
										<thead>
											<tr>
												<th ><?=GRADE?></th>
												<th ><?=NUMBER_GRADE?></th>
												<th ><?=CALCULATE_GPA_1?></th>
												<th ><?=UNITS_ATTEMPTED_1?></th>
												<th ><?=UNITS_COMPLETED_1?></th>
												<th ><?=UNITS_IN_PROGRESS_1?></th>
												<th ><?=WEIGHTED_GRADE_CALC_1?></th>
												<th ><?=RETAKE_UPDATE_1?></th>
												<th ><?=RETAKE_GRADE?></th>
												<th ><?=DISPLAY_ORDER?></th>
												<th ><?=IS_DEFAULT?></th>
												<th ><?=ACTIVE?></th>
												<th ><?=DELETE?></th>
											</tr>
										</thead>
										<tbody>
											<? 
											//DIAM-1855
											$perPage = 25;
											$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
											$startAt = $perPage * ($page - 1);

											$result1_cnt = $db->Execute("SELECT PK_GRADE FROM S_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY DISPLAY_ORDER ASC");
											$reccnt = $result1_cnt->RecordCount();
											$totalPages = ceil($reccnt / $perPage);
											$count = 1; 
											$result1 = $db->Execute("SELECT PK_GRADE FROM S_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY DISPLAY_ORDER ASC LIMIT $startAt, $perPage"); //LIMIT $startAt, $perPage
											//DIAM-1855
											$reccnt = $result1->RecordCount();
											while (!$result1->EOF) {
												$_REQUEST['PK_GRADE'] 	= $result1->fields['PK_GRADE'];
												$_REQUEST['count']  	= $count;
												
												include('ajax_grade.php');
												
												$count++;	
												$result1->MoveNext();
											} 
											
											?>
										</tbody>
									</table>
									<?
										echo custom_pagination($page, $totalPages, 'grade?page=%s', 2);
									?>
								<br /><br />
									<div class="row">
                                        <div class="col-md-7">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="button" class="btn btn-primary" onClick="add_fields()" /><?=ADD ?></button>
												
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='setup'" ><?=CANCEL?></button>
												
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
   
	<? require_once("js.php"); ?>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');
		
		var cunt = '<?=$count?>';
		function add_fields(){
			jQuery(document).ready(function($) {
				var data = 'count='+cunt+'&ACTION=<?=$_GET['act']?>';
				var value = $.ajax({
					url: "ajax_grade",	
					type: "POST",
					data: data,		
					async: false,
					cache :false,
					success: function (data) {
						$('#grade_table tbody').append(data);
						cunt++;
					}		
				}).responseText;
			});
		}
		
		function delete_row(id,type){
			jQuery(document).ready(function($) {
				if(type == 'grade')
					document.getElementById('delete_message').innerHTML = '<?=DELETE_MESSAGE_GENERAL?>?';
		
				$("#deleteModal").modal()
				$("#DELETE_ID").val(id)
				$("#DELETE_TYPE").val(type)
			});
		}
		function conf_delete(val,id){
			jQuery(document).ready(function($) {
				if(val == 1) {
					if($("#DELETE_TYPE").val() == 'grade'){
						var iid = $("#DELETE_ID").val()
						$("#table_"+iid).remove()
					}
				}
				$("#deleteModal").modal("hide");
			});
		}
		function set_default(id){
			var IS_DEFAULT 	= document.getElementsByClassName('IS_DEFAULT')
			for(var i = 0 ; i < IS_DEFAULT.length ; i++){
				IS_DEFAULT[i].checked = false
			}
			document.getElementById('IS_DEFAULT_'+id).checked = true;
		}
		function show_retake_grade(id){
			if(document.getElementById('RETAKE_UPDATE_'+id).checked == true){
				document.getElementById('RETAKE_GRADE_'+id).style.display 	= 'block';
				document.getElementById('RETAKE_GRADE_'+id).className 		= 'form-control required-entry';
			} else {
				document.getElementById('RETAKE_GRADE_'+id).style.display = 'none';
				document.getElementById('RETAKE_GRADE_'+id).className 		= 'form-control';
			}
		}
	</script>
	<link href="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.css" rel="stylesheet" type="text/css" />
	<script src="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.js"></script>
</body>

</html>
