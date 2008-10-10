<?php
/**
 * Extended pagination style
 * 
 * @preview  « Previous | Page 2 of 11 | Showing items 6–10 of 52 | Next »
 */
?>

<p class="pagination">

	<?php if ($p->previous_page): ?>
		<a href="<?php echo $p->url($p->previous_page) ?>">&laquo;&nbsp;<?php echo Kohana::lang('pagination.previous') ?></a>
	<?php else: ?>
		&laquo;&nbsp;<?php echo Kohana::lang('pagination.previous') ?>
	<?php endif ?>

	| <?php echo Kohana::lang('pagination.page') ?> <?php echo $p->current_page ?> <?php echo Kohana::lang('pagination.of') ?> <?php echo $p->total_pages ?>

	| <?php echo Kohana::lang('pagination.items') ?> <?php echo $p->current_first_item ?>–<?php echo $p->current_last_item ?> <?php echo Kohana::lang('pagination.of') ?> <?php echo $p->total_items ?>

	| <?php if ($p->next_page): ?>
		<a href="<?php echo $p->url($p->next_page) ?>"><?php echo Kohana::lang('pagination.next') ?>&nbsp;&raquo;</a>
	<?php else: ?>
		<?php echo Kohana::lang('pagination.next') ?>&nbsp;&raquo;
	<?php endif ?>

</p>