<?php
get_header();
?>

<section>
  <h1>Latest Posts</h1>

  <?php
  echo do_shortcode( '[sb_filter]' );

  if ( have_posts() ) :
    while ( have_posts() ) : the_post();
  ?>
      <article>
        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
        <div><?php the_excerpt(); ?></div>
      </article>
  <?php
    endwhile;
  else:
  ?>
    <p>No posts found.</p>
  <?php
  endif;
  ?>
</section>

<?php
get_footer();
?>
