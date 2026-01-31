<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
require_once("check_access.php");

if (check_access('REPORT_REGISTRAR') == 0) {
    header("location:../index");
    exit;
}


$REPORT_TYPE  = $_POST['REPORT_TYPE'];
?>


<div class="row d-none" class="d-none" id="AR_REP_FILTER">
    <?php if ($REPORT_TYPE == 13 || $REPORT_TYPE == 16 || $REPORT_TYPE == 15 || $REPORT_TYPE == 17) { ?>
        <div class="col-sm-12 row <? if($REPORT_TYPE == 17){ echo " d-none "; }?>">
            <!-- ADD PRINT TYPE (by instructor or course) -->
            <div class="col-md-3 " id="AR_PRINT_TYPE_DIV">
                <select id="AR_PRINT_TYPE" name="AR_PRINT_TYPE" class="form-control required-entry" onchange="get_course_or_instructor()">
                    <option value="">Select Print type</option>
                    <option value="1" <? if($REPORT_TYPE == 17){ echo " selected "; }?> >Print By Selected Course Offering</option>
                    <option value="2">Print By Selected Instructor</option>
                </select>
            </div>
        </div>
        <div class="col-sm-12 row mt-3">

            <!-- ADD CAMPUS  -->
            <div class="col-md-2" id="AR_PK_CAMPUS_DIV">
                <select id="AR_PK_CAMPUS" name="AR_PK_CAMPUS[]" multiple class="form-control required-entry">
                    <?
                    $res_type = $db->Execute("select ACTIVE,CAMPUS_CODE,PK_CAMPUS,ACTIVE from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by ACTIVE DESC,CAMPUS_CODE ASC");
                    while (!$res_type->EOF) { ?>
                        <option value="<?= $res_type->fields['PK_CAMPUS'] ?>" <? if ($res_type->RecordCount() == 1) echo "selected"; ?> <?php if ($res_type->fields['ACTIVE'] == '0') echo ' style="color : red" ' ?>><?= $res_type->fields['CAMPUS_CODE'] ?> <?php if ($res_type->fields['ACTIVE'] == '0') echo " (Inactive) " ?>
                        </option>
                    <? $res_type->MoveNext();
                    }  ?>
                </select>
            </div>

            <!-- ADD TERM -->
            <div class="col-md-2 " id="AR_PK_TERM_MASTER_DIV">
                <select id="AR_PK_TERM_MASTER" name="AR_PK_TERM_MASTER" class="form-control required-entry" onchange="get_course_or_instructor()">
                    <option value="">Select Course Term</option>
                    <? $res_term = $db->Execute("select PK_TERM_MASTER, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, ACTIVE from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, BEGIN_DATE DESC");
                    while (!$res_term->EOF) { ?>
                        <option value="<?= $res_term->fields['PK_TERM_MASTER'] ?>" <?php if ($res_term->fields['ACTIVE'] == '0') echo ' style="color : red" ' ?>><?= $res_term->fields['BEGIN_DATE_1'] ?><?php if ($res_term->fields['ACTIVE'] == '0') echo " (Inactive) " ?></option>
                    <? $res_term->MoveNext();
                    } ?>
                </select>
            </div>

            <!-- ADD COURSE / INSTRUCTOR  DIV -->
            <div class="col-md-2 " id="AR_CI_DIV">
                <select class="form-control required-entry" disabled>
                    <option value="">Select Course / Instructor</option>
                </select>
            </div>
        </div>
        <div class="col-sm-12 row mt-3">

            <?php if ($REPORT_TYPE == 13 || $REPORT_TYPE == 16 || $REPORT_TYPE == 15 || $REPORT_TYPE == 17) {
            ?>
                <div class="col-md-2 ">
                    <input type="text" class="form-control date" id="START_DATE_analysis" name="START_DATE_analysis" <? if ($REPORT_TYPE != 16) {
                                                                                                                            echo 'placeholder="Scheduled Start Date"';
                                                                                                                        } else {
                                                                                                                            echo 'placeholder="Scheduled Class Begin Date"';
                                                                                                                        }
                                                                                                                        ?>>
                </div>
                <div class="col-md-2 ">
                    <input type="text" class="form-control date" id="AS_OF_DATE" name="AS_OF_DATE" <? if ($REPORT_TYPE != 16) {
                                                                                                        echo 'placeholder="Scheduled End Date"';
                                                                                                    } else {
                                                                                                        echo 'placeholder="Scheduled Class End Date"';
                                                                                                    }
                                                                                                    ?>>
                </div>
                <?php if ($REPORT_TYPE == 16) { ?>
                    <div class="col-6 col-sm-6 custom-control custom-checkbox ">
                        <input type="checkbox" class="custom-control-input INFO_check_box" id="SHOW_TOTAL_HOURS" name="SHOW_TOTAL_HOURS">
                        <label class="custom-control-label" for="SHOW_TOTAL_HOURS">Show Total Hours</label>

                    </div>
                <? } ?>



            <?php } ?>


            <!-- ADD Fetching option (days) -->
            <div class="col-md-2 <?php if ($REPORT_TYPE == 15 || $REPORT_TYPE == 16 || $REPORT_TYPE == 17) {
                                        echo 'd-none';
                                    } ?>" id="AR_DAYS_TYPE_DIV">
                <select id="AR_DAYS" name="AR_DAYS" class="form-control required-entry">
                    <option value="">Report Option</option>
                    <option value="mon_fri" <?php if ($REPORT_TYPE == 15) {
                                                echo "selected";
                                            } ?>>Monday to Friday</option>
                    <option value="sun_sat" <?php if ($REPORT_TYPE == 16) {
                                                echo "selected";
                                            } ?>>Sunday to Saturday</option>
                </select>
            </div>

            <!-- Add class meeting date -->
            <div class="col-md-2 d-none" id="AR_DAY_DIV">
                <input type="hidden" class="form-control date required-entry" id="AR_CLASS_MEETING_DATE" name="AR_CLASS_MEETING_DATE" placeholder="Scheduled <?= DATE ?>">

            </div>
            <? if($REPORT_TYPE != 17 ){ ?>
            <div class="col-md-2 float-right" id="AR_DAY_DIV">
                <button type="button" class="btn waves-effect waves-light btn-info mt-0" onclick="fetch_attendnace_rooster()">PDF</button>
            </div>
            <? } else { ?>
                <div class="col-md-2 float-right" id="AR_DAY_DIV">
                <button type="button" class="btn waves-effect waves-light btn-info mt-0" onclick="fetch_daily_attendance_sheet()">PDF</button>
                </div>
            <?}?>

        </div>
    <? } ?>

