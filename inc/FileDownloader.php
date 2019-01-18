<?php

namespace Inpsyde\SearchReplace;

use Inpsyde\SearchReplace\Database\Exporter;
use Inpsyde\SearchReplace\Service\MaxExecutionTime;

/**
 * Class FileDownloader
 *
 * @package Inpsyde\SearchReplace
 */
class FileDownloader {

	/**
	 * @var string
	 */
	private $nonce_action = 'download_sql';

	/**
	 * @var string
	 */
	private $nonce_name = 'insr_nonce_download';

	/**
	 * @var Exporter
	 */
	private $dbe;

	/**
	 * @var MaxExecutionTime
	 */
	private $max_execution;

	/**
	 * Admin constructor.
	 *
	 * @param Exporter $dbe
	 * @param MaxExecutionTime $max_execution
	 */
	public function __construct( Exporter $dbe, MaxExecutionTime $max_execution ) {

		$this->dbe           = $dbe;
		$this->max_execution = $max_execution;
	}

	/**
	 * Renders to download modal.
	 *
	 * @param array $report
	 */
	public function show_modal( $report ) {

		// Set compress status.
		// @codingStandardsIgnoreLine
		$compress = ( isset( $_POST['compress'] ) && 'on' === $_POST['compress'] );

		if ( array_key_exists( 'changes', $report ) && ! empty( $report['changes'] ) ) :
			?>
			<div class="updated notice is-dismissible">
				<?php
				// Show changes if there are any.
				if ( count( $report['changes'] ) > 0 ) {
					$this->show_changes( $report );
				}

				// If no changes found report that.
				if ( 0 === count( $report ['changes'] ) ) {
					echo '<p>' . esc_html__( 'Search pattern not found.', 'search-and-replace' ) . '</p>';
				}
				?>
			</div>
		<?php
		endif;
		?>

		<div class="updated notice is-dismissible insr_sql_button_wrap">
			<p><?php esc_html_e( 'Your SQL file was created!', 'search-and-replace' ); ?> </p>
			<form action method="post">
				<?php wp_nonce_field( $this->nonce_action, $this->nonce_name ); ?>
				<input type="hidden" name="action" value="download_file"/>
				<input type="hidden" name="sql_file" value="<?php echo esc_attr( $report['filename'] ); ?>">
				<input type="hidden" name="compress" value="<?php echo esc_attr( $compress ); ?>">
				<input id="insr_submit" type="submit" value="<?php esc_attr_e(
					'Download SQL File', 'search-and-replace'
				) ?>" class="button"/>
			</form>
		</div>
		<?php
	}

	/**
	 * Displays the changes made to the db
	 * echoes the changes in formatted html.
	 *
	 * @param array $report WP-Error Object if Errors
	 *
	 *      'tables' : Number of tables processed
	 *      'changes_count' : Number of changes made
	 *      'changes' : Array  with at least these elements:
	 *          'table_name'=> $[name of current table],
	 *          'changes'   => array('row'    => [row that has been changed ],
	 *          'column'    => [column that has been changed],
	 *          'from'      => ( old value ),
	 *          'to'        => ( $new value ),
	 * .
	 */
	public function show_changes( $report ) {

		// Get search & replace values in order to highlight them in the results.
		// @codingStandardsIgnoreStart
		$search  = isset( $_POST['search'] ) ?
			esc_html( filter_var( $_POST ['search'], FILTER_SANITIZE_STRING ) ) :
			'';
		$replace = isset( $_POST['replace'] ) ?
			esc_html( filter_var( $_POST ['replace'], FILTER_SANITIZE_STRING ) ) :
			'';
		// @codingStandardsIgnoreEnd

		$search_highlight  = '<span class="search-replace-search-value">' . $search . '</span>';
		$replace_highlight = '<span class ="search-replace-replace-value">' . $replace . '</span>';
		$delimiter         = [ ' ...', '...<br>' ];

		$this->show_message_by_report( $report );

		?>
		<p><a href="#" id="changes-modal-button"><?php esc_html_e( 'View details', 'search-and-replace' ); ?></a></p>
		<div id="changes-modal-background" class="search-replace-modal-background" style="display: none;"></div>
		<div id="changes-modal" class="search-replace-modal " style="display: none;">
			<div class="search-replace-modal-header">
				<button type="button" id="changes-modal-close" class="search-replace-modal-close-button"></button>
			</div>
			<div class="search-replace-changes-modal-content">
				<?php
				foreach ( $report['changes'] as $table_report ) :
					$changes      = $table_report['changes'];
					$changes_made = count( $changes );

					if ( $changes_made < 1 ) {
						continue;
					}

					$table = $table_report['table_name'];
					?>
					<h2 class="search-replace-modal-table-headline">
						<strong><?php esc_html_e( 'Table:', 'search-and-replace' ); ?></strong>
						<?php echo esc_html( $table ); ?>

						<strong><?php esc_html_e( 'Columns Changes:', 'search-and-replace' ); ?></strong>
						<?php echo esc_html( $changes_made ); ?>
					</h2>

					<table class="search-replace-modal-table">

						<thead>
						<tr>
							<th class="search-replace-row search-replace-narrow">
								<?php esc_html_e( 'Row', 'search-and-replace' ); ?>
							</th>
							<th class="search-replace-column">
								<?php esc_html_e( 'Column', 'search-and-replace' ); ?>
							</th>
							<th class="search-replace-old-value">
								<?php esc_html_e( 'Old value', 'search-and-replace' ); ?>
							</th>
							<th class="search-replace-new-value">
								<?php esc_html_e( 'New value', 'search-and-replace' ); ?>
							</th>
						</tr>
						</thead>

						<tbody>
						<?php
						foreach ( $changes as $change ) :
							// Trim results and wrap with highlight class.
							$old_value = esc_html( $change ['from'] );
							$old_value = $this->trim_search_results( $search, $old_value, $delimiter );
							$old_value = str_replace( $search, $search_highlight, $old_value );

							$new_value = esc_html( $change['to'] );
							$new_value = $this->trim_search_results( $replace, $new_value, $delimiter );
							$new_value = str_replace( $replace, $replace_highlight, $new_value );

							if ( $old_value && $new_value ) :
								?>
								<tr>
									<td class="search-replace-row search-replace-narrow">
										<?php echo esc_html( $change ['row'] ); ?>
									</td>
									<td class="search-replace-column">
										<?php echo esc_html( $change ['column'] ); ?>
									</td>
									<td class="search-replace-old-value">
										<?php echo wp_kses( $old_value, [ 'span' => [ 'class' => [] ] ] ); ?>
									</td>
									<td class="search-replace-new-value">
										<?php echo wp_kses( $new_value, [ 'span' => [ 'class' => [] ] ] ); ?>
									</td>
								</tr>
							<?php
							endif;
						endforeach;
						?>
						</tbody>

					</table>

				<?php endforeach; ?>

			</div>
		</div>
		<?php
	}

	/**
	 * Show Message by Report
	 *
	 * @param array $report The list of all the changes made.
	 *
	 * @return void
	 */
	private function show_message_by_report( $report ) {

		$msg = sprintf(
				// translators: %s is the count of tables.
			_n(
				'%s table was processed. ',
				'%s tables were processed. ',
				$report['tables'],
				'search-and-replace'
			),
			(int) $report['tables']
		);

		$msg .= sprintf(
				// translators: %s is the count of selected cells after search in tables.
			_n(
				'%s cell needs to be updated.',
				'%s cells need to be updated.',
				$report['changes_count'],
				'search-and-replace'
			),
			(int) $report['changes_count']
		);

		echo '<p>' . esc_html( $msg ) . '</p>';
	}

	/**
	 * Trims a given string to 50 chars before and after the search string, if the string is longer than 199 chars.
	 *
	 * @param string $needle
	 * @param string $haystack
	 * @param array  $delimiter $delimiter[0]=start delimiter, $delimiter[1] = end delimiter.
	 *
	 * @return string The trimmed $haystack
	 */
	public function trim_search_results( $needle, $haystack, $delimiter ) {

		// Ff result has <100 characters we return the whole string.
		if ( strlen( $haystack ) < 100 ) {
			return $haystack;
		}
		$trimmed_results = null;
		// Get all occurrences of $needle with up to 50 chars front & back.
		$matches      = preg_match_all( '@.{0,50}' . $needle . '.{0,50}@', $haystack, $trimmed_results );
		$return_value = '';

		// Don't need to perform any action if no matches.
		if ( ! $matches ) {
			return $return_value;
		}

		for ( $i = 0; $i < $matches; $i ++ ) {
			// Reset delimiter, might have been changed.
			$local_delimiter = $delimiter;

			// Check if the first trimmed result is the beginning of $haystack. if so remove leading delimiter.
			if ( $i === 0 ) {
				$pos = strpos( $haystack, $trimmed_results[0][ $i ] );
				if ( $pos === 0 ) {
					$local_delimiter[0] = '';
				}
			}

			// Check if the last trimmed result is the end of $haystack. if so, remove trailing delimiter.
			$last_index = $matches - 1;

			if ( $i === $last_index ) {
				$trimmed_result_length = strlen( $trimmed_results[0][ $i ] );
				$substring             = substr( $haystack, - $trimmed_result_length );

				if ( $substring === $trimmed_results[0][ $i ] ) {
					$local_delimiter[1] = '';
				}
			}
			$return_value .= $local_delimiter[0] . $trimmed_results[0][ $i ] . $local_delimiter[1];
		}

		return $return_value;
	}

	/**
	 * Calls the file delivery in Class DatabaseExporter.
	 *
	 * @wp-hook init
	 *
	 * @return bool
	 */
	public function deliver_backup_file() {

		// Retrieve the nonce value.
		// @codingStandardsIgnoreStart
		$nonce = isset( $_POST[ $this->nonce_name ] ) ?
			filter_var( $_POST[ $this->nonce_name ], FILTER_SANITIZE_STRING )
			: '';
		// @codingStandardsIgnoreEnd

		// If nonce has not been send, just return nothing else to do here.
		// The method may be hooked to a wp action, so it's executed on every page request.
		if ( ! $nonce ) {
			return false;
		}

		// Die in case the nonce has been passed but not a valid one.
		// @codingStandardsIgnoreLine
		if ( ! wp_verify_nonce( $_POST[ $this->nonce_name ], $this->nonce_action ) ) {
			wp_die( 'Cheating Uh?' );
		}

		$this->max_execution->set();

		// Get the action to perform.
		// @codingStandardsIgnoreLine
		$action = isset( $_POST['action'] ) ? filter_var( $_POST['action'], FILTER_SANITIZE_STRING ) : '';

		if ( 'download_file' !== $action ) {
			return false;
		}

		$sql_file = '';
		$compress = false;

		// @codingStandardsIgnoreLine
		if ( isset( $_POST['sql_file'] ) ) {
			// @codingStandardsIgnoreLine
			$sql_file = sanitize_file_name( $_POST['sql_file'] );
		}

		if ( ! $sql_file ) {
			wp_die( esc_html__( 'The file you are looking for doesn\'t exists.', 'search-and-replace' ) );
		}

		// If file name contains path or does not end with '.sql' exit.
		// @ToDo create a function to prevent traversal path.
		$ext = strrchr( $sql_file, '.' );
		if ( false !== strpos( $sql_file, '/' ) || '.sql' !== $ext ) {
			wp_die( 'Cheating Uh?' );
		}

		// @codingStandardsIgnoreLine
		if ( isset( $_POST['compress'] ) ) {
			// @codingStandardsIgnoreLine
			$compress = (bool) filter_var( $_POST['compress'], FILTER_VALIDATE_BOOLEAN );
		}

		// Download the file.
		$this->dbe->deliver_backup( $sql_file, $compress );

		$this->max_execution->restore();

		return true;
	}
}
