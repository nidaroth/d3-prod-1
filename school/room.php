<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/room.php");
require_once("check_access.php");

if(check_access('SETUP_SCHOOL') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$CAMPUS_ROOM = $_POST;
	
	$CAMPUS_ROOM['PK_CAMPUS'] = $_POST['PK_CAMPUS'];
		
	if($_GET['id'] == ''){
		$CAMPUS_ROOM['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$CAMPUS_ROOM['CREATED_BY']  = $_SESSION['PK_USER'];
		$CAMPUS_ROOM['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('M_CAMPUS_ROOM', $CAMPUS_ROOM, 'insert');
	} else {
		$cond = " AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";
			
		$CAMPUS_ROOM['EDITED_BY'] = $_SESSION['PK_USER'];
		$CAMPUS_ROOM['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('M_CAMPUS_ROOM', $CAMPUS_ROOM, 'update'," PK_CAMPUS_ROOM = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond ");
	}
	header("location:manage_room");
}
if($_GET['id'] == ''){
	$PK_CAMPUS 			= '';
	$ROOM_NO 			= '';
	$ROOM_DESCRIPTION 	= '';
	$CLASS_SIZE 		= '';
	$ACTIVE	 			= '';
	
	/* Ticket #849  */
	$res_camp = $db->Execute("select PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	if($res_camp->RecordCount() == 1)
		$PK_CAMPUS = $res_camp->fields['PK_CAMPUS'];
	/* Ticket #849  */
		
} else {
	$cond = " AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";
			
	$res = $db->Execute("SELECT * FROM M_CAMPUS_ROOM WHERE PK_CAMPUS_ROOM = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond "); 
	if($res->RecordCount() == 0){
		header("location:manage_room");
		exit;
	}
	
	$PK_CAMPUS 			= $res->fields['PK_CAMPUS'];
	$ROOM_NO 			= $res->fields['ROOM_NO'];
	$ROOM_DESCRIPTION 	= $res->fields['ROOM_DESCRIPTION'];
	$CLASS_SIZE 		= $res->fields['CLASS_SIZE'];
	$ACTIVE  			= $res->fields['ACTIVE'];
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
	<title><?=ROOM_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else EDIT; ?> <?=ROOM_PAGE_TITLE?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
									
									<? $cond = "";
									if($_SESSION['PK_ROLES'] == 3)
										$cond = " AND PK_CAMPUS IN ($_SESSION[PK_CAMPUS]) "; ?>
									<div class="row">
										<div class="col-md-6">
											<div class="form-group m-b-40">
												<select id="PK_CAMPUS" name="PK_CAMPUS" class="form-control required-entry" >
													<option selected></option>
													 <? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond order by CAMPUS_CODE ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_CAMPUS'] ?>" <? if($PK_CAMPUS == $res_type->fields['PK_CAMPUS']) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span> 
												<label for="PK_CAMPUS"><?=CAMPUS?></label>
											</div>
										</div>
									</div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control required-entry" id="ROOM_NO" name="ROOM_NO" value="<?=$ROOM_NO?>" >
												<span class="bar"></span>
												<label for="ROOM_NO"><?=ROOM_NO?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control" id="ROOM_DESCRIPTION" name="ROOM_DESCRIPTION" value="<?=$ROOM_DESCRIPTION?>" >
												<span class="bar"></span>
												<label for="ROOM_DESCRIPTION"><?=DESCRIPTION?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control" id="CLASS_SIZE" name="CLASS_SIZE" value="<?=$CLASS_SIZE?>" >
												<span class="bar"></span>
												<label for="CLASS_SIZE"><?=ROOM_SIZE?></label>
											</div>
										</div>
                                    </div>
																	
									<? if($_GET['id'] != ''){ ?>
									<div class="row">
										<div class="col-md-6">
											<div class="row form-group">
												<div class="custom-control col-md-2"><?=ACTIVE?></div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="customRadio11" name="ACTIVE" value="1" <? if($ACTIVE == 1) echo "checked"; ?> class="custom-control-input">
													<label class="custom-control-label" for="customRadio11"><?=YES?></label>
												</div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="customRadio22" name="ACTIVE" value="0" <? if($ACTIVE == 0) echo "checked"; ?>  class="custom-control-input">
													<label class="custom-control-label" for="customRadio22"><?=NO?></label>
												</div>
											</div>
										</div>
									</div>
									<? } ?>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_room'" ><?=CANCEL?></button>
												
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
	</script>

</body>

</html>