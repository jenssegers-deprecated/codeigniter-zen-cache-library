CodeIgniter Zen Cache Library
=============================

A caching library to cache attributes or function calls to libraries and models. This library was built to be as user-friendly as possible. You can add zen caching to existing code by adding a zen chain-link before your actual library or model.

Installation
------------

Place the files from the repository in their respective folders (or use spark).

Edit the configuration file:

	/*
	| -------------------------------------------------------------------
	| Zen configuration
	| -------------------------------------------------------------------
	| This file will contain the settings for the Zen library.
	|
	| 'adapter'	= the cache adapter you want the system to use (apc, file, memcached, dummy)
	| 'expires'	= the default time an item should remain in cache
	| 'folder'	= when using the file adapter you can choose to store the items in a sub 
	|             directory of CodeIgniter's cache folder
	*/

	$config['adapter'] = 'file';
	$config['expires'] = 60;
	$config['folder']  = 'zen';

The Zen library uses CodeIgniter's caching adapters internally, so you are able to set adapter specific using that adapter's configuration file. (http://codeigniter.com/user_guide/libraries/caching.html)
	
When using the file cache adapter, the Zen library will use the folder config parameter as a subdirectory for CodeIgniter's current cache directory. It will also add a .zen file extension.

Example
-------

	// uncached
	$this->blog_model->get_all();
	
	// cached
	$this->zen->blog_model->get_all();
	
	// cached with custom expire time in seconds
	$this->zen->expires(120)->blog_model->get_all();
	
	// cache a regular function
	$replaced = $this->zen->preg_replace($pattern, $replacement, $subject);
	
	// remove all cache related to blog_model
	$this->zen->clean('blog_model');
	
	// remove all cache
	$this->zen->clean();
	
**NOTE:** the clean functions are only available when using the file adapter!