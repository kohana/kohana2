<h2><span>Total Downloads: <?php echo $counter ?></span>Download Kohana</h2>

<p>You are downloading Kohana v<?php echo KOHANA_VERSION ?>. Please use <?php echo html::anchor('http://trac.kohanaphp.com/newticket', 'Trac tickets') ?> to report any bugs you experience. <span style="font-size:0.8em;font-style:italic;">Download files were last synced on <?php echo date('F jS, Y', $sync_date) ?>.</span></p>


<?php echo form::open('download') ?> 
<p>Choose your download type:</p>

<ul>
<li><label><?php echo form::radio('group', 'minimal', ($this->validation->group == 'minimal')) ?> <strong>Tiny Flower (Minimal)</strong></label> &ndash; Included libraries: Session, Validation. Included helpers: array, cookie, form, html, security, url, and validation.</li>
<li><label><?php echo form::radio('group', 'standard', ($this->validation->group == 'standard')) ?> <strong>Naked Babe (Standard)</strong></label> &ndash; Additional libraries: Archive, Database, Encryption, Pagination, and Profiler. Additional helpers: date, download, feed, inflector, and text.</li>
</ul>

<p>Include the following vendor tools in my download:</p>

<ul>
<?php

foreach($vendors as $name => $data):

	$key = strtolower($name);

?>
<li><label><?php echo form::checkbox('vendor['.$key.']', $name, isset($this->validation->vendor[$key])) ?> <strong><?php echo $name ?></strong></label> &ndash; <?php echo $data['description'] ?> <?php echo html::anchor($data['link'], 'More Information') ?>.</li>
<?php

endforeach;

?>
</ul>

<p>Include the following languages in my download:</p>
<?php echo $this->validation->languages_error ?> 
<ul>
<?php

foreach ($languages as $code => $lang):

?>
<li><label><?php echo form::checkbox('languages['.$code.']', $code, isset($this->validation->languages[$code])) ?> <?php echo $lang ?></label></li>
<?php

endforeach;

?>
</ul>

<p>Choose your download format:</p>
<?php echo $this->validation->format_error ?> 
<ul>
<?php

foreach ($formats as $ext => $format):

?>
<li><label><?php echo form::radio('format', $ext, ($this->validation->format == $ext)) ?> <?php echo $format ?></label></li>
<?php

endforeach;

?>
</ul>

<?php echo form::button(array('type' => 'submit', 'name' => 'download'), 'Download Kohana!') ?> 
<?php echo form::close() ?> 