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
<p>Parameters<br>
customer-name - compulsory,
email - optional, 
phone-number - optional,
address - optional, 
branch-id - optional
</p>
<p>Used to add new customer to the database.</p>

<br>

<h4>GET - /api/v1/customers </h4>
<p>Parameters 
<br>
start - optional -> when there are more than 30 results 
results are broken into pages. index to start retrieving results
branch-id - optional
</p>
<p>Returns customer data</p>

<br>

<h4>POST - /api/v1/customers/update</h4>
<p>Parameters 
<br>
customer-id - compulsory,
customer-name - optional,
email - optional, 
phone-number - optional,
address - optional, 
branch-id - optional,
banned - optional
</p>
<p>Updates customer data</p>

POST - /api/v1/customers/delete
Parameters
customer-id - compulsory

POST - /api/v1/branches/add
Parameters
branch-name - optional
address - optional
manager-id - optional

GET - /api/v1/branches
start - optional

POST - /api/v1/branches/update
Parameters
branch-id - compulsory
branch-name - optional
address - optional
manager-id - optional

POST - /api/v1/branches/delete
Parameters
branch-id - compulsory

GET - /api/v1/employees
Parameters
start - optional

POST - /api/v1/employees/add
Parameters
employee-name - compulsory
address - optional
email - optional
phone-number - optional
branch-id - optional
join-date - optional
left-date - optional

POST - /api/v1/employees/update
Parameters
employee-id - compulsory
employee-name - optional
address - optional
email - optional
phone-number - optional
branch-id - optional
join-date - optional
left-date - optional

POST - /api/v1/employees/delete
Parameters
employee-id - compulsory

GET - /api/v1/users
Parameters
start - optional

POST - /api/v1/users/add
Parameters
username - compulsory
password - compulsory
role - compulsory
email - optional
firstname - optional
lastname - optional
branch-id - optional

POST - /api/v1/users/update
Parameters
user-id - compulsory
username - optional
password - optional
role - optional
email - optional
firstname - optional
lastname - optional
branch-id - optional

