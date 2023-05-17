window.addEventListener("load",function(){
    document.getElementById("orderIdInput").addEventListener("keyup",getAllPaymentsFromSearch)
    document.getElementById("clear").addEventListener("click",resetSearch)
    getAllPayments()
})

async function resetSearch(){
    document.getElementById("orderIdInput").value = ""
    let paymentsTable = document.getElementById("paymentsTable")
    paymentsTable.innerHTML = ""
    await getAllPayments()
}

function clearTable(){
    let paymentsTable = document.getElementById("paymentsTable")
    paymentsTable.innerHTML = ""
}

async function getAllPayments(){

    let response = await getJsonResponse("http://www.laundry-api.localhost/api/v1/payments")

    let resJson = await response.json()

    if(resJson.statusMessage == "success"){
        let paymentsTable = document.getElementById("paymentsTable")
        let payments = resJson["body"]["payments"]

        for(payment of payments){
            let row = paymentsTable.insertRow(-1)

            row.insertCell(0).innerText = payment["payment_id"]
            row.insertCell(1).innerText = payment["order_id"]
            row.insertCell(2).innerText = `Rs: ${payment["paid_amount"]}`
            row.insertCell(3).innerText = payment["paid_date"]
        }

    }
}

async function getAllPaymentsFromSearch(){
    
    let orderId = document.getElementById("orderIdInput").value

    if(orderId==""){
        await getAllPayments()
        return
    }
    clearTable()
    let response = await getAllUsersReq("http://www.laundry-api.localhost/api/v1/payments?",{
        "order-id":Number(orderId)
    })

    let resJson = await response.json()

    if(resJson.statusMessage == "success"){
        let payments = resJson["body"]["payments"]
        let paymentsTable = document.getElementById("paymentsTable")

        for(payment of payments){
            let row = paymentsTable.insertRow(-1)

            row.insertCell(0).innerText = payment["payment_id"]
            row.insertCell(1).innerText = payment["order_id"]
            row.insertCell(2).innerText = `Rs: ${payment["paid_amount"]}`
            row.insertCell(3).innerText = payment["paid_date"]
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

async function getAllUsersReq(url,params) {
    return await fetch(url+ new URLSearchParams(params), {
        headers: {
            'Authorization': 'Bearer ' +localStorage.getItem('authToken')
        },
        credentials: "same-origin"
    })
}
