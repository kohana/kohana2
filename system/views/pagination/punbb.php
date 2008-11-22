<?php
/**
 * PunBB pagination style
 * 
 * @preview  Pages: 1 … 4 5 6 7 8 … 15
 */
?>

<p class="pagination">

	<?php echo Kohana::lang('pagination.pages') ?>:

	<?php if ($p->current_page > 3): ?>
		<a href="<?php echo $p->url(1) ?>">1</a>
		<?php if ($p->current_page != 4) echo '…' ?>
	<?php endif ?>


	<?php for ($i = $p->current_page - 2, $stop = $p->current_page + 3; $i < $stop; ++$i): ?>

		<?php if ($i < 1 OR $i > $p->total_pages) continue ?>

		<?php if ($p->current_page == $i): ?>
			<strong><?php echo $i ?></strong>
		<?php else: ?>
			<a href="<?php echo $p->url($i) ?>"><?php echo $i ?></a>
		<?php endif ?>

	<?php endfor ?>


	<?php if ($p->current_page <= $p->total_pages - 3): ?>
		<?php if ($p->current_page != $p->total_pages - 3) echo '…' ?>
		<a href="<?php echo $p->url($p->total_pages) ?>"><?php echo $p->total_pages ?></a>
	<?php endif ?>

</p>