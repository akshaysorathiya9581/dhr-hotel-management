<?php

/**
 * Hotel Rooms Display Template
 * Matches the bys-rooms design structure
 */

if (!defined('ABSPATH')) {
    exit;
}

$layout = isset($hotel_data['layout']) ? $hotel_data['layout'] : 'grid';
$hotel_code = $hotel_data['hotel_code'];
$hotel_name = $hotel_data['hotel_name'];
$channel_id = isset($hotel_data['channel_id']) ? (int) $hotel_data['channel_id'] : 30;
$rooms = $hotel_data['rooms'];
$book_now_checkin = function_exists('wp_date') ? wp_date('Y-m-d') : date('Y-m-d', current_time('timestamp'));
$book_now_checkout = function_exists('wp_date') ? wp_date('Y-m-d', current_time('timestamp') + 2 * DAY_IN_SECONDS) : date('Y-m-d', strtotime('+5 days', current_time('timestamp')));

// echo $book_now_checkin;
// exit;
$columns = $hotel_data['columns'];
$show_images = $hotel_data['show_images'];
$show_amenities = $hotel_data['show_amenities'];
$show_description = $hotel_data['show_description'];
$plugin_url = plugin_dir_url(dirname(__FILE__, 2));


// echo "<pre>";
// print_r($rooms);
// die();
/**
 * Get amenity icon SVG (wrapped in function_exists for multiple shortcode instances on same page)
 */
if (!function_exists('get_amenity_icon')) {
function get_amenity_icon($amenity)
{
    $amenity_arr  = is_array($amenity) ? $amenity : array();
    $amenity_name = '';
    $amenity_id   = '';

    if (!empty($amenity_arr)) {
        $amenity_name = isset($amenity_arr['name']) ? (string) $amenity_arr['name'] : '';
        // OTA uses RoomAmenityCode in 'code'. Other APIs might use id/amenity_id.
        $amenity_id = isset($amenity_arr['code']) ? (string) $amenity_arr['code'] : (isset($amenity_arr['id']) ? (string) $amenity_arr['id'] : (isset($amenity_arr['amenity_id']) ? (string) $amenity_arr['amenity_id'] : ''));
    } elseif (is_string($amenity)) {
        $amenity_name = $amenity;
    }

    $amenity_name = trim($amenity_name);
    $amenity_id   = trim($amenity_id);

    $plugin_file = dirname(__FILE__, 3) . '/dhr-hotel-management.php';
    $plugin_url  = plugins_url('/', $plugin_file);
    $plugin_path = dirname(__FILE__, 3) . '/';

    $base_rel = 'assets/images/amenity-icon/';

    // 1) Prefer ID-based SVGs like assets/images/amenity-icon/12.svg
    if ($amenity_id !== '') {
        $id_filename = preg_replace('/[^0-9A-Za-z_-]/', '', $amenity_id) . '.svg';
        $id_rel      = $base_rel . $id_filename;
        $id_abs      = $plugin_path . $id_rel;
        if (is_file($id_abs)) {
            $src = $plugin_url . $id_rel;
            $alt = $amenity_name !== '' ? $amenity_name : $amenity_id;
            return '<img aria-hidden="true" class="bys-amenity-icon-img" src="' . esc_url($src) . '" alt="' . esc_attr($alt) . '">';
        }
    }

    // 2) Fallback: slug-name SVGs like assets/images/amenity-icon/terrace.svg
    if ($amenity_name !== '') {
        $slug = function_exists('sanitize_title') ? sanitize_title($amenity_name) : strtolower(preg_replace('/[^0-9a-z]+/i', '-', $amenity_name));
        $name_rel = $base_rel . $slug . '.svg';
        $name_abs = $plugin_path . $name_rel;
        if (is_file($name_abs)) {
            $src = $plugin_url . $name_rel;
            return '<img aria-hidden="true" class="bys-amenity-icon-img" src="' . esc_url($src) . '" alt="' . esc_attr($amenity_name) . '">';
        }
    }

    // 3) Default icon when no ID/name SVG exists
    $default_rel = $base_rel . 'default.svg';
    $default_abs = $plugin_path . $default_rel;
    $alt_fallback  = $amenity_name !== '' ? $amenity_name : ($amenity_id !== '' ? $amenity_id : __('Amenity', 'dhr-hotel-management'));
    if (is_file($default_abs)) {
        $src = $plugin_url . $default_rel;
        return '<img aria-hidden="true" class="bys-amenity-icon-img bys-amenity-icon-img--default" src="' . esc_url($src) . '" alt="' . esc_attr($alt_fallback) . '">';
    }

    // Inline fallback if default.svg is missing from the install
    return '<span class="bys-amenity-icon-fallback" aria-hidden="true"><svg class="bys-amenity-icon-svg" width="15" height="17" viewBox="0 0 15 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.5 0.5L9.35 5.65L14.5 7.5L9.35 9.35L7.5 14.5L5.65 9.35L0.5 7.5L5.65 5.65L7.5 0.5Z" stroke="#D3AA74" stroke-width="1.2" stroke-linejoin="round" fill="none"/><circle cx="7.5" cy="7.5" r="1.8" fill="#D3AA74"/></svg></span>';
}
}

