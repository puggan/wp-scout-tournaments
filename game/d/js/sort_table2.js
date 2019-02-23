// Dependings: misc.js 

// Define constants for our sort orders.
{
	var SORT_NONE = 0;
	var SORT_ASC  = 1;
	var SORT_DESC = 2;
}

/**
 * 
 */
function init_table_sort()
{
	//
	var tables = document.getElementsByTagName("TABLE");
	
	//
	if(typeof(tables) != "object" || tables == null)
	{
		//
		return false;
	}
	
	//
	for(var table_index = 0; table_index < tables.length; table_index++)
	{
		//
		var current_table = tables[table_index];
		
		//
		if(typeof(current_table) != "object" || current_table == null)
		{
			//
			continue;
		}
		
		//
		if(typeof(current_table.tHead) !== "object" || current_table.tHead == null)
		{
			//
			continue;
		}

		//
		{
			//
			var thead_rows = current_table.tHead.getElementsByTagName("TR");

			//
			var table_head_cells = thead_rows[thead_rows.length - 1].getElementsByTagName("TH");
		}
		
		//
		if(typeof(table_head_cells) != "object" || table_head_cells == null)
		{
			//
			continue;
		}
		
		//
		{
			//
			var manage_table_rows = current_table.tHead.getElementsByClassName("manage_table");

			//
			if(typeof(manage_table_rows[0]) == "object")
			{
				//
				continue;
			}
		}
		
		//
		{
			//
			var filter_row = document.createElement("TR");
			
			//
			filter_row.className = "manage_table";
		}
		
		//
		var add_manage_table = false;
		
		//
		for(var i = 0; i < table_head_cells.length; i++)
		{
			//
			var current_head_cell = table_head_cells[i];
			
			//
			if(typeof(current_head_cell) != "object" || current_head_cell == null)
			{
				//
				continue;
			}
			
			//
			{
				//
				var new_filter_cell = document.createElement("TH");
				
				//
				if(current_head_cell.colSpan != 1)
				{
					//
					new_filter_cell.colSpan = current_head_cell.colSpan;
				}
			}
			
			//
			{
				//
				var filter_content = document.createElement("SPAN");
				filter_content.className = "filter_content";
			}
			
			//
			{
				//
				var add_filter = is_item_in_list("filter_yes", current_table.className);
				var add_sort = is_item_in_list("sort_yes", current_table.className);
				
				//
				console_log(add_filter);
				
				//
				if(is_item_in_list("filter_yes", current_head_cell.className))
				{
					//
					add_filter = true;
				}
				else if(is_item_in_list("filter_no", current_head_cell.className))
				{
					//
					add_filter = false;
				}

				//
				if(is_item_in_list("sort_yes", current_head_cell.className))
				{
					//
					add_sort = true;
				}
				else if(is_item_in_list("sort_no", current_head_cell.className))
				{
					//
					add_sort = false;
				}
				
				//
				if(add_sort || add_filter)
				{
					//
					var add_manage_table = true;

					//
					var filter_input = document.createElement("INPUT");
					
					//
					filter_input.type = "text";
					filter_input.className = "filter_field";
					filter_input.setAttribute("onkeyup", "this.onchange();");
					filter_input.setAttribute("onchange", "window.filter_table(this);");
					filter_input.size = "12";
						
					//
					if(add_filter)
					{
						filter_input.placeholder = "Filtrera..";
					}
					else
					{
						filter_input.disabled = true;
						filter_input.placeholder = "Sortera..";
					}
					
					//
					filter_content.appendChild(filter_input);
				
					//
					if(add_sort)
					{
						//
						{
							//
							var sort_span = document.createElement("SPAN");
							
							//
							sort_span.className = "sort_table";
						}
						
						//
						{
							//
							var sort_asc = document.createElement("IMG");
							
							//
							sort_asc.src = "/d/img/arrow_short_down_blue.png";
							sort_asc.className = "sort_icon";
							sort_asc.setAttribute("onclick", "sort_table(this, SORT_ASC);");
							
							//
							sort_span.appendChild(sort_asc);
						}

						//
						{
							//
							var sort_desc = document.createElement("IMG");
							
							//
							sort_desc.src = "/d/img/arrow_short_up_blue.png";
							sort_desc.className = "sort_icon";
							sort_desc.setAttribute("onclick", "sort_table(this, SORT_DESC);");
							
							//
							sort_span.appendChild(sort_desc);
						}
						
						//
						filter_content.appendChild(sort_span);
					}
				}
				
				console_log(filter_content.innerHTML);
				
				//
				if(filter_content.innerHTML != "")
				{
					//
					new_filter_cell.appendChild(filter_content);
				}

				//
				filter_row.appendChild(new_filter_cell);
			}
		}
		
		//
		if(add_manage_table)
		{
			//
			current_table.tHead.appendChild(filter_row);
		}
	}
}

