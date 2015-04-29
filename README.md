# wppb-mod

A standardized, organized, object-oriented foundation for building high-quality WordPress Plugins.

## Requirements ##

wppb-mod comes with these tool requirements:

1. PHP 5.3+
	* Note: WordPress is compatible back to 5.2, so not all your users will be able to use this plugin yet.
2. [Composer][1], for back-end libraries.
3. [npm][2], for build tools.
3. [Bower][3], for front-end libraries.
4. [Gulp][4], for project builds

## Features

* New plugins can be generated with `composer create-project maadhattah/wppb-mod <target_dir>`.
	* The flattened structure is required for this to work.
* boilerplate is based on the [Plugin API](http://codex.wordpress.org/Plugin_API), [Coding Standards](http://codex.wordpress.org/WordPress_Coding_Standards), and [Documentation Standards](http://make.wordpress.org/core/handbook/inline-documentation-standards/php-documentation-standards/).
* All classes, functions, and variables are documented so that you know what you need to be changed.
* The app is loaded into a singleton so third-party developers can manipulate the hooks.
* The project includes a `.pot` file as a starting point for internationalization.
* The unit tests are scaffolded and ready to go, based on `wp scaffold plugin-tests`, which provides support for [travis-ci][1].

## Installation

The plugin can be developed in your `wp-content` folder directly. Run `gulp` to make the minified and concatenated files and begin the watch process. Whenever the scripts or styles change, gulp will recompile them into their respective css and js files.

When you want to provide a version to distribute, run `gulp build` and distribute the resulting .zip file.

## Recommended Tools

### i18n Tools

The WordPress Plugin Boilerplate uses a variable to store the text domain used when internationalizing strings throughout the Boilerplate. To take advantage of this method, there are tools that are recommended for providing correct, translatable files:

* [Poedit](http://www.poedit.net/)
* [makepot](http://i18n.svn.wordpress.org/tools/trunk/)
* [i18n](https://github.com/grappler/i18n)

Any of the above tools should provide you with the proper tooling to internationalize the plugin.

## License

The WordPress Plugin Boilerplate is licensed under the GPL v2 or later.

> This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License, version 2, as published by the Free Software Foundation.

> This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

> You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA

A copy of the license is included in the root of the directory. The file is named `LICENSE`.

## Important Notes

### Licensing

The WordPress Plugin Boilerplate is licensed under the GPL v2 or later; however, if you opt to use third-party code that is not compatible with v2, then you may need to switch to using code that is GPL v3 compatible.

For reference, [here's a discussion](http://make.wordpress.org/themes/2013/03/04/licensing-note-apache-and-gpl/) that covers the Apache 2.0 License used by [Bootstrap](http://twitter.github.io/bootstrap/).

### Includes

Note that if you include third-party libraries, use either `bower install --save-dev`, for front end libraries, or `composer require`, for php libraries.

Note that previous versions of the Boilerplate did not include `Loader` but this class is used to register all filters and actions with WordPress.

The example code provided shows how to register your hooks with the Loader class. More information will be provided in the upcoming documentation on the website.

### Assets

The `assets` directory contains three files.

1. `banner-772x250.png` is used to represent the plugin’s header image.
2. `icon-256x256.png` is a used to represent the plugin’s icon image (which is new as of WordPress 4.0).
3. `screenshot-1.png` is used to represent a single screenshot of the plugin that corresponds to the “Screenshots” heading in your plugin `README.txt`.

The WordPress Plugin Repository directory structure contains three directories:

1. `assets`
2. `branches`
3. `trunk`

The Boilerplate offers support for `assets` and `trunk` as `branches` is something that isn’t often used and, when it is, is done so under advanced circumstances.

When committing code to the WordPress Plugin Repository, all of the banner, icon, and screenshot should be placed in the `assets` directory of the Repository, and the core code should be placed in the `trunk` directory.

# Credits

The WordPress Plugin Boilerplate was started in 2011 by [Tom McFarlin](http://twitter.com/tommcfarlin/) and his since included a number of great contributions.

The current version of the Boilerplate was developed in conjunction with [Josh Eaton](https://twitter.com/jjeaton), [Ulrich Pogson](https://twitter.com/grapplerulrich), and [Brad Vincent](https://twitter.com/themergency). This fork is developed and maintained by [James DiGioia](http://jamesdigioia.com/).

The homepage is based on a design as provided by [HTML5Up](http://html5up.net), the Boilerplate logo was designed by  Rob McCaskill of [BungaWeb](http://bungaweb.com), and the site `favicon` was created by [Mickey Kay](https://twitter.com/McGuive7).

  [1]: https://getcomposer.org/
  [2]: https://www.npmjs.org/
  [3]: http://bower.io/
  [4]: http://gulpjs.com/
  [5]: https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate
