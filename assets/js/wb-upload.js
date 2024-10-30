jQuery(document).ready(function(){

	jQuery('.color-picker').iris();

	jQuery('.wpanel-uploader').click(function(e) {
	var send_attachment_bkp = wp.media.editor.send.attachment;
	var button = jQuery(this);
	_custom_media = true;
	wp.media.editor.send.attachment = function(props, attachment){
	  if ( _custom_media ) {
	    //jQuery("#"+id).val(attachment.url);
	    button.prev('input[type="hidden"]').val(attachment.url);
	  } else {
	    return _orig_send_attachment.apply( this, [props, attachment] );
	  };
	}
	wp.media.editor.open(button);
	return false;
	});
	jQuery('.add_media').on('click', function(){
	    _custom_media = false;
	});

});