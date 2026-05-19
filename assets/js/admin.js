/* global jQuery */
(function ($) {
	'use strict';

	function updateRepeaterIndexes($repeater) {
		$repeater.find( '.jr-content-admin-repeater-row' ).each(
			function (index) {
				$( this ).attr( 'data-row-index', index );
			}
		);
		$repeater.attr( 'data-next-index', $repeater.find( '.jr-content-admin-repeater-row' ).length );
	}

	function createRowFromTemplate($repeater) {
		var templateId = $repeater.data( 'template-id' );
		var nextIndex  = parseInt( $repeater.attr( 'data-next-index' ), 10 ) || 0;
		var template   = $( '#' + templateId ).html();

		if ( ! template) {
			return $();
		}

		template = template.replace( /__INDEX__/g, nextIndex );
		return $( template );
	}

	function openMediaFrame($button) {
		var $row  = $button.closest( '.jr-content-admin-repeater-row' );
		var frame = wp.media(
			{
				title: jrContentAdmin.selectImage,
				button: { text: jrContentAdmin.useImage },
				library: { type: 'image' },
				multiple: false
			}
		);

		frame.on(
			'select',
			function () {
				var attachment = frame.state().get( 'selection' ).first().toJSON();
				$row.find( '.jr-content-admin-media-id' ).val( attachment.id );
				$row.find( '.jr-content-admin-image-preview' ).html( '<img src="' + attachment.url + '" alt="">' );
			}
		);

		frame.open();
	}

	$(
		function () {
			$( document ).on(
				'click',
				'.jr-content-admin-add-row',
				function () {
					var $repeater = $( this ).closest( '.jr-content-admin-repeater' );
					var $rows     = $repeater.find( '.jr-content-admin-repeater-rows' );
					var $row      = createRowFromTemplate( $repeater );

					if ( ! $row.length) {
						return;
					}

					$rows.append( $row );
					updateRepeaterIndexes( $repeater );
				}
			);

			$( document ).on(
				'click',
				'.jr-content-admin-remove-row',
				function () {
					var $repeater = $( this ).closest( '.jr-content-admin-repeater' );
					$( this ).closest( '.jr-content-admin-repeater-row' ).remove();
					updateRepeaterIndexes( $repeater );
				}
			);

			$( document ).on(
				'click',
				'.jr-content-admin-move-up',
				function () {
					var $row      = $( this ).closest( '.jr-content-admin-repeater-row' );
					var $previous = $row.prev( '.jr-content-admin-repeater-row' );
					var $repeater = $( this ).closest( '.jr-content-admin-repeater' );

					if ($previous.length) {
						$row.insertBefore( $previous );
						updateRepeaterIndexes( $repeater );
					}
				}
			);

			$( document ).on(
				'click',
				'.jr-content-admin-move-down',
				function () {
					var $row      = $( this ).closest( '.jr-content-admin-repeater-row' );
					var $next     = $row.next( '.jr-content-admin-repeater-row' );
					var $repeater = $( this ).closest( '.jr-content-admin-repeater' );

					if ($next.length) {
						$row.insertAfter( $next );
						updateRepeaterIndexes( $repeater );
					}
				}
			);

			$( document ).on(
				'click',
				'.jr-content-admin-select-image',
				function (event) {
					event.preventDefault();
					openMediaFrame( $( this ) );
				}
			);

			$( document ).on(
				'click',
				'.jr-content-admin-clear-image',
				function (event) {
					event.preventDefault();
					var $row         = $( this ).closest( '.jr-content-admin-repeater-row' );
					var emptyPreview = (typeof jrContentAdmin !== 'undefined' && jrContentAdmin.emptyPreview)
					? jrContentAdmin.emptyPreview
					: 'No image selected.';
					$row.find( '.jr-content-admin-media-id' ).val( '' );
					$row.find( '.jr-content-admin-image-preview' ).html( '<span class="description">' + emptyPreview + '</span>' );
				}
			);
		}
	);
})( jQuery );
