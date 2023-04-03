// main.js

// Modules to control application life and create native browser window
const {app, BrowserWindow, ipcMain} = require('electron')
const path = require('path')
const https = require('https')
const userHandler = require('./logical_scripts/userHandler')
const checkInternetConnected = require('check-internet-connected')

let mainWindow;

const createMainWindow = () => {
    // Create the browser window.
    mainWindow = new BrowserWindow({
        minWidth: 1250,
        minHeight: 700,
        autoHideMenuBar: true,
        webPreferences: {
            contextIsolation: true,
            nodeIntegration: true,
            preload: path.join(__dirname, 'preload.js')
        }
    })

    // and load the index.html of the app.
    mainWindow.loadFile(__dirname + '/html/index.html')

    //Open the DevTools.
    //mainWindow.webContents.openDevTools()
}

let loadWindow;

//creating loading window
function createLoadingWindow() {
    loadWindow = new BrowserWindow({
        height: 300,
        width: 500,
        autoHideMenuBar: true,
        frame: false
    })

    loadWindow.loadFile(__dirname + "/html/loading.html")

}


//Getting user email and password
ipcMain.on("usernameAndPassword", function (event, data) {
    sendLoginDataToTheServer(data)
})

//This function send user Input email and password to the server
async function sendLoginDataToTheServer(data) {
    try {
        console.log(data)
        global.authToken = await userHandler.getAuthToken(data)
        if(authToken==false){
            console.log(authToken)
            mainWindow.webContents.send("error")
        }else{
            console.log(authToken)
        }
    } catch (err) {

    }
}

//This function runs every 1 sec to see internet connection
setInterval(checkInternetConnection,1000)


//this funtion checks the internet availability
function checkInternetConnection(){
    checkInternetConnected().then(function(result){
        if(mainWindow?.isDestroyed()){
            createMainWindow()
            loadWindow.close()
        }
    }).catch(function(err){
        mainWindow.close()
        createLoadingWindow()
        loadWindow.loadFile(__dirname + "/html/noConnection.html")
    })
}
// This method will be called when Electron has finished
// initialization and is ready to create browser windows.
// Some APIs can only be used after this event occurs.
app.whenReady().then(() => {
    createLoadingWindow()

    //checking internet connetion
    checkInternetConnected()
  .then((result) => {
    createMainWindow()
    loadWindow.close()    
    //successfully connected to a server
  })
  .catch((ex) => {
    loadWindow.loadFile(__dirname + "/html/noConnection.html")
    // cannot connect to a server or error occurred.
  });
    

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
