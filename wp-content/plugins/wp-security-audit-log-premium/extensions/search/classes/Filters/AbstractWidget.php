<?php
/**
 * Class: Abstract Widget
 *
 * Abstract widget class.
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
 * Class WSAL_AS_Filters_AbstractWidget
 *
 * @package wsal
 * @subpackage search
 */
abstract class WSAL_AS_Filters_AbstractWidget {

	/**
	 * Widget ID.
	 *
	 * @var string
	 */
	public $id;

	/**
	 * The filter for this class.
	 *
	 * @var WSAL_AS_Filters_AbstractFilter
	 */
	public $filter;

	/**
	 * Widget title/label.
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Value prefix for the filter.
	 *
	 * @var string
	 */
	public $prefix;

	/**
	 * Data loader callback.
	 *
	 * @var callable|null
	 */
	protected $data_loader_func = null;

	/**
	 * Data loader data.
	 *
	 * @var array
	 */
	protected $data_loader_data = null;

	/**
	 * True if data is loaded.
	 *
	 * @var bool
	 */
	protected $data_loaded = false;

	/**
	 * Counter.
	 *
	 * @var int
	 */
	protected static $counter = 0;

	/**
	 * Constructor.
	 *
	 * @param WSAL_AS_Filters_AbstractFilter $filter Filter.
	 * @param string                         $prefix Prefix.
	 * @param string                         $title  Title.
	 */
	public function __construct( $filter, $prefix, $title = '' ) {
		$this->filter = $filter;
		$this->prefix = $prefix;
		$this->id     = 'wsal_as_widget_' . $this->prefix;
		$this->title  = $title;
	}

	/**
	 * Set data loading callback.
	 *
	 * @param callable $ldr A callback that will receive this widget as first parameter and is supposed to populate this widget.
	 * @param mixed    $usr Some data to be passed to callback as 2nd parameter.
	 */
	public function set_data_loader( $ldr, $usr = null ) {
		$this->data_loader_func = $ldr;
		$this->data_loader_data = $usr;
	}

	/**
	 * Called when widget needs to be populated.
	 *
	 * @param bool $force_load Force (re)loading data.
	 */
	public function load_data( $force_load = false ) {
		if ( ( ! $this->data_loaded || $force_load ) && $this->data_loader_func ) { // Avoid loading data multiple times.
			call_user_func( $this->data_loader_func, $this, $this->data_loader_data );
			$this->data_loaded = true;
		}
	}

	/**
	 * Handle ajax calls here.
	 */
	public function handle_ajax(){ }

	/**
	 * Renders widget HTML directly.
	 */
	public function render() {
		$this->load_data();
		$this->render_label();
		$this->render_field();
	}

	/**
	 * Renders widget label (left).
	 */
	protected function render_label() {
		?>
		<label for="<?php echo esc_attr( $this->id ); ?>">
			<?php echo esc_html( $this->title ); ?>
		</label>
		<?php
	}

	/**
	 * Renders widget field (right).
	 */
	protected function render_field() {
		?>
		<input type="text" id="<?php echo esc_attr( $this->id ); ?>"
				data-prefix="<?php echo esc_attr( $this->prefix ); ?>"/>
		<?php
	}

	/**
	 * Called only once per class.
	 */
	public function static_header(){ }

	/**
	 * Called only once per class.
	 */
	public function static_footer(){ }

	/**
	 * Called only once per instance.
	 */
	public function dynamic_header(){ }

	/**
	 * Called only once per instance.
	 */
	public function dynamic_footer(){ }

	/**
	 * Generates a widget name.
	 *
	 * @return string
	 */
	public function get_safe_name() {
		return strtolower( get_class( $this ) );
	}
}
