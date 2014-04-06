<?php
/** AVTransport UPnP Class
 * Used for controlling renderers
 *
 * @author jalder
 */

namespace Upnp;

class Model_Renderer extends \Model
{

	/**
	 * Renderers may have the following UPnP Methods:
	 * SetAVTransportURI
	 * SetNextAVTransportURI
	 * GetMediaInfo
	 * GetMediaInfo_Ext
	 * GetTransportInfo
	 * GetPositionInfo
	 * GetDeviceCapabilities
	 * GetTransportSettings
	 * Stop
	 * Play
	 * Pause
	 * Record
	 * Seek
	 * Next
	 * Previous
	 * Ext_Exit
	 * SetPlayMode
	 * SetRecordQualityMode
	 * GetCurrentTransportActions
	 */

	public $ctrlurl;
	private $upnp;

	public function __construct($ctrlurl = '')
	{
		require_once(APPPATH.'/modules/upnp/vendor/phpupnp.class.php');
		$this->upnp = new \phpUPnP();
		if($ctrlurl)
		{
			$this->ctrlurl = $ctrlurl;
		}
	}

	public function loadCtrlFromDesc($description_url)
	{
		$this->ctrlurl = $this->getControlURL($description_url, 'AVTransport');
	}

	public function search()
	{
		$found = array();
		$found = $this->upnp->mSearch('urn:schemas-upnp-org:service:AVTransport:1');
		$results = array();
		foreach($found as $renderer)
		{
			$curl = \Request::forge($renderer['location'],'curl');
			$data = $curl->execute()->response();
			$description = \Format::forge($data->body,'xml:ns')->to_array();
			$results[$renderer['location']] = $description;
		}
		return $results;
	}

	public function play($description_url,$url)
	{
//		var_dump($description_url); die();
		$args = array(
			'InstanceID'=>0,
			'CurrentURI'=>'<![CDATA['.$url.']]>',
			//'CurrentURI' => $url,
			'CurrentURIMetaData'=>'testmetadata'
		);
		var_dump($description_url);
		$ctrlurl = $this->getControlURL($description_url, 'AVTransport');
		var_dump($ctrlurl);
		var_dump($args);
		$response = $this->upnp->sendRequestToDevice('SetAVTransportURI',$args,$ctrlurl,$type = 'AVTransport');
		//$this->instanceOnly('Play');
		var_dump($response); //die();
		//one upnp device automatically started playing
		//the other (XBMC upnp client) requires you to send a Play command
		$args = array('InstanceID'=>0,'Speed'=>1);
		$this->upnp->sendRequestToDevice('Play',$args,$ctrlurl,$type = 'AVTransport');
		return $response;
	}

	//this should be moved to the upnp model
	public function getControlURL($description_url, $service = 'AVTransport')
	{
		$description = $this->getDescription($description_url);
		//	var_dump($description); die();

		switch($service)
		{
			case 'AVTransport':
				$serviceType = 'urn:schemas-upnp-org:service:AVTransport:1';
				break;
			default:
				$serviceType = 'urn:schemas-upnp-org:service:AVTransport:1';
				break;
		}

		foreach($description['device']['serviceList']['service'] as $service)
		{
			//var_dump($service);
			if($service['serviceType'] == $serviceType)
			{
				//var_dump($service); die();
				$url = parse_url($description_url);
				return $url['scheme'].'://'.$url['host'].':'.$url['port'].$service['controlURL']; //fix this to get device base url
			}
		}
		//die();
	}
	//this should also be moved off to the upnp model
	public function getDescription($description_url)
	{
		$upnp = new Model_Upnp();
		return $upnp->get_description($description_url);
	}

	public function getBaseURL($url)
	{
		$parts = parse_url($url);
		return $parts['scheme'].'://'.$parts['host'].':'.$parts['port'];
	}

	public function getIcon($description_url)
	{
		$description = $this->getDescription($description_url);
		if(!$description)
		{
			return 'http://i.imgur.com/RT9OEQt.png';
		}
	//	var_dump($description);
		if(!isset($description['device']['iconList']))
		{
			return 'defaulticon.jpg';
		}
		if(isset($description['device']['iconList']['icon']['url']))
		{
			return $this->getBaseURL($description_url).$description['device']['iconList']['icon']['url'];
		}
		else
		{
			foreach($description['device']['iconList']['icon'] as $icon)
			{
				return $this->getBaseURL($description_url).$icon['url'];
			}
		}
		return '';
	}

	public function setNext($ctrlurl,$url)
	{
		$args = array(
			'InstanceID'=>0,
			'NextURI'=>$url,
			'NextURIMetaData'=>'testmetadata'
		);
		return $this->upnp->sendRequestToDevice('SetNextAVTransportURI',$args,$ctrlurl,$type = 'AVTransport');
	}

	public function getState()
	{
		//var_dump($this->instanceOnly('GetTransportInfo'));
		return $this->instanceOnly('GetTransportInfo');
	}

	public function getPosition()
	{
		return $this->instanceOnly('getPositionInfo');
	}

	//helper function for calls that require only an instance id
	private function instanceOnly($command,$type = 'AVTransport', $id = 0)
	{
		$args = array(
			'InstanceID'=>$id
		);
		$response = $this->upnp->sendRequestToDevice($command,$args,$this->ctrlurl,$type);
		$response = \Format::forge($response,'xml:ns')->to_array();
		return $response['s:Body']['u:'.$command.'Response'];
	}

	public function getMedia()
	{
		return $this->instanceOnly('GetMediaInfo');
	}

	public function stop()
	{
		return $this->instanceOnly('Stop');
	}
	
	public function unpause()
	{
		$args = array('InstanceID'=>0,'Speed'=>1);
		return $this->upnp->sendRequestToDevice('Play',$args,$this->ctrlurl,$type = 'AVTransport');
	}

	public function pause()
	{
		return $this->instanceOnly('Pause');
	}

	public function next()
	{
		return $this->instanceOnly('Next');
	}

	public function previous()
	{
		return $this->instanceOnly('Previous');
	}

	public function seek($unit = 'TRACK_NR', $target=0)
	{
		$response = $this->upnp->sendRequestToDevice('Seek',$args,$this->ctrlurl.'serviceControl/AVTransport','AVTransport');
		$response = \Format::forge($response,'xml:ns')->to_array();
		return $response['s:Body']['u:SeekResponse'];
	}

}
