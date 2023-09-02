<template>
    <div class="h-[10%] w-full shadow-md flex flex-row">
        <div class="grow p-2 flex flex-row gap-3">
            <router-link :to="{ name: 'NewOrder' }" #default="{ isExactActive }">
                <button class="btn-sm text-base rounded-sm" :class="{'btn-primary': isExactActive, 'btn-ghost': !isExactActive}">New Order</button>
            </router-link>
            <router-link :to="{ name: 'ViewOrder' }" #default="{ isExactActive }">
                <button class="btn-sm text-base rounded-sm" :class="{'btn-primary': isExactActive, 'btn-ghost': !isExactActive}">View Order</button>
            </router-link>
        </div>
        <div>Settings And Notifications</div>
    </div>
    <router-view></router-view>
</template>

<script setup lang="ts">
import { useAuthentication } from '@/composibles/authentication';

const { invalidateToken } = useAuthentication()
</script>
<style lang="">
    
</style>