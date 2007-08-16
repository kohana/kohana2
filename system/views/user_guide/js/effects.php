$(document).ready(function(){
	$('#menu ul:first > li').addClass('first');
	$('#menu li ul li')
		.hide()   // Hide these li's
		.parent() // Parent ul
		.parent() // Parent li
		.hover(   // Hover affects the ul > li inside of this li
			function(){ $('ul > li', this).stack('show', 200); },
			function(){ $('ul > li', this).stack('hide', 200); }
		);
});