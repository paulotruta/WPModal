/* globals tinymce */

'use strict';

(function($){

	console.log("Hello plugin javascript!");

	tinymce.PluginManager.requireLangPack('wpmodal', 'pt_PT');

	tinymce.PluginManager.add('wpmodal', function(editor, url) {

		var $this = this;

        editor.addButton('wpmodal', {
			type: 'button',
			icon: true,
			title: editor.getLang('wpmodal.insertModalTitle'),
			image: url + '/../img/tinymce_btn_icon.png',
			onclick: function (e) {
				editor.windowManager.open( {
					title: editor.getLang('wpmodal.insertModalTitle'),
					inline: 'yes',
					body: [
						{
							type: 'label',
							name: 'helpText',
							text: editor.getLang('wpmodal.modalHelpText'),
						},
						{
							type: 'textbox',
							name: 'modal-tag',
							classes: 'modal-tag',
							minWidth: 300,
							label: editor.getLang('wpmodal.modalLabelHtmlWrapperTag'),
						},
						{
							type: 'textbox',
							name: 'modal-classes',
							classes: 'modal-classes',
							minWidth: 300,
							label: editor.getLang('wpmodal.modalLabelCSSWrapperClasses'),
						},
						{
							type: 'textbox',
							name: 'modal-label',
							classes: 'modal-label',
							minWidth: 300,
							label: editor.getLang('wpmodal.modalLabelWrapperLabel'),
						},
						{
							type: 'textbox',
							name: 'modal-title',
							classes: 'modal-title',
							minWidth: 300,
							label: editor.getLang('wpmodal.modalLabelTitle'),
						},
						{
							type: 'button',
							text: editor.getLang('wpmodal.modalPictureBtnText'),
							name: 'modal-picture-btn',
							classes: 'modal-picture-btn',
							minWidth: 150,
							maxWidth: 200,
							label: editor.getLang('wpmodal.modalLabelPicture'),
							onclick: function() {

								// Start the media gallery window.
								var gallery_window = wp.media({
									title: editor.getLang('wpmodal.mediaGalleryTitle'),
									library: {type: 'image'},
									multiple: false, // One 1 picture allowed.
									button: {text: 'Choose'}
								});

								// Before issuing the media gallery opening command, we must first register the selection callback in order to get the user picture selection.
								gallery_window.on('select', function() {
									// Although the selection outputs multiple values, in this scenario we only want to fetch the "first()" value.
									var user_selection = gallery_window.state().get('selection').first().toJSON();

									// user_selection breaks down into an object.
									// Clean the current preview, and populate the picture preview placeholder, and the shortcode corresponding field.
									$('.mce-modal-picture-preview').remove();
									$('.mce-modal-picture-btn').after(
										'<img class="mce-modal-picture-preview" src="' + user_selection.url + '">'
									);
									$('.mce-modal-picture').first().val(user_selection.id);

								});

								gallery_window.open();

							}
						},
						{
							type: 'textbox', // The hidden textbox that will hold the value for the picture id.
							name: 'modal-picture',
							classes: 'modal-picture',
							minWidth: 300,
						},
					],
					onsubmit: function( e ) {
						var tag = 'tag="' + $('.mce-modal-tag').first().val() + '" ';
						var classes = 'classes="' + $('.mce-modal-classes').first().val() + '" ';
						var label = 'label="' + $('.mce-modal-label').first().val() + '" ';
						var modal_title = 'modal_title="' + $('.mce-modal-title').first().val() + '" ';
						var modal_picture = 'modal_picture="' + $('.mce-modal-picture').first().val() + '" ';
						var selected_data = tinyMCE.activeEditor.selection.getContent();
						var opening_shortcode = '[unimodal ' + tag + classes + label + modal_title + modal_picture + ']';
						var closing_shortcode = '[/unimodal]';

						var final_shortcode = opening_shortcode;
						if( selected_data ){
							final_shortcode = opening_shortcode + selected_data + closing_shortcode;
						}

						editor.insertContent( final_shortcode );
					}
				});
			}}
		);
	});
})(jQuery);
