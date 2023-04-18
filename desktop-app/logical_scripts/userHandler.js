const superagent = require('superagent').agent()

//This function send user Input email and password to the server. Returns token if login is successful.
//Returns false if login is failed.
async function getAuthToken(data) {
    try {
        console.log(data)
        data['dev'] = true;
        let data2 = await superagent
            .post('http://localhost/api/v1/login')
            .send(data);
        let resp = JSON.parse(data2.text)
        if (resp['statusMessage'] === 'success'){
            return resp['body']['token']
        }
        return false
    } catch (error) {
        console.log(error)
        return false
    }
}

async function getAuthToken2(data) {
    try {
        console.log(data)
        data['dev'] = true;
        let data2 = await superagent
            .post('http://localhost/api/v1/customers/add')
            .send(data);
        let resp = JSON.parse(data2.text)
        if (resp['statusMessage'] === 'success'){
            return resp['body']['token']
        }
        return false
    } catch (error) {
        console.log(error)
        return false
    }
}

module.exports = {getAuthToken,getAuthToken2}