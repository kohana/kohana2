<?php

include Kohana::find_file('vendor', 'smarty/Smarty.class');

class MY_Smarty_Core extends Smarty {

	function __construct()
	{
		// Check if we should use smarty or not
		if (Kohana_Config::get('smarty.integration') == FALSE)
			return;

		// Okay, integration is enabled, so call the parent constructor
		parent::Smarty();

		$this->cache_dir      = Kohana_Config::get('smarty.cache_path');
		$this->compile_dir    = Kohana_Config::get('smarty.compile_path');
		$this->config_dir     = Kohana_Config::get('smarty.configs_path');
		$this->plugins_dir[]  = Kohana_Config::get('smarty.plugins_path');
		$this->debug_tpl      = Kohana_Config::get('smarty.debug_tpl');
		$this->debugging_ctrl = Kohana_Config::get('smarty.debugging_ctrl');
		$this->debugging      = Kohana_Config::get('smarty.debugging');
		$this->caching        = Kohana_Config::get('smarty.caching');
		$this->force_compile  = Kohana_Config::get('smarty.force_compile');
		$this->security       = Kohana_Config::get('smarty.security');

		// check if cache directory is exists
		$this->checkDirectory($this->cache_dir);

		// check if smarty_compile directory is exists
		$this->checkDirectory($this->compile_dir);

		// check if smarty_cache directory is exists
		$this->checkDirectory($this->cache_dir);

		if ($this->security)
		{
			$configSecureDirectories = Kohana_Config::get('smarty.secure_dirs');
			$safeTemplates           = array(Kohana_Config::get('smarty.global_templates_path'));

			$this->secure_dir                          = array_merge($configSecureDirectories, $safeTemplates);
			$this->security_settings['IF_FUNCS']       = Kohana_Config::get('smarty.if_funcs');
			$this->security_settings['MODIFIER_FUNCS'] = Kohana_Config::get('smarty.modifier_funcs');
		}

		// Autoload filters
		$this->autoload_filters = array('pre'    => Kohana_Config::get('smarty.pre_filters'),
										'post'   => Kohana_Config::get('smarty.post_filters'),
										'output' => Kohana_Config::get('smarty.output_filters'));

		// Add all helpers to plugins_dir
		$helpers = glob(APPPATH . 'helpers/*', GLOB_ONLYDIR | GLOB_MARK);

		foreach ($helpers as $helper)
		{
			$this->plugins_dir[] = $helper;
		}
	}

	public function checkDirectory($directory)
	{
		if ((! file_exists($directory) AND ! @mkdir($directory, 0755)) OR ! is_writable($directory) OR !is_executable($directory))
		{
			$error = 'Compile/Cache directory "%s" is not writeable/executable';
			$error = sprintf($error, $directory);

			throw new Kohana_User_Exception('Compile/Cache directory is not writeable/executable', $error);
		}

		return TRUE;
	}
}
