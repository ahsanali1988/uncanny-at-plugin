<?php
/**
 * Edit account form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-edit-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 2.6.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wc_print_notices();

/**
 * My Account navigation.
 * @since 2.6.0
 */
do_action( 'woocommerce_account_navigation' );
$subscribed         = 0;
$tags               = array();
$user_id            = wp_get_current_user()->ID;
$subscriber_id      = get_user_meta( $user_id, 'activecampaign_contact_id', true );
$subscriber_details = \uncanny_advance_trainings\WoocommerceMods::get_subscriber_details( $subscriber_id );
if ( $subscriber_details ) {
	$subscribed = $subscriber_details['status'];
	$tags       = $subscriber_details['tags'];
}

?>

<div class="woocommerce-MyAccount-content">

	<form class="woocommerce-EditAccountForm edit-account" action="<?php echo get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ); ?>" method="post">

		<p class="form-row terms wc-uo-sign-up-at-newsletter">
			<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
				<input type="checkbox" <?php if ( $subscriber_details && ! empty( $tags ) && is_array( $tags ) && in_array( 'Unsubscribe', $tags ) ) {
					//User is Unsubscribed
				}else{
					echo 'checked="checked"';
				} ?> class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" value="1" name="uo-sign-up-at-newsletter" id="uo-sign-up-at-newsletter"/>
				<span><?php _e( 'OK to send me the Advanced-Trainings Newsletter (articles, techniques, announcements).', 'woocommerce' ) ?></span>
			</label>
		</p>
		<p class="form-row terms wc-marketing-related-emails">
			<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
				<input type="checkbox" <?php if ( $subscriber_details && ! empty( $tags ) && is_array( $tags ) && in_array( 'Send Product Emails', $tags ) ) {
					echo 'checked="checked"';
				} ?> class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" value="1" name="marketing-related-emails" id="marketing-related-emails"/>
				<span><?php _e( 'OK to send me emails related to my purchase (course followup info, product updates, etc).', 'woocommerce' ); ?></span>
			</label>
		</p>

		<p>
			<?php wp_nonce_field( 'save_email_preferences', 'save_email_preferences' ); ?>
			<input type="submit" class="woocommerce-Button button" name="save_account_details" value="<?php esc_attr_e( 'Save changes', 'woocommerce' ); ?>"/>
			<input type="hidden" name="action" value="save_email_preferences"/>
		</p>
	</form>
</div>