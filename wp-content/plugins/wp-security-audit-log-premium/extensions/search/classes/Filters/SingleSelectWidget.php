<?php
/**
 * Class: Single Select Widget
 *
 * Single Select Widget for search extension.
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
 * Class WSAL_AS_Filters_SingleSelectWidget
 *
 * @package wsal
 * @subpackage search
 */
class WSAL_AS_Filters_SingleSelectWidget extends WSAL_AS_Filters_AbstractWidget {

	/**
	 * Items.
	 *
	 * @var array
	 */
	protected $items = array();

	/**
	 * {@inheritDoc}
	 */
	protected function render_field() {
		?>
		<select class="<?php echo esc_attr( $this->get_safe_name() ); ?>"
			id="<?php echo esc_attr( $this->id ); ?>"
			data-prefix="<?php echo esc_attr( $this->prefix ); ?>">
			<option value=""></option>
			<?php
			foreach ( $this->items as $value => $text ) {
				if ( is_object( $text ) ) {
					// Render group (and items).
					echo '<optgroup label="' . esc_attr( $value ) . '">';
					foreach ( $text->items as $s_value => $s_text ) {
						echo '<option value="' . esc_attr( $s_value ) . '">' . esc_html( $s_text ) . '</option>';
					}
					echo '</optgroup>';
				} else {
					// Render item.
					echo '<option value="' . esc_attr( $value ) . '">' . esc_html( $text ) . '</option>';
				}
			}
			?>
		</select>
		<button id="<?php echo esc_attr( $this->get_safe_name() ); ?>_add_button" class="button wsal-button wsal-filter-add-button"><?php esc_html_e( 'Add this filter', 'wp-security-audit-log' ); ?></button>
		<?php
	}

	/**
	 * Adds item to the list.
	 *
	 * @param string $text Text.
	 * @param string $value Value.
	 */
	public function add( $text, $value ) {
		$this->items[ $value ] = $text;
	}

	/**
	 * Adds group.
	 *
	 * @param string $name Group name.
	 *
	 * @return WSAL_AS_Filters_SingleSelectWidgetGroup
	 */
	public function add_group( $name ) {
		$this->items[ $name ] = new WSAL_AS_Filters_SingleSelectWidgetGroup();
		return $this->items[ $name ];
	}

	/**
	 * {@inheritDoc}
	 */
	public function static_footer() {
		?>
		<script>
			jQuery( document ).ready( function() {
				window.WsalAs.Attach(function(){
					jQuery('button#<?php echo esc_attr( $this->get_safe_name() ); ?>_add_button').click(
						function(e){
							e.preventDefault();
							let value = jQuery('select.<?php echo esc_attr( $this->get_safe_name() ); ?> :selected').val();

							if (value) {
								WsalAs.AddFilter(jQuery('select.<?php echo esc_attr( $this->get_safe_name() ); ?>').attr('data-prefix') + ':' + value);
								jQuery('select.<?php echo esc_attr( $this->get_safe_name() ); ?>').prop("selectedIndex", 0);
							}
						}
					);

					// jQuery('select.<?php echo esc_attr( $this->get_safe_name() ); ?>').change(function(){
					// 	if(this.value){
					// 		WsalAs.AddFilter(jQuery(this).attr('data-prefix') + ':' + this.value);
					// 		this.value = '';
					// 	}
					// });
				});
			});
		</script>
		<?php
	}
}

/**
 * Class WSAL_AS_Filters_SingleSelectWidgetGroup
 *
 * phpcs:disable Generic.Files.OneObjectStructurePerFile.MultipleFound
 */
class WSAL_AS_Filters_SingleSelectWidgetGroup {

	/**
	 * Items.
	 *
	 * @var array
	 */
	public $items = array();

	/**
	 * Adds item to the list.
	 *
	 * @param string $text Text.
	 * @param string $value Value.
	 */
	public function add( $text, $value ) {
		$this->items[ $value ] = $text;
	}
}
