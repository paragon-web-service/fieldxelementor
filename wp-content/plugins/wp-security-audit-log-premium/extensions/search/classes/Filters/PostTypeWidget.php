<?php
/**
 * Post Type Widget
 *
 * Post Type widget class file.
 *
 * @package wsal
 * @subpackage search
 * @since 3.2.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WSAL_AS_Filters_PostTypeWidget' ) ) {

	/**
	 * WSAL_AS_Filters_PostTypeWidget.
	 *
	 * Class: Post Type Widget.
	 */
	class WSAL_AS_Filters_PostTypeWidget extends WSAL_AS_Filters_SingleSelectWidget {

		/**
		 * Render widget field.
		 */
		protected function render_field() {
			?>
			<div class="wsal-widget-container">
				<select class="<?php echo esc_attr( $this->get_safe_name() ); ?>"
					id="<?php echo esc_attr( $this->id ); ?>"
					data-prefix="<?php echo esc_attr( $this->prefix ); ?>"
					>
					<option value="" disabled selected hidden><?php esc_html_e( 'Select a post type to filter', 'wp-security-audit-log' ); ?></option>
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
			</div>
			<?php
		}
	}
}
