<?php 
function getOS($user_agent) {
	$os_platform = "unknown";
	$os_array = array(
		'/windows nt 10/i'      =>  'Windows 10',
		'/windows nt 6.3/i'     =>  'Windows 8.1',
        '/windows nt 6.2/i'     =>  'Windows 8',
        '/windows nt 6.1/i'     =>  'Windows 7',
        '/windows nt 6.0/i'     =>  'Windows Vista',
        '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
        '/windows nt 5.1/i'     =>  'Windows XP',
        '/windows xp/i'         =>  'Windows XP',
        '/windows nt 5.0/i'     =>  'Windows 2000',
        '/windows me/i'         =>  'Windows ME',
        '/win98/i'              =>  'Windows 98',
        '/win95/i'              =>  'Windows 95',
        '/win16/i'              =>  'Windows 3.11',
        '/macintosh|mac os x/i' =>  'Mac OS X',
        '/mac_powerpc/i'        =>  'Mac OS 9',
        '/linux/i'              =>  'Linux',
        '/ubuntu/i'             =>  'Ubuntu',
        '/iphone/i'             =>  'iPhone',
        '/ipod/i'               =>  'iPod',
        '/ipad/i'               =>  'iPad',
        '/android/i'            =>  'Android',
        '/blackberry/i'         =>  'BlackBerry',
        '/webos/i'              =>  'Mobile'
	);
	foreach($os_array as $regex => $value)
		if (preg_match($regex, $user_agent))
			$os_platform = $value;
	return $os_platform;
}

function getBrowser($user_agent) {
    $browser = "unknown";
    $browser_array = array(
		'/msie/i'      => 'Internet Explorer',
        '/firefox/i'   => 'Firefox',        
        '/chrome/i'    => 'Chrome',
        '/safari/i'    => 'Safari',
        '/edge/i'      => 'Edge',
        '/opera/i'     => 'Opera',
        '/netscape/i'  => 'Netscape',
        '/maxthon/i'   => 'Maxthon',
        '/konqueror/i' => 'Konqueror',
        '/mobile/i'    => 'Handheld Browser'
	);

    foreach ($browser_array as $regex => $value)
        if (preg_match($regex, $user_agent)) {
            $browser = $value;
            goto end;
        }

    end: return $browser;
}

function getDevice($mac) {
	$url = "https://api.macvendors.com/" . urlencode($mac);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$response = curl_exec($ch);
	if ($response) {
		if (preg_match("/errors/", $response)) {
			return "unknown";
		} else {
			return $response;
		}
	} else {
		return "unknown";
	}
}

?>