</div>
<div class="row mb-3">
    <?php if ($REPORT_TYPE == 14) {
    ?>
        <div class="col-md-2 ">
            <input type="text" class="form-control date" id="START_DATE_analysis" name="START_DATE_analysis" placeholder="Attendance Start Date">
        </div>
        <div class="col-md-2 ">
            <input type="text" class="form-control date" id="AS_OF_DATE" name="AS_OF_DATE" placeholder="Attendance End Date">
        </div>
        <button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info mr-2" id="btn_1" style="display:none"><?= PDF ?></button>

        <button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info" id="btn_2" style="display:none"><?= EXCEL ?></button>
        <div class="col-md-12 mt-2" style="border-bottom:1px solid black;">

        </div>
    <?php } ?>
</div>



<?php if ($REPORT_TYPE == 12) { ?>
    <!-- Ticket # 1635  -->
    <div class="row" style="padding-bottom:40px;" id="ATT_BY_DATE_RANGE_FIELDS" style="display:none">

        <div class="col-md-2">
            <select id="TERM_PK_CAMPUS" name="TERM_PK_CAMPUS[]" multiple class="form-control"> <!-- // DIAM-757 onchange="get_course_term_from_campus()" -->
                <? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS,ACTIVE from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, CAMPUS_CODE ASC");
                while (!$res_type->EOF) { ?>
                    <option value="<?= $res_type->fields['PK_CAMPUS'] ?>" <? if ($res_type->RecordCount() == 1) echo "selected";
                                                                            if ($res_type->fields['ACTIVE'] == '0') echo ' style="color : red" ' ?>><?= $res_type->fields['CAMPUS_CODE']   ?> <?php if ($res_type->fields['ACTIVE'] == '0') echo " (Inactive) " ?></option>
                <? $res_type->MoveNext();
                } ?>
            </select>
        </div>
        <?php echo getDateRangeContent(); ?>
        <div class="col-md-2" id="COURSE_PK_TERM_DIV_HIDDEN" style="display:none;"></div><!--// DIAM-757 -->
        <!-- <div class="col-md-2" id="COURSE_PK_TERM_DIV"> -->
        <!-- // DIAM-757 -->
        <!-- <select id="COURSE_PK_TERM" name="COURSE_PK_TERM[]" multiple class="form-control" onchange="get_course_from_term()">
                <? //$res_type = $db->Execute("select ACTIVE,PK_TERM_MASTER, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by ACTIVE DESC,BEGIN_DATE DESC");
                //while (!$res_type->EOF) {
                ?>
                    <option value="<? //= $res_type->fields['PK_TERM_MASTER'] 
                                    ?>" <?php //if ($res_type->fields['ACTIVE'] == '0') echo ' style="color : red" '
                                        ?>><? //= $res_type->fields['BEGIN_DATE_1'] . " - " . $res_type->fields['END_DATE_1'] . " - " . $res_type->fields['TERM_DESCRIPTION']
                                            ?><?php //if ($res_type->fields['ACTIVE'] == '0') echo " (Inactive) "
                                                ?></option>
                <? //$res_type->MoveNext();
                //}
                ?>
            </select> -->
        <!-- </div> -->

        <div class="col-md-2" id="COURSE_COURSE_DIV">

            <select id="COURSE_PK_COURSE" name="COURSE_PK_COURSE[]" multiple class="form-control required-entry"></select>
        </div>

        <div class="col-md-2" id="COURSE_COURSE_OFFERING_DIV">
            <!-- <select id="COURSE_PK_COURSE_OFFERING" name="COURSE_PK_COURSE_OFFERING[]" multiple class="form-control">
                <? //$res_type = $db->Execute("Select S_COURSE_OFFERING.ACTIVE,PK_COURSE_OFFERING, COURSE_CODE, IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') AS TERM_BEGIN_DATE, SESSION_NO, SESSION, TRANSCRIPT_CODE, COURSE_DESCRIPTION from S_COURSE, S_COURSE_OFFERING LEFT JOIN M_SESSION on M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER WHERE S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE order by S_COURSE_OFFERING.ACTIVE DESC, S_TERM_MASTER.BEGIN_DATE DESC, COURSE_CODE ASC, SESSION ASC, SESSION_NO ASC ");
                //while (!$res_type->EOF) {
                ?>
                    <option value="<? //= $res_type->fields['PK_COURSE_OFFERING'] 
                                    ?>" <? //if ($res_type->fields['PK_COURSE_OFFERING'] == $PK_COURSE_OFFERING) echo "selected";
                                        ?> <?php //if ($res_type->fields['ACTIVE'] == '0') echo ' style="color : red" '
                                            ?>>
                        <? //echo $res_type->fields['COURSE_CODE'] . " (" . substr($res_type->fields['SESSION'], 0, 1) . "-" . $res_type->fields['SESSION_NO'] . ") " . $res_type->fields['TRANSCRIPT_CODE'] . ' - ' . $res_type->fields['COURSE_DESCRIPTION'] . " - " . $res_type->fields['TERM_BEGIN_DATE'];
                        ?><?php //if ($res_type->fields['ACTIVE'] == '0') echo " (Inactive) "
                            ?>

                    </option>
                <? //$res_type->MoveNext();
                //}
                ?>
            </select> -->
            <select id="COURSE_PK_COURSE_OFFERING" name="COURSE_PK_COURSE_OFFERING[]" multiple class="form-control required-entry"></select>
        </div>
    </div>
    <!-- Ticket # 1635  -->
<? } ?>


