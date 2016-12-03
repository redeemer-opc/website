/**
 * @author Will Groenendyk
 * @created Fall 2016
 *
 * This file provides support for interacting with the member center page
 */
(function( $ )
{
	var selectors = {
		actual_picture         : '.actual-picture',
		expand_picture         : '.actual-picture',
		family_container       : '.family-main-container',
		family_name            : '.family-name',
		month_selector         : '[data-month-for]',
		nav_button             : '.nav-btn',
		picture_backdrop       : '.picture-backdrop',
		picture_overlay        : '.pic-overlay',
		record_container       : '[data-record-id]',
		search_input           : '.search-input',
		visible_mobile_content : '.mobile-show:visible',
	};
	
	//////////////////////////////////////////////////////////////////////////////////////////////
	// Utility Methods
	//////////////////////////////////////////////////////////////////////////////////////////////
	
	/**
	 * @retval bool
	 */
	function isMobile()
	{
		return !! $( selectors.visible_mobile_content ).length;
	}
	
	//////////////////////////////////////////////////////////////////////////////////////////////
	// Event Handlers
	//////////////////////////////////////////////////////////////////////////////////////////////
	
	$( document ).on( 'click touchstart', selectors.expand_picture,  onExpandPictureClicked );
	$( document ).on( 'click touchstart', selectors.picture_overlay, onPictureOverlayClicked );
	$( window   ).on( 'resize',                                      onWindowResize );
	$( document ).on( 'ready',                                       onWindowResizeKernel );
	$( document ).on( 'keyup',            selectors.search_input,    onSearchKeyUp );
	$( document ).on( 'change',           selectors.month_selector,  onMonthChanged );

	/**
	 * @brief
	 *	Click handler for a family's picture; shows the picture inside of an overlay
	 *
	 * @param Event e
	 */
	function onExpandPictureClicked( e )
	{
		var family_id = $( e.target ).closest( selectors.record_container ).attr( 'data-record-id' );
		var pic_element = $( e.target ).closest( selectors.actual_picture );
		var pic_src = pic_element.attr( 'data-picture-url' );
		var caption = pic_element.attr( 'data-picture-caption' ).trim();
		var caption_element = can_edit || caption
			? '<span class="picture-caption" data-placeholder="Caption" '
				+ 'data-edit-field="ropc_family.picture_caption">' + caption + '</span>'
			: '';
		
		$( 'body' ).append( '<div data-record-id="' + family_id
			+ '" class="pic-overlay"><img src="' + pic_src + '"/>' 
			+ '<div class="picture-caption-container">' + caption_element + '</div></div>' );
	}

	/**
	 * @brief
	 *	Click handler for a an overlay displaying a family's picture; hide the overlay
	 *
	 * @param Event e
	 */
	function onPictureOverlayClicked( e )
	{
		if ( $( e.target ).is( selectors.picture_overlay ) )
		{
			$( e.target ).remove();
		}
	}
	
	// For performance reasons, debounces the window.resize event
	var timeout = null;
	function onWindowResize()
	{
		clearTimeout( timeout );
		setTimeout( onWindowResizeKernel, 100 );
	}
	
	/**
	 * @brief
	 *	Window.resize handler; ensures that on mobile devices, family headings containing the
	 *	picture and name are the approrpiate height
	 *
	 * @param Event e
	 */
	function onWindowResizeKernel()
	{
		if ( isMobile() )
		{
			$( selectors.family_container ).each( function( i, element )
			{
				var backdrop = $( selectors.picture_backdrop, element );

				var needed_height = $( selectors.family_name, backdrop ).height()
					+ $( selectors.actual_picture + ":visible", backdrop ).height();

				backdrop.height( needed_height );
			} );
		}
		else
		{
			$( '.picture-backdrop' ).css( 'height', '' );
		}
	}

	/**
	 * @brief
	 *	Keyup handler for the search box; triggers a search when the user presses `enter`
	 *
	 * @param Event e
	 */	
	function onSearchKeyUp( e )
	{
		if ( e.which == 13 )
		{
			window.location = window.location.href.split("?")[0]
				+ "?search=" + $( e.target ).val().replace( /\W/, '' );
		}
	}

	/**
	 * @brief
	 *	Change handler for the birthday/anniversary month selector; sends the user to the
	 *	approrpiate page
	 *
	 * @param Event e
	 */		
	function onMonthChanged( e )
	{
		var page_type = $( e.target ).attr( 'data-month-for' )
		var new_month = $( e.target ).val();
		location = '/member-center?' + page_type + '=' + new_month;
	}
	
}( jQuery ));