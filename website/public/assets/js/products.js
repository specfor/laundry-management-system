window.addEventListener("load",function(){
    document.getElementById("btnNewAddItem").addEventListener("click",sendNewProductToTheServer)
})

function sendNewProductToTheServer(){
    let itemName = document.getElementById("newProduct").value 
    let action = document.getElementById("newActionId").value
    let unitP = document.getElementById("unitPrice").value

    if(itemName == "" || action == "" || unitP == ""){
        alert("All the required fields must be filled!")
    }else{
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