<?php if ($REPORT_TYPE == 12) { ?>
    <!-- Ticket # 1508  -->
    <div class="row mb-3"><!-- Ticket # 1635  -->


        <div class="col-md-2 align-self-center" id="START_DATE_1_div">
            <input type="text" class="form-control date required-entry" id="START_DATE_1" name="START_DATE_1" placeholder="<?= START_DATE ?>">
        </div>



        <div class="col-md-2 align-self-center" id="END_DATE_1_div">
            <input type="text" class="form-control date required-entry" id="END_DATE_1" name="END_DATE_1" placeholder="<?= END_DATE ?>">
        </div>



        <!-- Ticket # 1600  -->
        <div class="col-md-2 align-self-center" id="MIN_PER_div">
            <input type="text" class="form-control" id="MIN_PER" name="MIN_PER" placeholder="<?= MINIMUM_PERCENTAGE ?>">
        </div>

        <div class="col-md-2 align-self-center" id="MAX_PER_div">
            <input type="text" class="form-control" id="MAX_PER" name="MAX_PER" placeholder="<?= MAXIMUM_PERCENTAGE ?>">
        </div>
        <!-- Ticket # 1600  -->

    </div>
<? } ?>
<?php if ($REPORT_TYPE == 12) { ?>
    <div class="row" style="padding-bottom:40px;">



        <div class="col-md-2 align-self-center" id="ENROLLMENT_TYPE_1_div">
            <select id="ENROLLMENT_TYPE_1" name="ENROLLMENT_TYPE_1" class="form-control"><!-- DIAM757 //onchange="search()"-->
                <option value="1">All Enrollments</option>
                <option value="2">Current Enrollment</option>
            </select>
        </div>
        <div class="col-md-3 align-self-center" id="EXCLUDE_INACTIVE_ATT_CODE_DIV">
            <div class="col-12 col-sm-12 custom-control custom-checkbox form-group">
                <input type="checkbox" class="custom-control-input" id="EXCLUDE_INACTIVE_ATT_CODE" name="EXCLUDE_INACTIVE_ATT_CODE" value="1">
                <label class="custom-control-label" for="EXCLUDE_INACTIVE_ATT_CODE"><?= EXCLUDE_INACTIVE_ATT_CODE ?></label>
            </div>
        </div>
        <div class="col-md-2 align-self-center" id="SUMMARY_REPORT_DIV">
            <div class="col-12 col-sm-12 custom-control custom-checkbox form-group">
                <input type="checkbox" class="custom-control-input" id="SUMMARY_REPORT" name="SUMMARY_REPORT" value="1" checked onclick="show_min_max_per()"> <!-- Ticket # 1600  -->
                <label class="custom-control-label" for="SUMMARY_REPORT"><?= SUMMARY_REPORT ?></label>
            </div>
        </div>


        <div class="col-md-2 ">
            <?php
            if ($REPORT_TYPE != 12) {

            ?>
                <button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info" id="btn_3" style="display:none"><?= PDF ?></button>
                <button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info" id="btn_4" style="display:none"><?= EXCEL ?></button>


            <?php
            } else {
            ?>

                <button type="button" onclick="get_report_ajax(1)" class="btn waves-effect waves-light btn-info" id="btn_3" style="display:none"><?= PDF ?></button>
                <button type="button" onclick="get_report_ajax(2)" class="btn waves-effect waves-light btn-info" id="btn_4" style="display:none"><?= EXCEL ?></button>


            <?php

            }
            ?>
        </div>

    </div>
<? } ?>
<!-- Ticket # 1508  -->
<!-- Ticket # 1635  -->

