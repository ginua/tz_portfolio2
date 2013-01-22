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

$file = file_get_contents('/var/www/html/paper/appdata.txt');
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

	//echo $k .' = '.print_r($ap);exit(0);
	$tz = new TZ_PortfolioModelArticle();

	$data = array();

	$data['title'] = $ap->title; //Fusce convallis accumsan
	$data['alias'] = str_replace(" ","_",$ap->title);
	$data['articletext'] = $ap->description; 

	
	if(isset($ap->image_url))
		$data['tz_img_gallery_server'][] = downloadFile($ap->image_url,'/tmp/file_cache_'.array_sum( explode( ' ' , microtime() ) ).'.jpg'); //$ap->image_url; 
	elseif (isset($ap->thumbnail_url_big))
		$data['tz_img_gallery_server'][] = downloadFile($ap->thumbnail_url_big,'/tmp/file_cache_'.array_sum( explode( ' ' , microtime() ) ).'.jpg'); //$ap->image_url; 

	if(isset($data['tz_img_gallery_server'][0]))
	{

	/*$data['tz_attachments_file'] = array(	'name' => 	
						array($data['tz_img_gallery_server'][0]),
					      	'type' => array('image/jpeg'),
						'tmp_name' => array($data['tz_img_gallery_server'][0]),
						'size' =>  array(filesize($data['tz_img_gallery_server'][0])));
	*/
	$data['tz_img'] = $data['tz_img_gallery_server'][0];
		
	$data['state'] = 1;
	$data['catid'] = 20;
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
	$data['groupid'] = 1;
	$data['type_of_media'] = 'image';

	$result = $tz->saveCli($data);

	if($result == true)
		echo 'saved article...'.PHP_EOL;
	else
		echo 'did not save '.$data['tz_img'].PHP_EOL;

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
