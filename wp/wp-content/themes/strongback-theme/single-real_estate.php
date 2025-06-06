<?php get_header(); ?>
<article class="real-estate-item">
  <h1><?php the_title(); ?></h1>

  <?php if ( has_post_thumbnail() ) : ?>
    <div class="featured">
      <?php the_post_thumbnail('large'); ?>
    </div>
  <?php endif; ?>

  <ul class="attributes">
    <li><strong>House Name:</strong> <?php echo esc_html( get_field('house_name') ); ?></li>
    <li><strong>Coordinates:</strong> <?php echo esc_html( get_field('location_coords') ); ?></li>
    <li><strong>Floors:</strong> <?php echo esc_html( get_field('floors_count') ); ?></li>
    <li><strong>Type:</strong> <?php echo esc_html( get_field('building_type') ); ?></li>
    <li><strong>Ecological Rating:</strong> <?php echo esc_html( get_field('ecological_rating') ); ?></li>
  </ul>

  <?php
  $image = get_field('images');
  $img = is_array($image) ? reset($image) : $image;

  if ( is_array($img) && ! empty($img['sizes']['medium']) ) {
      $url = $img['sizes']['medium'];
      $alt = $img['alt'] ?? '';
  } elseif ( intval($img) ) {
      $url = wp_get_attachment_image_url( $img, 'medium' );
      $alt = get_post_meta( $img, '_wp_attachment_image_alt', true );
  } else {
      return;
  }
  ?>

  <div class="gallery">
    <img src="<?php echo esc_url( $url ); ?>" alt="<?php echo esc_attr( $alt ); ?>" />
  </div>

  <?php if ( have_rows('rooms') ) : ?>
    <h2>Rooms</h2>
    <ul class="rooms">
    <?php while ( have_rows('rooms') ) : the_row(); ?>
      <li>
        <strong>Area:</strong> <?php the_sub_field('room_area'); ?> m²,<br>
        <strong>Count:</strong> <?php the_sub_field('room_count'); ?>,<br>
        <strong>Balcony:</strong> <?php echo get_sub_field('has_balcony') ? 'Yes' : 'No'; ?>,<br>
        <strong>Bathroom:</strong> <?php echo get_sub_field('has_bathroom') ? 'Yes' : 'No'; ?>

        <?php
        $rimgs = get_sub_field('room_image');
        $ri = $rimgs ? ( is_array($rimgs) ? $rimgs : [$rimgs] ) : [];

        if ( is_array($ri) && ! empty($ri['sizes']['thumbnail']) ) {
          $url = $ri['sizes']['thumbnail'];
          $alt = $ri['alt'] ?? '';
        } elseif ( intval($ri) ) {
          $url = wp_get_attachment_image_url( $ri, 'thumbnail' );
          $alt = get_post_meta( $ri, '_wp_attachment_image_alt', true );
        } else {
          continue;
        }
        ?>
        <div class="room-gallery">
          <img src="<?php echo esc_url( $url ); ?>" alt="<?php echo esc_attr( $alt ); ?>" />
        </div>

      </li>
    <?php endwhile; ?>
    </ul>
  <?php endif; ?>

</article>
<?php get_footer(); ?>
