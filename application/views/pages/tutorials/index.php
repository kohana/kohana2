<h2><?php echo Kohana::lang('tutorials.title');?></h2>
<p class="intro"><?php echo Kohana::lang('tutorials.intro');?></p>
<p><?php echo Kohana::lang('tutorials.content', array(html::mailto('woody.gilk@kohanaphp.com', 'Woody Gilk'), html::anchor('http://qbnz.com/highlighter/', 'Geshi')));?></p>
<?php foreach($titles as $heading => $group): ?>
	<h5><?php echo $heading ?></h5>
	<ul>
	<?php foreach($group as $link => $title): ?>
		<li><?php echo html::anchor('tutorials/'.$link, $title) ?></li>
	<?php endforeach; ?>
	</ul>
<?php endforeach; ?>