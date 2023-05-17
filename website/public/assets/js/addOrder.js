

let allOrder = []
let eachOrderReq = []
let del_id = 1


window.addEventListener("load",function(){
    document.getElementById("btnAddItem").addEventListener("click",makingSendReq)
    document.getElementById("paymentProceed").addEventListener("click",paymentPage)
    document.getElementById("confirmCheckout").addEventListener("click",checkCheckout)
    document.getElementById("addPayment").addEventListener("click",addFinalPayment)

    loadAllItems()
    getAllActions()
})

function paymentPage(){
    if(allOrder.length==0){
        alert("Add at least 1 order to continue.")
        return
    }
    document.getElementById("addOrderDiv").classList.add("d-none")
    document.getElementById("checkoutDiv").classList.remove("d-none")

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

   
    let response = await getAllCusReq("http://www.laundry-api.localhost/api/v1/items?",{
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


//updating data into the table
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

    
    let orderTable = document.getElementById("addOrderTable")

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

function prepareDeletion(){
    SampleId = event.target.id.split("-")[2]

    let orderTable = document.getElementById("addOrderTable")
    for(let i = 0, row; row = orderTable.rows[i]; i++){
        if(row.cells[0].innerText==SampleId){
            orderTable.deleteRow(i)
            deleteFromArray(SampleId)
        }
    }
    
}

function deleteFromArray(id){
    for(item of allOrder){
        if(item.rowId==id){
            allOrder.splice(allOrder.indexOf(item),1)
        }
    }
}

//This function get all customers
async function getAllCustomers(){

    let customerArray = []

    let response = await getJsonResponse("http://www.laundry-api.localhost/api/v1/customers")

    let resJson = await response.json()

    if(resJson.statusMessage == "success"){
        let customers = resJson["body"]["customers"]

        for(customer of customers){
            customerArray.push(customer["name"])
        }

        return customerArray
    }
}

//Check checkout inputs
async function checkCheckout(){
    let customerName = document.getElementById("autoComplete").value
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

    if(customerName==""){
        alert("Enter the customer name.")
        return
    }
    
    if(autoCalculate.checked == true){
        let cus = await getCustomer(customerName)

        if(cus[0] == true){
            await addOrderTotheDBautoP(cus[1])
            return
        }
        let addCus =await addCustomer(customerName)

        if(addCus[0] == true){
            await addOrderTotheDBautoP(addCus[1])
            return
        }
        
    }
    if(customPrice.checked == true){
        let cus = await getCustomer(customerName)
    
        if(cus[0] == true){
            await addOrderTotheDBcustomP(cus[1],cusPriceInput)
            return

        }
        let addCus =await addCustomer(customerName)
        if(addCus[0] == true){
            await addOrderTotheDBcustomP(addCus[1],cusPriceInput)
            return
        }
        
    }
}

//Add order to the db
async function addOrderTotheDBautoP(customerId){

    let eachData = {}

    for(eachOrder of allOrder){

        eachData[eachOrder.id] = {
            "amount":Number(eachOrder["quantity"]),
            "return-date":eachOrder["deliveryDate"],
            "defects":eachOrder["defects"]
        }

    }

    let response = await sendJsonRequest("http://www.laundry-api.localhost/api/v1/orders/add",{
        "items":eachData,
        "customer-id":customerId,        
    })

    let resJson = await response.json()

    if(resJson.statusMessage == "success"){
        alert("Order added successfully.")
    
        let response = await getJsonResponse("http://www.laundry-api.localhost/api/v1/orders")

        let resJson = await response.json()

        if(resJson.statusMessage == "success"){
            document.getElementById("methodDiv").classList.remove("d-none")

            let lastOrderArray = resJson["body"]["orders"].splice(-1)
            let lastOrder = lastOrderArray[0]

            document.getElementById("totalPrice").innerText = `Rs:${lastOrder["total_price"]}`

        }
    }


}

async function addOrderTotheDBcustomP(customerId,price){

    let eachData = {}

    for(eachOrder of allOrder){

        eachData[eachOrder.id] = {
            "amount":Number(eachOrder["quantity"]),
            "return-date":eachOrder["deliveryDate"],
            "defects":eachOrder["defects"]
        }

    }


    let response = await sendJsonRequest("http://www.laundry-api.localhost/api/v1/orders/add",{
        "items":eachData,
        "customer-id":customerId,
        "total-price":price
    })

    let resJson = await response.json()

    if(resJson.statusMessage == "success"){
        alert("Order added successfully.")
        let response = await getJsonResponse("http://www.laundry-api.localhost/api/v1/orders")

        let resJson = await response.json()

        if(resJson.statusMessage == "success"){
            document.getElementById("methodDiv").classList.remove("d-none")

            let lastOrderArray = resJson["body"]["orders"].splice(-1)
            let lastOrder = lastOrderArray[0]

            document.getElementById("totalPrice").innerText = `Rs:${lastOrder["total_price"]}`

        }
        
    }


}

//Add customer
async function addCustomer(customerName){
    let response = await sendJsonRequest("http://www.laundry-api.localhost/api/v1/customers/add",{
        "customer-name":customerName
    })

    let resJson = await response.json()

    if(resJson.statusMessage == "success"){
        let x = await getCustomer(customerName)
        return x
    }
}

//Get customer id 
async function getCustomer(customerName){
    

    let response = await getJsonResponse("http://www.laundry-api.localhost/api/v1/customers")

    let resJson = await response.json()

    if(resJson.statusMessage == "success"){
        let customers = resJson["body"]["customers"]

        for(customer of customers){

            if(customer["name"] == customerName){
                return [true,customer["customer_id"]]
            }
        }
        return [false]
    }
}

function arrayEquals(a, b) {
    return Array.isArray(a) &&
      Array.isArray(b) &&
      a.length === b.length &&
      a.every((val, index) => val === b[index]);
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

//Add final payment
async function addFinalPayment(){
    let response = await getJsonResponse("http://www.laundry-api.localhost/api/v1/orders")

    let resJson = await response.json()

    if(resJson.statusMessage == "success"){
        let lastOrderArray = resJson["body"]["orders"].splice(-1)
        let lastOrder = lastOrderArray[0]

        let responseNew = await sendJsonRequest("http://www.laundry-api.localhost/api/v1/payments/add",{
                "order-id":lastOrder["order_id"],
                "paid-amount":lastOrder["total_price"],
                "paid-date":new Date().toISOString().slice(0, 10)
        })

        let resJsonNew = await responseNew.json()

        if(resJsonNew.statusMessage == "success"){
            alert("Payment added successfully/")
            location.reload()
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

  