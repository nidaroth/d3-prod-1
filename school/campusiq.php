<? 
require_once("../global/config.php");   
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("check_access.php");

// if(empty($_SESSION['PK_ACCOUNT']) || $_SESSION['PK_USER']){ 
//     header("location:../index");
//     exit;
// }

$PK_ACCOUNT = $_SESSION['PK_ACCOUNT'];
$PK_USER    = $_SESSION['PK_USER'];

/* ==========================================
   0) Obtener PK_EMPLOYEE_MASTER desde Z_USER
   ========================================== */

$resUser = $db->Execute("
    SELECT ID ,PK_USER_TYPE,PK_ROLES
    FROM Z_USER 
    WHERE PK_USER = '".$PK_USER."' 
    --  AND PK_ACCOUNT = '".$PK_ACCOUNT."'
");

if ($resUser->RecordCount() == 0) {
    // Usuario sin relación a empleado → no tiene dashboards
    $dashboardsData = [];
} else {

    $PK_EMPLOYEE_MASTER = (int)$resUser->fields['ID'];

    /* ==========================================
       1) Dashboards configurados para la cuenta
          (Z_ACCOUNT.CAMPUSIQ_DASHBOARDPK)
       ========================================== */
    $res_dashboard = $db->Execute("
        SELECT CAMPUSIQ_DASHBOARDPK
        FROM Z_ACCOUNT 
        WHERE PK_ACCOUNT = ".$PK_ACCOUNT."
    ");

    $PK_DASHBOARD_LIST = $res_dashboard->fields['CAMPUSIQ_DASHBOARDPK'] ?? '';
    $accountDashboardIds = array_filter(array_map('trim', explode(",", $PK_DASHBOARD_LIST))); 

    /* ==========================================
       2) Dashboards asignados al empleado
          (CAMPUSIQ_EMPLOYEE.PKDASHBOARD CSV)
       ========================================== */
    $employeeDashboardIds = [];

    $resEmp = $db->Execute("
        SELECT PKDASHBOARD
        FROM CAMPUSIQ_EMPLOYEE
        WHERE PK_ACCOUNT = ".$PK_ACCOUNT."
          AND PK_EMPLOYEE_MASTER = ".$PK_EMPLOYEE_MASTER."
    ");
    // si es usuario type 1 y pk role 1 ve todos los dashboard
    if($resUser->fields['PK_USER_TYPE'] == 1 && $resUser->fields['PK_ROLES'] == 1){
        $resEmp = $db->Execute("
            SELECT CAMPUSIQ_DASHBOARDPK as PKDASHBOARD
            FROM Z_ACCOUNT 
            WHERE PK_ACCOUNT = ".$PK_ACCOUNT."
        ");

    }

    if ($resEmp->RecordCount() > 0) {
        $pkDashboardCsv = $resEmp->fields['PKDASHBOARD']; // ej: "id1,id2,id3"
        if (!empty($pkDashboardCsv)) {
            $employeeDashboardIds = array_filter(array_map('trim', explode(",", $pkDashboardCsv)));
        }
    }



    // Si no tiene nada configurado, no ve ningún dashboard
    if (empty($employeeDashboardIds)) {
        $effectiveDashboardIds = [];
    } else {
        // Intersección: lo que está en la cuenta y además en el empleado
        $effectiveDashboardIds = array_values(array_intersect($accountDashboardIds, $employeeDashboardIds));
    }

    /* ==========================================
       3) Armar data final: nombre + embed_url
       ========================================== */

    $dashboardsData = [];

    if (!empty($effectiveDashboardIds)) {

        // 3.1 Nombres desde CAMPUSIQ_DASHBOARDS
        $in = [];
        foreach ($effectiveDashboardIds as $id) {
            $in[] = '"'.$id.'"';
        }

        $nameByDashboardId = [];

        if (!empty($in)) {
            $sqlNames = "
                SELECT DASHBOARDID, NAME 
                FROM CAMPUSIQ_DASHBOARDS 
                WHERE DASHBOARDID IN (".implode(',', $in).")
            ";
            $resNames = $db->Execute($sqlNames);

            while (!$resNames->EOF) {
                $dId   = $resNames->fields['DASHBOARDID'];
                $dName = $resNames->fields['NAME'];
                $nameByDashboardId[$dId] = $dName;
                $resNames->MoveNext();
            }
        }

        // 3.2 Consumir endpoint solo para dashboards permitidos al usuario
        foreach ($effectiveDashboardIds as $dashId) {

            $url = "https://campusiq.diamondsis.io/api/v1/test/dashboard-embed"
                 . "?PK_ACCOUNT=" . urlencode($PK_ACCOUNT)
                 . "&token=campusiq-diamondsis-05122025"
                 . "&PK_DASHBOARD=" . urlencode($dashId);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);

            $response = curl_exec($ch);

            if ($response !== false) {
                $json = json_decode($response, true);

                if (isset($json['embed_url'])) {
                    $dashboardsData[] = [
                        'id'   => preg_replace('/[^a-zA-Z0-9_-]/', '_', $dashId), // id seguro para HTML
                        'name' => $nameByDashboardId[$dashId] ?? $dashId,
                        'url'  => $json['embed_url']
                    ];
                }
            }

            // print_r($response);

            curl_close($ch);
        }
    }
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
    <title>CAMPUS IQ | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <?php if(count($dashboardsData) > 0) { ?>

                                    <!-- TABS -->
                                    <ul class="nav nav-tabs" role="tablist">
                                        <?php foreach ($dashboardsData as $index => $db): ?>
                                            <li class="nav-item">
                                                <a class="nav-link <?=($index==0?'active':'')?>"
                                                   data-toggle="tab"
                                                   href="#dash<?=$db['id']?>"
                                                   role="tab">
                                                   <?=$db['name']?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>

                                    <!-- TAB CONTENT -->
                                    <div class="tab-content">

                                        <?php foreach ($dashboardsData as $index => $db): ?>
                                            <div class="tab-pane fade <?=($index==0?'show active':'')?>"
                                                 id="dash<?=$db['id']?>"
                                                 role="tabpanel" style="position: relative;">

                                                <iframe 
                                                    width="100%" 
                                                    height="720" 
                                                    style="border:0;"
                                                    src="<?=$db['url']?>">
                                                </iframe>
                                                <div style="height: 30px; width: 160px; background: #000;position: absolute; bottom: 0;right: 0;"></div>

                                            </div>
                                        <?php endforeach; ?>

                                    </div>

                                <?php } else { ?>

                                    <div class="alert alert-warning">
                                        You do not have access to any Campus IQ dashboards, or no dashboards are configured for this account.
                                    </div>

                                <?php } ?>

                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        <? require_once("footer.php"); ?>
    </div>
   
    <? require_once("js.php"); ?>

</body>

</html>
