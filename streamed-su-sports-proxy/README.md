## About streamed-su-sports-proxy

The `streamed-su-sports-proxy` was created to allow streaming applications that don’t support certain playlist formats, like Tivimate, Kodi, or VLC, to access and play streams. However, your player must support AES key decryption to successfully play the streams. I have personally tested this on Jellyfin and Smarters — while Jellyfin successfully played the streams, Smarters did not. 

Follow the guide below to set up the `streamed-su-sports-proxy` on Cloudflare.

## How to Setup a Worker



https://github.com/user-attachments/assets/ebcfd0ca-dddd-4f09-822e-b2388a662d13



## Deploy to Cloudflare Workers

Cloudflare Workers allows you to run JavaScript code at the edge, right on Cloudflare's infrastructure, with a generous free tier. A free Cloudflare account offers **100,000 requests per day**, which is **more than enough** to proxy and manage these streams efficiently, even if running them all day 24/7/365.

### Why 100,000 Requests is Enough:

- **Efficient for Streaming**: For most use cases, streaming media or proxying a few streams won’t hit the daily limit. Even for small-scale or personal use of IPTV or media streams, 100,000 requests per day will cover most needs.
- **No Additional Costs**: Cloudflare’s free tier is perfect for handling your project with no cost unless your usage exceeds the limit.

### Steps to Manually Deploy Your Worker

You can easily deploy this Worker to your Cloudflare account by following these steps:

1. Sign up or log in to your Cloudflare account at [https://dash.cloudflare.com/](https://dash.cloudflare.com/).

2. Navigate to **Workers** in the left sidebar.

3. Click **Create a Worker**.

4. Copy the code from the [worker.js](https://github.com/dtankdempse/streamed-su-sports/blob/main/streamed-su-sports-proxy/worker.js) file in this repository and paste it into the Cloudflare Workers editor.

5. Save and deploy the Worker.

6. Use the automatically generated Worker URL to start proxying your streams or bind it to a custom domain.

### Free Plan Benefits:

- **100,000 requests per day** (3 million requests per month).
- **Free to deploy** Cloudflare Workers with no upfront costs.
- **Global infrastructure** for fast performance and low latency.

For more details, you can visit the [Cloudflare Workers Documentation](https://developers.cloudflare.com/workers/).
