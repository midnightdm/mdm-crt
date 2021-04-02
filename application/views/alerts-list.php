<div id="post-menu-body">
	<ul class="nav2">
	<li><a class="nav-link" href="../alerts">All</a></li>
	<li><a class="nav-link" href="passenger">Passenger</a></li>
	<li><a class="nav-link selected" href="watchlist">Watch List</a></li>  
	</ul>
	<div id="content-container">
			
		<div class="container">	
		<div class="row">
				<div class="col-4">
					<table class="table table-image">
					<thead>
						<tr>
							<th>Image</th> <th>Type</th> <th>Name</th><th>MMSI#</th>      
						</tr>
					</thead>
					<tbody>
						<?php echo $table_rows;?>
					</tbody>
					</table>   
				</div>
			</div>
		</div>

		<div class="container">
			<p>These view-worthy vessels which are scheduled to tour the upper Mississippi river in 2021 or 2022 have been preselected for 
			updates when vessels are near.</p>
			<p>
			<div class="button_cont"><a class="example_c" href="subscribePassenger">Get Notifications!</a></div>
			<p>The button above will trigger a request from your web browser to approve notifications from the CRT Passenger stream. Accepting will 
			join your device to get notification events for each of the listed vessels when traveling upriver:</p>
			<ol>
			<li>When the vessel's radio transponder is first detected</li>
			<li>When it reaches 3 miles South of the Clinton drawbridge</li>
			<li>When it arrives at the drawbridge</li>
			<li>At Lock and Dam 13</li>
			<li>At the point 3 miles North of Lock 13</li>
			</ol>
			<p>(The order of 2 - 5 is reversed for vessels traveling downriver.)</p>
		</div>
	
	</div>
</div>
