<?php
class bingMapsForWordpressContent
{
	/*
	 * Bing Maps API Key
	 * @var string
	 * @access public
	 */
	var $apiKey;

	/**
	 * Attributes from shortcode
	 * @var array
	 * @access public
	 */
	var $atts = array();
	
	/**
	 * Location Latitude
	 * @var float
	 * @access public
	 */
	var $lat = false;
	
	/**
	 * Location Longitude
	 * @var float
	 * @access public
	 */
	var $long = false;
	
	/**
	 * Static map types
	 * @var array
	 * @access public
	 */
	var $maptypes = array(
		'road' => 'Road', 
		'aerial' => 'Aerial', 
		'aerialwithlabels' => 'AerialWithLabels'
	);
	
	/**
	 * Dynamic map types
	 * @var array
	 * @access public
	 */
	var $maptypesDynamic = array(
		'road' => 'Road', 
		'aerial' => 'Aerial', 
		'aerialwithlabels' => 'Hybrid',
		'birdseye' => 'Birdseye'
	);
	
	/**
	 * Set debug mode - displays lat/long on dynamic map pushpins
	 * @var boolean
	 * @access public
	 */
	var $debug = false;
	
	/**
	 * Map counter for multiple maps in posts
	 * @access public
	 * @var integer
	 */
	var $mapCount = 0;
		
	/**
	 * PHP5 constructor
	 * @access public
	 * @return void
	 */
	function __construct(){ $this->bingMapsForWordpressContent();}
	
	/**
	 * PHP4 constructor
	 * @access public
	 * @return void
	 */
	function bingMapsForWordpressContent()
	{		
		// Get options
		$options = get_option('bing_maps_for_wordpress');

		// Only run if we have an API key
		if(isset($options['api']) AND $options['api'] != '')
		{
			// Header action - to load up Bing maps JavaScript
			add_action('wp_head', array($this, '__header'));
			
			// Set the API key
			$this->apiKey = $options['api'];
			
			// Add shortcode handler
			add_shortcode('bingMap', array($this, 'shortcode'));
		}
	}
	
	/**
	 * Handle [bing] shortcode - make sure settings are all correct
	 * @param array $atts
	 * @access public
	 * @return string
	 */
	function shortcode($atts)
	{
		// Increase the map count
		$this->mapCount++;
		
		// Encode location ready for lookup
		if(isset($atts['location'])) $atts['location'] = rawurlencode($atts['location']);
		
		// Get shortcode attributes
		extract(shortcode_atts(array(
			'size' => '400',
			'width' => '400',
			'height' => '400',
			'title' => '',
			'locationtitle' => '', //Allows the overide of the title of the main pin on a dynamic map
			'locationlink' => '', //Allows a link to be added to the title of the main pin on a dynamic map
			'description' => '',
			'location' => 'timbuktu',
			'maptype' => 'Road',
			'zoom' => '0',
			'zoomDynamic' => '10',
			'type' => 'dynamic'
		), $atts));

		// Check for any missing values
		if(!isset($atts['size']) OR $atts['size'] == '') $atts['size'] = 400;
		if(!isset($atts['width']) OR $atts['width'] == '') $atts['width'] = 400;
		if(!isset($atts['height']) OR $atts['height'] == '') $atts['height'] = 400;
		if(!isset($atts['location']) OR $atts['location'] == '') $atts['location'] = 'timbuktu';
		if(!isset($atts['maptype']) OR $atts['maptype'] == '') $atts['maptype'] = 'Road';
		if(!isset($atts['zoom']) OR $atts['zoom'] == '') $atts['zoom'] = 0;
		if(!isset($atts['zoomDynamic']) OR $atts['zoomDynamic'] == '') $atts['zoomDynamic'] = 10;
		if(!isset($atts['type']) OR $atts['type'] == '') $atts['type'] = 'dynamic';
		
		// Make sure map type is valid
		switch($atts['type'])
		{
			case 'static':		
				if(!isset($this->maptypes[strtolower($atts['maptype'])])) $atts['maptype'] = 'Road';
				break;
			case 'dynamic':
				if(!isset($this->maptypesDynamic[strtolower($atts['maptype'])])) $atts['maptype'] = 'Road';
				if(isset($atts['zoom']) AND $atts['zoom'] > 0) $atts['zoomDynamic'] = $atts['zoom'];
				break;
		}
		
		// Set attributes
		$this->atts = $atts;
		
		// Resolve input - might not have the lat/long yet
		$locationLookup = $this->__resolveLocation($this->atts['location']);
		
		if($locationLookup)
		{
			$this->lat = $locationLookup['lat'];
			$this->long = $locationLookup['long'];
		}
		
		// Display a map (default to dynamic if we can't find the type
		if(method_exists($this, '__display'.ucwords($this->atts['type']).'Map'))
		{
			$method = '__display'.ucwords($this->atts['type']).'Map';
		} else
		{
			$method = '__displayDynamicMap';
		}
		return $this->{$method}();
	}
	
