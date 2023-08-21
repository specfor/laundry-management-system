<template>
    <ModelTemplate v-slot="{ resolve, reject }">
        <div class="fixed inset-0 bg-gray-300/30 flex items-center justify-center z-30">
            <div class="card w-96 bg-white shadow-lg">
                <div class="card-body items-center text-center">
                    <h2 class="card-title">{{ _header }}</h2>
                    <p>{{ _subHeader }}</p>
                    <div class="card-actions justify-end">
                        <button class="btn btn-error" @click="resolve({ action: 'confirm' })">{{ _confirmActionText }}</button>
                        <button class="btn btn-ghost" @click="reject({ action: 'cancel' })">{{ _cancelActionText }}</button>
                    </div>
                </div>
            </div>
        </div>
    </ModelTemplate>

    <!-- Loading Model -->
    <Teleport to="body">
        <template v-if="isLoadingModelVisible">
            <div class="fixed inset-0 bg-gray-300/30 flex items-center justify-center z-30">
                <form method="dialog" class="modal-box flex flex-row justify-center w-auto">
                    <span class="loading loading-spinner loading-lg"></span>
                    <div class="ml-5 prose">
                        <h3 class="font-bold text-xl p-0 m-0">{{ loadingModelHeader }}</h3>
                        <p>Please wait .... </p>
                    </div>
                </form>
            </div>
        </template>
    </Teleport>
</template>

<script setup lang="ts" generic="PassToSlot">
import { createTemplatePromise, useConfirmDialog } from '@vueuse/core';
import { type Ref, ref } from 'vue';

defineExpose({
    setup: (header: string, subHeader:string, confirmActionText?: string, cancelActionText?: string, passToSlot?: PassToSlot) => {
        _header.value = header;
        _subHeader.value = subHeader;
        confirmActionText ? _confirmActionText.value = confirmActionText : null;
        cancelActionText ? _cancelActionText.value = cancelActionText : null;
        passToSlot ? _passToSlot.value = passToSlot : null;

        const showLoading = (header?: string) => {
            header ? loadingModelHeader.value = header : null;
            revealLoadingModel()
        }

        return { start: ModelTemplate.start, showLoading, finish: hideLoadingModel }
    }
});

const _passToSlot = ref<PassToSlot | undefined>() as Ref<PassToSlot | undefined>;
const _header = ref("");
const _subHeader = ref("");
const _confirmActionText = ref("Confirm")
const _cancelActionText = ref("Cancel")

// For the Loading model after clicking save records
const { isRevealed: isLoadingModelVisible, reveal: revealLoadingModel, cancel: hideLoadingModel } = useConfirmDialog()
const loadingModelHeader = ref("Performing Action");

type ModelReturnType = {
    action: "cancel" | "confirm",
}

const ModelTemplate = createTemplatePromise<ModelReturnType>({
    transition: {
        name: 'model-content',
        appear: true,
    },
});
</script>

<style>
.model-content-enter-active,
.model-content-leave-active {
    transition: opacity 0.5s ease;
}

.model-content-enter-from,
.model-content-leave-to {
    opacity: 0;
}
</style>