<div class="row" style="padding-bottom:10px;">
    <?php if ($REPORT_TYPE == 4 || $REPORT_TYPE == 9) { ?>
        <div class="col-md-3 " id="PRINT_TYPE_DIV">
            <select id="PRINT_TYPE" name="PRINT_TYPE" class="form-control" onchange="get_course_offering()">
                <option value="1">Print By Selected Course Offering</option>
                <option value="2">Print By Selected Instructor</option>
            </select>
        </div>
    <?php } ?>

    <?php if ($REPORT_TYPE == 2 || $REPORT_TYPE == 12 || $REPORT_TYPE == 14 || $REPORT_TYPE == 10) { //Ticket # 659
    ?>

        <?php

        if ($REPORT_TYPE == 12) {
            echo '<div class="col-md-12">
        <h5 class="text-themecolor">Student Filters</h5>
        </div>';
        }


        ?>

        <!-- Ticket # 1247  -->
        <div class="col-md-2" id="PK_CAMPUS_DIV">
            <select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control">
                <? /* Ticket # 1635 */
                $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS,ACTIVE from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by ACTIVE DESC,CAMPUS_CODE ASC");
                while (!$res_type->EOF) { ?>
                    <option value="<?= $res_type->fields['PK_CAMPUS'] ?>" <? if ($res_type->RecordCount() == 1) echo "selected"; ?> <?php if ($res_type->fields['ACTIVE'] == '0') echo ' style="color : red" ' ?>><?= $res_type->fields['CAMPUS_CODE'] ?> <?php if ($res_type->fields['ACTIVE'] == '0') echo " (Inactive) " ?>
                    </option>
                <? $res_type->MoveNext();
                } /* Ticket # 1635 */ ?>
            </select>
        </div>
        <!-- Ticket # 1247  -->
    <?php } ?>

    <?php if ($REPORT_TYPE == 1 || $REPORT_TYPE == 4) {
        $onchangefun = '';
        if ($REPORT_TYPE == 1) {
            $onchangefun = 'get_term_from_campus_by_date(' . $REPORT_TYPE . ')';
        } else if ($REPORT_TYPE == 4) {
            $onchangefun = 'get_term_from_campus()';
        }

    ?>
        <!-- Ticket # 1341  -->
        <div class="col-md-2" id="PK_CAMPUS_DIV_1" style="max-width:180px !important;">
            <select id="PK_CAMPUS_1" name="PK_CAMPUS_1[]" multiple class="form-control" onchange="<?= $onchangefun ?>"> <? //get_term_from_campus()
                                                                                                                        ?>
                <? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS,ACTIVE from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by ACTIVE DESC,CAMPUS_CODE ASC");
                while (!$res_type->EOF) { ?>
                    <option value="<?= $res_type->fields['PK_CAMPUS'] ?>" <? if ($res_type->RecordCount() == 1) echo "selected"; ?> <?php if ($res_type->fields['ACTIVE'] == '0') echo ' style="color : red" ' ?>><?= $res_type->fields['CAMPUS_CODE'] ?> <?php if ($res_type->fields['ACTIVE'] == '0') echo " (Inactive) " ?>
                    </option>
                <? $res_type->MoveNext();
                } ?>
            </select>
        </div>
    <?php } ?>

    <?php if ($REPORT_TYPE == 1) { ?>
        <div class="" id="PK_TERM_MASTER_5_DIV"></div>
        <?php echo getDateRangeContent(); ?>
    <?php } ?>

    <?php if ($REPORT_TYPE == 1) { ?>
        <div class="col-md-2 " id="PK_COURSE_OFFERING_1_DIV">
            <select id="PK_COURSE_OFFERING_1" name="PK_COURSE_OFFERING_1" class="form-control required-entry">
                <option value=""><?= COURSE_OFFERING_PAGE_TITLE ?></option>
            </select>
        </div>
    <?php } ?>

    <!--DIAM-1417-->
    <?php
    $ATTENDACE_TEXT='';
    if ($REPORT_TYPE == 5) {
        require_once("attendance_incomplete_additional_filters.php");
        $ATTENDACE_TEXT = 'Attendance ';
     } ?>
    <!--DIAM-1417-->
    <!-- Ticket # 1341  -->
    <?php if ($REPORT_TYPE == 9) { ?>
        <div class="col-md-2 " id="PK_TERM_MASTER_1_DIV">
            <select id="PK_TERM_MASTER_1" name="PK_TERM_MASTER_1" class="form-control required-entry" onchange="get_course_offering(this.value)">
                <option value="" selected><?= FIRST_TERM ?></option>
                <? $res_type = $db->Execute("select PK_TERM_MASTER,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 , ACTIVE from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'   order by ACTIVE DESC,BEGIN_DATE DESC");
                while (!$res_type->EOF) { ?>
                    <option value="<?= $res_type->fields['PK_TERM_MASTER'] ?>" <?php if ($res_type->fields['ACTIVE'] == '0') echo ' style="color : red" ' ?>><?= $res_type->fields['BEGIN_DATE_1'] ?> <?php if ($res_type->fields['ACTIVE'] == '0') echo " (Inactive) " ?>
                    </option>
                <? $res_type->MoveNext();
                } ?>
            </select>
        </div>
    <?php } ?>

    <?php if ($REPORT_TYPE == 4) { ?>
        <!-- Ticket # 1344  -->
        <div class="col-md-2 " id="PK_TERM_MASTER_6_DIV">
            <select id="PK_TERM_MASTER_6" name="PK_TERM_MASTER_6[]" multiple class="form-control required-entry" onchange="get_course_offering(this.value)">
                <? $res_type = $db->Execute("select ACTIVE , PK_TERM_MASTER,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'   order by ACTIVE DESC,BEGIN_DATE DESC");
                while (!$res_type->EOF) { ?>
                    <option value="<?= $res_type->fields['PK_TERM_MASTER'] ?>" <?php if ($res_type->fields['ACTIVE'] == '0') echo ' style="color : red" ' ?>><?= $res_type->fields['BEGIN_DATE_1'] ?> <?php if ($res_type->fields['ACTIVE'] == '0') echo " (Inactive) " ?>
                    </option>
                <? $res_type->MoveNext();
                } ?>
            </select>
        </div>
        <!-- Ticket # 1344  -->
    <?php } ?>
    <?php if ($REPORT_TYPE == 2 || $REPORT_TYPE == 14 || $REPORT_TYPE ==  7 || $REPORT_TYPE == 8 || $REPORT_TYPE == 12 || $REPORT_TYPE == 10) { //Ticket # 659
    ?>
        <div class="col-md-2 " id="PK_TERM_MASTER_DIV">
            <select id="PK_TERM_MASTER" name="PK_TERM_MASTER[]" multiple class="form-control"> <? $res_type = $db->Execute("select ACTIVE,PK_TERM_MASTER,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'   order by ACTIVE DESC,BEGIN_DATE DESC");
                                                                                                while (!$res_type->EOF) { ?>
                    <option value="<?= $res_type->fields['PK_TERM_MASTER'] ?>" <?php if ($res_type->fields['ACTIVE'] == '0') echo ' style="color : red" ' ?>><?= $res_type->fields['BEGIN_DATE_1'] ?> <?php if ($res_type->fields['ACTIVE'] == '0') echo " (Inactive) " ?>
                    </option>
                <? $res_type->MoveNext();
                                                                                                } ?>
            </select>
        </div>
    <?php } ?>

    <?php if ($REPORT_TYPE == 2 || $REPORT_TYPE == 14 || $REPORT_TYPE ==  7 || $REPORT_TYPE == 8 || $REPORT_TYPE == 12 || $REPORT_TYPE == 10) { //Ticket # 659
    ?>
        <div class="col-md-2 " id="PK_CAMPUS_PROGRAM_DIV">
            <select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control">
                <? $res_type = $db->Execute("select ACTIVE,PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by ACTIVE DESC,CODE ASC");
                while (!$res_type->EOF) { ?>
                    <option value="<?= $res_type->fields['PK_CAMPUS_PROGRAM'] ?>" <?php if ($res_type->fields['ACTIVE'] == '0') echo ' style="color : red" ' ?>><?= $res_type->fields['CODE'] . ' - ' . $res_type->fields['DESCRIPTION'] ?> <?php if ($res_type->fields['ACTIVE'] == '0') echo " (Inactive) " ?>
                    </option>
                <? $res_type->MoveNext();
                } ?>
            </select>
        </div>
    <?php } ?>

    <?php if ($REPORT_TYPE == 2 || $REPORT_TYPE == 14 || $REPORT_TYPE ==  7 || $REPORT_TYPE == 8 || $REPORT_TYPE == 12 || $REPORT_TYPE == 10) { //Ticket # 659
    ?>

        <div class="col-md-2 " id="PK_STUDENT_STATUS_DIV">
            <select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control">
                <? $res_type = $db->Execute("select ACTIVE,PK_STUDENT_STATUS,STUDENT_STATUS, DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND ADMISSIONS = 0  order by ACTIVE DESC,STUDENT_STATUS ASC");
                while (!$res_type->EOF) { ?>
                    <option value="<?= $res_type->fields['PK_STUDENT_STATUS'] ?>" <?php if ($res_type->fields['ACTIVE'] == '0') echo ' style="color : red" ' ?>><?= $res_type->fields['STUDENT_STATUS'] . ' - ' . $res_type->fields['DESCRIPTION'] ?> <?php if ($res_type->fields['ACTIVE'] == '0') echo " (Inactive) " ?>
                    </option>
                <? $res_type->MoveNext();
                } ?>
            </select>
        </div>
    <?php } ?>

    <?php if ($REPORT_TYPE == 2 || $REPORT_TYPE == 14 || $REPORT_TYPE ==  7 || $REPORT_TYPE == 8 || $REPORT_TYPE == 12 || $REPORT_TYPE == 10) { //Ticket # 659
    ?>
        <div class="col-md-2 " id="PK_STUDENT_GROUP_DIV">
            <select id="PK_STUDENT_GROUP" name="PK_STUDENT_GROUP[]" multiple class="form-control">
                <? $res_type = $db->Execute("select ACTIVE,PK_STUDENT_GROUP,STUDENT_GROUP from M_STUDENT_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by ACTIVE DESC,STUDENT_GROUP ASC");
                while (!$res_type->EOF) { ?>
                    <option value="<?= $res_type->fields['PK_STUDENT_GROUP'] ?>" <?php if ($res_type->fields['ACTIVE'] == '0') echo ' style="color : red" ' ?>><?= $res_type->fields['STUDENT_GROUP'] ?> <?php if ($res_type->fields['ACTIVE'] == '0') echo " (Inactive) " ?>
                    </option>
                <? $res_type->MoveNext();
                } ?>
            </select>
        </div>
    <?php } ?>

