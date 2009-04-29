<div id="sidecontent">
	<h6><?php echo Kohana::lang('sidebar.title');?></h6>
	<p id="donate"><?php echo Kohana::lang('sidebar.content', array(html::anchor('donate', Kohana::lang('sidebar.donating')), html::anchor('donate/donation_list', Kohana::lang('sidebar.your_donations'))));?></p>
	<?php foreach($feeds as $name => $data): ?>
		<h6 class="feed"><?php echo html::anchor($data['url'], Kohana::lang($data['title'])) ?></h6>
		<ul>
		<?php
			foreach($data['items'] as $item):
				$date = date('M j, g:i:s A', strtotime(isset($item['pubDate']) ? $item['pubDate'] : $item['updated']));
		?>
			<li><strong><?php echo html::specialchars($item['title']) ?></strong> &ndash; <?php echo $date ?> - <?php echo html::anchor(isset($item['link']) ? $item['link'] : $item['id'], Kohana::lang('sidebar.read_more'))?></li>
		<?php endforeach;?>
		</ul>
	<?php endforeach;?>
</div>