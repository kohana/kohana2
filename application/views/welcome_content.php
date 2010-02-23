<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div class="box">
	<p><?php echo \Library\I18n::__('This is the default Kohana index page. You may also access this page as')?> <code><?php echo \Helper\html::anchor('welcome/index', 'welcome/index') ?></code>.</p>

	<p>
		<?php echo \Library\I18n::__('To change what gets displayed for this page, edit')?> <code>application/controllers/welcome.php</code>.<br />
		<?php echo \Library\I18n::__('To change this text, edit')?> <code>application/views/welcome_content.php</code>.
	</p>
</div>

<ul>
<?php foreach ($links as $title => $url): ?>
	<li><?php echo ($title === 'License') ? \Helper\html::file_anchor($url, \Helper\html::chars(\Library\I18n::__($title))) : \Helper\html::anchor($url, \Helper\html::chars(\Library\I18n::__($title))) ?></li>
<?php endforeach ?>
</ul>