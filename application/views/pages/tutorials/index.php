<h2>Tutorials</h2>

<p class="intro">The following tutorials have been contributed by Kohana developers and users.</p>

<p>If you would like to see your tutorial here, please email <?php echo html::mailto('woody.gilk@kohanaphp.com', 'Woody Gilk') ?> with your tutorial as a PHP page or zip archive. We prefer that you use <?php echo html::anchor('http://qbnz.com/highlighter/', 'Geshi') ?> for syntax highlighting, specifically the <tt>geshi_highlight()</tt> function. However, you are free to highlight your code however you want, if you prefer not to use Geshi.</p>

<?php foreach($titles as $heading => $group): ?>
<h5><?php echo $heading ?></h5>
<ul>
<?php foreach($group as $link => $title): ?>
<li><?php echo html::anchor('tutorials/'.$link, $title) ?></li>
<?php endforeach; ?>
</ul>

<?php endforeach; ?>