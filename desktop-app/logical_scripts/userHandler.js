const superagent = require('superagent').agent()

//This function send user Iput email and password to the server
async function sendLoginDataToTheServer(data) {
    try {
        console.log(data)
        data['dev'] = true;
        data2 = await superagent
            .post('http://localhost/api/v1/login')
            .send(data);


    } catch (error) {
        console.log(error)
    }
}

module.exports = {sendLoginDataToTheServer,data2}
