<?php

set_time_limit(1000);

function discoverListings() {
    $maxRetries = 3;
    $attempt = 0;
    $success = false;
    $apiUrl = 'https://streamed.su/api/matches/all';

    while ($attempt < $maxRetries && !$success) {
        $attempt++;
        $json = file_get_contents($apiUrl);

        if ($json !== false) {
            $success = true;
        } else {
            echo "Attempt $attempt failed.\n";
        }

        if (!$success && $attempt < $maxRetries) {
            sleep(30);
        }
    }

    if (!$success) {
        echo "Failed after $maxRetries attempts.\n";
        exit;
    }

    $jsonData = json_decode($json, true);

    if (json_last_error() === JSON_ERROR_NONE) {
        $items = [];

	foreach ($jsonData as $match) {
		$poster = 'https://raw.githubusercontent.com/dtankdempse/streamed-su-sports/main/images/bg/sports.png';

		// Determine the category-specific poster image
		$categories = [
			'afl' => 'afl.png',
			'american-football' => 'am-football.png',
			'baseball' => 'baseball.png',
			'basketball' => 'basketball.png',
			'billiards' => 'billiards.png',
			'cricket' => 'cricket.png',
			'darts' => 'darts.PNG',
			'football' => 'football.png',
			'fight' => 'fighting.png',
			'golf' => 'golf.png',
			'hockey' => 'hockey.png',
			'motor-sports' => 'motor.png',
			'nba' => 'nba.png',
			'rugby' => 'rugby.png',
			'tennis' => 'tennis.png'
		];

		foreach ($categories as $category => $image) {
			if (stripos($match['category'], $category) === 0) {
				$poster = "https://github.com/dtankdempse/streamed-su-sports/blob/main/images/bg/{$image}?raw=true";
				break;
			}
		}

		// Process the first source
		if (isset($match['sources'][0]['source'], $match['date'], $match['id'], $match['title'], $match['category'])) {
			$timestamp = $match['date'] / 1000;
			$date = new DateTime();
			$date->setTimestamp($timestamp);
			$date->setTimezone(new DateTimeZone('America/New_York'));
			$formattedDate = $date->format('h:i A T - (m/d/Y)');
			$streamUrl = "https://rr.vipstreams.in/" . $match['sources'][0]['source'] . "/js/" . $match['sources'][0]['id'] . "/1/playlist.m3u8";
			$epgId = md5($match['id'] . $match['date']);

			$eventImage = isset($match['poster'])
				? 'https://streamed.su' . $match['poster']
				: $poster;

			$eventSPDBImage = fetchEventImageSPDB($match['title']);
			if($eventSPDBImage){
				$eventImage = $eventSPDBImage;
			}

			// Sleep for 0.75 seconds to avoid exceeding the API limit
			usleep(750000);

			$items[] = [
				'id' => $match['id'],
				'date' => $formattedDate,
				'time' => $match['date'],
				'title' => $match['title'],
				'posterImage' => $poster,
				'eventImage' => $eventImage,
				'url' => "https://streamed.su/watch/" . $match['id'],
				'stream' => $streamUrl,
				'Referer' => 'https://embedme.top/',
				'type' => ucwords(strtolower($match['category'])),
				'epg' => $epgId
			];

			// Add mirror links for additional sources
			for ($i = 1; $i < count($match['sources']); $i++) {
				$source = $match['sources'][$i];
				$mirrorUrl = "https://rr.vipstreams.in/" . $source['source'] . "/js/" . $source['id'] . "/1/playlist.m3u8";
				
				$epgId = md5($match['id'] . '-' . $i . $match['date']);

				$items[] = [
					'id' => $match['id'],
					'date' => $formattedDate,
					'time' => $match['date'],
					'title' => $match['title'] . " - [" . strtoupper($source['source']) . "]",
					'posterImage' => $poster,
					'eventImage' => $eventImage,
					'url' => "https://streamed.su/watch/" . $match['id'],
					'stream' => $mirrorUrl,
					'Referer' => 'https://embedme.top/',
					'type' => 'Mirror Links',
					'epg' => $epgId
				];
			}
		}
	}

        return $items;
    } else {
        return ["error" => "Failed to decode the JSON data. Error: " . json_last_error_msg()];
    }
}

