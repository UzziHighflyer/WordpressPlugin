<?php
	if(isset($_GET['pt_table']) && !empty($_GET['pt_table'])){		
		$fulltablename          = htmlspecialchars($_GET['pt_table']);
		$divide 		        = explode('.', $fulltablename);
		if(count($divide) == 2){
            $databaseName           = $divide[0];
		    $tablename 		        = htmlspecialchars($divide[1]);
        }else{
            $databaseName           = "";
            $tablename              = "";
        }

        // Define global wpdb object
        global $wpdb;
        $prefix     = $wpdb->prefix;
		// Get options from database 
		$option 	= get_option("PTDB_DEFAULT");
		$dbhost 	= get_option("PTDB_HOST");
		$dbname 	= get_option("PTDB_NAME");
		$username	= get_option("PTDB_USER");
		$password 	= get_option("PTDB_PASS");
		// Check if default database is set, if not, use custom database connection
		if(!$option || (!empty($dbhost) && !empty($dbname) && !empty($username))){
            $wpdb   = new wpdb($username, $password, $dbname, $dbhost);
        }
			 
		$sql 	    = "SELECT  *
    					from information_schema.tables
    					where table_type = 'BASE TABLE'
    					and table_schema not in ('information_schema','mysql',
    						                             'performance_schema','sys')
                        and table_schema = %s
    					and table_name = %s;";
		// Get and fetch table as OBJECT 
		$result         = $wpdb->get_results($wpdb->prepare($sql,[$databaseName,$tablename]),OBJECT);

		// Check if result was returned
		if(!empty($result)){
			$sql1 	    = "SELECT *
							FROM INFORMATION_SCHEMA.COLUMNS
							WHERE TABLE_NAME = %s
                            AND TABLE_SCHEMA = %s";
			$columns    = $wpdb->get_results($wpdb->prepare($sql1,[$result[0]->TABLE_NAME,$databaseName]));
		}else{
			die("<a href='admin.php?page=pikto_tables'>&larr;Go back</a><div class='wrap'><h2>Requested table not found</h2></div>");
		}
    
        $tablenamewithoutprefix     = explode("pikto_",$result[0]->TABLE_NAME);
        $tablenamewithoutprefix     = $tablenamewithoutprefix[1];
        // Get index from table selected
        $index      = $wpdb->get_results("show index from {$databaseName}.{$tablename}");

		if(isset($_POST['edittable'])){
			$post = $_POST;
            $requesturi     = $_SERVER['REQUEST_URI'];
            $withoutget     = explode('/',$requesturi);
            $withoutget     = $withoutget[count($withoutget)-1];
            $withoutget     = explode('&' , $withoutget);
            $link           = $withoutget[0];                                
            
                // If name is editted
                if($result[0]->TABLE_NAME != $prefix .'pikto_'.$post['tablename']){
                    $newtablename       = str_replace(" ", "_", $post['tablename']);
                    $rename             = $wpdb->query("ALTER TABLE {$databaseName}.{$result[0]->TABLE_NAME} RENAME TO {$databaseName}.{$prefix}pikto_{$newtablename}");
                    $newtablename       = $prefix . "pikto_" . $newtablename;
                    $fulltablename      = $databaseName . '.' . $newtablename;
                    $tablename          = $newtablename;
                    $fulllink           = $link . "&pt_table=$databaseName.$newtablename";
                    if($rename){
                    ?>
                        <div id='setting-error-settings_updated' class='updated settings-error notice is-dismissible'> <p><strong>Uspesno ste promenili ime tabele. 
                            <?php echo $result[0]->TABLE_NAME . ' -> ' . $newtablename ;?> <a href="<?php echo $fulllink ?>">Go to renamed plugin.</a></strong></p><button type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button>
                        </div>
                    <?php
                    }
                    // If not successful
                    else{
                    ?>
                        <div id='setting-error-settings_updated' class='settings-error notice is-dismissible'> <p><strong>Niste uspeli da promenite ime tabele 
                            <?php echo $wpdb->last_error?></strong></p><button type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button>
                        </div>
                    <?php
                    }
                }
                // If comment is editted
                if($result[0]->TABLE_COMMENT != $post['tabledescription']){
                    $comment        = htmlspecialchars($post['tabledescription']);
                    $changecomment  = $wpdb->query($wpdb->prepare("ALTER TABLE $fulltablename COMMENT = %s", $comment));
                    if($changecomment){
                        ?>
                            <div id='setting-error-settings_updated' class='updated settings-error notice is-dismissible'> <p><strong>Uspesno ste promenili opis tabele. </strong></p><button type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button>
                            </div>
                        <?php
                    }
				    // If not successful
                    else{
                        ?>
                            <div id='setting-error-settings_updated' class='settings-error notice is-dismissible'> <p><strong>Niste uspeli da promenite opis tabele. 
                            <?php echo $wpdb->last_error?></strong></p><button type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button></div>
                        <?php
                    }
                }

                if($result[0]->ENGINE != $post['dbengine']){
                    $dbengine       = $post['dbengine'];
                    $changeengine   = $wpdb->query("ALTER TABLE $fulltablename ENGINE = {$dbengine}");
                    if($changeengine){
                        ?>
                            <div id='setting-error-settings_updated' class='updated settings-error notice is-dismissible'> <p><strong>Uspesno ste promenili engine tabele. </strong></p><button type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button></div>
                        <?php
                    }
                    // If not successful
                    else{
                        ?>
                            <div id='setting-error-settings_updated' class='settings-error notice is-dismissible'> <p><strong>Niste uspeli da promenite engine tabele. 
                            <?php echo $wpdb->last_error?></strong></p><button type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button></div>
                        <?php
                    }

                }
				// Preparing added data for inserting into table
				unset($post['tabledescription']);
				unset($post['dbengine']);
				unset($post['pt_row0']);
				unset($post['edittable']);
				unset($post['tablename']);
                unset($post['pt_columnname']);

                $edittingarray  = [];
                foreach($post as $key=>&$value){
                    if($key == $value['rowid']){
                        $edittingarray[$key] = $value;
                        unset($post[$key]);
                    }
                } 
                $edittingarray      = array_values($edittingarray);

                $editarraycount     = count($edittingarray);

                $uniqueindex        = [];
                $addforeignkey      = [];
                $changecolumnsql    = "";
                if(!empty($edittingarray)){
                    $changecolumn      = [];
                    //Foreach array in $dataarray, add one row 
                    for ($i=0; $i < $editarraycount  ; $i++) {
                        if(!empty($edittingarray[$i]['columnname'])){ 
                            $columnname             = str_replace(" ", "_", $edittingarray[$i]['columnname']);
                            $oldcolumnname          = $edittingarray[$i]['oldcolumnname'];
                            $notnullcolumn          = (isset($edittingarray[$i]['nn']) || isset($edittingarray[$i]['ai']))? 'NOT NULL':'NULL';
                            $unsignedcolumn         = isset($edittingarray[$i]['un'])? 'UNSIGNED':'';
                            $zerofillcolumn         = isset($edittingarray[$i]['zf'])? 'ZEROFILL':'';
                            $autoincrementcolumn    = isset($edittingarray[$i]['ai'])? 'AUTO_INCREMENT':'';
                            $defaultcolumn          = (!empty($edittingarray[$i]['default']) && !isset($edittingarray[$i]['ai']))? "DEFAULT '{$edittingarray[$i]['default']}'":'';
                            $datatype               = (isset($edittingarray[$i]['datatype']) && $edittingarray[$i]['datatype'] != 'Relation')?$edittingarray[$i]['datatype']:'bigint';
                            $uniqueindex[]          = (isset($edittingarray[$i]['uq']) && isset($edittingarray[$i]['columnname']))?"ADD UNIQUE INDEX `{$columnname}_UNIQUE` (`$columnname` ASC)":"";
                            $size                   = (!empty($edittingarray[$i]['size']) && $edittingarray[$i]['datatype'] != "Relation" && $edittingarray[$i]['datatype'] != "ENUM")?"({$edittingarray[$i]['size']})":"";
                            $foreignkey             = ($edittingarray[$i]['datatype'] === "Relation" && isset($edittingarray[$i]['size']))?"ADD CONSTRAINT `fk_{$tablename}_$i`FOREIGN KEY ({$columnname}) REFERENCES {$edittingarray[$i]['size']}(ID)":"";
                            
                            if($edittingarray[$i]['datatype'] === "ENUM" && isset($edittingarray[$i]['size'])){
                                $explode    = explode(',',$edittingarray[$i]['size']);
                                foreach($explode as &$value){
                                    $value = "'". $value ."'";
                                }
                                $enum   = implode(',',$explode);
                                $enum   = "(" . $enum . ")";
                                                                            
                            }else{
                                $enum   = "";
                            }
                            $changecolumn[]         = "CHANGE COLUMN {$oldcolumnname} {$columnname} {$datatype} {$size} {$enum}  {$unsignedcolumn} {$zerofillcolumn} {$notnullcolumn} {$autoincrementcolumn} {$defaultcolumn}";
                            $changecolumnsql        = implode(",",$changecolumn);

                            if(!empty($foreignkey)){
                                $addforeignkey[]      =  $foreignkey;
                            }
                                  
                        }
                    }
                }

				// New array just for data to insert
				$dataarray      = array_values($post);

				// Get number of elements in new array
				$dataarraycount = count($dataarray);
                
                $addcolumnsql   = "";
				if(!empty($dataarray)){
                    $addcolumn      = [];
                    $countforeignkey    = count($addforeignkey);
                    $i  = 0;
                    $foreignkeyid       = $countforeignkey + $i;
                    //Foreach array in $dataarray, add one row 
    				for ($i; $i < $dataarraycount  ; $i++) {
                        if(!empty($dataarray[$i]['columnname'])){ 
        					$columnname 			= str_replace(" ", "_", $dataarray[$i]['columnname']);
        					$notnullcolumn  		= (isset($dataarray[$i]['nn']) || isset($dataarray[$i]['ai']))? 'NOT NULL':'NULL';
        					$unsignedcolumn 		= isset($dataarray[$i]['un'])? 'UNSIGNED':'';
        					$zerofillcolumn 		= isset($dataarray[$i]['zf'])? 'ZEROFILL':'';
        					$autoincrementcolumn    = isset($dataarray[$i]['ai'])? 'AUTO_INCREMENT':'';
        					$defaultcolumn	 		= (!empty($dataarray[$i]['default']) && !isset($dataarray[$i]['ai']))? "DEFAULT '{$dataarray[$i]['default']}'":'';
                            $datatype               = (isset($dataarray[$i]['datatype']) && $dataarray[$i]['datatype'] != 'Relation')?$dataarray[$i]['datatype']:'bigint';
                            $uniqueindex[]          = (isset($dataarray[$i]['uq']) && isset($dataarray[$i]['columnname']))?"ADD UNIQUE INDEX `{$columnname}_UNIQUE` (`$columnname` ASC)":"";
                            $size                   = (!empty($dataarray[$i]['size']) && $dataarray[$i]['datatype'] != "Relation" && $dataarray[$i]['datatype'] != "ENUM")?"({$dataarray[$i]['size']})":"";
                            $foreignkey             = ($dataarray[$i]['datatype'] === "Relation" && isset($dataarray[$i]['size']))?"ADD CONSTRAINT `fk_{$tablename}_$foreignkeyid`FOREIGN KEY ({$columnname}) REFERENCES {$dataarray[$i]['size']}(ID)":"";

                            if($dataarray[$i]['datatype'] === "ENUM" && isset($dataarray[$i]['size'])){
                                $explode    = explode(',',$dataarray[$i]['size']);
                                foreach($explode as &$value){
                                    $value = "'". $value ."'";
                                }
                                $enum   = implode(',',$explode);
                                $enum   = "(" . $enum . ")";
                                                                            
                            }else{
                                $enum   = "";
                            }
                            $addcolumn[]    = "ADD COLUMN {$columnname} {$datatype} {$size} {$enum}  {$unsignedcolumn} {$zerofillcolumn} {$notnullcolumn} {$autoincrementcolumn} {$defaultcolumn}";
                            $addcolumnsql   = implode(",",$addcolumn);
                            if(!empty($changecolumnsql)){
                                $addcolumnsql   = "," . $addcolumnsql;
                            }
                            if(!empty($foreignkey)){
                                $addforeignkey[]      =  $foreignkey;
                            }                           
                        }
    				}
                }

                if(!empty($addcolumnsql) || !empty($changecolumnsql)){
                    $sqlcolumn              = "ALTER TABLE {$fulltablename} {$changecolumnsql} {$addcolumnsql}";
                    $querycolumn            = $wpdb->query($sqlcolumn);

                    if($querycolumn){
                    ?>
                        <div id='setting-error-settings_updated' class='updated settings-error notice is-dismissible'><p><strong>Uspesno ste izmenili kolone. </strong></p> 
                            <button  type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button>
                        </div>
                    <?php
                    }else{
                    ?>
                        <div id='setting-error-settings_updated' class='settings-error notice is-dismissible'><p><strong>Neuspesno ste izmenili kolone:  
                            <?php echo $wpdb->last_error ?></strong></p><button type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button>
                        </div>
                    <?php
                    }
                }

                if(!empty($addforeignkey)){
                    $addforeignkeysql   = implode(",", $addforeignkey);
                    $foreignkeyadd      = $wpdb->query("ALTER TABLE {$fulltablename} {$addforeignkeysql}");
                    if($foreignkeyadd){
                    ?>
                        <div id='setting-error-settings_updated' class='updated settings-error notice is-dismissible'><p><strong>Uspesno ste dodali spolje kljuceve. </strong></p> 
                            <button  type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button>
                        </div>
                    <?php    
                    }else{
                    ?>
                        <div id='setting-error-settings_updated' class='settings-error notice is-dismissible'><p><strong>Nista uspeli da dodate spoljne kljuceve. 
                            <?php echo $wpdb->last_error ?></strong></p><button type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button>
                        </div>
                    <?php    
                    }
                }
                if(!empty($uniqueindex)){
                    foreach ($uniqueindex as $key=> &$value) {
                        if(empty($uniqueindex[$key])){
                            unset($uniqueindex[$key]);
                        }
                    }
                    if(!empty($uniqueindex)){
                        print_r($uniqueindex);
                        $adduniqueindex     = implode(",",$uniqueindex);
                        $uniqueindexadd     = $wpdb->query("ALTER TABLE {$fulltablename} {$adduniqueindex}");
                        if($uniqueindexadd){
                        ?>
                            <div id='setting-error-settings_updated' class='updated settings-error notice is-dismissible'><p><strong>Uspesno ste dodali unique indexe. </strong></p> 
                                <button  type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button>
                            </div>
                        <?php    
                        }else{
                        ?>
                            <div id='setting-error-settings_updated' class='settings-error notice is-dismissible'><p><strong>Nista uspeli da dodate unique indexe. 
                                <?php echo $wpdb->last_error ?></strong></p><button type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button>
                            </div>
                        <?php    
                        }
                    }
                }

            }
            if(isset($_POST['pt_deletecolumn'])){
                $columnname     = $_POST['pt_columnname'];
                $deletecolumn   = $wpdb->query("ALTER TABLE {$fulltablename} DROP COLUMN {$columnname}");
                if($deletecolumn){
                    ?>
                        <div id='setting-error-settings_updated' class='updated settings-error notice is-dismissible'> <p><strong>Uspesno ste obrisali kolonu iz tabele.</strong></p><button type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button></div>
                    <?php
                }else{
                    ?>
                        <div id='setting-error-settings_updated' class='settings-error notice is-dismissible'> <p><strong>Niste uspeli da obirsete kolonu iz tabele. 
                            <?php echo $wpdb->last_error?></strong></p><button type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button>
                        </div>
                    <?php
                }
            }
	}else{
        die("<a href='admin.php?page=pikto_tables'>&larr;Go back</a> <h2>Requested table not found</h2>");
    }
