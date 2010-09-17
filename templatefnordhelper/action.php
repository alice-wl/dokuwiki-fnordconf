<?
/** another template changer
 */

if(!defined('DOKU_INC')) die();

// if called via preload 
#if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
#require_once(DOKU_PLUGIN.'action.php');
#require_once(DOKU_PLUGIN.'templatefnordhelper/conf/preload.php');

class action_plugin_templatefnordhelper extends DokuWiki_Action_Plugin {


  function getInfo(){
    return array(
        'author' => 'ai',
        'email'  => 'ai',
        'date'   => '2010-02-07',
        'name'   => 'template functions',
        'desc'   => 'collection of functions used for eh2010 and user template switching',
        'url'    => 'wiki.muc.ccc.de',
    );
  }

/**
 * template_action is now called by preload to work on css.php
 * should support both methods
 * in preload or on load
 *
 */
  function register(&$controller) {/*{{{*/

      #$controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE',  $this, 'template_action' );

  }/*}}}*/

  function template_action( ) {/*{{{*/
    global $conf;
    $tpl    = $conf['template'];
    $theme  = '';
    $switch = false;

    $u = $this->getUser( ); // init user data

    if( isset( $u['template'] ) && $u['template'] != $tpl ) {
	$tpl = $u['template'];
	$switch = true;
    }
    if( isset( $u['template_theme'] ) && $u['template_theme'] != $tpl ) {
	$theme = $u['template_theme'];
	$switch = true;
    }

    if( isset( $_GET['utpl'] ) && preg_match( '/^[\w-]+$/', $_GET['utpl'] )) {
        if( $_GET['utpl'] != $tpl && $_GET['utpl'] != $conf['template'] ) { 
	    $switch = true;
        }
	$tpl = $_GET['utpl']; 
        $this->saveSession( 'template', $tpl );
    }

    if( isset( $_GET['utpl_theme'] ) && preg_match( '/^[\w-]*$/', $_GET['utpl_theme'] )) { 
        if( $_GET['utpl_theme'] ) {
            $theme = $_GET['utpl_theme'];
            $switch = true;
        }
        $this->saveSession( 'template_theme', $_GET['utpl_theme'] );
    }

    if( $switch && preg_match( '/^[\w-]+$/', $tpl ) 
	&& is_dir( DOKU_INC.'lib/tpl/'.$tpl."/" ) ) {

        #$this->saveSession( 'template', $tpl );
#	if( isset( $_GET['utpl_save'] )) {
     // TODO: save user settings to file
#	    $this->saveUser( 'template', $tpl );
#	}

	$this->_switch( $tpl, $theme );
    }
      
  }/*}}}*/

  function getUser( $var=false ) {/*{{{*/
      if( !isset( $this->u['load'] )) {
        @session_start();
        $this->u = $_SESSION[DOKU_COOKIE]['tpl'];
      }

      if( !isset( $this->u['load'] )) {
	  $this->u['load'] = 1;
	  // TODO: load wikiuser selection from file
      }
      if( $var ) return isset( $this->u[$var] ) ? $this->u[$var] : false;
      return $this->u;
  }/*}}}*/


  function saveSession( $var, $val ) {/*{{{*/
      $this->u[$var] = $val;

      @session_start();
      $_SESSION[DOKU_COOKIE]['tpl'] = $this->u;
      session_write_close();
  }/*}}}*/

  function saveUser( $var, $val ) {
    return false;
  }

  /**
   * actual helper function
   * changes style after doku init 
   */

  function tpl_switch(  $tpl ) {/*{{{*/
    global $conf;
    if( $conf['template'] == $tpl ) { return ''; }

  // prevent userstyle from beeing overwritten ... one or the other way 
    if( $this->u['template'] ) { return ''; }
    if( preg_match( '/^[\w-]+$/', $tpl ) 
	&& is_dir( DOKU_TPLINC.'/../'.$tpl."/" ) ) {
	    $this->_switch( $tpl );
    }
  }/*}}}*/

  function _switch( $tpl, $theme='' ) {/*{{{*/
    global $conf;
    global $tpl_configloaded;

    if( $theme ) {
        $conf['template_theme'] = $theme;
    }

    $conf['template'] = $tpl;

    $tconf = $this->tpl_loadConfig( $tpl ); 
    if ($tconf !== false){
      foreach ($tconf as $key => $value){
	if (isset($conf['tpl'][$tpl][$key])) continue;
	$conf['tpl'][$tpl][$key] = $value;
      }
      $tpl_configloaded = true;
    }
  }/*}}}*/

  function tpl_loadConfig( $tpl ) {/*{{{*/
    $file = DOKU_TPLINC.'../'.$tpl.'/conf/default.php';
    $conf = array();
    if (!@file_exists($file)) return false;
    
    include($file);
    return $conf;
  } /*}}}*/

}
