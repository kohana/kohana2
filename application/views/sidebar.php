<?php

$feed_names = array
(
	'trac' => 'Latest Changes',
	'forums' => 'Latest Forum Activity'
);

?>

<div id="sidecontent">
<h6>Keep Kohana Alive</h6>
<p id="donate">If you use Kohana and find it worth something, please consider <strong><?php echo html::anchor('donate', 'donating') ?></strong>. Your donations will be directly used to cover the costs of maintaining Kohana.</li>
</p>

<?php

foreach($feeds as $name => $items): 

?>
<h6><?php echo $feed_names[$name] ?></h6>
<ul>
<?php

foreach($items as $data):

	$date = date('M j, g:i:s A', strtotime($data['pubDate']));

?>
<li><strong><?php echo html::specialchars($data['title']) ?></strong> &ndash; <?php echo $date ?> - <?php echo html::anchor($data['link'], 'Read More')?></li>
<?php

endforeach;

?>
</ul>
<?php

endforeach;

?>

</div>