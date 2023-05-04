let cus_id;

window.addEventListener("load",function(){
    document.getElementById("btnAddCus").addEventListener("click",sendCustomerDataToDB)
    document.getElementById("btnEditCus").addEventListener("click",updateCusToDB)
    document.getElementById("btnConfirmDeletion").addEventListener("click",deleteCustomer)
    document.getElementById("phoneNumSearch").addEventListener("keyup",getAllCustomersFromSearch)
    document.getElementById("customerNameSearch").addEventListener("keyup",getAllCustomersFromSearch)
    document.getElementById("cusNameClear").addEventListener("click",undoSearch)
    document.getElementById("phoneNumClear").addEventListener("click",undoSearch)

    getAllCustomers()
})

async function undoSearch(){
    let customerTable = document.getElementById("customerTable")
    customerTable.innerHTML = ""
    document.getElementById("phoneNumSearch").value = ""
    document.getElementById("customerNameSearch").value = ""
    await getAllCustomers()
}

function clearTable(){
    let customerTable = document.getElementById("customerTable")
    customerTable.innerHTML = ""
}

async function sendCustomerDataToDB(){
    //console.log("working")
    let name = document.getElementById("fName").value
    let phoneNumber = document.getElementById("contactNo").value
    let email = document.getElementById("email").value

    if(name=="" || phoneNumber == "" || email == ""){
        alert("Fill all the fields.")
        return
    }
    let response = await sendJsonRequest("http://www.laundry-api.localhost/api/v1/customers/add",{
        "customer-name":name,
        "phone-number":phoneNumber,
        "email":email
    })

    let resJson = await response.json()

    if(resJson.statusMessage == "success"){
        await updateCusTable(name,phoneNumber,email)
    }

}

async function deleteCustomer(){
    let response = await sendJsonRequest("http://www.laundry-api.localhost/api/v1/customers/delete",{
        "customer-id":cus_id
    })

    let resJson = await response.json()

    if(resJson.statusMessage == "success"){
        let customerTable = document.getElementById("customerTable")

    for (let i = 0, row; row = customerTable.rows[i]; i++) {
        if (row.cells[0].innerText == cus_id) {
            customerTable.deleteRow(i)
        }
    }
    if (customerTable.innerHTML == ''){
        await getAllCustomers()
    }
    alert(resJson.body.message)
}else{
    alert(resJson.body.message)
    }
}

function prepareDeletion(){
    cus_id = event.target.id.split("-")[2]
}

function prepareEdit(){
    cus_id = event.target.id.split("-")[2]
}

async function updateCusToDB(){
    let newName = document.getElementById("efName").value
    let newEmail = document.getElementById("eEmail").value
    let newPhone =  document.getElementById("econtactNo").value

    if(newName=="" || newEmail == "" || newPhone == ""){
        alert("All the fields must be filled.")
        return
    }

    let response = await sendJsonRequest("http://www.laundry-api.localhost/api/v1/customers/update",{
        "customer-id":cus_id,
        "customer-name":newName,
        "phone-number":newPhone,
        "email":newEmail
    })

    let resJson = await response.json()

    if(resJson.statusMessage == "success"){
        let customerTable = document.getElementById("customerTable")

        for(let i = 0, row; row = customerTable.rows[i]; i++){
            if(row.cells[0].innerText == cus_id ){
                row.cells[1].innerText = newName
                row.cells[2].innerText = newPhone
                row.cells[3].innerText = newEmail
                row.cells[4].innerHTML = `<button class="btn btn-primary" onclick="prepareEdit()" type="button" data-bs-toggle="modal" data-bs-target="#editCustmer" id="btn-edit-${cus_id}">Edit Details</button>
              <button class="btn btn-danger" onclick="prepareDeletion()" type="button" id="btn-delete-${cus_id}" data-bs-toggle="modal" data-bs-target="#confirmDelete"">Delete</button>`
            }
        }
    }
}


async function updateCusTable(name,phone,email){
    let response = await getJsonResponse("http://www.laundry-api.localhost/api/v1/customers")

    let resJson = await response.json()

    if(resJson.statusMessage == "success"){
        let customers = resJson["body"]["customers"][0]
        let id = customers.customer_id

        let customerTable = document.getElementById("customerTable")

        let row = customerTable.insertRow(-1)

        row.insertCell(0).innerText = id
        row.insertCell(1).innerText = name
        row.insertCell(2).innerText = phone
        row.insertCell(3).innerText = email
        row.insertCell(4).innerHTML = `<button class="btn btn-primary" onclick="prepareEdit()" type="button" data-bs-toggle="modal" data-bs-target="#editCustmer" id="btn-edit-${id}">Edit Details</button>
        <button class="btn btn-danger" onclick="prepareDeletion()" type="button" id="btn-delete-${id}" data-bs-toggle="modal" data-bs-target="#confirmDelete"">Delete</button>`
    }
}

