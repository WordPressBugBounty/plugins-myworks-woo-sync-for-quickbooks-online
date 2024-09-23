(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	$(function() {
		$('.mw_wc_qbo_sync_span').on('click',function(e){
			$(this).siblings().removeAttr('readonly');
		});

		jQuery("#myworks_wc_qbo_sync_check_license").submit(function(e){            
            e.preventDefault();
            var data = {
                "action": "myworks_wc_qbo_sync_check_license"
            };
            data = jQuery(this).serialize() + "&" + jQuery.param(data);
			jQuery('#mwqs_license_chk_loader').css('visibility','visible');
            jQuery.ajax({
               type: "POST",
               url: ajaxurl,
               data: data,
               cache:false,
               datatype: "json",
               success: function(data){
				   jQuery('#mwqs_license_chk_loader').css('visibility','hidden');
                   alert(data);
				   if(data=='License Activated'){
					   location.reload();
				   }
                   /*
                   if(data!=''){              
                    var data = jQuery.parseJSON(data);
                    var status = data.status;
                    var msg = data.msg;                    
                   }
                   */
               },
			   error: function(data) {
					jQuery('#mwqs_license_chk_loader').css('visibility','hidden');
				    alert('Error');
			   }
             });
			 
        });
		
		
		jQuery('.mwqs_stb a').on('click',function(e){
			var click_id = $(this).attr('id');
			$('#mw_qbo_sybc_settings_current_tab').val(click_id);
			jQuery('.mwqs_stb a').each(function(){
				var tab_id = $(this).attr('id');
				if(tab_id!=click_id){
					$('#'+tab_id).parent().removeClass('active');					
					$('#'+tab_id+'_body').hide();					
				}
			});
			$('#'+click_id).parent().addClass('active');
			$('#'+click_id+'_body').show();
		});
	


    $('#'+$('#mw_qbo_sybc_settings_current_tab').val()).parent().addClass('active');
    $('#'+$('#mw_qbo_sybc_settings_current_tab').val()+'_body').show();

    $('.close').on('click',function(e){
		$('.status_popup').hide();
	});

	})

})( jQuery );


function mw_qbo_sync_check_all(checkbox,start_with){
	jQuery('input:checkbox').each(function(){		
		if(typeof(jQuery(this).attr('id'))!=='undefined' && jQuery(this).attr('id').match("^"+start_with)){			
			if(checkbox.checked){				
				jQuery(this).attr('checked','checked');
			}else{
				jQuery(this).removeAttr('checked');
			}
		}		
	});
}

var mwQsPopUpWin_obj=0;
function popUpWindow(URLStr,popUpWin, left, top, width, height){
 //Fixed Width Height
 width = 750;
 height = 480;
 
 left = (screen.width/2)-(width/2);
 top = (screen.height/2)-(height/2);
	
  if(mwQsPopUpWin_obj){
    //if(!mwQsPopUpWin_obj.closed) mwQsPopUpWin_obj.close();    
    if(mwQsPopUpWin_obj.name==popUpWin){
	 alert('Sync status window already opened');
     return false;
     }
  }
 mwQsPopUpWin_obj = open(URLStr, popUpWin, 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=yes,width='+width+',height='+height+',left='+left+', top='+top+',screenX='+left+',screenY='+top+'');
 return mwQsPopUpWin_obj;
}

jQuery(document).ready(function($){
	$('.tooltipped').removeClass('material-icons');
});