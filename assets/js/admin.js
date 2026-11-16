( function ( $ ) {
	'use strict';

	$( function () {
		$( '.smg-color-picker' ).wpColorPicker();

		var mediaFrame;
		var $logoIdInput = $( '#smg_logo_id' );
		var $logoPreview = $( '#smg_logo_preview' );
		var $removeBtn = $( '#smg_remove_logo' );

		$( '#smg_select_logo' ).on( 'click', function ( e ) {
			e.preventDefault();

			if ( mediaFrame ) {
				mediaFrame.open();
				return;
			}

			mediaFrame = wp.media( {
				title: window.smgAdminL10n ? smgAdminL10n.mediaTitle : 'Select Logo',
				button: {
					text: window.smgAdminL10n ? smgAdminL10n.mediaButton : 'Use this image'
				},
				multiple: false,
				library: { type: 'image' }
			} );

			mediaFrame.on( 'select', function () {
				var attachment = mediaFrame.state().get( 'selection' ).first().toJSON();
				var imgUrl = ( attachment.sizes && attachment.sizes.medium )
					? attachment.sizes.medium.url
					: attachment.url;

				$logoIdInput.val( attachment.id );
				$logoPreview.html( $( '<img>', { src: imgUrl, alt: '' } ) );
				$removeBtn.removeClass( 'hidden' );
			} );

			mediaFrame.open();
		} );

		$removeBtn.on( 'click', function ( e ) {
			e.preventDefault();
			$logoIdInput.val( '0' );
			$logoPreview.empty();
			$removeBtn.addClass( 'hidden' );
		} );

		$( '#smg_copy_bypass_url' ).on( 'click', function () {
			var $btn = $( this );
			var input = document.getElementById( 'smg_bypass_url_input' );
			if ( ! input ) {
				return;
			}

			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				navigator.clipboard.writeText( input.value ).then( function () {
					showCopiedState( $btn );
				} );
			} else {
				input.select();
				document.execCommand( 'copy' );
				showCopiedState( $btn );
			}
		} );

		function showCopiedState( $btn ) {
			var original = window.smgAdminL10n ? smgAdminL10n.copyText : 'Copy URL';
			var copied = window.smgAdminL10n ? smgAdminL10n.copiedText : 'Copied!';
			$btn.text( copied );
			setTimeout( function () {
				$btn.text( original );
			}, 1800 );
		}
	} );
}( jQuery ) );
