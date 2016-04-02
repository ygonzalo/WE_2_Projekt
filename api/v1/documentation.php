<?php
$app->get('/', function() use ($app) {
	?>
	<h1>Watched that movie REST API</h1>
	<h2>Authentication</h2>
	<h3>Session</h3>
	<table border="1">
		<tbody>
		<tr>
			<th>URL</th>
			<td>/session</td>
		</tr>
		<tr>
			<th>Method</th>
			<td>GET</td>
		</tr>
		<tr>
			<th>Results</th>
			<td>Current session data as JSON</td>
		</tr>
		<tr>
			<th>Success Response</th>
			<td>{ "userID":[string], "email":[string], "name":[string] }</td>
		</tr>
		</tbody>
	</table>
	<h3>Sign Up</h3>
	<table border="1">
		<tbody>
		<tr>
			<th>URL</th>
			<td>/signUp</td>
		</tr>
		<tr>
			<th>Method</th>
			<td>POST</td>
		</tr>
		<tr>
			<th>Body Params</th>
			<td>{ <b>"user"</b>:{ <b>"email"</b> : [string], <b>"password"</b>:[string], <b>"name"</b>:[string]}}</td>
		</tr>
		<tr>
			<th>Results</th>
			<td>Creates new user account</td>
		</tr>
		<tr>
			<th>Success Response</th>
			<td>{ "status":"success", "message":"User account created successfully" }</td>
		</tr>
		</tbody>
	</table>
	<h3>Login</h3>
	<table border="1">
		<tbody>
		<tr>
			<th>URL</th>
			<td>/login</td>
		</tr>
		<tr>
			<th>Method</th>
			<td>POST</td>
		</tr>
		<tr>
			<th>Body Params</th>
			<td>{ <b>"user"</b>:{ <b>"email"</b> : [string], <b>"password"</b>:[string]}}</td>
		</tr>
		<tr>
			<th>Results</th>
			<td>Logs user in</td>
		</tr>
		<tr>
			<th>Success Response</th>
			<td>{"status": "success","message": "Logged in successfully.","name": [string],"userID": [string],"email": [string]}</td>
		</tr>
		</tbody>
	</table>
	<h3>Logout</h3>
	<table border="1">
		<tbody>
		<tr>
			<th>URL</th>
			<td>/logout</td>
		</tr>
		<tr>
			<th>Method</th>
			<td>GET</td>
		</tr>
		<tr>
			<th>Results</th>
			<td>Logs user out</td>
		</tr>
		<tr>
			<th>Success Response</th>
			<td>{"status": "success","message": "Logged Out Successfully..."}</td>
		</tr>
		</tbody>
	</table>

	<h2>Movies</h2>
	<h3>Search movie by title</h3>
	<table border="1">
		<tbody>
		<tr>
			<th>URL</th>
			<td>/movies/search/<b>:query</b></td>
		</tr>
		<tr>
			<th>Method</th>
			<td>GET</td>
		</tr>
		<tr>
			<th>Results</th>
			<td>Array with all found movies</td>
		</tr>
		<tr>
			<th>Success Response</th>
			<td>{"matches":[ ...all matched movies... ], "status": "success"}</td>
		</tr>
		</tbody>
	</table>

	<h3>Change movie status</h3>
	<table border="1">
		<tbody>
		<tr>
			<th>URL</th>
			<td>/movies/:movieID/status</td>
		</tr>
		<tr>
			<th>Method</th>
			<td>POST</td>
		</tr>
		<tr>
			<th>Body Params</th>
			<td>{ <b>"status"</b>:[string], <b>"index"</b>:[integer]}</td>
		</tr>
		<tr>
			<th>Results</th>
			<td>Changes the status of a movie to <b>watched</b>, <b>watchlist</b> or <b>deleted</b></td>
		</tr>
		<tr>
			<th>Success Response</th>
			<td>{"status": "success"}</td>
		</tr>
		</tbody>
	</table>

	<h3>Get user's watchlist</h3>
	<table border="1">
		<tbody>
		<tr>
			<th>URL</th>
			<td>/movies/watchlist</td>
		</tr>
		<tr>
			<th>URLS Params</th>
			<td></td>
		</tr>
		<tr>
			<th>Results</th>
			<td>Array with all movies in watchlist</td>
		</tr>
		<tr>
			<th>Success Response</th>
			<td>{"matches":[ ...all matched movies... ], "status": "success"}</td>
		</tr>
		</tbody>
	</table>
	<h3>Get list of user's watched movies</h3>
	<table border="1">
		<tbody>
		<tr>
			<th>URL</th>
			<td>/movies/watched</td>
		</tr>
		<tr>
			<th>Method</th>
			<td>GET</td>
		</tr>
		<tr>
			<th>Results</th>
			<td>Array with watched movies</td>
		</tr>
		<tr>
			<th>Success Response</th>
			<td>{"matches":[ ...all matched movies... ], "status": "success"}</td>
		</tr>
		</tbody>
	</table>

	<h2>Friends</h2>
	<h3>Find a user by name or email</h3>
	<table border="1">
		<tbody>
		<tr>
			<th>URL</th>
			<td>/friends/search/<b>:query</b></td>
		</tr>
		<tr>
			<th>Method</th>
			<td>GET</td>
		</tr>
		<tr>
			<th>Results</th>
			<td>Array with all matched users</td>
		</tr>
		<tr>
			<th>Success Response</th>
			<td>{"users":[ ...all matched users... ], "status": "success"}</td>
		</tr>
		</tbody>
	</table>
	<h3>Send a friend request</h3>
	<table border="1">
		<tbody>
		<tr>
			<th>URL</th>
			<td>/request</td>
		</tr>
		<tr>
			<th>Method</th>
			<td>POST</td>
		</tr>
		<tr>
			<th>Body Params</th>
			<td>{<b>"friendID"</b>:[integer]}</td>
		</tr>
		<tr>
			<th>Results</th>
			<td>Adds a new friend request</td>
		</tr>
		<tr>
			<th>Success Response</th>
			<td>{"status": "success", "message":"Request sent"}</td>
		</tr>
		</tbody>
	</table>
	<h3>Accept or deny a friend request</h3>
	<table border="1">
		<tbody>
		<tr>
			<th>URL</th>
			<td>/request</td>
		</tr>
		<tr>
			<th>Method</th>
			<td>PUT</td>
		</tr>
		<tr>
			<th>Body Params</th>
			<td>{<b>"friendID"</b>:[integer],<b>"status"</b>:[string]}</td>
		</tr>
		<tr>
			<th>Results</th>
			<td>Updates user relationship to accepted or denied</td>
		</tr>
		<tr>
			<th>Success Response</th>
			<td>{"status": "success", "message":"Status changed to accepted/denied"}</td>
		</tr>
		</tbody>
	</table>
	<h3>Get list of all pending requests</h3>
	<table border="1">
		<tbody>
		<tr>
			<th>URL</th>
			<td>/requests</td>
		</tr>
		<tr>
			<th>Method</th>
			<td>GET</td>
		</tr>
		<tr>
			<th>URLS Params</th>
			<td></td>
		</tr>
		<tr>
			<th>Results</th>
			<td>Array with all friend requests</td>
		</tr>
		<tr>
			<th>Success Response</th>
			<td>{"requests":[ ...all requests... ], "status": "success"}</td>
		</tr>
		</tbody>
	</table>
	<h3>Get list of all friends</h3>
	<table border="1">
		<tbody>
		<tr>
			<th>URL</th>
			<td>/friendlist</td>
		</tr>
		<tr>
			<th>Method</th>
			<td>GET</td>
		</tr>
		<tr>
			<th>URLS Params</th>
			<td></td>
		</tr>
		<tr>
			<th>Results</th>
			<td>Array with all friend requests</td>
		</tr>
		<tr>
			<th>Success Response</th>
			<td>{"friends":[ ...all friends... ], "status": "success"}</td>
		</tr>
		</tbody>
	</table>
	<h2>User</h2>
	<h3>Change Password</h3>
	<table border="1">
		<tbody>
		<tr>
			<th>URL</th>
			<td>/changePassword</td>
		</tr>
		<tr>
			<th>Method</th>
			<td>POST</td>
		</tr>
		<tr>
			<th>Body Params</th>
			<td>{<b>"password"</b>:[string]}</td>
		</tr>
		<tr>
			<th>Results</th>
			<td>Changes the user's password</td>
		</tr>
		<tr>
			<th>Success Response</th>
			<td>{"status": "success","message":"Password changed"}</td>
		</tr>
		</tbody>
	</table>
	<h3>Change Email</h3>
	<table border="1">
		<tbody>
		<tr>
			<th>URL</th>
			<td>/changeEmail</td>
		</tr>
		<tr>
			<th>Method</th>
			<td>POST</td>
		</tr>
		<tr>
			<th>Body Params</th>
			<td>{<b>"email"</b>:[string]}</td>
		</tr>
		<tr>
			<th>Results</th>
			<td>Changes the user's email</td>
		</tr>
		<tr>
			<th>Success Response</th>
			<td>{"status": "success","message":"Email changed"}</td>
		</tr>
		</tbody>
	</table>
	<?php
});