	/**
	 * Resolve a location
	 * @param string $location
	 * @access private
	 * @return mixed
	 */
	function __resolveLocation($location)
	{
		// If we have a comma in the location, try and get lat/long
		if(strpos($location, ','))
		{
			list($lat, $long) = explode(',', $location);
		} else
		{
			$lat = false;
			$long = false;
		}
		
		// We don't have lat or long
		if(!is_numeric($lat) OR !is_numeric($long))
		{
			// Get lat/long from Bing maps location API
			$url = 'http://dev.virtualearth.net/REST/v1/Locations?q='.$location.'&o=xml&key='.$this->apiKey;
			
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$contents = curl_exec($curl);
			curl_close($curl);
			
			// Build XML
			$xml = simplexml_load_string($contents);
			
			// If we have a location, return it
			if(isset($xml->ResourceSets->ResourceSet->Resources->Location->Point))
			{
				return array(
					'lat' => trim($xml->ResourceSets->ResourceSet->Resources->Location->Point->Latitude), 
					'long' => trim($xml->ResourceSets->ResourceSet->Resources->Location->Point->Longitude)
				);
			}
			return false;
		} else
		{
			return true;
		}
	}
	
	/**
	 * Display a static Bing map
	 * @access private
	 * @return string
	 */
	function __displayStaticMap()
	{
		if(!$this->lat AND !$this->long) return '<div class="bingMapsForWordpressContainer">'.__('Unknown location').'</div>';
		
		$pushpins = array();
		foreach($this->atts as $key => $val)
		{
			if(preg_match('/^pp[0-9]+/', $key))
			{
				$pushpins[$key] = $val;
			}
		}
		
		if(!empty($pushpins)) $this->atts['zoom'] = 0;
		
		$string = '<div class="bingMapsForWordpressContainer"><div id="bingMapsForWordpress'.$this->mapCount.'" class="bingMapsForWordpress" style="width:'.$this->atts['width'].'px;height:'.$this->atts['height'].'px;">';
		
		// Display title if we've specified one
		if(isset($this->atts['title']) AND $this->atts['title'] != '')
		{
			$string .= '<span class="bingMapsForWordpressTitle">'.$this->atts['title'].'</span>';
		}
		
		$string .= '<img src="http://dev.virtualearth.net/REST/v1/Imagery/Map/';
		$string .= $this->atts['maptype'];
		$string .= '/';
		$string .= $this->lat.','.$this->long;
		$string .= '/'.$this->atts['zoom'].'?mapSize='.$this->atts['width'].','.$this->atts['height'];
		
		// Pushpins
		if(!isset($this->atts['pp']))
		{
			// No pushpin selected
			$string .= '&amp;pp='.$this->lat.','.$this->long.';0;';
		} else
		{
			// Get lat of pushpin
			if(strpos($this->atts['pp'], ';') !== false)
			{
				list($ppLocation, $ppStyle) = explode(';', $this->atts['pp']);	
			} else
			{
				$ppLocation = $this->atts['pp'];
				$ppStyle = 0;
			}
			if(!is_numeric($ppStyle)) $ppStyle = 0;
			
			$pushpinLocation = $this->__resolveLocation(rawurlencode($ppLocation));
			
			if($pushpinLocation) $string .= '&amp;pp='.$pushpinLocation['lat'].','.$pushpinLocation['long'].';'.$ppStyle;
		}
		
		// Any other pushpins
		foreach($pushpins as $key => $val)
		{
			if(strpos($val, ';') !== false)
			{
				list($ppLocation, $ppStyle) = explode(';', $val);	
			} else
			{
				$ppLocation = $val;
				$ppStyle = 0;
			}
			if(!is_numeric($ppStyle)) $ppStyle = 0;
			
			$pushpinLocation = $this->__resolveLocation(rawurlencode($ppLocation));
			if($pushpinLocation) $string .= '&amp;pp='.$pushpinLocation['lat'].','.$pushpinLocation['long'].';'.$ppStyle.';';
		}
		
		$string .= '&amp;key='.$this->apiKey.'" alt="" />';
		$string .= '</div></div>';
		return $string;
	}
	
