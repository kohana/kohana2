<?php
/**
 * Classic pagination style
 * 
 * @preview  ‹ First  < 1 2 3 >  Last ›
 */
?>

<p>
	
	<?php if ($first_page) { ?>
		<a href="<?php echo __Pagination__->url(1) ?>">&lsaquo;&nbsp;First</a>
	<?php } ?>

	<?php if ($previous_page) { ?>
		<a href="<?php echo __Pagination__->url($previous_page) ?>">&lt;</a>
	<?php } ?>
	

	<?php for ($i = 1; $i <= $total_pages; $i++) { ?>
		
		<?php if ($i == $current_page) { ?>
			<strong><?php echo $i ?></strong>
		<?php } else { ?>
			<a href="<?php echo __Pagination__->url($i) ?>"><?php echo $i ?></a>
		<?php } ?>
		
	<?php } ?>


	<?php if ($next_page) { ?>
		<a href="<?php echo __Pagination__->url($next_page) ?>">&gt;</a>
	<?php } ?>

	<?php if ($last_page) { ?>
		<a href="<?php echo __Pagination__->url($last_page) ?>">Last&nbsp;&rsaquo;</a>
	<?php } ?>

</p>