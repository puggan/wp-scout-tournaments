/**
* Project Interfaceways, all source code and data files except images,
* Copyright 2008 Jonas Lihnell
*
* Permission is granted to FaceWays to use and modify as they see fit.
**/

/**
	Delay the sale buttons.

	@param seconds Seconds to delay the buttons.
**/
function delay_sales(seconds)
{
	// Create function to run later.
	var run_delay = function()
	{
		// Get sales_delay input.
		var sales_delay = get_element("sales_delay");

		// If we got the element.
		if(sales_delay)
		{
			// Set it's value to 1.
			sales_delay.value = "1";

			// Set it's class to valid.
			sales_delay.className = replace_item_in_list("clean", "dirty", sales_delay.className);

			// Revalidate all forms.
			validate_forms();
		}
	}

	// Run the delay function after x seconds.
	setTimeout(run_delay, 1000 * seconds);
}

//
if(typeof(String.prototype.trim) != "object" || typeof(String.prototype.trim) != "function")
{
	//
	String.prototype.trim = function()
	{
		//
		return this.replace(/^\s+|\s+$/g, '');
	};
}

/**
	Check if an value is in a list.

	@param item - The value to check for.
	@param list - The lsit to check in.
**/
function is_item_in_list(item, list)
{
	// If we don't have an item.
	if(typeof(item) != "string")
	{
		// Add an error to console.
		console_log("item is not a string in is_item_in_list().");
		
		// Exit.
		return false;
	}

	// If we don't have a list.
	if(typeof(list) != "string")
	{
		//
		if(typeof(list) != "undefined")
		{
			// Add an error to console.
			console_log("list is of type '" + typeof(list) + "' but a string was expected, in is_item_in_list().");
		}
		
		// Exit.
		return false;
	}
	
	// Pad list with " ".
	list = " " + list + " ";
	
	// Return if item is in list.
	return (list.indexOf(" " + item + " ") == -1 ? false : true);
}

/**
 * Add a value to a list.
 * 
 * @param item - The value to be added to the list.
 * @param list - The list to add the value in.
**/
function add_item_to_list(item, list)
{
	// If we don't have a list.
	if(typeof(list) != "string")
	{
		// Exit.
		return item;
	}

	// If list is empty.
	if(list == "")
	{
		// Return item.
		return item;
	}

	// If item is not allready in list.
	if(!is_item_in_list(item, list))
	{
		// Add item to list.
		list += " " + item;
	}
	
	// Return new list.
	return list;
}

/**
 * Remove a value from a list.
 * 
 * @param item - The value to be removed.
 * @param list - The list to search for the value in.
 * 
 * @return - The new list.
 */
function remove_item_from_list(item, list)
{
	// If we don't have a list.
	if(typeof(list) != "string" || list == "")
	{
		// Return an empty string.
		return "";
	}

	// If the item is not in the list.
	if(!is_item_in_list(item, list))
	{
		// Just return list.
		return list;
	}
	
	// While item exists in list.
	while(is_item_in_list(item, list))
	{
		// Pad string with " ".
		list = " " + list + " ";
		
		// Get the first position of our item in the list.
		var position = list.indexOf(" " + item + " ");
		
		// Remove the first apearence of item from list.
		list = list.substring(0, position) + list.substring(position + item.length + 1);
	}
	
	// Trim new list and return it.
	return list.trim();
}

/**
 * Replace a value with another one, in an array.
 * 
 * @param from - The value to be preplaced.
 * @param to - The new value.
 * @param list - The aray to search in.
 * 
 * @return - The new list.
 */
function replace_item_in_list(from, to, list)
{
	// Remove the old value from the array.
	list = remove_item_from_list(from, list);

	// Add the new value to the array.
	list = add_item_to_list(to, list);

	// Return the new array.
	return list;
}

/**
	Check if a value exists in an array.

	@param needle The value we look for in the array.
	@param haystack The array to look in.
**/
function in_array(needle, haystack)
{
	// If the array is an not object.
	if(typeof(haystack) != "object" || haystack == null)
	{
		// Exit.
		return false;
	}

	// Iterate through the items in the array.
	for(var item in haystack)
	{
		// Compare current item with our needle.
		if(haystack[item] == needle)
		{
			// Return true if they are the same.
			return true;
		}
	}

	// Return false if the array was not an object.
	return false;
}

/**
	Check if a value exists in an array.

	@param needle The value we look for in the array.
	@param haystack The array to look in.
**/
function array_search(needle, haystack)
{
	// If the array is not an object.
	if(typeof(haystack) != "object" || haystack == null)
	{
		// Exit.
		return false
	}

	// Iterate through the items in the array.
	for(var item in haystack)
	{
		// Compare current item with our needle.
		if(haystack[item] == needle)
		{
			// Return true if they are the same.
			return item;
		}
	}

	// Return false if the array was not an object.
	return false;
}

