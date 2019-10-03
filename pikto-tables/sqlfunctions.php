<?php 
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	function pikto_dbsettings(){
		add_option("PTDB_DEFAULT","TRUE");
		add_option("PTDB_NAME","");
		add_option("PTDB_HOST","");
		add_option("PTDB_USER","");
		add_option("PTDB_PASS","");
		add_option("PTDB_PORT","");
	}

	function delete_pikto_dbsettings(){
		delete_option("PTDB_DEFAULT");
	}