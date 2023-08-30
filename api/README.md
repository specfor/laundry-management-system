<div style="text-align: center;">
<h1><b>API DOCUMENTATION</b></h1>
</div> 

<h3>Database Migrations</h3>
<p>To make updates to databases with migrations, you have to 
create a file named "migrationLock.lock" in the base folder.</p>

<p>It will put the website into maintenance mode and start to run
migrations one by one in the alphabetical order of the naming</p>

<h3>API Endpoints</h3>

<h4>GET - /api/v1/whoami</h4>
Returns the details of the current user

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
customer-id - optional
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
branch-id - optional
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
user-id - optional

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
password - optional
role - optional
email - optional
firstname - optional
lastname - optional
branch-id - optional

POST - /api/v1/users/delete
Parameters
user-id - compulsory

GET - /api/v1/user-roles/permissions
Get all permission types available

GET - /api/v1/users/login-history
page-num
user-id - use this user passed user-id only if user has permission to get other user data. 
login-date
ip-address

POST - /api/v1/profile/profile-picture  ********* must be a multipart request (not json) ********
profile-picture - compulsory  - mulitpart parameter

POST - /api/v1/users/update/profile-picture  ********* must be a multipart request (not json) ********
profile-picture - compulsory - multipart parameter
user-id - compulsory - must be passed as a header

GET - /api/v1/user-roles
page-num - optional
role-id - optional
name - optional
permissions - optional
description - optional

POST - /api/v1/user-roles/add
name - compulsory
permissions - compulsory
description - optional

POST - /api/v1/user-roles/update
role-id - compulsory
name - optional
permissions - optional
            {
                "users": ["read", "write", "modify", "delete"]       "users"-> main permission class, one or more of
                                                                    permissions from ["read", "write", "modify", "delete"]
            }
description - optional

POST - /api/v1/user-roles/delete
role-id - compulsory

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

GET - /api/v1/orders/status-messages
Get all available order status messages

GET - /api/v1/orderCount
Get daily order counts.
branch-id - passed branch id is used only if user is admin level user.
no-days-backward - number of days to look backward to get daily order counts.

POST - /api/v1/orders/add
Parameters
items - compulsory - array of {item-id: {'amount':amount,'return-date': return-date, 'defects': [defect1, defect2,...]}, item-id2: { ...}}
customer-id - optional
total-price - optional - only if need to override generated price
branch-id - optional - only if user is not someone asigned to a branch, if user is assigned to a branch then send branch-id will not be used.
customer-comments - optional

POST - /api/v1/orders/update
Parameters
order-id - compulsory
customer-id - optional
branch-id - optional
order-status - optional
items - optional - should contain all items as order items are overwritten.
                    array of {item-id: {'amount':amount,'return-date': return-date, 'defects': [defect1, defect2,...]}, item-id2: { ...}}
total-price - optional

POST - /api/v1/orders/delete
Parameters
order-id - compulsory

GET - /api/v1/payments
Parameters
page-num - optional
payment-id - optional
order-id - optional
paid-date - optional

POST - /api/v1/payments/add
Parameters
order-id - compulsory
paid-amount - compulsory
paid-date - optional

POST - /api/v1/payments/update
Parameters
payment-id - compulsory
refunded - optional

POST - /api/v1/payments/delete
Parameters
payment-id - compulsory



ACCOUNTING API SECTION


GET - /api/v1/taxes
Parameters
page-num - optional
tax-id - optional
tax-name - optional
description - optional
rate-min - optional
rate-max - optional
limit

POST - /api/v1/taxes/add
Parameters
tax-name - compulsory
description - optional
tax-rate - compulsory

POST - /api/v1/taxes/update
Parameters
tax-id - compulsory
tax-name - optional
description - optional
tax-rate - optional

POST - /api/v1/taxes/delete
Parameters
tax-id - compulsory

GET - /api/v1/financial-accounts
Parameters
page-num
account-id
account-name
account-code
account-type
tax-id
description
limit

POST - /api/v1/financial-accounts/add
Parameters
account-name - compulsory
account-code - compulsory
account-type - compulsory
tax-id - compulsory
description

POST - /api/v1/financial-accounts/update
Parameters
account-id - compulsory
account-name
tax-id
description

POST - /api/v1/financial-accounts/delete
Parameters
account-id - compulsory

GET - /api/v1/financial-account-types
Returns all account categories and their sub categories.

GET - /api/v1/general-ledger
Parameters
page-num
narration
date
record-id
limit - no of records to return

POST - /api/v1/general-ledger/add 
Parameters
narration - compulsory - a message explaining why this entry is adding
body - compulsory - structure is as folllows
        {
            "narration": "buying some blue berries",
            "tax-type": 'tax inclusive',          ---one of 'no tax', 'tax inclusive' or 'tax exclusive'. 'no tax' will
                                                    not add any taxes. use 'tax inclusive' when taxes are already in the 
                                                    credit/debit amount.
            "body": [
                {
                "account_id": 3,
                "debit": 2300.50,
                "description": "ha",
                "tax_id": 2                  ---optional. use when need to override the default account tax rate.
                },
                {
                "account_id": 2,
                "credit": 2300.50,
                "description": ""
                }, 
                {...}
            ]
        }     
date - optional - "yyyy-mm-dd"

GET - /api/v1/account-totals
By just hitting the endpoint without 'force-recalculate' will calculate total credit & debit amounts in a smart way
    preventing read of whole ledger records table.
Parameters
force-recalculate - optional - only use when necessary. this will read whole ledger record table and calculate total
                                credit & debit amounts. with billions of records this can take some time.

GET - /api/v1/account-totals/day-basis
Parameters
account-id
dates      - 2023-08-10,2023-08-11,...  - can get details of more dates once by sending a string of dates seperated by commas.
year 

GET - /api/v1/account-totals/month-basis 
Parameters
account-id
month
year

GET - /api/v1/account-totals/year-basis 
Parameters
account-id
year