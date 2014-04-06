<?php

namespace Upnp;

class Controller_Upnp extends \Controller
{

	public function action_explore()
	{
		$upnp = new Model_Upnp();
		$results = $upnp->explore();
		return $results;
	}
}

