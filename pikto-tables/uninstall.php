<?php 
/**
* @package PiktoTables
*/
        if(!defined('WP_UNINSTALL_PLUGIN')){
		die;
	}
        //Get options for database connection
        $dbhost     = get_option("PTDB_HOST");
        $dbname     = get_option("PTDB_NAME");
        $username   = get_option("PTDB_USER");
        $password   = get_option("PTDB_PASS");
        global $wpdb;
        
        // Check if database connection data is set with get_option, if not use global wpdb, if yes, use new instance of wpdb obejct  
        if (empty($dbhost) && empty($dbname) && empty($username)) {
            
            $tables = $wpdb->get_results("
                SELECT  table_schema as database_name,
    				table_name, table_comment
    			from information_schema.tables
    			where table_type = 'BASE TABLE'
    		    and table_schema not in ('information_schema','mysql','performance_schema','sys')
    			and table_name like '{$wpdb->prefix}pikto_%'
    			order by database_name, table_name;");
            // Use for loop to drop all tables from array
            if(!empty($tables)){
                for ($i=0, $count = count($tables); $i < $count ; $i++) {
                    $query	 = $wpdb->query("DROP TABLE IF EXISTS {$tables[$i]->table_name}");
                }
            }
        }else{
            // Declare conn variable as new instancce of wpdb 
            $conn   = new wpdb($username,$password,$dbname,$dbhost);
            // Get all tables created with plugin and put them in array 
            $tables = $conn->get_results("
                SELECT  table_schema as database_name,
    				table_name, table_comment
    			from information_schema.tables
    			where table_type = 'BASE TABLE'
    		    and table_schema not in ('information_schema','mysql','performance_schema','sys')
    			and table_name like '{$wpdb->prefix}pikto_%'
    			order by database_name, table_name;
            ");
            if(!empty($tables)){
                for ($i=0, $count = count($tables); $i < $count ; $i++) { 
                    $query	 = $conn->query("DROP TABLE IF EXISTS {$tables[$i]->database_name}.{$tables[$i]->table_name}");
                }
            }
        }
	
	// Delete options 
        
    delete_option("PTDB_DEFAULT");
	delete_option("PTDB_NAME");
	delete_option("PTDB_HOST");
	delete_option("PTDB_USER");
	delete_option("PTDB_PASS");
	delete_option("PTDB_PORT");

	