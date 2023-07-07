export async function sendJsonPostRequest(url, jsonBody, headers = window.httpHeaders, jsonifyResponse = true) {
    headers['Content-Type'] = 'application/json'

    let response = null
    try {
        response = await fetch(url, {
            method: 'POST',
            headers: headers,
            body: JSON.stringify(jsonBody),
            credentials: "same-origin"
        })
    } catch (e) {
        return {status: 'error', message: 'Failed to connect to the servers.', data: null}
    }

    if (response.status !== 200)
        return {status: 'error', message: 'Something went wrong.', data: null}

    let data_
    if (jsonifyResponse) {
        data_ = await response.json()
        return {status: data_.statusMessage, data: data_.body, message: data_.body.message ?? null}
    } else {
        data_ = await response.text()
        return {status: 'success', data: data_}
    }
}

export async function sendGetRequest(url, params = {}, headers = window.httpHeaders, jsonifyResponse = true) {
    let response = null
    try {
        if (params)
            url = url + "?" + new URLSearchParams(params)
        response = await fetch(url, {
            method: 'GET',
            headers: headers,
            credentials: "same-origin"
        })
    } catch (e) {
        return {status: 'error', message: 'Failed to connect to the servers.', data: null}
    }

    if (response.status !== 200)
        return {status: 'error', message: 'Something went wrong.', data: null}

    let data_
    if (jsonifyResponse) {
        data_ = await response.json()
        return {status: data_.statusMessage, data: data_.body, message: data_.body.message ?? null}
    } else {
        data_ = await response.text()
        return {status: 'success', data: data_}
    }
}
