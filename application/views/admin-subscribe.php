<!DOCTYPE html>
<html>
<head>
  <title>Subscribe: Admin Notification</title>  
  <link rel="stylesheet" href="<?php echo $css;?>" type="text/css">
  <script type="application/javascript" src="https://js.pusher.com/beams/1.0/push-notifications-cdn.js"></script>
</head>
<body>
  <div id="wrapper">    
    <div id="main">
        <script type="application/javascript">
            const beamsClient = new PusherPushNotifications.Client({
                instanceId: '<?php echo getEnv('PUSHER_INSTANCE_ID');?>',
            });
            console.log("request made for Passenger Notifications");
            beamsClient.start()
            .then(() => beamsClient.addDeviceInterest('admin'))
            .then(() => console.log('Successfully registered and subscribed!'))
            .catch(console.error);
        </script>
        <h3>Waypoint crossing notifications for select passenger vessels vessels passing Clinton, Iowa on the Mississippi river</h3>
        <p>Look for a request from your web browser to approve notifications from the CRT Passenger Vessels stream. 
        Accept it to subscribe to notification events.</p>
        <p><a href="<?php echo getEnv('BASE_URL');?>admin"><< Back</a></p>
    </div>
  </div>  
</body>
</html>