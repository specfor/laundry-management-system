window.addEventListener("load",function(){
    document.getElementById("login").addEventListener("click",sendLoginData2Server)

})

async function sendLoginData2Server(){
    let username = document.getElementById("username")
    let password = document.getElementById("password")

    try{
        let response =await sendJsonRequest("http://www.laundry-api.localhost//api/v1/login",{
            username:username,
            password:password
        })

        console.log(response)

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