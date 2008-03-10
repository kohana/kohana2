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
#kohana-unit-test .k-debug
{
	padding: 3px;
	background-color: #FFF0F0;
	border: 1px solid #FFD0D0;
	border-right-color: #FFFBFB;
	border-bottom-color: #FFFBFB;
	color: #83919C;
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
#kohana-unit-test .k-passed
{
	background-color: #E0FFE0;
}
#kohana-unit-test .k-altrow .k-passed
{
	background-color: #D0FFD0;
}
#kohana-unit-test .k-failed
{
	background-color: #FFE0E0;
}
#kohana-unit-test .k-altrow .k-failed
{
	background-color: #FFD0D0;
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

		text::alternate();
		foreach ($methods as $method => $result):

		?>

			<tr class="<?php echo text::alternate('', 'k-altrow') ?>">
				<td class="k-name"><?php echo $method ?></td>

				<?php if ($result === TRUE): ?>

					<td class="k-passed"><strong><?php echo Kohana::lang('unit_test.passed') ?></strong></td>

				<?php else: /* $result == Kohana_Unit_Test_Exception */ ?>

					<td class="k-failed">
						<strong><?php echo Kohana::lang('unit_test.failed') ?></strong>
						<pre><?php echo html::specialchars($result->message) ?></pre>
						<?php echo html::specialchars($result->file) ?> (<?php echo Kohana::lang('unit_test.line') ?>&nbsp;<?php echo $result->line ?>)

						<?php if ($result->debug !== NULL): ?>
							<pre class="k-debug" title="Debug info"><?php echo '(', gettype($result->debug), ') ', html::specialchars(var_export($result->debug, TRUE)) ?></pre>
						<?php endif ?>

					</td>

				<?php endif ?>

			</tr>

		<?php endforeach ?>

	</table>

<?php endforeach ?>

</div>