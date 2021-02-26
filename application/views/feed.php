<?php  echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n"; ?>
<rss xmlns:content="http://purl.org/rss/1.0/modules/content/" version="2.0">

<channel>
  <title><?php echo $title; ?></title>
  <link><?php echo getEnv('BASE_URL'); ?></link>
  <description>Waypoint crossing notifications for commercial vessels passing Clinton, Iowa on the Mississippi river.</description>
  <language>en</language>
  <pubDate><?php echo $pubdate; ?></pubDate>
  <?php echo $items; ?>
</channel>

</rss> 