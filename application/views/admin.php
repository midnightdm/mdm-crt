<ul class="nav">
  <li><a class="nav-link selected" href="admin">Login</a></li>
  <li><a class="nav-link" href="admin/vessels">Vessels</a></li>
  <li><a class="nav-link" href="admin/watchlist">Watch List</a></li>  
</ul>
<h1>Admin Login</h1>
<h2><?php echo $response;?></h2>
<form class="myForm" method="post" enctype="application/x-www-form-urlencoded" action="admin">

<fieldset>
<legend>Enter credentials to get your cookie and continue navigating with authority.</legend>
<p><label>Email 
<input type="email" name="email_address" size="25">
</label></p>
<p><label>Password 
<input type="password" name="password" size="25">
</label></p>
<p><button>Submit</button></p>
</fieldset>


</form>

