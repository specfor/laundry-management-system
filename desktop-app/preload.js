const { contextBridge,ipcRenderer } = require('electron')
const Toastify = require("toastify-js")
const superagent = require('superagent');

contextBridge.exposeInMainWorld('ipcRenderer',{
    send:(channel,info)=>
        ipcRenderer.send(channel,info),
    on:(channel,listner) => 
        ipcRenderer.on(channel,(event,...args)=> listner(...args))
    })

contextBridge.exposeInMainWorld('Toastify',{
    alertToast:(info)=>
        Toastify(info).showToast()
})