/**
	Get an element by id, if the element exists, is of the right data type and has the right tag name.

	Returns the element if everything gose right, else it returns false.

	@param element_id Id of the element we trying to get.
	@param element_type Which data type to check for.
	@param element_tag Which tag name to check for.
**/
function get_element(element_id, element_type, element_tag)
{
	// If element id is not a string.
	if(typeof(element_id) != "string" || element_id == "")
	{
		//
		console_log("element_id is not a string in get_element().");
		
		// Exit.
		return false;
	}

	// Get the element.
	var element_object = document.getElementById(element_id);

	// If we don't have an element.
	if(typeof(element_object) != "object" || element_object == null)
	{
		// Return false.
		return false;
	}

	// Return the element.
	return element_object;
}

/**
 * Open a new window of Google Maps.
 *
 * @param address_id A string with the first part of the name, of all inputs in an address.
 */
function open_google_maps(address_id)
{
	// If address_id is not a string.
	if(typeof(address_id) != "string" || address_id == "")
	{
		// Exit.
		return false;
	}

	// Get street name element.
	var street_name = get_element(address_id + "_street_name");

	// Get street number element.
	var street_number = get_element(address_id + "_street_number");

	// Get post number element.
	var post_number = get_element(address_id + "_post_number");

	// Get post city element.
	var post_city = get_element(address_id + "_post_city");

	// We don't want to send undefined content to google, so we only open goole maps if all four fields has content.
	if(street_name.value && street_number.value && post_number.value && post_city.value)
	{
		// Google Maps
		window.open("http://maps.google.com/maps?f=q&source=s_q&hl=sv&geocode=&ie=UTF8&z=18&q=" + street_name.value + " " + street_number.value + ", " + post_number.value + ", Sweden", "_blank");
	}
}

/**
 * Open a new window of 121.nu.
 *
 * @param company_identification A string with a company's identification.
 */
function open_one2one(company_identification)
{
	// If company_identification is not a string.
	if(typeof(company_identification) != "string" || company_identification == "")
	{
		// Exit.
		return false;
	}

	// Open 121.nu.
	window.open("http://www.121.nu//onetoone/sokresultat.aspx?typeofsearch=standard&sokord=" + company_identification + "&lokaldel=&ort=", "_blank");
}

/*
 * Fix for visiting an url and pass by post data.
 * It creates a hidden form with one hidden input for each value you have in the post_data-array.
 *
 * @param url The url you want to visist and send the post data to.
 * @param post_data An array with the data you want to send through post.
 */
function link_to_post_url(url, post_data)
{
	// If post-data is not a string.
	if(typeof(post_data) != "string" || post_data == "")
	{
		// Exit.
		return false;
	}

	// If url is not a string.
	if(typeof(url) != "string" || url == "")
	{
		// Exit.
		return false;
	}

	// Parse JSON string to an object.
	post_data = parseJSON(decodeURIComponent(post_data));

	// Create a form
	var form = document.createElement("form");

	// Set the forms action to the url param.
	form.action = url;

	// Set the forms method to post.
	form.method = "post";


	// Loop through the array of post params.
	for(var post_index in post_data)
	{
		// Create an input for the current param.
		var input = document.createElement("INPUT");

		// Set the inputs value.
		input.value = post_data[post_index];

		// Set the inputs type to hidden.
		input.type = "hidden";

		// Set the inputs name.
		input.name = post_index;

		// Make the input a child om the form.
		form.appendChild(input);
	}

	// Add the form to the body.
	document.body.appendChild(form);

	// Submit the form.
	form.submit();
}

function init_work_in_grogress()
{
	// Get all forms
	var forms = document.getElementsByTagName("FORM");

	// If there are no forms.
	if(typeof(forms) != "object")
	{
		// Exit.
		return false;
	}

	// Loop through all forms.
	for(var form_index in forms)
	{
		// Store current form.
		var current_form = forms[form_index];

		// If current for is not an object.
		if(typeof(current_form) != "object")
		{
			continue;
		}

		// Callback function to run on the event.
		var callback = function(e)
		{
			// If user have clicked on a export button.
			if(typeof(window.is_exporting) == "boolean" && window.is_exporting)
			{
				// Set is_exporting to false.
				window.is_exporting = false;

				// Clear target on all forms.
				setTimeout("clean_form_targets()", 2000);
			}
			else
			{
				// Else page will soon reload, and we show work in progress.
				fade_in("status", 10, 0);
			}
		}

		// Add onsubmit event listener on current form.
		add_event_listener("submit", callback, current_form);
	}
}

// Add init_work_in_grogress to onload manager.
onload_manager.add("init_work_in_grogress()");

function add_event_listener(event_type, callback, event_element, capture)
{
	// If event_type is not a string.
	if(typeof(event_type) != "string" || event_type == "")
	{
		// Exit.
		console_log("add_event_listener: no event type.");
		return false;
	}

	// callback is not a function.
	if(typeof(callback) != "object" && typeof(callback) != "function")
	{
		// Exit.
		return false;
	}

	// If no event_element was passed.
	if(typeof(event_element) != "object" || event_element == null)
	{
		// Set window as event_element.
		event_element = window;
	}

	// Check if we can use addEventListener.
	if(typeof(event_element.addEventListener) == "function" || typeof(event_element.addEventListener) == "object")
	{
		// Create event listener with addEventListener.
		return event_element.addEventListener(event_type, callback, capture);
	}

	// Create event listener the microsoft way.
	return event_element.attachEvent("on" + event_type, callback);
}

