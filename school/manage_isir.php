<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/isir.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_FINANCE') == 0 ){
    header("location:../index");
    exit;
}
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
    <title><?=ISIR_PAGE_TITLE?> | <?=$title?></title>
    <link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
    
    <style>
    /* Asegurar que el modal sea suficientemente grande */
    #studentModal .modal-dialog {
        max-width: 90%;
        width: 1200px;
    }

    /* Ajustar el z-index del modal para que esté sobre otros elementos */
    #studentModal {
        z-index: 9999 !important;
    }

    /* Estilos para DataTables en el modal */
    #studentModal .dataTables_wrapper {
        margin-top: 20px;
    }

    #studentModal .dataTables_filter {
        display: none; /* Ocultamos el filtro de DataTables ya que usamos nuestro propio campo de búsqueda */
    }

    #studentModal .table-responsive {
        border: none;
    }

    /* Asegurar que los botones se vean bien */
    .btn-link-student {
        padding: 2px 8px;
        font-size: 12px;
    }

    /* Loading overlay para el modal */
    .modal-loading {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }
    </style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles d-flex align-items-center">
                    <div class="col-md-1 align-self-center">
                        <h4 class="text-themecolor"><?=ISIR_PAGE_TITLE?> </h4>
                    </div>

                    <!-- DIAM-2228 -->
                    <div class="col-md-2 align-self-center" style="max-width: 13.999%;"  >
                       <select id="PK_AWARD_YEAR" name="PK_AWARD_YEAR" class="form-control " onchange="doSearch();" >
                            <option value="">Award Year</option>
                            <? $res_type = $db->Execute("SELECT PK_ISIR_SETUP_MASTER AS PK_AWARD_YEAR,SUBSTRING(Z_ISIR_SETUP_MASTER.FROM_NAME,1,9) AS AWARD_YEAR from Z_ISIR_SETUP_MASTER WHERE ACTIVE = 1 order by YEAR_INDICATION DESC");
                                while (!$res_type->EOF) { ?>
                                    <option value="<?=$res_type->fields['PK_AWARD_YEAR']?>" ><?=$res_type->fields['AWARD_YEAR']?></option>
                                <?    $res_type->MoveNext();
                                } ?>
                        </select>
                    </div>

                    <div class="col-md-2 align-self-center" style="max-width: 13.999%;" >
                        <select id="PK_CAMPUS" name="PK_CAMPUS" class="form-control" onchange="doSearch()" >
                            <option value=""><?=CAMPUS?></option>
                            <? $res_type = $db->Execute("select PK_CAMPUS,CAMPUS_CODE, ACTIVE from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, CAMPUS_CODE ASC");
                            while (!$res_type->EOF) {
                                $option_label = $res_type->fields['CAMPUS_CODE'];
                                if($res_type->fields['ACTIVE'] == 0)
                                    $option_label .= " (Inactive)"; ?>
                                <option value="<?=$res_type->fields['PK_CAMPUS'] ?>" <? if($_SESSION['SRC_PK_CAMPUS'] == $res_type->fields['PK_CAMPUS']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
                            <?    $res_type->MoveNext();
                            } ?>
                        </select>
                    </div>

                    <div class="col-md-2 align-self-center" style="max-width: 13.999%;margin-bottom: 10px;"  >
                        <input type="text" name="IMPORT_START_DATE" id="IMPORT_START_DATE" placeholder="Import Start Date" class="form-control date" onchange="doSearch();" >
                    </div>

                    <div class="col-md-2 align-self-center" style="max-width: 13.999%;margin-bottom: 10px;"  >
                        <input type="text" name="IMPORT_END_DATE" id="IMPORT_END_DATE" placeholder="Import End Date" class="form-control date" onchange="doSearch();" >
                    </div>
                    <!-- End DIAM-2228 -->

                    <div class="col-md-4 align-self-center text-right d-flex align-items-center" style="margin-bottom: 10px">
                        <div>
                            <input type="text" class="form-control" id="SEARCH" name="SEARCH" placeholder="&#xF002; <?=SEARCH?>"  style="font-family: FontAwesome" onkeypress="search(event)">
                        </div>
                        <!-- dvb 18 11 2024 -->
                        <a class="btn btn-info d-none d-lg-block m-l-15" href="nubo_isir_student_background"><i class="fas fa-upload"></i> <?=UPLOAD?></a>
                        <a class="btn btn-default d-none d-lg-block m-l-15" href="manage_isir_background"><i class="fas fa-upload"></i> Background Process</a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <table id="tt" striped = "true" class="easyui-datagrid table table-bordered table-striped"
                                        url="grid_isir?id=<?=$_GET['id']?>" toolbar="#tb" loadMsg="Processing, please wait..." pagination="true" pageSize = 25
                                        data-options="
                                        onClickCell: function(rowIndex, field, value){
                                            $('#tt').datagrid('selectRow',rowIndex);
                                            if(field != 'ACTION' ){
                                                var selected_row = $('#tt').datagrid('getSelected');
                                                window.location.href='isir?id='+selected_row.PK_ISIR_STUDENT_MASTER+'&iid='+selected_row.PK_ISIR_SETUP_MASTER+'&sid=<?=$_GET['id']?>';
                                            }
                                        },
                                        view: $.extend(true,{},$.fn.datagrid.defaults.view,{
                                            onAfterRender: function(target){
                                                $.fn.datagrid.defaults.view.onAfterRender.call(this,target);
                                                $('.datagrid-header-inner').width('100%') 
                                                $('.datagrid-btable').width('100%') 
                                                $('.datagrid-body').css({'overflow-y': 'hidden'});
                                            }
                                        })
                                        ">
                                            <thead>
                                                <tr>
                                                    <th field="PK_ISIR_STUDENT_MASTER" width="150px" hidden="true" sortable="true" ></th>
                                                    <th field="PK_ISIR_SETUP_MASTER" width="150px" hidden="true" sortable="true" ></th>
                                                    
                                                    <th field="FILE_NAME" width="250px" align="left" sortable="true" ><?=FILE_NAME?></th>
                                                    <th field="AWARD_YEAR" width="100px" align="left" sortable="true" >Award Year</th>
                                                    <th field="ISIR_TRANS_NO" width="120px" align="left" sortable="true" >ISIR Trans No</th>
                                                    <th field="STUDENT_ID" width="100px" align="left" sortable="true" >Student ID</th>
                                                    <th field="CAMPUS_CODE" width="100px" align="left" sortable="true" >Campus</th>
                                                    <th field="FIRST_NAME" width="150px" align="left" sortable="true" ><?=FIRST_NAME?></th>
                                                    <th field="LAST_NAME" width="150px" align="left" sortable="true" ><?=LAST_NAME?></th>
                                                    <th field="EMAIL" width="250px" align="left" sortable="true" ><?=EMAIL ?></th>
                                                    <th field="ACTION" width="100px" align="left" sortable="false" ><?=OPTIONS?></th>
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
    </div>

    <!-- MODAL PARA ENLAZAR ESTUDIANTE -->
    <div class="modal fade" id="studentModal" tabindex="-1" role="dialog" aria-labelledby="studentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="studentModalLabel">
                        <i class="fas fa-link"></i> Link Student to ISIR
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="position: relative;">
                    <div class="modal-loading" id="modalLoading" style="display: none;">
                        <div class="text-center">
                            <i class="fas fa-spinner fa-spin fa-2x"></i>
                            <div class="mt-2">Loading students...</div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <strong>ISIR ID:</strong> <span id="selectedStudentId" class="badge badge-primary"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" class="form-control" id="studentSearch" placeholder="Search by First Name or Last Name" minlength="2">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button" onclick="searchStudents()" id="searchBtn">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted">Enter at least 2 characters to search</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="studentsTable" class="table table-striped table-bordered nowrap" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Student ID</th>
                                            <th>Full Name</th>
                                            <th>First Name</th>
                                            <th>Last Name</th>
                                            <th>SSN</th>
                                            <th>Date of Birth</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- DataTables will populate this -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <? require_once("js.php"); ?>
    
    <script src="../backend_assets/dist/js/jquery-migrate-1.0.0.js"></script>
    <script type="text/javascript" src="../backend_assets/dist/js/jquery.easyui.min.js"></script>
    <script src="../backend_assets/dist/js/jquery-ui.js"></script>
    <script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
    
    <!-- DataTables JS -->
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>
    
    <script type="text/javascript">
    var searchInProgress = false;
    var studentsDataTable;
    var currentIsirId;

    jQuery(document).ready(function($) {
        jQuery('.date').datepicker({
            todayHighlight: true,
            orientation: "bottom auto"
        });

        // Configurar búsqueda al presionar Enter
        $('#studentSearch').keypress(function(e) {
            if(e.which == 13) {
                searchStudents();
            }
        });

        // Validar longitud mínima en tiempo real
        $('#studentSearch').on('input', function() {
            var searchTerm = $(this).val().trim();
            if(searchTerm.length >= 2) {
                $('#searchBtn').prop('disabled', false);
            } else {
                $('#searchBtn').prop('disabled', true);
            }
        });
        
        // Limpiar cuando se cierra el modal
        $('#studentModal').on('hidden.bs.modal', function () {
            if (studentsDataTable) {
                studentsDataTable.destroy();
                studentsDataTable = null;
            }
            $('#studentSearch').val('');
            $('#searchBtn').prop('disabled', true);
            $('#modalLoading').hide();
            searchInProgress = false;
        });

        // Inicializar DataTable vacía al abrir el modal
        $('#studentModal').on('shown.bs.modal', function () {
            if (!studentsDataTable) {
                initializeStudentsTable();
            }
        });
    });

    function doSearch(){
        jQuery(document).ready(function($) {
            $('#tt').datagrid('load',{
                SEARCH  : $('#SEARCH').val(),
                PK_AWARD_YEAR           : $('#PK_AWARD_YEAR').val(),
                PK_CAMPUS                  : $('#PK_CAMPUS').val(),
                IMPORT_START_DATE          : $('#IMPORT_START_DATE').val(),
                IMPORT_END_DATE          : $('#IMPORT_END_DATE').val()
            });
        });
    }

    function search(e){
        if (e.keyCode == 13) {
            doSearch();
        }
    }

    // FUNCIÓN PARA ABRIR EL MODAL
    function openStudentModal(pk_isir_student_master) {
        console.log('Opening student modal for ISIR ID:', pk_isir_student_master);
        currentIsirId = pk_isir_student_master;
        $('#selectedStudentId').text(pk_isir_student_master);
        $('#studentSearch').val('');
        $('#searchBtn').prop('disabled', true);
        
        // Mostrar el modal
        $('#studentModal').modal('show');
    }

    // FUNCIÓN PARA INICIALIZAR LA TABLA DE ESTUDIANTES
    function initializeStudentsTable() {
        if (studentsDataTable) {
            studentsDataTable.destroy();
        }

        studentsDataTable = $('#studentsTable').DataTable({
            "processing": true,
            "serverSide": false,
            "responsive": true,
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            "language": {
                "emptyTable": "No students found. Use the search box above to find students.",
                "info": "Showing _START_ to _END_ of _TOTAL_ students",
                "infoEmpty": "No students to show",
                "infoFiltered": "(filtered from _MAX_ total students)",
                "lengthMenu": "Show _MENU_ students per page",
                "loadingRecords": "Loading students...",
                "processing": "Processing...",
                "search": "Search:",
                "zeroRecords": "No matching students found"
            },
            "columns": [
                { "data": "STUDENT_ID", "width": "12%" },
                { "data": "FULL_NAME", "width": "20%" },
                { "data": "FIRST_NAME", "width": "15%" },
                { "data": "LAST_NAME", "width": "15%" },
                { "data": "SSN_DISPLAY", "width": "12%" },
                { "data": "DOB_DISPLAY", "width": "12%" },
                {
                    "data": null,
                    "width": "14%",
                    "orderable": false,
                    "render": function(data, type, row) {
                        return '<button type="button" class="btn btn-success btn-sm btn-link-student" onclick="linkStudent(' + row.PK_STUDENT_MASTER + ')" title="Link Student"><i class="fas fa-link"></i> Link</button>';
                    }
                }
            ],
            "data": [], // Inicializar vacía
            "order": [[1, 'asc']], // Ordenar por nombre completo
            "dom": 'lrtip' // Ocultar el campo de búsqueda de DataTables (usamos el nuestro)
        });
    }

    // FUNCIÓN PARA BUSCAR ESTUDIANTES
    function searchStudents() {
        if(searchInProgress) {
            console.log('Search already in progress, skipping...');
            return;
        }
        
        var searchTerm = $('#studentSearch').val().trim();
        console.log('Searching for:', searchTerm);
        
        if(searchTerm.length < 2) {
            alert('Please enter at least 2 characters to search');
            return;
        }
        
        searchInProgress = true;
        $('#modalLoading').show();
        $('#searchBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Searching...');
        
        $.ajax({
            url: 'search_students.php',
            type: 'POST',
            data: {
                SEARCH: searchTerm
            },
            dataType: 'json',
            success: function(response) {
                console.log('Search response:', response);
                
                if(response.rows && Array.isArray(response.rows)) {
                    // Limpiar y recargar datos en DataTable
                    studentsDataTable.clear();
                    studentsDataTable.rows.add(response.rows);
                    studentsDataTable.draw();
                    
                    if(response.rows.length === 0) {
                        console.log('No students found for search term:', searchTerm);
                    } else {
                        console.log('Found', response.rows.length, 'students');
                    }
                } else {
                    console.error('Invalid response format:', response);
                    alert('Invalid response format from server');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
                console.error('Response text:', xhr.responseText);
                alert('An error occurred while searching: ' + error);
            },
            complete: function() {
                searchInProgress = false;
                $('#modalLoading').hide();
                $('#searchBtn').prop('disabled', false).html('<i class="fas fa-search"></i> Search');
            }
        });
    }

    // FUNCIÓN PARA ENLAZAR ESTUDIANTE
    function linkStudent(pk_student_master) {
        var pk_isir_student_master = currentIsirId;
        
        console.log('Linking student:', pk_student_master, 'to ISIR:', pk_isir_student_master);
        
        if(confirm('Are you sure you want to link this student to the ISIR record?')) {
            $('#modalLoading').show();
            
            $.ajax({
                url: 'link_student.php',
                type: 'POST',
                data: {
                    pk_isir_student_master: pk_isir_student_master,
                    pk_student_master: pk_student_master
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Link response:', response);
                    
                    if(response.success) {
                        alert('Student linked successfully!');
                        $('#studentModal').modal('hide');
                        doSearch(); // Refrescar la tabla principal
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                    console.error('Response text:', xhr.responseText);
                    alert('An error occurred while linking the student: ' + error);
                },
                complete: function() {
                    $('#modalLoading').hide();
                }
            });
        }
    }

    $(function(){
        jQuery(document).ready(function($) {
            // El DataGrid principal ya está inicializado con la clase easyui-datagrid
        });
    });

    jQuery(document).ready(function($) {
        $(window).resize(function() {
            $('#tt').datagrid('resize');
            if (studentsDataTable) {
                studentsDataTable.columns.adjust().responsive.recalc();
            }
            $('#tb').panel('resize');
        })
    });
    </script>

</body>

</html>
