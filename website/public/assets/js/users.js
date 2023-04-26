window.addEventListener("load",function(){
    document.getElementById("addUser").addEventListener("click", sendUserData2DB)
    document.getElementById('update').addEventListener('click', updateUserToDatabase)
    document.getElementById('changePass').addEventListener('click', changePass)
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

async function addUserToTable(userId, username, email, firstname, lastname, userRole,branchId) {
    let userTable = document.getElementById("userTable")

    let newRow = userTable.insertRow(-1)

    newRow.insertCell(0).innerText = userId
    newRow.insertCell(1).innerText = username
    newRow.insertCell(2).innerText = email
    newRow.insertCell(3).innerText = firstname
    newRow.insertCell(4).innerText = lastname
    newRow.insertCell(5).innerText = userRoles[userRole]
    newRow.insertCell(6).innerText = branchId
    newRow.insertCell(7).innerHTML = `<div class="input-group mb-3">
  <button onclick="editUser()" class="edit btn btn-primary fw-bold" type="button" id="btn-edit-${userId}" data-bs-toggle="modal" data-bs-target="#editUserModal">Edit User</button>
  <button onclick="prepareChangePass()" class="edit btn btn-primary fw-bold" type="button" id="btn-edit-${userId}" data-bs-toggle="modal" data-bs-target="#changePasswordModal">Change User Password</button>
  <button onclick="prepareDeleteUser()" class="delete btn btn-danger fw-bold" type="button" id="btn-delete-${userId}" data-bs-toggle="modal" data-bs-target="#confirmDelete">Delete</button>
</div>`
}

async function updateUserToDatabase() {
    let username = document.getElementById('newUsername').value
    let email = document.getElementById('newEmail').value
    let firstName = document.getElementById('newFirstName').value
    let lastName = document.getElementById('newLastName').value
    let userRole = document.getElementById('selectionUserRoles2').value

    if (!username || !firstName || !lastName || !userRole){
        alert("Fill all required fields.")
        return
    }
    
}

async function changePass() {
    let newPass = document.getElementById("newUserPassword").value
    let newPassConfirm = document.getElementById("newUserPasswordConfirm").value

    
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

