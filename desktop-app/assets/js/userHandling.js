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


//function to send data to the server
function sendLoginDataToTheServer(data){
    superagent
  .post('http://localhost/')
  .send({ name: 'Manny', species: 'cat' }) // sends a JSON post body
  .set('X-API-Key', 'foobar')
  .set('accept', 'json')
  .end((err, res) => {
    // Calling the end function will send the request
  });
}