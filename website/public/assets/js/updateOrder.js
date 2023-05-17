
let prevOrder = []
let del;
let allOrder = []
let del_id = 1

window.addEventListener("load",function(){
    document.getElementById("saveChanges").addEventListener("click",makingSendReq)
    document.getElementById("paymentProceed").addEventListener("click",paymentPage)
    document.getElementById("confirmCheckout").addEventListener("click",checkCheckout)
    document.getElementById("addPayment").addEventListener("click",loadOrders)
    
    getOrder()
    getAllActions()
    loadAllItems()
    
})

function loadOrders(){
    location.replace("../orders")
}

function paymentPage(){
    if(prevOrder.length==0 && allOrder.length==0){
        alert("There should bea at least 1 item to continue.")
        return
    }
    document.getElementById("addOrderDiv").classList.add("d-none")
    document.getElementById("checkoutDiv").classList.remove("d-none")

}


async function getOrder(){
    
    if(localStorage.getItem("order_id") == ""){
        return
    }

    let response = await getAllCustomersReq("http://www.laundry-api.localhost/api/v1/orders?",{
        "order-id":localStorage.getItem("order_id")
    })
    let resJson = await response.json()

    if(resJson.statusMessage == "success"){
        let ordersArray = resJson["body"]["orders"][0]["items"]
        prevOrder= ordersArray

        for(item of ordersArray){
            let updateTable = document.getElementById("updateOrderTable")

            let row = updateTable.insertRow(-1)

            row.insertCell(0).innerText = item["item_id"]
            row.insertCell(1).innerText = item["item_name"]
            row.insertCell(2).innerText = item["amount"]
            row.insertCell(3).innerText = await getActions(item["item_id"])
            row.insertCell(4).innerHTML =  item["defects"]
            row.insertCell(5).innerText = item["return-date"]
            row.insertCell(6).innerHTML = `<button class="btn btn-danger" onclick="deleteItem()" id="btn-delete-${item["item_id"]}">Delete</button>`
        }
    }
}

function clearInputs(){
    document.getElementById("quantity").value = ""
    document.getElementById("deliveryDate").value = ""
    let actions = document.querySelectorAll(".check")
    let defects = document.querySelectorAll("#check")

    actions.forEach(function(action){
        if(action.checked == true){
            action.checked = false
        }
    })

    defects.forEach(function(defect){
        if(defect.checked == true){
            defect.checked = false
        }
    })
}
function arrayEquals(a, b) {
    return Array.isArray(a) &&
      Array.isArray(b) &&
      a.length === b.length &&
      a.every((val, index) => val === b[index]);
  }

async function makingSendReq(){
    
    let defectsArray = []
    let actionArray = []

    let itemName = document.getElementById("itemId").value 
    let quantity =  document.getElementById("quantity").value
    let actions = document.querySelectorAll(".check")
    let defects = document.querySelectorAll("#check")
    let deliveryDate = document.getElementById("deliveryDate").value

    if(itemName=="" || quantity == "" || deliveryDate==""){
        return
    }

    

    actions.forEach(function(action){
        if(action.checked == true){
           actionArray.push(action.value)
        }
    })

    defects.forEach(function(defect){
        if(defect.checked==true){
            defectsArray.push(defect.value)
        }
    })

   
    let response = await getAllCustomersReq("http://www.laundry-api.localhost/api/v1/items?",{
        "item-name":itemName
    })
    let resJson = await response.json()

    if(resJson.statusMessage == "success"){
        let items = resJson["body"]["items"]

            items.forEach(function(item){
            
            let categoryArray = item.categories
            if(arrayEquals(categoryArray,actionArray)){
                let eachOrder = {}
                eachOrder.rowId = del_id
                eachOrder.id = item["item_id"]
                eachOrder.name = item["name"]
                eachOrder.quantity = quantity
                eachOrder.actions = actionArray
                eachOrder.defects = defectsArray
                eachOrder.deliveryDate = deliveryDate
              
                allOrder.push(eachOrder)
                clearInputs()
                updateDataIntoTable(allOrder)
                            
            }
            
        })                     
    }
    
}

