<?php

class Stm_Socials_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'socials', // Base ID
			__('Socials', 'stm-post-type'), // Name
			array( 'description' => __( 'Socials widget(customize order from theme options -> Social Media section -> Social Widget subsection )', 'stm-post-type' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo stm_post_type_filtered_output($args['before_widget']);
		if ( ! empty( $title ) ) {
			echo stm_post_type_filtered_output($args['before_title']) . esc_html( $title ) . $args['after_title'];
		}
		echo '<div class="socials_widget_wrapper">';
	        echo '<ul class="widget_socials list-unstyled clearfix">';
	        	global $stm_option;
	        	foreach ( $stm_option['stm_social_widget_sort'] as $key => $val ) {
					if ( ! empty( $stm_option[$key] ) && $val != '' ) {
						echo "<li class='simple_flip_container'>
							<div class='simple_flipper'>
								<div class='front'>
									<a href='{$stm_option[$key]}' target='_blank' class='" . sanitize_title($key) ."'><i class='fab fa-{$key}'></i></a>
								</div>
								<div class='back'>
									<a href='{$stm_option[$key]}' target='_blank'><i class='fab fa-{$key}'></i></a>
								</div>
							</div>
						</li>";
					}
				}
				?>

	        <?php echo '</ul>';
		echo '</div>';


		echo stm_post_type_filtered_output($args['after_widget']);
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		$title = '';

		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}else {
			$title = __( 'Social Network', 'stm-post-type' );
		}

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'stm-post-type' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
	<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? esc_attr( $new_instance['title'] ) : '';

		return $instance;
	}

}

function register_socials_widget() {
	register_widget( 'Stm_Socials_Widget' );
}
add_action( 'widgets_init', 'register_socials_widget' );