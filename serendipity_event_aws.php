<?php 

// AWS Plugin for Serendipity
// 20110212 by E Camden Fisher <fishnix@gmail.com>

if (IN_serendipity != true) {
    die ("Don't hack!"); 
}
    
$time_start = microtime(true);
require_once 'sdk-1.2.3/sdk.class.php';

// Probe for a language include with constants. Still include defines later on, if some constants were missing
$probelang = dirname(__FILE__) . '/' . $serendipity['charset'] . 'lang_' . $serendipity['lang'] . '.inc.php';

if (file_exists($probelang)) {
    include $probelang;
}

include_once dirname(__FILE__) . '/lang_en.inc.php';

class serendipity_event_aws extends serendipity_event
{

    function example() 
    {
      echo PLUGIN_EVENT_AWS_INSTALL;
    }

    function introspect(&$propbag)
    {
        global $serendipity;

        $propbag->add('name',         PLUGIN_EVENT_AWS_NAME);
        $propbag->add('description',  PLUGIN_EVENT_AWS_DESC);
        $propbag->add('stackable',    false);
        $propbag->add('groups',       array('IMAGES'));
        $propbag->add('author',       'E Camden Fisher <fish@fishnix.net>');
        $propbag->add('version',      '0.0.2');
        $propbag->add('requirements', array(
            'serendipity' => '1.5.0',
            'smarty'      => '2.6.7',
            'php'         => '5.2.0'
        ));

      // make it cacheable
      $propbag->add('cachable_events', array(
            'frontend_display' => true));
            
      $propbag->add('event_hooks',   array(
        //'entries_header' => true,
        //'entry_display' => true,
        //'backend_entry_presave' => true,
        //'backend_publish' => true,
        //'backend_save' => true,
        //'frontend_image_add_unknown' => true,
        //'frontend_image_add_filenameonly' => true,
        //'frontend_image_selector_submit' => true,
        //'frontend_image_selector_more' => true,
        //'frontend_image_selector_imagecomment' => true,
        //'frontend_image_selector_imagelink' => true,
        //'frontend_image_selector_imagealign' => true,
        //'frontend_image_selector_imagesize' => true,
        //'frontend_image_selector_hiddenfields' => true,
        //'frontend_image_selector' => true,
        'backend_image_add' => true,
        'backend_image_addHotlink' => true,
        'backend_image_addform' => true,
        'frontend_display' => true,
        'backend_preview' => true
        ));

      $this->markup_elements = array(
          array(
            'name'     => 'ENTRY_BODY',
            'element'  => 'body',
          ),
          array(
            'name'     => 'EXTENDED_BODY',
            'element'  => 'extended',
          ),
          array(
            'name'     => 'HTML_NUGGET',
            'element'  => 'html_nugget',
          )
      );

        $conf_array = array();

        foreach($this->markup_elements as $element) {
            $conf_array[] = $element['name'];
        }

        $conf_array[] = 'using_aws_s3';
        $conf_array[] = 'aws_cache_only';
        $conf_array[] = 'aws_key';
        $conf_array[] = 'aws_secret_key';
        $conf_array[] = 'aws_account_id';
        $conf_array[] = 'aws_canonical_id';
        $conf_array[] = 'aws_canonical_name';
        $conf_array[] = 'aws_s3_bucket_name';
				$conf_array[] = 'aws_objlist_mech';

        $propbag->add('configuration', $conf_array);
    }

    function generate_content(&$title) {
      $title = $this->title;
    }

