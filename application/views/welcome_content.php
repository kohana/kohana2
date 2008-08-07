<p class="intro">This is the default Kohana index page. You may also access this page as <code><?php echo html::anchor('welcome/index', 'welcome/index') ?></code>.</p>

<p>To change what gets displayed for this page, edit <code>application/controllers/welcome.php</code>.</p>

<p>To change this text, edit <code>application/views/welcome.php</code> and <code>application/views/welcome_content.php</code>.

<ul>
<?php foreach ($links as $title => $url): ?>
<li><?php echo ($title === 'License') ? html::file_anchor($url, $title) : html::anchor($url, $title) ?></li>
<?php endforeach ?>
</ul>