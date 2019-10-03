	<div class="wrap">            
		<?php
            // Declare createtable so it doesnt throw error later in file 	
            $createtable = "";  
            // Check if createtable 
			if(isset($_POST['pt_createtable'])){	
				if(isset($_POST['pt_tablename']) && isset($_POST['pt_dbengine'])){
					$post               = $_POST;
                    $getptrow           = $post;
                    unset($getptrow['pt_tablename']);
                    unset($getptrow['pt_tabledescription']);
                    unset($getptrow['pt_dbengine']);
                    unset($getptrow['pt_createtable']);
                    $getptrows          = array_keys($getptrow);
                                        
                                        
					$numberofrows       = count($post) - 4;
					$tablename          = htmlspecialchars($post['pt_tablename']);
					$tablename 	    	= str_replace(" ", "_", $tablename);
					$tabledescription   = htmlspecialchars($post['pt_tabledescription']);
					$dbengine 	    	= $post['pt_dbengine'];
		
					global $wpdb;   
					$prefix 	= $wpdb->prefix;                                                

					$result 	= get_option("PTDB_DEFAULT");
					$dbhost 	= get_option("PTDB_HOST");
					$dbname 	= get_option("PTDB_NAME");
					$port 		= get_option("PTDB_PORT");
					$username   = get_option("PTDB_USER");
					$password 	= get_option("PTDB_PASS");
					// Ako je izabrana wordpress baza za pravljenje tabela
					if((!empty($dbhost) && !empty($dbname) && !empty($username)) || !$result){
						$wpdb       = new wpdb($username, $password, $dbname, $dbhost);
					}else{
						$dbname 	= DB_NAME;
					}

                    // If no rows are added, declare  empty foreignkey and index varialbes to escape error 
                    $foreignkey 	= "";
                    $index 			= "";
                        if ($numberofrows > 0) {
							// Take post array and only leave columns 
							$newpostarray = array_slice($post, 3);
							unset($newpostarray["pt_createtable"]);

							// Turn to numeric array
							$newpostarray = array_values($newpostarray);
                            $sqlcolumn    = [];
                            $uniqueindex  = [];
								
							// Prepare every column for table
							for ($i=0; $i < count($newpostarray); $i++) { 
								$columnname 			= str_replace(" ","_", $newpostarray[$i]['columnname']);
								$notnullcolumn  		= (isset($newpostarray[$i]['nn']) || isset($newpostarray[$i]['ai']))? 'NOT NULL':'NULL';
								$unsignedcolumn 		= isset($newpostarray[$i]['un'])? 'UNSIGNED':'';
								$zerofillcolumn 		= isset($newpostarray[$i]['zf'])? 'ZEROFILL':'';
								$autoincrementcolumn    = isset($newpostarray[$i]['ai'])? 'AUTO_INCREMENT':'';
								$defaultcolumn	 		= (!empty($newpostarray[$i]['default']) && !isset($newpostarray[$i]['ai']))? "DEFAULT '{$newpostarray[$i]['default']}'":'';
                                $datatype               = (isset($newpostarray[$i]['datatype']) && $newpostarray[$i]['datatype'] != 'Relation')?$newpostarray[$i]['datatype']:'int(12)';
                                $uniqueindex[] 			= (isset($newpostarray[$i]['uq']) && isset($newpostarray[$i]['columnname']))?",UNIQUE INDEX `{$columnname}_UNIQUE` (`$columnname` ASC)":"";
                                $size                   = (!empty($newpostarray[$i]['size']) && $newpostarray[$i]['datatype'] != "Relation" && $newpostarray[$i]['datatype'] != "ENUM")?"({$newpostarray[$i]['size']})":"";
                                $foreignkey             = ($newpostarray[$i]['datatype'] === "Relation" && isset($newpostarray[$i]['size']))?",FOREIGN KEY ({$columnname}) REFERENCES {$newpostarray[$i]['size']}(ID)":"";
								if($newpostarray[$i]['datatype']=== "ENUM" && isset($newpostarray[$i]['size'])){
                                    $explode    = explode(',',$newpostarray[$i]['size']);
                                    foreach($explode as &$value){
                                        $value = "'". $value ."'";
                                    }
                                    $enum = implode(',',$explode);
                                    $enum = "(" . $enum . ")";
                                                                    
                                }else{
                                    $enum   = "";
                                }
                                $sqlcolumn[$i]  		= "{$columnname} {$datatype} {$size} {$enum} {$unsignedcolumn} {$notnullcolumn}  {$zerofillcolumn} {$autoincrementcolumn} {$defaultcolumn},";
                                                                	
							}
                            $columns        = implode("",$sqlcolumn);
                            $index 			= implode("",$uniqueindex);

                    	}
                        if(!isset($columns)){
                            $columns = "";
                        }
                        // Create table with id column as primary key and auto increment
                        $sql = "CREATE table {$dbname}.{$prefix}pikto_{$tablename}(
							`ID` int  NOT NULL  AUTO_INCREMENT,
                                {$columns}
							PRIMARY KEY(`ID`)
                                {$foreignkey}
                                {$index}
							) ENGINE = $dbengine COMMENT = %s;"; 
						
						
						// Table creation query
						$createtable = $wpdb->query(
							$wpdb->prepare(
								$sql,
								$tabledescription
							)
						);

						if($createtable){ 
							?>
                                <div id='setting-error-settings_updated' class='updated settings-error notice is-dismissible'> <p><strong>Uspesno kreirana tabela: <?php echo  $dbname . '.'. $prefix . 'pikto_' .$tablename ?></strong></p><button type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button><div><a class='button-primary' href='javascript:window.location.href=window.location.href'>Create more</a> <a class='button' href='admin.php?page=pikto_tables'>View tables</a></div> </div>                  
							<?php
						}else if(!$createtable){
                            ?>
                                <div id="setting-error-settings_updated" class="settings-error notice is-dismissible"> <p><strong><?php echo $wpdb->last_error ?></strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
                        	<?php
                        }
                }
            }               
		?>                                                                        
			<h1 class="wp-heading-inline"><?php echo __('Create a new table','pikto-tables') ?></h1>
            <h3>Current database selected: <?php echo (get_option("PTDB_DEFAULT") || !get_option("PTDB_NAME"))?DB_NAME:get_option("PTDB_NAME") ?></h3>
	<?php
    if(!$createtable){    
    ?>
        <form id="pt_createTableForm" method="post" action="#">
			<table class="form-table" >	
				<tbody>
					<tr>
						<th scope="row">	
							<label for="pt_tablename" style="font-weight: bold"><?php echo __('Table Name','pikto-tables') ?></label>
						</th>
						<td>
							<input type="text" name="pt_tablename" aria-describedby="dbhost-description" value="<?php echo (!empty($_POST['pt_tablename']) &&  !$createtable )?$_POST['pt_tablename']:''?>" required>
							<p class="description" id="dbhost-description">Enter name of the table.</p> 
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="pt_tabledescription" style="font-weight: bold"><?php echo __('Table Description','pikto-tables') ?> </label>
						</th>	
						<td>	
							<input type="text" name="pt_tabledescription" aria-descibedby="dbname-description" value="<?php echo (!empty($_POST['pt_tabledescription'])  && !$createtable )?$_POST['pt_tabledescription']:''?>">
							<p class="description" id="dbname-description">Enter additional information about your table.</p>  
						</td>
					</tr>
					<tr>
						<th scope="row">	
							<label for="pt_dbengine" style="font-weight: bold">DB Engine</label>
						</th>
						<td>
							<select name="pt_dbengine">
								<option selected>InnoDB</option>
								<option>MyISAM</option>
							</select>
							<p class="description" id="port-description">DB engine, InnoDB is selected by default.</p> 
						</td>
					</tr>
				</tbody>
                        </table>
                        

			<table border="2" style="border-collapse: collapse;" id="pt_createTable">
				<caption><small>*<b>Primary key</b> column is <b>auto created</b> with name <b>ID</b>, autoincrement</small></caption>
				<caption><small>*In the <b>size column</b> enter <b>enum</b> values separated with comma for <b>ENUM</b> type</small></caption>
                <caption><small>*If Relation Datatype is set, type of data will be set to bigint by default and Size field will be used for table to reference. Note that field that is being referenced must be unique.</small></caption>
                <tbody>
					<tr id="pt_row-1">
						<th>Column Name</th>
						<th>Datatype</th>
						<th>Size</th>
						<th>AI</th>
						<th>NN</th>
						<th>UQ</th>
						<th>UN</th>
						<th>ZF</th>						
						<th>Default/Expression</th>
                                                <th></th>
					</tr>
                                <?php
                if(isset($_POST['pt_createtable'])){
                                    for($i=0,$count = count($getptrows);$i < $count;$i++){
                                       
                                    ?>
                    <tr id="<?php echo "{$getptrows[$i]}"?>" class="pt_row">
                        <td><input type="text" name="<?php echo "{$getptrows[$i]}[columnname]"?>" value="<?php echo (!empty($_POST["{$getptrows[$i]}"]['columnname'])&& !$createtable )?$_POST["$getptrows[$i]"]['columnname']:''?>" required></td>
						<td>
                            <select id="selectType" required name="<?php echo "{$getptrows[$i]}[datatype]"?>" onchange="">
								<option <?php echo($_POST["$getptrows[$i]"]['datatype'] == "INT")?"selected":"" ?>>INT</option>
								<option <?php echo($_POST["$getptrows[$i]"]['datatype'] == "VARCHAR")?"selected":"" ?>>VARCHAR</option>
								<option <?php echo($_POST["$getptrows[$i]"]['datatype'] == "DATETIME")?"selected":"" ?>>DATETIME</option>
								<option <?php echo($_POST["$getptrows[$i]"]['datatype'] == "BLOB")?"selected":"" ?>>BLOB</option>	
								<option <?php echo($_POST["$getptrows[$i]"]['datatype'] == "DATE")?"selected":"" ?>>DATE</option>
								<option <?php echo($_POST["$getptrows[$i]"]['datatype'] == "BINARY")?"selected":"" ?>>BINARY</option>
								<option <?php echo($_POST["$getptrows[$i]"]['datatype'] == "TINYINT")?"selected":"" ?>>TINYINT</option>
								<option <?php echo($_POST["$getptrows[$i]"]['datatype'] == "SMALLINT")?"selected":"" ?>>SMALLINT</option>
								<option <?php echo($_POST["$getptrows[$i]"]['datatype'] == "MEDIUMINT")?"selected":"" ?>>MEDIUMINT</option>
								<option <?php echo($_POST["$getptrows[$i]"]['datatype'] == "BIGINT")?"selected":"" ?>>BIGINT</option>
								<option <?php echo($_POST["$getptrows[$i]"]['datatype'] == "FLOAT")?"selected":"" ?>>FLOAT</option>
								<option <?php echo($_POST["$getptrows[$i]"]['datatype'] == "DOUBLE")?"selected":"" ?>>DOUBLE</option>
								<option <?php echo($_POST["$getptrows[$i]"]['datatype'] == "DECIMAL")?"selected":"" ?>>DECIMAL</option>
								<option <?php echo($_POST["$getptrows[$i]"]['datatype'] == "DATETIME")?"selected":"" ?>>DATETIME</option>
								<option <?php echo($_POST["$getptrows[$i]"]['datatype'] == "TIMESTAMP")?"selected":"" ?>>TIMESTAMP</option>
								<option <?php echo($_POST["$getptrows[$i]"]['datatype'] == "TIME")?"selected":"" ?>>TIME</option>
								<option <?php echo($_POST["$getptrows[$i]"]['datatype'] == "YEAR")?"selected":"" ?>>YEAR</option>
								<option <?php echo($_POST["$getptrows[$i]"]['datatype'] == "CHAR")?"selected":"" ?>>CHAR</option>
								<option <?php echo($_POST["$getptrows[$i]"]['datatype'] == "TEXT")?"selected":"" ?>>TEXT</option>
								<option <?php echo($_POST["$getptrows[$i]"]['datatype'] == "TINYTEXT")?"selected":"" ?>>TINYTEXT</option>
								<option <?php echo($_POST["$getptrows[$i]"]['datatype'] == "MEDIUMTEXT")?"selected":"" ?>>MEDIUMTEXT</option>
								<option <?php echo($_POST["$getptrows[$i]"]['datatype'] == "LONGTEXT")?"selected":"" ?>>LONGTEXT</option>
								<option <?php echo($_POST["$getptrows[$i]"]['datatype'] == "ENUM")?"selected":"" ?>>ENUM</option>
                                <option <?php echo($_POST["$getptrows[$i]"]['datatype'] == "Relation")?"selected":"" ?>>Relation</option>
							</select>
						</td>
						<td><input type="text" name="<?php echo "$getptrows[$i][size]"?>" id="size1"  min="0" value="<?php echo (!empty($_POST["{$getptrows[$i]}"]['size'])&&  !$createtable )?$_POST["{$getptrows[$i]}"]['size']:''?>"></td>
						<td><input type="checkbox" name="<?php echo "$getptrows[$i][ai]"?>" <?php echo (isset($_POST["{$getptrows[$i]}"]['ai'])&& !$createtable )?'checked':''?>></td>
						<td><input type="checkbox" name="<?php echo "$getptrows[$i][nn]"?>" <?php echo (isset($_POST["{$getptrows[$i]}"]['nn'])&&  !$createtable )?'checked':''?>></td>
						<td><input type="checkbox" name="<?php echo "$getptrows[$i][uq]"?>" <?php echo (isset($_POST["{$getptrows[$i]}"]['uq'])&& !$createtable )?'checked':''?>></td>
						<td><input type="checkbox" name="<?php echo "$getptrows[$i][un]"?>" <?php echo (isset($_POST["{$getptrows[$i]}"]['un'])&&  !$createtable )?'checked':''?>></td>
						<td><input type="checkbox" name="<?php echo "$getptrows[$i][zf]"?>" <?php echo (isset($_POST["{$getptrows[$i]}"]['zf'])&& !$createtable )?'checked':''?>></td>
						<td><input type="text" name="<?php echo "$getptrows[$i][default]"?>" value="<?php echo (!empty($_POST["{$getptrows[$i]}"]['default']) && !$createtable )?$_POST["{$getptrows[$i]}"]['default']:''?>"></td>
                                                <td><button id="pt_deleteRowButton" class="deletebutton button button-secondary" type="button"><span>&#8722;</span></button></td>
					</tr>
                                <?php
                                    }
                                }
                                ?>
                                    	
				</tbody>
			</table>
			<button style="margin-top:10px" id="pt_addRowButton" class="button button-primary" type="button"><span>&#43;</span></button>
			<br>
			<button style="margin-top:10px" class="button" type="reset">Cancel</button>
			<p class="submit">
				<input type="submit" value="Create" name="pt_createtable" class="button button-primary">
			</p>
				
		</form>
    <?php
    }
    ?>
        </div>