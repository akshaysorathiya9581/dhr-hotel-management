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

/**
 * Get amenity icon SVG (wrapped in function_exists for multiple shortcode instances on same page)
 */
if (!function_exists('get_amenity_icon')) {
function get_amenity_icon($amenity_name)
{
    // Map amenity names to SVG icon image paths
    $plugin_url = plugin_dir_url(dirname(__FILE__, 2));

    $icon_map = array(
        'Bathroom amenities' => 'assets/images/amenity-icon/bathroom/bathroom-amenities.svg',
        'Bathtub Ensuite bath' => 'assets/images/amenity-icon/bathroom/bathtub-ensuite-bath.svg',
        'Hairdryer in room' => 'assets/images/amenity-icon/bathroom/hairdryer-in-room.svg',
        'Shower' => 'assets/images/amenity-icon/bathroom/shower.svg',

        'Telephone' => 'assets/images/amenity-icon/communication-telephone/telephone.svg',

        'Private Pool' => 'assets/images/amenity-icon/fitness-health/private-pool.svg',

        'Double bed' => 'assets/images/amenity-icon/general/double-bed.svg',
        'Double Bed' => 'assets/images/amenity-icon/general/double-bed.svg',
        'King bed' => 'assets/images/amenity-icon/general/king-bed.svg',
        'Queen bed' => 'assets/images/amenity-icon/general/queen-bed.svg',
        'Single bed' => 'assets/images/amenity-icon/general/single-bed.svg',
        'Terrace' => 'assets/images/amenity-icon/general/terrace.svg',
        'Twin bed' => 'assets/images/amenity-icon/general/twin-bed.svg',

        'Wheelchair access - Handicap facilities' => 'assets/images/amenity-icon/handicap-facilities/wheelchair-access-handicap-facilities.svg',

        'Bottled Water' => 'assets/images/amenity-icon/kitchen-dining/bottled-water.svg',
        'Coffee Maker' => 'assets/images/amenity-icon/kitchen-dining/coffee-maker.svg',
        'Cups glasses' => 'assets/images/amenity-icon/kitchen-dining/cups-glasses.svg',
        'Desk' => 'assets/images/amenity-icon/kitchen-dining/desk.svg',
        'Dishes - plates' => 'assets/images/amenity-icon/kitchen-dining/dishes-plates.svg',
        'Dishwasher' => 'assets/images/amenity-icon/kitchen-dining/dishwasher.svg',
        'Kitchen' => 'assets/images/amenity-icon/kitchen-dining/kitchen.svg',
        'Kitchenette' => 'assets/images/amenity-icon/kitchen-dining/kitchenette.svg',
        'Microwave' => 'assets/images/amenity-icon/kitchen-dining/microwave.svg',
        'Oven' => 'assets/images/amenity-icon/kitchen-dining/oven.svg',
        'Posts - pans' => 'assets/images/amenity-icon/kitchen-dining/posts-pans.svg',
        'Refrigeration' => 'assets/images/amenity-icon/kitchen-dining/refrigeration.svg',
        'Silverware' => 'assets/images/amenity-icon/kitchen-dining/silverware.svg',
        'Stove' => 'assets/images/amenity-icon/kitchen-dining/stove.svg',
        'Table - chairs' => 'assets/images/amenity-icon/kitchen-dining/table-chairs.svg',

        'Air conditioner' => 'assets/images/amenity-icon/room-type/air-conditioner.svg',
        'Balcony' => 'assets/images/amenity-icon/room-type/balcony.svg',
        'Ceiling fan' => 'assets/images/amenity-icon/room-type/ceiling-fan.svg',
        'Childrens Suite' => 'assets/images/amenity-icon/room-type/childrens-suite.svg',
        'Cribs' => 'assets/images/amenity-icon/room-type/cribs.svg',
        'Fireplace' => 'assets/images/amenity-icon/room-type/fireplace.svg',
        'Iron' => 'assets/images/amenity-icon/room-type/iron.svg',
        'Ironing board' => 'assets/images/amenity-icon/room-type/ironing-board.svg',
        'Loft' => 'assets/images/amenity-icon/room-type/loft.svg',
        'Minibar' => 'assets/images/amenity-icon/room-type/minibar.svg',
        'Plush bathrobes' => 'assets/images/amenity-icon/room-type/plush-bathrobes.svg',
        'Plush slippers' => 'assets/images/amenity-icon/room-type/plush-slippers.svg',
        'Plush towels' => 'assets/images/amenity-icon/room-type/plush-towels.svg',
        'Private Patio' => 'assets/images/amenity-icon/room-type/private-patio.svg',
        'Safe' => 'assets/images/amenity-icon/room-type/safe.svg',
        'Sitting area' => 'assets/images/amenity-icon/room-type/sitting-area.svg',
        'Sofa bed' => 'assets/images/amenity-icon/room-type/sofa-bed.svg',
        'Solid wood writing desk and chair' => 'assets/images/amenity-icon/room-type/solid-wood-writing-desk-and-chair.svg',
        'Washer - dryer' => 'assets/images/amenity-icon/room-type/washer-dryer.svg',

        'DVD' => 'assets/images/amenity-icon/technology/dvd.svg',
        'Internet Access - Complimentary' => 'assets/images/amenity-icon/technology/internet-access-complimentary.svg',
        'Internet Access - Wireless' => 'assets/images/amenity-icon/technology/internet-access-complimentary.svg',
        'Satellite TV' => 'assets/images/amenity-icon/technology/satellite-tb.svg',
        'TV' => 'assets/images/amenity-icon/technology/tv.svg',

        'Checkmark' => 'assets/images/amenity-icon/generic-icon/checkmark.svg',
        


        'Shower'             => 'assets/images/amenity-icon/bathroom/shower.svg',
        'Bidet'              => 'assets/images/amenity-icon/bathroom/bathroom-amenities.svg',
        'Bath Tub'           => 'assets/images/amenity-icon/bathroom/bathtub-ensuite-bath.svg',
        'Bathrobes'          => 'assets/images/amenity-icon/room-type/plush-bathrobes.svg',
        'DSTV'               => 'assets/images/amenity-icon/technology/tv.svg',
        'Internet available' => 'assets/images/amenity-icon/technology/internet-access-complimentary.svg',
        'Safe'               => 'assets/images/amenity-icon/room-type/safe.svg',
        'Air Conditioning'   => 'assets/images/amenity-icon/room-type/air-conditioner.svg',
        'Minibar'            => 'assets/images/amenity-icon/room-type/minibar.svg',
        'Balcony'            => 'assets/images/amenity-icon/room-type/balcony.svg',
        'Hairdryer In Room'  => 'assets/images/amenity-icon/bathroom/hairdryer-in-room.svg',
        'Toilet'             => 'assets/images/amenity-icon/fitness-health/private-pool.svg',
        'Bathroom Private'   => 'assets/images/amenity-icon/fitness-health/private-pool.svg',
    );

    if (!isset($icon_map[$amenity_name])) {
        return '';
    }

    $src = $plugin_url . $icon_map[$amenity_name];

    return '<img aria-hidden="true" class="bys-amenity-icon-img" src="' . esc_url($src) . '" alt="' . esc_attr($amenity_name) . '">';
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
            ?>
                <div class="bys-room-card">
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
                                                <?php echo get_amenity_icon($amenity_name); ?>
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
                ?>
                    <div class="swiper-slide" style="display: grid;">
                        <div class="bys-room-card">
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
                                                        <?php echo get_amenity_icon($amenity_name); ?>
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
                                    <a href="javascript:void(0)" class="bys-package-button button--theme-3" data-room-code="<?php echo esc_attr($room->room_type_code); ?>"
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
            ?>
                <div class="bys-hotel-room-card">
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
        document.querySelectorAll('.bys-room-image-slider').forEach(function (sliderEl) {
            var paginationEl = sliderEl.querySelector('.bys-room-image-pagination');

            new Swiper(sliderEl, {
                slidesPerView: 1,
                // loop: true,
                spaceBetween: 10,
                // autoplay: {
                //     delay: 1500,
                //     disableOnInteraction: false,
                //     pauseOnMouseEnter: true,
                // },
                speed: 1500,
                pagination: paginationEl ? {
                    el: paginationEl,
                    clickable: true,
                } : false,
            });
        });
    });
</script>
