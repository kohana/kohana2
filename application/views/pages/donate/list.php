<h2><?php echo Kohana::lang('donate.list');?></h2>
<p><?php echo Kohana::lang('donate.list_help');?></p>
<ul>
<?php foreach ($donation_list as $person):?>
    <li><strong><?php echo $person->name ?></strong> - <em>$<?php echo $person->amount ?></em> - <?php echo date('Y/m/d', $person->date) ?></li>
<?php endforeach; ?> 
</ul>