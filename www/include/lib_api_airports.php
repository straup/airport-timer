<?php

	#################################################################

	loadlib("airports");
	loadlib("airports_utils");

	#################################################################

	# TO DO: API methods for the airport/terminal bits so we can run
	# this as multiple API calls (20111128/straup)

	function api_airports_recordWaitTime(){

		# pull in params

		$airport_code = post_str("airport");
		$terminal_name = post_str("terminal");
		$checkpoint = post_str("checkpoint");

		$stop = post_int32("stop");
		$duration = post_int32("duration");

		if (! airports_utils_is_valid_iata_code($airport_code)){
			api_output_error(400, "missing or invalid airport code");
		}

		if (! airports_utils_is_valid_terminal_name($terminal_name)){
			api_output_error(401, "missing or invalid airport terminal: '{$terminal_name}'");
		}

		if (($checkpoint) && (! airports_utils_is_valid_terminal_name($checkpoint))){
			api_output_error(401, "missing or invalid checkpoint name");
		}

		if (! $duration){
			api_output_error(402, "missing duration");
		}

		if (! $stop){
			api_output_error(403, "missing stop time");
		}

		# start with the airport

		$airport = airports_get_by_iata_code($airport_code);

		if (! $airport){

			$rsp = airports_create_airport($airport_code);

			if (! $rsp['ok']){
				api_output_error(500, "failed to create airport: {$rsp['error']}");
			}

			$airport = $rsp['airport'];
		}

		# create the terminal

		$terminals = airports_get_terminals($airport);

		$terminal = (isset($terminals[$terminal_name])) ? $terminals[$terminal_name] : null;

		if (! $terminal){

			$rsp = airports_create_terminal($airport, $terminal_name, $checkpoint);

			if (! $rsp['ok']){
				api_output_error(510, "failed to create terminal: {$rsp['error']}");
			}

			$terminal = $rsp['terminal'];
		}

		# now the actual record...

		$details = array(
			'stop' => $stop,
			'duration' => $duration,
		);

		$rsp = airports_record_wait_time($airport, $terminal, $details);

		if (! $rsp['ok']){
			api_output_error(520, "failed to record wait time: {$rsp['error']}");
		}

		$out = array(
			'airport_id' => $airport['id'],
			'terminal_id' => $terminal['id'],
		);

		api_output_ok($out);
	}

	#################################################################
?>
