<?php
namespace Inpsyde\SearchReplace;

use Inpsyde\SearchReplace\Database\Exporter;

/**
 * Class FileDownloader
 *
 * @package Inpsyde\SearchReplace
 */
class FileDownloader {

	private $nonce_action = 'download_sql';

	private $nonce_name = 'insr_nonce';

	/**
	 * @var Exporter
	 */
	protected $dbe;

	/**
	 * Admin constructor.
	 *
	 * @param Exporter $dbe
	 */
	public function __construct( Exporter $dbe ) {

		$this->dbe = $dbe;
	}

	/**
	 * Renders to download modal.
	 *
	 * @param array $report
	 */
	public function show_download_modal( $report ) {

		if ( ! isset( $report[ 'changes' ] ) ) {
			echo '<p>' . esc_html__( 'Search pattern not found.', 'search-and-replace' ) . '</p>';
			return;
		}
		$compress = (bool) ( isset( $_POST[ 'compress' ] ) && 'on' === $_POST[ 'compress' ] );

		?>

		<div class="updated notice is-dismissible">
			<?php
			//show changes if there are any
			if ( count( $report[ 'changes' ] ) > 0 ) {
				$this->show_changes( $report );
			}
	
			//if no changes found report that
			if ( 0 === count( $report [ 'changes' ] ) ) {
				echo '<p>' . esc_html__( 'Search pattern not found.', 'search-and-replace' ) . '</p>';
			}
			?>
		</div>

		<div class="updated notice is-dismissible insr_sql_button_wrap">
			<p><?php esc_html_e('Your SQL file was created!', 'search-and-replace');?> </p>
			<form action method="post">
				<?php wp_nonce_field( $this->nonce_action, $this->nonce_name ); ?>
				<input type="hidden" name="action" value="download_file" />
				<input type ="hidden" name="sql_file" value="<?php echo esc_attr( $report[ 'filename' ] ); ?>">
				<input type ="hidden" name="compress" value="<?php echo esc_attr( $compress ); ?>">
				<input id ="insr_submit" type="submit" value="<?php esc_attr_e( 'Download SQL File', 'search-and-replace' ) ?>" class="button" />
			</form>
		</div>
		<?php
	}


	/**
	 * displays the changes made to the db
	 * echoes the changes in formatted html
	 *
	 * @param $report                 array 'errors' : WP-Error Object if Errors
	 *                                'tables' : Number of tables processed
	 *                                'changes_count' : Number of changes made
	 *                                'changes'
	 *                                Array  with at least these elements:
	 *                                'table_name'=>$[name of current table],
	 *                                'changes' => array('row'    => [row that has been changed ],
	 *                                'column' => [column that has been changed],
	 *                                'from'   => ( old value ),
	 *                                'to'     => ( $new value ),
	 *
	 * @return string
	 */
	private function show_changes( $report ) {

		//get search & replace values in order to highlight them in the results
		$search            = esc_html( $_POST [ 'search' ] );
		$search_highlight  = '<span class="search-replace-search-value">' . $search . '</span>';
		$replace           = esc_html( $_POST [ 'replace' ] );
		$replace_highlight = '<span class ="search-replace-replace-value">' . $replace . '</span>';
		$delimiter         = array( ' ...', '...<br>' );

		$msg = sprintf(
			_n(
				'%s table was processed.',
				'%s tables were processed.',
				$report[ 'tables' ],
				'search-and-replace'
			),
			$report[ 'tables' ]
		);

		$msg .= sprintf(
			_n(
				'%s cell needs to be updated.',
				'%s cells need to be updated.',
				$report[ 'changes_count' ],
				'search-and-replace'
			),
			$report[ 'changes_count' ]
		);
		echo esc_html( $msg );

		//create modal window for detailed view of changes
		?>
		<p><a href="#" id="changes-modal-button"><?php esc_html_e( 'View details', 'search-and-replace' ); ?></a></p>
		<div id="changes-modal-background" class="search-replace-modal-background" style="display: none;"></div>
		<div id="changes-modal" class="search-replace-modal " style="display: none;">
			<div class="search-replace-modal-header">
				<button type="button" id="changes-modal-close" class="search-replace-modal-close-button"></button>
			</div>
			<div class="search-replace-changes-modal-content">
		<?php
		foreach ( $report[ 'changes' ] as $table_report ) {
			$changes      = $table_report[ 'changes' ];
			$changes_made = count( $changes );

			if ( $changes_made > 0 ) {
				$table = $table_report[ 'table_name' ];
				$html  = '<h2 class = "search-replace-modal-table-headline">';
				$html .= '<strong>' . esc_attr__( 'Table:', 'search-and-replace' ) . '</strong> ' . $table;
				$html .= '<strong>' . esc_attr__( 'Changes:', 'search-and-replace' ) . '</strong> ' . $changes_made;
				$html .= '</h2>';

				$html .= '<table class="search-replace-modal-table"><colgroup><col><col><col><col><col><col><col><col></colgroup>';

				foreach ( $changes as $change ) {

					$html .= '<tr>';
					$html .= '<th class="search-replace-narrow">' . __( 'row', 'search-and-replace' ) . '</th>
						<td class="search-replace-narrow">' . $change [ 'row' ] . '</td>
				         <th> ' . __( 'column', 'search-and-replace' ) . '</th>
				        <td>' . $change [ 'column' ] . '</td> ';

					//trim results and wrap with highlight class
					$old_value = esc_html( $change [ 'from' ] );
					$old_value = $this->trim_search_results( $search, $old_value, $delimiter );
					$old_value = str_replace( $search, $search_highlight, $old_value );

					$new_value = esc_html( $change[ 'to' ] );
					$new_value = $this->trim_search_results( $replace, $new_value, $delimiter );
					$new_value = str_replace( $replace, $replace_highlight, $new_value );

					$html .= '<th>' . __( 'Old value:', 'search-and-replace' ) . '</th>
							<td>' . $old_value . '</td>
						<th> ' . __( 'New value:', 'search-and-replace' ) . '</th><td>' . $new_value . '</td>';
					$html .= '</tr>';
				}
				$html .= '</table>';

				echo $html;
			}
		}

		echo '</div></div>';
	}


	/**
	 * calls the file delivery in Class DatabaseExporter
	 *
	 * @wp-hook init
	 */
	public function deliver_backup_file() {

		if ( ! $_SERVER[ 'REQUEST_METHOD' ] === 'POST' ) {
			return;
		}

		if ( ! isset( $_POST[ 'insr_nonce' ] ) || ! wp_verify_nonce( $_POST[ 'insr_nonce' ], 'download_sql' ) ) {
			return;
		}

		if ( isset( $_POST[ 'action' ] ) && 'download_file' === $_POST[ 'action' ] ) {

			$sql_file = '';
			if ( isset( $_POST[ 'sql_file' ] ) ) {
				$sql_file = $_POST[ 'sql_file' ];
			}

			$compress = FALSE;
			if ( isset( $_POST[ 'compress' ] ) ) {
				$compress = $_POST[ 'compress' ];
			}

			// If file name contains path or does not end with '.sql' exit.
			$ext = strrchr( $sql_file, '.' );
			if ( FALSE !== strpos( $sql_file, '/' ) || '.sql' !== $ext ) {
				die;
			}
			$this->dbe->deliver_backup( $sql_file, $compress );
		}

	}
}