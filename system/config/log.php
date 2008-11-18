<?php
/**
 * Set default logging threshold.
 *
 *     $config['threshold'] = 1;
 *
 * It is highly recommended to enable error and exception logging on production
 * websites and to disable
 *
 * - 0: Disable all logging
 * - 1: Log only PHP errors and exceptions
 * - 2: Also log PHP warnings
 * - 3: Also log PHP notices
 * - 4: Also log Kohana debugging messages
 */
$config['threshold'] = 1;

/**
 * Set default logging directory.
 *
 *     $config['log_directory'] = APPPATH.'logs';
 *
 * Any writable directory can be specified here. Path can be relative to the
 * DOCROOT, or an absolute path.
 */
$config['directory'] = APPPATH.'logs';
