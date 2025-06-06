<?php get_header(); ?>
<main>
    <h1><?php the_title(); ?></h1>
    <div><?php the_content(); ?></div>

    <?php if (function_exists('get_field')): ?>
        <ul>
            <li><strong>Назва будинку:</strong> <?php the_field('назва_будинку'); ?></li>
            <li><strong>Координати:</strong> <?php the_field('координати_місцезнаходження'); ?></li>
            <li><strong>Кількість поверхів:</strong> <?php the_field('кількість_поверхів'); ?></li>
            <li><strong>Тип будівлі:</strong> <?php the_field('тип_будівлі'); ?></li>
            <li><strong>Екологічність:</strong> <?php the_field('екологічність'); ?></li>
        </ul>

        <h3>Приміщення:</h3>
        <?php if (have_rows('приміщення')): ?>
            <ul>
                <?php while (have_rows('приміщення')): the_row(); ?>
                    <li>
                        Площа: <?php the_sub_field('площа'); ?> м²,
                        Кімнат: <?php the_sub_field('кіл_кімнат'); ?>,
                        Балкон: <?php the_sub_field('балкон'); ?>,
                        Санвузол: <?php the_sub_field('санвузол'); ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php endif; ?>
    <?php endif; ?>

    <p><strong>Район:</strong> <?php echo get_the_term_list(get_the_ID(), 'district', '', ', '); ?></p>
</main>
<?php get_footer(); ?>
