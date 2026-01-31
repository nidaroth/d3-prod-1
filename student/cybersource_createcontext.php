<?php
// /D3/school/cybersource_createcontext.php
header('Content-Type: application/json');
require_once("../global/config.php");

if(empty($_SESSION['PK_ACCOUNT'])){
    $_SESSION['PK_ACCOUNT'] = $_SESSION['PK_ACCOUNT'] ?? 1; // Default si no existe
}

// Obtener configuración desde la BD
$res_cs = $db->Execute("SELECT * FROM S_CYBERSOURCE_SETTINGS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
if(!$res_cs || $res_cs->RecordCount() == 0) {
    http_response_code(500);
    echo json_encode(["error" => "Configuración no encontrada"]);
    exit;
}

$merchantId = trim($res_cs->fields['MERCHANT_ID']);
$keyId = trim($res_cs->fields['KEY_ID']);
$secretKey = trim($res_cs->fields['SECRET_KEY']);
$host = $res_cs->fields['ENVIRONMENT'] ?: 'apitest.cybersource.com';

// Preparar el request
$date = gmdate("D, d M Y H:i:s") . " GMT";
$requestTarget = "post /microform/v2/sessions";

// Obtener el dominio actual para targetOrigins
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$domain = $_SERVER['HTTP_HOST'];
$targetOrigin = $protocol . "://" . $domain;

// Body del request
$body = json_encode([
    "clientReferenceInformation" => ["code" => "generate_capture_context_" . time()],
    "targetOrigins" => [
        $targetOrigin,
        "https://localhost",
        "http://localhost",
        "https://uat-74.diamondsis.io" // Agregar tu dominio específico si es necesario
    ]
]);

// Generar digest
$digest = base64_encode(hash("sha256", $body, true));

// Crear signature string (orden específico es importante)
$signatureString = "host: $host\n" .
                   "date: $date\n" .
                   "digest: SHA-256=$digest\n" .
                   "request-target: $requestTarget\n" .
                   "v-c-merchant-id: $merchantId";

// Generar signature
$signature = base64_encode(hash_hmac("sha256", $signatureString, base64_decode($secretKey), true));

// Headers
$headers = [
    "Content-Type: application/json",
    "v-c-merchant-id: $merchantId",
    "Date: $date",
    "Host: $host",
    "Digest: SHA-256=$digest",
    "Signature: keyid=\"$keyId\", algorithm=\"HmacSHA256\", headers=\"host date digest request-target v-c-merchant-id\", signature=\"$signature\""
];

// Hacer la llamada con cURL
$ch = curl_init("https://$host/microform/v2/sessions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    http_response_code(500);
    echo json_encode(["error" => "cURL Error: " . $error]);
    exit;
}

if ($httpCode !== 201 && $httpCode !== 200) {
    http_response_code($httpCode);
    echo json_encode([
        "error" => "Error al generar contexto",
        "httpCode" => $httpCode,
        "response" => $response
    ]);
    exit;
}

// Devolver el captureContext
echo json_encode(["captureContext" => $response]);
?>