    function introspect_config_item($name, &$propbag) {
      switch($name) {
        case 'using_aws_s3':
          $propbag->add('name',           PLUGIN_EVENT_AWS_PROP_AWS_S3_ON);
          $propbag->add('description',    PLUGIN_EVENT_AWS_PROP_AWS_S3_ON_DESC);
          $propbag->add('default',        'true');
          $propbag->add('type',           'boolean');
        break;
        case 'aws_cache_only':
          $propbag->add('name',           PLUGIN_EVENT_AWS_PROP_AWS_CACHE_ONLY);
          $propbag->add('description',    PLUGIN_EVENT_AWS_PROP_AWS_CACHE_ONLY_DESC);
          $propbag->add('default',        'true');
          $propbag->add('type',           'boolean');
        break;
        case 'aws_key':
          $propbag->add('name',           PLUGIN_EVENT_AWS_PROP_AWS_KEY);
          $propbag->add('description',    PLUGIN_EVENT_AWS_PROP_AWS_KEY_DESC);
          $propbag->add('default',        '');
          $propbag->add('type',           'string');
        break;
        case 'aws_secret_key':
          $propbag->add('name',           PLUGIN_EVENT_AWS_PROP_AWS_SECRET_KEY);
          $propbag->add('description',    PLUGIN_EVENT_AWS_PROP_AWS_SECRET_KEY_DESC);
          $propbag->add('default', '');
          $propbag->add('type', 'string');
        break;
        case 'aws_account_id':
          $propbag->add('name',           PLUGIN_EVENT_AWS_PROP_AWS_ACCOUNT_ID);
          $propbag->add('description',    PLUGIN_EVENT_AWS_PROP_AWS_ACCOUNT_ID_DESC);
          $propbag->add('default', '');
          $propbag->add('type', 'string');
        break;
        case 'aws_canonical_id':
          $propbag->add('name',           PLUGIN_EVENT_AWS_PROP_AWS_CANONICAL_ID);
          $propbag->add('description',    PLUGIN_EVENT_AWS_PROP_AWS_CANONICAL_ID_DESC);
          $propbag->add('default', '');
          $propbag->add('type', 'string');
        break;
        case 'aws_canonical_name':
          $propbag->add('name',           PLUGIN_EVENT_AWS_PROP_AWS_CANONICAL_NAME);
          $propbag->add('description',    PLUGIN_EVENT_AWS_PROP_AWS_CANONICAL_NAME_DESC);
          $propbag->add('default', '');
          $propbag->add('type', 'string');
        break;
        case 'aws_s3_bucket_name':
          $propbag->add('name',           PLUGIN_EVENT_AWS_PROP_AWS_S3_BUCKET_NAME);
          $propbag->add('description',    PLUGIN_EVENT_AWS_PROP_AWS_S3_BUCKET_NAME_DESC);
          $propbag->add('default', '');
          $propbag->add('type', 'string');
        break;
        case 'aws_objlist_mech':
					$options = $this->get_objlist_mech();

          $propbag->add('name',           PLUGIN_EVENT_AWS_PROP_AWS_S3_OBJLIST_MECH);
          $propbag->add('description',    PLUGIN_EVENT_AWS_PROP_AWS_S3_OBJLIST_MECH_DESC);
          $propbag->add('type',        		'select');
          $propbag->add('select_values', 	$this->get_objlist_mech());
          $propbag->add('default',     		0);
        break;
        default:
          return false;
        break;
        
      }
      
      return true;
    }
    
    function install() {
				$this->setupDB();
        serendipity_plugin_api::hook_event('backend_cache_entries', $this->title);
    }

    function uninstall() {
        serendipity_plugin_api::hook_event('backend_cache_purge', $this->title);
        serendipity_plugin_api::hook_event('backend_cache_entries', $this->title);
    }
    
    function cleanup() {
        global $serendipity;
    
        // check AWS S3 class is loaded
        if (class_exists('AmazonS3')) {
          
          // If AWS S3 is disabled
          if (!$this->get_config('using_aws_s3')) {
            $this->outputMSG('error', sprintf(PLUGIN_EVENT_AWS_DISABLED));
          }
          
          // get config information
          $aws_key        = $this->get_config('aws_key');
          $aws_secret_key = $this->get_config('aws_secret_key');
          $bucket         = $this->get_config('aws_s3_bucket_name');

          $s3 = new AmazonS3($aws_key, $aws_secret_key);
          
          // check the bucket exists
          if ($s3->if_bucket_exists($bucket)) {
            $this->outputMSG('success', sprintf(PLUGIN_EVENT_AWS_VERIFIED . ": $bucket/" . $s3->get_bucket_object_count($bucket)));
          } else {
            $this->outputMSG('notice', sprintf(PLUGIN_EVENT_AWS_BAD_BUCKET_OR_CREDS));
            return false;
          }
            
        } else {
          $this->outputMSG('error', sprintf(PLUGIN_EVENT_AWS_MISSING_LIBS));
          return false;
        }

				// ensure the DB is setup when we save config
				$this->setupDB();
				
				// rebuild the aws item cache on config save
				$this->purgeCache();
				$this->buildCache();
        
        // we should rebuild the cache if we change configs
        serendipity_plugin_api::hook_event('backend_cache_purge', $this->title);
        serendipity_plugin_api::hook_event('backend_cache_entries', $this->title);

    }

		/*
		 *
		 * Get list of mechanisms for object list -- we can check available libs, etc here
		 *
		 */
		function get_objlist_mech()
		{
			$mechs = array('database', 'file', 'none');
			
			return($mechs);
		}