//
window.onload_manager.add("init_table_sort();");

/**
 * Sorts all table rows in a table.
 * 
 * @param element - An element inside a th in table head.
 * @param sort_direction - One of our sort order constants.
 */
function sort_table(element, sort_direction)
{
	// If element is not an object.
	if(typeof(element) != "object" || element == null)
	{
		// Add an error to the console log.
		console_log("element is '" + typeof(element) + "', an object was expected. In sort_table().");
		
		// Exit.
		return false;
	}
	
	// If sort_direction is not a number or is not one of our sort constants.
	if(typeof(sort_direction) != "number" || (sort_direction != SORT_NONE && sort_direction != SORT_ASC && sort_direction != SORT_DESC))
	{
		// Set sort_direction to SORT_ASC.
		sort_direction = SORT_ASC;
	}
	
	// Get first parent of element, that is a <th>.
	var current_th = get_first_parent_by_tagname("TH", element);
	
	// If no parent th was found.
	if(!current_th)
	{
		// Add an error to console.
		console_log("sort_table() was not triggered by an element inside a <th>.");
		
		// Exit.
		return false;
	}
	
	// Get first parent of current th, that is a table.
	var current_table = get_first_parent_by_tagname("TABLE", element);
	
	// If no parent table was found.
	if(!current_table)
	{
		// Add an error to console.
		console_log("current_th has no parent that is a table in sort_table().");
		
		// Exit.
		return false;
	}

	// Store some data on the table about the sort.
	{
		// If current table don't have the sort_data object.
		if(typeof(current_table.sort_data) != "object" || current_table.sort_data == null)
		{
			// Create an array to store data about the sort in.
			current_table.sort_data = [];
			
			// Set sont initialization to true.
			current_table.sort_data.init = true;
			
			// Set last_real_cell_index to null.
			current_table.sort_data.last_real_cell_index = null;
			
			// Create an array to store the original order of the rows in.
			current_table.sort_data.default_order = [];
		}
	
		// Store which th we are currently sorting by.
		current_table.sort_data.current_th = current_th;
		
		// Calculate and store that th's cell index.
		current_table.sort_data.real_cell_index = get_cell_index_in_tr(current_th.parentNode, current_th.cellIndex);
		
		// If current sort direction is the same as last one.
		if(current_table.sort_data.sort_direction == sort_direction && current_table.sort_data.real_cell_index == current_table.sort_data.last_real_cell_index)
		{
			// Go back to the unsorted order.
			current_table.sort_data.sort_direction = SORT_NONE;
		}
		else
		{
			// Store the sort direction.
			current_table.sort_data.sort_direction = sort_direction;
		}
		
		// Set last_real_cell_index to real_cell_index.
		current_table.sort_data.last_real_cell_index = current_table.sort_data.real_cell_index;
	}

	// Create a place to copy all tbodies and rows, in the sorted order, before we uppdate the real tbodies.
	var temporary_tbodies_storage = [];

	// Loop through all tbodies in current table.
	for(var tbody_index in current_table.tBodies)
	{
		// Get current tbody.
		var current_tbody = current_table.tBodies[tbody_index];
		
		// If current tbody is not an object.
		if(typeof(current_tbody) != "object" || current_tbody == null)
		{
			// Continue to next tbody.
			continue;
		}
		
		// Create an array in the temporary storage to store table rows for current tbody.
		temporary_tbodies_storage[tbody_index] = [];
		
		// If we are initializing the sort.
		if(current_table.sort_data.init)
		{
			// Add current tbody to the default order, and create an array to store the default order of it's rows in. 
			current_table.sort_data.default_order[tbody_index] = [];
		}

		// Loop thruogh current tbody's rows.
		for(var row_index in current_tbody.rows)
		{
			// Get current row.
			var current_row = current_tbody.rows[row_index];
			
			// If current row is not an object.
			if(typeof current_row != "object" || current_row == null)
			{
				// Continue to next row.
				continue;
			}
			
			// Add current table row to current tbody's array in the temporary storage.
			temporary_tbodies_storage[tbody_index].push(current_row);
			
			// If we are initializing the sort.
			if(current_table.sort_data.init)
			{
				// Add current row to current tbody's default order.
				current_table.sort_data.default_order[tbody_index].push(current_row);
			}
		}

		// If we are sorting by any other direction then none.
		if(current_table.sort_data.sort_direction != SORT_NONE)
		{
			// Sort current tbody's storage, with our own sort function sort_rows().
			temporary_tbodies_storage[tbody_index].sort(sort_rows);

			// Loop through all table rows in current tbody's storage.
			for(var row_index in temporary_tbodies_storage[tbody_index])
			{
				// Get current row.
				var current_row = temporary_tbodies_storage[tbody_index][row_index];
				
				// If current row is not an object.
				if(typeof(current_row) != "object" || current_row == null)
				{
					// Go to next row.
					continue;
				}

				// By appending current row in to the real tbody, we will move it from it's original position to the bottom. After every row is moved to the bottom the first we moved will be att the top.
				current_table.tBodies[tbody_index].appendChild(current_row);
			}
		}
		else
		{
			// Loop through all table rows in current tbody's storage.
			for(var row_index in current_table.sort_data.default_order[tbody_index])
			{
				// Get current row.
				var current_row = current_table.sort_data.default_order[tbody_index][row_index];
				
				// If current row is not an object.
				if(typeof(current_row) != "object" || current_row == null)
				{
					// Go to next row.
					continue;
				}

				// By appending current row in to the real tbody, we will move it from it's original position to the bottom. After every row is moved to the bottom the first we moved will be att the top.
				current_table.tBodies[tbody_index].appendChild(current_row);
			}
		}
	}
	
	// Update the sort classes on all divs containing sort images.
	{
		// First we reset all classes, so none is marked as the one we are sorting by.
		{
			// Get all div's containing the sort iamges.
			var sort_table_spans = current_table.getElementsByClassName("sort_table");
		
			// If the array of div's is an object.
			if(typeof(sort_table_spans) == "object" && sort_table_spans != null)
			{
				// Loop through all divs.
				for(var sort_table_index in sort_table_spans)
				{
					// Get current div.
					var current_sort_table_span = sort_table_spans[sort_table_index];
					
					// If current div is not an object.
					if(typeof(current_sort_table_span) != "object" || current_sort_table_span == null)
					{
						// Continue to next div.
						continue;
					}
					
					// Trying to remove all sort classes on current div.
					current_sort_table_span.className = remove_item_from_list("sort_none", current_sort_table_span.className);
					current_sort_table_span.className = remove_item_from_list("sort_asc", current_sort_table_span.className);
					current_sort_table_span.className = remove_item_from_list("sort_desc", current_sort_table_span.className);
				}
			}
		}
		
		// Update the class on the sort div, that the table is currently sorted by.
		{
			// Get the current sort table div, that the table is sorted by.
			var current_sort_table_span = get_first_parent_by_tagname("SPAN", element);
			
			// Switch the possible sort direction.
			switch(current_table.sort_data.sort_direction)
			{
				// If the sort order is SORT_NONE.
				case SORT_NONE:
				{
					// Add sort_none as current sort div's class.
					current_sort_table_span.className = add_item_to_list("sort_none", current_sort_table_span.className);
					
					break;
				}
				
				// If the sort order is SORT_ASC.
				case SORT_ASC:
				{
					// Add sort_asc as current sort div's class.
					current_sort_table_span.className = add_item_to_list("sort_asc", current_sort_table_span.className);
					
					break;
				}
				
				// If the sort order is SORT_DESC.
				case SORT_DESC:
				{
					// Add sort_desc as current sort div's class.
					current_sort_table_span.className = add_item_to_list("sort_desc", current_sort_table_span.className);
					
					break;
				}
				
				// If the sort order is something else.
				default:
				{
					// Add an error to console.
					console_log("'" + current_table.sort_data.sort_direction + "' is not a valid sort order in sort_table().");
					
					// Exit.
					return false;
				}
			}
		}
	}
	
	// Mark the table as sorted or not sorted.
	{
		// If current table's sort direction is not SORT_NONE.
		if(current_table.sort_data.sort_direction != SORT_NONE)
		{
			// Set table as sorted.
			current_table.sorted = true;
		}
		else
		{
			// Else set table as not sorted.
			current_table.sorted = false;
		}
	}
	
	// Add or remove the modified class on the sort/filter row.
	{
		// Get the sort/filter row.
		var filter_row = get_first_parent_by_tagname("TR", current_th);
		
		// If filter row is not an object.
		if(typeof(filter_row) != "object" || filter_row == null)
		{
			// Add an error to console.
			console_log("No filter row found in sort_table().");
			
			// Exit.
			return false;
		}
		
		// If our table is sorted or filtered.
		if((typeof(current_table.sorted) == "boolean" && current_table.sorted) || (typeof(current_table.filtered) == "boolean" && current_table.filtered))
		{
			console_log("Sorted = " + current_table.sorted + "; filtered = " + current_table.filtered);
			
			// Add the modified class to the filter row.
			filter_row.className = add_item_to_list("modified", filter_row.className);
		}
		else
		{
			// Else remove the modified class.
			filter_row.className = remove_item_from_list("modified", filter_row.className);
		}
	}
	
	// Set initialization to false.
	current_table.sort_data.init = false;
}

