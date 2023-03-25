// main.js

// Modules to control application life and create native browser window
const { app, BrowserWindow,ipcMain } = require('electron')
const path = require('path')
const https = require('https')
const superagent = require('superagent')


let mainWindow;

const createMainWindow = () => {
  // Create the browser window.
    mainWindow = new BrowserWindow({
    minWidth: 1250,
    minHeight: 700,
    autoHideMenuBar: true,
    webPreferences:{
      contextIsolation:true,
      nodeIntegration:true,
      preload: path.join(__dirname,'preload.js')
  }
  })

  // and load the index.html of the app.
  mainWindow.loadFile(__dirname+'/html/index.html')

  //Open the DevTools.
  //mainWindow.webContents.openDevTools()
}

let loadWindow;
//creating loading window
function createLoadingWindow(){
    loadWindow = new BrowserWindow({
    height:300,
    width:500,
    autoHideMenuBar:true,
    frame:false
  })

  loadWindow.loadFile(__dirname+"/html/loading.html")

}



//Getting user email and password
ipcMain.on("usernameAndPassword",function(event,data){
    sendLoginDataToTheServer(data)
})

//This function send userIput email and password to the server
function sendLoginDataToTheServer(data){
  try{ 
    console.log(data)
    superagent
    .post('http://localhost/api/v1/login')
    .send(data) 
    .set('X-API-Key', 'foobar')
    .set('accept', 'json')
    .end((err, res) => {
      console.log(res.text)
    });

  }catch(error){
    console.log(error)
  }
}

// This method will be called when Electron has finished
// initialization and is ready to create browser windows.
// Some APIs can only be used after this event occurs.
app.whenReady().then(() => {
  createLoadingWindow()
  
  //checking internet connetion
  const url = "https://www.google.com/"; 
  https.get(url,function(respone){
    if(respone.statusCode==200){
      createMainWindow()
      loadWindow.hide()
    }
  }).on('error',function(err){
    loadWindow.loadFile(__dirname+"/html/noConnection.html")
  })


  app.on('activate', () => {
    // On macOS it's common to re-create a window in the app when the
    // dock icon is clicked and there are no other windows open.
    if (BrowserWindow.getAllWindows().length === 0) createLoadingWindow()
  })
})

// Quit when all windows are closed, except on macOS. There, it's common
// for applications and their menu bar to stay active until the user quits
// explicitly with Cmd + Q.
app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') app.quit()
})


// In this file you can include the rest of your app's specific main process
// code. You can also put them in separate files and require them here.
