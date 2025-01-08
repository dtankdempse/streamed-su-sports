# Streamed Su Sports Playlists

Streamed su is a platform that offers live sports streaming on their website. Users can stream and watch sports directly through their browser without the need for an account or subscription.

For added flexibility, this repository provides an M3U playlist featuring Streamed su's channels. With this, you can load the streams into any IPTV application that supports M3U-formatted playlists.

You can view the latest events added to the playlist [here](https://github.com/dtankdempse/streamed-su-sports/blob/main/events.txt).

---

## M3U-Playlist-Proxy (MPP) Required!

Due to recent updates by Streamed Su Sports, the M3U-Playlist-Proxy (MPP) is now required to stream this playlist. To get started, check out the video guide on setting up and downloading the M3U-Playlist-Proxy, available at this [GitHub link](https://github.com/dtankdempse/m3u-playlist-proxy). If you’re already using the MPP proxy, please update to the latest version by pulling the new Docker image, downloading the updated zip, or redeploying to Vercel to ensure you have the latest changes.


- **Playlist.m3u8**  
  This is a standard M3U playlist. To use it, load the Playlist URL into MPP and ensure both the `Referer` and `User-Agent` headers are correctly configured. Set the `Referer` to `https://embedme.top/` and use a compatible `User-Agent` string to enable stream access. Without these headers, streaming attempts will result in a 403 error.


  - **Playlist URL:** [https://bit.ly/su-m3u1](https://bit.ly/su-m3u1)
  - **EPG URL:** [https://bit.ly/su-epg](https://bit.ly/su-epg)
  - **Referer:** `https://embedme.top/`
  - **User-Agent:** `Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:132.0) Gecko/20100101 Firefox/132.0`  
    (Alternatively, you may use your own User-Agent string.)

---

## Important Information for VLC Users:

VLC may not be the ideal player for this site unless you enable the 'Play and Stop' option in the preferences. Here's why: if VLC continuously attempts to load streams that fail, your IP could be rate-limited or even temporarily banned. To prevent this, follow these steps:

**On Desktop (Windows, macOS, Linux):**

- Open VLC Media Player.
- Go to the Tools menu and select Preferences (or press Ctrl + P).
- At the bottom of the Preferences window, select All under Show settings to switch to advanced mode.
- In the left sidebar, navigate to Playlist.
- In the Playlist settings, locate the option 'Play and Stop'.
- Check the box next to Play and Stop.
- Click Save to apply the changes.
- Restart VLC to ensure the settings take effect.

**What Does "Play and Stop" Do?**

When enabled, VLC will stop playback entirely when the current item finishes or fails, rather than jumping to the next item in the playlist. This is exactly what you need to prevent it from trying to play the next stream when one fails.

---

## Playlist and EPG Syncing:

The playlist and EPG data are updated every 4 hours. Since streaming data can change frequently, it’s recommended to sync the refresh of both your playlist and guide information simultaneously in your IPTV application. If you notice 'No information' being displayed in the guide, try manually refreshing the EPG within your application to ensure the most up-to-date data is loaded.

## Sports:

Basketball, Football, American Football, Hockey, Baseball, Motor Sports, Fight (UFC, Boxing), Tennis, Rugby, Golf, Billiards, AFL, Darts, Cricket, Other

---

<details>
<summary>Click to read Disclaimer.</summary>

## Disclaimer:

This repository has no control over the streams, links, or the legality of the content provided by Streamed.su. It is the end user's responsibility to ensure the legal use of these playlists, and we strongly recommend verifying that the content complies with the laws and regulations of your country before use.
</details>

<details>
<summary>Click to read DMCA Notice.</summary>
  
## DMCA Notice:

This repository does not host or store any video files. It simply organizes publicly accessible web links, which can be accessed through a web browser, into an M3U-formatted playlist. To the best of our knowledge, the content was intentionally made publicly available by the copyright holders or with their permission and consent granted to these websites to stream and share the content they provide.

Please note that linking does not directly infringe copyright, as no copies are made on this repository or its servers. Therefore, sending a DMCA notice to GitHub or the maintainers of this repository is not a valid course of action. To remove the content from the web, you should contact the website or hosting provider actually hosting the material.

If you still believe a link infringes on your rights, you can request its removal by opening an [issue](https://github.com/dtankdempse/streamed-su-sports/issues) or submitting a [pull request](https://github.com/dtankdempse/streamed-su-sports/pulls). Be aware, however, that removing a link here will not affect the content hosted on the external websites, as this repository has no control over the files or the content being provided.

</details>
