// main.js

// Modules to control application life and create native browser window
const {app, BrowserWindow, ipcMain} = require('electron')
const path = require('path')
const userHandler = require('./logical_scripts/userHandler')
const checkInternetConnected = require('check-internet-connected')
const easyinvoice = require('easyinvoice')
const fs = require('fs')
const superagent = require('superagent').agent()


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
    mainWindow.loadFile(__dirname + '/html/dashboard.html')
    //Open the DevTools.
    //mainWindow.webContents.openDevTools()
}

ipcMain.on("clientData",function(event,data){
    console.log(data)
})

//sending order details to the server
ipcMain.on("clientOrderDetails",function(event,data){
    sendClientDataToTheServer('http://localhost/api/v1/customers/add',data[0])
    //createInvoice(data)

    mainWindow.webContents.send("done")
})

 
async function createInvoice(orderInfo){
    //This describes the layout of the invoice
    let data = {
        "client": {
            "company": orderInfo[0].name,
            "address": orderInfo[0].address,
            "country": "Sri Lanka"
        },

        "sender": {
            "company": "LogicLeap Soltions",
            "address": "Rahula Rd.",
            "zip": "81000",
            "city": "Matara",
            "country": "Sri Lanka"
        },

        "images":{
            logo: fs.readFileSync('./assets/Images/logoMain.png', 'base64'),
        },

        "bottomNotice": "Kindly pay your invoice within 15 days.",

        "settings": {
            "currency": "lkr"
        },
    }

    data["products"] = []

    let b = 1;      
    while(b < orderInfo.length){


        let obj = {
            "quantity": orderInfo[b].amount,
            "description": orderInfo[b].item,
            "tax-rate": 0,
            "price": 1000
        }
        
        data["products"].push(obj)
        
        b++
 
    }

    await easyinvoice.createInvoice(data,function(result){
        fs.writeFileSync(`./invoices/${orderInfo[0].name}.pdf`,result.pdf, 'base64')
    })

    
}

let loadWindow;


function createLoadingWindow() {
    loadWindow = new BrowserWindow({
        height: 300,
        width: 500,
        autoHideMenuBar:false,
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
            mainWindow.loadFile(__dirname+"/html/dashboard.html")
        }
    } catch (err) {

    }
}
ipcMain.on("addNewClient",function(event,data){
    console.log(data)
    //sendClientDataToTheServer('http://localhost/api/v1/customers/add',data)
    mainWindow.webContents.send("successfullyAddedClient")
})


//send order details  to the server
async function sendClientDataToTheServer(url, data) {
    try {
        console.log(data)
        console.log(authToken)
        let data2 = await superagent
            .post(url)
            .auth(authToken, { type: 'bearer'})
            .send(data);
        console.log(data2.text)
        let resp = JSON.parse(data2.text)
        if (resp['statusMessage'] === 'success'){
            console.log('data added to database successfully.')
        }else{
            console.log(resp['body']['message'])
        }
    } catch (error) {
        console.log(error)
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
