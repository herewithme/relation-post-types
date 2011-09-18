=== Relations Post Types ===
Contributors: momo360modena,Rahe
Donate link: http://www.beapi.fr/donate/
Tags : custom, post types, cms, post type, relation
Requires at least: 3.0
Tested up to: 3.2.1
Stable tag: 1.2.1

== Description ==

WordPress 3.0 and up allow to manage new custom post type. Cool !
This plugin allow to build relation between 2 custom types, very useful for manage related content.

For full info go the [Relations Post Types](http://redmine.beapi.fr/projects/show/relations-post-types) page.

== Installation ==

1. Download, unzip and upload to your WordPress plugins directory
2. Activate the plugin within you WordPress Administration Backend
3. Go to Settings > Relations and follow the steps on the [Simple Custom Types](http://redmine.beapi.fr/projects/show/relations-post-types) page.

TODO : Add some theme integration here

== Screenshots ==

1. Metabox in Post Types for creating a relation
2. Option page to set relations between two content types

== Changelog ==

* Version 1.2.1 :
	* Add query var with prefix "rel-" for each CPT allow filtering on URL
* Version 1.2 :
	* Stable enough for remove beta version
	* Add some template functions for an easier usage
	* Fix a very rare bug when a post type have relation with the same post type and when this relation is empty, that delete others relations of this post id. (thanks to bniess for reporting bug)
* Version 1.1-beta2 :
	* Fix possible bug with others plugin's beapi that use import 
* Version 1.1-beta1 :
	* Add tab for searching
	* Add tab to select most used elements
	* Add function to get the most associated elements from a post_type
	* Add import/export config tool
* Version 1.0.13 :
	* Fix a warning on admin write page
* Version 1.0.12 :
	* Fix a bug with CSS not existing. (bug copy/paste)
	* Add a argument for get single relation
* Version 1.0.11 :
	* Fix name plugin
	* Possible fix for widget, add reset and use "post__in" instead "include"
* Version 1.0.10 :
	* Fix bug with data lost and quick edit
* Version 1.0.9 :
	* Fix possible bug with folder name
	* Fix bug with functions API and switch_to_blog()
* Version 1.0.8 :
	* Fix potential warning during saving.
	* Fix bug that not allow to uncheck all relations
* Version 1.0.7 :
	* Fix several bugS during saving relations, get relations.
* Version 1.0.6 :
	* Check compatibility with WP 3.0.1
	* Fix constant construction. (try dynamic method)
* Version 1.0.5 :
	* Fix renaming class
* Version 1.0.4 :
	* Fix generation of metabox on admin. Fix for correspond to description and remove stupid return...
* Version 1.0.3 :
	* Fix notices
	* Allow relations between objects of the same post type
* Version 1.0.2 :
	* Optimize some PHP codes
	* Add POT language + French
* Version 1.0.1 :
	* Finalize widget !
	* Add nonce for improve security
* Version 1.0.0 :
	* First version stable