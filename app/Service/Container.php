<?php
namespace ReadSync\Service;

/**
 * The Service Container class.
 *
 * @package    ReadSync
 * @author     Your Name <email@example.com>
 * @link       http://example.com
 * @since      1.0.0
 * @todo       Documentation
 */
class Container {

	/**
	 * Services registered in the container
	 * @var array
	 */
	protected $services = array();

	/**
	 * Searches the registered directories for modules to register
	 *
	 * @since 1.0.0
	 */
	public function register() {
		$folders = apply_filters( 'readsync_search_dirs', array( __DIR__ ) );

		foreach ( $folders as $folder ) {
			if ( ! is_dir( $folder ) ) {
				// @todo throw warning because this is the registered directory
				// and someone configured something wrong
				continue;
			}

			$folder_contents = array_diff( scandir( $folder ), array( '.', '..' ) );
			// @todo make callback a method?
			$folder_contents = array_map( function( $val ) use ( $folder ) {
				return $folder . '/' . $val;
			}, $folder_contents );

			$folder_dirs = array_filter( $folder_contents, 'is_dir' );

			foreach ( $folder_dirs as $dir ) {
				$classes = $this->classes_in_file( $dir . '/Module.php' );
				$info = array_shift( $classes );

				foreach ( $info['classes'] as $class_info ) {
					if ( 'CLASS' === $class_info['type'] ) {
						$class = '\\' . $info['namespace'] . '\\' . $class_info['name'];
						break;
					}
				}
			}

			// @todo check the class implements the set interface

			$this->services[] = array(
				'slug'  => $class::$slug,
				'class' => $class,
			);
		}

		foreach ( $this->services as $service ) {
			pressforward_register_module( array(
				'slug'  => $service['slug'],
				'class' => $service['class'],
			) );

		}
	}

	/**
	 * Searches a filepath and returns the classes in the file
	 *
	 * @param  string $file path to file to check
	 * @return mixed        class string, null if not found
	 * @source  http://stackoverflow.com/a/11114724/2757940
	 */
	protected function classes_in_file( $file ) {

		$classes = $nsPos = $final = array();
		$foundNS = false;
		$ii = 0;

		if ( ! file_exists( $file ) ) {
			return null;
		}

		$php_code = file_get_contents( $file );
		$tokens = token_get_all( $php_code );
		$count = count( $tokens );

		for ( $i = 0; $i < $count; $i++ ) {
			if ( ! $foundNS && T_NAMESPACE == $tokens[ $i ][0] ) {
				$nsPos[ $ii ]['start'] = $i;
				$foundNS = true;
			}
			elseif ( $foundNS && ( ';' == $tokens[ $i ] || '{' == $tokens[ $i ] ) ) {
				$nsPos[ $ii ]['end'] = $i;
				$ii++;
				$foundNS = false;
			} elseif ( $i-2 >= 0 && $tokens[ $i - 2 ][0] == T_CLASS && $tokens[ $i - 1 ][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING ) {
				if( $i-4 >=0 && $tokens[$i - 4][0] == T_ABSTRACT ) {
					$classes[$ii][] = array('name' => $tokens[$i][1], 'type' => 'ABSTRACT CLASS');
				} else {
					$classes[$ii][] = array('name' => $tokens[$i][1], 'type' => 'CLASS');
				}
			} elseif ( $i-2 >= 0 && $tokens[$i - 2][0] == T_INTERFACE && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING ) {
				$classes[$ii][] = array('name' => $tokens[$i][1], 'type' => 'INTERFACE');
			}
		}

		if ( empty( $classes ) ) {
			return null;
		}

		if(!empty($nsPos))
		{
				foreach($nsPos as $k => $p)
				{
						$ns = '';
						for($i = $p['start'] + 1; $i < $p['end']; $i++)
								$ns .= $tokens[$i][1];

						$ns = trim($ns);
						$final[$k] = array('namespace' => $ns, 'classes' => $classes[$k+1]);
				}
				$classes = $final;
		}
		return $classes;
	}
}
