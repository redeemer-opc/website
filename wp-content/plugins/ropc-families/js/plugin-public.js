/**
 * @author Will Groenendyk
 * @created Fall 2016
 *
 * This file provides support for interacting with the member center page
 */
(function( $ )
{
	var selectors = {
		expand_picture       : '.actual-picture',
		picture_overlay      : '.pic-overlay',
		search_input         : '.search-input',
		birthday_selector    : '[name=birthday_month]',
		anniversary_selector : '[name=anniversary_month]',
	};

	function isMobile()
	{
		return !! $( '.mobile-show:visible' ).length;
	}
	
	$( document ).on( 'click touchstart', selectors.expand_picture,       onExpandPictureClicked );
	$( document ).on( 'click touchstart', selectors.picture_overlay,      onPictureOverlayClicked );
	$( window   ).on( 'resize',                                           onWindowResize );
	$( document ).on( 'ready',                                            onWindowResizeKernel );
	$( document ).on( 'keyup',            selectors.search_input,         onSearchKeyUp );
	$( document ).on( 'change',           selectors.birthday_selector,    onBirthdayMonthChanged );
	$( document ).on( 'change',           selectors.anniversary_selector, onAnninversaryMonthChanged );

	/**
	 * @brief
	 *	Change handler for a family's picture
	 *
	 * @param Event e
	 */
	function onFamilyImageChanged( e )
	{
		// Submit the image's containing form via Ajax
		$( e.target ).closest( "form" ).ajaxSubmit( options );
	}

	function onExpandPictureClicked( e )
	{
		var family_id = $( e.target ).closest( '[data-record-id]' ).attr( 'data-record-id' );
		var pic_element = $( e.target ).closest( '.actual-picture' );
		var pic_src = pic_element.attr( 'data-picture-url' );
		var caption = pic_element.attr( 'data-picture-caption' );
		$( 'body' ).append( '<div data-record-id="' + family_id + '" class="pic-overlay"><img src="' + pic_src + '"/>' 
			+ '<div class="picture-caption-container"><span class="picture-caption" data-placeholder="Caption" data-edit-field="ropc_family.picture_caption">' + caption + '</span></div></div>' );
	}

	function onPictureOverlayClicked( e )
	{
		if ( $( e.target ).is( selectors.picture_overlay ) )
		{
			$( e.target ).remove();
		}
	}
	
	var timeout = null;
	function onWindowResize()
	{
		clearTimeout( timeout );
		setTimeout( onWindowResizeKernel, 100 );
	}
	
	function onWindowResizeKernel()
	{
		if ( isMobile() )
		{
			var needed_height = $( '.family-name' ).height() + $( '.actual-picture' ).height();
			var padding_top = Number( $( '.picture-backdrop' ).css( 'padding-top' ).replace( /px$/, '' ) );
			var padding_bottom = Number( $( '.picture-backdrop' ).css( 'padding-bottom' ).replace( /px$/, '' ) );
			vertical_padding = 0;//( padding_top + padding_bottom ) || 0;
			$( '.picture-backdrop' ).height( needed_height + vertical_padding )
		}
		else
		{
			$( '.picture-backdrop' ).css( 'height', '' );
		}
	}
	
	function onSearchKeyUp( e )
	{
		if ( e.which == 13 )
		{
			window.location = window.location.href.split("?")[0] + "?search=" + $( e.target ).val().replace( /\W/, '' );
		}
	}
	
	function onBirthdayMonthChanged( e )
	{
		var new_month = $( e.target ).val();
		location = '/member-center?bd-month=' + new_month;
	}
	
	function onAnninversaryMonthChanged( e )
	{
		var new_month = $( e.target ).val();
		location = '/member-center?anniv-month=' + new_month;
	}
	
}( jQuery ));