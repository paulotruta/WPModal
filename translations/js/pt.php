<?php # -*- coding:  utf-8 -*-
// This file is based on wp-includes/js/tinymce/langs/wp-langs.php

if ( ! defined( 'ABSPATH' ) )
    exit;


if ( ! class_exists( '_WP_Editors' ) )
    require( ABSPATH . WPINC . '/class-wp-editor.php' );

function wpmodal_tinymce_plugin_translation() {
    $strings = array(
        'key' => esc_js( __( 'Translatable String', 'wpmodal' ) ),       
    );

    $locale = _WP_Editors::$mce_locale;
    $translated = 'tinyMCE.addI18n("' . $locale . '.evasoes", ' . json_encode( $strings ) . ");\n";

    return $translated;
}

$strings = wpmodal_tinymce_plugin_translation();
