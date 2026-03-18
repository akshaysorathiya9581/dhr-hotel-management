<?php
/**
 * First Package Design Template
 * Shortcode: [dhr_package_first_design]
 * Displays packages from database with same design.
 */

if (!defined('ABSPATH')) {
    exit;
}

$plugin_url = isset($plugin_url) ? $plugin_url : DHR_HOTEL_PLUGIN_URL;
$packages = isset($packages) ? $packages : array();
$channel_id = (int) get_option('dhr_shr_channel_id', '30');
$book_now_checkin = function_exists('wp_date') ? wp_date('Y-m-d') : date('Y-m-d', current_time('timestamp'));
$book_now_checkout = function_exists('wp_date') ? wp_date('Y-m-d', current_time('timestamp') + 2 * DAY_IN_SECONDS) : date('Y-m-d', strtotime('+2 days', current_time('timestamp')));
?>

<!-- First Package Design -->
<div class="bys-packages">
    <?php if (empty($packages)) : ?>
        <p class="dhr-no-packages"><?php esc_html_e('No packages available at the moment.', 'dhr-hotel-management'); ?></p>
    <?php else : ?>
        <?php
        $featured_item = $packages[0];
        $slider_items = array_slice($packages, 1);

        $render_package_card = function ($item, $show_category_label = true) use ($plugin_url, $channel_id, $book_now_checkin, $book_now_checkout) {
            $pkg = $item['package'];
            $details = $item['details'];
            $hotel = $item['hotel'];

            $title = $details && !empty($details->name) ? $details->name : $pkg->package_code;
            $desc = $details && !empty($details->description) ? wp_kses_post(wp_unslash((string) $details->description)) : '';
            $short_desc = $desc ? wp_trim_words(wp_strip_all_tags($desc), 25) : '';

            $img_url = $plugin_url . 'assets/images/package/1.png';
            if ($details && !empty($details->images) && is_array($details->images)) {
                $first = reset($details->images);
                if (is_array($first) && !empty($first['fileName'])) {
                    $img_url = esc_url($first['fileName']);
                } elseif (is_object($first) && !empty($first->fileName)) {
                    $img_url = esc_url($first->fileName);
                }
            }

            $hotel_name = $hotel && !empty($hotel->name) ? $hotel->name : $pkg->hotel_code;
            $location_line = $hotel ? trim($hotel->city . ($hotel->province ? ', ' . $hotel->province : '') . ($hotel->country ? ', ' . $hotel->country : '')) : '';
            $category_label = !empty($pkg->category_title) ? wp_unslash((string) $pkg->category_title) : __('Package Experience', 'dhr-hotel-management');

            $included_list = array();
            if ($details && !empty($details->description)) {
                $text = wp_strip_all_tags(wp_unslash((string) $details->description));
                $lines = preg_split('/\n|\r\n?|\.\s+/', $text, 5, PREG_SPLIT_NO_EMPTY);
                $included_list = array_slice(array_filter(array_map('trim', $lines)), 0, 4);
            }
            if (empty($included_list)) {
                $included_list = array(__('Package details available on request.', 'dhr-hotel-management'));
            }
            ?>
            <div class="bys-packages-card">
                <div class="bys-packages-card__top">
                    <div class="bys-packages-card__frature-img" style="background-image: url('<?php echo esc_url($img_url); ?>')"></div>
                    <div class="card__top-content">
                        <?php
                        $pkg_icon_type = isset($pkg->category_icon_type) ? $pkg->category_icon_type : 'url';
                        if ($pkg_icon_type === 'svg' && !empty($pkg->category_icon_svg)) : ?>
                            <span class="top-left__icon top-left__icon--svg"><?php echo $pkg->category_icon_svg; ?></span>
                        <?php elseif (!empty($pkg->category_icon_url)) : ?>
                            <img class="top-left__icon" src="<?php echo esc_url($pkg->category_icon_url); ?>" alt="">
                        <?php else : ?>
                            <img class="top-left__icon" src="<?php echo esc_url($plugin_url . 'assets/images/icons/experience-icon.svg'); ?>" alt="">
                        <?php endif; ?>
                    </div>
                    <div class="package-overlay">
                        <?php if ($show_category_label) : ?>
                            <span class="featured-experience__label"><?php echo esc_html('FEATURED PACKAGED EXPERIENCE'); ?></span>
                        <?php endif; ?>
                        <h3 class="package-overlay__title"><?php echo esc_html($title); ?></h3>
                        <div class="package-overlay__divider"><span></span></div>
                        <div class="package-overlay__location">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="18" viewBox="0 0 12 18">
                                <path d="M6 0.75C3.1084 0.75 0.75 3.1084 0.75 6C0.75 8.63672 2.71289 10.8135 5.25 11.1797V18H6.75V11.1797C9.28711 10.8135 11.25 8.63672 11.25 6C11.25 3.1084 8.8916 0.75 6 0.75ZM6 2.25C8.08008 2.25 9.75 3.91992 9.75 6C9.75 8.08008 8.08008 9.75 6 9.75C3.91992 9.75 2.25 8.08008 2.25 6C2.25 3.91992 3.91992 2.25 6 2.25ZM6 3C4.35059 3 3 4.35059 3 6H4.5C4.5 5.16211 5.16211 4.5 6 4.5V3Z"></path>
                            </svg>
                            <div>
                                <h6><?php echo esc_html($hotel_name); ?></h6>
                                <p><?php echo esc_html($location_line); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bys-package-content">
                    <h4 class="package-content__title"><?php esc_html_e('INCLUDED', 'dhr-hotel-management'); ?></h4>
                    <div>
                        <?php if ($short_desc) : ?><p class="package-content__description"><?php echo esc_html($short_desc); ?></p><?php endif; ?>
                        <ul class="bys-package-features">
                            <?php foreach ($included_list as $line) : ?><li><?php echo esc_html($line); ?></li><?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="package-content__divider"></div>
                    <a href="javascript:void(0)" class="bys-package-button bys-book-now-link"
                        data-hotel-code="<?php echo esc_attr($pkg->hotel_code); ?>"
                        data-channel-id="<?php echo esc_attr($channel_id); ?>"
                        data-checkin="<?php echo esc_attr($book_now_checkin); ?>"
                        data-checkout="<?php echo esc_attr($book_now_checkout); ?>"
                        data-adults="2"
                        data-children="0"
                        data-rooms="1"><?php esc_html_e('View Package', 'dhr-hotel-management'); ?></a>
                </div>
            </div>
            <?php
        };
        ?>

        <div class="first-packages__featured">
            <?php $render_package_card($featured_item, true); ?>
        </div>

        <?php if (!empty($slider_items)) : ?>
            <div class="first-packages__design swiper package-first-swiper">
                <div class="swiper-wrapper">
                    <?php foreach ($slider_items as $item) : ?>
                        <div class="swiper-slide">
                            <?php $render_package_card($item, false); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="package-swiper-pagination first-package-pagination"></div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php if (!empty($packages) && count($packages) > 1) : ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var packageSwiper = new Swiper('.package-first-swiper', {
            slidesPerView: 1,
            spaceBetween: 15,
            loop: false,
            navigation: false,
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },
            speed: 1500,
            pagination: {
                el: '.first-package-pagination',
                clickable: true,
                bulletClass: 'package-swiper-pagination-bullet',
                bulletActiveClass: 'package-swiper-pagination-bullet-active',
            },
            breakpoints: {
                768: { slidesPerView: 2, spaceBetween: 20, pagination: false },
                1024: { slidesPerView: 3, spaceBetween: 25, pagination: false },
                1280: { slidesPerView: 3, spaceBetween: 32, pagination: false }
            }
        });
    });
</script>
<?php endif; ?>
