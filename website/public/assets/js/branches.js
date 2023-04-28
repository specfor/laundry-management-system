let branch_id;

window.addEventListener("load",function(){
    document.getElementById("addBranch").addEventListener("click",sendDataToDB)
    document.getElementById("btnConfirmDeletion").addEventListener("click",deleteBranch)
    document.getElementById("editBranch").addEventListener("click",updateBranchInfo)
})


async function sendDataToDB(){

   let branchName = document.getElementById("branchName").value
   let contactInfo = document.getElementById("contactInfo").value

    if(branchName=="" || contactInfo==""){
        alert("All the fields must be filled!")
    }else{
        try{
            let response = await sendJsonRequest("")
        }catch(err){

        }
    }
}

async function updateDataToTable(branchName,contactInfo){
    let response = await getJsonResponse("http://www.laundry-api.localhost/api/v1/branches")
    
    let resp = await response.json()
    //let user = resp["body"]["users"].slice(-1)
    //let id = user[0]["id"]

    let branchesTable = document.getElementById("branchesTable")

    let newRow = branchesTable.insertRow(-1)

    newRow.insertCell(0).innerText = id
    newRow.insertCell(1).innerText = branchName
    newRow.insertCell(2).innerText = contactInfo
    newRow.insertCell(3).innerText = `<button class="btn btn-primary" onclick="editBranch()" type="button"data-bs-toggle="modal" data-bs-target="#EditBranch" id="btn-edit-${id}">Edit Details</button>
    <button class="btn btn-danger" type="button" id="btn-dele-${id}" onclick="prepareDeletion()" type="button"data-bs-toggle="modal" data-bs-target="#confirmDelete">Delete</button>`
}

function prepareDeletion(){
    branch_id = event.target.id.split('-')[2]
}

function editBranch(){
    branch_id = event.target.id.split('-')[2]
}

async function updateBranchInfo(){
    let branchName = document.getElementById("editBranchName").value
    let contactInfo = document.getElementById("editContactInfo").value 

    if(branchName=="" || contactInfo==""){
        alert("All the fields must be filled")
    }else{
        try{
            let response = await sendJsonRequest("http://www.laundry-api.localhost/api/v1/branches/update")
        
            let resJson = await response.json()

            if(resJson.statusMessage == "success"){
                
            }

        }catch(err){
            
        }

    }
}


async function deleteBranch(){
    let response = await sendJsonRequest("http://www.laundry-api.localhost/api/v1/branches/delete")

    let resJson = await response.json()

    if(resJson.statusMessage == "success"){
        alert("Branch deleted successfully")
        
    }
}

async function getAllBranches(){
    let response = await getJsonResponse("http://www.laundry-api.localhost/api/v1/branches/delete")

    let resJson = await response.json()

    if(resJson.statusMessage == "success"){

    }else{
        alert("Something went wrong.Try again.")
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