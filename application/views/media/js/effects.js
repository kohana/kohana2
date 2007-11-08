$(document).ready(function()
{
	if ($.browser.msie)
	{
		// IE has issues without the nifty css clearfix
		$('#menu').css('margin-bottom', '-2px');
		$('.clearfix').removeClass('clearfix').append('<div style="height:1px;clear:both;"></div>');
	}
});
