<?php
function tpl_include( $file, $t=false, $allowphp=true ) {
    global $conf, $ID, $INFO;
    static $included_templates;
    if( !is_array( $included_templates )) $included_templates = array( );

    if( !$t ) { $t = $conf['template']; }
    if( !$t || ( !$include = getConfigPath( 'template_dir', $t.'/'.$file )) || in_array( $include, $included_templates )) {
        if( $t != $conf['default_tpl'] )
            $include = getConfigPath( 'template_dir', $conf['default_tpl'].'/'.$file );
    }
    
    if( !$include || in_array( $include, $included_templates )) {
	$include = getConfigPath( 'template_dir', $conf['base_tpl'].'/'.$file );
    }

    if( $allowphp || $conf['tpl_allowphp'] ) {
        $included_templates[] = $include;
        include( $include );
    } else {
        // TODO, read file 
    }
    return $include;

}

// you are here less verbose 
function tpl_youarehere_lv($sep=' &raquo; '){/*{{{*/
  global $conf;
  global $ID;
  global $lang;
  
  // check if enabled
  //if(!$conf['youarehere']) return false;
  
  $parts = explode(':', $ID);
  $count = count($parts);
  
  echo '<span class="bchead">&nbsp;</span>&nbsp;';
  if( $count > 1 ) {                                                                                                                                                            

     // always print the startpage
      $title = useHeading('navigation') ? p_get_first_heading($conf['start']) : $conf['start'];
      if(!$title) $title = $conf['start'];
      #tpl_link(wl($conf['start']),hsc($title),'title="'.$conf['start'].'"');

      // print intermediate namespace links
      $part = '';
      for($i=0; $i<$count - 1; $i++){
        $part .= $parts[$i].':';
        $page = $part;
        resolve_pageid('',$page,$exists);
        if ($page == $conf['start']) continue; // Skip startpage

        // output
	echo "<div><nobr>";
        echo $sep;
        if($exists){
          #$title = useHeading('navigation') ? p_get_first_heading($page) : $parts[$i];
          $title = $parts[$i];
          tpl_link(wl($page),hsc($title),'title="'.$page.'"');
        }else{
          tpl_link(wl($page),$parts[$i],'title="'.$page.'" class="wikilink2" rel="nofollow"');
        }
	echo "</nobr></div>";

      }
      //
      // print current page, skipping start page, skipping for namespace index
      if(isset($page) && $page==$part.$parts[$i]) return;
      $page = $part.$parts[$i];
      if($page == $conf['start']) return;
      echo "<div class='active'><nobr>";
      echo $sep;

      if(page_exists($page)){
        #$title = useHeading('navigation') ? p_get_first_heading($page) : $parts[$i];
        $title = $parts[$i];
        tpl_link(wl($page),hsc($title),'title="'.$page.'"');
      }else{
        tpl_link(wl($page),$parts[$i],'title="'.$page.'" class="wikilink2" rel="nofollow"');
      }
      echo "</nobr></div>";
  }
  
  return true;
}/*}}}*/

// userinfo less verbose
function tpl_userinfo_lv(){
    global $lang;
    global $INFO;
    if(isset($_SERVER['REMOTE_USER'])){
        print $lang['loggedinas'].': ('.hsc($_SERVER['REMOTE_USER']).')';
        return true;
    }
    return false;
}
function tpl_topbar_lv( ){/*{{{*/
        global $INFO;

        $sb=':'.$INFO['namespace'].':topbar';

        $data = p_wiki_xhtml($sb,'',false);                                                                                               
       if(auth_quickaclcheck($sb) >= AUTH_EDIT) {
            $data .= '<div class="secedit">'.html_btn('secedit',$sb,'',array('do'=>'edit','rev'=>'','post')).'</div>';
        }
        $data = preg_replace('/<div class="toc">.*?(<\/div>\n<\/div>)/s', '', $data);

        echo $data;
/*
        #$data = preg_replace('/(<h.*?><a.*?name=")(.*?)(".*?id=")(.*?)(">.*?<\/a><\/h.*?>)/','\1sb_'.$pos.'_\2\3sb_'.$pos.'_\4\5', $data);
*/

}/*}}}*/ 
function tpl_topfnord() {
    global $ID;

    $found = false;
    $tbar  = '';
    $path  = explode(':', $ID);

    while(!$found && count($path) >= 0) {
        $tbar = implode(':', $path) . ':' . 'topbar';
        $found = @file_exists(wikiFN($tbar));
        array_pop($path);
        // check if nothing was found
        if(!$found && $tbar == ':topbar') return;
    }

    if($found && auth_quickaclcheck($tbar) >= AUTH_READ) {
    	print '<div id="menu">';
        print p_wiki_xhtml($tbar,'',false);
	print "</div>";
    }
}

// translation plugin
#function tpl_translation() {
#  $translation = &plugin_load('syntax','translation');
#	echo $translation->_showTranslations();
#}
function tpl_sidebar_lv( ){/*{{{*/
return;
    global $INFO;

        $sb=':'.$INFO['namespace'].':topbar';
        $data = p_wiki_xhtml($sb,'',false);                                                                                               
        if(auth_quickaclcheck($sb) >= AUTH_EDIT) {
            $data .= '<div class="secedit">'.html_btn('secedit',$sb,'',array('do'=>'edit','rev'=>'','post')).'</div>';
         }
         $data = preg_replace('/<div class="toc">.*?(<\/div>\n<\/div>)/s', '', $data);
/*
        #$data = preg_replace('/(<h.*?><a.*?name=")(.*?)(".*?id=")(.*?)(">.*?<\/a><\/h.*?>)/','\1sb_'.$pos.'_\2\3sb_'.$pos.'_\4\5', $data);
*/
         echo $data;

}/*}}}*/ 
