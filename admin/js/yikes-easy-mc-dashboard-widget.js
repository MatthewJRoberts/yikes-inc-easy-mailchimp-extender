(function( $ ) {	// document ready DOM	$( document ).ready( function() {				$( '#yikes-easy-mc-dashboard-change-list' ).on( 'change' , function() {						// append preloader to our data			$( '#yikes-easy-mc-dashboard-widget-stats' ).children().not( '.yikes-easy-mc-widget-preloader' ).fadeTo( 'fast' , '.5' );			$( '#yikes-easy-mc-dashboard-widget-stats' ).append( object.preloader );					var selected_list = $( this ).find( 'option:selected' ).attr( 'val' );						// build our data			var data = {				'action' : 'get_new_list_data',				'list_id' : selected_list // grab the form ID to query the API for field data			};						// submit our ajax request to grab new list data			$.ajax({				url: object.ajax_url,				type:'POST',				data: data,				success : function( response, textStatus, jqXHR) { 					$( '.yikes-easy-mc-widget-preloader' ).remove();					$( '#yikes-easy-mc-dashboard-widget-stats' ).children().fadeTo( 'fast' , '1' );					$( '#yikes-easy-mc-dashboard-widget-stats' ).html( response );				},				error : function( jqXHR, textStatus, errorThrown ) { 					alert( textStatus+jqXHR.status+jqXHR.responseText+"..." ); 				},				complete : function( jqXHR, textStatus ) {					console.log( 'field successfully added to the form' );				}							});						return false;					});			});		})(jQuery);function changeList( selected ) {	alert( selected.find( 'option:selected').value );	jQuery( '#yikes-easy-mc-dashboard-widget-stats' ).html( '<h4>TEST</h4>' );}