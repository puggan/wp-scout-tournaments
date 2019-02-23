/**
 * Change check-value for all checkboxes in a form.
 * 
 * @param form_name The name of the form to search for checkboxes in.
 * @param check_value Value to set all checkboxes checked value to.
 */
function set_checkboxes_in_form(form_name, check_value)
{
	// Get form
	var form = get_element(form_name);
	
	// Return false if the form we look for don't exists.
	if(!form)
	{
		//
		return false;
	}

	// Get all inputs from the form.
	var list_of_inputs = form.getElementsByTagName("input");

	// Return false if we did not get any inputs.
	if(typeof(list_of_inputs) != "object")
	{
		//
		return false;
	}

	// Iterate over all input elements in the form.
	for(var input_index in list_of_inputs)
	{
		// Get current input.
		var current_input = list_of_inputs[input_index];
		
		// If current input's type is not undefined.
		if(typeof current_input != "object")
		{
			//
			continue;
		}
		
		// If current input is a checkbox.
		if(current_input.getAttribute('type') == "checkbox")
		{
			// Set current input's checked value to the one passed to this function.
			current_input.checked = check_value;
		}
	}
}

/**
 * Change check-value for all checkboxes in an element.
 * 
 * @param element_id The id of the element to search for checkboxes in.
 * @param check_value Value to set all checkboxes checked value to.
 */
function set_checkboxes_in_element(element_id, check_value)
{
	// Get target element.
	var target_element = get_element(element_id);
	
	// Return true if the element we look for don't exists.
	if(!target_element)
	{
		// Add an error to the console.
		console_log("No element by id 'element_id' was found (set_checkboxes_in_element).");
		
		// Exit.
		return false;
	}

	// Get all inputs from the element.
	var list_of_inputs = target_element.getElementsByTagName("input");

	// Return true if we did not get any inputs.
	if(typeof(list_of_inputs) != "object" && typeof(list_of_inputs) != "function")
	{
		// Add an error to the console.
		console_log("No checkboxes found (set_checkboxes_in_element).");
		
		// Exit.
		return false;
	}

	// Iterate over all input elements in the element.
	for(var input_index in list_of_inputs)
	{
		// Get current input.
		var current_input = list_of_inputs[input_index];
		
		// If current input is not an object.
		if(typeof(current_input) != "object")
		{
			// Go to next input.
			continue;
		}

		// If current input is a checkbox.
		if(current_input.getAttribute('type') != "checkbox")
		{
			//
			continue;
		}
		
		// Get table row.
		var table_row = get_first_parent_by_tagname("TR", current_input);

		// If master checkbox checked value is true.
		if(check_value)
		{
			// Check current checkbox.
			current_input.setAttribute("checked", "checked");
			current_input.checked = true;
			
			// If row was found.
			if(table_row)
			{
				// Mark row as selected.
				table_row.className = add_item_to_list("marked", table_row.className);
			}
		}
		else
		{
			// Remove checked from current checkbox.
			current_input.removeAttribute("checked");
			current_input.checked = false;
			
			// If row was found.
			if(table_row)
			{
				// Unmark row as selected.
				table_row.className = remove_item_from_list("marked", table_row.className);
			}
		}
	}
}

/**
 * Set a table row as marked.
 * 
 * @param checkbox - A checkbox object.
 * @param radio - True or false, if the checkbox element is a radio button.
 */
function set_row_as_marked(checkbox, radio, event)
{
	// If checkbox is not an object.
	if(typeof(checkbox) != "object" || checkbox == null)
	{
		// Exit.
		return false;
	}
	
	//
	{
		// Get checkbox's parent table.
		var table = get_first_parent_by_tagname("TABLE", checkbox);

		// If no table was found.
		if(!table)
		{
			// Add an error to the console.
			console_log("checkbox is not in a table (set_row_as_marked).");
			
			// Exit.
			return false;
		}
	}
	
	//
	{
		// Get table row.
		var table_row = get_first_parent_by_tagname("TR", checkbox);
		
		// If no row was found.
		if(!table_row)
		{
			// Add an error to the console.
			console_log("checkbox is not in a table row (set_row_as_marked).");
			
			// Exit.
			return false;
		}
	}
	
	// If we have an event.
	if(event)
	{
		// If shift key is pressed.
		if(event.shiftKey)
		{
			// Select multiple rows.
			multi_table_row_selection(table, table_row);
		}
	}
	
	// If checkbox is a radio button.
	if(radio)
	{
		// Get parent table object.
		var table = get_first_parent_by_tagname("TABLE", table_row);
		
		// If table was found.
		if(table)
		{
			// If we know that there is only one row marked.
			if(table.current_marked)
			{
				// Unmark that row.
				table.current_marked.className = remove_item_from_list("marked", table.current_marked.className);
			}
			else
			{
				// Else we have to loop through all table rows to get the one marked.
				{
					// If we have the getElementsByClassName function.
					if(typeof(table.getElementsByClassName) == "object" || typeof(table.getElementsByClassName) == "object")
					{
						// Only get the marked rows.
						var table_rows = table.getElementsByClassName("marked");
					}
					else
					{
						// Else get all rows.
						var table_rows = table.getElementsByTagName("TR");
					}
					
					// Loop through the table rows.
					for(var row_index in table_rows)
					{
						// Get current row.
						var current_row = table_rows[row_index];
						
						// If current row is not an object.
						if(typeof(current_row) != "object" || current_row == null)
						{
							// Go to next one.
							continue;
						}
						
						// If the row has the marked class.
						if(is_item_in_list("marked", current_row.className))
						{
							// Remove the marked class.
							current_row.className = remove_item_from_list("marked", current_row.className);
						}
					}
				}
			}
			
			// Add the row we clicked on has the current marked in the table object.
			table.current_marked = table_row;
		}
	}

	// If checkbox is checked.
	if(checkbox.checked)
	{
		// Give the clicked row the marked class.
		table_row.className = add_item_to_list("marked", table_row.className);
	}
	else
	{
		// Else remove the marked class from the row.
		table_row.className = remove_item_from_list("marked", table_row.className);
	}
	
	// Set current row as last marked row.
	table.last_marked_row = table_row;
}

