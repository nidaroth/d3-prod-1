<?php
// /D3/student/add_cc_cybersource.php
require_once("../global/config.php");
require_once("../language/common.php");

// Verificar acceso
$res_pay = $db->Execute("SELECT ENABLE_DIAMOND_PAY FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
if($res_pay->fields['ENABLE_DIAMOND_PAY'] != 3) {
    header("location:../index");
    exit;
}

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER_TYPE'] != 3){
    header("location:../index");
    exit;
}

$PK_STUDENT_MASTER = $_SESSION['PK_STUDENT_MASTER'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Agregar Tarjeta - Cybersource</title>
    <script src="https://testflex.cybersource.com/microform/bundle/v2/flex-microform.min.js"></script>
    <? require_once("css.php"); ?>
    <style>
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
        #card_number, #card_cvv { height: 48px; }
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
                        <h4 class="text-themecolor">Agregar Tarjeta de Crédito</h4>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form id="cardForm" onsubmit="return false;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h5>Información de la Tarjeta</h5>
                                            
                                            <div class="form-group">
                                                <label>Nombre en la tarjeta *</label>
                                                <input type="text" id="card_name" class="form-control" required>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Número de tarjeta *</label>
                                                <div id="card_number" class="microform-field"></div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>CVV *</label>
                                                        <div id="card_cvv" class="microform-field"></div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Mes (MM) *</label>
                                                        <input type="text" id="card_month" class="form-control"
                                                               maxlength="2" pattern="[0-9]{2}" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Año (YYYY) *</label>
                                                        <input type="text" id="card_year" class="form-control"
                                                               maxlength="4" pattern="[0-9]{4}" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <h5>Dirección de Facturación</h5>
                                            
                                            <div class="form-group">
                                                <label>Email *</label>
                                                <input type="email" id="card_email" class="form-control"
                                                       value="<?=$_SESSION['EMAIL']?>" required>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Teléfono</label>
                                                <input type="tel" id="card_phone" class="form-control">
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Dirección *</label>
                                                <input type="text" id="card_address" class="form-control" required>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Ciudad *</label>
                                                        <input type="text" id="card_city" class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Estado *</label>
                                                        <input type="text" id="card_state" class="form-control"
                                                               maxlength="2" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>C.P. *</label>
                                                        <input type="text" id="card_zip" class="form-control" required>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="form-group">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="make_primary">
                                                    <label class="custom-control-label" for="make_primary">
                                                        Establecer como tarjeta primaria
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-12">
                                            <div id="errorMsg" class="alert alert-danger" style="display:none;"></div>
                                            <div id="successMsg" class="alert alert-success" style="display:none;"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-12 text-right">
                                            <button type="button" id="saveCardBtn" class="btn btn-primary" onclick="saveCard()">
                                                <i class="fa fa-save"></i> Guardar Tarjeta
                                            </button>
                                            <button type="button" class="btn btn-secondary"
                                                    onclick="window.location.href='payment_info_cybersource'">
                                                Cancelar
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
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
            // Obtener captureContext (usa el mismo endpoint que school)
            const response = await fetch('cybersource_createcontext.php', {
                method: 'POST',
                credentials: 'same-origin'
            });
            
            const data = await response.json();
            
            if (!data || !data.captureContext) {
                throw new Error('No se pudo obtener el contexto de captura');
            }
            
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
            console.log('Microform inicializado');
            
        } catch (error) {
            console.error('Error inicializando Microform:', error);
            document.getElementById('errorMsg').textContent = 'Error inicializando el formulario seguro';
            document.getElementById('errorMsg').style.display = 'block';
        }
    }
    
    async function saveCard() {
        const btn = document.getElementById('saveCardBtn');
        const errorDiv = document.getElementById('errorMsg');
        const successDiv = document.getElementById('successMsg');
        
        errorDiv.style.display = 'none';
        successDiv.style.display = 'none';
        
        // Validar campos
        const requiredFields = ['card_name', 'card_email', 'card_month', 'card_year',
                               'card_address', 'card_city', 'card_state', 'card_zip'];
        
        for (let field of requiredFields) {
            const value = document.getElementById(field).value.trim();
            if (!value) {
                errorDiv.textContent = 'Por favor complete todos los campos requeridos';
                errorDiv.style.display = 'block';
                return;
            }
        }
        
        const month = document.getElementById('card_month').value;
        const year = document.getElementById('card_year').value;
        
        if (!/^(0[1-9]|1[0-2])$/.test(month)) {
            errorDiv.textContent = 'Mes inválido';
            errorDiv.style.display = 'block';
            return;
        }
        
        if (!/^\d{4}$/.test(year)) {
            errorDiv.textContent = 'Año inválido';
            errorDiv.style.display = 'block';
            return;
        }
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Procesando...';
        
        try {
            // Generar token transitorio
            const transientToken = await new Promise((resolve, reject) => {
                microform.createToken(
                    {
                        cardExpirationMonth: month,
                        cardExpirationYear: year
                    },
                    (err, token) => err ? reject(err) : resolve(token)
                );
            });
            
            // Enviar al servidor (usa el endpoint compartido)
            const formData = {
                transientToken: transientToken,
                s_id: <?=$PK_STUDENT_MASTER?>,
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
            
            const saveResponse = await fetch('cybersource_save_card.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData),
                credentials: 'same-origin'
            });
            
            const result = await saveResponse.json();
            
            if (result.success) {
                successDiv.textContent = 'Tarjeta guardada exitosamente';
                successDiv.style.display = 'block';
                
                setTimeout(() => {
                    window.location.href = 'payment_info_cybersource';
                }, 1500);
                
            } else {
                throw new Error(result.error || 'Error al guardar la tarjeta');
            }
            
        } catch (error) {
            errorDiv.textContent = error.message || 'Error al procesar la tarjeta';
            errorDiv.style.display = 'block';
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-save"></i> Guardar Tarjeta';
        }
    }
    
    // Inicializar al cargar
    document.addEventListener('DOMContentLoaded', function() {
        initMicroform();
    });
    </script>
</body>
</html>

