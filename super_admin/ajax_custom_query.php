<? require_once("../global/config.php"); 
require_once("../global/image_fun.php");
require_once("../language/school_profile.php"); 

if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}
$search = $_REQUEST['search'];
$cq_cond = "";
if($search != '') {
	$cq_cond = " AND (CUSTOM_NAME LIKE '%$search%' or INTERNAL_DESCRIPTION LIKE '%$search%' or EXTERNAL_DESCRIPTION LIKE '%$search%' or SCHOOL_NAME LIKE '%$search%' ) ";
}
?>

<table class="table table-hover">
	<thead>
		<tr>
			<th>#</th>
			<th>PK</th>
			<th>Name</th>
			<th>External Description</th>
			<th>Internal Description</th>
			<th>Date Created</th>
			<th>Associated Accounts</th>
			<th>Options</th>
		</tr>
	</thead>
	<tbody>
		<? $res_type = $db->Execute("SELECT M_CUSTOM_QUERY.*, GROUP_CONCAT(SCHOOL_NAME ORDER BY SCHOOL_NAME ASC SEPARATOR '<br />' ) as SCHOOL_NAME FROM M_CUSTOM_QUERY LEFT JOIN M_CUSTOM_QUERY_ACCOUNT ON M_CUSTOM_QUERY_ACCOUNT.PK_CUSTOM_QUERY = M_CUSTOM_QUERY.PK_CUSTOM_QUERY LEFT JOIN Z_ACCOUNT ON Z_ACCOUNT.PK_ACCOUNT = M_CUSTOM_QUERY_ACCOUNT.PK_ACCOUNT WHERE 1 = 1 $cq_cond GROUP By M_CUSTOM_QUERY.PK_CUSTOM_QUERY ORDER BY CUSTOM_NAME ASC ");
		$i = 0;
		while (!$res_type->EOF) { 
			$i++; ?>
			<tr>
				<td><?=$i?></td>
				<td><?=$res_type->fields['PK_CUSTOM_QUERY']?></td>
				<td><?=$res_type->fields['CUSTOM_NAME']?></td>
				<td><?=$res_type->fields['EXTERNAL_DESCRIPTION']?></td>
				<td><?=$res_type->fields['INTERNAL_DESCRIPTION']?></td>
				<td><?=date("m/d/Y", strtotime($res_type->fields['DATE_CREATED']))?></td>
				<td>
					<?=$res_type->fields['SCHOOL_NAME']; ?>
				</td>
				<td>
					<!--<a href="custom_query?id=<?=$res_type->fields['PK_CUSTOM_QUERY']?>&s_id=<?=$_GET['id']?>" title="Edit" class="btn btn-secondary btn-circle"><i class="far fa-edit"></i> </a>
					<a href="javascript:void(0);" onclick="delete_row('<?=$res_type->fields['PK_CUSTOM_QUERY']?>','custom_query')" title="Delete" class="btn btn-primary btn-circle"><i class="far fa-trash-alt"></i> </a>-->
					
					<a href="custom_query?id=<?=$res_type->fields['PK_CUSTOM_QUERY']?>&s_id=<?=$_GET['id']?>" title="View" >View </a>&nbsp;|&nbsp;
					
					<? $res_type_1 = $db->Execute("select PK_CUSTOM_QUERY_ACCOUNT from M_CUSTOM_QUERY_ACCOUNT WHERE PK_ACCOUNT = '$_GET[id]' AND PK_CUSTOM_QUERY = '".$res_type->fields['PK_CUSTOM_QUERY']."' "); 
					if($res_type_1->RecordCount() == 0){ ?>
						<a href="accounts?act=cq&iid=<?=$res_type->fields['PK_CUSTOM_QUERY']?>&id=<?=$_GET['id']?>" >Assign</a>
					<? } else { ?>
						<a href="javascript:void(0);" onclick="delete_row('<?=$res_type_1->fields['PK_CUSTOM_QUERY_ACCOUNT']?>','custom_query_account')" title="Unassign" >Unassign</a>
					<? } ?>
				</td>
			</tr>
		<?	$res_type->MoveNext();
		} ?>
	</tbody>
</table>