let branch_id;

window.addEventListener("load",function(){
    document.getElementById("btnAddBranch").addEventListener("click",sendDataToDB)
    document.getElementById("btnConfirmDeletion").addEventListener("click",deleteBranch)
    document.getElementById("editBranch").addEventListener("click",updateBranchInfo)
    document.getElementById("branchNameSearch").addEventListener("keyup",getBranchesFromSearch)
    document.getElementById("PhoneNum").addEventListener("keyup",getBranchesFromSearch)
    getAllBranches()
})


async function sendDataToDB(){

   let branchName = document.getElementById("branchName").value
   let contactInfo = document.getElementById("contactInfo").value

    if(!branchName || !contactInfo){
        alert("All the fields must be filled!")
        return
    }
    console.log()
        try{
            let response = await sendJsonRequest("http://www.laundry-api.localhost/api/v1/branches/add",{
                "branch-name":branchName,
                "phone-number":contactInfo
            })

            let resJson = await response.json()
            console.log(resJson)
            if(resJson.statusMessage == "success"){
               await updateDataToTable(branchName,contactInfo)
            }
        }catch(err){

        }
   
}

async function updateDataToTable(branchName,contactInfo){

    let response = await getJsonResponse("http://www.laundry-api.localhost/api/v1/branches")
    
    let resp = await response.json()

    let branch = resp["body"]["branches"].slice(-1)
    let id = branch[0]["branch_id"]

    let branchesTable = document.getElementById("branchesTable")

    let newRow = branchesTable.insertRow(-1)

    newRow.insertCell(0).innerText = id
    newRow.insertCell(1).innerText = branchName
    newRow.insertCell(2).innerText = contactInfo
    newRow.insertCell(3).innerHTML = `<button class="btn btn-primary" onclick="editBranch()" type="button"data-bs-toggle="modal" data-bs-target="#EditBranch" id="btn-edit-${id}">Edit Details</button>
    <button class="btn btn-danger" type="button" id="btn-dele-${id}" onclick="prepareDeletion()" type="button"data-bs-toggle="modal" data-bs-target="#confirmDelete">Delete</button>`
}

function prepareDeletion(){
    branch_id = event.target.id.split('-')[2]

}

function editBranch(){
    branch_id = event.target.id.split('-')[2]
}

async function deleteBranch(){

    let response = await sendJsonRequest("http://www.laundry-api.localhost/api/v1/branches/delete",{
        "branch-id":branch_id
    })
    let resJson = await response.json()


     if(resJson.statusMessage == "success"){

        let branchesTable = document.getElementById("branchesTable")

        for (let i = 0, row; row = branchesTable.rows[i]; i++) {
            if (row.cells[0].innerText == branch_id) {
               branchesTable.deleteRow(i)
            }
        }
        if (branchesTable.innerHTML == ''){
            await getAllBranches()
        }
        alert(resJson.body.message)
    }else{
        alert(resJson.body.message)
    }
}

async function updateBranchInfo(){
    let branchName = document.getElementById("editBranchName").value
    let contactInfo = document.getElementById("editContactInfo").value 

    if(branchName=="" || contactInfo==""){
        alert("All the fields must be filled")
    }else{
        try{
            let response = await sendJsonRequest("http://www.laundry-api.localhost/api/v1/branches/update",{
                "branch-id":branch_id,
                "branch-name":branchName,
                "phone-number":contactInfo
            })
        
            let resJson = await response.json()

            if(resJson.statusMessage == "success"){
                let branchesTable = document.getElementById("branchesTable")
                for(let i = 0, row; row = branchesTable.rows[i]; i++){
                    if(row.cells[0].innerText == branch_id){
                        row.cells[1].innerText = branchName
                        row.cells[2].innerText = contactInfo
                    }
                }
            }

        }catch(err){
            
        }

    }
}


async function getAllBranches(){
    let response = await getJsonResponse("http://www.laundry-api.localhost/api/v1/branches")

    let resJson = await response.json()

    if(resJson.statusMessage == "success"){
        let branches = resJson["body"]["branches"]

        branches.forEach(function(branch){
            let branchesTable = document.getElementById("branchesTable")

            let newRow = branchesTable.insertRow(-1)

            newRow.insertCell(0).innerText = branch["branch_id"]
            newRow.insertCell(1).innerText = branch["name"] 
            newRow.insertCell(2).innerText = branch["phone_num"]
            newRow.insertCell(3).innerHTML = `<button class="btn btn-primary" onclick="editBranch()" type="button"data-bs-toggle="modal" data-bs-target="#EditBranch" id="btn-edit-${branch["branch_id"]}">Edit Details</button>
            <button class="btn btn-danger" type="button" id="btn-dele-${branch["branch_id"]}" onclick="prepareDeletion()" type="button"data-bs-toggle="modal" data-bs-target="#confirmDelete">Delete</button>`
        })
    }else{
        alert("Something went wrong.Try again.")
    }

}


