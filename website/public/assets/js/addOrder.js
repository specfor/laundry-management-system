

let allOrder = []
let eachOrderReq = []

window.addEventListener("load",function(){
    document.getElementById("btnAddItem").addEventListener("click",makingSendReq)

    loadAllItems()
    getAllActions()
})

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

   
    //console.log(defectsArray,actionArray)
    let response = await getAllCusReq("http://www.laundry-api.localhost/api/v1/items?",{
        "item-name":itemName
    })
    let resJson = await response.json()

    if(resJson.statusMessage == "success"){
        let items = resJson["body"]["items"]

            items.forEach(function(item){
            let categoryArray = item.categories
            if(arrayEquals(categoryArray,actionArray)){
                let eachOrder = {
                    "id":item["item_id"],
                    "name":item["name"],
                    "amount":quantity,
                    "defects":defectsArray,
                    "delivery-Date":deliveryDate
                }
              allOrder.push(eachOrder)                  
            }
            
        })

  
    }
    
}


async function sendOrderRequest(array){
    array.forEach(function(order){
        
    })
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