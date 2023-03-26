const { contextBridge,ipcRenderer } = require('electron')
const Toastify = require("toastify-js")


contextBridge.exposeInMainWorld('ipcRenderer',{
    send:(channel,info)=>
        ipcRenderer.send(channel,info),
    on:(channel,listner) => 
        ipcRenderer.on(channel,(event,...args)=> listner(...args))
    })

contextBridge.exposeInMainWorld('Toastify',{
    alertToast:(info)=>
        Toastify(info).showToast()
<<<<<<< HEAD
})

=======
})
>>>>>>> 9262d07abd0df5927b6bea2b3adeae4a453fdf2f
