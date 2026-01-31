<? require_once("../global/config.php");
require_once("../language/student.php");
require_once("../language/common.php");

if ($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '') {
    header("location:../index");
    exit;
}
$PK_STUDENT_MASTER111          = $_REQUEST['id'];
$PK_STUDENT_ENROLLMENT111      = $_REQUEST['eids'];
$t                          = $_REQUEST['t'];

$cond111 = "";
if (isset($_REQUEST['eids']))
    $cond111 = " AND S_STUDENT_LEDGER.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT111) ";

?>
<style>
    .tableFixHead {
        overflow-y: auto;
        height: 500px;
    }

    .tableFixHead thead th {
        position: sticky;
        top: 0;
    }

    .tableFixHead thead th {
        background: #E8E8E8;
    }
</style>
<div class="tableFixHead"> <!-- Ticket # 1016  -->
    <table class="table table-hover table-striped" id="stud_ledger_table_1">
        <thead>
            <tr>
                <th class="sticky_header" scope="col"><?= TRANSACTION_DATE ?></th>
                <th class="sticky_header" scope="col"><?= LEDGER_CODE ?></th>
                <th class="sticky_header" scope="col"><?= AY_1 ?></th>
                <th class="sticky_header" scope="col"><?= AP_1 ?></th>
                <th class="sticky_header" scope="col" style="text-align: right;"><?= DEBIT ?></th>
                <th class="sticky_header" scope="col" style="text-align: right;"><?= CREDIT ?></th>
                <th class="sticky_header" scope="col" style="text-align: right;"><?= BALANCE ?></th>
                <th class="sticky_header" scope="col"><?= DESCRIPTION ?></th>

                <th class="sticky_header" scope="col"><?= SOURCE ?></th>
                <th class="sticky_header" scope="col"><?= RECEIPT_NO ?></th>
                <?php if ($_SESSION['PK_ACCOUNT'] == 505) { ?>
                    <th class="sticky_header" scope="col"><?= REFERENCE_NO ?></th> <!-- DIAM-1329 -->
                <?php } ?>
                <th class="sticky_header" scope="col"><?= CHECK_NO ?></th>
                <th class="sticky_header" scope="col"><?= ENROLLMENT ?></th>
                <th class="sticky_header" scope="col"><?= PAYMENT_TYPE_DETAIL ?></th>
                <th class="sticky_header" scope="col" style="text-align: right;"><?= GROSS_AMOUNT ?></th>
                <th class="sticky_header" scope="col" style="text-align: right;"><?= NET_AMOUNT ?></th>
                <th class="sticky_header" scope="col"><?= BATCH_DETAIL ?></th>
                <th class="sticky_header" scope="col"><?= TERM_BLOCK ?></th>
                <th class="sticky_header" scope="col"><?= PYA ?></th>
                <th class="sticky_header" scope="col"><?= CREATED_DATE ?></th>
            </tr>
        </thead>
        <!-- Ticket # 1016  -->
        <tbody>
            <? $BALANCE = 0;
            $TOT_DEBIT    = 0;
            $TOT_CREDIT    = 0;
            //PK_STUDENT_FEE_BUDGET, PK_STUDENT_DISBURSEMENT, PK_PAYMENT_BATCH_DETAIL, PK_MISC_BATCH_DETAIL, PK_TUITION_BATCH_DETAIL
            $res_ledger = $db->Execute("select PK_STUDENT_LEDGER,LEDGER_DESCRIPTION ,IF(TRANSACTION_DATE = '0000-00-00','', DATE_FORMAT(TRANSACTION_DATE, '%m/%d/%Y' )) AS TRANSACTION_DATE_1, CREDIT,DEBIT, M_AR_LEDGER_CODE.CODE, PK_STUDENT_FEE_BUDGET, PK_STUDENT_DISBURSEMENT, PK_PAYMENT_BATCH_DETAIL, PK_MISC_BATCH_DETAIL, PK_TUITION_BATCH_DETAIL, S_STUDENT_LEDGER.PK_STUDENT_ENROLLMENT from S_STUDENT_LEDGER LEFT JOIN M_AR_LEDGER_CODE On M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_LEDGER.PK_AR_LEDGER_CODE WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER111' AND S_STUDENT_LEDGER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND (PK_PAYMENT_BATCH_DETAIL > 0 OR PK_MISC_BATCH_DETAIL > 0 OR PK_TUITION_BATCH_DETAIL > 0 ) $cond111 ORDER BY TRANSACTION_DATE ASC ");
            while (!$res_ledger->EOF) {
                $TOT_DEBIT  += $res_ledger->fields['DEBIT'];
                $TOT_CREDIT += $res_ledger->fields['CREDIT'];
                if ($res_ledger->fields['DEBIT'] != 0)
                    $BALANCE += $res_ledger->fields['DEBIT'];

                if ($res_ledger->fields['CREDIT'] != 0)
                    $BALANCE -= $res_ledger->fields['CREDIT'];

                $res_enroll = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1 , IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS END_DATE_1, IS_ACTIVE_ENROLLMENT, CAMPUS_CODE FROM S_STUDENT_ENROLLMENT LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER111' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '" . $res_ledger->fields['PK_STUDENT_ENROLLMENT'] . "' GROUP BY S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT "); //Ticket # 1693

                $ACADEMIC_YEAR         = "";
                $ACADEMIC_PERIOD     = "";
                $DESCRIPTION         = "";
                $CHECK_NO             = "";
                $RECEIPT_NO         = "";
                $REFERENCE_NO         = ""; // DIAM-1329
                $SOURCE             = "";
                $GROSS_AMOUNT         = "";
                $FEE_AMOUNT         = "";
                $PRIOR_YEAR            = "";
                $CREATED_ON            = "";
                $DETAIL1             = '';

                $LEDGER_TABLE      = '';
                $LEDGER_TABLE_ID = '';
                if ($res_ledger->fields['PK_PAYMENT_BATCH_DETAIL'] > 0) {
                    $res_det = $db->Execute("SELECT S_PAYMENT_BATCH_MASTER.PK_PAYMENT_BATCH_MASTER, S_PAYMENT_BATCH_DETAIL.CHECK_NO, RECEIPT_NO, BATCH_DETAIL_DESCRIPTION,BATCH_NO, PRIOR_YEAR, ACADEMIC_YEAR, S_PAYMENT_BATCH_DETAIL.PK_TERM_BLOCK, S_PAYMENT_BATCH_DETAIL.CREATED_ON, ACADEMIC_PERIOD, PK_DETAIL_TYPE, DETAIL,S_PAYMENT_BATCH_DETAIL.REFERENCE_NO FROM S_PAYMENT_BATCH_MASTER, S_PAYMENT_BATCH_DETAIL LEFT JOIN S_STUDENT_DISBURSEMENT ON S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT = S_PAYMENT_BATCH_DETAIL.PK_STUDENT_DISBURSEMENT WHERE S_PAYMENT_BATCH_MASTER.PK_PAYMENT_BATCH_MASTER = S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_MASTER AND S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_DETAIL = '" . $res_ledger->fields['PK_PAYMENT_BATCH_DETAIL'] . "' ");

                    $LEDGER_TABLE      = 'S_PAYMENT_BATCH_DETAIL';
                    $LEDGER_TABLE_ID = $res_ledger->fields['PK_PAYMENT_BATCH_DETAIL'];

                    $CREATED_ON          = $res_det->fields['CREATED_ON'];
                    $DESCRIPTION         = $res_det->fields['BATCH_DETAIL_DESCRIPTION'];
                    $CHECK_NO              = $res_det->fields['CHECK_NO'];
                    $REFERENCE_NO          = $res_det->fields['REFERENCE_NO']; // DIAM-1329
                    $RECEIPT_NO          = '<a href="receipt_pdf?did=' . $res_ledger->fields['PK_PAYMENT_BATCH_DETAIL'] . '">' . $res_det->fields['RECEIPT_NO'] . '</a>';
                    $SOURCE              = '<a href="batch_payment?id=' . $res_det->fields['PK_PAYMENT_BATCH_MASTER'] . '" target="_blank" >Payment: ' . $res_det->fields['BATCH_NO'] . '</a>';
                    $ACADEMIC_YEAR         = $res_det->fields['ACADEMIC_YEAR'];
                    $ACADEMIC_PERIOD     = $res_det->fields['ACADEMIC_PERIOD'];
                    $LED_PK_TERM_BLOCK     = $res_det->fields['PK_TERM_BLOCK'];

                    if ($res_det->fields['PK_DETAIL_TYPE'] == 4) {
                        $DETAIL = $res_det->fields['DETAIL'];
                        $res_det1a = $db->Execute("select AR_PAYMENT_TYPE from M_AR_PAYMENT_TYPE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_PAYMENT_TYPE = '$DETAIL' ");
                        $DETAIL1 = $res_det1a->fields['AR_PAYMENT_TYPE'];
                    }

                    if ($res_det->fields['PRIOR_YEAR'] == 1)
                        $PRIOR_YEAR = 'Yes';
                    else
                        $PRIOR_YEAR = 'No';
                } else if ($res_ledger->fields['PK_MISC_BATCH_DETAIL'] > 0) {
                    $res_det = $db->Execute("SELECT BATCH_NO,BATCH_DETAIL_DESCRIPTION, PK_AR_FEE_TYPE, PRIOR_YEAR, PK_AR_PAYMENT_TYPE, S_MISC_BATCH_DETAIL.CREATED_ON, AY, AP, PK_TERM_BLOCK, S_MISC_BATCH_MASTER.PK_MISC_BATCH_MASTER, MISC_RECEIPT_NO, S_MISC_BATCH_DETAIL.PK_STUDENT_CREDIT_CARD_PAYMENT, PAYMENT_MODE, REF_NUMBER,S_MISC_BATCH_DETAIL.REFERENCE_NO FROM S_MISC_BATCH_MASTER,S_MISC_BATCH_DETAIL WHERE S_MISC_BATCH_MASTER.PK_MISC_BATCH_MASTER = S_MISC_BATCH_DETAIL.PK_MISC_BATCH_MASTER AND PK_MISC_BATCH_DETAIL = '" . $res_ledger->fields['PK_MISC_BATCH_DETAIL'] . "' "); //Ticket # 1860

                    $LEDGER_TABLE      = 'S_MISC_BATCH_DETAIL';
                    $LEDGER_TABLE_ID = $res_ledger->fields['PK_MISC_BATCH_DETAIL'];

                    $MISC_RECEIPT_NO      = $res_det->fields['MISC_RECEIPT_NO'];
                    $CREATED_ON          = $res_det->fields['CREATED_ON'];
                    $DESCRIPTION         = $res_det->fields['BATCH_DETAIL_DESCRIPTION'];
                    $SOURCE              = '<a href="misc_batch?id=' . $res_det->fields['PK_MISC_BATCH_MASTER'] . '" target="_blank" >Misc: ' . $res_det->fields['BATCH_NO'] . '</a>';

                    $ACADEMIC_YEAR         = $res_det->fields['AY'];
                    $ACADEMIC_PERIOD     = $res_det->fields['AP'];
                    $LED_PK_TERM_BLOCK     = $res_det->fields['PK_TERM_BLOCK'];
                    $CHECK_NO            = $res_det->fields['REF_NUMBER'];
                    $REFERENCE_NO          = $res_det->fields['REFERENCE_NO']; // DIAM-1329


                    $DETAIL1 = '';
                    if ($res_det->fields['PAYMENT_MODE'] == 1)
                        $DETAIL1 = 'Check';
                    else if ($res_det->fields['PAYMENT_MODE'] == 2)
                        $DETAIL1 = 'Cash';
                    else if ($res_det->fields['PAYMENT_MODE'] == 3)
                        $DETAIL1 = 'Money Order';
                    else if ($res_det->fields['PAYMENT_MODE'] == 4 || $res_det->fields['PAYMENT_MODE'] == 5) //Ticket #1081
                        $DETAIL1 = 'Credit Card/Visa';

                    if ($MISC_RECEIPT_NO == '')
                        $RECEIPT_NO = '';
                    else
                        $RECEIPT_NO = '<a href="receipt_pdf?misc_id=' . $res_ledger->fields['PK_MISC_BATCH_DETAIL'] . '">' . $MISC_RECEIPT_NO . '</a>';

                    /* Ticket # 1860 */
                    if ($res_det->fields['PK_AR_FEE_TYPE'] > 0) {
                        $res11 = $db->Execute("select AR_FEE_TYPE FROM M_AR_FEE_TYPE WHERE PK_AR_FEE_TYPE = '" . $res_det->fields['PK_AR_FEE_TYPE'] . "' ");
                        $DETAIL1 = $res11->fields['AR_FEE_TYPE'];
                    } else if ($res_det->fields['PK_AR_PAYMENT_TYPE'] > 0) {
                        $res11 = $db->Execute("select AR_PAYMENT_TYPE FROM M_AR_PAYMENT_TYPE WHERE PK_AR_PAYMENT_TYPE = '" . $res_det->fields['PK_AR_PAYMENT_TYPE'] . "' ");
                        $DETAIL1 = $res11->fields['AR_PAYMENT_TYPE'];
                    }

                    if ($res_det->fields['PRIOR_YEAR'] == 1)
                        $PRIOR_YEAR = 'Yes';
                    else
                        $PRIOR_YEAR = 'No';
                    /* Ticket # 1860 */
                } else if ($res_ledger->fields['PK_TUITION_BATCH_DETAIL'] > 0) {
                    $res_det = $db->Execute("SELECT BATCH_NO,AY,AP,BATCH_DETAIL_DESCRIPTION, S_TUITION_BATCH_DETAIL.CREATED_ON, PK_TERM_BLOCK, S_TUITION_BATCH_MASTER.PK_TUITION_BATCH_MASTER FROM S_TUITION_BATCH_MASTER, S_TUITION_BATCH_DETAIL WHERE S_TUITION_BATCH_MASTER.PK_TUITION_BATCH_MASTER = S_TUITION_BATCH_DETAIL.PK_TUITION_BATCH_MASTER AND PK_TUITION_BATCH_DETAIL = '" . $res_ledger->fields['PK_TUITION_BATCH_DETAIL'] . "' ");

                    $LEDGER_TABLE      = 'S_TUITION_BATCH_DETAIL';
                    $LEDGER_TABLE_ID = $res_ledger->fields['PK_TUITION_BATCH_DETAIL'];

                    $ACADEMIC_YEAR      = $res_det->fields['AY'];
                    $ACADEMIC_PERIOD = $res_det->fields['AP'];
                    $DESCRIPTION      = $res_det->fields['BATCH_DETAIL_DESCRIPTION'];
                    $SOURCE           = '<a href="tuition_batch?id=' . $res_det->fields['PK_TUITION_BATCH_MASTER'] . '" target="_blank" >Tuition: ' . $res_det->fields['BATCH_NO'] . '</a>';

                    $CREATED_ON  = $res_det->fields['CREATED_ON'];
                    $LED_PK_TERM_BLOCK     = $res_det->fields['PK_TERM_BLOCK'];
                } /*else if($res_ledger->fields['PK_STUDENT_DISBURSEMENT'] > 0) {
                    $res_det = $db->Execute("SELECT ACADEMIC_YEAR, ACADEMIC_PERIOD, GROSS_AMOUNT, FEE_AMOUNT ,COMMENTS FROM S_STUDENT_DISBURSEMENT WHERE PK_STUDENT_DISBURSEMENT = '".$res_ledger->fields['PK_STUDENT_DISBURSEMENT']."' ");
                    $ACADEMIC_YEAR         = $res_det->fields['ACADEMIC_YEAR'];
                    $ACADEMIC_PERIOD     = $res_det->fields['ACADEMIC_PERIOD'];
                    $DESCRIPTION         = $res_det->fields['COMMENTS'];
                    $SOURCE              = "Disbursement";
                    $GROSS_AMOUNT         = $res_det->fields['GROSS_AMOUNT'];
                    $FEE_AMOUNT           = $res_det->fields['FEE_AMOUNT'];
                } else if($res_ledger->fields['PK_STUDENT_FEE_BUDGET'] > 0) {
                    $res_det = $db->Execute("SELECT ACADEMIC_YEAR,ACADEMIC_PERIOD, DESCRIPTION FROM S_STUDENT_FEE_BUDGET WHERE PK_STUDENT_FEE_BUDGET = '".$res_ledger->fields['PK_STUDENT_FEE_BUDGET']."' ");
                    $ACADEMIC_YEAR         = $res_det->fields['ACADEMIC_YEAR'];
                    $ACADEMIC_PERIOD     = $res_det->fields['ACADEMIC_PERIOD'];
                    $DESCRIPTION         = $res_det->fields['DESCRIPTION'];
                    $SOURCE              = "Estimated Fees";
                }*/

                if ($CREATED_ON != '' && $CREATED_ON != '0000-00-00 00:00;00')
                    $CREATED_ON = date("m/d/Y", strtotime($CREATED_ON)); ?>
                <tr>
                    <td>
                        <div style="width:130px"><?= $res_ledger->fields['TRANSACTION_DATE_1'] ?></div>
                    </td>
                    <td>
                        <div style="width:130px"><?= $res_ledger->fields['CODE'] ?></div>
                    </td>
                    <td>
                        <div style="width:30px"><?= $ACADEMIC_YEAR ?></div>
                    </td>
                    <td>
                        <div style="width:30px"><?= $ACADEMIC_PERIOD ?></div>
                    </td>
                    <td>
                        <div style="text-align:right;width:90px;">$<?= number_format_value_checker($res_ledger->fields['DEBIT'], 2) ?></div>
                    </td>
                    <td>
                        <div style="text-align:right;width:90px;">$<?= number_format_value_checker($res_ledger->fields['CREDIT'], 2) ?></div>
                    </td>
                    <td>
                        <div style="text-align:right;width:90px;">$<?= number_format_value_checker($BALANCE, 2) ?></div>
                    </td>
                    <td>
                        <? if ($t == 5 || ($_SESSION['PK_ROLES'] == 2 || $_SESSION['PK_ROLES'] == 3)) { ?>
                            <input type="hidden" name="LEDGER_PK_STUDENT_LEDGER[]" id="LEDGER_PK_STUDENT_LEDGER_<?= $res_ledger->fields['PK_STUDENT_LEDGER'] ?>" value="<?= $res_ledger->fields['PK_STUDENT_LEDGER'] ?>" />
                            <input type="hidden" name="LEDGER_TABLE[]" id="LEDGER_TABLE_<?= $res_ledger->fields['PK_STUDENT_LEDGER'] ?>" value="<?= $LEDGER_TABLE ?>" />
                            <input type="hidden" name="LEDGER_TABLE_ID[]" id="LEDGER_TABLE_ID_<?= $res_ledger->fields['PK_STUDENT_LEDGER'] ?>" value="<?= $LEDGER_TABLE_ID ?>" />

                            <input type="text" class="form-control" placeholder="" name="LEDGER_BATCH_DETAIL_DESCRIPTION[]" id="LEDGER_BATCH_DETAIL_DESCRIPTION_<?= $res_ledger->fields['PK_STUDENT_LEDGER'] ?>" value="<?= $DESCRIPTION ?>" style="width:150px" <? if ($t == 5) echo "readonly"; ?> /> <!-- Ticket # 1913   -->
                        <? } else
                            echo $DESCRIPTION; ?>
                    </td>

                    <td><?= $SOURCE ?></td>
                    <td>
                        <div style="width:80px;"><?= $RECEIPT_NO ?></div>
                    </td>
                    <?php if ($_SESSION['PK_ACCOUNT'] == 505) { ?>
                        <td>
                            <div style="width:90px;"><input type="text" class="form-control" placeholder="" name="REFERENCE_NO[]" id="REFERENCE_NO_<?= $res_ledger->fields['PK_STUDENT_LEDGER'] ?>" value="<?= $REFERENCE_NO ?>"></div>
                        </td>

                    <?php } ?>
                    <td>
                        <div style="width:80px;"><?= $CHECK_NO ?></div>
                    </td>
                    <td>
                        <!-- Ticket # 1693 -->
                        <? if ($t == 5 || ($_SESSION['PK_ROLES'] == 2 || $_SESSION['PK_ROLES'] == 3)) { ?>
                            <select id="LEDGER_PK_STUDENT_ENROLLMENT_<?= $res_ledger->fields['PK_STUDENT_LEDGER'] ?>" name="LEDGER_PK_STUDENT_ENROLLMENT[]" class="form-control" style="width:250px">
                                <? $res_type = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IS_ACTIVE_ENROLLMENT, CAMPUS_CODE FROM S_STUDENT_ENROLLMENT LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER111' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' GROUP BY S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT");
                                while (!$res_type->EOF) { ?>
                                    <option value="<?= $res_type->fields['PK_STUDENT_ENROLLMENT'] ?>" <? if ($res_type->fields['PK_STUDENT_ENROLLMENT'] == $res_ledger->fields['PK_STUDENT_ENROLLMENT']) echo "selected"; ?> <? if ($res_type->fields['IS_ACTIVE_ENROLLMENT'] == 1) echo "class='option_red'";  ?>><?= $res_type->fields['BEGIN_DATE_1'] . ' - ' . $res_type->fields['CODE'] . ' - ' . $res_type->fields['STUDENT_STATUS'] . ' - ' . $res_type->fields['CAMPUS_CODE'] ?></option>
                                <? $res_type->MoveNext();
                                } ?>
                            </select>
                        <? } else
                            echo $res_enroll->fields['BEGIN_DATE_1'] . ' - ' . $res_enroll->fields['STUDENT_STATUS'] . ' - ' . $res_enroll->fields['CODE'] . ' - ' . $res_enroll->fields['CAMPUS_CODE']; ?>
                        <!-- Ticket # 1693 -->
                    </td>
                    <td>
                        <div style="width:150px;"><?= $DETAIL1 ?></div>
                    </td>
                    <td>
                        <div style="text-align:right;width:120px;"><? if ($GROSS_AMOUNT != '') echo '$ ' . number_format_value_checker($GROSS_AMOUNT, 2) ?></div>
                    </td>
                    <td>
                        <div style="text-align:right;width:90px;"><? if ($FEE_AMOUNT != '') echo '$ ' . number_format_value_checker($FEE_AMOUNT, 2) ?></div>
                    </td>
                    <td>
                        <div style="width:90px;">Program: <?= $res_enroll->fields['CODE'] ?></div>
                    </td>
                    <td>
                        <? if ($t == 5 || ($_SESSION['PK_ROLES'] == 2 || $_SESSION['PK_ROLES'] == 3)) { ?>
                            <select id="LEDGER_PK_TERM_BLOCK_<?= $res_ledger->fields['PK_STUDENT_LEDGER'] ?>" name="LEDGER_PK_TERM_BLOCK_<?= $res_ledger->fields['PK_STUDENT_LEDGER'] ?>" class="form-control" style="width:110px">
                                <option></option>
                                <? /* Ticket # 1693 */
                                $res_type = $db->Execute("select PK_TERM_BLOCK, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS END_DATE_1, DESCRIPTION, ACTIVE from S_TERM_BLOCK WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, BEGIN_DATE DESC");
                                while (!$res_type->EOF) {
                                    $ACTIVE = '';
                                    if ($res_type->fields['ACTIVE'] == 0)
                                        $ACTIVE = ' (Inactive)'; ?>
                                    <option value="<?= $res_type->fields['PK_TERM_BLOCK'] ?>" <? if ($LED_PK_TERM_BLOCK == $res_type->fields['PK_TERM_BLOCK']) echo "selected"; ?> <? if ($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?= $res_type->fields['BEGIN_DATE_1'] . ' - ' . $res_type->fields['END_DATE_1'] . ' - ' . $res_type->fields['DESCRIPTION'] . ' ' . $ACTIVE ?></option>
                                <? $res_type->MoveNext();
                                }
                                /* Ticket # 1693 */ ?>
                            </select>
                        <? } else {
                            $res_type = $db->Execute("select IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1 from S_TERM_BLOCK WHERE PK_TERM_BLOCK = '$PK_TERM_BLOCK' ");
                            echo $res_type->fields['BEGIN_DATE_1'];
                        } ?>
                    </td>
                    <td><?= $PRIOR_YEAR ?></td>
                    <td>
                        <div style="width:120px;"><?= $CREATED_ON ?></div>
                    </td>
                </tr>
            <? $res_ledger->MoveNext();
            }
            $BALANCE = round($BALANCE, 2);
            $_SESSION['student_ledger_balance'] = $BALANCE; //DIAM-1090
            ?>
        </tbody>
    </table>
</div> <!-- Ticket # 1016  -->

<div class="row">
    <div class=" col-sm-4">&nbsp;</div>
    <div class=" col-sm-1">
        <div style="text-align:right;font-weight:bold;"><?= DEBIT ?></div>
    </div>
    <div class=" col-sm-1">
        <div style="text-align:right;font-weight:bold;"><? echo '$' . number_format_value_checker($TOT_DEBIT, 2) ?></div>
    </div>
</div>
<div class="row">
    <div class=" col-sm-4">&nbsp;</div>
    <div class=" col-sm-1">
        <div style="text-align:right;font-weight:bold;"><?= CREDIT ?></div>
    </div>
    <div class=" col-sm-1">
        <div style="text-align:right;font-weight:bold;"><? echo '$' . number_format_value_checker($TOT_CREDIT, 2) ?></div>
    </div>
</div>
<div class="row">
    <div class=" col-sm-4">&nbsp;</div>
    <div class=" col-sm-1" style="border-top: 1px solid #000;">
        <div style="text-align:right;font-weight:bold;"><?= BALANCE ?></div>
    </div>
    <div class=" col-sm-1" style="border-top: 1px solid #000;">
        <div style="text-align:right;font-weight:bold;"><? echo '$' . number_format_value_checker($BALANCE, 2) ?></div>
    </div>
</div>