//
function console_log(log_message)
{
	// If we don't have a console.
	if(typeof(console) != "object" || console == null)
	{
		// Exit.
		return false;
	}

	// If we don't have the console.log function.
	if(typeof(console.log) != "object" && typeof(console.log) != "function")
	{
		// Exit.
		return false;
	}

	// Write to console log.
	console.log(log_message);
}

//
function clean_form_targets()
{
	// Get all forms.
	var forms = document.getElementsByTagName("FORM");

	// If forms not an object.
	if(typeof(forms) != "object" || forms == null)
	{
		//
		console_log("form collection is of type " + typeof(forms) + " instead of object.");

		// Exit.
		return false;
	}

	// Loop through each form.
	for(var form_index in forms)
	{
		// Get current form.
		var current_form = forms[form_index];

		// Check if current form is not an object.
		if(typeof(current_form) != "object" || current_form == null)
		{
			// Move to next form.
			continue;
		}

		// Remove target attribute from current form.
		current_form.removeAttribute("target");
	}

	//
	setTimeout('fade_out("status", 10, 0)', 5000);
}

//
function select_days(days, target_select_id)
{
	// If target select id is not a string.
	if(typeof(target_select_id) != "string" || target_select_id == "")
	{
		// Exit.
		return false;
	}

	days = parseInt(days);

	// If days is not a number.
	if(typeof(days) != "number")
	{
		// Exit
		return false;
	}

	// Get element.
	var target_select = get_element(target_select_id);

	// If we got an element.
	if(!target_select)
	{
		//
		return false;
	}
	
	// Switch days.
	switch(days)
	{
		// Select all.
		case 1:
		{
			//
			target_select.options[0].selected = true;
			target_select.options[1].selected = true;
			target_select.options[2].selected = true;
			target_select.options[3].selected = true;
			target_select.options[4].selected = true;
			target_select.options[5].selected = true;
			target_select.options[6].selected = true;
			
			//
			break;
		}

		// Select work days.
		case 2:
		{
			//
			target_select.options[0].selected = true;
			target_select.options[1].selected = true;
			target_select.options[2].selected = true;
			target_select.options[3].selected = true;
			target_select.options[4].selected = true;
			target_select.options[5].selected = false;
			target_select.options[6].selected = false;
			
			//
			break;
		}

		// Select weekends.
		case 3:
		{
			//
			target_select.options[0].selected = false;
			target_select.options[1].selected = false;
			target_select.options[2].selected = false;
			target_select.options[3].selected = false;
			target_select.options[4].selected = false;
			target_select.options[5].selected = true;
			target_select.options[6].selected = true;
			
			//
			break;
		}
	}
}

// FIXME: What is this?
function get_worktime_plan(plan_id)
{
	//
	var callback = function(data)
	{
		// Get all needed elements.
		var planned_worktime_name = get_element("planned_worktime_name");
		var worktime_type_id      = get_element("worktime_type_id");
		var company_id            = get_element("company_id");
		var pdate_from            = get_element("pdate_from");
		var date_to               = get_element("date_to");
		var ate_from              = get_element("ate_from");
		var te_to                 = get_element("te_to");
		var repetition_days       = get_element("repetition_days");
	}

	//
	var request_data = [];
	
	//
	request_data["plan_id"] = plan_id;
	
	//
	ajax_get_request("/src/ajax/get_worktime_plan.php", request_data, callback);
}

//
function str_pad(str, pad_string, length, direction)
{
	//
	if(typeof(str) != "string")
	{
		//
		console_log("str_pad: str is not a string.");
		
		//
		return false;
	}

	//
	if(typeof(pad_string) != "string")
	{
		//
		console_log("str_pad: pad_string is not a string.");
		
		//
		return false;
	}

	//
	if(typeof(length) != "number")
	{
		//
		console_log("str_pad: length is not a number.");
		
		//
		return false;
	}

	//
	if(typeof(direction) != "string")
	{
		//
		direction = "left";
	}

	//
	if(str.length >= length)
	{
		//
		return str;
	}

	//
	while(str.length < length)
	{
		//
		switch(direction)
		{
			//
			case "left":
			{
				//
				str = pad_string + str;
				
				//
				break;
			}

			//
			case "right":
			{
				//
				str = str + pad_string;
				
				//
				break;
			}

			//
			default:
			{
				//
				console_log("str_pad: '" + direction + "' is not a valid direction.");
				
				//
				return false;
			}
		}
	}

	//
	return str;
}

