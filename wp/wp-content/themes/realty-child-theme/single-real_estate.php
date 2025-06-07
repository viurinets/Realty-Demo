<?php get_header(); ?>
<main>
    <h1><?php the_title(); ?></h1>
    <div><?php the_content(); ?></div>

    <?php if (function_exists('get_field')): ?>
        <ul>
            <?php
            $main_image = get_field('main_image');
            if (!empty($main_image) && is_array($main_image)) {
                echo '<li><img src="' . esc_url($main_image['url']) . '" alt="' . esc_attr($main_image['alt']) . '" width="300"></li>';
            }
            ?>
            <li><strong>Назва будинку:</strong> <?php the_field('building_name'); ?></li>
            <li><strong>Координати:</strong> <?php the_field('location_coordinates'); ?></li>
            <li><strong>Кількість поверхів:</strong> <?php the_field('number_of_floors'); ?></li>
            <li><strong>Тип будівлі:</strong> <?php the_field('building_type'); ?></li>
            <li><strong>Екологічність:</strong> <?php the_field('eco_rating'); ?></li>
        </ul>

        <h3>Приміщення:</h3>
        <?php if (have_rows('rooms')): ?>
            <ul>
                <?php while (have_rows('rooms')): the_row(); ?>
                    <?php
                    $room_image = get_sub_field('room_image');
                    if (!empty($room_image) && is_array($room_image)) {
                        echo '<li><img src="' . esc_url($room_image['url']) . '" alt="' . esc_attr($room_image['alt']) . '" width="200"></li>';
                    }
                    ?>
                    <li>
                        Площа: <?php the_sub_field('area'); ?> м²,
                        Кімнат: <?php the_sub_field('room_count'); ?>,
                        Балкон: <?php the_sub_field('balcony'); ?>,
                        Санвузол: <?php the_sub_field('bathroom'); ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php endif; ?>
    <?php endif; ?>

    <p><strong>Район:</strong> <?php echo get_the_term_list(get_the_ID(), 'district', '', ', '); ?></p>
</main>
<?php get_footer(); ?>
