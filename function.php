<?php
//affiche la carte sur mobile , android/iphone
require 'map_mobile.php';

/* -------------------------renvoie la date en français------------------------------------*/
function date_atelier()
{

    $dateformatstring = "l d F Y";
    $unixtimestamp = strtotime(get_field('date-atelier'));

    echo date_i18n($dateformatstring, $unixtimestamp);
}

function date_actu()
{

    $dateformatstring = "";
    $unixtimestamp = strtotime(the_time("l d F Y"));

    return date_i18n($dateformatstring, $unixtimestamp);
}

/*
 *--------------------------------- google map api-------------------------------------
 */
define("KEY_GOOGLE", "AIzaSyAPxLZSlrq3fvGps2L9J6FgsjxuLfK9Ljg");
function my_acf_google_map_api($api)
{
    $api['key'] = KEY_GOOGLE;
    return $api;
}

add_filter('acf/fields/google_map/api', 'my_acf_google_map_api');


/*-----------------------filtre tab mobile--------------------------------------------------*/


add_filter('woocommerce_product_tabs', 'woo_new_product_tab');

function woo_new_product_tab($tabs)
{

    if (get_field("lieu-atelier")) {
        $tabs['test_tab'] = array(

            'title' => __('Adresse', 'woocommerce'),
            'priority' => 50,
            'callback' => 'woo_new_product_tab_content'
        );


    }
    return $tabs;
}

function woo_new_product_tab_content()
{

    echo '<h2>Adresse</h2>';
    $at = get_field("lieu-atelier");
    $mob = new map_mobile($at);
    $n_faq_tab = $mob->map();
    echo '<p>' . $n_faq_tab . '</p>';

}

/*---------------------------supprimer tab-------------------------------------------------*/

add_filter('woocommerce_product_tabs', 'woo_remove_product_tabs');
function woo_remove_product_tabs($tabs)
{

    // Remove the reviews tab
    if (get_field("lieu-atelier")) {

        unset($tabs['additional_information']);    // Remove the additional information tab

    }
    return $tabs;

}


/*---------------------------enleve fils ariane woocommerce--------------------------------*/
remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0);
/*-----------------------------------------------------------------------------------------------*/

/*------------------------- ne pas renvoyer il y a X résultats -----------*/
// Removes showing results

remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
/*------------------------supprime le produit association de la page produit-----------------------------------------------*/
add_action('pre_get_posts', 'custom_pre_get_posts_query');

function custom_pre_get_posts_query($q)
{

    if (!$q->is_main_query()) return;
    if (!$q->is_post_type_archive()) return;

    if (!is_admin() && is_shop()) {

        $q->set('tax_query', array(array(
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => array('association'), // Don't display products in the knives category on the shop page
            'operator' => 'NOT IN'
        )));

    }

    remove_action('pre_get_posts', 'custom_pre_get_posts_query');

}

/*----------------------------------exclu le widget de la page association-------------------------------------------------------*/


add_filter('woocommerce_product_categories_widget_dropdown_args', 'rv_exclude_wc_widget_categories');
//* Used when the widget is displayed as a list
add_filter('woocommerce_product_categories_widget_args', 'rv_exclude_wc_widget_categories');
function rv_exclude_wc_widget_categories($cat_args)
{
    $cat_args['exclude'] = array('17', '158'); // Insert the product category IDs you wish to exclude
    return $cat_args;
}

/*-----------------------------------------CPT SLIDER------------------------------------------------------------------------------*/
function cpt_slider()
{
    // creating (registering) the custom type
    register_post_type('slider', /* (http://codex.wordpress.org/Function_Reference/register_post_type) */
        // let's now add all the options for this post type
        array('labels' => array(
            'name' => __('Slider', 'jointswp'), /* This is the Title of the Group */
            'singular_name' => __('Slider', 'jointswp'), /* This is the individual type */
            'all_items' => __('Tous les slides', 'jointswp'), /* the all items menu item */
            'add_new' => __('Ajouter un slide', 'jointswp'), /* The add new menu item */
            'add_new_item' => __('Ajouter un slider', 'jointswp'), /* Add New Display Title */
            'edit' => __('Editer un slider', 'jointswp'), /* Edit Dialog */
            'edit_item' => __('Editer un slider', 'jointswp'), /* Edit Display Title */
            'new_item' => __('Nouveau slider', 'jointswp'), /* New Display Title */
            'view_item' => __('Voir slider', 'jointswp'), /* View Display Title */
            'search_items' => __('Chercher slider', 'jointswp'), /* Search Custom Type Title */
            'not_found' => __('Non trouvé dans la base de donnée.', 'jointswp'), /* This displays if there are no entries yet */
            'not_found_in_trash' => __('Non trouvé dans la corbeille', 'jointswp'), /* This displays if there is nothing in the trash */
            'parent_item_colon' => ''
        ), /* end of arrays */
            'description' => __('Desciption slider', 'jointswp'), /* Custom Type Description */
            'public' => true,
            'publicly_queryable' => true,
            'exclude_from_search' => false,
            'show_ui' => true,
            'query_var' => true,
            'menu_position' => 8, /* this is what order you want it to appear in on the left hand side menu */
            'menu_icon' => 'dashicons-format-image', /* the icon for the custom post type menu. uses built-in dashicons (CSS class name) */
            'rewrite' => array('slug' => 'slider', 'with_front' => false), /* you can specify its url slug */
            'has_archive' => 'slider', /* you can rename the slug here */
            'capability_type' => 'post',
            'hierarchical' => false,
            /* the next one is important, it tells what's enabled in the post editor */
            'supports' => array('title', 'thumbnail')
        ) /* end of options */
    ); /* end of register post type */


}


