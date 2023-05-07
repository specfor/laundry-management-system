let action_id;
let item_id;

window.addEventListener("load",function(){
    document.getElementById("btnNewAddItem").addEventListener("click",sendNewProductToTheServer)
    document.getElementById("btnNewAddAction").addEventListener("click",sendActionTotheServer)
    document.getElementById("btnConfirmDeletionAction").addEventListener("click",confirmDeletionAction)
    document.getElementById("btnConfirmDeletionItem").addEventListener("click",confirmDeletionItem)
    document.getElementById("EditItemSave").addEventListener("click",updateItemsToDatabase)
    document.getElementById("nameProduct").addEventListener("keyup",getAllItemsformSearch)
    document.getElementById("clear").addEventListener("click",undoSearch)


    getAllActions()
    getAllActionsToModal()
    getAllItems()
})

async function undoSearch(){
    let allItemsTable  = document.getElementById("itemInputTable")
    allItemsTable.innerHTML = ""
    await getAllItems()
}
async function updateItemsToDatabase() {
    
    let actionArray = []
    let actionArrayDemo = []
    let itemName = document.getElementById('EditnewProduct').value
    let actions = document.querySelectorAll(".eCheck")
    let unitPrice = document.getElementById("EditunitPrice").value

    if (!itemName || !unitPrice){
        alert("Fill all required fields.")
        return
    }else{

        actions.forEach(function(action){
            if(action.checked == true){
                actionArray.push(action.value)
                actionArrayDemo.push(`${action.value}<br>`)
            }
        })

        let response = await sendJsonRequest("http://www.laundry-api.localhost/api/v1/items/update",{
            "item-id":item_id,
            "item-name":itemName,
            "item-price":[actionArray,Number(unitPrice)]
        })
        
       let updateRes = await response.json()
       console.log(updateRes)

       if(updateRes.statusMessage == "success"){
        let allItemsTable  = document.getElementById("itemInputTable")
        for(let i = 0, row; row =allItemsTable.rows[i]; i++){
            if(row.cells[0].innerText == item_id){
                row.cells[1].innerText = itemName
                row.cells[2].innerHTML= actionArrayDemo.join("")
                row.cells[3].innerText = unitPrice

            }
        }
       }
                
    }
    
}

async function getAllActionsToModal(){
    let response = await getJsonResponse("http://www.laundry-api.localhost/api/v1/category")

    let resJson = await response.json()

    if(resJson.statusMessage == "success"){
        let actions = resJson["body"]["categories"]
        actions.forEach(function(action){
            let addActionDiv =  document.getElementById("addActionDiv")
            let newAction = `<div class="form-check" id="check-btn-${action["category_id"]}">
            <input class="form-check-input check" type="checkbox" value="${action["name"]}" >
            <label class="form-check-label" for="flexCheckDefault">
                ${action["name"]}
            </label>
         </div>`
         
            addActionDiv.innerHTML += newAction

            let editActionDiv =  document.getElementById("editActionDiv")
            let editAction = `<div class="form-check" id="Echeck-btn-${action["category_id"]}">
            <input class="form-check-input eCheck" type="checkbox" value="${action["name"]}" >
            <label class="form-check-label" for="flexCheckDefault">
                ${action["name"]}
            </label>
         </div>`
         
            editActionDiv.innerHTML += editAction
        
        })
    }
}

function prepareEditItem(){
    item_id = event.target.id.split("-")[2]
}

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
       //console.log(resJson) 

    let actions = resJson["body"]["categories"].slice(-1)
    
    let id = actions[0]["category_id"]
    console.log(id)

    let actionTable = document.getElementById("actionTable")

    let row = actionTable.insertRow(-1)

     row.insertCell(0).innerHTML = id
     row.insertCell(1).innerText = action
     row.insertCell(2).innerHTML = `
     <button   class="delete btn btn-danger fw-bold" type="button" onclick="prepareDeletion()" id="btn-delete-${id}" data-bs-toggle="modal" data-bs-target="#confirmDeleteAction">Delete</button>
   </div>` 


   let addActionDiv =  document.getElementById("addActionDiv")
   let newAction = `<div class="form-check" id="check-btn-${id}">
   <input class="form-check-input check" type="checkbox" value="${action}" >
   <label class="form-check-label" for="flexCheckDefault">
       ${action}
   </label>
</div>`

   addActionDiv.innerHTML += newAction

   let editActionDiv =  document.getElementById("editActionDiv")
   let editAction = `<div class="form-check" id="Echeck-btn-${id}">
   <input class="form-check-input eCheck" type="checkbox" value="${action}" >
   <label class="form-check-label" for="flexCheckDefault">
       ${action}
   </label>
</div>`

   editActionDiv.innerHTML += editAction
    }    
}

function clearTable(){
    let allItemsTable  = document.getElementById("itemInputTable")
    allItemsTable.innerHTML = ""
}

async function getAllItemsformSearch(){
    let productName = document.getElementById("nameProduct").value
    clearTable()

    if(productName == ""){
        await getAllItems()
    }

    let response = await getAllItemsReq("http://www.laundry-api.localhost/api/v1/items?",
        {
         "item-name":productName  
        })

        let resp = await response.json()
        //console.log(resp)

        if(resp.statusMessage == "success"){
            clearTable()
            let items = resp["body"]["items"]
    
            items.forEach(function(item){
                let sampleArray = []
        
                item["categories"].forEach(function(item){
                item += "<br>"
                sampleArray.push(item)
            })

            let allItemsTable  = document.getElementById("itemInputTable")

            let row  = allItemsTable.insertRow(-1)
    
            row.insertCell(0).innerHTML = item["item_id"]
            row.insertCell(1).innerHTML = item["name"]
            row.insertCell(2).innerHTML = sampleArray.join("")
            row.insertCell(3).innerHTML =  item["price"]
            row.insertCell(4).innerHTML = `<button class="btn btn-primary" onclick="prepareEditItem()" type="button"data-bs-toggle="modal" data-bs-target="#EditaddNewProduct" id="btn-edit-${item["item_id"]}">Edit Details</button>
        <button class="btn btn-danger" type="button" id="btn-delete-${item["item_id"]}" onclick="prepareDeleteItem()" data-bs-toggle="modal" data-bs-target="#confirmDeleteItem">Delete</button>`
            })
        }
        
}


