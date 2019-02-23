/*
	This class lets us have multiple functions/actions in our window.onload.
*/
function window_onload_manager()
{
	//
	this.load_element = null;
	
	//
	this.action_list = [];

	/*
		The function that window.onload calls.
		It loops through the list of actions and runs them, with an eval().
	*/
	this.execute = function()
	{
		//
		for(var i = 0; i < this.action_list.length; i++)
		{
			//
			eval(this.action_list[i]);
		}

		//
		this.action_list = [];
		
		//
		this.hide_load();
	}

	/*
		Functions which is used to add actions to the array.
	*/
	this.add = function(action)
	{
		//
		this.action_list.push(action);
	}
	
	//
	this.hide_load = function()
	{
		//
		this.load_element = get_element("status");
		
		//
		if(this.load_element)
		{
			//
			this.load_element.style.opacity = "0";
			
			//
			window.setTimeout("window.onload_manager.load_element.style.display = 'none';", 300);
		}
	}
}

// Create the onload manager object.
window.onload_manager = new window_onload_manager();

// Set the onload managers execute-method as the window onload function.
window.onload = function()
{
	//
	window.onload_manager.execute();
}
