<?
	#
	# $Id$
	#

	include('include/init.php');

	if (! $GLOBALS['cfg']['user']['id']){
		$smarty->display('page_index_signedout.txt');
		exit();
	}

	$smarty->display('page_index.txt');
	exit();
?>
