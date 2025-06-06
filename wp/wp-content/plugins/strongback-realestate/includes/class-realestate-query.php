<?php
if ( ! class_exists( 'SB_RealEstate_Query' ) ) :

class SB_RealEstate_Query {
    public static function init() {
        add_action( 'pre_get_posts', [ __CLASS__, 'modify_query_order' ] );
    }
    public static function modify_query_order( $query ) {
        if ( is_admin() || ! $query->is_main_query() ) return;
        if ( $query->is_post_type_archive('real_estate') || $query->is_tax('district') ) {
            $query->set('meta_key','ecological_rating');
            $query->set('orderby','meta_value_num');
            $query->set('order','DESC');
        }
    }
}

endif;
SB_RealEstate_Query::init();
