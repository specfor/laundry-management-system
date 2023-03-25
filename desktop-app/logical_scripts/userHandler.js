const superagent = require('superagent')

//This function send user Iput email and password to the server
function sendLoginDataToTheServer(data) {
    try {
        console.log(data)
        superagent
            .post('http://localhost/api/v1/login')
            .send(data)
            .set('accept', 'json')
            .end((err, res) => {
                console.log(res.text)
            });

    } catch (error) {
        console.log(error)
    }
}

module.exports = {sendLoginDataToTheServer}