//
function format_time(h, m, s)
{
	//
	if(h != (h % 24))
	{
		//
		return false;
	}
	
	//
	if(m != (m % 60))
	{
		//
		return false;
	}
	
	//
	if(s != (s % 60))
	{
		//
		return false;
	}

	//
	var t = '';
	
	//
	if(h < 10)
	{
		//
		t += '0';
	}
	
	//
	t += h + ':';
	
	//
	if(m < 10)
	{
		t += '0';
	}
	
	//
	t += m + ':';
	
	//
	if(s < 10)
	{
		//
		t += '0';
	}
	
	//
	t += s;

	//
	return t;
}

//
function format_date(y, m, d)
{
	//
	if(y < 0)
	{
		//
		return false;
	}
	
	//
	if(y < 100)
	{
		//
		if(y < 35)
		{
			y += 2000;
		}
		else
		{
			y += 1900;
		}
	}
	
	//
	if(y < 1000)
	{
		//
		return false;
	}
	
	//
	if(m < 1 || m > 12)
	{
		//
		return false;
	}
	
	//
	if(d < 1 || d > 31)
	{
		//
		return false;
	}
	
	//
	var t = y + '-';
	
	//
	if(m < 10)
	{
		//
		t += '0';
	}
	
	//
	t += m + '-';
	
	//
	if(d < 10)
	{
		//
		t += '0';
	}
	
	//
	t += d;

	//
	return t;
}

//
function get_form_values(form, include_text)
{
	// If form is not an object.
	if(typeof(form) != "object" || form == null)
	{
		//
		console_log("form is not an object (" + typeof(form) + ")");

		// Exit.
		return;
	}

	//
	if((typeof(form.elements) != "object" && typeof(form.elements) != "function") || form.elements == null)
	{
		//
		console_log("There are no form elements in form");
		
		//
		return;
	}

	//
	var form_elements = [];

	//
	for(var element_index in form.elements)
	{
		//
		var current_element = form.elements[element_index];

		//
		if(typeof(current_element) != "object" || current_element == null)
		{
			//
			continue;
		}

		//
		if(!current_element.name)
		{
			//
			continue;
		}

		//
		var current_element_name = element_name_to_js_safe_name(current_element);

		//
		switch(current_element.tagName)
		{
			//
			case "INPUT":
			{
				//
				switch(current_element.type)
				{
					//
					case "checkbox":
					case "radio":
					{
						//
						if(current_element.checked)
						{
							//
							form_elements[current_element_name] = current_element.value;
							
							//
							if(include_text)
							{
								//
								var current_element_name_text = "element_text[" + (current_element_name + "[").replace("[", "][").substring(0, current_element_name.length + 1);

								//
								form_elements[current_element_name_text] = current_element.title;
							}
						}
						
						//
						break;
					}

					//
					case "text":
					case "password":
					case "hidden":
					{
						//
						form_elements[current_element_name] = current_element.value;
						
						//
						break;
					}

					//
					case "submit":
					{
						//
						if(typeof(current_element.form.last_button) == "string" && current_element.form.last_button != "")
						{
							//
							if(current_element_name == current_element.form.last_button)
							{
								//
								form_elements[current_element_name] = current_element.value;
							}
						}

						//
						break;
					}
				}

				//
				break;
			}

			//
			case "TEXTAREA":
			{
				//
				form_elements[current_element_name] = current_element.value;
				
				//
				break;
			}

			//
			case "SELECT":
			{
				//
				if(current_element.multiple)
				{
					// FIXME: multiple?
					form_elements[current_element_name] = [];
					
					//
					for(var option_index in current_element.options)
					{
						//
						if(current_element.options[option_index].selected)
						{
							//
							form_elements[current_element_name].push(current_element.options[option_index].value);
							
							//
							if(include_text)
							{
								//
								var current_element_name_text = "element_text[" + (current_element_name + "[").replace("[", "][").substring(0, current_element_name.length + 1);

								//
								form_elements[current_element_name_text] = [];
								
								//
								form_elements[current_element_name_text].push(current_element.options[current_element.selectedIndex].text);
							}
						}
					}
				}
				else
				{
					//
					if(current_element.options[current_element.selectedIndex])
					{
						//
						form_elements[current_element_name] = current_element.options[current_element.selectedIndex].value;
						
						//
						if(include_text)
						{
							//
							var current_element_name_text = "element_text[" + (current_element_name + "[").replace("[", "][").substring(0, current_element_name.length + 1);

							//
							form_elements[current_element_name_text] = current_element.options[current_element.selectedIndex].text;
						}
					}
					else
					{
						//
						console_log(current_element_name + "funkar inte");
					}
				}
				
				//
				break;
			}
		}
	}

	//
	return form_elements;
}

//
function element_name_to_js_safe_name(current_element)
{
	//
	if(typeof(current_element) != "object" || current_element == null)
	{
		//
		return false;
	}

	//
	if(typeof(current_element.form.input_name_counter) != "numeric")
	{
		//
		current_element.form.input_name_counter = 0;
	}

	//
	var current_element_name = current_element.name;

	//
	if(typeof(current_element_name) != "string" || current_element_name == "")
	{
		//
		return false;
	}

	//
	if(current_element_name.match(/[\[\]]$/))
	{
		//
		current_element_name = current_element_name.replace("[]", "[" + current_element.form.input_name_counter + "]");
		
		//
		current_element.form.input_name_counter += 1;
	}

	//
	return current_element_name;
}

