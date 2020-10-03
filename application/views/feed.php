<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">

<channel>
  <title><?php echo $title; ?></title>
  <link><?php echo getEnv('BASE_URL'); ?></link>
  <description>Vessel Waypoint Passage Alerts</description>
  <pubDate><?php echo $pubdate; ?></pubDate>
  <?php echo $items; ?>
</channel>

</rss> 