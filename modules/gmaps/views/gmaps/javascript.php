if (GBrowserIsCompatible())
{
	// Initialize the GMap
	<?php echo $map, "\n" ?>
	<?php echo $controls, "\n" ?>
	<?php echo $center, "\n" ?>
	<?php echo $zoom, "\n" ?>
	<?php echo $options->render(1), "\n" ?>

	// Show map points
<?php foreach ($markers as $marker): ?>
	<?php echo $marker->render(1), "\n" ?>
<?php endforeach ?>
}