//
function get_position(element)
{
	// If element is not an object.
	if((typeof(element) != "object" && typeof(element) != "function") || element == null)
	{
		// Add an error to the console.
		console_log("element is not an object (get_position).");

		// Exit.
		return false;
	}

	//
	if((typeof(element.offsetParent) != "object" && typeof(element.offsetParent) != "function") || element.offsetParent == null)
	{
		// Add an error to the console.
		console_log("element do not have offsetParent property (get_position).")

		// Exit.
		return false;
	}

	// Create two ints to store left and top postion in.
	var position_left = 0;
	var position_top = 0;

	//
	do
	{
		//
		position_left += element.offsetLeft;
		position_top += element.offsetTop;
	}
	while(obj = element.offsetParent);

	//
	var position = [];
	
	//
	position["left"] = position_left;
	position["top"] = position_top;

	//
	return position;
}

//
function get_first_parent_by_tagname(tagname, child_element)
{
	//
	if(typeof(tagname) != "string")
	{
		//
		console_log("tagname is not a string");

		//
		return false;
	}

	//
	if((typeof(child_element) != "object" && typeof(child_element) != "function") || child_element == null)
	{
		//
		console_log("child_element is not an object");
	}

	//
	while(child_element = child_element.parentNode)
	{
		//
		if(child_element.tagName == tagname)
		{
			//
			return child_element;
		}
	}

	//
	return false;
}

//
function sv_number_format(number, decimals)
{
	//
	var parts = number.toFixed(decimals).split('.');

	//
	switch(parts[0].length % 3)
	{
		//
		case 1:
		{
			//
			parts[0] = ' ' + parts[0];
		}
		
		//
		case 2:
		{
			//
			parts[0] = ' ' + parts[0];
		}
		
		//
		default:
		{
			//
			parts[0] = parts[0].replace(/(...)/g, '$1 ').trim();
		}
	}

	//
	if(decimals)
	{
		//
		return '"' + parts[0] + ',' + parts[1] + '"';
	}
	else
	{
		//
		return parts[0]
	}
}

//
function fix_export(button, export_value)
{
	//
	if(typeof(button) != "object" || button == null)
	{
		//
		console_log("button is of type " + typeof(button) + " instead of object");

		//
		return false;
	}

	//
	if(export_value == "true")
	{
		//
		window.is_exporting = true;
	}
	else
	{
		//
		window.is_exporting = true;
		
		//
		button.form.target = export_value;
	}

	//
	window.setTimeout(clean_form_targets, 2000);
}

//
function fix_date_input(input)
{
	//
	if(typeof(input) != "object" || input == null)
	{
		//
		console_log("input is of type " + typeof(input) + " instead of object in fix_date().");

		//
		return false;
	}

	// Do magic
	var date = fix_date(input.value.trim());
	
	//
	if(date)
	{
		//
		input.value = date;
		
		// dirty.
		validate_forms();
		
		//
		return true;
	}
	else
	{
		//
		return false;
	}
}

