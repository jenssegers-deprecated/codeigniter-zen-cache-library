CodeIgniter Zen Cache Library
=============================

A caching library to cache attributes or function calls to libraries and models.

Example
-------

	// uncached
	$this->blog_model->get_all();
	
	// cached
	$this->zen->blog_model->get_all();
	
	// cached with custom expire time in seconds
	$this->zen->expires(120)->blog_model->get_all();