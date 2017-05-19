<?php
/**
 * The modal template to be outputted to the footer.
 * For use with the modal shortcode.
 *
 * @package wp-modal
 * @var  $i The modal number to uniquely identify it.
 * @var  $atts The various attributes passed in the shortcode.
 */

$allowed_sizes = array(
	'small' => 'modal-sm',
	'large' => 'modal-lg',
);
$size = isset( $allowed_sizes[ $atts['size'] ] ) ? $allowed_sizes[ $atts['size'] ] : '';
$title = empty( $atts['modal_title'] ) ? $atts['label'] : $atts['modal_title'];
?>

<div id="wpmodal-<?php echo esc_attr( $i ); ?>" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="ModalLabel-<?php echo esc_attr( $i ); ?>" aria-hidden="true" style="display: none;">
	<div class="modal-dialog <?php echo esc_attr( $size ); ?>">
		<div class="modal-content">
			<div class="modal-header">
				<a class="close frm_icon_font frm_cancel1_icon alignright" data-dismiss="modal"></a>
				<h3 class="modal-title" id="ModalLabel-<?php echo esc_attr( $i ); ?>"><?php echo esc_attr( "$title" ); ?></h3>
			</div>
			<div class="modal-body">
				<?php
				echo wp_get_attachment_image(
					$atts['modal_picture'],
					'full',
					false,
					array(
						'class' => 'img-responsive',
					)
				);
				
				$shortcode_atts = '';
                foreach ( $atts as $att => $val ) {
                    if ( 'type' != $att ) {
                        $shortcode_atts .= ' ' . sanitize_text_field( $att . '="' . $val . '"' );
                    }
                }
                echo do_shortcode( $atts['inner_content'] ); // Ensures any shortcode contained in the content will be correctly rendered.

				?>
			</div>
		</div>
	</div>
</div>