/**
 * Format room description (wrapped in function_exists for multiple shortcode instances on same page)
 */
if (!function_exists('format_room_description')) {
function format_room_description($room)
{
    // Use API shortDescription when available
    if (!empty($room->description)) {
        return wp_unslash((string) $room->description);
    }

    $parts = array();

    // Bed type
    if (!empty($room->standard_num_beds)) {
        $bed_text = $room->standard_num_beds > 1 ? 'beds' : 'bed';
        $parts[] = $room->standard_num_beds . ' ' . $bed_text;
    }

    // Room type
    if (!empty($room->room_type_name)) {
        $parts[] = $room->room_type_name;
    }

    // Occupancy
    if (!empty($room->max_occupancy)) {
        $adults = $room->max_occupancy;
        $children = max(0, $adults - 2); // Estimate children
        if ($children > 0) {
            $parts[] = $adults . ' adults, ' . $children . ' child';
        } else {
            $parts[] = $adults . ' adults';
        }
    }

    return implode(' • ', $parts);
}
}

/**
 * Format price for display (e.g. 2500 → 2,500)
 */
if (!function_exists('dhr_format_room_price')) {
function dhr_format_room_price($amount) {
    return number_format((int) $amount, 0, '.', ',');
}
}
?>

<link rel='stylesheet' id='custom-icons-animation-css-css'
    href='https://dhr.4shaw-development.co/le-franschhoek-hotel-spa/wp-content/uploads/elementor/custom-icons/facilitiesandactivityicons/css/animation.css?ver=1743154111'
    media='all' />
<link rel='stylesheet' id='custom-icons-facilitiesandactivityicons-codes-css-css'
    href='https://dhr.4shaw-development.co/le-franschhoek-hotel-spa/wp-content/uploads/elementor/custom-icons/facilitiesandactivityicons/css/facilitiesandactivityicons-codes.css?ver=1743154111'
    media='all' />
<link rel='stylesheet' id='custom-icons-facilitiesandactivityicons-embedded-css-css'
    href='https://dhr.4shaw-development.co/le-franschhoek-hotel-spa/wp-content/uploads/elementor/custom-icons/facilitiesandactivityicons/css/facilitiesandactivityicons-embedded.css?ver=1743154111'
    media='all' />
<link rel='stylesheet' id='custom-icons-facilitiesandactivityicons-ie7-codes-css-css'
    href='https://dhr.4shaw-development.co/le-franschhoek-hotel-spa/wp-content/uploads/elementor/custom-icons/facilitiesandactivityicons/css/facilitiesandactivityicons-ie7-codes.css?ver=1743154111'
    media='all' />
<link rel='stylesheet' id='custom-icons-facilitiesandactivityicons-ie7-css-css'
    href='https://dhr.4shaw-development.co/le-franschhoek-hotel-spa/wp-content/uploads/elementor/custom-icons/facilitiesandactivityicons/css/facilitiesandactivityicons-ie7.css?ver=1743154111'
    media='all' />
<link rel='stylesheet' id='custom-icons-facilitiesandactivityicons-css-css'
    href='https://dhr.4shaw-development.co/le-franschhoek-hotel-spa/wp-content/uploads/elementor/custom-icons/facilitiesandactivityicons/css/facilitiesandactivityicons.css?ver=1743154111'
    media='all' />

