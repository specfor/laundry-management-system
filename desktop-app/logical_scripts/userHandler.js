const superagent = require('superagent').agent()

//This function send user Iput email and password to the server
async function sendLoginDataToTheServer(data) {
    try {
        console.log(data)
        data['dev'] = true;
        let data2 = await superagent
            .post('http://localhost/api/v1/login')
            .send(data);
        console.log(data2.text)
    } catch (error) {
        console.log(error)
    }
}

module.exports = {sendLoginDataToTheServer}