jQuery( document ).ready( function ( e ) {
	
});

// Use via <button onclick="toggle_button(this)"></button>
function toggle_button(btn) {
	var jbtn = jQuery(btn);
	jbtn.attr('value', jbtn.attr('value') == '1' ? '0' : '1');
}