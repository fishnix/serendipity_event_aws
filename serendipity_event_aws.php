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

        $propbag->add('name',          PLUGIN_EVENT_AWS_NAME);
        $propbag->add('description',   PLUGIN_EVENT_AWS_DESC);
        $propbag->add('stackable',     false);
        $propbag->add('groups', array('IMAGES'));
        $propbag->add('author',        'E Camden Fisher');
        $propbag->add('version',       '0.0.1');
        $propbag->add('requirements',  array(
            'serendipity' => '1.5.0',
            'smarty'      => '2.6.7',
            'php'         => '5.2.0'
        ));

			$propbag->add('event_hooks',   array(
				'entries_header' => true,
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
				'frontend_image_selector' => true,
				'backend_image_add' => true,
				'backend_image_addHotlink' => true,
				'backend_image_addform' => true,
				'frontend_display' => true
				));

			$this->markup_elements = array(
				array(
					'name'     => 'ENTRY_BODY',
					'element'  => 'body',
					),
				array(
					'name'     => 'EXTENDED_BODY',
					'element'  => 'extended',
					)
				);

        $conf_array = array();

        foreach($this->markup_elements as $element) {
            $conf_array[] = $element['name'];
        }

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
		
    function event_hook($event, &$bag, &$eventData) {
        global $serendipity;
				
				$hooks = &$bag->get('event_hooks');
				
				if (isset($hooks[$event])) {
					switch($event) {
						case 'backend_image_addform':
							if (class_exists('AmazonS3')) {
									$checkedY = "";
									$checkedN = "";
									$this->get_config('s3upload') ? $checkedY = "checked='checked'" : $checkedN = "checked='checked'";
									?>
										<p>
										<strong><?php echo PLUGIN_EVENT_AWS_UPLOAD_FILES;?></strong><br />
										<?php echo PLUGIN_EVENT_AWS_UPLOAD_FILES_DESC;?>
										<p>
											<input type="radio" class="input_radio" id="uploads3_yes" name="serendipity[uploads3]" value="<?php echo YES;?>"
											<?php echo $checkedY;?>><label for="uploads3_yes"><?php echo YES;?></label>
											<input type="radio" class="input_radio" id="uploads3_no" name="serendipity[uploads3]" value="<?php echo NO;?>"
											<?php echo $checkedN;?>><label for="uploads3_no"><?php echo NO;?></label>
										</p>
										</p>
		
									<?php
								}
						break;
						
						case 'backend_image_add':
							global $new_media;
							
							$full_path = $serendipity['serendipityPath'] . $serendipity['uploadPath'];
							$target_img = $eventData;
							preg_match('@(^.*/)+(.*\.+\w*)@', $target_img, $matches);
							$target_dir = $matches[1];
							$filename   = $matches[2];
							
							$fp_length = strlen($full_path);
							$rel_filename = substr($target_img, $fp_length);
							
							$authorid   = (isset($serendipity['POST']['all_authors']) && $serendipity['POST']['all_authors'] == 'true') ? '0' : $serendipity['authorid'];
              
							// only if AmazonS3 class is loaded and radio button for s3 is selected
							if ((class_exists('AmazonS3')) && ($serendipity['POST']['uploads3'] == YES)) {

								// get config information
								$aws_key 				= $this->get_config('aws_key');
								$aws_secret_key = $this->get_config('aws_secret_key');
								$bucket 				= $this->get_config('aws_s3_bucket_name');
							
								$s3 = new AmazonS3($aws_key, $aws_secret_key);

								// upload image to amazon s3								
								$uploadresponse = $s3->create_object($bucket, $rel_filename, array(
									'fileUpload' 		=> $target_img,
									'acl' 					=> $s3::ACL_PUBLIC,
									'storage' 			=> $s3::STORAGE_REDUCED
								));
								
								print_r($uploadresponse);
								
								//$serendipity['serendipityPath'] . $serendipity['uploadPath']
								$createresponse = $s3->create_object($bucket, 'upload-log.txt', array(
									'body' => $full_path . " " . $target_img . " " . $rel_filename,
									'contentType' => 'text/plain',
									'acl' => $s3::ACL_PUBLIC,
									'storage' => $s3::STORAGE_REDUCED
								));

								print_r($createresponse);
								
							}	
						break;
						
						case 'frontend_display':
						break;
						
					}
				}
				return true;
		}
}

?>