//
function multi_table_row_selection(table, table_row)
{
	// If table has a last marked row.
	if(typeof(table.last_marked_row) == "object" && table != null)
	{
		// Get last marked row's table checkbox.
		{
			// Get last marked row's all checkboxes with class "table_checkbox".
			var last_marked_row_checkboxes = table.last_marked_row.getElementsByClassName("table_checkbox");
			
			// If any checkboxes where found.
			if(typeof(last_marked_row_checkboxes) == "object" && last_marked_row_checkboxes != null)
			{
				// Set first as last marked row's table checkbox.
				var last_marked_row_checkbox = last_marked_row_checkboxes[0];
			}
		}
		
		// If last marked row's table checkbox is an object.
		if(typeof(last_marked_row_checkbox) == "object" && last_marked_row_checkbox != null)
		{
			// Get start and end point for the selection.
			{
				// If current table row's index is heigher than last marked row's index. 
				if(table_row.rowIndex > table.last_marked_row.rowIndex)
				{
					// Use last marked row's index as start point.
					var start_row_index = table.last_marked_row.rowIndex;
					
					// And use current row's index as end point.
					var end_row_index = table_row.rowIndex;
				}
				else
				{
					// Use current row's index as start point.
					var start_row_index = table_row.rowIndex;
					
					// And use last marked row's index as end point.
					var end_row_index = table.last_marked_row.rowIndex;
				}
			}
			
			// Loop through all rows between the two points.
			for(current_row_index = start_row_index; current_row_index <= end_row_index; current_row_index++)
			{
				// Get current row,
				var current_table_row = table.rows[current_row_index];
				
				// Get all checkboxes with class "table_checkbox" in current row.
				var current_table_row_checkboxes = current_table_row.getElementsByClassName("table_checkbox");
				
				// If any checkboxes was found.
				if(typeof(current_table_row_checkboxes) == "object" && current_table_row_checkboxes != null)
				{
					// Use first checkbox as current rows table checkbox.
					var current_table_row_checkbox = current_table_row_checkboxes[0];
					
					// If row has a checkbox.
					if(typeof(current_table_row_checkbox) == "object" && current_table_row_checkbox != null)
					{
						// If current row's checkbox don't have the same checked as last marked raw's table checkbox.
						if(current_table_row_checkbox.checked != last_marked_row_checkbox.checked)
						{
							// Run current row's table checkbox's on click.
							current_table_row_checkbox.click();
						}
					}
				}
			}
		}
	}
}


/**
 * Change check-value for all checkboxes in an element.
 * 
 * @param element_id The id of the element to search for checkboxes in.
 * @param check_value Value to set all checkboxes checked value to.
 */
function set_checkboxes_in_visibile_parts_of_table(element_id, check_value)
{
	// Get target element.
	var target_element = get_element(element_id);
	
	// Return true if the element we look for don't exists.
	if(!target_element)
	{
		// Add an error to the console.
		console_log("No element by id 'element_id' was found (set_checkboxes_in_element).");
		
		// Exit.
		return false;
	}
	
	if(!target_element.getElementsByTagName)
	{
		return false;
	}
	
	var list_of_trs = target_element.getElementsByTagName("tr");
	
	for(var tr_index in list_of_trs)
	{
		var current_tr = list_of_trs[tr_index];
		
		if(!current_tr.getElementsByTagName)
		{
			continue;
		}
		
		if(is_item_in_list('hidden', current_tr.className))
		{
			continue;
		}
		
		// Get all inputs from the element.
		var list_of_inputs = current_tr.getElementsByTagName("input");
	
		// Return true if we did not get any inputs.
		if(typeof(list_of_inputs) != "object" && typeof(list_of_inputs) != "function")
		{
			// Add an error to the console.
			console_log("No checkboxes found (set_checkboxes_in_element).");
			
			// Exit.
			return false;
		}
		
		// Iterate over all input elements in the element.
		for(var input_index in list_of_inputs)
		{
			// Get current input.
			var current_input = list_of_inputs[input_index];
			
			// If current input is not an object.
			if(typeof(current_input) != "object")
			{
				// Go to next input.
				continue;
			}
			
			// If current input is a checkbox.
			if(current_input.getAttribute('type') != "checkbox")
			{
				//
				continue;
			}
			
			// Get table row.
			var table_row = get_first_parent_by_tagname("TR", current_input);
			
			// If master checkbox checked value is true.
			if(check_value)
			{
				// Check current checkbox.
				current_input.setAttribute("checked", "checked");
				current_input.checked = true;
				
				// If row was found.
				if(table_row)
				{
					// Mark row as selected.
					table_row.className = add_item_to_list("marked", table_row.className);
				}
			}
			else
			{
				// Remove checked from current checkbox.
				current_input.removeAttribute("checked");
				current_input.checked = false;
				
				// If row was found.
				if(table_row)
				{
					// Unmark row as selected.
					table_row.className = remove_item_from_list("marked", table_row.className);
				}
			}
		}
	}
}