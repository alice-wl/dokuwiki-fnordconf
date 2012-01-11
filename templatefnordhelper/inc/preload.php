<?php
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once(DOKU_PLUGIN.'templatefnordhelper/inc/confutils.php');
require_once(DOKU_PLUGIN.'templatefnordhelper/inc/template.php');

global $config_cascade, $conf;
if( !isset( $config_cascade['template_dir'] )) {
   $config_cascade['template_dir'] = array(    // used in confutils	
        'default' => array( dirname( DOKU_TPLINC ).'/' ),
   );
}

$conf['default_tpl'] = $conf['template'];
if( !isset( $conf['base_tpl'] )) 
    $conf['base_tpl'] = $conf['plugin']['templatefnordhelper']['base_tpl'];

/**
 * intercept css.php calls
 */
if( strpos( $_SERVER['PHP_SELF'], 'css.php' ) !== false ) {
  $e = new action_plugin_templatefnordhelper_templateaction( );
  $e->template_action( );
  require_once(DOKU_PLUGIN.'templatefnordhelper/exe/css.php');
  exit;
}  

