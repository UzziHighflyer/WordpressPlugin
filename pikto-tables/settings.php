<?php 
	if(isset($_POST['pt_savecustom'])){
		if(isset($_POST['pt_dbhost']) && isset($_POST['pt_dbname']) && isset($_POST['pt_username']) &&  $_POST['pt_port']){
			$dbhost 	= $_POST['pt_dbhost'];
			$dbname 	= $_POST['pt_dbname'];
			$username       = $_POST['pt_username'];
			$password 	= $_POST['pt_password'];
			$port 		= $_POST['pt_port'];

			// Try connection
 			try{
 				$conn = new PDO("mysql:host={$dbhost}:{$port};dbname:{dbname}",$username,$password);

 				// Check if database exists
 				$query = $conn->prepare("SELECT schema_name FROM information_schema.schemata WHERE schema_name = :dbname");
				$query->execute(array(':dbname'=>$dbname));
				$db 	= $query->fetchAll(PDO::FETCH_OBJ);
				if(empty($db)){
					throw new PDOException("Database {$dbname} doesn't exist.");
				}
 				// Add new options to table and delete PTDB_DEFAULT
				update_option("PTDB_HOST", $dbhost);
				update_option("PTDB_NAME", $dbname);
				update_option("PTDB_USER", $username);
				update_option("PTDB_PASS", $password);
				update_option("PTDB_PORT", $port);
				delete_option("PTDB_DEFAULT");
                                ?>
                                    <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> <p><strong>Custom settings saved.</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
                                <?php
                                
			// If it doesn't work then display error message
			}catch(PDOException $e){
				?>
                                        <div id="setting-error-settings_updated" class="failed settings-error notice is-dismissible"> <p><strong>Connection failed: <?php echo $e->getMessage()?></strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
 				<?php
 			}


		}
	}

	if(isset($_POST['pt_testconnection'])){
		if(isset($_POST['pt_dbhost']) && isset($_POST['pt_dbname']) && isset($_POST['pt_username'])  && $_POST['pt_port']){
			$dbhost 	= $_POST['pt_dbhost'];
			$dbname 	= $_POST['pt_dbname'];
			$username       = $_POST['pt_username'];
			$password 	= $_POST['pt_password'];
			$port 		= $_POST['pt_port'];

			try{
				$conn = new PDO("mysql:host={$dbhost}:{$port};dbname:{$dbname}",$username,$password);
				// Check if database exists
				$query = $conn->prepare("SELECT schema_name FROM information_schema.schemata WHERE schema_name = :dbname");
				$query->execute(array(':dbname'=>$dbname));
				$db 	= $query->fetchAll(PDO::FETCH_OBJ);
				if(empty($db)){
					throw new PDOException("Database {$dbname} doesn't exist.");
				}
					?>
                                                <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> <p><strong>Database connection data is valid</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
                                        <?php

			}catch(PDOException $e){
				?>
                                        <div id="setting-error-settings_updated" class="settings-error notice is-dismissible"> <p><strong>Connection failed: <?php echo $e->getMessage()?></strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
				<?php
			}

		}
	}

	if(isset($_POST['pt_savedefault'])){
		add_option("PTDB_DEFAULT", "TRUE");
                ?>
                     <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> <p><strong>Settings saved.</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>                   
                <?php
	}
?>

<div class="wrap">
	<?php  
		// Get option, check if wordpress (default) database was set
		$result = get_option( "PTDB_DEFAULT");
		
		if($result){
			$database = DB_NAME;
		}else{
			$database =  get_option("PTDB_NAME");
                        if(!$database){
                            $database = DB_NAME;
                        }
		}
	?>
	<h1><?php echo __('Database settings','pikto-tables')?></h1>
	<h2><?php echo __('Current Database selected','pikto-tables')?>: <span style="font-weight: bold"><?php echo $database?></span></h2>
	<input type="radio" name="pt_database" value="default" id="pt_default" <?php echo ($database == DB_NAME && !isset($_POST['pt_testconnection']))?'checked':''?>> <?php echo __('Use Wordpress database (default)','pikto-tables')?>
	<input type="radio" name="pt_database" value="custom"  id="pt_custom" <?php echo  ($database == DB_NAME && !isset($_POST['pt_testconnection']))?'':'checked'?>>  <?php echo __('Use custom database','pikto-tables')?>

	<form method="POST" action="#" id="pt_defaultForm" <?php echo ($database== DB_NAME && !isset($_POST['pt_testconnection']))?'':'style="display: none;"'?>>
		<p class="submit">
			<input type="submit" value="Save changes" name="pt_savedefault" class="button button-primary">
		</p>	
	</form>

	<form  action="#" method="POST" id="pt_customForm" <?php echo ($database== DB_NAME && !isset($_POST['pt_testconnection']))?'style="display: none;"':''?>>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">	
						<label for="pt_dbhost" style="font-weight: bold">Database host </label>
					</th>
					<td>
						<input type="text" name="pt_dbhost" aria-describedby="dbhost-description" value="<?php echo (!empty(get_option( "PTDB_HOST"))?get_option( "PTDB_HOST"):((!empty($_POST['pt_dbhost']))?$_POST['pt_dbhost']:""));?>" required>
						<p class="description" id="dbhost-description">Enter host of Database e.g. Localhost</p> 
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="pt_dbname" style="font-weight: bold">Database Name </label>
					</th>	
					<td>	
						<input type="text" name="pt_dbname" aria-descibedby="dbname-description" value="<?php echo (!empty(get_option("PTDB_NAME"))?get_option( "PTDB_NAME"):((!empty($_POST['pt_dbname']))?$_POST['pt_dbname']:""));?>" required>
						<p class="description" id="dbname-description">Enter name of Database you wish to use</p>  
					</td>
				</tr>
				<tr>
					<th scope="row">	
						<label for="pt_username" style="font-weight: bold">Username </label>
					</th>
					<td>
						<input type="text" name="pt_username"  autocomplete="off"  value="<?php echo (!empty(get_option("PTDB_USER"))?get_option( "PTDB_USER"):((!empty($_POST['pt_username']))?$_POST['pt_username']:""));?>"  required>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="pt_password" style="font-weight: bold">Password </label>
					</th>
					<td>	
						<input type="password" name="pt_password"  autocomplete="off" value="<?php echo (!empty(get_option("PTDB_PASS"))?get_option( "PTDB_PASS"):((!empty($_POST['pt_password']))?$_POST['pt_password']:""));?>"> 
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="pt_port" style="font-weight: bold">Port </label>
					</th>
					<td>
						<input type="number" name="pt_port" class="small-text" aria-describedby="port-description" value="<?php echo !empty(get_option("PTDB_PORT"))?get_option("PTDB_PORT"):"3306"?>" required> 
						<p class="description" id="port-description">Database connection port, Default is 3306.</p> 
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit">
			
			<input type="submit" name="pt_testconnection" class="button" id="testConnection" value="Test Connection">
			<input type="submit" value="Save changes" name="pt_savecustom" class="button button-primary">
		</p>
	</form> 
		
</div>