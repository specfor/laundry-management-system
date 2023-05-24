export async function sendJsonPostRequest(url, jsonBody, headers = []) {
    headers['Content-Type'] = 'application/json'
    return await fetch(url, {
        method: 'POST',
        headers: headers,
        body: JSON.stringify(jsonBody),
        credentials: "same-origin"
    })
}

export async function sendGetRequest(url, params, headers) {

    return await fetch(url, {
        method: 'GET',
        headers: headers,
        credentials: "same-origin"
    })
}
