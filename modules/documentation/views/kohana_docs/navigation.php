<div class="navigation">

<?php if ( ! empty($previous)): ?>
	Previous Page: <a href="<?php echo $previous[0] ?>"><?php echo $previous[1] ?></a>
	&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;&nbsp;
<?php endif; ?>

	<a href="#top">Top of the Page</a>

<?php if ( ! empty($next)): ?>
	&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;&nbsp;
	Next Page: <a href="<?php echo $next[0] ?>"><?php echo $next[1] ?></a>
<?php endif; ?>

</div>
