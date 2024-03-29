<?php
/**
 * Plugin Name: WP Site Checkout Page Modifications
 * Plugin URI: https://github.com/mmainulhasan/wordpress
 * Description: Modifies default looks of checkout pages.
 * Version: 0.1
 * Author: Mohammad Mainul Hasan (moh.mainul.hasan@gmail.com)
 * Author URI: https://github.com/mmainulhasan
 */

add_filter('woocommerce_checkout_fields', 'wp_override_checkout_fields');

function wp_override_checkout_fields($fields)
{
    // Change billing fields
    $fields['billing']['billing_city']['class'][0] = 'form-row-first';
    $fields['billing']['billing_state']['class'][0] = 'form-row-last';
    unset($fields['billing']['billing_company']);
    $fields['billing']['billing_postcode']['class'][0] = 'form-row-first';
    $fields['billing']['billing_phone']['class'][0] = 'form-row-last';
    $fields['billing']['billing_phone']['label'] = 'Mobile phone';
    $fields['billing']['billing_phone']['required'] = true;

    // Change shipping fields
    $fields['shipping']['shipping_city']['class'][0] = 'form-row-first';
    $fields['shipping']['shipping_state']['class'][0] = 'form-row-last';
    unset($fields['shipping']['shipping_company']);
    $fields['shipping']['shipping_postcode']['class'][0] = 'form-row-wide';

    // Account fields
    $fields['account']['account_password']['label'] = 'Set a password for your account';

    return $fields;
}

add_action('wp_footer', 'reposition_shipping_fields_on_checkout');

function reposition_shipping_fields_on_checkout()
{
    if (is_checkout()) {
        ?>
        <script>
            // Reposition some shipping fields
            var shippingFirstNameField = jQuery('#shipping_first_name_field');
            jQuery('#wp-shipping-detached-fields').append(shippingFirstNameField);

            var shippingLastNameField = jQuery('#shipping_last_name_field');
            jQuery('#wp-shipping-detached-fields').append(shippingLastNameField);

            var shippingPostCodeField = jQuery('#shipping_postcode_field');
            jQuery('#wp-shipping-detached-fields').append(shippingPostCodeField);

            // Selectively hide optional text
            jQuery('.woocommerce-additional-fields span.optional').hide();
            jQuery('#createaccount').prop('checked', true);
        </script>
        <?php
    }
}

add_action('woocommerce_checkout_process', 'wp_enforce_password_min_length');

function wp_enforce_password_min_length()
{
    if (!is_user_logged_in() && strlen(trim($_POST['account_password'])) < 6) {
        wc_add_notice(__('Please enter a <b>password with at least six digits</b>.'), 'error');
    }
}

add_filter('gettext', 'wp_ship_to_different_address_translation', 20, 3);

function wp_ship_to_different_address_translation($translated_text, $text, $domain)
{
    switch ($translated_text) {
        case 'Ship to a different address?' :
            $translated_text = __('Instead of shipping to a FedEx Hold Location; ship FedEx Priority Overnight to a different address for an additional $6.99.', 'woocommerce');
            break;
    }
    return $translated_text;
}

add_filter('woocommerce_states', 'wp_remove_some_us_states');

function wp_remove_some_us_states($states)
{
    $non_allowed_us_states = array('HI', 'AA', 'AE', 'AP');

    // Loop through non-allowed US states and remove them
    foreach ($non_allowed_us_states as $state_code) {
        if (isset($states['US'][$state_code])) {
            unset($states['US'][$state_code]);
        }
    }

    return $states;
}