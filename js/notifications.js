
const beamsClient = new PusherPushNotifications.Client({
instanceId: 'c229f02c-e435-41ed-899f-756bf916f1f5',
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
    .then(beamsClient => beamsClient.getDeviceId())
    .then(deviceId =>
      console.log('Successfully registered with Beams. Device ID:', deviceId)
    )
    .then(() => beamsClient.addDeviceInterest('CRT Passenger Vessels'))
    .then(() => beamsClient.getDeviceInterests())
    .then(interests => console.log('Current interests:', interests))
    .catch(console.error);
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