
const beamsClient = new PusherPushNotifications.Client({
instanceId: '1ba67efe-2cfc-4fc6-af88-890780051fe8',
});

beamsClient.start()
  .then(beamsClient => beamsClient.getDeviceId())
  .then(deviceId =>
    console.log('Successfully registered with Beams. Device ID:', deviceId)
  )
  .catch(console.error)


function requestPassengerNotifications() {
    console.log("requestPassengerNotifications()");
    beamsClient.start()
    .then(() => beamsClient.addDeviceInterest('CRT Passenger Vessels'))
    .then(() => console.log('Successfully registered and subscribed!'))
    .catch(console.error);
    /*
    beamsClient.start()
    .then(beamsClient => beamsClient.getDeviceId())
    .then(deviceId =>
      console.log('Successfully registered with Beams. Device ID:', deviceId)
    )
    .then(() => beamsClient.addDeviceInterest('CRT Passenger Vessels'))
    .then(() => beamsClient.getDeviceInterests())
    .then(interests => console.log('Current interests:', interests))
    .catch(console.error);
    */
}

function requestAllNotifications() {
    console.log("requestPassengerNotifications()");
    beamsClient.start()
    .then(beamsClient => beamsClient.getDeviceId())
    .then(deviceId =>
      console.log('Successfully registered with Beams. Device ID:', deviceId)
    )
    .then(() => beamsClient.addDeviceInterest('CRT All Vessels'))
    .then(() => beamsClient.getDeviceInterests())
    .then(interests => console.log('Current interests:', interests))
    .catch(console.error);
}







