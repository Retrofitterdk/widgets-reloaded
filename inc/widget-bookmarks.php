<?php
/**
 * The Bookmarks widget replaces the default WordPress Links widget. This version gives total
 * control over the output to the user by allowing the input of all the arguments typically seen
 * in the wp_list_bookmarks() function.
 *
 * @package    Hybrid
 * @subpackage Includes
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2008 - 2015, Justin Tadlock
 * @link       http://themehybrid.com/hybrid-core
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

namespace Widgets_Reloaded;

/**
 * Bookmarks Widget Class
 *
 * @since  0.6.0
 * @access public
 */
class Widget_Bookmarks extends Widget {

	/**
	 * Default arguments for the widget settings.
	 *
	 * @since  2.0.0
	 * @access public
	 * @var    array
	 */
	public $defaults = array();

	/**
	 * Set up the widget's unique name, ID, class, description, and other options.
	 *
	 * @since  1.2.0
	 * @access public
	 * @return void
	 */
	function __construct() {

		// Set up the widget options.
		$widget_options = array(
			'classname'                   => 'widget-bookmarks widget_links',
			'description'                 => esc_html__( 'An advanced widget that gives you total control over the output of your bookmarks (links).', 'widgets-reloaded' ),
			'customize_selective_refresh' => true
		);

		// Set up the widget control options.
		$control_options = array(
			'width'  => 800,
			'height' => 350
		);

		// Create the widget.
		parent::__construct( 'hybrid-bookmarks', __( 'Bookmarks', 'widgets-reloaded' ), $widget_options, $control_options );

		// Set up the defaults.
		$this->defaults = array(
			'title_li'         => esc_attr__( 'Bookmarks', 'widgets-reloaded' ),
			'categorize'       => true,
			'category_order'   => 'ASC',
			'category_orderby' => 'name',
			'category'         => array(),
			'exclude_category' => array(),
			'limit'            => -1,
			'order'            => 'ASC',
			'orderby'          => 'name',
			'include'          => array(),
			'exclude'          => array(),
			'search'           => '',
			'hide_invisible'   => true,
			'show_description' => false,
			'show_images'      => false,
			'show_rating'      => false,
			'show_updated'     => false,
			'show_private'     => false,
			'show_name'        => false,
			'class'            => 'linkcat',
			'link_before'      => '',
			'link_after'       => '',
			'between'          => '&thinsp;&ndash;&thinsp;',
		);
	}

	/**
	 * Outputs the widget based on the arguments input through the widget controls.
	 *
	 * @since  0.6.0
	 * @access public
	 * @param  array  $sidebar
	 * @param  array  $instance
	 * @return void
	 */
	function widget( $sidebar, $instance ) {

		// Set up the $before_widget ID for multiple widgets created by the bookmarks widget.
		if ( !empty( $instance['categorize'] ) )
			$sidebar['before_widget'] = preg_replace( '/id="[^"]*"/','id="%id"', $sidebar['before_widget'] );

		// Add a class to $before_widget if one is set.
		if ( !empty( $instance['class'] ) )
			$sidebar['before_widget'] = str_replace( 'class="', 'class="' . esc_attr( $instance['class'] ) . ' ', $sidebar['before_widget'] );

		// Set the $args for wp_list_bookmarks() to the $instance array.
		$args = wp_parse_args( $instance, $this->defaults );

		// wp_list_bookmarks() hasn't been updated in WP to use wp_parse_id_list(), so we have to pass strings for includes/excludes.
		if ( !empty( $args['category'] ) && is_array( $args['category'] ) )
			$args['category'] = join( ', ', $args['category'] );

		if ( !empty( $args['exclude_category'] ) && is_array( $args['exclude_category'] ) )
			$args['exclude_category'] = join( ', ', $args['exclude_category'] );

		if ( !empty( $args['include'] ) && is_array( $args['include'] ) )
			$args['include'] = join( ',', $args['include'] );

		if ( !empty( $args['exclude'] ) && is_array( $args['exclude'] ) )
			$args['exclude'] = join( ',', $args['exclude'] );

		// If no limit is given, set it to -1.
		$args['limit'] = empty( $args['limit'] ) ? -1 : $args['limit'];

		// Some arguments must be set to the sidebar arguments to be output correctly.
		$args['title_li']        = apply_filters( 'widget_title', ( empty( $args['title_li'] ) ? __( 'Bookmarks', 'widgets-reloaded' ) : $args['title_li'] ), $instance, $this->id_base );
		$args['title_before']    = $sidebar['before_title'];
		$args['title_after']     = $sidebar['after_title'];
		$args['category_before'] = $sidebar['before_widget'];
		$args['category_after']  = $sidebar['after_widget'];
		$args['category_name']   = '';
		$args['echo']            = false;

		// Output the bookmarks widget.
		$bookmarks = str_replace( array( "\r", "\n", "\t" ), '', wp_list_bookmarks( $args ) );

		// If no title is given and the bookmarks aren't categorized, add a wrapper <ul>.
		if ( empty( $args['title_li'] ) && false === $args['categorize'] )
			$bookmarks = '<ul class="xoxo bookmarks">' . $bookmarks . '</ul>';

		// Output the bookmarks.
		echo $bookmarks;
	}