function deleteFromArray(id){
    console.log("working")
    for(item of allOrder){
        if(item.rowId==id){
            allOrder.splice(allOrder.indexOf(item),1)
        }
    }
}

function prepareDeletion(){
    SampleId = event.target.id.split("-")[2]

    let orderTable = document.getElementById("updateOrderTable")
    for(let i = 0, row; row = orderTable.rows[i]; i++){
        if(row.cells[0].innerText==SampleId){
            orderTable.deleteRow(i)
            for(item of allOrder){
                if(item.rowId==SampleId){
                    allOrder.splice(allOrder.indexOf(item),1)
                }
            }
        }
    }
    
}



function updateDataIntoTable(y){
    if(y.length==0){
        return
    }
    let rowD= y.slice(-1)
    let rowData = rowD[0]
    
    let arrayOne = []
    let arrayTwo = []
    
    let defects = rowData["defects"]

    for(defect of defects){
        arrayOne.push(`${defect}<br>`)
    }

    let actions = rowData["actions"]
    for(action of actions){
        arrayTwo.push(`${action}<br>`)
    }

    
    let orderTable = document.getElementById("updateOrderTable")

    let row = orderTable.insertRow(-1)

    row.insertCell(0).innerText = del_id
    row.insertCell(1).innerText = rowData["name"]
    row.insertCell(2).innerText = rowData["quantity"]
    row.insertCell(3).innerHTML = arrayTwo.join("")
    row.insertCell(4).innerHTML = arrayOne.join("")
    row.insertCell(5).innerText = rowData["deliveryDate"]
    row.insertCell(6).innerHTML = `<button class="btn btn-danger" onclick="prepareDeletion()" id="btn-del-${del_id}">Delete</button>`

    del_id++
}


async function getAllActions(){
    let response = await getJsonResponse("http://www.laundry-api.localhost/api/v1/category")
    let resJson = await response.json()

    if(resJson.statusMessage == "success"){
        let actions = resJson["body"]["categories"]
        actions.forEach(function(action){
            let actionSelect = document.getElementById("actionsAll")
            let newAction = `<div class="form-check">
            <input class="form-check-input check" type="checkbox" value="${action.name}" id="check-btn-${action.category_id}}">
            <label class="form-check-label" for="flexCheckDefault">
                ${action.name}
            </label>
         </div>`

            actionSelect.innerHTML += newAction
        })
    }
}

async function checkCheckout(){
    let autoCalculate = document.getElementById("autoCal")
    let customPrice = document.getElementById("customP")
    let cusPriceInput =  document.getElementById("customPrice").value


    if(autoCalculate.checked == true && customPrice.checked == true){
        alert("Please select one price option only.")
        return
    }
    if(autoCalculate.checked == false && customPrice.checked == false){
        alert("Please select a price option.")
        return
    }

    if(autoCalculate.checked == true && !cusPriceInput==""){
        alert("Select custom price option to enter a custom price")
        return
    }

    if(customPrice.checked == true && cusPriceInput==""){
        alert("Please enter a custom price to continue")
        return
    }

    if(autoCalculate.checked == true){
        await addOrderTotheDBautoP()
        
        return
    }

    if(customPrice.checked == true){

        await addOrderTotheDBcustomP(Number(cusPriceInput))

        return
    }
}

async function addOrderTotheDBautoP(){

    let eachData = {}

    if(!prevOrder.length == 0){
        for(each of prevOrder){

            eachData[each.item_id] = {
                "amount":each["amount"],
                "return-date":each["return-date"],
                "defects":each["defects"]
            }
        }
    }


    if(!allOrder.length == 0){
        for(eachOrder of allOrder){

            if(typeof eachData[eachOrder.id] !== "undefined"){
                eachData[eachOrder.id]["amount"] += Number(eachOrder["quantity"])
            }else{
                eachData[eachOrder.id] = {
                    "amount":Number(eachOrder["quantity"]),
                    "return-date":eachOrder["deliveryDate"],
                    "defects":eachOrder["defects"]
                }
            }
    
        }
    }


    

   
    let response = await sendJsonRequest("http://www.laundry-api.localhost/api/v1/orders/update",{
        "order-id":localStorage.getItem("order_id"),
        "items":eachData     
    })

    let resJson = await response.json()

    if(resJson.statusMessage == "success"){
        alert("Order updated successfully.")
    
        let response = await getAllCustomersReq("http://www.laundry-api.localhost/api/v1/orders?",{
            "order-id":localStorage.getItem("order_id")
        })

        let resJson = await response.json()

        if(resJson.statusMessage == "success"){
            //console.log(resJson)
            document.getElementById("methodDiv").classList.remove("d-none")

            let lastOrderArray = resJson["body"]["orders"]
            let lastOrder = lastOrderArray[0]

            await addFinalPayment(lastOrder["total_price"])
        }
    }

}

