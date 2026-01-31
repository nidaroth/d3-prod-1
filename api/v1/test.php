<?
$APIKEY = 'HlvjJ4diRozPe8FWznOUDvSDF2CAjH8KfsD6qYzH';
//$APIKEY = 'mXbR9Yy0RBGAc8IbZKB9ifLFGzlhuZJqriyQMXlQ';

$DATA = '{"PLACEMENT_COMPANY_EVENT_TYPE_ID": 1,"EVENT_DATE": "2021-05-26","FOLLOW_UP_DATE": "2021-05-26","COMPANY_CONTACT_ID": 7,"SCHOOL_CONTACT_ID": 65,"COMPLETE": "Yes","NOTE": "This is my event note 1"}';
//$URL = "http://localhost/DSIS/api/v1/list-race/Black+or+African+American";
//$URL = "http://localhost/DSIS/api/v1/create-course";
$URL = "http://localhost/DSIS/api/v1/list-placement-company-question";

$curl = curl_init();
curl_setopt_array($curl, array(
	CURLOPT_URL => $URL,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => "",
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 30,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_POST => 1,
	CURLOPT_POSTFIELDS => $DATA,
	CURLOPT_HTTPHEADER => array(
		"APIKEY: ".$APIKEY
	),
));

$response 	= curl_exec($curl);
$err 		= curl_error($curl);

curl_close($curl);

if($err) {
	echo "cURL Error #:" . $err;
} else {
	print_r($response);
}
?>