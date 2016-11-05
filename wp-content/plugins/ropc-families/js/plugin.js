(function( $ )
{
	var selectors = {
		editable_field : '[data-edit-field]',
		record_container : '[data-record-id]',
		add_child : '.add-child',
		add_family : '.add-family',
		child_template : '.child-template:first',
		family_template : '.family-template:first',
		families_table : '.families-table',
		family_image : '.family-image',
		upload_image_form : '[data-role=family-upload]',
		upload_image_submit : '[name=upload-image]',
		delete_person : '.delete-person',
		delete_family : '.delete-family',
	};

	function onEditableClicked( e )
	{
		var current_value = $( e.target ).text();
		var data_field = $( e.target ).attr( 'data-edit-field' );
		var placeholder = $( e.target ).attr( 'data-placeholder' );
		var new_input = $( '<input>' ).attr(  'data-edit-field', data_field )
			.val( current_value )
			.attr( 'placeholder', placeholder )
			.blur( onEditableBlur.bind( undefined, current_value ) );
		
		if ( data_field.match( /phone/ ) )
		{
			new_input.inputmask( {"mask": "(999) 999-9999"} );
		}
		
		$( e.target ).html( new_input );
		new_input.focus();
	}

	function onEditableBlur( original_value, e )
	{
		var current_value = $( e.target ).val();
		var record_id = $( e.target ).closest( selectors.record_container ).attr( 'data-record-id' );

		var data_field = $( e.target ).attr( 'data-edit-field' );
		var field_parts = data_field.split( "." );
		var table = field_parts[ 0 ];
		var field = field_parts[ 1 ];

		if ( !record_id )
		{
			createNew( table, field, current_value, $( e.target ).closest( selectors.record_container ) );
		}
		else if ( original_value != current_value )
		{
			pushUpdate( table, field, current_value, record_id , $( e.target ).parent() );
		}
		$( e.target ).parent().text( current_value );
	}

	function pushUpdate( table, field, value, id, element )
	{
		var fields = {};
		fields[ field ] = value;
		var payload = {
			action : 'update_ropc_member_info',
			data_action : 'update',
			table : table,
			fields : fields,
			id : id,
		};
		$.post( ajaxurl, payload, function( data )
		{
			if ( ! element )
			{
				return;
			}

			if ( Object.keys( data ).indexOf( 'birthday' ) !== -1 )
			{
				element.text( data.birthday || '' );
			}
		} ).fail( onSaveFail );
	}

	function createFamily( data, element, callback )
	{
		var loader = $( '<div class="loader-container"><div class="sk-cube-grid"><div class="sk-cube sk-cube1"></div><div class="sk-cube sk-cube2"></div><div class="sk-cube sk-cube3"></div><div class="sk-cube sk-cube4"></div><div class="sk-cube sk-cube5"></div><div class="sk-cube sk-cube6"></div><div class="sk-cube sk-cube7"></div><div class="sk-cube sk-cube8"></div><div class="sk-cube sk-cube9"></div></div></div>' );
		$( "body" ).append( loader );
		if ( typeof callback != 'function' )
		{
			callback = function(){};
		}

		if ( typeof data != 'object' )
		{
			data = {};
		}

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
				$( element ).attr( 'data-record-id', data.id );
				loader.remove();
				callback( data.id );
			}
		} ).fail( onSaveFail );
	}

	function createPerson( data, element, callback )
	{
		if ( typeof callback != 'function' )
		{
			callback = function(){};
		}

		if ( typeof data != 'object' )
		{
			data = {};
		}

		data.family_role = $( element ).attr( 'data-family-role' );

		var payload = {
			action : 'update_ropc_member_info',
			data_action : 'create',
			table : 'ropc_family_member',
			fields : data,
		};

		$.post( ajaxurl, payload, function( data )
		{
			if ( data.id )
			{
				$( element ).attr( 'data-record-id', data.id );
				callback( data.id );
			}
		} ).fail( onSaveFail );
	}

	function createNew( table, field, value, element )
	{
		var data = {};
		data[ field ] = value;
		if ( table == 'ropc_family_member' )
		{
			var family_id = element.parents( selectors.record_container ).attr( 'data-record-id' );
			if ( ! family_id )
			{
				var onFamilyCreated = function( family_id )
				{
					data.family_id = family_id;
					createPerson( data, element );
				};
				createFamily( {}, element.parents( selectors.record_container ), onFamilyCreated );
			}
			else
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

	function onAddChildClicked( e )
	{
		var html = $( selectors.child_template ).html();
		$( html ).insertBefore( e.target );
	}

	function onAddFamilyClicked( e )
	{
		var html = $( $( selectors.family_template + " tr" ).parent().html() );
		html.find( selectors.upload_image_form ).ajaxForm( options );
		if ( $( selectors.families_table + " tr:last" ).length )
		{
			$( selectors.families_table + " tr:last" ).after( html );
		}
		else
		{
			$( selectors.families_table ).append( html );
		}
	}
		
	function onDeleteFamilyClicked( e )
	{
		if ( ! confirm( 'Are you sure you want to remove this famliy?' ) )
		{
			return;
		}
		var family = $( e.target ).closest( 'tr' ).next();
		var family_id = family.attr( 'data-record-id' );

		var payload = {
			action : 'update_ropc_member_info',
			data_action : 'delete',
			table : 'ropc_family',
			id : family_id,
		};

		$.post( ajaxurl, payload, function( data )
		{
			family.prev().remove();
			family.remove();
		} ).fail( onSaveFail );
	}

	function onDeletePersonClicked( e )
	{
		if ( ! confirm( 'Are you sure you want to remove this person?' ) )
		{
			return;
		}
		var person = $( e.target ).closest( '[data-record-id]' );
		var person_id = person.attr( 'data-record-id' );
		
		var payload = {
			action : 'update_ropc_member_info',
			data_action : 'delete',
			table : 'ropc_family_member',
			id : person_id,
		};
		
		$.post( ajaxurl, payload, function( data )
		{
			person.remove();
		} ).fail( onSaveFail );
	}

	function onSaveFail()
	{
		alert( 'Something went wrong while saving your changes. Please refresh the page and try '
			+ 'again. If you continue to see this message, please notify the webmaster.' );
	}

	function onFamilyImageChanged( e )
	{
		$( e.target ).closest( "form" ).ajaxSubmit( options );
	}
	
	function preRequest( formData, jqForm, options )
	{
		jqForm.find( selectors.family_image ).attr( 'disabled', 'disabled' ).closest( 'label' ).find( '.label-text' ).html( '<i class="fa fa-spin fa-refresh fa-fw"></i>  Uploading...' );
		var family_id = jqForm.find( '[name=family_id]' ).val();
		if ( ! family_id )
		{
			createFamily( {}, jqForm.parents( selectors.record_container ), function( id )
			{
				jqForm.find( '[name=family_id]' ).val( id );
				jqForm.submit();
			} );
			return false;
		}
	}

	function responseReceived( responseText, statusText, xhr, jqForm )
	{
		jqForm.find( selectors.family_image )
			.attr( 'disabled', null )
			.closest( 'label' )
			.find( '.label-text' )
			.text( 'Change picture' );
		
		if ( typeof responseText == "object" )
		{
			jqForm.closest( '[data-record-id]' ).find( '.family-pic' ).attr( 'src', responseText[ 0 ] );
		}
	}

	function onEditableKeypressed( e )
	{
		if ( e.which == 9 )
		{
			var next = $( e.target ).nextThrough( selectors.editable_field + ":visible" );
			if ( next.length )
			{
				e.preventDefault();
				//$( e.target ).blur();
				next.click();
			}
		}
	}
	
	var options = {
		beforeSubmit:  preRequest,   // pre-submit callback
		success:       responseReceived,  // post-submit callback
		//url:           ajaxurl        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
	};

	$( function(){ options.url = ajaxurl; $( selectors.upload_image_form ).ajaxForm( options ) } );

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
	
	$( document ).on( 'click',    selectors.editable_field, onEditableClicked );
	$( document ).on( 'keydown', selectors.editable_field, onEditableKeypressed );
	$( document ).on( 'click',    selectors.add_child,      onAddChildClicked );
	$( document ).on( 'click',    selectors.add_family,     onAddFamilyClicked );
	$( document ).on( 'click',    selectors.delete_person,  onDeletePersonClicked );
	$( document ).on( 'click',    selectors.delete_family,  onDeleteFamilyClicked );
	$( document ).on( 'change',   selectors.family_image,   onFamilyImageChanged );

}( jQuery ));