async function getAllCustomers(){
    let response = await getJsonResponse("http://www.laundry-api.localhost/api/v1/customers")

    let resJson = await response.json()

    if(resJson.statusMessage == "success"){
        let customers = resJson["body"]["customers"]

        customers.forEach(function(customer){
        let customerTable = document.getElementById("customerTable")

        let row = customerTable.insertRow(-1)

        row.insertCell(0).innerText = customer["customer_id"]
        row.insertCell(1).innerText = customer["name"]
        row.insertCell(2).innerText = customer["phone_num"]
        row.insertCell(3).innerText = customer["email"]
        row.insertCell(4).innerHTML = `<button class="btn btn-primary" onclick="prepareEdit()" type="button" data-bs-toggle="modal" data-bs-target="#editCustmer" id="btn-edit-${customer["customer_id"]}">Edit Details</button>
        <button class="btn btn-danger" onclick="prepareDeletion()" type="button" id="btn-delete-${customer["customer_id"]}" data-bs-toggle="modal" data-bs-target="#confirmDelete"">Delete</button>`
        })
    }
}

async function getAllCustomersFromSearch(){
    let nameSearch = document.getElementById("customerNameSearch").value
    let phoneNumSearch = document.getElementById("phoneNumSearch").value

    clearTable()
    if(nameSearch=="" && phoneNumSearch==""){
        await getAllCustomers()
    }else if(!nameSearch=="" && !phoneNumSearch==""){
        let response = await getAllCusReq("http://www.laundry-api.localhost/api/v1/customers?",{
            "name":nameSearch,
            "phone-number":phoneNumSearch
        })

        let resJson = await response.json()

        if(resJson.statusMessage == "success"){
            let customers = resJson["body"]["customers"]

          customers.forEach(function(customer){
      

            let customerTable = document.getElementById("customerTable")

            let row = customerTable.insertRow(-1)
    
            row.insertCell(0).innerText = customer["customer_id"]
            row.insertCell(1).innerText = customer["name"]
            row.insertCell(2).innerText = customer["phone_num"]
            row.insertCell(3).innerText = customer["email"]
            row.insertCell(4).innerHTML = `<button class="btn btn-primary" onclick="prepareEdit()" type="button" data-bs-toggle="modal" data-bs-target="#editCustmer" id="btn-edit-${customer["customer_id"]}">Edit Details</button>
            <button class="btn btn-danger" onclick="prepareDeletion()" type="button" id="btn-delete-${customer["customer_id"]}" data-bs-toggle="modal" data-bs-target="#confirmDelete"">Delete</button>`
  })
        }
    }else if(!nameSearch==""){
        let response = await getAllCusReq("http://www.laundry-api.localhost/api/v1/customers?",{
            "name":nameSearch,
        })

        let resJson = await response.json()

        if(resJson.statusMessage == "success"){
            let customers = resJson["body"]["customers"]

          customers.forEach(function(customer){
      

            let customerTable = document.getElementById("customerTable")

            let row = customerTable.insertRow(-1)
    
            row.insertCell(0).innerText = customer["customer_id"]
            row.insertCell(1).innerText = customer["name"]
            row.insertCell(2).innerText = customer["phone_num"]
            row.insertCell(3).innerText = customer["email"]
            row.insertCell(4).innerHTML = `<button class="btn btn-primary" onclick="prepareEdit()" type="button" data-bs-toggle="modal" data-bs-target="#editCustmer" id="btn-edit-${customer["customer_id"]}">Edit Details</button>
            <button class="btn btn-danger" onclick="prepareDeletion()" type="button" id="btn-delete-${customer["customer_id"]}" data-bs-toggle="modal" data-bs-target="#confirmDelete"">Delete</button>`
  })
        }
    }else{
        let response = await getAllCusReq("http://www.laundry-api.localhost/api/v1/customers?",{
            "phone-number":phoneNumSearch
        })

        let resJson = await response.json()

        if(resJson.statusMessage == "success"){
            let customers = resJson["body"]["customers"]

          customers.forEach(function(customer){
      

            let customerTable = document.getElementById("customerTable")

            let row = customerTable.insertRow(-1)
    
            row.insertCell(0).innerText = customer["customer_id"]
            row.insertCell(1).innerText = customer["name"]
            row.insertCell(2).innerText = customer["phone_num"]
            row.insertCell(3).innerText = customer["email"]
            row.insertCell(4).innerHTML = `<button class="btn btn-primary" onclick="prepareEdit()" type="button" data-bs-toggle="modal" data-bs-target="#editCustmer" id="btn-edit-${customer["customer_id"]}">Edit Details</button>
            <button class="btn btn-danger" onclick="prepareDeletion()" type="button" id="btn-delete-${customer["customer_id"]}" data-bs-toggle="modal" data-bs-target="#confirmDelete"">Delete</button>`
  })
        }
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
  
  async function getAllCusReq(url,params) {
    return await fetch(url+ new URLSearchParams(params), {
        headers: {
            'Authorization': 'Bearer ' +localStorage.getItem('authToken')
        },
        credentials: "same-origin"
    })
  }