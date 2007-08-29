<?php
/**
 * Extended pagination style
 * 
 * @preview  « Previous | Page 2 of 11 | Showing items 6-10 of 52 | Next »
 */
?>

<p class="pagination">
	
	<?php if ($previous_page) { ?>
		<a href="<?php echo $this->pagination->url($previous_page) ?>">&laquo;&nbsp;Previous</a>
	<?php } else { ?>
		&laquo;&nbsp;Previous
	<?php } ?>
	
	| Page <?php echo $current_page ?> of <?php echo $total_pages ?>
	
	| Showing items <?php echo $current_first_item ?>-<?php echo $current_last_item ?> of <?php echo $total_items ?>
	
	| <?php if ($next_page) { ?>
		<a href="<?php echo $this->pagination->url($next_page) ?>">Next&nbsp;&raquo;</a>
	<?php } else { ?>
		Next&nbsp;&raquo;
	<?php } ?>
	
</p>