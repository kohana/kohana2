<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
<title>Google Maps JavaScript API Example</title>
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo Config::item('gmaps.api_key') ?>"type="text/javascript"></script>
<script type="text/javascript">
function showmap(){
//<![CDATA[

	if (GBrowserIsCompatible()) {
		var map = new GMap(document.getElementById("map"));
		map.centerAndZoom(new GPoint(0,35), 16);
		map.enableScrollWheelZoom();

		// Prevents the page from scrolling
		GEvent.addDomListener(map.getContainer(), "DOMMouseScroll", wheelevent);
		// map.getContainer().onmousewheel = wheelevent;
	}

	var icon = new GIcon();
	icon.image = "http://labs.google.com/ridefinder/images/mm_20_red.png";
	icon.shadow = "http://labs.google.com/ridefinder/images/mm_20_shadow.png";
	icon.iconSize = new GSize(12, 20);
	icon.shadowSize = new GSize(22, 20);
	icon.iconAnchor = new GPoint(6, 20);
	icon.infoWindowAnchor = new GPoint(5, 1);

	map.addControl(new GSmallMapControl());
	map.addControl(new GMapTypeControl());

	var marker0 = new GMarker(new GPoint(-93.328379, 45.109871));
	map.addOverlay(marker0);
	GEvent.addListener(marker0, "click", function() {
		marker0.openInfoWindowHtml("Minneapolis, Minnesota");
	});

	var marker1 = new GMarker(new GPoint(139.682282, 35.678451));
	map.addOverlay(marker1);
	GEvent.addListener(marker1, "click", function() {
		marker1.openInfoWindowHtml("Tokyo, Japan");
	});

//]]>
}
function wheelevent(e) {
	e.preventDefault();
	e.returnValue = false;
}
window.onload = showmap;
</script>
</head>
<body>
<p>You can use your scroll wheel to zoom in and out of the map.</p>
<div id="map" style="width: 600px; height: 400px"></div>
</body>
</html>