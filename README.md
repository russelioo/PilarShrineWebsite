# PilarShrineWebsite

## Facebook livestream indicator

The public website checks `/api/livestream-status` once per minute. When the
configured Facebook Page has an active live broadcast, a pulsing red **LIVE
NOW** banner appears below the site navigation and links directly to the video.

The administrator can control this without a Facebook API token from the
dashboard using the **Turn livestream ON/OFF** button. Run migrations once after
deploying this feature:

```shell
php artisan migrate
```

Create a Meta app with access to the Pilar Shrine Facebook Page, then add these
server-side values to `.env`:

```dotenv
FACEBOOK_PAGE_ID=your_numeric_page_id
FACEBOOK_PAGE_URL=https://www.facebook.com/PilarShrineSorsogon
FACEBOOK_PAGE_ACCESS_TOKEN=your_page_access_token
FACEBOOK_GRAPH_VERSION=v23.0
```

Never expose the Page access token through a `VITE_` environment variable.
After changing production environment values, run `php artisan config:clear`.
Diocesan Shrine and Parish of Our Lady of the Pillar
