<div style="text-align: center;">
<h1><b>API DOCUMENTATION</b></h1>
</div> 

<h3>Database Migrations</h3>
<p>To make updates to databases with migrations, you have to 
create a file named "migrationLock.lock" in the base folder.</p>

<p>It will put the website into maintenance mode and start to run
migrations one by one in the alphabetical order of the naming</p>

<h3>API Endpoints</h3>

<h4>POST - /api/v1/login</h4>
<p>Parameters - username , password</p>
<p>Used to log in Users.</p>
<p>Returned token should be set as a header named
"HTTP_AUTHORIZATION". This header should be sent with every request.</p>

<br>

<h4>POST - /api/v1/customers/add</h4>
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
page-num - optional -> when there are more than 30 results, 
results are broken into pages. page number to retrieve results
branch-id - optional
email - optional
phone-number - optional
name - optional
address - optional
banned - optional
join-date - optional

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

GET - /api/v1/branches
Parameters
page-num - optional
branch-name - optional
address - optional
manager-id - optional
phone-number - optional

POST - /api/v1/branches/add
Parameters
branch-name - compulsory
address - optional
manager-id - optional
phone-number - optional

POST - /api/v1/branches/update
Parameters
branch-id - compulsory
branch-name - optional
phone-number - optional
address - optional
manager-id - optional

POST - /api/v1/branches/delete
Parameters
branch-id - compulsory

GET - /api/v1/employees
Parameters
page-num - optional
name - optional
address - optional
email - optional
phone-number - optional
branch-id - optional
is-left - optional

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
page-num - optional
username - optional
email - optional
name - optional
role - optional
branch-id - optional

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

POST - /api/v1/users/delete
Parameters
user-id - compulsory

GET - /api/v1/category
Parameters
page-num - optional
category-name - optional

POST - /api/v1/category/add
Parameters
category-name - compulsory

POST - /api/v1/category/update
Parameters
category-id - compulsory
category-name - compulsory

POST - /api/v1/category/delete
Parameters
category-id - compulsory

GET - /api/v1/items
Parameters
page-num - optional
item-id - optional
item-name - optional
item-price - optional
blocked - optional

POST - /api/v1/items/add
Parameters
item-name - compulsory
item-price - optional - [[categ_1, categ_2], 350.00]
ex - "item-price": [["washing","dry cleaning"], 758]
blocked - optional

POST - /api/v1/items/update
Parameters
item-id - compulsory
item-name - optional
item-price - optional
blocked - optional

POST - /api/v1/items/delete
Parameters
item-id - compulsory

GET - /api/v1/orders
Parameters
page-num - optional
order-id - optional
branch-id - optional
added-date - optional
order-status - optional

POST - /api/v1/orders/add
Parameters
items - compulsory - array of [item-id: amount, item-id2: amount, ...]
total-price - optional - only if need to override generated price
branch-id - optional - only if user is not someone asigned to a branch, if user is assigned to a branch then send branch-id will not be used.

POST - /api/v1/orders/delete
Parameters
order-id - compulsory