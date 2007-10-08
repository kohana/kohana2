<?php
/**
 * Classic pagination style
 * 
 * @preview  ‹ First  < 1 2 3 >  Last ›
 */
?>

<p class="pagination">
	
	<?php if ($first_page): ?>
		<a href="<?php echo $this->pagination->url(1) ?>">&lsaquo;&nbsp;First</a>
	<?php endif; ?>

	<?php if ($previous_page): ?>
		<a href="<?php echo $this->pagination->url($previous_page) ?>">&lt;</a>
	<?php endif; ?>
	

	<?php for ($i = 1; $i <= $total_pages; $i++): ?>
		
		<?php if ($i == $current_page): ?>
			<strong><?php echo $i ?></strong>
		<?php else: ?>
			<a href="<?php echo $this->pagination->url($i) ?>"><?php echo $i ?></a>
		<?php endif; ?>
		
	<?php endfor; ?>


	<?php if ($next_page): ?>
		<a href="<?php echo $this->pagination->url($next_page) ?>">&gt;</a>
	<?php endif; ?>

	<?php if ($last_page): ?>
		<a href="<?php echo $this->pagination->url($last_page) ?>">Last&nbsp;&rsaquo;</a>
	<?php endif; ?>

</p>