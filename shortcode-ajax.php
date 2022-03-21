/**
 * FX Photo Video Gallery extension function with pagination
 *
 * @param   bool    $register   If true, block assets will be registered
 * @return  bool                If true, block assets will be registered
 */
if ( class_exists( 'FX_Gallery' ) ) {
    function fxgallery_pagination_function_shortcode( $atts ) {
        FX_Gallery::fx_gallery_styles_n_scripts();
        
        $atts = shortcode_atts(
          array(
            'gallery_id' => '',
            'per_load' => '8'
          ), 
        $atts, 'fxgallery_pagination_function_shortcode' );

        ob_start();
            $args = array(
                'posts_per_page' => -1,
                'post_type' => 'photo-video-gallery',
                'p' => $atts['gallery_id'],
            );

            $query = new WP_Query( $args );
            if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post(); ?>
                <style type="text/css">
                    .gallery-loader {
                        position: fixed;
                        left: 0;
                        top: 0;
                        width: 100%;
                        height: 100%;
                        z-index: 9999;
                        background: url(/wp-content/uploads/2022/03/loader.gif) 50% 50% no-repeat #f9f9f9;
                        opacity: .8;
                        display: none;
                    }
                </style>
                <div class="gallery-loader"></div>
                <div class="fx-gallery-result">
                    <section class="media-gallery media-gallery--stacked-layout grid"> <div class="grid-sizer"></div>
                        <?php
                        //Start with ACF GALLERY TYPE loop
                        $images = get_field('gallery_quick_add');
                        if( $images ):
                           foreach( array_slice( $images, 0, $atts['per_load'] ) as $image ):
                               
                            //Setting all our variables we'll need (same titles as other query so we can keep the same templates)
                            //save ID for assurance
                            $current_id = $image['ID'];
                            //URL for popup
                            $gallery_url = esc_url( $image['url'] );
                            //captions pulled from media lib
                            if ( wp_get_attachment_caption( $current_id ) ) {
                                $gallery_captions = wp_get_attachment_caption( $current_id );
                            } else {
                                $gallery_captions = "";
                            }
                            //Media title 
                            if ( get_the_title( $current_id ) ) {
                                $gallery_entry_title = get_the_title( $current_id );
                            } else {
                                $gallery_entry_title = "";
                            }
                            //URL-source 
                            $gallery_thumbnail = esc_url( $image['url'] );
                            //alt text
                            if ( $image['alt'] ) {
                                $gallery_thumbnail_alt = esc_url( $image['alt'] );
                            } else {
                                $gallery_thumbnail_alt = $gallery_entry_title;
                            }
                    
                        
                            //Logic for only showing category filter     
                            //grab current taxonomies on each
                            $quick_add_category = get_field("gallery_categories", $current_id );
                            //set empty array
                             $category_names = array();
                             //loop to build what we want (all categories)
                            if ( $quick_add_category ) {
                                foreach ( $quick_add_category as $term ) {
                                    $category_names[] = esc_html( $term->name );
                                }
                            }
                            //logic to compare given category and skip entry if not in our categories we want
                            if( ( !empty( $_POST['category'] ) ) ) {
                                $gallery_category_entry = $_POST['category'];

                                if( in_array('all', $gallery_category_entry ) ) {
                                    // do nothing
                                }
                                    
                                elseif( !array_intersect( $gallery_category_entry, $category_names ) ) {
                                    continue;
                                } else {
                                    $gallery_category_entry = "";
                                }
                            }
                            ?>
                            <figure class="media-gallery__item grid-item"> <a href="<?php echo $gallery_url; ?>" class="media-gallery__item-link " data-group="group-1" title="<?php echo $gallery_entry_title; ?>"><img class="media-gallery__item-img" src="<?php echo $gallery_url; ?>" alt="<?php echo $gallery_thumbnail_alt; ?>"><figcaption class="media-gallery__item-title" style="display: none;"><p><?php echo $gallery_entry_title; ?></p></figcaption></a>
                            </figure>
                            <?php
                            //END ACF GALLERY LOOP     
                            endforeach;
                        endif;  
                        ?>    
                       
                    </section>
                </div>    
                <?php if( $atts['per_load'] <= count( $images ) ): ?>
                    <form class="fx-gallery-form" method="post" action="<?php echo admin_url( "admin-ajax.php" ); ?>">
                        <input type="hidden" name="action" value="gallery_filter"> 
                        <input type="hidden" name="gallery_id" value="<?php echo $atts['gallery_id']; ?>" class="gallery-id">
                        <input type="hidden" name="gallery_counter" value="0" class="gallery-counter">
                        <input type="hidden" name="gallery_per_load" value="<?php echo $atts['per_load']; ?>" class="gallery-per-load">
                        <input type="hidden" name="gallery_total" value="<?php echo count( $images ); ?>" class="gallery-total">
                        <button id="fx-load-more-pagination" class="btn">Load More</button>
                    </form>  
                <?php endif; ?>    
                   
            <?php endwhile; endif; wp_reset_postdata(); ?>       
        <?php return ob_get_clean();
    }

    add_shortcode('fx_gallery_pagination', 'fxgallery_pagination_function_shortcode');

    //Gallery AJAX Result
    add_action( 'wp_ajax_gallery_filter', 'fx_gallery_ajax_result' ); 
    add_action( 'wp_ajax_nopriv_gallery_filter', 'fx_gallery_ajax_result' );
    function fx_gallery_ajax_result() {
        if( isset( $_POST['gallery_id'] ) ):
            $args = array(
                'posts_per_page' => -1,
                'post_type' => 'photo-video-gallery',
                'p' => $_POST['gallery_id'],
            );

            $query = new WP_Query( $args );
            if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post(); ?>
                <?php
                //Start with ACF GALLERY TYPE loop
                $images = get_field('gallery_quick_add');
                if( $images ):
                    foreach( array_slice( $images, $_POST['gallery_counter'], $_POST['gallery_per_load'] ) as $image ):
                    //Setting all our variables we'll need (same titles as other query so we can keep the same templates)
                    //save ID for assurance
                    $current_id = $image['ID'];
                    //URL for popup
                    $gallery_url = esc_url( $image['url'] );
                    //captions pulled from media lib
                    if ( wp_get_attachment_caption( $current_id ) ) {
                        $gallery_captions = wp_get_attachment_caption( $current_id );
                    } else {
                        $gallery_captions = "";
                    }
                    //Media title 
                    if ( get_the_title( $current_id ) ) {
                        $gallery_entry_title = get_the_title( $current_id );
                    } else {
                        $gallery_entry_title = "";
                    }
                    //URL-source 
                    $gallery_thumbnail = esc_url( $image['url'] );
                    //alt text
                    if ( $image['alt'] ) {
                        $gallery_thumbnail_alt = esc_url( $image['alt'] );
                    } else {
                        $gallery_thumbnail_alt = $gallery_entry_title;
                    }
            
                
                    //Logic for only showing category filter     
                    //grab current taxonomies on each
                    $quick_add_category = get_field("gallery_categories", $current_id );
                    //set empty array
                     $category_names = array();
                     //loop to build what we want (all categories)
                    if ( $quick_add_category ) {
                        foreach ( $quick_add_category as $term ) {
                            $category_names[] = esc_html( $term->name );
                        }
                    }
                    //logic to compare given category and skip entry if not in our categories we want
                    if( (!empty( $_POST['category'] ) ) ) {
                        $gallery_category_entry = $_POST['category'];

                        if( in_array('all', $gallery_category_entry ) ) {
                            // do nothing
                        }
                            
                        elseif( !array_intersect( $gallery_category_entry, $category_names ) ) {
                            continue;
                        } else {
                            $gallery_category_entry = "";
                        }
                    }
                    ?>
                    <figure class="media-gallery__item grid-item"> <a href="<?php echo $gallery_url; ?>" class="media-gallery__item-link " data-group="group-1" title="<?php echo $gallery_entry_title; ?>"><img class="media-gallery__item-img" src="<?php echo $gallery_url; ?>" alt="<?php echo $gallery_thumbnail_alt; ?>"><figcaption class="media-gallery__item-title" style="display: none;"><p><?php echo $gallery_entry_title; ?></p></figcaption></a>
                    </figure>
                    
                    <?php
                    //END ACF GALLERY LOOP     
                    endforeach;
                endif;  
                ?>      
            <?php endwhile; endif; wp_reset_postdata(); ?>    
            <?php die(); ?>
        <?php endif; ?>  
    <?php    
    }
}
