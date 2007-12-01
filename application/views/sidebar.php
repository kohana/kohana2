<div id="sidecontent">
<h6>Keep Kohana Alive</h6>
<p id="donate">If you use Kohana and find it worth something, please consider <strong><?php echo html::anchor('donate', 'donating') ?></strong>. <?php echo html::anchor('donate/donation_list', 'Your donations') ?> will be directly used to cover the costs of maintaining Kohana.</li>
</p>

<?php

foreach($feeds as $name => $data): 

?>
<h6 class="feed"><?php echo html::anchor($data['url'], $data['title']) ?></h6>
<ul>
<?php

foreach($data['items'] as $item):

	$date = date('M j, g:i:s A', strtotime($item['pubDate']));

?>
<li><strong><?php echo html::specialchars($item['title']) ?></strong> &ndash; <?php echo $date ?> - <?php echo html::anchor($item['link'], 'Read More')?></li>
<?php

endforeach;

?>
</ul>
<?php

endforeach;

?>

</div>