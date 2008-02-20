<script type="text/javascript">

function showmap(){
	//<![CDATA[

	if (GBrowserIsCompatible()) {

		var map = new GMap(document.getElementById("map"));
		map.centerAndZoom(new GPoint(-77.035971,38.898590), 4);
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
		var point0 = new GPoint(-77.035971,38.898590);
		var marker0 = new GMarker(point0);

		map.addOverlay(marker0)

		GEvent.addListener(marker0, "click", function() {
			marker0.openInfoWindowHtml("The White House");
		});
	//]]>

}
window.onload = showmap;
</script>