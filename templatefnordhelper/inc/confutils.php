<?php
function getConfigPath($type, $file) {
  global $config_cascade;

  if (!is_array($config_cascade[$type])) trigger_error('Missing config cascade for "'.$type.'"',E_USER_WARNING);
  foreach (array('protected', 'local','default') as $config_group) {
    if (empty($config_cascade[$type][$config_group])) continue;
    foreach( $config_cascade[$type][$config_group] as $path ) {
        if( file_exists( $path.$file )) {
            return $path.$file;
        }
    }
  }
}

