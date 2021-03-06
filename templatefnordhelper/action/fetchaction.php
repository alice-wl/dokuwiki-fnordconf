<?php
if(!defined('DOKU_INC')) die();

class action_plugin_templatefnordhelper_fetchaction extends DokuWiki_Action_Plugin {

  function getInfo(){
    return array(
        'author' => 'ai',
    );
  }

  function register(&$controller) {/*{{{*/

      $controller->register_hook('FETCH_MEDIA_STATUS', 'BEFORE', $this, 'fetch_action' );

  }/*}}}*/

  function fetch_action( $evt ) {/*{{{*/
    #$data = $evt->data;
    global $data, $conf, $MEDIA, $EXT, $WIDTH, $HEIGHT;

    if( isset( $_GET['mode'] ) && $_GET['mode'] == 'styleimg' ) {
        // fallthrough for other errors than not found
        if( $data['status'] != '404' ) {
            return false;
        }
	$tpl = $_GET['template'];

        if( !preg_match( '/^[\w-]*$/', $tpl )) {
            return false;
        }

        $plugins = plugin_list( );

        if( !$file = getConfigPath( 'template_dir', $tpl.'/images/'.$MEDIA ))
          $file = getConfigPath( 'template_dir', $conf['base_tpl'].'/images/'.$MEDIA );
        
        //fall through with 404
        if(!@file_exists( $file )) {
            return false;
        }

        $orig = $file;
      
      //handle image resizing/cropping
        if((substr($MIME,0,5) == 'image') && $WIDTH){
         if($HEIGHT){
            $file = media_crop_image($file,$EXT,$WIDTH,$HEIGHT);
         }else{
            $file = media_resize_image($file,$EXT,$WIDTH,$HEIGHT);
         }
       }  

       $data['status'] = '200';
       $data['statusmessage'] = null;
       $data['orig'] = $orig;
       $data['file'] = $file;
    }
  
  }/*}}}*/
}
