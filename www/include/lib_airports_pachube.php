<?php

	loadlib("pachube");

	#################################################################

	function airports_is_valid_iata_code($code){

		return (preg_match("/^[A-Z]{3}$/", $code)) ? 1 : 0;
	}

	#################################################################

	function airports_get_by_iata_code($code){

		$args = array(
			'tag' => "iata:code={$code}",
		);

		$rsp = pachube_feeds_fetch($args);

		if (! $rsp['ok']){
			return null;
		}

		if (! count($rsp['rows'])){
			return null;
		}

		return $rsp['rows'][0];
	}

	#################################################################

	function airports_get_terminals(&$airport, $force=0){

		# TO DO: refetch $airport from the API

		if (! isset($airport['datastreams'])){
			return;
		}

		if (! is_array($airport['tags'])){
			return;
		}

		$iata_code = null;

		foreach ($airport['tags'] as $t){

			if (preg_match("/^iata:code=[A-Z]{3}$/", $t)){
				$iata_code = $t;
				break;
			}
		}

		if (! $iata_code){
			return;
		}

		$datastreams = array();

		foreach ($airport['datastreams'] as $ds){
			$datastreams[$ds['id']] = $ds;
		}

		# overkill? probably...

		/*
		foreach ($airport['datastreams'] as $ds){

			if (! is_array($ds['tags'])){
				continue;
			}

			foreach ($ds['tags'] as $t){

				if ($t == $iata_code){
					$datastreams[$ds['id']] = $ds;
					break;
				}
			}
		}
		*/

		return $datastreams;
	}

	#################################################################

	function airports_create_airport($code){

		$tags = array(
			"iata:code={$code}",
			"airport-timer",
		);

		$more = array(
			'tags' => $tags,
		);

		$rsp = pachube_feeds_create($code, $more);

		if (! $rsp['ok']){
			return $rsp;
		}

		$out = array(
			'airport' => $rsp['feed'],
		);

		return okay($out);
	}

	#################################################################

	function airports_create_terminal(&$airport, $terminal, $checkpoint=''){

		$datastream = _airports_fq_terminal_name($terminal, $checkpoint);

		$more = array();
		$tags = array();

		if (is_array($airport['tags'])){

			foreach ($airport['tags'] as $t){

				if (preg_match("/^iata:code=[A-Z]{3}$/", $t)){
					$tags[] = $t;
				}
			}
		}

		if (count($tags)){
			$more['tags'] = $tags;
		}

		$rsp = pachube_feeds_create_datastream($airport['id'], $datastream, $more);

		if (! $rsp['ok']){
			return $rsp;
		}

		$rsp = array(
			'terminal' => $datastream,
		);

		return okay($rsp);
	}

	#################################################################

	function airports_record_wait_time(&$airport, &$terminal, $details){

		$fmt = "Y-m-d\TH:i:s\Z";
		$dt = gmdate($fmt, $details['stop']);

		$datapoints = array(array(
			'at' => "{$dt}",
			'value' => "{$details['duration']}",
		));

		$rsp = pachube_feeds_create_datapoints($airport['id'], $terminal['id'], $datapoints);
		return $rsp;
	}

	#################################################################

	function _airports_fq_terminal_name($terminal, $checkpoint=null){

		$name = $terminal;

		if ($checkpoint){
			$name = "{$terminal} ({$checkpoint})";
		}

		return $name;
	}

	#################################################################

?>
