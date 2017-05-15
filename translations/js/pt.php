<?php # -*- coding:  utf-8 -*-
// This file is based on wp-includes/js/tinymce/langs/wp-langs.php

if ( ! defined( 'ABSPATH' ) )
    exit;


if ( ! class_exists( '_WP_Editors' ) )
    require( ABSPATH . WPINC . '/class-wp-editor.php' );

function wpmodal_tinymce_plugin_translation() {
    $strings = array(
        'key' => esc_js( __( 'Translatable String', 'wpmodal' ) ),       
    	'insertModalTitle' => esc_js( __( 'Insert modal', 'wpmodal' ) ),
    	'modalHelpText' => esc_js( __( 'Fill the information regarding the wrapper and content of the modal.', 'wpmodal' ) ),
    	'modalLabelHtmlWrapperTag' => esc_js( __( 'Wrapper Html tag', 'wpmodal' ) ),
    	'modalLabelCSSWrapperClasses' => esc_js( __( 'Wrapper CSS classes', 'wpmodal' ) ),
    	'modalLabelWrapperLabel' => esc_js( __( 'Call-to-action label', 'wpmodal' ) ),
    	'modalLabelTitle' => esc_js( __( 'Modal title', 'wpmodal' ) ),
    	'modalLabelPicture' => esc_js( __( 'Modal Background', 'wpmodal' ) ),
    	'modalPictureBtnText' => esc_js( __( 'Choose Image', 'wpmodal' ) ),
    	'mediaGalleryTitle' => esc_js( __( 'Choose a background image', 'wpmodal' ) ),
        'modalCta' => esc_js( __( 'Call-to-action type', 'wpmodal' ) ),
        'modalAdvanced' => esc_js( __( 'Show advanced options', 'wpmodal' ) ),
        'successIconUrl' => str_replace('/translations', '', plugin_dir_url( dirname(__FILE__) ) . 'assets/img/success_icon.png' ),
    );

    $locale = _WP_Editors::$mce_locale;
    $translated = 'tinyMCE.addI18n("' . $locale . '.wpmodal", ' . json_encode( $strings ) . ");\n";

    return $translated;
}

$strings = wpmodal_tinymce_plugin_translation();
