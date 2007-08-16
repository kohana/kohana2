$(document).ready(function(){
	$('#menu ul:first > li').addClass('first');
	$('#menu li ul li')
		.hide()   // Hide these li's
		.parent() // Parent ul
		.parent() // Parent li
		.hover(function(){// Hover affects the ul > li inside of this li
				$('ul > li', this)
				.stack('show', 200)
				.children('a')
				.css('color', '#607258')
				.hover(function() {
						$(this)
						.css('color', '#45721d');
					}, function() {
						$(this)
						.css('color', ''); 
					}
				);
			}, function(){
				$('ul > li', this)
				.stack('hide', 200)
				.children('a')
				.css('color', '');
			}
		);
});