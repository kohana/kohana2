// $Id$
$(document).ready(function(){
	// Append the AJAX loader
	$('body').append('<div id="loading">&hellip;loading&hellip;</div>');
	// To prevent extra querying
	Kohana.loading = $('#loading').hide().css('opacity', 0.85);
	// Make the menu sticky
	$(window).scroll(function(){
		$('#menu').css('top', $(window).scrollTop());
	});
	// Apply menu effects
	$('#menu li.first li')
	.hide()   // Hide these li's
	.parent() // Parent ul
	.parent() // Parent li
	.click(function(){
			// Hover affects the ul > li inside of this li
			var curr = $('#menu li.active');
			var self = $(this);
			// Clicks to the same element will do nothing
			if (self.is('.active') == false)
			{
				// Hide the current elements
				curr.removeClass('active')
				.find('ul > li')
				.stack('animate', {height: 'hide', width: 'hide'}, 100);
				// Show the new elements
				self.addClass('active')
				.find('ul > li')
				.stack('animate', {height: 'show', width: 'show'}, 100);
			}
		}
	).
	// Find links in the menu
	find('a')
	.click(function(){
		// Show loading
		Kohana.toggleLoading(true);
		// Fetch the current link
		var link = $(this);
		// Remove the "lite" class from the active link
		$('#menu li.active li.lite').removeClass('lite');
		// Make AJAX request
		$.get(link.attr('href'), {ajax: 'true'}, function(data) {
			// Add the "lite" class to the current link
			link.parent().addClass('lite');
			// Load new AJAX content
			$('#body').html(data);
			// Hide loading
			Kohana.toggleLoading(false)
		});
		return false;
	});
	// Show the active menu
	$('#menu li.active ul > li')
	.stack('animate', {height: 'show', width: 'show'}, 100);
});
// Special Kohana functions
var Kohana = {
	loading: false,
	waiting: false,
	toggleLoading: function(on) {
		// If we are waiting for an animation, retry in 5ms
		if (Kohana.waiting == true) {
			setTimeout('Kohana.toggleLoading('+on+')', 5);
			return false;
		}
		// Toggle waiting state
		Kohana.waiting = true;
		if (on == true) { // Show loading
			this.loading.slideDown(200, function() {
				Kohana.waiting = false;
			});
		} else { // Hide loading
			this.loading.slideUp(200, function() {
				Kohana.waiting = false;
			});
		}
	}
};