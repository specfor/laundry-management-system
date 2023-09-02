<template lang="">
    <div>
        New Order
        <input type="text" placeholder="Type here" class="input input-bordered w-full max-w-xs" />
        <button class="btn" @click="invalidateToken()">Invalidate Token</button>
   </div>
</template>
<script setup lang="ts">
import { useAuthentication } from '@/composibles/authentication';

const { invalidateToken } = useAuthentication()
</script>
<style lang="">
    
</style>