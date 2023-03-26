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

