<h2><span><?php echo Kohana::lang('download.total', $counter);?></span><?php echo Kohana::lang('download.version', $release_version);?></h2>
<?php echo form::open('download', array('method' => 'get', 'id' => 'downloadBuilder')) ?> 
<p style="font-size:1.2em"><?php echo Kohana::lang('download.parts');?></p>
<p style="font-size:0.8em;font-style:italic;"><?php echo Kohana::lang('download.release_codename', array(date('F jS, Y', $release_date), $release_codename));?></p>
<?php include Kohana::find_file('views', 'form_errors') ?>
<fieldset>
    <span class="legend"><?php echo Kohana::lang('download.include_modules');?></span>
    <ul>
    <?php
	foreach($modules as $name => $description):
	    $key = strtolower($name);
    ?>
		<li>
	    	<label><?php echo form::checkbox('modules['.$key.']', $name, isset($download['modules'][$key])) ?> <strong><?php echo $name ?></strong></label> 
	    	&ndash; <?php echo $description ?>
		</li>
    <?php endforeach;?>
    </ul>
</fieldset>
<fieldset>
    <span class="legend"><?php echo Kohana::lang('download.include_vendor');?></span>
    <ul>
    <?php
	foreach($vendors as $name => $data):
	    $key = strtolower($name);
    ?>
		<li><label><?php echo form::checkbox('vendors['.$key.']', $name, isset($download['vendors'][$key])) ?> <strong><?php echo $name ?></strong></label> &ndash; <?php echo $data['description'] ?> <?php echo html::anchor($data['link'], Kohana::lang('download.more_information')) ?></li>
    <?php endforeach;?>
    </ul>
</fieldset>
<fieldset>
    <span class="legend"><?php echo Kohana::lang('download.include_languages');?></span>
    <ul>
    <?php
	foreach ($languages as $code => $lang):
    ?>
		<li><label><?php echo form::checkbox('languages['.$code.']', $code, isset($download['languages'][$code])) ?> <?php echo $lang ?></label></li>
    <?php endforeach;?>
    </ul>
</fieldset>
<fieldset>
    <span class="legend"><?php echo Kohana::lang('download.compress');?></span>
    <ul>
    <?php
	foreach ($formats as $ext => $format):
    ?>
		<li><label><?php echo form::radio('format', $ext, ($download['format'] === $ext)) ?> <?php echo $format ?></label></li>
    <?php endforeach;?>
    </ul>
</fieldset>
<?php echo Kohana::lang('download.buttons', array(form::button(array('type' => 'submit', 'id' => 'downloadBuilderSubmit'), Kohana::lang('download.button')), form::button(array('type' => 'button', 'id' => 'queryViewButton'), Kohana::lang('download.url')))) ?>
<textarea id="downloadUrlDisplay" class="legend"><!-- AJAX --></textarea>
<?php echo form::close() ?>