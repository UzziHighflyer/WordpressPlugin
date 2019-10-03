jQuery(document).ready(function($){
	// Used in settings.php file to toggle between setting forms for database selection 
	 $("settings.php").ready(function(){
		var radio = $("input[name='pt_database']");
		
		// If custom was selected
		$('#pt_custom').change(function(){
			$('#pt_customForm').show();
			$('#pt_defaultForm').hide();
		});
		//  If default was selected 	
		$('#pt_default').change(function(){
			$('#pt_customForm').hide();
			$('#pt_defaultForm').show();
		});
	});
	
	var n = 0;

	// Used in createtables.php file to add rows to table for table creation and adding rows sql
	$("createtables.php").ready(function(){
		$('#pt_addRowButton').click(function(){
            var lastChild   = $("tbody").children().last();
            var id  = lastChild.attr('id');
                        
            var lastid  = id.substr(6);
                        
            n = parseInt(lastid) + 1;
                        
            // Add row to table for creation 
			$('#pt_createTable').append('<tr id="pt_row'+ n +'"><td><input type="text" name="pt_row'+n+'[columnname]"></td><td><select id="selectType'+n+'" required name="pt_row'+n+'[datatype]"><option>INT</option><option>VARCHAR</option><option>DATETIME</option><option>BLOB</option><option>DATE</option><option>BINARY</option><option>TINYINT</option><option>SMALLINT</option><option>MEDIUMINT</option><option>BIGINT</option><option>FLOAT</option><option>DOUBLE</option><option>DECIMAL</option><option>DATETIME</option><option>TIMESTAMP</option><option>TIME</option><option>YEAR</option><option>CHAR</option><option>TEXT</option><option>TINYTEXT</option><option>MEDIUMTEXT</option><option>LONGTEXT</option><option>ENUM</option><option>Relation</option></select></td><td><input type="text" id="size'+n+'" name="pt_row'+n+'[size]" min="0"></td><td><input type="checkbox" name="pt_row'+n+'[ai]"></td><td><input type="checkbox" name="pt_row'+n+'[nn]"></td><td><input type="checkbox" name="pt_row'+n+'[uq]"></td><td><input type="checkbox" name="pt_row'+n+'[un]"></td><td><input type="checkbox" name="pt_row'+n+'[zf]"></td><td><input type="text" name="pt_row'+n+'[default]"></td><td><button id="pt_deleteRowButton" class="deletebutton button button-secondary" type="button"><span>&#8722;</span></button></td></tr>');                      
                        
			n++;
			return n;                
		});
        $(document).on("click","#pt_deleteRowButton", function(){
            $(this).parent().parent().remove();
		})
		$("#pt_createTable tr:not(#pt_row0)").click(function(){
			var input = $(this).children().not("#pt_row0").children();
			input.each(function(){
				$(this).attr("disabled",false);
 			})
		});             
	});

	// Used in showdata.php to add row to insert data into mysql table
	$("showdata.php").ready(function(){
		$('#pt_addRowButtonData').click(function(){
			var numberofvalues = $('.numberofvalue');
			var number = numberofvalues.length;
			
			var count = $('.pt_datarow');
			
			count = count.length;	
			
			var newNumber = number + 1;
			// Add form with table and rows inside for adding table data 
			$('#pt_showData').after('<form method="POST" id="pt_form'+newNumber+'"></form>');
			$('#pt_form'+newNumber).append('<table border="2" id="pt_table'+newNumber+'" class="wp-list-table widefat fixed striped pages"></table>');
			$('#pt_table'+newNumber).append('<tr id="pt_row_'+newNumber+'"><td class="numberofvalue">'+newNumber+'</td></tr>');

			$('#pt_row_'+newNumber).append('<td><input type="text" value="null" readonly name="pt_row'+newNumber+'[]"></td>');

			for (var i = 0; i < count-1; i++) {
				$('#pt_row_'+newNumber).append('<td><input type="text" name="pt_row'+newNumber+'[]"></td>');
			}
			$('#pt_row_'+newNumber).append('<td><input type="submit" class="button" name="pt_addrow" value="Add"></td>');
			$('#pt_addRowButtonData').hide();		
		});
		$('body').on("dblclick",'[data-editable]',function(){
			var $el 	= $(this);

			var $input  = $("<input/>").val($el.text());
			$el.replaceWith($input);
			$input.attr({
				'name':'pt_field'
			});
			var parent = $input.parent();
			var button = $(parent).children()[3];
			$(button).show();

			var save 	= function(){
				$el.text($input.val());
				$input.replaceWith($el);
				
			};
			

		});
	});

	// Used in edittable.php to add row to table for editting tables in mysql
	$("edittables.php").ready(function(){
		$('#pt_addRowButtonEdit').click(function(){			
            var lastChild   = $("tbody").children().last();
            var id  = lastChild.attr('id');
                        
            var lastid  = id.substr(6);
                        
            n = parseInt(lastid) + 1;
            $('#pt_createTable').append('<tr id="pt_row'+ n +'"><td><input type="text" name="pt_row'+n+'[columnname]"></td><input type="hidden" name="pt_row'+n+'[rowid]" value="0"><td><select id="selectType'+n+'" required name="pt_row'+n+'[datatype]"><option>INT</option><option>VARCHAR</option><option>DATETIME</option><option>BLOB</option><option>DATE</option><option>BINARY</option><option>TINYINT</option><option>SMALLINT</option><option>MEDIUMINT</option><option>BIGINT</option><option>FLOAT</option><option>DOUBLE</option><option>DECIMAL</option><option>DATETIME</option><option>TIMESTAMP</option><option>TIME</option><option>YEAR</option><option>CHAR</option><option>TEXT</option><option>TINYTEXT</option><option>MEDIUMTEXT</option><option>LONGTEXT</option><option>ENUM</option><option>Relation</option></select></td><td><input type="text" id="size'+n+'" name="pt_row'+n+'[size]" min="0"></td><td><input type="checkbox" name="pt_row'+n+'[ai]"></td><td><input type="checkbox" name="pt_row'+n+'[nn]"></td><td><input type="checkbox" name="pt_row'+n+'[uq]"></td><td><input type="checkbox" name="pt_row'+n+'[un]"></td><td><input type="checkbox" name="pt_row'+n+'[zf]"></td><td><input type="text" name="pt_row'+n+'[default]"></td><td><button id="pt_deleteRowButton" class="deletebutton button button-secondary" type="button"><span>&#8722;</span></button></td></tr>');
            n++;
			return n; 		
		});
                $(document).on("click","#pt_deleteRowButton", function(){
                        $(this).parent().parent().remove();
		})
	});	
});