//
function fix_date(date)
{
	// match 20130128 AND 130128
	if(/^[0-9]+$/.test(date))
	{
		//
		switch(date.length)
		{
			//
			case 8:
			{
				//
				return date.substring(0,4) + '-' + date.substring(4,6) + '-' + date.substring(6,8);
			}
			
			//
			case 6:
			{
				//
				var year = date.substring(0,2);
				
				//
				if(year < 30)
				{
					//
					year = '20' + year;
				}
				else
				{
					//
					year = '19' + year;
				}
				
				//
				return year + '-' + date.substring(2,4) + '-' + date.substring(4,6);
			}
			default:
			{
				//
				return false;
			}
		}
	}

	// match 2013-01-28 AND 2013/01/28
	if(/^[0-9]{4}[/-][0-9]{2}[/-][0-9]{2}$/.test(date))
	{
		//
		return date.substring(0,4) + '-' + date.substring(5,7) + '-' + date.substring(8,10);
	}

	// match 13-01-28 AND 13/01/28
	if(/^[0-9]{2}[/-][0-9]{2}[/-][0-9]{2}$/.test(date))
	{
		//
		var year = date.substring(0,2);
		
		//
		if(year < 30)
		{
			//
			year = '20' + year;
		}
		else
		{
			//
			year = '19' + year;
		}
		
		//
		return year + '-' + date.substring(3,5) + '-' + date.substring(6,8);
	}

	//
	var matches = date.match(/^([0-9]+)[/-]([0-9]+)[/-]([0-9]+)$/);
	
	//
	if(matches)
	{
		//
		console_log("1: " + matches[1] + ", 2: " + matches[2] + ", 3: " + matches[3]);
		
		//
		var year = parseInt(matches[1]);
		
		//
		if(year > 999)
		{
			//
			//year = year;
		}
		else
		{
			//
			if(year > 99)
			{
				//
				year = 2000 + year;
			}
			else
			{
				//
				if(year < 30)
				{
					//
					year = 2000 + year;
				}
				else
				{
					//
					year = 1900 + year;
				}
			}
		}
		
		//
		month = parseInt(matches[2]);
		
		//
		if(month < 10)
		{
			//
			month = '0' + month;
		}
		
		//
		day = parseInt(matches[3]);
		
		//
		if(day < 10)
		{
			//
			day = '0' + day;
		}
		
		//
		return year + '-' + month + '-' + day;
	}

	//
	var matches = date.match(/^([0-9]+)[/]([0-9]+)([ -]+([0-9]+))?$/);
	
	//
	if(matches)
	{
		//
		var year = parseInt(matches[4]);
		
		//
		if(isNaN(year))
		{
			//
			year = 1900 + (new Date()).getYear();
		}
		else
		{
			//
			if(year < 30)
			{
				//
				year = 2000 + year;
			}
			else
			{
				//
				year = 1900 + year;
			}
		}
		
		//
		var month = matches[2];
		
		//
		if(month.length == 1)
		{
			//
			month = '0' + month;
		}

		//
		var day = matches[1];
		
		//
		if(day.length == 1)
		{
			//
			day = '0' + day;
		}

		//
		return year + '-' + month + '-' + day;
	}

	//
	date = date.replace(/[^0-9]+/g, '');

	//
	switch(date.length)
	{
		//
		case 8:
		{
			//
			return date.substring(0,4) + '-' + date.substring(4,6) + '-' + date.substring(6,8);
		}
		
		//
		case 6:
		{
			//
			var year = date.substring(0,2);
			
			//
			if(year < 30)
			{
				//
				year = '20' + year;
			}
			else
			{
				//
				year = '19' + year;
			}
			
			//
			return year + '-' + date.substring(2,4) + '-' + date.substring(4,6);
		}
		
		//
		default:
		{
			//
			return false;
		}
	}
}

//
function fix_time_input(input)
{
	//
	if(typeof(input) != "object" || input == null)
	{
		//
		console_log("input is of type " + typeof(input) + " instead of object in fix_time_input().");

		//
		return false;
	}

	// Do magic
	var time = fix_time(input.value.trim());

	//
	if(time)
	{
		//
		input.value = time;
		
		// dirty
		validate_forms();
		
		//
		return true;
	}
	else
	{
		//
		return false;
	}
}

//
function fix_time(time)
{
	//
	time = time.replace(/[^0-9]+/g, '');

	//
	switch(time.length)
	{
		//
		case 6:
		{
			//
			return time.substring(0,2) + ':' + time.substring(2,4) + ':' + time.substring(4,6);
		}
		
		//
		case 5:
		{
			//
			return '0' + time.substring(0,1) + ':' + time.substring(1,3) + ':' + time.substring(3,5);
		}
		
		//
		case 4:
		{
			//
			return time.substring(0,2) + ':' + time.substring(2,4) + ':00';
		}
		
		//
		case 3:
		{
			//
			return '0' + time.substring(0,1) + ':' + time.substring(1,3) + ':00';
		}
		
		//
		case 2:
		{
			//
			return time.substring(0,2) + ':00:00';
		}
		
		//
		case 1:
		{
			//
			return '0' + time.substring(0,1) + ':00:00';
		}
		
		//
		default:
		{
			//
			return false;
		}
	}
	
	//
	return true;
}

//
function fix_datetime_input(input)
{
	//
	if(typeof(input) != "object" || input == null)
	{
		//
		console_log("input is of type " + typeof(input) + " instead of object in fix_datetime_input().");

		//
		return false;
	}

	// Do magic
	var datetime = fix_datetime(input.value.trim());

	//
	if(datetime)
	{
		//
		input.value = datetime;
		
		// dirty
		validate_forms();
		
		//
		return true;
	}
	else
	{
		//
		return false;
	}
}

