<?php
	global $wpdb;
	if(isset($_GET['pt_table'])){
		// Get and process table name from url
		$fulltablename          = htmlspecialchars($_GET['pt_table'],ENT_QUOTES);
		$divide 		= explode('.', $fulltablename);
		$databaseName           = $divide[0];
		$tablename 		= htmlspecialchars($divide[1],ENT_QUOTES);
	}
	// Get options to check what db connection method to use
	$option 	= get_option("PTDB_DEFAULT");
	$dbhost 	= get_option("PTDB_HOST");
	$dbname 	= get_option("PTDB_NAME");
	$username	= get_option("PTDB_USER");
	$password 	= get_option("PTDB_PASS");
	// Check if user has entered data for database connection, if yes, use pdo, if not use $wpdb object
	if(!$option || (!empty($dbhost) && !empty($dbname) && !empty($username))){
		try{
			// Connect to database 
			$conn 	= new PDO("mysql:host={$dbhost};dbname:{$databaseName}",$username,$password);
			$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}catch(PDOException $e){
			// Throw error if not able to connect
			echo $e->getMessage();
		}
		

		if(isset($_POST['drop'])){	
			// Delete row if 'Delete row' is clicked
			$rowid		= $_POST['rowid'];
			$columnname     = $_POST['columnname'];
			try{
                $drop           = $conn->query("DELETE FROM {$fulltablename} WHERE {$columnname} = {$rowid}");
                ?>
                    <div id='setting-error-settings_updated' class='updated settings-error notice is-dismissible'> <p><strong>Uspesno ste obrisali red u tabeli.</strong></p><button type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button></div>                
                <?php
            } catch (PDOException $e) {
                ?>
                    <div id='setting-error-settings_updated' class='settings-error notice is-dismissible'> <p><strong>Neuspesno ste obrisali red u tabeli. Error:<?php echo  $e->getMessage(); ?></strong></p><button type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button></div>                
                <?php
            }
                           
		}

		if(isset($_POST['pt_addrow'])){
		// Add row to table
			
			// Get values from $_POST into new array $post
			$post             = array_values($_POST);
			
			// Only add array with values to new array $array 
			$array            = $post[0];
			$numberofdata     = count($array);

			// Add question marks for prepare based on how many array elements there are
			$arrayfill        = array_fill(0,$numberofdata,'?');
			$arrayfill        = implode(',',$arrayfill);

			// Insert row into table
			$sql  	= "INSERT INTO {$fulltablename} VALUES({$arrayfill})";
			$query  = $conn->prepare($sql);
                        
			// Bind values for question mark parameters
			foreach ($array as $key => $value) {
				$key = $key + 1;
				if($value == 'null'){
					$query->bindValue($key,null,PDO::PARAM_INT);
				}else{
					$query->bindValue($key,$value,PDO::PARAM_STR);
				}
			}

			// Execute the query
			
            try{
                $addrow = $query->execute();
                if($addrow){
                ?>
                    <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> <p><strong>Uspesno ste dodali red.</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>   
                <?php
                }else{
                ?>
                    <div id="setting-error-settings_updated" class=" settings-error notice is-dismissible"> <p><strong>Dodavanje reda nije uspelo.</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
                <?php
                }
                            
            }catch (PDOException $e) {
            ?>
                <div id="setting-error-settings_updated" class=" settings-error notice is-dismissible"> <p><strong>Error: <?php echo $e->getMessage(); ?></strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
            <?php
            }
		}
		if(isset($_POST['pt_updatefield'])){
			$columnname		= $_POST['columnname'];
			$rowid			= $_POST['rowid'];
			$field 			= $_POST['pt_field'];
			$sql 			= "UPDATE {$fulltablename} SET {$columnname} = :field WHERE ID = {$rowid}";
			$query 			= $conn->prepare($sql);
			try{
				$updaterow 	= $query->execute(array(":field"=>$field));
				if($updaterow){
					?>
						<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> <p><strong>Uspesno ste azurirali polje.</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
					<?php
				}else{
					?>
						<div id="setting-error-settings_updated" class=" settings-error notice is-dismissible"> <p><strong>Niste uspeli da azurirate polje.</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
					<?php
				}
			}catch(PDOException $e){
				?>
					<div id="setting-error-settings_updated" class=" settings-error notice is-dismissible"> <p><strong>Error: <?php echo $e->getMessage();  ?></strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
				<?php
			}
		}

		// Check if limit was set
		if(isset($_GET['limit'])){
			// Get limit variable from $_GET array
			$limit 		= ($_GET['limit']!= 'all')?$_GET['limit']:'';

			// Prepare limit sql for $conn->query
			$limitsql 	= (!empty($limit))?"LIMIT {$limit}":"";
			
			// Check if offset is set
			if(isset($_GET['pagination'])){
				$offset 	= ($_GET['pagination'] * $limit) - $limit;

				// Prepare offset sql for $conn->query 
				$offsetsql 	= (!empty($offset))?"OFFSET {$offset}":"";
			}else{
				// If pagination is not set, no need for offset query 
				$offsetsql  = "";
			}
		}else{
			// If limit is not set, no need for limit query
			$limit     = 0;
			$limitsql  = "";

			// If pagination is not set, no need for offset query
			$offsetsql  = "";
		}

		// Get all columns from table, if table name not valid, stop script and display message
		try{
			$query1 		= $conn->query("SHOW COLUMNS FROM {$fulltablename}");
		}catch(PDOException $e){
			die('<a href="admin.php?page=pikto_tables">&larr;Go back</a> <h2>Requested table not found</h2>');
		}

		$columns 		= $query1->fetchAll(PDO::FETCH_OBJ);


		// Count columns returned
		$numberofcolumns = count($columns);
		
		// Create array for column field names
		$columnsArray   = [];
		for($c = 0; $c < $numberofcolumns; $c++){
			$columnsArray[]  = $columns[$c]->Field;
		}

		// Create string out of columnsArray for sql match 
		$columnsString = implode(',', $columnsArray);

		// Declare search sql  as empty string
		$searchsql 	= "";
		

		// Check if search button was clicked and search form submitted
		if(isset($_POST['searchbutton'])){
			// Check if value from form is set and not empty
			if(isset($_POST['search']) && !empty($_POST['search'])){
				// Create sql query for search with match() against() sql function
				$search 	= $_POST['search'];
				$search 	= $wpdb->esc_like( $search );
				$search 	= "%" . $search . "%";
				$searchsql  = "WHERE CONCAT($columnsString) LIKE '$search'";
				
			}
		}
		
		// Get Number of rows in table
		$count 		= $conn->query("SELECT COUNT(*) FROM {$fulltablename}");
		$countdata 	= $count->fetchAll(PDO::FETCH_ASSOC);
		
		// Number of rows in table
		$countdata  = $countdata[0]["COUNT(*)"];

		// Check if limit is larger than number of data returned, if yes, no need for pagination
		if($limit > $countdata){
			$offsetsql = "";
		}  

		
		// Get rows from table
		$getsql	= "SELECT * FROM {$fulltablename} {$searchsql} {$limitsql} {$offsetsql}";
		try{
			$get  	= $conn->query($getsql);
			$data 	= $get->fetchAll(PDO::FETCH_ASSOC);
			// Number of rows returned from sql
			$numberofdata = count($data);
		}catch(PDOException $e){
			?>
				<div id="setting-error-settings_updated" class="settings-error notice is-dismissible"> <p><strong><?php echo $e->getMessage(); ?></strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
			<?php
		}	
	// $wpdb object is used when data for database connection is not set
	}else{
		if(isset($_POST['drop'])){	
			// Delete row if 'Delete row' is clicked
			$rowid		= $_POST['rowid'];
			$columnname     = $_POST['columnname'];
			$drop 		= $wpdb->query("DELETE FROM {$fulltablename} WHERE {$columnname} = {$rowid}");
			if ($drop) {
				?>
				<div id='setting-error-settings_updated' class='updated settings-error notice is-dismissible'> <p><strong>Uspesno ste obrisali red u tabeli.</strong></p><button 	type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button>
				</div>
				<?php
			}else{
				?>
				<div id='setting-error-settings_updated' class='settings-error notice is-dismissible'> <p><strong>Neuspesno ste obrisali red u tabeli. Error:
					<?php echo  $wpdb->last_error;?></strong></p><button type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button>
				</div>
				<?php
			}
		}
		if(isset($_POST['pt_addrow'])){
		// Add row to table
			
			// Get values from $_POST into new array $post
			$post 			= array_values($_POST);
			
			// Only add array with values to new array array 
			$array  		= $post[0];
                        
			$numberofdata 	= count($array);
		
			// Prepare placeholders for sql query
			$arrayfill 		= [];
			foreach ($array as $key => $value) {
				$key = $key+1;
				if($value == 'null'){
					$arrayfill[$key] = "%d";
				}else{
					$arrayfill[$key] = "%s";
				}	
			}
			$arrayfill  	= implode(",",$arrayfill);

			// Insert row into table
			$sql  	= "INSERT INTO {$fulltablename} VALUES({$arrayfill})";
			$query  = $wpdb->query($wpdb->prepare($sql,$array));

			if($query){
				?>
				<div id='setting-error-settings_updated' class='updated settings-error notice is-dismissible'> <p><strong>Uspesno ste dodali red u tabeli.</strong></p><button 
					type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button>
				</div>
				<?php
			}else{
				?>
				<div id='setting-error-settings_updated' class='settings-error notice is-dismissible'> <p><strong>Niste uspeli da dodate red u tabeli. Error:
					<?php echo $wpdb->last_error; ?></strong></p><button type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button>
				</div>
				<?php
			}

		}

		if(isset($_POST['pt_updatefield'])){
			$columnname		= $_POST['columnname'];
			$rowid			= $_POST['rowid'];
			$field 			= $_POST['pt_field'];
			$sql 			= "UPDATE {$fulltablename} SET {$columnname} = %s WHERE ID = {$rowid}";
			$updaterow 		= $wpdb->query($wpdb->prepare($sql,$field));

			if($updaterow){
			?>
				<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> <p><strong>Uspesno ste azurirali polje.</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
			<?php
			}else{
			?>
				<div id="setting-error-settings_updated" class=" settings-error notice is-dismissible"> <p><strong>Niste uspeli da azurirate polje. Error: <?php echo $wpdb->last_error ?></strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
			<?php
			}
			
		}

		// Check if limit was set
		if(isset($_GET['limit'])){
			// Get limit variable from $_GET array
			$limit 		= ($_GET['limit']!= 'all')?$_GET['limit']:'';

			// Prepare limit sql for $conn->query
			$limitsql 	= (!empty($limit))?"LIMIT {$limit}":"";
			
			// Check if offset is set
			if(isset($_GET['pagination'])){
				$offset 	= ($_GET['pagination'] * $limit) - $limit;

				// Prepare offset sql for $conn->query 
				$offsetsql 	= (!empty($offset))?"OFFSET {$offset}":"";
			}else{
				// If pagination is not set, no need for offset query 
				$offsetsql  = "";
			}
		}else{
			// If limit is not set, no need for limit query
			$limit     = 0;
			$limitsql  = "";

			// If pagination is not set, no need for offset query
			$offsetsql  = "";
		}
		// Get columns from table, if query not successfull, stop execution of script
		$columns 		= $wpdb->get_results("SHOW COLUMNS FROM {$fulltablename}",OBJECT_K);
		if($wpdb->last_error){
			die('<a href="admin.php?page=pikto_tables">&larr;Go back</a> <h2>Requested table not found</h2>');
		}
        $columns = array_values($columns);

		// Count columns returned
		$numberofcolumns = count($columns);
		
		// Create array for column field names
		$columnsArray   = [];
		for($c = 0; $c < $numberofcolumns; $c++){
			$columnsArray[]  = $columns[$c]->Field;
		}

		// Create string out of columnsArray for sql match 
		$columnsString = implode(',', $columnsArray);

		// Declare search sql  as empty string
		$searchsql 	= "";
		
		// Check if search button was clicked and search form submitted
		if(isset($_POST['searchbutton'])){
			// Check if value from form is set and not empty
			if(isset($_POST['search']) && !empty($_POST['search'])){
				// Create sql query for search with match() against() sql function
				$search 	= $_POST['search'];
				$search 	= $wpdb->esc_like( $search );
				$search 	= "%" . $search . "%";
				$searchsql  = "WHERE CONCAT($columnsString) LIKE '$search'";
				
			}
		}
		
		// Get Number of rows in table
		$countdata 		= $wpdb->get_results("SELECT COUNT(*) FROM {$fulltablename}",ARRAY_A);
		
		// Number of rows in table
		$countdata  = $countdata[0]["COUNT(*)"];

		// Check if limit is larger than number of data returned, if yes, no need for pagination
		if($limit > $countdata){
			$offsetsql = "";
		}  
		
		// Get rows from table and fetch them
		$data  	= $wpdb->get_results("SELECT * FROM {$fulltablename} {$searchsql} {$limitsql} {$offsetsql}",ARRAY_A);
		if($wpdb->last_error){
			?>
				<div id='setting-error-settings_updated' class='settings-error notice is-dismissible'> <p><strong>Greska u mysql kodu. Error: <?php echo $wpdb->last_error; ?>
				</strong></p><button type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button>
				</div>
			<?php
		}	
		// Number of rows returned from sql
		$numberofdata = count($data);
	}
