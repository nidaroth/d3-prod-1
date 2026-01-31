<?
require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
require_once("check_access.php");

if (check_access('REPORT_REGISTRAR') == 0) {
    header("location:../index");
    exit;
}

if (!empty($_POST)) {
    //echo "<pre/>"; print_r($_POST) ;  die;
    /* Ticket # 1673
    $stud_id = "";
    foreach($_POST['PK_STUDENT_ENROLLMENT'] as $PK_STUDENT_ENROLLMENT) {
        if($stud_id != '')
            $stud_id .= ',';
        $stud_id .= $_POST['PK_STUDENT_MASTER_'.$PK_STUDENT_ENROLLMENT];
    } Ticket # 1673*/
    if ($_POST['REPORT_TYPE'] == 1 || $_POST['REPORT_TYPE'] == 5) {
        $_POST['PK_TERM_MASTER_5'] = explode(",", $_POST['PK_TERM_MASTER_5']);
    } else if ($_POST['REPORT_TYPE'] == 3) {
        $_POST['PK_TERM_MASTER_2'] = explode(",", $_POST['PK_TERM_MASTER_2']);
    }

    /* Ticket # 1673 */
    if ($_POST['REPORT_TYPE'] == 12) {
        $_POST['SELECTED_PK_STUDENT_MASTER'] = $_POST['SELECTED_PK_STUDENT_MASTER_w2ui'];
    }

    $temp = explode(",", $_POST['SELECTED_PK_STUDENT_MASTER']);
    $temp = array_unique($temp, SORT_NUMERIC);
    $stud_id = implode(",", $temp);
    /* Ticket # 1673 */

    $PK_STUDENT_MASTER        = $stud_id;
    $PK_STUDENT_ENROLLMENT     = implode(",", $_POST['PK_STUDENT_ENROLLMENT']);

    if ($_POST['REPORT_TYPE'] == 1) {
        if ($_POST['FORMAT'] == 1)
            header("location:attendance_absences_by_course_pdf?dt=" . $_POST['AS_OF_DATE'] . '&t_id=' . implode(",", $_POST['PK_TERM_MASTER_5']) . '&co=' . implode(",", $_POST['PK_COURSE_OFFERING_1']) . '&campus=' . implode(",", $_POST['PK_CAMPUS_1'])); //Ticket # 1194  Ticket # 1341
        else
            header("location:attendance_absences_by_course_excel?dt=" . $_POST['AS_OF_DATE'] . '&t_id=' . implode(",", $_POST['PK_TERM_MASTER_5']) . '&co=' . implode(",", $_POST['PK_COURSE_OFFERING_1']) . '&campus=' . implode(",", $_POST['PK_CAMPUS_1']));  //Ticket # 1194  Ticket # 1341
    } else if ($_POST['REPORT_TYPE'] == 2) {
        header("location:student_attendance_analysis_report?START_DATE=" . $_POST['START_DATE_analysis'] . "&eid=&id=&date=" . $_POST['AS_OF_DATE'] . '&FORMAT=' . $_POST['FORMAT'] . '&ENROLLMENT_TYPE=' . $_POST['ENROLLMENT_TYPE'] . '&s_id=' . $stud_id . '&incomplete=' . $_POST['INCLUDE_INCOMPLETE_ATTENDANCE'] . '&gpa=' . $_POST['INCLUDE_GPA'] . '&campus=' . implode(",", $_POST['PK_CAMPUS'])); //Ticket # 1247
    } else if ($_POST['REPORT_TYPE'] == 14) {
        header("location:student_attendance_analysis_report_wip?START_DATE=" . $_POST['START_DATE_analysis'] . "&eid=&id=&date=" . $_POST['AS_OF_DATE'] . '&FORMAT=' . $_POST['FORMAT'] . '&ENROLLMENT_TYPE=' . $_POST['ENROLLMENT_TYPE'] . '&s_id=' . $stud_id . '&incomplete=' . $_POST['INCLUDE_INCOMPLETE_ATTENDANCE'] . '&gpa=' . $_POST['INCLUDE_GPA'] . '&campus=' . implode(",", $_POST['PK_CAMPUS'])); //Ticket # 1247
    } else if ($_POST['REPORT_TYPE'] == 3) {
        header("location:attendance_daily_absents_by_course?AS_OF_DATE=" . $_POST['AS_OF_DATE'] . "&GROUP_BY=" . $_POST['GROUP_BY'] . '&FORMAT=' . $_POST['FORMAT'] . '&t_id=' . implode(",", $_POST['PK_TERM_MASTER_2']) . '&campus=' . implode(",", $_POST['PK_CAMPUS_2'])); //Ticket # 824 // Ticket # 1343

    } else if ($_POST['REPORT_TYPE'] == 4) {
        header("location:attendance_daily_sign_in_sheet?st=" . $_POST['START_DATE'] . "&et=" . $_POST['END_DATE'] . '&pt=' . $_POST['PRINT_TYPE'] . '&co=' . implode(",", $_POST['PK_COURSE_OFFERING']) . '&ins=' . implode(",", $_POST['INSTRUCTOR']) . '&tm=' . implode(",", $_POST['PK_TERM_MASTER_6']) . '&campus=' . implode(",", $_POST['PK_CAMPUS_1'])); //Ticket # 1344

    } else if ($_POST['REPORT_TYPE'] == 5) {
        header("location:attendance_incomplete_report?st=" . $_POST['START_DATE'] . "&et=" . $_POST['END_DATE'] . '&FORMAT=' . $_POST['FORMAT']. '&t_id=' . implode(",", $_POST['PK_TERM_MASTER_5']) . '&co=' . implode(",", $_POST['PK_COURSE_OFFERING_1']) . '&campus=' . implode(",", $_POST['PK_CAMPUS_1'])."&co_start_date=" . $_POST['term_begin_start_date'] . "&co_end_date=" . $_POST['term_begin_end_date']); //DIAM-1417
    } else if ($_POST['REPORT_TYPE'] == 6) {
        if ($_POST['FORMAT'] == 1)
            header("location:attendance_make_up_hours_pdf?st=" . $_POST['START_DATE'] . "&et=" . $_POST['END_DATE']); //Ticket # 1553
        else if ($_POST['FORMAT'] == 2)
            header("location:attendance_make_up_hours_excel?st=" . $_POST['START_DATE'] . "&et=" . $_POST['END_DATE']); //Ticket # 1553
    } else if ($_POST['REPORT_TYPE'] == 7) {
        if ($_POST['FORMAT'] == 1)
            header("location:attendance_report?eid=" . $PK_STUDENT_ENROLLMENT);
        else
            header("location:attendance_report_excel?eid=" . $PK_STUDENT_ENROLLMENT);
    } else if ($_POST['REPORT_TYPE'] == 8) {
        if ($_POST['FORMAT'] == 1)
            header("location:attendance_with_loa?eid=" . $PK_STUDENT_ENROLLMENT);
        else if ($_POST['FORMAT'] == 2)
            header("location:attendance_with_loa_excel?eid=" . $PK_STUDENT_ENROLLMENT);
    } else if ($_POST['REPORT_TYPE'] == 9) {
        header("location:attendance_roster_old?pt=" . $_POST['PRINT_TYPE'] . '&co=' . implode(",", $_POST['PK_COURSE_OFFERING']) . '&ins=' . implode(",", $_POST['INSTRUCTOR']) . '&tm=' . $_POST['PK_TERM_MASTER_1']);
    } else if ($_POST['REPORT_TYPE'] == 10) {
        // Ticket # DIAM-659
        //if ($_POST['FORMAT'] == 1)
        //header("location:attendance_tardy_hours_pdf?st=" . $_POST['START_DATE'] . '&et=' . $_POST['END_DATE'] . '&comm=' . $_POST['INCLUDE_ATTENDANCE_COMMENTS']); //Ticket # 1894
        //else if ($_POST['FORMAT'] == 2)
        //header("location:attendance_tardy_hours_excel?st=" . $_POST['START_DATE'] . '&et=' . $_POST['END_DATE'] . '&comm=' . $_POST['INCLUDE_ATTENDANCE_COMMENTS']); //Ticket # 1894
        // Ticket # DIAM-659
    } else if ($_POST['REPORT_TYPE'] == 11) { /* Ticket # 1266 */
        header("location:attendance_analysis_by_term?co=" . implode(",", $_POST['PK_COURSE_OFFERING']) . '&FORMAT=' . $_POST['FORMAT'] . '&campus=' . implode(",", $_POST['PK_CAMPUS_3'])); //Ticket # 1342
        /* Ticket # 1266 */
    } else if ($_POST['REPORT_TYPE'] == 12) { /* Ticket # 1508 */


        $temp_12 = explode(",", $_POST['SELECTED_PK_STUDENT_MASTER_w2ui']);
        $temp_12 = array_unique($temp_12, SORT_NUMERIC);
        $stud_id_12 = implode(",", $temp_12);

        if ($_POST['SUMMARY_REPORT'] == 1) {
            // header("location:attendance_summary_report_by_date?st=" . $_POST['START_DATE_1'] . "&et=" . $_POST['END_DATE_1'] . '&FORMAT=' . $_POST['FORMAT'] . '&ENROLLMENT_TYPE=' . $_POST['ENROLLMENT_TYPE_1'] . '&s_id=' . $stud_id_12 . '&exc_inactive=' . $_POST['EXCLUDE_INACTIVE_ATT_CODE'] . '&campus=' . implode(",", $_POST['PK_CAMPUS']) . "&min=" . $_POST['MIN_PER'] . "&max=" . $_POST['MAX_PER']); //Ticket # 1600


            $url = "location:attendance_summary_report_by_date?st=" . $_POST['START_DATE_1'] . "&et=" . $_POST['END_DATE_1'] . '&FORMAT=' . $_POST['FORMAT'] . '&ENROLLMENT_TYPE=' . $_POST['ENROLLMENT_TYPE_1'] . '&s_id=' . $stud_id_12 . '&exc_inactive=' . $_POST['EXCLUDE_INACTIVE_ATT_CODE'] . '&campus=' . implode(",", $_POST['PK_CAMPUS']) . "&min=" . $_POST['MIN_PER'] . "&max=" . $_POST['MAX_PER'];
            // Initialize a CURL session.
            $ch = curl_init();
            // Return Page contents.
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $json = json_encode(['s_id' => $stud_id_12]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            //grab URL and pass it to the variable.
            curl_setopt($ch, CURLOPT_URL, $url);
            $result = curl_exec($ch);
            echo $result;
        } else {
            header("location:attendance_report_by_date?st=" . $_POST['START_DATE_1'] . "&et=" . $_POST['END_DATE_1'] . '&FORMAT=' . $_POST['FORMAT'] . '&ENROLLMENT_TYPE=' . $_POST['ENROLLMENT_TYPE_1'] . '&s_id=' . $stud_id_12 . '&exc_inactive=' . $_POST['EXCLUDE_INACTIVE_ATT_CODE'] . '&campus=' . implode(",", $_POST['PK_CAMPUS']) . "&min=" . $_POST['MIN_PER'] . "&max=" . $_POST['MAX_PER']); //Ticket # 1600
        }
        /* Ticket # 1508 */
    }
    exit;
}


$res_camp_count = $db->Execute("select PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
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
    <title><?= MNU_ATTENDANCE ?> | <?= $title ?></title>
    <style>
        li>a>label {
            position: unset !important;
        }

        #advice-required-entry-PK_COURSE_OFFERING,
        #advice-required-entry-PK_TERM_MASTER_2,
        #advice-required-entry-PK_TERM_MASTER_3,
        #advice-required-entry-PK_TERM_MASTER_4,
        #advice-required-entry-INSTRUCTOR,
        #advice-required-entry-PK_TERM_MASTER_5,
        #advice-required-entry-PK_COURSE_OFFERING_1 {
            position: absolute;
            top: 38px;
        }

        .dropdown-menu>li>a {
            white-space: nowrap;
        }

        /* Ticket # 1607 */

        .lds-ring {
            position: absolute;
            left: 0;
            top: 0;
            right: 0;
            bottom: 0;
            margin: auto;
            width: 64px;
            height: 64px;
        }

        .lds-ring div {
            box-sizing: border-box;
            display: block;
            position: absolute;
            width: 51px;
            height: 51px;
            margin: 6px;
            border: 6px solid #0066ac;
            border-radius: 50%;
            animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
            border-color: #007bff transparent transparent transparent;
        }

        .lds-ring div:nth-child(1) {
            animation-delay: -0.45s;
        }

        .lds-ring div:nth-child(2) {
            animation-delay: -0.3s;
        }

        .lds-ring div:nth-child(3) {
            animation-delay: -0.15s;
        }

        @keyframes lds-ring {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        #loaders {
            position: fixed;
            width: 100%;
            z-index: 9999;
            bottom: 0;
            background-color: #2c3e50;
            display: block;
            left: 0;
            top: 0;
            right: 0;
            bottom: 0;
            opacity: 0.6;
            display: none;
        }

        /* #loadBtn {
            background-color: #499749;
            padding: 8px 17px;
            color: #fff;
            border-radius: 5px;
            font-size: 17px;
        } */
        .loadmore {
            text-align: center;
            margin-top: 10px;
        }

        /* 14 june 2023 */
        #PK_COURSE_OFFERING_1_DIV .multiselect-container,
        #PK_COURSE_OFFERING_DIV .multiselect-container {
            overflow-x: scroll;
            max-width: 650px !important;
        }

        /* 14 june 2023 */
    </style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
    <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
        <? require_once("menu.php"); ?>
        <div id="loaders" style="display: none;">
            <div class="lds-ring">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
        </div>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">
                            <?= MNU_ATTENDANCE ?>
                        </h4>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels" method="post" name="form1" id="form1">
                                    <input type="hidden" name="SELECTED_PK_STUDENT_MASTER" id="SELECTED_PK_STUDENT_MASTER" value=""> <!-- Ticket # 1673  -->
                                    <div class="row" style="padding-bottom:30px;">
                                        <div class="col-md-3 ">
                                            <b><?= ATTENDANCE_REPORT_TYPE ?></b>
                                            <select id="REPORT_TYPE" name="REPORT_TYPE" class="form-control" onchange="attendanceReportFilters(this.value);">
                                                <option value="1"><?= MNU_ATTENDANCE_ABSENCES_BY_COURSE ?></option>
                                                <option value="2"><?= MNU_STUDENT_ATTENDANCE_ANALYSIS_REPORT ?></option>
                                                <option value="11"><?= MNU_ATTENDANCE_ANALYSIS_BY_TERM ?></option> <!-- Ticket # 1266  -->
                                                <option value="16">Attendance Course Offering 2 Week</option>

                                                <option value="3"><?= MNU_ATTENDANCE_DAILY_ABSENTS_BY_COURSE ?></option>
                                                <option value="4"><?= MNU_ATTENDANCE_DAILY_SIGN_IN_SHEET ?></option>

                                                <option value="5"><?= MNU_ATTENDANCE_INCOMPLETE ?></option>
                                                <option value="6"><?= MNU_ATTENDANCE_MAKEUP_REPORT ?></option>
                                                <option value="14"><?= MNU_STUDENT_ATTENDANCE_MONTHLY_ANALYSIS_REPORT ?></option>
                                                <option value="7"><?= MNU_ATTENDANCE_REPORT ?></option>
                                                <option value="12">Attendance Report By Date Range</option> <!-- Ticket # 1508  -->
                                                <option value="8"><?= MNU_ATTENDANCE_WITH_LOA ?></option>
                                                <option value="9"><?= MNU_ATTENDANCE_ROSTER ?></option>
                                                <option value="13"> <?= MNU_ATTENDANCE_ROSTER ?> - Weekly</option>
                                                <option value="15"><?= MNU_ATTENDANCE_WEEKLY_SIGN_IN ?></option>
                                                <option value="10"><?= MNU_ATTENDANCE_TARDY_HOURS ?></option>
                                                <option value="17">Daily Attendance Sheet</option>
                                                <option value="18">Daily Attendance Signature Sheet - Time In/Out</option> <!-- DIAM-2155 -->
                                            </select>
                                        </div>
                                    </div>

                                    <div style="padding-bottom:10px;" id="attendance_report_type_filter" class="attendance_report_type_filter">
                                    </div>
                                    <div class="row" id="SEARCH_STUDENT_BAR">
                                        <div class="col-sm-3 ">
                                            <input style="display:none" type="text" class="form-control ui-autocomplete-input" id="SEARCH" name="SEARCH" placeholder="Search" onkeyup="apply_student_search(this)" value="" autocomplete="off">
                                        </div>
                                    </div>

                                    <div id="student_div" style="margin-top:20px;">
                                        <? /*$_REQUEST['show_check']     = 1;
                                        $_REQUEST['show_count']        = 1;
                                        $_REQUEST['group_by']        = '';
                                        $_REQUEST['ENROLLMENT']        = 1;
                                        require_once('ajax_search_student_for_reports.php');*/ ?>
                                    </div>
                                    <!-- 30 may 2023 -->
                                </form>
                                <div id="student_div_2" style="height:450px">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <? require_once("footer.php"); ?>
    </div>

    <? require_once("js.php"); ?>

    <script src="../backend_assets/dist/js/validation_prototype.js"></script>
    <script src="../backend_assets/dist/js/validation.js"></script>

    <style>
        /* Fix height of toolbox */
        #grid_grid_toolbar {
            height: 52px !important;
            overflow: hidden;
        }
        /* Hide MultiSearch row which shows PK_CAMPUS etc in search header */
        #grid_grid_searches{
            display: none !important;
        }

        /* Prevent w2ui's search input to overlap main header on scroll */
        #grid_grid_search_all {
            z-index: 10 !important;
        }
        /* Prevent w2ui's search input to overlap main header on scroll */
        .w2ui-toolbar .w2ui-scroll-wrapper .w2ui-tb-button {
            z-index: 10 !important;
        }
        /* Hide Reload button  */
        #tb_grid_toolbar_item_w2ui-reload{
            display: none !important;
        }
    </style>
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/w2ui/1.5.0/w2ui.min.css" />
    <!-- <script type="text/javascript" src="https://rawgit.com/vitmalina/w2ui/master/dist/w2ui.js"></script> -->
    <script type="text/javascript" src="../backend_assets/w2ui.js"></script>

    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css"> <!-- DIAM-757 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script> <!-- DIAM-757 -->

    <!-- SCRIPTS FOR : DIAM-628  -->

    <script>
        var grid = false;
        var selected_grid_ids = false;
        // Initialize Toastr
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: 5000 // Time duration the toastr is shown
        };



        jQuery(document).ready(function($) {
            // $('#AR_PK_CAMPUS').multiselect({
            //     includeSelectAllOption: true,
            //     allSelectedText: 'All <?= CAMPUS ?>',
            //     nonSelectedText: '<?= CAMPUS ?>',
            //     numberDisplayed: 2,
            //     nSelectedText: '<?= CAMPUS ?> selected',
            //     onDropdownHide: function(event) {
            //         // alert('hi')
            //         get_term_date_for_campus()
            //     }
            // });

            // jQuery('#AR_PK_TERM_MASTER').multiselect({
            //     includeSelectAllOption: true,
            //     allSelectedText: 'All <?= TERM ?>',
            //     nonSelectedText: '<?= TERM ?>',
            //     numberDisplayed: 2,
            //     nSelectedText: '<?= TERM ?> selected',
            //     onDropdownHide: function(event) {}
            // });

            var element = document.getElementById('student_div');

            element.addEventListener('DOMSubtreeModified', myFunctionresetsearch);

            function myFunctionresetsearch(e) {
                // console.log('element.innerHTML.trim()', element.innerHTML.trim());

                var x = document.getElementById("SEARCH");
                if (element.innerHTML.trim() != '') {
                    // alert("Modifying Search : Show")
                    document
                    x.style.display = "block";
                } else {
                    // alert("Modifying Search : Hide")
                    x.style.display = "none";

                }
            }


            $('.multiselect-btn').hover(function() {
                // over
                $('.multiselect-btn>button').removeAttr('title');
                console.log($('.multiselect-btn>button'));

            }, function() {
                // out
            });
            // $(document).on('mouseenter', '.select2-selection__rendered', function () { $(this).removeAttr('title');  });



        });



        function get_term_date_for_campus() {
            // Simplificado: El select de términos ya viene cargado desde PHP
            // Esta función solo resetea los dropdowns de Course Offering e Instructor
            try {
                jQuery(document).ready(function($) {
                    $('#AR_PK_COURSE_OFFERING').empty();
                    $('#AR_PK_COURSE_OFFERING').multiselect('destroy');
                    $('#AR_PK_COURSE_OFFERING').multiselect({
                        includeSelectAllOption: true,
                        allSelectedText: 'All <?= COURSE_OFFERING_PAGE_TITLE ?>',
                        nonSelectedText: '<?= COURSE_OFFERING_PAGE_TITLE ?>',
                        numberDisplayed: 2,
                        nSelectedText: '<?= COURSE_OFFERING_PAGE_TITLE ?> selected'
                    });
                });
            } catch (err) {
                console.log(err);
            }

            try {
                jQuery(document).ready(function($) {
                    $('#AR_INSTRUCTOR').empty();
                    $('#AR_INSTRUCTOR').multiselect('destroy');
                    $('#AR_INSTRUCTOR').multiselect({
                        includeSelectAllOption: true,
                        allSelectedText: 'All <?= INSTRUCTOR ?>',
                        nonSelectedText: '<?= INSTRUCTOR ?>',
                        numberDisplayed: 2,
                        nSelectedText: '<?= INSTRUCTOR ?> selected'
                    });
                });
            } catch (err) {
                console.log(err);
            }
        }


        function get_course_or_instructor() {

            jQuery(document).ready(function($) {
                // alert($('#AR_PK_TERM_MASTER').val());
                var PRINT_TYPE = document.getElementById('AR_PRINT_TYPE').value;
                // alert(PRINT_TYPE);
                if (PRINT_TYPE == 1) {
                    var data = 'PK_TERM_MASTER=' + $('#AR_PK_TERM_MASTER').val() + '&dont_show_term=1';
                    var url = "ajax_get_course_offering_from_term";
                } else if (PRINT_TYPE == 2) {
                    var data = 'PK_TERM_MASTER=' + $('#AR_PK_TERM_MASTER').val();
                    if (document.getElementById('REPORT_TYPE').value == 15) {
                        data += '&SHOW_UNASSIGNED=true';
                    }
                    var url = "ajax_get_course_offering_instructor_from_term";
                } else {

                    document.getElementById('AR_CI_DIV').innerHTML = '<select class="form-control required-entry" disabled> <option value="">Select Course / Instructor</option> </select>';
                    return;
                }


                var value = $.ajax({
                    url: url,
                    type: "POST",
                    data: data,
                    async: false,
                    cache: false,
                    success: function(data) {
                        data = data.replace('PK_COURSE_OFFERING', 'AR_PK_COURSE_OFFERING')
                        data = data.replace('INSTRUCTOR', 'AR_INSTRUCTOR')
                        if (PRINT_TYPE == 1) {
                            document.getElementById('AR_CI_DIV').innerHTML = data;
                            document.getElementById('AR_PK_COURSE_OFFERING').className = 'required-entry';
                            document.getElementById('AR_PK_COURSE_OFFERING').setAttribute('multiple', true);
                            document.getElementById('AR_PK_COURSE_OFFERING').name = "AR_PK_COURSE_OFFERING[]"
                            $("#AR_PK_COURSE_OFFERING option[value='']").remove();

                            $('#AR_PK_COURSE_OFFERING').multiselect({
                                includeSelectAllOption: true,
                                allSelectedText: 'All <?= COURSE_OFFERING_PAGE_TITLE ?>',
                                nonSelectedText: '<?= COURSE_OFFERING_PAGE_TITLE ?>',
                                numberDisplayed: 2,
                                nSelectedText: '<?= COURSE_OFFERING_PAGE_TITLE ?> selected'
                            });
                        } else {
                            document.getElementById('AR_CI_DIV').innerHTML = data;
                            document.getElementById('AR_INSTRUCTOR').className = 'required-entry';
                            document.getElementById('AR_INSTRUCTOR').setAttribute('multiple', true);
                            document.getElementById('AR_INSTRUCTOR').name = "AR_INSTRUCTOR[]"
                            $("#AR_INSTRUCTOR option[value='']").remove();

                            $('#AR_INSTRUCTOR').multiselect({
                                includeSelectAllOption: true,
                                allSelectedText: 'All <?= INSTRUCTOR ?>',
                                nonSelectedText: '<?= INSTRUCTOR ?>',
                                numberDisplayed: 2,
                                nSelectedText: '<?= INSTRUCTOR ?> selected'
                            });
                        }
                    }
                });
            });
        }
        function fetch_daily_attendance_sheet(){

            jQuery(document).ready(function($) {

            try {
                jQuery('.remove_on_reload').remove();
            } catch (error) {
                //do nothing
            }

                var error_div = '<div class="validation-advice remove_on_reload">This is a required field.</div>';
                var CHECK_COURSE_OR_INSTRUCTOR = '';
                var AR_PK_CAMPUS = $('#AR_PK_CAMPUS').val();
                var AR_PK_TERM_MASTER = $('#AR_PK_TERM_MASTER').val();
                var AR_PRINT_TYPE = $('#AR_PRINT_TYPE').val();
                var AR_DAYS = $('#AR_DAYS').val();
                var AS_OF_DATE = $('#AS_OF_DATE').val();
                var START_DATE_NEW = $('#START_DATE_analysis').val();
                var AR_PK_COURSE_OFFERING = $('#AR_PK_COURSE_OFFERING').val();
                if (AR_PK_CAMPUS == '' || AR_PK_TERM_MASTER == '' ||  AR_PK_COURSE_OFFERING == '') {

                    if ($('#AR_PK_CAMPUS').val() == '' || $('#AR_PK_CAMPUS').val() == null) {
                        $('#AR_PK_CAMPUS').parent().append(error_div);
                    }
                    // console.log("$('#AR_PK_TERM_MASTER').val()", $('#AR_PK_TERM_MASTER').val())

                    if ($('#AR_PK_TERM_MASTER').val() == '' || $('#AR_PK_TERM_MASTER').val() == null) {
                        $('#AR_PK_TERM_MASTER').parent().append(error_div);
                    }

                    if ($('#AR_PRINT_TYPE').val() == '' || $('#AR_PRINT_TYPE').val() == null) {
                        $('#AR_PRINT_TYPE').parent().append(error_div);
                    }

                    if ($('#AR_DAYS').val() == '' || $('#AR_DAYS').val() == null) {
                        $('#AR_DAYS').parent().append(error_div);
                    }

                    if ($('#AS_OF_DATE').val() == '' || $('#AS_OF_DATE').val() == null) {
                        // $('#AS_OF_DATE').parent().append(error_div);
                    }

                    try {
                        if ($('#AR_PK_COURSE_OFFERING').val() == '') {
                            $('#AR_PK_COURSE_OFFERING').parent().append(error_div);
                        }
                    } catch (err) {}


                    try {
                        if ($('#AR_INSTRUCTOR').val() == '') {
                            $('#AR_INSTRUCTOR').parent().append(error_div);
                        }
                    } catch (err) {}
                    alert("Please fill all fields !");
                }else{
                    // alert("hi");

                    data = {
                        AR_PK_CAMPUS: AR_PK_CAMPUS,
                        PK_TERM_MASTER: AR_PK_TERM_MASTER,
                        PRINT_TYPE: AR_PRINT_TYPE,
                        FORMAT: 1,
                        AR_DAYS: AR_DAYS,
                        AS_OF_DATE: AS_OF_DATE,
                        START_DATE_NEW: START_DATE_NEW,
                        PK_COURSE_OFFERING: AR_PK_COURSE_OFFERING,
                        ACTION: 'get_rooster_ajax'

                    }
                    //DIAM-2155
                    if (document.getElementById('REPORT_TYPE').value == 18) {
                        var ajax_url= 'attendance_daily_attendance_signature_sheet.php';
                        var pdf_file_name= 'Daily_attendance_signature_sheet.pdf';
                    }else{
                        var ajax_url= 'attendance_daily_attendance_sheet.php';
                        var pdf_file_name= 'Daily_attendance_sheet.pdf';
                    }
                    //DIAM-2155
                    //Generate pdf

                    var value = $.ajax({

                        url: ajax_url, //DIAM-2155
                        type: "POST",
                        data: data,
                        async: true,
                        cache: false,
                        beforeSend: function() {
                            document.getElementById('loaders').style.display = 'block';
                        },

                        success: function(data) {
                            const text = window.location.href;
                            const word = '/school';
                            const textArray = text.split(word); // ['This is ', ' text...']
                            const result = textArray.shift();
                            var report_download_name = pdf_file_name; //DIAM-2155
                            downloadDataUrlFromJavascript(report_download_name, result + '/school/' + data.path)
                            // alert(result + '/school/' + data.path);
                        },

                        complete: function() {
                            document.getElementById('loaders').style.display = 'none';
                        }
                    });
                }

            });

            }
        function getDivHeight(htmlContent) {
            // Create a new div element
            var contentDiv = document.createElement('div');

            // Set the HTML content
            contentDiv.innerHTML = htmlContent;

            // Set styles to make the div invisible
            contentDiv.style.width = '1123px'
            contentDiv.style.position = 'absolute';
            contentDiv.style.left = '-9999px';
            contentDiv.style.visibility = 'hidden';

            // Append the div to the document body
            document.body.appendChild(contentDiv);

            // Get the computed height of the div in pixels
            var divHeight = window.getComputedStyle(contentDiv).height;

            // Remove the div from the DOM
            document.body.removeChild(contentDiv);

            // Log the height (you can return it or use it as needed)
            console.log('Div Height:', divHeight);

            // Optionally, return the height
            return divHeight.replace('px' , '');
        }
        function pixelsToMillimeters(pixels, dpi) {
            // Convert pixels to millimeters using the formula
            var millimeters = (pixels / dpi) * 25.4;

            // Round to two decimal places (optional)
            millimeters = Math.round(millimeters * 100) / 100;

            return millimeters;
        }

        function fetch_attendnace_rooster() {

            //collect data

            // var AR_PK_CAMPUS = '';
            var PK_TERM_MASTER = '';
            var PRINT_TYPE = '';
            // var AR_DAYS = '';
            var PK_COURSE_OFFERING = '';
            var INSTRUCTOR = '';
            try {
                jQuery('.remove_on_reload').remove();
            } catch (error) {

            }


            jQuery(document).ready(function($) {
                var CHECK_COURSE_OR_INSTRUCTOR = '';
                var AR_PK_CAMPUS = $('#AR_PK_CAMPUS').val();
                var AR_PK_TERM_MASTER = $('#AR_PK_TERM_MASTER').val();

                var AR_PRINT_TYPE = $('#AR_PRINT_TYPE').val();
                var AR_DAYS = $('#AR_DAYS').val();
                var AS_OF_DATE = $('#AS_OF_DATE').val();
                var START_DATE_NEW = $('#START_DATE_analysis').val();
                // alert(START_DATE_NEW);
                if (AR_PRINT_TYPE == 1) {
                    var AR_PK_COURSE_OFFERING = $('#AR_PK_COURSE_OFFERING').val();







                    data = {
                        AR_PK_CAMPUS: AR_PK_CAMPUS,
                        PK_TERM_MASTER: AR_PK_TERM_MASTER,
                        PRINT_TYPE: AR_PRINT_TYPE,
                        FORMAT: 1,
                        AR_DAYS: AR_DAYS,
                        AS_OF_DATE: AS_OF_DATE,
                        START_DATE_NEW: START_DATE_NEW,
                        PK_COURSE_OFFERING: AR_PK_COURSE_OFFERING,
                        ACTION: 'get_rooster_ajax'
                    }

                    var PK_COURSE_OFFERING = data.PK_COURSE_OFFERING;
                    CHECK_COURSE_OR_INSTRUCTOR = PK_COURSE_OFFERING;

                } else {

                    var AR_INSTRUCTOR = $('#AR_INSTRUCTOR').val();
                    data = {
                        AR_PK_CAMPUS: AR_PK_CAMPUS,
                        PK_TERM_MASTER: AR_PK_TERM_MASTER,
                        PRINT_TYPE: AR_PRINT_TYPE,
                        FORMAT: 1,
                        AR_DAYS: AR_DAYS,
                        AS_OF_DATE: AS_OF_DATE,
                        START_DATE_NEW: START_DATE_NEW,
                        INSTRUCTOR: AR_INSTRUCTOR,
                        ACTION: 'get_rooster_ajax'
                    }
                    var INSTRUCTOR = data.INSTRUCTOR;
                    CHECK_COURSE_OR_INSTRUCTOR = INSTRUCTOR;
                }

                var PK_TERM_MASTER = data.PK_TERM_MASTER;
                var PRINT_TYPE = data.PRINT_TYPE;

                //ADD VALIDATION
                // alert("add form validation before this call"+PK_TERM_MASTER)
                var error_div = '<div class="validation-advice remove_on_reload">This is a required field.</div>';

                if (AR_PK_CAMPUS == '' || PK_TERM_MASTER == '' || PRINT_TYPE == '' || AR_DAYS == '' || AR_CLASS_MEETING_DATE == '' || (CHECK_COURSE_OR_INSTRUCTOR == '')) {

                    if ($('#AR_PK_CAMPUS').val() == '' || $('#AR_PK_CAMPUS').val() == null) {
                        $('#AR_PK_CAMPUS').parent().append(error_div);
                    }
                    // console.log("$('#AR_PK_TERM_MASTER').val()", $('#AR_PK_TERM_MASTER').val())
                    if ($('#AR_PK_TERM_MASTER').val() == '' || $('#AR_PK_TERM_MASTER').val() == null) {
                        $('#AR_PK_TERM_MASTER').parent().append(error_div);
                    }
                    if ($('#AR_PRINT_TYPE').val() == '' || $('#AR_PRINT_TYPE').val() == null) {
                        $('#AR_PRINT_TYPE').parent().append(error_div);
                    }
                    if ($('#AR_DAYS').val() == '' || $('#AR_DAYS').val() == null) {
                        $('#AR_DAYS').parent().append(error_div);
                    }
                    if ($('#AS_OF_DATE').val() == '' || $('#AS_OF_DATE').val() == null) {
                        // $('#AS_OF_DATE').parent().append(error_div);
                    }
                    try {
                        if ($('#AR_PK_COURSE_OFFERING').val() == '') {
                            $('#AR_PK_COURSE_OFFERING').parent().append(error_div);
                        }
                    } catch (err) {}

                    try {
                        if ($('#AR_INSTRUCTOR').val() == '') {
                            $('#AR_INSTRUCTOR').parent().append(error_div);
                        }
                    } catch (err) {}

                    // alert("Please fill all fields !");

                } else {
                    var report_url = 'attendance_roster_new.php';
                    var uniqid = '<?=uniqid()?>';
                    var report_download_name = "Attendance Roster - Weekly Report_"+uniqid+".pdf";
                    if (document.getElementById('REPORT_TYPE').value == 15) {
                        var report_url = 'attendance_roster_new_one_week_per_page.php';
                    } else if (document.getElementById('REPORT_TYPE').value == 14) {
                        var report_url = 'attendance_roster_new.php';
                    } else if (document.getElementById('REPORT_TYPE').value == 16) {
                        var report_url = 'attendance_roster_weekly_signin_ETC.php';
                        var report_download_name = "Course Offering Attendance Weekly Report_"+uniqid+".pdf";
                        if ($("#SHOW_TOTAL_HOURS").prop('checked') == true) {
                            data.SHOW_TOTAL_HOURS = "true";
                        }
                    }
                    //Get Footer size if reprot is ETC
                    if (document.getElementById('REPORT_TYPE').value == 16) {
                        var footer_height;
                        var value = $.ajax({
                        url: 'wkhtml_get_footer_height',
                        type: "POST",
                        data: data,
                        async: false,
                        cache: false,
                        success: function(data) {
                            footer_height = getDivHeight(data);
                            footer_height = pixelsToMillimeters(footer_height , 96)
                        }
                    });
                    data.PRECALCULATED_FOOTER_HEIGHT = footer_height;
                    }

                    // alert("loading");
                    var value = $.ajax({
                        url: report_url,
                        type: "POST",
                        data: data,
                        async: true,
                        cache: false,
                        beforeSend: function() {
                            document.getElementById('loaders').style.display = 'block';
                        },
                        success: function(data) {
                            const text = window.location.href;
                            const word = '/school';
                            const textArray = text.split(word); // ['This is ', ' text...']
                            const result = textArray.shift();

                            downloadDataUrlFromJavascript(report_download_name, result + '/school/' + data.path)
                            // alert(result + '/school/' + data.path);

                        },
                        complete: function() {
                            document.getElementById('loaders').style.display = 'none';

                        }
                    });
                }




            });


        }

        function downloadDataUrlFromJavascript(filename, dataUrl) {

            // Construct the 'a' element
            var link = document.createElement("a");
            link.download = filename;
            link.target = "_blank";

            // Construct the URI
            link.href = dataUrl;
            document.body.appendChild(link);
            link.click();

            // Cleanup the DOM
            document.body.removeChild(link);
            delete link;
        }
    </script>
    <!-- END OF SCRIPTS : DIAM-628  -->

    <script type="text/javascript">
        var form1 = new Validation('form1');
        jQuery(document).ready(function($) {
            $('#student_div_2').hide();
            attendanceReportFilters(1);


            $(document).on('mouseenter', '.select2-selection__rendered', function() {
                $(this).removeAttr('title');
            });
            //30 may 2023
            //show_filters(1) //30 may 2023
            //get_term_from_campus() //Ticket # 1341   //30 may 2023
        });

        function submit_form(val) {
            // alert("hi");
            jQuery(document).ready(function($) {
                var valid = new Validation('form1', {
                    onSubmit: false
                });
                var result = valid.validate();
                // console.log('result > ', result, '< end of result');
                //validation for DIAM-648
                // if (document.getElementById('REPORT_TYPE').value == 14) {
                //     var date1_check_1 = document.getElementById('START_DATE_analysis').value;
                //     var date2_check_1 = document.getElementById('AS_OF_DATE').value

                //     console.log(date1_check_1,
                //         date2_check_1);
                //     const date1_check = new Date(date1_check_1);
                //     const date2_check = new Date(date2_check_1);
                //     const diffTime = Math.abs(date2_check - date1_check);
                //     const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                //     console.log(diffTime + " milliseconds");
                //     console.log(diffDays + " days");
                //     if (diffDays > 366) {
                //         alert("Please select date range between 12 months")
                //         result = false;
                //     }
                //     if (get_count() > 800) {
                //     alert("Please select less than 800 students !");
                //     result = false;
                //     }
                // }
                if (result == true) {
                    document.getElementById('loaders').style.display = 'block';
                    document.getElementById('FORMAT').value = val


                    if (document.getElementById('REPORT_TYPE').value == 14) {
                        var data = $("#form1").serialize();
                        var value = $.ajax({
                            url: "student_attendance_analysis_report_wip",
                            type: "POST",
                            data: data,
                            async: true,
                            cache: false,
                            success: function(data) {
                                const text = window.location.href;
                                const word = '/school';
                                const textArray = text.split(word); // ['This is ', ' text...']
                                const result_url = textArray.shift();
                                // var fname = data.path;
                                // fname = fname.substring(fname.lastIndexOf('/') + 1);

                                // alert(result_url + '/school/' + data.path)
                                if (document.getElementById('FORMAT').value == 1)
                                    // document.getElementById('SEARCH_STUDENT_BAR').innerHTML = data;
                                    downloadDataUrlFromJavascript("Monthly_Attendance_Analysis_<? echo $_SESSION['PK_ACCOUNT'] ?>_" + Date.now() + ".pdf", result_url + '/school/' + data.path)
                                if (document.getElementById('FORMAT').value == 2)
                                    downloadDataUrlFromJavascript("Monthly_Attendance_Analysis_<? echo  $_SESSION['PK_ACCOUNT'] ?>_" + Date.now() + ".xlsx", result_url + '/school/' + data.path)
                                document.getElementById('loaders').style.display = 'none';

                            },
                            complete: function() {
                                document.getElementById('loaders').style.display = 'none';

                            }
                        }).responseText;
                    } else if (document.getElementById('REPORT_TYPE').value == 10) { // Ticket # DIAM-659

                        if (val == 1)
                            var actionurl = 'attendance_tardy_hours_pdf';
                        if (val == 2)
                            var actionurl = 'attendance_tardy_hours_excel';

                        var data = $("#form1").serialize();
                        var value = $.ajax({
                            url: actionurl,
                            type: "POST",
                            data: data + "&ajaxpost=1",
                            async: true,
                            cache: false,
                            success: function(data) {
                                // console.log(data);
                                const text = window.location.href;
                                const word = '/school';
                                const textArray = text.split(word);
                                const result_url = textArray.shift();

                                if (document.getElementById('FORMAT').value == 1)
                                    downloadDataUrlFromJavascript(data.filename, result_url + '/school/' + data.path)
                                if (document.getElementById('FORMAT').value == 2)
                                    downloadDataUrlFromJavascript(data.filename, result_url + '/school/' + data.path)
                                document.getElementById('loaders').style.display = 'none';

                            },
                            complete: function() {
                                document.getElementById('loaders').style.display = 'none';

                            }
                        }).responseText;
                        // Ticket # DIAM-659
                    } else {
                        document.form1.submit();
                        document.getElementById('loaders').style.display = 'none';
                    }
                }
            });
        }


        function get_report_ajax(report_type) {

            val = report_type;
            if (document.getElementById('REPORT_TYPE').value == 12) {
                document.getElementById('loaders').style.display = 'block';
                // Ticket # DIAM-659
                if (val == 1){
                    if (document.getElementById('SUMMARY_REPORT').checked) {
                        var actionurl = 'attendance_summary_report_by_date';
                    }else{
                        var actionurl = 'attendance_report_by_date';
                    }
                }
                if (val == 2){
                    if (document.getElementById('SUMMARY_REPORT').checked) {
                        var actionurl = 'attendance_summary_report_by_date';
                    }else{
                        var actionurl = 'attendance_report_by_date';
                    }
                }
                    

                // var data = $("#form1").serialize();
                if (document.getElementById('REPORT_TYPE').value == 12) {
                    var final_selected_records_values = new Array();
                    final_selected_records = grid.getSelection(true);
                    // console.log(final_selected_records);
                    final_selected_records.forEach(function(ele) {
                        final_selected_records_values.push(grid.records[ele].PK_STUDENT_MASTER);
                    });
                    final_selected_records_values_str = final_selected_records_values.join(',');
                    console.log(final_selected_records_values_str);
                }
                if (final_selected_records_values_str == '') {
                    toastr.warning("Please select students to generate the report !", 'Alert');
                    document.getElementById('loaders').style.display = 'none';
                    return;
                }
                var value = jQuery.ajax({
                    url: actionurl,
                    type: "POST",
                    dataType: 'JSON',
                    data: {
                        st: document.getElementById("START_DATE_1").value,

                        et: document.getElementById("END_DATE_1").value,

                        FORMAT: val,

                        ENROLLMENT_TYPE: document.getElementById("ENROLLMENT_TYPE_1").value,

                        s_id: final_selected_records_values_str,

                        exc_inactive: document.getElementById("EXCLUDE_INACTIVE_ATT_CODE").value,

                        campus: document.getElementById("PK_CAMPUS").value,

                        min: document.getElementById("MIN_PER").value,

                        max: document.getElementById("MAX_PER").value,

                        output_file_loc_in_json : 'yes'
                    },
                    async: true,
                    cache: false,
                    success: function(data) {
                        // console.log(data);
                        // console.log(data.path);
                        const text = window.location.href;
                        const word = '/school';
                        const textArray = text.split(word);
                        const result_url = textArray.shift();

                        if (report_type == 1)
                            downloadDataUrlFromJavascript(data.filename, result_url + '/school/' + data.path)
                        if (report_type == 2)
                            downloadDataUrlFromJavascript(data.filename, result_url + '/school/' + data.path)
                        document.getElementById('loaders').style.display = 'none';

                    },
                    complete: function() {
                        document.getElementById('loaders').style.display = 'none';

                    },
                    error: function() {
                        document.getElementById('loaders').style.display = 'none';
                    }
                }).responseText;
                // e.preventDefault();
                // Ticket # DIAM-659
            }




        }

        /* Ticket # 1266  */
        function get_course_offering(val) {
            jQuery(document).ready(function($) {
                if (document.getElementById('REPORT_TYPE').value == 4 || document.getElementById('REPORT_TYPE').value == 9) {
                    var PRINT_TYPE = document.getElementById('PRINT_TYPE').value
                    /* Ticket # 1344  */
                    var term_id = "";
                    if (document.getElementById('REPORT_TYPE').value == 4)
                        term_id = "PK_TERM_MASTER_6";
                    else
                        term_id = "PK_TERM_MASTER_1";
                    if (PRINT_TYPE == 1) {
                        var data = 'PK_TERM_MASTER=' + $('#' + term_id).val() + '&dont_show_term=1';
                        var url = "ajax_get_course_offering_from_term";
                    } else {
                        var data = 'PK_TERM_MASTER=' + $('#' + term_id).val();
                        var url = "ajax_get_course_offering_instructor_from_term";
                    }
                    /* Ticket # 1344  */
                } else if (document.getElementById('REPORT_TYPE').value == 11) {
                    var data = 'PK_TERM_MASTER=' + $('#PK_TERM_MASTER_4').val() + '&dont_show_term=1&sort=asc'; //Ticket # 1342
                    var url = "ajax_get_course_offering_from_term";
                } else if (document.getElementById('REPORT_TYPE').value == 1 || document.getElementById('REPORT_TYPE').value == 5) { //<!--DIAM-1417-->
                    /* Ticket # 1341   */
                    var data = 'PK_TERM_MASTER=' + $('#PK_TERM_MASTER_5').val() + '&dont_show_term=2' + '&PK_CAMPUS=' + $('#PK_CAMPUS_1').val();
                    var url = "ajax_get_course_offering_from_term"; /* Ticket # 1341   */
                } else if (document.getElementById('REPORT_TYPE').value == 12) {
                    /* Ticket # 1635 */
                    var data = 'val=' + $('#COURSE_PK_COURSE').val() + '&multiple=1&PK_TERM_MASTER=' + $('#COURSE_PK_TERM').val();
                    var url = "ajax_get_course_offering";
                    /* Ticket # 1635 */
                } else {
                    var data = 'val=' + $('#PK_COURSE').val() + '&multiple=0';
                    var url = "ajax_get_course_offering";
                }

                var value = $.ajax({
                    url: url,
                    type: "POST",
                    data: data,
                    async: false,
                    cache: false,
                    success: function(data) {
                        //alert(data)
                        if (document.getElementById('REPORT_TYPE').value == 4 || document.getElementById('REPORT_TYPE').value == 9) {
                            if (PRINT_TYPE == 1) {
                                document.getElementById('PK_COURSE_OFFERING_DIV').innerHTML = data;
                                document.getElementById('PK_COURSE_OFFERING').className = 'required-entry';
                                document.getElementById('PK_COURSE_OFFERING').setAttribute('multiple', true);
                                document.getElementById('PK_COURSE_OFFERING').name = "PK_COURSE_OFFERING[]"
                                $("#PK_COURSE_OFFERING option[value='']").remove();

                                $('#PK_COURSE_OFFERING').multiselect({
                                    includeSelectAllOption: true,
                                    allSelectedText: 'All <?= COURSE_OFFERING_PAGE_TITLE ?>',
                                    nonSelectedText: '<?= COURSE_OFFERING_PAGE_TITLE ?>',
                                    numberDisplayed: 2,
                                    nSelectedText: '<?= COURSE_OFFERING_PAGE_TITLE ?> selected'
                                });
                            } else {
                                document.getElementById('PK_COURSE_OFFERING_DIV').innerHTML = data;
                                document.getElementById('INSTRUCTOR').className = 'required-entry';
                                document.getElementById('INSTRUCTOR').setAttribute('multiple', true);
                                document.getElementById('INSTRUCTOR').name = "INSTRUCTOR[]"
                                $("#INSTRUCTOR option[value='']").remove();

                                $('#INSTRUCTOR').multiselect({
                                    includeSelectAllOption: true,
                                    allSelectedText: 'All <?= INSTRUCTOR ?>',
                                    nonSelectedText: '<?= INSTRUCTOR ?>',
                                    numberDisplayed: 2,
                                    nSelectedText: '<?= INSTRUCTOR ?> selected'
                                });
                            }
                        } else if (document.getElementById('REPORT_TYPE').value == 11) {
                            document.getElementById('PK_COURSE_OFFERING_DIV').innerHTML = data;
                            document.getElementById('PK_COURSE_OFFERING').className = 'required-entry';
                            document.getElementById('PK_COURSE_OFFERING').setAttribute('multiple', true);
                            document.getElementById('PK_COURSE_OFFERING').name = "PK_COURSE_OFFERING[]"
                            $("#PK_COURSE_OFFERING option[value='']").remove();

                            $('#PK_COURSE_OFFERING').multiselect({
                                includeSelectAllOption: true,
                                allSelectedText: 'All <?= COURSE_OFFERING_PAGE_TITLE ?>',
                                nonSelectedText: '<?= COURSE_OFFERING_PAGE_TITLE ?>',
                                numberDisplayed: 2,
                                nSelectedText: '<?= COURSE_OFFERING_PAGE_TITLE ?> selected'
                            });
                        } else if (document.getElementById('REPORT_TYPE').value == 1 || document.getElementById('REPORT_TYPE').value == 5) { //<!--DIAM-1417-->
                            /* Ticket # 1341 */
                            data = data.replace('id="PK_COURSE_OFFERING"', 'id="PK_COURSE_OFFERING_1"');
                            document.getElementById('PK_COURSE_OFFERING_1_DIV').innerHTML = data;
                            // document.getElementById('PK_COURSE_OFFERING_1').className = 'required-entry'; // DIAM-2183, remove validation
                            document.getElementById('PK_COURSE_OFFERING_1').setAttribute('multiple', true);
                            document.getElementById('PK_COURSE_OFFERING_1').name = "PK_COURSE_OFFERING_1[]"
                            $("#PK_COURSE_OFFERING_1 option[value='']").remove();

                            $('#PK_COURSE_OFFERING_1').multiselect({
                                includeSelectAllOption: true,
                                allSelectedText: 'All <?= COURSE_OFFERING_PAGE_TITLE ?>',
                                nonSelectedText: '<?= COURSE_OFFERING_PAGE_TITLE ?>',
                                numberDisplayed: 2,
                                nSelectedText: '<?= COURSE_OFFERING_PAGE_TITLE ?> selected'
                            });

                            /* Ticket # 1341 */
                        } else if (document.getElementById('REPORT_TYPE').value == 12) {
                            /* Ticket # 1635 */
                            data = data.replace('id="PK_COURSE_OFFERING"', 'id="COURSE_PK_COURSE_OFFERING"');
                            document.getElementById('COURSE_COURSE_OFFERING_DIV').innerHTML = data;
                            document.getElementById('COURSE_PK_COURSE_OFFERING').className = '';
                            document.getElementById('COURSE_PK_COURSE_OFFERING').setAttribute('multiple', true);
                            document.getElementById('COURSE_PK_COURSE_OFFERING').name = "COURSE_PK_COURSE_OFFERING[]"
                            $("#COURSE_PK_COURSE_OFFERING option[value='']").remove();
                            $("#COURSE_PK_COURSE_OFFERING option[value='0']").remove();
                            // DIAM-757
                            //document.getElementById('COURSE_PK_COURSE_OFFERING').setAttribute("onchange", "search()");

                            $('#COURSE_PK_COURSE_OFFERING').multiselect({
                                includeSelectAllOption: true,
                                allSelectedText: 'All <?= COURSE_OFFERING ?>',
                                nonSelectedText: '<?= COURSE_OFFERING ?>',
                                numberDisplayed: 2,
                                nSelectedText: '<?= COURSE_OFFERING ?> selected'
                            });

                            /* Ticket # 1635 */
                        } else {
                            document.getElementById('PK_COURSE_OFFERING_DIV').innerHTML = data;
                            document.getElementById('PK_COURSE_OFFERING').setAttribute('multiple', true);
                            document.getElementById('PK_COURSE_OFFERING').name = "PK_COURSE_OFFERING[]"
                            $("#PK_COURSE_OFFERING option[value='']").remove();

                            document.getElementById('PK_COURSE_OFFERING').setAttribute("onchange", "search()");

                            $('#PK_COURSE_OFFERING').multiselect({
                                includeSelectAllOption: true,
                                allSelectedText: 'All <?= COURSE_OFFERING_PAGE_TITLE ?>',
                                nonSelectedText: '<?= COURSE_OFFERING_PAGE_TITLE ?>',
                                numberDisplayed: 2,
                                nSelectedText: '<?= COURSE_OFFERING_PAGE_TITLE ?> selected'
                            });
                        }

                        var dd = document.getElementsByClassName('multiselect-native-select');
                        for (var i = 0; i < dd.length; i++) {
                            dd[i].style.width = '100%';
                        }
                    }
                }).responseText;
            });
        }
        /* Ticket # 1266  */

        function get_course_offering_session() {}

        // function checkvalidation(){
        //     jQuery(document).ready(function($) {
        //         var valid = new Validation('form1', {
        //             onSubmit: false
        //         });
        //         //var result = valid.validate();

        //     });
        //     return result;
        // }

        /* Ticket # 1247 */
        function search() {
            //alert(checkvalidation());
            document.getElementById('loaders').style.display = 'block';
            jQuery(document).ready(function($) {
                var ENROLLMENT = 1
                if (document.getElementById('REPORT_TYPE').value == 2)
                    ENROLLMENT = $('#ENROLLMENT_TYPE').val()
                else if (document.getElementById('REPORT_TYPE').value == 12) //Ticket # 1508
                    ENROLLMENT = 2 //Ticket # 1508
                else if (document.getElementById('REPORT_TYPE').value == 14)
                    ENROLLMENT = $('#ENROLLMENT_TYPE').val()
                /* Ticket # 1635 */
                if (document.getElementById('REPORT_TYPE').value == 12)
                    var PK_COURSE_OFFERING = $('#COURSE_PK_COURSE_OFFERING').val()
                else
                    var PK_COURSE_OFFERING = $('#PK_COURSE_OFFERING').val()
                /* Ticket # 1635 */

                if (PK_COURSE_OFFERING === undefined) {
                    PK_COURSE_OFFERING = "";
                }

                var PK_COURSE = $('#PK_COURSE').val();
                if (PK_COURSE === undefined) {
                    PK_COURSE = "";
                }

                var PK_CAMPUS = $('#PK_CAMPUS').val();
                if (PK_CAMPUS === undefined) {
                    PK_CAMPUS = "";
                }


                var data = 'PK_STUDENT_GROUP=' + $('#PK_STUDENT_GROUP').val() + '&PK_TERM_MASTER=' + $('#PK_TERM_MASTER').val() + '&PK_CAMPUS_PROGRAM=' + $('#PK_CAMPUS_PROGRAM').val() + '&PK_STUDENT_STATUS=' + $('#PK_STUDENT_STATUS').val() + '&PK_COURSE=' + PK_COURSE + '&PK_COURSE_OFFERING=' + PK_COURSE_OFFERING + '&show_check=1&show_count=1&group_by=&ENROLLMENT=' + ENROLLMENT + '&PK_CAMPUS=' + PK_CAMPUS + '&REPORT_TYPE=' + $('#REPORT_TYPE').val();
                try {
                    //DIAM-757 - new feedback to remove this filters for report 12
                    if (document.getElementById('REPORT_TYPE').value != 12){
                        data += '&TREM_BEGIN_START_DATE=' + $('#term_begin_start_date').val() + '&TREM_BEGIN_END_DATE=' + $('#term_begin_end_date').val() + '&TREM_END_START_DATE=' + $('#term_end_start_date').val() + '&TREM_END_END_DATE=' + $('#term_end_end_date').val();
                    }
                } catch (error) {

                }
                //Ticket # 1247 //Ticket # 1635
                // DIAM-757 REPORT_TYPE
                var value = $.ajax({
                    url: "ajax_search_student_for_reports",
                    type: "POST",
                    data: data,
                    async: false,
                    cache: false,
                    success: function(data) {
                        // console.log(data);
                        try {
                            grid.clear();
                        } catch (error) {

                        }

                        if (document.getElementById('REPORT_TYPE').value == 12) { // DIAM-757
                            var res_data = JSON.parse(data);
                            document.getElementById('student_div').innerHTML = res_data.tablehtml;
                            if (document.getElementById('student_div').innerHTML == 'undefined')
                                document.getElementById('student_div').innerHTML = '';
                            async function populateTable(data) {
                                // document.getElementById('loaders').style.display = 'block';
                                //    console.log(data.length);
                                var chunk = 1000000;
                                let totalRows = data.length;
                                while (totalRows > 0) {
                                    if (totalRows < chunk) {
                                        chunk = totalRows;
                                    }
                                    loadedrows = totalRows;
                                    await addChunk(data, chunk, loadedrows);
                                    totalRows = totalRows - chunk;

                                }
                                document.getElementById('loaders').style.display = 'none';


                                return;
                            }
                            var table = $('#student_update_table');

                            function addChunk(datas, chunksize, loadedrows) {

                                var columns = 9;
                                var chunk = chunksize;
                                return new Promise(resolve => {
                                    setTimeout(() => {
                                        $('.loadedchunks').text((datas.length - loadedrows) + ' Out of ' + datas.length);
                                        for (var i = 0; i < chunk; i++) {
                                            // data_row = Object.values(datas[i]);
                                            //console.log(i+'=='+data_row);
                                            //if(data_row.length > 0){
                                            // var tr = $('<tr></tr>');
                                            // for (var j = 0; j < columns; j++) {
                                            //     if (j == 0) {
                                            //         var td = $('<td>' + data_row[j] + '</td>');
                                            //         //td.text(data_row[j]);
                                            //     } else {
                                            //         var td = $('<td></td>');
                                            //         td.text(data_row[j]);
                                            //     }

                                            //     td.appendTo(tr);
                                            // }
                                            // tr.appendTo(table);
                                            resolve();
                                            //# w2ui Grid
                                            grid.records.push({
                                                PK_STUDENT_ENROLLMENT: datas[i].PK_STUDENT_ENROLLMENT,
                                                PK_STUDENT_MASTER: datas[i].PK_STUDENT_MASTER,
                                                STU_NAME: datas[i].STU_NAME,
                                                STUDENT_ID: datas[i].STUDENT_ID,
                                                CAMPUS_CODE: datas[i].CAMPUS_CODE,
                                                BEGIN_DATE_1: datas[i].BEGIN_DATE_1,
                                                CODE: datas[i].CODE,
                                                STUDENT_STATUS: datas[i].STUDENT_STATUS,
                                                STUDENT_GROUP: datas[i].STUDENT_GROUP
                                            });

                                            //}

                                        }
                                        // grid.records.push(datas);
                                        // console.log(datas);
                                        // console.log("Added chucnk of " + chunksize + " rows");
                                        grid.refresh()

                                        //#End of wui2 grid
                                        resolve();
                                    }, 50);
                                });


                            }

                            if (res_data.json_data.length > 0) {
                                setTimeout(populateTable(res_data.json_data), 0);
                            } else {
                                document.getElementById('loaders').style.display = 'none';
                            }

                            document.getElementById('SELECTED_PK_STUDENT_MASTER').value = ''; //Ticket # 1673
                            show_btn() //Ticket # 1247
                        } else {
                            document.getElementById('student_div').innerHTML = data;
                            document.getElementById('SELECTED_PK_STUDENT_MASTER').value = ''; //Ticket # 1673
                            show_btn() //Ticket # 1247
                            document.getElementById('loaders').style.display = 'none';
                        }
                    }
                }).responseText;
            });
        }
        /* Ticket # 1247 */

        function fun_select_all() {
            var str = '';
            if (document.getElementById('SEARCH_SELECT_ALL').checked == true)
                str = true;
            else
                str = false;

            var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
            for (var i = 0; i < PK_STUDENT_ENROLLMENT.length; i++) {
                PK_STUDENT_ENROLLMENT[i].checked = str
            }
            get_count()
        }
        /* Ticket # 1673 */
        function get_count() {
            var PK_STUDENT_MASTER_sel = '';
            var tot = 0
            var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
            for (var i = 0; i < PK_STUDENT_ENROLLMENT.length; i++) {
                if (PK_STUDENT_ENROLLMENT[i].checked == true) {
                    if (PK_STUDENT_MASTER_sel != '')
                        PK_STUDENT_MASTER_sel += ',';

                    PK_STUDENT_MASTER_sel += document.getElementById('S_PK_STUDENT_MASTER_' + PK_STUDENT_ENROLLMENT[i].value).value
                    tot++;
                }
            }
            document.getElementById('SELECTED_PK_STUDENT_MASTER').value = PK_STUDENT_MASTER_sel
            //alert(PK_STUDENT_MASTER_sel)

            document.getElementById('SELECTED_COUNT').innerHTML = tot
            show_btn()
            return tot;
        }
        /* Ticket # 1673 */

        function show_btn() {

            if (document.getElementById('btn_1'))
                document.getElementById('btn_1').style.display = 'none'; //Ticket # 1247

            if (document.getElementById('btn_2'))
                document.getElementById('btn_2').style.display = 'none'; //Ticket # 1247

            var flag = 0;
            var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
            for (var i = 0; i < PK_STUDENT_ENROLLMENT.length; i++) {
                if (PK_STUDENT_ENROLLMENT[i].checked == true) {
                    flag++;
                    break;
                }
            }

            if (flag == 1) {
                /* Ticket # 1508 */
                if (document.getElementById('REPORT_TYPE').value == 12) {
                    document.getElementById('btn_3').style.display = 'inline';
                    document.getElementById('btn_4').style.display = 'inline';
                } else {
                    document.getElementById('btn_1').style.display = 'inline';

                    if (document.getElementById('REPORT_TYPE').value == 2 || document.getElementById('REPORT_TYPE').value == 14 || document.getElementById('REPORT_TYPE').value == 5 || document.getElementById('REPORT_TYPE').value == 6 || document.getElementById('REPORT_TYPE').value == 7 || document.getElementById('REPORT_TYPE').value == 8 || document.getElementById('REPORT_TYPE').value == 10 || document.getElementById('REPORT_TYPE').value == 12)
                        document.getElementById('btn_2').style.display = 'inline';
                }
                /* Ticket # 1508 */
            }
        }

        function show_btn_w2ui(cnt) {
            if (cnt > 0) {
                display_val = 'inline';
            } else {
                display_val = 'none';
            }
            if (document.getElementById('REPORT_TYPE').value == 12) {
                document.getElementById('btn_3').style.display = display_val;
                document.getElementById('btn_4').style.display = display_val;
            } else {
                document.getElementById('btn_1').style.display = display_val;

                if (document.getElementById('REPORT_TYPE').value == 2 || document.getElementById('REPORT_TYPE').value == 14 || document.getElementById('REPORT_TYPE').value == 5 || document.getElementById('REPORT_TYPE').value == 6 || document.getElementById('REPORT_TYPE').value == 7 || document.getElementById('REPORT_TYPE').value == 8 || document.getElementById('REPORT_TYPE').value == 10 || document.getElementById('REPORT_TYPE').value == 12)
                    document.getElementById('btn_2').style.display = display_val;
            }
        }

        /* Ticket # 1194  */
        function show_filters(val) {

            if (document.getElementById('btn_1'))
                document.getElementById('btn_1').style.display = 'none';

            if (document.getElementById('btn_2'))
                document.getElementById('btn_2').style.display = 'none';

            if (document.getElementById('GROUP_BY_DIV'))
                document.getElementById('GROUP_BY_DIV').style.display = 'none';

            if (document.getElementById('START_DATE_div'))
                document.getElementById('START_DATE_div').style.display = 'none';

            if (document.getElementById('END_DATE_div'))
                document.getElementById('END_DATE_div').style.display = 'none';

            if (document.getElementById('PK_TERM_MASTER_2_DIV'))
                document.getElementById('PK_TERM_MASTER_2_DIV').style.display = 'none';

            /* Ticket # 1508 */
            if (document.getElementById('btn_3'))
                document.getElementById('btn_3').style.display = 'none';

            if (document.getElementById('btn_4'))
                document.getElementById('btn_4').style.display = 'none';

            if (document.getElementById('START_DATE_1_div'))
                document.getElementById('START_DATE_1_div').style.display = 'none';

            if (document.getElementById('END_DATE_1_div'))
                document.getElementById('END_DATE_1_div').style.display = 'none';

            if (document.getElementById('ENROLLMENT_TYPE_1'))
                document.getElementById('ENROLLMENT_TYPE_1').style.display = 'none';

            if (document.getElementById('EXCLUDE_INACTIVE_ATT_CODE_DIV'))
                document.getElementById('EXCLUDE_INACTIVE_ATT_CODE_DIV').style.display = 'none';

            if (document.getElementById('SUMMARY_REPORT_DIV'))
                document.getElementById('SUMMARY_REPORT_DIV').style.display = 'none';
            /* Ticket # 1508 */

            if (document.getElementById('PK_TERM_MASTER_1_DIV'))
                document.getElementById('PK_TERM_MASTER_1_DIV').style.display = 'none';

            if (document.getElementById('PK_STUDENT_STATUS_DIV'))
                document.getElementById('PK_STUDENT_STATUS_DIV').style.display = 'none';

            if (document.getElementById('PK_CAMPUS_PROGRAM_DIV'))
                document.getElementById('PK_CAMPUS_PROGRAM_DIV').style.display = 'none';

            if (document.getElementById('PK_TERM_MASTER_DIV'))
                document.getElementById('PK_TERM_MASTER_DIV').style.display = 'none';

            if (document.getElementById('PK_STUDENT_GROUP_DIV'))
                document.getElementById('PK_STUDENT_GROUP_DIV').style.display = 'none';

            if (document.getElementById('PK_COURSE_OFFERING_DIV'))
                document.getElementById('PK_COURSE_OFFERING_DIV').style.display = 'none';

            if (document.getElementById('PK_COURSE_DIV'))
                document.getElementById('PK_COURSE_DIV').style.display = 'none';

            if (document.getElementById('AS_OF_DATE_div'))
                document.getElementById('AS_OF_DATE_div').style.display = 'none';

            if (document.getElementById('PRINT_TYPE_DIV'))
                document.getElementById('PRINT_TYPE_DIV').style.display = 'none';

            if (document.getElementById('PK_CAMPUS_DIV'))
                document.getElementById('PK_CAMPUS_DIV').style.display = 'none'; //Ticket # 1247

            if (document.getElementById('PK_CAMPUS_3_DIV'))
                document.getElementById('PK_CAMPUS_3_DIV').style.display = 'none'; //Ticket # 1342

            /* Ticket # 1247 */
            if (document.getElementById('ENROLLMENT_TYPE_div'))
                document.getElementById('ENROLLMENT_TYPE_div').style.display = 'none';

            if (document.getElementById('INCLUDE_INCOMPLETE_ATTENDANCE_DIV'))
                document.getElementById('INCLUDE_INCOMPLETE_ATTENDANCE_DIV').style.display = 'none';

            if (document.getElementById('INCLUDE_GPA_DIV'))
                document.getElementById('INCLUDE_GPA_DIV').style.display = 'none';
            /* Ticket # 1247 */

            if (document.getElementById('PK_TERM_MASTER_4_DIV'))
                document.getElementById('PK_TERM_MASTER_4_DIV').style.display = 'none';

            /* Ticket # 1341  */

            if (document.getElementById('PK_CAMPUS_DIV_1'))
                document.getElementById('PK_CAMPUS_DIV_1').style.display = 'none';

            if (document.getElementById('PK_COURSE_OFFERING_1_DIV'))
                document.getElementById('PK_COURSE_OFFERING_1_DIV').style.display = 'none';

            if (document.getElementById('PK_TERM_MASTER_5_DIV'))
                document.getElementById('PK_TERM_MASTER_5_DIV').style.display = 'none';
            /* Ticket # 1341  */

            /* Ticket # 1600 */
            if (document.getElementById('MIN_PER_div'))
                document.getElementById('MIN_PER_div').style.display = 'none';

            if (document.getElementById('MAX_PER_div'))
                document.getElementById('MAX_PER_div').style.display = 'none';
            /* Ticket # 1600 */

            if (document.getElementById('PK_TERM_MASTER_6_DIV'))
                document.getElementById('PK_TERM_MASTER_6_DIV').style.display = 'none'; //Ticket # 1344

            if (document.getElementById('ATT_BY_DATE_RANGE_FIELDS'))
                document.getElementById('ATT_BY_DATE_RANGE_FIELDS').style.display = 'none'; //Ticket # 1635

            if (document.getElementById('PK_CAMPUS_DIV_2'))
                document.getElementById('PK_CAMPUS_DIV_2').style.display = 'none'; //Ticket # 1343

            if (document.getElementById('INCLUDE_ATTENDANCE_COMMENTS_DIV'))
                document.getElementById('INCLUDE_ATTENDANCE_COMMENTS_DIV').style.display = 'none'; //Ticket # 1894

            if (val == 1 || val == 2 || val == 14 || val == 3) {
                if (document.getElementById('AS_OF_DATE_div'))
                    document.getElementById('AS_OF_DATE_div').style.display = 'block';
            }


            if (val == 1) {
                if (document.getElementById('PK_COURSE_OFFERING_DIV'))
                    document.getElementById('PK_COURSE_OFFERING_DIV').style.display = 'none';

                if (document.getElementById('PK_COURSE_DIV'))
                    document.getElementById('PK_COURSE_DIV').style.display = 'none';

                if (document.getElementById('START_DATE_div'))
                    document.getElementById('START_DATE_div').style.display = 'none';

                if (document.getElementById('END_DATE_div'))
                    document.getElementById('END_DATE_div').style.display = 'none';

                if (document.getElementById('PK_TERM_MASTER_2_DIV'))
                    document.getElementById('PK_TERM_MASTER_2_DIV').style.display = 'none'; //Ticket # 1341

                /* Ticket # 1341 */
                document.getElementById('PK_CAMPUS_DIV_1').style.display = 'block';
                document.getElementById('PK_TERM_MASTER_5_DIV').style.display = 'block';
                document.getElementById('PK_COURSE_OFFERING_1_DIV').style.display = 'block';
                /* Ticket # 1341 */

                document.getElementById('student_div').innerHTML = ''
                document.getElementById('btn_1').style.display = 'inline';
                document.getElementById('btn_2').style.display = 'inline';
            } else if (val == 2 || val == 14) {
                /* Ticket # 1247 */
                document.getElementById('PK_STUDENT_STATUS_DIV').style.display = 'inline';
                document.getElementById('PK_CAMPUS_PROGRAM_DIV').style.display = 'inline';
                document.getElementById('PK_TERM_MASTER_DIV').style.display = 'inline';
                document.getElementById('PK_STUDENT_GROUP_DIV').style.display = 'inline';


                if (val == 2) {
                    document.getElementById('ENROLLMENT_TYPE_div').style.display = 'inline';
                    document.getElementById('INCLUDE_GPA_DIV').style.display = 'inline';
                    document.getElementById('INCLUDE_INCOMPLETE_ATTENDANCE_DIV').style.display = 'inline';

                }

                if (val == 14) {
                    document.getElementById('ENROLLMENT_TYPE_div').style.display = 'inline';
                }
                document.getElementById('PK_CAMPUS_DIV').style.display = 'inline'; //Ticket # 1247

                //document.getElementById('btn_1').style.display                     = 'inline';
                //document.getElementById('btn_2').style.display                     = 'inline';
                /* Ticket # 1247 */
            } else if (val == 3) {

                document.getElementById('PK_TERM_MASTER_2_DIV').style.display = 'block'; //Ticket # 824
                document.getElementById('student_div').innerHTML = ''
                document.getElementById('btn_1').style.display = 'inline';
                document.getElementById('btn_2').style.display = 'inline';
                document.getElementById('GROUP_BY_DIV').style.display = 'inline';
                document.getElementById('PK_CAMPUS_DIV_2').style.display = 'inline'; //Ticket # 1343

            } else if (val == 4 || val == 9) {
                /* Ticket # 1344  */
                document.getElementById('PRINT_TYPE_DIV').style.display = 'inline';
                document.getElementById('PK_COURSE_OFFERING_DIV').style.display = 'inline';
                document.getElementById('btn_1').style.display = 'inline';

                if (val == 4) {

                    document.getElementById('PK_CAMPUS_DIV_1').style.display = 'inline';
                    document.getElementById('START_DATE_div').style.display = 'inline';
                    document.getElementById('END_DATE_div').style.display = 'inline';
                    document.getElementById('PK_TERM_MASTER_6_DIV').style.display = 'inline';
                } else {
                    document.getElementById('PK_TERM_MASTER_1_DIV').style.display = 'inline';
                }
                /* Ticket # 1344  */

                document.getElementById('student_div').innerHTML = ''
            } else if (val == 5 || val == 6 || val == 10) {

                if (document.getElementById('PK_COURSE_OFFERING_DIV'))
                    document.getElementById('PK_COURSE_OFFERING_DIV').style.display = 'none';

                if (document.getElementById('PK_COURSE_DIV'))
                    document.getElementById('PK_COURSE_DIV').style.display = 'none';

                document.getElementById('START_DATE_div').style.display = 'inline';
                document.getElementById('END_DATE_div').style.display = 'inline';

                document.getElementById('student_div').innerHTML = ''
                document.getElementById('btn_1').style.display = 'inline';
                document.getElementById('btn_2').style.display = 'inline';

                /* Ticket # 1894  */
                if (val == 10)
                    document.getElementById('INCLUDE_ATTENDANCE_COMMENTS_DIV').style.display = 'inline';
                /* Ticket # 1894  */
            } else if (val == 7 || val == 8) {
                document.getElementById('PK_STUDENT_STATUS_DIV').style.display = 'inline';
                document.getElementById('PK_CAMPUS_PROGRAM_DIV').style.display = 'inline';
                document.getElementById('PK_TERM_MASTER_DIV').style.display = 'inline';
                document.getElementById('PK_STUDENT_GROUP_DIV').style.display = 'inline';

                document.getElementById('PK_COURSE_OFFERING_DIV').style.display = 'inline';
                document.getElementById('PK_COURSE_DIV').style.display = 'inline';

                search() ///////////////////...............................here find inline or block dive
            } else if (val == 11) {
                /* Ticket # 1266  */
                document.getElementById('PK_TERM_MASTER_4_DIV').style.display = 'inline';
                document.getElementById('PK_COURSE_OFFERING_DIV').style.display = 'inline';
                document.getElementById('PK_CAMPUS_3_DIV').style.display = 'inline'; // Ticket # 1342
                document.getElementById('student_div').innerHTML = ''; // Ticket # 1342
                document.getElementById('btn_1').style.display = 'inline';
                document.getElementById('btn_2').style.display = 'inline';
            } /* Ticket # 1266  */
            else if (val == 12) {
                /* Ticket # 1508  */
                document.getElementById('PK_STUDENT_STATUS_DIV').style.display = 'inline';
                document.getElementById('PK_CAMPUS_PROGRAM_DIV').style.display = 'inline';
                document.getElementById('PK_TERM_MASTER_DIV').style.display = 'inline';
                document.getElementById('PK_STUDENT_GROUP_DIV').style.display = 'inline';
                document.getElementById('PK_CAMPUS_DIV').style.display = 'inline';

                //document.getElementById('PK_COURSE_OFFERING_DIV').style.display         = 'inline'; Ticket # 1635
                //document.getElementById('PK_COURSE_DIV').style.display                     = 'inline'; Ticket # 1635
                document.getElementById('START_DATE_1_div').style.display = 'inline';
                document.getElementById('END_DATE_1_div').style.display = 'inline';
                document.getElementById('ENROLLMENT_TYPE_1').style.display = 'inline';
                document.getElementById('EXCLUDE_INACTIVE_ATT_CODE_DIV').style.display = 'inline';
                document.getElementById('SUMMARY_REPORT_DIV').style.display = 'inline';

                show_min_max_per() // Ticket # 1600

                document.getElementById('ATT_BY_DATE_RANGE_FIELDS').style.display = 'flex'; //Ticket # 1635

                //search() // DIAM-757
                /* Ticket # 1508  */
            } else if (val == 13 || val == 16 || val == 15 || val == 17 || val == 18) { //DIAM-2155
                document.getElementById('AR_REP_FILTER').classList.remove("d-none");
            }

            if (val == 14) {
                document.getElementById('PK_CAMPUS_DIV').style.display = 'inline';
            }
            //Ticket # 659
            if (val == 10) {
                document.getElementById('PK_CAMPUS_DIV').style.display = 'inline';
                document.getElementById('PK_TERM_MASTER_DIV').style.display = 'inline';
                document.getElementById('PK_CAMPUS_PROGRAM_DIV').style.display = 'inline';
                document.getElementById('PK_STUDENT_STATUS_DIV').style.display = 'inline';
                document.getElementById('PK_STUDENT_GROUP_DIV').style.display = 'inline';
            }
            //Ticket # 659
            //<!--DIAM-1417-->
            if(val == 5){
                document.getElementById('PK_CAMPUS_DIV_1').style.display = 'block';
                document.getElementById('PK_TERM_MASTER_5_DIV').style.display = 'block';
                document.getElementById('PK_COURSE_OFFERING_1_DIV').style.display = 'block';
            }
            //<!--DIAM-1417-->
            <? if ($res_camp_count->RecordCount() == 1) { ?>
                if (val == 1 || val == 11 || val == 3 || val == 4)
                    //get_term_from_campus() //30 may 2023
                    get_term_from_campus_by_date(val);
                else if (val == 2)
                    search()
                // else if (val == 12)
                //     get_course_term_from_campus() //DIAM-757
            <? } ?>

            var dd = document.getElementsByClassName('multiselect-native-select');
            for (var i = 0; i < dd.length; i++) {
                dd[i].style.width = '100%';
            }


        }
        /* Ticket # 1194  */

        /* Ticket # 1600 */
        function show_min_max_per() {
            if (document.getElementById('SUMMARY_REPORT').checked == true) {
                document.getElementById('MIN_PER_div').style.display = 'inline';
                document.getElementById('MAX_PER_div').style.display = 'inline';
            } else {
                document.getElementById('MIN_PER_div').style.display = 'none';
                document.getElementById('MAX_PER_div').style.display = 'none';
            }
        }
        /* Ticket # 1600 */

        /* Ticket # 1341 */
        function get_term_from_campus() {
            /* Ticket # 1344  */

            jQuery(document).ready(function($) {
                var data = 'PK_CAMPUS=' + $('#PK_CAMPUS_1').val();

                if (document.getElementById('REPORT_TYPE').value == 11) //Ticket # 1342
                    data = 'PK_CAMPUS=' + $('#PK_CAMPUS_3').val(); //Ticket # 1342

                var value = $.ajax({
                    url: "ajax_get_term_from_campus",
                    type: "POST",
                    data: data,
                    async: false,
                    cache: false,
                    success: function(data) {
                        var term_id = '';

                        if (document.getElementById('REPORT_TYPE').value == 4)
                            term_id = "PK_TERM_MASTER_6";
                        else if (document.getElementById('REPORT_TYPE').value == 3) //Ticket # 1343
                            term_id = "PK_TERM_MASTER_2"; //Ticket # 1343
                        else if (document.getElementById('REPORT_TYPE').value == 11) //Ticket # 1342
                            term_id = "PK_TERM_MASTER_4"; //Ticket # 1342
                        else
                            term_id = "PK_TERM_MASTER_5";

                        data = data.replace('id="PK_TERM_MASTER"', 'id="' + term_id + '"');

                        if (document.getElementById(term_id + '_DIV'))
                            document.getElementById(term_id + '_DIV').innerHTML = data;

                        if (document.getElementById(term_id)) {
                            document.getElementById(term_id).className = 'required-entry';
                            document.getElementById(term_id).name = term_id + "[]"
                            document.getElementById(term_id).setAttribute('multiple', true);
                        }

                        if (document.getElementById('REPORT_TYPE').value != 3) { //Ticket # 1343

                            if (document.getElementById(term_id))
                                document.getElementById(term_id).setAttribute("onchange", "get_course_offering()");
                        }

                        $("#" + term_id + " option[value='']").remove();

                        $('#' + term_id).multiselect({
                            includeSelectAllOption: true,
                            allSelectedText: 'All <?= TERM ?>',
                            nonSelectedText: '<?= TERM ?>',
                            numberDisplayed: 2,
                            nSelectedText: '<?= TERM ?> selected'
                        });

                    }
                }).responseText;
            });
            /* Ticket # 1344  */
        }
        /* Ticket # 1341 */

        /* Ticket # 1635 */
        function get_course_term_from_campus() {
            jQuery(document).ready(function($) {
                var data = 'PK_CAMPUS=' + $('#TERM_PK_CAMPUS').val();
                var value = $.ajax({
                    url: "ajax_get_term_from_campus",
                    type: "POST",
                    data: data,
                    async: false,
                    cache: false,
                    success: function(data) {
                        var term_id = 'COURSE_PK_TERM';

                        data = data.replace('id="PK_TERM_MASTER"', 'id="' + term_id + '"');
                        document.getElementById(term_id + '_DIV').innerHTML = data;
                        document.getElementById(term_id).className = '';
                        document.getElementById(term_id).name = term_id + "[]"
                        document.getElementById(term_id).setAttribute('multiple', true);
                        document.getElementById(term_id).setAttribute("onchange", "get_course_from_term()");

                        $("#" + term_id + " option[value='']").remove();

                        $('#' + term_id).multiselect({
                            includeSelectAllOption: true,
                            allSelectedText: 'All <?= COURSE_TERM ?>',
                            nonSelectedText: '<?= COURSE_TERM ?>',
                            numberDisplayed: 2,
                            nSelectedText: '<?= COURSE_TERM ?> selected'
                        });

                    }
                }).responseText;
            });
        }

        function get_course_from_term() {
            jQuery(document).ready(function($) {
                var data = 'PK_TERM=' + $('#COURSE_PK_TERM').val();
                var value = $.ajax({
                    url: "ajax_get_course_from_term",
                    type: "POST",
                    data: data,
                    async: false,
                    cache: false,
                    success: function(data) {
                        data = data.replace('id="PK_COURSE"', 'id="COURSE_PK_COURSE"');
                        document.getElementById('COURSE_COURSE_DIV').innerHTML = data;
                        document.getElementById('COURSE_PK_COURSE').className = '';
                        document.getElementById('COURSE_PK_COURSE').name = "COURSE_PK_COURSE[]"
                        document.getElementById('COURSE_PK_COURSE').setAttribute('multiple', true);
                        document.getElementById('COURSE_PK_COURSE').setAttribute("onchange", "get_course_offering()");

                        $("#COURSE_PK_COURSE option[value='']").remove();

                        $('#COURSE_PK_COURSE').multiselect({
                            includeSelectAllOption: true,
                            allSelectedText: 'All <?= COURSE ?>',
                            nonSelectedText: '<?= COURSE ?>',
                            numberDisplayed: 2,
                            nSelectedText: '<?= COURSE ?> selected'
                        });

                        //Also flush course offering selection
                        if (document.getElementById('REPORT_TYPE').value == 12) {
                            try {
                                /* Ticket # 1635 */
                             
                                document.getElementById('COURSE_PK_COURSE_OFFERING').innerHTML = '';
                                // DIAM-757
                                //document.getElementById('COURSE_PK_COURSE_OFFERING').setAttribute("onchange", "search()");
                                $('#COURSE_PK_COURSE_OFFERING').multiselect('rebuild');
                                $('#COURSE_PK_COURSE_OFFERING').multiselect({
                                    includeSelectAllOption: true,
                                    allSelectedText: 'All <?= COURSE_OFFERING ?>',
                                    nonSelectedText: '<?= COURSE_OFFERING ?>',
                                    numberDisplayed: 2,
                                    nSelectedText: '<?= COURSE_OFFERING ?> selected'
                                });
                                /* Ticket # 1635 */
                            } catch (error) {
                                    console.info("Error" , error);
                            }
                        }

                    }
                }).responseText;
            });
        }

        /* Ticket # 1635 */
    </script>

    <script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
    <link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css" />


    <script type="text/javascript">
        function addMultiselect(report_type) {
            var $ = jQuery.noConflict();
            //jQuery(document).ready(function($) {

            if (report_type == 2 || report_type == 14 || report_type == 7 || report_type == 8) {
                $('#PK_COURSE').multiselect({
                    includeSelectAllOption: true,
                    allSelectedText: 'All <?= COURSE_CODE ?>',
                    nonSelectedText: '<?= COURSE_CODE ?>',
                    numberDisplayed: 2,
                    nSelectedText: '<?= COURSE_CODE ?> selected'
                });
            }

            if (report_type == 2 || report_type == 14 || report_type == 7 || report_type == 12 || report_type == 8 || report_type == 10) { //Ticket # 659
                $('#PK_STUDENT_GROUP').multiselect({
                    includeSelectAllOption: true,
                    allSelectedText: 'All <?= GROUP_CODE ?>',
                    nonSelectedText: '<?= GROUP_CODE ?>',
                    numberDisplayed: 2,
                    nSelectedText: '<?= GROUP_CODE ?> selected'
                });
            }

            if (report_type == 2 || report_type == 14 || report_type == 7 || report_type == 12 || report_type == 8 || report_type == 10) { //Ticket # 659
                $('#PK_TERM_MASTER').multiselect({
                    includeSelectAllOption: true,
                    allSelectedText: 'All <?= FIRST_TERM ?>',
                    nonSelectedText: '<?= FIRST_TERM ?>',
                    numberDisplayed: 2,
                    nSelectedText: '<?= FIRST_TERM ?> selected'
                });

            }

            if (report_type == 2 || report_type == 14 || report_type == 7 || report_type == 12 || report_type == 8 || report_type == 10) { //Ticket # 659
                $('#PK_CAMPUS_PROGRAM').multiselect({
                    includeSelectAllOption: true,
                    allSelectedText: 'All <?= PROGRAM ?>',
                    nonSelectedText: '<?= PROGRAM ?>',
                    numberDisplayed: 2,
                    nSelectedText: '<?= PROGRAM ?> selected'
                });
            }

            if (report_type == 2 || report_type == 14 || report_type == 7 || report_type == 12 || report_type == 8 || report_type == 10) { //Ticket # 659
                $('#PK_STUDENT_STATUS').multiselect({
                    includeSelectAllOption: true,
                    allSelectedText: 'All <?= STATUS ?>',
                    nonSelectedText: '<?= STATUS ?>',
                    numberDisplayed: 2,
                    nSelectedText: '<?= STATUS ?> selected'
                });

            }

            if (report_type == 11 || report_type == 4 || report_type == 7 || report_type == 8 || report_type == 9) {
                $('#PK_COURSE_OFFERING').multiselect({
                    includeSelectAllOption: true,
                    allSelectedText: 'All <?= COURSE_OFFERING_PAGE_TITLE ?>',
                    nonSelectedText: '<?= COURSE_OFFERING_PAGE_TITLE ?>',
                    numberDisplayed: 2,
                    nSelectedText: '<?= COURSE_OFFERING_PAGE_TITLE ?> selected'
                });

            }

            if (report_type == 3) {
                /* Ticket # 1194 */
                $('#PK_TERM_MASTER_2').multiselect({
                    includeSelectAllOption: true,
                    allSelectedText: 'All <?= COURSE_TERM ?>',
                    nonSelectedText: '<?= COURSE_TERM ?>',
                    numberDisplayed: 2,
                    nSelectedText: '<?= COURSE_TERM ?> selected'
                });
                /* Ticket # 1194 */
            }

            if (report_type == 12 || report_type == 2 || report_type == 14 || report_type == 10) { //Ticket # 659
                //$("#PK_CAMPUS").multiselect('destroy');
                /* Ticket # 1247 */
                $('#PK_CAMPUS').multiselect({
                    includeSelectAllOption: true,
                    allSelectedText: 'All <?= CAMPUS ?>',
                    nonSelectedText: '<?= CAMPUS ?>',
                    numberDisplayed: 2,
                    nSelectedText: '<?= CAMPUS ?> selected'
                });
                /* Ticket # 1247 */
            }


            if (report_type == 1 || report_type == 4) {
                /* Ticket # 1341 */
                $('#PK_CAMPUS_1').multiselect({
                    includeSelectAllOption: true,
                    allSelectedText: 'All <?= CAMPUS ?>',
                    nonSelectedText: '<?= CAMPUS ?>',
                    numberDisplayed: 2,
                    nSelectedText: '<?= CAMPUS ?> selected',
                });
            }


            if (report_type == 1) {

                $('#PK_TERM_MASTER_5').multiselect({
                    includeSelectAllOption: true,
                    allSelectedText: 'All <?= TERM ?>',
                    nonSelectedText: '<?= TERM ?>',
                    numberDisplayed: 2,
                    nSelectedText: '<?= TERM ?> selected'
                });
                /* Ticket # 1341 */
            }

            if (report_type == 4) {
                /* Ticket # 1344 */
                $('#PK_TERM_MASTER_6').multiselect({
                    includeSelectAllOption: true,
                    allSelectedText: 'All <?= FIRST_TERM ?>',
                    nonSelectedText: '<?= FIRST_TERM ?>',
                    numberDisplayed: 2,
                    nSelectedText: '<?= FIRST_TERM ?> selected'
                });
                /* Ticket # 1344 */
            }

            if (report_type == 12) {
                /* Ticket # 1635 */
                $('#TERM_PK_CAMPUS').multiselect({
                    includeSelectAllOption: true,
                    allSelectedText: 'All <?= COURSE_CAMPUS ?>',
                    nonSelectedText: '<?= COURSE_CAMPUS ?>',
                    numberDisplayed: 2,
                    nSelectedText: '<?= COURSE_CAMPUS ?> selected'
                });

            }

            if (report_type == 12) {
                $('#COURSE_PK_TERM').multiselect({
                    includeSelectAllOption: true,
                    allSelectedText: 'All <?= COURSE_TERM ?>',
                    nonSelectedText: '<?= COURSE_TERM ?>',
                    numberDisplayed: 2,
                    nSelectedText: '<?= COURSE_TERM ?> selected'
                });
            }

            if (report_type == 12) {
                $('#COURSE_PK_COURSE').multiselect({
                    includeSelectAllOption: true,
                    allSelectedText: 'All <?= COURSE ?>',
                    nonSelectedText: '<?= COURSE ?>',
                    numberDisplayed: 2,
                    nSelectedText: '<?= COURSE ?> selected'
                });
            }

            if (report_type == 12) {
                $('#COURSE_PK_COURSE_OFFERING').multiselect({
                    includeSelectAllOption: true,
                    allSelectedText: 'All <?= COURSE_OFFERING ?>',
                    nonSelectedText: '<?= COURSE_OFFERING ?>',
                    numberDisplayed: 2,
                    nSelectedText: '<?= COURSE_OFFERING ?> selected'
                });
                /* Ticket # 1635 */
            }

            if (report_type == 3) {
                /* Ticket # 1343 */
                $('#PK_CAMPUS_2').multiselect({
                    includeSelectAllOption: true,
                    allSelectedText: 'All <?= CAMPUS ?>',
                    nonSelectedText: '<?= CAMPUS ?>',
                    numberDisplayed: 2,
                    nSelectedText: '<?= CAMPUS ?> selected'
                });
                /* Ticket # 1343 */
            }

            if (report_type == 11) {
                /* Ticket # 1342 */
                $('#PK_CAMPUS_3').multiselect({
                    includeSelectAllOption: true,
                    allSelectedText: 'All <?= CAMPUS ?>',
                    nonSelectedText: '<?= CAMPUS ?>',
                    numberDisplayed: 2,
                    nSelectedText: '<?= CAMPUS ?> selected'
                });
            }

            if (report_type == 11) {
                $('#PK_TERM_MASTER_4').multiselect({
                    includeSelectAllOption: true,
                    allSelectedText: 'All <?= TERM ?>',
                    nonSelectedText: '<?= TERM ?>',
                    numberDisplayed: 2,
                    nSelectedText: '<?= TERM ?> selected'
                });
                /* Ticket # 1342 */
            }

            if (report_type == 13 || report_type == 16 || report_type == 15 || report_type == 17 || report_type == 18) { //DIAM-2155
                $('#AR_PK_CAMPUS').multiselect({
                    includeSelectAllOption: true,
                    allSelectedText: 'All <?= CAMPUS ?>',
                    nonSelectedText: '<?= CAMPUS ?>',
                    numberDisplayed: 2,
                    nSelectedText: '<?= CAMPUS ?> selected',
                    onDropdownHide: function(event) {
                        // alert('hi')
                        get_term_date_for_campus()
                    }
                });
                if($('#AR_PK_CAMPUS > option').length == 1 ){
                    get_term_date_for_campus()
                }
            }
            //DIAM-1417
            if (report_type == 5) {
                /* Ticket # 1341 */
                $('#PK_CAMPUS_1').multiselect({
                    includeSelectAllOption: true,
                    allSelectedText: 'All Course <?= CAMPUS ?>',
                    nonSelectedText: 'Course <?= CAMPUS ?>',
                    numberDisplayed: 2,
                    nSelectedText: '<?= CAMPUS ?> selected',
                });

                $('#PK_TERM_MASTER_5').multiselect({
                    includeSelectAllOption: true,
                    allSelectedText: 'All <?= TERM ?>',
                    nonSelectedText: '<?= TERM ?>',
                    numberDisplayed: 2,
                    nSelectedText: '<?= TERM ?> selected'
                });

            }
            //DIAM-1417

            document.getElementById('loaders').style.display = 'none';
            //});

        }

        function get_course_details() {}
        //  30 may  2023
        function attendanceReportFilters(report_type) {

            if(report_type == 18) report_type =17; //DIAM-2155 // Same UI Used report 17
        
            //console.log(report_type);
            jQuery(document).ready(function($) {
                $("#student_div").empty();
                $('#student_div_2').hide();
                if (report_type == 12) {
                    $('#student_div_2').show();
                    grid = $('#student_div_2').w2grid({
                        name: 'grid',
                        header: 'List of Names',
                        //Improving W2UI GRID to retain selected option upon search ..  This custome property keeps a track of total seelctions and do not reset upon global search
                        av_totalselected : [],
                        show: {
                            selectColumn: true,
                            searchColumn: true,
                            lineNumbers: true,
                            toolbar: true,
                            footer: true,
                            searchSave: false,
                            toolbarColumns  : false,

                        },
                        "searchLogic": "OR",
                        defaultOperator: {
                            'text': 'contains'
                        },
                        lineNumberWidth: 65,
                        multiSelect: true,
                        recid: 'PK_STUDENT_ENROLLMENT',
                        // returnIndex: 'PK_STUDENT_ENROLLMENT',
                        columns: [{
                                field: 'PK_STUDENT_ENROLLMENT',
                                text: 'Enrollment ID',
                                size: '0px',
                                hidden: true
                            },
                            {
                                field: 'STU_NAME',
                                text: 'Student',
                                size: '22%',
                                type: "text"
                            },
                            {
                                field: 'STUDENT_ID',
                                text: 'Student ID',
                                size: '14%',
                                type: "text"
                            },
                            {
                                field: 'CAMPUS_CODE',
                                text: 'Campus',
                                size: '14%',
                                type: "text"
                            },
                            {
                                field: 'BEGIN_DATE_1',
                                text: 'First Term',
                                size: '12%',
                                type: "text"
                            },
                            {
                                field: 'CODE',
                                text: 'Program',
                                size: '12%',
                                type: "text"
                            },
                            {
                                field: 'STUDENT_STATUS',
                                text: 'Status',
                                size: '12%',
                                type: "text"
                            },
                            {
                                field: 'STUDENT_GROUP',
                                text: 'Student Group',
                                size: '14%',
                                type: "text"
                            }

                        ],
                        async onSelect(event) {
                            await event.complete
                            await event.done;
                            selected_grid_ids = this.getSelection(true);
                            // console.log('selection:', selected_grid_ids);
                            if (selected_grid_ids.length > 0) {
                                show_btn_w2ui(selected_grid_ids.length)
                            }
                        }
                    });
                }
            });

            document.getElementById('loaders').style.display = 'block';

            jQuery(document).ready(function($) {

                var data = 'REPORT_TYPE=' + report_type;
                var value = $.ajax({
                    url: "ajax_get_attendance_reports_filters",
                    type: "POST",
                    data: data,
                    async: false,
                    cache: false,
                    success: function(data) {
                        document.getElementById('attendance_report_type_filter').innerHTML = data;

                        //jQuery(document).ready(function($) {
                        $('.date').datepicker({
                            todayHighlight: true,
                            orientation: "bottom auto"
                        });

                        $('#term_begin_start_date').datepicker({
                            autoclose: true,
                            todayHighlight: true,
                            orientation: "bottom auto"
                        }).on('change', function(sdate) {
                            get_term_from_campus_by_date(report_type);
                        });

                        $('#term_begin_end_date').datepicker({
                            autoclose: true,
                            todayHighlight: true,
                            orientation: "bottom auto"
                        }).on('change', function(edate) {
                            get_term_from_campus_by_date(report_type);
                        });


                        $('#term_end_start_date').datepicker({
                            autoclose: true,
                            todayHighlight: true,
                            orientation: "bottom auto"
                        }).on('change', function(sdate) {
                            get_term_from_campus_by_date(report_type);
                        });

                        $('#term_end_end_date').datepicker({
                            autoclose: true,
                            todayHighlight: true,
                            orientation: "bottom auto"
                        }).on('change', function(edate) {
                            get_term_from_campus_by_date(report_type);
                        });

                        //    });

                        addMultiselect(report_type);
                        show_filters(report_type); //30 may 2023
                        if (report_type == 13 || report_type == 16 || report_type == 15) {
                            get_term_date_for_campus()
                        }
                        // if(report_type!==13){
                        // // get_term_from_campus();
                        // }
                        //document.getElementById('loaders').style.display = 'none';
                        if (document.getElementById('REPORT_TYPE').value == 17 || document.getElementById('REPORT_TYPE').value == 18) { //DIAM-2155
                            get_course_or_instructor();
                        }
                    },
                    complete: function() {
                        var changed_filters = [];
                        $('.attendance_report_type_filter').find('input,select')
                            .each(function() {
                                $(this).change(function() {
                                    if ($(this).attr('id') != undefined && !changed_filters.includes($(this).attr('id'))) {
                                        changed_filters.push($(this).attr('id'));
                                    }
                                    // $("#new_filters").text(changed_filters.length);
                                    $("#student_div").empty();
                                    if (report_type == 14) {
                                        if (document.getElementById('btn_1'))
                                            document.getElementById('btn_1').style.display = 'none'; //Ticket # 1247

                                        if (document.getElementById('btn_2'))
                                            document.getElementById('btn_2').style.display = 'none'; //Ticket # 1247
                                    }
                                    // console.log("Some input changed !!!");
                                })
                            });
                    }
                }).responseText;
            });

        }
        jQuery(document).ready(function($) {
            $(document).on('mouseenter', '.multiselect', function(event) {
                $(this).removeAttr('title');
            }).on('mouseleave', '.multiselect', function() {
                $(this).removeAttr('title');
            });
        });

        function get_term_from_campus_by_date(report_type) {

            jQuery(document).ready(function($) {

                if ($('#term_begin_start_date').val() === '' && $('#term_begin_end_date').val() === '') return false;

                var PK_CAMPUS = '';
                if (report_type == 1 || report_type == 5) { //<!--DIAM-1417-->
                    PK_CAMPUS = $('#PK_CAMPUS_1').val();
                } else if (report_type == 11) {
                    PK_CAMPUS = $('#PK_CAMPUS_3').val();
                } else if (report_type == 3) {
                    PK_CAMPUS = $('#PK_CAMPUS_2').val();
                } else if (report_type == 12) {
                    PK_CAMPUS = $('#PK_CAMPUS').val(); // DIAM-757
                }

                var data = 'report_type=' + report_type + '&PK_CAMPUS=' + PK_CAMPUS + '&TREM_BEGIN_START_DATE=' + $('#term_begin_start_date').val() + '&TREM_BEGIN_END_DATE=' + $('#term_begin_end_date').val() + '&TREM_END_START_DATE=' + $('#term_end_start_date').val() + '&TREM_END_END_DATE=' + $('#term_end_end_date').val();
                var value = $.ajax({
                    url: "ajax_get_attendance_term_from_campus_by_date",
                    type: "POST",
                    data: data,
                    async: false,
                    cache: false,
                    success: function(data) {

                        if (report_type == 1 || report_type == 5) //<!--DIAM-1417-->
                            document.getElementById('PK_TERM_MASTER_5_DIV').innerHTML = data;


                        if (report_type == 11)
                            document.getElementById('PK_TERM_MASTER_4_DIV').innerHTML = data;


                        if (report_type == 3)
                            document.getElementById('PK_TERM_MASTER_2_DIV').innerHTML = data;

                        if (report_type == 12) // DIAM-757
                            document.getElementById('COURSE_PK_TERM_DIV_HIDDEN').innerHTML = data; // DIAM-757

                        if (report_type == 12) { // DIAM-757
                            get_course_from_term();
                        } else { // DIAM-757
                            get_course_offering();
                        } // DIAM-757

                    }
                });

            });

        }

        function apply_student_search(x) {
            jQuery(document).ready(function($) {
                var $rows = $('#student_update_table tbody tr'); // DIAM-757
                var val = $.trim($(x).val()).replace(/ +/g, ' ').toLowerCase();
                $rows.show().filter(function() {
                    var text = $(this).find('td:nth-child(2),td:nth-child(3)').text().replace(/\s+/g, ' ').toLowerCase();
                    //console.log('row ->')
                    //console.log($(this).find('td:nth-child(2),td:nth-child(3)').text())
                    return !~text.indexOf(val);
                }).hide();
            })
        }

        //  30 may  2023

        //TERM DROPDOWN EXPERIMENTATION !!!
    </script>


    <script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>

    <link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
</body>

</html>
