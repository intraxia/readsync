<?php
namespace Intraxia\Readsync\Providers;

use Intraxia\Jaxion\Contract\Core\Container;
use Intraxia\Jaxion\Contract\Core\ServiceProvider;
use Intraxia\Readsync\View\Auth;
use Intraxia\Readsync\View\Settings;

/**
 * Class ViewServiceProvider
 *
 * @package    Intraxia\Readsyn
 * @subpackage Providers
 */
class ViewServiceProvider implements ServiceProvider {
	/**
	 * {@inheritdoc}
	 *
	 * @param Container $container
	 */
	public function register( Container $container ) {
		$container->share( 'view.settings', function ( Container $container ) {
			return new Settings( $container->fetch( 'slug' ), $container->fetch( 'basename' ) );
		} );

		$container->share( 'view.auth', function ( Container $container ) {
			return new Auth( $container->fetch( 'api.pocket' ) );
		} );
	}
}
