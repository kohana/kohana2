$(document).ready(function()
{
	if ($.browser.msie)
	{
		// IE has issues with the nifty css clearfix that every other browser supports
		$('.clearfix').removeClass('clearfix').append('<div style="height:1px;clear:both;"></div>');
		$('#menu').css('margin-bottom', '-2px');
	}
	// Menu link effects
	$('#menu li').not('.active').find('a').each(function()
	{
		var hover  = {backgroundColor: '#76b714', color: '#2c360b'};
		var normal = {backgroundColor: '#83c018', color: '#3f5c0b'};
		// Apply hover effect
		$(this).css(normal).hover(function()
		{
			$(this).stop().animate(hover, 250);
		},
		function()
		{
			$(this).stop().animate(normal, 250);
		});
	});
	// Download button effects
	$('#download a').each(function()
	{
		var hover  = {backgroundColor: '#a2d135'};
		var normal = {backgroundColor: '#93c924'};
		// Apply hover effects
		$(this).css(normal).hover(function()
		{
			$(this).stop().animate(hover, 250);
		},
		function()
		{
			$(this).stop().animate(normal, 250);
		});
	});
});