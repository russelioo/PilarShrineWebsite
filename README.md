# PilarShrineWebsite

## Inquiries

Authenticated users can find registered users by name and exchange private
church-related messages at `/inquiries` (also linked from the parishioner and
staff Inquiries pages). Use Refresh messages to check for replies. Only the
sender and recipient can download attachments, stored on the private local disk.
Each sender may upload 3 images and 3 documents per Philippine calendar day
across all conversations, at most 10 MB each.

Run `php artisan migrate` when deploying. PHP must allow `upload_max_filesize=10M`,
`post_max_size=65M`, and `max_file_uploads=6` or higher. `public/.user.ini` provides
these values for CGI/FPM; for `artisan serve`, use
`php -d upload_max_filesize=10M -d post_max_size=65M artisan serve`.
Configure the web server request body limit to at least 65 MB as well.

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
