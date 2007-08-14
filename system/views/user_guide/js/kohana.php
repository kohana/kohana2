$(document).ready(function(){
	// Hide menu
	var menu = $('#menu ul:first > li');
	menu.children('ul').children('li').hide();
	menu.eq(1).addClass('active').children('ul').children('li').stack('animate', {
		height: 'toggle', marginLeft: 'toggle', opacity: 'show'
	}, 400, 'expoin');
	menu.not('.active').hover(function(){
		$(this).children('ul').children('li').stack('show', 200);
	}, function(){
		$(this).children('ul').children('li').stack('hide', 200);
	});
});