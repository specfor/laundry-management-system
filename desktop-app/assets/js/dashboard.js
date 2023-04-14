let activeTab = 'home-view'

window.addEventListener("load", function () {

    //document.getElementById("dataSubmit").addEventListener("click",autoFill)
    document.getElementById("continue").addEventListener("click", sendDataToTheServer)
    document.getElementById("btnAddItem").addEventListener("click", addItemTotheTable)
    document.getElementById("goBack").addEventListener("click", goBackOrder)

    // Event listeners for main menu buttons
    document.getElementById("btn-section-home").addEventListener("click",
        () => {
            setActiveTab('home-view')
        })
    document.getElementById("btn-section-place-order-1").addEventListener("click",
        () => {
            setActiveTab('place-order-1')
        })
    document.getElementById("btn-section-maintain-view").addEventListener("click",
        () => {
            setActiveTab('maintain-view')
        })
    document.getElementById("btn-section-profile").addEventListener("click",
        () => {
            setActiveTab('profile')
        })
})

function setActiveTab(tabName) {
    if (tabName === activeTab)
        return
    let activeSection = document.getElementById('section-' + activeTab)
    let section = document.getElementById('section-' + tabName)
    section.classList.remove('d-none')
    activeSection.classList.add('d-none')
    activeTab = tabName
}

setInterval(function () {
    const date = new Date()

    let hours = date.getHours()
    let minutes = date.getMinutes()

    let year = date.getFullYear()
    let month = date.getMonth()
    let day = date.getDate()

    //let dayW = date.getDay()

    document.getElementById("timeNow").innerText = `${hours}:${minutes} |`

    document.getElementById("dateNow").innerText = `${year}-${month+1}-${day}`
}, 1000)

function goBackOrder() {
    document.getElementById("section-place-order-2").classList.add("d-none")
    document.getElementById("section-place-order-1").classList.remove("d-none")

}


function addItemTotheTable() {

    let array1 = []

    let itemTable = document.getElementById("itemBody")

    let item = document.getElementById("itemId").value
    let quantity = document.getElementById("quantity").value
    let priority = document.getElementById("priorityId").value
    let action = document.getElementById("actionId").value
    let deliveryDate = document.getElementById("deliveryDate").value

    document.querySelectorAll("#check").forEach(function (i) {
        if (i.checked == true) {
            array1.push(`${i.value}<br>`)
        }
    })

    if (item == "" || priority == "" || action == "" || quantity == "") {

        alert("All the required fields must be filled.")

    } else {
        let row = itemTable.insertRow(-1)

        row.insertCell(0).innerHTML = item
        row.insertCell(1).innerHTML = quantity
        row.insertCell(2).innerHTML = priority
        row.insertCell(3).innerHTML = action
        row.insertCell(4).innerHTML = array1.join("")
        row.insertCell(5).innerHTML = deliveryDate
    }


}

function sendDataToTheServer() {
    let orderPlace = document.getElementById("section-place-order-1")

    let name = document.getElementById("name").value
    let contactNum = document.getElementById("contactNum").value
    let quantity = document.getElementById("quantity").value
    let address = document.getElementById("address").value
    let itemTableLen = document.getElementById("itemBody").rows.length
    let itemTable = document.getElementById("itemBody")
    let deliveryDate = document.getElementById("deliveryDate").value

    let clientOrder;

    if (itemTableLen == 0) {
        alert("Items must be added to continue")
    } else if (name == "" || contactNum == "") {
        alert("All the fields must be filled.")
    } else {

        clientOrder = [
            {
                "name": name,
                "contactNumber": contactNum,
                "address": address
            }
        ]

        let a = 0
        while (a < itemTableLen) {
            let item = itemTable.rows[a].cells[0].innerText
            let amount = itemTable.rows[a].cells[1].innerText
            let priority = itemTable.rows[a].cells[2].innerText
            let action = itemTable.rows[a].cells[3].innerText
            let defects = itemTable.rows[a].cells[4].innerText

            let obj = {
                "item": item,
                "amount": amount,
                "priority": priority,
                "action": action,
                "defects": defects,
                "deliveryDate": deliveryDate,
            }

            clientOrder.push(obj)

            a++

        }
        //console.log(clientOrder)

        ipcRenderer.send("clientOrderDetails", clientOrder)
    }


    ipcRenderer.on("done", function (data) {
        console.log("working fine")
        orderPlace.classList.add('d-none')
        document.getElementById("section-place-order-2").classList.remove('d-none')
        checkout(clientOrder)
    
        
    })


}


function checkout(clientOrder) {
    document.getElementById("cusName").value = clientOrder[0].name
    document.getElementById("contactInfo").value = clientOrder[0].contactNumber

    document.getElementById("delDate").value = document.getElementById("deliveryDate").value

    const date = new Date()


    let year = date.getFullYear()
    let month = date.getMonth()
    let day = date.getDate()

    document.getElementById("Orderdate").value = `${year}-${month+1}-${day}`


}