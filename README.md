# Simwood API Examples

We have created a couple of demos that could form the basis of your own monitoring and alerting. These are in PHP and hook directly into our API and leverage our realtime 'calls in progress' data.

For those who aren't familiar with this, we bill every customer call pretty much every second, and we expose that data through the API. Given certain attacks relay on large volumes of calls being placed in parallel this enables you to monitor for activity on your account that doesn't fit your normal business pattern. Of course, we'll alert you if a call is to a known bad number or your other thresholds are breached, but what about calls that actually get through? 

These features enables you to see real-time where you are spending money and drive your own business logic;

## The Examples

### simwood_map.php

This example simply calls the API and visualises the data both in a Google GeoChart and a sorted table. This might be the basis of something interesting for your NOC wall!

### simwood_alert.php

This is far more what we envisaged for this end-point - a simple query to get calls in progress which is then iterated over, applying your own business logic. It may be normal for you to have large volumes of calls to a particular country, it may not. Here you can set your own simple rules by country and alert accordingly!

## Our API

Full documentation on our API can be found at https://mirror.simwood.com/pdfs/APIv3.pdf

We look forward to seeing what you build with it.