<style>
    @media (max-width: 767px) {
        .bys-hotel-rooms__md-p-0 {
            padding: 0;
        }
    }

    /* Room image slider: enable Swiper only on mobile (<768px) */
    @media (min-width: 768px) {
        /* If Swiper is not initialized (or destroyed), avoid stacking all slides */
        .bys-room-image-slider:not(.swiper-initialized) .swiper-wrapper {
            display: block;
        }
        .bys-room-image-slider:not(.swiper-initialized) .swiper-slide {
            display: none;
        }
        .bys-room-image-slider:not(.swiper-initialized) .swiper-slide:first-child {
            display: block;
        }
        .bys-room-image-slider .bys-room-image-pagination {
            display: none !important;
        }
    }
</style>    

<?php if ($layout === 'grid'): ?>
    <div class="bys-hotel-rooms">
        <div class="bys-rooms-grid" data-columns="<?php echo esc_attr($columns); ?>">
            <?php foreach ($rooms as $room):
                $room_images = !empty($room->images) && is_array($room->images) ? $room->images : array();
                $room_amenities = !empty($room->amenities) && is_array($room->amenities) ? $room->amenities : array();
                $first_image = !empty($room_images) ? $room_images[0] : 'https://dummyimage.com/1024x682/ccc/000';
                $has_images = $show_images && !empty($room_images);
                $room_price = isset($room->from_price) ? (int) $room->from_price : 0;
                $room_card_id = 'bys-room-' . (function_exists('sanitize_title')
                    ? sanitize_title((string) $room->room_type_name)
                    : strtolower(trim(preg_replace('/[^0-9a-z]+/i', '-', (string) $room->room_type_name), '-')));
            ?>
                <div class="bys-room-card" id="<?php echo esc_attr($room_card_id); ?>">
                    <span class="bys-room-price"></span>

                    <?php if ($has_images): ?>
                        <div class="bys-room-image bys-room-image-slider swiper">
                            <div class="swiper-wrapper">
                                <?php foreach ($room_images as $image_url): ?>
                                    <div class="swiper-slide">
                                        <img src="<?php echo esc_url($image_url); ?>"
                                            alt="<?php echo esc_attr($room->room_type_name); ?>"
                                            loading="lazy">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="bys-room-price-badge">
                                <span class="bys-price-label">FROM</span>
                                <span class="bys-price-amount">R<?php echo esc_html(dhr_format_room_price($room_price)); ?></span>
                                <span class="bys-price-period">/ NIGHT</span>
                            </div>
                            <div class="bys-room-image-pagination"></div>
                        </div>
                    <?php else: ?>
                        <div class="bys-room-image bys-room-image-placeholder">
                            <img src="<?php echo esc_url($first_image); ?>" alt="<?php echo esc_attr($room->room_type_name); ?>"
                                loading="lazy">
                            <div class="bys-room-price-badge">
                                <span class="bys-price-label">FROM</span>
                                <span class="bys-price-amount">R<?php echo esc_html(dhr_format_room_price($room_price)); ?></span>
                                <span class="bys-price-period">/ NIGHT</span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="bys-room-content">
                        <div>
                            <h3 class="bys-room-title"><?php echo esc_html($room->room_type_name); ?></h3>
    
                            <?php if ($room->max_occupancy): ?>
                                <div class="bys-room-specs">
                                    <span class="bys-room-specs-line"><?php echo esc_html($room->max_occupancy); ?>
                                        <?php echo $room->max_occupancy == 1 ? __('Guest', 'dhr-hotel-management') : __('Guests', 'dhr-hotel-management'); ?></span>
                                </div>
                            <?php endif; ?>
    
                            <?php if ($show_amenities && !empty($room_amenities)): ?>
                                <ul class="bys-room-amenities">
                                    <?php foreach ($room_amenities as $amenity):
                                        $amenity_name = isset($amenity['name']) ? $amenity['name'] : (is_string($amenity) ? $amenity : '');
                                        if (empty($amenity_name))
                                            continue;
                                    ?>
                                        <li class="bys-room-amenity-item">
                                            <span class="bys-amenity-icon">
                                                <?php echo get_amenity_icon($amenity); ?>
                                            </span>
                                            <span class="bys-amenity-text"><?php echo esc_html($amenity_name); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
    
                            <?php if ($show_description): ?>
                                <div class="bys-room-description">
                                    <?php echo esc_html(format_room_description($room)); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="bys-room-actions">
                            <a href="javascript:void(0)" class="bys-book-now-link" data-room-code="<?php echo esc_attr($room->room_type_code); ?>"
                                data-hotel-code="<?php echo esc_attr($hotel_code); ?>" data-channel-id="<?php echo esc_attr($channel_id); ?>"
                                data-checkin="<?php echo esc_attr($book_now_checkin); ?>" data-checkout="<?php echo esc_attr($book_now_checkout); ?>"
                                data-adults="<?php echo esc_attr($room->max_occupancy ?: 2); ?>" data-children="0" data-rooms="1">
                                <?php _e('Discover More', 'dhr-hotel-management'); ?>
                            </a>
                            <!-- <a href="#" class="bys-book-now-button" data-room-code="<?php //echo esc_attr($room->room_type_code); 
                                                                                            ?>"
                                data-hotel-code="<?php //echo esc_attr($hotel_code); 
                                                    ?>" data-property-id=""
                                data-checkin="<?php //echo esc_attr(date('Y-m-d', strtotime('+1 day'))); 
                                                ?>"
                                data-checkout="<?php //echo esc_attr(date('Y-m-d', strtotime('+3 days'))); 
                                                ?>"
                                data-adults="<?php //echo esc_attr($room->max_occupancy ?: 2); 
                                                ?>" data-children="0" data-rooms="1">
                                <?php //_e('Book Now', 'dhr-hotel-management'); 
                                ?>
                            </a> -->
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php if ($layout === 'grid_second'): ?>
    <div class="bys-hotel-rooms bys-hotel-rooms__md-p-0">
        <div class="bys-rooms-two swiper rooms-design-swiper">
            <div class="swiper-wrapper">
                <?php foreach ($rooms as $room):
                    $room_images = !empty($room->images) && is_array($room->images) ? $room->images : array();
                    $room_amenities = !empty($room->amenities) && is_array($room->amenities) ? $room->amenities : array();
                    $first_image = !empty($room_images) ? $room_images[0] : 'https://dummyimage.com/1024x682/ccc/000';
                    $has_images = $show_images && !empty($room_images);
                    $room_price = isset($room->from_price) ? (int) $room->from_price : 0;
                    $room_card_id = 'bys-room-' . (function_exists('sanitize_title')
                        ? sanitize_title((string) $room->room_type_name)
                        : strtolower(trim(preg_replace('/[^0-9a-z]+/i', '-', (string) $room->room_type_name), '-')));
                ?>
                    <div class="swiper-slide" style="display: grid;">
                        <div class="bys-room-card" id="<?php echo esc_attr($room_card_id); ?>">
                            <span class="bys-room-price"></span>
            
                            <?php if ($has_images): ?>
                                <div class="bys-room-image bys-room-image-slider swiper">
                                    <div class="swiper-wrapper">
                                        <?php foreach ($room_images as $image_url): ?>
                                            <div class="swiper-slide">
                                                <img src="<?php echo esc_url($image_url); ?>"
                                                    alt="<?php echo esc_attr($room->room_type_name); ?>"
                                                    loading="lazy">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="bys-room-price-badge">
                                        <span class="bys-price-label">FROM</span>
                                        <span class="bys-price-amount">R<?php echo esc_html(dhr_format_room_price($room_price)); ?></span>
                                        <span class="bys-price-period">/ NIGHT</span>
                                    </div>
                                    <div class="bys-room-image-pagination"></div>
                                </div>
                            <?php else: ?>
                                <div class="bys-room-image bys-room-image-placeholder">
                                    <img src="<?php echo esc_url($first_image); ?>" alt="<?php echo esc_attr($room->room_type_name); ?>"
                                        loading="lazy">
                                    <div class="bys-room-price-badge">
                                        <span class="bys-price-label">FROM</span>
                                        <span class="bys-price-amount">R<?php echo esc_html(dhr_format_room_price($room_price)); ?></span>
                                        <span class="bys-price-period">/ NIGHT</span>
                                    </div>
                                </div>
                            <?php endif; ?>
            
                            <div class="bys-room-content">
                                <div>
                                    <h3 class="bys-room-title"><?php echo esc_html($room->room_type_name); ?></h3>
                
                                    <?php if ($room->max_occupancy): ?>
                                        <div class="bys-room-specs">
                                            <span class="bys-room-specs-line"><?php echo esc_html($room->max_occupancy); ?>
                                                <?php echo $room->max_occupancy == 1 ? __('Guest', 'dhr-hotel-management') : __('Guests', 'dhr-hotel-management'); ?></span>
                                        </div>
                                    <?php endif; ?>
                
                                    <?php if ($show_amenities && !empty($room_amenities)): ?>
                                        <ul class="bys-room-amenities">
                                            <?php foreach ($room_amenities as $amenity):
                                                $amenity_name = isset($amenity['name']) ? $amenity['name'] : (is_string($amenity) ? $amenity : '');
                                                if (empty($amenity_name))
                                                    continue;
                                            ?>
                                                <li class="bys-room-amenity-item">
                                                    <span class="bys-amenity-icon">
                                                        <?php echo get_amenity_icon($amenity); ?>
                                                    </span>
                                                    <span class="bys-amenity-text"><?php echo esc_html($amenity_name); ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                
                                    <?php if ($show_description): ?>
                                        <div class="bys-room-description">
                                            <?php echo esc_html(format_room_description($room)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="bys-room-actions bys-packages">
                                    <a href="javascript:void(0)" class="bys-package-button button--theme-3 bys-book-now-link" data-room-code="<?php echo esc_attr($room->room_type_code); ?>"
                                        data-hotel-code="<?php echo esc_attr($hotel_code); ?>" data-channel-id="<?php echo esc_attr($channel_id); ?>"
                                        data-checkin="<?php echo esc_attr($book_now_checkin); ?>" data-checkout="<?php echo esc_attr($book_now_checkout); ?>"
                                        data-adults="<?php echo esc_attr($room->max_occupancy ?: 2); ?>" data-children="0" data-rooms="1" style="width: auto; margin: 0;">
                                        <?php _e('Book Now', 'dhr-hotel-management'); ?>
                                    </a>
                                    <!-- <a href="#" class="bys-book-now-button" data-room-code="<?php //echo esc_attr($room->room_type_code); 
                                                                                                    ?>"
                                        data-hotel-code="<?php //echo esc_attr($hotel_code); 
                                                            ?>" data-property-id=""
                                        data-checkin="<?php //echo esc_attr(date('Y-m-d', strtotime('+1 day'))); 
                                                        ?>"
                                        data-checkout="<?php //echo esc_attr(date('Y-m-d', strtotime('+3 days'))); 
                                                        ?>"
                                        data-adults="<?php //echo esc_attr($room->max_occupancy ?: 2); 
                                                        ?>" data-children="0" data-rooms="1">
                                        <?php //_e('Book Now', 'dhr-hotel-management'); 
                                        ?>
                                    </a> -->
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($layout === 'cards'): ?>
    <div class="bys-hotel-rooms-second">
        <div class="bys-hotel-room-grid">
            <?php foreach ($rooms as $room):
                $room_images = !empty($room->images) && is_array($room->images) ? $room->images : array();
                $first_image = !empty($room_images) ? $room_images[0] : $plugin_url . 'assets/images/package/2.png';
                $room_price = isset($room->from_price) ? (int) $room->from_price : 0;
                $room_card_id = 'bys-hotel-room-' . (function_exists('sanitize_title')
                    ? sanitize_title((string) $room->room_type_name)
                    : strtolower(trim(preg_replace('/[^0-9a-z]+/i', '-', (string) $room->room_type_name), '-')));
            ?>
                <div class="bys-hotel-room-card" id="<?php echo esc_attr($room_card_id); ?>">
                    <div class="bys-hotel-room-card__frature-img bys-room-image-slider swiper">
                        <div class="swiper-wrapper">
                            <?php if (!empty($room_images)): ?>
                                <?php foreach ($room_images as $image_url): ?>
                                    <div class="swiper-slide">
                                        <img src="<?php echo esc_url($image_url); ?>"
                                            alt="<?php echo esc_attr($room->room_type_name); ?>"
                                            loading="lazy">
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="swiper-slide">
                                    <img src="<?php echo esc_url($first_image); ?>"
                                        alt="<?php echo esc_attr($room->room_type_name); ?>"
                                        loading="lazy">
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="bys-room-image-pagination"></div>
                    </div>
                    <div class="bys-hotel__content">
                        <div class="card__top-badge">
                            <p class="package-overlay__tag">
                                <?php echo esc_html(sprintf(__('From R%s/Night', 'dhr-hotel-management'), dhr_format_room_price($room_price))); ?>
                            </p>
                        </div>
                        <div class="bys-hotel-overlay__content">
                            <div class="bys-hotel-overlay__content__inner">
                                <h3 class="bys-hotel-overlay__main-title"><?php echo esc_html($room->room_type_name); ?></h3>
                            </div>
                            <div class="bys-hotel-btn-grp">
                                <a href="#" class="bys-hotel-btn button-light bys-book-now-link"
                                    data-room-code="<?php echo esc_attr($room->room_type_code); ?>"
                                    data-hotel-code="<?php echo esc_attr($hotel_code); ?>"
                                    data-channel-id="<?php echo esc_attr($channel_id); ?>"
                                    data-checkin="<?php echo esc_attr($book_now_checkin); ?>"
                                    data-checkout="<?php echo esc_attr($book_now_checkout); ?>"
                                    data-adults="<?php echo esc_attr($room->max_occupancy ?: 2); ?>"
                                    data-children="0"
                                    data-rooms="1"><?php _e('Book Now', 'dhr-hotel-management'); ?></a>
                                <a href="#" class="bys-hotel-btn button-dark"><?php _e('View Room', 'dhr-hotel-management'); ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var packageSwiper = new Swiper('.hotel-rooms-swiper', {
            slidesPerView: 1,
            spaceBetween: 10,
            loop: false,
            navigation: false,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },
            speed: 2000,
            pagination: {
                el: '.package-swiper-pagination',
                clickable: true,
                bulletClass: 'package-swiper-pagination-bullet',
                bulletActiveClass: 'package-swiper-pagination-bullet-active',
            },
            breakpoints: {
                768: {
                    slidesPerView: 2,
                    spaceBetween: 10,
                    pagination: false
                },
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 10,
                    pagination: false
                },
                1280: {
                    slidesPerView: 3,
                    spaceBetween: 10,
                    pagination: false
                }
            }
        });
    });


    document.addEventListener('DOMContentLoaded', function () {
        var packageSwiper = new Swiper('.rooms-design-swiper', {
            slidesPerView: 1,
            spaceBetween: 30,
            loop: false,
            navigation: false,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },
            speed: 2000,
            pagination: {
                el: '.package-swiper-pagination',
                clickable: true,
                bulletClass: 'package-swiper-pagination-bullet',
                bulletActiveClass: 'package-swiper-pagination-bullet-active',
            },
            breakpoints: {
                768: {
                    slidesPerView: 2,
                    spaceBetween: 30,
                    pagination: false
                },
                1024: {
                    slidesPerView: 2,
                    spaceBetween: 60,
                    pagination: false
                },
                1280: {
                    slidesPerView: 2,
                    spaceBetween: 60,
                    pagination: false
                }
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        var MOBILE_MAX_WIDTH = 767;

        function isMobileSliderEnabled() {
            return window.innerWidth <= MOBILE_MAX_WIDTH;
        }

        function initRoomSliders() {
            document.querySelectorAll('.bys-room-image-slider').forEach(function (sliderEl) {
                // Only init once
                if (sliderEl && sliderEl.swiper) {
                    return;
                }

                var paginationEl = sliderEl.querySelector('.bys-room-image-pagination');

                new Swiper(sliderEl, {
                    slidesPerView: 1,
                    spaceBetween: 10,
                    speed: 1500,
                    pagination: paginationEl ? {
                        el: paginationEl,
                        clickable: true,
                    } : false,
                });
            });
        }

        function destroyRoomSliders() {
            document.querySelectorAll('.bys-room-image-slider').forEach(function (sliderEl) {
                if (sliderEl && sliderEl.swiper && typeof sliderEl.swiper.destroy === 'function') {
                    // destroy(deleteInstance=true, cleanStyles=true)
                    sliderEl.swiper.destroy(true, true);
                }
            });
        }

        function syncRoomSliders() {
            if (typeof Swiper === 'undefined') {
                return;
            }
            if (isMobileSliderEnabled()) {
                initRoomSliders();
            } else {
                destroyRoomSliders();
            }
        }

        var resizeTimer = null;
        window.addEventListener('resize', function () {
            if (resizeTimer) {
                window.clearTimeout(resizeTimer);
            }
            resizeTimer = window.setTimeout(function () {
                syncRoomSliders();
            }, 150);
        });

        syncRoomSliders();
    });
</script>
