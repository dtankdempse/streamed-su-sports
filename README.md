**Important:** Due to TinyURL’s use of Cloudflare, the playlist short links have been updated from TinyURL to Bit.ly. The old TinyURL links will continue to work once the protective mode is lifted, but it's recommended to switch to the new Bit.ly links.

# Streamed su Sports Playlists

Streamed su is a platform that offers live sports streaming on their website. Users can stream and watch sports directly through their browser without the need for an account or subscription.

For added flexibility, this repository provides an M3U playlist featuring Streamed su's channels. With this, you can load the streams into any IPTV application that supports M3U-formatted playlists.

You can view the latest events added to the playlist [here](https://github.com/dtankdempse/streamed-su-sports/blob/main/events.txt).

## Playlist Formats:

There are four different playlist formats available, each tailored for specific applications or media players.

- **playlist.m3u8:**
  A standard M3U playlist. If you're using this playlist, make sure your IPTV application allows the setting of a custom `Referer` header. The `Referer` must be set to `https://embedme.top/` in order to access the streams. Failure to set the `Referer` will result in a 403 error when attempting to stream.
  
  **Playlist:** `https://bit.ly/su-m3u1`  
  **EPG URL:** `https://bit.ly/su-epg`  
  **Referer:** `https://embedme.top/`    

- **tivimate_playlist.m3u8:**
  This playlist is specifically formatted for use with TiviMate. To use it, simply load the URL provided in this repository into Tivimate as an "M3U Playlist." No additional setup is needed as Tivimate handles the required headers for playback.
  
   **Playlist:** `https://bit.ly/su-m3u2`  
   **EPG URL:** `https://bit.ly/su-epg`  
   **Referer:** `Included` 

- **kodi_playlist.m3u8:**
	This playlist is designed for Kodi, utilizing `#KODIPROP` properties to handle the necessary stream settings, including the `Referer` header. It is optimized for Kodi's PVR IPTV Simple Client, ensuring compatibility with your Kodi setup. This format is primarily designed for Kodi, but it may or may not be compatible with other applications.
	
	**Playlist:** `https://bit.ly/su-m3u3`  
	**EPG URL:** `https://bit.ly/su-epg`  
	**Referer:** `Included` 

- **vlc_playlist.m3u8:**
  Optimized for VLC Media Player. This playlist uses VLC-specific formatting to ensure streams play correctly, including setting the necessary `http-referrer` via `#EXTVLCOPT`. This format is primarily designed for VLC, but it may or may not be compatible with other applications.
  
  	**Playlist:** `https://bit.ly/su-m3u4`  
	**EPG URL:** `https://bit.ly/su-epg`  
	**Referer:** `Included`


If none of these playlists work with your IPTV application, you can try using the [m3u-playlist-proxy](https://github.com/dtankdempse/m3u-playlist-proxy). This proxy acts as a middle layer to help resolve potential issues with playing the playlists, especially if your IPTV app doesn't support setting a referer header.

## Usage Instructions:

1. Choose the playlist format that suits your application or media player.
2. Add the playlist URL to your IPTV application.
3. Ensure that your application supports the required settings, such as setting the `Referer` header for streams to play.

## Playlist and EPG Syncing:

The playlist and EPG data are updated every 4 hours. Since streaming data can change frequently, it’s recommended to sync the refresh of both your playlist and guide information simultaneously in your IPTV application. If you notice 'No information' being displayed in the guide, try manually refreshing the EPG within your application to ensure the most up-to-date data is loaded.

## Sports:

Basketball, Football, American Football, Hockey, Baseball, Motor Sports, Fight (UFC, Boxing), Tennis, Rugby, Golf, Billiards, AFL, Darts, Cricket, Other

## Disclaimer:

This repository has no control over the streams, links, or the legality of the content provided by Streamed.su. It is the end user's responsibility to ensure the legal use of these streams, and we strongly recommend verifying that the content complies with the laws and regulations of your country before use.