?><div class="wrap">
            <a href="admin.php?page=pikto_tables">&larr;Go back</a>
            <h1> <?php echo __('Edit table','pikto-tables');?><b> <?php echo isset($tablename)?$tablename:'';?></b></h1>
            <h3>Current database selected: <?php echo (get_option("PTDB_DEFAULT") || !get_option("PTDB_NAME"))?DB_NAME:get_option("PTDB_NAME") ?></h3>
			<form id="pt_createTableForm" method="post" action="#">
				<table class="form-table">	
					<tbody>
						<tr>
							<th scope="row">	
								<label for="tablename" style="font-weight: bold">Table Name </label>
							</th>
							
                            <td>
								<?php echo $prefix ?>pikto_<input type="text" name="tablename" aria-describedby="dbhost-description" value="<?php echo isset($tablenamewithoutprefix)?$tablenamewithoutprefix:'';?>" required>
								<p class="description" id="dbhost-description">Enter name of the table.</p> 
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="tabledescription" style="font-weight: bold">Table Description </label>
							</th>	
							<td>	
								<input type="text" name="tabledescription" aria-describedby="dbname-description" value="<?php echo isset($result[0]->TABLE_COMMENT)?$result[0]->TABLE_COMMENT:'';?>">
								<p class="description" id="dbname-description">Enter additional information about your table.</p>  
							</td>
						</tr>
						<tr>
							<th scope="row">	
								<label for="dbengine" style="font-weight: bold">DB Engine</label>
							</th>
							<td>
								<select name="dbengine">
									<option selected>InnoDB</option>
									<option <?php echo ($result[0]->ENGINE == 'MyISAM')?'selected':'';?>>MyISAM</option>
								</select>
								<p class="description" id="port-description">DB engine, InnoDB is selected by default.</p> 
							</td>
						</tr>
						
					</tbody>
				</table>

				<table border="2" style="border-collapse: collapse;" id="pt_createTable">
					<caption><small>*If Relation Datatype is set, type of data will be set to bigint by default and Size field will be used for table to reference. Note that field that is being referenced must be unique.</small></caption>
                    <caption><small>*In the size column enter enum values separated with comma for ENUM type</small></caption>
					<tr>
						<th>Column Name</th>
						<th>Datatype</th>
						<th>Size</th>
						<th>AI</th>
						<th>NN</th>
						<th>UQ</th>
						<th>UN</th>
						<th>ZF</th>						
						<th>Default</th>
                        <th></th>
					</tr>
				<?php 
				if(!empty($columns)){  
                    for ($i=0; $i < count($columns); $i++) {
                                            
						$columntype = explode("(",$columns[$i]->COLUMN_TYPE);
                        if(!isset($columntype[1])){
                            $columntype[1]  = "";
                        } 
                        // Check if column datatype is enum
                        if($columns[$i]->DATA_TYPE == "enum"){

                            $size 		    = str_replace(')', '', $columntype[1]); 
                            $newsize        = str_replace('\'','',$size);
                        }else{
                            $size 		    = str_replace(')', '', $columntype[1]);
                            if(strpos($size, "unsigned")!== false){
                                $newsize    = str_replace("unsigned",'',$size);
                                if(strpos($size, "zerofill")!== false){
                                    $newsize    = str_replace("zerofill",'',$newsize);
                                }
                            }else{
                                $newsize    =   $size;
                            } 
                            if(strpos($newsize, "zerofill")!== false && strpos($size, "zerofill") !== false){
                                $newsize    = str_replace("zerofill",'',$size);
                            }                                                    
                        }   

                                                   
				?>
                    <tr id="pt_row<?php echo $i?>">
                        <td><input type="text" name="pt_row<?php echo $i?>[columnname]" value="<?php echo $columns[$i]->COLUMN_NAME;?>" disabled required></td>
                        <input type="hidden" name="pt_row<?php echo $i?>[rowid]" value="pt_row<?php echo $i?>">
                        <input type="hidden" name="pt_row<?php echo $i?>[oldcolumnname]" value="<?php echo $columns[$i]->COLUMN_NAME;?>" >
                        <td>
                            <select disabled required name="pt_row<?php echo $i?>[datatype]">
                                <option <?php echo($columns[$i]->DATA_TYPE == 'int')?'selected':''?>>INT</option>
                                <option <?php echo($columns[$i]->DATA_TYPE == 'varchar')?'selected':''?>>VARCHAR</option>
                                <option <?php echo($columns[$i]->DATA_TYPE == 'datetime')?'selected':''?>>DATETIME</option>
                                <option <?php echo($columns[$i]->DATA_TYPE == 'blob')?'selected':''?>>BLOB</option>	
                                <option <?php echo($columns[$i]->DATA_TYPE == 'date')?'selected':''?>>DATE</option>
                                <option <?php echo($columns[$i]->DATA_TYPE == 'binary')?'selected':''?>>BINARY</option>
                                <option <?php echo($columns[$i]->DATA_TYPE == 'tinyint')?'selected':''?>>TINYINT</option>
                                <option <?php echo($columns[$i]->DATA_TYPE == 'smallint')?'selected':''?>>SMALLINT</option>
                                <option <?php echo($columns[$i]->DATA_TYPE == 'mediumint')?'selected':''?>>MEDIUMINT</option>
                                <option <?php echo($columns[$i]->DATA_TYPE == 'bigint')?'selected':''?>>BIGINT</option>
                                <option <?php echo($columns[$i]->DATA_TYPE == 'float')?'selected':''?>>FLOAT</option>
                                <option <?php echo($columns[$i]->DATA_TYPE == 'double')?'selected':''?>>DOUBLE</option>
                                <option <?php echo($columns[$i]->DATA_TYPE == 'decimal')?'selected':''?>>DECIMAL</option>
                                <option <?php echo($columns[$i]->DATA_TYPE == 'datetime')?'selected':''?>>DATETIME</option>
                                <option <?php echo($columns[$i]->DATA_TYPE == 'timestamp')?'selected':''?>>TIMESTAMP</option>
                                <option <?php echo($columns[$i]->DATA_TYPE == 'time')?'selected':''?>>TIME</option>
                                <option <?php echo($columns[$i]->DATA_TYPE == 'year')?'selected':''?>>YEAR</option>
                                <option <?php echo($columns[$i]->DATA_TYPE == 'char')?'selected':''?>>CHAR</option>
                                <option <?php echo($columns[$i]->DATA_TYPE == 'text')?'selected':''?>>TEXT</option>
                                <option <?php echo($columns[$i]->DATA_TYPE == 'tinytext')?'selected':''?>>TINYTEXT</option>
                                <option <?php echo($columns[$i]->DATA_TYPE == 'mediumtext')?'selected':''?>>MEDIUMTEXT</option>
                                <option <?php echo($columns[$i]->DATA_TYPE == 'longtext')?'selected':''?>>LONGTEXT</option>
                                <option <?php echo($columns[$i]->DATA_TYPE == 'enum')?'selected':''?>>ENUM</option>
                                <option>Relation</option>
                            </select>
                        </td>
                        <td><input type="text" name="pt_row<?php echo $i?>[size]"  value="<?php echo $newsize?>" disabled ></td>
                        <td><input type="checkbox" name="pt_row<?php echo $i?>[ai]" disabled <?php echo(($columns[$i]->EXTRA == 'auto_increment')?'checked':'')?>></td>
                        <td><input type="checkbox" name="pt_row<?php echo $i?>[nn]" disabled <?php echo((($columns[$i]->EXTRA == 'auto_increment') || $columns[$i]->IS_NULLABLE == 'NO' ) ?'checked':'')?>></td> 
                        <td><input type="checkbox" name="pt_row<?php echo $i?>[uq]" 
                            <?php 
                                for ($k=0,$countindex = count($index); $k < $countindex; $k++) { 
                                    if($index[$k]->Non_unique == 0 && $index[$k]->Column_name == $columns[$i]->COLUMN_NAME){
                                        echo "checked";
                                    }else{
                                        echo "";
                                    }
                                }
                            ?>
                        disabled></td>
                        <td><input type="checkbox" name="pt_row<?php echo $i?>[un]" disabled <?php echo (strpos($size,"unsigned")!== false)?'checked':''?>></td>
                        <td><input type="checkbox" name="pt_row<?php echo $i?>[zf]" disabled <?php echo (strpos($size,"zerofill")!== false)?'checked':''?>></td>
                        <td><input type="text" value="<?php echo(!empty($columns[$i]->COLUMN_DEFAULT)?$columns[$i]->COLUMN_DEFAULT:'')?>" disabled name="pt_row<?php echo $i?>[default]"></td>
                        <td>
                            <form method="post" onsubmit="confirm('By deleting a column you could make table unusable. Are you sure you want to delete column?')">
                                <input type="hidden" name="pt_columnname" value="<?php echo $columns[$i]->COLUMN_NAME?>">
                                <button <?php echo ($i == 0)?"disabled":""?> type="submit" class="deletebutton button button-secondary" name="pt_deletecolumn"><span>&#8722;</span></button>
                            </form>
                        </td>
                    </tr>				
				<?php  
					}
				}
				?>					
				</table>
				<button style="margin-top:10px" id="pt_addRowButtonEdit" class="button button-primary" type="button"><span>&#43;</span></button>
				
                <br>
				
                <button style="margin-top:10px" class="button" type="reset">Cancel</button>
				<p class="submit">
					<input type="submit" value="Update" name="edittable" class="button button-primary">
				</p>
				
			</form>
</div>