<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * WC_Admin_Pointers Class.
 */
class MyWorks_WC_QBO_Sync_Admin_Pointers {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_enqueue_scripts', array( $this, 'setup_pointers_for_screen' ) );
    }
    
    public function setup_pointers_for_screen() {
        
        $new_pointer_content = get_option('mw_wc_qbo_sync_admin_pointers');
        
        if(empty($new_pointer_content)) {
            return;
        }
       
       if ( $this->custom_admin_pointers_check() ) {
          add_action( 'admin_print_footer_scripts', array( $this,'custom_admin_pointers_footer') );

          wp_enqueue_script( 'wp-pointer' );
          wp_enqueue_style( 'wp-pointer' );
       }        
    }

    public function custom_admin_pointers_check() {
       $admin_pointers = $this->custom_admin_pointers();
       foreach ( $admin_pointers as $pointer => $array ) {
          if ( $array['active'] )
             return true;
       }
    }

    public function custom_admin_pointers_footer() {
       $admin_pointers = $this->custom_admin_pointers();
       
       if(!empty($admin_pointers)) {
       ?>
    <script type="text/javascript">
    /* <![CDATA[ */
    ( function($) {
       <?php
       foreach ( $admin_pointers as $pointer => $array ) {
          if ( $array['active'] ) {
             ?>
             $( '<?php echo $array['anchor_id']; ?>' ).pointer( {
                content: '<?php echo $array['content']; ?>',
                position: {
                edge: '<?php echo $array['edge']; ?>',
                align: '<?php echo $array['align']; ?>'
             },
                close: function() {
                   $.post( ajaxurl, {
                      pointer: '<?php echo $pointer; ?>',
                      action: 'dismiss-wp-pointer'
                   } );
                }
             } ).pointer( 'open' );
             <?php
          }
       }
       ?>
    } )(jQuery);
    /* ]]> */
    </script>
       <?php
       }
       delete_option( 'mw_wc_qbo_sync_admin_pointers' );
    }

    public function custom_admin_pointers() {
        $dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_mw_pointers', true ) );
        
        $new_pointer_content = get_option('mw_wc_qbo_sync_admin_pointers');
        
        if(empty($new_pointer_content)) {
            return array();
        }
		
       return array(
          MW_QBO_SYNC_EXT_DOMAIN . 'qb_online_sync' => array(
             'content' => $new_pointer_content,
             'anchor_id' => '#toplevel_page_myworks-wc-qbo-sync',
             'edge' => 'left',
             'align' => 'left',
             'active' => ( ! in_array( MW_QBO_SYNC_EXT_DOMAIN . 'qb_online_sync', $dismissed ) )
          ),
       );
    }    

}

new MyWorks_WC_QBO_Sync_Admin_Pointers();