<?php
foreach ($items as $heading => $entries):

	?>
	<ul>
		<li><h2><?php echo $heading ?></h2></li>
		<?php

		foreach ($entries as $url => $item):

			if (is_array($item)):

				?>
				<li><h3><?php echo $url ?></h3></li>
				<li>
					<ul class="level2">
					<?php

					foreach ($item as $url2 => $item2):

						?>
						<li><a href="<?php echo $url2 ?>"><?php echo $item2 ?></a></li>
						<?php

					endforeach;

					?>
					</ul>
				</li>
				<?php

			else:

				?>
				<li><a href="<?php echo $url ?>"><?php echo $item ?></a></li>
				<?php

			endif;

		endforeach;

		?>
	</ul>
	<?php

endforeach;
