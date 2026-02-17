<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
// Ensure the file is being accessed through the WordPress admin area
if (!defined('ABSPATH')) {
    die;
}

$form_mask_installed_date = get_option('fme-installDate');
$conditional_fields_installed_date = get_option('cfef-installDate');
$conditional_fields_pro_installed_date = get_option('cfefp-installDate');
$country_code_installed_date = get_option('ccfef-installDate');

$stored_oldest_plugin = get_option('oldest_plugin');

$plugins_dates = [
    'fim_plugin'  => $form_mask_installed_date,
    'cfef_plugin' => $conditional_fields_installed_date,
    'cfefp_plugin' => $conditional_fields_pro_installed_date,
    'ccfef_plugin' => $country_code_installed_date,
];

$plugins_dates = array_filter($plugins_dates);

$install_by_plugin = get_option('conditional-fields-install-by');

if(! empty( $install_by_plugin )){
    $first_plugin = $install_by_plugin;
}
else if ( ! empty( $stored_oldest_plugin ) ) {
    $first_plugin = $stored_oldest_plugin;

}else{

    if (!empty($plugins_dates)) {
        asort($plugins_dates);
        $first_plugin = key($plugins_dates);
    } else {
        $first_plugin = 'cfef_plugin';
    }

    // Store it so it never changes on re-install
    update_option('oldest_plugin', $first_plugin);
}




?>
<div class="cfkef-license-box">
    <div class="wrapper-header">
        <div class="cfkef-save-all">
            <div class="cfkef-title-desc">
                <h2><?php esc_html_e('License Key','conditional-fields-for-elementor-form'); ?></h2>
            </div>
            <div class="cfkef-save-controls">
                <span><?php esc_html_e('Free','conditional-fields-for-elementor-form'); ?></span>
                <a class="button button-primary upgrade-pro-btn" target="_blank" href="https://coolformkit.com/pricing/?utm_source=<?php echo esc_attr($first_plugin); ?>&utm_medium=inside&utm_campaign=get_pro&utm_content=license_dashboard">
                    <img class="crown-diamond-pro" src="<?php echo esc_url(CFEF_PLUGIN_URL . 'assets/images/crown-diamond-pro.png'); ?>" alt="Cool FormKit Logo">
                    <?php esc_html_e('Upgrade To Pro','conditional-fields-for-elementor-form'); ?>
                </a>
            </div>
        </div>
    </div>
    <div class="wrapper-body">
        <p><?php esc_html_e('Your license key provides access to pro version updates and support.','conditional-fields-for-elementor-form'); ?></p>
        <p><?php esc_html_e('You\'re using ','conditional-fields-for-elementor-form'); ?><strong><?php esc_html_e('free plugin ','conditional-fields-for-elementor-form'); ?></strong><?php esc_html_e('- no license needed. Enjoy! 😊','conditional-fields-for-elementor-form'); ?></p>
        <div class="cfkef-license-upgrade-box">
            <p><?php esc_html_e('To unlock more features, consider ','conditional-fields-for-elementor-form'); ?><a href="https://coolformkit.com/pricing/?utm_source=<?php echo esc_attr($first_plugin); ?>&utm_medium=inside&utm_campaign=get_pro&utm_content=license_dashboard" target="_blank"><?php esc_html_e('upgrading to Pro','conditional-fields-for-elementor-form'); ?></a>.
            </p>
            <em><?php esc_html_e('As a valued user, you automatically receive an exclusive discount on the Annual License and an even greater discount on the POPULAR Lifetime License at checkout!','conditional-fields-for-elementor-form'); ?></em>
        </div>


        <div class="pro-plugin-buy cfkef-license-upgrade-box">

            <p><?php esc_html_e('Have you purchased pro plugin?','conditional-fields-for-elementor-form'); ?></p>



            <div class="pro-plugin-buy-buttons-con">

                <a class="button button-primary pro-bought-btn" target="_blank" href="https://my.coolplugins.net/account/downloads">
                    <?php esc_html_e('Yes','conditional-fields-for-elementor-form'); ?>
                </a>


                <a class="button button-primary no-pro-buy-btn" target="_blank" href="https://coolformkit.com/pricing/?utm_source=<?php echo esc_attr($first_plugin); ?>&utm_medium=inside&utm_campaign=get_pro&utm_content=license_dashboard">
                    <?php esc_html_e('No','conditional-fields-for-elementor-form'); ?>
                </a>
            </div>

            <em>If you've purchased the Pro plugin, download it from <a href="https://my.coolplugins.net/account/downloads">my.coolplugins.net</a>, deactivate the free plugin, then install and activate the Pro version along with the license.</em>

        </div>

    </div>
</div>