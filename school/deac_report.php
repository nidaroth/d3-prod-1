<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/deac.php");
require_once("check_access.php");
require_once('custom_excel_generator.php');
 

#EXCEL REPORTS 

if (isset($_POST['action']) && $_POST['action'] == 'download_excel') {
    $REPORT_OPTION = $_POST['REPORT_OPTION'];
    if($_POST['PK_CAMPUS'] == ''){

    $res_type_campus_2 = $db->Execute("select OFFICIAL_CAMPUS_NAME,PK_CAMPUS,CAMPUS_CODE,ACTIVE from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC,OFFICIAL_CAMPUS_NAME ASC");
    while (!$res_type_campus_2->EOF) {
        $PK_CAMPUS_ARR_TMP[] = $res_type_campus_2->fields['PK_CAMPUS'];
        $res_type_campus_2->MoveNext();
    }
    $PK_CAMPUS = implode(',',$PK_CAMPUS_ARR_TMP);
    }else{
        $PK_CAMPUS = implode(',',$_POST['PK_CAMPUS']);
    }
    
    $START_DATE = $_POST['START_DATE'];
    $END_DATE = $_POST['END_DATE'];
    $report_year = $_POST['report_year'];

    $REPORT_OPTION  = $_POST['REPORT_OPTION'];
    if ($REPORT_OPTION == '1') {
        $FILE_NAME = 'DEAC_2A_Enrollment.xlsx';
        $SP_CALL = "CALL DEAC10001(15,'$PK_CAMPUS','$report_year','2A Enrollment')";
        generate_excel_from_data($SP_CALL , $FILE_NAME);
    } 
    if ($REPORT_OPTION == '2') {
        $FILE_NAME = 'DEAC_2B_New_Student_Enrollment.xlsx';
        $SP_CALL = "call DEAC10002(15,'$PK_CAMPUS','$report_year','2B New Student Enrollment')";
        generate_excel_from_data($SP_CALL , $FILE_NAME);
    }
    if ($REPORT_OPTION == '3') {
        $FILE_NAME = 'DEAC_3A_Program_Totals.xlsx';
        $SP_CALL = "call DEAC10003(15,'$PK_CAMPUS','3a Program Totals')";
        generate_excel_from_data($SP_CALL , $FILE_NAME);
    }
    if ($REPORT_OPTION == '4') {
        $FILE_NAME = 'DEAC_3B_Program_Graduate_Outcomes.xlsx';
        $SP_CALL = "call DEAC10003(15,'$PK_CAMPUS','3b Program Graduate Outcomes')";
        generate_excel_from_data($SP_CALL , $FILE_NAME);
    }
    if ($REPORT_OPTION == '5') {
        $FILE_NAME = 'DEAC_3C_Program_Withdrawals.xlsx';
        $SP_CALL = "call DEAC10003(15,'$PK_CAMPUS','3c Program Withdrawals')";
        generate_excel_from_data($SP_CALL , $FILE_NAME);
    } 
    if ($REPORT_OPTION == '6') {
        $FILE_NAME = 'DEAC_4_Degree_Program_Graduation_Rate.xlsx';
        $SP_CALL = "call DEAC10004(15,'$PK_CAMPUS','4 Degree Program Grad Rate')";
        generate_excel_from_data($SP_CALL , $FILE_NAME);
    }  
    if ($REPORT_OPTION == '7') {
        $FILE_NAME = 'DEAC_5_Non_Degree_Program_Completion.xlsx'; 
        $SP_CALL = "call DEAC10005(15,'$PK_CAMPUS','5 NonDegree Program Completion')";
        generate_excel_from_data($SP_CALL , $FILE_NAME);
    }  
}

