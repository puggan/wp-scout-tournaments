/**
 * Filter a table based on all filter fields.
 * 
 * @param input - Input element that triggerd this function.
 */
function filter_table(input)
{
	// Get input's table parent.
	var current_table = get_first_parent_by_tagname("TABLE", input);
	
	// Get all tbodies in table.
	var tbodies = current_table.getElementsByTagName("TBODY");
	
	// Get the filter table row.
	var filter_row = get_first_parent_by_tagname("TR", input);
	
	// Get all filter search fields.
	var filter_fields = current_table.getElementsByClassName("filter_field");

	// Set that the table is not filtered.
	current_table.filtered = false;
	
	// Loop through all filter fields.
	for(filter_field_index in filter_fields)
	{
		// Get current filter field.
		var current_filter_field = filter_fields[filter_field_index];
		
		// If current filter field is not an object.
		if(typeof(current_filter_field) != "object" || current_filter_field == null)
		{
			// Continue to next.
			continue;
		}
		
		// Get current filter field's parent th.
		var parent_th = get_first_parent_by_tagname("TH", current_filter_field);
		
		// If there where a parent th.
		if(typeof(parent_th) == "object" && parent_th != null)
		{
			// Get real column index for current filter field.
			current_filter_field.column_index = get_cell_index_in_tr(parent_th.parentNode, parent_th.cellIndex);
			
			// Get current filter fields colspan.
			current_filter_field.colSpan = parent_th.colSpan;
		}
		
		// If current filter field has a value.
		if(current_filter_field.value != "")
		{
			// Mark table as filtered.
			current_table.filtered = true;
		}
	}
	
	// If table is filtered or sorted.
	if((typeof(current_table.sorted) == "boolean" && current_table.sorted) || (typeof(current_table.filtered) == "boolean" && current_table.filtered))
	{
		console_log("Sorted = " + current_table.sorted + "; filtered = " + current_table.filtered);
		
		// Add the modified class to table.
		filter_row.className = add_item_to_list("modified", filter_row.className);
	}
	else
	{
		// Else remove the modified class.
		filter_row.className = remove_item_from_list("modified", filter_row.className);
	}

	// Loop through all tbodies in current table.
	for(tbody_index in tbodies)
	{
		// Get current tbody.
		var current_tbody = tbodies[tbody_index];
		
		// If current tbody is not an object.
		if(typeof(current_tbody) != "object" || current_tbody == null)
		{
			// Continue to next tbody.
			continue;
		}
		
		// Get all table rows in current tbody.
		var rows = current_tbody.getElementsByTagName("TR");

		// Loop through all table rows.
		for(row_index in rows)
		{
			// Get current table row.
			var current_row = rows[row_index];
			
			// If current table row is not an object.
			if(typeof(current_row) != "object" || current_row == null)
			{
				// Go to next table row.
				continue;
			}

			// Get all columns in current row.
			var columns = current_row.getElementsByTagName("TD");
			
			// Set show to true, until we know if it shall be shown or hidden.
			var show = true;

			// Loop through all filter fields.
			for(filter_field_index in filter_fields)
			{
				// Get current filter field.
				var current_filter_field = filter_fields[filter_field_index];
				
				// If current filter field is not an object.
				if(typeof(current_filter_field) != "object" || current_filter_field == null)
				{
					// Continue to next field.
					continue;
				}
				
				// Use current filter's value as the needle, when we compare it to the column's content.
				var needle = current_filter_field.value;
				
				if(needle)
				{
					// Get current columns content as the haystack.
					var haystack = get_cells_content(current_row, current_filter_field.column_index, current_filter_field.column_index + current_filter_field.colSpan -1);
					
					console_log([current_row, current_filter_field.column_index, current_filter_field.column_index + current_filter_field.colSpan -1]);
					
					// Check if the needle is not found in the haystack.
					if(!ifw_match(needle, haystack))
					{
						// Set show to false.
						show = false;
						
						// Stop looping the filter fields.
						break;
					}
				}
			}
			
			// If show.
			if(show)
			{
				// Remove the hidden class from current row.
				current_row.className = remove_item_from_list("hidden", current_row.className);
			}
			else
			{
				// Else add the hidden class to current row.
				current_row.className = add_item_to_list("hidden", current_row.className);
			}
		}
	}
}