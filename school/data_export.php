<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/earnings_setup.php");
require_once("../language/menu.php");
require_once("check_access.php");

// dvb 29 10 25
$allowed_accounts = [46, 63, 64, 72];
if(!in_array($_SESSION['PK_ACCOUNT'], $allowed_accounts)){
    header("location:../school");
}

if (check_access('MANAGEMENT_ACCOUNTING') == 0) {
    header("location:../index");
    exit;
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <? require_once("css.php"); ?>
    <title><?= DATA_EXPORT ?> | <?= $title ?></title>
    <link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
    <style>
        .datagrid-header td {
            font-size: 14px !important;
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
                    <div class="col-md-6 align-self-center">
                        <h4 class="text-themecolor"><?= DATA_EXPORT ?> </h4>
                    </div>
                    <div class="col-md-3 align-self-center text-right">
                        <input type="text" class="form-control" id="SEARCH" name="SEARCH" placeholder="&#xF002; <?= SEARCH ?>" style="font-family: FontAwesome" onkeypress="search(event)">
                    </div>

                    <div class="col-md-3 align-self-center text-right">

                        <div class="d-flex justify-content-end align-items-center">
                            <select name="EXPORT_TYPE" id="EXPORT_TYPE">
                                <option value="csv">CSV</option>
                                <!-- <option value="sql">sql(bak)</option> -->
                            </select>
                            <button class="btn btn-info d-none d-lg-block m-l-15" onclick="generate_export()"><?= EXPORT ?></button>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <table id="tt" striped="true" class="easyui-datagrid table table-bordered table-striped" url="grid_data_export" toolbar="#tb" pagination="true" pageSize=25>
                                            <thead>
                                                <tr>
                                                    <th field="SELECT" width="25px" sortable="false">
                                                        <input type="checkbox" name="select" id="select_all" onchange="selectall()">
                                                    </th>
                                                    <th field="EXPORT_NAME" align="left" sortable="true"><?= DATA_VIEW ?></th>
                                                    <th field="LAST_EXPORTED_ON" align="left" sortable="true"><?= LAST_EXPORTED_ON ?></th>
                                                    <th field="LAST_EXPORTED_BY" align="left" sortable="true"><?= LAST_EXPORTED_BY ?></th>

                                                    <!-- <th field="ACTION" width="100px" align="left" sortable="false" ><?= OPTION ?></th> -->
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <? require_once("footer.php"); ?>
        <div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="exampleModalLabel1"><?= DELETE_CONFIRMATION ?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <?= DELETE_MESSAGE_GENERAL ?>
                            <input type="hidden" id="DELETE_ID" value="0" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" onclick="conf_delete(1)" class="btn waves-effect waves-light btn-info"><?= YES ?></button>
                        <button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete(0)"><?= NO ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <? require_once("js.php"); ?>

    <script src="../backend_assets/dist/js/jquery-migrate-1.0.0.js"></script>
    <script type="text/javascript" src="../backend_assets/dist/js/jquery.easyui.min.js"></script>
    <script src="../backend_assets/dist/js/jquery-ui.js"></script>
    <script type="text/javascript">
        function doSearch() {
            jQuery(document).ready(function($) {
                $('#tt').datagrid('load', {
                    SEARCH: $('#SEARCH').val(),
                });
            });
        }

        function search(e) {
            if (e.keyCode == 13) {
                doSearch();
            }
        }
        $(function() {
            jQuery(document).ready(function($) {
                $('#tt').datagrid({
                    onClickCell: function(rowIndex, field, value) {
                        $('#tt').datagrid('selectRow', rowIndex);
                        if (field != 'ACTION') {
                            // var selected_row = $('#tt').datagrid('getSelected');
                            // window.location.href='earnings_setup?id='+selected_row.PK_EARNINGS_SETUP;
                        }
                    }
                });

                $('#tt').datagrid({
                    view: $.extend(true, {}, $.fn.datagrid.defaults.view, {
                        onAfterRender: function(target) {
                            $.fn.datagrid.defaults.view.onAfterRender.call(this, target);
                            $('.datagrid-header-inner').width('100%')
                            $('.datagrid-btable').width('100%')
                            $('.datagrid-body').css({
                                'overflow-y': 'hidden'
                            });
                        }
                    })
                });

            });
        });
        jQuery(document).ready(function($) {
            $(window).resize(function() {
                $('#tt').datagrid('resize');
                $('#tb').panel('resize');
            })



        });

        function generate_export(params) {

            var loader = document.getElementsByClassName("preloader")
            loader[0].style.display = "block";
            var checked = false;
            $("input[name='PK_DATA_EXPORT[]']:checked").each(function(index, obj) {

                if (checked) {
                    checked += "," + obj.value;
                } else {
                    checked = obj.value;
                }

            });
            if (checked != '') {

                set_notification = false;
                var value = $.ajax({
                    url: "ajax_generate_export",
                    type: "POST",
                    data: {
                        "export_id": checked,
                        "EXPORT_TYPE": $('#EXPORT_TYPE').val()
                    },
                    async: true,
                    cache: false,
                    timeout: 180000, // 3 minutos
                    success: function(data) {
                        //alert("We are generating selected exports. Download link will be available soon");
                        set_notification = true;
                        if (data.path) {
                            downloadFile(window.location.origin + '/' + data.path, data.name);

                        } else {
                            loader[0].style.display = "none";
                        }

                    }
                }).responseText;

            }
        }

        function downloadFile(urlToSend, filename) {
            var loader = document.getElementsByClassName("preloader");

            //alert(urlToSend);
            var req = new XMLHttpRequest();
            req.open("GET", urlToSend, true);
            req.responseType = "blob";
            req.onload = function(event) {
                if (this.status == 200) {
                    var blob = req.response;
                    var fileName = filename //if you have the fileName header available
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = fileName;
                    link.click();
                    loader[0].style.display = "none";
                } else {
                    //do nothing
                    loader[0].style.display = "none";
                }

            };

            req.send();
        }

        function selectall() {
            $flag = $('#select_all').is(":checked");
            if ($flag) {
                if (confirm("Are you sure you want to select all?") == true) {
                    $("input[name='PK_DATA_EXPORT[]']").not(this).prop('checked', $('#select_all').is(":checked"));
                } else {
                    $('#select_all').prop('checked', !$('#select_all').is(":checked"));
                }
            } else {
                if (confirm("Are you sure you want to unselect all?") == true) {
                    $("input[name='PK_DATA_EXPORT[]']").not(this).prop('checked', $('#select_all').is(":checked"));
                } else {
                    $('#select_all').prop('checked', !$('#select_all').is(":checked"));
                }
            }



        }
    </script>

</body>

</html>
