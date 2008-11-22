<?php
/**
 * Digg pagination style
 * 
 * @preview  « Previous  1 2 … 5 6 7 8 9 10 11 12 13 14 … 25 26  Next »
 */
?>

<p class="pagination">

	<?php if ($p->previous_page): ?>
		<a href="<?php echo $p->url($p->previous_page) ?>">&laquo;&nbsp;<?php echo Kohana::lang('pagination.previous') ?></a>
	<?php else: ?>
		&laquo;&nbsp;<?php echo Kohana::lang('pagination.previous') ?>
	<?php endif ?>


	<?php if ($p->total_pages < 13): /* « Previous  1 2 3 4 5 6 7 8 9 10 11 12  Next » */ ?>

		<?php for ($i = 1; $i <= $p->total_pages; $i++): ?>
			<?php if ($i == $p->current_page): ?>
				<strong><?php echo $i ?></strong>
			<?php else: ?>
				<a href="<?php echo $p->url($i) ?>"><?php echo $i ?></a>
			<?php endif ?>
		<?php endfor ?>

	<?php elseif ($p->current_page < 9): /* « Previous  1 2 3 4 5 6 7 8 9 10 … 25 26  Next » */ ?>

		<?php for ($i = 1; $i <= 10; $i++): ?>
			<?php if ($i == $p->current_page): ?>
				<strong><?php echo $i ?></strong>
			<?php else: ?>
				<a href="<?php echo $p->url($i) ?>"><?php echo $i ?></a>
			<?php endif ?>
		<?php endfor ?>

		…
		<a href="<?php echo $p->url($p->total_pages - 1) ?>"><?php echo $p->total_pages - 1 ?></a>
		<a href="<?php echo $p->url($p->total_pages) ?>"><?php echo $p->total_pages ?></a>

	<?php elseif ($p->current_page > $p->total_pages - 8): /* « Previous  1 2 … 17 18 19 20 21 22 23 24 25 26  Next » */ ?>

		<a href="<?php echo $p->url(1) ?>">1</a>
		<a href="<?php echo $p->url(2) ?>">2</a>
		…

		<?php for ($i = $p->total_pages - 9; $i <= $p->total_pages; $i++): ?>
			<?php if ($i == $p->current_page): ?>
				<strong><?php echo $i ?></strong>
			<?php else: ?>
				<a href="<?php echo $p->url($i) ?>"><?php echo $i ?></a>
			<?php endif ?>
		<?php endfor ?>

	<?php else: /* « Previous  1 2 … 5 6 7 8 9 10 11 12 13 14 … 25 26  Next » */ ?>

		<a href="<?php echo $p->url(1) ?>">1</a>
		<a href="<?php echo $p->url(2) ?>">2</a>
		…

		<?php for ($i = $p->current_page - 5; $i <= $p->current_page + 5; $i++): ?>
			<?php if ($i == $p->current_page): ?>
				<strong><?php echo $i ?></strong>
			<?php else: ?>
				<a href="<?php echo $p->url($i) ?>"><?php echo $i ?></a>
			<?php endif ?>
		<?php endfor ?>

		…
		<a href="<?php echo $p->url($p->total_pages - 1) ?>"><?php echo $p->total_pages - 1 ?></a>
		<a href="<?php echo $p->url($p->total_pages) ?>"><?php echo $p->total_pages ?></a>

	<?php endif ?>


	<?php if ($p->next_page): ?>
		<a href="<?php echo $p->url($p->next_page) ?>"><?php echo Kohana::lang('pagination.next') ?>&nbsp;&raquo;</a>
	<?php else: ?>
		<?php echo Kohana::lang('pagination.next') ?>&nbsp;&raquo;
	<?php endif ?>

</p>