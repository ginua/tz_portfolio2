<?php
error_reporting(E_ALL);
ini_set('display_error', 1);

define('_JEXEC', 1);

define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', dirname(__FILE__).DS.'administrator');
define('JPATH_COMPONENT', JPATH_BASE .DS.'/../components/' . 'com_tz_portfolio');
define('JPATH_COMPONENT_SITE', JPATH_BASE . '/../components/' . 'com_tz_portfolio');
define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_BASE.'/components/' . 'com_tz_portfolio');

require_once( JPATH_BASE .DS.'includes'.DS.'defines.php' );
require_once( JPATH_BASE .DS.'includes'.DS.'framework.php' );
require_once( JPATH_BASE .DS.'..'.DS.'libraries'.DS.'joomla'.DS.'user'.DS.'user.php' );
require_once( JPATH_BASE .DS.'..'.DS.'libraries'.DS.'joomla'.DS.'application'.DS.'component'.DS.'helper.php' );
// Define component path.

require_once( JPATH_BASE .DS.'components'.DS.'com_tz_portfolio'.DS.'models'.DS.'article.php' );

jimport('joomla.database.database');
jimport('joomla.database.table' );
jimport('joomla.application.component.helper'); 


$jconfig = new JConfig();
$options        = array ( 'driver' => $jconfig->dbtype,
                          'host' => $jconfig->host ,
                          'user' => $jconfig->user,
                          'password' => $jconfig->password,
                          'database' =>$jconfig->db,
                          'prefix' => $jconfig->dbprefix );

$mainframe      =& JFactory::getApplication('administrator');

$file = file_get_contents('/var/www/html/appdata.txt');
$appdata = json_decode($file);

if(!isset($appdata))
{
	echo "I didn't find the json I needed"; 
	exit(0);
}
else
{
	echo "Finished converting json data.".PHP_EOL;
}
	



