<h1>List of Vessels Logged</h1>
<p>This is a list of all transponder equipped commercial vessels that have passed by Clinton since September 2020. To view a record of the time and passage direction put a check by the vessel and click submit. You may also narrow the date range before submitting.</p>
<form action="" method="post"></form>

<ul class="vessels-list">
  <?php foreach($datalist as $vessel) {
  echo '<li><img class="vessel" src="'.$vessel['vesselImageUrl'].'" /><br><span>'.$vessel['vesselName'].'</span><br><span>'.$vessel['vesselID'].'</span><input type="checkbox" value="'.$vessel['vesselID'].'"></li>';
}
?>
  
</ul>  

<input type="submit" value="Submit" name="submit"/>
</form>