		/*
		 *
		 * Purge the object list cache
		 * To be safe, purge should flush every means of storing the object list.
		 *
		 */
		function purgeCache()
		{
				global $serendipity;
				
				$objcache = 'templates_c/foobar.txt';
				if (is_writeable($objcache) && is_file($objcache)) { 
					unlink($objcache); 
				} elseif (!is_writeable($objcache) && is_file($objcache)){ 
					chmod($objcache,0666); 
					unlink($objcache); 
				}
								
				serendipity_db_query("truncate {$serendipity['dbPrefix']}aws_objectlist");
		}
		
		/*
		 *
		 * Build the object list cache
		 * We should only build the mechanism we're using!
		 * 
		 */
		function buildCache()
		{
				global $serendipity;
				
				$i = $this->get_config('aws_objlist_mech');
				$mechlist = $this->get_objlist_mech();
				switch($mechlist[$i]) {
					case 'none':
						// nothin' to do!
						// really! it's loaded everytime frontend_display is called
					break;
				
					case 'database':
						$values = array();
						foreach ($this->s9y_get_s3_list() as $file) { 
							$f = serendipity_db_escape_string($file);
							$values = array(
								'object' => $f,
								'timestamp' => time(),
								'last_modified' => time()
							);
							serendipity_db_insert('aws_objectlist', $values);
						}
						
						$this->outputMSG('success', PLUGIN_EVENT_AWS_WROTE_CACHE_DB);
					break;
					
					case 'file':
						$objects = $this->s9y_get_s3_list();
						$objcache = 'templates_c/foobar.txt';
						
						if (is_array($objects)) {
						
							$fp = @fopen($objcache, 'w');

            	if (!$fp) {
                	$this->outputMSG('error', sprintf(FILE_WRITE_ERROR, $objcache));
                	return $error;
            	}

            	fwrite($fp, implode("\n",$objects));
            	fclose($fp);

            	#$this->fileperm($target, false);

            	$this->outputMSG('success', PLUGIN_EVENT_AWS_WROTE_CACHE_FILE);
						} else {
							$this->outputMSG('error', sprintf(FILE_WRITE_ERROR, $objcache));
						}
						
					break;
				}
		}