async function addOrderTotheDBcustomP(customAmount){

    let eachData = {}

    if(!prevOrder.length == 0){
        for(each of prevOrder){

            eachData[each.item_id] = {
                "amount":each["amount"],
                "return-date":each["return-date"],
                "defects":each["defects"]
            }
        }
    }


    if(!allOrder.length == 0){
        for(eachOrder of allOrder){

            if(typeof eachData[eachOrder.id] !== "undefined"){
                eachData[eachOrder.id]["amount"] += Number(eachOrder["quantity"])
            }else{
                eachData[eachOrder.id] = {
                    "amount":Number(eachOrder["quantity"]),
                    "return-date":eachOrder["deliveryDate"],
                    "defects":eachOrder["defects"]
                }
            }
    
        }
    }
    
    

    let response = await sendJsonRequest("http://www.laundry-api.localhost/api/v1/orders/update",{
        "order-id":localStorage.getItem("order_id"),
        "items":eachData,
        "total-price":customAmount     
    })

    let resJson = await response.json()

    if(resJson.statusMessage == "success"){
        alert("Order updated successfully.")
    
        let response = await getAllCustomersReq("http://www.laundry-api.localhost/api/v1/orders?",{
            "order-id":localStorage.getItem("order_id")
        })

        let resJson = await response.json()

        if(resJson.statusMessage == "success"){
            document.getElementById("methodDiv").classList.remove("d-none")

            let lastOrderArray = resJson["body"]["orders"]
            let lastOrder = lastOrderArray[0]
            
            await addFinalPayment(lastOrder["total_price"])

        }
    }


}



async function loadAllItems(){
    let duplicateArray = []
    let response = await getJsonResponse("http://www.laundry-api.localhost/api/v1/items")
    let resJson = await response.json()

    if(resJson.statusMessage == "success"){
     let items = resJson["body"]["items"]
     
     items.forEach(function(item){
        duplicateArray.push(item.name)
       
     })

     let filterArray =  duplicateArray.filter((item,
        index) => duplicateArray.indexOf(item) === index);


      filterArray.forEach(function(item){
        let itemSelect = document.getElementById("itemId")
        let newItem =  `<option>${item}</option>`

        itemSelect.innerHTML += newItem
      })  
    }
}

async function addFinalPayment(lastPrice){
    let response = await sendJsonRequest("http://www.laundry-api.localhost/api/v1/payments/add",{
        "order-id":localStorage.getItem("order_id"),
        "paid-amount":lastPrice,
        "paid-date":new Date().toISOString().slice(0, 10)
    })

    let resJson = await response.json()

    if(resJson.statusMessage == "error"){
        document.getElementById("totalPrice").innerText = resJson.body.message
        
    }
}

async function deleteItem(){
    del = event.target.id.split("-")[2]

    let updateTable = document.getElementById("updateOrderTable")
    for(let i = 0, row; row = updateTable.rows[i]; i++){
        if(row.cells[0].innerText==del){
            updateTable.deleteRow(i)
            deleteFromArray(del)
            //console.log(prevOrder)
        }
    }
   
}

function deleteFromArray(id){
    for(item of prevOrder){
        if(item["item_id"]==id){
            prevOrder.splice(prevOrder.indexOf(item),1)
        }
    }
}

async function getActions(itemId){
    let response = await getAllCustomersReq("http://www.laundry-api.localhost/api/v1/items?",{
        "item-id":itemId
    })

    let resJson = await response.json()

    if(resJson.statusMessage == "success"){
        let item = resJson["body"]["items"][0]["categories"]

        return item
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