<<?php echo $type.html::attributes($attr) ?>></<?php echo $type ?>>
<script type="text/javascript">
$.plot($('div#<?php echo $attr['id'] ?>'),
[
<?php echo "\t".implode(",\n\t", $dataset)."\n" ?>
],
<?php echo $options."\n" ?>
);
</script>