</div>

<div class="row">

    <?php if ($REPORT_TYPE == 11) {
        $onchangefun = 'get_term_from_campus_by_date(' . $REPORT_TYPE . ')';
    ?>
        <!-- Ticket # 1342 -->
        <div class="col-md-2 " id="PK_CAMPUS_3_DIV">
            <select id="PK_CAMPUS_3" name="PK_CAMPUS_3[]" multiple class="form-control" onchange="<?= $onchangefun ?>"><? //get_term_from_campus()
                                                                                                                        ?>
                <? $res_type = $db->Execute("select ACTIVE,CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by ACTIVE DESC,CAMPUS_CODE ASC");
                while (!$res_type->EOF) { ?>
                    <option value="<?= $res_type->fields['PK_CAMPUS'] ?>" <? if ($res_type->RecordCount() == 1) echo "selected"; ?> <?php if ($res_type->fields['ACTIVE'] == '0') echo ' style="color : red" ' ?>><?= $res_type->fields['CAMPUS_CODE'] ?> <?php if ($res_type->fields['ACTIVE'] == '0') echo " (Inactive) " ?>
                    </option>
                <? $res_type->MoveNext();
                } ?>
            </select>
        </div>
        <!-- Ticket # 1342 -->
    <?php } ?>

    <?php if ($REPORT_TYPE == 11) { ?>
        <!-- Ticket # 1266  -->
        <div class="" id="PK_TERM_MASTER_4_DIV"></div> <!-- Ticket # 1266  -->
        <?php echo getDateRangeContent(); ?>
    <?php } ?>


    <?php if ($REPORT_TYPE == 7 || $REPORT_TYPE == 8) { ?>
        <div class="col-md-2 " id="PK_COURSE_DIV">
            <select id="PK_COURSE" name="PK_COURSE[]" multiple class="form-control" onchange="get_course_offering(this.value);search()">
                <? /* Ticket # 1740  */
                $res_type = $db->Execute("select ACTIVE,PK_COURSE,COURSE_CODE, TRANSCRIPT_CODE,COURSE_DESCRIPTION from S_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by ACTIVE DESC, COURSE_CODE ASC");
                while (!$res_type->EOF) { ?>
                    <option value="<?= $res_type->fields['PK_COURSE'] ?>" <?php if ($res_type->fields['ACTIVE'] == '0') echo ' style="color : red" ' ?>><?= $res_type->fields['COURSE_CODE'] . ' - ' . $res_type->fields['TRANSCRIPT_CODE'] . ' - ' . $res_type->fields['COURSE_DESCRIPTION'] ?> <?php if ($res_type->fields['ACTIVE'] == '0') echo " (Inactive) " ?>
                    </option>
                <? $res_type->MoveNext();
                } /* Ticket # 1740  */ ?>
            </select>
        </div>
    <?php } ?>

    <?php if ($REPORT_TYPE == 4 || $REPORT_TYPE == 7 || $REPORT_TYPE == 8 || $REPORT_TYPE == 9 || $REPORT_TYPE == 11) { ?>
        <div class="col-md-2 " id="PK_COURSE_OFFERING_DIV">
            <select id="PK_COURSE_OFFERING" name="PK_COURSE_OFFERING" class="form-control">
                <option value=""><?= COURSE_OFFERING_PAGE_TITLE ?></option>
            </select>
        </div>
    <?php } ?>

    <?php if ($REPORT_TYPE == 3) {
        $onchangefun = 'get_term_from_campus_by_date(' . $REPORT_TYPE . ')';
    ?>
        <!-- Ticket # 1343  -->
        <div class="col-md-2" id="PK_CAMPUS_DIV_2">
            <select id="PK_CAMPUS_2" name="PK_CAMPUS_2[]" multiple class="form-control" onchange="<?= $onchangefun ?>"> <? //get_term_from_campus()
                                                                                                                        ?>
                <? $res_type = $db->Execute("select ACTIVE,CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by ACTIVE DESC,CAMPUS_CODE ASC");
                while (!$res_type->EOF) { ?>
                    <option value="<?= $res_type->fields['PK_CAMPUS'] ?>" <? if ($res_type->RecordCount() == 1) echo "selected"; ?> <?php if ($res_type->fields['ACTIVE'] == '0') echo ' style="color : red" ' ?>><?= $res_type->fields['CAMPUS_CODE'] ?> <?php if ($res_type->fields['ACTIVE'] == '0') echo " (Inactive) " ?>
                    </option>
                <? $res_type->MoveNext();
                } ?>
            </select>
        </div>
        <!-- Ticket # 1343  -->
    <?php } ?>

    <?php if ($REPORT_TYPE == 3) { ?>
        <div class="col-md-2 " id="GROUP_BY_DIV">
            <select id="GROUP_BY" name="GROUP_BY" class="form-control">
                <option value="">No Grouping</option>
                <option value="1">Group By Program</option>
            </select>
        </div>
    <?php } ?>

    <?php if ($REPORT_TYPE == 1 ||  $REPORT_TYPE ==  2  || $REPORT_TYPE == 3) { ?>

        <div class="col-md-2 " id="AS_OF_DATE_div">
            <input type="text" class="form-control date required-entry" id="AS_OF_DATE" name="AS_OF_DATE" placeholder="<?= AS_OF_DATE ?>">
        </div>
    <?php }
    ?>

    <?php if ($REPORT_TYPE == 2 || $REPORT_TYPE == 14) { ?>
        <!-- Ticket # 1247  -->
        <div class="col-md-2 align-self-center" id="ENROLLMENT_TYPE_div">
            <select id="ENROLLMENT_TYPE" name="ENROLLMENT_TYPE" class="form-control">
                <option value="1">All Enrollments</option>
                <option value="2">Current Enrollment</option>
            </select>
        </div>
    <?php } ?>

    <?php if ($REPORT_TYPE == 2 /*|| $REPORT_TYPE == 14*/) { ?>
        <div class="col-md-2 align-self-center" id="INCLUDE_INCOMPLETE_ATTENDANCE_DIV">
            <div class="col-12 col-sm-12 custom-control custom-checkbox form-group">
                <input type="checkbox" class="custom-control-input" id="INCLUDE_INCOMPLETE_ATTENDANCE" name="INCLUDE_INCOMPLETE_ATTENDANCE" value="1">
                <label class="custom-control-label" for="INCLUDE_INCOMPLETE_ATTENDANCE"><?= INCLUDE_INCOMPLETE_ATTENDANCE ?></label>
            </div>
        </div>
    <?php } ?>

    <?php if ($REPORT_TYPE == 2) { ?>
        <div class="col-md-2 align-self-center" id="INCLUDE_GPA_DIV">
            <div class="col-12 col-sm-12 custom-control custom-checkbox form-group">
                <input type="checkbox" class="custom-control-input" id="INCLUDE_GPA" name="INCLUDE_GPA" value="1">
                <label class="custom-control-label" for="INCLUDE_GPA"><?= INCLUDE_GPA ?></label>
            </div>
        </div>
        <!-- Ticket # 1247  -->
    <?php } ?>

    <?php if ($REPORT_TYPE == 3) { ?>
        <!-- Ticket # 1194  -->
        <div class="" id="PK_TERM_MASTER_2_DIV"></div>
        <?php echo getDateRangeContent(); ?>
    <?php } ?>

    <?php if ($REPORT_TYPE == 4 || $REPORT_TYPE == 5 || $REPORT_TYPE == 6 || $REPORT_TYPE == 10) { ?>
        <!-- Ticket # 1194  -->
        <div class="col-md-2 align-self-center" id="START_DATE_div">
            <input type="text" class="form-control date required-entry" id="START_DATE" name="START_DATE" placeholder="<?= $ATTENDACE_TEXT.START_DATE ?>"><!--DIAM-1417-->
        </div>
    <?php } ?>

    <?php if ($REPORT_TYPE == 4 || $REPORT_TYPE == 5 || $REPORT_TYPE == 6 || $REPORT_TYPE == 10) { ?>

        <div class="col-md-2 align-self-center" id="END_DATE_div">
            <input type="text" class="form-control date required-entry" id="END_DATE" name="END_DATE" placeholder="<?= $ATTENDACE_TEXT.END_DATE ?>"><!--DIAM-1417-->
        </div>
    <?php } ?>

    <?php if ($REPORT_TYPE == 10) { ?>

        <!-- Ticket # 1894 -->
        <div class="col-md-3 align-self-center" id="INCLUDE_ATTENDANCE_COMMENTS_DIV">
            <div class="col-12 col-sm-12 custom-control custom-checkbox form-group">
                <input type="checkbox" class="custom-control-input" id="INCLUDE_ATTENDANCE_COMMENTS" name="INCLUDE_ATTENDANCE_COMMENTS" value="1">
                <label class="custom-control-label" for="INCLUDE_ATTENDANCE_COMMENTS"><?= INCLUDE_ATTENDANCE_COMMENTS ?></label>
            </div>
        </div>
        <!-- Ticket # 1894 -->
    <?php } ?>

    <div class="col-md-2 " style="margin-top:10px">
        <!--
