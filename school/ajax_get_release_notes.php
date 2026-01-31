<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/release_notes.php");
require_once("check_access.php");

$cond = "";
if($_REQUEST['PK_RELEASE_TYPE'] != '')
	$cond .= " AND Z_RELEASE_NOTES.PK_RELEASE_TYPE = '$_REQUEST[PK_RELEASE_TYPE]' ";
	
if($_REQUEST['PK_RELEASE_TYPE'] != '')
	$cond .= " AND Z_RELEASE_NOTES.PK_RELEASE_TYPE = '$_REQUEST[PK_RELEASE_TYPE]' ";

$PK_RELEASE_CATEGORY_ARR = array();
if($_REQUEST['PK_RELEASE_CATEGORY'] != '')
	$PK_RELEASE_CATEGORY_ARR = explode(",",$_REQUEST['PK_RELEASE_CATEGORY']);
	
if($_REQUEST['START_DATE'] != '' && $_REQUEST['END_DATE'] != '') {
	$cond .= " AND DATE_FORMAT(PUSHED_TO_D3_DATE,'%m/%d/%Y') BETWEEN '$_REQUEST[START_DATE]' AND '$_REQUEST[END_DATE]' ";
} else if($_REQUEST['START_DATE'] != '') {
	$cond .= " AND DATE_FORMAT(PUSHED_TO_D3_DATE,'%m/%d/%Y') >= '$_REQUEST[START_DATE]' ";
} else if($_REQUEST['END_DATE'] != '') {
	$cond .= " AND DATE_FORMAT(PUSHED_TO_D3_DATE,'%m/%d/%Y') <='$_REQUEST[END_DATE]' ";
}

if($_REQUEST['SEARCH'] != '')
	$cond .= " AND (SUBJECT LIKE '%$_REQUEST[SEARCH]%' OR RELEASE_NOTES LIKE '%$_REQUEST[SEARCH]%') ";

$res = $db->Execute("select PK_RELEASE_CATEGORY, RELEASE_TYPE, SUBJECT, PUSHED_TO_D3_DATE, LOCATION, RELEASE_NOTES, KNOWLEDGEBASE_URL FROM Z_RELEASE_NOTES LEFT JOIN M_RELEASE_TYPE ON M_RELEASE_TYPE.PK_RELEASE_TYPE = Z_RELEASE_NOTES.PK_RELEASE_TYPE WHERE RELEASE_NOTES_PUSHED = 1 $cond ORDER BY PUSHED_TO_D3_DATE DESC, RELEASE_TYPE ASC, SUBJECT ASC");
while (!$res->EOF) { 
	$PK_RELEASE_CATEGORY 		= $res->fields['PK_RELEASE_CATEGORY'];
	$PK_RELEASE_CATEGORY1_ARR 	= explode(",",$PK_RELEASE_CATEGORY);
	
	if(empty($PK_RELEASE_CATEGORY_ARR)){
		$flag = 1;
	} else {
		$flag = 0;
		foreach($PK_RELEASE_CATEGORY_ARR as $PK_RELEASE_CATEGORY_SEARCH){
			foreach($PK_RELEASE_CATEGORY1_ARR as $PK_RELEASE_CATEGORY1){
				if($PK_RELEASE_CATEGORY_SEARCH == $PK_RELEASE_CATEGORY1) {
					$flag = 1;
					break;
				}
			}
		}
	}
	
	$CATEGORY = '';
	$res_type_1 = $db->Execute("select RELEASE_CATEGORY from M_RELEASE_CATEGORY WHERE PK_RELEASE_CATEGORY IN ($PK_RELEASE_CATEGORY) ORDER BY RELEASE_CATEGORY ASC");
	while (!$res_type_1->EOF) { 
		if($CATEGORY != '')
			$CATEGORY .= ', ';
			
		$CATEGORY .= $res_type_1->fields['RELEASE_CATEGORY'];
		
		$res_type_1->MoveNext();
	} 
	if($flag == 1){ ?>
	<div class="row">
		<div class="col-md-2">
			<div class="form-group" style="margin-bottom: 5px;" >
				<b style='font-weight: bold;' ><?=DATE.': ' ?></b>
				<? if($res->fields['PUSHED_TO_D3_DATE'] != '0000-00-00')
					echo ' '.date("m/d/Y",strtotime($res->fields['PUSHED_TO_D3_DATE'])); ?>
			</div>
		</div>
		
		<div class="col-md-6">
			<div class="form-group" style="margin-bottom: 5px;" >
				<b style='font-weight: bold;' ><?=CATEGORY.': ' ?></b>
				<?=$CATEGORY?>
			</div>
		</div>
		
		<div class="col-md-2">
			<div class="form-group" style="margin-bottom: 5px;" >
				<b style='font-weight: bold;' ><?=TYPE.': ' ?></b>
				<?=$res->fields['RELEASE_TYPE']?>
			</div>
		</div>
		
		<div class="col-md-2">
			<div class="form-group" style="margin-bottom: 5px;" >
			<? if($res->fields['KNOWLEDGEBASE_URL'] != ''){ ?>
				<a href="help_docs?id=<?=$res->fields['KNOWLEDGEBASE_URL']?>" target="_blank" >View Knowledge Base Article</a>
				<? } ?>
			</div>
		</div>
	</div>
	
	<div class="row">
		<div class="col-md-12">
			<div class="form-group" style="margin-bottom: 5px;" >
				<b style='font-weight: bold;' ><?=LOCATION.': ' ?></b>
				<?=$res->fields['LOCATION']?>
			</div>
		</div>
	</div>
	
	<div class="row">
		<div class="col-md-12">
			<div class="form-group" style="margin-bottom: 5px;" >
				<b style='font-weight: bold;' ><?=SUBJECT.': ' ?></b>
				<?=$res->fields['SUBJECT']?>
			</div>
		</div>
	</div>
	
	<div class="row">
		<div class="col-md-12">
			<div class="form-group" style="margin-bottom: 5px;" >
				<b style='font-weight: bold;' ><?=RELEASE_NOTES.': ' ?></b>
				<?=nl2br($res->fields['RELEASE_NOTES']) ?>
			</div>
		</div>
	</div>
	<hr />
<?	}
	$res->MoveNext();
} ?>