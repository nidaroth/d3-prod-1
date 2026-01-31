<? require_once("../language/common.php");
require_once("../language/student.php");

require_once("check_access.php");
$FINANCE_ACCESS 	= check_access('FINANCE_ACCESS');
$ACCOUNTING_ACCESS 	= check_access('ACCOUNTING_ACCESS');

if ($FINANCE_ACCESS == 0 && $ACCOUNTING_ACCESS == 0) {
	header("location:../index");
	exit;
}

$worksheet_cond = " AND PK_STUDENT_MASTER = '$_REQUEST[sid]' ";
if ($_REQUEST['ws_eid'] != '')
	$worksheet_cond .= " AND PK_STUDENT_ENROLLMENT IN ($_REQUEST[ws_eid]) ";
?>

<div id="WORKSHEET_DETAIL_DIV">
	<div class="row">
		<div class="col-md-6 align-self-center">
			<div class="row">
				<div class="col-md-12" style="height:150px;overflow-y:scroll;">
					<table class="table table-hover table-bordered table_5" style="margin-bottom: 0;">
						<tbody>
							<tr>
								<td style="background-color: #9992a0;" width="50%">
									<div style="font-weight:bold;"><?= FEES ?></div>
								</td>
								<td style="background-color: #9992a0;" width="20%">
									<div style="font-weight:bold;"><?= TRANS_DATE ?></div>
								</td>
								<td style="background-color: #9992a0;" width="30%">
									<div style="font-weight:bold;text-align:right;"><?= AMOUNT ?></div>
								</td>
							</tr>
							<? $TOT_DEBIT = 0;
							$res_ledger = $db->Execute("select PK_STUDENT_LEDGER,LEDGER_DESCRIPTION,TRANSACTION_DATE AS TRANSACTION_DATE_1 ,IF(TRANSACTION_DATE = '0000-00-00','', DATE_FORMAT(TRANSACTION_DATE, '%m/%d/%Y' )) AS TRANSACTION_DATE,CREDIT,DEBIT, M_AR_LEDGER_CODE.CODE from S_STUDENT_LEDGER LEFT JOIN M_AR_LEDGER_CODE On M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_LEDGER.PK_AR_LEDGER_CODE WHERE S_STUDENT_LEDGER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND DEBIT != 0 AND (PK_PAYMENT_BATCH_DETAIL > 0 OR PK_MISC_BATCH_DETAIL > 0 OR PK_TUITION_BATCH_DETAIL > 0 ) $worksheet_cond ORDER BY TRANSACTION_DATE_1 ASC, CODE ASC "); //Ticket # 1611 //Ticket # 1613
							while (!$res_ledger->EOF) {
								$TOT_DEBIT  += $res_ledger->fields['DEBIT']; ?>

								<tr>
									<td><?= $res_ledger->fields['CODE'] ?></td>
									<td><?= $res_ledger->fields['TRANSACTION_DATE'] ?></td>
									<td>
										<div style="text-align:right">$ <?= number_format_value_checker($res_ledger->fields['DEBIT'], 2) ?></div>
									</td>
								</tr>
							<? $res_ledger->MoveNext();
							} ?>
						</tbody>
					</table>
				</div>
				<div class="col-md-12" style="max-width: 97.7%;">
					<table class="table table-hover table-bordered">
						<tbody>
							<tr>
								<td width="50%">
									<div style="font-weight:bold"><?= TOTAL ?></div>
								</td>
								<td width="20%"></td>
								<td width="30%">
									<div style="text-align:right;font-weight:bold">$ <?= number_format_value_checker($TOT_DEBIT, 2) ?></div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="col-md-6 align-self-center">
			<div class="row" style="margin-top: -17px;">
				<div class="col-md-6" style="font-weight:bold">
					<div class="table-responsive">
						<table class="table table-borderless">
							<tbody>
								<tr>
									<td class="col-sm-4" style="font-weight:bold"><?= FEE_TOTAL ?></td>
									<td class="col-sm-4  border-bottom border-dark"> </td>
									<td class="col-sm-4  border-bottom border-dark" style="font-weight:bold;text-align:right;">$ <?= number_format_value_checker($TOT_DEBIT, 2) ?></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>

	<br />
	<div class="row">
		<div class="col-md-6 align-self-center">
			<div class="row">
				<div class="col-md-12" style="height:150px;overflow-y:scroll;">
					<table class="table table-hover table-bordered table_5" style="margin-bottom: 0;">
						<tbody>
							<tr>
								<td style="background-color: #9992a0;" width="50%">
									<div style="font-weight:bold;"><?= RECEIVED ?></div>
								</td>
								<td style="background-color: #9992a0;" width="20%">
									<div style="font-weight:bold;"><?= TRANS_DATE ?></div>
								</td>
								<td style="background-color: #9992a0;" width="30%">
									<div style="font-weight:bold;text-align:right;"><?= AMOUNT ?></div>
								</td>
							</tr>
							<? $TOT_CREDIT = 0;
							$res_ledger = $db->Execute("select PK_STUDENT_LEDGER,LEDGER_DESCRIPTION, TRANSACTION_DATE AS TRANSACTION_DATE_1 ,IF(TRANSACTION_DATE = '0000-00-00','', DATE_FORMAT(TRANSACTION_DATE, '%m/%d/%Y' )) AS TRANSACTION_DATE,CREDIT,DEBIT, M_AR_LEDGER_CODE.CODE from S_STUDENT_LEDGER LEFT JOIN M_AR_LEDGER_CODE On M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_LEDGER.PK_AR_LEDGER_CODE WHERE S_STUDENT_LEDGER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND CREDIT != 0 AND (PK_PAYMENT_BATCH_DETAIL > 0 OR PK_MISC_BATCH_DETAIL > 0 OR PK_TUITION_BATCH_DETAIL > 0 ) $worksheet_cond ORDER BY TRANSACTION_DATE_1 ASC, CODE ASC  "); //Ticket # 1611 ///Ticket # 1613
							while (!$res_ledger->EOF) {
								$TOT_CREDIT  += $res_ledger->fields['CREDIT']; ?>
								<tr>
									<td><?= $res_ledger->fields['CODE'] ?></td>
									<td><?= $res_ledger->fields['TRANSACTION_DATE'] ?></td>
									<td>
										<div style="text-align:right">$ <?= number_format_value_checker($res_ledger->fields['CREDIT'], 2) ?></div>
									</td>
								</tr>
							<? $res_ledger->MoveNext();
							} ?>
						</tbody>
					</table>
				</div>
				<div class="col-md-12" style="max-width: 97.7%;">
					<table class="table table-hover table-bordered">
						<tbody>
							<tr>
								<td width="50%">
									<div style="font-weight:bold"><?= TOTAL ?></div>
								</td>
								<td width="20%"></td>
								<td width="30%">
									<div style="text-align:right;font-weight:bold">$ <?= number_format_value_checker($TOT_CREDIT, 2) ?></div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="col-md-6 align-self-center">
			<div class="row" style="height:159px;margin-top: -27px;">
				<div class="col-md-6" style="font-weight:bold">
					<div class="table-responsive">
						<table class="table table-borderless">
							<tbody>
								<tr>
									<td class="col-sm-4" style="font-weight:bold"><?= RECEIVED_TOTAL ?></td>
									<td class="col-sm-4"></td>
									<td class="col-sm-4" style="font-weight:bold;text-align:right;">$ <?= number_format_value_checker($TOT_CREDIT, 2) ?></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-md-6" style="font-weight:bold">
					<div class="table-responsive">
						<table class="table table-borderless">
							<tbody>
								<tr>
									<td class="col-sm-4" style="font-weight:bold"><?= SUB_TOTAL ?></td>
									<td class="col-sm-4  border-bottom border-dark"></td>
									<td class="col-sm-4  border-bottom border-dark" style="font-weight:bold;text-align:right;">$ <?= number_format_value_checker(($TOT_DEBIT - $TOT_CREDIT), 2) ?></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>

	<br />
	<div class="row">
		<div class="col-md-6 align-self-center">
			<div class="row">
				<div class="col-md-12" style="height:150px;overflow-y:scroll;">
					<table class="table table-hover table-bordered table_5" style="margin-bottom: 0;">
						<tbody>
							<tr>
								<td style="background-color: #9992a0;" width="50%">
									<div style="font-weight:bold;"><?= PROJECTED ?></div>
								</td>
								<td style="background-color: #9992a0;" width="20%">
									<div style="font-weight:bold;"><?= TRANS_DATE ?></div>
								</td>
								<td style="background-color: #9992a0;" width="30%">
									<div style="font-weight:bold;text-align:right;"><?= AMOUNT ?></div>
								</td>
							</tr>
							<? $TOT_PROJECTED = 0;
							$res_ledger = $db->Execute("select DISBURSEMENT_AMOUNT, DISBURSEMENT_DATE as DISBURSEMENT_DATE_1, IF(DISBURSEMENT_DATE = '0000-00-00','', DATE_FORMAT(DISBURSEMENT_DATE, '%m/%d/%Y' )) AS DISBURSEMENT_DATE, M_AR_LEDGER_CODE.CODE from S_STUDENT_DISBURSEMENT LEFT JOIN M_AR_LEDGER_CODE On M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE WHERE S_STUDENT_DISBURSEMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DISBURSEMENT_STATUS = 2 $worksheet_cond ORDER BY DISBURSEMENT_DATE_1 ASC, CODE ASC "); //Ticket # 1613
							while (!$res_ledger->EOF) {
								$TOT_PROJECTED  += $res_ledger->fields['DISBURSEMENT_AMOUNT']; ?>
								<tr>
									<td><?= $res_ledger->fields['CODE'] ?></td>
									<td><?= $res_ledger->fields['DISBURSEMENT_DATE'] ?></td>
									<td>
										<div style="text-align:right">$ <?= number_format_value_checker($res_ledger->fields['DISBURSEMENT_AMOUNT'], 2) ?></div>
									</td>
								</tr>
							<? $res_ledger->MoveNext();
							} ?>
						</tbody>
					</table>
				</div>
				<div class="col-md-12" style="max-width: 97.7%;">
					<table class="table table-hover table-bordered">
						<tbody>
							<tr>
								<td width="50%">
									<div style="font-weight:bold"><?= TOTAL ?></div>
								</td>
								<td width="20%"></td>
								<td width="30%">
									<div style="text-align:right;font-weight:bold">$ <?= number_format_value_checker($TOT_PROJECTED, 2) ?></div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="col-md-6 align-self-center">
			<div class="row" style="height:159px;margin-top: -27px;">
				<div class="col-md-6" style="font-weight:bold">
					<div class="table-responsive">
						<table class="table table-borderless">
							<tbody>
								<tr>
									<td class="col-sm-4" style="font-weight:bold"><?= PROJECTED_TOTAL ?></td>
									<td class="col-sm-4"></td>
									<td class="col-sm-4" style="font-weight:bold;text-align:right;">$ <?= number_format_value_checker($TOT_PROJECTED, 2) ?></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>

			<div class="row">

				<div class="col-md-6" style="font-weight:bold">
					<div class="table-responsive">
						<table class="table table-borderless">
							<tbody>
								<tr>
									<td class="col-sm-4" style="font-weight:bold"><?= GRAND_TOTAL ?></td>
									<td class="col-sm-4  border-bottom border-dark"></td>
									<td class="col-sm-4  border-bottom border-dark" style="font-weight:bold;text-align:right;">$ <?= number_format_value_checker(($TOT_DEBIT - $TOT_CREDIT - $TOT_PROJECTED), 2) ?></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>


			</div>
		</div>
	</div>
