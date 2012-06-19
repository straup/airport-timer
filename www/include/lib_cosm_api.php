<?php

	# This is not an all-purpose Cosm wrapper library.
	# Also, it has not been tested yet (20120618/straup)

	loadlib("http");

	#################################################################

	function cosm_api_get($url, $headers=array()){

		cosm_api_assign_headers($headers);

		$rsp = http_get($url, $headers);

		return cosm_api_parse_response($rsp);
	}

	#################################################################

	function cosm_api_post($url, $body, $headers=array()){

		cosm_api_assign_headers($headers);

		$rsp = http_post($url, $body, $headers);

		return cosm_api_parse_response($rsp);
	}

	#################################################################

	function cosm_api_parse_response(&$rsp){

		$data = json_decode($rsp['body'], 'as hash');

		if ((! $rsp['ok']) && (isset($data['errors']))){
			$err_str = (is_array($data['errors'])) ? implode(";", $data['errors']) : $data['errors'];
			return not_okay($err_str);
		}

		if (! $rsp['ok']){
			return $rsp;
		}

		# WRITE ME ?

		return $rsp;
	}

	#################################################################

	function cosm_api_assign_headers(&$headers){

		$headers['Accept'] = 'application/json';
		$headers['X-ApiKey'] = $GLOBALS['cfg']['cosm_api_key'];

		# Note the pass-by-ref
	}

	#################################################################
?>
