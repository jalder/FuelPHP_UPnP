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

	public function play($ctrlurl,$url)
	{
		$args = array(
			'InstanceID'=>0,
			'CurrentURI'=>$url,
			'CurrentURIMetaData'=>'testmetadata'
		);
		$response = $this->upnp->sendRequestToDevice('SetAVTransportURI',$args,$ctrlurl,$type = 'AVTransport');
		return $response;
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
		return $this->instanceOnly('GetTransportInfo');
	}

	public function getPosition()
	{
		return $this->instanceOnly('getPositionInfo');
	}

	//helper function for calls that require only an instance id
	private function instanceOnly($command,$type = 'AVTransport', $id = 0)
	{
		$upnp = new \phpUPnP();
		$args = array(
			'InstanceID'=>$id
		);
		$response = $upnp->sendRequestToDevice($command,$args,$this->ctrlurl.'serviceControl/AVTransport',$type);
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
		$response = $this->upnp->sendRequestToDevice('Seek',$args,'AVTransport');
		return $response;
	}

}
