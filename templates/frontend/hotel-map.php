<?php
/**
 * Frontend hotel map template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="dhr-hotel-map-container" style="height: <?php echo esc_attr($atts['height']); ?>;">
    <div class="dhr-hotel-info-panel">
        <div class="dhr-hotel-info-content">
            <?php
            // Get dynamic settings with defaults
            $location_heading = get_option('dhr_hotel_location_heading', 'LOCATED IN THE WESTERN CAPE');
            $main_heading = get_option('dhr_hotel_main_heading', 'Find Us');
            $description_text = get_option('dhr_hotel_description_text', 'Discover our hotel locations across the Western Cape. Click on any marker to view hotel details and make a reservation.');
            ?>
            <?php if (!empty($location_heading)): ?>
                <h2 class="dhr-location-heading"><?php echo esc_html($location_heading); ?></h2>
            <?php endif; ?>
            <?php if (!empty($main_heading)): ?>
                <h3 class="dhr-main-heading"><?php echo esc_html($main_heading); ?></h3>
            <?php endif; ?>
            <?php if (!empty($description_text)): ?>
                <p class="dhr-description">
                    <?php echo esc_html($description_text); ?>
                </p>
            <?php endif; ?>

            <div class="map-btn">
                <a href="#" class="map-btn__link">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_4637_2271)">
                            <path
                                d="M23.4059 10.1507L23.2848 9.63696H12.1184V14.363H18.7902C18.0975 17.6523 14.8832 19.3837 12.2577 19.3837C10.3473 19.3837 8.33357 18.5802 7.00071 17.2886C6.2975 16.5962 5.73774 15.772 5.3535 14.863C4.96925 13.9541 4.76805 12.9782 4.76143 11.9914C4.76143 10.0007 5.65607 8.00946 6.95786 6.69964C8.25964 5.38982 10.2257 4.65696 12.1805 4.65696C14.4193 4.65696 16.0237 5.84571 16.6237 6.38786L19.9821 3.04714C18.997 2.18143 16.2905 0 12.0723 0C8.81786 0 5.69732 1.24661 3.41625 3.52018C1.16518 5.75893 0 8.99625 0 12C0 15.0038 1.1025 18.0793 3.28393 20.3357C5.61482 22.7421 8.91589 24 12.315 24C15.4077 24 18.3391 22.7882 20.4284 20.5896C22.4823 18.4254 23.5446 15.4307 23.5446 12.2914C23.5446 10.9698 23.4118 10.185 23.4059 10.1507Z"
                                fill="currentColor" />
                        </g>
                        <defs>
                            <clipPath id="clip0_4637_2271">
                                <rect width="24" height="24" fill="white" />
                            </clipPath>
                        </defs>
                    </svg>
                    View On Google Maps
                </a>
            </div>

            <?php //if (!empty($hotels)): ?>
            <!-- <div class="dhr-hotels-list"> -->
            <?php //foreach ($hotels as $hotel): ?>
            <!-- <div class="dhr-hotel-item" data-hotel-id="<?php echo esc_attr($hotel->id); ?>">
                            <h4><?php echo esc_html($hotel->name); ?></h4>
                            <p><?php echo esc_html($hotel->city . ', ' . $hotel->province); ?></p>
                        </div> -->
            <?php //endforeach; ?>
            <!-- </div> -->
            <?php //endif; ?>

            <?php
            // Get reservation settings
            $reservation_label = get_option('dhr_hotel_reservation_label', 'RESERVATION BY PHONE');
            $reservation_phone = get_option('dhr_hotel_reservation_phone', '');

            // Use setting phone if available, otherwise fall back to first hotel's phone
            $display_phone = !empty($reservation_phone) ? $reservation_phone : '';
            if (empty($display_phone) && !empty($hotels) && isset($hotels[0])) {
                $display_phone = $hotels[0]->phone;
            }

            // Only show reservation section if we have a phone number
            if (!empty($display_phone)):
                ?>
                <div class="dhr-reservation-info">
                    <div class="dhr-phone-section">
                        <span class="dhr-phone-icon">
                            <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M10.8203 3.75C10.166 3.75 9.52149 3.98438 8.98438 4.41406L8.90626 4.45312L8.86719 4.49219L4.96094 8.51562L5.00001 8.55469C3.79395 9.66797 3.42286 11.333 3.94532 12.7734C3.9502 12.7832 3.94044 12.8027 3.94532 12.8125C5.00489 15.8447 7.71485 21.6992 13.0078 26.9922C18.3203 32.3047 24.2529 34.9072 27.1875 36.0547H27.2266C28.7451 36.5625 30.3906 36.2012 31.5625 35.1953L35.5078 31.25C36.543 30.2148 36.543 28.418 35.5078 27.3828L30.4297 22.3047L30.3906 22.2266C29.3555 21.1914 27.5195 21.1914 26.4844 22.2266L23.9844 24.7266C23.0811 24.292 20.9277 23.1787 18.8672 21.2109C16.8213 19.2578 15.7764 17.0117 15.3906 16.1328L17.8906 13.6328C18.9404 12.583 18.96 10.835 17.8516 9.80469L17.8906 9.76562L17.7734 9.64844L12.7734 4.49219L12.7344 4.45312L12.6563 4.41406C12.1191 3.98438 11.4746 3.75 10.8203 3.75ZM10.8203 6.25C10.9131 6.25 11.0059 6.29395 11.0938 6.36719L16.0938 11.4844L16.2109 11.6016C16.2012 11.5918 16.2842 11.7236 16.1328 11.875L13.0078 15L12.4219 15.5469L12.6953 16.3281C12.6953 16.3281 14.1309 20.1709 17.1484 23.0469L17.4219 23.2812C20.3272 25.9326 23.75 27.3828 23.75 27.3828L24.5313 27.7344L28.2422 24.0234C28.457 23.8086 28.418 23.8086 28.6328 24.0234L33.75 29.1406C33.9648 29.3555 33.9648 29.2773 33.75 29.4922L29.9219 33.3203C29.3457 33.8135 28.7354 33.916 28.0078 33.6719C25.1758 32.5586 19.6729 30.1416 14.7656 25.2344C9.81934 20.2881 7.23634 14.6777 6.28907 11.9531C6.09864 11.4453 6.23536 10.6934 6.67969 10.3125L6.75782 10.2344L10.5469 6.36719C10.6348 6.29395 10.7275 6.25 10.8203 6.25Z"
                                    fill="#462801" />
                            </svg>
                        </span>
                        <div>
                            <?php if (!empty($reservation_label)): ?>
                                <p class="dhr-reservation-label"><?php echo esc_html($reservation_label); ?></p>
                            <?php endif; ?>
                            <p class="dhr-phone-number"><?php echo esc_html($display_phone); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="dhr-hotel-map-panel">
        <div id="dhr-hotel-map" class="dhr-hotel-map"></div>
    </div>
</div>

<div id="dhr-hotel-info-window-template" style="display: none;">
    <div class="dhr-info-window">
        <div class="dhr-info-window-image">
            <img src="{image_url}" alt="{name}"
                onerror="this.onerror=null; this.src='{pluginUrl}assets/images/default-hotel.jpg';">
        </div>
        <div class="dhr-info-window-content">
            <h3 class="dhr-info-window-title">{name}</h3>
            <p class="dhr-info-window-location">{city} | {province}</p>
            <div class="dhr-info-window-actions">
                <a href="{google_maps_url}" target="_blank" class="dhr-btn-info">
                    <svg width="21" height="21" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M10.4544 1.95996C5.77085 1.95996 1.96021 5.77061 1.96021 10.4542C1.96021 15.1377 5.77085 18.9484 10.4544 18.9484C15.138 18.9484 18.9486 15.1377 18.9486 10.4542C18.9486 5.77061 15.138 1.95996 10.4544 1.95996ZM10.4544 3.26676C14.431 3.26676 17.6418 6.47761 17.6418 10.4542C17.6418 14.4307 14.431 17.6416 10.4544 17.6416C6.47785 17.6416 3.26701 14.4307 3.26701 10.4542C3.26701 6.47761 6.47785 3.26676 10.4544 3.26676ZM9.80101 6.53376V7.84056H11.1078V6.53376H9.80101ZM9.80101 9.14736V14.3746H11.1078V9.14736H9.80101Z"
                            fill="#0B5991" />
                    </svg>
                </a>
                <a href="tel:{phone}" class="dhr-btn-book">
                    Book Now
                </a>
            </div>
        </div>
    </div>
</div>