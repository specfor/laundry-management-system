let action_id;

window.addEventListener("load",function(){
    document.getElementById("btnNewAddItem").addEventListener("click",sendNewProductToTheServer)
    document.getElementById("btnNewAddAction").addEventListener("click",sendActionTotheServer)
    document.getElementById("btnConfirmDeletionAction").addEventListener("click",confirmDeletionAction)

    getAllActions()
})



async function sendActionTotheServer(){
    let action = document.getElementById("newAction").value

    if(action==""){
        alert("Fil required fields!")
    }

    let response = await sendJsonRequest("http://www.laundry-api.localhost/api/v1/category/add",{
        "category-name":action,
    })

    let resJson = await response.json()

    if(resJson.statusMessage == "success"){
        await updateActionTable(action)
    }

}


async function updateActionTable(action){

    let response = await getJsonResponse("http://www.laundry-api.localhost/api/v1/category")

    let resJson = await response.json()

    if(resJson.statusMessage == "success"){
       console.log(resJson) 

    let actions = resJson["body"]["categories"].slice(-1)
    
    let id = actions[0]["category_id"]

    let actionTable = document.getElementById("actionTable")

    let row = actionTable.insertRow(-1)

     row.insertCell(0).innerHTML = id
     row.insertCell(1).innerText = action
     row.insertCell(2).innerHTML = `
     <button  class="delete btn btn-danger fw-bold" type="button" id="btn-delete-${id}" data-bs-toggle="modal" data-bs-target="#confirmDeleteAction">Delete</button>
   </div>` 

    }    
}

async function confirmDeletionAction(){
    let response = await sendJsonRequest("http://www.laundry-api.localhost/api/v1/category/delete",{
        "category-id":action_id
    })
    let resJson = await response.json()


     if(resJson.statusMessage == "success"){

        let actionTable = document.getElementById("actionTable")

        for (let i = 0, row; row = actionTable.rows[i]; i++) {
            if (row.cells[0].innerText == action_id) {
                actionTable.deleteRow(i)
            }
        }
        if (actionTable.innerHTML == ''){
            await getAllActions()
        }
        alert(resJson.body.message)
    }else{
        alert(resJson.body.message)
    }
}

async function getAllActions(){
    let response = await getJsonResponse("http://www.laundry-api.localhost/api/v1/category")
    

    let resp = await response.json()
    if(resp.statusMessage=="success"){
        let categories = resp["body"]["categories"]

    categories.forEach(function(category){
        

        let actionTable = document.getElementById("actionTable")

        let newRow = actionTable.insertRow(-1)
    
        newRow.insertCell(0).innerText = category["category_id"]
        newRow.insertCell(1).innerText = category["name"]
        newRow.insertCell(2).innerHTML = `
        <button  class="delete btn btn-danger fw-bold" onclick="prepareDeletion()" type="button" id="btn-delete-${category["category_id"]}" data-bs-toggle="modal" data-bs-target="#confirmDeleteAction">Delete</button>
      </div>`
    
    })
    }else(
        alert("Something went wrong.Try again.")
    )
}

function prepareDeletion(){
    action_id = event.target.id.split("-")[2]
    console.log(action_id)
}

async function sendNewProductToTheServer(){
    let itemName = document.getElementById("newProduct").value 
    let action = document.getElementById("newActionId").value
    let unitP = document.getElementById("unitPrice").value

    if(itemName == "" || action == "" || unitP == ""){
        alert("All the required fields must be filled!")
    }else{
        let response = await sendJsonRequest("")

        updateAllItemsTable(itemName,action,unitP)
    }

}

function updateAllItemsTable(itemName,action,unitPrice){
    let allItemsTable  = document.getElementById("itemInputTable")

    let row  = allItemsTable.insertRow(-1)

    row.insertCell(0).innerHTML = "1"
    row.insertCell(1).innerHTML = itemName
    row.insertCell(2).innerHTML = action
    row.insertCell(3).innerHTML = unitPrice
    row.insertCell(4).innerHTML = `<button class="btn btn-primary" type="button"data-bs-toggle="modal" data-bs-target="#EditaddNewProduct">Edit Details</button>
    <button class="btn btn-danger" type="button">Delete</button>`
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

async function getAllUsersReq(url,params) {
    return await fetch(url+ new URLSearchParams(params), {
        headers: {
            'Authorization': 'Bearer ' +localStorage.getItem('authToken')
        },
        credentials: "same-origin"
    })
}
