<?php
/**
 * ReadSync
 *
 * ReadSync backs up your reading list from Pocket, using PressForward's import.
 *
 * @package   Intraxia\Readsync
 * @author    James DiGioia <jamesorodig@gmail.com>
 * @license   GPL-2.0+
 * @link      http://jamesdigioia.com/readsync/
 * @copyright 2017 James DiGioia
 *
 * @wordpress-plugin
 * Plugin Name:       Readsync
 * Plugin URI:        http://www.jamesdigioia.com/readsync/
 * Description:       ReadSync backs up your reading list from Pocket, using PressForward's import.
 * Version:           0.1.0
 * Author:            James DiGioia
 * Author URI:        http://www.jamesdigioia.com/
 * Text Domain:       readsync
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/intraxia/readlinks
 */

// Protect File.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Autoload Classes & CMB2.
$autoload = __DIR__ . '/lib/autoload.php';
if ( file_exists( $autoload ) ) {
	require_once $autoload;
}

// Validate PHP Version.
$update_php = new WPUpdatePhp( '5.3.0', 'readlinks' );

if ( ! $update_php->does_it_meet_required_php_version( PHP_VERSION ) ) {
	return;
}

// Boot!
call_user_func( array( new Intraxia\Readsync\App( __FILE__ ), 'boot' ) );