function generateM3U8($items) {
    $m3u8 = "#EXTM3U url-tvg=\"https://raw.githubusercontent.com/dtankdempse/streamed-su-sports/main/epg.xml\"\n";
    foreach ($items as $item) {        
        $date = new DateTime("@".($item['time'] / 1000));
        $date->setTimezone(new DateTimeZone('America/New_York'));
        $formattedTime = $date->format('h:i A -');

        $m3u8 .= "#EXTINF:-1 tvg-id=\"" . $item['epg'] . "\" tvg-name=\"" . $item['title'] . "\" tvg-logo=\"" . $item['eventImage'] . "\" group-title=\"" . $item['type'] . "\",";
        $m3u8 .= $formattedTime . " " . $item['title'] . " - " . $item['date'] . "\n";
        $m3u8 .= $item['stream'] . "\n";
    }
    file_put_contents('playlist.m3u8', mb_convert_encoding($m3u8, 'UTF-8', 'auto'));
}

function generateProxyM3U8($items) {
    $m3u8 = "#EXTM3U url-tvg=\"https://raw.githubusercontent.com/dtankdempse/streamed-su-sports/main/epg.xml\"\n";
    foreach ($items as $item) {        
        $date = new DateTime("@".($item['time'] / 1000));
        $date->setTimezone(new DateTimeZone('America/New_York'));
        $formattedTime = $date->format('h:i A -');

        $m3u8 .= "#EXTINF:-1 tvg-id=\"" . $item['epg'] . "\" tvg-name=\"" . $item['title'] . "\" tvg-logo=\"" . $item['eventImage'] . "\" group-title=\"" . $item['type'] . "\",";
        $m3u8 .= $formattedTime . " " . $item['title'] . " - " . $item['date'] . "\n";
        $m3u8 .= "https://m3u8.justchill.workers.dev?url=" . $item['stream'] . "&referer=" . $item['Referer'] . "\n";
    }
    file_put_contents('proxied_playlist.m3u8', $m3u8);
}

function generateTivimateM3U8($items) {
    $m3u8 = "#EXTM3U url-tvg=\"https://raw.githubusercontent.com/dtankdempse/streamed-su-sports/main/epg.xml\"\n";
    foreach ($items as $item) {        
        $date = new DateTime("@".($item['time'] / 1000));
        $date->setTimezone(new DateTimeZone('America/New_York'));
        $formattedTime = $date->format('h:i A -');

        $m3u8 .= "#EXTINF:-1 tvg-id=\"" . $item['epg'] . "\" tvg-name=\"" . $item['title'] . "\" tvg-logo=\"" . $item['eventImage'] . "\" group-title=\"" . $item['type'] . "\",";
        $m3u8 .= $formattedTime . " " . $item['title'] . " - " . $item['date'] . "\n";
        $m3u8 .= $item['stream'] . "|Referer=" . $item['Referer'] . "|User-Agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:131.0) Gecko/20100101 Firefox/131.0\n";
    }
	file_put_contents('tivimate_playlist.m3u8', mb_convert_encoding($m3u8, 'UTF-8', 'auto'));
    //file_put_contents('tivimate_playlist.m3u8', $m3u8);
}

function generateVLC($items) {
	$vlc = "#EXTM3U url-tvg=\"https://raw.githubusercontent.com/dtankdempse/streamed-su-sports/main/epg.xml\"\n";
    foreach ($items as $item) {
        $date = new DateTime("@".($item['time'] / 1000));
        $date->setTimezone(new DateTimeZone('America/New_York'));
        $formattedTime = $date->format('h:i A -');

        $vlc .= "#EXTINF:-1 tvg-id=\"" . $item['epg'] . "\" tvg-name=\"" . $item['title'] . "\" tvg-logo=\"" . $item['eventImage'] . "\" group-title=\"" . $item['type'] . "\",";
        $vlc .= $formattedTime . " " . $item['title'] . " - " . $item['date'] . "\n";
        $vlc .= "#EXTVLCOPT:http-referrer=" . $item['Referer'] . "\n";
		$vlc .= "#EXTVLCOPT:http-user-agent=Mozilla/5.0 (iPhone; CPU iPhone OS 17_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.0 Mobile/15E148 Safari/604.1\n";
        $vlc .= $item['stream'] . "\n";
    }
	file_put_contents('vlc_playlist.m3u8', mb_convert_encoding($vlc, 'UTF-8', 'auto'));
    //file_put_contents('vlc_playlist.m3u8', $vlc);
}

