(function($){
// The $ variable is aliased and ready
$(document).ready(function()
{
	$('pre.kohana_error a[href="#show_trace"]').click(function()
	{
		$(this).parents('pre.kohana_error').next('div.kohana_trace').toggle();

		return false;
	});
	$('div.kohana_trace a[href="#show_source"]').click(function()
	{
		$(this).next('pre').toggle();

		return false;
	});
});
// End of $ alias
})(jQuery.noConflict());