		/*
		 * Setup the DB
		 */
    function setupDB()
    {
        global $serendipity;

        $built = $this->get_config('aws_db_built', null);
        if ((empty($built)) && (!defined('AWSDB_SETUP_DONE'))) {
            serendipity_db_schema_import("CREATE TABLE {$serendipity['dbPrefix']}aws_objectlist (
                    id {AUTOINCREMENT} {PRIMARY},
                    object varchar(255) not null default '',
                    timestamp int(10) {UNSIGNED} default null,
                    last_modified int(10) {UNSIGNED} default null) {UTF_8}");

            $this->set_config('aws_db_built', '1');
            @define('AWSDB_SETUP_DONE', true);
        }
    }

    
    function event_hook($event, &$bag, &$eventData) {
        global $serendipity;
        
        $hooks = &$bag->get('event_hooks');
        
        if (isset($hooks[$event])) {
          switch($event) {
            case 'backend_image_addform':
              if (class_exists('AmazonS3')) {
                  $checkedY = "";
                  $checkedN = "";
                  $this->get_config('using_aws_s3') ? $checkedY = "checked='checked'" : $checkedN = "checked='checked'";
                  ?>
                    <p>
                    <strong><?php echo PLUGIN_EVENT_AWS_UPLOAD_FILES;?></strong><br />
                    <?php echo PLUGIN_EVENT_AWS_UPLOAD_FILES_DESC;?>
                    <p>
                      <input type="radio" class="input_radio" id="uploads3_yes" name="serendipity[using_aws_s3]" value="<?php echo YES;?>"
                        <?php echo $checkedY;?>><label for="uploads3_yes"><?php echo YES;?></label>
                      <input type="radio" class="input_radio" id="uploads3_no" name="serendipity[using_aws_s3]" value="<?php echo NO;?>"
                        <?php echo $checkedN;?>><label for="uploads3_no"><?php echo NO;?></label>
                    </p>
                    </p>
    
                  <?php
                }
            break;
            
            case 'backend_image_add':
              // only burn cycles if AmazonS3 class is loaded and radio button for s3 is selected
              if ((class_exists('AmazonS3')) && ($serendipity['POST']['using_aws_s3'] == YES)) {
                
                $full_path = $serendipity['serendipityPath'] . $serendipity['uploadPath'];
                $target_img = $eventData;
              
                preg_match('@(^.*/)+(.*)\.+(\w*)@',$target_img, $matches);
                $target_dir   = $matches[1];
                $basename     = $matches[2];
                $extension    = $matches[3];
                $filename     = $basename.".".$extension;
                $target_thm   = $target_dir . $basename . "." . $serendipity['thumbSuffix'] . "." . $extension;
              
                $fp_length = strlen($full_path);
                $rel_filename = substr($target_img, $fp_length);
                $rel_thumbname = substr($target_thm, $fp_length);
              
                $authorid   = (isset($serendipity['POST']['all_authors']) && $serendipity['POST']['all_authors'] == 'true') ? '0' : $serendipity['authorid'];

								// upload image + thumb
								$this->s9y_aws_upload($this->get_config('aws_key'), $this->get_config('aws_secret_key'), $this->get_config('aws_s3_bucket_name'), $rel_filename, $target_image);
              	$this->s9y_aws_upload($this->get_config('aws_key'), $this->get_config('aws_secret_key'), $this->get_config('aws_s3_bucket_name'), $rel_filename, $target_thm);
              
              	echo PLUGIN_EVENT_AWS_UPLOAD_SUCCESS;
                  
               } else {
                  echo PLUGIN_EVENT_AWS_UPLOAD_FAILED;
               }
            break;
   
            case 'backend_preview':
            case 'frontend_display':
              // only burn cycles if aws is enabled...
              if ($this->get_config('using_aws_s3')) {
                foreach ($this->markup_elements as $temp) {
                  if (serendipity_db_bool($this->get_config($temp['name'], true)) && isset($eventData[$temp['element']])) {
                      $element = $temp['element'];
                      $bucket  = $this->get_config('aws_s3_bucket_name');
                      $uploadHTTPPath = $serendipity['serendipityHTTPPath'] . $serendipity['uploadHTTPPath'];
      
                      $text =  $this->s9y_aws_munge($eventData[$element], $uploadHTTPPath, $bucket, $this->get_config('aws_cache_only'), $this->get_config('aws_objlist_mech'));
                      $eventData[$element] = $text;   
                      
                    }
                  }
                }
              return true;
            break;

            default:
              return false;
            } 
        } else {
        return false;
      }
    }
    
		/*
		 *
		 * Do Upload to S3
		 *
		 */
		function s9y_aws_upload($aws_key, $aws_secret_key, $bucket, $rel_filename, $target_obj) {
			
			$s3 = new AmazonS3($aws_key, $aws_secret_key);

      if ($s3->if_bucket_exists($bucket)) {
      
        // upload image to amazon s3                
        $media_uploadresponse = $s3->create_object($bucket, $rel_filename, array(
          'fileUpload'    => $target_obj,
          'acl'           => AmazonS3::ACL_PUBLIC,
          'storage'       => AmazonS3::STORAGE_REDUCED
        ));

    	}
				// TODO: insert new image into DB!
				// TODO: check upload response!

			return('success');
			
        //// TESTING -- WRITE OUT A LOG TO S3
        //// start creating log file for testing
        //$upload_log = "Full Path: " .           $full_path . "\n" . 
        //              "Full Filename: " .       $target_img . "\n" .
        //              "Relative Filename: " .   $rel_filename . "\n" . 
        //              "Full Thumbname: " .      $target_thm . "\n" .
        //              "Relative Thumbname: " .  $rel_thumbname . "\n" .
        //              "Target Dir: " .          $target_dir . "\n";
        //
        //// list all props in serendipity
        //foreach ($serendipity as $key=>$value) {
        //  $upload_log = $upload_log . "\n" . $key . ":" . $value;
        //}
        //
        //// upload log b/c it's an easy way to see what's going on
        //$createresponse = $s3->create_object($bucket, 'upload-log.txt', array(
        //  'body' => $upload_log,
        //  'contentType' => 'text/plain',
        //  'acl' => AmazonS3::ACL_PUBLIC,
        //  'storage' => AmazonS3::STORAGE_REDUCED
        //));      
		}

