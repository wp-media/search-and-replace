<?php

namespace Inpsyde\SearchAndReplace\Core;

use Inpsyde\SearchAndReplace\Exception\ConfigAlreadyFrozenException;
use Inpsyde\SearchAndReplace\Exception\NotFoundException;
use Psr\Container\ContainerInterface;

class PluginConfig implements ContainerInterface {

	/**
	 * List of properties.
	 *
	 * @var array
	 */
	protected $properties = [];

	/**
	 * Record of deleted properties.
	 *
	 * @var array
	 */
	protected $deleted = [];

	/**
	 * Write and delete protection.
	 *
	 * @var bool
	 */
	protected $frozen = FALSE;

	/**
	 * Set new value.
	 *
	 * @param  string $name
	 * @param  mixed  $value
	 *
	 * @return PluginConfig
	 */
	public function set( $name, $value ): PluginConfig {

		if ( $this->frozen ) {
			$this->stop(
				'This object has been frozen.
				You cannot set properties anymore.'
			);
		}
		$this->properties[ $name ] = $value;
		unset( $this->deleted[ $name ] );

		return $this;
	}

	/**
	 * Import an array or an object as properties.
	 *
	 * @param  array|object $var
	 *
	 * @return PluginConfig
	 */
	public function import( $var ): PluginConfig {

		if ( $this->frozen ) {
			$this->stop(
				'This object has been frozen.
				You cannot set properties anymore.'
			);
		}
		if ( ! is_array( $var ) && ! is_object( $var ) ) {
			$this->stop(
				'Cannot import this variable.
				Use arrays and objects only, not a "' . gettype( $var ) . '".'
			);
		}
		foreach ( $var as $name => $value ) {
			$this->properties[ $name ] = $value;
		}

		return $this;
	}

	/**
	 * @param string $id
	 *
	 * @return mixed
	 * @throws NotFoundException
	 */
	public function get( $id ) {

		if ( ! $this->has( $id ) ) {
			throw new NotFoundException( sprintf( 'The given key "%s" was not found', $id ) );
		}

		return $this->properties[ $id ];
	}

	/**
	 * Get all properties.
	 *
	 * @return array
	 */
	public function get_all(): array {

		return $this->properties;
	}

	/**
	 * @param string $id
	 *
	 * @return bool
	 */
	public function has( $id ): bool {

		if ( isset( $this->properties[ $id ] ) ) {
			return TRUE;
		}
		if ( isset( $this->deleted[ $id ] ) ) {
			return FALSE;
		}

		return FALSE;
	}

	/**
	 * Delete a key and set its name to the $deleted list.
	 *
	 * Further calls to has() and get() will not take this property into account.
	 *
	 * @param  string $name
	 *
	 * @return PluginConfig
	 */
	public function delete( $name ): PluginConfig {

		if ( $this->frozen ) {
			$this->stop(
				'This object has been frozen.
				You cannot delete properties anymore.'
			);
		}
		$this->deleted[ $name ] = TRUE;
		unset( $this->properties[ $name ] );

		return $this;
	}

	/**
	 * Lock write access to this object's instance. Forever.
	 *
	 * @return PluginConfig
	 */
	public function freeze(): PluginConfig {

		$this->frozen = TRUE;

		return $this;
	}

	/**
	 * Test from outside if an object has been frozen.
	 *
	 * @return boolean
	 */
	public function is_frozen(): bool {

		return $this->frozen;
	}

	/**
	 * Used for attempts to write to a frozen instance.
	 *
	 * Might be replaced by a child class.
	 *
	 * @param  string $msg  Error message. Always be specific.
	 * @param  string $code Re-use the same code to group error messages.
	 *
	 * @throws ConfigAlreadyFrozenException
	 */
	protected function stop( $msg, $code = '' ) {

		if ( '' === $code ) {
			$code = __CLASS__;
		}
		throw new ConfigAlreadyFrozenException( $msg, $code );
	}
}