?><div class="wrap">
	<a href="admin.php?page=pikto_tables">&larr;Go back</a>
	<h1><?php echo __('Table data for','pikto-tables') ?><b> <?php echo isset($tablename)?$tablename:''?></b></h1>
        <h3>Current database selected: <?php echo (get_option("PTDB_DEFAULT") || !get_option("PTDB_NAME"))?DB_NAME:get_option("PTDB_NAME") ?></h3>

	<form method="post">
		<p class="search-box">
			<input type="search" name="search" class="post-search-input" required>
			<input type="submit" class="button" name="searchbutton" id="search-submit" value="Search Table">		
		</p>
	</form>
	
	<?php echo __('Number of rows','pikto-tables') ?>
	<select class="small-text" onchange="window.location.href +='&limit='+this.value">
		<option value="all">All</option>
		<option value="10"  <?php echo(isset($_GET['limit']) && $_GET['limit']=="10" )?'selected':''?>>10</option>
		<option value="30"  <?php echo(isset($_GET['limit']) && $_GET['limit']=="30" )?'selected':''?>>30</option>
		<option value="50"  <?php echo(isset($_GET['limit']) && $_GET['limit']=="50" )?'selected':''?>>50</option>
		<option value="100" <?php echo(isset($_GET['limit']) && $_GET['limit']=="100")?'selected':''?>>100</option>
	</select>
	<?php
            if(isset($_POST['searchbutton'])){
			// Check if value from form is set and not empty
			if(isset($_POST['search']) && !empty($_POST['search'])){
				// Create sql query for search with match() against() sql function
				if(strpos($search, "%")!== false){
					$search 	= str_replace("%", "", $search);
				}
				?>	
					<form method="post">
						<input type="hidden" name="search" value="">
						<h3>Searching for: <button class='button' name="searchbutton"><?php echo $search ?></button></h3>
					</form>
				<?php
			}
		}
	?>
		<table border="2" id="pt_showData" class="wp-list-table widefat fixed striped pages">
			<caption>*<b>Double click</b> on field with data to edit</caption>
			<tbody>
				<tr>
					<?php
					echo "<th>#</th>";
					// Show all columns inside <th> tag	
					for($j = 0;$j < $numberofcolumns;$j++){
                    ?>
                        <th class="pt_datarow"><b><?php echo $columns[$j]->Field?></b> - <?php echo $columns[$j]->Type ?></th>
					<?php 
					}	
					?>
					<th>Action</th>
				</tr>
			<?php

			// Create <tr> for every row returned from table
			for($i = 0;$i < $numberofdata;$i++){
				$value = array_values($data[$i]);
				
			?>		
				<tr>
					<td class="numberofvalue"><?php echo $i + 1?></td>
					<?php

					// Crete <td> for every column in table
					for($n = 0;$n < $numberofcolumns;$n++){
						
					?>
							<td class="title column-title has-row-actions column-primary page-title"><form method="post"><input type="hidden" name="rowid" 
								value="<?php echo $value[0]?>"> <input type="hidden" name="columnname" value="<?php echo $columns[$n]->Field?>"><span data-editable><?php echo 
								(!empty($value[$n]) || $value[$n] === 0)?$value[$n]:"-"?></span> <button  class="button" name="pt_updatefield" style="display: none;">&#10003;</button></form>
							</td>
					<?php 
						
					}	
					?>
					<td>
						<form method="post" onsubmit="return confirm('Je l ste sigurni da hocete da obrisete red?')">
							<input type="hidden" name="columnname" value="<?php echo $columns[0]->Field?>">
							<input type="hidden" name="rowid" value="<?php echo $value[0]?>">
							<input type="submit" name="drop" class="button" value="Delete row">
						</form>
					</td>
				</tr>
			<?php
			}
			?>	

			</tbody>
		</table>

		<button style="margin-top:10px" id="pt_addRowButtonData" class="button button-primary" type="button"><span>&#43;</span></button>

		<?php 

			// Check if limit is equal or larger than minimal limit 
			if($limit >= 10){
				// Check if number of data in table is larger than row limit, if yes, add pagination buttons
				if($countdata > $limit){
					$pagination  = $countdata/$limit;
					echo '<ul class="pt_pagination">';
					// Add pagination button for every ceiling of data count / limit 
					for($i = 0; $i < ceil($pagination);$i++){
						?>
							<li><a <?php echo (isset($_GET['pagination']) && $_GET['pagination'] == $i +1 )?'class="button-primary"':''?> onclick="window.location.href +='&pagination='+<?php echo $i+1?>" class="button"><?php echo$i+1?></a></li>
						<?php
					}
					echo '</ul>';
				}
			}
		?> 
	
</div>


