<? require_once("../global/config.php");
require_once("function_student_ledger.php");
require_once("../language/common.php");
require_once("../language/student.php");
require_once("../language/batch_payment.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_ACCOUNTING') == 0 ){
    header("location:../index");
    exit;
}

$PK_PAYMENT_BATCH_MASTER = $_REQUEST['pms'];
?>
<div class="row">
    <div class="col-md-12">
        <table class="table-striped table table-hover table-bordered" id="disbursement_table" >
            <thead style="position: sticky;top: 0;z-index: 9;" >
                <tr>
                    <th class="sticky_header" scope="col" ><?=NAME?></th>
                    <th class="sticky_header" scope="col" ><?=STUDENT_ID?></th>
                    <th class="sticky_header" scope="col" ><?=LEDGER_CODE?></th>
                    <th class="sticky_header" scope="col" ><?=DISBURSEMENT_DATE?></th>
                    <th class="sticky_header" scope="col" ><?=TRANSACTION_DATE?></th>
                    <th class="sticky_header" scope="col" ><div style="text-align:right"><?=DISBURSEMENT_AMOUNT.'<br />(Credits)'?></div></th>
                    <th class="sticky_header" scope="col" ><?=BATCH_DETAIL?></th>
                    <th class="sticky_header" scope="col" ><?=PAYMENT_TYPE?></th>
                    <th class="sticky_header" scope="col" ><?=AY_1?></th>
                    <th class="sticky_header" scope="col" ><?=AP_1?></th>
                    <th class="sticky_header" scope="col" ><?=CHECK_NO?></th>
                    <th class="sticky_header" scope="col" ><?=RECEIPT_NO?></th>
                    <th class="sticky_header" scope="col" ><?=REFERENCE_NO?></th> <!-- DIAM-1329 -->
                    <th class="sticky_header" scope="col" ><?=STATUS?></th>
                    <th class="sticky_header" scope="col" ><?=ENROLLMENT?></th>
                    <th class="sticky_header" scope="col" ><?=TERM_BLOCK?></th>
                    <th class="sticky_header" scope="col" ><?=PRIOR_YEAR?></th>
                    <th class="sticky_header" scope="col" ><?=MESSAGE?></th>
                    <? if($_REQUEST['PK_BATCH_STATUS'] != 2){ ?>
                    <th class="sticky_header" scope="col" ><?=ACTION?></th>
                    <? } ?>
                </tr>
            </thead>
            <tbody>
                <?
                $sql = "SELECT
                S_STUDENT_DISBURSEMENT.PK_PAYMENT_BATCH_DETAIL,
                S_PAYMENT_BATCH_DETAIL.PK_STUDENT_ENROLLMENT,
                IF(BATCH_TRANSACTION_DATE = '0000-00-00',
                '',
                DATE_FORMAT(BATCH_TRANSACTION_DATE, '%m/%d/%Y' )) AS BATCH_TRANSACTION_DATE,
                S_STUDENT_DISBURSEMENT.PK_PAYMENT_BATCH_DETAIL,
                S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT,
                M_AR_LEDGER_CODE.CODE AS LEDGER,
                CONCAT(LAST_NAME, ', ', FIRST_NAME) AS NAME,
                RECEIPT_NO,
                BATCH_NO,
                ACADEMIC_YEAR,
                ACADEMIC_PERIOD,
                BATCH_DETAIL_DESCRIPTION,
                IF(DISBURSEMENT_DATE = '0000-00-00',
                '',
                DATE_FORMAT(DISBURSEMENT_DATE, '%m/%d/%Y' )) AS DISBURSEMENT_DATE1,
                DISBURSEMENT_AMOUNT,
                IF(DEPOSITED_DATE = '0000-00-00',
                '',
                DATE_FORMAT(DEPOSITED_DATE, '%m/%d/%Y' )) AS DEPOSITED_DATE,
                BATCH_PAYMENT_STATUS,
                BATCH_NO,
                RECEIVED_AMOUNT,
                IF(PRIOR_YEAR = 1,
                'Yes',
                IF(PRIOR_YEAR = 2,
                'No',
                '')) AS PRIOR_YEAR_1,
                PRIOR_YEAR,
                PK_DETAIL_TYPE,
                DETAIL ,
                IF(BEGIN_DATE = '0000-00-00',
                '',
                DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1,
                S_PAYMENT_BATCH_DETAIL.CHECK_NO AS STUD_CHECK_NO,
                S_PAYMENT_BATCH_DETAIL.REFERENCE_NO AS REFERENCE_NO,
                STUDENT_ID,
                CAMPUS_CODE,
                DISBURSEMENT_TYPE
            FROM  S_PAYMENT_BATCH_DETAIL
                            
            LEFT JOIN S_PAYMENT_BATCH_MASTER ON
                S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_MASTER = S_PAYMENT_BATCH_MASTER.PK_PAYMENT_BATCH_MASTER
            LEFT JOIN M_BATCH_PAYMENT_STATUS ON
                M_BATCH_PAYMENT_STATUS.PK_BATCH_PAYMENT_STATUS = S_PAYMENT_BATCH_DETAIL.PK_BATCH_PAYMENT_STATUS
            LEFT JOIN S_TERM_BLOCK ON
                S_TERM_BLOCK.PK_TERM_BLOCK = S_PAYMENT_BATCH_DETAIL.PK_TERM_BLOCK
            LEFT JOIN S_STUDENT_DISBURSEMENT ON
                S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_DETAIL = S_STUDENT_DISBURSEMENT.PK_PAYMENT_BATCH_DETAIL
            LEFT JOIN S_STUDENT_MASTER ON
                S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_DISBURSEMENT.PK_STUDENT_MASTER
            LEFT JOIN S_STUDENT_ACADEMICS ON
                S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER
            LEFT JOIN S_STUDENT_ENROLLMENT ON
                S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_PAYMENT_BATCH_DETAIL.PK_STUDENT_ENROLLMENT
            LEFT JOIN M_AR_LEDGER_CODE ON
                M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE
            LEFT JOIN S_STUDENT_CAMPUS ON
                S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT
            LEFT JOIN S_CAMPUS ON
                S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS
            WHERE 
                S_PAYMENT_BATCH_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'
                AND S_PAYMENT_BATCH_MASTER.PK_PAYMENT_BATCH_MASTER = '$PK_PAYMENT_BATCH_MASTER'
                GROUP  BY S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_DETAIL
            ORDER BY
                CONCAT(LAST_NAME, ', ', FIRST_NAME) ASC,
                DISBURSEMENT_DATE ASC,
                M_AR_LEDGER_CODE.CODE ASC";

        // echo $sql;exit;


                $res_disb = $db->Execute($sql);

                $posted_total = 0;
                while (!$res_disb->EOF) {
                    $posted_total += $res_disb->fields['RECEIVED_AMOUNT'];
                    $DETAIL = '';
                    if($res_disb->fields['PK_DETAIL_TYPE'] == 4) {
                        $DETAIL1 = $res_disb->fields['DETAIL'];
                        $res_det1 = $db->Execute("select AR_PAYMENT_TYPE from M_AR_PAYMENT_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_PAYMENT_TYPE = '$DETAIL1' ");
                        $DETAIL = $res_det1->fields['AR_PAYMENT_TYPE'];
                    }
                    
                    $PK_STUDENT_ENROLLMENT = $res_disb->fields['PK_STUDENT_ENROLLMENT'];
                    $res_en_2 = $db->Execute("SELECT PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IS_ACTIVE_ENROLLMENT FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");  ?>
                    
                    <input type="hidden" class="pk_stud_enrol" name="PK_STUDENT_ENROLLMENT[]" id="PK_STUDENT_ENROLLMENT" value="<?=$res_disb->fields['PK_STUDENT_ENROLLMENT']?>" />
                    <tr>
                        <td><div style="width:150px"><?=$res_disb->fields['NAME'] ?></div></td>
                        <td><div style="width:130px"><?=$res_disb->fields['STUDENT_ID']?></div></td>
                        <td><div style="width:100px"><?=$res_disb->fields['LEDGER']?></div></td>
                        <td><div style="width:100px"><?=$res_disb->fields['DISBURSEMENT_DATE1']?></div></td>
                        <td><div style="width:100px"><?=$res_disb->fields['BATCH_TRANSACTION_DATE']?></div></td>
                        <td>
                            <div style="text-align:right;width:100px;" >
                                $ <?=number_format_value_checker($res_disb->fields['RECEIVED_AMOUNT'],2)?>
                            </div>
                        </td>
                        <td>
                            <div style="width:150px">
                                <? echo $res_en_2->fields['CODE'].' - '.$res_en_2->fields['BEGIN_DATE_1'] ?>
                            </div>
                        </td>
                        <td><div style="width:120px"><?=$DETAIL?></div></td>
                        <td><div style="width:30px"><?=$res_disb->fields['ACADEMIC_YEAR']?></div></td>
                        <td><div style="width:30px"><?=$res_disb->fields['ACADEMIC_PERIOD']?></div></td>
                        <td><div style="width:80px"><?=$res_disb->fields['STUD_CHECK_NO']?></div></td>
                        <td><div style="width:80px"><a href="receipt_pdf?did=<?=$res_disb->fields['PK_PAYMENT_BATCH_DETAIL']?>"><?=$res_disb->fields['RECEIPT_NO']?></a></div></td>
                        <td><div style="width:90px"><?=$res_disb->fields['REFERENCE_NO']?></div></td> <!-- DIAM-1329 -->
                        <td><div style="width:80px"><?=$res_disb->fields['BATCH_PAYMENT_STATUS']?></div></td>
                        <td>
                            <div style="width:200px"><? echo $res_en_2->fields['BEGIN_DATE_1'].' - '.$res_en_2->fields['CODE'].' - '.$res_en_2->fields['STUDENT_STATUS'].' - '.$res_disb->fields['CAMPUS_CODE']; ?></div>
                        </td>
                        <td><div style="width:110px"><?=$res_disb->fields['BEGIN_DATE_1']?></div></td>
                        <td><div style="width:50px"><?=$res_disb->fields['PRIOR_YEAR_1']?></div></td>
                        <td><div style="width:60px"><? if($res_disb->fields['DISBURSEMENT_TYPE'] == 1) {echo "Split"; } elseif($res_disb->fields['DISBURSEMENT_TYPE'] == 2) { echo "Adjust"; }; ?></div></td>
                        
                        <? if($_REQUEST['PK_BATCH_STATUS'] != 2){ ?>
                        <td>
                            <a href="javascript:void(0);" onclick="edit_row('<?=$res_disb->fields['PK_PAYMENT_BATCH_DETAIL']?>','<?=$res_disb->fields['DISBURSEMENT_AMOUNT']?>','<?=$res_disb->fields['PRIOR_YEAR']?>')" title="<?=EDIT?>" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>
                            
                            <a href="javascript:void(0);" onclick="delete_row('<?=$res_disb->fields['PK_PAYMENT_BATCH_DETAIL']?>')" title="<?=DELETE?>" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>
                        </td>
                        <? } ?>
                    </tr>
                <?    $res_disb->MoveNext();
                } ?>
            </tbody>
        </table>
        <br />
    </div>
</div>