/**
 * Get content of all cells in a table row, that is relevant for a given column.
 * 
 * @param table_row - A table row to get the content of the cells from.
 * @param head_cell_start_position - The position of the td that we looking for.
 * @param head_cell_end_position - The the td's postion + it's colspan.
 */
function get_cells_content(table_row, head_cell_start_position, head_cell_end_position)
{
	var first_index = -1;
	var last_index = 0;
	var real_index = 0;

	// If the td's end position is less the it's start postion.
	if(head_cell_end_position < head_cell_start_position)
	{
		// Set it's end position to it's start position. 
		head_cell_end_position = head_cell_start_position;
	}

	while(head_cell_end_position > 0)
	{
		var current_colspan = table_row.cells[real_index].colSpan;

		//console_log("index: " + real_index + "; colspan: " + current_colspan + "; start: " + head_cell_start_position + "; end: " + head_cell_end_position);
		
		if(current_colspan)
		{
			head_cell_start_position -= current_colspan;
			head_cell_end_position -= current_colspan;
		}
		else
		{
			head_cell_start_position--;
			head_cell_end_position--;
		}

		if(first_index == -1 && head_cell_start_position < 0)
		{
			first_index = real_index;
		}

		if(head_cell_end_position < 0)
		{
			break;
		}

		real_index++;

	}

	last_index = real_index;

	if(first_index == -1)
	{
		first_index = real_index;
	}

	var text = "";
	var cell_text = "";

	for(var real_index = first_index; real_index <= last_index; real_index++)
	{
		// clean cell text 
		cell_text = table_row.cells[real_index].innerHTML.replace(/<[^>]*>/g, "").replace(/^[ \s]*/g, "").replace(/[ \s]*$/g, "");

		// if cell contains no text ..
		if(!cell_text)
		{
			// ..clean again but keep title or alt text from tags 
			cell_text = table_row.cells[real_index].innerHTML.replace(/<[^>]*title=['"]([^'">]+)['"][^>]*>/g, " $1 ").replace(/<[^>]*alt=['"]([^'">]+)['"][^>]*>/g, " $1 ").replace(/<[^>]*>/g, "").replace(/^[ \s]*/g, "").replace(/[ \s]*$/g, "");
		}

		// append cell_text to the big text 
		text += cell_text + "\t";
	}

	return text.replace(/[ \s]*$/g, "");
}

