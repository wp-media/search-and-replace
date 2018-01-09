<?php

namespace Inpsyde\SearchAndReplace\Settings\View;

use Brain\Nonces\NonceInterface;
use Inpsyde\SearchAndReplace\Settings\SettingsPageInterface;

/**
 * @package Inpsyde\SearchAndReplace\Page
 */
interface SettingsPageViewInterface {

	/**
	 * Render all pages and handling save.
	 *
	 * @param SettingsPageInterface[] $pages
	 * @param NonceInterface          $nonce
	 */
	public function render( array $pages = [], NonceInterface $nonce );

}
