const https = require('https');

const url = "https://www.google.com/";
https.get("https://www.google.com/",function(respone){
    console.log(respone.statusCode)
}).on('error',function(err){
  console.log('error')
})

if(data2._body.statusMessage=="success"){
  mainWindow.webContents.send("success")
}else{
  mainWindow.webContents.send("error")
}

ipcRenderer.on("success",function(event){
  Toastify.alertToast({
      text: "Login successfull!",
      className: "info",
      style: {
          background: "green",
          color:"white"
}
  })
})

ipcRenderer.on("error",function(event){
  Toastify.alertToast({
      text: "Email or password is incorrect!",
      className: "info",
      style: {
          background: "red",
          color:"white"
}
  })
})





//admin_{342365(_)08
  //rlsjp6)rg_34_)(23as