	/**
	 * The update callback for the widget control options.  This method is used to sanitize and/or
	 * validate the options before saving them into the database.
	 *
	 * @since  0.6.0
	 * @access public
	 * @param  array  $new_instance
	 * @param  array  $old_instance
	 * @return array
	 */
	function update( $new_instance, $old_instance ) {

		// Strip tags.
		$instance['title_li'] = strip_tags( $new_instance['title_li'] );
		$instance['search']   = strip_tags( $new_instance['search']   );

		// Arrays of post IDs (integers).
		$instance['category']         = array_map( 'absint', $new_instance['category']         );
		$instance['exclude_category'] = array_map( 'absint', $new_instance['exclude_category'] );
		$instance['include']          = array_map( 'absint', $new_instance['include']          );
		$instance['exclude']          = array_map( 'absint', $new_instance['exclude']          );

		// HTML class.
		$instance['class'] = sanitize_html_class( $new_instance['class'] );

		// Integers.
		$instance['limit'] = intval( $new_instance['limit'] );

		// Whitelist options.
		$category_order = $order = array( 'ASC', 'DESC' );
		$category_orderby        = array( 'count', 'ID', 'name', 'slug' );
		$orderby                 = array( 'id', 'description', 'length', 'name', 'notes', 'owner', 'rand', 'rating', 'rel', 'rss', 'target', 'updated', 'url' );

		$instance['category_order']   = in_array( $new_instance['category_order'],   $category_order )   ? $new_instance['category_order']   : 'ASC';
		$instance['category_orderby'] = in_array( $new_instance['category_orderby'], $category_orderby ) ? $new_instance['category_orderby'] : 'name';
		$instance['order']            = in_array( $new_instance['order'],            $order )            ? $new_instance['order']            : 'ASC';
		$instance['orderby']          = in_array( $new_instance['orderby'],          $orderby )          ? $new_instance['orderby']          : 'name';

		// Text boxes. Make sure user can use 'unfiltered_html'.
		$instance['link_before'] = current_user_can( 'unfiltered_html' ) ? $new_instance['link_before'] : wp_filter_post_kses( $new_instance['link_before'] );
		$instance['link_after']  = current_user_can( 'unfiltered_html' ) ? $new_instance['link_after']  : wp_filter_post_kses( $new_instance['link_after']  );
		$instance['between']     = current_user_can( 'unfiltered_html' ) ? $new_instance['between']     : wp_filter_post_kses( $new_instance['between']     );

		// Checkboxes.
		$instance['categorize']       = isset( $new_instance['categorize'] )       ? 1 : 0;
		$instance['hide_invisible']   = isset( $new_instance['hide_invisible'] )   ? 1 : 0;
		$instance['show_private']     = isset( $new_instance['show_private'] )     ? 1 : 0;
		$instance['show_rating']      = isset( $new_instance['show_rating'] )      ? 1 : 0;
		$instance['show_updated']     = isset( $new_instance['show_updated'] )     ? 1 : 0;
		$instance['show_images']      = isset( $new_instance['show_images'] )      ? 1 : 0;
		$instance['show_name']        = isset( $new_instance['show_name'] )        ? 1 : 0;
		$instance['show_description'] = isset( $new_instance['show_description'] ) ? 1 : 0;

		// Return sanitized options.
		return $instance;
	}

