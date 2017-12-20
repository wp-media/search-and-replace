<?php
/**
 * Template for displaying replace domain page
 */

// Prevent direct access.
if ( ! defined( 'SEARCH_REPLACE_BASEDIR' ) ) {
	echo "Hi there!  I'm just a part of plugin, not much I can do when called directly.";
	exit;
}
?>
<p>
	<?php esc_html_e(
		'If you want to migrate your site to another domain, enter the new URL in the field "Replace with" and click "Do Replace Domain/Url". You can then download a database backup containing the new URL.',
		'search-and-replace'
	); ?>
</p>

<form action="" method="post">

	<table class="form-table">
		<tbody>

		<tr>
			<th>
				<label for="search">
					<?php esc_html_e( 'Search for: ', 'search-and-replace' ); ?>
				</label>
			</th>
			<td>
				<input id="search" type="text" name="search" value="<?php esc_url( get_site_url() ); ?>" />
			</td>
		</tr>

		<tr>
			<th>
				<label for="replace">
					<?php esc_html_e( 'Replace with: ', 'search-and-replace' ); ?>
				</label>
			</th>
			<td>
				<input
					id="replace"
					type="text"
					name="replace"
					placeholder="<?php esc_attr_e( 'New URL', 'search-and-replace' ); ?>"
				/>
			</td>
		</tr>

		<tr>
			<th>
				<label for="change_db_prefix">
					<?php esc_html_e( 'Change database prefix', 'search-and-replace' ); ?>
				</label>
			</th>
			<td><input id="change_db_prefix" type="checkbox" name="change_db_prefix" /></td>
		</tr>

		<tr class="disabled">
			<th>
				<label for="current_db_prefix">
					<?php esc_html_e( 'Current prefix: ', 'search-and-replace' ); ?>
				</label>
			</th>
			<td>
				<?php echo esc_html( $this->dbm->get_base_prefix() ); ?>
			</td>
		</tr>

		<tr class="maybe_disabled disabled">
			<th>
				<label for="new_db_prefix">
					<?php esc_html_e( 'New prefix: ', 'search-and-replace' ); ?>
				</label>
			</th>
			<td>
				<input
					id="new_db_prefix"
					type="text"
					name="new_db_prefix"
					disabled
					placeholder="<?php esc_attr_e( 'E.g new_', 'search-and-replace' ); ?>"
				/>
				<p>
					<?php esc_html_e( 'Underscore suffix "_" can be omitted', 'search-and-replace' ); ?>
				</p>
			</td>
		</tr>
		</tbody>
	</table>
	<?php $this->show_submit_button(); ?>
</form>
