<?php
if (!defined('ABSPATH')) exit;

// Handle the form submission for clearing the log
if (isset($_POST['wcps_action']) && $_POST['wcps_action'] === 'clear_failed_log' && isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'wcps_clear_failed_log_nonce')) {
    delete_option('wcps_failed_scrapes');
    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('لیست خطاهای اسکرپ با موفقیت پاک شد.', 'wc-price-scraper') . '</p></div>';
}
?>
<div class="wrap wcps-settings-wrap">
    <h1><?php esc_html_e('تنظیمات اسکرپر قیمت ووکامرس', 'wc-price-scraper'); ?></h1>
    <form method="post" action="options.php">
        <?php settings_fields('wc_price_scraper_group'); ?>

        <div class="postbox">
            <h2 class="hndle"><span><?php esc_html_e('بخش ۱: پنهان‌سازی و ادغام ویژگی‌ها', 'wc-price-scraper'); ?></span></h2>
            <div class="inside">
                <p class="description"><?php esc_html_e('ویژگی‌هایی که در اینجا وارد می‌کنید (مثل گارانتی) همیشه از دید کاربر پنهان شده و در فرآیند ادغام متغیرهای مشابه استفاده می‌شوند.', 'wc-price-scraper'); ?></p>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="wcps_always_hide_keys"><?php esc_html_e('کلید ویژگی‌ها برای پنهان‌سازی', 'wc-price-scraper'); ?></label></th>
                        <td>
                            <textarea id="wcps_always_hide_keys" name="wcps_always_hide_keys" rows="4" class="large-text code" placeholder="<?php esc_attr_e('هر کلید را در یک خط وارد کنید. مثال: pa_guarantee', 'wc-price-scraper'); ?>"><?php echo esc_textarea(get_option('wcps_always_hide_keys', '')); ?></textarea>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="postbox">
            <h2 class="hndle"><span><?php esc_html_e('بخش ۲: قوانین حذف شرطی متغیرها', 'wc-price-scraper'); ?></span></h2>
            <div class="inside">
                <p class="description"><?php esc_html_e('قوانینی برای حذف متغیرها بر اساس مقدار یک ویژگی خاص تعریف کنید. اگر قانونی باعث شود هیچ متغیری باقی نماند، اجرا نخواهد شد.', 'wc-price-scraper'); ?></p>
                <table class="form-table wcps-repeater-table" id="wcps-conditional-rules-table">
                    <tbody id="wcps-rules-container">
                        <?php
                        $conditional_rules = get_option('wcps_conditional_rules', [['key' => '', 'value' => '']]);
                        foreach ($conditional_rules as $i => $rule) :
                        ?>
                        <tr valign="top" class="wcps-rule-row">
                            <td>
                                <label><?php esc_html_e('اگر ویژگی', 'wc-price-scraper'); ?></label>
                                <input type="text" name="wcps_conditional_rules[<?php echo $i; ?>][key]" value="<?php echo esc_attr($rule['key']); ?>" placeholder="مثال: pa_location" class="regular-text" />
                            </td>
                            <td>
                                <label><?php esc_html_e('برابر بود با', 'wc-price-scraper'); ?></label>
                                <input type="text" name="wcps_conditional_rules[<?php echo $i; ?>][value]" value="<?php echo esc_attr($rule['value']); ?>" placeholder="مثال: فروشگاه تهران" class="regular-text" />
                            </td>
                            <td class="wcps-repeater-action">
                                <button type="button" class="button button-link-delete wcps-remove-rule" title="<?php esc_attr_e('حذف این قانون', 'wc-price-scraper'); ?>"><span class="dashicons dashicons-trash"></span></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr><td colspan="3"><button type="button" class="button" id="wcps-add-rule"><span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e('افزودن قانون جدید', 'wc-price-scraper'); ?></button></td></tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="postbox">
            <h2 class="hndle"><span><?php esc_html_e('تنظیمات قیمت‌گذاری', 'wc-price-scraper'); ?></span></h2>
            <div class="inside">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="wcps_enable_rounding"><?php esc_html_e('رند کردن قیمت نهایی', 'wc-price-scraper'); ?></label></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" id="wcps_enable_rounding" name="wcps_enable_rounding" value="yes" <?php checked(get_option('wcps_enable_rounding', 'no'), 'yes'); ?>>
                                    <?php esc_html_e('فعال‌سازی', 'wc-price-scraper'); ?>
                                </label>
                                <div id="rounding-details" style="margin-top: 10px; <?php if (get_option('wcps_enable_rounding', 'no') !== 'yes') echo 'display:none;'; ?>">
                                    <select name="wcps_rounding_direction" style="vertical-align: middle;">
                                        <option value="up" <?php selected(get_option('wcps_rounding_direction', 'up'), 'up'); ?>><?php esc_html_e('رند کردن به بالا', 'wc-price-scraper'); ?></option>
                                        <option value="down" <?php selected(get_option('wcps_rounding_direction', 'up'), 'down'); ?>><?php esc_html_e('رند کردن به پایین', 'wc-price-scraper'); ?></option>
                                    </select>
                                    <input type="number" name="wcps_rounding_multiple" value="<?php echo esc_attr(get_option('wcps_rounding_multiple', 1000)); ?>" class="small-text" min="1" step="1" style="vertical-align: middle;">
                                    <p class="description"><?php esc_html_e('قیمت به نزدیک‌ترین مضرب این عدد رند می‌شود (مثال: ۱۰۰۰).', 'wc-price-scraper'); ?></p>
                                </div>
                            </fieldset>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <?php submit_button(); ?>
    </form>
    
    <script>
    jQuery(document).ready(function($) {
        // Toggle for rounding options
        $('#wcps_enable_rounding').on('change', function() {
            $('#rounding-details').slideToggle($(this).is(':checked'));
        });

        // Repeater field logic for conditional rules
        function wcps_reindex_rules() {
            $('#wcps-rules-container .wcps-rule-row').each(function(index) {
                $(this).find('input').each(function() {
                    if (this.name) {
                        this.name = this.name.replace(/\[\d+\]/, '[' + index + ']');
                    }
                });
            });
        }

        $('#wcps-add-rule').on('click', function() {
            var newRow = $('#wcps-rules-container .wcps-rule-row:first').clone();
            newRow.find('input').val('');
            $('#wcps-rules-container').append(newRow);
            wcps_reindex_rules();
        });

        $('#wcps-rules-container').on('click', '.wcps-remove-rule', function(e) {
            e.preventDefault();
            if ($('#wcps-rules-container .wcps-rule-row').length > 1) {
                $(this).closest('.wcps-rule-row').remove();
            } else {
                $(this).closest('.wcps-rule-row').find('input').val('');
            }
            wcps_reindex_rules();
        });
    });
    </script>
</div>