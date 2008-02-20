<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
<title>Google Maps JavaScript API Example</title>
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo Config::item('gmaps.api_key') ?>"
	type="text/javascript"></script>
<script type="text/javascript">

//<![CDATA[
function initialize() {
	if (GBrowserIsCompatible()) {
		geoXml = new GGeoXml('http://wgilk.com/georss.xml');
		map = new GMap2(document.getElementById("map"));
		map.setCenter(new GLatLng(49.496675,-102.65625), 3); 
		map.addControl(new GLargeMapControl());
		map.addControl(new GLargeMapControl());
		map.addOverlay(geoXml);
	}
}

//]]>
</script>
</head>
<body onload="initialize()">
<div id="map" style="width: 500px; height: 300px"></div>
</body>
</html>