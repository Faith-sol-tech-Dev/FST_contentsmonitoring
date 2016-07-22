<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return array(
	'v'  => array('v' => 'v1'),
	'db' => array(
		//'dsn' => 'mysql:dbname=dz49_contents_monitor_stg;host=mysql1.sun.site-advance.jp',
		//'username' => 'dz49_9835',
		//'password' => 'asdf!234',
		'dsn' => 'mysql:dbname=staging.cm;host=10.150.200.128;',
		'username' => 'root',
		'password' => 'Work-277',
	),
	'session' => array(
		'length' => 40,
		'expire' => 1800,
		'limit' => 100,
	),
	'token' => array(
		'index' => 'cm',
		'key' => 'token-%s',
	),
	'mail' => array(
	),
	'const' => array(
		'rand_list' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
		'regex_mail' => "/\A[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@((?:[_a-z0-9][-_a-z0-9]*\.)*(?:[a-z0-9][-a-z0-9]{0,62})\.(?:(?:[a-z]{2}\.)?[a-z]{2,}))\z/u",
		'regex_url' => "/\A(https?):\/\/([-_.!~*\'()a-z0-9;\/?:@&=+$,%#]+)\z/ui",
		'cryptography_key' => 'safa1iK3wnks%lD8',
	),
	'api' => array(
		'user_agent' => 'Mozilla/5.0 (compatible; ContentsMonitor/1.0; +http://www.faith-sol-tech.com/)',
		'retry_count' => 3,
		'interval' => 5,
		'timeout' => 5,
		'max_redirects' => 20,
		'mail' => array(
			'from' => array(
				'email' => 'admin@inoue.cm.ip128.ip140.faith-sol-tech.local',
				'name' => 'あどみん',
			),
			'to' => array(
				array(
					'email' => 'dev-test@faith-sol-tech.com',
					'name' => 'ふぇいす',
				),
				array(
					'email' => 'inoue@verso.jp',
					'name' => 'べるそ',
				),
			),
			'template' => array(
				'success' => array(
					'subject' => '【たいとる】API処理成功！',
					'body' => '【ほんぶん】API処理成功！',
				),
				'failure' => array(
					'subject' => '【たいとる】API処理失敗！',
					'body' => '【ほんぶん】API処理失敗！',
				),
			),
		),
	),
);
