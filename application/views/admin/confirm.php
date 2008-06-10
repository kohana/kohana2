<p class="warning message"><?php echo Kohana::lang('admin.action_undone');?></p>

<p class="confirm"><?php echo Kohana::lang('admin.confirm', array(html::anchor($action.'?confirm=yes', Kohana::lang('admin.continue')), html::anchor($action.'?confirm=no', Kohana::lang('admin.abort')))) ?></p>