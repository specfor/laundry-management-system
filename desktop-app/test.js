const https = require('https');

const url = "https://www.google.com/";
https.get("https://www.google.com/",function(respone){
    console.log(respone.statusCode)
}).on('error',function(err){
  console.log('error')
})