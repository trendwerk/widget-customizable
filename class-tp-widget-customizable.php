<?php
/**
 * Plugin Name: Customizable widget
 * Description: Customizable widget.
 *
 * Plugin URI: https://github.com/trendwerk/widget-customizable
 * 
 * Author: Trendwerk
 * Author URI: https://github.com/trendwerk
 * 
 * Version: 1.0.1
 */

class TP_Widget_Customizable_Plugin {

	function __construct() {
		add_action( 'plugins_loaded', array( $this, 'localization' ) );
		add_action( 'init', array( $this, 'image_size' ) );
	}

	/**
	 * Load localization
	 */
	function localization() {
		load_muplugin_textdomain( 'widget-customizable', dirname( plugin_basename( __FILE__ ) ) . '/assets/lang/' );
	}

	/**
	 * Image size
	 */
	function image_size() {
		$args = apply_filters( 'widget-customizable-image-size', array(
			'width'  => 300,
			'height' => 500,
			'crop'   => false,
		) );

		add_image_size( 'customizable-widget', $args['width'], $args['height'], $args['crop'] );
	}

} new TP_Widget_Customizable_Plugin;

class TP_Widget_Customizable extends WP_Widget {

	function __construct() {
		parent::__construct( 'TP_Widget_Customizable', __( 'Customizable widget', 'widget-customizable' ), array(
			'description' => __( 'Editable title, image, content, more link, telephone number.', 'widget-customizable' ),
		) );

		$this->types = array(
			'more-link'        => __( 'Read more link', 'widget-customizable' ),
			'button'           => __( 'Primary button', 'widget-customizable' ),
			'button secondary' => __( 'Secondary button', 'widget-customizable' ),
		);

		add_action( 'admin_enqueue_scripts', array( $this, 'localize' ), 11 );
	}

	function localize() {
		global $current_screen;
		
		if( 'widgets' == $current_screen->base )
			wp_enqueue_media();

		wp_enqueue_script( 'widget-customizable', plugins_url( 'assets/coffee/admin.js', __FILE__ ), array( 'jquery' ) );

		wp_localize_script( 'widget-customizable', 'TP_Widget_Customizable', array(
			'media_frame_labels' => array(
				'title'          => __( 'Choose an image', 'widget-customizable' ),
				'button'         => __( 'Insert into widget', 'widget-customizable' ),
			),
		) );

		wp_enqueue_style( 'widget-customizable', plugins_url( 'assets/sass/admin.css', __FILE__ ) );
	}

	function form( $instance ) {
		$defaults = array(
			'title'       => '',
			'image'       => 0,
			'content'     => '',
			'show_button' => false,
			'button_text' => '',
			'button_link' => '',
			'link_type'   => 'more-link',
			'external'    => false,
		);

		$instance = wp_parse_args( $instance, $defaults );
		?>

		<p>
			<label>
				<strong><?php _e( 'Title' ); ?></strong><br />
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $instance['title']; ?>" />
			</label>
		</p>

		<p>
			<label>
				<strong><?php _e( 'Image' ); ?></strong><br />

				<?php 
					if( ! $instance['image'] ) {
						echo '<a class="tp-upload-image button-secondary">';
							_e( 'Upload' );
						echo '</a>';
					} else {
						echo '<div class="tp-edit-image">';
							echo wp_get_attachment_image( $instance['image'], array( 400, 600 ) );
						echo '</div>';

						echo '<a class="tp-edit-image button-secondary">';
							_e( 'Edit' );
						echo '</a> ';

						echo '<a class="tp-remove-image button-secondary">';
							_e( 'Remove' );
						echo '</a>';
					} 
				?>
				<input class="tp-upload-image-content" type="hidden" name="<?php echo $this->get_field_name('image'); ?>" value="<?php echo $instance['image']; ?>" />
			</label>
		</p>

		<p>
			<label>
				<strong><?php _e( 'Content' ); ?></strong><br />
				<textarea class="widefat" name="<?php echo $this->get_field_name('content'); ?>" ><?php echo $instance['content']; ?></textarea>
			</label>
		</p>

		<p>
			<label>
				<input class="tp-show-button" type="checkbox" name="<?php echo $this->get_field_name('show_button'); ?>" <?php checked( $instance['show_button'], true ); ?>>
				<?php _e('Show button / read more link','widget-customizable'); ?>
			</label>
		</p>

		<div class="tp-show-button-content <?php if( ! $instance['show_button'] ) echo 'tp-hide'; ?>">

			<p>
				<label>
					<strong><?php _e( 'Button text', 'widget-customizable' ); ?></strong><br />
					<input class="widefat" name="<?php echo $this->get_field_name( 'button_text' ); ?>" type="text" value="<?php echo $instance['button_text']; ?>" />
				</label>
			</p>

			<p>
				<label>
					<strong><?php _e( 'Button link', 'widget-customizable' ); ?></strong><br />
					<input class="widefat" name="<?php echo $this->get_field_name( 'button_link' ); ?>" type="text" value="<?php echo $instance['button_link']; ?>" />
				</label>
			</p>


			<p>
				<label>
					<strong><?php _e( 'Link type', 'widget-customizable' ); ?></strong><br />
					<select class="widefat" name="<?php echo $this->get_field_name( 'link_type' ); ?>" >
						<?php 
							foreach( $this->types as $type => $label )
								echo '<option value="' . $type . '" ' . selected( $type, $instance['link_type'] ) . '>' . $label . '</option>';
						?>
					</select>
				</label>
			</p>

			<p>
				<label>
					<input type="checkbox" name="<?php echo $this->get_field_name( 'external' ); ?>" value="true" <?php checked( $instance['external'], true ); ?>>
					<?php _e( 'Open link in new window / tab', 'widget-customizable' ); ?>
				</label>
			</p>

		</div>

		<?php
	}
	
	function update( $new_instance, $old_instance ) {
		$new_instance['show_button'] = isset( $new_instance['show_button'] );
		$new_instance['external'] = isset( $new_instance['external'] );
		
		return $new_instance;
	}
	
	function widget( $args, $instance ) {
		extract($args);

		echo $before_widget;
			if( $instance['title'] )
				echo $before_title . $instance['title'] . $after_title; 

			if( $instance['image'] )
				echo wp_get_attachment_image( $instance['image'], 'customizable-widget' );

			if( $instance['content'] )
				echo '<p>' . nl2br( $instance['content'] ) . '</p>';
		
		    if( $instance['show_button'] ) { 
		    	?>

		    	<p>
		    		<a class="<?php echo $instance['link_type']; ?>" href="<?php echo $instance['button_link']; ?>" <?php if( $instance['external'] ) echo 'target="_blank"'; ?>>
		    			<?php echo $instance['button_text']; ?>
		    		</a>
		    	</p>

	    		<?php 
	    	} 
		echo $after_widget;
	}
}

add_action( 'widgets_init', function() {
	return register_widget( 'TP_Widget_Customizable' );
} );
