<?php
// /D3/student/payment_info_cybersource.php
require_once("../global/config.php");
require_once("../language/common.php");

// Verificar acceso
$res_pay = $db->Execute("SELECT ENABLE_DIAMOND_PAY FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
if($res_pay->fields['ENABLE_DIAMOND_PAY'] != 3) { // 3 = Cybersource
    header("location:../index");
    exit;
}

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER_TYPE'] != 3){
    header("location:../index");
    exit;
}

// Manejar acciones
if($_GET['act'] == 'del_cc') {
    $cardId = $_GET['iid'] ?? 0;
    if($cardId) {
        $db->Execute("UPDATE S_STUDENT_CREDIT_CARD_CYBERSOURCE 
                     SET ACTIVE = 0,
                         EDITED_BY = '$_SESSION[PK_USER]',
                         EDITED_ON = NOW()
                     WHERE PK_STUDENT_CREDIT_CARD_CYBERSOURCE = '$cardId'
                     AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'
                     AND PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]'");
        
        header("location:payment_info_cybersource.php?id=".$_GET['id']."&page=".$_GET['page']);
        exit;
    }
}
elseif($_GET['act'] == 'pri') {
    // Desmarcar todas como primaria
    $db->Execute("UPDATE S_STUDENT_CREDIT_CARD_CYBERSOURCE 
                 SET IS_PRIMARY = 0 
                 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
                 AND PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]'");
    
    // Marcar la seleccionada como primaria
    $db->Execute("UPDATE S_STUDENT_CREDIT_CARD_CYBERSOURCE 
                 SET IS_PRIMARY = 1 
                 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
                 AND PK_STUDENT_CREDIT_CARD_CYBERSOURCE = '$_GET[iid]' 
                 AND PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]'");
    
    header("location:payment_info_cybersource.php?id=".$_GET['id']."&page=".$_GET['page']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Información de Tarjetas - Cybersource</title>
    <? require_once("css.php"); ?>
</head>
<body class="horizontal-nav boxed skin-megna fixed-layout">
    <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
        <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-10 align-self-center">
                        <h4 class="text-themecolor">Información de Tarjetas</h4>
                    </div>
                    <div class="col-md-2 text-right">
                        <a href="add_cc_cybersource" class="btn btn-info d-none d-lg-block m-l-15">
                            <i class="fa fa-plus-circle"></i> Agregar Nueva
                        </a>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <?php
                                    $res_cards = $db->Execute("SELECT * FROM S_STUDENT_CREDIT_CARD_CYBERSOURCE 
                                                             WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
                                                             AND PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' 
                                                             AND ACTIVE = 1
                                                             ORDER BY IS_PRIMARY DESC, PK_STUDENT_CREDIT_CARD_CYBERSOURCE DESC");
                                    
                                    if($res_cards->RecordCount() > 0) { ?>
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nombre en Tarjeta</th>
                                                <th>Número</th>
                                                <th>Expiración</th>
                                                <th>Tipo</th>
                                                <th>Primaria</th>
                                                <th>Tokenizada</th>
                                                <th>Opciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <? while (!$res_cards->EOF) {
                                                $isTokenized = !empty($res_cards->fields['CUSTOMER_ID']) &&
                                                              !empty($res_cards->fields['PAYMENT_INSTRUMENT_ID']);
                                            ?>
                                            <tr>
                                                <td><?=$res_cards->fields['NAME_ON_CARD']?></td>
                                                <td>•••• <?=$res_cards->fields['CARD_LAST_FOUR']?></td>
                                                <td><?=$res_cards->fields['EXPIRATION_MONTH']?>/<?=$res_cards->fields['EXPIRATION_YEAR']?></td>
                                                <td><?=$res_cards->fields['CARD_BRAND']?></td>
                                                <td>
                                                    <? if($res_cards->fields['IS_PRIMARY'] == 1) { ?>
                                                        <span class="badge badge-success">Sí</span>
                                                    <? } else { ?>
                                                        No<br />
                                                        <? if($isTokenized) { ?>
                                                        <a href="javascript:void(0);"
                                                           onclick="set_as_primary('<?=$res_cards->fields['PK_STUDENT_CREDIT_CARD_CYBERSOURCE']?>')"
                                                           title="Establecer como primaria">
                                                            Hacer primaria
                                                        </a>
                                                        <? } ?>
                                                    <? } ?>
                                                </td>
                                                <td>
                                                    <? if($isTokenized) { ?>
                                                        <span class="badge badge-success">Sí</span>
                                                    <? } else { ?>
                                                        <span class="badge badge-danger">No</span>
                                                    <? } ?>
                                                </td>
                                                <td>
                                                    <a href="javascript:void(0);"
                                                       onclick="delete_card_popup('<?=$res_cards->fields['PK_STUDENT_CREDIT_CARD_CYBERSOURCE']?>')"
                                                       title="Eliminar"
                                                       class="btn delete-color btn-circle">
                                                        <i class="far fa-trash-alt"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <? $res_cards->MoveNext();
                                            } ?>
                                        </tbody>
                                    </table>
                                    <? } else { ?>
                                    <div class="alert alert-info">
                                        No hay tarjetas guardadas.
                                        <a href="add_cc_cybersource">Agregar primera tarjeta</a>
                                    </div>
                                    <? } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal de confirmación para eliminar -->
        <div class="modal" id="deleteModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Confirmación</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>¿Está seguro de eliminar esta tarjeta?</p>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" id="DELETE_ID" value="">
                        <button type="button" onclick="conf_delete_card_popup(1)" class="btn btn-info">Sí</button>
                        <button type="button" onclick="conf_delete_card_popup(0)" class="btn btn-dark">No</button>
                    </div>
                </div>
            </div>
        </div>
        
        <? require_once("footer.php"); ?>
    </div>
    
    <? require_once("js.php"); ?>
    
    <script>
    function delete_card_popup(id) {
        $("#deleteModal").modal();
        $("#DELETE_ID").val(id);
    }
    
    function conf_delete_card_popup(val) {
        if(val == 1) {
            window.location.href = "payment_info_cybersource.php?id=<?=$_GET['id']?>&page=<?=$_GET['page']?>&act=del_cc&iid=" + $("#DELETE_ID").val();
        }
        $("#deleteModal").modal("hide");
    }
    
    function set_as_primary(id) {
        if(confirm('¿Establecer esta tarjeta como primaria para pagos automáticos?')) {
            window.location.href = "payment_info_cybersource.php?id=<?=$_GET['id']?>&page=<?=$_GET['page']?>&act=pri&iid=" + id;
        }
    }
    </script>
</body>
</html>

