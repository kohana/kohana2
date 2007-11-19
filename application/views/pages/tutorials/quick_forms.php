<h2><span>By Woody Gilk, &copy; 2007</span>Quick Form Generation</h2>

<p class="intro">Easily set up and validate forms with a <strong>custom model</strong> and <strong>view</strong>.</p>

<p>This tutorial will focus on showing you how to setup an easy and reusable form generator using models and views. The example here is for a user login form. This tutorial does not deal with database interaction, but only building, validating, and fetching the form data.</p>

<p class="note">This tutorial does not use the <abbr title="PHP Extension and Application Repository">PEAR</abbr> package <a href="http://pear.php.net/package/HTML_QuickForm2">HTML_QuickForm2</a>, but uses built-in Kohana functionality.</p>

<h4>The Model</h4>

<p>First, we need to create a model. Our model will allow us to set the form title and action, the Validation rules, and inputs. It will also handle validation automatically.</p>

<?php

echo geshi_highlight(file_get_contents(Kohana::find_file('models', 'form')), 'php', NULL, TRUE);

?>

<p style="color:#600">This file is included in Kohana by default as <tt>system/models/form.php</tt>.</p>

<p>One thing to note about our model is that most of the methods use <tt>return $this;</tt>. This allows chaining:</p>

<?php

echo geshi_highlight(
'$form = $this->load->model(\'form\', TRUE)
	->title(\'User Login\')
	->action(\'user/login\');
', 'php', NULL, TRUE);

?>

<p>All of the form methods except <tt>validate</tt> and <tt>build</tt> can be chained together.</p>

<h4>The View</h4>

<p>In order to display our form, we need a view. By default, the model will load a view called <tt>generate_form</tt>, but you are free to change the default name, or use a custom template with the <tt>generate</tt> method.</p>

<?php

echo geshi_highlight(file_get_contents(Kohana::find_file('views', 'kohana_form')), 'php', NULL, TRUE);

?>

<p style="color:#600">This file is included in Kohana by default as <tt>system/views/kohana_form.php</tt>. You can override the default form by creating <tt>application/views/kohana_form.php</tt>.</p>

<p>That concludes the entire tutorial. From here, you can expand the Form model to handle database connections, or even extend the model with another model, to handle specific forms. You can modify the view to suit your preferred way of templating forms, or use a custom view for specific forms.</p>

<p>For more information about handling database interaction in your forms, I recommend looking at the <?php echo html::anchor('tutorials/model_validation', 'Model Validation') ?> tutorial.</p>

<h4>Questions or Comments?</h4>

<p>Feel free to send me questions and comments, my email address is <?php echo html::mailto('woody.gilk@kohanaphp.com') ?>.</p>
