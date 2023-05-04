let emp_id; 

window.addEventListener("load", function() {
  document.getElementById("btnAddEmp").addEventListener("click", checkData)
  document.getElementById("btnConfirmDeletion").addEventListener("click", confirmDeletion)
  document.getElementById("btnEditEmp").addEventListener("click",updateEmpToDatabase)
  document.getElementById("empNameSearch").addEventListener("keyup",getAllEmpesFromSearch)
  document.getElementById("phoneNumSearch").addEventListener("keyup",getAllEmpesFromSearch)
  document.getElementById("empNameClear").addEventListener("click",getAllEmployees)
  document.getElementById("phoneNumClear").addEventListener("click",getAllEmployees)

  getAllEmployees()
})



async function checkData() {
  let fName = document.getElementById("fName").value
  let contactNo = document.getElementById("contactNo").value
  let address = document.getElementById("address").value
  let dateJoined = document.getElementById("dateJoined").value

  if (fName == "" || contactNo == "" || dateJoined == "") {
    alert("All the required fields must be filled")
  } else {
    let response = await sendJsonRequest("http://www.laundry-api.localhost/api/v1/employees/add",{
        "employee-name":fName,
        "address":address,
        "phone-number":contactNo,
        "join-date":dateJoined
    })
    let resJson = await response.json()

    if(resJson.statusMessage == "success"){
      await updateTheTable(fName,contactNo, address, dateJoined)
    }

    
  }
}

async function updateTheTable(fName, contactNo, address, dateJoined) {

  let response = await getJsonResponse("http://www.laundry-api.localhost/api/v1/employees")

  let resJson = await response.json()
  let emp = resJson["body"]["employees"].slice(-1)
  console.log(resJson)
  let id = emp[0]['employee_id']

  if(resJson.statusMessage == "success"){
    let tableEmployee = document.getElementById("empTable")

    let row = tableEmployee.insertRow(-1)
  
    row.insertCell(0).innerHTML = id
    row.insertCell(1).innerHTML = fName
    row.insertCell(2).innerHTML = contactNo
    row.insertCell(3).innerHTML = address
    row.insertCell(4).innerHTML = dateJoined
    row.insertCell(5).innerHTML = `<button class="btn btn-primary" onclick="prepareEdit()" type="button" data-bs-toggle="modal" data-bs-target="#editEmployee" id="btn-edit-${id}">Edit Details</button>
                  <button class="btn btn-danger" onclick="prepareDeletion()" type="button" id="btn-delete-${id}" data-bs-toggle="modal" data-bs-target="#confirmDelete"">Delete</button>`
  }
}

function prepareEdit(){
  emp_id = event.target.id.split("-")[2]
}

async function updateEmpToDatabase() {
  let firstName = document.getElementById('efName').value
  let contactNo= document.getElementById('econtactNo').value
  let address = document.getElementById('eaddress').value
  let dateJoined = document.getElementById('edateJoined').value


  if (firstName=="" || contactNo=="" || address=="" || dateJoined==""){
      alert("Fill all required fields.")
      return
  }else{
      let response = await sendJsonRequest("http://www.laundry-api.localhost/api/v1/employees/update",{
        
        "employee-name":firstName,
        "employee-id":emp_id,
        "address":address,
        "phone-number":contactNo,
        "join-date":dateJoined
      })
     let updateRes = await response.json()
     if(updateRes.statusMessage == "success"){
      let tableEmployee = document.getElementById("empTable")

      for(let i = 0, row; row = tableEmployee.rows[i]; i++){
          if(row.cells[0].innerText == emp_id){
              row.cells[1].innerText = fname
              row.cells[2].innerText = contactNo
              row.cells[3].innerText = address
              row.cells[4].innerText = dateJoined
              row.cells[5].innerText = `<button class="btn btn-primary" onclick="prepareEdit()" type="button" data-bs-toggle="modal" data-bs-target="#editEmployee" id="btn-edit-${emp_id}">Edit Details</button>
              <button class="btn btn-danger" onclick="prepareDeletion()" type="button" id="btn-delete-${emp_id}" data-bs-toggle="modal" data-bs-target="#confirmDelete"">Delete</button>`
          }
      }
     }
              
  }
  
}

function clearTable(){
  let tableEmployee = document.getElementById("empTable")

  tableEmployee.innerHTML = ""

}


