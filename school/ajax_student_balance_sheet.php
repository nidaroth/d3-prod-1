<? require_once("../global/config.php");
require_once("../language/student.php");
require_once("../language/common.php");

if ($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '') {
	header("location:../index");
	exit;
}
$PK_STUDENT_MASTER111  		= $_REQUEST['id'];
$PK_STUDENT_ENROLLMENT111  	= $_REQUEST['eids'];
$t  						= $_REQUEST['t'];

$cond111 = "";
if (isset($_REQUEST['eids']))
	$cond111 = " AND PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT111) ";

?>
<table class="table table-hover table-bordered">
	<tbody>
		<tr>
			<td style="background-color: #9992a0;">
				<div style="font-weight:bold;"><?= ESTIMATED_FEES ?></div>
			</td>
			<? $AY_EST 				= array();
			$EST_PK_AR_LEDGER_CODE 	= array();
			$EST_AR_LEDGER_CODE 	= array();
			$res_ay = $db->Execute("select * FROM (select DISTINCT(ACADEMIC_YEAR) as ACADEMIC_YEAR from S_STUDENT_FEE_BUDGET WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER111' $cond111 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' union select DISTINCT(ACADEMIC_YEAR) as ACADEMIC_YEAR from S_STUDENT_DISBURSEMENT WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER111' $cond111 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY ACADEMIC_YEAR ASC) as TEMP ORDER BY ACADEMIC_YEAR ASC ");
			while (!$res_ay->EOF) {
				$AY_EST[] = $res_ay->fields['ACADEMIC_YEAR']; ?>
				<td style="background-color: #9992a0;">
					<div style="font-weight:bold;"><?= AY . ' ' . $res_ay->fields['ACADEMIC_YEAR'] ?></div>
				</td>
			<? $res_ay->MoveNext();
			} ?>
			<td style="background-color: #9992a0;">
				<div style="font-weight:bold;"><?= TOTAL ?></div>
			</td>
		</tr>
		<? $res_ay = $db->Execute("select CONCAT(M_AR_LEDGER_CODE.CODE, ' - ', M_AR_LEDGER_CODE.LEDGER_DESCRIPTION) AS LEDGER, S_STUDENT_FEE_BUDGET.PK_AR_LEDGER_CODE from S_STUDENT_FEE_BUDGET LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_FEE_BUDGET.PK_AR_LEDGER_CODE WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER111' $cond111 AND S_STUDENT_FEE_BUDGET.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' GROUP BY S_STUDENT_FEE_BUDGET.PK_AR_LEDGER_CODE ");
		while (!$res_ay->EOF) {
			$EST_PK_AR_LEDGER_CODE[] = $res_ay->fields['PK_AR_LEDGER_CODE'];
			$EST_AR_LEDGER_CODE[] 	 = $res_ay->fields['LEDGER'];
			$res_ay->MoveNext();
		}
		$i = 0;
		$COL_TOTAL_PROG_FEE = array();
		$COL_TOTAL_COA_FEE 	= array();
		foreach ($EST_PK_AR_LEDGER_CODE as $PK_AR_LEDGER_CODE) {
			$row_total_prog = 0;
			$row_total_coa  = 0; ?>
			<tr>
				<td><?= $EST_AR_LEDGER_CODE[$i] ?></td>
				<? foreach ($AY_EST as $AY_EST_1) {
					$res_ay1 = $db->Execute("select SUM(FEE_AMOUNT) as FEE_AMOUNT ,PK_FEE_TYPE from S_STUDENT_FEE_BUDGET WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER111' $cond111 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACADEMIC_YEAR = '$AY_EST_1' AND PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' GROUP BY PK_FEE_TYPE ");
					if ($res_ay1->fields['PK_FEE_TYPE'] == 2) {
						$row_total_prog					+= $res_ay1->fields['FEE_AMOUNT'];
						$COL_TOTAL_PROG_FEE[$AY_EST_1]  += $res_ay1->fields['FEE_AMOUNT'];;
					}

					$row_total_coa 					+= $res_ay1->fields['FEE_AMOUNT'];
					$COL_TOTAL_COA_FEE[$AY_EST_1]   += $res_ay1->fields['FEE_AMOUNT'];; ?>
					<td>
						<div style="text-align:right">$ <?= number_format_value_checker($res_ay1->fields['FEE_AMOUNT'], 2) ?></div>
					</td>
				<? }
				$COL_TOTAL_COA_FEE[-1]  += $row_total_coa;
				$COL_TOTAL_PROG_FEE[-1] += $row_total_prog; ?>
				<td>
					<div style="text-align:right">$ <?= number_format_value_checker($row_total_coa, 2) ?></div>
				</td>
			</tr>
		<? $i++;
			$res_ay->MoveNext();
		} ?>
		<tr>
			<td>
				<div style="font-weight:bold;"><?= EST_PROGRAM_COST ?></div>
			</td>
			<? foreach ($AY_EST as $AY_EST_1) { ?>
				<td>
					<div style="text-align:right;font-weight:bold;">$ <?= number_format_value_checker($COL_TOTAL_PROG_FEE[$AY_EST_1], 2) ?></div>
				</td>
			<? } ?>
			<td>
				<div style="text-align:right;font-weight:bold;">$ <?= number_format_value_checker($COL_TOTAL_PROG_FEE[-1], 2) ?></div>
			</td>
		</tr>
		<tr>
			<td>
				<div style="font-weight:bold;"><?= EST_COA ?></div>
			</td>
			<? foreach ($AY_EST as $AY_EST_1) { ?>
				<td>
					<div style="text-align:right;font-weight:bold;">$ <?= number_format_value_checker($COL_TOTAL_COA_FEE[$AY_EST_1], 2) ?></div>
				</td>
			<? } ?>
			<td>
				<div style="text-align:right;font-weight:bold;">$ <?= number_format_value_checker($COL_TOTAL_COA_FEE[-1], 2) ?></div>
			</td>
		</tr>

		<tr>
			<td style="background-color: #9992a0;">
				<div style="font-weight:bold;"><?= ESTIMATED_DISBURSEMENTS ?></div>
			</td>
			<? $EST_PK_AR_LEDGER_CODE 	= array();
			$EST_AR_LEDGER_CODE 	= array();
			foreach ($AY_EST as $AY_EST12) {  ?>
				<td style="background-color: #9992a0;">
					<div style="font-weight:bold;"><?= AY . ' ' . $AY_EST12 ?></div>
				</td>
			<? $res_ay->MoveNext();
			} ?>

			<td style="background-color: #9992a0;">
				<div style="font-weight:bold;"><?= TOTAL ?></div>
			</td>
		</tr>
		<? $res_ay = $db->Execute("select CONCAT(M_AR_LEDGER_CODE.CODE, ' - ', M_AR_LEDGER_CODE.LEDGER_DESCRIPTION) AS LEDGER, S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE from S_STUDENT_DISBURSEMENT LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER111' $cond111 AND S_STUDENT_DISBURSEMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' GROUP BY S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE ");
		while (!$res_ay->EOF) {
			$EST_PK_AR_LEDGER_CODE[] = $res_ay->fields['PK_AR_LEDGER_CODE'];
			$EST_AR_LEDGER_CODE[] 	 = $res_ay->fields['LEDGER'];
			$res_ay->MoveNext();
		}
		$i = 0;
		$COL_TOTAL_AWARD 	= array();
		foreach ($EST_PK_AR_LEDGER_CODE as $PK_AR_LEDGER_CODE) {
			$row_total = 0; ?>
			<tr>
				<td><?= $EST_AR_LEDGER_CODE[$i] ?></td>
				<? foreach ($AY_EST as $AY_EST_1) {
					$res_ay1 = $db->Execute("select SUM(DISBURSEMENT_AMOUNT) AS DISBURSEMENT_AMOUNT from S_STUDENT_DISBURSEMENT WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER111' $cond111 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACADEMIC_YEAR = '$AY_EST_1' AND PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' ");
					$row_total 					  += $res_ay1->fields['DISBURSEMENT_AMOUNT'];
					$COL_TOTAL_AWARD[$AY_EST_1]   += $res_ay1->fields['DISBURSEMENT_AMOUNT'];; ?>
					<td>
						<div style="text-align:right">$ <?= number_format_value_checker($res_ay1->fields['DISBURSEMENT_AMOUNT'], 2) ?></div>
					</td>
				<? }
				$COL_TOTAL_AWARD[-1] += $row_total;  ?>
				<td>
					<div style="text-align:right">$ <?= number_format_value_checker($row_total, 2) ?></div>
				</td>
			</tr>
		<? $i++;
			$res_ay->MoveNext();
		} ?>
		<tr>
			<td>
				<div style="font-weight:bold;"><?= TOTAL_ESTIMATED_DISBURSEMENTS ?></div>
			</td>
			<? foreach ($AY_EST as $AY_EST_1) { ?>
				<td>
					<div style="text-align:right;font-weight:bold;">$ <?= number_format_value_checker($COL_TOTAL_AWARD[$AY_EST_1], 2) ?></div>
				</td>
			<? } ?>
			<td>
				<div style="text-align:right;font-weight:bold;">$ <?= number_format_value_checker($COL_TOTAL_AWARD[-1], 2) ?></div>
			</td>
		</tr>
		<tr>
			<td>
				<div style="font-weight:bold;"><?= PROJECTED_BALANCE ?></div>
			</td>
			<? foreach ($AY_EST as $AY_EST_1) { ?>
				<td>
					<div style="text-align:right;font-weight:bold;">$ <?= number_format_value_checker(($COL_TOTAL_PROG_FEE[$AY_EST_1] - $COL_TOTAL_AWARD[$AY_EST_1]), 2) ?></div>
				</td>
			<? } ?>
			<td>
				<div style="text-align:right;font-weight:bold;">$ <?= number_format_value_checker(($COL_TOTAL_PROG_FEE[-1] - $COL_TOTAL_AWARD[-1]), 2) ?></div>
			</td>
		</tr>
	</tbody>
</table>