<?php 
$args = array(
  'post_type'=> 'artist',
  'orderby' => 'title',
  'order'    => 'ASC',
  'posts_per_page' => '1000'
);              

echo '<div class="artistOverlay"></div>';

$artist_query = new WP_Query( $args );

if($artist_query->have_posts() ) : while ( $artist_query->have_posts() ) : $artist_query->the_post();

    echo '<div class="artistModal artist-'; the_ID(); echo '">'; 
        the_title('<h4>', '</h4>');
        echo '<div class="excerpt">'; the_excerpt(); echo '</div>';
        if( get_field('website_url') || get_field('facebook_url') || get_field('twitter_url') || get_field('instagram_url') ):
            echo '<div class="artistLinks">';
                if( get_field('website_url') ):
                    echo '<a href="'; the_field('website_url'); echo '" target="_blank"><img src="/wp-content/uploads/2021/10/link-icon.svg" alt="Website icon"></a>';
                endif;     
                if( get_field('facebook_url') ):
                    echo '<a href="'; the_field('facebook_url'); echo '" target="_blank"><img src="/wp-content/uploads/2021/10/facebook-icon.svg" alt="Facebook icon"></a>';
                endif; 
                if( get_field('twitter_url') ):
                    echo '<a href="'; the_field('twitter_url'); echo '" target="_blank"><img src="/wp-content/uploads/2021/10/twitter-icon.svg" alt="Twitter icon"></a>';
                endif; 
                if( get_field('instagram_url') ):
                    echo '<a href="'; the_field('instagram_url'); echo '" target="_blank"><img src="/wp-content/uploads/2021/10/instagram-icon.svg" alt="Instagram icon"></a>';
                endif;
                if( get_field('youtube_url') ):
                    echo '<a href="'; the_field('youtube_url'); echo '" target="_blank"><img src="/wp-content/uploads/2021/10/youtube-icon.svg" alt="Youtube icon"></a>';
                endif;
            echo '</div>'; 
        endif; 
        the_content();  
        echo '<a href="#artist-'; the_ID(); echo '" class="closeArtist"><span class="material-icons">close</span></a>';
    echo '</div>';

endwhile; else: 

endif; wp_reset_postdata();

echo '<ul class="list artists">';

if($artist_query->have_posts() ) : while ( $artist_query->have_posts() ) : $artist_query->the_post();

echo '<li class="artist">';
    echo '<a href="#artist-'; the_ID(); echo '" class="bioBtn">';
    echo '<div class="artistImg">'; the_post_thumbnail(); echo '</div>';
    echo '</a>'; 
    echo '<div class="artistDetails"><h4 class="artistName">'; echo '<a href="#artist-'; the_ID(); echo '" class="bioBtn">'; the_title(); echo '</a>'; echo '</h4>'; echo '<div class="artistContent">'; the_excerpt(); echo '</div>'; echo '</div>'; 
echo '</li>';

endwhile; else: 

endif; wp_reset_postdata();

echo '</ul>';

?>