//
function fix_datetime(datetime)
{
	//
	datetime = datetime.trim();

	//
	if(!datetime)
	{
		//
		return false;
	}

	// match 20130128 AND 130128
	if(/^[0-9]+$/.test(datetime))
	{
		//
		switch(datetime.length)
		{
			//
			case 14:
			{
				//
				return datetime.substring(0,4) + '-' + datetime.substring(4,6) + '-' + datetime.substring(6,8) + ' ' + datetime.substring(8,10) + ':' + datetime.substring(10,12) + ':' + datetime.substring(12,14);
			}
			
			//
			case 12:
			{
				//
				return datetime.substring(0,4) + '-' + datetime.substring(4,6) + '-' + datetime.substring(6,8) + ' ' + datetime.substring(8,10) + ':' + datetime.substring(10,12) + ':00';
			}
			
			//
			case 10:
			{
				//
				var year = datetime.substring(0,2);
				
				//
				if(year < 30)
				{
					//
					year += 2000;
				}
				else
				{
					//
					year += 1900;
				}
				
				//
				return year + '-' + datetime.substring(2,4) + '-' + datetime.substring(4,6) + ' ' + datetime.substring(6,8) + ':' + datetime.substring(8,10) + ':00';
			}
			
			//
			case 8:
			{
// 				return datetime.substring(0,4) + '-' + datetime.substring(4,6) + '-' + datetime.substring(6,8) + ' 00:00:00';
				//
				return datetime.substring(0,4) + '-' + datetime.substring(4,6) + '-' + datetime.substring(6,8);// + ' 00:00:00';
			}
			
			//
			case 6:
			{
				//
				var year = datetime.substring(0,2);
				
				//
				if(year < 30)
				{
					//
					year = '20' + year;
				}
				else
				{
					//
					year = '19' + year;
				}
// 				return year + '-' + datetime.substring(2,4) + '-' + datetime.substring(4,6) + ' 00:00:00';

				//
				return year + '-' + datetime.substring(2,4) + '-' + datetime.substring(4,6); // + ' 00:00:00';
			}
			
			//
			default:
			{
				//
				return false;
			}
		}
	}

	//
	var matches = datetime.match(/^(.*) ([0-9:]+)$/);
	
	//
	if(matches)
	{
		//
		var date = fix_date(matches[1]);
		var time = fix_time(matches[2]);
		
		//
		if(date && time)
		{
			//
			return date + ' ' + time;
		}
	}

	//
	var date = fix_date(datetime);
	
	//
	if(date)
	{
// 		return date + ' 00:00:00';
		
		//
		return date; // + ' 00:00:00';
	}

	//
	return fix_datetime(datetime.replace(/[^0-9]+/g, ''));
}

/**
 * Stop an event from bubbeling.
 *
 * @param e - The event to stop.
 */
function stop_bubbling(event)
{
	// Cancel bubbeling. (IE)
	event.cancelBubble = true;

	// Check if we have the stopPropagation function. (W3C)
	if(event.stopPropagation)
	{
		// Run function to to stop bubbeling.
		event.stopPropagation();
	}
}

//
function open_link_ticket_role(ticket_id, ticket_role_id, e)
{
	//
	stop_bubbling(e);

	// Create an array to store request data in.
	var request_data = [];
	
	//
	request_data["ticket_id"] = ticket_id;
	request_data["ticket_role_id"] = ticket_role_id;
	
	// Create a callback function.
	var callback = function(result)
	{
		// Create a box.
		create_box("selector_link_ticket_role", "", "Länka en medborgare till ärendet", result, "group_add.png");
		
		// Revalidate all forms.
		validate_forms();
	}

	// Make the ajax request.
	ajax_post_request("/views/render_link_ticket_role.php", request_data, callback);
	
	// Exit.
	return true;
}

function link_ticket_role(form, status)
{
	// If form is not an object.
	if(typeof(form) != "object" || form == null)
	{
		//
		console_log("form is not an object, in link_ticket_role().");
		
		// Exit.
		return true;
	}
	
	// Get all form values.
	var request_data = get_form_values(form);
	
	// Create a callback to be runned after api call.
	var callback = function(data)
	{
		// Convert api result to an object.
		var result = parseJSON(data);
		
		//
		if(!result)
		{
			//
			console_log("result is not a valid JSON-object, in link_ticket_role().");
			
			//
			return true;
		}
		
		//
		if(result.status == 1)
		{
			//
			window.location.href = window.location.href;
		}
		else
		{
			// If the list of errors is an object.
			if(typeof(result.errors) == "object")
			{
				// Ty to get the error box.
				var notice = get_element("link_ticket_role_errors");
				
				// If no error box was found.
				if(!notice)
				{
					// Create an error box.
					var notice = document.createElement("P");
					
					//
					notice.id = "link_ticket_role_errors";
					
					//
					form.parentNode.insertBefore(notice, form.parentNode.firstChild);
					
					//
					notice.className = "warning";
				}
				
				// Crate a date object.
				var current_time = new Date();
				
				// Add the date nice formated to the error box.
				notice.innerHTML = str_pad(current_time.getHours().toString(), "0", 2) + ":" + str_pad(current_time.getMinutes().toString(), "0", 2) + ":" + str_pad(current_time.getSeconds().toString(), "0", 2) + ":<br />";
				
				// Loop through all errors.
				for(var error_index in result.errors)
				{
					// Add error to error box.
					notice.innerHTML += result.errors[error_index].error_description + "<br />";
				}
			}
		}

	}

	//api_url, api_message, api_status, api_data, api_callback
	api_call("/api/link_ticket_role.php", "Lägg till arbetare", status, request_data, callback);
	
	// Return true.
	return true;
}

