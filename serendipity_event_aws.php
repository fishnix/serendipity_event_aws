<?php 

/*

    AWS Plugin for Serendipity
    E. Camden Fisher <fishnix@gmail.com>
    
*/

if (IN_serendipity != true) {
    die ("Don't hack!"); 
}
    
$time_start = microtime(true);
require_once 'sdk-2.6.15/aws-autoloader.php';
use Aws\S3\S3Client;

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
        $propbag->add('version',      '0.1.0');
        $propbag->add('requirements', array(
            'serendipity' => '1.5.0',
            'smarty'      => '2.6.7',
            'php'         => '5.2.0'
        ));

      // make it cacheable
      $propbag->add('cachable_events', array(
            'frontend_display' => true));
            
      $propbag->add('event_hooks',   array(
        /*'entries_header' => true,
        'entry_display' => true,
        'backend_entry_presave' => true,
        'backend_publish' => true,
        'backend_save' => true,
        'frontend_image_add_unknown' => true,
        'frontend_image_add_filenameonly' => true,
        'frontend_image_selector_submit' => true,
        'frontend_image_selector_more' => true,
        'frontend_image_selector_imagecomment' => true,
        'frontend_image_selector_imagelink' => true,
        'frontend_image_selector_imagealign' => true,
        'frontend_image_selector_imagesize' => true,
        'frontend_image_selector_hiddenfields' => true,
        'frontend_image_selector' => true, */
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
        $conf_array[] = 'aws_s3_bucket_name';
        $conf_array[] = 'aws_s3_bucket_subdir';
        $conf_array[] = 'aws_s3_storage_type';
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
          $propbag->add('default',        'false');
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
        case 'aws_s3_bucket_name':
          $propbag->add('name',           PLUGIN_EVENT_AWS_PROP_AWS_S3_BUCKET_NAME);
          $propbag->add('description',    PLUGIN_EVENT_AWS_PROP_AWS_S3_BUCKET_NAME_DESC);
          $propbag->add('default', '');
          $propbag->add('type', 'string');
        break;
        case 'aws_s3_bucket_subdir':
          $propbag->add('name',           PLUGIN_EVENT_AWS_PROP_AWS_S3_BUCKET_SUBDIR);
          $propbag->add('description',    PLUGIN_EVENT_AWS_PROP_AWS_S3_BUCKET_SUBDIR_DESC);
          $propbag->add('default', '/');
          $propbag->add('type', 'string');
        break;
        case 'aws_s3_storage_type':
          $propbag->add('name',           PLUGIN_EVENT_AWS_PROP_AWS_S3_STORAGE_TYPE);
          $propbag->add('description',    PLUGIN_EVENT_AWS_PROP_AWS_S3_STORAGE_TYPE_DESC);
          $propbag->add('type',        		'select');
          $propbag->add('select_values', 	$this->get_s3_storage_types());
          $propbag->add('default',     		0);
        break;
        case 'aws_objlist_mech':
          $propbag->add('name',           PLUGIN_EVENT_AWS_PROP_AWS_S3_OBJLIST_MECH);
          $propbag->add('description',    PLUGIN_EVENT_AWS_PROP_AWS_S3_OBJLIST_MECH_DESC);
          $propbag->add('type',        		'select');
          $propbag->add('select_values', 	$this->get_objlist_mech());
          $propbag->add('default',     		0);
        break;
        case 'aws_cachefile_name':
          $propbag->add('name',           PLUGIN_EVENT_AWS_PROP_AWS_CACHEFILE_NAME);
          $propbag->add('description',    PLUGIN_EVENT_AWS_PROP_AWS_CACHEFILE_NAME_DESC);
          $propbag->add('default', 'aws_cache');
          $propbag->add('type', 'string');
        break;
        default:
          return false;
        break;
        
      }
      
      return true;
    }
    
    /*
     * install plugin
     */
    function install() {
				$this->setupDB();
        serendipity_plugin_api::hook_event('backend_cache_entries', $this->title);
    }

    /*
     * uninstall plugin
     */
    function uninstall() {
        serendipity_plugin_api::hook_event('backend_cache_purge', $this->title);
        serendipity_plugin_api::hook_event('backend_cache_entries', $this->title);
    }
    
    /*
     * Called when plugin config is saved
     */
    function cleanup() {
        global $serendipity;
    
        // check AWS S3 class is loaded
        if (class_exists('Aws\S3\Command\S3Command')) {
          
          // If AWS S3 is disabled
          if (!$this->get_config('using_aws_s3')) {
            $this->outputMSG('error', sprintf(PLUGIN_EVENT_AWS_DISABLED));
            
            // make sure the object list caches are purged if we are disabled
            $this->purgeCache();
            
            // we should rebuild the cache if we change configs
            serendipity_plugin_api::hook_event('backend_cache_purge', $this->title);
            serendipity_plugin_api::hook_event('backend_cache_entries', $this->title);
            
            return false;
          }
          
          // get config information
          $bucket         = $this->get_config('aws_s3_bucket_name');
          $awsopts = array( "key" => $this->get_config('aws_key'),
                            "secret" => $this->get_config('aws_secret_key'));
                            
          // Instantiate the S3 client with your AWS credentials
          $s3 = S3Client::factory($awsopts);
          
          // check the bucket exists
          if ($s3->doesBucketExist($bucket)) {
            $bpath = $bucket . $this->get_config('aws_s3_bucket_subdir');
            $this->outputMSG('success', sprintf(PLUGIN_EVENT_AWS_VERIFIED . " ($bpath)"));
          } else {
            $this->outputMSG('notice', sprintf(PLUGIN_EVENT_AWS_BAD_BUCKET_OR_CREDS));
            return false;
          }
            
        } else {
          $this->outputMSG('error', sprintf(PLUGIN_EVENT_AWS_MISSING_LIBS));
          return false;
        }

        if ($this->get_config('aws_cache_only')) {
				  // ensure the DB is setup when we save config
				  $this->setupDB();
				
				  // rebuild the aws item cache on config save
				  $this->purgeCache();
				  $this->buildCache();
			  }
        
        // we should rebuild the cache if we change configs
        serendipity_plugin_api::hook_event('backend_cache_purge', $this->title);
        serendipity_plugin_api::hook_event('backend_cache_entries', $this->title);

    }

		/*
		 * Setup the DB
		 */
    function setupDB()
    {
        global $serendipity;

        $built = $this->get_config('aws_db_built', null);
        if ((empty($built)) && (!defined('AWSDB_SETUP_DONE'))) {
            $this->outputMSG('notice', sprintf("Trying to setup DB! " . $serendipity['dbPrefix'] . "aws_objectlist"));
            
            $q = "CREATE TABLE IF NOT EXISTS {$serendipity['dbPrefix']}aws_objectlist (
                  id {AUTOINCREMENT} {PRIMARY},
                  object varchar(255) not null default 0,
                  timestamp int(10) {UNSIGNED} default null,
                  last_modified int(10) {UNSIGNED} default null,
                  INDEX(object)) {UTF_8}";
            
            $this->outputMSG('notice', sprintf("Executing " . $q));
            
            $sql = serendipity_db_schema_import($q);
            
            $this->outputMSG('notice', sprintf("Output " . $sql));
            
            $this->set_config('aws_db_built', '1');
            @define('AWSDB_SETUP_DONE', true);
        }
    }
    
		/*
		 * Get list of mechanisms for object list -- we can check available libs, etc here
		 */
		function get_objlist_mech()
		{
			$mechs = array('database', 'file', 'none');
			return($mechs);
		}
		
		/*
		 * Get list of storage types for s3 -- we can check available libs, etc here
		 */
		function get_s3_storage_types()
		{
			$mechs = array('REDUCED_REDUNDANCY', 'STANDARD');
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
				
				$cachefile = $this->get_config('aws_cachefile_name');
				$objcache = 'templates_c/' . $cachefile;
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
						/* nothin' to do! */
					break;
				
					case 'database':            
			      if ((class_exists('Aws\S3\Command\S3Command')) && ($this->get_config('using_aws_s3'))) {
			        // get config information
			        $bucket = $this->get_config('aws_s3_bucket_name');
      
			        $awsopts = array( "key" => $this->get_config('aws_key'),
			                          "secret" => $this->get_config('aws_secret_key'));
			        $s3 = S3Client::factory($awsopts);
                
			        // check the bucket exists
			        if ($s3->doesBucketExist($bucket)) {
								try {
									$count = 0;
							    $objects = $s3->getIterator('ListObjects', array(
							        'Bucket' => $bucket
							    ));
									
									foreach ($objects as $object) {
										$f = serendipity_db_escape_string($object['Key']);
										$values = array(
											'object' => $f,
											'timestamp' => time(),
											'last_modified' => time()
										);
										serendipity_db_insert('aws_objectlist', $values);
										$count++;
									}
									
									$this->outputMSG('success', PLUGIN_EVENT_AWS_CACHE_UPDATE_SUCCESS . "($count)");
									
								} catch (S3Exception $e) {
									$this->outputMSG('error', PLUGIN_EVENT_AWS_CACHE_UPDATE_FAILURE);
									$this->outputMSG('error', $e->getMessage() . "\n");
								}
							} else {
								$this->outputMSG('error', PLUGIN_EVENT_AWS_CACHE_UPDATE_FAILURE);
							}
						}
					break;
					
					case 'file':
						
			      if ((class_exists('Aws\S3\Command\S3Command')) && ($this->get_config('using_aws_s3'))) {
							
							$cachefile = $this->get_config('aws_cachefile_name');
							$objcache = 'templates_c/' . $cachefile;
							$maxkeys = 1000;
						
							// truncate the cachefile first
							$f = @fopen($objcache, "r+");
							if ($f !== false) {
							    ftruncate($f, 0);
							    fclose($f);
							}
						
			        // get config information
			        $bucket = $this->get_config('aws_s3_bucket_name');
      
			        $awsopts = array( "key" => $this->get_config('aws_key'),
			                          "secret" => $this->get_config('aws_secret_key'));
			        $s3 = S3Client::factory($awsopts);
                
			        // check the bucket exists
			        if ($s3->doesBucketExist($bucket)) {
								try {
									$count = 0;
							    $objects = $s3->getIterator('ListObjects', array(
							        'Bucket' => $bucket,
											'MaxKeys' => $maxkeys
							    ));
									
									foreach ($objects as $object) {
										file_put_contents($objcache, $object['Key'] . "\n", FILE_APPEND | LOCK_EX);
										$count++;
									}
									
									$this->outputMSG('success', PLUGIN_EVENT_AWS_CACHE_UPDATE_SUCCESS . "($count)");
									
									// if ($objects['IsTruncated']) {
									// 	$this->outputMSG('success', PLUGIN_EVENT_AWS_CACHE_UPDATE_SUCCESS . "($objcache : $count)");
									// } else {
									// 	$this->outputMSG('error', PLUGIN_EVENT_AWS_CACHE_UPDATE_FAILURE . " Object list truncated by AWS!");
									// }
									
								} catch (S3Exception $e) {
									$this->outputMSG('error', PLUGIN_EVENT_AWS_CACHE_UPDATE_FAILURE);
									$this->outputMSG('error', $e->getMessage() . "\n");
								}
							} else {
								$this->outputMSG('error', PLUGIN_EVENT_AWS_CACHE_UPDATE_FAILURE);
							}
						}
					break;
				}
		}

    function event_hook($event, &$bag, &$eventData) {
        global $serendipity;
        
        $hooks = &$bag->get('event_hooks');
        
        if (isset($hooks[$event])) {
          switch($event) {
            case 'backend_image_addform':
              if (class_exists('Aws\S3\Command\S3Command')) {
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
              // only burn cycles if S3Command class is loaded and radio button for s3 is selected
              if ((class_exists('Aws\S3\Command\S3Command')) && ($serendipity['POST']['using_aws_s3'] == YES)) {
                
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
								
								/*
              	$this->outputMSG('notice', sprintf("target_image: $target_img"));
              	$this->outputMSG('notice', sprintf("target_thumb: $target_thm"));
                */
								
								$uploads = array( $rel_filename => $target_img,
								                  $rel_thumbname => $target_thm );

								// upload image + thumb
								$validate = $this->s9y_aws_upload($uploads);
								
								if (is_array($validate)) {
								  foreach ($validate as $object => $success) {
								    if ($success) {
								      $this->outputMSG('success', sprintf(PLUGIN_EVENT_AWS_UPLOAD_SUCCESS . " $object"));
								      // add to cache only if we are in cache-only mode
								      if ($this->get_config('aws_cache_only')) {
								        $this->add_object_to_cache($object);
							        }
								    } else {
								      $this->outputMSG('error', sprintf(PLUGIN_EVENT_AWS_UPLOAD_FAILED . " $object"));
								    }
								  }
								} else {
								  $this->outputMSG('error', sprintf(PLUGIN_EVENT_AWS_UPLOAD_FAILED . " $object"));
								}
                
              } else {
							  $this->outputMSG('error', sprintf(PLUGIN_EVENT_AWS_UPLOAD_FAILED));
              }
              
              return true;
            break;
   
            case 'backend_preview':
            case 'frontend_display':
              // only burn cycles if aws is enabled...
              if ($this->get_config('using_aws_s3')) {
                foreach ($this->markup_elements as $temp) {
                  if (serendipity_db_bool($this->get_config($temp['name'], true)) && isset($eventData[$temp['element']])) {
                      $element = $temp['element'];
                      $uploadHTTPPath = $serendipity['serendipityHTTPPath'] . $serendipity['uploadHTTPPath'];
											// error_log("Working with element: " . $element);
                      $eventData[$element] =  $this->s9y_aws_munge($eventData[$element], $uploadHTTPPath);
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
		 * Upload to S3
		 *
		 */
		function s9y_aws_upload(array $uploads) {
		  
			$bucket = $this->get_config('aws_s3_bucket_name');
      $awsopts = array( "key" => $this->get_config('aws_key'),
                        "secret" => $this->get_config('aws_secret_key'));
      $s3 = S3Client::factory($awsopts);

      if ($s3->doesBucketExist($bucket)) {
      
        $validate = array();
        foreach ($uploads as $rel_filename => $target_obj) {
          
          $i = $this->get_config('aws_s3_storage_type');
  				$storagetype = $this->get_s3_storage_types();

  				switch($storagetype[$i]) {
  					case 'REDUCED_REDUNDANCY':
              // upload image to amazon s3                
              $media_uploadresponse = $s3->putObject(array(
								'Key'						=> $rel_filename,
								'Bucket'				=> $bucket,
                'SourceFile'    => $target_obj,
                'ACL'           => AmazonS3::ACL_PUBLIC,
                'storage'       => AmazonS3::STORAGE_REDUCED
              )); 
            break;
  					case 'STANDARD':
    					// upload image to amazon s3                
              $media_uploadresponse = $s3->putObject(array(
								'Key'						=> $rel_filename,
								'Bucket'				=> $bucket,
                'SourceFile'    => $target_obj,
                'ACL'           => AmazonS3::ACL_PUBLIC,
                'storage'       => AmazonS3::STORAGE_STANDARD
              )); 
                
  					break;
					}
            
          if ($s3->doesObjectExist($bucket, $rel_filename)) {
            $validate[$rel_filename] = true;
				  } else {
				    $validate[$rel_filename] = false;
				  }
    	  }
    	  
    	  return $validate;
    	}
    	
			return null;
			
		}

		/*
		 *
		 * Add item to cache
		 *
		 */
		function add_object_to_cache($filename) {
			
			global $serendipity;
			
			$i = $this->get_config('aws_objlist_mech');
			
			$mechlist = $this->get_objlist_mech();
			$result = "";
			switch($mechlist[$i]) {
				case 'none':
					/* nothin' to do! */
				break;
			
				case 'database':
					$values = array();
					
					$f = serendipity_db_escape_string($filename);
					$query = "SELECT id FROM {$serendipity['dbPrefix']}aws_objectlist WHERE object like '$f'";
					$id = serendipity_db_query($query , true);
					
					$value = array(
						'object' => $f,
						'timestamp' => time(),
						'last_modified' => time()
						);
					
					if ($id) {
						$result = serendipity_db_update('aws_objectlist', array('id' => $id), $value);
					}	else {
						$result = serendipity_db_insert('aws_objectlist', $value);
					}
          // TODO: add validation of cache update
          $this->outputMSG('notice', PLUGIN_EVENT_AWS_CACHE_UPDATE_SUCCESS);		
				break;
				
				case 'file':
				  $cachefile = $this->get_config('aws_cachefile_name');
					$objcache = 'templates_c/' . $cachefile;
					
					$result = file_put_contents($objcache, $filename, FILE_APPEND | LOCK_EX);
					
					// TODO: add validation of cache update
					$this->outputMSG('notice', PLUGIN_EVENT_AWS_CACHE_UPDATE_SUCCESS);
					
					break;
				}
			
			return($result);
		}

		/*
		 *
     *	munge text and replace s9ymdb stuff with s3 links
		 *
		 */
    function s9y_aws_munge($text, $uploadHTTPPath) {
  		global $serendipity;

			// error_log("TEXT: $text");

      // set amazon url + bucket name
      $amazonurl = 'https://s3.amazonaws.com' . '/' . $this->get_config('aws_s3_bucket_name');

      // if we are in cache only mode, only replace what we've got in s3
      if ($this->get_config('aws_cache_only')){
				$images = array();
        // create an array of patterns and replaces
        $pattern_list = array();
        $replace_list = array();
				
				// get the pattern to find all images
				$pattern = $this->build_image_pattern($uploadHTTPPath);
				
				// find all of the images in the given text
				$nummatches = preg_match_all($pattern, $text, $images);

        $objlist_mech = $this->get_config('aws_objlist_mech');
				$mechlist = $this->get_objlist_mech();
				switch($mechlist[$objlist_mech]) {
					case 'none':					
		        $bucket = $this->get_config('aws_s3_bucket_name');  
		        $awsopts = array( "key" => $this->get_config('aws_key'),
		                          "secret" => $this->get_config('aws_secret_key'));	
		        $s3 = S3Client::factory($awsopts);
            
		        // check the bucket exists
		        if ($s3->doesBucketExist($bucket)) {
							// for each img we found earlier, check if it exists
							// in S3, if so, add it to the pattern_list and the
							// replacement to the replacement_list
							foreach($images[1] as $imgrel) {
								try {
									// error_log("Checking if /$imgrel exists at amazon!");
									if($s3->doesObjectExist($bucket, '/' . $imgrel)) {
										// error_log("/$imgrel exists at amazon!");
										
										$srcpat = '"' . $uploadHTTPPath . $imgrel . '"'; 
										$reppat = $amazonurl . '/' . $imgrel;
										array_push($pattern_list, preg_quote($srcpat));
										array_push($replace_list, $reppat);
									} else {
										error_log("$imgrel does not exist at amazon!");
									}
								} catch (S3Exception $e) {
									error_log("ERROR checking S3 for image: /" . $imgrel);
								}	
							}
						} else {
							error_log("ERROR Bucket does not exist: " . $bucket);
						}
					break;
					
					case 'database':
					
						// for each img we found earlier, check if it exists
						// in the db object cache, if so, add it to the pattern_list 
						// and the replacement to the replacement_list
						foreach($images[1] as $imgrel) {
							try {
								$whereobj = serendipity_db_escape_string($imgrel);
								$query = "SELECT object FROM {$serendipity['dbPrefix']}aws_objectlist WHERE object LIKE '$whereobj'";
								$cached_obj = serendipity_db_query($query, true);
								// error_log("Checking if " . $imgrel . " exists in the database! Query: " . $query . " Result: " . $cached_obj[0]);
								
								if ($imgrel == $cached_obj[0]) {
									// error_log("$imgrel exists in the database!");
									$srcpat = '"' . $uploadHTTPPath . $imgrel . '"';
									$reppat = $amazonurl . '/' . $imgrel;
									array_push($pattern_list, preg_quote($srcpat));
									array_push($replace_list, $reppat);
								} else {
									error_log("$imgrel does not exist in the database cache!");
								}
							} catch (Exception $e) {
								error_log("ERROR checking DB cache for image: " . $imgrel . "Exception: " . $e->getMessage());
							}	
						}

					break;
					
					case 'file':
					  $cachefile = $this->get_config('aws_cachefile_name');
						$target = 'templates_c/' . $cachefile;
						
						if(file_exists($target)) {
							$filecontents = file($target);
							
							// for each img we found earlier, check if it exists
							// in the file object cache, if so, add it to the pattern_list 
							// and the replacement to the replacement_list
							foreach($images[1] as $imgrel) {
								try {
									// error_log("Checking if " . $imgrel . " exists in the file cache! File: " . $target);
									$cached_obj = preg_grep("#^$imgrel#", file($target));
									$cached_obj = array_shift($cached_obj);
									$cached_obj = rtrim($cached_obj);
									// error_log("Result: " . $cached_obj);
									
									if($cached_obj == $imgrel) {
										// error_log("$imgrel exists in the file cache!");
										$srcpat = '"' . $uploadHTTPPath . $imgrel . '"';
										$reppat = $amazonurl . '/' . $imgrel;
										array_push($pattern_list, preg_quote($srcpat));
										array_push($replace_list, $reppat);
									} else {
										error_log("$imgrel does not exist in the file cache!");
									}
								} catch (Exception $e) {
									error_log("ERROR checking file cache for image: " . $imgrel . "Exception: " . $e->getMessage());
								}	
							}
						} else {
							error_log("ERROR: file cache ". $target . "does not exist.");
						}
					break;
					
				}
      
        /* munge!  note: we are passing 2 arrays here */
        $text = preg_replace($pattern_list, $replace_list, $text);

      } else {   
				// TODO: revisit this with new pattern matching!
				// this replaces everything up to the upload path... probably we should
				// replace the src=".*?" instead
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
		 * Build the image finding pattern
		 */
		function build_image_pattern($uploadHTTPPath) {
			$pattern = '/';
			$pattern = $pattern . preg_quote('<!-- s9ymdb:', '/');
			$pattern = $pattern . '\d+\s+';
			$pattern = $pattern . preg_quote('--><img', '/');
			$pattern = $pattern . '.*?';
			$pattern = $pattern . preg_quote('src="', '/');
			$pattern = $pattern . preg_quote($uploadHTTPPath, '/');
			$pattern = $pattern . '(.*?)".*?';
			$pattern = $pattern . preg_quote('/>', '/');
			$pattern = $pattern . '/';
			
			return $pattern;
		}

		/*
     * Get a list of stuff in the bucket
		 */
    function s9y_get_s3_list() {
      
      // set response to empty array
      $response = array();
      
      if ((class_exists('Aws\S3\Command\S3Command')) && ($this->get_config('using_aws_s3'))) {
        // get config information
        $bucket         = $this->get_config('aws_s3_bucket_name');
      
        $awsopts = array( "key" => $this->get_config('aws_key'),
                          "secret" => $this->get_config('aws_secret_key'));
        $s3 = S3Client::factory($awsopts);
                
        // check the bucket exists
        if ($s3->doesBucketExist($bucket)) {
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
