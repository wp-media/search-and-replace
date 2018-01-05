<?php

namespace Inpsyde\SearchAndReplace\Settings\Auth;

use Brain\Nonces\ArrayContext;
use Brain\Nonces\NonceInterface;
use Brain\Nonces\WpNonce;
use Inpsyde\SearchAndReplace\Events\LoggingEvents;

class SettingsPageAuth implements SettingsPageAuthInterface {

	const DEFAULT_CAP = 'manage_options';

	/**
	 * @var string
	 */
	private $cap;

	/**
	 * @var WpNonce
	 */
	private $nonce;

	/**
	 * SettingsPageAuth constructor.
	 *
	 * @param string         $action
	 * @param string         $cap
	 * @param NonceInterface $nonce
	 */
	public function __construct( string $action, $cap = NULL, NonceInterface $nonce = NULL ) {

		$this->cap   = $cap === NULL ? self::DEFAULT_CAP : $cap;
		$this->nonce = $nonce == NULL ? new WpNonce( $action ) : $nonce;
	}

	/**
	 * @param array $request_data
	 *
	 * @return bool
	 */
	public function is_allowed( array $request_data = [] ) {

		if ( ! current_user_can( $this->cap ) ) {
			do_action(
				LoggingEvents::ERROR,
				'User has no sufficient rights to save page',
				[
					'method' => __METHOD__,
					'cap'    => $this->cap,
					'nonce'  => $this->nonce,
				]
			);

			return FALSE;
		}
		if ( is_multisite() && ms_is_switched() ) {
			return FALSE;
		}

		return $this->nonce->validate( new ArrayContext( $request_data ) );
	}

	/**
	 * @return NonceInterface
	 */
	public function nonce() {

		return $this->nonce;
	}

	/**
	 * @return string
	 */
	public function cap() {

		return (string) $this->cap;
	}
}