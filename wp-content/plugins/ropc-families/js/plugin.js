(function( $ )
{
	var selectors = {
		editable_field : '[data-edit-field]',
		record_container : '[data-record-id]',
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
		if ( original_value != current_value )
		{
			pushUpdate( data_field, current_value, record_id );
		}
		$( e.target ).parent().text( current_value );
	}

	function pushUpdate( data_field, value, id )
	{
		var payload = {
			action : 'update_ropc_member_info',
			update_field : data_field,
			value : value,
			id : id,
		};
		$.post( ajaxurl, payload ).fail( onSaveFail );
	}

	function onSaveFail()
	{
		alert( 'Something went wrong while saving your changes. Please refresh the page and try '
			+ 'again. If you continue to see this message, please notify the webmaster.' );
	}

	$( document ).on( 'click',    selectors.editable_field,            onEditableClicked );

}( jQuery ));
