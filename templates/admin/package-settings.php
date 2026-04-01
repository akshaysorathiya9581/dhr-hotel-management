<?php
/**
 * Package Settings – category-wise shortcode generator
 */

if (!defined('ABSPATH')) {
    exit;
}

$categories = isset($categories) ? $categories : array();
$designs = array(
    'first_home_design' => array(
        'label' => __('First Home Design', 'dhr-hotel-management'),
        'desc'  => __('Same as First Design layout and content output', 'dhr-hotel-management'),
    ),
    'first_design' => array(
        'label' => __('First Design', 'dhr-hotel-management'),
        'desc'  => __('Grid / Swiper cards with icon, overlay info, and included list', 'dhr-hotel-management'),
    ),
    'second_design' => array(
        'label' => __('Second Design', 'dhr-hotel-management'),
        'desc'  => __('Swiper cards with overlay, mobile view-package button', 'dhr-hotel-management'),
    ),
    'kids_design' => array(
        'label' => __('Kids Design', 'dhr-hotel-management'),
        'desc'  => __('Compact swiper cards with overlay button', 'dhr-hotel-management'),
    ),
    'early_bird_design' => array(
        'label' => __('Early Bird Design', 'dhr-hotel-management'),
        'desc'  => __('Premium badge cards with validity dates', 'dhr-hotel-management'),
    ),
);
?>

