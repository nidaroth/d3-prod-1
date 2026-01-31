<?php




require_once("../global/config.php");


function pdf_footer()
{



    global $db;

    $res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
    $timezone = $res->fields['PK_TIMEZONE'];
    if ($timezone == '' || $timezone == 0)
        $timezone = 4;
    $res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");

    $TIMEZONE = $res->fields['TIMEZONE'];
    // dd($TIMEZONE);
    $date = convert_to_user_date(date('Y-m-d H:i:s'), 'l, F d, Y h:i A', $TIMEZONE, date_default_timezone_get());


    $PK_PDF_FOOTER = 17;
    $res_type = $db->Execute("SELECT FOOTER_LOC, CONTENT FROM S_PDF_FOOTER,S_PDF_FOOTER_CAMPUS WHERE S_PDF_FOOTER.ACTIVE = 1 AND S_PDF_FOOTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = '$PK_PDF_FOOTER' AND S_PDF_FOOTER.PK_PDF_FOOTER = S_PDF_FOOTER_CAMPUS.PK_PDF_FOOTER "); //AND PK_CAMPUS = '$PK_CAMPUS'

    $CONTENT = nl2br($res_type->fields['CONTENT']);



    $footer = '				<table width="100%" border="0"> 
								<tr>
									<td valign="top" style="font-size:10px ; text-align:center">' . $CONTENT . '</td>
								</tr>
							</table>
							';
    $date = convert_to_user_date(date('Y-m-d H:i:s'), 'l, F d, Y h:i A', $TIMEZONE, date_default_timezone_get());
    $footer .= '	
							<table width="100%" border="0"> 
							<tr> <td style="font-size:16px !important">Instructor Signature ____________________________________________________</td> <td style="font-size:16px !important"> Date : ___________________________________</td> </tr>
								<tr>
									<td valign="top" style="font-size:10px"><i>' . $date . '</i></td>
									<td valign="top" style="font-size:10px;" align="right" colspan="2">Page <span id="page"></span> of
										<span id="topage"></span>
									</td>
									<td valign="top" style="font-size:10px;"></td>
								</tr>
							</table>';


                            $footer_cont = '
						<!DOCTYPE HTML>
						<html>
						
						<head>
							<style>
								tbody td {
									font-size: 14px !important;
								}
								body,html {
									padding: 0;
									margin: 0;
									font-size: 0;
								}
								table { page-break-inside:auto }
								tr    { page-break-inside:avoid; page-break-after:auto }
								thead { display:table-header-group }
								tfoot { display:table-footer-group }
							</style>
						</head>
						
						<body> <span></span>' . $footer . '
						
							<script>
								var vars = {};
								var x = window.location.search.substring(1).split("&");
								for (var i in x) {
									var z = x[i].split("=", 2);
									vars[z[0]] = unescape(z[1]);
								}
								document.getElementById("page").innerHTML = vars.sitepage;
								document.getElementById("topage").innerHTML = vars.sitepages;
							</script>
						</body>
						
						</html>';
    echo $footer_cont;
}

pdf_footer();
