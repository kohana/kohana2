<h2>Tutorials</h2>

<p>The following tutorials have been contributed by Kohana developers and users.</p>

<p>If you would like to see your tutorial here, please email <?php echo html::mailto('woody.gilk@gmail.com', 'Woody Gilk') ?> with your tutorial as an HTML page. We prefer that you use <?php echo html::anchor('http://qbnz.com/highlighter/', 'Geshi') ?> for syntax highlighting, specifically the <tt>geshi_highlight()</tt> function. However, you are free to highlight your code however you want, if you prefer not to use Geshi.</p>

<ul>
<?php foreach($titles as $link => $title): ?>
<li><?php echo html::anchor('tutorials/'.$link, $title) ?></li>
<?php endforeach; ?>
</ul>