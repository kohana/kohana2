var kohana_errors_ui = (function()
{				
	var kohana_trace_href = '#show_trace';			
	var kohana_source_href = '#show_source';

	var old_onload = (typeof window.onload == 'function') ? window.onload : function(){};

	window.onload = function()
	{
		old_onload();

		var current_href;

		var document_links = document.getElementsByTagName('a');

		for(var a = 0; a < document_links.length; a++)
		{
			current_href = document_links[a].getAttribute('href');
			if(typeof current_href == 'string')
			{
				switch(current_href)
				{
					case kohana_trace_href:
					case kohana_source_href:
						document_links[a].onclick = kohana_errors_ui.toggler;
						document_links[a].onclick();
						break;
				}
			}
		}
	}

	return {
		toggler : function()
		{
			var id, list, instance_identifier;

			id = this.getAttribute('id');
			if(typeof id == 'string')
			{
				list = id.split('_');
				instance_identifier = list[4];

				kohana_errors_ui.toggle_block_show('kohana_error_'+list[2]+'_'+instance_identifier);
			}

			return false;
		},
		set_display : function(element, display)
		{
			if(typeof element == 'string')
				element = document.getElementById(element);

			if(typeof element == 'object' && element !== 'null')
				element.style.display = display;
		},
		toggle_block_show : function(element)
		{
			if(typeof element == 'string')
				element = document.getElementById(element);

			if(typeof element == 'object' && element !== 'null')
				kohana_errors_ui.set_display(element, ( ! element.style.display || element.style.display == 'block' ? 'none' : 'block'));
		}
	}
})();