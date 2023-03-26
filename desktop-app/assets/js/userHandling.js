
//function to send login data info to the main 
function login() {
    const loginUsername = document.getElementById('userNameInput').value
    const loginPassword = document.getElementById('passwordInput').value


    //sending user input data to the main process
    ipcRenderer.send("usernameAndPassword", {
        username: loginUsername,
        password: loginPassword
    })
}

window.addEventListener('load', () => {
    let buttonLogin = document.getElementById('btn-login')
    buttonLogin.addEventListener('click', (event) => {
        event.preventDefault()
        login()
    })
})

ipcRenderer.on("error",function(event){
    Toastify.alertToast({
        text: "Email or password is incorrect!",
        duration:5000,
        className: "info",
        style: {
            background: "red",
            color:"white",
  }
    })
  })
  
ipcRenderer.on("success",function(event){
    Toastify.alertToast({
        text: "Login successfull!",
        className: "info",
        style: {
            background: "green",
            color:"white"
  }
    })
  })