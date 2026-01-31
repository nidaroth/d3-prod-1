<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/diamond_pay_transaction.php");
require_once("../language/menu.php");
require_once("check_access.php");

$res_pay = $db->Execute("select ENABLE_DIAMOND_PAY from Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if ($res_pay->fields['ENABLE_DIAMOND_PAY'] == 0 || check_access('MANAGEMENT_DIAMOND_PAY') == 0) {
	header("location:../index");
	exit;
}
$ST = date("Y-m-d", strtotime($_REQUEST['START_DATE']));
$ET = date("Y-m-d", strtotime($_REQUEST['END_DATE']));
$cond = " AND PAID_ON BETWEEN '$ST' AND '$ET' ";

if ($_REQUEST['AMOUNT'] != '')
	$cond .= " AND AMOUNT_CHARGED = '$_REQUEST[AMOUNT]' ";

if ($_REQUEST['FEE'] != '')
	$cond .= " AND CONV_FEE_AMOUNT = '$_REQUEST[FEE]' ";

if ($_REQUEST['TOTAL_CHARGE'] != '')
	$cond .= " AND TOTAL_CHARGE = '$_REQUEST[TOTAL_CHARGE]' ";

if ($_REQUEST['TRANSACTION_ID'] != '')
	$cond .= " AND ORDER_ID = '" . trim($_REQUEST['TRANSACTION_ID']) . "' ";

if ($_REQUEST['STUDENT'] != '')
	$cond .= " AND CONCAT(LAST_NAME,' ',FIRST_NAME) LIKE '%$_REQUEST[STUDENT]%' ";

if ($_REQUEST['LAST_4_CC'] != '')
	$cond .= " AND CARD_NO LIKE '%$_REQUEST[LAST_4_CC]' ";

$campus_cond1 = "";
if ($_REQUEST['PK_CAMPUS'] != '') {
	$cond .= " AND  S_STUDENT_CREDIT_CARD_PAYMENT.PK_STUDENT_ENROLLMENT > 0 AND S_STUDENT_CREDIT_CARD_PAYMENT.PK_STUDENT_ENROLLMENT IN (SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS IN ($_REQUEST[PK_CAMPUS])) ";
	$campus_cond1 = " AND S_CAMPUS.PK_CAMPUS IN ($_REQUEST[PK_CAMPUS]) ";
}

$query = "select CONCAT(LAST_NAME,' ',FIRST_NAME) as NAME, AMOUNT_CHARGED, CONV_FEE_AMOUNT, TOTAL_CHARGE, ORDER_ID, IF(PAID_ON = '0000-00-00','', DATE_FORMAT(PAID_ON, '%Y-%m-%d' )) AS PAID_ON_1, CARD_NO, S_STUDENT_CREDIT_CARD_PAYMENT.PK_STUDENT_ENROLLMENT, STUDENT_ID from S_STUDENT_CREDIT_CARD_PAYMENT, S_STUDENT_MASTER LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER  WHERE S_STUDENT_CREDIT_CARD_PAYMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_CREDIT_CARD_PAYMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND FAILED = 0 $cond GROUP BY PK_STUDENT_CREDIT_CARD_PAYMENT ORDER BY PAID_ON DESC, CONCAT(LAST_NAME,' ',FIRST_NAME) ASC ";
$_SESSION['query'] = $query;
//echo $query;
?>
<table data-toggle="table" class="table-striped" id="report_1">
	<thead>
		<tr>
			<th><?= STUDENT ?></th>
			<th>Student ID</th>
			<th><?= CAMPUS ?></th>
			<th><?= DATE_PAID ?></th>
			<th><?= AMOUNT ?></th>
			<th><?= FEE ?></th>
			<th><?= TOTAL_CHARGE ?></th>
			<th><?= LAST_4_CC ?></th>
			<th><?= TRANSACTION_ID ?></th>
		</tr>
	</thead>
	<tbody>
		<? $res_payment = $db->Execute($query);
		while (!$res_payment->EOF) { ?>
			<tr>
				<td><?= $res_payment->fields['NAME'] ?></td>
				<td><?= $res_payment->fields['STUDENT_ID'] ?></td>
				<td>
					<? $PK_STUDENT_ENROLLMENT = $res_payment->fields['PK_STUDENT_ENROLLMENT'];
					$res_campus = $db->Execute("SELECT CAMPUS_CODE FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS AND PK_STUDENT_ENROLLMENT > 0  $campus_cond1 ");
					echo $res_campus->fields['CAMPUS_CODE']; ?>
				</td>
				<td><?= $res_payment->fields['PAID_ON_1'] ?></td>
				<td>
					<div style="padding-top: 11px;width:100%;text-align:right">$ <?= number_format_value_checker($res_payment->fields['AMOUNT_CHARGED'], 2) ?></div>
				</td>
				<td>
					<div style="padding-top: 11px;width:100%;text-align:right">$ <?= number_format_value_checker($res_payment->fields['CONV_FEE_AMOUNT'], 2) ?></div>
				</td>
				<td>
					<div style="padding-top: 11px;width:100%;text-align:right">$ <?= number_format_value_checker($res_payment->fields['TOTAL_CHARGE'], 2) ?></div>
				</td>
				<td>
					<? if ($res_payment->fields['CARD_NO'] != '')
						echo substr($res_payment->fields['CARD_NO'], -4) ?>
				</td>
				<td><?= $res_payment->fields['ORDER_ID'] ?></td>
			</tr>
		<? $res_payment->MoveNext();
		} ?>
	</tbody>
</table>