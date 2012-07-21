<?php
if(!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/../../');
if(!defined('NOSESSION')) define('NOSESSION',true); // we do not use a session or authentication here (better caching)
if(!defined('DOKU_DISABLE_GZIP_OUTPUT')) define('DOKU_DISABLE_GZIP_OUTPUT',1); // we gzip ourself here
require_once(DOKU_INC.'inc/init.php');
require_once(DOKU_INC.'inc/pageutils.php');
require_once(DOKU_INC.'inc/httputils.php');
require_once(DOKU_INC.'inc/io.php');
require_once(DOKU_INC.'inc/confutils.php');

// Main (don't run when UNIT test)
if(!defined('SIMPLE_TEST')){
    header('Content-Type: text/css; charset=utf-8');
    css_out_tfh();
}


// ---------------------- functions ------------------------------

/**
 * Output all needed Styles
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function css_out_tfh(){
    global $conf;
    global $lang;
    global $config_cascade;

    $mediatype = 'screen';
    if (isset($_REQUEST['s']) &&
        in_array($_REQUEST['s'], array('all', 'print', 'feed'))) {
        $mediatype = $_REQUEST['s'];
    }

    $tpl = trim(preg_replace('/[^\w-]+/','',$_REQUEST['t']));
    if($tpl){
        #$tplinc = DOKU_INC.'lib/tpl/'.$tpl.'/';
        $tpldir = DOKU_BASE.'lib/tpl/'.$tpl.'/';
    }else{
        #$tplinc = DOKU_TPLINC;
        $tpldir = DOKU_TPL;
    }

    $cache = '';

    // changes for farming start here

    // meant to detect changes in config_cascade
    foreach( $config_cascade['template_dir'] as $k => $v ) {
        $cache.= str_replace( array( ':', '/' ), '', implode( $v ));   
    }
    $cache .= $tpl;

    // The generated script depends on some dynamic options
    $cache = new cache('styles'.$_SERVER['HTTP_HOST'].$_SERVER['SERVER_PORT'].DOKU_BASE.$cache.$mediatype,'.css');

    // load template styles
    $tplstyles = array();
    $style_ini = css_getpath( $tpl, 'style.ini' );
    if( $style_ini ){
        $ini = parse_ini_file( $style_ini, true);
        if( count( $ini )) {
            foreach($ini['stylesheets'] as $file => $mode){
                $tplstyles[$mode][css_getpath( $tpl, $file )] = $tpldir;
            }
        }
    }

    // Array of needed files and their web locations, the latter ones
    // are needed to fix relative paths in the stylesheets
    $files   = array();
    // load core styles
    // compatibility with 2010-11-07a
    if(!isset($_REQUEST['s'])) {
        $files[DOKU_INC.'lib/styles/style.css'] = DOKU_BASE.'lib/styles/';    // compatibility with 2010-11-07a
    }

    $files[DOKU_INC.'lib/styles/'.$mediatype.'.css'] = DOKU_BASE.'lib/styles/';
    // load jQuery-UI theme
    $files[DOKU_INC.'lib/scripts/jquery/jquery-ui-theme/smoothness.css'] = DOKU_BASE.'lib/scripts/jquery/jquery-ui-theme/';
    // load plugin styles
    $files = array_merge($files, css_pluginstyles($mediatype));
    // load template styles
    if (isset($tplstyles[$mediatype])) {
        $files = array_merge($files, $tplstyles[$mediatype]);
    }
    // if old 'default' userstyle setting exists, make it 'screen' userstyle for backwards compatibility
    if (isset($config_cascade['userstyle']['default'])) {
        $config_cascade['userstyle']['screen'] = $config_cascade['userstyle']['default'];
    }
    // load user styles
    if(isset($config_cascade['userstyle'][$mediatype])){
        $files[$config_cascade['userstyle'][$mediatype]] = DOKU_BASE;
    }
    // load rtl styles
    // @todo: this currently adds the rtl styles only to the 'screen' media type
    //        but 'print' and 'all' should also be supported

    if ($mediatype=='screen') {
        if($lang['direction'] == 'rtl'){
            if (isset($tplstyles['rtl'])) $files = array_merge($files, $tplstyles['rtl']);
        }
    }

    $cache_files = array_merge(array_keys($files), getConfigFiles('main'));
    $cache_files[] = $tplinc.$style_ini;
    $cache_files[] = __FILE__;


    // check cache age & handle conditional request
    // This may exit if a cache can be used
    http_cached($cache->cache,
                $cache->useCache(array('files' => $cache_files)));

    // start output buffering and build the stylesheet
    ob_start();

    // print the default classes for interwiki links and file downloads
    css_interwiki();
    css_filetypes();

    // load files
    foreach($files as $file => $location){
        print css_loadfile($file, $location);
    }

    // end output buffering and get contents
    $css = ob_get_contents();
    ob_end_clean();

    // apply style replacements
    $css = css_applystyle_tfh($css,$tpl);   // removed tplinc

    // place all @import statements at the top of the file
    $css = css_moveimports($css);


    // compress whitespace and comments
    if($conf['compress']){
        $css = css_compress($css);
    }

    // embed small images right into the stylesheet
    if($conf['cssdatauri']){
        $base = preg_quote(DOKU_BASE,'#');
        $css = preg_replace_callback('#(url\([ \'"]*)('.$base.')(.*?(?:\.(png|gif)))#i','css_datauri',$css);
    }

    http_cached_finish( $cache->cache, $css);
}


/**
 * Does placeholder replacements in the style according to
 * the ones defined in a templates style.ini file
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function css_applystyle_tfh($css, $tpl ){
    global $conf;

      if( !$file = getConfigPath( 'template_dir', $tpl.'/style.ini' ))
        $file = getConfigPath( 'template_dir', $conf['default_tpl'].'/style.ini' );

    $ini = parse_ini_file( $file, true);
    $css = strtr($css,$ini['replacements']);

    return $css;
}


function css_getpath( $t, $file ) {
    global $conf;

    if( !$t ) { $t = $conf['template']; }
    if( !$t || !$include = getConfigPath( 'template_dir', $t.'/'.$file )) {

        if( $conf['template'] && $t != $conf['template'] )
            $include = getConfigPath( 'template_dir', $conf['template'].'/'.$file );
        elseif( $conf['default_tpl'] && $t != $conf['default_tpl'] )
            $include = getConfigPath( 'template_dir', $conf['default_tpl'].'/'.$file );
    }
    if( !$include ) {
        $include = getConfigPath( 'template_dir', $conf['base_tpl'].'/'.$file );
    }
#echo "include($file): $include<br>\n";

    return $include; 

}
//Setup VIM: ex: et ts=4 enc=utf-8 :
