( function ( $ ) {
	'use strict';

	$( function () {
		$( '.gtvx-color-picker' ).wpColorPicker();

		var mediaFrame;
		var $logoIdInput = $( '#gtvx_logo_id' );
		var $logoPreview = $( '#gtvx_logo_preview' );
		var $removeBtn = $( '#gtvx_remove_logo' );

		$( '#gtvx_select_logo' ).on( 'click', function ( e ) {
			e.preventDefault();

			if ( mediaFrame ) {
				mediaFrame.open();
				return;
			}

			mediaFrame = wp.media( {
				title: window.gtvxAdminL10n ? gtvxAdminL10n.mediaTitle : 'Select Logo',
				button: {
					text: window.gtvxAdminL10n ? gtvxAdminL10n.mediaButton : 'Use this image'
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

		$( '#gtvx_copy_bypass_url' ).on( 'click', function () {
			var $btn = $( this );
			var input = document.getElementById( 'gtvx_bypass_url_input' );
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
			var original = window.gtvxAdminL10n ? gtvxAdminL10n.copyText : 'Copy URL';
			var copied = window.gtvxAdminL10n ? gtvxAdminL10n.copiedText : 'Copied!';
			$btn.text( copied );
			setTimeout( function () {
				$btn.text( original );
			}, 1800 );
		}
	} );
}( jQuery ) );
