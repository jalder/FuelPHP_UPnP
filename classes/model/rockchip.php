<?php
/**
 * UPnP Control for the Rockchip eHomeMediacenter Renderer
 *
 * @author jalder
 * **/
namespace Upnp;

class Model_Rockchip extends \Model
{
	public function play($location = '', $file = '')
	{
		require_once(APPPATH.'/modules/upnp/vendor/phpupnp.class.php');
		$upnp = new \phpUPnP();
		$url = $location.'serviceControl/AVTransport';
		$args = array(
			'InstanceID'=>111,
			'CurrentURI'=>urldecode('http%3A%2F%2Fvideo.ted.com%2Ftalks%2Fpodcast%2FVilayanurRamachandran_2007_480.mp4'), //test URI
			'CurrentURIMetaData'=>'testurimetadata'
		);
		if($file)
		{	
			$args['CurrentURI'] = $file;
			$response = $upnp->sendRequestToDevice('SetAVTransportURI',$args,$url,$type = 'AVTransport');
			var_dump($response);
		}
		return true;
	}
}
