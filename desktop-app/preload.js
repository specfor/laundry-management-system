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

contextBridge.exposeInMainWorld('superagent',{
    post:(url)=>superagent.post(url),
    send:(data)=>superagent.send(data),
    set:(key,e)=>superagent.set(key,e),
    end:(error,response)=>superagent.end(error,response)
}
    )