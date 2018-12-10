<?php

/**
 * 
 * @author Krios Mane
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */

if(!function_exists('getUsbStatus'))
{
	function getUsbStatus()
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		return file_exists($CI->config->item('usb_file'));
	}
}

if(!function_exists('storeNetworkSettings'))
{
	function storeNetworkSettings($net_type, $iface, $mode, $address, $netmask, $gateway, $ssid = '', $password = '', $psk = '', $hostname = '', $description = '')
	{
		$CI =& get_instance();
		$CI->load->model('Configuration', 'configuration');
		
		$raw = $CI->configuration->load('network', '{}');
		
		$network_settings = json_decode($raw, true);
		$data = array();
		
		switch($net_type)
		{
			case "eth":
				$data['net_type'] = $net_type;
				$data['mode'] = $mode;
				$data['address'] = $address;
				$data['netmask'] = $netmask;
				$data['gateway'] = $gateway;
				$network_settings['interfaces'][$iface] = $data;
				break;
			case "wlan":
				$data['net_type'] = $net_type;
				$data['mode'] = $mode;
				$data['address'] = $address;
				$data['netmask'] = $netmask;
				$data['gateway'] = $gateway;
				$data['ssid'] = $ssid;
				$data['password'] = $password;
				$data['psk'] = $psk;
				$network_settings['interfaces'][$iface] = $data;
				break;
			case "dnssd":
				$network_settings['hostname'] = $hostname;
				$network_settings['description'] = $description;
				break;
		}
		
		$CI->configuration->store('network', json_encode($network_settings) );
	}
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('configureWireless'))
{
	/**
	 * Configure a wireless interface.
	 */
	function configureWireless($iface, $ssid, $password, $psk, $mode = 'dhcp', $address = '', $netmask = '', $gateway = '', $channel = '1')
	{
		$CI =& get_instance();
		$CI->load->helper('fabtotum');
		$args = '-i'.$iface.' -s "'.$ssid.'"';
		if($psk != '')
		{
			$args .= ' -k "'.$psk.'"';
		}
		else if($password != '')
		{
			$args .= ' -p "'.$password.'"';
		}
		
		switch($mode)
		{
			case "dhcp":
				$args .= ' -D';
				break;
			case "static":
				$args .= ' -S -a '.$address.' -n '.$netmask.' -g'.$gateway;
				break;
			case "static-ap":
				$args .= ' -A -a '.$address.' -n '.$netmask.' -c '.$channel;
				break;
			case "disabled":
				$args .= ' -M disabled';
				break;
			default:
				return false;
		}
		$result = json_decode( startBashScript('set_wifi.sh', $args, false, true), true);
		return $result;
	}
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('configureEthernet'))
{
	/**
	 * Configure a wireless interface.
	 */
	function configureEthernet($iface, $mode = 'dhcp', $address = '', $netmask = '', $gateway = '')
	{
		$CI =& get_instance();
		$CI->load->helper('fabtotum');
		$args = '-i'.$iface;
		switch($mode)
		{
			case "dhcp":
				$args .= ' -D';
				break;
			case "static":
				$args .= ' -S -a '.$address.' -n '.$netmask.' -g'.$gateway;
				break;
			default:
				return false;
		}
		$result = json_decode( startBashScript('set_ethernet.sh', $args, false, true), true);
		return $result;
	}
} 
 
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getInterfaces'))
{
	/**
	 * Get network interfaces data
	 */
	function getInterfaces()
	{
		$CI =& get_instance();
		$CI->load->helper('fabtotum');
		$result = json_decode( startBashScript('get_net_interfaces.sh', '', false, true), true);
		return $result;
	}
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getDNS'))
{
	/**
	 * Get system DNS data
	 */
	function getDNS()
	{
		$CI =& get_instance();
		$CI->load->helper('fabtotum');
		$result = startBashScript('get_dns.sh', '', false, true);
		
		$dns = array(
			'head' => array(),
			'current' => array(),
			'tail' => array()
		);
		
		$list = explode(";", $result);
		
		foreach($list as $entry)
		{
			$entry = trim($entry);
			if( startsWith($entry, 'H=') ) {
				$dns['head'][] = ltrim($entry, "H=");
			}
			else if ( startsWith($entry, 'C=') ) {
				$c = ltrim($entry, "C=");
				if(!in_array($c, $dns['head']) && !in_array($c, $dns['tail'])) {
					$dns['current'][] = $c;
				}
			}
			else if ( startsWith($entry, 'T=') ) {
				$dns['tail'][] = ltrim($entry, "T=");
			}
		}
		
		return $dns;
	}
}

if(!function_exists('configureDNS'))
{
	function configureDNS($dns_settings)
	{
		
		$head_content = '';
		if(isset($dns_settings['head'])) {
			foreach($dns_settings['head'] as $entry)
			{
				$head_content .= "nameserver " . $entry . PHP_EOL;
			}
		}
		
		$current_content = '';
		if(isset($dns_settings['current'])) {
			foreach($dns_settings['current'] as $entry)
			{
				$current_content .= "nameserver " . $entry . PHP_EOL;
			}
		}
		
		$tail_content = '';
		if(isset($dns_settings['tail'])) {
			foreach($dns_settings['tail'] as $entry)
			{
				$tail_content .= "nameserver " . $entry . PHP_EOL;
			}
		}
		
		file_put_contents('/tmp/fabui/resolv.conf.head', $head_content);
		file_put_contents('/tmp/fabui/resolv.conf', $head_content . $current_content . $tail_content);
		file_put_contents('/tmp/fabui/resolv.conf.tail', $tail_content);
		
		shell_exec("sudo mv /tmp/fabui/resolv.conf.head /etc/resolv.conf.head");
		shell_exec("sudo mv /tmp/fabui/resolv.conf /etc/resolv.conf");
		shell_exec("sudo mv /tmp/fabui/resolv.conf.tail /etc/resolv.conf.tail");
		
		return true;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('cidr2NetmaskAddr'))
{
	function cidr2NetmaskAddr($cidr)
	{
		$ta = substr($cidr, strpos($cidr, '/') + 1) * 1;
		$netmask = str_split(str_pad(str_pad('', $ta, '1'), 32, '0'), 8);
		foreach ($netmask as &$element) $element = bindec($element);
		return join('.', $netmask);
    }
}

if(!function_exists('getHostName'))
{
	/**
	 * @return Hostname
	 */
	function getHostName()
	{
		return shell_exec('cat /etc/hostname');
	}
}

if(!function_exists('setHostName'))
{
    /**
     * @param string $hostname hostname
     * @param string $name service decription
     */
	function setHostName($hostname, $name="")
	{
		$CI =& get_instance();
		$CI->load->helper('fabtotum');
		$unitDescription = getUnitTypeDescription();
		if($name == "" ) $name = $unitDescription;
		$response = startBashScript('set_hostname.sh', '"'.$hostname.'" "'.$name.'" "'.$unitDescription.'" ', false, true);
		return $response;
	}
}

if(!function_exists('getAvahiServiceName'))
{
	/**
	 * @return Service name stored in avahi fabtotum.service
	 */
	function getAvahiServiceName()
	{
		if(file_exists('/etc/avahi/services/fabtotum.service')){
			$xml_service = simplexml_load_file('/etc/avahi/services/fabtotum.service','SimpleXMLElement', LIBXML_NOCDATA);
			return trim(str_replace('(%h)', '', $xml_service->name));
		}else{
			return 'Fabtotum Personal Fabricator';
		}
	}
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('scanWlan'))
{
	/**
	 * @param string $interface wlan interface name
	 * @return array list of discovered wifi's nets 
	 */
	function scanWlan($interface = 'wlan0')
	{
		$CI =& get_instance();
		$CI->load->helper('fabtotum');
		
		$data = getInterfaces();
		if( $data[$interface]['address_mode'] == 'manual' )
		{
			$args = '-i ' . $interface . ' -M default';
			$result = startBashScript('set_wifi.sh', $args, false, true);
		}
		
		$result = startPyScript('scan_wifi.py', $interface, false, true);
		$nets = json_decode( $result, true);

		//order nets
		uasort($nets, 'wlanSort');
		return $nets;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getFromRegEx'))
{
	/**
	 * 
	 */
	function getFromRegEx($regEx, $string)
	{
		preg_match($regEx, $string, $tempResult);
		return isset($tempResult[1]) ? $tempResult[1] : '';
	}
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('disconnectFromWireless'))
{
	/**
	 * disconnect from wifi network
	 */
	function disconnectFromWireless($interface)
	{
		$CI =& get_instance();
		$CI->load->helper('fabtotum');
		$result = startBashScript('disconnect_wifi.sh', $interface, false, true);
		return true;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('setSystemDate'))
{
	/**
	 * set system date format = YYYY-MM-DD HH:mm:ss
	 */
	function setSystemDate($date)
	{
		log_message('debug', 'Set system date: "'.$date.'"');
		shell_exec('sudo date -s "'.$date.'"');
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('isInternetAvaialable'))
{
	/**
	 * check if internet connection is avaialable
	 */
	function isInternetAvaialable()
	{
		$CI =& get_instance();
		$CI->load->helper('fabtotum');
		$result = startBashScript('internet.sh', null, false, true);
		return trim($result) == 'online';
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('downloadRemoteFile'))
{
	/**
	 * download remote file 
	 */
	function downloadRemoteFile($remoteUrl, $path, $timeout=30, $do_internet_check=true, $ssl=false)
	{	
		if($do_internet_check)
		{
			if(!isInternetAvaialable())
			{
				log_message('debug', 'Internet connection not available');
				return false;
			}
		}

		$curl = curl_init($remoteUrl);
		curl_setopt($curl, CURLOPT_TIMEOUT,        $timeout);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		
		if($ssl == false){
		    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		}
		
		$downloadedFile = curl_exec($curl); //make call
		$info = curl_getinfo($curl);
		if(isset($info['http_code']) && $info['http_code'] == 200 && $downloadedFile != ""){ //if response is OK and response is not empty
			
			$CI =& get_instance();
			$CI->load->helper('file_helper');
			write_file($path, $downloadedFile, 'w+');
			return true;
		}else{
			return false;
		}
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getRemoteFile'))
{
	/**
	 * 
	 */
	function getRemoteFile($url, $do_internet_check=true, $headers = array(), $timeout=30, $ssl=false )
	{
		/**
		*	disable connections
		**/
		return false;

		if($do_internet_check)
		{
			if(!isInternetAvaialable())
			{
				log_message('debug', 'Internet connection not available');
				return false;
			}
		}
		
		$curl = curl_init($url);
		
		curl_setopt($curl, CURLOPT_TIMEOUT,        $timeout);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		
		if($ssl == false){
		    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		}
		
		if(!empty($headers)){
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		}
		$content = curl_exec($curl); //make call
		$info    = curl_getinfo($curl);
		
		if(isset($info['http_code']) && $info['http_code'] == 200){ //if response is OK
			return $content;
		}else{
			return false;
		}

	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('setTimeZone'))
{
	/**
	 * set time zone 
	 */
	function setTimeZone($timeZone)
	{	
		if($timeZone != ''){
			$CI =& get_instance();
			$CI->load->helper('fabtotum');
			$scriptResult = startBashScript('set_time_zone.sh', $timeZone, false, true);
			log_message('debug', 'set_time_zone.sh' .' '.$timeZone);
			return true;
		}
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getTimeZone'))
{
	/**
	 * get time zone
	 */
	function getTimeZone()
	{
		return trim(shell_exec('cat /etc/timezone'));
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('transformSeconds'))
{
	function transformSeconds($seconds)
	{
		$sec_num = intval($seconds); // don't forget the second param
		$hours   = floor($sec_num / 3600);
		$minutes = floor(($sec_num - ($hours * 3600)) / 60);
		$seconds = $sec_num - ($hours * 3600) - ($minutes * 60);

		if ($hours   < 10) {$hours   = "0".$hours;}
		if ($minutes < 10) {$minutes = "0".$minutes;}
		if ($seconds < 10) {$seconds = "0".$seconds;}
		return $hours . ':' . $minutes . ':' . $seconds;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('humanFileSize'))
{
	/**
	 * Human readable byte size
	 * @param $int
	 * @return string
	 */
	function humanFileSize($bytes) {
		$bytes = intval($bytes);
		$ret = "unknown";
		if ($bytes > 1000000) {
			$bytes = round($bytes / 1000000, 2);
			$ret = "$bytes MB";
		} else if ($bytes > 1000) {
			$bytes = round($bytes / 1000, 2);
			$ret = "$bytes kB";
		} else {
			$ret = "$bytes bytes";
		}
		return $ret;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('writeNetworkInfo'))
{
	function writeNetworkInfo($interfaces = array()){
		$CI =& get_instance();
		//load config, helpers
		$CI->config->load('fabtotum');
		$CI->load->helper('file');
		
		if(empty($interfaces)) $interfaces = getInterfaces();
		
		$data['interfaces'] = $interfaces;
		$data['internet'] = isInternetAvaialable();
		write_file($CI->config->item('network_info_file'), json_encode($data));
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getNetworkInfo'))
{
	function getNetworkInfo()
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		return json_decode(file_get_contents($CI->config->item('network_info_file')), true);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getMACAddress'))
{
	/**
	 * return mac address
	 */
	function getMACAddres($interface = 'eth0')
	{
	    /*
		$interfaces = getInterfaces();
		if(array_key_exists($interface,$interfaces)){
			if(isset($interfaces[$interface]["mac_address"]))
				return $interfaces[$interface]["mac_address"];
		}
		return false;
		*/
		return trim(shell_exec("ifconfig " . $interface . " | grep -o -E '([[:xdigit:]]{1,2}:){5}[[:xdigit:]]{1,2}'"));
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getUploadMaxFileSize'))
{
	/**
	 * return upload_max_filesize from php.ini
	 */
	function getUploadMaxFileSize()
	{
		return intval(str_replace("MB","", ini_get('upload_max_filesize')));
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('restartLighttpd'))
{
	/**
	 * 
	 */
	function restartLighttpd()
	{
		$CI =& get_instance();
		$CI->load->helper('fabtotum');
		log_message('debug', 'Restart Lighttpd');
		startBashScript('restart_lighttpd.sh', null, false, true);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists("wlanSort"))
{
	/**
	 * order network by quality
	 */
	function wlanSort($netA, $netB){
		
		if ($netA['quality'] == $netB['quality']) {
			return 0;
		}
		return ($netA['quality'] > $netB['quality']) ? -1 : 1;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists("fix_folder_permissions"))
{
	/**
	 * 
	 */
	function fix_folder_permissions($folder, $owner, $group = '')
	{
		if($group == '') $group = $owner;
		//example sudo chown -R www-data:www-data /tmp/fabui/
		shell_exec('sudo chown -R '.$owner.':'.$group.' '.$folder);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('rpi_version'))
{
	/**
	 * return Raspberry Model
	 */
	function rpi_version()
	{
		$rpi_versions = array(
			'BCM2708' => array('version' => 1, 'description' => 'Raspberry Pi Model B'),
			'BCM2709' => array('version' => 3, 'description' => 'Raspberry Pi 3 Model B')
		);
		
		$code = trim(shell_exec('</proc/cpuinfo grep Hardware | awk \'{print $3}\''));
		
		if(isset($rpi_versions[$code]))
			return $rpi_versions[$code];
		else
			return 'unkwnon';
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('disable_bluetooth'))
{
    /**
     * disable bluetooth
     */
    function disable_bluetooth()
    {
        $CI =& get_instance();
        $CI->load->helper('fabtotum');
        $args = array(
            '-a' => 'disable'
        );
        $scriptResult = startBashScript('bluetooth.sh', $args, false, true);
        return json_decode($scriptResult,true);
    }
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('enable_bluetooth'))
{
    /**
     * disable bluetooth
     */
    function enable_bluetooth()
    {
        $CI =& get_instance();
        $CI->load->helper('fabtotum');
        $args = array(
            '-a' => 'enable'
        );
        $scriptResult = startBashScript('bluetooth.sh', $args, false, true);
        return json_decode($scriptResult,true);
    }
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('bluetooth_status'))
{
    /**
     * disable bluetooth
     */
    function bluetooth_status()
    {
        $CI =& get_instance();
        $CI->load->helper('fabtotum');
        $args = array(
            '-a' => 'status'
        );
        $scriptResult = startBashScript('bluetooth.sh', $args, false, true);
        return json_decode($scriptResult,true);
    }
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('bluetooth_remove_device'))
{
    /**
     * disable bluetooth
     */
    function bluetooth_remove_device($mac_address)
    {
        $CI =& get_instance();
        $CI->load->helper('fabtotum');
        $args = array(
            '-a' => 'remove',
            '-m'  => $mac_address
        );
        $scriptResult = trim(startBashScript('bluetooth.sh', $args, false, true));
        return $scriptResult != '';
        
    }
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('bluetooth_restart'))
{
    /**
     * disable bluetooth
     */
    function bluetooth_restart()
    {
        $CI =& get_instance();
        $CI->load->helper('fabtotum');
        $args = array(
            '-a' => 'restart',
        );
        $scriptResult = trim(startBashScript('bluetooth.sh', $args, false, true));
        return $scriptResult != '';
        
    }
}
?>
