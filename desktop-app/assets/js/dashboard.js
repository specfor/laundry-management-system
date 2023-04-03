

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
   let urgency = document.getElementById("urgency")
   let from = document.getElementById("from").value
   let to = document.getElementById("to").value

    console.log(urgency) 

    //checks whether fields are enpty or not
    if(firstName=="" || lastName=="" || email=="" || mobileNumber=="" || address=="" || 
    from=="" ||to==""){
        
        Toastify.alertToast({
            text: "All the Fields must be Filled!",
            duration:5000,
            className: "info",
            style: {
                background: "red",
                color:"white",
      }
        })
    }else{
              
        ipcRenderer.send("clientData",{
            "first-name":firstName,
            "last-name":lastName,
            "email":email,
            "mobile-number":mobileNumber,
            "address":address,
            "urgent":urgency.checked,
            "took-date":from,
            "should-give":to,
        })
    }

}