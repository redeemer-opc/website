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
		upload_image_form : '[data-role=family-upload]',
		upload_image_submit : '[name=upload-image]',
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
			pushUpdate( table, field, current_value, record_id );
		}
		$( e.target ).parent().text( current_value );
	}

	function pushUpdate( table, field, value, id )
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
		$.post( ajaxurl, payload ).fail( onSaveFail );
	}

	function createFamily( data, element, callback )
	{
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
		var html = $( selectors.family_template ).html();
		$( html ).find( selectors.upload_image_form ).ajaxForm( options );
		$( html ).appendTo( selectors.families_table );
	}

	function onSaveFail()
	{
		alert( 'Something went wrong while saving your changes. Please refresh the page and try '
			+ 'again. If you continue to see this message, please notify the webmaster.' );
	}

	function preRequest( formData, jqForm, options )
	{
		jqForm.find( selectors.upload_image_submit ).attr( 'disabled', 'disabled' );
		var family_id = jqForm.closest( '[data-record-id]' );
		jqForm.find( '[name=family_id]' ).val( family_id );
	}
	
	function responseReceived( responseText, statusText, xhr, jqForm )
	{
		jqForm.find( selectors.upload_image_submit ).attr( 'disabled', null );
	}
	
	var options = { 
		beforeSubmit:  preRequest,   // pre-submit callback 
		success:       responseReceived,  // post-submit callback 
		//url:           ajaxurl        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php     
	}; 

	$( function(){ options.url = ajaxurl; $( selectors.upload_image_form ).ajaxForm( options ) } ); 
	
	$( document ).on( 'click', selectors.editable_field, onEditableClicked );
	$( document ).on( 'click', selectors.add_child,      onAddChildClicked );
	$( document ).on( 'click', selectors.add_family,     onAddFamilyClicked );

}( jQuery ));
