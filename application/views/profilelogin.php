<h2>Create A Profile to Receive FREE Alerts</h2>
<p>This site doesn't require registration to use, but if you
 add your mobile phone number or email address below you can
  maintain a list of vessels to receive notifications when they 
  pass through the area. We ask only for your first name or a nickname 
  to personalize your profile. It can be doesn't even have to be real.
  No body will check.</p>  
  
<h3>About Cookies and Tokens</h3>
<p>To keep your profile unique a randomly generated 
  alphanumeric token will be generated and saved in our database along 
  with your nickname and phone or email address. If you have cookies enabled
  in your web browser (most people do), you won't see or have to worry about the token.
  But if your a privacy advocate who prefers to disable cookies while browsing, there is still a 
  way to create a profile and use our free alert notification service. You'll just have to bookmark
  a url that points to your profile page which includes the token at the end in order to get in.</p>

  <h3>Fill out the form to get started</h3>
<?php echo form_open("profile/login"); ?>
<p><label for="proName">Name</label></br>
<input type="text" id="proName" name="proName" required maxlength="50" size="25"/></p>
<p><label for="proEmail">Email</label></br>
<input type="email" id="proEmail" name="proEmail" maxlenght="50" size="25"/></p>
<p><label for="proPhone">Phone</label></br>
<input type="text" id="proPhone" name="proPhone" placeholder="15635559876" pattern="^\d{11}$" maxlength="11" size="11"/></p>
<div class="emphasis">--> USA mobile phone 11-digit format</div>
<?php echo form_submit('submit', 'Submit'); 
echo form_close(); ?>

