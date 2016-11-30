/**
 * @author Will Groenendyk
 * @created Fall 2016
 *
 * This file provides support for editing families and family members
 */
(function( $ )
{
	var selectors = {
		add_child           : '.add-child',
		add_family          : '.add-family',
		all_families        : '.all-families',
		child_template      : '.child-template:first',
		delete_family       : '.delete-family',
		delete_person       : '.delete-person',
		editable_field      : '[data-edit-field]',
		expand_picture      : '.actual-picture',
		families_table      : '.families-table',
		family_container    : '.family-main-container',
		family_image        : '.family-image',
		family_template     : '.family-template',
		loader_container    : '.loader-container',
		member_type         : 'select.member-type',
		picture_overlay     : '.pic-overlay',
		search_input        : '.search-input',
		record_container    : '[data-record-id]',
		upload_image_form   : '[data-role=family-upload]',
	};

	
	//////////////////////////////////////////////////////////////////////////////////////////////
	// Utility Methods
	//////////////////////////////////////////////////////////////////////////////////////////////
	
	/**
	 * @brief
	 *	jQuery plugin to find the next element in the DOM that matches the given selector
	 */
	$.fn.nextThrough = function( selector )
	{
		// Our reference will be the last element of the current set
		var $reference = $(this).last();
		// Add the reference element to the set the elements that match the selector
		var $set = $(selector).add($reference);
		// Find the reference position to the set
		var $pos = $set.index($reference);
		// Return an empty set if it is the last one
		if ($set.length == $pos) return $();
		// Return the next element to our reference
		return $set.eq($pos + 1);
	}
	
	/**
	 * @brief
	 *	Shows a spinner that covers the entire current page
	 */
	function showLoader()
	{
		var loader = $(
			  '<div class="loader-container">'
			+ 	'<div class="sk-cube-grid">'
			+		'<div class="sk-cube sk-cube1"></div>'
			+		'<div class="sk-cube sk-cube2"></div>'
			+		'<div class="sk-cube sk-cube3"></div>'
			+		'<div class="sk-cube sk-cube4"></div>'
			+		'<div class="sk-cube sk-cube5"></div>'
			+		'<div class="sk-cube sk-cube6"></div>'
			+		'<div class="sk-cube sk-cube7"></div>'
			+		'<div class="sk-cube sk-cube8"></div>'
			+		'<div class="sk-cube sk-cube9"></div>'
			+ 	'</div>'
			+ '</div>' );
		$( "body" ).append( loader );
	}

	/**
	 * @brief
	 *	Removes the loader created in showLoader();
	 */
	function hideLoader()
	{
		$( selectors.loader_container ).remove();
	}
	
	/**
	 * @brief
	 *	Creates a new family
	 *
	 * @param object data
	 *	Fields in the `ropc_family` table to populate
	 * @param jQuery element
	 *	The family's container element
	 * @param function callback
	 *	OPTIONAL. If given, this function is called after the family is successfully created, and is
	 *	passed the newly-created family's id
	 */
	function createFamily( data, element, callback )
	{
		showLoader();

		// Validate function parameters
		if ( typeof callback != 'function' )
		{
			callback = function(){};
		}
		if ( typeof data != 'object' )
		{
			data = {};
		}
		
		// If no data was given to populate the new family record with, include a dummy value (the
		// server will save this as a null value)
		if ( ! Object.keys( data ).length )
		{
			data.address1 = '';
		}

		var payload = {
			action : 'update_ropc_member_info',
			data_action : 'create',
			table : 'ropc_family',
			fields : data,
		};
		$.post( ajaxurl, payload, function( data )
		{
			if ( data.id )
			{
				// Update the family's container element with the new family id
				$( element ).attr( 'data-record-id', data.id );
				
				hideLoader();
				callback( data.id );
			}
		} ).fail( onSaveFail );
	}

	/**
	 * @brief
	 *	Creates a new person
	 *
	 * @param object data
	 *	Fields in the `ropc_family_member` table to populate
	 * @param jQuery element
	 *	The person's container element
	 * @param function callback
	 *	OPTIONAL. If given, this function is called after the person is successfully created, and is
	 *	passed the newly-created person's id
	 */
	function createPerson( data, element, callback )
	{
		// Validate function parameters
		if ( typeof callback != 'function' )
		{
			callback = function(){};
		}
		if ( typeof data != 'object' )
		{
			data = {};
		}

		// Determine if the new person is a husband, wife, or child
		data.family_role = $( element ).attr( 'data-family-role' );

		var payload = {
			action : 'update_ropc_member_info',
			data_action : 'create',
			table : 'ropc_family_member',
			fields : data,
		};
		$.post( ajaxurl, payload, function( data )
		{
			// If the person was created successfuly
			if ( data.id )
			{
				// Update the person's container element with the person's new id
				$( element ).attr( 'data-record-id', data.id );

				callback( data.id );
			}
		} ).fail( onSaveFail );
	}

	/**
	 * @brief
	 *	Creates a new ropc_family or ropc_family_member record
	 *
	 * @param string table
	 *	Either 'ropc_family' or 'ropc_family_member'
	 * @param string field
	 *	The name of a field to populate upon creation of this record, using the `value` param
	 * @param string value
	 * @param jQuery element
	 *	The HTML container element for the family/person
	 */
	function createNew( table, field, value, element )
	{
		var data = {};
		data[ field ] = value;
		
		// If we're creating a new person
		if ( table == 'ropc_family_member' )
		{
			// If this person is part of a new family
			var family_id = element.parents( selectors.record_container ).attr( 'data-record-id' );
			if ( ! family_id )
			{
				// First create the new family, then create the new person
				var onFamilyCreated = function( family_id )
				{
					data.family_id = family_id;
					createPerson( data, element );
				};
				createFamily( {}, element.parents( selectors.record_container ), onFamilyCreated );
			}
			else // The new person will be added to an existing family
			{
				data.family_id = family_id;
				createPerson( data, element );
			}
		}
		else
		{
			createFamily( data, element );
		}
	}
	
	/**
	 * @brief
	 *	Sends a family/person update to the server
	 *
	 * @param string table
	 *	The name of the DB table to update
	 * @param string field
	 *	The name of the DB field to update
	 * @param string new_value
	 * @param int id
	 *	The record id
	 * @param jQuery element
	 *	OPTIONAL. A jQuery of the edited element (the *parent* of the <input>)
	 */
	function pushUpdate( table, field, new_value, id, element )
	{
		var fields = {};
		fields[ field ] = new_value;
		
		var payload = {
			action : 'update_ropc_member_info',
			data_action : 'update',
			table : table,
			fields : fields,
			id : id,
		};
		$.post( ajaxurl, payload, function( data )
		{
			// If we received an 'element' param
			if ( element )
			{
				// If we're updating a date field, update the field according to the server's
				// response (if the given date was invalid, this will clear out the field)
				[ 'birthday', 'anniversary' ].forEach( function( value )
				{
					if ( Object.keys( data ).indexOf( value ) !== -1 )
					{
						element.text( data[ value ] || '' );
					}
				} );
			}
		} ).fail( onSaveFail );
	}

	//////////////////////////////////////////////////////////////////////////////////////////////
	// Event Handlers
	//////////////////////////////////////////////////////////////////////////////////////////////
	
	$( document ).on( 'click',   selectors.editable_field,  onEditableClicked );
	$( document ).on( 'keydown', selectors.editable_field,  onEditableKeypressed );
	$( document ).on( 'click',   selectors.add_child,       onAddChildClicked );
	$( document ).on( 'click',   selectors.add_family,      onAddFamilyClicked );
	$( document ).on( 'click',   selectors.delete_person,   onDeletePersonClicked );
	$( document ).on( 'click',   selectors.delete_family,   onDeleteFamilyClicked );
	$( document ).on( 'change',  selectors.family_image,    onFamilyImageChanged );
	$( document ).on( 'change',  selectors.member_type,     onMemberTypeChanged )
	$( document ).on( 'ready',                              init );
	
	/**
	 * @brief
	 *	Click handler for any editable field; converts a plain text field to a textfield input to
	 *	allow the field to be edited.
	 *
	 * @param Event e
	 */
	function onEditableClicked( e )
	{
		// Extract the field's current value, then create a textfield pre-populated with this
		// field's current value
		var current_value = $( e.target ).text().trim();
		var data_field = $( e.target ).attr( 'data-edit-field' );
		var placeholder = $( e.target ).attr( 'data-placeholder' );
		var new_input = $( '<input>' ).attr(  'data-edit-field', data_field )
			.val( current_value )
			.attr( 'placeholder', placeholder )
			.blur( onEditableBlur.bind( undefined, current_value ) );
		
		// If this is a phone number, apply an mask on the field for consistent formatting
		if ( data_field.match( /phone/ ) )
		{
			new_input.inputmask( {"mask": "(999) 999-9999"} );
		}

		// Replace the current field's text with the textfield we just generated
		$( e.target ).html( new_input );
		new_input.focus();
	}

	/**
	 * @brief
	 *	Blur handler for any editable field. Sends the updated value to the server
	 *
	 * @param string original_value
	 *	Typically this is supplied by bind()'ing this function before using it as a callback
	 * @param Event e
	 */
	function onEditableBlur( original_value, e )
	{
		// Get the field's new value and record id
		var new_value = $( e.target ).val();
		var record_id = $( e.target ).closest( selectors.record_container ).attr( 'data-record-id' );
		
		// Parse out the field's table and column name
		var data_field = $( e.target ).attr( 'data-edit-field' );
		var field_parts = data_field.split( "." );
		var table = field_parts[ 0 ];
		var field = field_parts[ 1 ];

		// If this is part of a new person or family
		if ( !record_id )
		{
			createNew( table, field, new_value, $( e.target ).closest( selectors.record_container ) );
		}
		// Otherwise, if this is part of an existing person or family and the value was just updated
		else if ( original_value != new_value )
		{
			pushUpdate( table, field, new_value, record_id , $( e.target ).parent() );
		}
		
		// Replace the textfield with plain text
		$( e.target ).parent().text( new_value );
	}

	/**
	 * @brief
	 *	Keypress handler for editable fields; if tab is pressed, moves on to the next field
	 *
	 * @param Event e
	 */
	function onEditableKeypressed( e )
	{
		// If tab was pressed
		if ( e.which == 9 )
		{
			e.preventDefault();
			var focus_on = $();
		
			// If shift + tab was pressed
			if ( e.shiftKey )
			{
				return;
				// TODO: Select the previous field
				//focus_on = $( e.target ).prevThrough( selectors.editable_field + ":visible" );
			}
			else
			{
				// We filter on visible elements so that we don't select editable fields that exist
				// inside of family/person templates
				focus_on = $( e.target ).nextThrough( selectors.editable_field + ":visible" );
			}

			// If any editable fields exist in the DOM after the current one, focus on that element
			if ( focus_on.length )
			{
				focus_on.click();
			}
		}
	}

	/**
	 * @brief
	 *	Click handler for the "Add child" button
	 *
	 * @param Event e
	 */
	function onAddChildClicked( e )
	{
		// Use the child template template to insert an empty "child" into the DOM, at the end of
		// the current family
		var html = $( selectors.child_template ).html();
		$( html ).insertBefore( e.target );
	}

	/**
	 * @brief
	 *	Click handler for the "Add family" button
	 *
	 * @param Event e
	 */
	function onAddFamilyClicked( e )
	{
		// Use the new family template template to insert an empty "family" into the DOM, after the
		// all other families
		var html = $( $( selectors.family_template ).html() );
		html.find( selectors.upload_image_form ).ajaxForm( options );
		$( selectors.all_families ).append( html );
		createFamily( undefined, html );
	}
	
	/**
	 * @brief
	 *	Click handler for the "delete" button for a family
	 */
	function onDeleteFamilyClicked( e )
	{
		// Confirm that the family should be deleted
		if ( ! confirm( 'Are you sure you want to remove this famliy?' ) )
		{
			return;
		}
	
		// Get the family's container element and its id
		var family = $( e.target ).closest( selectors.family_container );
		var family_id = family.attr( 'data-record-id' );

		// Send the deletion to the server
		var payload = {
			action : 'update_ropc_member_info',
			data_action : 'delete',
			table : 'ropc_family',
			id : family_id,
		};
		$.post( ajaxurl, payload, function( data )
		{
			family.remove();
		} ).fail( onSaveFail );
	}

	/**
	 * @brief
	 *	Click handler for the "delete" button for a person
	 */
	function onDeletePersonClicked( e )
	{
		// Confirm that the person should be deleted
		if ( ! confirm( 'Are you sure you want to remove this person?' ) )
		{
			return;
		}
		
		// Get the person's container element and its id
		var person = $( e.target ).closest( '[data-record-id]' );
		var person_id = person.attr( 'data-record-id' );
		
		// Send the deletion to the server
		var payload = {
			action : 'update_ropc_member_info',
			data_action : 'delete',
			table : 'ropc_family_member',
			id : person_id,
		};
		$.post( ajaxurl, payload, function( data )
		{
			// Remove the person's container element from the family's container element
			person.remove();
		} ).fail( onSaveFail );
	}

	/**
	 * @brief
	 *	Handles responses from the server that indicate some sort of failure
	 */
	function onSaveFail()
	{
		alert( 'Something went wrong while saving your changes. Please refresh the page and try '
			+ 'again. If you continue to see this message, please notify the webmaster.' );
	}

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
	
	function onMemberTypeChanged( e )
	{
		var member_id = $( e.target ).closest( '[data-record-id]' ).attr( 'data-record-id' );
		pushUpdate( 'ropc_family_member', 'type', $( e.target ).val(), member_id );
	}
	
	function init()
	{
		$( selectors.editable_field ).each( function( i, element )
		{
			$( element ).text( $( element ).text().trim() );
		} );
	}

	///////////////////////////////////////////////////////////////////////////////////////////////
	// Ajax Form Config
	///////////////////////////////////////////////////////////////////////////////////////////////	
	
	var options = {

		/**
		 * @brief
		 *	Called prior to an Ajax form being submitted
		 *
		 * @param object data
		 * @param jQuery form
		 */
		beforeSubmit:  function ( data, form )
		{
			// Disable the "Add [Change] picture" button
			form.find( selectors.family_image )
				.find( '[type=file]' ) 
				.attr( 'disabled', 'disabled' )
				.closest( 'label' )
				.attr( 'data-disabled', 'disabled' )
				.find( '.label-text' )
				.html( '<i class="fa fa-spin fa-refresh fa-fw"></i>  Uploading...' );
			
			// If we're adding a picture to a new family, first create the new family before
			// submitting the form
			var family_id = form.find( '[name=family_id]' ).val();
			if ( ! family_id )
			{
				if ( form.closest( '[data-record-id]' ).length )
				{
					for ( var i in data )
					{
						if ( data[ i ].name == 'family_id' )
						{
							 data[ i ].value = form.closest( '[data-record-id]' ).attr( 'data-record-id' );
						}
					}
				}
				else
				{
					createFamily( {}, form.parents( selectors.record_container ), function( id )
					{
						form.find( '[name=family_id]' ).val( id );
						form.submit();
					} );
				
					return false;
				}
			}
		},

		/**
		 * @brief
		 *	Called after the server responds to an ajax form being submitted
		 *
		 * @param mixed data
		 * @param string status
		 * @param jqXhr xhr
		 * @param jQuery form
		 */
		success: function( data, status, xhr, form )
		{
			// Re-enable the "Add [Change] picture" button
			form.find( selectors.family_image )
				.find( '[type=file]' ) 
				.attr( 'disabled', null )
				.val( "" )
				.closest( 'label' )
				.attr( 'data-disabled', null )
				.find( '.label-text' )
				.text( 'Change picture' );
			
			// If the server sent us information about the newly uploaded image, display that image
			if ( typeof data == "object" )
			{
				form.closest( '[data-record-id]' )
					.find( '.picture-backdrop-blurry-img' )
					.css( 'background-image', "url(" + data.full[ 0 ] + ")" )
					.closest( '[data-record-id]' )
					.find( '.actual-picture' )
					.attr( 'data-picture-url', data.full[ 0 ] )
					.css( 'background-image', "url(" + data.thumb[ 0 ] + ")" )
					.closest( '.no-picture' )
					.removeClass( 'no-picture' );
			}
		},
		// url: Populated on document.ready
	};

	// On document.ready, populate options.url initialize all image upload forms as Ajax forms
	$( function()
	{
		options.url = ajaxurl;
		$( selectors.upload_image_form ).ajaxForm( options )
	} );
	
}( jQuery ));