<div class="wrap dhr-hotel-admin">
    <h1 class="wp-heading-inline"><?php esc_html_e('Package Settings', 'dhr-hotel-management'); ?></h1>
    <p class="description" style="margin-top:6px; font-size:13px; max-width:700px;"><?php esc_html_e('Choose one or more categories and a display design, then generate a shortcode. Paste the shortcode on any page to display packages from the selected categories.', 'dhr-hotel-management'); ?></p>

    <div style="display:flex; flex-wrap:wrap; gap:24px; margin-top:24px; align-items:flex-start;">

        <!-- Categories Card -->
        <div style="flex:1; min-width:340px; max-width:480px; background:#fff; border:1px solid #c3c4c7; border-radius:6px; box-shadow:0 1px 3px rgba(0,0,0,.06);">
            <div style="padding:16px 20px; border-bottom:1px solid #e2e4e7; display:flex; align-items:center; justify-content:space-between;">
                <h2 style="margin:0; font-size:15px; font-weight:600;"><?php esc_html_e('Select Categories', 'dhr-hotel-management'); ?></h2>
                <?php if (!empty($categories)) : ?>
                    <label style="display:inline-flex; align-items:center; gap:6px; font-size:12px; font-weight:500; color:#2271b1; cursor:pointer; user-select:none;">
                        <input type="checkbox" id="dhr-pkg-select-all" style="margin:0;">
                        <?php esc_html_e('Select All', 'dhr-hotel-management'); ?>
                    </label>
                <?php endif; ?>
            </div>
            <div style="padding:16px 20px;">
                <?php if (empty($categories)) : ?>
                    <p style="color:#787c82; margin:0;"><?php esc_html_e('No categories found. Add categories from Category List first.', 'dhr-hotel-management'); ?></p>
                <?php else : ?>
                    <div style="display:flex; flex-direction:column; gap:10px;">
                        <?php foreach ($categories as $cat) :
                            $cat_icon_type = isset($cat->icon_type) ? $cat->icon_type : 'url';
                        ?>
                            <label style="display:flex; align-items:center; gap:10px; padding:8px 12px; border:1px solid #e2e4e7; border-radius:5px; cursor:pointer; transition:all .15s;" class="dhr-pkg-cat-label">
                                <input type="checkbox" class="dhr-package-settings-category" name="dhr_package_categories[]" value="<?php echo esc_attr($cat->id); ?>" style="margin:0;">
                                <span style="display:inline-flex; width:28px; height:28px; align-items:center; justify-content:center; flex-shrink:0; background:#f0f6fc; border-radius:4px; overflow:hidden;">
                                    <?php if ($cat_icon_type === 'svg' && !empty($cat->icon_svg)) : ?>
                                        <span style="display:inline-flex; width:20px; height:20px;"><?php echo $cat->icon_svg; ?></span>
                                    <?php elseif (!empty($cat->icon_url)) : ?>
                                        <img src="<?php echo esc_url($cat->icon_url); ?>" alt="" style="width:20px; height:20px; object-fit:contain;">
                                    <?php else : ?>
                                        <span class="dashicons dashicons-category" style="font-size:16px; width:16px; height:16px; color:#2271b1;"></span>
                                    <?php endif; ?>
                                </span>
                                <span style="font-size:13px; font-weight:500; color:#1d2327;"><?php echo esc_html(wp_unslash((string) ($cat->title ?? ''))); ?></span>
                                <?php if ($cat->is_active) : ?>
                                    <span style="margin-left:auto; font-size:10px; padding:2px 6px; background:#d4edda; color:#155724; border-radius:3px; font-weight:600; text-transform:uppercase;"><?php esc_html_e('Active', 'dhr-hotel-management'); ?></span>
                                <?php else : ?>
                                    <span style="margin-left:auto; font-size:10px; padding:2px 6px; background:#f8d7da; color:#721c24; border-radius:3px; font-weight:600; text-transform:uppercase;"><?php esc_html_e('Inactive', 'dhr-hotel-management'); ?></span>
                                <?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="description" style="margin-top:10px; font-size:11.5px;"><?php esc_html_e('Leave all unchecked to show packages from all categories.', 'dhr-hotel-management'); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Display Design Card -->
        <div style="flex:1; min-width:340px; max-width:540px; background:#fff; border:1px solid #c3c4c7; border-radius:6px; box-shadow:0 1px 3px rgba(0,0,0,.06);">
            <div style="padding:16px 20px; border-bottom:1px solid #e2e4e7;">
                <h2 style="margin:0; font-size:15px; font-weight:600;"><?php esc_html_e('Display Design', 'dhr-hotel-management'); ?></h2>
            </div>
            <div style="padding:16px 20px;">
                <div style="display:grid; grid-template-columns:repeat(2, 1fr); gap:12px;">
                    <?php
                    $design_svgs = array(
                        'first_design' => '<svg viewBox="0 0 120 80" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="1" y="1" width="118" height="78" rx="4" fill="#f7f9fc" stroke="#d0d5dd"/><rect x="6" y="6" width="33" height="42" rx="3" fill="#e3ecf7"/><rect x="43" y="6" width="33" height="42" rx="3" fill="#e3ecf7"/><rect x="80" y="6" width="33" height="42" rx="3" fill="#e3ecf7"/><rect x="8" y="34" width="16" height="3" rx="1" fill="#2271b1"/><rect x="8" y="39" width="26" height="2" rx="1" fill="#8c9bab"/><rect x="45" y="34" width="16" height="3" rx="1" fill="#2271b1"/><rect x="45" y="39" width="26" height="2" rx="1" fill="#8c9bab"/><rect x="82" y="34" width="16" height="3" rx="1" fill="#2271b1"/><rect x="82" y="39" width="26" height="2" rx="1" fill="#8c9bab"/><rect x="6" y="52" width="33" height="6" rx="2" fill="#d4edda"/><rect x="43" y="52" width="33" height="6" rx="2" fill="#d4edda"/><rect x="80" y="52" width="33" height="6" rx="2" fill="#d4edda"/><circle cx="12" cy="12" r="4" fill="#bfd5ef"/><circle cx="49" cy="12" r="4" fill="#bfd5ef"/><circle cx="86" cy="12" r="4" fill="#bfd5ef"/><rect x="6" y="62" width="33" height="12" rx="2" fill="#fff" stroke="#e2e4e7"/><rect x="43" y="62" width="33" height="12" rx="2" fill="#fff" stroke="#e2e4e7"/><rect x="80" y="62" width="33" height="12" rx="2" fill="#fff" stroke="#e2e4e7"/><rect x="10" y="65" width="20" height="2" rx="1" fill="#c3c4c7"/><rect x="10" y="69" width="14" height="2" rx="1" fill="#c3c4c7"/><rect x="47" y="65" width="20" height="2" rx="1" fill="#c3c4c7"/><rect x="47" y="69" width="14" height="2" rx="1" fill="#c3c4c7"/><rect x="84" y="65" width="20" height="2" rx="1" fill="#c3c4c7"/><rect x="84" y="69" width="14" height="2" rx="1" fill="#c3c4c7"/></svg>',
                        'second_design' => '<svg viewBox="0 0 120 80" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="1" y="1" width="118" height="78" rx="4" fill="#f7f9fc" stroke="#d0d5dd"/><rect x="6" y="6" width="33" height="50" rx="3" fill="#dfe8f3"/><rect x="43" y="6" width="33" height="50" rx="3" fill="#dfe8f3"/><rect x="80" y="6" width="33" height="50" rx="3" fill="#dfe8f3"/><rect x="8" y="40" width="16" height="3" rx="1" fill="#2271b1"/><rect x="8" y="45" width="28" height="2" rx="1" fill="#8c9bab"/><rect x="8" y="49" width="22" height="2" rx="1" fill="#8c9bab"/><rect x="45" y="40" width="16" height="3" rx="1" fill="#2271b1"/><rect x="45" y="45" width="28" height="2" rx="1" fill="#8c9bab"/><rect x="45" y="49" width="22" height="2" rx="1" fill="#8c9bab"/><rect x="82" y="40" width="16" height="3" rx="1" fill="#2271b1"/><rect x="82" y="45" width="28" height="2" rx="1" fill="#8c9bab"/><rect x="82" y="49" width="22" height="2" rx="1" fill="#8c9bab"/><circle cx="12" cy="12" r="4" fill="#bfd5ef"/><circle cx="49" cy="12" r="4" fill="#bfd5ef"/><circle cx="86" cy="12" r="4" fill="#bfd5ef"/><rect x="6" y="60" width="33" height="14" rx="2" fill="#fff" stroke="#e2e4e7"/><rect x="43" y="60" width="33" height="14" rx="2" fill="#fff" stroke="#e2e4e7"/><rect x="80" y="60" width="33" height="14" rx="2" fill="#fff" stroke="#e2e4e7"/><rect x="10" y="64" width="20" height="2" rx="1" fill="#c3c4c7"/><rect x="10" y="68" width="25" height="2" rx="1" fill="#c3c4c7"/><rect x="47" y="64" width="20" height="2" rx="1" fill="#c3c4c7"/><rect x="47" y="68" width="25" height="2" rx="1" fill="#c3c4c7"/><rect x="84" y="64" width="20" height="2" rx="1" fill="#c3c4c7"/><rect x="84" y="68" width="25" height="2" rx="1" fill="#c3c4c7"/></svg>',
                        'kids_design' => '<svg viewBox="0 0 120 80" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="1" y="1" width="118" height="78" rx="4" fill="#fef9f0" stroke="#d0d5dd"/><rect x="6" y="6" width="33" height="68" rx="3" fill="#fce8cc"/><rect x="43" y="6" width="33" height="68" rx="3" fill="#fce8cc"/><rect x="80" y="6" width="33" height="68" rx="3" fill="#fce8cc"/><rect x="8" y="50" width="16" height="3" rx="1" fill="#d97706"/><rect x="8" y="55" width="28" height="2" rx="1" fill="#92742e"/><rect x="45" y="50" width="16" height="3" rx="1" fill="#d97706"/><rect x="45" y="55" width="28" height="2" rx="1" fill="#92742e"/><rect x="82" y="50" width="16" height="3" rx="1" fill="#d97706"/><rect x="82" y="55" width="28" height="2" rx="1" fill="#92742e"/><circle cx="12" cy="12" r="4" fill="#f5d9a8"/><circle cx="49" cy="12" r="4" fill="#f5d9a8"/><circle cx="86" cy="12" r="4" fill="#f5d9a8"/><rect x="10" y="63" width="25" height="7" rx="3" fill="#d97706"/><rect x="47" y="63" width="25" height="7" rx="3" fill="#d97706"/><rect x="84" y="63" width="25" height="7" rx="3" fill="#d97706"/></svg>',
                        'early_bird_design' => '<svg viewBox="0 0 120 80" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="1" y="1" width="118" height="78" rx="4" fill="#f7f9fc" stroke="#d0d5dd"/><rect x="6" y="6" width="52" height="68" rx="3" fill="#e3ecf7"/><rect x="62" y="6" width="52" height="68" rx="3" fill="#e3ecf7"/><rect x="8" y="8" width="20" height="8" rx="2" fill="#d4aa70"/><rect x="64" y="8" width="20" height="8" rx="2" fill="#d4aa70"/><rect x="8" y="50" width="24" height="4" rx="1" fill="#1d2327"/><rect x="8" y="56" width="44" height="2" rx="1" fill="#8c9bab"/><rect x="8" y="60" width="36" height="2" rx="1" fill="#8c9bab"/><rect x="8" y="66" width="18" height="2" rx="1" fill="#c3c4c7"/><rect x="64" y="50" width="24" height="4" rx="1" fill="#1d2327"/><rect x="64" y="56" width="44" height="2" rx="1" fill="#8c9bab"/><rect x="64" y="60" width="36" height="2" rx="1" fill="#8c9bab"/><rect x="64" y="66" width="18" height="2" rx="1" fill="#c3c4c7"/></svg>',
                    );
                    $idx = 0;
                    foreach ($designs as $value => $info) :
                        $is_first = ($idx === 0);
                    ?>
                        <label class="dhr-design-option <?php echo $is_first ? 'dhr-design-selected' : ''; ?>" style="display:flex; flex-direction:column; border:2px solid <?php echo $is_first ? '#2271b1' : '#e2e4e7'; ?>; border-radius:6px; cursor:pointer; transition:all .15s; overflow:hidden; background:#fff;">
                            <input type="radio" name="dhr_package_design" value="<?php echo esc_attr($value); ?>" <?php checked($is_first); ?> style="display:none;">
                            <div style="padding:10px 12px; background:#f9fafb; border-bottom:1px solid #e2e4e7; display:flex; align-items:center; justify-content:center;">
                                <span style="display:block; width:100%; max-width:140px;"><?php echo isset($design_svgs[$value]) ? $design_svgs[$value] : $design_svgs['first_design']; ?></span>
                            </div>
                            <div style="padding:10px 12px;">
                                <span style="font-size:12.5px; font-weight:600; color:#1d2327; display:block;"><?php echo esc_html($info['label']); ?></span>
                                <span style="font-size:11px; color:#787c82; line-height:1.4; display:block; margin-top:3px;"><?php echo esc_html($info['desc']); ?></span>
                            </div>
                        </label>
                    <?php $idx++; endforeach; ?>
                </div>
            </div>
        </div>

    </div>

    <!-- Generate Section -->
    <div style="margin-top:24px; max-width:1044px;">
        <button type="button" id="dhr-package-settings-generate" class="button button-primary button-hero" style="min-width:200px;"><?php esc_html_e('Generate Shortcode', 'dhr-hotel-management'); ?></button>

        <div id="dhr-package-settings-output" style="display:none; margin-top:16px; padding:16px 20px; background:#f0f6fc; border:1px solid #c3c4c7; border-left:4px solid #2271b1; border-radius:4px;">
            <label style="display:block; margin-bottom:8px; font-weight:600; font-size:13px;"><?php esc_html_e('Generated Shortcode', 'dhr-hotel-management'); ?></label>
            <div style="display:flex; gap:8px; align-items:center;">
                <input type="text" id="dhr-package-settings-shortcode" class="large-text" readonly style="background:#fff; font-family:monospace; font-size:13px; padding:8px 12px;">
                <button type="button" id="dhr-package-settings-copy" class="button" style="white-space:nowrap; min-width:80px;">
                    <span class="dashicons dashicons-admin-page" style="font-size:16px; width:16px; height:16px; vertical-align:middle; margin-right:2px;"></span>
                    <?php esc_html_e('Copy', 'dhr-hotel-management'); ?>
                </button>
            </div>
            <p class="description" style="margin-top:8px; font-size:11.5px;"><?php esc_html_e('Paste this shortcode into any page or post to display the packages.', 'dhr-hotel-management'); ?></p>
        </div>
    </div>
