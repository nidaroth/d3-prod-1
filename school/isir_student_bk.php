<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/isir_student.php");
require_once("../global/common_functions.php");

if(isset($_POST) && isset($_FILES['pdfFile'] )) {

	$file_dir_1 = 'temp/';
	$extn 			= explode(".",$_FILES['pdfFile']['name']);
	$iindex			= count($extn) - 1;
	$rand_string 	= time()."_".rand(10000,99999);
	$file11			= $rand_string.".".$extn[$iindex];	
	$extension   	= strtolower($extn[$iindex]);
	
	if($extension == "txt" ){ 
		$newfile1 = $file_dir_1.$file11;
		move_uploaded_file($_FILES['pdfFile']['tmp_name'], $newfile1);
	
		$file = file_get_contents($newfile1, true);
		$pdfText = explode("\n", $file);

		$isir_db_table = get_db_table($db_name,'S_ISIR_MASTER');  

		foreach($isir_db_table as $key => $val) {
			if($key <= 1) {
				$isir_tbl_column = get_db_field($val);
				foreach($isir_tbl_column as $k => $v) {
					$isir_result[$val][$k] = $v;
				}
			}
		}
		//echo'<pre>';print_r($isir_result);die;
		foreach($pdfText as $key => $val) {
			$nextStrAccurance = 0;
			$preStrAccurance = 0;
			if(!empty(trim($val))) {
				foreach($isir_result as $k => $v) {
					foreach($v as $i => $j) {
						if(strpos($j['Field'],'PK_ISIR_MASTER') === false && strpos($j['Field'],'ACTIVE') === false && strpos($j['Field'],'CREATED_ON') === false && strpos($j['Field'],'CREATED_BY') === false && strpos($j['Field'],'EDITED_ON') === false && strpos($j['Field'],'EDITED_BY') === false) {
							$resVal = substr($pdfText[$key], $nextStrAccurance, $j['Type']);
							$pushArr = array('value' => $resVal);
							array_push($isir_result[$k][$i],$pushArr); 
							$nextStrAccurance = $nextStrAccurance + $j['Type'];
							$ISIR_MASTER[$key][$k][$j['Field']] =  $resVal;
						}
						if ($j['Field'] == "CREATED_ON") {
							$ISIR_MASTER[$key][$k]['CREATED_ON'] = date("Y-m-d H:i");
						}
						if ($j['Field'] == "CREATED_BY") {
							$ISIR_MASTER[$key][$k]['CREATED_BY'] = $_SESSION['PK_USER'];
						}
					}
				}
				echo'<pre>---';print_r($ISIR_MASTER[$key]['S_ISIR_MASTER']);
				db_perform('S_ISIR_MASTER', $ISIR_MASTER[$key]['S_ISIR_MASTER'], 'insert');
				$PK_ISIR_MASTER = $db->insert_ID();
				
				// echo'<pre>';print_r($PK_ISIR_MASTER);
				$ISIR_MASTER[$key]['S_ISIR_MASTER_DETAIL_1']['PK_ISIR_MASTER'] = $PK_ISIR_MASTER;
				db_perform('S_ISIR_MASTER_DETAIL_1', $ISIR_MASTER[$key]['S_ISIR_MASTER_DETAIL_1'], 'insert');

				// echo'<pre>';print_r($ISIR_MASTER[$key]['S_ISIR_MASTER_DETAIL_1']);die;
			}
		}
	}
	// echo'<pre>';print_r($ISIR_MASTER);
	die;
	
	/*foreach($isir_result as $key => $val) {
		foreach($val as $k => $v) {
			$ISIR_MASTER = array();
			echo'<pre>';print_r($v);die;
			$ISIR_MASTER[$v]
			foreach($v as $m => $n) {
							echo'<pre>';print_r($n[1]);die;

				//$ISIR_MASTER[$v][$m] = $n;
			}
		}
	}*/
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
	<title>Test</title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
</head>
<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-2 align-self-center">
                        <h4 class="text-themecolor"><?=ISIR_UPLOAD_PAGE_TITLE?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="p-20">
                            	<form enctype="multipart/form-data" class="floating-labels m-t-40" method="post" name="form1" id="form1">
									<p><input type="file" name="pdfFile" /><br />
									<br />
									<input type="submit" name="Upload" value="Upload" class="btn btn-info d-none d-lg-block m-l-15" /></p>
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
					
</body>
</html>