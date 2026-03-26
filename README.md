# Personal Spatial Memory Log

## Overview

Personal Spatial Memory Log is a Laravel application for tracking meaningful places, preserving spatial experiences, storing memory notes and reference images, and viewing saved locations on an interactive map.

The application combines a server-rendered CRUD interface with JSON map endpoints, queued geocoding, and realtime map updates over WebSockets. It is designed as a deployable production application, not a scaffold or demo.

## Features

- Place CRUD with place name, coordinates, context, impression, experience time, memory notes, reference image, and revisit intention
- Interactive map view powered by Leaflet
- Automatic address geocoding with queued background processing
- Nearby place lookup based on browser geolocation
- Realtime place create, update, and delete broadcasts
- Public landing page and unified auth entry page
- Email pre-verification registration flow
- Google OAuth login via Socialite
- Google One Tap login
- Theme support with persisted light, dim, and dark modes
- Filtered and sortable place listing
- Responsive Blade UI built with Tailwind and Alpine

## Tech Stack

- Backend: PHP 8.3, Laravel 13
- Frontend: Blade, Vite, Tailwind CSS v4, Alpine.js
- HTTP client: Axios
- Maps: Leaflet
- Auth integration: Laravel Socialite, Google Identity Services
- Database: MySQL
- Cache: Redis
- Queue: Redis
- Realtime: Laravel Reverb, Pusher protocol, Laravel Echo, pusher-js
- Storage: Laravel filesystem public disk for uploaded images

## Architecture

### HTTP flow

- Web routes in routes/web.php
- API routes in routes/api.php
- Server-rendered UI handled by Blade views
- Place CRUD handled by PlaceController
- Map and nearby JSON endpoints handled by Api\MapController
- Auth flows handled by dedicated auth controllers

### Background flow

- Place create/update triggers observer side effects
- Observer invalidates map tile cache
- Pending addresses dispatch GeocodePlace to the queue
- Geocoding resolves coordinates and updates the place record

### Realtime flow

- Place lifecycle events broadcast over places.map
- Events are queued and delivered through Laravel broadcasting
- Frontend is prepared for Pusher-compatible WebSocket transport
- Reverb is the default broadcast backend and uses the Pusher protocol

## Installation

composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --force
npm run build

If you are using uploaded images:

php artisan storage:link

## Environment

```
APP_NAME=
APP_ENV=
APP_DEBUG=
APP_URL=

DB_CONNECTION=mysql
DB_HOST=
DB_PORT=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=database

REDIS_HOST=
REDIS_PORT=
REDIS_PASSWORD=
REDIS_DB=
REDIS_CACHE_DB=
REDIS_QUEUE_DB=
REDIS_BROADCAST_DB=

BROADCAST_CONNECTION=reverb

REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
REVERB_HOST=
REVERB_PORT=
REVERB_SCHEME=
REVERB_ALLOWED_ORIGINS=

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=
PUSHER_HOST=
PUSHER_PORT=
PUSHER_SCHEME=
PUSHER_USE_TLS=

VITE_REVERB_APP_KEY=
VITE_REVERB_HOST=
VITE_REVERB_PORT=
VITE_REVERB_SCHEME=
VITE_PUSHER_APP_CLUSTER=

MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME=

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=
```

## Running Locally

npm run build
composer dev

## Production Notes

- Run npm run build during deployment
- Run database migrations before serving traffic
- Ensure storage:link exists if image uploads are enabled
- Configure Redis for cache, queue, and broadcast support
- Run a queue worker continuously
- Run Reverb or a compatible WebSocket server
- Use Laravel cache commands in production
- Set APP_DEBUG=false
- Protect environment secrets

## License

MIT
