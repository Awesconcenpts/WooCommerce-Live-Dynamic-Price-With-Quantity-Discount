<?php
	class WP_Price_List_Table extends WP_Widget {

	public function __construct() {
		$widget_ops = array( 'classname' => '', 'description' => __( 'Your site’s most  custom Posts.', 'post-widgets' ) );
		parent::__construct( 'wp-price-list-table', __( 'GLP Price Table', 'post-widgets' ), $widget_ops );
		$this->alt_option_name = 'wp_price_list_table';

		add_action( 'save_post', array( &$this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( &$this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( &$this, 'flush_widget_cache' ) );
	}

	public function widget( $args, $instance ) {

		$cache = array();
		$title = $instance['title'];
		$metal=$instance['metal'];
		$currency=$instance['currency'];
		$symbols=$instance['symbols'];
		$units=$instance['units'];
		if ( ! $this->is_preview() ) {
			$cache = wp_cache_get( 'wp_price_list_table', 'widget' );
		}

		if ( ! is_array( $cache ) ) {
			$cache = array();
		}

		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		if ( isset( $cache[ $args['widget_id'] ] ) ) {
			echo $cache[ $args['widget_id'] ];
			return;
		}

		ob_start();

	

		
			 ?>
				<?php
				$dir = plugin_dir_path( __FILE__ );
				$include_file=$class;
                ?>
				<?php do_shortcode("[glp_top currencys='".$currency."' units='".$units."' metals='".$metal."' symbols='".$symbols."']"); ?>
				<?php
				wp_reset_postdata();
				
			
		if ( ! $this->is_preview() ) {
			$cache[ $args['widget_id'] ] = ob_get_flush();
			wp_cache_set( 'wp_price_list_table', $cache, 'widget' );
		}
		else {
			ob_end_flush();
		}
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] =  $new_instance['title'];
		$instance['metal'] =  $new_instance['metal'];
		$instance['currency'] =  $new_instance['currency'];
		$instance['symbols'] =  $new_instance['symbols'];
		$instance['units'] =  $new_instance['units'];
        /* end link start */
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset( $alloptions['wp_price_list_table'] ) ) {
			delete_option( 'wp_price_list_table' );
		}

		return $instance;
	}

	public function flush_widget_cache() {
		wp_cache_delete( 'wp_price_list_table', 'widget' );
	}

	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$metal = isset( $instance['metal'] ) ? esc_attr( $instance['metal'] ) : '';
		$currency = isset( $instance['currency'] ) ? esc_attr( $instance['currency'] ) : '';
		$symbols = isset( $instance['symbols'] ) ? esc_attr( $instance['symbols'] ) : '';
		$units = isset( $instance['units'] ) ? esc_attr( $instance['units'] ) : '';
        /* end Link */
?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'post-widgets' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'metal' ); ?>">
				<?php _e( 'Metal:', 'post-widgets' ); ?>
			</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'metal' ); ?>" name="<?php echo $this->get_field_name( 'metal' ); ?>" value="<?php echo $metal; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'currency' ); ?>">
				<?php _e( 'Currency:', 'post-widgets' ); ?>
			</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'currency' ); ?>" name="<?php echo $this->get_field_name( 'currency' ); ?>" value="<?php echo $currency; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'symbols' ); ?>">
				<?php _e( 'Symbols:', 'post-widgets' ); ?>
			</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'symbols' ); ?>" name="<?php echo $this->get_field_name( 'symbols' ); ?>" value="<?php echo $symbols; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'units' ); ?>">
				<?php _e( 'Units:', 'post-widgets' ); ?>
			</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'units' ); ?>" name="<?php echo $this->get_field_name( 'units' ); ?>" value="<?php echo $units; ?>" />
		</p>

		

<?php
	}
}