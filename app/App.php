<?php
namespace Intraxia\Readsync;

use Intraxia\Jaxion\Core\Application;

/**
 * Class App
 *
 * @package Intraxia\ReadSync
 */
class App extends Application {
	/**
	 * Application service providers.
	 *
	 * @var array
	 */
	public $providers = array(
		'Intraxia\\Readsync\\Providers\\ApiServiceProvider',
		'Intraxia\\Readsync\\Providers\\ModuleServiceProvider',
		'Intraxia\\Readsync\\Providers\\ViewServiceProvider',
	);
}