Search button needed ?
1 - no
2 - yes
3 - no
4 - no
5 - no
6 - no
7 - yes
8 - no
9 - yes
10 - no
11 - no
12 - REMOVEEEEEEEEEEEEEEEEEEE
13 - no
14 - yes

DIAM-757 added search btn for report id 12 in below in array     -->
        <?php if (in_array($REPORT_TYPE, [2, 7, 14, 12])) { ?>
            <button type="button" onclick="search()" class="btn waves-effect waves-light btn-info"><?= SEARCH ?></button>
        <? } ?>
        <?php

        if ($REPORT_TYPE != 14) {
            //if($REPORT_TYPE == 1 || $REPORT_TYPE == 3 ||$REPORT_TYPE == 4 || $REPORT_TYPE == 5 || $REPORT_TYPE == 6 || $REPORT_TYPE == 9 || $REPORT_TYPE == 10 || $REPORT_TYPE == 11){
        ?>
        <?php //}
        }
        ?>

        <?php //f($REPORT_TYPE == 1 || $REPORT_TYPE == 3 ||$REPORT_TYPE == 5 || $REPORT_TYPE == 6 || $REPORT_TYPE == 10 || $REPORT_TYPE == 11){
        if ($REPORT_TYPE != 14) {
        ?>
            <button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info" id="btn_1" style="display:none"><?= PDF ?></button>

            <? if ($REPORT_TYPE == 10) { //Ticket # 659
            ?>
                <button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info" id="btn_2" style="display:none"><?= CSV ?></button>
            <? } else { ?>
                <button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info" id="btn_2" style="display:none"><?= EXCEL ?></button>
            <? } ?>

        <?php }
        ?>
        <input type="hidden" name="FORMAT" id="FORMAT">
    </div>