		/*
		 *
     *	munge text and replace s9ymdb stuff with s3 links
		 *
		 */
    function s9y_aws_munge($text, $uploadHTTPPath, $bucket, $cacheonly, $objlist_mech) {
  		global $serendipity;

      // set amazon url + bucket name
      $amazonurl = 'https://s3.amazonaws.com' . '/' . $bucket;
      
      // if we are in cache only mode, just replace what we've got in s3
      if ($cacheonly){
        // create an array of patterns and replaces
        $pattern_list = array();
        $replace_list = array();

				#$i = $this->get_config('aws_objlist_mech');
				$mechlist = $this->get_objlist_mech();
				switch($mechlist[$objlist_mech]) {
					case 'none':
						$bucket_list = $this->s9y_get_s3_list();
						
						foreach($bucket_list as $object) {
		         	$r  = '$1' . $amazonurl . '/' . $object;
		         	array_push($replace_list, $r);

		         	$p = '(<!-- s9ymdb:\d+ -->.*)' . $uploadHTTPPath . $object;
		         	$p = str_replace('/','\/', $p);
		         	$p = '/' . $p . '/';
		         	array_push($pattern_list, $p);
		        }
		
					break;
					
					case 'database':
						// incremental changes... will make this better 
 						$bucket_list = serendipity_db_query("SELECT object FROM {$serendipity['dbPrefix']}aws_objectlist");

		        foreach($bucket_list as $i) {
							$object = $i['object'];

		         	$r  = '$1' . $amazonurl . '/' . $object;
		         	array_push($replace_list, $r);

		         	$p = '(<!-- s9ymdb:\d+ -->.*)' . $uploadHTTPPath . $object;
		         	$p = str_replace('/','\/', $p);
		         	$p = '/' . $p . '/';
		         	array_push($pattern_list, $p);
		        }
		
					break;
					
					case 'file':
						$target = 'templates_c/foobar.txt';
						$bucket_list = file($target);
						
						foreach($bucket_list as $i) {
							$object = rtrim($i);

		         	$r  = '$1' . $amazonurl . '/' . $object;
		         	array_push($replace_list, $r);

		         	$p = '(<!-- s9ymdb:\d+ -->.*)' . $uploadHTTPPath . $object;
		         	$p = str_replace('/','\/', $p);
		         	$p = '/' . $p . '/';
		         	array_push($pattern_list, $p);
		
							// Testing
							#$text = $text . "Pattern: $p  REPLACE: $r \n";
		        }
					break;
					
				}
      
        // munge!  note: we are passing 2 arrays here
        $text = preg_replace($pattern_list, $replace_list, $text);

      } else {   // otherwise replace all s9y media db stuff
        $pattern = '(<!-- s9ymdb:\d+ -->.*)' . $uploadHTTPPath;
        $pattern = str_replace('/','\/', $pattern);
        $pattern = '/' . $pattern . '/';
        
        $replace = '$1' . $amazonurl . '/';
        
        $text = preg_replace($pattern, $replace, $text);
      }
      
      // return munged text
      return $text;

    }

		/*
     * Get a list of stuff in the bucket
		 */
    function s9y_get_s3_list() {
      
      // set response to empty array
      $response = array();
      
      if ((class_exists('AmazonS3')) && ($this->get_config('using_aws_s3'))) {
        // get config information
        $aws_key        = $this->get_config('aws_key');
        $aws_secret_key = $this->get_config('aws_secret_key');
        $bucket         = $this->get_config('aws_s3_bucket_name');
      
        $s3 = new AmazonS3($aws_key, $aws_secret_key);
                
        // check the bucket exists
        if ($s3->if_bucket_exists($bucket)) {
          // get the object list from s3
          $response = $s3->get_object_list($bucket);
        }
        
      }
      
      // return the array of items in the response
      return $response;
    }
    
    function outputMSG($status, $msg) {
        switch($status) {
            case 'notice':
                echo '<div class="serendipityAdminMsgNotice"><img style="width: 22px; height: 22px; border: 0px; padding-right: 4px; vertical-align: middle" src="' . serendipity_getTemplateFile('admin/img/admin_msg_note.png') . '" alt="" />' . $msg . '</div>' . "\n";
                break;

            case 'error':
                echo '<div class="serendipityAdminMsgError"><img style="width: 22px; height: 22px; border: 0px; padding-right: 4px; vertical-align: middle" src="' . serendipity_getTemplateFile('admin/img/admin_msg_error.png') . '" alt="" />' . $msg . '</div>' . "\n";
                break;

            default:
            case 'success':
                echo '<div class="serendipityAdminMsgSuccess"><img style="height: 22px; width: 22px; border: 0px; padding-right: 4px; vertical-align: middle" src="' . serendipity_getTemplateFile('admin/img/admin_msg_success.png') . '" alt="" />' . $msg . '</div>' . "\n";
                break;
        }
    }
}

?>