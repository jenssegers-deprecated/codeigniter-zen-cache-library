<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| Zen configuration
| -------------------------------------------------------------------
| This file will contain the settings for the Zen library.
|
| 'adapter'	= the cache adapter you want the system to use (apc, file, memcached, dummy)
| 'expires'	= the default time an item should remain in cache
| 'folder'	= the sub directory to store cached items in, relative from default cache folder
*/

$config['adapter'] = 'file';
$config['expires'] = 60;
$config['folder']  = 'zen/';