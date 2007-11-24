<h2><span>By <?php echo $author ?>, &copy; <?php echo $copyright ?></span><?php echo $title ?></h2>

<script type="text/javascript">
$(document).ready(function()
{
	$('div#flash')
		.css('width', <?php echo $width ?>)
		.css('height', <?php echo $height ?>);

	$('div#flash span').click(function()
	{
		$('div#flash').flash(
		{
			src: '<?php echo $video ?>',
			width: <?php echo $width ?>,
			height: <?php echo $height ?>
			// wmode: 'transparent'
		}, { version: 8 });
		return false;
	});
});
</script>
<div id="flash"><span>LOAD / PLAY</span></div>