<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Auth library configuration. By default, Auth will use the controller
 * database connection. If Database is not loaded, it will use use the default
 * database group.
 *
 * In order to log a user in, a user must have the `login` role. You may create
 * and assign any other role to your users.
 *
 * Database table schemas:
 * <code sql>
 * CREATE TABLE IF NOT EXISTS `users` (
 *   `id` int(11) unsigned NOT NULL auto_increment,
 *   `email` varchar(127) NOT NULL,
 *   `username` varchar(32) NOT NULL default '',
 *   `password` char(50) NOT NULL,
 *   `logins` int(10) unsigned NOT NULL default '0',
 *   PRIMARY KEY  (`id`),
 *   UNIQUE KEY `uniq_username` (`username`),
 *   UNIQUE KEY `uniq_email` (`email`)
 * ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
 *
 * CREATE TABLE IF NOT EXISTS `roles` (
 *   `id` int(11) unsigned NOT NULL auto_increment,
 *   `name` varchar(32) NOT NULL,
 *   `description` varchar(255) NOT NULL,
 *   PRIMARY KEY  (`id`),
 *   UNIQUE KEY `uniq_name` (`name`)
 * ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
 *
 * INSERT INTO `roles` (`id`, `name`, `description`) VALUES (1, 'login', 'Login access privileges');
 *
 * CREATE TABLE IF NOT EXISTS `users_roles` (
 *   `user_id` int(10) unsigned NOT NULL,
 *   `role_id` int(10) unsigned NOT NULL,
 *   PRIMARY KEY  (`user_id`,`role_id`)
 * ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
 * </code>
 */

/**
 * Type of hash to use for passwords. Any algorithm supported by the hash function
 * can be used here. Note that the length of your password is determined by the
 * hash type + the number of salt characters.
 * @see http://php.net/hash
 * @see http://php.net/hash_algos
 */
$config['hash_method'] = 'sha1';

/**
 * Defines the hash offsets to
 */
$config['salt_pattern'] = '1, 3, 5, 9, 14, 15, 20, 21, 28, 30';