/**
 * Get the index of a td, by adding all colspans from tds to the left.
 * 
 * @param table_row - The row to get cell index from.
 * @param cell_position - Position of an td.
 */
function get_cell_index_in_tr(table_row, cell_position)
{
	//console_log("Getting the index for the TH nr " + cell_position);

	// Set index to 0.
	var real_position = 0;

	// Loop through all cells in current table row.
	for(var current_cell_index in table_row.cells)
	{
		// If current_cell_index is more or same as cell_position.
		if(current_cell_index >= cell_position)
		{
			// Exit loop.
			break;
		}
		
		// Get current td.
		var current_td = table_row.cells[current_cell_index];
		
		// If current td is not an object.
		if(typeof(current_td) != "object" || current_td == null)
		{
			// Go to next cell.
			continue;
		}
		
		// If current td has colspan.
		if(current_td.colSpan)
		{
			// Add the colspan to real position.
			real_position += current_td.colSpan;
		}
		else
		{
			// Else increase real_position by 1.
			real_position++;
		}
	}

	// Return real position.
  return real_position;
}

/**
 * Sort rows.
 * 
 * @param row1 - One row to compare against an other.
 * @param row2 - The other row.
 */
function sort_rows(row1, row2)
{
	// If row1 don't have any cells.
	if(row1.cells.length == 0)
	{
		// Return that row1 shall have a heigher position then row2.
		return -1;
	}
	
	// If row2 don't have any cells.
	if(row2.cells.length == 0)
	{
		// Return that row2 shall have a heigher position then row1.
		return 1;
	}
	
	//
	if(row1.getElementsByTagName("TD").length == 0)
	{
		//
		return -1;
	}

	//
	if(row2.getElementsByTagName("TD").length == 0)
	{
		//
		return 1;
	}
	
	// If row1 has one of our footer classes.
	if(is_item_in_list("foot", row1.className) || is_item_in_list("footer", row1.className) || is_item_in_list("summary", row1.className))
	{
		// Return that row2 shall have a heigher position then row1.
		return 1;
	}
	
	// If row2 has one of our footer classes.
	if(is_item_in_list("foot", row2.className) || is_item_in_list("footer", row2.className) || is_item_in_list("summary", row2.className))
	{
		// Return that row1 shall have a heigher position then row2.
		return -1;
	}
	
	// Get the table element of the rows that we sorting.
	var current_table = get_first_parent_by_tagname('TABLE', row1);
	
	// Get content of the cell we are sorting by from row1.
	var row1_cell_content = get_cells_content(row1, current_table.sort_data.real_cell_index, current_table.sort_data.real_cell_index + current_table.sort_data.current_th.colSpan - 1);
	
	// Get content of the cell we are sorting by from row2.
	var row2_cell_content = get_cells_content(row2, current_table.sort_data.real_cell_index, current_table.sort_data.real_cell_index + current_table.sort_data.current_th.colSpan - 1);

	//console_log("Com.." + row1_cell_content + " med " + row2_cell_content + " = " + nature_cmp(row1_cell_content, row2_cell_content));

	// Switch over sort direction.
	switch(current_table.sort_data.sort_direction)
	{
		// If we are sorting decending.
		case SORT_DESC:
		{
			// Compare the two rows cell content, and return the oposit result.
			return -nature_cmp(row1_cell_content, row2_cell_content);
		}
		
		// If we are sorting acending.
		case SORT_ASC:
		{
			// Compare the two rows cell content, and return the result.
			return nature_cmp(row1_cell_content, row2_cell_content);
		}
		
		// If we are sorting the orginal way.
		case SORT_NONE:
		{
			// Compare the two rows cell content, and return the result.
			return nature_cmp(row1_cell_content, row2_cell_content);
		}
		
		// If sort direction is something else.
		default:
		{
			// Add error to console.
			console_log(current_table.sort_data.sort_direction + "is not a valid sort direction.");
			
			// Exit.
			return;
		}
	}
}