function generate_excel_from_data($SP_CALL, $FILE_NAME)
{
    header('Content-Type: application/json; charset=utf-8');

    
    global $db;
    $SP_DATA = $db->Execute($SP_CALL);
    if ($SP_DATA->RecordCount() > 0) {

        $data = [];
        $header =  array_keys($SP_DATA->fields);
        $data[] = ['*bold*' => $header];
        while (!$SP_DATA->EOF) {
            $data_row = [];
            # code...
            foreach ($SP_DATA->fields as $key => $value) {
                if($value == '2222-02-22'){
                    $value = '';
                }
                $data_row[] = $value;
            }
            $data[] = $data_row;
            $SP_DATA->MoveNext();
        }
        // dd($data); 
        $outputFileName = $FILE_NAME;
        $outputFileName = str_replace(
            pathinfo($outputFileName, PATHINFO_FILENAME),
            pathinfo($outputFileName, PATHINFO_FILENAME) . "_" . $_SESSION['PK_USER'] . "_" . floor(microtime(true) * 1000),
            $outputFileName
        );
        $output = CustomExcelGenerator::makecustom('Excel2007', 'temp/', $outputFileName, $data);
        $response["file_name"] = $outputFileName;
        $response["path"] =  $output;
        $data['query_call'] = $SP_CALL;
        echo json_encode($response);
        exit;
    } else {
        $data['error'] = 'Something went wrong. No data found ! Try again or check report setup';
        $data['query_call'] = $SP_CALL;
        echo json_encode($data);
        exit;
    }
}


















// Edited By 
$ress = $db->Execute("SELECT EDITED_ON, EDITED_BY FROM DEAC_SETUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if ($ress->RecordCount() == 0) {
    // header("location:deac_report_setup");
    // exit;
}
$EDITED_ON_1        = '';
if ($ress->fields['EDITED_ON'] == '0000-00-00 00:00:00') {
    $EDITED_ON_1    = '';
} else {
    $EDITED_ON_1    = date("m/d/Y", strtotime($ress->fields['EDITED_ON']));
}
$EDITED_ON            = $ress->fields['EDITED_BY'];

//$EDITED_ON    	    = date("m/d/Y");

$res_usr_name = $db->Execute("SELECT FIRST_NAME,LAST_NAME FROM S_EMPLOYEE_MASTER,Z_USER WHERE Z_USER.PK_USER = '$EDITED_ON' AND Z_USER.ID = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER");
$Edited_Name_1 = "";
if ($res_usr_name->RecordCount() == 1) {
    $Edited_Name_1 = $res_usr_name->fields['LAST_NAME'] . ', ' . $res_usr_name->fields['FIRST_NAME'];
}
// End - Edited By 

// Campus 
$res_type_campus = $db->Execute("select OFFICIAL_CAMPUS_NAME,PK_CAMPUS,CAMPUS_CODE,ACTIVE from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC,OFFICIAL_CAMPUS_NAME ASC");
// End Campus
if (check_access('MANAGEMENT_ACCREDITATION') == 0) {
    header("location:../index");
    exit;
}