function clearTable(){
    let branchesTable = document.getElementById("branchesTable")
    branchesTable.innerHTML = ""
}

async function getBranchesFromSearch(){
    let phoneNum = document.getElementById("PhoneNum").value
    let branchName = document.getElementById("branchNameSearch").value
    
    clearTable()
    if(branchName=="" && phoneNum==""){
        await getAllBranches()
    }else if(!branchName=="" && !phoneNum==""){
        let response = await getAllBranchesReq("http://www.laundry-api.localhost/api/v1/branches?",
        {
            "branch-name":branchName,
            "phone-number":phoneNum
        })

        let resp = await response.json()
        
        if(resp.statusMessage == "success"){
            let branches = resp["body"]["branches"]
            console.log(branches)

   branches.forEach(function(branch){
        

    let branchesTable = document.getElementById("branchesTable")

    let newRow = branchesTable.insertRow(-1)

    newRow.insertCell(0).innerText = branch["branch_id"]
    newRow.insertCell(1).innerText = branch["name"] 
    newRow.insertCell(2).innerText = branch["phone_num"]
    newRow.insertCell(3).innerHTML = `<button class="btn btn-primary" onclick="editBranch()" type="button"data-bs-toggle="modal" data-bs-target="#EditBranch" id="btn-edit-${branch["branch_id"]}">Edit Details</button>
    <button class="btn btn-danger" type="button" id="btn-dele-${branch["branch_id"]}" onclick="prepareDeletion()" type="button"data-bs-toggle="modal" data-bs-target="#confirmDelete">Delete</button>`
    })

        }
      
           
            }else if(!branchName==""){
                
                let response = await getAllBranchesReq("http://www.laundry-api.localhost/api/v1/branches?",
                {
                    "branch-name":branchName
              
                })
        let resp = await response.json()            

        if(resp.statusMessage == "success"){
            let branches = resp["body"]["branches"]
            console.log(branches)
            branches.forEach(function(branch){
        

            let branchesTable = document.getElementById("branchesTable")

            let newRow = branchesTable.insertRow(-1)

            newRow.insertCell(0).innerText = branch["branch_id"]
            newRow.insertCell(1).innerText = branch["name"] 
            newRow.insertCell(2).innerText = branch["phone_num"]
            newRow.insertCell(3).innerHTML = `<button class="btn btn-primary" onclick="editBranch()" type="button"data-bs-toggle="modal" data-bs-target="#EditBranch" id="btn-edit-${branch["branch_id"]}">Edit Details</button>
            <button class="btn btn-danger" type="button" id="btn-dele-${branch["branch_id"]}" onclick="prepareDeletion()" type="button"data-bs-toggle="modal" data-bs-target="#confirmDelete">Delete</button>`
            })
                   
        }
        
            }else{ 
                let response = await getAllBranchesReq("http://www.laundry-api.localhost/api/v1/branches?",
        {
            "phone-number":phoneNum
        })
        let resp = await response.json()
        
        if(resp.statusMessage == "success"){
            let branches = resp["body"]["branches"]
            console.log(branches)
            branches.forEach(function(branch){
        

            let branchesTable = document.getElementById("branchesTable")

            let newRow = branchesTable.insertRow(-1)

            newRow.insertCell(0).innerText = branch["branch_id"]
            newRow.insertCell(1).innerText = branch["name"] 
            newRow.insertCell(2).innerText = branch["phone_num"]
            newRow.insertCell(3).innerHTML = `<button class="btn btn-primary" onclick="editBranch()" type="button"data-bs-toggle="modal" data-bs-target="#EditBranch" id="btn-edit-${branch["branch_id"]}">Edit Details</button>
            <button class="btn btn-danger" type="button" id="btn-dele-${branch["branch_id"]}" onclick="prepareDeletion()" type="button"data-bs-toggle="modal" data-bs-target="#confirmDelete">Delete</button>`
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

async function getAllBranchesReq(url,params) {
    return await fetch(url+ new URLSearchParams(params), {
        headers: {
            'Authorization': 'Bearer ' +localStorage.getItem('authToken')
        },
        credentials: "same-origin"
    })
}