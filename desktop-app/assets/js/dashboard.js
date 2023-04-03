
window.addEventListener("load",function(){
    
    //document.getElementById("dataSubmit").addEventListener("click",autoFill)
    document.getElementById("continue").addEventListener("click",sendDataToTheServer)

})


function sendDataToTheServer(){
   let firstName = document.getElementById("fname").value
   let lastName = document.getElementById("lname").value
   let email = document.getElementById("email").value
   let mobileNumber = document.getElementById("mobiNum").value
   let address = document.getElementById("address").value
   let urgency = document.getElementById("urgency").value
   let from = document.getElementById("from").value
   let to = document.getElementById("to").value

   console.log(`${firstName} ${lastName} ${email} ${mobileNumber} ${address} ${urgency} ${from} ${to}`)
}