<?php

namespace Inpsyde\SearchAndReplace\Settings\Auth;

use Brain\Nonces\ArrayContext;
use Brain\Nonces\NonceInterface;
use Brain\Nonces\WpNonce;
use Inpsyde\SearchAndReplace\Events\LogEvents;
use Inpsyde\SearchAndReplace\Http\Request;

/**
 * Class SettingsPageAuth
 *
 * @package Inpsyde\SearchAndReplace\Settings\Auth
 */
class SettingsPageAuth implements SettingsPageAuthInterface {

	const DEFAULT_CAP = 'manage_options';

	/**
	 * @var string
	 */
	protected $cap;

	/**
	 * @var WpNonce
	 */
	protected $nonce;

	/**
	 * @var array
	 */
	protected $errors = [];

	/**
	 * SettingsPageAuth constructor.
	 *
	 * @param string         $action
	 * @param string         $cap
	 * @param NonceInterface $nonce
	 */
	public function __construct( string $action = 'search-and-replace', $cap = self::DEFAULT_CAP, NonceInterface $nonce = NULL ) {

		$this->cap   = $cap;
		$this->nonce = $nonce == NULL ? new WpNonce( $action ) : $nonce;
	}

	/**
	 * @param Request $request
	 *
	 * @return bool
	 */
	public function is_allowed( Request $request ) {

		$context = [
			'method'  => __METHOD__,
			'cap'     => $this->cap,
			'nonce'   => $this->nonce,
			'request' => $request,
		];

		if ( ! current_user_can( $this->cap ) ) {
			$msg = __( 'User has no sufficient rights to save page', 'search-and-replace' );

			$this->errors[] = $msg;
			do_action(
				LogEvents::ERROR,
				$msg,
				$context
			);

			return FALSE;
		}

		$nonce_validated = $this->nonce->validate( new ArrayContext( (array) $request->data() ) );

		if ( ! $nonce_validated ) {
			$msg = __( 'Nonce did not validated correctly.', 'search-and-replace' );

			$this->errors[] = $msg;
			do_action(
				LogEvents::ERROR,
				$msg,
				$context
			);
		}

		return TRUE;
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

	/**
	 * @return array
	 */
	public function errors() {

		return $this->errors;
	}
}