</div>

<div id="WORKSHEET_SUMMARY_DIV" style="display:none">
	<div class="row">
		<div class="col-md-6 align-self-center">
			<div class="row">
				<div class="col-md-12" style="max-height:150px;overflow-y:scroll;">
					<table class="table table-hover table-bordered table_5" style="margin-bottom: 0;">
						<tbody>
							<tr>
								<td style="background-color: #9992a0;" width="70%">
									<div style="font-weight:bold;"><?= FEES ?></div>
								</td>
								<td style="background-color: #9992a0;" width="30%">
									<div style="font-weight:bold;"><?= AMOUNT ?></div>
								</td>
							</tr>
							<? $TOT_DEBIT = 0;
							$res_ledger = $db->Execute("select SUM(CREDIT) as CREDIT, SUM(DEBIT) as DEBIT, M_AR_LEDGER_CODE.CODE from S_STUDENT_LEDGER LEFT JOIN M_AR_LEDGER_CODE On M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_LEDGER.PK_AR_LEDGER_CODE WHERE S_STUDENT_LEDGER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND DEBIT != 0 AND (PK_PAYMENT_BATCH_DETAIL > 0 OR PK_MISC_BATCH_DETAIL > 0 OR PK_TUITION_BATCH_DETAIL > 0 ) $worksheet_cond GROUP BY S_STUDENT_LEDGER.PK_AR_LEDGER_CODE ORDER BY CODE ASC"); //Ticket # 1611 //Ticket # 1613
							while (!$res_ledger->EOF) {
								$TOT_DEBIT  += $res_ledger->fields['DEBIT']; ?>

								<tr>
									<td><?= $res_ledger->fields['CODE'] ?></td>
									<td>
										<div style="text-align:right">$ <?= number_format_value_checker($res_ledger->fields['DEBIT'], 2) ?></div>
									</td>
								</tr>
							<? $res_ledger->MoveNext();
							} ?>
						</tbody>
					</table>
				</div>
				<div class="col-md-12" style="max-width: 97.7%;">
					<table class="table table-hover table-bordered">
						<tbody>
							<tr>
								<td width="70%">
									<div style="font-weight:bold"><?= TOTAL ?></div>
								</td>
								<td width="30%">
									<div style="text-align:right;font-weight:bold">$ <?= number_format_value_checker($TOT_DEBIT, 2) ?></div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="col-md-6 align-self-center">
			<div class="row">
				<div class="col-md-6" style="font-weight:bold">
					<div class="table-responsive">
						<table class="table table-borderless">
							<tbody>
								<tr>
									<td class="col-sm-4" style="font-weight:bold"><?= FEE_TOTAL ?></td>
									<td class="col-sm-4  border-bottom border-dark" style="font-weight:bold;text-align:right;">$ <?= number_format_value_checker($TOT_DEBIT, 2) ?></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>

	<br />
	<div class="row">
		<div class="col-md-6 align-self-center">
			<div class="row">
				<div class="col-md-12" style="max-height:150px;overflow-y:scroll;">
					<table class="table table-hover table-bordered table_5" style="margin-bottom: 0;">
						<tbody>
							<tr>
								<td style="background-color: #9992a0;" width="70%">
									<div style="font-weight:bold;"><?= RECEIVED ?></div>
								</td>
								<td style="background-color: #9992a0;" width="30%">
									<div style="font-weight:bold;"><?= AMOUNT ?></div>
								</td>
							</tr>
							<? $TOT_CREDIT = 0;
							$res_ledger = $db->Execute("select SUM(CREDIT) as CREDIT, SUM(DEBIT) as DEBIT, M_AR_LEDGER_CODE.CODE from S_STUDENT_LEDGER LEFT JOIN M_AR_LEDGER_CODE On M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_LEDGER.PK_AR_LEDGER_CODE WHERE  S_STUDENT_LEDGER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND CREDIT != 0 AND (PK_PAYMENT_BATCH_DETAIL > 0 OR PK_MISC_BATCH_DETAIL > 0 OR PK_TUITION_BATCH_DETAIL > 0 ) $worksheet_cond GROUP BY S_STUDENT_LEDGER.PK_AR_LEDGER_CODE ORDER BY CODE ASC "); //Ticket # 1611 //Ticket # 1613
							while (!$res_ledger->EOF) {
								$TOT_CREDIT  += $res_ledger->fields['CREDIT']; ?>
								<tr>
									<td><?= $res_ledger->fields['CODE'] ?></td>
									<td>
										<div style="text-align:right">$ <?= number_format_value_checker($res_ledger->fields['CREDIT'], 2) ?></div>
									</td>
								</tr>
							<? $res_ledger->MoveNext();
							} ?>
						</tbody>
					</table>
				</div>
				<div class="col-md-12" style="max-width: 97.7%;">
					<table class="table table-hover table-bordered">
						<tbody>
							<tr>
								<td width="70%">
									<div style="font-weight:bold"><?= TOTAL ?></div>
								</td>
								<td width="30%">
									<div style="text-align:right;font-weight:bold">$ <?= number_format_value_checker($TOT_CREDIT, 2) ?></div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="col-md-6 align-self-center">
			<div class="row">
				<div class="col-md-6" style="font-weight:bold">
					<div class="table-responsive">
						<table class="table table-borderless">
							<tbody>
								<tr>
									<td class="col-sm-4" style="font-weight:bold"><?= RECEIVED_TOTAL ?></td>
									<td class="col-sm-4  border-bottom border-dark" style="font-weight:bold;text-align:right;">$ <?= number_format_value_checker($TOT_CREDIT, 2) ?></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

			</div>

			<div class="row">

				<div class="col-md-6 mt-5" style="font-weight:bold">
					<div class="table-responsive">
						<table class="table table-borderless">
							<tbody>
								<tr>
									<td class="col-sm-4" style="font-weight:bold"><?= SUB_TOTAL ?></td>
									<td class="col-sm-4  border-bottom border-dark" style="font-weight:bold;text-align:right;">$ <?= number_format_value_checker(($TOT_DEBIT - $TOT_CREDIT), 2) ?></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

			</div>


		</div>
	</div>

	<br />
	<div class="row">
		<div class="col-md-6 align-self-center">
			<div class="row">
				<div class="col-md-12" style="max-height:150px;overflow-y:scroll;">
					<table class="table table-hover table-bordered table_5" style="margin-bottom: 0;">
						<tbody>
							<tr>
								<td style="background-color: #9992a0;" width="70%">
									<div style="font-weight:bold;"><?= PROJECTED ?></div>
								</td>
								<td style="background-color: #9992a0;" width="30%">
									<div style="font-weight:bold;"><?= AMOUNT ?></div>
								</td>
							</tr>
							<? $TOT_PROJECTED = 0;
							$res_ledger = $db->Execute("select SUM(DISBURSEMENT_AMOUNT) AS DISBURSEMENT_AMOUNT, M_AR_LEDGER_CODE.CODE from S_STUDENT_DISBURSEMENT LEFT JOIN M_AR_LEDGER_CODE On M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE WHERE S_STUDENT_DISBURSEMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DISBURSEMENT_STATUS = 2 $worksheet_cond GROUP BY S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE ORDER BY CODE ASC "); //Ticket # 1613
							while (!$res_ledger->EOF) {
								$TOT_PROJECTED  += $res_ledger->fields['DISBURSEMENT_AMOUNT']; ?>
								<tr>
									<td><?= $res_ledger->fields['CODE'] ?></td>
									<td>
										<div style="text-align:right">$ <?= number_format_value_checker($res_ledger->fields['DISBURSEMENT_AMOUNT'], 2) ?></div>
									</td>
								</tr>
							<? $res_ledger->MoveNext();
							} ?>
						</tbody>
					</table>
				</div>
				<div class="col-md-12" style="max-width: 97.7%;">
					<table class="table table-hover table-bordered">
						<tbody>
							<tr>
								<td width="70%">
									<div style="font-weight:bold"><?= TOTAL ?></div>
								</td>
								<td width="30%">
									<div style="text-align:right;font-weight:bold">$ <?= number_format_value_checker($TOT_PROJECTED, 2) ?></div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="col-md-6 align-self-center">
			<div class="row">
				<div class="col-md-6 mt-5" style="font-weight:bold">
					<div class="table-responsive">
						<table class="table table-borderless">
							<tbody>
								<tr>
									<td class="col-sm-4" style="font-weight:bold"><?= PROJECTED_TOTAL ?></td>
									<td class="col-sm-4  border-bottom border-dark" style="font-weight:bold;text-align:right;">$ <?= number_format_value_checker($TOT_PROJECTED, 2) ?></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

			</div>

			<div class="row">
				<div class="col-md-6 mt-5" style="font-weight:bold">
					<div class="table-responsive">
						<table class="table table-borderless">
							<tbody>
								<tr>
									<td class="col-sm-4" style="font-weight:bold"><?= GRAND_TOTAL ?></td>
									<td class="col-sm-4  border-bottom border-dark" style="font-weight:bold;text-align:right;">$ <?= number_format_value_checker(($TOT_DEBIT - $TOT_CREDIT - $TOT_PROJECTED), 2) ?></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

			</div>

			<br /><br /><br /><br />
		</div>
	</div>
</div>