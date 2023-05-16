window.addEventListener("load",function(){
    getAllOrders()
})

async function getAllOrders(){
    let response = await getJsonResponse("http://www.laundry-api.localhost/api/v1/orders")
    let resJson = await response.json()

    console.log(resJson)

    let orders = resJson["body"]["orders"]

    for(order of orders){
        let ordersTable = document.getElementById("allOrdersTable")

        let row = ordersTable.insertRow(-1)
        
        let cellOne = row.insertCell(0)   
        cellOne.innerText = order["order_id"]

        let cellTwo = row.insertCell(1)
        cellTwo.innerText = await getCustomerName(Number(order["customer_id"]))

        row.insertCell(2).innerText = order["added_date"]

        let itemsArray = await readingArrays(order["items"])
        row.insertCell(3).innerHTML =  itemsArray.join("")

        let amountArray = readingArraysForAmount(order["items"])
        row.insertCell(4).innerHTML = amountArray.join("")

        let actionsArary = await readingArraysForActions(order["items"])
        row.insertCell(5).innerHTML = actionsArary.join("")

        let returnDateArray = readingArrayForDelivery(order["items"])
        row.insertCell(6).innerHTML = returnDateArray.join("")

        row.insertCell(7).innerText =   `Rs: ${order["total_price"]}`

        row.insertCell(8).innerHTML = `<button class="btn btn-primary" onclick="prepareEdit()" id="btn-edit-${order["order_id"]}">Edit</button>
        <button class="btn btn-danger" onclick="prepareDeletion()" id='btn-delete-${order["order_id"]}'>Delete</button>`

    }
}

function readingArrayForDelivery(itemArray){
    let array1 = []
    for(item of itemArray){
        array1.push(`<span >${item["return-date"]}</span><br>`)  
     }
 
     return array1
}

function readingArraysForAmount(itemArray){

    let array1 = []
    for(item of itemArray){
        array1.push(`<h5 class="h5">${item["amount"]}</h5>`)  
     }
 
     return array1
    
}

async function readingArraysForActions(itemArray){
    
    let array1 = []
    for(item of itemArray){
        let itemActions = await getItemsWithActions(Number(item["item_id"]))

        array1.push(`${itemActions[1]}<br>`)
    } 
    return array1
}

async function readingArrays(itemArray){
    
    let array1 = []
    for(item of itemArray){
       let itemName = await getItemsWithActions(Number(item["item_id"]))

       array1.push(`<h5 class="h5">${itemName[0]}</h5>`)  
    }

    return array1
}

async function getItemsWithActions(itemId){
    let response = await getAllCustomersReq("http://www.laundry-api.localhost/api/v1/items?",{
        "item-id":itemId
    })
    let resJson = await response.json()

    if(resJson.statusMessage == "success"){
        let item = resJson["body"]["items"][0]
        return [item["name"],item["categories"]]
    }
}

async function getCustomerName(customerId){
    let response = await getAllCustomersReq("http://www.laundry-api.localhost/api/v1/customers?",{
        "customer-id":customerId
    })

    let resJson = await response.json()

    if(resJson.statusMessage == "success"){
        let customer = resJson["body"]["customers"][0]
        
        return customer["name"]
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

async function getAllCustomersReq(url,params) {
    return await fetch(url+ new URLSearchParams(params), {
        headers: {
            'Authorization': 'Bearer ' +localStorage.getItem('authToken')
        },
        credentials: "same-origin"
    })
}