let user_Id ;

window.addEventListener("load",function(){
    document.getElementById("addUser").addEventListener("click", sendUserData2DB)
    document.getElementById('update').addEventListener('click', updateUserToDatabase)
    document.getElementById('changePass').addEventListener('click', changePass)

    getAllUsers()
})

async function sendUserData2DB() {
    let username = document.getElementById("username").value
    let email = document.getElementById("email").value
    let fName = document.getElementById("firstName").value
    let lName = document.getElementById("lastName").value
    let password = document.getElementById("password").value
    let userRole = document.getElementById("selectionUserRoles1").value
    let branchId = document.getElementById("branchId").value


    if (!username || !fName || !lName || !password || !userRole ){
        alert("Fill all required fields.")
        return
    }
    
    try{
        let response = await sendJsonRequest("http://www.laundry-api.localhost/api/v1/users/add",{
                username:username,
                password:password,
                role:userRole,
                email:email,
                firstname:fName,
                lastname:lName,
                "branch-id":branchId               
            })
        
            console.log(await response.json())

            //let res = await getJsonResponse("http://www.laundry-api.localhost/api/v1/users")
            //console.log(await res.json())
            await addUserToTable(username,email,fName,lName,userRole,branchId)
           
            clearAllInputs()

    }catch(err){

    }
    
}

function clearAllInputs() {
    document.getElementById("username").value = ''
    document.getElementById("email").value = ''
    document.getElementById("firstName").value = ''
    document.getElementById("password").value = ''
    document.getElementById("lastName").value = ''
    document.getElementById("branchId").value = ''
}

async function addUserToTable(username, email, firstname, lastname, userRole,branchId) {
       
    let response = await getJsonResponse("http://www.laundry-api.localhost/api/v1/users")
    
    let resp = await response.json()
    let user = resp["body"]["users"].slice(-1)
    let id = user[0]["id"]
    
    let userTable = document.getElementById("userTable")

    let newRow = userTable.insertRow(-1)

    newRow.insertCell(0).innerText = id
    newRow.insertCell(1).innerText = username
    newRow.insertCell(2).innerText = email
    newRow.insertCell(3).innerText = firstname
    newRow.insertCell(4).innerText = lastname
    newRow.insertCell(5).innerText = userRole
    newRow.insertCell(6).innerText = branchId
    newRow.insertCell(7).innerHTML = `<div class="input-group mb-3">
  <button onclick="editUser()" class="edit btn btn-primary fw-bold" type="button" id="btn-edit-${id}" data-bs-toggle="modal" data-bs-target="#editUserModal">Edit User</button>
  <button onclick="prepareChangePass()" class="edit btn btn-primary fw-bold" type="button" id="btn-edit-${id}" data-bs-toggle="modal" data-bs-target="#changePasswordModal">Change User Password</button>
  <button onclick="prepareDeleteUser()" class="delete btn btn-danger fw-bold" type="button" id="btn-delete-${id}" data-bs-toggle="modal" data-bs-target="#confirmDelete">Delete</button>
</div>`
}

function editUser(){
    user_Id = event.target.id.split('-')[2]
}

async function updateUserToDatabase() {
    
    console.log(user_Id)

    let email = document.getElementById('newEmail').value
    let firstName = document.getElementById('newFirstName').value
    let lastName = document.getElementById('newLastName').value
    let userRole = document.getElementById('selectionUserRoles2').value
    let branchId = document.getElementById("newBranchId").value

    if (!firstName || !lastName || !userRole || !email || !branchId){
        alert("Fill all required fields.")
        return
    }else{
        let response = await sendJsonRequest("http://www.laundry-api.localhost/api/v1/users/update",{
            "user-id": user_Id,
            role :userRole,
            email : email,
            firstname : firstName,
            lastname : lastName,
            "branch-id" : branchId
        })
       let updateRes = await response.json()

       if(updateRes.statusMessage == "success"){
        let userTable = document.getElementById("userTable")
        for(let i = 0, row; row = userTable.rows[i]; i++){
            if(row.cells[0].innerText == user_Id){
                row.cells[2].innerText = email
                row.cells[3].innerText = firstName
                row.cells[4].innerText = lastName
                row.cells[5].innerText = userRole
                row.cells[6].innerText = branchId
            }
        }
       }
                
    }
    
}

async function changePass() {
    let newPass = document.getElementById("newUserPassword").value
    let newPassConfirm = document.getElementById("newUserPasswordConfirm").value

    
}

async function prepareDeleteUser(){
    user_Id = event.target.id.split("-")[2]
}

async function confirmDeletion(){


}



async function getAllUsers(){
    let response = await getJsonResponse("http://www.laundry-api.localhost/api/v1/users")
    

    let resp = await response.json()
    if(resp.statusMessage=="success"){
        let users = resp["body"]["users"]
    console.log(users)

    users.forEach(function(user){
        
         function getRole(role){
            if(role == 1){
                return "Administrator"
            }else if(role == 2){
                return "Manager"
            }else{
                return "Cashier"
            }
        }


        let userTable = document.getElementById("userTable")

        let newRow = userTable.insertRow(-1)
    
        newRow.insertCell(0).innerText = user["id"]
        newRow.insertCell(1).innerText = user["username"]
        newRow.insertCell(2).innerText = user["email"]
        newRow.insertCell(3).innerText = user["firstname"]
        newRow.insertCell(4).innerText = user["lastname"]
        newRow.insertCell(5).innerText = getRole(user["role"])
        newRow.insertCell(6).innerText = user["branch_id"]
        newRow.insertCell(7).innerHTML = `<div class="input-group mb-3">
      <button onclick="editUser()" class="edit btn btn-primary fw-bold" type="button" id="btn-edit-${user["id"]}" data-bs-toggle="modal" data-bs-target="#editUserModal">Edit User</button>
      <button onclick="prepareChangePass()" class="edit btn btn-primary fw-bold" type="button" id="btn-edit-${user["id"]}" data-bs-toggle="modal" data-bs-target="#changePasswordModal">Change User Password</button>
      <button onclick="prepareDeleteUser()" class="delete btn btn-danger fw-bold" type="button" id="btn-delete-${user["id"]}" data-bs-toggle="modal" data-bs-target="#confirmDelete">Delete</button>
    </div>`
    })
    }
    
    
}


async function sendJsonRequest(url, jsonBody) {
    return await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' +localStorage.getItem('authToken')
        },
        body: JSON.stringify(jsonBody),
        credentials: "same-origin"
    })
}

async function getJsonResponse(url) {
    return await fetch(url, {
        headers: {
            'Authorization': 'Bearer ' +localStorage.getItem('authToken')
        },
        credentials: "same-origin"
    })
}

