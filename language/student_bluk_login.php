<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2) ){ 
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	include '../global/excel/Classes/PHPExcel/IOFactory.php';
	$cell1  = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");		
	define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

	$total_fields = 120;
	for($i = 0 ; $i <= $total_fields ; $i++){
		if($i <= 25)
			$cell[] = $cell1[$i];
		else {
			$j = floor($i / 26) - 1;
			$k = ($i % 26);
			//echo $j."--".$k."<br />";
			$cell[] = $cell1[$j].$cell1[$k];
		}	
	}

	$dir 			= 'temp/';
	$inputFileType  = 'Excel2007';
	$file_name 		= 'Student-Login.xlsx';
	$outputFileName = $dir.$file_name; 
$outputFileName = str_replace(
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

	$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
	$objReader->setIncludeCharts(TRUE);
	//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
	$objPHPExcel = new PHPExcel();
	$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

	$line 	= 1;	
	$index 	= -1;

	$heading[] = STUDENT_NAME;
	$width[]   = 20;
	$heading[] = LOGIN_ID;
	$width[]   = 20;
	$heading[] = PASSWORD;
	$width[]   = 20;
	
	$i = 0;
	foreach($heading as $title) {
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
		$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
	}
	
	$res = $db->Execute("SELECT * FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	$STU_DEFAULT_PASSWORD = $res->fields['STU_DEFAULT_PASSWORD'];
	
	$i = 0;
	foreach($_POST['PK_STUDENT_MASTER_1'] as $PK_STUDENT_MASTER){
		
		$res = $db->Execute("SELECT CONCAT(LAST_NAME,', ',FIRST_NAME) as NAME, STUDENT_ID FROM S_STUDENT_MASTER, S_STUDENT_ACADEMICS WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND LOGIN_CREATED = '0' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER "); 
		if($res->RecordCount() > 0) {
			
			$K = 0;
			do {
				$STUDENT_ID = $res->fields['STUDENT_ID'];
				if($K > 0)
					$STUDENT_ID .= $K;
					
				$res_key = $db->Execute("SELECT USER_ID FROM Z_USER where USER_ID = '$STUDENT_ID'");
				$K++;
			} while ($res_key->RecordCount() > 0);
			
			do {
				$USER_API_KEY = generateRandomString(60);
				$res_key = $db->Execute("SELECT PK_USER FROM Z_USER where USER_API_KEY = '$USER_API_KEY'");
			} while ($res_key->RecordCount() > 0);

			$STUDENT_MASTER['LOGIN_CREATED'] = 1;
			db_perform('S_STUDENT_MASTER', $STUDENT_MASTER, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ");
			
			$salt = substr(strtr(base64_encode(openssl_random_pseudo_bytes(22)),'+','.'),0,22);
			$hash = crypt($STU_DEFAULT_PASSWORD, '$2y$12$' . $salt);
			$USER['PASSWORD']  	 	= $hash;
			$USER['ID']  	 	 	= $PK_STUDENT_MASTER;
			$USER['USER_API_KEY']  	= $USER_API_KEY;
			$USER['USER_ID']  		= $STUDENT_ID;
			$USER['PK_USER_TYPE']  	= 3;
			$USER['PK_LANGUAGE']  	= 1;
			$USER['PK_ACCOUNT']  	= $_SESSION['PK_ACCOUNT'];
			$USER['CREATED_BY']  	= $_SESSION['PK_USER'];
			$USER['CREATED_ON']  	= date("Y-m-d H:i");
			db_perform('Z_USER', $USER, 'insert');
			
			$line++;
			$index = -1;
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NAME']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($STUDENT_ID);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($STU_DEFAULT_PASSWORD);
		}
		$i++;
	}
	//echo $outputFileName;exit;
	$objWriter->save($outputFileName);
	$objPHPExcel->disconnectWorksheets();
	header("location:".$outputFileName);
	
	exit;
	
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
	<? require_once("css.php"); ?>
	<title><?=MNU_CREATE_STUDENT_LOGIN?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor">
						<? echo BULK.' - '.MNU_CREATE_STUDENT_LOGIN ?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
									<div class="row" style="padding-bottom:10px;" >
										<div class="col-md-2 ">
											<select id="PK_COURSE" name="PK_COURSE" class="form-control" onchange="get_course_offering(this.value)" >
												<option value=""><?=COURSE_CODE?></option>
												<? $res_type = $db->Execute("select PK_COURSE,COURSE_CODE,COURSE_DESCRIPTION from S_COURSE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by COURSE_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_COURSE']?>" ><?=$res_type->fields['COURSE_CODE'].' - '.$res_type->fields['COURSE_DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 " id="PK_COURSE_OFFERING_DIV" >
											<select id="PK_COURSE_OFFERING" name="PK_COURSE_OFFERING" class="form-control" >
												<option value=""><?=COURSE_OFFERING_PAGE_TITLE?></option>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_STUDENT_GROUP" name="PK_STUDENT_GROUP" class="form-control" >
												<option value=""><?=GROUP_CODE?></option>
												<? $res_type = $db->Execute("select PK_STUDENT_GROUP,STUDENT_GROUP from M_STUDENT_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by STUDENT_GROUP ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_GROUP']?>" ><?=$res_type->fields['STUDENT_GROUP']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_TERM_MASTER" name="PK_TERM_MASTER" class="form-control" >
												<option value=""><?=FIRST_TERM?></option>
												<? $res_type = $db->Execute("select PK_TERM_MASTER,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_TERM_MASTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by BEGIN_DATE DESC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" ><?=$res_type->fields['BEGIN_DATE_1']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM" class="form-control" >
												<option value=""><?=PROGRAM?></option>
												<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
									
										<div class="col-md-2 ">
											<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS" class="form-control">
												<option value=""><?=STATUS?></option>
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS, DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by STUDENT_STATUS ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
									</div>
									<div class="row">
										<div class="col-md-9 align-self-center "></div>
										<div class="col-md-3 " style="text-align:right" >
											<button type="button" class="btn waves-effect waves-light btn-info" id="btn" style="display:none" onclick="add_to_update_list()" ><?=ADD_TO_BULK_CREATE_LIST?></button>
											<button type="button" class="btn waves-effect waves-light btn-dark" onclick="search()" ><?=SEARCH?></button>
										</div>
									</div>
									<br />
									<div id="student_div" style="max-height:300px;overflow: auto;"></div>
									
									<div class="row page-titles">
										<div class="col-md-5 align-self-center">
											<h4 class="text-themecolor">
												<?=UPDATE_LIST?>
											</h4>
										</div>
									</div>
									<div id="student_update_div" style="max-height:300px;overflow: auto;">
										<table class="table table-hover" id="student_update_table" >
											<thead>
												<tr>
													<th><?=STUDENT?></th>
													<th><?=GROUP_CODE?></th>
													<th><?=FIRST_TERM?></th>
													<th><?=PROGRAM?></th>
													<th><?=STATUS?></th>
													<th><?=ACTION?></th>
												</tr>
											</thead>
											<tbody>
											</tbody>
										</table>
									</div>
									<div id="action_div" >
										<div class="d-flex">
											<div class="col-12 col-sm-6 form-group">&nbsp;</div>
											<div class="col-12 col-sm-3 form-group">
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=CREATE_LOGIN?></button>
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
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');
		
		function search(){
			jQuery(document).ready(function($) {
				var data  = 'PK_STUDENT_GROUP='+$('#PK_STUDENT_GROUP').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&PK_STUDENT_COURSE=<?=$_GET['id']?>'+'&PK_COURSE='+$('#PK_COURSE').val()+'&PK_COURSE_OFFERING='+$('#PK_COURSE_OFFERING').val()+'&type=bulk_create_login';
				var value = $.ajax({
					url: "ajax_search_student",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						document.getElementById('student_div').innerHTML = data
					}		
				}).responseText;
			});
		}
		function show_btn(){
			
			var flag = 0;
			var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
			for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
				if(PK_STUDENT_ENROLLMENT[i].checked == true) {
					flag++;
					break;
				}
			}
			
			if(flag == 1)
				document.getElementById('btn').style.display = 'inline';
			else
				document.getElementById('btn').style.display = 'none';
			
		}
		
		function get_course_offering(val){
			jQuery(document).ready(function($) { 
				var data  = 'val='+val+'&multiple=0';
				var value = $.ajax({
					url: "ajax_get_course_offering",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						document.getElementById('PK_COURSE_OFFERING_DIV').innerHTML = data;
						document.getElementById('PK_COURSE_OFFERING').options[0].text = '<?=COURSE_OFFERING_PAGE_TITLE?>';
					}		
				}).responseText;
			});
		}
		
		function fun_select_all(){
			var str = '';
			if(document.getElementById('SEARCH_SELECT_ALL').checked == true)
				str = true;
			else
				str = false;
				
			var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
			for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
				PK_STUDENT_ENROLLMENT[i].checked = str
			}
			show_btn()
		}
		
		function add_to_update_list(){
			jQuery(document).ready(function($) { 
				var str = '';
				var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
				for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
					if(PK_STUDENT_ENROLLMENT[i].checked == true) {
						if(str != '')
							str += ',';
							
						str += PK_STUDENT_ENROLLMENT[i].value;
					}
				}
				
				var str1 = '';
				var PK_STUDENT_ENROLLMENT_1 = document.getElementsByName('PK_STUDENT_ENROLLMENT_1[]')
				for(var i = 0 ; i < PK_STUDENT_ENROLLMENT_1.length ; i++){
					if(str1 != '')
						str1 += ',';
						
					str1 += PK_STUDENT_ENROLLMENT_1[i].value;
				}
				
				var data  = 'str='+str+'&str1='+str1;
				var value = $.ajax({
					url: "ajax_get_student_details_from_id",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						$("#student_update_table tbody").append(data)
					}		
				}).responseText;
			});
		}
		
		function delete_row(id){
			jQuery(document).ready(function($) { 
				$("#stu_tr_"+id).remove()
			});
		}
	</script>

</body>

</html>