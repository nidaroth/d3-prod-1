<?php
// /D3/school/card_info_cybersource.php
require_once("../global/config.php");
require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0) {
    header("location:../index");
    exit;
}

$PK_STUDENT_MASTER = $_GET['s_id'] ?? 0;
$PK_STUDENT_ENROLLMENT = $_GET['eid'] ?? 0;

// Verificar que Cybersource esté habilitado
$res_pay = $db->Execute("SELECT ENABLE_DIAMOND_PAY FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
if($res_pay->fields['ENABLE_DIAMOND_PAY'] != 3) {
    die("Cybersource no está habilitado para esta cuenta");
}

// Obtener información del estudiante
$res_student = $db->Execute("SELECT * FROM S_STUDENT_MASTER 
                            WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' 
                            AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");

if($res_student->RecordCount() == 0) {
    die("Estudiante no encontrado");
}

$STUDENT_NAME = $res_student->fields['FIRST_NAME'] . ' ' . $res_student->fields['LAST_NAME'];

// Obtener dirección del estudiante
$res_address = $db->Execute("SELECT * FROM S_STUDENT_ADDRESS 
                            WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' 
                            AND PK_ADDRESS_TYPE = 1 
                            LIMIT 1");

$DEFAULT_ADDRESS = '';
$DEFAULT_CITY = '';
$DEFAULT_STATE = '';
$DEFAULT_ZIP = '';
$DEFAULT_EMAIL = $_SESSION['EMAIL'] ?? '';
$DEFAULT_PHONE = '';

if($res_address && $res_address->RecordCount() > 0) {
    $DEFAULT_ADDRESS = $res_address->fields['ADDRESS_1'];
    $DEFAULT_CITY = $res_address->fields['CITY'];
    $DEFAULT_STATE = $res_address->fields['STATE'];
    $DEFAULT_ZIP = $res_address->fields['ZIP'];
    $DEFAULT_PHONE = $res_address->fields['PHONE'];
}

// Obtener tarjetas existentes
$res_cards = $db->Execute("SELECT * FROM S_STUDENT_CREDIT_CARD_CYBERSOURCE 
                          WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
                          AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' 
                          AND ACTIVE = 1
                          ORDER BY IS_PRIMARY DESC, PK_STUDENT_CREDIT_CARD_CYBERSOURCE DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administrar Tarjetas - <?=$STUDENT_NAME?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Cambiar a producción: https://flex.cybersource.com/microform/bundle/v2/flex-microform.min.js -->
    <script src="https://testflex.cybersource.com/microform/bundle/v2/flex-microform.min.js"></script>
    <? require_once("css.php"); ?>
    <style>
        .card-container { max-width: 900px; margin: 20px auto; }
        .microform-field {
            height: 48px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 0 12px;
            display: flex;
            align-items: center;
            transition: border-color .15s, box-shadow .15s;
            background: white;
        }
        .microform-field:focus-within {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13,110,253,.15);
        }
        .saved-card {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
        }
        .saved-card.primary {
            border-color: #0d6efd;
            background: #f0f8ff;
        }
        .card-info { flex-grow: 1; }
        .card-actions { display: flex; gap: 10px; }
        #card_number, #card_cvv { height: 48px; }
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(0,0,0,.1);
            border-radius: 50%;
            border-top-color: #0d6efd;
            animation: spin 1s ease-in-out infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
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
                        <h4 class="text-themecolor">Administrar Tarjetas - <?=$STUDENT_NAME?></h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <button type="button" class="btn btn-dark"
                                onclick="window.location.href='student?t=<?=$_GET['t']?>&eid=<?=$PK_STUDENT_ENROLLMENT?>&id=<?=$PK_STUDENT_MASTER?>&tab=disbursementTab'">
                            <i class="fa fa-arrow-left"></i> Regresar
                        </button>
                    </div>
                </div>

                <!-- Tarjetas guardadas -->
                <? if($res_cards->RecordCount() > 0) { ?>
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Tarjetas Guardadas</h4>
                        <div id="saved-cards-list">
                            <? while(!$res_cards->EOF) {
                                $isTokenized = !empty($res_cards->fields['CUSTOMER_ID']) &&
                                              !empty($res_cards->fields['PAYMENT_INSTRUMENT_ID']);
                            ?>
                            <div class="saved-card <?=$res_cards->fields['IS_PRIMARY'] ? 'primary' : ''?>"
                                 id="card_<?=$res_cards->fields['PK_STUDENT_CREDIT_CARD_CYBERSOURCE']?>">
                                <div class="card-info">
                                    <strong>
                                        <i class="fa fa-credit-card"></i>
                                        •••• <?=$res_cards->fields['CARD_LAST_FOUR']?>
                                    </strong>
                                    - <?=$res_cards->fields['NAME_ON_CARD']?>
                                    (<?=$res_cards->fields['CARD_BRAND']?>)
                                    <br>
                                    <small>Exp: <?=$res_cards->fields['EXPIRATION_MONTH']?>/<?=$res_cards->fields['EXPIRATION_YEAR']?></small>
                                    <? if($res_cards->fields['IS_PRIMARY']) { ?>
                                        <span class="badge badge-primary">Primaria</span>
                                    <? } ?>
                                    <? if(!$isTokenized) { ?>
                                        <span class="badge badge-danger">No tokenizada - Eliminar y agregar de nuevo</span>
                                    <? } ?>
                                </div>
                                <div class="card-actions">
                                    <? if(!$res_cards->fields['IS_PRIMARY'] && $isTokenized) { ?>
                                    <button class="btn btn-sm btn-info"
                                            onclick="setPrimary(<?=$res_cards->fields['PK_STUDENT_CREDIT_CARD_CYBERSOURCE']?>)">
                                        Hacer Primaria
                                    </button>
                                    <? } ?>
                                    <button class="btn btn-sm btn-danger"
                                            onclick="deleteCard(<?=$res_cards->fields['PK_STUDENT_CREDIT_CARD_CYBERSOURCE']?>)">
                                        <i class="fa fa-trash"></i> Eliminar
                                    </button>
                                </div>
                            </div>
                            <? $res_cards->MoveNext();
                            } ?>
                        </div>
                    </div>
                </div>
                <? } ?>

                <!-- Agregar nueva tarjeta -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Agregar Nueva Tarjeta</h4>
                        <form id="cardForm">
                            <input type="hidden" id="s_id" value="<?=$PK_STUDENT_MASTER?>">
                            <input type="hidden" id="eid" value="<?=$PK_STUDENT_ENROLLMENT?>">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nombre en la tarjeta *</label>
                                        <input type="text" id="card_name" class="form-control"
                                               placeholder="Como aparece en la tarjeta" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email *</label>
                                        <input type="email" id="card_email" class="form-control"
                                               value="<?=$DEFAULT_EMAIL?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Número de tarjeta *</label>
                                        <div id="card_number" class="microform-field"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>CVV *</label>
                                        <div id="card_cvv" class="microform-field"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Mes (MM) *</label>
                                        <input type="text" id="card_month" class="form-control"
                                               placeholder="MM" maxlength="2" pattern="[0-9]{2}" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Año (YYYY) *</label>
                                        <input type="text" id="card_year" class="form-control"
                                               placeholder="YYYY" maxlength="4" pattern="[0-9]{4}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Teléfono</label>
                                        <input type="tel" id="card_phone" class="form-control"
                                               value="<?=$DEFAULT_PHONE?>" placeholder="555-555-5555">
                                    </div>
                                </div>
                            </div>

                            <h5>Dirección de Facturación</h5>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Dirección *</label>
                                        <input type="text" id="card_address" class="form-control"
                                               value="<?=$DEFAULT_ADDRESS?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Ciudad *</label>
                                        <input type="text" id="card_city" class="form-control"
                                               value="<?=$DEFAULT_CITY?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Estado *</label>
                                        <input type="text" id="card_state" class="form-control"
                                               value="<?=$DEFAULT_STATE?>" maxlength="2" placeholder="CA" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Código Postal *</label>
                                        <input type="text" id="card_zip" class="form-control"
                                               value="<?=$DEFAULT_ZIP?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="make_primary">
                                    <label class="custom-control-label" for="make_primary">
                                        Establecer como tarjeta primaria para pagos recurrentes
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <button type="button" id="saveCardBtn" class="btn btn-primary" onclick="saveCard()">
                                    <i class="fa fa-save"></i> Guardar Tarjeta
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                    <i class="fa fa-times"></i> Cancelar
                                </button>
                            </div>

                            <div id="errorMsg" class="alert alert-danger" style="display:none;"></div>
                            <div id="successMsg" class="alert alert-success" style="display:none;"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <? require_once("footer.php"); ?>
    </div>

    <? require_once("js.php"); ?>
    
    <script>
    let microform = null;
    let microformInitialized = false;
    
    async function initMicroform() {
        if (microformInitialized) return;
        
        try {
            console.log('Inicializando Microform...');
            
            // Obtener captureContext del servidor
            const response = await fetch('cybersource_createcontext.php', {
                method: 'POST',
                credentials: 'same-origin'
            });
            
            const data = await response.json();
            
            if (!data || !data.captureContext) {
                throw new Error('No se pudo obtener el contexto de captura');
            }
            
            console.log('CaptureContext obtenido');
            
            // Inicializar Microform
            const flex = new Flex(data.captureContext);
            microform = flex.microform('card', {
                styles: {
                    'input': {
                        'font-size': '16px',
                        'font-family': 'system-ui, -apple-system, sans-serif',
                        'color': '#1f2937'
                    },
                    '::placeholder': {
                        'color': '#9ca3af'
                    }
                }
            });
            
            // Crear campos
            const cardNumber = microform.createField('number', {
                placeholder: '•••• •••• •••• ••••'
            });
            const cvv = microform.createField('securityCode', {
                placeholder: '•••'
            });
            
            // Montar campos
            cardNumber.load('#card_number');
            cvv.load('#card_cvv');
            
            microformInitialized = true;
            console.log('Microform inicializado correctamente');
            
        } catch (error) {
            console.error('Error inicializando Microform:', error);
            document.getElementById('errorMsg').textContent = 'Error inicializando el formulario seguro: ' + error.message;
            document.getElementById('errorMsg').style.display = 'block';
        }
    }
    
    async function saveCard() {
        const btn = document.getElementById('saveCardBtn');
        const errorDiv = document.getElementById('errorMsg');
        const successDiv = document.getElementById('successMsg');
        
        // Ocultar mensajes previos
        errorDiv.style.display = 'none';
        successDiv.style.display = 'none';
        
        // Validar campos requeridos
        const requiredFields = {
            'card_name': 'Nombre en la tarjeta',
            'card_email': 'Email',
            'card_month': 'Mes de expiración',
            'card_year': 'Año de expiración',
            'card_address': 'Dirección',
            'card_city': 'Ciudad',
            'card_state': 'Estado',
            'card_zip': 'Código postal'
        };
        
        for (let [field, label] of Object.entries(requiredFields)) {
            const value = document.getElementById(field).value.trim();
            if (!value) {
                errorDiv.textContent = `Por favor ingrese: ${label}`;
                errorDiv.style.display = 'block';
                return;
            }
        }
        
        // Validar mes y año
        const month = document.getElementById('card_month').value;
        const year = document.getElementById('card_year').value;
        
        if (!/^(0[1-9]|1[0-2])$/.test(month)) {
            errorDiv.textContent = 'Mes inválido (use formato MM: 01-12)';
            errorDiv.style.display = 'block';
            return;
        }
        
        if (!/^\d{4}$/.test(year) || parseInt(year) < new Date().getFullYear()) {
            errorDiv.textContent = 'Año inválido (use formato YYYY)';
            errorDiv.style.display = 'block';
            return;
        }
        
        // Validar email
        const email = document.getElementById('card_email').value;
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            errorDiv.textContent = 'Email inválido';
            errorDiv.style.display = 'block';
            return;
        }
        
        btn.disabled = true;
        btn.innerHTML = '<span class="loading-spinner"></span> Procesando...';
        
        try {
            console.log('Generando token transitorio...');
            
            // Generar token transitorio con Microform
            const transientToken = await new Promise((resolve, reject) => {
                microform.createToken(
                    {
                        cardExpirationMonth: month,
                        cardExpirationYear: year
                    },
                    (err, token) => {
                        if (err) {
                            console.error('Error creando token:', err);
                            reject(err);
                        } else {
                            console.log('Token creado exitosamente');
                            resolve(token);
                        }
                    }
                );
            });
            
            console.log('Enviando datos al servidor...');
            
            // Preparar datos para enviar
            const formData = {
                transientToken: transientToken,
                s_id: document.getElementById('s_id').value,
                card_name: document.getElementById('card_name').value,
                card_email: document.getElementById('card_email').value,
                card_phone: document.getElementById('card_phone').value,
                card_address: document.getElementById('card_address').value,
                card_city: document.getElementById('card_city').value,
                card_state: document.getElementById('card_state').value,
                card_zip: document.getElementById('card_zip').value,
                card_month: month,
                card_year: year,
                make_primary: document.getElementById('make_primary').checked ? 1 : 0
            };
            
            // Enviar al servidor para guardar token permanente
            const saveResponse = await fetch('cybersource_save_card.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData),
                credentials: 'same-origin'
            });
            
            const result = await saveResponse.json();
            console.log('Respuesta del servidor:', result);
            
            if (result.success) {
                successDiv.textContent = 'Tarjeta guardada exitosamente';
                successDiv.style.display = 'block';
                
                // Limpiar formulario
                resetForm();
                
                // Recargar página después de 1.5 segundos
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
                
            } else {
                throw new Error(result.error || 'Error al guardar la tarjeta');
            }
            
        } catch (error) {
            console.error('Error completo:', error);
            errorDiv.textContent = error.message || 'Error al procesar la tarjeta';
            errorDiv.style.display = 'block';
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-save"></i> Guardar Tarjeta';
        }
    }
    
    function resetForm() {
        document.getElementById('cardForm').reset();
        document.getElementById('errorMsg').style.display = 'none';
        document.getElementById('successMsg').style.display = 'none';
    }
    
    async function setPrimary(cardId) {
        if (!confirm('¿Establecer esta tarjeta como primaria para pagos recurrentes?')) return;
        
        try {
            const response = await fetch('ajax_set_primary_card.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `card_id=${cardId}&student_id=<?=$PK_STUDENT_MASTER?>`,
                credentials: 'same-origin'
            });
            
            const result = await response.text();
            if (result.trim() === 'success') {
                window.location.reload();
            } else {
                alert('Error al actualizar la tarjeta');
            }
        } catch (error) {
            alert('Error: ' + error.message);
        }
    }
    
    async function deleteCard(cardId) {
        if (!confirm('¿Está seguro de eliminar esta tarjeta? Esta acción no se puede deshacer.')) return;
        
        try {
            const response = await fetch('ajax_delete_card.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `card_id=${cardId}`,
                credentials: 'same-origin'
            });
            
            const result = await response.text();
            if (result.trim() === 'success') {
                document.getElementById(`card_${cardId}`).remove();
                
                // Si no quedan tarjetas, recargar la página
                if (document.querySelectorAll('.saved-card').length === 0) {
                    window.location.reload();
                }
            } else {
                alert('Error al eliminar la tarjeta');
            }
        } catch (error) {
            alert('Error: ' + error.message);
        }
    }
    
    // Inicializar al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        initMicroform();
    });
    </script>
</body>
</html>

