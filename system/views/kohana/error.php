<pre class="kohana_error"><a href="#show_trace"><?php echo $code ?></a>: <code><?php echo $error ?></code> [ <strong><?php echo $file ?></strong>, line <strong><?php echo $line ?></strong> ]</pre>
<?php if ( ! empty($trace)): ?>
<div class="kohana_trace">
<?php if ( ! empty($source)): ?>
<div class="source"><a href="#show_source">Source</a><pre><?php echo $source ?></pre></div>
<?php endif ?>
<pre class="trace"><?php echo implode("\n", $trace) ?></pre>
</div>
<?php endif ?>
