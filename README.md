# Ditco Affiliate Gateway

Backend for affiliate landing pages. A PHP + MySQL API with a Node.js notification service.
Affiliates register, get a landing page with their product, and clicks to the main
product site are tracked.

## Setup

1. Install PHP dependencies

   composer install

2. Create the .env file and fill in your DB details and JWT secret

   cp .env.example .env

   The JWT secret needs to be at least 32 characters.

3. Load the schema into your database

   mysql -h <host> -P <port> -u <user> -p ditco_affiliate < schema.sql

   If you don't have a mysql client that works with your server, run php load.php
   from the project root instead (same result).

4. Start the API

   php -S 127.0.0.1:8000 -t public

## Notification service

In another terminal:

   cd notification-service
   npm install
   npm start

It runs on port 4000. Every time an affiliate registers, it prints a line like:

   [NOTIFICATION] affiliate.registered: Acme Corp <acme@example.com> ...

## Notes

- Slugs are unique; if one is taken a number is appended (acme-1, acme-2, ...).
- Passwords must have at least 2 uppercase, 2 lowercase, 1 number and 1 special
  character.
- Click tracking is rate limited to 5 requests per IP per 60 seconds.
- DB credentials and the JWT secret live in .env, which is not committed.