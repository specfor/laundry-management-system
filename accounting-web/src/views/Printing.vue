<template>
    <div class="prose">
        <h3>Printing</h3>
    </div>
</template>

<script setup>
// @ts-check
import { whenever } from "@vueuse/core";
import { usePrinterService } from "../composibles/print-service";

const { serverStatus, getPrinters } = usePrinterService();

whenever(serverStatus, async () => {
    console.log(await getPrinters(true))
})

</script>

<style>
    
</style>