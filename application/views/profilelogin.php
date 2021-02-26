<h2>Create A Profile to Receive FREE Alerts</h2>
<p>This site doesn't require registration to use, but if you
 add your mobile phone number or email address below you can
  maintain a list of vessels to receive notifications when they 
  pass through the area. We ask only for your first name or a nickname 
  to personalize your profile. It doesn't even have to be real.
  No body will check. <span id="both">If you want notifications by both sms and email, set up two profiles.</span></p>  
  
<h3>About Cookies and Tokens</h3>
<p>To keep your profile unique a randomly generated 
  alphanumeric token will be generated and saved in our database along 
  with your nickname and phone or email address. If you have cookies enabled
  in your web browser (most people do), you won't see or have to worry about the token.
  But if you're a privacy advocate who prefers to disable cookies while browsing, there is still a 
  way to create a profile and use our free alert notification service. You'll just have to bookmark
  a url that points to your profile page which includes the token at the end in order to get in.</p>

  <h3>Fill out the form to get started</h3>
<?php echo form_open("profile/register"); ?>
<p><label for="proName">Name:</label></br>
<input type="text" id="proName" name="proName" required maxlength="50" size="25"/>

<label for="rPhone">Text</label><input type="radio" id="rPhone" name="method" value="sms" checked="checked"/>

<label for="rEmail">Email</label><input type="radio" id="rEmail" name="method" value="email" /></p>

<p><label id="destlab" for="destination">Mobile Phone Number (11-digit USA format):</label></br>
<input type="text" id="destination" name="destination" placeholder="15635559876" maxlength="11" size="11"/>
</p>


<?php echo form_submit('submit', 'Submit'); 
echo form_close(); ?>

<script src="<?php echo $main['path'];?>js/jquery-3.5.1.min.js"></script>
<script type="text/javascript">
$(document).ready(
  function() {
    $("input:radio").click(
      function() {
        if(document.getElementById('rPhone').checked) {
          var $str = "^\d{11}$";
          $('#destination').attr({type:"text", placeholder:"15635559876", pattern:$str, maxlength:"11", size:"11"});
          $('#destlab').text("Mobile Phone Number (11-digit USA format):");
        } else if(document.getElementById('rEmail').checked) {
          $('#destination').attr({type:"email", placeholder:"user@basedomain.tld", maxlength:"50", size:"25"});
          $('#destlab').text("Email Address:");
        }
      }
    );
  }
);

</script>