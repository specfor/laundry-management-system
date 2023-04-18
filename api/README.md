<div style="text-align: center;">
<h1><b>API DOCUMENTATION</b></h1>
</div> 

<h3>Database Migrations</h3>
<p>To make updates to databases with migrations, you have to 
create a file named "migrationLock.lock" in the base folder.</p>

<p>It will put the website into maintenance mode and start to run
migrations one by one in the alphabetical order of the naming</p>

<h3>API Endpoints</h3>

<h4>POST -  /api/v1/login</h4>
<p>Parameters - username , password</p>
<p>Used to log in Users.</p>
<p>Returned token should be set as a header named
"HTTP_AUTHORIZATION". This header should be sent with every request.</p>

<br>

<h4>POST -  /api/v1/customers/add</h4>
<p>Parameters - email , customer-name, phone-number
 , address</p>
<p>Used to add new customer to the database.</p>

<br>

<h4>GET - /api/v1/customers </h4>
<p>Parameters 
<br>
- start -> when there are more than 30 results 
results are broken into pages. index to start retrieving results
</p>
<p>Returns customer data</p>