</div>

<style>
.dhr-pkg-cat-label:hover {
    background: #f0f6fc;
    border-color: #2271b1 !important;
}
.dhr-pkg-cat-label:has(input:checked) {
    background: #f0f6fc;
    border-color: #2271b1 !important;
    box-shadow: 0 0 0 1px #2271b1;
}
.dhr-design-option:hover {
    border-color: #2271b1 !important;
}
.dhr-design-selected {
    box-shadow: 0 0 0 1px #2271b1;
}
.dhr-design-option svg {
    width: 100%;
    height: auto;
}
.dhr-pkg-cat-label .dashicons {
    line-height: 16px;
}
</style>

<script>
(function() {
    var generateBtn = document.getElementById('dhr-package-settings-generate');
    var outputBox = document.getElementById('dhr-package-settings-output');
    var shortcodeInput = document.getElementById('dhr-package-settings-shortcode');
    var copyBtn = document.getElementById('dhr-package-settings-copy');
    var selectAllCb = document.getElementById('dhr-pkg-select-all');

    // Select All toggle
    if (selectAllCb) {
        selectAllCb.addEventListener('change', function() {
            var checkboxes = document.querySelectorAll('.dhr-package-settings-category');
            checkboxes.forEach(function(cb) { cb.checked = selectAllCb.checked; });
        });
        document.querySelectorAll('.dhr-package-settings-category').forEach(function(cb) {
            cb.addEventListener('change', function() {
                var all = document.querySelectorAll('.dhr-package-settings-category');
                var checked = document.querySelectorAll('.dhr-package-settings-category:checked');
                selectAllCb.checked = (all.length === checked.length);
                selectAllCb.indeterminate = (checked.length > 0 && checked.length < all.length);
            });
        });
    }

    // Design selection
    document.querySelectorAll('.dhr-design-option input[type="radio"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.dhr-design-option').forEach(function(opt) {
                opt.classList.remove('dhr-design-selected');
                opt.style.borderColor = '#e2e4e7';
                opt.style.boxShadow = 'none';
            });
            if (radio.checked) {
                var label = radio.closest('.dhr-design-option');
                label.classList.add('dhr-design-selected');
                label.style.borderColor = '#2271b1';
                label.style.boxShadow = '0 0 0 1px #2271b1';
            }
        });
    });

    // Generate shortcode
    if (generateBtn && outputBox && shortcodeInput) {
        generateBtn.addEventListener('click', function() {
            var checked = document.querySelectorAll('.dhr-package-settings-category:checked');
            var ids = [];
            checked.forEach(function(cb) { ids.push(cb.value); });
            var designRadio = document.querySelector('.dhr-design-option input[type="radio"]:checked');
            var design = designRadio ? designRadio.value : 'first_design';
            var atts = [];
            if (ids.length > 0) {
                atts.push('categories="' + ids.join(',') + '"');
            }
            atts.push('design="' + design + '"');
            var shortcode = '[dhr_packages ' + atts.join(' ') + ']';
            shortcodeInput.value = shortcode;
            outputBox.style.display = 'block';
            outputBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    }

    // Copy to clipboard
    if (copyBtn && shortcodeInput) {
        copyBtn.addEventListener('click', function() {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(shortcodeInput.value).then(function() {
                    showCopied();
                });
            } else {
                shortcodeInput.select();
                document.execCommand('copy');
                showCopied();
            }
        });
        function showCopied() {
            var origHTML = copyBtn.innerHTML;
            copyBtn.innerHTML = '<span class="dashicons dashicons-yes" style="font-size:16px; width:16px; height:16px; vertical-align:middle; color:#00a32a;"></span> <?php echo esc_js(__('Copied!', 'dhr-hotel-management')); ?>';
            copyBtn.style.borderColor = '#00a32a';
            copyBtn.style.color = '#00a32a';
            setTimeout(function() {
                copyBtn.innerHTML = origHTML;
                copyBtn.style.borderColor = '';
                copyBtn.style.color = '';
            }, 2000);
        }
    }
})();
</script>