</div>






<div id="hidden_iframe_div" class="d-none"></div>
</div>

<?php
function getDateRangeContent()
{

    $term_start_date = date('m/d/Y', strtotime("-3 months", strtotime(date('Y-m-d'))));
    $term_end_date = date('m/d/Y', strtotime("+3 months", strtotime(date('Y-m-d'))));

    return '<div class="col-md-3" style="bottom:21px;max-width:320px">
    <b  style="margin-bottom:5px">Term Begin Date Range</b>
        <div class="d-flex " style="margin-bottom:5px">
        <input type="text" class="form-control date" name="term_begin_start_date" field="term_begin_start_date" id="term_begin_start_date"  placeholder="Start Date" value="' . $term_start_date . '" >
        <input type="text" class="form-control date" name="term_begin_end_date"  field="term_begin_end_date" id="term_begin_end_date"   placeholder="End Date" value="' . $term_end_date . '" >
    </div>
</div>
<div class="col-md-3" style="bottom:21px;max-width:320px">
<b  style="margin-bottom:5px">Term End Date Range</b>
        <div class="d-flex ">
        <input type="text" class="form-control date" name="term_end_start_date" id="term_end_start_date"  placeholder="Start Date">
        <input type="text" class="form-control date" name="term_end_end_date" id="term_end_end_date"   placeholder="End Date">

    </div>
</div>';
}
?>
