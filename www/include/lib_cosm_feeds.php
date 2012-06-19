<?php

	loadlib("cosm_api");

	#################################################################

	function cosm_feeds_fetch($args=array()){

		$query = http_build_query($args);
		$url = "{$GLOBALS['cfg']['cosm_api_endpoint']}feeds/?{$query}";

		$rsp = cosm_api_get($url);

		if (! $rsp['ok']){
			return $rsp;
		}

		$data = json_decode($rsp['body'], 'as hash');

		if (! $data){
			return not_okay("failed to parse response");
		}

		# pagination goes here

		$out = array(
			'rows' => $data['results'],
			'pagination' => null,
		);

		return okay($out);
	}

	#################################################################

	function cosm_feeds_create($feed, $more=array()){

		$url = "{$GLOBALS['cfg']['cosm_api_endpoint']}feeds/";

		$data = array(
			"title" => $feed,
			"version" => "1.0.0",
			"datastreams" => array(),
		);

		if (is_array($more['tags'])){
			$data['tags'] = $more['tags'];
		}

		$body = json_encode($data);
		$rsp = cosm_api_post($url, $body);

		if (! $rsp['ok']){
			return $rsp;
		}

		# MAKE ME BETTER

		$headers = $rsp['headers'];

		if (! preg_match("!^http://api.cosm.com/v2/feeds/(\d+)$!", $headers['location'], $m)){
			return not_okay("missing location");
		}

		$data["id"] = $m[1];

		$out = array(
			'feed' => $data,
		);

		return okay($out);
	}

	#################################################################

	function cosm_feeds_create_datastream($feed_id, $datastream, $more=array()){

		$url = "{$GLOBALS['cfg']['cosm_api_endpoint']}feeds/{$feed_id}/datastreams";

		$ds = array(
			'current_value' => 0,
			'id' => $datastream,
		);

		if (is_array($more['tags'])){
			$ds['tags'] = $more['tags'];
		}

		$data = array(
			'version' => '1.0.0',
			'datastreams' => array($ds),
		);

		$body = json_encode($data);
		$rsp = cosm_api_post($url, $body);

		if (! $rsp['ok']){
			return $rsp;
		}

		$rsp = array(
			'datastream' => $ds,
		);

		return okay($rsp);
	}

	#################################################################

	function cosm_feeds_create_datapoints($feed_id, $datastream_id, $datapoints){

		$url = "{$GLOBALS['cfg']['cosm_api_endpoint']}feeds/{$feed_id}/datastreams/{$datastream_id}/datapoints";

		$data = array(
			'version' => '1.0.0',
			'datapoints' => $datapoints,
		);

		$body = json_encode($data);
		$rsp = cosm_api_post($url, $body);

		return $rsp;
	}

	#################################################################
?>
