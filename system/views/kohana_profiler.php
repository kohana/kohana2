<style type="text/css">
#kohana-profiler
{
	font-family: Monaco, 'Courier New';
	background-color: #F8FFF8;
	margin-top: 20px;
	clear: both;
	padding: 10px 10px 0;
	border: 1px solid #E5EFF8;
	text-align: left;
}
#kohana-profiler pre
{
	margin: 0;
	font: inherit;
}
<?php echo $styles ?>
</style>
<div id="kohana-profiler">
<?php
foreach ($profiles as $profile)
{
	echo $profile->render();
}
?>
Profiler executed in <?php echo number_format($execution_time, 3) ?>s
</div>