<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
<title>Gmaps jQuery + XML Example</title>
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo Config::item('gmaps.api_key') ?>" type="text/javascript"></script>
<?php echo html::script('jquery.min') ?>

<script type="text/javascript">
$(document).ready(function()
{
	if (GBrowserIsCompatible())
	{
		// Initialize the map.
		map = new GMap(document.getElementById('map'));
		map.addControl(new GLargeMapControl());
		map.centerAndZoom(new GPoint(0,35), 16);
		map.enableScrollWheelZoom();

		// Disable the scrollwheel from scrolling the map
		GEvent.addDomListener(map.getContainer(), 'DOMMouseScroll', function(e)
		{
			e.preventDefault();
			e.returnValue = false;
		});

		// Load map markers
		$.ajax
		({
			type: 'GET',
			url: '<?php echo url::site('google_map/xml') ?>',
			dataType: 'xml',
			success: function(data, status)
			{
				$('marker', data).each(function()
				{
					// Current marker
					var node = $(this);

					// Extract HTML
					var html = node.find('html').text();

					// Create a new map marker
					var marker = new GMarker(new GLatLng(node.attr("lat"), node.attr("lon")));
					GEvent.addListener(marker, "click", function()
					{
						marker.openInfoWindowHtml(html);
					});

					// Add the marker to the map
					map.addOverlay(marker);
				});
			},
			error: function(request, status, error)
			{
				alert('There was an error retrieving the marker information, please refresh the page to try again.');
			}
		});
	}
});
// Unload the map when the window is closed
$(document.body).unload(function(){ GBrowserIsCompatible() && GUnload(); });
</script>
</head>
<body>
<p>You can use your scroll wheel to zoom in and out of the map.</p>
<div id="map" style="width:600px;height:400px;"></div>
</body>
</html>