/**
 * Compare two strings.
 * 
 * @param string1 - One string to compare with an other.
 * @param string2 - The other string.
 */
function nature_cmp(string1, string2)
{
	// If the strings are the same.
	if(string1 == string2)
	{
		// Return 0.
		return 0;
	}

	// Slit string1 into a list by \t.
	var string1_list = string1.split('\t', 2);
	
	// Slit string2 into a list by \t.
	var string2_list = string2.split('\t', 2);

	// If both strings length is nore then 1.
	if(string1_list.length > 1 && string2_list.length > 1)
	{
		//console_log("Ja, " + string1_list.length + ", '" + string1_list[0] + "' vs '" +  string2_list[0] + "'\n");
		
		// 
		if(string1_list[0] == string2_list[0])
		{
			//
			return nature_cmp(string1_list[1], string2_list[1]);
		}

		//
		var first_column_compare = nature_cmp(string1_list[0], string2_list[0]);;
		
		//
		if(first_column_compare)
		{
			//
			return first_column_compare;
		}
		else
		{
			//
			return nature_cmp(string1_list[1], string2_list[1]);
		}
	}
	else
	{
		//console_log("Nej");
	}

	// remove leading spaces, removie leading zeroes, replace month names with date number 
	an = nature_cmp_value(string1);
	bn = nature_cmp_value(string2);

	if(!isNaN(an) && !isNaN(bn))
	{
		if(an < bn)
		{
			return -1;
		}
		else if(bn < an)
		{
			return 1;
		}
		else
		{
			// if same integer, do a normat text sort 
			return String(string1).localeCompare(String(string2));
		}
	}
	else
	{
		return String(string1).localeCompare(String(string2));
	}
}

function nature_cmp_value(text)
{
	if(text.match(/[0-9]/))
	{
		return parseInt(text.replace(/&nbsp;/,"").replace(/\s+/g,"").replace(/^0+/,"").replace(/Jan/i,"1 ").replace(/Feb/i,"2 ").replace(/Mar/i,"3 ").replace(/Apr/i,"4 ").replace(/May/i,"5 ").replace(/Jun/i,"6 ").replace(/Jul/i,"7 ").replace(/Aug/i,"8 ").replace(/Sep/i,"9 ").replace(/Oct/i,"10 ").replace(/Nov/i,"11 ").replace(/Dec/i,"12 "));
	}
	else
	{
		return NaN;
	}
}