foreach($appdata as $id => $app)
{
	foreach($app as $k => $ap)
	{

	

		$primaryDb =& JDatabase::getInstance( $options );
		$q = 'SELECT contentid FROM #__tz_portfolio WHERE value=\''.$ap->mimvi_app_id.'\'';
		$primaryDb->setQuery($q);
		$result = $primaryDb->loadResult();

		if(isset($result) && $result != '')
		{
			echo 'app is already in the db...'.$ap->mimvi_app_id.PHP_EOL;
		} else {

			$content = '<p>By: '.$ap->author.'</p>';
			$content .= '<p>Available: <a href="'.$ap->download_url.'">'. ucfirst($ap->platform) .' Store</a></p>';
			$content .= '<p>'.$ap->description.'</p><p></p>';
			//echo $k .' = '.print_r($ap);exit(0);
			$tz = new TZ_PortfolioModelArticle();

			$data = array();

			$data['title'] = $ap->title; 
			$data['alias'] = str_replace(" ","_",$ap->title);

			if($ap->rating == '1.0' || $ap->rating == '1.5')
			$rating = 0;
			switch($ap->rating)
			{
				case '1.0':
				case '0.5';
				case '0.0';
					$rating = 1;
					break;
				case '2.0':
				case '2.5';
					$rating = 2;
					break;
				case '3.0':
				case '3.5';
					$rating = 3;
					break;
				case '4.0':
				case '4.5';
					$rating = 4;
					break;
				case '5.0':
				case '5.5';
					$rating = 5;
					break;
				default:
					$rating = 0;
			}

			if($rating != 0)
				$content .= '<p>App Store Rating<br /><img src="images/'.$rating.'-stars.png" border="0" height="64" style="line-height: 1.3em; border: 0;" /></p>';
	
			if(isset($ap->thumbnail_url_big) && 
				(strpos($ap->thumbnail_url_big,'tif') != strlen($ap->thumbnail_url_big)-3) &&
				(strpos($ap->thumbnail_url_big,'tif') != strlen($ap->thumbnail_url_big)-4))
			{
				$data['tz_img_gallery_server'][] = downloadFile($ap->thumbnail_url_big,'/tmp/file_cache_'.array_sum( explode( ' ' , microtime() ) ).'.jpg'); //$ap->image_url; 
			
			}
			elseif (isset($ap->image_url))
				$data['tz_img_gallery_server'][] = downloadFile($ap->image_url,'/tmp/file_cache_'.array_sum( explode( ' ' , microtime() ) ).'.jpg'); //$ap->image_url; 

			if(isset($data['tz_img_gallery_server'][0]))
			{

	/*$data['tz_attachments_file'] = array(	'name' => 	
						array($data['tz_img_gallery_server'][0]),
					      	'type' => array('image/jpeg'),
						'tmp_name' => array($data['tz_img_gallery_server'][0]),
						'size' =>  array(filesize($data['tz_img_gallery_server'][0])));
	*/
				$data['tz_img'] = $data['tz_img_gallery_server'][0];
				$data['articletext'] = $content;		
				$data['tzfields2'] = '&lt;a href=&quot;'.$ap->download_short_url.'&quot; target=&quot;_self&quot;&gt;Download&lt;/a&gt;';
				$data['tzfields3'] = $ap->platform; 
				$data['tzfields4'][] = $ap->rating; 
				$data['tzfields5'] = $ap->num_ratings; 
				$data['tzfields6'] = $ap->author; 
				$data['tzfields7'] = $ap->price; 
				$data['tzfields8'] = $ap->mimvi_app_id; 
				$data['tzfields9'] = $ap->id; 
				$data['state'] = 1;

				switch ($ap->platform) {
					case 'itunes':
						$data['catid'] = 8;
						break;
					case 'android':
						$data['catid'] = 22;
						break;
					case 'blackberry':
						$url = substr($ap->image_url,0,strlen($ap->image_url)-1).'12';
						$data['tz_img_gallery_server'][0] = downloadFile($url, '/tmp/file_cache_'.array_sum( explode(' ', microtime() )).'.png');
						$data['catid'] = 13;
						break;
					case 'windows':
						$data['catid'] = 12;
						break;
					case 'ipad':
						$data['catid'] = 9;
						break;
					case 'iphone':
						$data['catid'] = 10;
						break;
					case 'facebook':
						$data['catid'] = 20;
						break;
					case 'mobileweb':
						$data['catid'] = 14;
						break;
					default:
						$data['catid'] = 8;
				}

				$data['created'] = date('Y-m-d'); // 2012-06-25 09:22:10
				$data['created_by'] = 203;
				$data['access'] = 1;
				$data['featured'] = 0;
				$data['attribs'] = array();
				$data['attribs']['adjustX'] = 0;
				$data['attribs']['adjustY'] = 0;
				$data['attribs']['tint'] = 0;
				$data['attribs']['tintOpacity'] = 0.5;
				$data['attribs']['lensOpacity'] = 0.5;
				$data['attribs']['softFocus'] = 0;
				$data['attribs']['smoothMove'] = 3;
				$data['attribs']['titleOpacity'] = 0.5;
				$data['attribs']['image_gallery_amination'] = 'none';
				$data['attribs']['image_gallery_animSpeed'] = 7000;
				$data['attribs']['image_gallery_animation_duration'] = 600;
				$data['groupid'] = 2;
				$data['type_of_media'] = 'image';

				$result = $tz->saveCli($data);

				if($result == true)
					echo 'saved article...'.PHP_EOL;
				else
					echo 'did not save '.$data['tz_img'].PHP_EOL;

			}
		}
	}
}

function downloadFile ($url, $path) {
  echo $url . ' => '. $path.PHP_EOL;
  if( $url  == '') return null;
  if( !isset($url) ) return null;
  if( $url == " ") return null;

  $newfname = $path;
  $file = fopen ($url, "rb");
  if ($file) {
    $newf = fopen ($newfname, "wb");

    if ($newf)
    while(!feof($file)) {
      fwrite($newf, fread($file, 1024 * 8 ), 1024 * 8 );
    }

  if ($file) {
    fclose($file);
  }

  if ($newf) {
    fclose($newf);
  }

  }

	return $newfname;
 }

?>
