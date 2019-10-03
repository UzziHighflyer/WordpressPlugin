<?php 
	global $wpdb;
        
	// Get options from database 
	$option 	= get_option("PTDB_DEFAULT");
	$dbhost 	= get_option("PTDB_HOST");
	$dbname 	= get_option("PTDB_NAME");
	$username	= get_option("PTDB_USER");
	$password 	= get_option("PTDB_PASS");
       
	// Check if default database is set, if not, use custom database connection
	if(!$option || (!empty($dbhost) && !empty($dbname) && !empty($username))){
		$conn   = new wpdb($username,$password,$dbname,$dbhost);
		$result = $conn->get_results("
			SELECT  table_schema as database_name,
					table_name, table_comment
				from information_schema.tables
				where table_type = 'BASE TABLE'
			    and table_schema not in ('information_schema','mysql','performance_schema','sys')
				and table_name like '{$wpdb->prefix}pikto_%'
				order by database_name, table_name;
		");
		// If truncate was clicked under pdo config 
		if (isset($_POST['pt_truncatetable'])) {
			$tablename 		= $_POST['pt_tablename'];
			$databasename           = $_POST['pt_databasename'];
			
			$query = $conn->query("TRUNCATE TABLE {$databasename}.{$tablename}");
			if($query){
				?>
					<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> <p><strong>Uspesno ste obrisali podatke iz tabele: <?php echo $databasename .  '.' . $tablename ?>.</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
				<?php
			}else{
				?>	
                    <div id="setting-error-settings_updated" class="failed settings-error notice is-dismissible"> <p><strong>Niste uspeli da obrisete podatke iz tabele. Error: <?php echo  $conn->last_error ?></strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.
                    </span></button></div>
				<?php
			}
		}
		// If drop table was clicked under pdo config
		if (isset($_POST['pt_droptable'])) {
			$tablename 			= $_POST['pt_tablename'];
			$databasename       = $_POST['pt_databasename'];
			
			
			$query = $conn->query("DROP TABLE {$databasename}.{$tablename}");
			if($query){
				?>
					<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> <p><strong>Uspesno ste obrisali tabelu: <?php echo $databasename .  '.' . $tablename ?>.</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.
					</span></button></div>
				<?php
			}else{
				?>	
                    <div id="setting-error-settings_updated" class="failed settings-error notice is-dismissible"> <p><strong>Niste uspeli da obrisete tabelu. Error:<?php echo  $conn->last_error ?></strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
                    </div>
				<?php
			}

		
		}
	}else{
		// If default is set, use wpdb object
		$result = $wpdb->get_results("
			SELECT  table_schema as database_name,
					table_name, table_comment
				from information_schema.tables
				where table_type = 'BASE TABLE'
			    and table_schema not in ('information_schema','mysql','performance_schema','sys')
				and table_name like '{$wpdb->prefix}pikto_%'
				order by database_name, table_name;
		");
		if (isset($_POST['pt_truncatetable'])){
			$tablename 		= $_POST['pt_tablename'];
			$databasename           = $_POST['pt_databasename'];
			
			$query = $wpdb->query("TRUNCATE TABLE {$databasename}.{$tablename}");
			if($query){
				?>
					<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> <p><strong>Uspesno ste obrisali tabelu: <?php echo $databasename .  '.' . $tablename ?>.</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
				<?php
			}else{
				?>	
                    <div id="setting-error-settings_updated" class="failed settings-error notice is-dismissible"> <p><strong>Niste uspeli da obrisete tabelu.  Error:<?php echo $wpdb->last_error ?></strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
                    </div>
				<?php
			}
		}
		if (isset($_POST['pt_droptable'])) {
			$tablename 				= $_POST['pt_tablename'];
			$databasename           = $_POST['pt_databasename'];
			
			
			$query = $wpdb->query("DROP TABLE {$databasename}.{$tablename}");
			if($query){
				?>
					<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> <p><strong>Uspesno ste obrisali podatke iz tabele: <?php echo $databasename .  '.' . $tablename ?>.</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
				<?php
			}else{
				?>	
                    <div id="setting-error-settings_updated" class="failed settings-error notice is-dismissible"> <p><strong>Niste uspeli da obrisete podatke iz tabele.  Error:<?php echo $wpdb->last_error ?></strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
				<?php
			}

		
		}
	}
?><div class="wrap">
		<h1 class="wp-heading-inline"><?php echo __('Tables','pikto-tables') ?> <a href="admin.php?page=create_tables" class="page-title-action">Create new</a></h1>
                
                <h3>Current database selected: <?php echo (get_option("PTDB_DEFAULT") || !get_option("PTDB_NAME"))?DB_NAME:get_option("PTDB_NAME") ?></h3>
		
		<?php 
			if(!empty($result)){
			?>
			 	<table border="2" class="wp-list-table widefat fixed striped pages">
                    <thead>
				 		<tr>
				 			<th>Name</th>
				 			<th>Description</th>
						</tr>
	                </thead>
	                <tbody>
				 		<?php foreach ($result as $table): ?>
							<tr>
								<td class="title column-title has-row-actions column-primary page-title">
									<?php echo $table->database_name?>.<b><?php echo $table->table_name?></b>
									<div class="row-actions">
										<span class="view"><a href="admin.php?page=show_data&pt_table=<?php echo $table->database_name?>.<?php echo $table->table_name?>">Open
										</a> |</span>
										<span class="edit"><a href="admin.php?page=edit_tables&pt_table=<?php echo $table->database_name?>.<?php echo $table->table_name?>">Edit
										</a> |</span>
											
										<form method="post" class="pt_formtruncate" onsubmit ="return confirm('Da li ste sigurni da zelite da obrisete sve podatke iz tabele?');">
											<input type="hidden" name="pt_databasename" value="<?php echo $table->database_name?>">
											<input type="hidden" name="pt_tablename" value="<?php echo $table->table_name?>">
											<span><input type="submit" class="pt_linkbutton" name="pt_truncatetable" value="Truncate"> | </span>
										</form> 
											
										<form method="post" class="pt_formtruncate" onsubmit ="return confirm('Da li ste sigurni da zelite da obrisete tabelu?');">
											<input type="hidden" name="pt_databasename" value="<?php echo $table->database_name?>">
											<input type="hidden" name="pt_tablename" value="<?php echo $table->table_name?>">
											<span><input type="submit" class="pt_linkbutton" name="pt_droptable" value="Drop"> | </span>
										</form> 
									</div>
								</td>
				 				<td><i><?php echo $table->table_comment?></i></td>								
				 			</tr>
			 			<?php endforeach ?>
                    </tbody>
			 	</table> 
			<?php
			}else{
			?>
				<p>There are no tables created with plugin. You are one click away from doing so.</p>
			<?php
			}
		?>	
	</div>