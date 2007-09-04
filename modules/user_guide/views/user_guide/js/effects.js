// $Id$
$(document).ready(function(){
	// Menu opacity hover effect, much fancy pants!
	$('#menu').css('opacity', 0.7).hover(function(){
		$(this).fadeTo(100, 1);
	}, function(){
		$(this).fadeTo(300, 0.7)
	});
	// Apply menu sliding effect
	$('#menu li.first').click(function(){
		// Define the current menu and the clicked menu
		var curr = $('#menu li.active');
		var self = $(this);
		// Clicks to the same menu will do nothing
		if (self.is('.active') == false)
		{
			// Hide the current elements
			curr.removeClass('active')
			.find('ul')
			.slideUp(250);
			// Show the new elements
			self.addClass('active')
			.find('ul')
			.slideDown(250);
		}
	})
	// Find and hide the sub menus that are not in the active menu
	.not('.active').find('ul').hide();
	// For syntax highlighting
	prettyPrint();
});