window.addEventListener("load",function(){
    document.getElementById("btnNewAddItem").addEventListener("click",sendNewProductToTheServer)
    document.getElementById("btnNewAddAction").addEventListener("click",sendActionTotheServer)
})

async function sendActionTotheServer(){
    let action = document.getElementById("newAction").value

    if(action==""){
        alert("Fil required fields!")
    }


}


async function updateActionTable(action){
    let actionTable = document.getElementById("actionTable")

    let row = actionTable.insertRow(-1)

     row.insertCell[0].innerText = action
     row.insertCell[1].innerHTML = `<div class="input-group mb-3">
     <button  class="edit btn btn-primary fw-bold" type="button" id="btn-edit-" data-bs-toggle="modal" data-bs-target="#editNewAction">Edit</button>
     <button  class="delete btn btn-danger fw-bold" type="button" id="btn-delete-" data-bs-toggle="modal" data-bs-target="#confirmDelete">Delete</button>
   </div>` 
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