function generateKODIPOP($items) {
	$kodipop = "#EXTM3U url-tvg=\"https://raw.githubusercontent.com/dtankdempse/streamed-su-sports/main/epg.xml\"\n";
    foreach ($items as $item) {
        $date = new DateTime("@".($item['time'] / 1000));
        $date->setTimezone(new DateTimeZone('America/New_York'));
        $formattedTime = $date->format('h:i A -');

        $kodipop .= "#EXTINF:-1 tvg-id=\"" . $item['epg'] . "\" tvg-name=\"" . $item['title'] . "\" tvg-logo=\"" . $item['eventImage'] . "\" group-title=\"" . $item['type'] . "\",";
        $kodipop .= $formattedTime . " " . $item['title'] . " - " . $item['date'] . "\n";
        //$kodipop .= "#KODIPROP:inputstream.adaptive.stream_headers=Referer=" . urlencode($item['Referer']) . "\n";
        $kodipop .= $item['stream'] . "|Referer=" . urlencode($item['Referer']) . "&User-Agent=Mozilla%2F5.0%20%28Android%209%3B%20Tablet%3B%20rv%3A62.0%29%20AppleWebKit%2F537.36%20%28KHTML%2C%20like%20Gecko%29%20Chrome%2F72.0.1388.65%20Safari%2F537.36\n";
    }
	file_put_contents('kodi_playlist.m3u8', mb_convert_encoding($kodipop, 'UTF-8', 'auto'));
    //file_put_contents('kodi_playlist.m3u8', $kodipop);
}

function generateTextlog($items) {
    $currentTime = new DateTime("now", new DateTimeZone('America/New_York'));
    $formattedCurrentTime = $currentTime->format('h:i A T');
    $updateTime = "Last updated at " . $formattedCurrentTime . "\n\n";

    $text = $updateTime;
    foreach ($items as $item) {        
        $date = new DateTime("@".($item['time'] / 1000));
        $date->setTimezone(new DateTimeZone('America/New_York'));
        $formattedTime = $date->format('h:i A -');
        $text .= $item['title'] . " - " . $item['date'] . "\n";        
    }
    file_put_contents('events.txt', $text);
}

function generateEPG($items) {
    $epg = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $epg .= '<tv>' . "\n";

    foreach ($items as $item) {
        $epg .= '  <channel id="' . $item['epg'] . '">' . "\n";
        $epg .= '    <display-name>' . htmlspecialchars($item['title'] . ' - ' . $item['date']) . '</display-name>' . "\n";
        $epg .= '    <icon src="' . htmlspecialchars($item['posterImage']) . '" />' . "\n";
        $epg .= '  </channel>' . "\n";
    }
	
	$currentTime = time() - 3600;

	foreach ($items as $item) {          
		$startTime = date('YmdHis', $currentTime) . ' +0000';
		$endTime = date('YmdHis', $currentTime + (48 * 3600)) . ' +0000';

		$date = new DateTime();
		$date->setTimestamp($item['time'] / 1000);

		$date->setTimezone(new DateTimeZone('America/Los_Angeles'));
		$ptTime = $date->format('h:i A T');

		$date->setTimezone(new DateTimeZone('America/Denver'));
		$mtTime = $date->format('h:i A T');

		$date->setTimezone(new DateTimeZone('America/New_York'));
		$etTime = $date->format('h:i A T');

		$date->setTimezone(new DateTimeZone('Europe/London'));
		$gmtTime = $date->format('h:i A T');

		$date->setTimezone(new DateTimeZone('Europe/Berlin'));
		$cetTime = $date->format('h:i A T');

		$date->setTimezone(new DateTimeZone('Asia/Shanghai'));
		$cstTime = $date->format('h:i A T');

		$formattedDate = $date->format('m/d/Y');
		$description = "$ptTime / $mtTime / $etTime - ($formattedDate)\n$gmtTime / $cetTime / $cstTime - ($formattedDate)";

		$epg .= '  <programme start="' . $startTime . '" stop="' . $endTime . '" channel="' . $item['epg'] . '">' . "\n";
		$epg .= '    <title>' . htmlspecialchars($item['title'] . ' - ' . $item['date']) . '</title>' . "\n";
		$epg .= '    <desc>' . htmlspecialchars($description) . '</desc>' . "\n";
		$epg .= '  </programme>' . "\n";
	}

    $epg .= '</tv>';

    file_put_contents('epg.xml', $epg);
}

