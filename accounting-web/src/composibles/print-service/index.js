// @ts-check

import { useFetch, watchOnce, whenever } from "@vueuse/core";
import { usePrintFetch } from "./print-fetch"
import { ref } from "vue";

export function usePrinterService() {
    
    /**
     * Retrieves a list of printers connected to the computer
     * @param {boolean} force Whether printer list should be refetched by the server. Cached list on the server will be disarded and refetched again
     * @returns {Promise<{ id: string, name: string, system_name: string }[]>}
     */
    const getPrinters = (force) => {
        return new Promise((resolve) => {
            const { data } = usePrintFetch(`/printer-list?force=${force}`, "Get printer list").json().get();
        
            watchOnce(data, () => resolve(data.value))
        })
    }

    /**
     * @typedef SetPrinterResponse
     * @property {boolean} success
     * @property {boolean} try_manual
     * @property {string} message
     */

    /**
     * Sets the printer belonging to the given ID as the bill printing printer. Will also try to share the printer with the name "Bill Printer"
     * @param {string} id Printer ID as per the printer list
     * @returns {Promise<SetPrinterResponse>}
     */
    const setPrinter = (id) => {
        return new Promise((resolve) => {
            const { data } = usePrintFetch(`/set-printer/${id}`, "Set Printer", true).json().get();
        
            watchOnce(data, () => resolve(data.value))
        })
    }

    /**
     * @typedef PrintResponse
     * @property {boolean} success
     * @property {boolean} ip_error
     * @property {string} message
     */

    /**
     * Sends the given string for printing to the printer
     * @param {string} text 
     * @returns {Promise<PrintResponse>}
     */
    const printText = (text) => {
        return new Promise((resolve) => {
            const { data } = usePrintFetch(`/print`, "Print").json().post({ content: text });
        
            watchOnce(data, () => resolve(data.value))
        })
    }

    /**
     * @typedef PrintBlockBase
     * @property {string} content
     * @property { "A" | "B" | "C" } [font]
     * @property { "U" | "B" | "U2" | "BU" | "BU2" | "NORMAL" } [style]
     * @property { "LT" | "RT" | "CT" } [alignment]
     * @property {0 | 2} [font_height]
     * @property {0 | 2} [font_width]
     * 
     * @typedef {import("ts-toolbelt").O.Either<PrintBlockBase, "font_height" | "font_width">} PrintBlock
     */

    /**
     * Prints a set of blocks
     * @param {PrintBlock[]} blocks 
     * @returns {Promise<PrintResponse>}
     */
    const printBlocks = (blocks) => {
        return new Promise((resolve) => {
            const { data } = usePrintFetch(`/print-blocks`, "Print").json().post(blocks);
        
            watchOnce(data, () => resolve(data.value))
        })
    }

    const serverStatus = () => {
        const online = ref(false);

        // Keep polling the server till a success response is recieved
        const poller = setInterval(() => {
            const { data } = useFetch("http://127.0.0.1").get();
            
            watchOnce(data, () => {
                clearInterval(poller);
                online.value = true;
            })
        }, 3000)

        return online;
    }

    return { serverStatus, printBlocks, printText, setPrinter, getPrinters };
}