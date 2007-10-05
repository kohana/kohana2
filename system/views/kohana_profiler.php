<style type="text/css">
	#kohana-profiler
	{
		font-family: Courier New;
		background-color: #F8FFF8;
		margin-top: 20px;
		clear: both;
		padding: 10px;
		border: 1px solid #E5EFF8;
	}
	#kohana-profiler table
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
	#kohana-profiler th
	{
		text-align: left;
		border-bottom: 1px solid #E5EFF8;
		background-color: #F9FCFE;
		padding: 3px;
		color: #263038;
	}
	#kohana-profiler td
	{
		background-color: #FFFFFF;
		border-bottom: 1px solid #E5EFF8;
		padding: 3px;
	}
	#kohana-profiler .kp-altrow td
	{
		background-color: #F7FBFF;
	}
	#kp-benchmarks th
	{
		background-color: #FFE0E0;
	}
	#kp-queries th
	{
		background-color: #E0FFE0;
	}
	#kp-postdata th
	{
		background-color: #E0E0FF;
	}
	#kohana-profiler .kp-time
	{
		width: 100px;
		background-color: #FAFAFB !important;
		border-left: 1px solid #E5EFF8;
		text-align: center;
	}
	#kohana-profiler .kp-postname
	{
		width: 200px;
		background-color: #FAFAFB !important;
		border-right: 1px solid #E5EFF8;
	}
</style>
<div id="kohana-profiler">

	<table id="kp-benchmarks">
		<tr>
			<th colspan="2"><?php echo Kohana::lang('profiler.benchmarks') ?></th>
		</tr>
		<?php
		$count = 0;
		foreach ($benchmarks as $name => $time)
		{
			$name = ucwords(str_replace(array('_', '-'), ' ', $name));
			?>
			<tr<?php if ($count % 2) echo ' class="kp-altrow"'; ?>>
				<td><?php echo $name ?></td>
				<td class="kp-time"><?php echo $time ?></td>
			</tr>
			<?php
			$count++;
		}
		?>
	</table>

	<table id="kp-queries">
		<tr>
			<th colspan="2"><?php echo Kohana::lang('profiler.queries') ?> (<?php echo count($queries) ?>)</th>
		</tr>
		<?php
		if ( ! $db_exists)
		{
			?>
			<tr><td colspan="2"><?php echo Kohana::lang('profiler.no_database') ?></td></tr>
			<?php
		}
		else
		{
			if (count($queries) == 0)
			{
				?>
				<tr><td colspan="2"><?php echo Kohana::lang('profiler.no_queries') ?></td></tr>
				<?php
			}
			else
			{
				$count = 0;
				foreach ($queries as $query)
				{
					?>
					<tr<?php if ($count % 2) echo ' class="kp-altrow"'; ?>>
						<td><?php echo htmlspecialchars($query['query']) ?></td>
						<td class="kp-time"><?php echo number_format($query['time'], 4) ?></td>
					</tr>
					<?php
					$count++;
				}
			}
		}
		?>
	</table>

	<table id="kp-postdata">
		<tr>
			<th colspan="2"><?php echo Kohana::lang('profiler.post_data') ?></th>
		</tr>
		<?php
		if (count($_POST) == 0)
		{
			?>
			<tr><td colspan="2"><?php echo Kohana::lang('profiler.no_post') ?></td></tr>
			<?php
		}
		else
		{
			$count = 0;
			foreach ($_POST as $name => $value)
			{
				?>
				<tr<?php if ($count % 2) echo ' class="kp-altrow"'; ?>>
					<td class="kp-postname"><?php echo $name ?></td>
					<td>
						<?php
						if (is_array($value))
						{
							echo '<pre>' . htmlspecialchars(print_r($value, true)) . '</pre>';
						}
						else
						{
							echo htmlspecialchars($value);
						}
						?>
					</td>
				</tr>
				<?php
				$count++;
			}
		}
		?>
	</table>

</div>
