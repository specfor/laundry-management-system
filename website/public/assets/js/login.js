window.addEventListener("load",function(){
    document.getElementById("login").addEventListener("click",sendLoginData2Server)

})

async function sendLoginData2Server(){
    let username = document.getElementById("username").value
    let password = document.getElementById("password").value

    try{
        let response =await sendJsonRequest("http://www.laundry-api.localhost/api/v1/login",{
            username:username,
            password:password
        })

        let data = await response.json()
        console.log(data);
        if (data.statusMessage === 'success'){
            localStorage.setItem('authToken',data.body.token)
            document.cookie = "authToken="+data.body.token
        }

    }catch(err){

    }
    

}

async function sendJsonRequest(url, jsonBody) {
    return await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(jsonBody),
        credentials: "same-origin"
    })
}