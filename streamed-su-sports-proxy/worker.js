addEventListener('fetch', event => {
  event.respondWith(handleRequest(event.request));
});

async function handleRequest(request) {
  const url = new URL(request.url);
  const pathname = url.pathname;

  // Handle the home page route
  if (pathname === '/' && !url.search) {
    const html = `
		<!DOCTYPE html>
		<html lang="en">
		<head>
		  <meta charset="UTF-8">
		  <meta name="viewport" content="width=device-width, initial-scale=1.0">
		  <title>streamed-su-sports-proxy</title>
		</head>
		<body style="font-size:18px;">
		  <h4 style="color:green;">Online, it works!</h4>
		  
		  <!-- Playlist Section -->
		  <p><strong>Playlist:</strong><br> 
			<code><span id="playlist-url"></span></code>
		  </p>
		  
		  <!-- EPG Section -->
		  <p><strong>EPG URL:</strong><br> 
			<code><span id="epg-url"></span></code>
		  </p>

		  <!-- Repo Information -->
		  <p>The repo <strong>streamed-su-sports</strong> can be found <a href="https://github.com/dtankdempse/streamed-su-sports" target="_blank">here</a>.</p>
		  
		  <script>
			// Dynamically get the base URL and append /playlist and /epg
			const baseUrl = window.location.origin;
			
			// Display the full Playlist URL
			document.getElementById('playlist-url').textContent = baseUrl + '/playlist';

			// Display the full EPG URL
			document.getElementById('epg-url').textContent = baseUrl + '/epg';
		  </script>
		</body>
		</html>
    `;
    return new Response(html, { headers: { 'Content-Type': 'text/html' } });
  }
  
    // Handle the /epg route
  if (pathname === '/epg') {
    const epgUrl = 'https://raw.githubusercontent.com/dtankdempse/streamed-su-sports/refs/heads/main/epg.xml';
    
    // Fetch the EPG XML data from the URL
    const epgResponse = await fetch(epgUrl);
    
    if (!epgResponse.ok) {
      return new Response('Failed to fetch EPG', { status: 500 });
    }
    
    const epgContent = await epgResponse.text(); // Get the XML as text
    return new Response(epgContent, {
      headers: { 'Content-Type': 'application/xml' }, // Set proper content type for XML
    });
  }

  // Handle the /playlist route
  if (pathname === '/playlist') {
    return handlePlaylistRequest(request);
  }

  // Handle proxied URL requests
  const params = url.searchParams;
  const requestUrl = params.has('url') ? decodeURIComponent(params.get('url')) : null;
  const secondaryUrl = params.has('url2') ? decodeURIComponent(params.get('url2')) : null;
  const data = params.get('data') ? atob(params.get('data')) : null; // Decode base64

  const isMaster = !params.has('url2'); // Check if it's a master playlist
  const finalRequestUrl = isMaster ? requestUrl : secondaryUrl;

  if (finalRequestUrl) {
    // Handle encryption key requests
    if (params.has('key') && params.get('key') === 'true') {
      return fetchEncryptionKey(finalRequestUrl, data);
    }

    // Set dataType based on whether it's a master playlist (text) or a segment (binary)
    const dataType = isMaster ? 'text' : 'binary';

    // Fetch content from the URL with appropriate headers and dataType
    const result = await fetchContent(finalRequestUrl, data, dataType);

    if (result.status >= 400) {
      return new Response(`Error: ${result.status}`, { status: result.status });
    }

    let content = result.content;

    // If it's a master playlist, rewrite the URLs and treat as text
    if (isMaster) {
      const baseUrl = new URL(result.finalUrl).origin;
      const proxyUrl = `${url.origin}`;
      content = rewriteUrls(content, baseUrl, proxyUrl, params.get('data'));
    }

    // Send the response with content and headers
    return new Response(content, { headers: result.headers, status: result.status });
  }

  return new Response('Bad Request', { status: 400 });
}