// $res = $db->Execute("SELECT ACICS FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
// if($res->fields['ACICS'] == 0 || $res->fields['ACICS'] == '') {
// 	header("location:../index");
// 	exit;
// }
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
    <title><?= MNU_DEAC ?> | <?= $title ?></title>
    <style>
        li>a>label {
            position: unset !important;
        }

        #advice-required-entry-PK_CAMPUS {
            position: absolute;
            top: 57px;
            width: 150px
        }

        .title-adjustment {
            padding-bottom: 12px;
            padding-top: 15px;
        }

        .adjust-sub-menu {
            padding-left: 10px;
            padding-top: 2px;
        }

        .button-adjustment {
            text-align: right;
        }

        .edited-by {
            font-weight: 500;
            padding-top: 7px;
        }
    </style>
    <style>
        li>a>label {
            position: unset !important;
        }

        .option_red>a>label {
            color: red !important
        }

        .custom_lable_av {
            color: #0e79e5;
            position: absolute;
            cursor: auto;
            top: 5px;
            transition: 0.2s ease all;
            -moz-transition: 0.2s ease all;
            -webkit-transition: 0.2s ease all;
        }
    </style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
    <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
        <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">
                            <?= MNU_DEAC ?>
                        </h4>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off">

                                    <div class="row">
                                        <div class="col-md-6"></div>
                                        <div class="col-md-6">
                                            <div class="button-adjustment">
                                                <button type="button" onclick="window.location.href='deac_report_setup'" class="btn waves-effect waves-light btn-info">Report Setup</button>
                                                <div class="edited-by">Edited :
                                                    <?
                                                    if ($EDITED_ON_1 != '') {
                                                        echo $Edited_Name_1 . '  ' . $EDITED_ON_1;
                                                    } else {
                                                        echo 'N/A';
                                                    } ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">

                                        <div class="col-md-3 ">
                                            <div id="report_option_div">
                                                <b><?= REPORT_OPTION ?></b>
                                                <select id="REPORT_OPTION" name="REPORT_OPTION" class="form-control" onchange="set_report_options()">
                                                    <option value="1">2a. Enrollment</option>
                                                    <option value="2">2b. New Student Enrollment</option>
                                                    <option value="3">3a. Program Totals</option>
                                                    <option value="4">3b. Program Graduate Outcomes</option>
                                                    <option value="5">3c. Program Withdrawals</option>
                                                    <option value="6">4. Degree Program Grad Rate</option>
                                                    <option value="7">5. Non Degree Program Completion</option>
                                                    <option value="8">7. State Authorization Distance Education</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3 PK_CAMPUS_DIV">
                                            <?= CAMPUS ?>
                                            <select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control required-entry">
                                                <?
                                                while (!$res_type_campus->EOF) {
                                                    $option_label = '';
                                                    $option_class = '';
                                                    if ($res_type_campus->fields['ACTIVE'] == 0) {
                                                        $option_label .= " (Inactive)";
                                                        $option_class .= " class='option_red' ";
                                                    }
                                                ?>

                                                    <option value="<?= $res_type_campus->fields['PK_CAMPUS'] ?>" <? echo $option_class ?>><?= $res_type_campus->fields['CAMPUS_CODE'] . $option_label ?></option>
                                                <? $res_type_campus->MoveNext();
                                                } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2 report_dates">
                                            <?= START_DATE ?>
                                            <input type="text" class="form-control date required-entry" id="START_DATE" name="START_DATE" value="">
                                        </div>
                                        <div class="col-md-2 report_dates">
                                            <?= END_DATE ?>
                                            <input type="text" class="form-control date required-entry" id="END_DATE" name="END_DATE" value="">
                                        </div>
                                        <div class="col-md-2 report_years">
                                            <b>Year</b>
                                            <select id="report_year" name="report_year" class="form-control">
                                                <option value="2023">2023</option>
                                                <option value="2022">2022</option>
                                                <option value="2021">2021</option>

                                            </select>
                                        </div>
                                        <!-- <div class="col-md-2 align-self-center" id="GROUP_PROGRAM_CODE_DIV">
                                                <div class="col-12 col-sm-12 custom-control custom-checkbox form-group">
                                                    <input type="checkbox" class="custom-control-input" id="GROUP_PROGRAM_CODE" name="GROUP_PROGRAM_CODE" value="1">
                                                    <label class="custom-control-label" for="GROUP_PROGRAM_CODE"><? //= GROUP_PROGRAM_CODE 
                                                                                                                    ?></label>
                                                </div>
                                            </div> -->
                                        <div class="col-md-1 ">
                                            <br />
                                            <button type="button" onclick="export_excel()" id="btn_2" class="btn waves-effect waves-light btn-info"><?= EXCEL ?></button>
                                            <input type="hidden" name="FORMAT" id="FORMAT">
                                        </div>
                                    </div>
                                    <br /><br /><br />
                                    <!-- <div class="row"></div> -->
                                </form>
                                <br /><br />
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

    <script src="../backend_assets/dist/js/jquery-migrate-1.0.0.js"></script>
    <script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            jQuery('.date').datepicker({
                todayHighlight: true,
                orientation: "bottom auto"
            });
        });

        function submit_form() {
            jQuery(document).ready(function($) {
                var valid = new Validation('form1', {
                    onSubmit: false
                });
                var result = valid.validate();
                if (result == true) {
                    document.form1.submit();
                }
            });
        }
    </script>

    <script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
    <link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css" />
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            hide_dates();
            $('#PK_CAMPUS').multiselect({
                includeSelectAllOption: true,
                allSelectedText: 'All <?= CAMPUS ?>',
                nonSelectedText: '',
                numberDisplayed: 1,
                nSelectedText: '<?= CAMPUS ?> selected'
            });
        });

        function set_report_options() {
            jQuery(document).ready(function($) {
                var REPORT_OPTION = $('#REPORT_OPTION').val();
                if (REPORT_OPTION == 1 || REPORT_OPTION == 2) {
                    hide_dates();
                } else  {
                    hide_all()
                }

                if(REPORT_OPTION == 8){
                    show_campus_drop(0)
                }else{
                    show_campus_drop(1)
                }
            })
        }

        function hide_dates() {
            jQuery(document).ready(function($) {
                $('.report_dates').hide();
                $('.report_years').show();
            })
        }

        function hide_years() {
            jQuery(document).ready(function($) {
                $('.report_dates').show();
                $('.report_years').hide();
            })
        }
        function hide_all() {
            jQuery(document).ready(function($) {
                $('.report_dates').hide();
                $('.report_years').hide();
            })
        }
        function show_campus_drop(flag) {

           
            jQuery(document).ready(function($) {
                if(flag == 0){
                    $('.PK_CAMPUS_DIV').hide();
                }else{
                    $('.PK_CAMPUS_DIV').show();
                }
            })
        }
        function export_excel() {

            var REPORT_OPTION_HERE  = document.getElementById("REPORT_OPTION").value; 
            if(REPORT_OPTION_HERE != 8){
                jQuery(document).ready(function($) {
                //Generate pdf
                var value = $.ajax({
                    url: 'deac_report.php',
                    type: "POST",
                    data: {
                        REPORT_OPTION: $('#REPORT_OPTION').val(),
                        PK_CAMPUS: $('#PK_CAMPUS').val(),
                        START_DATE: $('#START_DATE').val(),
                        END_DATE: $('#END_DATE').val(),
                        report_year: $('#report_year').val(),
                        action : 'download_excel'
                    },
                    async: true,
                    cache: false,
                    beforeSend: function() {
                        // document.getElementById('loaders').style.display = 'block';
                    },
                    success: function(data, textStatus, xhr) {
                        // document.getElementById('loaders').style.display = 'none';
                        // console.log(data, textStatus, xhr, xhr.status);
                        if (data.error == "Something went wrong. No data found ! Try again or check report setup") {
                        	alert("Something went wrong. No data found ! Try again or check report setup");
                        	return;
                        }

                        const text = window.location.href;
                        const word = '/school';
                        const textArray = text.split(word); // ['This is ', ' text...']
                        const result = textArray.shift();
                        console.log(data, data.file_name, result + '/school/' + data.path);
                        downloadDataUrlFromJavascript(data.file_name, result + '/school/' + data.path)
                        // alert(result + '/school/' + data.path); 

                    },
                    error: function() {
                        // document.getElementById('loaders').style.display = 'none';
                        // alert("Something went wrong , Check your IPEDS setup and try again");
                    },
                    complete: function() {
                        // document.getElementById('loaders').style.display = 'none';
                        // document.getElementById('loaders').style.display = 'none';

                    }
                });
            });
            }else{
                nc_sara_state_status_export_excel()
            }
            
        }

        function nc_sara_state_status_export_excel() {
						jQuery(document).ready(function($) {
							//Generate pdf
							var value = $.ajax({
								url: 'nc_sara_state_excel.php',
								type: "POST",
								async: true,
								cache: false,
								beforeSend: function() {
									// document.getElementById('loaders').style.display = 'block';
								},
								success: function(data, textStatus, xhr) {
									// document.getElementById('loaders').style.display = 'none';
									// console.log(data, textStatus, xhr, xhr.status);
                                    if (data.error == "Something went wrong. No data found ! Try again or check report setup") {
                                        alert("Something went wrong. No data found ! Try again or check report setup");
                                        return;
                                    }

									const text = window.location.href;
									const word = '/school';
									const textArray = text.split(word); // ['This is ', ' text...']
									const result = textArray.shift();
									console.log(data, data.file_name, result + '/school/' + data.path);
									downloadDataUrlFromJavascript(data.file_name, result + '/school/' + data.path)
									// alert(result + '/school/' + data.path); 

								},
								error: function() {
									// document.getElementById('loaders').style.display = 'none';
									// alert("Something went wrong , Check your IPEDS setup and try again");
								},
								complete: function() {
									// document.getElementById('loaders').style.display = 'none';
									// document.getElementById('loaders').style.display = 'none';

								}
							});
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

</body>

</html>