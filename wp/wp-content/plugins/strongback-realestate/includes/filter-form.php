<?php
if ( ! function_exists( 'sb_render_filter_form' ) ) :

/**
 * Renders the real-estate filter form and results container.
 */
function sb_render_filter_form() {
    wp_enqueue_script(
        'sb-filter-js',
        plugin_dir_url( __FILE__ ) . '../assets/filter.js',
        [ 'jquery' ],
        null,
        true
    );
    wp_localize_script(
        'sb-filter-js',
        'sb_vars',
        [ 'ajax_url' => admin_url( 'admin-ajax.php' ) ]
    );
    wp_enqueue_style(
        'sb-filter-css',
        plugin_dir_url( __FILE__ ) . '../assets/filter.css'
    );

    // 2) Fetch all District terms
    $districts = get_terms( [
        'taxonomy'   => 'district',
        'hide_empty' => false,
    ] );
    ?>

    <form id="sb-filter-form">
      <div>
        <label for="sb-house-name">House Name:</label>
        <input id="sb-house-name" type="text" name="house_name">
      </div>

      <div>
        <label for="sb-location">Location (coords):</label>
        <input id="sb-location" type="text" name="location_coords">
      </div>

      <div>
        <label for="sb-floors">Floors:</label>
        <select id="sb-floors" name="floors_count">
          <option value="">— any —</option>
          <?php for ( $i = 1; $i <= 20; $i++ ) : ?>
            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
          <?php endfor; ?>
        </select>
      </div>

      <fieldset>
        <legend>Building Type:</legend>
        <?php
        $types = [ 'panel' => 'Panel', 'brick' => 'Brick', 'pinoblock' => 'Foam block' ];
        foreach ( $types as $val => $label ) : ?>
          <label>
            <input type="radio" name="building_type" value="<?php echo esc_attr( $val ); ?>">
            <?php echo esc_html( $label ); ?>
          </label>
        <?php endforeach; ?>
      </fieldset>

      <div>
        <label for="sb-eco">Ecological Rating:</label>
        <select id="sb-eco" name="ecological_rating">
          <option value="">— any —</option>
          <?php for ( $e = 1; $e <= 5; $e++ ) : ?>
            <option value="<?php echo $e; ?>"><?php echo $e; ?></option>
          <?php endfor; ?>
        </select>
      </div>

      <div>
        <label for="sb-district">District:</label>
        <select id="sb-district" name="district">
          <option value="">— any —</option>
          <?php foreach ( $districts as $d ) : ?>
            <option value="<?php echo esc_attr( $d->slug ); ?>">
              <?php echo esc_html( $d->name ); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <button type="submit">Filter</button>
    </form>

    <div id="sb-filter-results"></div>

<?php
}
endif;