// Function to fetch content from a URL using fetch
async function fetchContent(url, data, dataType = 'text') {
  const headers = new Headers();

  if (data) {
    const headersArray = data.split('|');
    headersArray.forEach(header => {
      const [key, value] = header.split('=');
      headers.append(key.trim(), value.trim().replace(/['"]/g, ''));
    });
  }

  const response = await fetch(url, { headers });
  const buffer = await response.arrayBuffer();
  let content;

  if (dataType === 'binary') {
    content = buffer; // Treat as binary
  } else {
    content = new TextDecoder('utf-8').decode(buffer); // Default to text
  }

  return {
    content,
    finalUrl: url,
    status: response.status,
    headers: response.headers,
  };
}

// Function to handle playlist requests
async function handlePlaylistRequest(request) {
  const playlistUrl = 'https://raw.githubusercontent.com/dtankdempse/streamed-su-sports/refs/heads/main/tivimate_playlist.m3u8';
  const result = await fetchContent(playlistUrl);

  if (result.status !== 200) {
    return new Response('Failed to fetch playlist', { status: 500 });
  }

  let playlistContent = result.content;
  const baseUrl = new URL(request.url).origin;
  playlistContent = rewritePlaylistUrls(playlistContent, baseUrl);

  return new Response(playlistContent, { headers: { 'Content-Type': 'text/plain' } });
}

// Function to fetch encryption key
async function fetchEncryptionKey(url, data) {
  const result = await fetchContent(url, data, 'binary'); // Pass 'binary' as the dataType

  if (result.status >= 400) {
    return new Response(`Failed to fetch encryption key: ${result.status}`, { status: 500 });
  }

  return new Response(result.content, { headers: result.headers });
}

// Rewrite URLs in the M3U8 playlist
function rewriteUrls(content, baseUrl, proxyUrl, data) {
  const lines = content.split('\n');
  const rewrittenLines = [];
  let isNextLineUri = false;

  lines.forEach(line => {
    if (line.startsWith('#')) {
      if (line.includes('URI="')) {
        const uriMatch = line.match(/URI="([^"]+)"/i);
        let uri = uriMatch[1];
        
        if (!uri.startsWith('http')) {
          uri = new URL(uri, baseUrl).href;
        }
        
        const rewrittenUri = `${proxyUrl}?url=${encodeURIComponent(uri)}&data=${encodeURIComponent(data)}${line.includes('#EXT-X-KEY') ? '&key=true' : ''}`;
        line = line.replace(uriMatch[1], rewrittenUri);
      }
      
      rewrittenLines.push(line);

      if (line.includes('#EXT-X-STREAM-INF')) {
        isNextLineUri = true;
      }

    } else if (line.startsWith('http') || isNextLineUri) {
      const urlParam = isNextLineUri ? 'url' : 'url2';
      let lineUrl = line;

      if (!lineUrl.startsWith('http')) {
        lineUrl = new URL(lineUrl, baseUrl).href;
      }

      const fullUrl = `${proxyUrl}?${urlParam}=${encodeURIComponent(lineUrl)}&data=${encodeURIComponent(data)}${urlParam === 'url' ? '&type=/index.m3u8' : '&type=/index.ts'}`;
      rewrittenLines.push(fullUrl);

      isNextLineUri = false;
    } else {
      rewrittenLines.push(line);
    }
  });

  return rewrittenLines.join('\n');
}

// Rewrite playlist URLs and encode headers
function rewritePlaylistUrls(content, baseUrl) {
  const lines = content.split('\n');
  const rewrittenLines = [];

  lines.forEach(line => {
    if (line.startsWith('#EXTINF')) {
      rewrittenLines.push(line);
    } else if (line.startsWith('http')) {
      const [streamUrl, headerPart] = line.split('|');
      const headers = headerPart ? headerPart.split('|').map(header => header.trim()) : [];
      const base64Data = headers.length > 0 ? btoa(headers.join('|')) : '';
      const newUrl = `${baseUrl}?url=${encodeURIComponent(streamUrl)}&data=${encodeURIComponent(base64Data)}`;
      rewrittenLines.push(newUrl);
    } else {
      rewrittenLines.push(line);
    }
  });

  return rewrittenLines.join('\n');
}