	/**
	 * Display a dynamic Bing map
	 * @access private
	 * @return string
	 */
	function __displayDynamicMap()
	{
		if(!$this->lat AND !$this->long) return '<div class="bingMapsForWordpressContainer">'.__('Unknown location').'</div>';
		
		// Display DIV to put the map in
		$string = '<div class="bingMapsForWordpressContainer">';

		// Display title if we've specified one
		if(isset($this->atts['title']) AND $this->atts['title'] != '')
		{
			$string .= '<span class="bingMapsForWordpressTitle">'.$this->atts['title'].'</span>';
		}
		
		$string .= '<div id="bingMapsForWordpress'.$this->mapCount.'" class="bingMapsForWordpress" style="position:relative; width:'.$this->atts['width'].'px; height:'.$this->atts['height'].'px;"></div></div>';
		
		// Work out if we have pushpins
		$pushpins = array();
		foreach($this->atts as $key => $val)
		{
			if(preg_match('/^pp[0-9]+/', $key))
			{
				$pushpins[$key] = $val;
			}
		}
		// Work out if we have pushpin titles
		$pushpinst = array();
		foreach($this->atts as $key => $val)
		{
			if(preg_match('/^ppt[0-9]+/', $key))
			{
				$pushpinst[$key] = $val;
			}
		}
		// Work out if we have pushpin descriptions
		$pushpinsd = array();
		foreach($this->atts as $key => $val)
		{
			if(preg_match('/^ppd[0-9]+/', $key))
			{
				$pushpinsd[$key] = $val;
			}
		}
		// Work out if we have pushpin links
		$pushpinsl = array();
		foreach($this->atts as $key => $val)
		{
			if(preg_match('/^ppl[0-9]+/', $key))
			{
				$pushpinsl[$key] = $val;
			}
		}
		
		// Initialize a Bing Map, set its initial lat/long & zoom
		$string .= '<script type="text/javascript">map = new VEMap(\'bingMapsForWordpress'.$this->mapCount.'\');map.SetCredentials("'.$this->apiKey.'");map.LoadMap(new VELatLong('.$this->lat.', '.$this->long.', 0, VEAltitudeMode.RelativeToGround), '.$this->atts['zoomDynamic'].', VEMapStyle.'.$this->maptypesDynamic[strtolower($this->atts['maptype'])].', false, VEMapMode.Mode2D, true, 1);var layer = new VEShapeLayer();';
		if(!isset($this->atts['pp']))
		{
			// If we haven't specified a "pp" attribute, display one where we've centered the map
			if (isset($this->atts['locationtitle']) AND $this->atts['locationtitle'] != '')
			{
				$pptitle = $this->atts['locationtitle']; //set pin title as locationtitle if set in shortcode
			} else {
				$pptitle = $this->atts['location']; //if location title isn't set, use location as pin title
			}
			// If a location link is set build the link
			if (isset($this->atts['locationlink']) AND $this->atts['locationlink'] != '')
			{
					$linkstring = '<a href=';
					$linkstring .= $this->atts['locationlink'];
					$linkstring .= '>';
					$linkstring .= $pptitle;
					$linkstring .= '</a>';
					$pptitle = $linkstring;
			}
			$string .= 'var pin = new VEShape(VEShapeType.Pushpin,map.GetCenter());pin.SetTitle("'.$pptitle.'");';
			
			if(isset($this->atts['description']) AND $this->atts['description'] != '') $string .= 'pin.SetDescription("'.$this->atts['description'].'");';
			
			$string .= 'layer.AddShape(pin);';
			
			// Set the pushpin attribute for later use
			$this->atts['pp'] = $this->atts['location'];
			
		} else
		{
			// Check if we haven't turned off the pushpin
			if($this->atts['pp'] != 'false')
			{
				// Resolve pushpin location
				$pushpinLocation = $this->__resolveLocation(rawurlencode($this->atts['pp']));
				
				if($pushpinLocation)
				{
					// Add the pushpin to the layer
					$string .= 'var pin = new VEShape(VEShapeType.Pushpin,new VELatLong('.$pushpinLocation['lat'].', '.$pushpinLocation['long'].'));pin.SetTitle("'.$this->atts['pp'].'");';
					
					if(isset($this->atts['description']) AND $this->atts['description'] != '') $string .= 'pin.SetDescription("'.$this->atts['description'].'");';
					
					$string .= 'layer.AddShape(pin);';
				}
			}
		}
		
		// Any other pushpins
		foreach($pushpins as $key => $val)
		{
			$pushpinLocation = $this->__resolveLocation(rawurlencode($val));
			if($pushpinLocation)
			{
				// build the IDs for ppt, ppd, and ppl from pp
				// I believe this could be improved to be more efficient
				$pptid = 'ppt';
				$ppdid = 'ppd';
				$pplid = 'ppl';
				$pptid .= substr($key, -1);
				$ppdid .= substr($key, -1);
				$pplid .= substr($key, -1);
				if (isset($pushpinst[$pptid]) AND $pushpinst[$pptid] != '')
				{
					$pptmessage = $pushpinst[$pptid]; //set the title to ppt if ppt is set
				} else
				{
					$pptmessage = $val; //if ppt is not set, use location as pin title
				}
				if (isset($pushpinsd[$ppdid]) AND $pushpinsd[$ppdid] != '')
				{
					$ppdmessage = $pushpinsd[$ppdid]; //set the pin description if ppd is set and not blank
				}
				if (isset($pushpinsl[$pplid]) AND $pushpinsl[$pplid] != '')
				{
					//build the pin link if ppl is set
					$pplmessage = '<a href=';
					$pplmessage .= $pushpinsl[$pplid];
					$pplmessage .= '>';
					$pplmessage .= $pptmessage;
					$pplmessage .= '</a>';
					$pptmessage = $pplmessage;
				}
					
				$string .= 'var pin = new VEShape(VEShapeType.Pushpin,new VELatLong('.$pushpinLocation['lat'].', '.$pushpinLocation['long'].'));pin.SetTitle("'.$pptmessage.'");';
				if(isset($ppdmessage) AND $ppdmessage != '') $string .= 'pin.SetDescription("'.$ppdmessage.'");';
				//unset variables to prevent data being used on further pins where data is not set
				unset($pptmessage);
				unset($ppdmessage);
				unset($pplmessage);
			}
			
			$string .= 'layer.AddShape(pin);';
		}
		
		// Don't re-work out the bounding box if we have 1 pushpin, or we're in birdseye mode
		if(
		$this->atts['maptype'] != "birdseye" AND 
			(($this->atts['pp'] != "false" AND count($pushpins) > 0) OR 
			($this->atts['pp'] == "false" AND count($pushpins) > 1))
		)
		{
			// Add shape, get bounding rectangle, and set the map view based on it
			$string .= 'map.AddShapeLayer(layer);rect = layer.GetBoundingRectangle();map.SetMapView(rect);'
			;
		} else
		{
			// Just add the shape to the map
			$string .= 'map.AddShapeLayer(layer);';
		}
		
		if(empty($pushpins))
		{
			// No pushpins - set the zoom level
			$string .= 'map.SetZoomLevel('.$this->atts['zoomDynamic'].');';
		}
		
		$string .= '</script>';
		
		return $string;
		
	}
	
	/**
	 * Load the Bing map controls JavaScript
	 * @access private
	 * @return void
	 */
	function __header()
	{
		echo '<script type="text/javascript" src="http://ecn.dev.virtualearth.net/mapcontrol/mapcontrol.ashx?v=6.3"></script>';
	}
}
