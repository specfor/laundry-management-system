//function to send login data info to the main 
function login(){
    const loginEmail = document.getElementById('emailInput').value
    const loginPassword = document.getElementById('passwordInput').value

    //Email valid check syntax
    let emailValid = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;

    if(loginEmail.match(emailValid)){
        //sending user input data to the main process
        ipcRenderer.send("emailAndPassword",{
            loginEmail:loginEmail,
            loginPassword:loginPassword
        })
    }else{
        //success alert
        Toastify.alertToast({
            text:"Enter a valid Email!",
            duration:5000,
            style:{
            background:"red",
            color:"white",
            }
        })

    }

}