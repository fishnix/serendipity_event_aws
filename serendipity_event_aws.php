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
						case 'backend_image_add';
							global $new_media;
						break;
						case 'frontend_display':
						break;
					}
				}
				return true;
		}
}

?>