add_action('init', 'cpt_slider');


/*------------------------------meta boxe Link slider----------------------------------------------------------*/
add_action('add_meta_boxes', 'initialisation_metaboxes');
function initialisation_metaboxes()
{

    add_meta_box('meta-slider', 'Lien slider', 'ma_meta_function', 'slider', 'normal', 'high');
}

function ma_meta_function($post)
{

    $val = get_post_meta($post->ID, '_link_slid', true);
    //verifie que le lien du slider ne soit pas déja utilisé
    global $post;
    $idlink = $post->ID;
    for ($i = 457; $i < 459; $i++) {
        $inc = get_post_meta($i, '_link_slid', true);
        if ($idlink != $i) {
            if ($val == $inc) {
                echo "<p style='color: red'>Vous utilisez déja ce lien dans le slider \"" . get_the_title($i) . "\"</p>";
            }
        }
    }
    echo "<p>vous avez selectionné \"" . get_the_title($val) . "\"</p><br>";
    echo '<label for="link_meta">Un article:</label><br>';
    echo '<select name="link_slid">';
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => '6'
    );

    $quer = new WP_Query($args);
    if ($quer->have_posts()) : while ($quer->have_posts()) : $quer->the_post(); ?>

        <option value="<?php echo get_the_ID() ?>"
            <?php //if (get_the_ID() == $val) {echo 'selected';} ?>>
            <?php echo get_the_title() ?></option>;
        <?php

    endwhile;
    echo '</select></br>';
    endif;
    wp_reset_postdata();

}

add_action('save_post', 'save_metaboxes');
function save_metaboxes($post_ID)
{
    if (isset($_POST['link_slid'])) {
        update_post_meta($post_ID, '_link_slid', $_POST['link_slid']);

    }

}

/*----------------------------Ajout size slider--------------------------------------------------------------*/

add_image_size("slid", 800, 300, array("center", "center"));
/*---------------------------------Taile 800*800 pour image en single product-------------------------------------------------*/
function yourtheme_woocommerce_image_dimensions()
{
    $size = array(800, 800);
    return $size;
}

add_filter('single_product_large_thumbnail_size', 'yourtheme_woocommerce_image_dimensions');
/*---------------------------------Taile 400*400 pour image en archive product-------------------------------------------------*/

function woocommerce_image_dimensions()
{
    $mini = array(400, 400);
    return $mini;
}

add_filter('single_product_archive_thumbnail_size', 'woocommerce_image_dimensions');
/*--------------------------------------------customisation des menus a gauche dans l admin--------------------------------------------*/
function remove_menus()
{


    remove_menu_page('edit-comments.php');
}

add_action('admin_menu', 'remove_menus');
function revcon_change_post_label()
{
    global $menu;
    $menu[5][0] = 'Actualités';


}

add_action('admin_menu', 'revcon_change_post_label');
function custom_menu_order($menu_ord)
{
    if (!$menu_ord) return true;

    return array(
        'index.php',
        'woocommerce',
        'edit.php?post_type=product',
        ' ',
        'edit.php?post_type=slider',
        'edit.php',
        'edit.php?post_type=page',
        ' ',
        'upload.php',
        'wpseo_dashboard',
    );
}

add_filter('custom_menu_order', 'custom_menu_order'); // Activate custom_menu_order
add_filter('menu_order', 'custom_menu_order');
/*---------------------------personnalisation du cpt slider admin-------------------------------------------------*/


function changeFeaturedImageLinks($content)
{
    global $post_type;
    if ($post_type == 'slider') {
        $content = str_replace(__('Featured Image'), ('Ajouter une image au slider'), $content);
        $content = str_replace(__('Remove featured image'), ('Supprimer l\'image du slider'), $content);

        return $content;
    }
    return $content;
}

add_filter('admin_post_thumbnail_html', 'changeFeaturedImageLinks');


add_action('do_meta_boxes', 'replace_featured_image_box');
function replace_featured_image_box()
{
    remove_meta_box('postimagediv', 'slider', 'side');
    add_meta_box('postimagediv', ('Image du slider <p style="color: #00AEFF"> L\'image doit faire au minimun 800 pixels de large et de hauteur</p>'), 'post_thumbnail_meta_box', 'slider', 'side', 'low');

}

/*---------------------------------------------------------------------------------------------------------------*/

function cat_atelier()
{

    $args = array(
        'tax_query' => array(
            array(
                'taxonomy' => 'product_cat',
                'terms' => '14'
            )
        )

    );


// Custom query.
    $query = new WP_Query($args);

// Check that we have query results.
    if ($query->have_posts()) {

        // Start looping over the query results.
        while ($query->have_posts()) {

            $query->the_post();
            ?>
            <p>En ce moment :<a href="<?php the_permalink() ?>"> <?php the_title() ?></a></p>


            <?php
        }

    }
}


/*------------------------------------Enléve les catégories dans les single product---------------------------------------------------*/
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);


/*--------------------------------------------REMOVE WORDPRESS IDENTITY---------------------------------------------------------------*/
remove_action("wp_head", "wp_generator");
