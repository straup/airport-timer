<?php

	#################################################################

	function airports_utils_is_valid_iata_code($code){

		return (preg_match("/^[A-Z]{3}$/", $code)) ? 1 : 0;
	}

	#################################################################

	# Quick. And. Dirty

	function airports_utils_is_valid_terminal_name($terminal){

		if (! preg_match("/^([A-Za-z0-9\-_\s]+)$/", $terminal)){
			return 0;
		}

		return 1;
	}

	#################################################################

?>
