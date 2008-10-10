<?php
/**
 * Classic pagination style
 * 
 * @preview  ‹ First  < 1 2 3 >  Last ›
 */
?>

<p class="pagination">

	<?php if ($p->first_page): ?>
		<a href="<?php echo $p->url(1) ?>">&lsaquo;&nbsp;<?php echo Kohana::lang('pagination.first') ?></a>
	<?php endif ?>

	<?php if ($p->previous_page): ?>
		<a href="<?php echo $p->url($p->previous_page) ?>">&lt;</a>
	<?php endif ?>


	<?php for ($i = 1; $i <= $p->total_pages; $i++): ?>

		<?php if ($i == $p->current_page): ?>
			<strong><?php echo $i ?></strong>
		<?php else: ?>
			<a href="<?php echo $p->url($i) ?>"><?php echo $i ?></a>
		<?php endif ?>

	<?php endfor ?>


	<?php if ($p->next_page): ?>
		<a href="<?php echo $p->url($p->next_page) ?>">&gt;</a>
	<?php endif ?>

	<?php if ($p->last_page): ?>
		<a href="<?php echo $p->url($p->last_page) ?>"><?php echo Kohana::lang('pagination.last') ?>&nbsp;&rsaquo;</a>
	<?php endif ?>

</p>