async function getAllEmpesFromSearch(){
  let name = document.getElementById("empNameSearch").value
  let phoneNumber = document.getElementById("phoneNumSearch").value
  
  clearTable()
  if(name=="" && phoneNumber==""){
      await getAllEmployees()
  }else if(!name=="" && !phoneNumber==""){
      let response = await getAllEmpsReq("http://www.laundry-api.localhost/api/v1/employees?",
      {
          name:name,
          "phone-number":phoneNumber
      })

      let resp = await response.json()
      
      if(resp.statusMessage == "success"){
          let employees = resp["body"]["employees"]

          employees.forEach(function(employee){
      

          let tableEmployee = document.getElementById("empTable")
          let row = tableEmployee.insertRow(-1)
  
      row.insertCell(0).innerHTML = employee["employee_id"]
      row.insertCell(1).innerHTML = employee["name"]
      row.insertCell(2).innerHTML = employee["phone_num"]
      row.insertCell(3).innerHTML = employee["address"]
      row.insertCell(4).innerHTML = employee["join_date"]
      row.insertCell(5).innerHTML = `<button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#editEmployee" id="btn-edit-${employee["employee_id"]}">Edit Details</button>
      <button class="btn btn-danger" onclick="prepareDeletion()" type="button" id="btn-delete-${employee["employee_id"]}" data-bs-toggle="modal" data-bs-target="#confirmDelete"">Delete</button>`
  })

      }
    
         
          }else if(!phoneNumber==""){
              
              let response = await getAllEmpsReq("http://www.laundry-api.localhost/api/v1/employees?",
      {
        "phone-number":phoneNumber
      })
      let resp = await response.json()            

      if(resp.statusMessage == "success"){
        let employees = resp["body"]["employees"]

        employees.forEach(function(employee){
            
      
          let tableEmployee = document.getElementById("empTable")
            let row = tableEmployee.insertRow(-1)
        
            row.insertCell(0).innerHTML = employee["employee_id"]
            row.insertCell(1).innerHTML = employee["name"]
            row.insertCell(2).innerHTML = employee["phone_num"]
            row.insertCell(3).innerHTML = employee["address"]
            row.insertCell(4).innerHTML = employee["join_date"]
            row.insertCell(5).innerHTML = `<button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#editEmployee" id="btn-edit-${employee["employee_id"]}">Edit Details</button>
            <button class="btn btn-danger" onclick="prepareDeletion()" type="button" id="btn-delete-${employee["employee_id"]}" data-bs-toggle="modal" data-bs-target="#confirmDelete"">Delete</button>`
          })
                 
      }
      
          }else{ 
              let response = await getAllEmpsReq("http://www.laundry-api.localhost/api/v1/employees?",
      {
          name:name,

      })
      let resp = await response.json()
      
      //console.lof(resp)
      if(resp.statusMessage == "success"){
        let employees = resp["body"]["employees"]

        employees.forEach(function(employee){
            
      
          let tableEmployee = document.getElementById("empTable")
            let row = tableEmployee.insertRow(-1)
        
            row.insertCell(0).innerHTML = employee["employee_id"]
            row.insertCell(1).innerHTML = employee["name"]
            row.insertCell(2).innerHTML = employee["phone_num"]
            row.insertCell(3).innerHTML = employee["address"]
            row.insertCell(4).innerHTML = employee["join_date"]
            row.insertCell(5).innerHTML = `<button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#editEmployee" id="btn-edit-${employee["employee_id"]}">Edit Details</button>
            <button class="btn btn-danger" onclick="prepareDeletion()" type="button" id="btn-delete-${employee["employee_id"]}" data-bs-toggle="modal" data-bs-target="#confirmDelete"">Delete</button>`
          })
                 
      }
          }
      
  }
  

async function confirmDeletion(){
  let response = await sendJsonRequest("http://www.laundry-api.localhost/api/v1/employees/delete",{
    "employee-id":emp_id
})
let resJson = await response.json()


 if(resJson.statusMessage == "success"){

  let tableEmployee = document.getElementById("empTable")

    for (let i = 0, row; row = tableEmployee.rows[i]; i++) {
        if (row.cells[0].innerText == emp_id) {
            tableEmployee.deleteRow(i)
        }
    }
    if (tableEmployee.innerHTML == ''){
        await getAllEmployees()
    }
    alert(resJson.body.message)
}else{
    alert(resJson.body.message)
}
}

function prepareDeletion(){
    emp_id = event.target.id.split("-")[2]

}

async function getAllEmployees(){
  let response = await getJsonResponse("http://www.laundry-api.localhost/api/v1/employees")
    

    let resp = await response.json()
    if(resp.statusMessage=="success"){
        let employees = resp["body"]["employees"]

    employees.forEach(function(employee){
      let tableEmployee = document.getElementById("empTable")

      let row = tableEmployee.insertRow(-1)
    
      row.insertCell(0).innerHTML = employee["employee_id"]
      row.insertCell(1).innerHTML = employee["name"]
      row.insertCell(2).innerHTML = employee["phone_num"]
      row.insertCell(3).innerHTML = employee["address"]
      row.insertCell(4).innerHTML = employee["join_date"]
      row.insertCell(5).innerHTML = `<button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#editEmployee" id="btn-edit-${employee["employee_id"]}">Edit Details</button>
      <button class="btn btn-danger" onclick="prepareDeletion()" type="button" id="btn-delete-${employee["employee_id"]}" data-bs-toggle="modal" data-bs-target="#confirmDelete"">Delete</button>`
    })
    }else(
        alert("Something went wrong.Try again.")
    )
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

async function getAllEmpsReq(url,params) {
  return await fetch(url+ new URLSearchParams(params), {
      headers: {
          'Authorization': 'Bearer ' +localStorage.getItem('authToken')
      },
      credentials: "same-origin"
  })
}