<?php
// /D3/school/cybersource_save_card.php
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar errores en producción
ini_set('log_errors', 1);

header('Content-Type: application/json');

// Función para devolver error JSON
function returnError($message, $code = 500) {
    http_response_code($code);
    echo json_encode(["success" => false, "error" => $message]);
    exit;
}

// Función para log de debugging
function debugLog($message, $data = null) {
    error_log("CYBERSOURCE_SAVE_CARD: " . $message . ($data ? " - " . json_encode($data) : ""));
}

try {
    require_once("../global/config.php");
    
    // Verificar sesión
    if (empty($_SESSION['PK_ACCOUNT']) || empty($_SESSION['PK_USER'])) {
        returnError("No autorizado", 401);
    }
    
    debugLog("Session OK", ["PK_ACCOUNT" => $_SESSION['PK_ACCOUNT']]);
    
    // Obtener configuración
    $res = $db->Execute("SELECT * FROM S_CYBERSOURCE_SETTINGS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
    if(!$res || $res->RecordCount() == 0) {
        returnError("Configuración no encontrada");
    }
    
    $merchantId = trim($res->fields['MERCHANT_ID']);
    $keyId      = trim($res->fields['KEY_ID']);
    $secretB64  = trim($res->fields['SECRET_KEY']);
    $host       = $res->fields['ENVIRONMENT'] ?: 'apitest.cybersource.com';
    
    debugLog("Config loaded", ["merchant" => $merchantId, "host" => $host]);
    
    // Obtener datos del request
    $inputRaw = file_get_contents("php://input");
    debugLog("Input received", ["length" => strlen($inputRaw)]);
    
    $input = json_decode($inputRaw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        returnError("JSON inválido: " . json_last_error_msg(), 400);
    }
    
    $transientToken = $input['transientToken'] ?? null;
    $studentId      = $input['s_id'] ?? null;
    $cardName       = $input['card_name'] ?? null;
    $cardEmail      = $input['card_email'] ?? null;
    $cardPhone      = $input['card_phone'] ?? null;
    $cardAddress    = $input['card_address'] ?? null;
    $cardCity       = $input['card_city'] ?? null;
    $cardState      = $input['card_state'] ?? null;
    $cardZip        = $input['card_zip'] ?? null;
    $cardMonth      = $input['card_month'] ?? null;
    $cardYear       = $input['card_year'] ?? null;
    $makePrimary    = $input['make_primary'] ?? 0;
    
    debugLog("Data parsed", ["studentId" => $studentId, "hasToken" => !empty($transientToken)]);
    
    if (!$transientToken || !$studentId || !$cardName) {
        returnError("Datos incompletos", 400);
    }
    
    // Limpiar número de teléfono
    $cardPhone = preg_replace('/[^0-9]/', '', $cardPhone);
    if (empty($cardPhone)) {
        $cardPhone = '5555555555';
    }
    
    // Separar nombre
    $nameParts = explode(' ', trim($cardName), 2);
    $firstName = $nameParts[0] ?? 'N/A';
    $lastName = $nameParts[1] ?? $firstName;
    
    // Asegurar que el estado sea de 2 caracteres
    $cardState = strtoupper(substr($cardState, 0, 2));
    if (strlen($cardState) != 2) {
        $cardState = 'CA'; // Default
    }
    
    // Crear payload para tokenización permanente
    $bodyArr = [
        "clientReferenceInformation" => [
            "code" => "SAVE_" . $studentId . "_" . time()
        ],
        "processingInformation" => [
            "capture" => false,
            "actionList" => ["TOKEN_CREATE"],
            "actionTokenTypes" => ["customer", "paymentInstrument"]
        ],
        "orderInformation" => [
            "amountDetails" => [
                "totalAmount" => "1.00",
                "currency" => "USD"
            ],
            "billTo" => [
                "firstName" => substr($firstName, 0, 60),
                "lastName"  => substr($lastName, 0, 60),
                "address1"  => substr($cardAddress, 0, 60),
                "locality"  => substr($cardCity, 0, 50),
                "administrativeArea" => $cardState,
                "postalCode" => substr($cardZip, 0, 10),
                "country"   => "US",
                "email"     => substr($cardEmail, 0, 255),
                "phoneNumber" => substr($cardPhone, 0, 15)
            ]
        ],
        "paymentInformation" => [
            "card" => [
                "expirationMonth" => str_pad($cardMonth, 2, '0', STR_PAD_LEFT),
                "expirationYear"  => $cardYear
            ]
        ],
        "tokenInformation" => [
            "transientTokenJwt" => $transientToken
        ]
    ];
    
    debugLog("Payload created");
    
    // Preparar request
    $path   = "/pts/v2/payments";
    $method = "post";
    $date   = gmdate("D, d M Y H:i:s T");
    $payload = json_encode($bodyArr, JSON_UNESCAPED_SLASHES);
    
    // Generar firma
    $digest = 'SHA-256=' . base64_encode(hash('sha256', $payload, true));
    
    $sigString =
        "(request-target): {$method} {$path}\n" .
        "host: {$host}\n" .
        "date: {$date}\n" .
        "v-c-merchant-id: {$merchantId}\n" .
        "digest: {$digest}";
    
    $secretBin = base64_decode($secretB64);
    $signature = base64_encode(hash_hmac('sha256', $sigString, $secretBin, true));
    $sigHeader = 'keyid="'.$keyId.'", algorithm="HmacSHA256", headers="(request-target) host date v-c-merchant-id digest", signature="'.$signature.'"';
    
    debugLog("Signature created");
    
    // Ejecutar cURL
    $ch = curl_init("https://{$host}{$path}");
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            "host: {$host}",
            "date: {$date}",
            "v-c-merchant-id: {$merchantId}",
            "digest: {$digest}",
            "signature: {$sigHeader}",
            "content-type: application/json"
        ],
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_TIMEOUT        => 30
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    debugLog("cURL executed", ["httpCode" => $httpCode, "hasResponse" => !empty($response)]);
    
    if ($response === false) {
        returnError("Error de conexión: " . $curlError);
    }
    
    $responseData = json_decode($response, true);
    
    debugLog("Response parsed", ["status" => $responseData['status'] ?? 'unknown']);
    
    // Buscar tokens en la respuesta
    $paymentInstrumentId = null;
    $customerId = null;
    $instrumentIdentifierId = null;
    $cardBrand = 'UNKNOWN';
    $cardLastFour = '****';
    
    // Buscar en tokenInformation primero, luego en paymentInformation
    if (isset($responseData['tokenInformation'])) {
        $tokenInfo = $responseData['tokenInformation'];
        
        if (isset($tokenInfo['paymentInstrument']['id'])) {
            $paymentInstrumentId = $tokenInfo['paymentInstrument']['id'];
        }
        if (isset($tokenInfo['customer']['id'])) {
            $customerId = $tokenInfo['customer']['id'];
        }
        if (isset($tokenInfo['instrumentIdentifier']['id'])) {
            $instrumentIdentifierId = $tokenInfo['instrumentIdentifier']['id'];
        }
    }
    
    // Si no encontramos en tokenInformation, buscar en paymentInformation
    if (!$paymentInstrumentId && isset($responseData['paymentInformation'])) {
        $paymentInfo = $responseData['paymentInformation'];
        
        if (isset($paymentInfo['paymentInstrument']['id'])) {
            $paymentInstrumentId = $paymentInfo['paymentInstrument']['id'];
        }
        if (isset($paymentInfo['customer']['id'])) {
            $customerId = $paymentInfo['customer']['id'];
        }
        if (isset($paymentInfo['instrumentIdentifier']['id'])) {
            $instrumentIdentifierId = $paymentInfo['instrumentIdentifier']['id'];
        }
    }
    
    // Obtener información de la tarjeta
    if (isset($responseData['paymentInformation']['card'])) {
        $cardInfo = $responseData['paymentInformation']['card'];
        $cardBrand = $cardInfo['type'] ?? 'UNKNOWN';
        
        // Intentar obtener últimos 4 dígitos
        if (isset($cardInfo['suffix'])) {
            $cardLastFour = $cardInfo['suffix'];
        } elseif (isset($responseData['paymentInformation']['tokenizedCard']['number'])) {
            $cardLastFour = substr($responseData['paymentInformation']['tokenizedCard']['number'], -4);
        }
    }
    
    debugLog("Tokens extracted", [
        "paymentInstrument" => $paymentInstrumentId,
        "customer" => $customerId,
        "brand" => $cardBrand
    ]);
    
    // Verificar que obtuvimos los tokens necesarios
    if (!$paymentInstrumentId || !$customerId) {
        debugLog("Missing tokens", $responseData);
        returnError("No se pudieron obtener los tokens necesarios. Response: " . json_encode($responseData), 400);
    }
    
    // Guardar en base de datos
    debugLog("Starting DB save");
    
    // Si se marca como primaria, desmarcar las demás
    if ($makePrimary == 1) {
        $updateQuery = "UPDATE S_STUDENT_CREDIT_CARD_CYBERSOURCE 
                       SET IS_PRIMARY = 0,
                           EDITED_BY = '$_SESSION[PK_USER]',
                           EDITED_ON = NOW()
                       WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
                       AND PK_STUDENT_MASTER = '$studentId'";
        $db->Execute($updateQuery);
        debugLog("Unmarked other primary cards");
    }
    
    // Verificar si es la primera tarjeta
    $countQuery = "SELECT COUNT(*) as total 
                  FROM S_STUDENT_CREDIT_CARD_CYBERSOURCE 
                  WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
                  AND PK_STUDENT_MASTER = '$studentId' 
                  AND ACTIVE = 1";
    $res_count = $db->Execute($countQuery);
    
    if ($res_count && $res_count->fields['total'] == 0) {
        $makePrimary = 1;
        debugLog("First card, making primary");
    }
    
    // Preparar datos para insertar
    $CARD_DATA = [
        'PK_ACCOUNT' => $_SESSION['PK_ACCOUNT'],
        'PK_STUDENT_MASTER' => $studentId,
        'CARD_LAST_FOUR' => $cardLastFour,
        'CARD_BRAND' => $cardBrand,
        'NAME_ON_CARD' => $cardName,
        'EXPIRATION_MONTH' => $cardMonth,
        'EXPIRATION_YEAR' => $cardYear,
        'CUSTOMER_ID' => $customerId,
        'PAYMENT_INSTRUMENT_ID' => $paymentInstrumentId,
        'INSTRUMENT_IDENTIFIER_ID' => $instrumentIdentifierId,
        'BILLING_ADDRESS1' => $cardAddress,
        'BILLING_CITY' => $cardCity,
        'BILLING_STATE' => $cardState,
        'BILLING_ZIP' => $cardZip,
        'BILLING_COUNTRY' => 'US',
        'BILLING_EMAIL' => $cardEmail,
        'BILLING_PHONE' => $cardPhone,
        'IS_PRIMARY' => $makePrimary,
        'ACTIVE' => 1,
        'CREATED_BY' => $_SESSION['PK_USER'],
        'CREATED_ON' => date("Y-m-d H:i:s")
    ];
    
    // Verificar si db_perform existe, si no usar INSERT manual
    if (function_exists('db_perform')) {
        db_perform('S_STUDENT_CREDIT_CARD_CYBERSOURCE', $CARD_DATA, 'insert');
        $cardId = $db->insert_ID();
    } else {
        // INSERT manual
        $fields = array_keys($CARD_DATA);
        $values = array_map(function($v) use ($db) {
            if (is_null($v)) return 'NULL';
            if (is_numeric($v)) return $v;
            return "'" . addslashes($v) . "'";
        }, array_values($CARD_DATA));
        
        $insertQuery = "INSERT INTO S_STUDENT_CREDIT_CARD_CYBERSOURCE 
                       (" . implode(', ', $fields) . ") 
                       VALUES (" . implode(', ', $values) . ")";
        
        $db->Execute($insertQuery);
        $cardId = $db->Insert_ID();
    }
    
    debugLog("Card saved", ["cardId" => $cardId]);
    
    // Log de la transacción
    $LOG_DATA = [
        'PK_STUDENT_MASTER' => $studentId,
        'PK_ACCOUNT' => $_SESSION['PK_ACCOUNT'],
        'STATUS' => 'TOKEN_CREATED',
        'TRANSACTION_ID' => $responseData['id'] ?? null,
        'REQUEST' => substr($payload, 0, 65535), // Limitar tamaño
        'RESPONSE' => substr(json_encode($responseData), 0, 65535),
        'TRANSACTION_START' => date("Y-m-d H:i:s"),
        'PK_STUDENT_CREDIT_CARD_CYBERSOURCE' => $cardId,
        'CREATED_ON' => date("Y-m-d H:i:s"),
        'CREATED_BY' => $_SESSION['PK_USER']
    ];
    
    if (function_exists('db_perform')) {
        db_perform('S_PAYMENT_CYBERSOURCE_LOG', $LOG_DATA, 'insert');
    } else {
        // INSERT manual para log
        $fields = array_keys($LOG_DATA);
        $values = array_map(function($v) use ($db) {
            if (is_null($v)) return 'NULL';
            if (is_numeric($v)) return $v;
            return "'" . addslashes($v) . "'";
        }, array_values($LOG_DATA));
        
        $insertLogQuery = "INSERT INTO S_PAYMENT_CYBERSOURCE_LOG 
                          (" . implode(', ', $fields) . ") 
                          VALUES (" . implode(', ', $values) . ")";
        
        $db->Execute($insertLogQuery);
    }
    
    debugLog("Transaction logged");
    
    // Respuesta exitosa
    echo json_encode([
        "success" => true,
        "cardId" => $cardId,
        "message" => "Tarjeta guardada exitosamente"
    ]);
    
} catch (Exception $e) {
    debugLog("Exception caught", ["message" => $e->getMessage(), "trace" => $e->getTraceAsString()]);
    returnError("Error del sistema: " . $e->getMessage());
}
?>

