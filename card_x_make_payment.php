<? 
function pnp_results( $post_args = array() ) {
    if ( !count( $post_args) ) {
        return array();
    }
/*
    $http_query = str_replace("&amp;", "&", ( http_build_query( $post_args ) ));
//echo $http_query."<br /><br />";
    // init curl handle
    $pnp_ch = curl_init('https://pay1.plugnpay.com/payment/pnpremote.cgi');
    curl_setopt($pnp_ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($pnp_ch, CURLOPT_POSTFIELDS, $http_query );
    #curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);  // Upon problem, uncomment for additional Windows 2003 compatibility

    // perform post
    $response = curl_exec($pnp_ch);
    parse_str( $response, $results_array );

    $results = (object) $results_array;
    return $results;
	*/
	
	 $http_query = str_replace("&amp;", "&", ( http_build_query( $post_args ) ));
	$URL = "https://pay1.plugnpay.com/payment/pnpremote.cgi";
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => $URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => $http_query
	));

	$response 	= curl_exec($curl);
	$err 		= curl_error($curl);

	curl_close($curl);
	
	print_r($response);
	print_r($err);
	
}

function payment(){

    $post_vals = array(
        'publisher-name' 		=> 'diamondir1',
		'publisher-password' 	=> '7cbSJFcRADPSqYXe',
        'mode'           		=> 'authprev',
        'prevorderid'     	 	=> '2021032413310319873',
		'card-amount'      		=> 1
    );


    // What packages were purchased?
    /*if ( isset( $args['packages'] )) {
        for($i = 0; $i < count($args['packages']); $i++){
            $j = $i + 1;
            $post_vals["item$j"]     = $args['packages'][$i]['packages_id'];
            $post_vals["cost$j"]     = $args['packages'][$i]['final_price'];
            $post_vals["quantity$j"] = $args['packages'][$i]['quantity'];
            $post_vals["description$j"] = 
                $all_packages[$args['packages'][$i]['packages_id']]['packages_name'];
        }
    }*/

    $return = pnp_results( $post_vals );
    $this->orderID = $return->orderID;
    return $return;
}
echo "<pre> ---- ";echo payment();