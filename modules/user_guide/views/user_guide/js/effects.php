$(document).ready(function(){
	$('#menu ul:first > li').addClass('first');
	$('#menu li ul li')
	.hide()   // Hide these li's
	.parent() // Parent ul
	.parent() // Parent li
	.click(function(){// Hover affects the ul > li inside of this li
			var curr = $('#menu li.active');
			var self = $(this);
			// Clicks to the same element will do nothing
			if (self.is('.active') == false)
			{
				// Hide the current elements
				curr.removeClass('active')
				.find('ul > li')
				.stack('hide', 200);
				// Show the new elements
				self.addClass('active')
				.find('ul > li')
				.stack('show', 200)
				.children('a')
				.hover(function() {
						$(this).css('color', '#45721d'); 
					}, function() { 
						$(this).css('color', ''); 
					}
				);
			}
		}
	);
	$('#menu li.active ul > li')
	.stack('show', 200);
});