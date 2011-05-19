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
        $propbag->add('groups', 			array('IMAGES'));
        $propbag->add('author',       'E Camden Fisher <fish@fishnix.net>');
        $propbag->add('version',      '0.0.1');
        $propbag->add('requirements', array(
            'serendipity' => '1.5.0',
            'smarty'      => '2.6.7',
            'php'         => '5.2.0'
        ));

			// make it cacheable
			$propbag->add('cachable_events', array(
						'frontend_display' => true));
						
			$propbag->add('event_hooks',   array(
				//entries_header' => true,
				//entry_display' => true,
				//backend_entry_presave' => true,
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
					$propbag->add('name', 					PLUGIN_EVENT_AWS_PROP_AWS_SECRET_KEY);
					$propbag->add('description', 		PLUGIN_EVENT_AWS_PROP_AWS_SECRET_KEY_DESC);
					$propbag->add('default', '');
					$propbag->add('type', 'string');
				break;
				case 'aws_account_id':
					$propbag->add('name', 					PLUGIN_EVENT_AWS_PROP_AWS_ACCOUNT_ID);
					$propbag->add('description',		PLUGIN_EVENT_AWS_PROP_AWS_ACCOUNT_ID_DESC);
					$propbag->add('default', '');
					$propbag->add('type', 'string');
				break;
				case 'aws_canonical_id':
					$propbag->add('name', 					PLUGIN_EVENT_AWS_PROP_AWS_CANONICAL_ID);
					$propbag->add('description', 		PLUGIN_EVENT_AWS_PROP_AWS_CANONICAL_ID_DESC);
					$propbag->add('default', '');
					$propbag->add('type', 'string');
				break;
				case 'aws_canonical_name':
					$propbag->add('name', 					PLUGIN_EVENT_AWS_PROP_AWS_CANONICAL_NAME);
					$propbag->add('description', 		PLUGIN_EVENT_AWS_PROP_AWS_CANONICAL_NAME_DESC);
					$propbag->add('default', '');
					$propbag->add('type', 'string');
				break;
				case 'aws_s3_bucket_name':
					$propbag->add('name', 					PLUGIN_EVENT_AWS_PROP_AWS_S3_BUCKET_NAME);
					$propbag->add('description', 		PLUGIN_EVENT_AWS_PROP_AWS_S3_BUCKET_NAME_DESC);
					$propbag->add('default', '');
					$propbag->add('type', 'string');
				break;
				default:
					return false;
				break;
				
			}
			
			return true;
		}
		
		function install() {
        serendipity_plugin_api::hook_event('backend_cache_entries', $this->title);
    }

    function uninstall() {
        serendipity_plugin_api::hook_event('backend_cache_purge', $this->title);
        serendipity_plugin_api::hook_event('backend_cache_entries', $this->title);
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
								#$thumbname  	= $basename . "." . $serendipity['thumbSuffix'] . "." . $extension;
								$target_thm 	= $target_dir . $basename . "." . $serendipity['thumbSuffix'] . "." . $extension;
							
								$fp_length = strlen($full_path);
								$rel_filename = substr($target_img, $fp_length);
								$rel_thumbname = substr($target_thm, $fp_length);
							
								$authorid   = (isset($serendipity['POST']['all_authors']) && $serendipity['POST']['all_authors'] == 'true') ? '0' : $serendipity['authorid'];
              
								// get config information
								$aws_key 				= $this->get_config('aws_key');
								$aws_secret_key = $this->get_config('aws_secret_key');
								$bucket 				= $this->get_config('aws_s3_bucket_name');
							
								$s3 = new AmazonS3($aws_key, $aws_secret_key);

								if ($s3->if_bucket_exists($bucket)) {
								
									// upload image to amazon s3								
									$media_uploadresponse = $s3->create_object($bucket, $rel_filename, array(
										'fileUpload' 		=> $target_img,
										'acl' 					=> AmazonS3::ACL_PUBLIC,
										'storage' 			=> AmazonS3::STORAGE_REDUCED
									));
									
									//upload thumbnail to amazon s3
									$thumb_uploadresponse = $s3->create_object($bucket, $rel_thumbname, array(
										'fileUpload'		=> $target_thm,
										'acl'						=> AmazonS3::ACL_PUBLIC,
										'storage'				=> AmazonS3::STORAGE_REDUCED
									));
									
									//// TESTING -- WRITE OUT A LOG TO S3
									//// start creating log file for testing
									//$upload_log = "Full Path: " .						$full_path . "\n" . 
									//							"Full Filename: " . 			$target_img . "\n" .
									//							"Relative Filename: " . 	$rel_filename . "\n" . 
									//							"Full Thumbname: " .			$target_thm . "\n" .
									//							"Relative Thumbname: " . 	$rel_thumbname . "\n" .
									//							"Target Dir: " . 					$target_dir . "\n";
								  //
									//// list all props in serendipity
									//foreach ($serendipity as $key=>$value) {
									//	$upload_log = $upload_log . "\n" . $key . ":" . $value;
									//}
								  //
								  //// upload log b/c it's an easy way to see what's going on
									//$createresponse = $s3->create_object($bucket, 'upload-log.txt', array(
									//	'body' => $upload_log,
									//	'contentType' => 'text/plain',
									//	'acl' => AmazonS3::ACL_PUBLIC,
									//	'storage' => AmazonS3::STORAGE_REDUCED
									//));
									
									echo PLUGIN_EVENT_AWS_UPLOAD_SUCCESS;
									
								} else {
									echo PLUGIN_EVENT_AWS_UPLOAD_FAILED;
								}
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
											
											// get the list of items in the bucket 
											// TODO: needs to be async + stored, should be call to cache or DB
											$bucket_list = $this->_s9y_get_s3_list();
											
											$text =  $this->_s9y_aws_munge($eventData[$element], $uploadHTTPPath, $bucket, $bucket_list);
											
											// TESTNG
											//foreach ($bucket_list as $e) {
											//	$text = $text . ' STUFF IN THE BUCKET: ' . $e . "\n";
											//}
											
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
		
		// munge text and replace s9ymdb stuff with s3 links
		// need to workout entry caching
		function _s9y_aws_munge($text, $uploadHTTPPath, $bucket, $bucket_list) {
	
			// set amazon url + bucket name
			$amazonurl = 'https://s3.amazonaws.com' . '/' . $bucket;
			
			// if we are in cache only mode, just replace what we've got in s3
			if ($this->get_config('aws_cache_only')){
				// create an array of patterns and replaces
				$pattern_list = array();
				$replace_list = array();
				
				foreach($bucket_list as $i) {
					$r  = '$1' . $amazonurl . '/' . $i;
					array_push($replace_list, $r);
				
					$p = '(s9ymdb.*)' . $uploadHTTPPath . $i;
					$p = str_replace('/','\/', $p);
					$p = '/' . $p . '/';
					array_push($pattern_list, $p);
				
					// Testing
					#$text = $text . "Pattern: $p  REPLACE: $r \n";
				}
			
				// munge!  note: we are passing 2 arrays here
				$text = preg_replace($pattern_list, $replace_list, $text);

			} else { // otherwise replace all s9y media db stuff
				$pattern = '(s9ymdb.*)' . $uploadHTTPPath;
				$pattern = str_replace('/','\/', $pattern);
				$pattern = '/' . $pattern . '/';
				
				$replace = '$1' . $amazonurl . '/';
				
				$text = preg_replace($pattern, $replace, $text);
			}
			
			// return munged text
			return $text;

		}

		// get a list of stuff in the bucket
		// this should be called async + dropped into DB/memcache
		function _s9y_get_s3_list() {
			
			// set response to empty array
			$response = array();
			
			if ((class_exists('AmazonS3')) && ($this->get_config('using_aws_s3'))) {
				// get config information
				$aws_key 				= $this->get_config('aws_key');
				$aws_secret_key = $this->get_config('aws_secret_key');
				$bucket 				= $this->get_config('aws_s3_bucket_name');
			
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
}

?>