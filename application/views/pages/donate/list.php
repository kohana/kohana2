<h2>Donation List</h2>
<p>Below is the list of names of those who generously gave money to the Kohana Project.</p>
<ul>
	<?php foreach ($donation_list as $person):?><li><strong><?php echo $person->name ?></strong> - <em>$<?php echo $person->amount ?></em> - <?php echo date('Y/m/d', $person->date) ?></li>
<?php endforeach; ?> 
</ul>