	/**
	 * Displays the widget control options in the Widgets admin screen.
	 *
	 * @since  0.6.0
	 * @access public
	 * @param  array  $instance
	 * @param  void
	 */
	function form( $instance ) {

		// Merge the user-selected arguments with the defaults.
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		$terms     = get_terms( 'link_category' );
		$bookmarks = get_bookmarks( array( 'hide_invisible' => false ) );

		$category_order = $order = array(
			'ASC'  => esc_attr__( 'Ascending',  'widgets-reloaded' ),
			'DESC' => esc_attr__( 'Descending', 'widgets-reloaded' )
		);

		$category_orderby = array(
			'count' => esc_attr__( 'Count', 'widgets-reloaded' ),
			'ID'    => esc_attr__( 'ID',    'widgets-reloaded' ),
			'name'  => esc_attr__( 'Name',  'widgets-reloaded' ),
			'slug'  => esc_attr__( 'Slug',  'widgets-reloaded' )
		);

		$orderby = array(
			'id'          => esc_attr__( 'ID',          'widgets-reloaded' ),
			'description' => esc_attr__( 'Description', 'widgets-reloaded' ),
			'length'      => esc_attr__( 'Length',      'widgets-reloaded' ),
			'name'        => esc_attr__( 'Name',        'widgets-reloaded' ),
			'notes'       => esc_attr__( 'Notes',       'widgets-reloaded' ),
			'owner'       => esc_attr__( 'Owner',       'widgets-reloaded' ),
			'rand'        => esc_attr__( 'Random',      'widgets-reloaded' ),
			'rating'      => esc_attr__( 'Rating',      'widgets-reloaded' ),
			'rel'         => esc_attr__( 'Rel',         'widgets-reloaded' ),
			'rss'         => esc_attr__( 'RSS',         'widgets-reloaded' ),
			'target'      => esc_attr__( 'Target',      'widgets-reloaded' ),
			'updated'     => esc_attr__( 'Updated',     'widgets-reloaded' ),
			'url'         => esc_attr__( 'URL',         'widgets-reloaded' )
		);
		?>

		<div class="hybrid-widget-controls columns-3">
		<p>
			<label for="<?php $this->field_id( 'title_li' ); ?>"><?php _e( 'Title:', 'widgets-reloaded' ); ?></label>
			<input type="text" class="widefat" id="<?php $this->field_id( 'title_li' ); ?>" name="<?php $this->field_name( 'title_li' ); ?>" value="<?php echo esc_attr( $instance['title_li'] ); ?>" placeholder="<?php echo esc_attr( $this->defaults['title_li'] ); ?>" />
		</p>
		<p>
			<label for="<?php $this->field_id( 'category_order' ); ?>"><code>category_order</code></label>
			<select class="widefat" id="<?php $this->field_id( 'category_order' ); ?>" name="<?php $this->field_name( 'category_order' ); ?>">
				<?php foreach ( $category_order as $option_value => $option_label ) { ?>
					<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $instance['category_order'], $option_value ); ?>><?php echo esc_html( $option_label ); ?></option>
				<?php } ?>
			</select>
		</p>
		<p>
			<label for="<?php $this->field_id( 'category_orderby' ); ?>"><code>category_orderby</code></label>
			<select class="widefat" id="<?php $this->field_id( 'category_orderby' ); ?>" name="<?php $this->field_name( 'category_orderby' ); ?>">
				<?php foreach ( $category_orderby as $option_value => $option_label ) { ?>
					<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $instance['category_orderby'], $option_value ); ?>><?php echo esc_html( $option_label ); ?></option>
				<?php } ?>
			</select>
		</p>
		<p>
			<label for="<?php $this->field_id( 'category' ); ?>"><code>category</code></label>
			<select class="widefat" id="<?php $this->field_id( 'category' ); ?>" name="<?php $this->field_name( 'category' ); ?>[]" size="4" multiple="multiple">
				<?php foreach ( $terms as $term ) { ?>
					<option value="<?php echo esc_attr( $term->term_id ); ?>" <?php echo ( in_array( $term->term_id, (array) $instance['category'] ) ? 'selected="selected"' : '' ); ?>><?php echo esc_html( $term->name ); ?></option>
				<?php } ?>
			</select>
		</p>
		<p>
			<label for="<?php $this->field_id( 'exclude_category' ); ?>"><code>exclude_category</code></label>
			<select class="widefat" id="<?php $this->field_id( 'exclude_category' ); ?>" name="<?php $this->field_name( 'exclude_category' ); ?>[]" size="4" multiple="multiple">
				<?php foreach ( $terms as $term ) { ?>
					<option value="<?php echo esc_attr( $term->term_id ); ?>" <?php echo ( in_array( $term->term_id, (array) $instance['exclude_category'] ) ? 'selected="selected"' : '' ); ?>><?php echo esc_html( $term->name ); ?></option>
				<?php } ?>
			</select>
		</p>
		<p>
			<label for="<?php $this->field_id( 'class' ); ?>"><code>class</code></label>
			<input type="text" class="smallfat code" id="<?php $this->field_id( 'class' ); ?>" name="<?php $this->field_name( 'class' ); ?>" value="<?php echo esc_attr( $instance['class'] ); ?>" placeholder="linkcat" />
		</p>

		</div>

		<div class="hybrid-widget-controls columns-3">

		<p>
			<label for="<?php $this->field_id( 'limit' ); ?>"><code>limit</code></label>
			<input type="number" class="smallfat code" size="5" min="-1" id="<?php $this->field_id( 'limit' ); ?>" name="<?php $this->field_name( 'limit' ); ?>" value="<?php echo esc_attr( $instance['limit'] ); ?>" placeholder="-1" />
		</p>
		<p>
			<label for="<?php $this->field_id( 'order' ); ?>"><code>order</code></label>
			<select class="widefat" id="<?php $this->field_id( 'order' ); ?>" name="<?php $this->field_name( 'order' ); ?>">
				<?php foreach ( $order as $option_value => $option_label ) { ?>
					<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $instance['order'], $option_value ); ?>><?php echo esc_html( $option_label ); ?></option>
				<?php } ?>
			</select>
		</p>
		<p>
			<label for="<?php $this->field_id( 'orderby' ); ?>"><code>orderby</code></label>
			<select class="widefat" id="<?php $this->field_id( 'orderby' ); ?>" name="<?php $this->field_name( 'orderby' ); ?>">
				<?php foreach ( $orderby as $option_value => $option_label ) { ?>
					<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $instance['orderby'], $option_value ); ?>><?php echo esc_html( $option_label ); ?></option>
				<?php } ?>
			</select>
		</p>
		<p>
			<label for="<?php $this->field_id( 'include' ); ?>"><code>include</code></label>
			<select class="widefat" id="<?php $this->field_id( 'include' ); ?>" name="<?php $this->field_name( 'include' ); ?>[]" size="4" multiple="multiple">
				<?php foreach ( $bookmarks as $bookmark ) { ?>
					<option value="<?php echo esc_attr( $bookmark->link_id ); ?>" <?php echo ( in_array( $bookmark->link_id, (array) $instance['include'] ) ? 'selected="selected"' : '' ); ?>><?php echo esc_html( $bookmark->link_name ); ?></option>
				<?php } ?>
			</select>
		</p>
		<p>
			<label for="<?php $this->field_id( 'exclude' ); ?>"><code>exclude</code></label>
			<select class="widefat" id="<?php $this->field_id( 'exclude' ); ?>" name="<?php $this->field_name( 'exclude' ); ?>[]" size="4" multiple="multiple">
				<?php foreach ( $bookmarks as $bookmark ) { ?>
					<option value="<?php echo esc_attr( $bookmark->link_id ); ?>" <?php echo ( in_array( $bookmark->link_id, (array) $instance['exclude'] ) ? 'selected="selected"' : '' ); ?>><?php echo esc_html( $bookmark->link_name ); ?></option>
				<?php } ?>
			</select>
		</p>
		<p>
			<label for="<?php $this->field_id( 'search' ); ?>"><code>search</code></label>
			<input type="text" class="widefat code" id="<?php $this->field_id( 'search' ); ?>" name="<?php $this->field_name( 'search' ); ?>" value="<?php echo esc_attr( $instance['search'] ); ?>" />
		</p>

		</div>

		<div class="hybrid-widget-controls columns-3 column-last">
		<p>
			<label for="<?php $this->field_id( 'between' ); ?>"><code>between</code></label>
			<input type="text" class="smallfat code" id="<?php $this->field_id( 'between' ); ?>" name="<?php $this->field_name( 'between' ); ?>" value="<?php echo esc_attr( $instance['between'] ); ?>" placeholder="&thinsp;&ndash;&thinsp;" />
		</p>
		<p>
			<label for="<?php $this->field_id( 'link_before' ); ?>"><code>link_before</code></label>
			<input type="text" class="smallfat code" id="<?php $this->field_id( 'link_before' ); ?>" name="<?php $this->field_name( 'link_before' ); ?>" value="<?php echo esc_attr( $instance['link_before'] ); ?>" />
		</p>
		<p>
			<label for="<?php $this->field_id( 'link_after' ); ?>"><code>link_after</code></label>
			<input type="text" class="smallfat code" id="<?php $this->field_id( 'link_after' ); ?>" name="<?php $this->field_name( 'link_after' ); ?>" value="<?php echo esc_attr( $instance['link_after'] ); ?>" />
		</p>
		<p>
			<label for="<?php $this->field_id( 'categorize' ); ?>">
			<input class="checkbox" type="checkbox" <?php checked( $instance['categorize'], true ); ?> id="<?php $this->field_id( 'categorize' ); ?>" name="<?php $this->field_name( 'categorize' ); ?>" /> <?php _e( 'Categorize?', 'widgets-reloaded' ); ?> <code>categorize</code></label>
		</p>
		<p>
			<label for="<?php $this->field_id( 'show_description' ); ?>">
			<input class="checkbox" type="checkbox" <?php checked( $instance['show_description'], true ); ?> id="<?php $this->field_id( 'show_description' ); ?>" name="<?php $this->field_name( 'show_description' ); ?>" /> <?php _e( 'Show description?', 'widgets-reloaded' ); ?> <code>show_description</code></label>
		</p>
		<p>
			<label for="<?php $this->field_id( 'hide_invisible' ); ?>">
			<input class="checkbox" type="checkbox" <?php checked( $instance['hide_invisible'], true ); ?> id="<?php $this->field_id( 'hide_invisible' ); ?>" name="<?php $this->field_name( 'hide_invisible' ); ?>" /> <?php _e( 'Hide invisible?', 'widgets-reloaded' ); ?> <code>hide_invisible</code></label>
		</p>
		<p>
			<label for="<?php $this->field_id( 'show_rating' ); ?>">
			<input class="checkbox" type="checkbox" <?php checked( $instance['show_rating'], true ); ?> id="<?php $this->field_id( 'show_rating' ); ?>" name="<?php $this->field_name( 'show_rating' ); ?>" /> <?php _e( 'Show rating?', 'widgets-reloaded' ); ?> <code>show_rating</code></label>
		</p>
		<p>
			<label for="<?php $this->field_id( 'show_updated' ); ?>">
			<input class="checkbox" type="checkbox" <?php checked( $instance['show_updated'], true ); ?> id="<?php $this->field_id( 'show_updated' ); ?>" name="<?php $this->field_name( 'show_updated' ); ?>" /> <?php _e( 'Show updated?', 'widgets-reloaded' ); ?> <code>show_updated</code></label>
		</p>
		<p>
			<label for="<?php $this->field_id( 'show_images' ); ?>">
			<input class="checkbox" type="checkbox" <?php checked( $instance['show_images'], true ); ?> id="<?php $this->field_id( 'show_images' ); ?>" name="<?php $this->field_name( 'show_images' ); ?>" /> <?php _e( 'Show images?', 'widgets-reloaded' ); ?> <code>show_images</code></label>
		</p>
		<p>
			<label for="<?php $this->field_id( 'show_name' ); ?>">
			<input class="checkbox" type="checkbox" <?php checked( $instance['show_name'], true ); ?> id="<?php $this->field_id( 'show_name' ); ?>" name="<?php $this->field_name( 'show_name' ); ?>" /> <?php _e( 'Show name?', 'widgets-reloaded' ); ?> <code>show_name</code></label>
		</p>
		<p>
			<label for="<?php $this->field_id( 'show_private' ); ?>">
			<input class="checkbox" type="checkbox" <?php checked( $instance['show_private'], true ); ?> id="<?php $this->field_id( 'show_private' ); ?>" name="<?php $this->field_name( 'show_private' ); ?>" /> <?php _e( 'Show private?', 'widgets-reloaded' ); ?> <code>show_private</code></label>
		</p>

		</div>
		<div style="clear:both;">&nbsp;</div>
	<?php
	}
}
