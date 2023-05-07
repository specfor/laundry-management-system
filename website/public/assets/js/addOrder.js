
window.addEventListener("load",function(){
    loadAllItems()
    getAllActions()
})

async function loadAllItems(){
    let response = await getJsonResponse("http://www.laundry-api.localhost/api/v1/items")
    let resJson = await response.json()

    if(resJson.statusMessage == "success"){
     let items = resJson["body"]["items"]
     //console.log(items)
     items.forEach(function(item){
        let itemSelect = document.getElementById("itemId")
        let newItem =  `<option>${item.name}</option>`

        itemSelect.innerHTML += newItem
     })
    }
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