//
function unlink_ticket_role(ticket_id, target_type, target_id, ticket_role_id, status)
{
	// Get all form values.
	var request_data = [];
	
	//
	request_data["role_id"] = ticket_role_id;
	request_data["target_type"] = target_type;
	request_data["target_id"] = target_id;
	request_data["ticket_id"] = ticket_id;
	
	// Create a callback to be runned after api call.
	var callback = function(data)
	{
		// Convert api result to an object.
		var result = parseJSON(data);
		
		//
		if(!result)
		{
			//
			console_log("result is not a valid JSON-object, in link_ticket_role().");
			
			//
			return true;
		}
		
		//
		if(result.status == 1)
		{
			//
			window.location.href = window.location.href;
		}
		else
		{
			// If the list of errors is an object.
			if(typeof(result.errors) == "object")
			{
				//
				console_log(result.errors);
			}
		}
	}
	
	//api_url, api_message, api_status, api_data, api_callback
	api_call("/api/link_ticket_role.php", "Lägg till arbetare", status, request_data, callback);
	
	//
	return true;
}

//
function render_errors(error_element_id, error_list, error_parent_element)
{
	//
	var error_list_element = get_element(error_element_id);
	
	//
	if(!error_list_element)
	{
		//
		error_list_element = document.createElement("DIV");
		
		//
		error_list_element.id = error_element_id;
	}
	else
	{
		//
		error_list_element.innerHTML = "";
	}
	
	//
	for(var error_index in error_list)
	{
		//
		var error_element = document.createElement("P");
		
		//
		error_element.className = "warning";
		error_element.innerHTML = error_list[error_index].error_description;
		
		//
		error_list_element.appendChild(error_element);
	}
	
	//
	error_parent_element.insertBefore(error_list_element, error_parent_element.firstChild);
}

//
function set_height_to_contents_height()
{
	//
	var iframes = document.getElementsByTagName("IFRAME");
	
	//
	if(typeof(iframes) != "object")
	{
		//
		return false;
	}
	
	//
	for(var iframe_index in iframes)
	{
		//
		var current_iframe = iframes[iframe_index];
		
		//
		if(typeof(current_iframe) != "object")
		{
			//
			continue;
		}
		
		//
		if(!is_item_in_list("resize_to_content", current_iframe.className))
		{
			//
			continue;
		}
		
		//
		try
		{
			//
			{
				var new_parent_height = (current_iframe.contentWindow.document.body.scrollHeight * 0.8) + 32;
			}
			
			//
			current_iframe.parentNode.style.height = new_parent_height + "px";
		}
		catch(error)
		{
			//
			console_log(error);
		}
	}
}

// TODO: Document this.
function ifw_match(needle, haystack)
{
	//
	if(!needle)
	{
		//
		return true;
	}

// 	var find_needles_regexp = new RegExp('("([^"]+)")|' + "('[^']+')" + '|([^"\' ]+)', 'ig');

	//
	var find_needles_regexp = /!?("([^"]+)")|!?('[^']+')|([^"' ]+)/ig;
	
	//
	var needles = needle.match(find_needles_regexp);
	
	//
	haystack = haystack.replace(/[ \t\r\n]+/g, ' ');
	
	//
	for(needle_index in needles)
	{
		//
		var current_needle = needles[needle_index];
		
		//
		var inverted = false;
		
		//
		if(current_needle.substring(0, 1) == '!')
		{
			//
			current_needle = current_needle.substring(1);
			
			//
			inverted = true;
		}
		
		//
		if(/^".*"$/.test(current_needle))
		{
			//
			current_needle = current_needle.substring(1, current_needle.length - 1);
		}
		else if(/^'.*'$/.test(current_needle))
		{
			//
			current_needle = current_needle.substring(1, current_needle.length - 1);
		}
		
		//
		var regexp = new RegExp(current_needle, 'i');
		
		//
		if(inverted)
		{
			//
			if(regexp.test(haystack))
			{
				//
				return false;
			}
		}
		else
		{
			//
			if(!regexp.test(haystack))
			{
				//
				return false;
			}
		}
	}
	
	//
	return true;
}

/**
 * TODO: Rename this function to toggle_table_row.
 * 
 * Toggles a table row.
 * 
 * @param checkbox - The checkbox that toggles to table row.
 */
function toggle_table_row(checkbox)
{
	// If checkbox is not an object.
	if(typeof(checkbox) != "object" || checkbox == null)
	{
		// Add an error to console.
		console_log("checkbox is of type '" + typeof(checkbox) + "' but object was expected, in toggle_table_row().");
		
		// Exit.
		return false;
	}
	
	// Get table row.
	var table_row = get_first_parent_by_tagname("TR", checkbox);
	
	// If table row is was not found or was not an object.
	if(!table_row)
	{
		// Add an error to console.
		console_log("checkbox has no parent element with tagname TR.");
		
		// Exit.
		return false;
	}
	
	// If checkbox is not checked.
	if(!checkbox.checked)
	{
		// Remove select class from table row.
		table_row.className = remove_item_from_list("selected", table_row.className);
		
		// Remove checked attribute on checkbox.
		checkbox.removeAttribute("checked");
	}
	else
	{
		// Add select class to table row.
		table_row.className = add_item_to_list("selected", table_row.className);
		
		// Add checked attribute to checkbox.
		checkbox.setAttribute("checked", "checked");
	}
}

//
onload_manager.add("set_height_to_contents_height();")