function fetchEventImageSPDB($eventName) {
    // Replace spaces with underscores in the event name
    $formattedEventName = str_replace(' ', '_', $eventName);
    $formattedEventName = str_replace('_v_', '_vs_', $formattedEventName);
    $useProxy = false;

    // Helper function using cURL
    $fetchFromApiInternal = function ($formattedEventName, $useProxy) {
        // Build the API URL
        $apiUrl = "https://www.thesportsdb.com/api/v1/json/3/searchevents.php?e=" . urlencode($formattedEventName);

        // Initialize cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Set proxy options if needed
        if ($useProxy) {
            curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:8888');
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        }

        // Set SSL options
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // Execute the request
        $response = curl_exec($ch);

        // Handle cURL errors
        if (curl_errno($ch)) {
            curl_close($ch);
            return null;
        }

        curl_close($ch);

        // Decode the JSON response
        return json_decode($response, true);
    };

    // Attempt the first search
    $data = $fetchFromApiInternal($formattedEventName, $useProxy);

    // If the event is null and the name contains "_vs_", split and search again
    if (!isset($data['event']) || empty($data['event'])) {
        if (strpos($formattedEventName, '_vs_') !== false) {
            // Split and reverse the teams
            $teams = explode('_vs_', $formattedEventName);
            if (count($teams) === 2) {
                $formattedEventName = $teams[1] . '_vs_' . $teams[0];
								// sleep for 0.25 of a second.
								usleep(250000);
                $data = $fetchFromApiInternal($formattedEventName, $useProxy);
            }
        }
    }

    // Validate the structure of the response
    if (!isset($data['event']) || empty($data['event'])) {
        return false;
    }

    // Get the first event
    $event = $data['event'][0];

    // Determine the image to return
    $image = null;
    if (!empty($event['strThumb'])) {
        $image = $event['strThumb'];
    } elseif (!empty($event['strLeagueBadge'])) {
        $image = $event['strLeagueBadge'];
    }

    // If no valid image is found, return false
    if (!$image) {
        return false;
    }

    // Append "/medium" to the image URL if not already present
    if (substr($image, -7) !== '/preview') {
        $image .= '/preview';
    }

    return $image;
}

function fix_json($j){
  $j = trim( $j );
  $j = ltrim( $j, '(' );
  $j = rtrim( $j, ')' );
  $a = preg_split('#(?<!\\\\)\"#', $j );
  for( $i=0; $i < count( $a ); $i+=2 ){
    $s = $a[$i];
    $s = preg_replace('#([^\s\[\]\{\}\:\,]+):#', '"\1":', $s );
    $a[$i] = $s;
  }
  $j = implode( '"', $a );
  return $j;
}

function saveItemsToJson($items) {
    $jsonData = json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($jsonData === false) {
        echo "JSON encode error: " . json_last_error_msg();
        return;
    }
    $result = file_put_contents('streamed_su.json', $jsonData);
    if ($result === false) {
        echo "Failed to write to file.";
        exit;
    }
}

// Filter out events that have passed by more than 4 hours
// Sort the remaining events by time (ascending, so soonest events first)
function filterAndSortEvents($items) {
    $currentTime = time();
    $fourHoursAgo = $currentTime - (4 * 3600); // 4 hours ago in seconds
   
    $upcomingEvents = array_filter($items, function ($item) use ($fourHoursAgo) {
        return ($item['time'] / 1000) >= $fourHoursAgo;
    });

    usort($upcomingEvents, function ($a, $b) {
        return ($a['time'] - $b['time']);
    });

    return $upcomingEvents;
}


header('Content-Type: application/json');
$items = discoverListings();
if (isset($items['error']) || empty($items)) {
    echo json_encode($items); 
    exit(1);
}
$filteredSortedItems = filterAndSortEvents($items);
generateM3U8($filteredSortedItems);
generateTivimateM3U8($filteredSortedItems);
generateVLC($filteredSortedItems);
generateProxyM3U8($filteredSortedItems);
generateKODIPOP($filteredSortedItems);
generateEPG($filteredSortedItems);
generateTextlog($filteredSortedItems);
saveItemsToJson($filteredSortedItems);
echo json_encode($filteredSortedItems);

?>
