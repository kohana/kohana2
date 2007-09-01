// $Id$
$(document).ready(function(){
	// Menu opacity hover effect, much fancy pants!
	$('#menu').css('opacity', 0.75).hover(function(){
		$(this).fadeTo(100, 0.9);
	}, function(){
		$(this).fadeTo(300, 0.7)
	});
	// Append the AJAX loader
	$('#container').append('<div id="loading">&nbsp;</div>');
	// To prevent extra querying, add the loading element to Kohana after hiding it
	Kohana.loading = $('#loading').hide();
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
	// Add AJAX functionality to the menu links
	$('#menu a').click(function(){
		// Show loading
		Kohana.toggleLoading(true);
		// Fetch the current link
		var link = $(this);
		// Make AJAX request, using ?ajax=true
		$.get(link.attr('href'), {ajax: 'true'}, function(data) {
			// Add the hilight class to the current link
			$('#menu ul li.lite').removeClass('lite');
			link.parent().addClass('lite');
			// Load new AJAX content
			$('#body').html(data);
			// Hide loading
			Kohana.toggleLoading(false)
		});
		return false;
	});
});
// Special Kohana functions
var Kohana = {
	loading: false,
	waiting: false,
	toggleLoading: function(on) {
		// If we are waiting for an animation, retry in 5ms
		if (Kohana.waiting == true) {
			setTimeout('Kohana.toggleLoading('+on+')', 20);
			return false;
		}
		// Toggle waiting state
		Kohana.waiting = true;
		if (on == true) { // Show loading
			this.loading.slideDown(250, function() {
				Kohana.waiting = false;
			});
		} else { // Hide loading
			this.loading.slideUp(250, function() {
				Kohana.waiting = false;
			});
		}
	}
};