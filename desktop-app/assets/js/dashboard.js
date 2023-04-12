
window.addEventListener("load",function(){
    
    //document.getElementById("dataSubmit").addEventListener("click",autoFill)
    document.getElementById("continue").addEventListener("click",sendDataToTheServer)
    document.getElementById("btnAddItem").addEventListener("click",addItemTotheTable)
    document.getElementById("goBack").addEventListener("click",goBackOrder)


})

setInterval(function(){
    const date = new Date()
    
    let hours = date.getHours()
    let minutes = date.getMinutes()

    let year = date.getFullYear()
    let month = date.getMonth()
    let day = date.getDate()

    //let dayW = date.getDay()

    document.getElementById("timeNow").innerText = `${hours}:${minutes} |`

    document.getElementById("dateNow").innerText = `${year}-${month}-${day}`
},1000)

function goBackOrder(){
    document.getElementById("summaryDiv").style.display = "none" 
    document.getElementById("orderPlaceDiv").style.display = "block"

}


function addItemTotheTable(){
    let itemTable = document.getElementById("itemBody")

    let item = document.getElementById("itemId").value
    let quantity = document.getElementById("quantity").value
    let priority = document.getElementById("priorityId").value
    let action = document.getElementById("actionId").value

    if(item=="" || priority=="" || action =="" || quantity==""){
        
        alert("All the fields must be filled.")
            
    }else{
        let row = itemTable.insertRow(-1)

        row.insertCell(0).innerHTML = item
        row.insertCell(1).innerHTML = quantity
        row.insertCell(2).innerHTML = priority
        row.insertCell(3).innerHTML = action
    }
    


}

function sendDataToTheServer(){
    let orderPlace = document.getElementById("orderPlaceDiv")
   
    let name = document.getElementById("name").value
   let contactNum= document.getElementById("contactNum").value
   let quantity= document.getElementById("quantity").value
   let address = document.getElementById("address").value
   let itemTableLen = document.getElementById("itemBody").rows.length
   let itemTable = document.getElementById("itemBody")


    let clientOrder;

   if(itemTableLen==0){
      alert("Items must be added to continue")  
    }else if(name=="" || contactNum==""){
        alert("All the fields must be filled.")
    }else{
        
         clientOrder = [
            {
                "name":name,
                "contactNumber":contactNum,
                "address":address
            }
           ]
        
        let a = 0
        while(a < itemTableLen){
            let item = itemTable.rows[a].cells[0].innerText
            let amount = itemTable.rows[a].cells[1].innerText
            let priority = itemTable.rows[a].cells[2].innerText
            let action = itemTable.rows[a].cells[3].innerText

            let obj = {
                "item":item,
                "amount":amount,
                "priority":priority,
                "action":action
            }

            clientOrder.push(obj)
            
            a++

        }
        //console.log(clientOrder)

        ipcRenderer.send("clientOrderDetails",clientOrder)
    }
        

    ipcRenderer.on("done",function(data){
        console.log("working fine")
        orderPlace.style.display = "none"
        document.getElementById("summaryDiv").style.display = "block"
        checkout(clientOrder)
    })

    
}


function checkout(clientOrder){
    document.getElementById("cusName").value = clientOrder[0].name
    document.getElementById("contactInfo").value = clientOrder[0].contactNumber

    const date = new Date()


    let year = date.getFullYear()
    let month = date.getMonth()
    let day = date.getDate()

    document.getElementById("Orderdate").value = `${year}-${month}-${day}`
    
}