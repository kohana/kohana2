<style type="text/css">
#kohana-unit-test
{
	font-family: Monaco, 'Courier New';
	background-color: #F8FFF8;
	margin-top: 20px;
	clear: both;
	padding: 10px 10px 0;
	border: 1px solid #E5EFF8;
	text-align: left;
}
#kohana-unit-test pre
{
	margin: 0;
	font: inherit;
}
#kohana-unit-test table
{
	font-size: 1.0em;
	color: #4D6171;
	width: 100%;
	border-collapse: collapse;
	border-top: 1px solid #E5EFF8;
	border-right: 1px solid #E5EFF8;
	border-left: 1px solid #E5EFF8;
	margin-bottom: 10px;
}
#kohana-unit-test th
{
	text-align: left;
	border-bottom: 1px solid #E5EFF8;
	background-color: #263038;
	padding: 3px;
	color: #FFF;
}
#kohana-unit-test td
{
	background-color: #FFF;
	border-bottom: 1px solid #E5EFF8;
	padding: 3px;
}
#kohana-unit-test .k-altrow td
{
	background-color: #F7FBFF;
}
#kohana-unit-test .k-name
{
	width: 25%;
	border-right: 1px solid #E5EFF8;
}
#kohana-unit-test .k-passed, #kohana-unit-test .k-altrow .k-passed
{
	background-color: #E0FFE0;
}
#kohana-unit-test .k-failed, #kohana-unit-test .k-altrow .k-failed
{
	background-color: #FFE0E0;
}
</style>

<div id="kohana-unit-test">

<?php

foreach ($results as $class => $methods):

?>

	<table>
		<tr>
			<th colspan="2"><?php echo $class ?></th>
		</tr>

		<?php

		foreach ($methods as $method => $result):

		?>

			<tr class="<?php echo text::alternate('', 'k-altrow') ?>">
				<td class="k-name"><?php echo $method ?></td>
				<td class="<?php echo ($result === TRUE) ? 'k-passed' : 'k-failed' ?>">
					<?php echo ($result === TRUE) ? 'Passed' : 'Failed' ?>
				</td>
			</tr>

		<?php

		endforeach;

		?>

	</table>

<?php

endforeach;

?>

</div>