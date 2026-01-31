<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/pdf_template.php");

if($_REQUEST['TAG_NAME'] != '')
	$cond .= " AND TAGS like '%$_REQUEST[TAG_NAME]%' ";
	
if($_REQUEST['PK_DOCUMENT_TEMPLATE_SUB_CATEGORY'] != '')
	$cond .= " AND Z_DOCUMENT_TEMPLATE_TAG.PK_DOCUMENT_TEMPLATE_SUB_CATEGORY IN ($_REQUEST[PK_DOCUMENT_TEMPLATE_SUB_CATEGORY]) ";
else {
	if($_REQUEST['PK_DOCUMENT_TEMPLATE_CATEGORY'] != '') {
		$PK_DOCUMENT_TEMPLATE_SUB_CATEGORY = '';
		$res_type = $db->Execute("SELECT PK_DOCUMENT_TEMPLATE_SUB_CATEGORY FROM Z_DOCUMENT_TEMPLATE_SUB_CATEGORY WHERE ACTIVE = 1 AND PK_DOCUMENT_TEMPLATE_CATEGORY IN ($_REQUEST[PK_DOCUMENT_TEMPLATE_CATEGORY]) ");
		while (!$res_type->EOF) {
			if($PK_DOCUMENT_TEMPLATE_SUB_CATEGORY != '')
				$PK_DOCUMENT_TEMPLATE_SUB_CATEGORY .= ',';
				
			$PK_DOCUMENT_TEMPLATE_SUB_CATEGORY .= $res_type->fields['PK_DOCUMENT_TEMPLATE_SUB_CATEGORY'];
			
			$res_type->MoveNext();
		}
		$cond .= " AND Z_DOCUMENT_TEMPLATE_TAG.PK_DOCUMENT_TEMPLATE_SUB_CATEGORY IN ($PK_DOCUMENT_TEMPLATE_SUB_CATEGORY) ";
	}
}

if($_REQUEST['t'] == 1) {
	$cond .= " AND Z_DOCUMENT_TEMPLATE_TAG.PK_DOCUMENT_TEMPLATE_SUB_CATEGORY NOT IN (5) ";
} else 
	$cond .= " AND Z_DOCUMENT_TEMPLATE_TAG.PK_DOCUMENT_TEMPLATE_SUB_CATEGORY NOT IN (6,7) ";

?>
<div class="table-responsive p-20">
	<table class="table table-hover" >
		<thead>
			<tr>
				<th><?=CATEGORY?></th>
				<th><?=SUBCATEGORY?></th>
				<th><?=TAGS?></th>
				<? if($_REQUEST['t'] == 1) { ?>
					<th><?=NOTIFICATION?></th>
				<? } ?>
			</tr>
		</thead>
		<tbody>
			<? $res_type = $db->Execute("SELECT DOCUMENT_TEMPLATE_CATEGORY, DOCUMENT_TEMPLATE_SUB_CATEGORY, TAGS, NOTIFICATION  
			FROM 
			Z_DOCUMENT_TEMPLATE_TAG 
			LEFT JOIN Z_DOCUMENT_TEMPLATE_SUB_CATEGORY ON Z_DOCUMENT_TEMPLATE_SUB_CATEGORY.PK_DOCUMENT_TEMPLATE_SUB_CATEGORY = Z_DOCUMENT_TEMPLATE_TAG.PK_DOCUMENT_TEMPLATE_SUB_CATEGORY 
			LEFT JOIN Z_DOCUMENT_TEMPLATE_CATEGORY ON Z_DOCUMENT_TEMPLATE_CATEGORY.PK_DOCUMENT_TEMPLATE_CATEGORY = Z_DOCUMENT_TEMPLATE_SUB_CATEGORY.PK_DOCUMENT_TEMPLATE_CATEGORY 
			WHERE Z_DOCUMENT_TEMPLATE_TAG.ACTIVE = 1  $cond 
			ORDER BY DOCUMENT_TEMPLATE_CATEGORY ASC, DOCUMENT_TEMPLATE_SUB_CATEGORY ASC , TAGS ASC ");
			while (!$res_type->EOF) { ?>
				<tr >
					<td><?=$res_type->fields['DOCUMENT_TEMPLATE_CATEGORY']?></td>
					<td><?=$res_type->fields['DOCUMENT_TEMPLATE_SUB_CATEGORY']?></td>
					<td><?=$res_type->fields['TAGS']?></td>
					<? if($_REQUEST['t'] == 1) { ?>
						<td><?=$res_type->fields['NOTIFICATION']?></td>
					<? } ?>
				</tr>
			<?	$res_type->MoveNext();
			} ?>
		</tbody>
	</table>
</div>