function deleteAction(){
    document.getElementById(`check-btn-${action_id}`).remove()
    document.getElementById(`Echeck-btn-${action_id}`).remove()
}

async function confirmDeletionAction(){
    //console.log(action_id)
    let response = await sendJsonRequest("http://www.laundry-api.localhost/api/v1/category/delete",{
        "category-id":action_id
    })
    let resJson = await response.json()


     if(resJson.statusMessage == "success"){
        

        let actionTable = document.getElementById("actionTable")

        for (let i = 0, row; row = actionTable.rows[i]; i++) {
            if (row.cells[0].innerText == action_id) {
                actionTable.deleteRow(i)
                deleteAction()
                
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
    
}

async function sendNewProductToTheServer(){
    console.log("fine")
    let itemName = document.getElementById("newProduct").value 
    let actions = document.querySelectorAll(".check")
    let unitP = document.getElementById("unitPrice").value

    arrayOne = []
    let arrayTwo = []
    
    if(itemName == "" || unitP == ""){
        alert("All the required fields must be filled!")
    }else{
        actions.forEach(function(action){
            if(action.checked == true){
                arrayOne.push(action.value)
                arrayTwo.push(`${action.value}<br>`)
            }
        })

        let response = await sendJsonRequest("http://www.laundry-api.localhost/api/v1/items/add",{
            "item-name":itemName,
            "item-price":[arrayOne,Number(unitP)]
        })
         
        let resJson = await response.json()
        console.log(resJson)
        if(resJson.statusMessage == "success"){
            await updateAllItemsTable(itemName,arrayTwo,unitP)
        }

        
    }

}

async function updateAllItemsTable(itemName,action,unitPrice){

    let response = await getJsonResponse("http://www.laundry-api.localhost/api/v1/items")
    
    let resJson = await response.json()

    if(resJson.statusMessage =="success"){
        let items = resJson["body"]["items"].slice(-1)
    
        let itemID = items[0]["item_id"]

        let allItemsTable  = document.getElementById("itemInputTable")

        let row  = allItemsTable.insertRow(-1)
    
        row.insertCell(0).innerHTML = itemID
        row.insertCell(1).innerHTML = itemName
        row.insertCell(2).innerHTML = action.join("")
        row.insertCell(3).innerHTML = unitPrice
        row.insertCell(4).innerHTML = `<button class="btn btn-primary" onclick="prepareEditItem()" type="button" data-bs-toggle="modal" data-bs-target="#EditaddNewProduct" id="btn-edit-${itemID}">Edit Details</button>
        <button class="btn btn-danger" type="button" id="btn-delete-${itemID}" onclick="prepareDeleteItem()" data-bs-toggle="modal" data-bs-target="#confirmDeleteItem">Delete</button>`
    }


}

async function getAllItems(){

    let response = await getJsonResponse("http://www.laundry-api.localhost/api/v1/items")

    let resJson = await response.json()

    if(resJson.statusMessage == "success"){
        let items = resJson["body"]["items"]
        
        items.forEach(function(item){
        let sampleArray = []
        
        item["categories"].forEach(function(item){
            item += "<br>"
            sampleArray.push(item)
        })

        let allItemsTable  = document.getElementById("itemInputTable")

        let row  = allItemsTable.insertRow(-1)
    
        row.insertCell(0).innerHTML = item["item_id"]
        row.insertCell(1).innerHTML = item["name"]
        row.insertCell(2).innerHTML = sampleArray.join("")
        row.insertCell(3).innerHTML =  item["price"]
        row.insertCell(4).innerHTML = `<button class="btn btn-primary" onclick="prepareEditItem()" type="button"data-bs-toggle="modal" data-bs-target="#EditaddNewProduct" id="btn-edit-${item["item_id"]}">Edit Details</button>
        <button class="btn btn-danger" type="button" id="btn-delete-${item["item_id"]}" onclick="prepareDeleteItem()" data-bs-toggle="modal" data-bs-target="#confirmDeleteItem">Delete</button>`
        })
    }else{
        alert("Something went wrong! Try again.")
    }
}
 function prepareDeleteItem(){
    item_id = event.target.id.split("-")[2]
 }

 async function confirmDeletionItem(){
    //console.log(action_id)
    let response = await sendJsonRequest("http://www.laundry-api.localhost/api/v1/items/delete",{
        "item-id":item_id
    })
    let resJson = await response.json()


     if(resJson.statusMessage == "success"){
        

        let allItemsTable  = document.getElementById("itemInputTable")

        for (let i = 0, row; row = allItemsTable.rows[i]; i++) {
            if (row.cells[0].innerText == item_id) {
                allItemsTable.deleteRow(i)
      
            }

                
        }
        if (allItemsTable.innerHTML == ''){
            await getAllItems()
        }
        alert(resJson.body.message)
    }else{
        alert(resJson.body.message)
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

async function getAllItemsReq(url,params) {
    return await fetch(url+ new URLSearchParams(params), {
        headers: {
            'Authorization': 'Bearer ' +localStorage.getItem('authToken')
        },
        credentials: "same-origin"
    })
}
