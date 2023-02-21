<?php
/**
 * Class: Autocomplete Widget
 *
 * Autocomplete Widget for search extension.
 *
 * @since 1.0.0
 * @package wsal
 * @subpackage search
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WSAL_AS_Filters_AutoCompleteWidget
 *
 * @package wsal
 * @subpackage search
 */
class WSAL_AS_Filters_AutoCompleteWidget extends WSAL_AS_Filters_AbstractWidget {

	/**
	 * User query.
	 *
	 * @var string
	 */
	public $user_query = '';

	/**
	 * Loaded data.
	 *
	 * @var array
	 */
	protected $loaded_data = array();

	/**
	 * Add values to the loaded data.
	 *
	 * @param string       $nice   Nicely formatted value.
	 * @param string|array $tokens Tokens.
	 */
	public function add( $nice, $tokens ) {
		if ( is_string( $tokens ) ) {
			$tokens = array( $tokens );
		}
		$this->loaded_data[] = array(
			'value'  => $nice,
			'tokens' => array_unique( $tokens ),
		);
	}

	/**
	 * Handles AJAX call to load data.
	 */
	public function handle_ajax() {
		$this->user_query = $_REQUEST['search']; // phpcs:ignore
		$this->load_data( true );
		header( 'Content-Type: application/json' );
		die( json_encode( $this->loaded_data ) ); // phpcs:ignore
	}

	/**
	 * {@inheritDoc}
	 */
	protected function render_field() {
		?>
		<input type="text" autocomplete="off"
			class="<?php echo esc_attr( $this->get_safe_name() ); ?>"
			id="<?php echo esc_attr( $this->id ); ?>"
			name="<?php echo esc_attr( $this->id ); ?>"
			data-prefix="<?php echo esc_attr( $this->prefix ); ?>"
			data-filter="<?php echo esc_attr( $this->filter->get_safe_name() ); ?>"
		/>
		<?php
	}

	/**
	 * {@inheritDoc}
	 */
	public function static_footer() {
		?>
		<script type="text/javascript">
		window.addEventListener('load', function() {
			window.WsalAs.Attach(function(){
				jQuery("input.<?php echo $this->get_safe_name(); // phpcs:ignore ?>").each(function(){
					var AsacCtrl = jQuery(this);
					if(!AsacCtrl.attr('data-asac-bound')){
						AsacCtrl.attr('data-asac-bound', '1');
						var filter = jQuery(this).attr('data-filter');
						var widget = <?php echo json_encode( $this->get_safe_name() ); // phpcs:ignore ?>;
						var source = new Bloodhound({
							datumTokenizer: function (datum) {
								return Bloodhound.tokenizers.whitespace(datum.value);
							},
							queryTokenizer: Bloodhound.tokenizers.whitespace,
							limit: 5,
							prefetch: WsalAs.AjaxUrl
								+ '?action=' + WsalAs.AjaxAction
								+ '&filter=' + filter
								+ '&widget=' + widget
								+ '&search=' + '%QUERY'
						});

						source.initialize();

						AsacCtrl.typeahead(null, {
							hint: true,
							highlight: true,
							displayKey: 'value',
							source: source.ttAdapter()
						})
						.on('typeahead:selected', function(ev, sg, dn){
							var $this = jQuery(this);
							if($this.val()){
								WsalAs.AddFilter($this.attr('data-prefix') + ':' + $this.val());
								$this.val('');
							}
						});
					}
				});
			});
		});
		</script>
		<?php
	}
}
