window.addEventListener("load", function () {
    document.getElementById("login").addEventListener("click", sendLoginData2Server)

})

async function sendLoginData2Server() {
    let username = document.getElementById("username").value
    let password = document.getElementById("password").value

    let loginButton = document.getElementById('login')
    loginButton.disabled = true
    loginButton.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"' +
        'fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" ' +
        'stroke-width="4"></circle><path class="opacity-75" fill="currentColor" ' +
        'd="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014' +
        ' 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Signing In'
    loginButton.classList.add('transition', 'ease-in-out', 'duration-150', 'cursor-not-allowed')

    try {
        let response = await sendJsonRequest("http://www.laundry-api.localhost/api/v1/login", {
            username: username,
            password: password
        })

        if (response.status === 200) {
            let data = await response.json()
            if (data.statusMessage === 'success') {
                localStorage.setItem('authToken', data.body.token)
                document.cookie = "auth-token=" + data.body.token
                window.location.replace("./dashboard")
                return
            } else if (data.statusMessage === 'error') {
                popUpError('Login Error', data.body.message)
            } else {
                popUpError('Login Error', 'Unknown error occurred.')
            }
        }
    } catch (err) {
        popUpError('Login Error', 'Unknown error occurred.')
    } finally {
        loginButton.innerHTML = "Sign in"
        loginButton.classList.remove('transition', 'ease-in-out', 'duration-150', 'cursor-not-allowed')
        loginButton.disabled = false
    }
}

function popUpError(topic, message) {
    let div = document.createElement('div')
    div.classList.add('bg-orange-100', 'border-l-4', 'border-orange-500', 'text-orange-700', 'p-4', 'fixed', 'top-0',
        'left-0', 'right-0', 'm-2')
    div.role = 'alert'
    div.innerHTML = `<div class="relative"><p class="font-bold">${topic}</p><p>${message}</p><button class="absolute
     top-0 right-0 font-bold" onclick="this.parentElement.parentElement.remove()">X</button></div>`
    document.body.appendChild(div)
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